<?php

namespace TurboSMTP\ProMailSMTP\Core;
if ( ! defined( 'ABSPATH' ) ) exit;


class ImportConnections
{
    private $wpmailOption;
    private $easysmtpOption;
    private $providerManager;

        public function __construct() {
            $this->wpmailOption = get_option('wp_mail_smtp');
            $this->easysmtpOption = get_option('swpsmtp_options');
            $this->providerManager = new ProviderManager();
        }

    public function isImportAvailable()
    {
        return [
            'wpMail' => $this->isWpMailSMTPAvailable(),
            'easySMTP' => $this->isEasySMTPAvailable(),
        ];
    }

    private function isWpMailSMTPAvailable()
    {
        $wpMailSMTP = $this->wpmailOption;
        if (!$wpMailSMTP) {
            $this->dismissNotice('pro_mail_smtp_import_wpmail_notice_dismissed');
            return false;
        }
        return true;
    }

    private function isEasySMTPAvailable()
    {
        $easySMTP = $this->easysmtpOption;

        if (!$easySMTP || !is_array($easySMTP)) {
            $this->dismissNotice('pro_mail_smtp_import_easysmtp_notice_dismissed');
            return false;
        }
        return true;
    }

    private function dismissNotice($optionName)
    {
        update_option($optionName, true);
    }

    public function importProviders($plugin){
        
        if ($plugin == 'wpMail') {
          return  $this->importProviderData($this->wpmailOption);
        }

        if ($plugin == 'easySMTP') {
         return   $this->importProviderData($this->easysmtpOption);
        }
    }


    private function importProviderData($info)
    {
        try{
            foreach ($info as $key => $value) {
                switch($key){
                    case 'smtp':
                        $this->importOtherProvider($value);
                        break;
                    case 'mailgun':
                        $this->importMailgunProvider($value);
                        break;
                    case 'smtp2go':
                        $this->importSMTP2GOProvider($value);
                        break;
                    case 'sendgrid':
                        $this->importSendGridProvider($value);
                        break;
                    case 'postmark':
                        $this->importPostmarkProvider($value);
                        break;
                    case 'sparkpost':
                        $this->importSparkpostProvider($value);
                        break;
                    case 'gmail':
                        $this->importGmailProvider($value);
                        break;
                    case 'outlook':
                        $this->importOutlookProvider($value);
                        break;
                    default:
                    break;
                }
            }
            return 'Imported Successfully';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
    

    private function importOtherProvider($data)
    {
        if ($data['host'] == '' || $data['user'] == '' || $data['pass'] == '') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        
        $providerData = [
            'provider' => 'other',
            'connection_id' => '',
            'connection_label' => 'Imported Other SMTP',
            'priority' => $available_priority[0],
            'config_keys' => [
                'smtp_host' => $data['host'],
                'smtp_port' => $data['port'],
                'smtp_encryption' => $data['encryption'],
                'smtp_user' => $data['user'],
                'smtp_pw' => $this->decodeWpMailPassword($data['pass'])
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }


    private function decodeWpMailPassword($encrypted)
    {
        if (apply_filters('wp_mail_smtp_helpers_crypto_stop', false) ||
            !function_exists('\mb_strlen') || 
            !function_exists('\mb_substr') || 
            !function_exists('\sodium_crypto_secretbox_open')) {
            return $encrypted;
        }

        $decoded = base64_decode($encrypted);
        if (false === $decoded) {
            return $encrypted;
        }

        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            return $encrypted;
        }

        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        
        $secret_key = $this->getWpMailSecretKey();
        if (empty($secret_key)) {
            return $encrypted;
        }

        $message = sodium_crypto_secretbox_open($ciphertext, $nonce, $secret_key);
        return $message !== false ? $message : $encrypted;
    }

    private function getWpMailSecretKey()
    {
        if (defined('WPMS_CRYPTO_KEY')) {
            return WPMS_CRYPTO_KEY;
        }

        $secret_key = get_option('wp_mail_smtp_mail_key');
        $secret_key = apply_filters('wp_mail_smtp_helpers_crypto_get_secret_key', $secret_key);
        
        if (false !== $secret_key) {
            $secret_key = base64_decode($secret_key);
        }

        return $secret_key;
    }

    private function importMailgunProvider($data)
    {
        if ($data['domain'] == '' || $data['api_key']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'mailgun',
            'connection_id' => '',
            'connection_label' => 'Imported Mailgun',
            'priority' => $available_priority[0],
            'config_keys' => [
                'domain' => $data['domain'],
                'api_key' => $data['api_key'],
                'region' => $data['region'] ?? 'us'
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    private function importSMTP2GOProvider($data)
    {
        if ($data['api_key'] == '')  {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'smtp2go',
            'connection_id' => '',
            'connection_label' => 'Imported SMTP2GO',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['api_key']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    private function importSendGridProvider($data)
    {
        if ($data['api_key'] == '')  {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'sendgrid',
            'connection_id' => '',
            'connection_label' => 'Imported SendGrid',
            'priority' => $available_priority[0],
            'config_keys' => [
                'sendgrid_key' => $data['api_key']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
    private function importPostmarkProvider($data)
    {
        if ($data['server_api_token'] == '') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'postmark',
            'connection_id' => '',
            'connection_label' => 'Imported Postmark',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['server_api_token']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
    private function importSparkpostProvider($data)
    {
        if ($data['api_key']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'sparkpost',
            'connection_id' => '',
            'connection_label' => 'Imported Sparkpost',
            'priority' => $available_priority[0],
            'config_keys' => [
                'api_key' => $data['api_key'],
                'region' => $data['region'] ?? 'us'
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
    private function importGmailProvider($data)
    {
        if ($data['client_secret']=='' || $data['client_id']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'gmail',
            'connection_id' => '',
            'connection_label' => 'Imported Gmail',
            'priority' => $available_priority[0],
            'config_keys' => [
                'client_id' => $data['client_id'],
                'client_secret' => $data['client_secret']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }

    private function importOutlookProvider($data)
    {
        if ($data['client_secret']=='' || $data['client_id']=='') {
            return;
        }
        $available_priority = $this->providerManager->get_available_priority();
        $providerData = [
            'provider' => 'outlook',
            'connection_id' => '',
            'connection_label' => 'Imported Outlook',
            'priority' => $available_priority[0],
            'config_keys' => [
                'client_id' => $data['client_id'],
                'client_secret' => $data['client_secret']
            ]
        ];
        $this->providerManager->save_provider($providerData);
    }
}