<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the constants class
require_once SHOW_SUBMISSIONS_PATH . 'includes/class-show-submissions-constants.php';



// Check if The Events Calendar is active
$events_calendar_active = class_exists( 'Tribe__Events__Main' );
?>
<div class="show-submission-form-wrapper" id="showSubmissionForm">
	<!-- Step 1: Form -->
	<div class="form-step" id="step1">
		<form id="showSubmissionStep1" class="show-submission-form" action="/" title="Show Submission Form">
			<div class="column">
				<formgroup class="random-bg form-columns">
					<?php if ( $events_calendar_active ) : ?>
						<div class="form-group">
							<label for="booking_name">Organizer *</label>
							<select id="booking_name" name="booking_name" required>
								<option value="">Select an Organizer</option>
								<option value="new">+ Add New Organizer</option>
								<?php
								// Get enabled promoters from settings
								$enabled_promoters    = get_option( 'show_submissions_promoter_status', array() );
								$enabled_promoter_ids = array_keys( $enabled_promoters );

								// Only get promoters that are enabled in settings
								$organizers = get_posts(
									array(
										'post_type'      => Tribe__Events__Main::ORGANIZER_POST_TYPE,
										'posts_per_page' => -1,
										'orderby'        => 'title',
										'order'          => 'ASC',
										'post__in'       => ! empty( $enabled_promoter_ids ) ? $enabled_promoter_ids : array( 0 ), // Use array(0) to return no results if no promoters are enabled
									)
								);

								foreach ( $organizers as $organizer ) {
									echo '<option value="' . esc_attr( $organizer->ID ) . '"
                                        data-email="' . esc_attr( get_post_meta( $organizer->ID, '_OrganizerEmail', true ) ) . '">' .
										esc_html( $organizer->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
					<?php else : ?>
						<div class="form-group">
							<label for="booking_name">Booking Name *</label>
							<input type="text" id="booking_name" name="booking_name" required="true">
						</div>
					<?php endif; ?>

					<div class="form-group" id="booking_email_group" style="display: none;">
						<label for="booking_email">Booking Contact Email</label>
						<input type="email" id="booking_email" name="booking_email">
					</div>
				</formgroup>

				<formgroup class="random-bg form-columns form-col">
					<?php if ( $events_calendar_active ) : ?>
						<div class="form-group">
							<label for="venue_name">Venue *</label>
							<select id="venue_name" name="venue_name" required="true">
								<option value="">Select a Venue</option>
								<option value="new">+ Add New Venue</option>
								<?php
								// Get enabled venues from settings
								$enabled_venues    = get_option( 'show_submissions_venue_status', array() );
								$enabled_venue_ids = array_keys( $enabled_venues );

								// Only get venues that are enabled in settings
								$venues = get_posts(
									array(
										'post_type'      => Tribe__Events__Main::VENUE_POST_TYPE,
										'posts_per_page' => -1,
										'orderby'        => 'title',
										'order'          => 'ASC',
										'post__in'       => ! empty( $enabled_venue_ids ) ? $enabled_venue_ids : array( 0 ), // Use array(0) to return no results if no venues are enabled
									)
								);

								foreach ( $venues as $venue ) {
									$address = array(
										'street' => get_post_meta( $venue->ID, '_VenueAddress', true ),
										'city'   => get_post_meta( $venue->ID, '_VenueCity', true ),
										'state'  => get_post_meta( $venue->ID, '_VenueStateProvince', true ),
										'zip'    => get_post_meta( $venue->ID, '_VenueZip', true ),
									);

									$full_address = implode( ', ', array_filter( $address ) );

									echo '<option value="' . esc_attr( $venue->ID ) . '"
                                        data-address="' . esc_attr( $full_address ) . '"
                                        data-street="' . esc_attr( $address['street'] ) . '"
                                        data-city="' . esc_attr( $address['city'] ) . '"
                                        data-state="' . esc_attr( $address['state'] ) . '"
                                        data-zip="' . esc_attr( $address['zip'] ) . '">' .
										esc_html( trim( $venue->post_title ) ) . '</option>';
								}
								?>
							</select>
							<span id="selected_venue_address" class="venue-address-display"></span>
						</div>
					<?php else : ?>
						<div class="form-group">
							<label for="venue_name">Venue Name *</label>
							<input type="text" id="venue_name" name="venue_name" required>
						</div>
					<?php endif; ?>

					<div class="form-group" id="venue_address_group">
						<label for="venue_address_input">Venue Address *</label>
						                        <gmp-autocomplete
                            id="venue_address_autocomplete"
                            class="google-places-autocomplete"
                            placeholder="Start typing venue address..."
                        >
                            <input
                                type="text"
                                id="venue_address_input"
                                name="venue_address_input"
                                class="form-control"
                            />
                        </gmp-autocomplete>

						<!-- Hidden address fields -->
						<input type="hidden" id="venue_street" name="venue_street">
						<input type="hidden" id="venue_city" name="venue_city">
						<input type="hidden" id="venue_state" name="venue_state">
						<input type="hidden" id="venue_zip" name="venue_zip">
						<input type="hidden" id="venue_address" name="venue_address">
					</div>
				</formgroup>

				<formgroup class="random-bg form-columns" style="grid-auto-rows: max-content">
					<div class="form-group">
						<label for="show_date">Show Date *</label>
						<input type="date" id="show_date" name="show_date" value="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" required>
					</div>

					<div class="form-group">
						<label for="door_time">Show Door Time *</label>
						<input type="time" id="door_time" name="door_time" value="18:00" required>
					</div>

					<div class="form-group">
						<label for="time_zone">Time Zone *</label>
						<select id="time_zone" name="time_zone" required>
							<option value="">Select Time Zone</option>
							<?php
							try {
								// Get time zones from global constants
								$constants = Show_Submissions_Constants::get_instance();
								if ( ! $constants ) {
									throw new Exception( 'Constants class not initialized' );
								}

								$time_zones = $constants->get_time_zones();
								if ( ! is_array( $time_zones ) ) {
									throw new Exception( 'Invalid timezone data' );
								}

								foreach ( $time_zones as $tz ) {
									if ( ! isset( $tz['zone'] ) || ! isset( $tz['name'] ) ) {
										continue;
									}

									try {
										$timezone = new DateTimeZone( $tz['zone'] );
										$offset   = $timezone->getOffset( new DateTime() );
										$tz_abbr  = ( new DateTime( 'now', $utc_tz ) )->format( 'T' );

										$selected = ( date_default_timezone_get() === $tz['zone'] ) ? 'selected' : '';

										printf(
											'<option value="%s" data-utc-offset="%s" %s>%s (%s)</option>',
											esc_attr( $tz['zone'] ),
											esc_attr( $offset ),
											esc_attr( $selected ),
											esc_html( $tz['name'] ),
											esc_html( $tz_abbr )
										);
									} catch ( Exception $e ) {
										continue;
									}
								}
							} catch ( Exception $e ) {
								echo '<option value="America/Kentucky/Louisville">Louisville Time (EDT)</option>';
							}
							?>
						</select>
					</div>

					<!-- Temporarily hidden
					<div class="form-group">
						<label for="music_start_time">Music Start Time *</label>
						<input type="time" id="music_start_time" name="music_start_time" value="19:00" required>
					</div>
					-->
					<p class="shows-help-text"><small>For fests or multi-day shows, just put in the first date/time</small></p>
				</formgroup>

				<formgroup class="random-bg form-columns form-columns--price">
					<!-- Temporarily hidden
					<div class="form-group">
						<label for="door_price">Door Price *</label>
						<input type="number" id="door_price" name="door_price" step="0.01" required placeholder="$5">
					</div>
					-->

					<div class="form-group">
						<label for="ticket_price">Price *</label>
						<input type="number" id="ticket_price" name="ticket_price" step="0.01" required placeholder="$5">
					</div>

					<div class="form-group show-link">
						<label for="show_link">Show Link</label>
						<input type="url" id="show_link" name="show_link" placeholder="https://...">
					</div>

					<!-- Temporarily hidden
					<div class="form-group ticket-link">
						<label for="ticket_link">Ticket Link</label>
						<input type="url" id="ticket_link" name="ticket_link" placeholder="https://...">
					</div>
					-->
				</formgroup>
			</div>
			<div class="column">
				<formgroup class="random-bg">
					<div class="form-group">
						<label for="performers">Bands/Performers *</label>
						<textarea id="performers" name="performers" required rows="6" placeholder="Bands"></textarea>
					</div>
					<p class="shows-help-text"><small>One per line, add any extra info here</small></p>
				</formgroup>

				<formgroup class="random-bg">
					<div class="form-group">
						<label for="images">Show Flyer Images (PNG, JPG, JPEG up to 5MB)</label>
						<div id="dropZone" class="drop-zone">
							<p>Drag & drop images here or click to select files</p>
							<input type="file" id="images" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp" hidden>
						</div>
						<div id="imagePreview" class="image-preview"></div>
					</div>
				</formgroup>

				<formgroup class="random-bg">
					<button type="submit" class="submit-button">Review Submission</button>
				</formgroup>
			</div>
		</form>
	</div>

	<!-- Step 2: Review -->
	<div class="form-step" id="step2">
		<h2>Review Your Submission</h2>
		<div id="submissionReview"></div>
		<div class="button-group">
			<button type="button" class="back-button" onclick="showStep(1)">Back to Edit</button>
			<button type="button" class="submit-button" onclick="submitFinal()">Submit Show</button>
		</div>
	</div>
</div>

<?php
// Add the necessary AJAX URL for the form submission
wp_localize_script(
	'show-submissions-form-script',
	'showSubmissions',
	array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'submit_show_nonce' ),  // Updated nonce name
	)
);
?>
