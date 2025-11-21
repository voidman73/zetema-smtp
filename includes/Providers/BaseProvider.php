<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class BaseProvider {
    protected $config_keys;
    
    public function __construct($config_keys) {
        $this->config_keys = $config_keys;
    }
    
    abstract public function send($data);
    
    protected function request($endpoint, $data = [], $override_base_api_url = false, $method = 'POST', $is_form_data = false, $headers = []) {
        $args = [
            'method' => $method,
            'headers' => !empty($headers) ? $headers : $this->get_headers(),
            'timeout' => 30,
        ];
        
        if ($method === 'GET' && !empty($data)) {
            $endpoint .= '?' . http_build_query($data);
        } else if ($method === 'POST' && !empty($data)) {
            if ($is_form_data) {
                $args['body'] = $data;
            } else {
                $args['body'] = json_encode($data);
            }
        }
        if($override_base_api_url){
            $response = wp_remote_request($endpoint, $args);
        }else{
            $response = wp_remote_request($this->get_api_url() . $endpoint, $args);
        }
        if (is_wp_error($response)) {

            throw new \Exception(esc_html($response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code < 200 || $code >= 300) {
            throw new \Exception(esc_html($this->get_error_message($body, $code)));
        }
        return json_decode($body, true);
    }
    
    abstract protected function get_api_url();
    
    abstract protected function get_headers();
    
    abstract protected function get_error_message($body, $code);

    abstract public function test_connection();

    abstract public function get_analytics($filters = []);
}
