<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class ProviderFactory {

   private $integrated_providers;

     public function __construct() {
        $this->integrated_providers = include __DIR__ . '/../../config/providers-list.php';
    }

    public function get_provider_class($connection) {
        if (!isset($connection->provider) || empty($connection->provider)) {
            throw new \Exception('Provider not found');
        }
        $provider_class = '\\TurboSMTP\\ProMailSMTP\\Providers\\' . $this->integrated_providers[$connection->provider]['class'];
        if (class_exists($provider_class)) {
            return new $provider_class((array)$connection->connection_data);
        }
        throw new \Exception('Provider not found');
    }
}