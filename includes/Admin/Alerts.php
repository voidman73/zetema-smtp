<?php
namespace TurboSMTP\ProMailSMTP\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\DB\AlertConfigRepository;

class Alerts {
    private $plugin_path;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pro_mail_smtp_save_alert_config', [$this, 'save_alert_config']);
        add_action('wp_ajax_pro_mail_smtp_test_alert', [$this, 'test_alert']);
        add_action('wp_ajax_pro_mail_smtp_delete_alert_config', [$this, 'delete_alert_config']);
        
        $this->plugin_path = PRO_MAIL_SMTP_PATH;
    }

    public function enqueue_scripts($hook) {
        $expected_hook = 'pro-mail-smtp_page_pro-mail-smtp-alerts';
        if ($hook !== $expected_hook) {
            return;
        }

        wp_enqueue_script(
            'pro-mail-smtp-alerts',
            plugins_url('assets/js/alerts.js', PRO_MAIL_SMTP_FILE),
            ['jquery', 'wp-util'],
            PRO_MAIL_SMTP_VERSION,
            true
        );

        wp_enqueue_style(
            'pro-mail-smtp-alerts',
            plugins_url('assets/css/alerts.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );

        wp_localize_script('pro-mail-smtp-alerts', 'ProMailSMTPAlerts', [
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('pro_mail_smtp_alerts'),
            'i18n' => [
                'error' => __('An error occurred. Please try again.', 'pro-mail-smtp'),
                'saving' => __('Saving...', 'pro-mail-smtp'),
                'saved' => __('Configuration saved successfully!', 'pro-mail-smtp'),
                'testing' => __('Testing...', 'pro-mail-smtp'),
                'tested' => __('Test alert sent successfully!', 'pro-mail-smtp'),
                'confirmDelete' => __('Are you sure you want to delete this alert configuration?', 'pro-mail-smtp'),
                'addNewAlert' => __('Add Alert Configuration', 'pro-mail-smtp'),
                'editAlert' => __('Edit Alert Configuration', 'pro-mail-smtp')
            ]
        ]);
    }

    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'pro-mail-smtp'));
        }

        $alert_repository = new AlertConfigRepository();
        $alert_configs = $alert_repository->get_all_alert_configs();
        
        $view_file = $this->plugin_path . '/views/admin/alerts/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Zetema SMTP Alerts', 'pro-mail-smtp') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('Error: View file not found.', 'pro-mail-smtp') . '</p></div>';
            echo '</div>';
        }
    }

    public function save_alert_config() {
        check_ajax_referer('pro_mail_smtp_alerts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'pro-mail-smtp'));
        }

        $config_data = [
            'channel_type' => sanitize_text_field($_POST['data']['channel_type'] ?? ''),
            'config_name' => sanitize_text_field($_POST['data']['config_name'] ?? ''),
            'webhook_url' => esc_url_raw($_POST['data']['webhook_url'] ?? ''),
            'failure_threshold' => absint($_POST['data']['failure_threshold'] ?? 0),
            'is_enabled' => intval($_POST['data']['is_enabled'] ?? 0) === 1 ? 1 : 0,
        ];

        // Validate required fields
        if (empty($config_data['channel_type']) || empty($config_data['config_name']) || empty($config_data['webhook_url'])) {
            wp_send_json_error(__('Please fill in all required fields.', 'pro-mail-smtp'));
        }

        // Validate channel type
        $allowed_channels = ['slack', 'discord', 'teams', 'webhook'];
        if (!in_array($config_data['channel_type'], $allowed_channels)) {
            wp_send_json_error(__('Invalid channel type.', 'pro-mail-smtp'));
        }

        $alert_repository = new AlertConfigRepository();
        
        if (isset($_POST['data']['id']) && !empty($_POST['data']['id'])) {
            // Update existing config
            $config_id = absint($_POST['data']['id']);
            $success = $alert_repository->update_alert_config($config_id, $config_data);
            
            if ($success) {
                wp_send_json_success(['message' => __('Alert configuration updated successfully.', 'pro-mail-smtp')]);
            } else {
                wp_send_json_error(__('Failed to update alert configuration.', 'pro-mail-smtp'));
            }
        } else {
            // Create new config
            $config_id = $alert_repository->create_alert_config($config_data);
            
            if ($config_id) {
                wp_send_json_success([
                    'message' => __('Alert configuration created successfully.', 'pro-mail-smtp'),
                    'config_id' => $config_id
                ]);
            } else {
                wp_send_json_error(__('Failed to create alert configuration.', 'pro-mail-smtp'));
            }
        }
    }

    public function test_alert() {
        check_ajax_referer('pro_mail_smtp_alerts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'pro-mail-smtp'));
        }

        $config_id = absint($_POST['config_id'] ?? 0);
        if (!$config_id) {
            wp_send_json_error(__('Invalid configuration ID.', 'pro-mail-smtp'));
        }

        $alert_repository = new AlertConfigRepository();
        $config = $alert_repository->get_alert_config($config_id);
        
        if (!$config) {
            wp_send_json_error(__('Alert configuration not found.', 'pro-mail-smtp'));
        }

        $alert_service = new \TurboSMTP\ProMailSMTP\Alerts\AlertService();
        $threshold = $config->failure_threshold;
        
        if ($threshold > 0) {
            // For threshold alerts, send a consolidated alert demo
            $mock_failures = $this->generate_mock_failures($threshold);
            $result = $alert_service->send_consolidated_alert($config, $mock_failures, $threshold);
            
            if ($result) {
                wp_send_json_success([
                    'message' => "Successfully sent consolidated test alert with {$threshold} mock failures! Check your webhook destination to see the consolidated format."
                ]);
            } else {
                wp_send_json_error("Failed to send consolidated test alert. Check webhook configuration.");
            }
        } else {
            // For threshold 0, send individual test alert
            $test_data = [
                'subject' => 'Individual failure test alert',
                'to_email' => 'test@example.com',
                'error_message' => 'This is a test alert for immediate notifications (threshold 0)',
                'provider' => 'test-provider',
                'is_test' => true
            ];

            $result = $alert_service->send_alert($config, $test_data);
            
            if ($result) {
                wp_send_json_success(['message' => 'Test alert sent successfully!']);
            } else {
                wp_send_json_error('Failed to send test alert.');
            }
        }
    }

    public function delete_alert_config() {
        check_ajax_referer('pro_mail_smtp_alerts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'pro-mail-smtp'));
        }

        $config_id = absint($_POST['config_id'] ?? 0);
        if (!$config_id) {
            wp_send_json_error(__('Invalid configuration ID.', 'pro-mail-smtp'));
        }

        $alert_repository = new AlertConfigRepository();
        $success = $alert_repository->delete_alert_config($config_id);

        if ($success) {
            wp_send_json_success(['message' => __('Alert configuration deleted successfully.', 'pro-mail-smtp')]);
        } else {
            wp_send_json_error(__('Failed to delete alert configuration.', 'pro-mail-smtp'));
        }
    }

    /**
     * Generate mock failures for testing consolidated alerts
     */
    private function generate_mock_failures($count) {
        $mock_failures = [];
        $providers = ['Gmail', 'SendGrid', 'Mailgun', 'SMTP2Go'];
        $error_types = [
            'SMTP authentication failed - invalid credentials',
            'Recipient address rejected - mailbox does not exist',
            'Daily sending quota exceeded',
            'Connection timeout - unable to reach server',
            'Message rejected - content identified as spam',
            'Invalid sender domain configuration'
        ];
        $subjects = [
            'Password Reset Request',
            'Order Confirmation #12345',
            'Weekly Newsletter',
            'Account Verification',
            'Payment Receipt',
            'Welcome Email'
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $mock_failure = (object) [
                'id' => $i + 1,
                'subject' => $subjects[array_rand($subjects)],
                'to_email' => 'user' . ($i + 1) . '@example.com',
                'provider' => $providers[array_rand($providers)],
                'error_message' => $error_types[array_rand($error_types)],
                'sent_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' minutes')),
                'status' => 'failed'
            ];
            $mock_failures[] = $mock_failure;
        }
        
        return $mock_failures;
    }
}
