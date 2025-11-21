<?php

namespace TurboSMTP\ProMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\Helpers\PluginSourceCache;

class WPMailCaller
{
    const UNKNOWN_SOURCE = 'Unknown Source';
    const MUST_USE_PLUGIN = 'Must Use Plugin';
    const CORE_WP = 'Core WP';
    const THEME = 'Theme';

    private $cache;

    public function __construct() {
        $this->cache = PluginSourceCache::getInstance();
    }

    /**
     * Gets the plugin/theme/core identifier for the source file that called wp_mail.
     *
     * @return string The source identifier (plugin slug, theme slug, 'Core WP', etc.).
     */
    public function getSourcePluginName()
    {
        if ($this->cache->hasPluginName()) {
            return $this->cache->getPluginName();
        }

        $source_info = $this->getSourceInfo();
        $source_file = isset($source_info['file']) && is_string($source_info['file']) ? $source_info['file'] : null;

        $plugin_name = $this->determineSourceType($source_file);
        // Cache the result
        $this->cache->setPluginName($plugin_name);
        return $plugin_name;
    }

    /**
     * Determines the type of source (plugin, theme, core, mu-plugin) based on the file path.
     * Uses normalized paths for reliable comparison across different server environments
     * and WordPress configurations.
     *
     * @param string|null $source_file Path to the source file, or null if unknown.
     * @return string Source type identifier (plugin name, 'Core WP', 'Theme: name', etc.)
     */
    private function determineSourceType($source_file)
    {
        if (empty($source_file) || !function_exists('wp_normalize_path')) {
            return self::UNKNOWN_SOURCE;
        }

        $normalized_source_file = wp_normalize_path($source_file);
        $source_type = self::UNKNOWN_SOURCE;

        $mu_plugins_path = defined('WPMU_PLUGIN_DIR') ? trailingslashit(wp_normalize_path(WPMU_PLUGIN_DIR)) : null;

        $plugins_path = defined('WP_PLUGIN_DIR') ? trailingslashit(wp_normalize_path(dirname( PRO_MAIL_SMTP_PATH ))) : null;

        $wp_includes_folder = (defined('ABSPATH') && defined('WPINC')) ? trailingslashit(wp_normalize_path(WPINC)) : null;

        $themes_path = function_exists('get_theme_root') ? trailingslashit(wp_normalize_path(get_theme_root())) : null;
     
        if ($mu_plugins_path && strpos($normalized_source_file, $mu_plugins_path) === 0) {
             $relative_path = str_replace($mu_plugins_path, '', $normalized_source_file);
             $parts = explode('/', $relative_path);
             $mu_plugin_file = !empty($parts[0]) ? $parts[0] : self::MUST_USE_PLUGIN;
             $source_type = self::MUST_USE_PLUGIN; 
        }
        elseif ($plugins_path && strpos($normalized_source_file, $plugins_path) === 0) {
            $source_type = $this->extractPluginFolderName($normalized_source_file, $plugins_path);
        }
        elseif ($wp_includes_folder && strpos($normalized_source_file, $wp_includes_folder) === 0) {
            $source_type = self::CORE_WP;
        }
        elseif ($themes_path && strpos($normalized_source_file, $themes_path) === 0) {
            $source_type = $this->extractThemeFolderName($normalized_source_file, $themes_path);
        }
      
        return $source_type;
    }

    /**
     * Extracts plugin folder name from a file path.
     * Assumes paths are normalized and $normalized_plugins_path has a trailing slash.
     *
     * @param string $normalized_source_file The normalized source file path.
     * @param string $normalized_plugins_path The normalized plugins directory path with trailing slash.
     * @return string Plugin folder name or UNKNOWN_SOURCE.
     */
    private function extractPluginFolderName($normalized_source_file, $normalized_plugins_path)
    {
        $relative_path = str_replace($normalized_plugins_path, '', $normalized_source_file);
        $parts = explode('/', $relative_path);
        return !empty($parts[0]) ? $parts[0] : self::UNKNOWN_SOURCE;
    }

     /**
     * Extracts theme folder name from a file path.
     * Assumes paths are normalized and $normalized_themes_path has a trailing slash.
     *
     * @param string $normalized_source_file The normalized source file path.
     * @param string $normalized_themes_path The normalized themes directory path with trailing slash.
     * @return string Formatted theme identifier (e.g., "Theme: twentytwentyfour") or 'Theme' constant.
     */
    private function extractThemeFolderName($normalized_source_file, $normalized_themes_path)
    {
        $relative_path = str_replace($normalized_themes_path, '', $normalized_source_file);
        $parts = explode('/', $relative_path);
        $theme_slug = !empty($parts[0]) ? $parts[0] : null;

        return $theme_slug ? (self::THEME . ': ' . $theme_slug) : self::THEME;
    }


    /**
     * Gets the source file and line number of the wp_mail call.
     * Traverses the backtrace to find the call originating outside of wp-includes.
     *
     * @return array An array containing 'file' and 'line' (both can be null).
     */
    private function getSourceInfo()
    {
        $result = ['file' => null, 'line' => null]; 
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 150); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

        $wp_mail_call_index = -1;
        foreach ($backtrace as $index => $item) {
            if (isset($item['function']) && $item['function'] === 'wp_mail') {
                 $wp_mail_call_index = $index;
                 break;
            }
        }

      if ($wp_mail_call_index !== -1) {
            for ($i = $wp_mail_call_index + 1; $i < count($backtrace); $i++) {
                $caller_item = $backtrace[$i];
                if (isset($caller_item['file'])) {
                   if (strpos($caller_item['file'], 'wp-includes') !== false && isset($backtrace[$i + 1])) {
                        continue; 
                    }
                    $result = [
                        'file' => $caller_item['file'],
                        'line' => isset($caller_item['line']) ? $caller_item['line'] : null,
                    ];
                    break;
                }
            }
        }

        return $result;
    }
}