<?php
/**
 * Plugin Name: LHxC Show Submissions
 * Plugin URI: https://www.louisvillehardcore.com/
 * Description: A submission system for show bookings with flyer upload and administration.
 * Version: 1.0.0
 * Author: Bryan Volz
 * Author URI: https://www.louisvillehardcore.com/
 * Text Domain: show-submissions
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SHOW_SUBMISSIONS_PATH', plugin_dir_path(__FILE__));
define('SHOW_SUBMISSIONS_URL', plugin_dir_url(__FILE__));
define('SHOW_SUBMISSIONS_HOLDING_DIR', SHOW_SUBMISSIONS_PATH . 'holding/');

// Include required files
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-activator.php';
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-deactivator.php';
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-block.php';
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-admin.php';
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-settings.php';

// Initialize settings
new Show_Submissions_Settings();

// Activation/Deactivation hooks
register_activation_hook(__FILE__, array('Show_Submissions_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Show_Submissions_Deactivator', 'deactivate'));

// Initialize the plugin
add_action('init', 'initialize_show_submissions');

function initialize_show_submissions() {
    new Show_Submissions_Block();
    new Show_Submissions_Admin();
}

// Add this after the existing define statements
define('SHOW_SUBMISSIONS_DEV_MODE', false);
define('SHOW_SUBMISSIONS_DIST_URL', SHOW_SUBMISSIONS_URL . 'dist/');

// Add this function
function show_submissions_get_asset_url($asset) {
    if (SHOW_SUBMISSIONS_DEV_MODE) {
        return 'http://localhost:5173/' . $asset;
    }
    return SHOW_SUBMISSIONS_DIST_URL . $asset;
}