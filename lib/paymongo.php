<?php
/**
 * Minimal PayMongo API client for QR Ph ("scan to pay") payments.
 * Docs: https://docs.paymongo.com/docs/payment-acceptance-qr-ph-api
 *
 * Every call here runs server-side using the secret key, so the key
 * never touches the customer's browser.
 */

require_once __DIR__ . '/../config/paymongo.php';

/**
 * Low-level request helper. Returns ['ok' => bool, 'http_code' => int, 'body' => array].
 */
function paymongo_request(string $method, string $path, ?array $body = null): array
{
    $ch = curl_init('https://api.paymongo.com/v1' . $path);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 20,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('PayMongo request failed: ' . $curlError);
        return ['ok' => false, 'http_code' => 0, 'body' => ['error' => $curlError]];
    }

    $decoded = json_decode($response, true) ?? [];

    return [
        'ok'        => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'body'      => $decoded,
    ];
}

/**
 * Step 1: create a Payment Intent for the given amount (in PHP pesos,
 * converted to centavos here) with QR Ph as the allowed method.
 */
function paymongo_create_payment_intent(float $amountPesos, string $description): array
{
    return paymongo_request('POST', '/payment_intents', [
        'data' => [
            'attributes' => [
                'amount'                 => (int) round($amountPesos * 100),
                'currency'               => PAYMONGO_CURRENCY,
                'payment_method_allowed' => ['qrph'],
                'description'            => $description,
            ],
        ],
    ]);
}

/**
 * Step 2: create a QR Ph payment method.
 */
function paymongo_create_qrph_payment_method(): array
{
    return paymongo_request('POST', '/payment_methods', [
        'data' => [
            'attributes' => [
                'type'           => 'qrph',
                'expiry_seconds' => PAYMONGO_QR_EXPIRY_SECONDS,
            ],
        ],
    ]);
}

/**
 * Step 3: attach the payment method to the intent. On success the
 * response contains next_action.code.image_url — the QR code to show.
 */
function paymongo_attach_payment_method(string $intentId, string $paymentMethodId, string $clientKey): array
{
    return paymongo_request('POST', "/payment_intents/{$intentId}/attach", [
        'data' => [
            'attributes' => [
                'payment_method' => $paymentMethodId,
                'client_key'     => $clientKey,
            ],
        ],
    ]);
}

/**
 * Poll the current status of a Payment Intent.
 * Status moves: awaiting_payment_method -> awaiting_next_action -> succeeded
 * (or back to awaiting_payment_method if the QR expires/fails).
 */
function paymongo_retrieve_payment_intent(string $intentId): array
{
    return paymongo_request('GET', "/payment_intents/{$intentId}");
}

/**
 * Convenience: runs the full create-intent -> create-method -> attach
 * flow in one call. Returns an array with either 'error' set, or the
 * intent id, client_key, and the QR image data URI.
 */
function paymongo_generate_qr(float $amountPesos, string $description): array
{
    $intentResult = paymongo_create_payment_intent($amountPesos, $description);
    if (!$intentResult['ok']) {
        return ['error' => paymongo_extract_error($intentResult)];
    }
    $intentId  = $intentResult['body']['data']['id'] ?? null;
    $clientKey = $intentResult['body']['data']['attributes']['client_key'] ?? null;

    if (!$intentId || !$clientKey) {
        return ['error' => 'PayMongo did not return a payment intent id.'];
    }

    $methodResult = paymongo_create_qrph_payment_method();
    if (!$methodResult['ok']) {
        return ['error' => paymongo_extract_error($methodResult)];
    }
    $paymentMethodId = $methodResult['body']['data']['id'] ?? null;

    if (!$paymentMethodId) {
        return ['error' => 'PayMongo did not return a payment method id.'];
    }

    $attachResult = paymongo_attach_payment_method($intentId, $paymentMethodId, $clientKey);
    if (!$attachResult['ok']) {
        return ['error' => paymongo_extract_error($attachResult)];
    }

    $imageUrl = $attachResult['body']['data']['attributes']['next_action']['code']['image_url'] ?? null;

    if (!$imageUrl) {
        return ['error' => 'PayMongo did not return a QR code image.'];
    }

    return [
        'intent_id'  => $intentId,
        'client_key' => $clientKey,
        'image_url'  => $imageUrl,
    ];
}

function paymongo_extract_error(array $result): string
{
    $message = $result['body']['errors'][0]['detail'] ?? null;
    if ($message) {
        return $message;
    }
    return 'PayMongo request failed (HTTP ' . $result['http_code'] . ').';
}