<?php
namespace TurboSMTP\ProMailSMTP\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;

class Menu {
    private $plugin_path;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_items']);
        $this->plugin_path = PRO_MAIL_SMTP_PATH;
    }

    private function get_svg_icon() {
        $svg_path = $this->plugin_path . '/assets/img/icon-white-svg.svg';
        if (file_exists($svg_path)) {
            return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($svg_path));
        }
        return 'dashicons-email';
    }

    public function add_menu_items() {
        $parent_slug = 'pro-mail-smtp-providers';

        add_menu_page(
            'Zetema SMTP',
            'Zetema SMTP',
            'manage_options',
            $parent_slug,
            [$this, 'render_providers_page'],
            $this->get_svg_icon(),
            30
        );

        $submenu_pages = [
            [
                'title' => 'Providers',
                'menu_title' => 'Providers',
                'capability' => 'manage_options',
                'slug' => $parent_slug,
                'callback' => 'render_providers_page'
            ],
            [
                'title' => 'Email Logs',
                'menu_title' => 'Email Logs',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-logs',
                'callback' => 'render_logs_page'
            ],
            [
                'title' => 'Providers Logs',
                'menu_title' => 'Providers Logs',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-analytics',
                'callback' => 'render_analytics_page'
            ],
            [
                'title' => 'Email Router',
                'menu_title' => 'Email Router',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-email-router',
                'callback' => 'render_email_router_page'
            ],
            [
                'title' => 'Alerts',
                'menu_title' => 'Alerts',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-alerts',
                'callback' => 'render_alerts_page'
            ],
            [
                'title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-settings',
                'callback' => 'render_settings_page'
            ],
            [
                'title' => 'About',
                'menu_title' => 'About',
                'capability' => 'manage_options',
                'slug' => 'pro-mail-smtp-about',
                'callback' => 'render_about_page'
            ]
        ];

        foreach ($submenu_pages as $page) {
            add_submenu_page(
                $parent_slug,
                $page['title'],
                $page['menu_title'],
                $page['capability'],
                $page['slug'],
                [$this, $page['callback']]
            );
        }
    }

    public function render_providers_page() {
        (new Providers())->render();
    }

    public function render_analytics_page() {
        (new Analytics())->render();
    }

    public function render_logs_page() {
        (new Logs())->render();
    }
    public function render_email_router_page() {
        (new EmailRouter())->render();
    }

    public function render_alerts_page() {
        (new Alerts())->render();
    }

    public function render_settings_page() {
        (new Settings())->render();
    }

    public function render_about_page() {
        (new About())->render();
    }
}
