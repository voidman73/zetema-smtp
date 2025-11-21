<?php
/**
 * Filters partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tablenav top">
    <form method="post" class="email-filters">
        <input type="hidden" name="page" value="pro_mail_smtp-logs">
        <input type="hidden" name="filter_action" value="filter_logs">
        <input type="hidden" name="paged" value="<?php echo isset($filters['paged']) ? absint($filters['paged']) : 1; ?>">
        <input type="hidden" name="orderby" value="<?php echo esc_attr($filters['orderby']); ?>">
        <input type="hidden" name="order" value="<?php echo esc_attr($filters['order']); ?>">
        <?php wp_nonce_field('pro_mail_smtp_logs_filter', 'pro_mail_smtp_logs_filter_nonce'); ?>
        
        <div class="alignleft actions filters">
            <!-- Provider Filter -->
            <select name="provider" class="provider-filter">
                <option value=""><?php esc_html_e('All Providers', 'pro-mail-smtp'); ?></option>
                <?php foreach ($providers as $key => $provider): ?>
                    <option value="<?php echo esc_attr($key); ?>"
                        <?php selected(esc_attr($filters['provider']), $key); ?>>
                        <?php echo esc_html($provider); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Status Filter -->
            <select name="status" class="status-filter">
                <option value=""><?php esc_html_e('All Statuses', 'pro-mail-smtp'); ?></option>
                <?php foreach (array_keys($statuses) as $status): ?>
                    <option value="<?php echo esc_attr($status); ?>"
                        <?php selected(esc_attr($filters['status']), $status); ?>>
                        <?php echo esc_html(ucfirst($status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Date From -->
            <input type="date"
                   name="date_from"
                   value="<?php echo esc_attr($filters['date_from']); ?>"
                   class="date-picker"
                   placeholder="<?php esc_attr_e('From Date', 'pro-mail-smtp'); ?>">

            <!-- Date To -->
            <input type="date"
                   name="date_to"
                   value="<?php echo esc_attr($filters['date_to']); ?>"
                   class="date-picker"
                   placeholder="<?php esc_attr_e('To Date', 'pro-mail-smtp'); ?>">

            <!-- Search -->
            <input type="search"
                   name="search"
                   value="<?php echo esc_attr($filters['search']); ?>"
                   class="search-input"
                   placeholder="<?php esc_attr_e('Search emails...', 'pro-mail-smtp'); ?>">

            <!-- Filter Button -->
            <input type="submit"
                   class="button apply-filter"
                   value="<?php esc_attr_e('Filter', 'pro-mail-smtp'); ?>">
                   
            <!-- Reset Button -->
            <button type="button" class="button reset-filter">
                <?php esc_html_e('Reset Filters', 'pro-mail-smtp'); ?>
            </button>
        </div>

        <div class="alignright">
            <span class="displaying-num">
                <?php 
                echo esc_html(sprintf(
                    /* translators: %s: number of items */
                    _n('%s item', '%s items', $total_items, 'pro-mail-smtp'),
                    number_format_i18n($total_items)
                )); ?>
            </span>
        </div>
    </form>
</div>
