<?php
namespace TurboSMTP\ProMailSMTP\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use TurboSMTP\ProMailSMTP\DB\EmailLogRepository;
use TurboSMTP\ProMailSMTP\Admin\Helpers\LogsHelper;

/**
 * Email Logs Admin Controller
 */
class Logs
{
    private $per_page = 20;
    private $providers_list = [];
    private $log_repository;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pro_mail_smtp_view_email_log', [$this, 'ajax_view_email_log']);
        add_action('wp_ajax_pro_mail_smtp_resend_email_log', [$this, 'ajax_resend_email_log']);
        add_action('wp_ajax_pro_mail_smtp_get_resend_modal', [$this, 'ajax_get_resend_modal']);
        $this->log_repository = new EmailLogRepository();
        $this->providers_list = include __DIR__ . '/../../config/providers-list.php';
    }

    public function enqueue_scripts($hook)
    {
        $expected_hook = 'pro-mail-smtp_page_pro-mail-smtp-logs';
        if ($hook !== $expected_hook) {
            return;
        }

        wp_enqueue_script(
            'pro-mail-smtp-logs',
            plugins_url('assets/js/logs.js', PRO_MAIL_SMTP_FILE),
            ['jquery'],
            PRO_MAIL_SMTP_VERSION,
            true
        );

        wp_localize_script(
            'pro-mail-smtp-logs',
            'proMailSMTPLogs',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pro_mail_smtp_logs_nonce')
            ]
        );

        wp_enqueue_style(
            'pro-mail-smtp-logs',
            plugins_url('assets/css/logs.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );
    }

    /**
     * Render the logs page
     */
    public function render()
    {
        $this->handle_form_submissions();
        
        $data = $this->prepare_view_data();
        
        $this->render_view('index', $data);
    }

    /**
     * Handle form submissions
     */
    private function handle_form_submissions()
    {
        // Handle retention settings update
        if (isset($_POST['retention_duration_setting']) && 
            isset($_POST['pro_mail_smtp_retention_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pro_mail_smtp_retention_nonce'])), 'pro_mail_smtp_update_retention')) {
            
            update_option('pro_mail_smtp_retention_duration', sanitize_text_field(wp_unslash($_POST['retention_duration_setting'])));
        }
        
        // Handle filters update
        if (isset($_POST['filter_action']) && 
            isset($_POST['pro_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pro_mail_smtp_logs_filter_nonce'])), 'pro_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : '',
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '',
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '',
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '',
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '',
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'sent_at',
                'order'     => isset($_POST['order']) && in_array(strtolower(wp_unslash($_POST['order'])), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : 'desc',
            ];
            
            update_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', $filter_data);
        }
    }

    /**
     * Prepare data for the view
     */
    private function prepare_view_data()
    {
        $current_retention = get_option('pro_mail_smtp_retention_duration', 'forever');
        $filters = $this->get_filters();
        $logs = $this->get_logs($filters);
        $total_items = $this->get_total_logs($filters);
        $total_pages = ceil($total_items / $this->per_page);

        return [
            'current_retention' => $current_retention,
            'filters' => $filters,
            'logs' => $logs,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'columns' => LogsHelper::get_columns(),
            'providers' => LogsHelper::get_providers($this->providers_list),
            'statuses' => LogsHelper::get_statuses(),
            'format_date' => [LogsHelper::class, 'format_date'],
            'time_diff' => [LogsHelper::class, 'time_diff'],
            'get_column_sort_class' => [LogsHelper::class, 'get_column_sort_class'],
        ];
    }

    /**
     * Render a view file
     */
    private function render_view($view, $data = [])
    {
        $view_file = __DIR__ . '/../../views/admin/logs/' . $view . '.php';
        
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            wp_die(sprintf('View file not found: %s', esc_html($view_file)));
        }
    }

    /**
     * Get logs from repository
     */
    private function get_logs($filters)
    {
        return $this->log_repository->get_logs($filters);
    }

    /**
     * Get total logs count
     */
    private function get_total_logs($filters)
    {
        return $this->log_repository->get_total_logs();
    }

    private function get_filters()
    {
        $defaults = [
            'paged'     => 1,
            'provider'  => '',
            'status'    => '',
            'search'    => '',
            'date_from' => '',
            'date_to'   => '',
            'orderby'   => 'sent_at',
            'order'     => 'desc',
        ];
        if (isset($_POST['pro_mail_smtp_logs_filter_nonce']) && 
            wp_verify_nonce(sanitize_text_field( wp_unslash ($_POST['pro_mail_smtp_logs_filter_nonce'])), 'pro_mail_smtp_logs_filter')) {
            
            $filter_data = [
                'paged'     => isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : $defaults['paged'],
                'provider'  => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : $defaults['provider'],
                'status'    => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : $defaults['status'],
                'search'    => isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : $defaults['search'],
                'date_from' => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : $defaults['date_from'],
                'date_to'   => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : $defaults['date_to'],
                'orderby'   => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : $defaults['orderby'],
                'order'     => isset($_POST['order']) && in_array(strtolower($_POST['order']), ['asc', 'desc'], true) 
                            ? strtolower(sanitize_text_field(wp_unslash($_POST['order']))) 
                            : $defaults['order'],
            ];
            
            $is_pagination_or_sort_only = isset($_POST['filter_action']) && 
                                          $_POST['filter_action'] === 'filter_logs' &&
                                          isset($_POST['paged']);
                                          
            $is_reset = isset($_POST['filter_action']) && 
                        $_POST['filter_action'] === 'filter_logs' &&
                        empty($_POST['provider']) && 
                        empty($_POST['status']) && 
                        empty($_POST['search']) && 
                        empty($_POST['date_from']) && 
                        empty($_POST['date_to']) &&
                        $_POST['paged'] == 1 &&
                        $_POST['orderby'] === 'sent_at' && 
                        $_POST['order'] === 'desc';
            
            if ($is_reset) {
                delete_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters');
                return $defaults;
            }
            
            if (!$is_pagination_or_sort_only || isset($_POST['provider']) || isset($_POST['status']) || 
                !empty($_POST['search']) || !empty($_POST['date_from']) || !empty($_POST['date_to'])) {
                
                $filter_save = $filter_data;
                $filter_save['paged'] = 1; 
                update_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', $filter_save);
            } else {
                $saved_filters = get_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', true);
                if (!empty($saved_filters) && is_array($saved_filters)) {
                    $filter_data = array_merge($saved_filters, ['paged' => $filter_data['paged']]);
                }
            }
            
            return $filter_data;
        }
        
        $saved_filters = get_user_meta(get_current_user_id(), 'pro_mail_smtp_log_filters', true);
        if (!empty($saved_filters) && is_array($saved_filters)) {
            return array_merge($defaults, $saved_filters);
        }
        
        return $defaults;
    }

    /**
     * AJAX handler for viewing email log details
     */
    public function ajax_view_email_log()
    {
        check_ajax_referer('pro_mail_smtp_logs_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pro-mail-smtp')]);
            return;
        }

        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        if (!$log_id) {
            wp_send_json_error(['message' => __('Invalid log ID.', 'pro-mail-smtp')]);
            return;
        }

        $log = $this->log_repository->get_log_by_id($log_id);
        if (!$log) {
            wp_send_json_error(['message' => __('Log entry not found.', 'pro-mail-smtp')]);
            return;
        }

        // Format the log data for display
        $formatted_log = [
            'id' => $log->id,
            'provider' => ucfirst($log->provider),
            'from_email' => $log->from_email ?? 'N/A',
            'to_email' => $log->to_email,
            'cc_email' => $log->cc_email ?? 'N/A',
            'bcc_email' => $log->bcc_email ?? 'N/A',
            'reply_to' => $log->reply_to ?? 'N/A',
            'subject' => $log->subject,
            'status' => ucfirst($log->status),
            'sent_at' => LogsHelper::format_date($log->sent_at),
            'message_id' => $log->message_id,
            'error_message' => $log->error_message,
            'email_content' => $log->message ?? (isset($log->email_content) ? $log->email_content : 'N/A'),
            'email_headers' => $log->headers ? json_decode($log->headers, true) : (isset($log->email_headers) ? json_decode($log->email_headers, true) : null),
            'attachment_data' => $log->attachment_data ? json_decode($log->attachment_data, true) : null,
            'is_resent' => $log->is_resent ?? false,
            'retry_count' => $log->retry_count ?? 0,
        ];

        wp_send_json_success(['log' => $formatted_log]);
    }

    /**
     * AJAX handler for getting resend modal with provider selection
     */
    public function ajax_get_resend_modal()
    {
        check_ajax_referer('pro_mail_smtp_logs_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pro-mail-smtp')]);
            return;
        }

        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        if (!$log_id) {
            wp_send_json_error(['message' => __('Invalid log ID.', 'pro-mail-smtp')]);
            return;
        }

        $log = $this->log_repository->get_log_by_id($log_id);
        if (!$log) {
            wp_send_json_error(['message' => __('Log entry not found.', 'pro-mail-smtp')]);
            return;
        }

        if ($log->status !== 'failed') {
            wp_send_json_error(['message' => __('Only failed emails can be resent.', 'pro-mail-smtp')]);
            return;
        }

        // Get available providers
        $conn_repo = new \TurboSMTP\ProMailSMTP\DB\ConnectionRepository();
        $provider_configs = $conn_repo->get_all_connections();
        
        $providers = [];
        foreach ($provider_configs as $config) {
            $providers[] = [
                'id' => $config->connection_id,
                'label' => $config->connection_label,
                'provider' => $config->provider
            ];
        }

        // Add fallback option
        $providers[] = [
            'id' => 'fallback',
            'label' => __('Fallback (PHP Mail)', 'pro-mail-smtp'),
            'provider' => 'phpmailer'
        ];

        wp_send_json_success([
            'log' => [
                'id' => $log->id,
                'to_email' => $log->to_email,
                'subject' => $log->subject,
                'email_content' => $log->message ?? (isset($log->email_content) ? $log->email_content : ''),
                'email_headers' => $log->headers ? json_decode($log->headers, true) : (isset($log->email_headers) ? json_decode($log->email_headers, true) : []),
                'provider' => $log->provider,
                'status' => $log->status,
                'sent_at' => LogsHelper::format_date($log->sent_at)
            ],
            'providers' => $providers
        ]);
    }

    /**
     * AJAX handler for resending failed emails with selected provider
     */
    public function ajax_resend_email_log()
    {
        check_ajax_referer('pro_mail_smtp_logs_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pro-mail-smtp')]);
            return;
        }

        $log_id = isset($_POST['log_id']) ? absint($_POST['log_id']) : 0;
        $provider_id = isset($_POST['provider_id']) ? sanitize_text_field(wp_unslash($_POST['provider_id'])) : '';
        $to_email = isset($_POST['to_email']) ? sanitize_email(wp_unslash($_POST['to_email'])) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? wp_unslash($_POST['message']) : '';
        
        if (!$log_id) {
            wp_send_json_error(['message' => __('Invalid log ID.', 'pro-mail-smtp')]);
            return;
        }

        if (empty($provider_id)) {
            wp_send_json_error(['message' => __('Please select a provider.', 'pro-mail-smtp')]);
            return;
        }

        if (empty($to_email) || empty($subject)) {
            wp_send_json_error(['message' => __('Recipient email and subject are required.', 'pro-mail-smtp')]);
            return;
        }

        $log = $this->log_repository->get_log_by_id($log_id);
        if (!$log) {
            wp_send_json_error(['message' => __('Log entry not found.', 'pro-mail-smtp')]);
            return;
        }

        if ($log->status !== 'failed') {
            wp_send_json_error(['message' => __('Only failed emails can be resent.', 'pro-mail-smtp')]);
            return;
        }

        try {
            // Parse headers and attachments from stored data
            $headers = [];
            $attachments = [];
            
            // Check both old and new column names for backward compatibility
            $stored_headers_data = $log->headers ?? (isset($log->email_headers) ? $log->email_headers : '');
            if (!empty($stored_headers_data)) {
                $stored_headers = json_decode($stored_headers_data, true);
                if (is_array($stored_headers)) {
                    $headers = $stored_headers;
                    // Extract attachments if they were stored in headers
                    if (isset($headers['attachments'])) {
                        $attachments = $headers['attachments'];
                        unset($headers['attachments']); // Remove from headers as it's handled separately
                    }
                }
            }
            
            // Check for attachment data in the new column
            if (!empty($log->attachment_data)) {
                $attachment_data = json_decode($log->attachment_data, true);
                if (is_array($attachment_data)) {
                    $attachments = array_merge($attachments, $attachment_data);
                }
            }

            // Use the Email Manager's manual resend function with modal data
            $email_manager = new \TurboSMTP\ProMailSMTP\Email\Manager();
            $result = $email_manager->manual_resend_email(
                $to_email,
                $subject,
                $message,
                $headers,
                $attachments,
                $provider_id,
                $log_id
            );

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => sprintf(__('Error resending email: %s', 'pro-mail-smtp'), $e->getMessage())]);
        }
    }
}
