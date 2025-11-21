<?php
namespace TurboSMTP\ProMailSMTP\Alerts;

if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\DB\AlertConfigRepository;

class AlertService {
    private $alert_repository;
    private $daily_failure_counts = [];

    public function __construct() {
        $this->alert_repository = new AlertConfigRepository();
    }

    /**
     * Process email failure and potentially send alerts
     */
    public function process_email_failure($email_data) {
        $enabled_configs = $this->alert_repository->get_enabled_alert_configs();
        
        if (empty($enabled_configs)) {
            return;
        }

        $today_date = \current_time('Y-m-d');
        
        // Clear cache to ensure we get the latest failure count, especially important
        // when multiple providers fail for the same email in a single request
        unset($this->daily_failure_counts[$today_date]);
        $failure_count = $this->get_daily_failure_count($today_date);
        
        foreach ($enabled_configs as $config) {
            // Ensure config is a proper object
            if (!is_object($config)) {
                error_log('AlertService: Invalid config in enabled_configs - expected object, got ' . gettype($config));
                continue;
            }
            
            // Ensure required properties exist
            if (!isset($config->failure_threshold)) {
                error_log('AlertService: Config missing failure_threshold property');
                continue;
            }
            
            $failure_threshold = (int)$config->failure_threshold;
            
            if ($failure_threshold == 0) {
                // Send alert for every failure if threshold is 0
                $this->send_alert($config, $email_data);
            } elseif ($failure_threshold > 0) {
                // Check if current failure count is a multiple of threshold
                if ($failure_count % $failure_threshold == 0) {
                    // Get recent failures for consolidated alert
                    $recent_failures = $this->alert_repository->get_recent_failures($failure_threshold);
                    
                    // Send consolidated alert with all recent failures
                    $this->send_consolidated_alert($config, $recent_failures, $failure_count);
                } else {
                    error_log("AlertService: Threshold not reached, no alert sent");
                }
            }
        }
    }

    /**
     * Send alert to specified channel
     */
    public function send_alert($config, $email_data, $failure_count = null) {
        // Ensure config is a proper object
        if (!is_object($config)) {
            error_log('AlertService: Invalid config passed - expected object, got ' . gettype($config));
            return false;
        }
        
        // Ensure required properties exist
        if (!isset($config->channel_type) || !isset($config->webhook_url)) {
            error_log('AlertService: Config missing required properties');
            return false;
        }
        
        $message = $this->format_alert_message($config, $email_data, $failure_count);
        
        switch ($config->channel_type) {
            case 'slack':
                return $this->send_slack_alert($config->webhook_url, $message, $email_data, $failure_count);
                
            case 'discord':
                return $this->send_discord_alert($config->webhook_url, $message, $email_data, $failure_count);
                
            case 'teams':
                return $this->send_teams_alert($config->webhook_url, $message, $email_data, $failure_count);
                
            case 'webhook':
                return $this->send_webhook_alert($config->webhook_url, $message, $email_data, $failure_count);
                
            default:
                return false;
        }
    }

    /**
     * Send consolidated alert with multiple failures
     */
    public function send_consolidated_alert($config, $recent_failures, $failure_count) {
        // Ensure config is a proper object
        if (!is_object($config)) {
            error_log('AlertService: Invalid config passed to send_consolidated_alert - expected object, got ' . gettype($config));
            return false;
        }
        
        // Ensure required properties exist
        if (!isset($config->channel_type) || !isset($config->webhook_url)) {
            error_log('AlertService: Config missing required properties in send_consolidated_alert');
            return false;
        }
        
        $consolidated_data = $this->format_consolidated_alert_data($config, $recent_failures, $failure_count);
        
        switch ($config->channel_type) {
            case 'slack':
                return $this->send_slack_consolidated_alert($config->webhook_url, $consolidated_data);
                
            case 'discord':
                return $this->send_discord_consolidated_alert($config->webhook_url, $consolidated_data);
                
            case 'teams':
                return $this->send_teams_consolidated_alert($config->webhook_url, $consolidated_data);
                
            case 'webhook':
                return $this->send_webhook_consolidated_alert($config->webhook_url, $consolidated_data);
                
            default:
                return false;
        }
    }

    /**
     * Format alert message with email failure details
     */
    private function format_alert_message($config, $email_data, $failure_count = null) {
        // Ensure config has required properties with defaults
        $failure_threshold = isset($config->failure_threshold) ? (int)$config->failure_threshold : 0;
        
        $is_test = $email_data['is_test'] ?? false;
        $today_failure_count = $failure_count ?? $this->get_daily_failure_count(\current_time('Y-m-d'));
        
        if ($is_test) {
            $message = "🧪 **Test Alert from Zetema SMTP**\n\n";
        } else {
            $message = "🚨 **Email Delivery Failure Alert**\n\n";
            
            // Add threshold information for non-test alerts
            if ($failure_threshold > 0 && $failure_count) {
                $multiple = $failure_count / $failure_threshold;
                $ordinal_text = $this->get_ordinal($multiple);
                $message .= "**Threshold Alert:** {$failure_count} failures reached (" . 
                           $ordinal_text . 
                           " threshold of {$failure_threshold})\n\n";
            }
        }
        
        $message .= "**Email Details:**\n";
        $message .= "• Subject: " . ($email_data['subject'] ?? 'N/A') . "\n";
        $message .= "• Recipient: " . ($email_data['to_email'] ?? 'N/A') . "\n";
        $message .= "• Provider: " . ($email_data['provider'] ?? 'N/A') . "\n";
        $message .= "• Error: " . ($email_data['error_message'] ?? 'N/A') . "\n";
        
        if (!$is_test) {
            $message .= "• Failures Today: " . $failure_count . "\n";
            
            if ($failure_threshold > 0) {
                $message .= "• Threshold: " . $failure_threshold . "\n";
            }
        }
        
        $message .= "\n**Site:** " . \get_bloginfo('name') . " (" . \home_url() . ")";
        
        return $message;
    }

    /**
     * Send Slack alert
     */
    private function send_slack_alert($webhook_url, $message, $email_data, $failure_count = null) {
        $is_test = $email_data['is_test'] ?? false;
        
        $payload = [
            'text' => $is_test ? '🧪 Test Alert from Zetema SMTP' : '🚨 Email Delivery Failure',
            'attachments' => [
                [
                    'color' => $is_test ? '#36a64f' : '#ff0000',
                    'fields' => [
                        [
                            'title' => 'Subject',
                            'value' => $email_data['subject'] ?? 'N/A',
                            'short' => true
                        ],
                        [
                            'title' => 'Recipient',
                            'value' => $email_data['to_email'] ?? 'N/A',
                            'short' => true
                        ],
                        [
                            'title' => 'Provider',
                            'value' => $email_data['provider'] ?? 'N/A',
                            'short' => true
                        ],
                        [
                            'title' => 'Error',
                            'value' => $email_data['error_message'] ?? 'N/A',
                            'short' => false
                        ]
                    ],
                    'footer' => \get_bloginfo('name'),
                    'footer_icon' => 'https://wordpress.org/favicon.ico'
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send Discord alert
     */
    private function send_discord_alert($webhook_url, $message, $email_data, $failure_count = null) {
        $is_test = $email_data['is_test'] ?? false;
        
        $payload = [
            'embeds' => [
                [
                    'title' => $is_test ? '🧪 Test Alert from Zetema SMTP' : '🚨 Email Delivery Failure',
                    'color' => $is_test ? 3581519 : 16711680, // Green for test, Red for failure
                    'fields' => [
                        [
                            'name' => 'Subject',
                            'value' => $email_data['subject'] ?? 'N/A',
                            'inline' => true
                        ],
                        [
                            'name' => 'Recipient',
                            'value' => $email_data['to_email'] ?? 'N/A',
                            'inline' => true
                        ],
                        [
                            'name' => 'Provider',
                            'value' => $email_data['provider'] ?? 'N/A',
                            'inline' => true
                        ],
                        [
                            'name' => 'Error',
                            'value' => $email_data['error_message'] ?? 'N/A',
                            'inline' => false
                        ]
                    ],
                    'footer' => [
                        'text' => \get_bloginfo('name')
                    ],
                    'timestamp' => \current_time('c')
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send Microsoft Teams alert
     */
    private function send_teams_alert($webhook_url, $message, $email_data, $failure_count = null) {
        $is_test = $email_data['is_test'] ?? false;
        
        $payload = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => $is_test ? 'Test Alert from Zetema SMTP' : 'Email Delivery Failure',
            'themeColor' => $is_test ? '36a64f' : 'ff0000',
            'title' => $is_test ? '🧪 Test Alert from Zetema SMTP' : '🚨 Email Delivery Failure',
            'sections' => [
                [
                    'facts' => [
                        [
                            'name' => 'Subject',
                            'value' => $email_data['subject'] ?? 'N/A'
                        ],
                        [
                            'name' => 'Recipient',
                            'value' => $email_data['to_email'] ?? 'N/A'
                        ],
                        [
                            'name' => 'Provider',
                            'value' => $email_data['provider'] ?? 'N/A'
                        ],
                        [
                            'name' => 'Error',
                            'value' => $email_data['error_message'] ?? 'N/A'
                        ],
                        [
                            'name' => 'Site',
                            'value' => \get_bloginfo('name') . ' (' . \home_url() . ')'
                        ]
                    ]
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send custom webhook alert
     */
    private function send_webhook_alert($webhook_url, $message, $email_data, $failure_count = null) {
        $payload = [
            'message' => $message,
            'email_data' => $email_data,
            'site_name' => \get_bloginfo('name'),
            'site_url' => \home_url(),
            'timestamp' => \current_time('c')
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send HTTP request to webhook
     */
    private function send_webhook_request($url, $payload) {
        $response = \wp_remote_post($url, [
            'body' => \wp_json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);

        if (\is_wp_error($response)) {
            error_log('Zetema SMTP Alert: Failed to send webhook: ' . $response->get_error_message());
            return false;
        }

        $response_code = \wp_remote_retrieve_response_code($response);
        return $response_code >= 200 && $response_code < 300;
    }

    /**
     * Get daily failure count with caching
     */
    private function get_daily_failure_count($date) {
        if (!isset($this->daily_failure_counts[$date])) {
            $this->daily_failure_counts[$date] = $this->alert_repository->get_failure_count_today($date);
        }
        
        return $this->daily_failure_counts[$date];
    }

    /**
     * Convert number to ordinal text (1st, 2nd, 3rd, etc.)
     */
    private function get_ordinal($number) {
        $number = (int) $number;
        
        if ($number == 1) {
            return "first";
        } elseif ($number == 2) {
            return "second";
        } elseif ($number == 3) {
            return "third";
        } elseif ($number == 4) {
            return "fourth";
        } elseif ($number == 5) {
            return "fifth";
        } else {
            return $number . "th";
        }
    }

    /**
     * Format consolidated alert data
     */
    private function format_consolidated_alert_data($config, $recent_failures, $failure_count) {
        $threshold = $config->failure_threshold;
        $multiple = $failure_count / $threshold;
        $ordinal = $this->get_ordinal($multiple);
        
        $data = [
            'title' => "🚨 Email Delivery Failures Alert",
            'threshold_info' => "{$failure_count} failures reached ({$ordinal} threshold of {$threshold})",
            'failure_count' => $failure_count,
            'threshold' => $threshold,
            'multiple' => $multiple,
            'ordinal' => $ordinal,
            'recent_failures' => $recent_failures,
            'summary' => $this->generate_failure_summary($recent_failures),
            'site_name' => \get_bloginfo('name'),
            'site_url' => \home_url(),
            'timestamp' => \current_time('c')
        ];
        
        return $data;
    }

    /**
     * Generate failure summary from recent failures
     */
    private function generate_failure_summary($recent_failures) {
        if (empty($recent_failures)) {
            return [];
        }
        
        $providers = [];
        $recipients = [];
        
        foreach ($recent_failures as $failure) {
            // Count providers
            $provider = $failure->provider ?? 'unknown';
            $providers[$provider] = ($providers[$provider] ?? 0) + 1;
            
            // Count unique recipients
            $to_email = $failure->to_email ?? 'unknown';
            if (!in_array($to_email, $recipients)) {
                $recipients[] = $to_email;
            }
        }
        
        return [
            'total_failures' => count($recent_failures),
            'providers' => $providers,
            'unique_recipients' => count($recipients),
            'recipients' => array_slice($recipients, 0, 5) // Show first 5 recipients
        ];
    }



    /**
     * Send Slack consolidated alert
     */
    private function send_slack_consolidated_alert($webhook_url, $data) {
        $summary = $data['summary'];
        
        $fields = [
            [
                'title' => 'Threshold Status',
                'value' => $data['threshold_info'],
                'short' => false
            ],
            [
                'title' => 'Total Failures',
                'value' => $summary['total_failures'],
                'short' => true
            ],
            [
                'title' => 'Unique Recipients',
                'value' => $summary['unique_recipients'],
                'short' => true
            ]
        ];
        
        // Add provider breakdown
        if (!empty($summary['providers'])) {
            $provider_text = '';
            foreach ($summary['providers'] as $provider => $count) {
                $provider_text .= "• {$provider}: {$count}\n";
            }
            $fields[] = [
                'title' => 'By Provider',
                'value' => trim($provider_text),
                'short' => true
            ];
        }
        
        // Add recent failure samples
        if (!empty($data['recent_failures'])) {
            $failure_details = '';
            foreach ($data['recent_failures'] as $index => $failure) {
                $failure_number = $index + 1;
                $failure_details .= "**Failure #{$failure_number}:**\n";
                $failure_details .= "• **Subject:** " . ($failure->subject ?? 'N/A') . "\n";
                $failure_details .= "• **Recipient:** " . ($failure->to_email ?? 'N/A') . "\n";
                $failure_details .= "• **Provider:** " . ($failure->provider ?? 'N/A') . "\n";
                $failure_details .= "• **Error:** " . ($failure->error_message ?? 'N/A') . "\n";
                $failure_details .= "• **Time:** " . ($failure->sent_at ?? 'N/A') . "\n\n";
            }
            $fields[] = [
                'title' => 'Individual Failure Details',
                'value' => trim($failure_details),
                'short' => false
            ];
        }
        
        $payload = [
            'text' => $data['title'],
            'attachments' => [
                [
                    'color' => 'danger',
                    'title' => $data['title'],
                    'fields' => $fields,
                    'footer' => $data['site_name'],
                    'footer_icon' => 'https://wordpress.org/favicon.ico',
                    'ts' => strtotime($data['timestamp'])
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send Discord consolidated alert
     */
    private function send_discord_consolidated_alert($webhook_url, $data) {
        $summary = $data['summary'];
        
        $fields = [
            [
                'name' => 'Threshold Status',
                'value' => $data['threshold_info'],
                'inline' => false
            ],
            [
                'name' => 'Total Failures',
                'value' => $summary['total_failures'],
                'inline' => true
            ],
            [
                'name' => 'Unique Recipients',
                'value' => $summary['unique_recipients'],
                'inline' => true
            ]
        ];
        
        // Add provider breakdown
        if (!empty($summary['providers'])) {
            $provider_text = '';
            foreach ($summary['providers'] as $provider => $count) {
                $provider_text .= "• {$provider}: {$count}\n";
            }
            $fields[] = [
                'name' => 'By Provider',
                'value' => $provider_text,
                'inline' => true
            ];
        }

        // Add detailed failure information
        if (!empty($data['recent_failures'])) {
            $failure_details = '';
            foreach ($data['recent_failures'] as $index => $failure) {
                $failure_number = $index + 1;
                $failure_details .= "**Failure #{$failure_number}:**\n";
                $failure_details .= "Subject: " . ($failure->subject ?? 'N/A') . "\n";
                $failure_details .= "Recipient: " . ($failure->to_email ?? 'N/A') . "\n";
                $failure_details .= "Provider: " . ($failure->provider ?? 'N/A') . "\n";
                $failure_details .= "Error: " . ($failure->error_message ?? 'N/A') . "\n";
                $failure_details .= "Time: " . ($failure->sent_at ?? 'N/A') . "\n\n";
            }
            $fields[] = [
                'name' => 'Individual Failure Details',
                'value' => trim($failure_details),
                'inline' => false
            ];
        }

        $payload = [
            'embeds' => [
                [
                    'title' => $data['title'],
                    'color' => 16711680, // Red
                    'fields' => $fields,
                    'footer' => [
                        'text' => $data['site_name']
                    ],
                    'timestamp' => $data['timestamp']
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send Teams consolidated alert
     */
    private function send_teams_consolidated_alert($webhook_url, $data) {
        $summary = $data['summary'];
        
        $facts = [
            [
                'name' => 'Threshold Status',
                'value' => $data['threshold_info']
            ],
            [
                'name' => 'Total Failures',
                'value' => $summary['total_failures']
            ],
            [
                'name' => 'Unique Recipients',
                'value' => $summary['unique_recipients']
            ]
        ];
        
        // Add provider breakdown
        if (!empty($summary['providers'])) {
            $provider_text = '';
            foreach ($summary['providers'] as $provider => $count) {
                $provider_text .= "{$provider}: {$count}, ";
            }
            $facts[] = [
                'name' => 'By Provider',
                'value' => rtrim($provider_text, ', ')
            ];
        }
        
        $facts[] = [
            'name' => 'Site',
            'value' => $data['site_name'] . ' (' . $data['site_url'] . ')'
        ];

        // Add detailed failure information
        if (!empty($data['recent_failures'])) {
            $failure_details = '';
            foreach ($data['recent_failures'] as $index => $failure) {
                $failure_number = $index + 1;
                $failure_details .= "Failure #{$failure_number}: ";
                $failure_details .= "Subject: " . ($failure->subject ?? 'N/A') . ", ";
                $failure_details .= "To: " . ($failure->to_email ?? 'N/A') . ", ";
                $failure_details .= "Provider: " . ($failure->provider ?? 'N/A') . ", ";
                $failure_details .= "Error: " . substr($failure->error_message ?? 'N/A', 0, 100) . "... ";
                $failure_details .= "Time: " . ($failure->sent_at ?? 'N/A') . "; ";
            }
            $facts[] = [
                'name' => 'Individual Failures',
                'value' => rtrim($failure_details, '; ')
            ];
        }

        $payload = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => $data['title'],
            'themeColor' => 'ff0000',
            'title' => $data['title'],
            'sections' => [
                [
                    'facts' => $facts
                ]
            ]
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

    /**
     * Send custom webhook consolidated alert
     */
    private function send_webhook_consolidated_alert($webhook_url, $data) {
        $payload = [
            'alert_type' => 'consolidated_failure',
            'title' => $data['title'],
            'threshold_info' => $data['threshold_info'],
            'failure_count' => $data['failure_count'],
            'threshold' => $data['threshold'],
            'multiple' => $data['multiple'],
            'summary' => $data['summary'],
            'individual_failures' => array_map(function($failure) {
                return [
                    'subject' => $failure->subject ?? null,
                    'to_email' => $failure->to_email ?? null,
                    'provider' => $failure->provider ?? null,
                    'error_message' => $failure->error_message ?? null,
                    'sent_at' => $failure->sent_at ?? null
                ];
            }, $data['recent_failures']),
            'site_name' => $data['site_name'],
            'site_url' => $data['site_url'],
            'timestamp' => $data['timestamp']
        ];

        return $this->send_webhook_request($webhook_url, $payload);
    }

}
