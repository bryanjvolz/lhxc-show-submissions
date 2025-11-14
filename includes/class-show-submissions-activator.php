<?php
class Show_Submissions_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'lhxc_show_submissions';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            submitter_name varchar(100) NOT NULL,
            submitter_email varchar(100) NOT NULL,
            booking_name varchar(100) NOT NULL,
            booking_email varchar(100) NOT NULL,
            venue_name varchar(100) NOT NULL,
            venue_address text NOT NULL,
            show_date date NOT NULL,
            door_time time NOT NULL,
            music_start_time time NOT NULL,
            performers text NOT NULL,
            door_price decimal(10,2) NOT NULL,
            ticket_price decimal(10,2) NOT NULL,
            show_link varchar(255),
            ticket_link varchar(255),
            images text,
            approved tinyint(1) DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'New',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Create holding directory
        if (!file_exists(SHOW_SUBMISSIONS_HOLDING_DIR)) {
            wp_mkdir_p(SHOW_SUBMISSIONS_HOLDING_DIR);
        }
    }
}