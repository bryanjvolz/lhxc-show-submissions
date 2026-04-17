<?php
if ( ! isset( $GLOBALS['wpdb'] ) ) {
	class WPDB_Stub {
		public $prefix = 'wp_';
		public function get_charset_collate() {
			return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
		public function prepare( $query, ...$args ) {
			return (string) $query; }
		public function get_var( $query ) {
			return null; }
		public function insert( $table, $data, $format = null ) {
			return 1; }
		public function update( $table, $data, $where, $format = null, $where_format = null ) {
			return 1; }
		public function get_row( $query ) {
			return null; }
		public function get_results( $query ) {
			return array(); }
		public function query( $sql ) {
			return 1; }
		public function esc_like( $text ) {
			return addslashes( (string) $text ); }
	}
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['wpdb'] = new WPDB_Stub();
}
