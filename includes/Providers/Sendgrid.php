<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Sendgrid extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.sendgrid.com/v3/';
    }
    
    protected function get_headers() {
        return [
            'Authorization' => 'Bearer ' .$this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }
    
    public function send($data) {
        $endpoint = 'mail/send';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];

        $payload = [
            'personalizations' => [
                [
                    'to' => array_map(function($email) {
                        return ['email' => $email];
                    }, $data['to'])
                ]
            ],
            'from' => [
                'email' => $email_from,
                'name' => $data['from_name']
            ],
            'subject' => $data['subject'],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $data['message']
                ]
            ]
        ];
        
        if (!empty($data['cc'])) {
            $payload['personalizations'][0]['cc'] = array_map(function($email) {
                return ['email' => $email];
            }, $data['cc']);
        }
        
        if (!empty($data['bcc'])) {
            $payload['personalizations'][0]['bcc'] = array_map(function($email) {
                return ['email' => $email];
            }, $data['bcc']);
        }
        
        if (!empty($data['reply_to'])) {
            $payload['reply_to'] = [
                'email' => $data['reply_to']
            ];
        }
        
        if (!empty($data['attachments'])) {
            $payload['attachments'] = array_map(function($attachment) {
                return [
                    'content' => $attachment['content'],
                    'filename' => $attachment['name'],
                    'type' => $attachment['type'],
                    'disposition' => 'attachment'
                ];
            }, $data['attachments']);
        }
        
        $response = $this->request($endpoint, $payload);
        
        return [
            'message_id' => 'sendgrid_' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['errors']) && is_array($data['errors'])) {
            return implode(', ', array_column($data['errors'], 'message'));
        }
        
        return "SendGrid API error (HTTP $code)";
    }
    
    public function test_connection() {
        $response = $this->request('tracking_settings', [], false, 'GET');
        if (isset($response['error'])) {
            throw new \Exception(esc_html($response['error']['message']));
        }
        
        return true;
    }
    
    public function get_analytics($filters = []) {
        $endpoint = 'messages';
        
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $query_params = [
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
        ];
        
        if (!empty($filters['date_from'])) {
            $query_params['query'] = 'last_event_time>' . $filters['date_from'];
            
            if (!empty($filters['date_to'])) {
                $query_params['query'] .= ' AND last_event_time<' . $filters['date_to'];
            }
        }
        
        $response = $this->request($endpoint, $query_params, false, 'GET');
        
        if (isset($response['error'])) {
            throw new \Exception(esc_html($response['error']['message']));
        }
        
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        
        return $data;
    }
    
    private function format_analytics_response($response) {
        $formatted_data = [];
        
        if (isset($response['messages']) && is_array($response['messages'])) {
            foreach ($response['messages'] as $message) {
                $formatted_data[] = [
                    'id' => isset($message['msg_id']) ? $message['msg_id'] : '',
                    'subject' => isset($message['subject']) ? $message['subject'] : '',
                    'sender' => isset($message['from_email']) ? $message['from_email'] : '',
                    'recipient' => isset($message['to_email']) ? json_encode([$message['to_email']]) : '[]',
                    'send_time' => isset($message['last_event_time']) ? $message['last_event_time'] : '',
                    'status' => isset($message['status']) ? $message['status'] : ''
                ];
            }
        }
        
        return $formatted_data;
    }
    
    private function analytics_table_columns() {
        return [
            'id', 'subject', 'sender', 'recipient', 'send_time', 'status'
        ];
    }
}
