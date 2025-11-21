<?php
namespace TurboSMTP\ProMailSMTP\Email;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class EmailFormatterService
 * Handles email formatting and processing for the Zetema SMTP plugin.
 */
class EmailFormatterService
{
    /**
     * Format email data for sending
     *
     * @param array $data The raw email data to format
     * @return array The formatted email data
     */
    public function format($data)
    {
       $formatted = $this->prepareEmailData($data);
        return $formatted;
    }

    /**
     * Prepare email data for processing
     *
     * @param array $args Array of email arguments including to, subject, message, headers, and attachments
     * @return array Processed email data with standardized format
     */
    private function prepareEmailData($args)
    {
        $to = is_array($args['to']) ? $args['to'] : [$args['to']];
        $headers = $this->parse_headers($args['headers']);
        $result =  [
            'to' => $to,
            'subject' => $args['subject'],
            'message' => $args['message'],
            'from_email' =>  isset($headers['from_email']) ? $headers['from_email'] : get_option('pro_mail_smtp_from_email', get_option('admin_email')) ,
            'from_name' => isset($headers['from_name']) ? $headers['from_name'] : get_option('pro_mail_smtp_from_name', get_bloginfo('name')),
            'reply_to' => $headers['reply_to'] ?? '',
            'cc' => $headers['cc'] ?? [],
            'bcc' => $headers['bcc'] ?? [],
            'attachments' => $this->prepare_attachments($args['attachments'])
        ];
        return $result;
    }

    /**
     * Parse email headers into a structured array
     *
     * @param string|array $headers Raw headers either as string or array
     * @return array Parsed headers with standardized keys
     */
    private function parse_headers($headers)
    {
        $parsed_headers = [];
        if (empty($headers)) {
            return $parsed_headers;
        }

        if (!is_array($headers)) {
            $headers = explode("\n", str_replace("\r\n", "\n", $headers));
        }
        foreach ($headers as $header) {
            if (strpos($header, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', trim($header), 2);
            $name = strtolower(trim($name));
            $value = trim($value);

            switch ($name) {
                case 'from':
                    $parsed_headers['from_email'] = $this->extract_email($value);
                    $parsed_headers['from_name'] = $this->extract_name($value);
                    break;
                case 'reply-to':
                    $parsed_headers['reply_to'] = $this->extract_email($value);
                    break;
                case 'cc':
                    $parsed_headers['cc'] = $this->extract_addresses($value);
                    break;
                case 'bcc':
                    $parsed_headers['bcc'] = $this->extract_addresses($value);
                    break;
                default:
                    // Ignore other headers
                    break;
            }
        }

        return $parsed_headers;
    }

    /**
     * Prepare email attachments for sending
     *
     * @param string|array $attachments Single attachment path or array of attachment paths
     * @return array Array of prepared attachment data including path, name, size, type, and base64 content
     */
    private function prepare_attachments($attachments) {
        if (empty($attachments)) {
            return [];
        }
    
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }
    
        $prepared_attachments = [];

        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $prepared_attachments[] = [
                    'path' => $attachment,
                    'name' => basename($attachment),
                    'size' => filesize($attachment),
                    'type' => mime_content_type($attachment),
                    'content' => base64_encode(file_get_contents($attachment))
                ];
            }
        }
    
        return $prepared_attachments;
    }

    /**
     * Extract email address from a formatted string
     *
     * @param string $string String potentially containing email in format "Name <email@domain.com>"
     * @return string Extracted email address
     */
    private function extract_email($string) {
        if (preg_match('/<(.+)>/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }

    /**
     * Extract name from a formatted email string
     *
     * @param string $string String potentially containing name in format "Name <email@domain.com>"
     * @return string Extracted name or empty string if not found
     */
    private function extract_name($string) {
        if (preg_match('/(.+)<.+>/', $string, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Extract multiple email addresses from a comma-separated string
     *
     * @param string $string Comma-separated list of email addresses
     * @return array Array of trimmed email addresses
     */
    private function extract_addresses($string) {
        $addresses = explode(',', $string);
        return array_map('trim', $addresses);
    }
}
