<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class TurboSMTP extends BaseProvider
{
    protected function get_api_url()
    {
        return 'https://pro.api.serversmtp.com/api/v2/';
    }

    protected function get_headers()
    {
        return [
            'consumerKey' => $this->config_keys['consumer_key'],
            'consumerSecret' => $this->config_keys['consumer_secret'],
            'Content-Type' => 'application/json'
        ];
    }

    public function send($data)
    {
        $endpoint = 'https://api.turbo-smtp.com/api/v2/mail/send';
        $email_from = $this->config_keys['email_from_overwrite'] ? $this->config_keys['email_from_overwrite'] : $data['from_email'];
        $payload = [
            'from' => $email_from,
            'subject' => $data['subject'],
            'to' => implode(",", $data['to']),
            'reply_to' => $data['reply_to'] ?? $email_from,
        ];
        
        if( $data['message'] !== wp_strip_all_tags($data['message']) ) {
            $payload['html_content'] = $data['message'];
        }else{
            $payload['content'] = $data['message'];
        }

        if (!empty($data['cc'])) {
            $payload['cc'] = implode(",", $data['cc']);
        }

        if (!empty($data['bcc'])) {
            $payload['bcc'] = implode(",", $data['bcc']);
        }
        if (!empty($data['attachments'])) {
            $payload['attachments'] = array_map(function ($attachment) {
                return [
                    'content' => $attachment['content'],
                    'name' => $attachment['name'],
                    'type' => $attachment['type']
                ];
            }, $data['attachments']);
        }
        $response = $this->request($endpoint, $payload, true, 'POST');

        return [
            'message_id' => 'turboSMTP_' . uniqid(),
            'provider_response' => $response
        ];
    }

    protected function prepare_request_body($data)
    {
        return json_encode($data);
    }

    protected function get_error_message($body, $code)
    {
        $data = json_decode($body, true);

        if (isset($data['message'])) {
            return "Turbo SMTP API error: {$data['message']}. (HTTP $code)";
        }

        return "Turbo SMTP API error (HTTP $code)";
    }

    public function test_connection()
    {
        $endpoint = 'meta/countries';
        $response = $this->request($endpoint, [], false, 'GET');

        if (isset($response['error'])) {
            throw new \Exception(esc_html($response['error']['message']));
        }

        return $response;
    }
    public function get_analytics($filters = [])
    {
        $endpoint = 'analytics';

        $response = $this->request($endpoint, [
            'from' => $filters['date_from'],
            'to' => $filters['date_to'],
            'page' => $filters['page'] ?? 1,
            'limit' => $filters['per_page'] ?? 5
        ], false, 'GET');
        
        $data = [];
        $data['data'] = $this->format_analytics_response($response);
        $data['columns'] = $this->analytics_table_columns();
        return $data;
    }

    private function format_analytics_response($response)
    {
        $formatted_data = [];
        foreach ($response['results'] as $data) {
            $formatted_data[] = [
                'id' => $data['id'],
                'subject' => $data['subject'],
                'sender' => $data['sender'],
                'recipient' => $data['recipient'],
                'send_time' => $data['send_time'],
                'status' => $data['status'],
                'domain' => $data['domain'],
                'provider_message' => $data['error']
            ];
        }

        return $formatted_data;
    }
    private function analytics_table_columns()
    {
        return [
            'id',
            'subject',
            'sender',
            'recipient',
            'send_time',
            'status',
            'domain',
            'provider_message'
        ];
    }
}
