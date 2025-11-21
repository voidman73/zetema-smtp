jQuery(document).ready(function($) {
    // Simple test to see if jQuery and DOM are ready
    console.log('jQuery loaded, DOM ready');
    console.log('Found view buttons:', $('.view-btn').length);
    console.log('Found resend buttons:', $('.resend-btn').length);
    
    // Test click handler
    $(document).on('click', '.view-btn', function() {
        console.log('View button click detected!');
    });
    
    $(document).on('click', '.resend-btn', function() {
        console.log('Resend button click detected!');
    });
    
    // Handle column sorting
    $('.sort-column').on('click', function(e) {
        e.preventDefault();
        
        var column = $(this).data('column');
        var currentOrderby = $('input[name="orderby"]').val();
        var currentOrder = $('input[name="order"]').val();
        
        var newOrder = (currentOrderby === column && currentOrder === 'desc') ? 'asc' : 'desc';
        
        $('input[name="orderby"]').val(column);
        $('input[name="order"]').val(newOrder);
        
        $('.email-filters').submit();
    });

    // Handle pagination
    $(document).on('click', '.pagination-button', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('disabled')) {
            return false;
        }
        
        var $form = $('.email-filters');
        var page = $(this).data('page');
        
        $form.find('input[name="paged"]').val(page);
        
        if ($form.find('input[name="filter_action"]').length === 0) {
            $form.append('<input type="hidden" name="filter_action" value="filter_logs">');
        } else {
            $form.find('input[name="filter_action"]').val('filter_logs');
        }
        
        $form.submit();
    });

    // Handle filter reset
    $('.reset-filter').on('click', function(e) {
        e.preventDefault();
        
        var $form = $('.email-filters');
        
        // Reset all filter inputs
        $form.find('.provider-filter').val('');
        $form.find('.status-filter').val('');
        $form.find('input[name="date_from"]').val('');
        $form.find('input[name="date_to"]').val('');
        $form.find('input[name="search"]').val('');
        
        // Reset sorting to default
        $form.find('input[name="orderby"]').val('sent_at');
        $form.find('input[name="order"]').val('desc');
        
        // Reset page to 1
        $form.find('input[name="paged"]').val(1);
        
        // Add filter_action if not present
        if ($form.find('input[name="filter_action"]').length === 0) {
            $form.append('<input type="hidden" name="filter_action" value="filter_logs">');
        }
        
        // Submit the form with reset values
        $form.submit();
    });

    // Handle View Email Log button
    // Handle View Email Log button
    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        
        var logId = $(this).data('log-id');
        var $button = $(this);
        var originalHtml = $button.html();
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        $button.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-update');
        
        $.ajax({
            url: proMailSMTPLogs.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_view_email_log',
                log_id: logId,
                nonce: proMailSMTPLogs.nonce
            },
            success: function(response) {
                if (response.success) {
                    showEmailLogModal(response.data.log);
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to load email details'));
                }
            },
            error: function() {
                alert('Error: Failed to load email details');
            },
            complete: function() {
                // Remove loading state
                $button.removeClass('loading').prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-visibility');
            }
        });
    });

    // Handle Resend Email Log button
    $(document).on('click', '.resend-btn:not(:disabled)', function(e) {
        e.preventDefault();
        
        var logId = $(this).data('log-id');
        var $button = $(this);
        var originalHtml = $button.html();
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        $button.find('.dashicons').removeClass('dashicons-email-alt').addClass('dashicons-update');
        
        $.ajax({
            url: proMailSMTPLogs.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_get_resend_modal',
                log_id: logId,
                nonce: proMailSMTPLogs.nonce
            },
            success: function(response) {
                if (response.success) {
                    showResendModal(response.data.log, response.data.providers);
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to load resend options'));
                }
            },
            error: function() {
                alert('Error: Failed to load resend options');
            },
            complete: function() {
                // Remove loading state
                $button.removeClass('loading').prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-email-alt');
            }
        });
    });

    // Function to show email log modal
    function showEmailLogModal(log) {
        // Remove existing modal if any
        $('#email-log-modal').remove();
        
        // Format headers for display
        var headersHtml = 'N/A';
        if (log.email_headers && typeof log.email_headers === 'object') {
            headersHtml = '<pre>' + JSON.stringify(log.email_headers, null, 2) + '</pre>';
        }
        
        // Format attachments for display
        var attachmentsHtml = 'N/A';
        if (log.attachment_data && Array.isArray(log.attachment_data) && log.attachment_data.length > 0) {
            attachmentsHtml = '<ul>';
            log.attachment_data.forEach(function(attachment) {
                attachmentsHtml += '<li>' + (attachment.name || attachment) + '</li>';
            });
            attachmentsHtml += '</ul>';
        }
        
        var modalHtml = `
            <div id="email-log-modal" class="email-log-modal">
                <div class="email-log-modal-content">
                    <div class="email-log-modal-header">
                        <h2>Email Log Details</h2>
                        <span class="email-log-modal-close">&times;</span>
                    </div>
                    <div class="email-log-modal-body">
                        <table class="email-log-details-table">
                            <tr>
                                <th>ID:</th>
                                <td>${log.id}</td>
                            </tr>
                            <tr>
                                <th>Provider:</th>
                                <td>${log.provider}</td>
                            </tr>
                            <tr>
                                <th>From Email:</th>
                                <td>${log.from_email}</td>
                            </tr>
                            <tr>
                                <th>To Email:</th>
                                <td>${log.to_email}</td>
                            </tr>
                            <tr>
                                <th>CC Email:</th>
                                <td>${log.cc_email}</td>
                            </tr>
                            <tr>
                                <th>BCC Email:</th>
                                <td>${log.bcc_email}</td>
                            </tr>
                            <tr>
                                <th>Reply To:</th>
                                <td>${log.reply_to}</td>
                            </tr>
                            <tr>
                                <th>Subject:</th>
                                <td>${log.subject}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span class="status-badge status-${log.status.toLowerCase()}">${log.status}</span></td>
                            </tr>
                            <tr>
                                <th>Sent At:</th>
                                <td>${log.sent_at}</td>
                            </tr>
                            <tr>
                                <th>Message ID:</th>
                                <td>${log.message_id || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Email Content:</th>
                                <td class="email-content"><pre>${log.email_content || 'N/A'}</pre></td>
                            </tr>
                            <tr>
                                <th>Attachments:</th>
                                <td class="email-attachments">${attachmentsHtml}</td>
                            </tr>
                            <tr>
                                <th>Headers:</th>
                                <td class="email-headers">${headersHtml}</td>
                            </tr>
                            <tr>
                                <th>Resent:</th>
                                <td>${log.is_resent ? 'Yes' : 'No'}</td>
                            </tr>
                            <tr>
                                <th>Retry Count:</th>
                                <td>${log.retry_count || 0}</td>
                            </tr>
                            <tr>
                                <th>Error Message:</th>
                                <td class="error-message">${log.error_message || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        $('#email-log-modal').show();
    }

    function showResendModal(log, providers) {
        // Remove existing modal if any
        $('#resend-email-modal').remove();
        
        var providersOptions = '';
        if (providers && providers.length > 0) {
            providers.forEach(function(provider) {
                providersOptions += `<option value="${provider.id}">${provider.label || provider.title}</option>`;
            });
        } else {
            providersOptions = '<option value="">No providers available</option>';
        }

        // Format attachments display
        var attachmentsHtml = '';
        if (log.email_headers && log.email_headers.attachments && log.email_headers.attachments.length > 0) {
            attachmentsHtml = '<div class="detail-row"><strong>Attachments:</strong><ul>';
            log.email_headers.attachments.forEach(function(attachment) {
                attachmentsHtml += `<li>${attachment}</li>`;
            });
            attachmentsHtml += '</ul></div>';
        }
        
        var modalHtml = `
            <div id="resend-email-modal" class="resend-email-modal">
                <div class="resend-email-modal-content">
                    <div class="resend-email-modal-header">
                        <h2>Resend Email</h2>
                        <span class="resend-email-modal-close">&times;</span>
                    </div>
                    <div class="resend-email-modal-body">
                        <div class="resend-details">
                            <div class="detail-row">
                                <label for="resend-to"><strong>To:</strong></label>
                                <input type="email" id="resend-to" class="regular-text" value="${log.to_email}" />
                            </div>
                            <div class="detail-row">
                                <label for="resend-subject"><strong>Subject:</strong></label>
                                <input type="text" id="resend-subject" class="large-text" value="${log.subject}" />
                            </div>
                            <div class="detail-row">
                                <label for="resend-message"><strong>Message:</strong></label>
                                <textarea id="resend-message" rows="8" class="large-text">${log.email_content || ''}</textarea>
                            </div>
                            ${attachmentsHtml}
                            <div class="detail-row">
                                <strong>Original Status:</strong> <span class="status-badge status-${log.status.toLowerCase()}">${log.status}</span>
                            </div>
                            <div class="detail-row">
                                <label for="resend-provider"><strong>Select Provider:</strong></label>
                                <select id="resend-provider" class="resend-provider-select">
                                    ${providersOptions}
                                </select>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="button button-primary" id="confirm-resend" data-log-id="${log.id}">
                                <span class="dashicons dashicons-email-alt"></span> Resend Email
                            </button>
                            <button type="button" class="button" id="cancel-resend">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        $('#resend-email-modal').show();
    }

    // Handle modal close
    $(document).on('click', '.email-log-modal-close, #email-log-modal', function(e) {
        if (e.target === this) {
            $('#email-log-modal').remove();
        }
    });

    // Handle resend modal close
    $(document).on('click', '.resend-email-modal-close, #resend-email-modal, #cancel-resend', function(e) {
        if (e.target === this) {
            $('#resend-email-modal').remove();
        }
    });

    // Handle resend confirmation
    $(document).on('click', '#confirm-resend', function() {
        var logId = $(this).data('log-id');
        var providerId = $('#resend-provider').val();
        var toEmail = $('#resend-to').val();
        var subject = $('#resend-subject').val();
        var message = $('#resend-message').val();
        var button = $(this);
        
        if (!providerId) {
            alert('Please select a provider to resend with.');
            return;
        }

        if (!toEmail || !subject) {
            alert('Please fill in the recipient email and subject.');
            return;
        }
        
        // Disable button during request
        button.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Resending...');
        
        $.ajax({
            url: proMailSMTPLogs.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_resend_email_log',
                log_id: logId,
                provider_id: providerId,
                to_email: toEmail,
                subject: subject,
                message: message,
                nonce: proMailSMTPLogs.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#resend-email-modal').remove();
                    alert('Email resent successfully!');
                    // Refresh the page to show updated status
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to resend email'));
                    button.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Resend Email');
                }
            },
            error: function() {
                alert('Error: Failed to resend email');
                button.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Resend Email');
            }
        });
    });

    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // Escape key
            $('#email-log-modal').remove();
            $('#resend-email-modal').remove();
        }
    });


});