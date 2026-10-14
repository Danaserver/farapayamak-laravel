<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "rest", "soap", "log"
    |
    */
    'default' => env('FARAPAYAMAK_DRIVER', 'rest'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode (Testing without real SMS cost)
    |--------------------------------------------------------------------------
    |
    | When set to true, SMS requests are not sent to Farapayamak servers,
    | but logged directly to your Laravel log file.
    |
    */
    'sandbox' => env('FARAPAYAMAK_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Farapayamak Default Credentials
    |--------------------------------------------------------------------------
    |
    | Provide your panel username, API Key/Password, and default sender number.
    |
    */
    'username' => env('FARAPAYAMAK_USERNAME', ''),
    'password' => env('FARAPAYAMAK_PASSWORD', ''),
    'from'     => env('FARAPAYAMAK_FROM', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum connection and response wait time in seconds.
    |
    */
    'timeout' => env('FARAPAYAMAK_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Failover (Automatic REST to SOAP fallback)
    |--------------------------------------------------------------------------
    |
    | If enabled, upon any connection failure with REST API, the client will
    | automatically retry sending through SOAP Web Service.
    |
    */
    'failover' => env('FARAPAYAMAK_FAILOVER', false),

    /*
    |--------------------------------------------------------------------------
    | Registered Service Patterns (الگوهای خدماتی / خط خدماتی اشتراکی)
    |--------------------------------------------------------------------------
    |
    | Define your registered approved template IDs (BodyId) with readable aliases.
    | You can also list and inspect them using: php artisan farapayamak:patterns
    |
    */
    'patterns' => [
        'otp' => [
            'id' => env('FARAPAYAMAK_PATTERN_OTP', 0),
            'title' => 'کد تایید ورود و ثبت‌نام (OTP)',
            'vars' => ['code'],
        ],
        'welcome' => [
            'id' => env('FARAPAYAMAK_PATTERN_WELCOME', 0),
            'title' => 'پیامک خوش‌آمدگویی',
            'vars' => ['name'],
        ],
        'order_tracking' => [
            'id' => env('FARAPAYAMAK_PATTERN_TRACKING', 0),
            'title' => 'کد رهگیری و وضعیت سفارش',
            'vars' => ['name', 'tracking_code'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | REST API Configuration
    |--------------------------------------------------------------------------
    */
    'rest' => [
        'base_url' => env('FARAPAYAMAK_REST_URL', 'https://rest.payamak-panel.com/api/SendSMS/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SOAP Web Service URLs
    |--------------------------------------------------------------------------
    */
    'soap' => [
        'send'      => 'http://api.payamak-panel.com/post/send.asmx?wsdl',
        'receive'   => 'http://api.payamak-panel.com/post/receive.asmx?wsdl',
        'schedule'  => 'http://api.payamak-panel.com/post/schedule.asmx?wsdl',
        'voice'     => 'http://api.payamak-panel.com/post/voice.asmx?wsdl',
        'contacts'  => 'http://api.payamak-panel.com/post/contacts.asmx?wsdl',
        'users'     => 'http://api.payamak-panel.com/post/users.asmx?wsdl',
        'tickets'   => 'http://api.payamak-panel.com/post/tickets.asmx?wsdl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Account Configuration (Optional)
    |--------------------------------------------------------------------------
    |
    | Define extra credentials if you have multiple panel accounts or lines:
    | Farapayamak::account('marketing')->send(...)
    |
    */
    'accounts' => [
        // 'marketing' => [
        //     'username' => 'marketing_user',
        //     'password' => 'secret_key',
        //     'from'     => '5000...',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Incoming Webhook & Delivery Reports
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'enabled' => env('FARAPAYAMAK_WEBHOOK_ENABLED', false),
        'route'   => env('FARAPAYAMAK_WEBHOOK_ROUTE', 'farapayamak/webhook'),
        'secret'  => env('FARAPAYAMAK_WEBHOOK_SECRET', ''),
    ],
];
