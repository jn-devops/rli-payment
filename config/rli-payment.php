<?php

return [
    'aub_paymate' => [
        'server' => [
            'api_url' => env('RLI_PAYMENT_API_URL'),
        ],
        'client' => [
            'body' => env('RLI_PAYMENT_BODY'),
            'device_info' => env('RLI_PAYMENT_DEVICE_INFO'),
            'mch_id' => env('RLI_PAYMENT_MCH_ID'),
            'notify_url' => env('RLI_PAYMENT_NOTIFY_URL'),
            'service' => env('RLI_PAYMENT_SERVICE'),
            'sign_type' => env('RLI_PAYMENT_SIGN_TYPE'),
        ],
        'api' => [
            'key' => env('RLI_PAYMENT_KEY'),
        ],
    ],
    'webhook' => env('RLI_PAYMENT_WEBHOOK'),
];
