<?php
// Lightweight WordPress stubs to ease static analysis and CLI tooling.
// These are safe no-ops when WordPress is not loaded. They never override
// real WordPress functions because each is wrapped with function_exists checks.

// Guard to avoid multiple loads
if (!defined('SHOW_SUBMISSIONS_WP_STUBS_LOADED')) {
    define('SHOW_SUBMISSIONS_WP_STUBS_LOADED', true);

    // Common constants
    if (!defined('ABSPATH')) {
        // Best-effort base path; harmless in real WP where ABSPATH is already defined
        define('ABSPATH', dirname(__DIR__, 3) . '/');
    }

    // Common helpers
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) {
            return rtrim(dirname($file), '/\\') . '/';
        }
    }

    if (!function_exists('plugin_dir_url')) {
        function plugin_dir_url($file) {
            // Minimal placeholder suitable for tooling; real WP calculates plugin URL
            return '/wp-content/plugins/' . basename(dirname($file)) . '/';
        }
    }

    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { return false; }
    }

    if (!function_exists('add_shortcode')) {
        function add_shortcode($tag, $callback) { return false; }
    }

    if (!function_exists('register_activation_hook')) {
        function register_activation_hook($file, $callback) { /* no-op */ }
    }

    if (!function_exists('register_deactivation_hook')) {
        function register_deactivation_hook($file, $callback) { /* no-op */ }
    }

    if (!function_exists('admin_url')) {
        function admin_url($path = '') { return '/wp-admin/' . ltrim($path, '/'); }
    }
    if (!function_exists('get_the_title')) {
        function get_the_title($post = 0) {
            $id = is_object($post) ? (isset($post->ID) ? (int)$post->ID : 0) : (int)$post;
            $p = function_exists('get_post') ? get_post($id) : null;
            return $p && isset($p->post_title) ? $p->post_title : 'Stub Title';
        }
    }
    if (!function_exists('add_menu_page')) { function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null) { return null; } }
    if (!function_exists('add_submenu_page')) { function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '') { return null; } }

    if (!function_exists('get_option')) {
        function get_option($option, $default = false) { return $default; }
    }

    // Escaping/sanitizing
    if (!function_exists('esc_attr')) { function esc_attr($text) { return is_scalar($text) ? (string)$text : ''; } }
    if (!function_exists('esc_url')) { function esc_url($url) { return is_scalar($url) ? (string)$url : ''; } }
    if (!function_exists('esc_html')) { function esc_html($text) { return is_scalar($text) ? (string)$text : ''; } }
    if (!function_exists('esc_textarea')) { function esc_textarea($text) { return is_scalar($text) ? (string)$text : ''; } }
    if (!function_exists('esc_url_raw')) { function esc_url_raw($url) { return is_scalar($url) ? (string)$url : ''; } }
    if (!function_exists('sanitize_text_field')) { function sanitize_text_field($str) { return is_scalar($str) ? trim((string)$str) : ''; } }
    if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($str) { return is_scalar($str) ? trim((string)$str) : ''; } }
    if (!function_exists('sanitize_email')) { function sanitize_email($email) { return is_scalar($email) ? (string)$email : ''; } }
    if (!function_exists('sanitize_file_name')) { function sanitize_file_name($name) { return preg_replace('/[^A-Za-z0-9\._-]/', '', (string)$name); } }

    // Attribute helpers
    if (!function_exists('selected')) {
        function selected($selected, $current, $echo = true) {
            $result = ((string)$selected === (string)$current) ? ' selected="selected"' : '';
            if ($echo) { echo $result; } else { return $result; }
        }
    }
    if (!function_exists('checked')) {
        function checked($checked, $current = true, $echo = true) {
            $is_checked = (string)$checked === (string)$current || (!empty($checked) && $current === true);
            $result = $is_checked ? ' checked="checked"' : '';
            if ($echo) { echo $result; } else { return $result; }
        }
    }

    // Enqueue stubs
    if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') { /* no-op */ } }
    if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) { /* no-op */ } }
    if (!function_exists('wp_localize_script')) { function wp_localize_script($handle, $object_name, $l10n) { /* no-op */ } }
    if (!function_exists('wp_register_script')) { function wp_register_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) { /* no-op */ } }
    if (!function_exists('wp_register_style')) { function wp_register_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all') { /* no-op */ } }
    if (!function_exists('wp_add_inline_style')) { function wp_add_inline_style($handle, $data) { /* no-op */ } }
    if (!function_exists('register_block_type')) { function register_block_type($name, $args = array()) { /* no-op */ } }

    // Nonces and JSON responses
    if (!function_exists('wp_nonce_field')) {
        function wp_nonce_field($action = -1, $name = '_wpnonce') {
            echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="stubbed-nonce" />';
        }
    }
    if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($nonce, $action = -1) { return true; } }
    if (!function_exists('wp_create_nonce')) { function wp_create_nonce($action = -1) { return 'stubbed-nonce'; } }
    if (!function_exists('check_ajax_referer')) { function check_ajax_referer($action = -1, $query_arg = '_ajax_nonce', $die = true) { return true; } }
    if (!function_exists('check_admin_referer')) { function check_admin_referer($action = -1, $query_arg = '_wpnonce') { return true; } }
    if (!function_exists('wp_die')) { function wp_die($message = '') { die($message); } }
    if (!function_exists('wp_send_json_error')) {
        function wp_send_json_error($data = null) {
            @header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'data' => $data));
            return;
        }
    }
    if (!function_exists('wp_send_json_success')) {
        function wp_send_json_success($data = null) {
            @header('Content-Type: application/json');
            echo json_encode(array('success' => true, 'data' => $data));
            return;
        }
    }

    // Settings API helpers
    if (!function_exists('settings_fields')) { function settings_fields($option_group) { /* no-op */ } }
    if (!function_exists('do_settings_sections')) { function do_settings_sections($page) { /* no-op */ } }
    if (!function_exists('submit_button')) { function submit_button($text = 'Save Changes') { echo '<button type="submit">' . htmlspecialchars($text) . '</button>'; } }

    // Media and uploads
    if (!function_exists('wp_upload_dir')) {
        function wp_upload_dir() {
            $tmp = sys_get_temp_dir();
            return array(
                'path'    => $tmp,
                'url'     => '/wp-content/uploads',
                'basedir' => $tmp,
                'baseurl' => '/wp-content/uploads',
            );
        }
    }
    if (!function_exists('wp_check_filetype')) {
        function wp_check_filetype($filename) {
            $ext = strtolower(pathinfo((string)$filename, PATHINFO_EXTENSION));
            $type = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
            return array('ext' => $ext, 'type' => $type);
        }
    }
    if (!function_exists('wp_insert_post')) { function wp_insert_post($postarr) { return rand(1000, 9999); } }
    if (!function_exists('wp_insert_attachment')) { function wp_insert_attachment($attachment, $file = false, $parent = 0, $wp_error = false) { return rand(1000, 9999); } }
    if (!function_exists('is_wp_error')) { function is_wp_error($thing) { return false; } }
    if (!function_exists('wp_generate_attachment_metadata')) { function wp_generate_attachment_metadata($attach_id, $file) { return array('file' => $file); } }
    if (!function_exists('wp_update_attachment_metadata')) { function wp_update_attachment_metadata($attach_id, $data) { return true; } }
    if (!function_exists('has_post_thumbnail')) { function has_post_thumbnail($post_id) { return false; } }
    if (!function_exists('set_post_thumbnail')) { function set_post_thumbnail($post_id, $thumb_id) { return true; } }
    if (!function_exists('update_post_meta')) { function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') { return true; } }
    if (!function_exists('wp_mkdir_p')) {
        function wp_mkdir_p($dir) {
            if (is_dir($dir)) return true;
            return @mkdir($dir, 0777, true);
        }
    }
    if (!function_exists('wp_tempnam')) { function wp_tempnam($filename = '', $dir = '') { return tempnam(sys_get_temp_dir(), 'wp-'); } }
    if (!function_exists('media_handle_sideload')) { function media_handle_sideload($file_array, $post_id, $desc = null, $post_data = array()) { return rand(1000, 9999); } }
    if (!function_exists('current_user_can')) { function current_user_can($capability, ...$args) { return true; } }

    // Content retrieval
    if (!function_exists('get_posts')) { function get_posts($args = array()) { return array(); } }
    if (!function_exists('get_post')) {
        function get_post($id) {
            return (object) array('ID' => (int)$id, 'post_title' => 'Stub Title');
        }
    }
    if (!function_exists('register_setting')) { function register_setting($option_group, $option_name, $args = array()) { return true; } }
    if (!function_exists('add_settings_section')) { function add_settings_section($id, $title, $callback, $page) { return true; } }
    if (!function_exists('add_settings_field')) { function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()) { return true; } }
    if (!function_exists('update_option')) { function update_option($option, $value, $autoload = null) { return true; } }
    if (!function_exists('do_action')) { function do_action($tag, ...$args) { /* no-op */ } }
    if (!function_exists('rest_sanitize_boolean')) { function rest_sanitize_boolean($value) { return filter_var($value, FILTER_VALIDATE_BOOLEAN); } }

    // Database stub ($wpdb)
    if (!isset($GLOBALS['wpdb'])) {
        class WPDB_Stub {
            public $prefix = 'wp_';
            public function get_charset_collate() { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
            public function prepare($query, ...$args) { return (string)$query; }
            public function get_var($query) { return null; }
            public function insert($table, $data, $format = null) { return 1; }
            public function update($table, $data, $where, $format = null, $where_format = null) { return 1; }
            public function get_row($query) { return null; }
            public function get_results($query) { return array(); }
            public function query($sql) { return 1; }
            public function esc_like($text) { return addslashes((string)$text); }
        }
        $GLOBALS['wpdb'] = new WPDB_Stub();
    }

    // dbDelta stub used by activator
    if (!function_exists('dbDelta')) { function dbDelta($sql) { return true; } }
}