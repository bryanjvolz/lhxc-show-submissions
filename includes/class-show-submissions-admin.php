<?php
// Load stubs for non-WP tooling if core hooks are unavailable
if (!function_exists('add_action')) {
    require_once __DIR__ . '/wp-stubs.php';
}
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-constants.php';

class Show_Submissions_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 10);
        // Decorate main menu label with a bubble showing 'New' count
        add_action('admin_menu', array($this, 'decorate_menu_with_new_count'), 99);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Add AJAX handlers
        add_action('wp_ajax_update_submission_approval', array($this, 'update_submission_approval'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Show Submissions',
            'Show Submissions',
            'edit_posts',
            'show-submissions',
            array($this, 'render_admin_page'),
            'dashicons-calendar-alt',
            30
        );

        // Add hidden submenu page for details
        add_submenu_page(
            null, // Setting parent slug to null hides the page from menu
            'Submission Details',
            'Submission Details',
            'edit_posts',
            'show-submission-details',
            array($this, 'render_submission_details')
        );
    }

    /**
     * Adds a red bubble badge to the main "Show Submissions" admin menu item
     * displaying the number of submissions marked as 'New'.
     */
    public function decorate_menu_with_new_count() {
        global $menu, $wpdb;

        if (!is_array($menu)) {
            return;
        }

        // Count submissions with status 'New'
        $table = $wpdb->prefix . 'lhxc_show_submissions';
        $count = 0;
        $sql = "SELECT COUNT(*) FROM {$table} WHERE status = 'New'";
        $db_count = $wpdb->get_var($sql);
        if (is_numeric($db_count)) {
            $count = (int)$db_count;
        }

        if ($count <= 0) {
            return; // No bubble when there are no new submissions
        }

        $display_count = function_exists('number_format_i18n') ? number_format_i18n($count) : (string)$count;

        // Find our top-level menu item by slug and append the bubble
        foreach ($menu as $index => $item) {
            if (!empty($item[2]) && $item[2] === 'show-submissions') {
                $menu[$index][0] = 'Show Submissions ' .
                    '<span class="update-plugins count-' . $count . '"><span class="update-count">' . esc_html($display_count) . '</span></span>';
                break;
            }
        }
    }

    public function render_submission_details() {
        if (!isset($_GET['id'])) {
            wp_die('Invalid submission ID');
        }

        // Handle form submission
        if (isset($_POST['save_submission'])) {
            $this->save_submission_details();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            intval($_GET['id'])
        ));

        if (!$submission) {
            wp_die('Submission not found');
        }

        include SHOW_SUBMISSIONS_PATH . 'templates/admin-details.php';
    }

    private function save_submission_details() {
        check_admin_referer('save_submission_details', 'submission_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            intval($_POST['submission_id'])
        ));

        // Allowed statuses
        $allowed_statuses = array('New', 'Edited', 'Approved', 'Archived');
        $posted_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $posted_status = in_array($posted_status, $allowed_statuses, true) ? $posted_status : '';

        // If currently Approved and still Approved, lock fields except status/approved
        if ($existing && isset($existing->status) && $existing->status === 'Approved' && $posted_status === 'Approved') {
            $data = array(
                'status' => 'Approved',
                'approved' => 1
            );
        } else {
            $data = array(
                'submitter_name' => sanitize_text_field($_POST['submitter_name'] ?? ''),
                'submitter_email' => sanitize_email($_POST['submitter_email'] ?? ''),
                'booking_name' => sanitize_text_field($_POST['booking_name'] ?? ''),
                'booking_email' => sanitize_email($_POST['booking_email'] ?? ''),
                'venue_name' => sanitize_text_field($_POST['venue_name'] ?? ''),
                'venue_address' => sanitize_textarea_field($_POST['venue_address'] ?? ''),
                'show_date' => sanitize_text_field($_POST['show_date'] ?? ''),
                'door_time' => sanitize_text_field($_POST['door_time'] ?? ''),
                'time_zone' => sanitize_text_field($_POST['time_zone'] ?? ''),
                'music_start_time' => sanitize_text_field($_POST['music_start_time'] ?? ''),
                'performers' => sanitize_textarea_field($_POST['performers'] ?? ''),
                'door_price' => isset($_POST['door_price']) ? floatval($_POST['door_price']) : 0,
                'ticket_price' => isset($_POST['ticket_price']) ? floatval($_POST['ticket_price']) : 0,
                'show_link' => esc_url_raw($_POST['show_link'] ?? ''),
                'ticket_link' => esc_url_raw($_POST['ticket_link'] ?? ''),
                'approved' => intval($_POST['approved'] ?? 0),
            );

            // Determine status: explicit selection wins; otherwise set to Edited on save
            if ($posted_status) {
                $data['status'] = $posted_status;
            } else {
                $data['status'] = 'Edited';
            }

            // Sync approved and status consistency
            if ($data['approved'] === 1 || $data['status'] === 'Approved') {
                $data['approved'] = 1;
                $data['status'] = 'Approved';
            } elseif ($data['status'] !== 'Approved') {
                // When not approved ensure approved flag is 0 unless explicitly set
                $data['approved'] = intval($_POST['approved'] ?? 0);
            }
        }

        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => intval($_POST['submission_id'])),
            // Formats: allow for both minimal (locked) or full updates
            array_map(function($k){
                // Basic format mapping for known fields
                $map = array(
                    'submitter_name' => '%s',
                    'submitter_email' => '%s',
                    'booking_name' => '%s',
                    'booking_email' => '%s',
                    'venue_name' => '%s',
                    'venue_address' => '%s',
                    'show_date' => '%s',
                    'door_time' => '%s',
                    'time_zone' => '%s',
                    'music_start_time' => '%s',
                    'performers' => '%s',
                    'door_price' => '%f',
                    'ticket_price' => '%f',
                    'show_link' => '%s',
                    'ticket_link' => '%s',
                    'approved' => '%d',
                    'status' => '%s',
                );
                return $map[$k] ?? '%s';
            }, array_keys($data)),
            array('%d')
        );

        if ($result === false) {
            $error = $wpdb->last_error ? $wpdb->last_error : 'Unknown DB error';
            add_action('admin_notices', function() use ($error) {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to save submission: ' . esc_html($error) . '</p></div>';
            });
            return;
        }

        // Create TEC event when approval flips from 0 -> 1
        // Re-read to determine approval transition, considering status logic above
        $nowApproved = (isset($data['approved']) && intval($data['approved']) === 1) ? 1 : 0;
        if ($existing && intval($existing->approved) === 0 && $nowApproved === 1) {
            $submission = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                intval($_POST['submission_id'])
            ));

            if ($submission) {
                $event_date = $submission->show_date;
                $start_time = $submission->music_start_time;
                $door_time = $submission->door_time;

                $venue_title = is_numeric($submission->venue_name)
                    ? (function_exists('get_the_title') ? get_the_title((int)$submission->venue_name) : ('Venue #' . (int)$submission->venue_name))
                    : $submission->venue_name;

                $event_data = array(
                    'post_title'   => sprintf('Show at %s', $venue_title),
                    'post_content' => $submission->performers,
                    'post_status'  => 'publish',
                    'post_type'    => 'tribe_events',
                    'meta_input'   => array(
                        '_EventStartDate'    => trim($event_date . ' ' . $start_time),
                        '_EventEndDate'      => trim($event_date . ' ' . date('H:i:s', strtotime($start_time . ' +3 hours'))),
                        '_EventVenueID'      => $this->get_or_use_venue_id($submission),
                        '_EventURL'          => $submission->show_link,
                        '_EventCost'         => $submission->ticket_price,
                        '_EventDoorTime'     => $door_time,
                        '_EventShowLink'     => $submission->show_link,
                        '_EventTicketLink'   => $submission->ticket_link
                    )
                );

                $event_id = wp_insert_post($event_data);
                if (!is_wp_error($event_id) && !empty($submission->images)) {
                    $images = explode(',', $submission->images);
                    foreach ($images as $image) {
                        $image_path = SHOW_SUBMISSIONS_HOLDING_DIR . $image;
                        if (file_exists($image_path)) {
                            $this->attach_image_to_event($event_id, $image_path);
                        }
                    }
                }
            }
        }

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Submission updated successfully.</p></div>';
        });
    }

    public function enqueue_admin_assets($hook) {
        if (!in_array($hook, array('toplevel_page_show-submissions', 'admin_page_show-submission-details'))) {
            return;
        }

        wp_enqueue_style('show-submissions-admin', show_submissions_get_asset_url('css/admin.min.css'));
        wp_enqueue_script('show-submissions-admin', show_submissions_get_asset_url('js/admin.min.js'), array('jquery'), '1.0.0', true);

        // Add localization data
        wp_localize_script(
            'show-submissions-admin',
            'showSubmissionsAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('show_submissions_admin')  // Remove '_nonce' suffix
            )
        );
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';
        $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        ?>
        <div class="wrap">
            <h1>Show Submissions</h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Flyer</th>
                        <th>Date Submitted</th>
                        <th>Show Date</th>
                        <th>Venue</th>
                        <th>Performers</th>
                        <th>Approval Status</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission):
                        $images = unserialize($submission->images);
                        $first_image = !empty($images) ? $images[0] : '';
                        $image_path = $first_image ? SHOW_SUBMISSIONS_URL . 'assets/submissions/' . $first_image : SHOW_SUBMISSIONS_URL . 'assets/placeholder.png';
                    ?>
                        <tr data-id="<?php echo esc_attr($submission->id); ?>">
                            <td>
                                <a href="<?php echo esc_url($image_path); ?>" target="_blank">
                                    <img src="<?php echo esc_url($image_path); ?>"
                                     alt="Show Flyer"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                                </a>
                            </td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($submission->created_at))); ?></td>
                            <td><?php echo esc_html($submission->show_date); ?></td>
                            <td><?php echo esc_html($submission->venue_name); ?></td>
                            <td><?php echo esc_html($submission->performers); ?></td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" class="approval-toggle"
                                           <?php echo $submission->approved ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td><?php echo esc_html(isset($submission->status) ? $submission->status : ''); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=show-submission-details&id=' . $submission->id); ?>"
                                   class="button">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function update_submission_approval() {
        // Get and validate parameters
        $submission_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $approved = isset($_POST['approved']) ? (bool)$_POST['approved'] : false;

        if (!$submission_id) {
            wp_send_json_error('Invalid submission ID');
        }

        // Update the approval status
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';

        // Get the submission data first
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            wp_send_json_error('Submission not found');
        }

        $update_data = array('approved' => $approved ? 1 : 0);
        if ($approved) {
            $update_data['status'] = 'Approved';
        }

        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $submission_id),
            array_map(function($k){ return $k === 'approved' ? '%d' : '%s'; }, array_keys($update_data)),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error('Failed to update approval status');
        }

        // If approved, create an event
        if ($approved) {
            // Format the event date and times
            $event_date = $submission->show_date;
            $start_time = $submission->music_start_time;
            $door_time = $submission->door_time;

            // Create the event
            $event_data = array(
                'post_title'   => sprintf('Show at %s', $submission->venue_name),
                'post_content' => $submission->performers,
                'post_status'  => 'publish',
                'post_type'    => 'tribe_events',
                'meta_input'   => array(
                    '_EventStartDate'    => $event_date . ' ' . $start_time,
                    '_EventEndDate'      => $event_date . ' ' . date('H:i:s', strtotime($start_time . ' +3 hours')),
                    '_EventVenueID'      => $this->get_or_create_venue($submission),
                    '_EventURL'          => $submission->show_link,
                    '_EventCost'         => $submission->ticket_price,
                    '_EventDoorTime'     => $door_time,
                    '_EventShowLink'     => $submission->show_link,
                    '_EventTicketLink'   => $submission->ticket_link
                )
            );

            $event_id = wp_insert_post($event_data);

            if (is_wp_error($event_id)) {
                $err_msg = (is_object($event_id) && method_exists($event_id, 'get_error_message'))
                    ? $event_id->get_error_message()
                    : 'Unknown error';
                wp_send_json_error('Failed to create event: ' . $err_msg);
            }

            // If there are images, attach them to the event
            if (!empty($submission->images)) {
                $images = explode(',', $submission->images);
                foreach ($images as $image) {
                    $image_path = SHOW_SUBMISSIONS_HOLDING_DIR . $image;
                    if (file_exists($image_path)) {
                        $this->attach_image_to_event($event_id, $image_path);
                    }
                }
            }
        }

        wp_send_json_success();
    }

    private function get_or_create_venue($submission) {
        // Check if venue exists
        $venues = get_posts(array(
            'post_type' => 'tribe_venue',
            'title' => $submission->venue_name,
            'posts_per_page' => 1
        ));

        if (!empty($venues)) {
            return $venues[0]->ID;
        }

        // Create new venue
        $venue_data = array(
            'post_title' => $submission->venue_name,
            'post_type' => 'tribe_venue',
            'post_status' => 'publish',
            'meta_input' => array(
                '_VenueAddress' => $submission->venue_address
            )
        );

        $venue_id = wp_insert_post($venue_data);
        return is_wp_error($venue_id) ? 0 : $venue_id;
    }

    private function get_or_use_venue_id($submission) {
        if (isset($submission->venue_name) && is_numeric($submission->venue_name)) {
            return (int)$submission->venue_name;
        }
        return $this->get_or_create_venue($submission);
    }

    private function attach_image_to_event($event_id, $image_path) {
        $wp_upload_dir = wp_upload_dir();
        $filename = basename($image_path);
        $new_path = $wp_upload_dir['path'] . '/' . $filename;

        // Copy file to uploads directory
        copy($image_path, $new_path);

        // Prepare attachment data
        $wp_filetype = wp_check_filetype($filename);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Insert attachment
        $attach_id = wp_insert_attachment($attachment, $new_path, $event_id);

        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $new_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Set as featured image if it's the first image
        if (!has_post_thumbnail($event_id)) {
            set_post_thumbnail($event_id, $attach_id);
        }
    }
}