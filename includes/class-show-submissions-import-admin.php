<?php
class Show_Submissions_Import_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'init', array( $this, 'schedule_cron_job' ) );
        add_action( 'show_submissions_import_cron', array( $this, 'import_shows_from_endpoints' ) );
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
        $this->handle_post_requests();
    }

    public function add_cron_schedules( $schedules ) {
        $endpoints = $this->get_endpoints();
        foreach ( $endpoints as $endpoint ) {
            if ( $endpoint->status === 'Active' ) {
                $schedules[ 'minutes_' . $endpoint->frequency ] = array(
                    'interval' => $endpoint->frequency * 60,
                    'display'  => __( $endpoint->frequency . ' minutes' ),
                );
            }
        }
        return $schedules;
    }

    public function schedule_cron_job() {
        $endpoints = $this->get_endpoints();
        foreach ( $endpoints as $endpoint ) {
            if ( $endpoint->status === 'Active' ) {
                if ( ! wp_next_scheduled( 'show_submissions_import_cron', array( $endpoint->id ) ) ) {
                    wp_schedule_event( \time(), 'minutes_' . $endpoint->frequency, 'show_submissions_import_cron', array( $endpoint->id ) );
                }
            } else {
                if ( wp_next_scheduled( 'show_submissions_import_cron', array( $endpoint->id ) ) ) {
                    wp_clear_scheduled_hook( 'show_submissions_import_cron', array( $endpoint->id ) );
                }
            }
        }
    }

    public function create_show_submissions( $data, $venue_name ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';

        foreach ( $data as $show ) {
            // This is an example of how to map the data from the API to the database.
            // You will need to adapt this to the actual structure of the API response.
            $wpdb->insert(
                $table_name,
                array(
                    'submitter_name'   => 'API Import',
                    'submitter_email'  => 'api@import.com',
                    'booking_name'     => 'API Import',
                    'booking_email'    => 'api@import.com',
                    'venue_name'       => $venue_name,
                    'venue_address'    => isset( $show['venue']['address'] ) ? $show['venue']['address'] : '',
                    'show_date'        => isset( $show['date'] ) ? $show['date'] : '',
                    'door_time'        => isset( $show['time'] ) ? $show['time'] : '',
                    'music_start_time' => isset( $show['time'] ) ? $show['time'] : '',
                    'performers'       => isset( $show['artist'] ) ? $show['artist'] : '',
                    'door_price'       => 0,
                    'ticket_price'     => 0,
                    'show_link'        => isset( $show['url'] ) ? $show['url'] : '',
                    'ticket_link'      => '',
                    'images'           => isset( $show['cover'] ) ? $show['cover'] : '',
                    'approved'         => 0,
                    'status'           => 'New',
                )
            );
        }
    }

    public function import_shows_from_endpoints( $endpoint_id ) {
        global $wpdb;
        $endpoints_table_name = $wpdb->prefix . 'lhxc_show_submission_endpoints';
        $endpoint = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $endpoints_table_name WHERE id = %d", $endpoint_id ) );

        if ( ! $endpoint ) {
            return;
        }

        $response = wp_remote_get( $endpoint->api_url );

        if ( is_wp_error( $response ) ) {
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = \json_decode( $body, true );

        if ( ! $data ) {
            return;
        }

        $this->create_show_submissions( $data, $endpoint->name );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'show-submissions',
            'Import Shows',
            'Import Shows',
            'manage_options',
            'show-submissions-import',
            array( $this, 'render_import_page' )
        );
    }

    public function render_import_page() {
        include_once SHOW_SUBMISSIONS_PATH . 'templates/admin-import.php';
    }

    public function handle_post_requests() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'add_endpoint' ) {
            $this->add_endpoint();
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_endpoint' ) {
            $this->delete_endpoint();
        }
    }

    public function add_endpoint() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submission_endpoints';

        $name      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $api_url   = isset( $_POST['api_url'] ) ? esc_url_raw( $_POST['api_url'] ) : '';
        $frequency = isset( $_POST['frequency'] ) ? \intval( $_POST['frequency'] ) : 0;
        $status    = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

        $wpdb->insert(
            $table_name,
            array(
                'name'      => $name,
                'api_url'   => $api_url,
                'frequency' => $frequency,
                'status'    => $status,
            )
        );
    }

    public function delete_endpoint() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submission_endpoints';
        $id = isset( $_GET['id'] ) ? \intval( $_GET['id'] ) : 0;
        $wpdb->delete( $table_name, array( 'id' => $id ) );

        // redirect back to the import page
        wp_redirect( admin_url( 'admin.php?page=show-submissions-import' ) );
        exit;
    }

    public function get_endpoints() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lhxc_show_submission_endpoints';
        return $wpdb->get_results( "SELECT * FROM $table_name" );
    }
}
