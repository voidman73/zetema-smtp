<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Sparkpost extends BaseProvider {
    public function __construct($config_keys) {
        if (!isset($config_keys['region']) || empty($config_keys['region'])) {
            $config_keys['region'] = 'eu';
        }
        parent::__construct($config_keys);
    }
    
    protected function get_api_url()
    {
        $region = isset($this->config_keys['region']) ? $this->config_keys['region'] : 'us';
        if ($region === 'eu') {
            return 'https://api.eu.sparkpost.com/api/v1/';
        }
        return 'https://api.sparkpost.com/api/v1/';
    }
    
    protected function get_headers() {
        return [
            'Authorization' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = 'transmissions';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];
        $payload = [
            'content' => [
                'from' => $email_from,
                'subject' => $data['subject'],
                'html' => $data['message'],
            ],
            'recipients' => array_map(function($recipient) {
                return ['address' => ['email' => $recipient]];
            }, $data['to'])
        ];

        if (!empty($data['cc'])) {
                $payload['header']['CC'] = implode(",", $data['cc']);
        }

        if (!empty($data['bcc'])) {
            foreach ($data['bcc'] as $bcc) {
                $payload['recipients'][] = ['address' => ['email' => $bcc, 'header_to' => $data['to'][0]]];
            }
        }

        if (!empty($data['attachments'])) {
            $payload['content']['attachments'] = array_map(function($attachment) {
                return [
                    'name' => $attachment['name'],
                    'type' => $attachment['type'],
                    'data' => $attachment['content'],
                ];
            }, $data['attachments']);
        }

        $response = $this->request($endpoint, $payload, false, 'POST');
        return [
            'message_id' => 'Sparkpost' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Sparkpost API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Sparkpost API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'account';
        try {
            $response = $this->request($endpoint, [], false, 'GET');
            return [
                'message_id' => 'Sparkpost__' . uniqid(),
                'provider_response' => $response
            ];
        } catch (\Exception $e) {
            if (isset($this->config_keys['region']) && $this->config_keys['region'] === 'eu') {
                $this->config_keys['region'] = 'us';
                $response = $this->request($endpoint, [], false, 'GET');
                return [
                    'message_id' => 'Sparkpost__' . uniqid(),
                    'provider_response' => $response
                ];
            }
            throw $e;
        }
    }

    public function get_analytics($filters = []) {
        $endpoint = 'events/message';
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $response = $this->request($endpoint, [
            'from'     => $filters['date_from'],
            'end_date' => gmdate('Y-m-d', strtotime($filters['date_to'] . ' +1 day')),
            'page'     => $page,
            'per_page' => $per_page
        ], false ,'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['results'] as $data) {
            $formatted_data[] = [
                'id' => $data['transmission_id'],
                'subject' => $data['subject'],
                'sender' => $data['friendly_from'],
                'recipient' => json_encode($data['rcpt_to']),
                'send_time' => $data['timestamp'],
                'status' => $data['type']
            ];
        }
        
        return $formatted_data;
    }

    private function analytics_table_columns(){
        return [
            'id', 'subject', 'sender', 'recipient', 'send_time', 'status'
        ];
    }
}