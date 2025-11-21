<?php
/**
 * Logs Table partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>        <?php foreach ($columns as $key => $label): ?>
            <th scope="col"
                class="manage-column column-<?php echo esc_attr($key); ?> <?php echo $key !== 'actions' ? esc_attr(call_user_func($data['get_column_sort_class'], $key, $filters)) : ''; ?>">
                <?php if ($key !== 'actions'): ?>
                    <a href="#" class="sort-column" data-column="<?php echo esc_attr($key); ?>">
                        <span><?php echo esc_html($label); ?></span>
                    </a>
                <?php else: ?>
                    <span><?php echo esc_html($label); ?></span>
                <?php endif; ?>
            </th>
        <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($logs)): ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="<?php echo count($columns); ?>">
                    <?php esc_html_e('No logs found.', 'pro-mail-smtp'); ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="column-date">
                        <?php echo esc_html(call_user_func($data['format_date'], $log->sent_at)); ?><br>
                        <small><?php echo esc_html(call_user_func($data['time_diff'], $log->sent_at)); ?></small>
                    </td>
                    <td class="column-provider">
                        <span class="provider-badge provider-<?php echo esc_attr($log->provider); ?>">
                            <?php echo esc_html(ucfirst($log->provider)); ?>
                        </span>
                    </td>
                    <td class="column-to">
                        <?php echo esc_html($log->to_email); ?>
                    </td>
                    <td class="column-subject">
                        <?php echo esc_html($log->subject); ?>
                    </td>
                    <td class="column-status">
                        <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                            <?php echo esc_html(ucfirst($log->status)); ?>
                        </span>
                        <?php if (isset($log->is_resent) && $log->is_resent == 1): ?>
                            <br><small class="resent-indicator">
                                <span class="dashicons dashicons-email-alt"></span>
                                <?php esc_html_e('Resent', 'pro-mail-smtp'); ?>
                            </small>
                        <?php endif; ?>
                        <?php if (isset($log->retry_count) && $log->retry_count > 0): ?>
                            <br><small class="retry-count">
                                <span class="dashicons dashicons-update"></span>
                                <?php printf(esc_html__('Retries: %d', 'pro-mail-smtp'), (int)$log->retry_count); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td class="column-details">
                        <?php echo esc_html($log->error_message); ?>
                    </td>
                    <td class="column-actions">
                        <div class="action-buttons">
                            <button type="button" 
                                    class="button button-secondary action-btn view-btn" 
                                    data-log-id="<?php echo esc_attr($log->id); ?>"
                                    title="<?php esc_attr_e('View email details', 'pro-mail-smtp'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e('View', 'pro-mail-smtp'); ?>
                            </button>
                            
                            <?php if ($log->status === 'failed'): ?>
                                <button type="button" 
                                        class="button button-primary action-btn resend-btn" 
                                        data-log-id="<?php echo esc_attr($log->id); ?>"
                                        title="<?php esc_attr_e('Resend failed email', 'pro-mail-smtp'); ?>">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <?php esc_html_e('Resend', 'pro-mail-smtp'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="button button-secondary action-btn resend-btn" 
                                        disabled
                                        title="<?php esc_attr_e('Only failed emails can be resent', 'pro-mail-smtp'); ?>">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <?php esc_html_e('Resend', 'pro-mail-smtp'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>

    <tfoot>
        <tr>
            <?php foreach ($columns as $key => $label): ?>
                <th scope="col" class="manage-column column-<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($label); ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>
