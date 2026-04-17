<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Show_Submissions_DB {
	public static function maybe_upgrade() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lhxc_show_submissions';

		// Ensure table exists before altering
		$table = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( $table !== $table_name ) {
			return; // Table not present yet; activator will create on activation
		}

		// Add status column if missing
		$status_col = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM %i LIKE %s', $table_name, 'status' ) );
		if ( ! $status_col ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %i ADD `status` varchar(20) NOT NULL DEFAULT %s AFTER `approved`', $table_name, 'New' ) );
			// Backfill any existing rows with New
			$wpdb->query( $wpdb->prepare( 'UPDATE %i SET `status` = %s WHERE `status` IS NULL OR `status` = %s', $table_name, 'New', '' ) );
		}
	}
}
