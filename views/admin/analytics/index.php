<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <div class="plugin-header">
    <span class="plugin-logo"></span>
    <h1><span><?php esc_html_e('PRO', 'pro-mail-smtp'); ?> </span><?php esc_html_e(' MAIL SMTP', 'pro-mail-smtp'); ?></h1>    </div>
    
    <p class="description"><?php esc_html_e('Setup custom SMTP or popular Providers to improve your WordPress email deliverability.', 'pro-mail-smtp'); ?></p>

    <nav class="pro-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Providers', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-logs')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-analytics')); ?>" class="pro-mail-smtp-nav-tab pro-mail-smtp-nav-tab-active"><?php esc_html_e('Providers Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-email-router')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Email Router', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-alerts')); ?>" class="pro-mail-smtp-nav-tab">Alerts</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-settings')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Settings', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-about')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('About', 'pro-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('pro_mail_smtp_messages'); ?>
    <!-- Filters Section -->
    <?php 
    $filters_file = PRO_MAIL_SMTP_PATH . 'views/admin/analytics/partials/filters.php'; 
    if (file_exists($filters_file)) {
        include $filters_file;
    }
    ?>
    
    <!-- GDPR Compliance Banner for TurboSMTP EU (always included, controlled by JavaScript) -->
    <?php 
    $show_banner_initially = false;
    if (isset($data['selected_provider_config']) && $data['selected_provider_config']) {
        if ($data['selected_provider_config']->provider_class === 'TurboSMTP') {
            $config_keys = $data['selected_provider_config']->connection_data;
            if (isset($config_keys['region']) && $config_keys['region'] === 'eu') {
                $show_banner_initially = true;
            }
        }
    }
    ?>
    <div class="turbo-smtp-gdpr-banner" <?php echo $show_banner_initially ? 'style="display: block;"' : ''; ?>>
        <div class="gdpr-banner-content">
            <div class="gdpr-banner-icon">
                <span class="dashicons dashicons-shield-alt"></span>
            </div>
            <div class="gdpr-banner-text">
                <h3><?php esc_html_e('GDPR Compliant Email Service', 'pro-mail-smtp'); ?></h3>
                <p><?php esc_html_e('TurboSMTP EU is 100% GDPR compliant, ensuring your email data is processed according to European data protection regulations.', 'pro-mail-smtp'); ?></p>
            </div>
            <div class="gdpr-banner-badge">
                <span class="gdpr-badge">100% GDPR</span>
            </div>
        </div>
    </div>
    
    <!-- Analytics Table -->
    <?php 
    $table_file = PRO_MAIL_SMTP_PATH . 'views/admin/analytics/partials/table.php';
    if (file_exists($table_file)) {
        include $table_file;
    }
    ?>

</div>

<div id="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>