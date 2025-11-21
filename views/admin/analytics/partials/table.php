<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<table class="wp-list-table widefat fixed striped analytics-table" id="analytics-table">
    <thead>
        <tr>
            <th scope="col"><?php esc_html_e('ID', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Subject', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Sender', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Recipient', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Send Time', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Status', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Domain', 'pro-mail-smtp'); ?></th>
            <th scope="col"><?php esc_html_e('Provider Message', 'pro-mail-smtp'); ?></th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td colspan="8"><?php esc_html_e('No data found.', 'pro-mail-smtp'); ?></td>
            </tr>
    </tbody>
</table>
<div id="pagination">
    <button id="prev-page">Prev</button>
    <span id="current-page">1</span>
    <button id="next-page">Next</button>
</div>