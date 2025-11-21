<?php

namespace TurboSMTP\ProMailSMTP\Admin\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class for Email Logs formatting and utilities
 */
class LogsHelper
{
    /**
     * Format date for display
     */
    public static function format_date($date)
    {
        return \date_i18n(\get_option('date_format') . ' ' . \get_option('time_format'), \strtotime($date));
    }

    /**
     * Get time difference for display
     */
    public static function time_diff($date)
    {
        return \human_time_diff(\strtotime($date), \current_time('timestamp')) . ' ' . \__('ago', 'pro-mail-smtp');
    }

    /**
     * Get column sort class
     */
    public static function get_column_sort_class($column, $filters)
    {
        $classes = ['sortable'];

        if ($filters['orderby'] === $column) {
            $classes[] = $filters['order'] === 'asc' ? 'asc' : 'desc';
            $classes[] = 'sorted';
        }

        return implode(' ', $classes);
    }

    /**
     * Get available columns
     */
    public static function get_columns()
    {
        return [
            'sent_at' => \__('Date', 'pro-mail-smtp'),
            'provider' => \__('Provider', 'pro-mail-smtp'),
            'to_email' => \__('To', 'pro-mail-smtp'),
            'subject' => \__('Subject', 'pro-mail-smtp'),
            'status' => \__('Status', 'pro-mail-smtp'),
            'details' => \__('Details', 'pro-mail-smtp'),
            'actions' => \__('Actions', 'pro-mail-smtp')
        ];
    }

    /**
     * Get available providers for filter
     */
    public static function get_providers($providers_list)
    {
        $providers_array = [];
        foreach ($providers_list as $key => $provider) {
            $providers_array[$key] = $provider['label'];
        }
        $providers_array['phpmailer'] = \__('Phpmailer', 'pro-mail-smtp');
        return $providers_array;
    }

    /**
     * Get available statuses
     */
    public static function get_statuses()
    {
        return [
            'sent' => '#3498db',
            'failed' => '#e74c3c',
            'resent' => '#17a2b8'
        ];
    }
}
