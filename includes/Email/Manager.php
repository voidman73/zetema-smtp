<?php
namespace TurboSMTP\ProMailSMTP\Email;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use TurboSMTP\ProMailSMTP\Providers\ProviderFactory;
use TurboSMTP\ProMailSMTP\Email\EmailFormatterService;
use TurboSMTP\ProMailSMTP\Email\EmailRoutingService;
use TurboSMTP\ProMailSMTP\Core\WPMailCaller;
use TurboSMTP\ProMailSMTP\Providers\PhpMailerProvider;
use TurboSMTP\ProMailSMTP\Alerts\AlertService;

/**
 * Class Manager
 *
 * Manages email sending operations through multiple providers with routing capabilities.
 * Handles provider initialization, email routing, and sending attempts with fallback support.
 *
 * @package TurboSMTP\ProMailSMTP\Email
 */
class Manager {
    private $connections = [];
    private $providerFactory;
    private $emailFormatterService;
    private $emailRoutingService;
    private $wpMailCaller;
    private $alertService;
    private $providersInitialized = false;

    /**
     * Initialize the Manager with required services and hook into WordPress.
     */
    public function __construct() {
        add_action('init', [$this, 'initProviders']);
        $this->providerFactory = new ProviderFactory();
        $this->emailFormatterService = new EmailFormatterService();
        $this->emailRoutingService = new EmailRoutingService();
        $this->wpMailCaller = new WPMailCaller();
        $this->alertService = new AlertService();
    }

    /**
     * Initialize email providers from database connections.
     * Loads and sorts providers based on their priority.
     *
     * @return void
     */
    public function initProviders() {
        if ($this->providersInitialized) {
            return;
        }
        $conn_repo = new \TurboSMTP\ProMailSMTP\DB\ConnectionRepository();
        $provider_configs = $conn_repo->get_all_connections();
        foreach ($provider_configs as $config) {
            if (!empty($config->provider) && !empty($config->id) && !empty($config->priority)) {
                $instance = $this->providerFactory->get_provider_class($config);
                $this->connections[] = [
                    'instance' => $instance,
                    'priority' => $config->priority,
                    'name' => $config->provider,
                    'connection_id' => $config->connection_id
                ];
            }
        }
        usort($this->connections, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        $this->providersInitialized = true;
    }

    /**
     * Handle email sending through configured providers.
     *
     * @param array $args     Email arguments containing to, subject, message, etc.
     * @return bool           True if email was sent successfully, false otherwise
     */
    public function sendMail($null, $args) {
        if (!$this->providersInitialized) {
            $this->initProviders();
        }
        $error_messages = [];
        $email_data = $this->emailFormatterService->format($args);
        $source_plugin = $this->wpMailCaller->getSourcePluginName();
        $email_data['source_app'] = $source_plugin;
        $matching_conditions = $this->emailRoutingService->getRoutingConditionIfExists($email_data);
        $routing_providers = $this->getRoutingProviders($matching_conditions);
        if ($this->trySendMail($routing_providers, $email_data, $error_messages)) {
            return true;
        }

        $remaining_providers = $this->getRemainingProviders($matching_conditions);
        if ($this->trySendMail($remaining_providers, $email_data, $error_messages)) {
            return true;
        }

        if(get_option('pro_mail_smtp_fallback_to_wp_mail', true)) {
            return $this->fallbackToWpMail($args);
        }
        return false;
    }

    private function fallbackToWpMail($args) {
        $current_email_data = $this->emailFormatterService->format($args);
        try {
        $phpmailer = new PhpMailerProvider();
        $result = $phpmailer->send($args);
        if($result) {
            $this->logEmail($current_email_data, $result, 'phpmailer', 'sent', null, false, 0);
            return true;
        }
    } catch (\Exception $e) {
            $error_message = $e->getMessage();
            $this->logEmail($current_email_data, null, 'phpmailer', 'failed', $error_message, false, 0);
            return false;
        }
    }

    /**
     * Get providers based on matching routing conditions.
     *
     * @param array $matching_conditions Array of routing conditions
     * @return array Array of providers with their routing configurations
     */
    private function getRoutingProviders($matching_conditions) {
        $routing_providers = [];
        if (!empty($matching_conditions)) {
            foreach ($matching_conditions as $condition) {
                $key = array_search($condition->connection_id, array_column($this->connections, 'connection_id'));
                if ($key !== false) {
                    $routing_providers[] = [
                        'provider' => $this->connections[$key],
                        'overwrite_sender' => $condition->overwrite_sender,
                        'overwrite_connection' => $condition->overwrite_connection,
                        'forced_senderemail' => $condition->forced_senderemail ?? '',
                        'forced_sendername' => $condition->forced_sendername ?? ''
                    ];
                }
            }

            usort($routing_providers, function($a, $b) {
                return $a['provider']['priority'] - $b['provider']['priority'];
            });
        }
        return $routing_providers;
    }

    /**
     * Get remaining providers that aren't included in routing conditions.
     *
     * @param array $matching_conditions Array of routing conditions
     * @return array Array of remaining providers
     */
    private function getRemainingProviders($matching_conditions) {
        $condition_ids = array_map(function($condition) {
            return $condition->connection_id;
        }, $matching_conditions);

        return array_filter($this->connections, function($provider) use ($condition_ids) {
            return !in_array($provider['connection_id'], $condition_ids);
        });
    }

    /**
     * Attempt to send email through a list of providers.
     *
     * @param array $providers      Array of providers to try
     * @param array $email_data     Formatted email data
     * @param array $error_messages Reference to array storing error messages
     * @return bool                 True if email was sent successfully, false otherwise
     */
    private function trySendMail($providers, $email_data, &$error_messages) {
        foreach ($providers as $provider_data) {
            try {
                $current_email_data = $email_data;
                if (isset($provider_data['overwrite_sender'])) {
                    $provider = $provider_data['provider'];

                    if ($provider_data['overwrite_sender']) {
                        if (!empty($provider_data['forced_senderemail'])) {
                            $current_email_data['from_email'] = $provider_data['forced_senderemail'];
                        }
                        if (!empty($provider_data['forced_sendername'])) {
                            $current_email_data['from_name'] = $provider_data['forced_sendername'];
                        }
                    }
                    if (!$provider_data['overwrite_connection']) {
                        try {
                            $result = $provider['instance']->send($current_email_data);
                            $this->logEmail($current_email_data, $result, $provider['name'], 'sent', null, false, 0);
                            return true;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                } else {
                    $provider = $provider_data;
                }
                $result = $provider['instance']->send($current_email_data);
                $this->logEmail($current_email_data, $result, $provider['name'], 'sent', null, false, 0);
                return true;
            } catch (\Exception $e) {
                $provider_name = isset($provider_data['provider']) ? $provider_data['provider']['name'] : $provider_data['name'];
                $error_messages[] = [
                    'provider' => $provider_name,
                    'error' => $e->getMessage()
                ];
                $this->logEmail($current_email_data ?? [], null, $provider_name, 'failed', $e->getMessage(), false, 0);
            }
        }
        return false;
    }

    /**
     * Log email sending attempt to database.
     *
     * @param array  $data     Email data
     * @param array  $result   Provider response
     * @param string $provider Provider name
     * @param string $status   Status of the email (sent/failed/resent)
     * @param string $error    Error message if any
     * @param bool   $is_resent Whether this is a resend attempt
     * @param int    $retry_count Number of retry attempts
     * @return void
     */
    private function logEmail($data, $result, $provider, $status, $error = null, $is_resent = false, $retry_count = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        $recipients = is_array($data['to']) ? $data['to'] : [$data['to']];
        
        foreach ($recipients as $to) {
            // Save email content for failed emails and resent emails to allow viewing
            $message_content = ($status === 'failed' || $status === 'resent') ? ($data['message'] ?? '') : null;
            $headers_data = (($status === 'failed' || $status === 'resent') && !empty($data['headers'])) ? json_encode($data['headers']) : null;
            
            // Extract CC and BCC from headers
            $cc_emails = '';
            $bcc_emails = '';
            $reply_to = '';
            if (!empty($data['headers']) && is_array($data['headers'])) {
                foreach ($data['headers'] as $header) {
                    if (is_string($header)) {
                        if (stripos($header, 'Cc:') === 0) {
                            $cc_emails = trim(substr($header, 3));
                        } elseif (stripos($header, 'Bcc:') === 0) {
                            $bcc_emails = trim(substr($header, 4));
                        } elseif (stripos($header, 'Reply-To:') === 0) {
                            $reply_to = trim(substr($header, 9));
                        }
                    }
                }
            }
            
            // Handle attachments (store for failed and resent emails)
            $attachment_data = null;
            if (($status === 'failed' || $status === 'resent') && !empty($data['attachments']) && is_array($data['attachments'])) {
                $attachment_data = json_encode($data['attachments']);
            }
            
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $insert_result = $wpdb->insert(
                $table_name,
                [
                    'provider' => $provider,
                    'from_email' => $data['from_email'] ?? '',
                    'to_email' => $to,
                    'cc_email' => $cc_emails,
                    'bcc_email' => $bcc_emails,
                    'reply_to' => $reply_to,
                    'subject' => $data['subject'] ?? '',
                    'message' => $message_content,
                    'headers' => $headers_data,
                    'attachment_data' => $attachment_data,
                    'status' => $status,
                    'message_id' => $result['message_id'] ?? null,
                    'error_message' => $error,
                    'is_resent' => $is_resent ? 1 : 0,
                    'retry_count' => $retry_count,
                    'sent_at' => gmdate('Y-m-d H:i:s')
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
            );
            
            // Process alerts for failed emails
            if ($status === 'failed') {
                $alert_data = [
                    'subject' => $data['subject'] ?? '',
                    'to_email' => $to,
                    'error_message' => $error,
                    'provider' => $provider,
                ];
                $this->alertService->process_email_failure($alert_data);
            }
        }
    }

    /**
     * Manually resend an email with a specific provider
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email content
     * @param array $headers Email headers
     * @param array $attachments Email attachments
     * @param int $connection_id Provider connection ID
     * @param int $original_log_id Original log ID for tracking resends
     * @return array Result with success status and message
     */
    public function manual_resend_email($to, $subject, $message, $headers = [], $attachments = [], $connection_id = null, $original_log_id = null)
    {
        try {
            // Get retry count for the original email if tracking resends
            $retry_count = 0;
            if ($original_log_id) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
                $retry_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT MAX(retry_count) + 1 FROM $table_name WHERE id = %d OR 
                     (to_email = %s AND subject = %s)", 
                    $original_log_id, $to, $subject
                ));
                $retry_count = $retry_count ? (int)$retry_count : 1;
            }
             if (!$this->providersInitialized) {
            $this->initProviders();
            }
            // Get the specific connection/provider
            $connection = null;
            foreach ($this->connections as $conn) {
                if ($conn['connection_id'] == $connection_id) {
                    $connection = $conn;
                    break;
                }
            }

            if (!$connection) {
                throw new \Exception('Provider connection not found');
            }
            
            // Prepare email data in wp_mail format
            $email_data = [
                'to' => $to,
                'subject' => $subject,
                'message' => $message,
                'headers' => $headers,
                'attachments' => [],
            ];
            
            // Format the email data
            $formatted_data = $this->emailFormatterService->format($email_data);
            $formatted_data['attachments'] = $attachments;
            // Create provider instance

            $provider_instance = $connection['instance'];
            
            if (!$provider_instance) {
                throw new \Exception('Could not create provider instance for: ' . $connection['name']);
            }
            
            // Send the email
            $result = $provider_instance->send($formatted_data);
            if ($result) {
                // Log successful resend
                $this->logEmail($formatted_data, $result, $connection['name'], 'resent', null, true, $retry_count);
                
                // Mark original email as resent if we have the original log ID
                if ($original_log_id) {
                    $this->markEmailAsResent($original_log_id);
                }
                
                return [
                    'success' => true,
                    'message' => 'Email resent successfully via ' . $connection['name']
                ];
            } else {
                throw new \Exception('Provider failed to send email');
            }
            
        } catch (\Exception $e) {
            // Log the failure with retry count
            if (isset($formatted_data)) {
                $this->logEmail($formatted_data, null, $connection['name'] ?? 'unknown', 'failed', $e->getMessage(), false, $retry_count);
            }
            
            return [
                'success' => false,
                'message' => 'Failed to resend email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark an email as resent in the database
     *
     * @param int $log_id Original log ID
     * @return void
     */
    private function markEmailAsResent($log_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_mail_smtp_email_log';
        
        $wpdb->update(
            $table_name,
            ['is_resent' => 1],
            ['id' => $log_id],
            ['%d'],
            ['%d']
        );
    }
}