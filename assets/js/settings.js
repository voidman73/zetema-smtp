jQuery(document).ready(function ($) {
    console.log("Zetema SMTP Settings JS loaded");
  
    // Open modal when delete button is clicked
    $('#pro-mail-smtp-delete-data').on('click', function() {
        $('#data-deletion-modal').show();
    });
    
    // Close modal when X is clicked
    $('.modal-close, .modal-cancel').click(function() {
        $('#data-deletion-modal').hide();
        $('#delete-confirmation').val('');
        $('#confirm-delete-data').prop('disabled', true);
    });
    
    // Enable confirm button only when "DELETE" is typed
    $('#delete-confirmation').on('input', function() {
        $('#confirm-delete-data').prop('disabled', $(this).val() !== 'DELETE');
    });
    
    // Handle deletion confirmation
    $('#confirm-delete-data').click(function() {
        if ($('#delete-confirmation').val() === 'DELETE') {
            const $button = $(this);
            $button.text('Deleting...').prop('disabled', true);
            
            $.ajax({
                url: ProMailSMTPAdminSettings.ajaxUrl, 
                type: 'POST',
                data: {
                    action: 'pro_mail_smtp_delete_all_data',
                    nonce: ProMailSMTPAdminSettings.nonce 
                },
                success: function(response) {
                    if (response.success) {
                        $('#data-deletion-modal').hide();
                        alert('All plugin data has been successfully deleted.');
                        window.location.href = ProMailSMTPAdminSettings.adminUrl || window.location.href;
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error occurred'));
                        $button.text('Permanently Delete All Data').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) { 
                    console.error('Delete data error:', error);
                    alert('Server error occurred. Please try again.');
                    $button.text('Permanently Delete All Data').prop('disabled', false);
                }
            });
        }
    });
    
    // Close modal when clicking outside
    $(window).click(function(e) {
        if ($(e.target).is('#data-deletion-modal')) {
            $('#data-deletion-modal').hide();
            $('#delete-confirmation').val('');
            $('#confirm-delete-data').prop('disabled', true);
        }
    });
    
    // Email summary toggle functionality
    const summaryEmail = $('#summary_email');
    const summaryFrequency = $('#summary_frequency');
    const enableSummary = $('#enable_email_summary');
    
    function toggleSummaryFields() {
        const isEnabled = enableSummary.is(':checked');
        summaryEmail.prop('disabled', !isEnabled);
        summaryFrequency.prop('disabled', !isEnabled);
        summaryEmail.closest('tr').toggleClass('disabled-field', !isEnabled);
        summaryFrequency.closest('tr').toggleClass('disabled-field', !isEnabled);
    }

    toggleSummaryFields();
    enableSummary.on('change', toggleSummaryFields);
});
