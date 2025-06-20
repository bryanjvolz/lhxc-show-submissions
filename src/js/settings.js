jQuery(document).ready(function($) {
    console.log('Settings script loaded');

    $(document).on('change', '.status-checkbox', function() {
        const checkbox = $(this);
        const type = checkbox.data('type');
        const id = checkbox.data('id');
        const checked = checkbox.prop('checked');

        console.log('Checkbox changed:', { type, id, checked });

        // Disable the checkbox while processing
        checkbox.prop('disabled', true);

        $.ajax({
            url: showSubmissionsSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_checkbox_status',
                nonce: showSubmissionsSettings.nonce,
                type: type,
                id: id,
                checked: checked
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    // Flash feedback
                    checkbox.closest('label').css('background-color', '#e7f7e7');
                    setTimeout(function() {
                        checkbox.closest('label').css('background-color', '');
                    }, 500);
                } else {
                    checkbox.prop('checked', !checked);
                    alert('Failed to update status: ' + (response.data?.message || 'Please try again.'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', { xhr, status, error });
                checkbox.prop('checked', !checked);
                alert('Failed to update status: ' + error);
            },
            complete: function() {
                // Re-enable the checkbox
                checkbox.prop('disabled', false);
            }
        });
    });
});