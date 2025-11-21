<?php

namespace TurboSMTP\ProMailSMTP\DB;
if ( ! defined( 'ABSPATH' ) ) exit;

class EmailLogRepository {
    public function get_logs($filters) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        $values = [];
        $where = '';
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM %i";
        $values[] =  $table_name;
  
        if (!empty($filters['provider'])) {
            $where .= 'provider = %s';
            $values[] = $filters['provider'];
        }

        if (!empty($filters['status'])) {
            $where .= $where !== '' ? ' AND ' : '';
            $where .= 'status = %s';
            $values[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where .= $where !== '' ? ' AND ' : '';
            $where .= '(to_email LIKE %s OR subject LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($filters['date_from'])) {
            $where .= $where !== '' ? ' AND ' : '';
            $where .= 'sent_at >= %s';
            $values[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where .= $where !== '' ? ' AND ' : '';
            $where .= 'sent_at <= %s';
            $values[] = $filters['date_to'] . ' 23:59:59';
        }

        if ($where !== '') {
            $sql .= ' WHERE '.$where;
        }

        $orderby = $this->validate_orderby($filters['orderby']);
        $order = isset($filters['order']) && strtolower($filters['order']) === 'asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY %i {$order} ";
        $values[] = $orderby;
        $per_page = 20;
        $offset = ($filters['paged'] - 1) * $per_page;
        $sql .= " LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($wpdb->prepare($sql, $values));
    }

    public function get_total_logs() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var('SELECT FOUND_ROWS()');
    }

    private function validate_orderby($orderby) {
        $allowed = ['sent_at', 'provider', 'to_email', 'subject', 'status'];
        return in_array($orderby, $allowed, true) ? $orderby : 'sent_at';
    }

    /**
     * Get a single log entry by ID
     */
    public function get_log_by_id($log_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $table_name,
            $log_id
        ));
    }

    /**
     * Mark a log entry as resent
     */
    public function mark_as_resent($log_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->update(
            $table_name,
            ['is_resent' => 1],
            ['id' => $log_id],
            ['%d'],
            ['%d']
        );
    }
}
