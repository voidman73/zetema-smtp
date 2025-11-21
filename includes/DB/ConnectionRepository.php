<?php
namespace TurboSMTP\ProMailSMTP\DB;

if ( ! defined( 'ABSPATH' ) ) exit;

class ConnectionRepository {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pro_mail_smtp_connections';
    }
    
    public function insert_connection($connection_id, $provider, $connection_data, $priority = 0, $connection_label = '') {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i", $this->table));


        if ($count >= 5) {
            return new \WP_Error('max_entries', 'Maximum number of connections reached.');
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i WHERE priority = %d", $this->table, $priority));

        if ($exists > 0) {
            return new \WP_Error('duplicate_priority', 'The priority value must be unique.');
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->insert(
            $this->table,
            [
                'connection_id'      => $connection_id,
                'provider'           => $provider,
                'connection_label'   => $connection_label,
                'priority'           => $priority,
                'connection_data'    => json_encode($connection_data),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%s'
            ]
        );
        return $result ? $wpdb->insert_id : false;
    }
    
    public function update_connection($connection_id, $connection_data, $connection_label = null, $priority = null) {
        global $wpdb;
        
        $current = $this->get_connection($connection_id);
        if (!$current) {
            return new \WP_Error('not_found', 'Connection not found.');
        }
        
        $new_priority = ($priority !== null) ? intval($priority) : intval($current->priority);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM %i WHERE priority = %d AND connection_id != %s",
            $this->table,
            $new_priority,
            $connection_id
        ));
        if ($exists > 0) {
            return new \WP_Error('duplicate_priority', 'The priority value must be unique.');
        }
        
        $update_data = [
            'connection_data' => json_encode($connection_data)
        ];
        $format = ['%s'];
        if (!is_null($connection_label)) {
            $update_data['connection_label'] = $connection_label;
            $format[] = '%s';
        }
        if ($priority !== null) {
            $update_data['priority'] = $new_priority;
            $format[] = '%d';
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->update(
            $this->table,
            $update_data,
            ['connection_id' => $connection_id],
            $format,
            ['%s']
        );
    }
    
    /**
     * Get a single connection by connection ID
     */
  public function get_connection($connection_id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM %i WHERE connection_id = %s", $this->table, $connection_id)
        );
        if ($row) {
            $row->connection_data = json_decode($row->connection_data, true);
        }
        return $row;
    }
    
    public function delete_connection($connection_id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->delete($this->table, ['connection_id' => $connection_id], ['%s']);
    }
    
    public function get_all_connections() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i ORDER BY priority ASC", $this->table));
        if ($results) {
            foreach ($results as &$row) {
                $decoded_data = json_decode($row->connection_data, true);
                $row->connection_data = is_array($decoded_data) ? $decoded_data : [];
            }
        }
        return $results;
    }

    public function get_available_priority() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i ORDER BY priority ASC", $this->table));
        $priorities = [];
        if ($results) {
            foreach ($results as $row) {
                $priorities[] = $row->priority;
            }
        }
        $available = [];
        for ($i = 1; $i < 10; $i++) {
            if (!in_array($i, $priorities)) {
                $available[] = $i;
            }
        }
        return $available;
    }

    public function provider_exists($provider) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM %i WHERE provider = %s",
        $this->table,
            $provider
        ));
        return $count > 0;
    }
}
