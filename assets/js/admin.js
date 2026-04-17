(function ($) {
  $(document).ready(function () {
    // Handle submission approval
    $('.approval-toggle').on('change', function () {
      const self = $(this);
      const submissionId = self.closest('tr').data('id');
      const isApproved = self.is(':checked');

      $.ajax({
        url: showSubmissionsAdmin.ajaxurl,
        type: 'POST',
        data: {
          action: 'update_submission_approval',
          nonce: showSubmissionsAdmin.nonce,
          id: submissionId,
          approved: isApproved,
        },
        success: function (response) {
          if (!response.success) {
            alert('Failed to update approval status.');
            self.prop('checked', !isApproved); // Revert the checkbox
          }
        },
        error: function () {
          alert('An error occurred.');
          self.prop('checked', !isApproved); // Revert the checkbox
        },
      });
    });

    // Handle submission deletion
    $('.delete-submission').on('click', function (e) {
      e.preventDefault();

      if (!confirm('Are you sure you want to delete this submission?')) {
        return;
      }

      const self = $(this);
      const submissionId = self.data('id');

      $.ajax({
        url: showSubmissionsAdmin.ajaxurl,
        type: 'POST',
        data: {
          action: 'delete_submission',
          nonce: showSubmissionsAdmin.nonce,
          id: submissionId,
        },
        success: function (response) {
          if (response.success) {
            self.closest('tr').fadeOut(300, function () {
              $(this).remove();
            });
          } else {
            alert(response.data || 'Failed to delete submission.');
          }
        },
        error: function () {
          alert('An error occurred while trying to delete the submission.');
        },
      });
    });
  });
})(jQuery);