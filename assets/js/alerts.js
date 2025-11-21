jQuery(document).ready(function($) {
    'use strict';

    const ProMailSMTPAlertsJS = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Modal controls
            $(document).on('click', '#add-alert-btn, #add-first-alert-btn', this.openModal);
            $(document).on('click', '.edit-alert-btn', this.editAlert);
            $(document).on('click', '.alert-modal-close, .cancel-btn', this.closeModal);
            $(document).on('click', '.alert-modal', function(e) {
                if (e.target === this) {
                    ProMailSMTPAlertsJS.closeModal();
                }
            });

            // Form submission
            $(document).on('submit', '#alert-config-form', this.saveAlert);

            // Test alert
            $(document).on('click', '.test-alert-btn', this.testAlert);

            // Delete alert
            $(document).on('click', '.delete-alert-btn', this.deleteAlert);

            // Channel type change
            $(document).on('change', '#channel-type', this.updateWebhookHelp);

            // Help tabs
            $(document).on('click', '.help-tab', this.switchHelpTab);

            // ESC key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    ProMailSMTPAlertsJS.closeModal();
                }
            });
        },

        openModal: function() {
            $('#config-id').val('');
            $('#alert-config-form')[0].reset();
            $('#modal-title').text(ProMailSMTPAlerts.i18n.addNewAlert || 'Add Alert Configuration');
            $('#is-enabled').prop('checked', true);
            $('#alert-config-modal').show();
            $('#config-name').focus();
        },

        editAlert: function() {
            const $btn = $(this);
            const configId = $btn.data('config-id');
            
            // Extract data from button data attributes
            const configName = $btn.data('config-name');
            const channelType = $btn.data('channel-type');
            const webhookUrl = $btn.data('webhook-url');
            const failureThreshold = $btn.data('failure-threshold');
            const isEnabled = $btn.data('is-enabled');
            
            // Populate form
            $('#config-id').val(configId);
            $('#config-name').val(configName);
            $('#channel-type').val(channelType).trigger('change');
            $('#webhook-url').val(webhookUrl);
            $('#failure-threshold').val(failureThreshold);
            
            // Handle is_enabled as it could be string or boolean
            const shouldCheck = (isEnabled === 1 || isEnabled === '1' || isEnabled === true || isEnabled === 'true');
            $('#is-enabled').prop('checked', shouldCheck);
            
            $('#modal-title').text(ProMailSMTPAlerts.i18n.editAlert || 'Edit Alert Configuration');
            $('#alert-config-modal').show();
            $('#config-name').focus();
        },

        closeModal: function() {
            $('#alert-config-modal').hide();
        },

        saveAlert: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Validate form
            if (!ProMailSMTPAlertsJS.validateForm($form)) {
                return false;
            }
            
            // Prepare data
            const formData = {
                action: 'pro_mail_smtp_save_alert_config',
                nonce: ProMailSMTPAlerts.nonce,
                data: {
                    id: $('#config-id').val(),
                    channel_type: $('#channel-type').val(),
                    config_name: $('#config-name').val(),
                    webhook_url: $('#webhook-url').val(),
                    failure_threshold: $('#failure-threshold').val(),
                    is_enabled: $('#is-enabled').is(':checked') ? 1 : 0
                }
            };
            
            // Show loading state
            $submitBtn.addClass('loading').text(ProMailSMTPAlerts.i18n.saving);
            
            $.post(ProMailSMTPAlerts.ajaxUrl, formData)
                .done(function(response) {
                    if (response.success) {
                        ProMailSMTPAlertsJS.showNotice('success', response.data.message);
                        ProMailSMTPAlertsJS.closeModal();
                        // Reload page to show updated data
                        location.reload();
                    } else {
                        ProMailSMTPAlertsJS.showNotice('error', response.data || ProMailSMTPAlerts.i18n.error);
                    }
                })
                .fail(function() {
                    ProMailSMTPAlertsJS.showNotice('error', ProMailSMTPAlerts.i18n.error);
                })
                .always(function() {
                    $submitBtn.removeClass('loading').text(originalText);
                });
        },

        testAlert: function() {
            const configId = $(this).data('config-id');
            const $btn = $(this);
            const originalText = $btn.text();
            
            if (!confirm('Send a test alert to verify your configuration?')) {
                return;
            }
            
            $btn.addClass('loading').text(ProMailSMTPAlerts.i18n.testing);
            
            $.post(ProMailSMTPAlerts.ajaxUrl, {
                action: 'pro_mail_smtp_test_alert',
                nonce: ProMailSMTPAlerts.nonce,
                config_id: configId
            })
            .done(function(response) {
                if (response.success) {
                    ProMailSMTPAlertsJS.showNotice('success', response.data.message);
                } else {
                    ProMailSMTPAlertsJS.showNotice('error', response.data || ProMailSMTPAlerts.i18n.error);
                }
            })
            .fail(function() {
                ProMailSMTPAlertsJS.showNotice('error', ProMailSMTPAlerts.i18n.error);
            })
            .always(function() {
                $btn.removeClass('loading').text(originalText);
            });
        },

        deleteAlert: function() {
            const configId = $(this).data('config-id');
            const $btn = $(this);
            
            if (!confirm(ProMailSMTPAlerts.i18n.confirmDelete)) {
                return;
            }
            
            const originalText = $btn.text();
            $btn.addClass('loading').text('Deleting...');
            
            $.post(ProMailSMTPAlerts.ajaxUrl, {
                action: 'pro_mail_smtp_delete_alert_config',
                nonce: ProMailSMTPAlerts.nonce,
                config_id: configId
            })
            .done(function(response) {
                if (response.success) {
                    ProMailSMTPAlertsJS.showNotice('success', response.data.message);
                    // Remove row from table
                    $btn.closest('tr').fadeOut(function() {
                        $(this).remove();
                        // Check if table is empty
                        if ($('.alerts-list tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    ProMailSMTPAlertsJS.showNotice('error', response.data || ProMailSMTPAlerts.i18n.error);
                }
            })
            .fail(function() {
                ProMailSMTPAlertsJS.showNotice('error', ProMailSMTPAlerts.i18n.error);
            })
            .always(function() {
                $btn.removeClass('loading').text(originalText);
            });
        },

        validateForm: function($form) {
            let isValid = true;
            
            // Remove previous error classes
            $form.find('.error').removeClass('error');
            
            // Required fields
            const requiredFields = ['#config-name', '#channel-type', '#webhook-url'];
            
            requiredFields.forEach(function(field) {
                const $field = $(field);
                if (!$field.val().trim()) {
                    $field.addClass('error');
                    isValid = false;
                }
            });
            
            // Validate URL
            const webhookUrl = $('#webhook-url').val();
            if (webhookUrl && !ProMailSMTPAlertsJS.isValidUrl(webhookUrl)) {
                $('#webhook-url').addClass('error');
                ProMailSMTPAlertsJS.showNotice('error', 'Please enter a valid webhook URL.');
                isValid = false;
            }
            
            return isValid;
        },

        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },

        updateWebhookHelp: function() {
            const channelType = $(this).val();
            const helpTexts = {
                slack: 'Enter your Slack incoming webhook URL (starts with https://hooks.slack.com/)',
                discord: 'Enter your Discord webhook URL (ends with /webhooks/...)',
                teams: 'Enter your Microsoft Teams incoming webhook URL',
                webhook: 'Enter your custom webhook endpoint URL'
            };
            
            const helpText = helpTexts[channelType] || 'Enter the webhook URL for your chosen platform.';
            $('#webhook-help').text(helpText);
            
            // Update placeholder
            const placeholders = {
                slack: 'https://hooks.slack.com/services/...',
                discord: 'https://discord.com/api/webhooks/...',
                teams: 'https://outlook.office.com/webhook/...',
                webhook: 'https://your-server.com/webhook'
            };
            
            const placeholder = placeholders[channelType] || 'https://your-webhook-url.com';
            $('#webhook-url').attr('placeholder', placeholder);
        },

        switchHelpTab: function() {
            const tabName = $(this).data('tab');
            
            // Update active tab
            $('.help-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update active panel
            $('.help-panel').removeClass('active');
            $('#help-' + tabName).addClass('active');
        },

        showNotice: function(type, message) {
            // Remove existing notices
            $('.pro-mail-smtp-notice').remove();
            
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible pro-mail-smtp-notice">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            // Insert after page title
            $('.wrap h1').first().after(notice);
            
            // Auto dismiss success notices
            if (type === 'success') {
                setTimeout(function() {
                    notice.fadeOut();
                }, 5000);
            }
            
            // Handle dismiss button
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut();
            });
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: notice.offset().top - 100
            }, 500);
        }
    };

    // Add error styling to form validation
    const style = $(`
        <style>
            .form-row input.error,
            .form-row select.error {
                border-color: #d63638 !important;
                box-shadow: 0 0 0 1px #d63638 !important;
            }
        </style>
    `);
    $('head').append(style);

    // Initialize
    ProMailSMTPAlertsJS.init();
});
