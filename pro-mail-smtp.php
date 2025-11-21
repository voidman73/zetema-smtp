<?php
/**
 * Plugin Name: Zetema SMTP
 * Description: Enhance email deliverability by connecting WordPress to SMTP providers with automatic failover, proactive alerts, advanced analytics, and intelligent routing.
 * Version: 1.6.2
 * Author: turbosmtp
 * Author URI:        https://www.serversmtp.com
 * Text Domain: pro-mail-smtp
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 6.3
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PRO_MAIL_SMTP_VERSION', '1.6.1');
define('PRO_MAIL_SMTP_FILE', __FILE__);
define('PRO_MAIL_SMTP_PATH', plugin_dir_path(__FILE__));
define('PRO_MAIL_SMTP_URL', plugin_dir_url(__FILE__));

function pro_mail_smtp_load_textdomain() {
    load_plugin_textdomain(
        'pro-mail-smtp',
        false,
        dirname(plugin_basename(PRO_MAIL_SMTP_FILE)) . '/languages/'
    );
}
add_action('init', 'pro_mail_smtp_load_textdomain');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'TurboSMTP\ProMailSMTP\\';
    $base_dir = PRO_MAIL_SMTP_PATH . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Initialize the main plugin components.
 *
 * @since 1.0.0
 */
function pro_mail_smtp_init() {
    if (class_exists('TurboSMTP\ProMailSMTP\Core\Plugin')) {
        $plugin = new TurboSMTP\ProMailSMTP\Core\Plugin();
        $plugin->init();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Zetema SMTP Error: Core plugin class not found. Please ensure the plugin files are intact.', 'pro-mail-smtp');
            echo '</p></div>';
        });
    }
}
add_action('init', 'pro_mail_smtp_init', 11);


register_activation_hook(__FILE__, function() {
    if (!class_exists('TurboSMTP\ProMailSMTP\Core\Installer')) {
         $installer_file = PRO_MAIL_SMTP_PATH . 'includes/Core/Installer.php';
         if (file_exists($installer_file)) {
             require_once $installer_file;
         }
    }
    if (class_exists('TurboSMTP\ProMailSMTP\Core\Installer')) {
        $installer = new TurboSMTP\ProMailSMTP\Core\Installer();
        $installer->install();
    }
});

register_deactivation_hook(__FILE__, function() {
    if (!class_exists('TurboSMTP\ProMailSMTP\Cron\CronManager')) {
         $cron_manager_file = PRO_MAIL_SMTP_PATH . 'includes/Cron/CronManager.php';
         if (file_exists($cron_manager_file)) {
             require_once $cron_manager_file;
         }
    }
    if (class_exists('TurboSMTP\ProMailSMTP\Cron\CronManager') && method_exists('TurboSMTP\ProMailSMTP\Cron\CronManager', 'get_instance')) {
        \TurboSMTP\ProMailSMTP\Cron\CronManager::get_instance()->deactivate_crons();
    }
});
