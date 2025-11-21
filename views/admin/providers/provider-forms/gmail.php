<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wizard-step">
    
    <h3><?php echo isset($is_edit) && $is_edit ? 'Edit Gmail Configuration' : 'Add Gmail Provider'; ?></h3>
    <p class="description">Enter your Gmail API credentials below.</p>
    <p class="description">Note: Ensure your redirect URL is set to <code><?php echo esc_url(admin_url('admin.php?page=pro-mail-smtp-providers')); ?></code></p>

    <form id="provider-form" method="post">
    <?php wp_nonce_field('pro_mail_smtp_nonce_providers', 'pro_mail_smtp_nonce_providers'); ?>
        
        <input type="hidden" name="provider" id="provider" value="gmail">
        <input type="hidden" name="connection_id" id="connection_id" value="">
        
        <table class="form-table">
        <tr>
                <th scope="row">
                    <label for="connection_label">Connection Label</label>
                </th>
                <td>
                    <input type="text" 
                           name="connection_label" 
                           id="connection_label" 
                           class="regular-text" 
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="email_from_overwrite">Email From</label>
                </th>
                <td>
                    <input type="email"
                        name="config_keys[email_from_overwrite]"
                        id="email_from_overwrite"
                        class="regular-text"
                        >
                        <p class="description">(Optional) Force sender email for this provider</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                <label for="client_id">Client ID</label>
                </th>
                <td>
                    <input type="text"
                        name="config_keys[client_id]"
                        id="client_id"
                        class="regular-text"
                        required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="client_secret">Client Secret</label>
                </th>
                <td>
                    <div class="secret-wrapper">
                        <input type="password"
                            name="config_keys[client_secret]"
                            id="client_secret"
                            class="regular-text"
                            required>
                        <span id="toggle_ssecret" class="dashicons dashicons-visibility"></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="priority">Priority</label>
                </th>
                <td>
                    <input type="number" 
                           name="priority" 
                           id="priority" 
                           class="small-text" 
                           min="1" 
                           value="1"
                           required>
                </td>
            </tr>
        </table>

        <div class="submit-wrapper">
            <?php if (!(isset($is_edit) && $is_edit)): ?>
                <button type="button" class="button back-step">Back</button>
            <?php endif; ?>
            <button type="submit" class="button button-primary save-provider">
                <?php echo isset($is_edit) ? 'Update Provider' : 'Add Provider'; ?>
            </button>
        </div>
    </form>
</div>