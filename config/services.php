<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Services
    |--------------------------------------------------------------------------
    |
    | Configuration for payment service providers including MPesa and Bank APIs
    |
    */

    'mpesa' => [
        'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
        'paybill_number' => env('MPESA_PAYBILL_NUMBER', '174379'),
        'consumer_key' => env('MPESA_CONSUMER_KEY', '69nVvO9gedEc5w4jPSKqPKpvrqQb0vVWtqs0NVnqG6IIMOXG'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET', 'y4ZgG2GkPgk2mfBGcdwCSBExGvWNQMh3iK0Y2C9r8BGBx2uzjFO6cz9mZ1MP6Ix8'),
        'passkey' => env('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
        'shortcode' => env('MPESA_SHORTCODE', '174379'),
        'initiator_name' => env('MPESA_INITIATOR_NAME', 'testapi'),
        'security_credential' => env('MPESA_SECURITY_CREDENTIAL', 'MuNAGAdaFX5j8T2m0lUDE2aGa+J+fxh0W9+A+SUzFUcup5F8emQIMuA6ywFVlH9m5uRgK
                                                                 fW5v6OHMgSpztg/bq9WytKwPffLxr4XH/VBKLv2gEpYonf4Wwo5YB1SiWRuRPxjfX8J8sGHl
                                                                 RZ3FR5PShnG67tH8aHHHlqpgOjxmsOMe69XpJ5zlkEOdfpCGjFDUm+RhH1rZRET1605DvzKPO
                                                                 P6g+fPDo4vUh5Xhn9Mzg69842XqOq3HFjOPS5AkMw2BYnEoLB+/3kIy1+Fm7deEkUSEEdbHvmn
                                                                 IiXFwK9QJ1dIDSUpW7H2pxPnvRQNxrDQ8AvtaaS3nFTp1B9X1mpQ0Q=='),
        'callback_url' => env('MPESA_CALLBACK_URL', 'https://afflictionless-speedfully-claudie.ngrok-free.dev/webhook/mpesa-payment'),
    ],

    'bank' => [
        'api_url' => env('BANK_API_URL'),
        'api_key' => env('BANK_API_KEY'),
        'account_number' => env('BANK_ACCOUNT_NUMBER'),
    ],

];