<?php
// Load stubs for non-WP tooling if core hooks are unavailable
if ( ! function_exists( 'add_action' ) ) {
	require_once __DIR__ . '/wp-stubs.php';
}
class Show_Submissions_Block {
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_ajax_submit_show', array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_submit_show', array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_add_to_media_library', array( $this, 'add_to_media_library' ) );
		add_shortcode( 'show_submission_form', array( $this, 'render_form' ) ); // Add this line
	}

	public function register_block() {
		register_block_type(
			'show-submissions/submission-form',
			array(
				'editor_script'   => 'show-submissions-block',
				'render_callback' => array( $this, 'render_form' ),
			)
		);

		wp_register_script(
			'show-submissions-block',
			SHOW_SUBMISSIONS_URL . 'js/block.js',  // Note: not using Vite for this file
			array( 'wp-blocks', 'wp-element' ),
			'1.0.0',
			true
		);
	}

	public function render_form() {
		$api_key = get_option( 'show_submissions_google_api_key' );

		if ( $api_key ) {
            // Enqueue the Google Maps script without a callback
            wp_enqueue_script(
                'google-places',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places&v=weekly',
                array(),
                '1.0.0',
                true
            );
        }

		wp_enqueue_script(
			'show-submissions-form',
			show_submissions_get_asset_url( 'js/form.min.js' ),
			array( 'jquery', 'google-places' ),
			'1.0.0',
			true
		);
		wp_enqueue_style(
			'show-submissions-style',
			show_submissions_get_asset_url( 'css/style.min.css' ),
			array(),
			'1.0.0'
		);

		// Add this code to localize the script
		wp_localize_script(
			'show-submissions-form',
			'showSubmissions',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'submit_show_nonce' ),
			)
		);

		ob_start();
		include SHOW_SUBMISSIONS_PATH . 'templates/form.php';
		return ob_get_clean();
	}

	public function handle_submission() {
		// Check nonce
		if ( ! check_ajax_referer( 'submit_show_nonce', '_ajax_nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
			return;
		}

		$upload_dir = SHOW_SUBMISSIONS_PATH . 'assets/submissions/';
		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		// Check if new organizer name is provided and use it instead of booking_name
		$booking_name = isset( $_POST['new_organizer_name'] ) && ! empty( $_POST['new_organizer_name'] )
			? sanitize_text_field( $_POST['new_organizer_name'] )
			: sanitize_text_field( $_POST['booking_name'] );

		$venue_name = isset( $_POST['new_venue_name'] ) && ! empty( $_POST['new_venue_name'] )
		? sanitize_text_field( $_POST['new_venue_name'] )
		: sanitize_text_field( $_POST['venue_name'] );

		// Collect form data
		$submission_data = array(
			'submitter_name'   => sanitize_text_field( $_POST['submitter_name'] ),
			'submitter_email'  => sanitize_email( $_POST['submitter_email'] ),
			'booking_name'     => $booking_name,
			'booking_email'    => sanitize_email( $_POST['booking_email'] ),
			'venue_name'       => $venue_name,
			'venue_address'    => sanitize_textarea_field( $_POST['venue_address'] ),
			'show_date'        => sanitize_text_field( $_POST['show_date'] ),
			'door_time'        => sanitize_text_field( $_POST['door_time'] ),
			'music_start_time' => sanitize_text_field( $_POST['music_start_time'] ),
			'performers'       => sanitize_textarea_field( $_POST['performers'] ),
			'door_price'       => floatval( $_POST['door_price'] ),
			'ticket_price'     => floatval( $_POST['ticket_price'] ),
			'show_link'        => esc_url_raw( $_POST['show_link'] ),
			'ticket_link'      => esc_url_raw( $_POST['ticket_link'] ),
		);

		// Handle venue address components
		if ( isset( $_POST['venue_street'] ) ) {
			$venue_components = array(
				'street' => sanitize_text_field( $_POST['venue_street'] ),
				'city'   => sanitize_text_field( $_POST['venue_city'] ),
				'state'  => sanitize_text_field( $_POST['venue_state'] ),
				'zip'    => sanitize_text_field( $_POST['venue_zip'] ),
			);

			// Create full address if individual components are provided
			if ( ! empty( array_filter( $venue_components ) ) ) {
				$submission_data['venue_address'] = implode( ', ', array_filter( $venue_components ) );
			}
		}

		// Handle file uploads
		$uploaded_files = array();
		if ( ! empty( $_FILES['images'] ) ) {
			$files = $_FILES['images'];

			$file_count = count( $files['name'] );
			for ( $i = 0; $i < $file_count; $i++ ) {
				if ( UPLOAD_ERR_OK === $files['error'][ $i ] ) {
					$tmp_name  = $files['tmp_name'][ $i ];
					$name      = sanitize_file_name( $files['name'][ $i ] );
					$file_info = wp_check_filetype( $name );

					// Verify file type
					if ( ! in_array( $file_info['ext'], array( 'jpg', 'jpeg', 'png' ), true ) ) {
						continue;
					}

					// Generate unique filename
					$filename    = uniqid() . '.' . $file_info['ext'];
					$destination = $upload_dir . $filename;

					// Move file
					if ( move_uploaded_file( $tmp_name, $destination ) ) {
						$uploaded_files[] = $filename;
					}
				}
			}
		}

		// Add images to submission data
		$submission_data['images'] = wp_json_encode( $uploaded_files );
		// Set initial status
		$submission_data['status'] = 'New';

		// Store in database
		global $wpdb;
		$table_name = $wpdb->prefix . 'lhxc_show_submissions';

		$result = $wpdb->insert(
			$table_name,
			$submission_data,
			array(
				'%s', // submitter_name
				'%s', // submitter_email
				'%s', // booking_name
				'%s', // booking_email
				'%s', // venue_name
				'%s', // venue_address
				'%s', // show_date
				'%s', // door_time
				'%s', // music_start_time
				'%s', // performers
				'%f', // price
				'%s', // show_link
				'%s', // ticket_link
				'%s',  // status
			)
		);

		if ( false === $result ) {
			wp_send_json_error( 'Failed to save submission' );
			return;
		}

		$submission_id = $wpdb->insert_id;

		// Update the images field if files were uploaded
		if ( ! empty( $uploaded_files ) ) {
			$wpdb->update(
				$table_name,
				array( 'images' => wp_json_encode( $uploaded_files ) ),
				array( 'id' => $submission_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		$notification_settings = get_option( 'show_submissions_new_event_notification' );

		if ( $notification_settings && $notification_settings['enabled'] ) {
			$to  = $notification_settings['to'];
			$cc  = isset( $notification_settings['cc'] ) ? $notification_settings['cc'] : '';
			$bcc = isset( $notification_settings['bcc'] ) ? $notification_settings['bcc'] : '';

			$subject = 'New Show Submission Received';
			$body    = '<p>A new show has been submitted.</p>';
			$body   .= '<ul>';
			foreach ( $submission_data as $key => $value ) {
				$body .= '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ':</strong> ' . esc_html( $value ) . '</li>';
			}
			$body .= '</ul>';

			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			if ( ! empty( $cc ) ) {
				$headers[] = 'Cc: ' . $cc;
			}
			if ( ! empty( $bcc ) ) {
				$headers[] = 'Bcc: ' . $bcc;
			}

			wp_mail( $to, $subject, $body, $headers );
		}

		wp_send_json_success(
			array(
				'message'       => 'Show submission received successfully!',
				'submission_id' => $submission_id,
			)
		);
	}

	public function add_to_media_library() {
		check_ajax_referer( 'show_submissions_admin', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$filename      = sanitize_file_name( $_POST['filename'] );
		$submission_id = intval( $_POST['submission_id'] );

		// Get the file path
		$file_path = SHOW_SUBMISSIONS_PATH . 'assets/submissions/' . $filename;

		if ( ! file_exists( $file_path ) ) {
			wp_send_json_error( 'File not found' );
		}

		// Check if file already exists in Media Library by comparing file hashes
		$file_hash = md5_file( $file_path );
		$args      = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => '_file_hash',
					'value' => $file_hash,
				),
			),
		);

		$existing_attachment = get_posts( $args );

		if ( ! empty( $existing_attachment ) ) {
			// File already exists in Media Library
			wp_send_json_success(
				array(
					'attachment_id' => $existing_attachment[0]->ID,
					'message'       => 'Image already exists in Media Library',
				)
			);
			return;
		}

		// Prepare file for upload
		$file = array(
			'name'     => $filename,
			'tmp_name' => $file_path,
			'error'    => 0,
			'size'     => filesize( $file_path ),
			'type'     => mime_content_type( $file_path ),
		);

		// Include required files for media handling
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Copy file to temp location
		$temp_file = wp_tempnam( $filename );
		copy( $file_path, $temp_file );
		$file['tmp_name'] = $temp_file;

		// Insert into media library
		$attachment_id = media_handle_sideload( $file, 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $temp_file );
			$message = ( is_object( $attachment_id ) && method_exists( $attachment_id, 'get_error_message' ) )
				? $attachment_id->get_error_message()
				: 'Failed to sideload media';
			wp_send_json_error( $message );
		}

		// Store file hash as attachment metadata
		update_post_meta( $attachment_id, '_file_hash', $file_hash );

		wp_delete_file( $temp_file );
		wp_send_json_success(
			array(
				'attachment_id' => $attachment_id,
				'message'       => 'Image added to Media Library',
			)
		);
		die();
	}
}
