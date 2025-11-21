<?php
namespace TurboSMTP\ProMailSMTP\DB;

if ( ! defined( 'ABSPATH' ) ) exit;

class AlertConfigRepository {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pro_mail_smtp_alert_configs';
    }

    public function get_all_alert_configs() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $configs = $wpdb->get_results(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC"
        );
        
        return $this->normalize_configs($configs);
    }

    public function get_alert_config($id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $config = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id)
        );
        
        return $this->normalize_config($config);
    }

    public function get_enabled_alert_configs() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $configs = $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE is_enabled = 1"
        );
        
        return $this->normalize_configs($configs);
    }

    public function create_alert_config($data) {
        global $wpdb;
        
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->insert(
            $this->table,
            $data,
            [
                '%s', // channel_type
                '%s', // config_name
                '%s', // webhook_url
                '%d', // failure_threshold
                '%d', // is_enabled
                '%s', // created_at
                '%s'  // updated_at
            ]
        );

        if ($result !== false) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public function update_alert_config($id, $data) {
        global $wpdb;
        
        $data['updated_at'] = current_time('mysql');
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->update(
            $this->table,
            $data,
            ['id' => $id],
            [
                '%s', // channel_type
                '%s', // config_name
                '%s', // webhook_url
                '%d', // failure_threshold
                '%d', // is_enabled
                '%s'  // updated_at
            ],
            ['%d']
        );
    }

    public function delete_alert_config($id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
    }

    public function get_failure_count_today($date = null) {
        global $wpdb;
        
        if (!$date) {
            $date = current_time('Y-m-d');
        }
        
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} 
                 WHERE status = 'failed' 
                 AND DATE(sent_at) = %s",
                $date
            )
        );
    }

    public function get_recent_failures($limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                 WHERE status = 'failed' 
                 ORDER BY sent_at DESC 
                 LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Normalize alert config data types after database retrieval
     */
    private function normalize_config($config) {
        if (!$config) {
            return $config;
        }
        
        $config->is_enabled = intval($config->is_enabled);
        $config->failure_threshold = intval($config->failure_threshold);
        $config->id = intval($config->id);
        
        return $config;
    }

    /**
     * Normalize array of alert configs
     */
    private function normalize_configs($configs) {
        if (!is_array($configs)) {
            return $configs;
        }
        
        return array_map([$this, 'normalize_config'], $configs);
    }
}
