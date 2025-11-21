<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class OtherSMTP
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecurity;
    private $smtpForcedSenderEmail;

    public function __construct($credentials)
    {
        $this->smtpUsername = $credentials['smtp_user'];
        $this->smtpPassword = $credentials['smtp_pw'];
        $this->smtpHost = $credentials['smtp_host'];
        $this->smtpPort = $credentials['smtp_port'] ?? 587;
        $this->smtpSecurity = $credentials['smtp_encryption'] ?? 'tls';
        $this->smtpForcedSenderEmail = $credentials['email_from_overwrite'] ?? '';
    }
    public function send($params)
    {
        try {
            if (empty($params['to']) || !is_array($params['to'])) {
                throw new Exception('Recipients list is empty or invalid');
            }

            $recipients = array_filter($params['to'], function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
            
            if (empty($recipients)) {
                throw new Exception('No valid recipient email addresses found');
            }
            
            $subject = sanitize_text_field($params['subject']);
            $body = wp_kses_post($params['message']);
            $email_from = $this->smtpForcedSenderEmail ? $this->smtpForcedSenderEmail : $params['from_email'];
            $from = filter_var($email_from , FILTER_VALIDATE_EMAIL);
            $fromName = sanitize_text_field($params['from_name'] ?? '');
            $cc = isset($params['cc']) ? array_map('filter_var', (array)$params['cc'], array_fill(0, count((array)$params['cc']), FILTER_SANITIZE_EMAIL)) : [];
            $bcc = isset($params['bcc']) ? array_map('filter_var', (array)$params['bcc'], array_fill(0, count((array)$params['bcc']), FILTER_SANITIZE_EMAIL)) : [];
            $replyTo = filter_var($params['reply_to'] ?? $from, FILTER_SANITIZE_EMAIL);
            $attachments = $params['attachments'] ?? [];

            $headers = [
                'From' => $fromName ? "$fromName <$from>" : $from,
                'Reply-To' => $replyTo,
                'X-Mailer' => 'PHP/' . phpversion(),
                'Content-Type' => 'text/html; charset=UTF-8'
            ];

            if (!empty($cc)) {
                $headers['Cc'] = implode(', ', $cc);
            }

            if (!empty($bcc)) {
                $headers['Bcc'] = implode(', ', $bcc);
            }

            $smtp = $this->mail_init();
            $this->mail_set_options($smtp);

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (is_string($attachment) && file_exists($attachment)) {
                        $this->mail_add_attachment($smtp, $attachment);
                    } elseif (is_array($attachment) && isset($attachment['path']) && file_exists($attachment['path'])) {
                        $this->mail_add_attachment($smtp, $attachment['path']);
                    }
                }
            }

            $sent = $this->mail_send($smtp, $recipients, $subject, $body, $headers);
            $this->mail_close($smtp);

            return [
                'success' => $sent,
                'message' => $sent ? 'Email sent successfully' : 'Failed to send email'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function mail_init()
    {
        return new PHPMailer(true);
    }

    private function mail_set_options($smtp)
    {
        $smtp->isSMTP();
        $smtp->Host = $this->smtpHost;
        $smtp->Port = $this->smtpPort;
        $smtp->SMTPAuth = true;
        $smtp->Username = $this->smtpUsername;
        $smtp->Password = $this->smtpPassword;

        if ($this->smtpSecurity === 'tls') {
            $smtp->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($this->smtpSecurity === 'ssl') {
            $smtp->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
    }

    private function mail_add_attachment($smtp, $attachment)
    {
            $smtp->addAttachment($attachment);
    }

    private function mail_send($smtp, $recipients, $subject, $body, $headers)
    {
        $fromEmail = is_array($headers['From']) ? $headers['From'][0] : $headers['From'];
        $fromName = '';

        if (preg_match('/^(.*?)\s*<(.+?)>$/', $fromEmail, $matches)) {
            $fromName = $matches[1];
            $fromEmail = $matches[2];
        }

        $smtp->setFrom($fromEmail, $fromName);

        foreach ($recipients as $recipient) {
            $smtp->addAddress($recipient);
        }

        if (isset($headers['Cc'])) {
            foreach ((array)$headers['Cc'] as $cc) {
                    $smtp->addCC($cc);
            }
        }

        if (isset($headers['Bcc'])) {
            foreach ((array)$headers['Bcc'] as $bcc) {
                    $smtp->addBCC($bcc);
            }
        }

        $smtp->Subject = $subject;
        $smtp->Body = $body;
        $smtp->isHTML(true);
        return $smtp->send();
    }

    private function mail_close($smtp)
    {
            $smtp->smtpClose();
    }

    public function test_connection()
    {
        try {
            $smtp = $this->mail_init();
            $this->mail_set_options($smtp);

            if ($smtp->smtpConnect()) {
                $smtp->smtpClose();
                return [
                    'success' => true,
                    'message' => 'SMTP connection successful'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to SMTP server'
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function get_analytics($filters = [])
    {
        throw new \Exception('Analytics not supported for SMTP Connections');
    }
}