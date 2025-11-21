<?php
/**
 * Uninstall script for Zetema SMTP
 *
 * This file runs when the plugin is deleted from the WordPress admin.
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// --- Database Table Deletion ---
global $wpdb;
$tables_to_drop = [
    $wpdb->prefix . 'pro_mail_smtp_email_log',
    $wpdb->prefix . 'pro_mail_smtp_email_router_conditions',
    $wpdb->prefix . 'pro_mail_smtp_connections',
];

foreach ($tables_to_drop as $table_name) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $table_name));
}

$options_to_delete = [
    'pro_mail_smtp_db_version',
    'pro_mail_smtp_from_email',
    'pro_mail_smtp_from_name',
    'pro_mail_smtp_enable_summary',
    'pro_mail_smtp_summary_email',
    'pro_mail_smtp_summary_frequency',
    'pro_mail_smtp_fallback_to_wp_mail',
    'pro_mail_smtp_gmail_access_token',
    'pro_mail_smtp_gmail_refresh_token',
    'pro_mail_smtp_outlook_refresh_token',
    'pro_mail_smtp_outlook_access_token',
    'pro_mail_smtp_import_easysmtp_notice_dismissed',
    'pro_mail_smtp_import_wpmail_notice_dismissed',
    'pro_mail_smtp_retention_duration'
];

foreach ($options_to_delete as $option_name) {
    delete_option($option_name);
}

$cron_hooks = [
    'pro_mail_smtp_summary_cron',
    'pro_mail_smtp_log_cleanup_cron',
    
];

foreach ($cron_hooks as $hook) {
    wp_clear_scheduled_hook($hook);
}
