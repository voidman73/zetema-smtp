<?php

namespace TurboSMTP\ProMailSMTP\Helpers;
 if ( ! defined( 'ABSPATH' ) ) exit;

class PluginListUpdater {

    /**
     * Updates the option storing the list of active plugins.
     *
     * @return void
     */
    public function updateActivePluginsOption() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins     = [];
        $all_plugins = wp_list_pluck(
            array_intersect_key( get_plugins(), array_flip( (array) get_option( 'active_plugins' ) ) ),
            'Name'
        );

        foreach ( $all_plugins as $plugin_file => $plugin_data ) {
            $plugins[] = [
                'name' => $plugin_data,
                'path' => dirname( $plugin_file ),
            ];
        }

        $mu_plugins = get_mu_plugins();
        foreach ( $mu_plugins as $plugin_file => $plugin_data ) {
            $plugins[] = [
                'name' => $plugin_data,
                'path' => dirname( $plugin_file ),
            ];
        }

        
            $theme = wp_get_theme();
            $theme_name = $theme->get( 'Name' );
            $plugins[] = [
                'name' => 'Theme: ' . $theme_name,
                'path' => $theme_name,
            ];
    

        $plugins[] = [
            'name' => 'Core WP',
            'path' => 'Core WP',
        ];
        update_option( 'pro_mail_smtp_active_plugins_list', $plugins );
    }
}