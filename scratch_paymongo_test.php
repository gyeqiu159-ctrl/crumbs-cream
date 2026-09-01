<?php
require_once __DIR__ . '/config/paymongo.php';

function request($method, $path, $body) {
    $ch = curl_init('https://api.paymongo.com/v1' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $res = curl_exec($ch);
    return json_decode($res, true);
}

// 1. Create PI
$pi = request('POST', '/payment_intents', [
    'data' => [
        'attributes' => [
            'amount' => 10000,
            'currency' => 'PHP',
            'payment_method_allowed' => ['card']
        ]
    ]
]);
$piId = $pi['data']['id'];
$clientKey = $pi['data']['attributes']['client_key'];

// 2. Create PM
$pm = request('POST', '/payment_methods', [
    'data' => [
        'attributes' => [
            'type' => 'card',
            'details' => [
                'card_number' => '4111111111111111',
                'exp_month' => 12,
                'exp_year' => 2030,
                'cvc' => '123'
            ]
        ]
    ]
]);
$pmId = $pm['data']['id'];

// 3. Attach PM
$attach = request('POST', "/payment_intents/{$piId}/attach", [
    'data' => [
        'attributes' => [
            'payment_method' => $pmId,
            'client_key' => $clientKey,
            'return_url' => 'http://localhost/success'
        ]
    ]
]);

echo "Status after attach: " . ($attach['data']['attributes']['status'] ?? 'unknown') . "\n";
print_r($attach);
