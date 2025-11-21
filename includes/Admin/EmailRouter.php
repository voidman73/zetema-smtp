<?php
namespace TurboSMTP\ProMailSMTP\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class EmailRouter {
    private $providersList = [];
    private $plugin_path;

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_pro_mail_smtp_save_email_router', [$this, 'save_email_router']);
        add_action('wp_ajax_pro_mail_smtp_update_email_router_status', [$this, 'update_condition_status']); 
        add_action('wp_ajax_pro_mail_smtp_get_email_router_condition', [$this, 'get_email_router_condition']); 
        add_action('wp_ajax_pro_mail_smtp_delete_email_router_condition', [$this, 'delete_email_router_condition']); 

        $this->providersList = include __DIR__ . '/../../config/providers-list.php';
        $this->plugin_path = PRO_MAIL_SMTP_PATH;
    }

    private function get_active_plugins_list() {
        return get_option('pro_mail_smtp_active_plugins_list', []);
    }

    public function enqueue_scripts($hook) {

        if ($hook !== 'pro-mail-smtp_page_pro-mail-smtp-email-router') {
            return;
        }
    
        wp_enqueue_style(
            'pro-mail-smtp-email-router',
            plugins_url('/assets/css/emailrouter.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );
    
        wp_enqueue_script(
            'pro-mail-smtp-email-router',
            plugins_url('/assets/js/emailrouter.js', PRO_MAIL_SMTP_FILE),
            ['jquery'],
            PRO_MAIL_SMTP_VERSION,
            true
        );
        
        wp_localize_script('pro-mail-smtp-email-router', 'ProMailSMTPEmailRouter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pro_mail_smtp_email_router'),
            'debug' => true,
            'pluginsList' => wp_json_encode($this->get_active_plugins_list())
        ]);
    }
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'pro-mail-smtp'));
        }
        $conditions_repo = new \TurboSMTP\ProMailSMTP\DB\ConditionRepository();
        $connections_repo = new \TurboSMTP\ProMailSMTP\DB\ConnectionRepository();

        $conditions_list = $conditions_repo->load_all_conditions();
        $connections_list = $connections_repo->get_all_connections();
        $providers_list = $this->providersList;
        $view_file = $this->plugin_path . '/views/admin/emailrouter/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Zetema SMTP Email Router', 'pro-mail-smtp') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('Error: View file not found.', 'pro-mail-smtp') . '</p></div>';
            echo '</div>';
        }
    }
    
    public function save_email_router() {
        check_ajax_referer('pro_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $prepared_data = [
            'connection_id'        => isset($_POST['data']['connection']['selected'])? sanitize_text_field(wp_unslash($_POST['data']['connection']['selected'])) : null,
            'condition_data'       => isset($_POST['data']['conditions']) ? wp_json_encode( map_deep( wp_unslash( $_POST['data']['conditions'] ), 'sanitize_text_field' ) ) : wp_json_encode([]),
            'condition_label'      => isset($_POST['data']['label']) ? sanitize_text_field(wp_unslash($_POST['data']['label'])) : '',
            'overwrite_connection' => isset($_POST['data']['connection']['enabled']) ? 1 : 0,
            'overwrite_sender'     => isset($_POST['data']['email']['enabled']) ? 1 : 0,
            'forced_senderemail'   => isset($_POST['data']['email']['enabled']) ? (isset($_POST['data']['email']['email']) ? sanitize_email(wp_unslash($_POST['data']['email']['email'])): null) : null,
            'forced_sendername'    => isset($_POST['data']['email']['enabled']) ? (isset($_POST['data']['email']['name'])  ? sanitize_text_field(wp_unslash($_POST['data']['email']['name'])) : null) : null,
            'is_enabled'           => isset($_POST['data']['is_enabled']) ? absint($_POST['data']['is_enabled']) : 0,
        ];

        $condition_repo = new \TurboSMTP\ProMailSMTP\DB\ConditionRepository();
        if (isset($_POST['data']['id']) && !empty($_POST['data']['id'])) {
            $condition_id = absint($_POST['data']['id']);
            $success = $condition_repo->update_condition($condition_id, $prepared_data);
            
            if (!$success) {
                wp_send_json_error(['message' => esc_html__('Failed to update router condition.', 'pro-mail-smtp')]);
                return;
            }
            
            wp_send_json_success([
                'message' => esc_html__('Router condition updated successfully!', 'pro-mail-smtp'),
                'id' => $condition_id,
                'operation' => 'update'
            ]);
        } 
        else {
            $insert_id = $condition_repo->add_condition($prepared_data);
            
            if (!$insert_id) {
                wp_send_json_error(['message' => esc_html__('Failed to create new router condition.', 'pro-mail-smtp')]);
                return;
            }
            
            wp_send_json_success([
                'message' => esc_html__('New router condition created successfully!', 'pro-mail-smtp'),
                'id' => $insert_id,
                'operation' => 'insert'
            ]);
        }
    }

    public function update_condition_status() {
        check_ajax_referer('pro_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        $status = isset($_POST['status']) ? absint($_POST['status']) : 0;

        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'pro-mail-smtp')]);
            return;
        }

        $update_data = ['is_enabled' => $status];
        $condition_repo = new \TurboSMTP\ProMailSMTP\DB\ConditionRepository();
        $updated = $condition_repo->update_condition($condition_id, $update_data);

        if (!$updated) {
            wp_send_json_error(['message' => esc_html__('Failed to update status.', 'pro-mail-smtp')]);
        } else {
            wp_send_json_success(['message' => esc_html__('Status updated successfully', 'pro-mail-smtp')]);
        }
    }

    public function get_email_router_condition() {
        check_ajax_referer('pro_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'pro-mail-smtp')]);
            return;
        }
        $condition_repo = new \TurboSMTP\ProMailSMTP\DB\ConditionRepository();
        $condition = $condition_repo->get_condition($condition_id);
        if (!$condition) {
            wp_send_json_error(['message' => esc_html__('Condition not found', 'pro-mail-smtp')]);
            return;
        }
        $condition->condition_data = json_decode($condition->condition_data, true);
        wp_send_json_success($condition);
    }

    public function delete_email_router_condition() {
        check_ajax_referer('pro_mail_smtp_email_router', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $condition_id = isset($_POST['condition_id']) ? absint($_POST['condition_id']) : 0;
        if (!$condition_id) {
            wp_send_json_error(['message' => esc_html__('Invalid condition ID', 'pro-mail-smtp')]);
            return;
        }

        $condition_repo = new \TurboSMTP\ProMailSMTP\DB\ConditionRepository();
        $deleted = $condition_repo->delete_condition($condition_id);

        if (!$deleted) {
            wp_send_json_error(['message' => esc_html__('Failed to delete condition.', 'pro-mail-smtp')]);
        } else {
            wp_send_json_success(['message' => esc_html__('Condition deleted successfully', 'pro-mail-smtp')]);
        }
    }
}
