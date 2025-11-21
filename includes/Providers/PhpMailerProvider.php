<?php
namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PhpMailerProvider - WordPress wp_mail() style implementation
 * 
 * This class mimics the WordPress wp_mail() function implementation
 * providing the same API and functionality as the core WordPress mail function.
 */
class PhpMailerProvider {

    /**
     * Send email using wp_mail() style implementation with PHPMailer
     * Main interface method that works with both array input and wp_mail parameters
     *
     * @param array|string $email_data Can be array of email data or the 'to' parameter for wp_mail style
     * @param string $subject Optional. Email subject (for wp_mail style calls).
     * @param string $message Optional. Message contents (for wp_mail style calls).
     * @param string|string[] $headers Optional. Additional headers (for wp_mail style calls).
     * @param string|string[] $attachments Optional. Paths to files to attach (for wp_mail style calls).
     * @return array|bool Returns array with message_id on success, false on failure.
     */
    public function send( $email_data, $subject = '', $message = '', $headers = '', $attachments = array() ) {
        // Handle both array input (formatted email data) and wp_mail style parameters
        if ( is_array( $email_data ) ) {
            // Array input - formatted email data from EmailFormatterService
            $result = $this->send_formatted_email( $email_data );
        } else {
            // wp_mail style parameters
            $result = $this->send_wp_mail_style( $email_data, $subject, $message, $headers, $attachments );
        }
        
        return $result;
    }

    /**
     * Send email with formatted email data (from EmailFormatterService)
     *
     * @param array $email_data Formatted email data array
     * @return array|bool Returns array with message_id on success, false on failure.
     */
    private function send_formatted_email( $email_data ) {
        // Convert formatted email data to wp_mail style parameters
        $to = $email_data['to'] ?? '';
        $subject = $email_data['subject'] ?? '';
        $message = $email_data['message'] ?? '';
        
        // Build headers from formatted data
        $headers = array();
        
        if ( !empty( $email_data['from_email'] ) || !empty( $email_data['from_name'] ) ) {
            $from_email = $email_data['from_email'] ?? '';
            $from_name = $email_data['from_name'] ?? '';
            
            if ( !empty( $from_name ) && !empty( $from_email ) ) {
                $headers[] = sprintf( 'From: %s <%s>', $from_name, $from_email );
            } elseif ( !empty( $from_email ) ) {
                $headers[] = sprintf( 'From: %s', $from_email );
            }
        }
        
        if ( !empty( $email_data['reply_to'] ) ) {
            $headers[] = sprintf( 'Reply-To: %s', $email_data['reply_to'] );
        }
        
        if ( !empty( $email_data['cc'] ) ) {
            foreach ( (array) $email_data['cc'] as $cc_email ) {
                $headers[] = sprintf( 'Cc: %s', $cc_email );
            }
        }
        
        if ( !empty( $email_data['bcc'] ) ) {
            foreach ( (array) $email_data['bcc'] as $bcc_email ) {
                $headers[] = sprintf( 'Bcc: %s', $bcc_email );
            }
        }
        
        // Handle attachments - convert from formatted data to file paths
        $attachments = array();
        if ( !empty( $email_data['attachments'] ) ) {
            foreach ( $email_data['attachments'] as $attachment ) {
                if ( isset( $attachment['path'] ) ) {
                    $attachments[] = $attachment['path'];
                }
            }
        }
        
        return $this->send_wp_mail_style( $to, $subject, $message, $headers, $attachments );
    }

    /**
     * Send email using wp_mail() style implementation with PHPMailer
     *
     * @param string|string[] $to Array or comma-separated list of email addresses to send message.
     * @param string $subject Email subject.
     * @param string $message Message contents.
     * @param string|string[] $headers Optional. Additional headers.
     * @param string|string[] $attachments Optional. Paths to files to attach.
     * @return array|bool Returns array with message_id on success, false on failure.
     */
    private function send_wp_mail_style( $to, $subject, $message, $headers = '', $attachments = array() ) {
        // Compact the input, apply the filters, and extract them back out.
        
        /**
         * Filters the pro_mail_smtp arguments.
         *
         * @param array $args Array of the pro_mail_smtp arguments.
         */
        $atts = \apply_filters( 'pro_mail_smtp_phpmailer', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

        /**
         * Filters whether to preempt sending an email.
         *
         * @param null|bool $return Short-circuit return value.
         * @param array     $atts Array of the pro_mail_smtp arguments.
         */
        $pre_mail = \apply_filters( 'pre_pro_mail_smtp_phpmailer', null, $atts );

        if ( null !== $pre_mail ) {
            return $pre_mail ? ['message_id' => 'pre_filtered', 'provider_response' => 'short_circuited'] : false;
        }

        // Extract arguments
        if ( isset( $atts['to'] ) ) {
            $to = $atts['to'];
        }

        if ( ! is_array( $to ) ) {
            $to = explode( ',', $to );
        }

        if ( isset( $atts['subject'] ) ) {
            $subject = $atts['subject'];
        }

        if ( isset( $atts['message'] ) ) {
            $message = $atts['message'];
        }

        if ( isset( $atts['headers'] ) ) {
            $headers = $atts['headers'];
        }

        if ( isset( $atts['attachments'] ) ) {
            $attachments = $atts['attachments'];
        }

        if ( ! is_array( $attachments ) ) {
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
        }

        // Basic validation
        if ( empty( $to ) ) {
            return false;
        }
        
        if ( empty( $subject ) && empty( $message ) ) {
            return false;
        }

        // Get WordPress PHPMailer instance - CORRECTED APPROACH
        $phpmailer = $this->get_wordpress_phpmailer();
        
        if ( ! $phpmailer ) {
            return false;
        }

        // Set email validator like WordPress does
        if ( method_exists( $phpmailer, 'setValidateAddress' ) ) {
            $phpmailer->setValidateAddress( function( $email ) {
                return (bool) \is_email( $email );
            });
        } else {
            // For older PHPMailer versions
            $phpmailer::$validator = static function ( $email ) {
                return (bool) \is_email( $email );
            };
        }

        // Headers processing
        $cc         = array();
        $bcc        = array();
        $reply_to   = array();
        $from_email = '';
        $from_name  = '';
        $content_type = '';
        $charset    = '';
        $boundary   = '';

        if ( empty( $headers ) ) {
            $headers = array();
        } else {
            if ( ! is_array( $headers ) ) {
                // Explode the headers out, so this function can take both string headers and an array of headers.
                $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents.
            if ( ! empty( $tempheaders ) ) {
                // Iterate through the raw headers.
                foreach ( (array) $tempheaders as $header ) {
                    if ( false === strpos( $header, ':' ) ) {
                        if ( false !== stripos( $header, 'boundary=' ) ) {
                            $parts    = preg_split( '/boundary=/i', trim( $header ) );
                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                        }
                        continue;
                    }
                    // Explode them out.
                    list( $name, $content ) = explode( ':', trim( $header ), 2 );

                    // Cleanup crew.
                    $name    = trim( $name );
                    $content = trim( $content );

                    switch ( strtolower( $name ) ) {
                        // Mainly for legacy -- process a "From:" header if it's there.
                        case 'from':
                            $bracket_pos = strpos( $content, '<' );
                            if ( false !== $bracket_pos ) {
                                // Text before the bracketed email is the "From" name.
                                if ( $bracket_pos > 0 ) {
                                    $from_name = substr( $content, 0, $bracket_pos );
                                    $from_name = str_replace( '"', '', $from_name );
                                    $from_name = trim( $from_name );
                                }

                                $from_email = substr( $content, $bracket_pos + 1 );
                                $from_email = str_replace( '>', '', $from_email );
                                $from_email = trim( $from_email );

                                // Avoid setting an empty $from_email.
                            } elseif ( '' !== trim( $content ) ) {
                                $from_email = trim( $content );
                            }
                            break;
                        case 'content-type':
                            if ( false !== strpos( $content, ';' ) ) {
                                list( $type, $charset_content ) = explode( ';', $content );
                                $content_type                   = trim( $type );
                                if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                    $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                    $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                    $charset  = '';
                                }

                                // Avoid setting an empty $content_type.
                            } elseif ( '' !== trim( $content ) ) {
                                $content_type = trim( $content );
                            }
                            break;
                        case 'cc':
                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                            break;
                        case 'bcc':
                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                            break;
                        case 'reply-to':
                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                            break;
                        default:
                            // Add it to our grand headers array.
                            $headers[ trim( $name ) ] = trim( $content );
                            break;
                    }
                }
            }
        }

        // Empty out the values that may be set.
        $phpmailer->clearAllRecipients();
        $phpmailer->clearAttachments();
        $phpmailer->clearCustomHeaders();
        $phpmailer->clearReplyTos();
        $phpmailer->Body    = '';
        $phpmailer->AltBody = '';

        // Set "From" name and email.

        // If we don't have a name from the input headers.
        if ( empty( $from_name ) ) {
            $from_name = 'WordPress';
        }

        /*
         * If we don't have an email from the input headers, default to wordpress@$sitename
         * Some hosts will block outgoing mail from this address if it doesn't exist,
         * but there's no easy alternative. Defaulting to admin_email might appear to be
         * another option, but some hosts may refuse to relay mail from an unknown domain.
         * See https://core.trac.wordpress.org/ticket/5007.
         */
        if ( empty( $from_email ) ) {
            // Get the site domain and get rid of www.
            $sitename   = \wp_parse_url( \network_home_url(), PHP_URL_HOST );
            $from_email = 'wordpress@';

            if ( null !== $sitename ) {
                if ( 0 === strpos( $sitename, 'www.' ) ) {
                    $sitename = substr( $sitename, 4 );
                }

                $from_email .= $sitename;
            }
        }

        /**
         * Filters the email address to send from.
         *
         * @param string $from_email Email address to send from.
         */
        $from_email = \apply_filters( 'pro_mail_smtp_phpmailer_from', $from_email );

        /**
         * Filters the name to associate with the "from" email address.
         *
         * @param string $from_name Name associated with the "from" email address.
         */
        $from_name = \apply_filters( 'pro_mail_smtp_phpmailer_from_name', $from_name );

        try {
            $phpmailer->setFrom( $from_email, $from_name, false );
        } catch ( \PHPMailer\PHPMailer\Exception $e ) {
            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This action is documented below */
            \do_action( 'pro_mail_smtp_phpmailer_failed', new \WP_Error( 'phpmailer_failed', $e->getMessage(), $mail_error_data ) );

            return false;
        }

        // Set mail's subject and body.
        $phpmailer->Subject = $subject;
        $phpmailer->Body    = $message;

        // Set destination addresses, using appropriate methods for handling addresses.
        $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

        foreach ( $address_headers as $address_header => $addresses ) {
            if ( empty( $addresses ) ) {
                continue;
            }

            foreach ( (array) $addresses as $address ) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                    $recipient_name = '';

                    if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                        if ( count( $matches ) === 3 ) {
                            $recipient_name = $matches[1];
                            $address        = $matches[2];
                        }
                    }

                    switch ( $address_header ) {
                        case 'to':
                            $phpmailer->addAddress( $address, $recipient_name );
                            break;
                        case 'cc':
                            $phpmailer->addCc( $address, $recipient_name );
                            break;
                        case 'bcc':
                            $phpmailer->addBcc( $address, $recipient_name );
                            break;
                        case 'reply_to':
                            $phpmailer->addReplyTo( $address, $recipient_name );
                            break;
                    }
                } catch ( \PHPMailer\PHPMailer\Exception $e ) {
                    continue;
                }
            }
        }

        // Set to use PHP's mail().
        $phpmailer->isMail();

        // Set Content-Type and charset.

        // If we don't have a Content-Type from the input headers.
        if ( empty( $content_type ) ) {
            $content_type = 'text/plain';
        }

        /**
         * Filters the pro_mail_smtp content type.
         *
         * @param string $content_type Default content type.
         */
        $content_type = \apply_filters( 'pro_mail_smtp_phpmailer_content_type', $content_type );

        $phpmailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type.
        if ( 'text/html' === $content_type ) {
            $phpmailer->isHTML( true );
        }

        // If we don't have a charset from the input headers.
        if ( empty( $charset ) ) {
            $charset = \get_bloginfo( 'charset' );
        }

        /**
         * Filters the default charset.
         *
         * @param string $charset Default email charset.
         */
        $phpmailer->CharSet = \apply_filters( 'pro_mail_smtp_phpmailer_charset', $charset );

        // Set custom headers.
        if ( ! empty( $headers ) ) {
            foreach ( (array) $headers as $name => $content ) {
                // Only add custom headers not added automatically by PHPMailer.
                if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ), true ) ) {
                    try {
                        $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                    } catch ( \PHPMailer\PHPMailer\Exception $e ) {
                        continue;
                    }
                }
            }

            if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
                $phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
            }
        }

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $filename => $attachment ) {
                $filename = is_string( $filename ) ? $filename : '';

                try {
                    $phpmailer->addAttachment( $attachment, $filename );
                } catch ( \PHPMailer\PHPMailer\Exception $e ) {
                    continue;
                }
            }
        }

        /**
         * Fires after PHPMailer is initialized.
         *
         * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
         */
        \do_action_ref_array( 'pro_mail_smtp_phpmailer_init', array( &$phpmailer ) );

        $mail_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );

        // Send!
        try {
            $send = $phpmailer->send();

            /**
             * Fires after PHPMailer has successfully sent an email.
             *
             * @param array $mail_data Array containing the email recipient(s), subject, message, headers, and attachments.
             */
            \do_action( 'pro_mail_smtp_phpmailer_succeeded', $mail_data );

            return $send ? [
                'message_id' => $phpmailer->getLastMessageID() ?: 'phpmailer_' . time(),
                'provider_response' => [
                    'success' => true,
                    'to' => $to,
                    'subject' => $subject
                ]
            ] : false;
        } catch ( \PHPMailer\PHPMailer\Exception $e ) {
            $mail_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a PHPMailer Exception is caught.
             *
             * @param \WP_Error $error A WP_Error object with the PHPMailer Exception message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            \do_action( 'pro_mail_smtp_phpmailer_failed', new \WP_Error( 'phpmailer_failed', $e->getMessage(), $mail_data ) );

            return false;
        }
    }

    /**
     * Get WordPress PHPMailer instance - the reliable way
     * CORRECTED: This replaces the problematic PHPMailer creation code
     *
     * @return \PHPMailer\PHPMailer\PHPMailer|false PHPMailer instance or false on failure
     */
    private function get_wordpress_phpmailer() {
        // Load WordPress PHPMailer files
        if ( ! class_exists( 'PHPMailer\\PHPMailer\\PHPMailer' ) ) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        }
        
        try {
            $phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );
            return $phpmailer;
        } catch ( \Exception $e ) {
            return false;
        }
    }
}