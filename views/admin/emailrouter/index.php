<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <div class="plugin-header">
    <span class="plugin-logo"></span>
    <h1><span><?php esc_html_e('PRO', 'pro-mail-smtp'); ?> </span><?php esc_html_e(' MAIL SMTP', 'pro-mail-smtp'); ?></h1>    </div>

    <p class="description"><?php esc_html_e('Setup custom SMTP or popular Providers to improve your WordPress email deliverability.', 'pro-mail-smtp'); ?></p>

    <nav class="pro-mail-smtp-nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Providers', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-logs')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Email Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-analytics')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Providers Logs', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-email-router')); ?>" class="pro-mail-smtp-nav-tab pro-mail-smtp-nav-tab-active"><?php esc_html_e('Email Router', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-alerts')); ?>" class="pro-mail-smtp-nav-tab">Alerts</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-settings')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('Settings', 'pro-mail-smtp'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-about')); ?>" class="pro-mail-smtp-nav-tab"><?php esc_html_e('About', 'pro-mail-smtp'); ?></a>
    </nav>

    <?php settings_errors('pro_mail_smtp_messages'); ?>

    <div class="tabset-content">
        <div class="table-header">
            <a href="#" class="page-title-action add-router-condition">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Router Condition', 'pro-mail-smtp'); ?>
            </a>
        </div>

        <div class="providers-table-wrapper">
            <table class="widefat fixed providers-table">
                <thead>
                    <tr>
                        <th class="column-label"><?php esc_html_e('Label', 'pro-mail-smtp'); ?></th>
                        <th class="column-provider"><?php esc_html_e('Enabled', 'pro-mail-smtp'); ?></th>
                        <th class="column-actions"><?php esc_html_e('Actions', 'pro-mail-smtp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($conditions_list)): ?>
                        <tr class="no-items">
                            <td colspan="5" class="empty-state">
                                <span class="empty-state-icon"></span>
                                <p><?php esc_html_e('It seems you haven\'t added any routing condition yet. Get started now.', 'pro-mail-smtp'); ?></p>
                                <button type="button" class="button button-primary save-condition" id="add-router-condition-button">
                                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Router Condition', 'pro-mail-smtp'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($conditions_list as $condition): ?>
                            <tr>
                                <td class="column-label">
                                    <strong><?php echo esc_html($condition->condition_label); ?></strong>
                                </td>
                                <td class="column-provider">
                                    <div class="toggle-container">
                                        <label class="toggle-switch">
                                            <input type="checkbox" class="toggle-is-enabled" data-id="<?php echo esc_attr($condition->id); ?>" <?php checked($condition->is_enabled, 1); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </td>
                                <td class="column-actions">
                                    <button type="button" class="button button-primary edit-condition" data-id="<?php echo esc_attr($condition->id); ?>">
                                        <span class="dashicons dashicons-edit"></span> <?php esc_html_e('Edit', 'pro-mail-smtp'); ?>
                                    </button>
                                    <button type="button" class="button button-secondary delete-condition" data-id="<?php echo esc_attr($condition->id); ?>">
                                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'pro-mail-smtp'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="router-modal" class="modal" style="display:none;">
            <div class="conditions-modal-content">
                <div class="conditions-modal-header">
                    <h2><?php esc_html_e('Configure Router Condition', 'pro-mail-smtp'); ?></h2>
                    <button type="button" onclick="ProMailSMTPRouter.closeModal(false)" class="conditions-modal-close">&times;</button>
                </div>
                <div class="conditions-modal-body">
                    <?php
                    $modal = PRO_MAIL_SMTP_PATH . '/views/admin/emailrouter/partials/modal.php';
                    if (file_exists($modal)) {
                        include $modal;
                    }
                    ?>
            </div>
            <div class="conditions-modal-footer">
                <div class="conditions-modal-footer-buttons">
                    <button type="button" class="btn btn-secondary" onclick="ProMailSMTPRouter.closeModal(false)"><?php esc_html_e('Close', 'pro-mail-smtp'); ?></button>
                    <button type="button" class="btn btn-primary save-condition" onclick="ProMailSMTPRouter.saveRouter()"><?php esc_html_e('Save', 'pro-mail-smtp'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>