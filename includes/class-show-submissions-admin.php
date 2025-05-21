<?php
class Show_Submissions_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 10); // Lower priority number to run first
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
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
            'index.php',
            'Submission Details',
            'Submission Details',
            'edit_posts',
            'show-submission-details',
            array($this, 'render_submission_details')
        );
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

        $data = array(
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

        $wpdb->update(
            $table_name,
            $data,
            array('id' => intval($_POST['submission_id'])),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s'),
            array('%d')
        );

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
                'nonce' => wp_create_nonce('show_submissions_admin_nonce')
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
                                <img src="<?php echo esc_url($image_path); ?>"
                                     alt="Show Flyer"
                                     style="width: 50px; height: 50px; object-fit: cover;">
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
}