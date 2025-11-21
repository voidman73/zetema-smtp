<?php

namespace TurboSMTP\ProMailSMTP\Cron;
if ( ! defined( 'ABSPATH' ) ) exit;

class SummaryMail implements CronInterface
{
    private $hook = 'pro_mail_smtp_summary_cron';
    private $interval;

    public function __construct()
    {
        $frequency = get_option('pro_mail_smtp_summary_frequency', 'weekly');
        $this->interval = $frequency === 'weekly' ? 'weekly' : 'pro_mail_smtp_monthly';
        add_action($this->hook, [$this, 'handle']);
        add_action('init', [$this, 'maybe_toggle_schedule']);
        add_filter('cron_schedules', [$this, 'add_monthly_interval']);
    }

    public function add_monthly_interval($schedules)
    {
        $schedules['pro_mail_smtp_monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('Once Monthly', 'pro-mail-smtp')
        ];
        return $schedules;
    }

    public function register()
    {
        if (!$this->is_scheduled()) {
            wp_clear_scheduled_hook($this->hook);
            wp_schedule_event(time(), $this->interval, $this->hook);
        }
    }

    public function deregister()
    {
        $timestamp = wp_next_scheduled($this->hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook);
        }
    }

    public function is_scheduled()
    {
        return (bool)wp_next_scheduled($this->hook);
    }

    public function get_interval()
    {
        return $this->interval;
    }

    public function get_hook()
    {
        return $this->hook;
    }

    public function maybe_toggle_schedule()
    {
        $enable_summary = get_option('pro_mail_smtp_enable_summary', 0);
        if ($enable_summary) {
            $this->register();
        } else {
            $this->deregister();
        }
    }

    public function handle()
    {
        if (!get_option('pro_mail_smtp_enable_summary', 0)) {
            $this->deregister();
            return;
        }
        $logs = $this->get_logs_since_last_summary();
        $admin_email = get_option('pro_mail_smtp_summary_email', get_option('admin_email'));

        $subject = sprintf('Email Log Summary - %s', get_bloginfo('name'));
        $message = $this->prepare_summary_message($logs);
        wp_mail($admin_email, $subject, $message);
    }

    private function get_logs_since_last_summary()
    {
        global $wpdb;

        $frequency = get_option('pro_mail_smtp_summary_frequency', 'weekly');
        if ($frequency === 'weekly') {
            $date_range = '-7 days';
        } else {
            $date_range = '-30 days';
        }

        $since_date = gmdate('Y-m-d H:i:s', strtotime($date_range));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                provider, 
                status, 
                COUNT(*) as count 
             FROM $wpdb->prefix" . "pro_mail_smtp_email_log 
             WHERE sent_at >= %s 
             GROUP BY provider, status",
            $since_date
        ), OBJECT);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $totals = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                status, 
                COUNT(*) as count 
             FROM $wpdb->prefix" . "pro_mail_smtp_email_log 
             WHERE sent_at >= %s 
             GROUP BY status",
            $since_date
        ), OBJECT);

        return [
            'by_provider' => $results,
            'totals' => $totals,
            'period' => $frequency,
            'since_date' => $since_date
        ];
    }

    private function prepare_summary_message($logs)
    {
        $period_text = ($logs['period'] === 'weekly') ? 'Week' : 'Month';

        $message = "Zetema SMTP Plugin: Email Summary for the Past {$period_text}\n";
        $message .= "Period: " . gmdate('Y-m-d', strtotime($logs['since_date'])) . " to " . gmdate('Y-m-d') . "\n\n";

        $providers = [];
        foreach ($logs['by_provider'] as $entry) {
            if (!isset($providers[$entry->provider])) {
                $providers[$entry->provider] = [
                    'success' => 0,
                    'failed' => 0
                ];
            }

            if ($entry->status === 'success') {
                $providers[$entry->provider]['success'] = $entry->count;
            } else {
                $providers[$entry->provider]['failed'] = $entry->count;
            }
        }

        $message .= "=== Provider Statistics ===\n\n";

        foreach ($providers as $provider => $stats) {
            $total = $stats['success'] + $stats['failed'];
            $success_rate = ($total > 0) ? round(($stats['success'] / $total) * 100, 1) : 0;

            $message .= "Provider: {$provider}\n";
            $message .= "- Successful: {$stats['success']}\n";
            $message .= "- Failed: {$stats['failed']}\n";
            $message .= "- Total: {$total}\n";
            $message .= "- Success Rate: {$success_rate}%\n\n";
        }

        $total_success = 0;
        $total_failed = 0;

        foreach ($logs['totals'] as $entry) {
            if ($entry->status === 'success') {
                $total_success = $entry->count;
            } else {
                $total_failed = $entry->count;
            }
        }

        $grand_total = $total_success + $total_failed;
        $overall_success_rate = ($grand_total > 0) ? round(($total_success / $grand_total) * 100, 1) : 0;

        $message .= "=== Overall Statistics ===\n\n";
        $message .= "Total Emails Sent: {$grand_total}\n";
        $message .= "Successful: {$total_success}\n";
        $message .= "Failed: {$total_failed}\n";
        $message .= "Overall Success Rate: {$overall_success_rate}%\n";
        return $message;
    }
}
