<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap pro_mail_smtp-wrap">
    <div class="plugin-header">
        <span class="plugin-logo"></span>
        <h1><span><?php esc_html_e('PRO', 'pro-mail-smtp'); ?> </span><?php esc_html_e(' MAIL SMTP', 'pro-mail-smtp'); ?></h1>
    </div>

    <p class="description">
        <?php esc_html_e('Configure proactive alerts for email delivery failures across multiple channels.', 'pro-mail-smtp'); ?>
    </p>

    <nav class="pro-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?>" class="pro-mail-smtp-nav-tab">Providers</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-logs')); ?>" class="pro-mail-smtp-nav-tab">Email Logs</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-analytics')); ?>" class="pro-mail-smtp-nav-tab">Providers Logs</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-email-router')); ?>" class="pro-mail-smtp-nav-tab">Email Router</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-alerts')); ?>" class="pro-mail-smtp-nav-tab pro-mail-smtp-nav-tab-active">Alerts</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-settings')); ?>" class="pro-mail-smtp-nav-tab">Settings</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-about')); ?>" class="pro-mail-smtp-nav-tab">About</a>
    </nav>

    <div class="alerts-container">
        <div class="alerts-header">
            <h2><?php esc_html_e('Alert Configurations', 'pro-mail-smtp'); ?></h2>
            <button type="button" class="button button-primary" id="add-alert-btn">
                <?php esc_html_e('Add New Alert', 'pro-mail-smtp'); ?>
            </button>
        </div>

        <!-- Alerts Features Info -->
        <div class="alerts-info-box">
            <h3><?php esc_html_e('🚨 Proactive Email Failure Alerts', 'pro-mail-smtp'); ?></h3>
            <p><?php esc_html_e('Get instant notifications when emails fail to deliver. Never miss a critical delivery issue again!', 'pro-mail-smtp'); ?></p>
            
            <div class="features-grid">
                <div class="feature">
                    <h4><?php esc_html_e('📱 Multi-Channel Support', 'pro-mail-smtp'); ?></h4>
                    <p><?php esc_html_e('Slack, Discord, Microsoft Teams, or Custom Webhooks', 'pro-mail-smtp'); ?></p>
                </div>
                <div class="feature">
                    <h4><?php esc_html_e('🎯 Smart Thresholds', 'pro-mail-smtp'); ?></h4>
                    <p><?php esc_html_e('Set daily failure thresholds to prevent notification spam', 'pro-mail-smtp'); ?></p>
                </div>
                <div class="feature">
                    <h4><?php esc_html_e('📊 Detailed Information', 'pro-mail-smtp'); ?></h4>
                    <p><?php esc_html_e('Subject, recipient, error message, and provider details', 'pro-mail-smtp'); ?></p>
                </div>
            </div>
        </div>

        <!-- Alert Configurations List -->
        <div class="alerts-list">
            <?php if (empty($alert_configs)): ?>
                <div class="no-alerts">
                    <div class="no-alerts-icon">🔔</div>
                    <h3><?php esc_html_e('No Alert Configurations Yet', 'pro-mail-smtp'); ?></h3>
                    <p><?php esc_html_e('Create your first alert configuration to start monitoring email delivery failures.', 'pro-mail-smtp'); ?></p>
                    <button type="button" class="button button-primary" id="add-first-alert-btn">
                        <?php esc_html_e('Create First Alert', 'pro-mail-smtp'); ?>
                    </button>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Name', 'pro-mail-smtp'); ?></th>
                            <th scope="col"><?php esc_html_e('Channel', 'pro-mail-smtp'); ?></th>
                            <th scope="col"><?php esc_html_e('Threshold', 'pro-mail-smtp'); ?></th>
                            <th scope="col"><?php esc_html_e('Status', 'pro-mail-smtp'); ?></th>
                            <th scope="col"><?php esc_html_e('Actions', 'pro-mail-smtp'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alert_configs as $config): ?>
                            <tr>
                                <td class="config-name">
                                    <strong><?php echo esc_html($config->config_name); ?></strong>
                                </td>
                                <td class="channel-type">
                                    <span class="channel-badge channel-<?php echo esc_attr($config->channel_type); ?>">
                                        <?php echo esc_html(ucfirst($config->channel_type)); ?>
                                    </span>
                                </td>
                                <td class="threshold">
                                    <?php if ($config->failure_threshold > 0): ?>
                                        <?php 
                                        // translators: %d is the number of failures
                                        echo esc_html(sprintf(__('For %d failures/day', 'pro-mail-smtp'), $config->failure_threshold)); 
                                        ?>
                                    <?php else: ?>
                                        <?php esc_html_e('Every failure', 'pro-mail-smtp'); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="status">
                                    <span class="status-badge status-<?php echo $config->is_enabled ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $config->is_enabled ? esc_html__('Enabled', 'pro-mail-smtp') : esc_html__('Disabled', 'pro-mail-smtp'); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button type="button" class="button button-small edit-alert-btn" 
                                            data-config-id="<?php echo esc_attr($config->id); ?>"
                                            data-config-name="<?php echo esc_attr($config->config_name); ?>"
                                            data-channel-type="<?php echo esc_attr($config->channel_type); ?>"
                                            data-webhook-url="<?php echo esc_attr($config->webhook_url); ?>"
                                            data-failure-threshold="<?php echo esc_attr($config->failure_threshold); ?>"
                                            data-is-enabled="<?php echo esc_attr($config->is_enabled ? '1' : '0'); ?>">
                                        <?php esc_html_e('Edit', 'pro-mail-smtp'); ?>
                                    </button>
                                    <button type="button" class="button button-small test-alert-btn" data-config-id="<?php echo esc_attr($config->id); ?>">
                                        <?php esc_html_e('Test', 'pro-mail-smtp'); ?>
                                    </button>
                                    <button type="button" class="button button-small delete-alert-btn" data-config-id="<?php echo esc_attr($config->id); ?>">
                                        <?php esc_html_e('Delete', 'pro-mail-smtp'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Configuration Modal -->
    <div id="alert-config-modal" class="alert-modal">
        <div class="alert-modal-content">
            <div class="alert-modal-header">
                <h2 id="modal-title"><?php esc_html_e('Add Alert Configuration', 'pro-mail-smtp'); ?></h2>
                <span class="alert-modal-close">&times;</span>
            </div>
            
            <form id="alert-config-form">
                <input type="hidden" id="config-id" name="config_id" value="">
                
                <div class="form-row">
                    <label for="config-name"><?php esc_html_e('Configuration Name', 'pro-mail-smtp'); ?> <span class="required">*</span></label>
                    <input type="text" id="config-name" name="config_name" placeholder="<?php esc_attr_e('e.g., Dev Team Slack Alerts', 'pro-mail-smtp'); ?>" required>
                    <p class="description"><?php esc_html_e('A descriptive name for this alert configuration.', 'pro-mail-smtp'); ?></p>
                </div>

                <div class="form-row">
                    <label for="channel-type"><?php esc_html_e('Channel Type', 'pro-mail-smtp'); ?> <span class="required">*</span></label>
                    <select id="channel-type" name="channel_type" required>
                        <option value=""><?php esc_html_e('Select Channel Type', 'pro-mail-smtp'); ?></option>
                        <option value="slack"><?php esc_html_e('Slack', 'pro-mail-smtp'); ?></option>
                        <option value="discord"><?php esc_html_e('Discord', 'pro-mail-smtp'); ?></option>
                        <option value="teams"><?php esc_html_e('Microsoft Teams', 'pro-mail-smtp'); ?></option>
                        <option value="webhook"><?php esc_html_e('Custom Webhook', 'pro-mail-smtp'); ?></option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="webhook-url"><?php esc_html_e('Webhook URL', 'pro-mail-smtp'); ?> <span class="required">*</span></label>
                    <input type="url" id="webhook-url" name="webhook_url" placeholder="https://hooks.slack.com/services/..." required>
                    <p class="description" id="webhook-help">
                        <?php esc_html_e('Enter the webhook URL for your chosen platform.', 'pro-mail-smtp'); ?>
                    </p>
                </div>

                <div class="form-row">
                    <label for="failure-threshold"><?php esc_html_e('Daily Failure Threshold', 'pro-mail-smtp'); ?></label>
                    <input type="number" id="failure-threshold" name="failure_threshold" value="0" min="0" max="1000">
                    <p class="description">
                        <?php esc_html_e('Number of failures per day before sending alert. Set to 0 to alert on every failure.', 'pro-mail-smtp'); ?>
                        <br><strong><?php esc_html_e('Unique Feature:', 'pro-mail-smtp'); ?></strong> 
                        <?php esc_html_e('This threshold prevents notification overload and respects API rate limits.', 'pro-mail-smtp'); ?>
                    </p>
                </div>

                <div class="form-row">
                    <label class="checkbox-label">
                        <input type="checkbox" id="is-enabled" name="is_enabled" checked>
                        <?php esc_html_e('Enable this alert configuration', 'pro-mail-smtp'); ?>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Configuration', 'pro-mail-smtp'); ?>
                    </button>
                    <button type="button" class="button cancel-btn">
                        <?php esc_html_e('Cancel', 'pro-mail-smtp'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Webhook Setup Help -->
    <div class="webhook-help-section">
        <h3><?php esc_html_e('Webhook Setup Instructions', 'pro-mail-smtp'); ?></h3>
        
        <div class="help-tabs">
            <button type="button" class="help-tab active" data-tab="slack">Slack</button>
            <button type="button" class="help-tab" data-tab="discord">Discord</button>
            <button type="button" class="help-tab" data-tab="teams">Teams</button>
            <button type="button" class="help-tab" data-tab="webhook">Custom</button>
        </div>

        <div class="help-content">
            <div id="help-slack" class="help-panel active">
                <h4><?php esc_html_e('Setting up Slack Webhooks', 'pro-mail-smtp'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Go to your Slack workspace and click on the workspace name', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Select "Settings & administration" → "Manage apps"', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Search for "Incoming Webhooks" and add it to your workspace', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Choose the channel where alerts should be posted', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Copy the webhook URL and paste it above', 'pro-mail-smtp'); ?></li>
                </ol>
            </div>

            <div id="help-discord" class="help-panel">
                <h4><?php esc_html_e('Setting up Discord Webhooks', 'pro-mail-smtp'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Go to your Discord server and select the channel for alerts', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Click the gear icon next to the channel name', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Go to "Integrations" → "Webhooks"', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Click "New Webhook" and give it a name', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Copy the webhook URL and paste it above', 'pro-mail-smtp'); ?></li>
                </ol>
            </div>

            <div id="help-teams" class="help-panel">
                <h4><?php esc_html_e('Setting up Microsoft Teams Webhooks', 'pro-mail-smtp'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Go to your Teams channel and click the "..." menu', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Select "Connectors"', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Find "Incoming Webhook" and click "Configure"', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Provide a name and upload an image (optional)', 'pro-mail-smtp'); ?></li>
                    <li><?php esc_html_e('Copy the webhook URL and paste it above', 'pro-mail-smtp'); ?></li>
                </ol>
            </div>

            <div id="help-webhook" class="help-panel">
                <h4><?php esc_html_e('Custom Webhook Format', 'pro-mail-smtp'); ?></h4>
                <p><?php esc_html_e('Your custom webhook will receive a POST request with this JSON payload:', 'pro-mail-smtp'); ?></p>
                <pre><code>{
  "message": "Email failure alert message",
  "email_data": {
    "subject": "Email Subject",
    "to_email": "recipient@example.com", 
    "error_message": "SMTP Error details",
    "provider": "provider_name"
  },
  "site_name": "Your WordPress Site",
  "site_url": "https://yoursite.com",
  "timestamp": "2024-01-01T12:00:00+00:00"
}</code></pre>
            </div>
        </div>
    </div>
</div>
