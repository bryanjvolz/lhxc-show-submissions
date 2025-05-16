<?php
class Show_Submissions_Deactivator {
    public static function deactivate() {
        $delete_table = get_option('show_submissions_delete_table', false);
        
        if ($delete_table) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'lhxc_show_submissions';
            
            // Drop the table
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
            
            // Delete the plugin options
            delete_option('show_submissions_google_api_key');
            delete_option('show_submissions_delete_table');
        }
    }
}