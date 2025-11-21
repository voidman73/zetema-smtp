<?php
namespace TurboSMTP\ProMailSMTP\Admin;

use TurboSMTP\ProMailSMTP\DB\ConnectionRepository;
use TurboSMTP\ProMailSMTP\Providers\ProviderFactory;
class Analytics {
    private $providers = [];
    private $plugin_path;
    private $connection_repository;
    private $provider_factory;

    public function __construct() {
        $this->plugin_path = PRO_MAIL_SMTP_PATH;
        $this->connection_repository = new ConnectionRepository();
        $this->providers = $this->connection_repository->get_all_connections();
        $this->provider_factory = new ProviderFactory();

        add_action('wp_ajax_pro_mail_smtp_fetch_provider_analytics', [$this, 'fetch_provider_analytics']);
        add_action('wp_ajax_pro_mail_smtp_get_provider_config', [$this, 'get_provider_config_ajax']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook) {

        if ($hook !== 'pro-mail-smtp_page_pro-mail-smtp-analytics') {
            return;
        }
    
        wp_enqueue_style(
            'pro-mail-smtp-analytics',
            plugins_url('/assets/css/analytics.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );
    
        wp_enqueue_script(
            'pro-mail-smtp-analytics',
            plugins_url('/assets/js/analytics.js', PRO_MAIL_SMTP_FILE),
            ['jquery'],
            PRO_MAIL_SMTP_VERSION,
            true
        );

        wp_localize_script(
            'pro-mail-smtp-analytics',
            'ProMailSMTPAnalytics',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pro_mail_smtp_analytics')
            ]
        );
    }

    public function render() {
        $filters = $this->get_filter_values();
        $selected_provider_config = null;
        
        if (!empty($filters['selected_provider'])) {
            $selected_provider_config = $this->get_provider_config($filters['selected_provider']);
        }
        
        $data = [
            'providers' => $this->providers,
            'filters' => $filters,
            'analytics_data' => $this->get_analytics_data(),
            'selected_provider_config' => $selected_provider_config
        ];

        $view_file = $this->plugin_path . '/views/admin/analytics/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        }
    }

    private function get_analytics_data() {
        $filters = $this->get_filter_values();
        try {
            if (!empty($filters['selected_provider'])) {
                return $this->get_provider_analytics($filters['selected_provider'], $filters);
            } else {
                $all_data = [];
                foreach ($this->providers as $provider) {
                    $provider_data = $this->get_provider_analytics($provider->connection_id, $filters);
                    $all_data = array_merge($all_data, $provider_data);
                }
                return $all_data;
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    private function get_filter_values() {
        $defaults = [
            'selected_provider' => '',
            'selected_status'   => '',
            'date_from'         => '',
            'date_to'           => '',
            'page'              => 1,
            'per_page'          => 10
        ];
        
        if (isset($_POST['filter_action']) && $_POST['filter_action'] === 'filter_analytics') {
            if (!isset($_POST['pro_mail_smtp_analytics_nonce']) || 
                !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['pro_mail_smtp_analytics_nonce'])), 'pro_mail_smtp_analytics')) {
                wp_die('Security check failed. Please try again.');
            }
            
            $filter_data = [
                'selected_provider' => isset($_POST['provider']) ? sanitize_text_field(wp_unslash($_POST['provider'])) : $defaults['selected_provider'],
                'selected_status'   => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : $defaults['selected_status'],
                'date_from'         => isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : $defaults['date_from'],
                'date_to'           => isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : $defaults['date_to'],
                'page'              => isset($_POST['page']) ? (int) $_POST['page'] : $defaults['page'],
                'per_page'          => isset($_POST['per_page']) ? (int) $_POST['per_page'] : $defaults['per_page']
            ];
            
            update_user_meta(get_current_user_id(), 'pro_mail_smtp_analytics_filters', $filter_data);
            return $filter_data;
        }
        
        $saved_filters = get_user_meta(get_current_user_id(), 'pro_mail_smtp_analytics_filters', true);
        if (!empty($saved_filters) && is_array($saved_filters)) {
            $merged_filters = array_merge($defaults, $saved_filters);
        } else {
            $merged_filters = $defaults;
        }
        
        // Auto-select first provider if none is selected
        if (empty($merged_filters['selected_provider']) && !empty($this->providers)) {
            $merged_filters['selected_provider'] = $this->providers[0]->connection_id;
        }
        
        return $merged_filters;
    }

    public function fetch_provider_analytics() {
        check_ajax_referer('pro_mail_smtp_analytics', 'nonce', true);

        $provider_id = isset($_POST['filters']['provider']) ? sanitize_text_field(wp_unslash($_POST['filters']['provider'])) : '';
        $status = isset($_POST['filters']['status']) ? sanitize_text_field(wp_unslash($_POST['filters']['status'])) : '';
        $date_from = isset($_POST['filters']['date_from']) ? sanitize_text_field(wp_unslash($_POST['filters']['date_from'])) : '';
        $date_to = isset($_POST['filters']['date_to']) ? sanitize_text_field(wp_unslash($_POST['filters']['date_to'])) : '';
        $page = isset($_POST['filters']['page']) ? max(1, (int) $_POST['filters']['page']) : 1;
        $per_page = isset($_POST['filters']['per_page']) ? max(1, (int) $_POST['filters']['per_page']) : 10;

        if (empty($provider_id)) {
            wp_send_json_error('Please setup a connection to fetch analytics.');
            return;
        }

        try {
            $provider_data = $this->get_provider_analytics(
                $provider_id,
                [
                    'status'    => $status,
                    'date_from' => $date_from,
                    'date_to'   => $date_to,
                    'page'      => $page,
                    'per_page'  => $per_page
                ]
            );
            wp_send_json_success($provider_data);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function get_provider_config_ajax() {
        check_ajax_referer('pro_mail_smtp_analytics', 'nonce', true);
        
        $provider_id = isset($_POST['provider_id']) ? sanitize_text_field(wp_unslash($_POST['provider_id'])) : '';
        
        if (empty($provider_id)) {
            wp_send_json_error('Provider ID is required.');
            return;
        }
        
        $provider_config = $this->get_provider_config($provider_id);
        
        if (!$provider_config) {
            wp_send_json_error('Provider configuration not found.');
            return;
        }
        
        wp_send_json_success($provider_config);
    }

    private function get_provider_analytics($provider_id, $filters) {
        $provider_config = $this->get_provider_config($provider_id);

        if (!$provider_config) {
            throw new \Exception('Provider configuration not found');
        }
        $provider = $this->provider_factory->get_provider_class($provider_config);
        return $provider->get_analytics($filters);
    }

    private function get_provider_config($connection_id) {
        $connection = $this->connection_repository->get_connection($connection_id);
        if (!$connection) {
            return null;
        }
        
        $providers_config = include PRO_MAIL_SMTP_PATH . 'config/providers-list.php';
        $provider_class = isset($providers_config[$connection->provider]['class']) 
            ? $providers_config[$connection->provider]['class'] 
            : ucfirst($connection->provider);
        
        $connection->provider_class = $provider_class;
        $connection->config_keys = json_encode($connection->connection_data);
        
        return $connection;
    }
}