jQuery(document).ready(function($) {

    // Approval toggle handler
    $('.approval-toggle').on('change', function() {
        const row = $(this).closest('tr');
        const submissionId = row.data('id');
        const approved = $(this).prop('checked');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_submission_approval',
                id: submissionId,
                approved: approved
            },
            success: function(response) {
                if (response.success) {
                    alert('Status updated successfully!');
                } else {
                    alert('Error updating status.');
                }
            }
        });
    });



    // View Details button handler
    $('.view-details').on('click', function() {
        const id = $(this).closest('tr').data('id');
        window.location.href = `admin.php?page=show-submission-details&id=${id}`;
    });

    // Add to Media Library button handler
    $('.add-to-media-library').on('click', function() {
        const button = $(this);
        const filename = button.data('filename');
        const submissionId = new URLSearchParams(window.location.search).get('id');

        button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: showSubmissionsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_media_library',
                nonce: showSubmissionsAdmin.nonce,
                filename: filename,
                submission_id: submissionId
            },
            success: function(response) {
                if (response.success) {
                    button.closest('.submission-image').fadeOut();
                } else {
                    alert('Error: ' + response.data);
                    button.prop('disabled', false).text('Add to Media Library');
                }
            },
            error: function() {
                alert('Error processing request');
                button.prop('disabled', false).text('Add to Media Library');
            }
        });
    });
});