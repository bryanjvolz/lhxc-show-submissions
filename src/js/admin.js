jQuery(document).ready(function($) {
    const modal = $('#submissionModal');
    const modalContent = $('#submissionDetails');

    // View Details button handler
    $('.view-details').on('click', function() {
        const row = $(this).closest('tr');
        const submissionId = row.data('id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_submission_details',
                id: submissionId
            },
            success: function(response) {
                modalContent.html(response);
                modal.show();
            }
        });
    });

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

    // Modal close button
    $('.close').on('click', function() {
        modal.hide();
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is(modal)) {
            modal.hide();
        }
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