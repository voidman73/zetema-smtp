<?php
/**
 * Retention Settings partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="logs-retention-settings">
    <h2><?php esc_html_e('Logs Retention Settings', 'pro-mail-smtp'); ?></h2>
    <p class="retention-description">
        <?php esc_html_e('Select how long you want to keep your email logs in the database. Logs older than the selected duration will be automatically deleted.', 'pro-mail-smtp'); ?>
    </p>
    
    <form method="post">
        <?php wp_nonce_field('pro_mail_smtp_update_retention', 'pro_mail_smtp_retention_nonce'); ?>
        
        <select name="retention_duration_setting">
            <option value="forever" <?php selected($current_retention, 'forever'); ?>>
                <?php esc_html_e('Forever', 'pro-mail-smtp'); ?>
            </option>
            <option value="1_week" <?php selected($current_retention, '1_week'); ?>>
                <?php esc_html_e('1 Week', 'pro-mail-smtp'); ?>
            </option>
            <option value="1_month" <?php selected($current_retention, '1_month'); ?>>
                <?php esc_html_e('1 Month', 'pro-mail-smtp'); ?>
            </option>
            <option value="1_year" <?php selected($current_retention, '1_year'); ?>>
                <?php esc_html_e('1 Year', 'pro-mail-smtp'); ?>
            </option>
        </select>
        
        <input type="submit" 
               class="button" 
               value="<?php esc_attr_e('Update Retention Setting', 'pro-mail-smtp'); ?>">
    </form>
</div>
