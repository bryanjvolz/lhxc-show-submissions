<?php
class Show_Submissions_Settings {
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'), 20);
    }

    public function add_settings_page() {
        add_submenu_page(
            'show-submissions',
            'Show Submissions Settings',
            'Settings',
            'manage_options',
            'show-submissions-settings',
            array($this, 'render_settings_page')
        );
    }

    // Remove the render_main_page method as it's not needed anymore
    public function render_main_page() {
        // Redirect to the admin class's main page
        do_action('show_submissions_render_admin_page');
    }

    public function register_settings() {
        register_setting('show_submissions_settings', 'show_submissions_google_api_key');
        register_setting('show_submissions_settings', 'show_submissions_delete_table');

        add_settings_section(
            'show_submissions_main_section',
            'API Settings',
            null,
            'show_submissions_settings'
        );

        add_settings_section(
            'show_submissions_danger_section',
            'Danger Zone',
            null,
            'show_submissions_settings'
        );

        add_settings_field(
            'google_api_key',
            'Google Places API Key',
            array($this, 'render_api_key_field'),
            'show_submissions_settings',
            'show_submissions_main_section'
        );

        add_settings_field(
            'delete_table',
            'Delete Data on Uninstall',
            array($this, 'render_delete_table_field'),
            'show_submissions_settings',
            'show_submissions_danger_section'
        );
    }

    public function render_api_key_field() {
        $api_key = get_option('show_submissions_google_api_key');
        ?>
        <input type="text"
               name="show_submissions_google_api_key"
               value="<?php echo esc_attr($api_key); ?>"
               class="regular-text">
        <p class="description">
            Enter your Google Places API key. You can get one from the
            <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>.
        </p>
        <?php
    }

    public function render_delete_table_field() {
        $delete_table = get_option('show_submissions_delete_table', false);
        ?>
        <label>
            <input type="checkbox"
                   name="show_submissions_delete_table"
                   value="1"
                   <?php checked($delete_table, true); ?>>
            Delete all show submissions data when plugin is deactivated
        </label>
        <p class="description" style="color: #d63638;">
            Warning: This will permanently delete all show submissions data when the plugin is deactivated. This action cannot be undone.
        </p>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Show Submissions Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('show_submissions_settings');
                do_settings_sections('show_submissions_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}