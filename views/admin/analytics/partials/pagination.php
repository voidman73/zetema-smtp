<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="tablenav bottom">
    <div class="tablenav-pages">
        <span class="displaying-num" id="items-count">
        </span>

        <span class="pagination-links">
            <button type="button" id="prev-page" class="prev-page button pagination-form-submit" disabled>
                <span aria-hidden="true">‹</span>
                <span class="screen-reader-text"><?php esc_html_e('Previous page', 'pro-mail-smtp'); ?></span>
            </button>
            
            <span class="paging-input">
                <span id="current-page">1</span>
            </span>
            
            <button type="button" id="next-page" class="next-page button pagination-form-submit">
                <span aria-hidden="true">›</span>
                <span class="screen-reader-text"><?php esc_html_e('Next page', 'pro-mail-smtp'); ?></span>
            </button>
        </span>
    </div>
</div>

<form id="pagination-form" method="post" style="display: none;">
    <?php wp_nonce_field('pro_mail_smtp_analytics', 'pro_mail_smtp_analytics_nonce'); ?>
    <input type="hidden" name="filter_action" value="filter_analytics">
    <input type="hidden" name="page" id="pagination-page-input" value="1">
    <input type="hidden" name="provider" id="pagination-provider-input" value="">
    <input type="hidden" name="status" id="pagination-status-input" value="">
    <input type="hidden" name="date_from" id="pagination-date-from-input" value="">
    <input type="hidden" name="date_to" id="pagination-date-to-input" value="">
    <input type="hidden" name="per_page" id="pagination-per-page-input" value="">
</form>