<?php
class Show_Submissions_Block {
    public function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('wp_ajax_submit_show', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_submit_show', array($this, 'handle_submission'));
        add_action('wp_ajax_add_to_media_library', array($this, 'add_to_media_library'));
        add_shortcode('show_submission_form', array($this, 'render_form')); // Add this line
    }

    public function register_block() {
        register_block_type('show-submissions/submission-form', array(
            'editor_script' => 'show-submissions-block',
            'render_callback' => array($this, 'render_form')
        ));

        wp_register_script(
            'show-submissions-block',
            SHOW_SUBMISSIONS_URL . 'js/block.js',  // Note: not using Vite for this file
            array('wp-blocks', 'wp-element')
        );
    }

    public function render_form() {
        $api_key = get_option('show_submissions_google_api_key');

        if ($api_key) {
            wp_enqueue_script(
                'google-places',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=initGooglePlacesAutocomplete',
                array(),
                null,
                true
            );
        }

        wp_enqueue_script(
            'show-submissions-form',
            show_submissions_get_asset_url('js/form.min.js'),
            array('jquery', 'google-places'),
            '1.0.0',
            true
        );
        wp_enqueue_style(
            'show-submissions-style',
            show_submissions_get_asset_url('css/style.min.css')
        );

        // Add this code to localize the script
        wp_localize_script(
            'show-submissions-form',
            'showSubmissions',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('show_submission_nonce')
            )
        );

        ob_start();
        include SHOW_SUBMISSIONS_PATH . 'templates/form.php';
        return ob_get_clean();
    }

    public function handle_submission() {
        // Check nonce
        if (!check_ajax_referer('submit_show_nonce', '_ajax_nonce', false)) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $upload_dir = SHOW_SUBMISSIONS_PATH . 'assets/submissions/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Collect form data
        $submission_data = array(
            'submitter_name' => sanitize_text_field($_POST['submitter_name']),
            'submitter_email' => sanitize_email($_POST['submitter_email']),
            'booking_name' => sanitize_text_field($_POST['booking_name']),
            'booking_email' => sanitize_email($_POST['booking_email']),
            'venue_name' => sanitize_text_field($_POST['venue_name']),
            'venue_address' => sanitize_textarea_field($_POST['venue_address']),
            'show_date' => sanitize_text_field($_POST['show_date']),
            'door_time' => sanitize_text_field($_POST['door_time']),
            'music_start_time' => sanitize_text_field($_POST['music_start_time']),
            'performers' => sanitize_textarea_field($_POST['performers']),
            'door_price' => floatval($_POST['door_price']),
            'ticket_price' => floatval($_POST['ticket_price']),
            'show_link' => esc_url_raw($_POST['show_link']),
            'ticket_link' => esc_url_raw($_POST['ticket_link'])
        );

        // Handle venue address components
        if (isset($_POST['venue_street'])) {
            $venue_components = array(
                'street' => sanitize_text_field($_POST['venue_street']),
                'city' => sanitize_text_field($_POST['venue_city']),
                'state' => sanitize_text_field($_POST['venue_state']),
                'zip' => sanitize_text_field($_POST['venue_zip'])
            );

            // Create full address if individual components are provided
            if (!empty(array_filter($venue_components))) {
                $submission_data['venue_address'] = implode(', ', array_filter($venue_components));
            }
        }

        // Handle file uploads
        $uploaded_files = array();
        if (!empty($_FILES['images'])) {
            $files = $_FILES['images'];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $files['tmp_name'][$i];
                    $name = sanitize_file_name($files['name'][$i]);
                    $file_info = wp_check_filetype($name);

                    // Verify file type
                    if (!in_array($file_info['ext'], array('jpg', 'jpeg', 'png'))) {
                        continue;
                    }

                    // Generate unique filename
                    $filename = uniqid() . '.' . $file_info['ext'];
                    $destination = $upload_dir . $filename;

                    // Move file
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_files[] = $filename;
                    }
                }
            }
        }

        // Add images to submission data
        $submission_data['images'] = serialize($uploaded_files);

        // Store in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';

        $result = $wpdb->insert(
            $table_name,
            $submission_data,
            array(
                '%s', // submitter_name
                '%s', // submitter_email
                '%s', // booking_name
                '%s', // booking_email
                '%s', // venue_name
                '%s', // venue_address
                '%s', // show_date
                '%s', // door_time
                '%s', // music_start_time
                '%s', // performers
                '%f', // price
                '%s', // show_link
                '%s'  // ticket_link
            )
        );

        if ($result === false) {
            wp_send_json_error('Failed to save submission');
            return;
        }

        $submission_id = $wpdb->insert_id;

        // Update the images field if files were uploaded
        if (!empty($uploaded_files)) {
            $wpdb->update(
                $table_name,
                array('images' => serialize($uploaded_files)),
                array('id' => $submission_id),
                array('%s'),
                array('%d')
            );
        }

        wp_send_json_success(array(
            'message' => 'Show submission received successfully!',
            'submission_id' => $submission_id
        ));
    }
}