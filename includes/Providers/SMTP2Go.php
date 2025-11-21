<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class SMTP2GO extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.smtp2go.com/v3/';
    }
    
    protected function get_headers() {
        return [
            'X-Smtp2go-Api-Key' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = 'email/send';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];
        $payload = [
            'sender' => $email_from,
            'to' => $data['to'],
            'subject' => $data['subject'],
            'text_body' => $data['message'],
        ];

        if (!empty($data['cc'])) {
            $payload['cc'] = $data['cc'];
        }

        if (!empty($data['bcc'])) {
            $payload['bcc'] = $data['bcc'];
        }

        if (!empty($data['attachments'])) {
            $payload['attachments'] = array_map(function($attachment) {
                return [
                    'filename' => $attachment['name'],
                    'mimetype' => $attachment['type'],
                    'fileblob' => $attachment['content'],
                ];
            }, $data['attachments']);
        }

        $response = $this->request($endpoint, $payload, false, 'POST');
        
        return [
            'message_id' => 'SMTP2GO__' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "SMTP2GO API error: {$data['message']}. (HTTP $code)";
            }
        
        return "SMTP2GO API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'stats/email_history';
        $response = $this->request($endpoint, [], false,'POST');
        if (isset($response['error'])) {
            throw new \Exception(esc_html($response['error']['message']));
        }
        
        return $response;
    }
    public function get_analytics($filters = []) {
        $endpoint = 'activity/search';
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $offset = ($page - 1) * $per_page;
        $response = $this->request($endpoint, [
            'start_date' => $filters['date_from'],
            'end_date'   => gmdate('Y-m-d', strtotime($filters['date_to'] . ' +1 day')),
            'limit'      => $per_page,
            'offset'     => $offset
        ], false ,'POST');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['data']['events'] as $data) {
            $formatted_data[] = [
                'subject' => $data['subject'],
                'sender' => $data['sender'],
                'recipient' => $data['to'],
                'send_time' => $data['date'],
                'status' => $data['event'],
                'provider_message' => $data['smtp_response']
            ];
        }
        
        return $formatted_data;
    }

    private function analytics_table_columns(){
        return [
            'subject', 'sender', 'recipient', 'send_time', 'status', 'provider_message'
        ];
    }
}