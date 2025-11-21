<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Postmark extends BaseProvider {
    protected function get_api_url() {
        return 'https://api.postmarkapp.com/';
    }
    
    protected function get_headers() {
        return [
            'X-Postmark-Server-Token' => $this->config_keys['api_key'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data) {
        $endpoint = '/email';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];
        $payload = [
            'From' => $email_from,
            'To' => $data['to'][0],
            'Subject' => $data['subject'],
            'HtmlBody' => $data['message'],
        ];

        if (!empty($data['cc'])) {
            $payload['Cc'] = $data['cc'];
        }

        if (!empty($data['bcc'])) {
            $payload['Bcc'] = $data['bcc'];
        }

        if (!empty($data['attachments'])) {
            $payload['attachments'] = array_map(function($attachment) {
                return [
                    'Name' => $attachment['name'],
                    'ContentType' => $attachment['type'],
                    'Content' => $attachment['content'],
                ];
            }, $data['attachments']);
        }

        $response = $this->request($endpoint, $payload, false, 'POST');
        return [
            'message_id' => 'Postmark' . uniqid(),
            'provider_response' => $response
        ];
    }
    
    protected function prepare_request_body($data) {
        return json_encode($data);
    }
    
    protected function get_error_message($body, $code) {
        $data = json_decode($body, true);
        
        if (isset($data['message'])) {
                return "Postmark API error: {$data['message']}. (HTTP $code)";
            }
        
        return "Postmark API error (HTTP $code)";
    }

    public function test_connection() {
        $endpoint = 'stats/outbound';


        $response = $this->request($endpoint, [], false, 'GET');
        return [
            'message_id' => 'Postmark__' . uniqid(),
            'provider_response' => $response
        ];
    }
    public function get_analytics($filters = []) {
        $endpoint = '/messages/outbound';
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $offset = ($page - 1) * $per_page;
        $response = $this->request($endpoint, [
            'fromdate' => $filters['date_from'],
            'todate'   => $filters['date_to'],
            'count'    => $per_page,
            'offset'   => $offset
        ], false ,'GET');
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response) {
        $formatted_data = [];
        foreach ($response['Messages'] as $data) {
            $formatted_data[] = [
                'id' => $data['MessageID'],
                'subject' => $data['Subject'],
                'sender' => $data['From'],
                'recipient' => json_encode($data['Recipients']),
                'send_time' => $data['ReceivedAt'],
                'status' => $data['Status']
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