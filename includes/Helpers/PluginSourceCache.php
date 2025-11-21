<?php

namespace TurboSMTP\ProMailSMTP\Helpers;
if ( ! defined( 'ABSPATH' ) ) exit;

class PluginSourceCache {
    private static $instance = null;
    private $cached_plugin_name = null;

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setPluginName($name) {
        $this->cached_plugin_name = $name;
    }

    public function getPluginName() {
        return $this->cached_plugin_name;
    }

    public function hasPluginName() {
        return $this->cached_plugin_name !== null;
    }
}
