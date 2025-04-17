<?php
if (!defined('ABSPATH')) exit;
?>
<div class="show-submission-form-wrapper" id="showSubmissionForm">
    <!-- Step 1: Form -->
    <div class="form-step" id="step1">
        <!-- <h2>Submit a Show</h2> -->
        <form id="showSubmissionStep1">
            <div class="form-columns">
                <div class="form-group">
                    <label for="submitter_name">Your Name *</label>
                    <input type="text" id="submitter_name" name="submitter_name" required>
                </div>

                <div class="form-group">
                    <label for="submitter_email">Your Email *</label>
                    <input type="email" id="submitter_email" name="submitter_email" required>
                </div>

                <div class="form-group">
                    <label for="booking_name">Booking Name *</label>
                    <input type="text" id="booking_name" name="booking_name" required>
                </div>

                <div class="form-group">
                    <label for="booking_email">Booking Contact Email *</label>
                    <input type="email" id="booking_email" name="booking_email" required>
                </div>

                <div class="form-group">
                    <label for="venue_name">Venue Name *</label>
                    <input type="text" id="venue_name" name="venue_name" required>
                </div>

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

                <div class="form-group">
                    <label for="show_date">Show Start Date *</label>
                    <input type="date" id="show_date" name="show_date" value="<?= Date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="door_time">Show Door Time *</label>
                    <input type="time" id="door_time" name="door_time" value="18:00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="performers">Bands/Performers * <small>One per line</small></label>
                <textarea id="performers" name="performers" required style="min-height:3em;"></textarea>
            </div>

            <div class="form-columns">
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="show_link">Show Link</label>
                    <input type="url" id="show_link" name="show_link">
                </div>

                <div class="form-group">
                    <label for="ticket_link">Ticket Link</label>
                    <input type="url" id="ticket_link" name="ticket_link">
                </div>
            </div>

            <div class="form-group">
                <label for="images">Show Flyer Images (PNG, JPG, JPEG up to 5MB)</label>
                <div id="dropZone" class="drop-zone">
                    <p>Drag & drop images here or click to select files</p>
                    <input type="file" id="images" name="images[]" multiple accept=".png,.jpg,.jpeg" hidden>
                </div>
                <div id="imagePreview" class="image-preview"></div>
            </div>

            <!-- Add this near the beginning of your form -->
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('show_submission_nonce'); ?>">
            <button type="submit" class="submit-button">Review Submission</button>
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
    .form-columns {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(2, 100px);
        grid-gap: 10px;
    }
</style>