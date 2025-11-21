<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Brevo extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.brevo.com/v3/smtp/';
    }
    
    protected function get_headers() {
        return [
            'api-key' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = 'email';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];

        $payload = [
            'sender' => [
                'email' => $email_from,
                'name' => $data['from_name']
            ],
            'to' => array_map(function($recipient) {
                return [
                    'email' => $recipient
                ];
            }, $data['to']),
            'subject' => $data['subject'],
            'htmlContent' => $data['message'],
            'replyTo' => [
                'email' => $email_from,
                'name' => $data['from_name']
            ],
        ];

        // Add cc if any
        if (!empty($data['cc'])) {
            $payload['cc'] = array_map(function($recipient) {
                return [
                    'email' => $recipient
                ];
            }, $data['cc']);
        }

        // Add bcc if any
        if (!empty($data['bcc'])) {
            $payload['bcc'] = array_map(function($recipient) {
                return [
                    'email' => $recipient
                ];
            }, $data['bcc']);
        }

        // Add attachments if any
        if (!empty($data['attachments'])) {
            $payload['attachment'] = array_map(function($attachment) {
                return [
                    'name' => $attachment['name'],
                    'content' => $attachment['content'],
                ];
            }, $data['attachments']);
        }

        $response = $this->request($endpoint, $payload, false, 'POST');
        
        return [
            'message_id' => 'Brevo__' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Brevo API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Brevo API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'https://api.brevo.com/v3/account';
        $response = $this->request($endpoint, [], true,'GET');

        if (isset($response['error'])) {
            throw new \Exception(esc_html($response['error']['message']));
        }
        
        return $response;
    }
    public function get_analytics($filters = []) {
        $endpoint = 'statistics/events';
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $response = $this->request($endpoint, array_merge([
            'startDate' => $filters['date_from'],
            'endDate'   => $filters['date_to']
        ], [
            'page' => $page,
            'per_page' => $per_page
        ]), false ,'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['events'] as $data) {
            $formatted_data[] = [
                'id' => $data['messageId'],
                'subject' => $data['subject'],
                'sender' => $data['from'],
                'recipient' => $data['email'],
                'send_time' => $data['date'],
                'status' => $data['event']
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