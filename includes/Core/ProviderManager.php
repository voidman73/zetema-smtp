<?php

namespace TurboSMTP\ProMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\DB\ConnectionRepository;

class ProviderManager
{
    private $conn_repo;
    
    public function __construct() {
        $this->conn_repo = new ConnectionRepository();
    }

    public function save_provider($data)
    {
        $provider = sanitize_text_field($data['provider']);

        if ($provider === 'gmail' && !$data['connection_id']) {
            if ($this->conn_repo->provider_exists('gmail')) {
                wp_send_json_error('Only one Gmail provider can be added.');
                return;
            }
        }
        if ($provider === 'outlook' && !$data['connection_id'] ) {
            if ($this->conn_repo->provider_exists('outlook')) {
                wp_send_json_error('Only one Outlook provider can be added.');
                return;
            }
        }
        $config_keys = [];
        if (isset($data['config_keys']) && is_array($data['config_keys'])) {
            foreach ($data['config_keys'] as $key => $value) {
                if(($key === 'client_secret' || $key === 'client_id' || $key === 'api_key' ) && empty($value)) {
                    wp_send_json_error('API key is required');
                    return;
                } 
                $config_keys[$key] = sanitize_text_field($value);
            }
        } else {
            wp_send_json_error('Config keys are required');
            return;
        }

        $priority = isset($data['priority']) ? intval($data['priority']) : 1;
        $connection_label = (isset($data['connection_label']) && !empty($data['connection_label']))
            ? sanitize_text_field($data['connection_label'])
            : $provider . '-' . uniqid();
        $config_keys['connection_label'] = $connection_label;

        if ($provider === 'gmail') {
            $gmail = new \TurboSMTP\ProMailSMTP\Providers\Gmail([
                'client_id'     => $config_keys['client_id'],
                'client_secret' => $config_keys['client_secret']
            ]);
            $config_keys['auth_url'] = $gmail->get_auth_url();
            $config_keys['authenticated'] = false;
        }
        if ($provider === 'outlook') {
            $outlook = new \TurboSMTP\ProMailSMTP\Providers\Outlook([
                'client_id'     => $config_keys['client_id'],
                'client_secret' => $config_keys['client_secret']
            ]);
            $config_keys['auth_url'] = $outlook->get_auth_url();
            $config_keys['authenticated'] = false;
        }

        if (isset($data['connection_id']) && !empty($data['connection_id'])) {
            $connection_id = sanitize_text_field($data['connection_id']);
            $result = $this->conn_repo->update_connection($connection_id, $config_keys, $connection_label, $priority);
            if ($result === false) {
                wp_send_json_error('Failed to update provider.');
                return;
            }
        } else {
            $connection_id = uniqid();
            $result = $this->conn_repo->insert_connection($connection_id, $provider, $config_keys, $priority, $connection_label);
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
                return;
            } elseif ($result === false) {
                wp_send_json_error('Failed to add provider.');
                return;
            }
        }
        return $connection_id;
    }

    public function get_available_priority()
    {
        return $this->conn_repo->get_available_priority();
    }
}
