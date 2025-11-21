<?php

namespace TurboSMTP\ProMailSMTP\Cron;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CronManager - Manages and initializes all cron jobs for Zetema SMTP.
 */
class CronManager {
    private $cron_classes = [];
    private static $instance = null;
    private $cron_instances = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        $this->register_cron_class(LogsCleanup::class);
        $this->register_cron_class(SummaryMail::class);        
        $this->initialize_cron_instances();
    }

    private function initialize_cron_instances() {
        foreach ($this->cron_classes as $class_name) {
            if (class_exists($class_name)) {
                $this->cron_instances[] = new $class_name();
            }
        }
    }

    public function register_cron_class($class_name) {
        if (!in_array($class_name, $this->cron_classes)) {
            $this->cron_classes[] = $class_name;
        }
    }

    public function activate_crons() {
        foreach ($this->cron_instances as $cron) {
            $cron->register();
        }
    }

    public function deactivate_crons() {
        foreach ($this->cron_instances as $cron) {
            $cron->deregister();
        }
    }
}
