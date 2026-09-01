<?php
/**
 * Polled by pay.php every few seconds. Checks the live status with
 * PayMongo and updates our own orders row when it changes.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/lib/paymongo.php';

$orderId = (int) ($_GET['order_id'] ?? 0);
$pdo = get_db_connection();

if ($orderId <= 0 || $pdo === null) {
    echo json_encode(['status' => 'error']);
    exit;
}

$stmt = $pdo->prepare('SELECT payment_status, payment_intent_id FROM orders WHERE id = :id');
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['status' => 'error']);
    exit;
}

if ($order['payment_status'] === 'paid') {
    echo json_encode(['status' => 'paid']);
    exit;
}

if (empty($order['payment_intent_id'])) {
    echo json_encode(['status' => 'pending']);
    exit;
}

$result = paymongo_retrieve_payment_intent($order['payment_intent_id']);

if (!$result['ok']) {
    echo json_encode(['status' => 'pending']); // transient API hiccup, keep polling
    exit;
}

$intentStatus = $result['body']['data']['attributes']['status'] ?? '';
$lastError = $result['body']['data']['attributes']['last_payment_error'] ?? null;

$newStatus = 'pending';

if ($intentStatus === 'succeeded') {
    $newStatus = 'paid';
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = :id");
    $stmt->execute([':id' => $orderId]);
} elseif ($intentStatus === 'awaiting_payment_method' && $lastError) {
    // The QR expired or the payment failed and PayMongo reset the intent.
    $newStatus = 'expired';
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'expired' WHERE id = :id");
    $stmt->execute([':id' => $orderId]);
}

echo json_encode(['status' => $newStatus]);