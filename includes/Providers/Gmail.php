<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;
class Gmail extends BaseProvider
{
    const GMAIL_API_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/';
    const OAUTH2_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    const SCOPES = [
        'https://www.googleapis.com/auth/gmail.send',
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.labels',
    ];

    private $access_token_data = null;

    public function __construct($config_keys)
    {
        parent::__construct($config_keys);
    }

    public function get_api_url()
    {
        return self::GMAIL_API_URL;
    }

    public function get_headers()
    {
        return [];
    }

    private function save_access_token_data($token_data)
    {
        if (isset($token_data['expires_in'])) {
            $token_data['created_at'] = time();
        }
        update_option('pro_mail_smtp_gmail_access_token_data', $token_data);
        $this->access_token_data = $token_data;
    }

    private function get_access_token_data()
    {
        if ($this->access_token_data === null) {
            $this->access_token_data = get_option('pro_mail_smtp_gmail_access_token_data', false);
        }
        return $this->access_token_data;
    }

    private function save_refresh_token($token)
    {
        update_option('pro_mail_smtp_gmail_refresh_token', $token);
    }

    private function get_refresh_token()
    {
        return get_option('pro_mail_smtp_gmail_refresh_token');
    }

    private function save_access_token($token)
    {
        update_option('pro_mail_smtp_gmail_access_token', $token);
    }

    private function get_valid_access_token()
    {
        $token_data = $this->get_access_token_data();

        if (empty($token_data) || empty($token_data['access_token'])) {
            throw new \Exception('Gmail authentication required. Please connect your account.');
        }

        $expires_at = ($token_data['created_at'] ?? 0) + ($token_data['expires_in'] ?? 0) - 60;
        if (time() >= $expires_at) {
            $refresh_token = $this->get_refresh_token();
            if (empty($refresh_token)) {
                delete_option('pro_mail_smtp_gmail_access_token_data');
                $this->access_token_data = null;
                throw new \Exception('Refresh token is missing or invalid. Re-authorization required.');
            }

            try {
                $new_token_data = $this->fetch_access_token_with_refresh_token($refresh_token);
                $new_token_data['refresh_token'] = $refresh_token;
                $this->save_access_token_data($new_token_data);
                $this->save_access_token($new_token_data['access_token']);
                return $new_token_data['access_token'];
            } catch (\Exception $e) {
                delete_option('pro_mail_smtp_gmail_access_token_data');
                delete_option('pro_mail_smtp_gmail_refresh_token');
                delete_option('pro_mail_smtp_gmail_access_token');
                $this->access_token_data = null;
                throw new \Exception('Authentication expired or failed to refresh. Please reconnect your Gmail account. Details: ' . esc_html($e->getMessage()));
            }
        }

        return $token_data['access_token'];
    }

    private function fetch_access_token_with_refresh_token($refresh_token)
    {
        $args = [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'client_id' => $this->config_keys['client_id'],
                'client_secret' => $this->config_keys['client_secret'],
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token',
            ],
        ];

        $response = wp_remote_post(self::OAUTH2_TOKEN_URL, $args);

        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed during token refresh: ' . esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code >= 400 || empty($data) || isset($data['error'])) {
            throw new \Exception(esc_html($this->get_error_message($body, $http_code)));
        }

        return $data;
    }

    public function send($data)
    {
        $access_token = $this->get_valid_access_token();

        $boundary = uniqid(wp_rand(), true);
        $email_parts = [];
        $email_from = !empty($this->config_keys['email_from_overwrite']) ? $this->config_keys['email_from_overwrite'] : $data['from_email'];
        $from_name = !empty($data['from_name']) ? $data['from_name'] : $email_from;

        $to_emails = [];
        if (isset($data['to']) && is_array($data['to'])) {
            foreach ($data['to'] as $to_entry) {
                if (is_string($to_entry)) {
                    $to_emails[] = $to_entry;
                } elseif (is_array($to_entry) && isset($to_entry['email'])) {
                    $to_emails[] = isset($to_entry['name'])
                        ? "{$to_entry['name']} <{$to_entry['email']}>"
                        : $to_entry['email'];
                }
            }
        } elseif (isset($data['to']) && is_string($data['to'])) {
            $to_emails[] = $data['to'];
        }

        if (empty($to_emails)) {
            throw new \Exception('No valid recipient provided.');
        }

        $email_parts[] = "To: " . implode(', ', $to_emails);
        $email_parts[] = "From: {$from_name} <{$email_from}>";
        $email_parts[] = "Subject: {$data['subject']}";
        $email_parts[] = "MIME-Version: 1.0";
        $email_parts[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";

        if (!empty($data['reply_to'])) {
            $reply_to_address = is_array($data['reply_to']) ? $data['reply_to']['email'] : $data['reply_to'];
            $reply_to_name = is_array($data['reply_to']) && isset($data['reply_to']['name']) ? $data['reply_to']['name'] : '';
            $email_parts[] = "Reply-To: " . ($reply_to_name ? "{$reply_to_name} <{$reply_to_address}>" : $reply_to_address);
        }

        foreach (['cc', 'bcc'] as $field) {
            if (!empty($data[$field])) {
                $addresses = [];
                if (is_string($data[$field])) $addresses[] = $data[$field];
                elseif (is_array($data[$field])) $addresses = $data[$field];

                if (!empty($addresses)) {
                    $email_parts[] = ucfirst($field) . ": " . implode(', ', $addresses);
                }
            }
        }
        $email_parts[] = "";

        $email_parts[] = "--{$boundary}";
        if ($data['message'] !== wp_strip_all_tags($data['message'])) {
            $content_type = 'text/html';
        } else {
            $content_type = 'text/plain';
        }
        $email_parts[] = "Content-Type: {$content_type}; charset=UTF-8";
        $email_parts[] = "Content-Transfer-Encoding: base64";
        $email_parts[] = "";
        $email_parts[] = rtrim(base64_encode($data['message']));
        $email_parts[] = "";

        if (!empty($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                if (empty($attachment['content']) || empty($attachment['name']) || empty($attachment['type'])) {
                    continue;
                }
                $email_parts[] = "--{$boundary}";
                $email_parts[] = "Content-Type: {$attachment['type']}; name=\"{$attachment['name']}\"";
                $email_parts[] = "Content-Disposition: attachment; filename=\"{$attachment['name']}\"";
                $email_parts[] = "Content-Transfer-Encoding: base64";
                $email_parts[] = "";
                $email_parts[] = $attachment['content'];
                $email_parts[] = "";
            }
        }

        $email_parts[] = "--{$boundary}--";

        $raw_email_message = implode("\r\n", $email_parts);

        $encoded_message = rtrim(strtr(base64_encode($raw_email_message), '+/', '-_'), '=');

        $args = [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['raw' => $encoded_message]),
        ];

        $response = wp_remote_post(self::GMAIL_API_URL . 'messages/send', $args);

        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed during email send: ' . esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $result_data = json_decode($body, true);
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code >= 400 || empty($result_data) || isset($result_data['error'])) {
            throw new \Exception(esc_html($this->get_error_message($body, $http_code)));
        }

        return [
            'message_id' => $result_data['id'] ?? null,
            'provider_response' => $result_data,
        ];
    }

    public function test_connection()
    {
        $access_token = $this->get_valid_access_token();

        $args = [
            'method' => 'GET',
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ];

        $response = wp_remote_get(self::GMAIL_API_URL . 'labels?maxResults=1', $args);

        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed during connection test: ' . esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code >= 400) {
            throw new \Exception('Gmail connection test failed: ' . esc_html($this->get_error_message($body, $http_code)));
        }

        return [
            'success' => true,
            'message' => 'Gmail connection verified successfully.',
        ];
    }

    public function get_auth_url()
    {
        $params = [
            'client_id' => $this->config_keys['client_id'],
            'redirect_uri' => admin_url('admin.php?page=pro-mail-smtp-providers'),
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => 'gmail'
        ];

        return self::OAUTH2_AUTH_URL . '?' . http_build_query($params);
    }

    public function handle_oauth_callback($code)
    {
        $args = [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'code' => $code,
                'client_id' => $this->config_keys['client_id'],
                'client_secret' => $this->config_keys['client_secret'],
                'redirect_uri' => admin_url('admin.php?page=pro-mail-smtp-providers'),
                'grant_type' => 'authorization_code',
            ],
        ];

        $response = wp_remote_post(self::OAUTH2_TOKEN_URL, $args);

        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed during token exchange: ' . esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code >= 400 || empty($data) || isset($data['error']) || !isset($data['access_token'])) {
            delete_option('pro_mail_smtp_gmail_access_token_data');
            delete_option('pro_mail_smtp_gmail_refresh_token');
            delete_option('pro_mail_smtp_gmail_access_token');
            $this->access_token_data = null;
            throw new \Exception('Failed to exchange authorization code for token: ' . esc_html($this->get_error_message($body, $http_code)));
        }

        $this->save_access_token_data($data);
        $this->save_access_token($data['access_token']);
        if (!empty($data['refresh_token'])) {
            $this->save_refresh_token($data['refresh_token']);
        }

        return true;
    }

    public function get_analytics($filters = [])
    {
        $access_token = $this->get_valid_access_token();

        $per_page = isset($filters['per_page']) ? max(1, (int) $filters['per_page']) : 10;
        $query_params = [
            'maxResults' => $per_page,
            'labelIds' => 'SENT',
        ];

        $q_parts = [];
        if (!empty($filters['date_from'])) {
            $q_parts[] = 'after:' . strtotime($filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q_parts[] = 'before:' . (strtotime($filters['date_to']) + 86400);
        }
        if (!empty($q_parts)) {
            $query_params['q'] = implode(' ', $q_parts);
        }

        $list_url = self::GMAIL_API_URL . 'messages?' . http_build_query($query_params);

        $args = [
            'method' => 'GET',
            'timeout' => 30,
            'headers' => ['Authorization' => 'Bearer ' . $access_token],
        ];

        $response = wp_remote_get($list_url, $args);

        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed fetching message list for analytics: ' . esc_html($response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $list_data = json_decode($body, true);
        $http_code = wp_remote_retrieve_response_code($response);

        if ($http_code >= 400 || isset($list_data['error'])) {
            throw new \Exception('Failed to list Gmail messages for analytics: ' . esc_html($this->get_error_message($body, $http_code)));
        }

        $analytics = [];
        if (!empty($list_data['messages'])) {
            foreach ($list_data['messages'] as $message_stub) {
                if (empty($message_stub['id'])) continue;

                $get_url = self::GMAIL_API_URL . 'messages/' . $message_stub['id'] . '?format=metadata&metadataHeaders=Subject&metadataHeaders=To&metadataHeaders=Date';
                $msg_response = wp_remote_get($get_url, $args);

                if (!is_wp_error($msg_response) && wp_remote_retrieve_response_code($msg_response) < 400) {
                    $msg_body = wp_remote_retrieve_body($msg_response);
                    $msg_data = json_decode($msg_body, true);

                    if ($msg_data && isset($msg_data['payload']['headers'])) {
                        $headers = $this->parse_api_headers($msg_data['payload']['headers']);
                        $analytics[] = [
                            'id' => esc_html($message_stub['id']),
                            'subject' => esc_html($headers['subject'] ?? ''),
                            'to' => esc_html($headers['to'] ?? ''),
                            'date' => esc_html($headers['date'] ?? ''),
                            'status' => 'sent',
                        ];
                    }
                } else {
                    throw new \Exception('Failed to fetch message details for analytics: ' . esc_html($this->get_error_message(wp_remote_retrieve_body($msg_response), wp_remote_retrieve_response_code($msg_response))));
                }
            }
        }

        return [
            'data' => $analytics,
            'columns' => ['id', 'subject', 'to', 'date', 'status'],
            'nextPageToken' => $list_data['nextPageToken'] ?? null,
            'totalMessages' => $list_data['resultSizeEstimate'] ?? count($analytics),
        ];
    }

    private function parse_api_headers($api_headers)
    {
        $headers = [];
        if (is_array($api_headers)) {
            foreach ($api_headers as $header) {
                if (isset($header['name']) && isset($header['value'])) {
                    $headers[strtolower($header['name'])] = $header['value'];
                }
            }
        }
        return $headers;
    }

    protected function get_error_message($body, $code)
    {
        $data = json_decode($body, true);
        $message = "Unknown Gmail API error.";

        if (isset($data['error']['message'])) {
            $message = esc_html($data['error']['message']);
            if (isset($data['error']['errors'][0]['reason'])) {
                $message .= " (Reason: " . esc_html($data['error']['errors'][0]['reason']) . ")";
            }
        } elseif (isset($data['error_description'])) {
            $message = esc_html($data['error_description']);
            if (isset($data['error'])) {
                $message .= " (Type: " . esc_html($data['error']) . ")";
            }
        } elseif (is_string($body) && !empty($body)) {
            $message = wp_strip_all_tags($body);
        }

        return "Gmail API Error: " . $message . " (HTTP " . esc_html($code) . ")";
    }
}