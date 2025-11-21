<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Outlook extends BaseProvider
{
    private $token_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $auth_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    private $api_url = 'https://graph.microsoft.com/v1.0/me/sendMail';

    public function get_api_url()
    {
        return $this->api_url;
    }

    public function get_headers()
    {
        $token = $this->get_access_token();
        if (empty($token['access_token'])) {
            return [];
        }
        return [
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Content-Type' => 'application/json'
        ];
    }

    private function save_access_token($token)
    {
        if (isset($token['expires_in'])) {
            $token['expires_at'] = time() + $token['expires_in'];
        }
        update_option('pro_mail_smtp_outlook_access_token', $token);
    }

    private function save_refresh_token($token)
    {
        update_option('pro_mail_smtp_outlook_refresh_token', $token);
    }

    private function get_access_token()
    {
        return get_option('pro_mail_smtp_outlook_access_token');
    }

    private function get_refresh_token()
    {
        return get_option('pro_mail_smtp_outlook_refresh_token');
    }

    public function send($data)
    {
        $token = $this->get_access_token();
        error_log('Zetema SMTP Outlook Provider: token: ' . print_r($token, true));
        
        $expires_at_readable = isset($token['expires_at']) ? date('Y-m-d H:i:s', $token['expires_at']) : 'not set';
        $current_time_readable = date('Y-m-d H:i:s', time());
        error_log('Zetema SMTP Outlook Provider: Token expires_at: ' . $expires_at_readable . ', Current time: ' . $current_time_readable);

        try {
            $token = $this->get_access_token();
            if (empty($token['access_token'])) {
                throw new \Exception('Outlook authentication required');
            }

            if (isset($token['expires_at']) && time() >= $token['expires_at']) {
                $this->refresh_token($this->get_refresh_token());
                $token = $this->get_access_token();
            }
            $email_from = $this->config_keys['email_from_overwrite'] ?? $data['from_email'];

            $email_data = [
                'message' => [
                    'subject' => $data['subject'],
                    'body' => [
                        'contentType' => 'HTML',
                        'content' => $data['message']
                    ],
                    'toRecipients' => array_map(function($email) {
                        return [
                            'emailAddress' => [
                                'address' => $email
                            ]
                        ];
                    }, (array)$data['to']),
                    'from' => [
                        'emailAddress' => [
                            'address' => $email_from,
                            'name' => $data['from_name']
                        ]
                    ]
                ]
            ];

            if (!empty($data['attachments'])) {
                $email_data['message']['attachments'] = array_map(function($attachment) {
                    return [
                        '@odata.type' => '#microsoft.graph.fileAttachment',
                        'name' => $attachment['name'],
                        'contentType' => $attachment['type'],
                        'contentBytes' => base64_encode($attachment['content'])
                    ];
                }, $data['attachments']);
            }

            $response = $this->request(
                $this->api_url,
                $email_data,
                [
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'Content-Type' => 'application/json'
                ],
                'POST'
            );

            if (isset($response['error'])) {
                throw new \Exception($this->get_error_message(json_encode($response), 400));
            }

            return [
                'message_id' => uniqid('outlook_', true),
                'provider_response' => $response
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function test_connection()
    {
        try {
            $token = $this->get_access_token();
            if (empty($token['access_token'])) {
                throw new \Exception('Outlook authentication required');
            }

            $admin_email = get_option('admin_email');
            $site_name = get_bloginfo('name');
            $site_url = get_bloginfo('url');
            
            $from_email = get_option('pro_mail_smtp_from_email', get_option('admin_email'));
            if (empty($from_email) || !is_string($from_email)) {
                $from_email = $admin_email;
            }
            
            $test_data = [
                'to' => [$admin_email],
                'subject' => 'Zetema SMTP: Outlook Test Email',
                'message' => sprintf(
                    'This is a test email from %s (%s) to verify your Outlook email configuration with Zetema SMTP plugin.<br><br>If you\'re reading this, your Outlook connection is working properly!<br><br>Sent: %s',
                    $site_name,
                    $site_url,
                    gmdate('Y-m-d H:i:s')
                ),
                'from_email' => $from_email,
                'from_name' => 'Zetema SMTP Test'
            ];
            
            $result = $this->send($test_data);
            
            if (!empty($result['message_id'])) {
                return [
                    'success' => true,
                    'message' => 'Outlook connection verified successfully. Test email sent to ' . $admin_email
                ];
            } else {
                throw new \Exception('Test email could not be sent');
            }
        } catch (\Exception $e) {
            throw new \Exception('Outlook connection test failed: ' . esc_html($e->getMessage()));
        }
    }

    public function get_auth_url()
    {
        $params = [
            'client_id'     => $this->config_keys['client_id'],
            'response_type' => 'code',
            'redirect_uri'  => admin_url('admin.php?page=pro-mail-smtp-providers'),
            'response_mode' => 'query',
            'scope'         => 'offline_access Mail.Send',
            'state'         => 'outlook'
        ];
        return $this->auth_url . '?' . http_build_query($params);
    }

    protected function get_error_message($body, $code)
    {
        $data = json_decode($body, true);

        if (isset($data['error']['message'])) {
            return "Outlook API error: {$data['error']['message']}. (HTTP $code)";
        }

        if (isset($data['message'])) {
            return "Outlook API error: {$data['message']}. (HTTP $code)";
        }

        return "Outlook API error (HTTP $code)";
    }

    public function get_analytics($filters = [])
    {
        return [];
    }

    public function handle_oauth_callback($code)
    {
        try {
            $response = $this->request(
                $this->token_url,
                [
                    'client_id' => $this->config_keys['client_id'],
                    'client_secret' => $this->config_keys['client_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => admin_url('admin.php?page=pro-mail-smtp-providers')
                ],
                true,
                'POST',
                true
            );
            if (isset($response['error'])) {
                throw new \Exception('OAuth error: ' . ($response['error_description'] ?? $response['error']));
            }

            $this->save_access_token($response);
            if (!empty($response['refresh_token'])) {
                $this->save_refresh_token($response['refresh_token']);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to authenticate with Outlook: ' . esc_html($e->getMessage()));
        }
    }

    private function refresh_token($refresh_token)
    {
        try {
            $response = $this->request(
                $this->token_url,
                [
                    'client_id' => $this->config_keys['client_id'],
                    'client_secret' => $this->config_keys['client_secret'],
                    'refresh_token' => $refresh_token,
                    'grant_type' => 'refresh_token'
                ],
                true,
                'POST',
                true,
                [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
                );

            if (isset($response['error'])) {
                throw new \Exception('Failed to refresh token: ' . ($response['error_description'] ?? $response['error']));
            }

            $this->save_access_token($response);
            if (!empty($response['refresh_token'])) {
                $this->save_refresh_token($response['refresh_token']);
            }
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('Authentication expired. Please reconnect your Outlook account.');
        }
    }
}
