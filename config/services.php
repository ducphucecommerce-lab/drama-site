<?php
return [
    'api_drama' => [
        'key'  => env('API_DRAMA_KEY'),
        'base' => env('API_DRAMA_BASE', 'https://api-drama.dobda.id'),
    ],
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'vnpay' => [
        'tmn_code'   => env('VNPAY_TMN_CODE'),
        'hash_secret'=> env('VNPAY_HASH_SECRET'),
        'url'        => env('VNPAY_URL', 'https://pay.vnpay.vn/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL'),
    ],
    'vip' => [
        'price_vnd'    => env('VIP_PRICE_VND', 99000),
        'price_usd'    => env('VIP_PRICE_USD', 5),
        'duration_days'=> env('VIP_DURATION_DAYS', 30),
    ],
];
