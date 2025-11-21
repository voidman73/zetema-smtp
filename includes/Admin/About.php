<?php

namespace TurboSMTP\ProMailSMTP\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class About
{
    private $plugin_path;

    public function __construct()
    {
        $this->plugin_path = PRO_MAIL_SMTP_PATH;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook)
    {
        // Only load on about page
        if (strpos($hook, 'pro-mail-smtp-about') === false) {
            return;
        }

        // Enqueue the CSS files
        wp_enqueue_style(
            'pro-mail-smtp-admin-css',
            plugins_url('/assets/css/admin.css', PRO_MAIL_SMTP_FILE),
            [],
            PRO_MAIL_SMTP_VERSION
        );
        
        // Enqueue dashicons
        wp_enqueue_style('dashicons');
        
        // Make sure WordPress loads the media library scripts for image handling
        wp_enqueue_media();
    }

    /**
     * Check if plugin icon files exist, otherwise create placeholders
     *
     * @return array
     */
    private function get_plugin_icons()
    {
        $icons = [
            'turbosmtp' => [
                'path' => '/assets/img/providers/turbosmtp.svg',
                'fallback' => 'dashicons-email-alt'
            ],
            'validator' => [
                'path' => '/assets/img/providers/turbosmtp.svg',
                'fallback' => 'dashicons-yes-alt'
            ]
        ];
        
        foreach ($icons as $key => $icon) {
            $full_path = plugin_dir_path(PRO_MAIL_SMTP_FILE) . $icon['path'];
            $icons[$key]['exists'] = file_exists($full_path);
            $icons[$key]['url'] = plugins_url($icon['path'], PRO_MAIL_SMTP_FILE);
        }
        
        return $icons;
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'pro-mail-smtp'));
        }

        // Get plugin icons to pass to the view
        $plugin_icons = $this->get_plugin_icons();
        
        $view_file = $this->plugin_path . '/views/admin/about/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('About Zetema SMTP', 'pro-mail-smtp') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('Error: View file not found.', 'pro-mail-smtp') . '</p></div>';
            echo '</div>';
        }
    }
}
