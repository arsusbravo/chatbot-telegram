<?php

return [
    'wallets' => [
        [
            'network' => 'TRC-20 (Tron)',
            'currency' => 'USDT',
            'address' => env('WALLET_TRC20', ''),
        ],
        [
            'network' => 'BEP-20 (BSC)',
            'currency' => 'USDT',
            'address' => env('WALLET_BEP20', ''),
        ],
    ],

    'packages' => [
        [
            'name' => 'Starter',
            'credits' => 50,
            'price' => 2,
            'currency' => 'USDT',
        ],
        [
            'name' => 'Regular',
            'credits' => 200,
            'price' => 5,
            'currency' => 'USDT',
            'popular' => true,
        ],
        [
            'name' => 'Sultan',
            'credits' => 1000,
            'price' => 15,
            'currency' => 'USDT',
        ],
    ],
];