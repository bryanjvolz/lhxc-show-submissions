<?php if (!defined('ABSPATH')) exit;
wp_enqueue_style('admin_style', show_submissions_get_asset_url('css/admin_style.min.css'), array(), '1.0.0');
?>

<div class="wrap">
    <h1>Show Submission Details</h1>
    <form method="post" id="submission-details-form" enctype="multipart/form-data">
        <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission->id); ?>">
        <?php wp_nonce_field('save_submission_details', 'submission_nonce'); ?>

        <table class="form-table">
            <!-- <tr>
                <th><label for="submitter_name">Submitter Name</label></th>
                <td><input type="text" id="submitter_name" name="submitter_name" class="regular-text"
                    value="<?php echo esc_attr($submission->submitter_name); ?>"></td>
            </tr>
            <tr>
                <th><label for="submitter_email">Submitter Email</label></th>
                <td><input type="email" id="submitter_email" name="submitter_email" class="regular-text"
                    value="<?php echo esc_attr($submission->submitter_email); ?>"></td>
            </tr> -->
            <tr>
                <th><label for="booking_name">Booking Name</label></th>
                <td>
                    <?php
                    if (class_exists('Tribe__Events__Main') && is_numeric($submission->booking_name)) {
                        $organizer = get_post($submission->booking_name);
                        echo '<input type="hidden" name="booking_name" value="' . esc_attr($submission->booking_name) . '">';
                        echo '<input type="text" class="regular-text" disabled value="' . esc_attr($organizer ? $organizer->post_title : $submission->booking_name) . '">';
                    } else {
                        echo '<input type="text" id="booking_name" name="booking_name" class="regular-text" value="' . esc_attr($submission->booking_name) . '">';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="booking_email">Booking Email</label></th>
                <td><input type="email" id="booking_email" name="booking_email" class="regular-text"
                    value="<?php echo esc_attr($submission->booking_email); ?>"></td>
            </tr>
            <tr>
                <th><label for="venue_name">Venue Name</label></th>
                <td>
                    <?php
                    if (class_exists('Tribe__Events__Main') && is_numeric($submission->venue_name)) {
                        $venue = get_post($submission->venue_name);
                        echo '<input type="hidden" name="venue_name" value="' . esc_attr($submission->venue_name) . '">';
                        echo '<input type="text" class="regular-text" disabled value="' . esc_attr($venue ? $venue->post_title : $submission->venue_name) . '">';
                    } else {
                        echo '<input type="text" id="venue_name" name="venue_name" class="regular-text" value="' . esc_attr($submission->venue_name) . '">';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="venue_address">Venue Address</label></th>
                <td><textarea id="venue_address" name="venue_address" rows="3" class="regular-text"><?php
                    echo esc_textarea($submission->venue_address); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="show_date">Show Date</label></th>
                <td><input type="date" id="show_date" name="show_date"
                    value="<?php echo esc_attr($submission->show_date); ?>"></td>
            </tr>
            <tr>
                <th><label for="door_time">Door Time</label></th>
                <td><input type="time" id="door_time" name="door_time"
                    value="<?php echo esc_attr($submission->door_time); ?>"></td>
            </tr>
            <tr>
                <th><label for="time_zone">Time Zone</label></th>
                <td>
                    <select id="time_zone" name="time_zone" class="regular-text">
                        <?php
                        // Get time zones from global constants
                        $timeZones = Show_Submissions_Constants::get_time_zones();
                        $current_timezone = esc_attr($submission->time_zone);
                        
                        foreach ($timeZones as $tz) {
                            $selected = ($current_timezone === $tz['zone']) ? 'selected' : '';
                            echo sprintf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($tz['zone']),
                                $selected,
                                esc_html($tz['name'])
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <!-- <tr>
                <th><label for="music_start_time">Music Start Time</label></th>
                <td><input type="time" id="music_start_time" name="music_start_time"
                    value="<?php echo esc_attr($submission->music_start_time); ?>"></td>
            </tr> -->
            <tr>
                <th><label for="performers">Performers</label></th>
                <td><textarea id="performers" name="performers" rows="3" clas="regular-text"><?php
                    echo esc_textarea($submission->performers); ?></textarea></td>
            </tr>
            <!-- <tr>
                <th><label for="door_price">Door Price</label></th>
                <td><input type="number" id="door_price" name="door_price" step="0.01"
                    value="<?php echo esc_attr($submission->door_price); ?>" class="regular-text"></td>
            </tr> -->
            <tr>
                <th><label for="ticket_price">Ticket Price</label></th>
                <td><input type="number" id="ticket_price" name="ticket_price" step="0.01"
                    value="<?php echo esc_attr($submission->ticket_price); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="show_link">Show Link</label></th>
                <td><input type="url" id="show_link" name="show_link" class="regular-text"
                    value="<?php echo esc_url($submission->show_link); ?>"></td>
            </tr>
            <!-- <tr>
                <th><label for="ticket_link">Ticket Link</label></th>
                <td><input type="url" id="ticket_link" name="ticket_link" class="regular-text"
                    value="<?php echo esc_url($submission->ticket_link); ?>"></td>
            </tr> -->
            <tr>
                <th>Images</th>
                <td>
                    <div id="imagePreview" class="image-preview">
                        <?php
                        $images = unserialize($submission->images);
                        if ($images) {
                            foreach ($images as $image) {
                                $image_url = SHOW_SUBMISSIONS_URL . 'assets/submissions/' . $image;
                                echo '<div class="preview-item submission-image">';
                                echo '<img src="' . esc_url($image_url) . '" alt="Show Flyer">';
                                echo '<button type="button" class="remove-image" data-filename="' . esc_attr($image) . '">×</button>';
                                echo '<button type="button" class="add-to-media-library" data-filename="' . esc_attr($image) . '">Add to Media Library</button>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <div id="dropZone" class="drop-zone">
                        <p>Drop files here or click to upload</p>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="approved">Approval Status</label></th>
                <td>
                    <label class="switch">
                        <input type="checkbox" id="approved" name="approved" <?php checked($submission->approved, 1); ?>>
                        <span class="slider round"></span>
                    </label>
                </td>
            </tr>
        </table>

        <div class="submit-buttons">
            <a href="<?php echo admin_url('admin.php?page=show-submissions'); ?>" class="button">Back to List</a>
            <input type="submit" name="save_submission" class="button button-primary" value="Save Changes">
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('images');
    const imagePreview = document.getElementById('imagePreview');
    let uploadedFiles = [];

    // Handle drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#000';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#ccc';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#ccc';
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle click to upload
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.match('image.*') && file.size <= 5 * 1024 * 1024) {
                uploadedFiles.push(file);
            }
        });
        updateImagePreview();
    }

    function updateImagePreview() {
        const existingImages = imagePreview.querySelectorAll('.preview-item');
        existingImages.forEach(item => {
            if (!item.querySelector('[data-filename]')) {
                item.remove();
            }
        });

        uploadedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" data-index="${index}">×</button>
                `;
                imagePreview.appendChild(div);

                div.querySelector('.remove-image').addEventListener('click', function() {
                    uploadedFiles.splice(index, 1);
                    updateImagePreview();
                });
            };
            reader.readAsDataURL(file);
        });
    }

    // Handle existing image removal
    document.querySelectorAll('.remove-image[data-filename]').forEach(button => {
        button.addEventListener('click', function() {
            const filename = this.getAttribute('data-filename');
            const item = this.closest('.preview-item');
            if (confirm('Are you sure you want to remove this image?')) {
                // Add a hidden input to track removed files
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'removed_images[]';
                input.value = filename;
                document.getElementById('submission-details-form').appendChild(input);
                item.remove();
            }
        });
    });
});
</script>
