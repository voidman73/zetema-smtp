<?php

namespace TurboSMTP\ProMailSMTP\Providers;
if ( ! defined( 'ABSPATH' ) ) exit;

class Zimbra extends OtherSMTP {

    public function __construct($credentials = []) {
        $defaults = [
            'smtp_host'            => 'smtp.zimbra.com',
            'smtp_port'            => 587,
            'smtp_encryption'      => 'tls',
            'smtp_user'            => '',
            'smtp_pw'              => '',
            'email_from_overwrite' => '',
        ];

        parent::__construct(array_merge($defaults, $credentials ?? []));
    }
}
