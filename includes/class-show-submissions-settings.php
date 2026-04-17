<?php
// Load stubs for non-WP tooling if core hooks are unavailable
if ( ! function_exists( 'add_action' ) ) {
	require_once __DIR__ . '/wp-stubs.php';
}
class Show_Submissions_Settings {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_update_checkbox_status', array( $this, 'update_checkbox_status' ) );
	}

	public function enqueue_admin_scripts( $hook ) {
		if ( 'show-submissions_page_show-submissions-settings' !== $hook ) {
			return;
		}

		wp_register_style(
			'show-submissions-settings',
			false,
			array(),
			'1.0.0'
		);
		wp_enqueue_style( 'show-submissions-settings' );

		wp_add_inline_style(
			'show-submissions-settings',
			'.notification-settings { background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; margin-top: 10px; }
            .notification-settings p { margin: 15px 0; }
            .notification-settings label { display: block; margin-bottom: 5px; }
            .notification-settings input[type="text"] { width: 100%; max-width: 400px; }
            .notification-settings .description { font-style: italic; color: #666; }'
		);

		wp_enqueue_script(
			'show-submissions-settings',
			plugin_dir_url( __DIR__ ) . 'dist/js/settings.min.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'show-submissions-settings',
			'showSubmissionsSettings',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'show_submissions_settings_nonce' ),
			)
		);
	}

	public function update_checkbox_status() {
		check_ajax_referer( 'show_submissions_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$type    = sanitize_text_field( $_POST['type'] );
		$id      = intval( $_POST['id'] );
		$checked = rest_sanitize_boolean( $_POST['checked'] );

		switch ( $type ) {
			case 'promoter':
				$option_name = 'show_submissions_promoter_status';
				break;
			case 'venue':
				$option_name = 'show_submissions_venue_status';
				break;
			case 'approval':
				$option_name = 'show_submissions_approval_notification';
				break;
			case 'new_event':
				$option_name = 'show_submissions_new_event_notification';
				break;
			default:
				wp_send_json_error( 'Invalid type' );
				return;
		}
		$current_values = get_option( $option_name, array() );

		if ( 'approval' === $type || 'new_event' === $type ) {
			$current_values = get_option( $option_name, array() );
			if ( ! is_array( $current_values ) ) {
				$current_values = array();
			}
			if ( 'enabled' === $id ) {
				$current_values['enabled'] = $checked;
				$current_values['to']      = isset( $current_values['to'] ) ? $current_values['to'] : '';
				$current_values['cc']      = isset( $current_values['cc'] ) ? $current_values['cc'] : '';
				$current_values['bcc']     = isset( $current_values['bcc'] ) ? $current_values['bcc'] : '';
			}
		} elseif ( $checked ) {
				$current_values[ $id ] = '1';
		} else {
			unset( $current_values[ $id ] );
		}

		update_option( $option_name, $current_values );
		wp_send_json_success(
			array(
				'type'    => $type,
				'id'      => $id,
				'checked' => esc_attr( $checked ),
				'values'  => $current_values,
			)
		);
	}

	public function add_settings_page() {
		add_submenu_page(
			'show-submissions',
			'Show Submissions Settings',
			'Settings',
			'manage_options',
			'show-submissions-settings',
			array( $this, 'render_settings_page' )
		);
	}

	// Remove the render_main_page method as it's not needed anymore
	public function render_main_page() {
		// Redirect to the admin class's main page
		do_action( 'show_submissions_render_admin_page' );
	}

	public function register_settings() {
		register_setting( 'show_submissions_settings', 'show_submissions_google_api_key' );
		register_setting( 'show_submissions_settings', 'show_submissions_delete_table' );
		register_setting( 'show_submissions_settings', 'show_submissions_promoter_status', array( $this, 'sanitize_checkbox_array' ) );
		register_setting( 'show_submissions_settings', 'show_submissions_venue_status', array( $this, 'sanitize_checkbox_array' ) );
		register_setting( 'show_submissions_settings', 'show_submissions_new_event_notification' );
		register_setting( 'show_submissions_settings', 'show_submissions_approval_notification' );

		add_settings_section(
			'show_submissions_promoters_section',
			'Promoters',
			array( $this, 'render_promoters_section' ),
			'show_submissions_settings'
		);

		add_settings_section(
			'show_submissions_venues_section',
			'Venues',
			array( $this, 'render_venues_section' ),
			'show_submissions_settings'
		);

		add_settings_section(
			'show_submissions_main_section',
			'API Settings',
			null,
			'show_submissions_settings'
		);

		add_settings_section(
			'show_submissions_notifications_section',
			'Notifications',
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
			'new_event_notification',
			'New Event Notification',
			array( $this, 'render_new_event_notification_field' ),
			'show_submissions_settings',
			'show_submissions_notifications_section'
		);

		add_settings_field(
			'approval_notification',
			'Event Approval Notification',
			array( $this, 'render_approval_notification_field' ),
			'show_submissions_settings',
			'show_submissions_notifications_section'
		);

		add_settings_field(
			'google_api_key',
			'Google Places API Key',
			array( $this, 'render_api_key_field' ),
			'show_submissions_settings',
			'show_submissions_main_section'
		);

		add_settings_field(
			'delete_table',
			'Delete Data on Uninstall',
			array( $this, 'render_delete_table_field' ),
			'show_submissions_settings',
			'show_submissions_danger_section'
		);
	}

	public function render_api_key_field() {
		$api_key = get_option( 'show_submissions_google_api_key' );
		?>
		<input type="text"
				name="show_submissions_google_api_key"
				value="<?php echo esc_attr( $api_key ); ?>"
				class="regular-text">
		<p class="description">
			Enter your Google Places API key. You can get one from the
			<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>.
		</p>
		<?php
	}

	public function render_delete_table_field() {
		$delete_table = get_option( 'show_submissions_delete_table', false );
		?>
		<label>
			<input type="checkbox"
					name="show_submissions_delete_table"
					value="1"
					<?php checked( $delete_table, true ); ?>>
			Delete all show submissions data when plugin is deactivated
		</label>
		<p class="description" style="color: #d63638;">
			Warning: This will permanently delete all show submissions data when the plugin is deactivated. This action cannot be undone.
		</p>
		<?php
	}

	public function sanitize_checkbox_array( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		return array_map( 'sanitize_text_field', $input );
	}

	public function render_promoters_section() {
		$saved_statuses = get_option( 'show_submissions_promoter_status', array() );
		$organizers     = get_posts(
			array(
				'post_type'      => 'tribe_organizer',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( empty( $organizers ) ) {
			echo '<p>No promoters found.</p>';
			return;
		}

		echo '<style>
            .settings-list {
                display: flex;
                flex-wrap: wrap;
                list-style: none;
                padding: 0;
                margin: 0;
                gap: 10px;
            }
            .settings-list li {
                flex: 1 1 calc(20% - 10px);
                min-width: 200px;
                padding: 5px;
            }
            .settings-list label {
                display: flex;
                align-items: center;
                gap: 5px;
            }
        </style>';

		echo '<ul class="settings-list">';
		foreach ( $organizers as $organizer ) {
			$checked = isset( $saved_statuses[ $organizer->ID ] ) ? 'checked' : '';
			echo '<li>';
			printf(
				'<label><input type="checkbox" class="status-checkbox" data-type="promoter" data-id="%s" name="show_submissions_promoter_status[%s]" value="1" %s> %s</label>',
				esc_attr( $organizer->ID ),
				esc_attr( $organizer->ID ),
				esc_attr( $checked ),
				esc_html( $organizer->post_title )
			);
			echo '</li>';
		}
		echo '</ul>';
	}

	public function render_venues_section() {
		$saved_statuses = get_option( 'show_submissions_venue_status', array() );
		$venues         = get_posts(
			array(
				'post_type'      => 'tribe_venue',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( empty( $venues ) ) {
			echo '<p>No venues found.</p>';
			return;
		}

		echo '<ul class="settings-list">';
		foreach ( $venues as $venue ) {
			$checked = isset( $saved_statuses[ $venue->ID ] ) ? 'checked' : '';
			echo '<li>';
			printf(
				'<label><input type="checkbox" class="status-checkbox" data-type="venue" data-id="%s" name="show_submissions_venue_status[%s]" value="1" %s> %s</label>',
				esc_attr( $venue->ID ),
				esc_attr( $venue->ID ),
				esc_attr( $checked ),
				esc_html( $venue->post_title )
			);
			echo '</li>';
		}
		echo '</ul>';
	}

	public function render_new_event_notification_field() {
		$settings = get_option(
			'show_submissions_new_event_notification',
			array(
				'enabled' => false,
				'to'      => '',
				'cc'      => '',
				'bcc'     => '',
			)
		);
		?>
		<div class="notification-settings">
			<p>
				<label>
					<input type="checkbox"
							class="status-checkbox"
							data-type="new_event"
							data-id="enabled"
							name="show_submissions_new_event_notification[enabled]"
							value="1"
							<?php checked( isset( $settings['enabled'] ) && $settings['enabled'] ); ?>>
					Enable email notification for new event submissions
				</label>
			</p>
			<p>
				<label>To:<br>
					<input type="text"
							name="show_submissions_new_event_notification[to]"
							value="<?php echo esc_attr( $settings['to'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p>
				<label>CC:<br>
					<input type="text"
							name="show_submissions_new_event_notification[cc]"
							value="<?php echo esc_attr( $settings['cc'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p>
				<label>BCC:<br>
					<input type="text"
							name="show_submissions_new_event_notification[bcc]"
							value="<?php echo esc_attr( $settings['bcc'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p class="description">Separate multiple email addresses with commas.</p>
		</div>
		<?php
	}

	public function render_approval_notification_field() {
		$settings = get_option(
			'show_submissions_approval_notification',
			array(
				'enabled' => false,
				'to'      => '',
				'cc'      => '',
				'bcc'     => '',
			)
		);
		?>
		<div class="notification-settings">
			<p>
				<label>
					<input type="checkbox"
							class="status-checkbox"
							data-type="approval"
							data-id="enabled"
							name="show_submissions_approval_notification[enabled]"
							value="1"
							<?php checked( isset( $settings['enabled'] ) && $settings['enabled'] ); ?>>
					Enable email notification when an event is approved
				</label>
			</p>
			<p>
				<label>To:<br>
					<input type="text"
							name="show_submissions_approval_notification[to]"
							value="<?php echo esc_attr( $settings['to'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p>
				<label>CC:<br>
					<input type="text"
							name="show_submissions_approval_notification[cc]"
							value="<?php echo esc_attr( $settings['cc'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p>
				<label>BCC:<br>
					<input type="text"
							name="show_submissions_approval_notification[bcc]"
							value="<?php echo esc_attr( $settings['bcc'] ); ?>"
							class="regular-text">
				</label>
			</p>
			<p class="description">Separate multiple email addresses with commas.</p>
		</div>
		<?php
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>Show Submissions Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'show_submissions_settings' );
				do_settings_sections( 'show_submissions_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}