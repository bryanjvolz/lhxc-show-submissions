<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1>Show Submission Details</h1>
    <form method="post" id="submission-details-form">
        <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission->id); ?>">
        
        <table class="form-table">
            <tr>
                <th><label for="submitter_name">Submitter Name</label></th>
                <td><input type="text" id="submitter_name" name="submitter_name" class="regular-text" 
                    value="<?php echo esc_attr($submission->submitter_name); ?>"></td>
            </tr>
            <tr>
                <th><label for="submitter_email">Submitter Email</label></th>
                <td><input type="email" id="submitter_email" name="submitter_email" class="regular-text" 
                    value="<?php echo esc_attr($submission->submitter_email); ?>"></td>
            </tr>
            <tr>
                <th><label for="booking_name">Booking Name</label></th>
                <td><input type="text" id="booking_name" name="booking_name" class="regular-text" 
                    value="<?php echo esc_attr($submission->booking_name); ?>"></td>
            </tr>
            <tr>
                <th><label for="booking_email">Booking Email</label></th>
                <td><input type="email" id="booking_email" name="booking_email" class="regular-text" 
                    value="<?php echo esc_attr($submission->booking_email); ?>"></td>
            </tr>
            <tr>
                <th><label for="venue_name">Venue Name</label></th>
                <td><input type="text" id="venue_name" name="venue_name" class="regular-text" 
                    value="<?php echo esc_attr($submission->venue_name); ?>"></td>
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
                <th><label for="performers">Performers</label></th>
                <td><textarea id="performers" name="performers" rows="3" class="regular-text"><?php 
                    echo esc_textarea($submission->performers); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="price">Price</label></th>
                <td><input type="number" id="price" name="price" step="0.01" 
                    value="<?php echo esc_attr($submission->price); ?>"></td>
            </tr>
            <tr>
                <th><label for="show_link">Show Link</label></th>
                <td><input type="url" id="show_link" name="show_link" class="regular-text" 
                    value="<?php echo esc_url($submission->show_link); ?>"></td>
            </tr>
            <tr>
                <th><label for="ticket_link">Ticket Link</label></th>
                <td><input type="url" id="ticket_link" name="ticket_link" class="regular-text" 
                    value="<?php echo esc_url($submission->ticket_link); ?>"></td>
            </tr>
            <tr>
                <th>Images</th>
                <td>
                    <?php 
                    $images = unserialize($submission->images);
                    if (!empty($images)):
                        foreach ($images as $image):
                            $image_path = SHOW_SUBMISSIONS_URL . 'assets/submissions/' . $image;
                    ?>
                        <div class="submission-image">
                            <img src="<?php echo esc_url($image_path); ?>" alt="Show Flyer" style="max-width: 200px;">
                            <button type="button" class="button add-to-media-library" data-filename="<?php echo esc_attr($image); ?>">
                                Add to Media Library
                            </button>
                        </div>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </td>
            </tr>
        </table>

        <div class="submit-buttons">
            <a href="<?php echo admin_url('admin.php?page=show-submissions'); ?>" class="button">Back to List</a>
            <input type="submit" name="save_submission" class="button button-primary" value="Save Changes">
        </div>
    </form>
</div>