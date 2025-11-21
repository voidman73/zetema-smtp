<?php
/**
 * Pagination partial for Email Logs
 */

if (!defined('ABSPATH')) {
    exit;
}

if ($total_pages <= 1) {
    return;
}

$current_page = $filters['paged'];
?>

<div class="tablenav bottom">
    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php 
            echo esc_html(sprintf(
                /* translators: %s: number of items */
                _n('%s item', '%s items', $total_items, 'pro-mail-smtp'),
                number_format_i18n($total_items)
            )); ?>
        </span>
        
        <span class="pagination-links">
            <?php 
            $first_page_disabled = $current_page <= 1 ? 'disabled' : '';
            $prev_page = max(1, $current_page - 1);
            $prev_page_disabled = $current_page <= 1 ? 'disabled' : '';
            $next_page = min($total_pages, $current_page + 1);
            $next_page_disabled = $current_page >= $total_pages ? 'disabled' : '';
            $last_page_disabled = $current_page >= $total_pages ? 'disabled' : '';
            ?>
            
            <button type="button" 
                    class="first-page button pagination-button <?php echo esc_attr($first_page_disabled); ?>" 
                    data-page="1" 
                    aria-label="<?php esc_attr_e('Go to the first page', 'pro-mail-smtp'); ?>">
                &laquo;
            </button>
            
            <button type="button" 
                    class="prev-page button pagination-button <?php echo esc_attr($prev_page_disabled); ?>" 
                    data-page="<?php echo esc_attr($prev_page); ?>" 
                    aria-label="<?php esc_attr_e('Go to the previous page', 'pro-mail-smtp'); ?>">
                &lsaquo;
            </button>
            
            <span class="paging-input">
                <span class="tablenav-paging-text">
                    <?php echo absint($current_page); ?> 
                    <?php esc_html_e('of', 'pro-mail-smtp'); ?> 
                    <span class="total-pages"><?php echo absint($total_pages); ?></span>
                </span>
            </span>
            
            <button type="button" 
                    class="next-page button pagination-button <?php echo esc_attr($next_page_disabled); ?>" 
                    data-page="<?php echo esc_attr($next_page); ?>" 
                    aria-label="<?php esc_attr_e('Go to the next page', 'pro-mail-smtp'); ?>">
                &rsaquo;
            </button>
            
            <button type="button" 
                    class="last-page button pagination-button <?php echo esc_attr($last_page_disabled); ?>" 
                    data-page="<?php echo esc_attr($total_pages); ?>" 
                    aria-label="<?php esc_attr_e('Go to the last page', 'pro-mail-smtp'); ?>">
                &raquo;
            </button>
        </span>
    </div>
</div>
