<?php
if (!defined('ABSPATH')) exit;

// Check if The Events Calendar is active
$events_calendar_active = class_exists('Tribe__Events__Main');
?>
<div class="show-submission-form-wrapper" id="showSubmissionForm">
    <!-- Step 1: Form -->
    <div class="form-step" id="step1">
        <form id="showSubmissionStep1" class="show-submission-form" action="/" title="Show Submission Form">
            <div class="column">
                <formgroup class="random-bg form-columns">
                    <div class="form-group">
                        <label for="submitter_name">Your Name *</label>
                        <input type="text" id="submitter_name" name="submitter_name" required>
                    </div>

                    <div class="form-group">
                        <label for="submitter_email">Your Email *</label>
                        <input type="email" id="submitter_email" name="submitter_email" required>
                    </div>

                    <?php if ($events_calendar_active): ?>
                        <div class="form-group">
                            <label for="booking_name">Organizer *</label>
                            <select id="booking_name" name="booking_name" required>
                                <option value="">Select an Organizer</option>
                                <option value="new">+ Add New Organizer</option>
                                <?php
                                $organizers = get_posts(array(
                                    'post_type' => Tribe__Events__Main::ORGANIZER_POST_TYPE,
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ));

                                foreach ($organizers as $organizer) {
                                    echo '<option value="' . esc_attr($organizer->ID) . '"
                                        data-email="' . esc_attr(get_post_meta($organizer->ID, '_OrganizerEmail', true)) . '">' .
                                        esc_html($organizer->post_title) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="booking_name">Booking Name *</label>
                            <input type="text" id="booking_name" name="booking_name" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="booking_email">Booking Contact Email *</label>
                        <input type="email" id="booking_email" name="booking_email" required>
                    </div>
                </formgroup>

                <formgroup class="random-bg form-columns">
                    <?php if ($events_calendar_active): ?>
                        <div class="form-group">
                            <label for="venue_name">Venue *</label>
                            <select id="venue_name" name="venue_name" required>
                                <option value="">Select a Venue</option>
                                <option value="new">+ Add New Venue</option>
                                <?php
                                $venues = get_posts(array(
                                    'post_type' => Tribe__Events__Main::VENUE_POST_TYPE,
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ));

                                foreach ($venues as $venue) {
                                    $address = array(
                                        'street' => get_post_meta($venue->ID, '_VenueAddress', true),
                                        'city' => get_post_meta($venue->ID, '_VenueCity', true),
                                        'state' => get_post_meta($venue->ID, '_VenueStateProvince', true),
                                        'zip' => get_post_meta($venue->ID, '_VenueZip', true)
                                    );

                                    $full_address = implode(', ', array_filter($address));

                                    echo '<option value="' . esc_attr($venue->ID) . '"
                                        data-address="' . esc_attr($full_address) . '"
                                        data-street="' . esc_attr($address['street']) . '"
                                        data-city="' . esc_attr($address['city']) . '"
                                        data-state="' . esc_attr($address['state']) . '"
                                        data-zip="' . esc_attr($address['zip']) . '">' .
                                        esc_html($venue->post_title) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="venue_name">Venue Name *</label>
                            <input type="text" id="venue_name" name="venue_name" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="venue_address_autocomplete">Venue Address *</label>
                        <input type="text" id="venue_address_autocomplete" placeholder="Start typing venue address..." required>

                        <!-- Hidden address fields -->
                        <input type="hidden" id="venue_street" name="venue_street">
                        <input type="hidden" id="venue_city" name="venue_city">
                        <input type="hidden" id="venue_state" name="venue_state">
                        <input type="hidden" id="venue_zip" name="venue_zip">
                        <input type="hidden" id="venue_address" name="venue_address">
                    </div>
                </formgroup>

                <formgroup class="random-bg form-columns form-columns-3lg" style="grid-auto-rows: max-content">
                    <div class="form-group">
                        <label for="show_date">Show Date *</label>
                        <input type="date" id="show_date" name="show_date" value="<?= Date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="door_time">Show Door Time *</label>
                        <input type="time" id="door_time" name="door_time" value="18:00" required>
                    </div>

                    <div class="form-group">
                        <label for="music_start_time">Music Start Time *</label>
                        <input type="time" id="music_start_time" name="music_start_time" value="19:00" required>
                    </div>
                    <p class="shows-help-text"><small>For fests or multi-day shows, just put in the first date/time</small></p>
                </formgroup>

                <formgroup class="random-bg">
                    <div class="form-group">
                        <label for="performers">Bands/Performers *</label>
                        <textarea id="performers" name="performers" required rows="6" placeholder="Bands"></textarea>
                    </div>
                    <p class="shows-help-text"><small>One per line, add any extra info here</small></p>
                </formgroup>

                <formgroup class="random-bg form-columns form-columns--price">
                    <div class="form-group">
                        <label for="price">Door Price *</label>
                        <input type="number" id="price" name="price" step="0.01" required placeholder="$5">
                    </div>

                    <div class="form-group">
                        <label for="ticket_price">Ticket Price *</label>
                        <input type="number" id="ticket_price" name="ticket_price" step="0.01" required placeholder="$5">
                    </div>

                    <div class="form-group show-link">
                        <label for="show_link">Show Link</label>
                        <input type="url" id="show_link" name="show_link" placeholder="https://...">
                    </div>

                    <div class="form-group ticket-link">
                        <label for="ticket_link">Ticket Link</label>
                        <input type="url" id="ticket_link" name="ticket_link" placeholder="https://...">
                    </div>
                </formgroup>
            </div>
            <div class="column">
                <formgroup class="random-bg">
                    <div class="form-group">
                        <label for="images">Show Flyer Images (PNG, JPG, JPEG up to 5MB)</label>
                        <div id="dropZone" class="drop-zone">
                            <p>Drag & drop images here or click to select files</p>
                            <input type="file" id="images" name="images[]" multiple accept=".png,.jpg,.jpeg" hidden>
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
    <div class="form-step" id="step2" style="display: none;">
        <h2>Review Your Submission</h2>
        <div id="submissionReview"></div>
        <div class="button-group">
            <button type="button" class="back-button" onclick="showStep(1)">Back to Edit</button>
            <button type="button" class="submit-button" onclick="submitFinal()">Submit Show</button>
        </div>
    </div>
</div>

<style>
    @media screen and (min-width: 800px) {
        .show-submission-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 1.5em;
        }

        .show-submission-form div:last-child {
            align-self: start;
            position: sticky;
            top: 0;
        }

        .form-columns {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-auto-rows: 100px;
            grid-gap: 10px;
        }

        .form-columns-3lg {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* .form-columns--price {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(2, 1fr);
        gap: 10px;

        div {
            grid-column: 1 / span 2;
        }
        div::nth-child(1)  {
            grid-column: 1 / span 1;
            grid-row-start: 1;
        }
        div::nth-child(2)  {
            grid-column: 2 / span 1;
            grid-row-start: 1;
        }
    } */
    .form-columns--price {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(3, 1fr);

        .show-link {
            grid-column: span 2 / span 2;
        }

        .ticket-link {
            grid-column: span 2 / span 2;
            grid-row-start: 3;
        }
    }
    .shows-help-text {
        grid-column: 1 / span all;
        padding: 0;
        margin: 0;
        width: 100%;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($events_calendar_active): ?>
            // Handle organizer selection
            const bookingNameSelect = document.getElementById('booking_name');
            const bookingEmailInput = document.getElementById('booking_email');
            const newOrganizerInput = document.createElement('input');

            // Create new organizer input
            newOrganizerInput.type = 'text';
            newOrganizerInput.id = 'new_organizer_name';
            newOrganizerInput.name = 'new_organizer_name';
            newOrganizerInput.required = false;
            newOrganizerInput.placeholder = 'Enter new organizer name';
            newOrganizerInput.className = 'form-control new-organizer-input';
            newOrganizerInput.style.display = 'none';
            bookingNameSelect.parentNode.insertBefore(newOrganizerInput, bookingNameSelect.nextSibling);

            bookingNameSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                // Handle new organizer option
                if (selectedOption.value === 'new') {
                    bookingNameSelect.style.display = 'none';
                    newOrganizerInput.style.display = 'block';
                    newOrganizerInput.required = true;
                    bookingNameSelect.required = false;
                    bookingEmailInput.value = '';
                    return;
                }

                // Handle existing organizer selection
                newOrganizerInput.style.display = 'none';
                newOrganizerInput.required = false;
                bookingNameSelect.required = true;
                bookingNameSelect.style.display = 'block';

                const email = selectedOption.getAttribute('data-email');
                if (email) {
                    bookingEmailInput.value = email;
                } else {
                    bookingEmailInput.value = '';
                }
            });

            // Handle venue selection
            const venueSelect = document.getElementById('venue_name');
            const venueAddressInput = document.getElementById('venue_address');
            const venueStreetInput = document.getElementById('venue_street');
            const venueCityInput = document.getElementById('venue_city');
            const venueStateInput = document.getElementById('venue_state');
            const venueZipInput = document.getElementById('venue_zip');
            const newVenueInput = document.createElement('input');

            // Create new venue input
            newVenueInput.type = 'text';
            newVenueInput.id = 'new_venue_name';
            newVenueInput.name = 'new_venue_name';
            newVenueInput.required = false;
            newVenueInput.placeholder = 'Enter new venue name';
            newVenueInput.className = 'form-control new-venue-input';
            newVenueInput.style.display = 'none';
            venueSelect.parentNode.insertBefore(newVenueInput, venueSelect.nextSibling);

            venueSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                // Handle new venue option
                if (selectedOption.value === 'new') {
                    venueSelect.style.display = 'none';
                    newVenueInput.style.display = 'block';
                    newVenueInput.required = true;
                    venueSelect.required = false;

                    // Clear address fields
                    venueAddressInput.value = '';
                    venueStreetInput.value = '';
                    venueCityInput.value = '';
                    venueStateInput.value = '';
                    venueZipInput.value = '';
                    return;
                }

                // Handle existing venue selection
                newVenueInput.style.display = 'none';
                newVenueInput.required = false;
                venueSelect.required = true;
                venueSelect.style.display = 'block';

                if (selectedOption.value) {
                    venueAddressInput.value = selectedOption.getAttribute('data-address');
                    venueStreetInput.value = selectedOption.getAttribute('data-street');
                    venueCityInput.value = selectedOption.getAttribute('data-city');
                    venueStateInput.value = selectedOption.getAttribute('data-state');
                    venueZipInput.value = selectedOption.getAttribute('data-zip');
                } else {
                    venueAddressInput.value = '';
                    venueStreetInput.value = '';
                    venueCityInput.value = '';
                    venueStateInput.value = '';
                    venueZipInput.value = '';
                }
            });
        <?php endif; ?>

        // generate random figures for background card rotations
        const randomBgs = document.querySelectorAll('.random-bg');
        console.table(randomBgs);

        function generateRandomRotation(elem) {
            console.log(this);
            const min = -.85;
            const max = .85;
            const rotateAmount = (Math.random() * (max - min + 1) + min);
            elem.style.setProperty('--rotate-amt', rotateAmount + 'deg');
        }
        Array.prototype.forEach.call(randomBgs, function(element) {
            generateRandomRotation(element);
        });
    });
</script>

<style>
    .new-venue-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 5px;
    }
</style>