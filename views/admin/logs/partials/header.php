<?php
/**
 * Header partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="plugin-header">
    <span class="plugin-logo"></span>
    <h1>
        <span><?php esc_html_e('PRO', 'pro-mail-smtp'); ?></span>
        <?php esc_html_e(' MAIL SMTP', 'pro-mail-smtp'); ?>
    </h1>
</div>

<p class="description">
    <?php esc_html_e('Setup custom SMTP or popular Providers to improve your WordPress email deliverability.', 'pro-mail-smtp'); ?>
</p>

<nav class="pro-mail-smtp-nav-tab-wrapper">
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('Providers', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-logs')); ?>" 
       class="pro-mail-smtp-nav-tab pro-mail-smtp-nav-tab-active">
        <?php esc_html_e('Email Logs', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-analytics')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('Providers Logs', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-email-router')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('Email Router', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-alerts')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('Alerts', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-settings')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('Settings', 'pro-mail-smtp'); ?>
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-about')); ?>" 
       class="pro-mail-smtp-nav-tab">
        <?php esc_html_e('About', 'pro-mail-smtp'); ?>
    </a>
</nav>
