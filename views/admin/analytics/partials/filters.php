<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
$current_month_start = gmdate('Y-m-01');
$current_month_end = gmdate('Y-m-d');
?>
<div class="tablenav top">
    <div class="alignleft actions filters-group">
        <form method="post" id="analytics-filter-form">
            <?php 
            wp_nonce_field('pro_mail_smtp_analytics', 'pro_mail_smtp_analytics_nonce'); 
            ?>
            <input type="hidden" name="filter_action" value="filter_analytics">
            
            <label for="provider-filter">Provider</label>
            <select id="provider-filter" name="provider">
                <?php foreach ($data['providers'] as $provider): ?>
                    <option value="<?php echo esc_attr($provider->connection_id); ?>"
                            <?php selected($data['filters']['selected_provider'], $provider->connection_id); ?>>
                        <?php echo esc_html($provider->connection_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
                        
            <label for="date-from">From Date</label>
            <input type="date" 
                   id="date-from"
                   name="date_from"
                   value="<?php echo esc_attr($data['filters']['date_from'] ?: $current_month_start); ?>" 
                   placeholder="From Date">
                   
            <label for="date-to">To Date</label>
            <input type="date" 
                   id="date-to"
                   name="date_to"
                   value="<?php echo esc_attr($data['filters']['date_to'] ?: $current_month_end); ?>" 
                   placeholder="To Date">
                   
            <label for="per-page">Rows per page</label>
            <input type="number" 
                   id="per-page"
                   name="per_page"
                   value="<?php echo esc_attr($data['filters']['per_page'] ?: 10); ?>" 
                   min="1" 
                   placeholder="Rows per page">
                   
            <input type="hidden" id="current-page-input" name="page" value="<?php echo esc_attr($data['filters']['page'] ?: 1); ?>">
            
            <button type="submit" class="button action apply-filter"><?php esc_html_e('Apply Filters', 'pro-mail-smtp'); ?></button>
        </form>
    </div>
</div>