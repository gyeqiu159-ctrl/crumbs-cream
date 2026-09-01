<?php
/**
 * Shows a "scan to pay" QR Ph code for one order, the same kind of
 * QR customers scan at McDo/Jollibee counters — except this one is
 * generated per-order with the exact amount baked in.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/products.php';
require_once __DIR__ . '/lib/paymongo.php';

$orderId = (int) ($_GET['order_id'] ?? 0);
$pdo = get_db_connection();

$order = null;
$pageError = null;
$qr = null;

if ($orderId <= 0) {
    $pageError = 'No order specified.';
} elseif ($pdo === null) {
    $pageError = 'We could not reach the database right now. Please try again shortly.';
} else {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        $pageError = 'We could not find that order.';
    } else {
        if (isset($_GET['test_success']) && $_GET['test_success'] == '1') {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = :id");
            $stmt->execute([':id' => $orderId]);
            $order['payment_status'] = 'paid';
        }
    }
}

if ($order && $order['payment_status'] !== 'paid') {
    $amount = $order['amount'];

    // First time paying this order: figure out the amount from size x qty.
    if ($amount === null) {
        $unitPrice = get_size_price($order['size']);
        if ($unitPrice === null) {
            $pageError = 'We could not determine the price for this order. Please contact us directly.';
        } else {
            $amount = $unitPrice * (int) $order['quantity'];
            $stmt = $pdo->prepare('UPDATE orders SET amount = :amount WHERE id = :id');
            $stmt->execute([':amount' => $amount, ':id' => $orderId]);
            $order['amount'] = $amount;
        }
    }

    if (!$pageError) {
        // Reuse an existing intent if one is still open; otherwise make a new one.
        $needsNewIntent = true;

        if (!empty($order['payment_intent_id'])) {
            $existing = paymongo_retrieve_payment_intent($order['payment_intent_id']);
            if ($existing['ok']) {
                $status = $existing['body']['data']['attributes']['status'] ?? '';
                $nextActionImage = $existing['body']['data']['attributes']['next_action']['code']['image_url'] ?? null;
                if ($status === 'awaiting_next_action' && $nextActionImage) {
                    $qr = [
                        'intent_id' => $order['payment_intent_id'],
                        'image_url' => $nextActionImage,
                    ];
                    $needsNewIntent = false;
                }
            }
        }

        if ($needsNewIntent) {
            $result = paymongo_generate_qr((float) $amount, 'Crumb & Cream Order #' . $orderId);

            if (isset($result['error'])) {
                $pageError = 'Could not generate a QR code right now: ' . $result['error'];
            } else {
                $qr = $result;
                $stmt = $pdo->prepare('UPDATE orders SET payment_intent_id = :intent WHERE id = :id');
                $stmt->execute([':intent' => $result['intent_id'], ':id' => $orderId]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Your Order | Crumb & Cream</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pay-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .pay-card { background: var(--cream-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-soft); padding: 36px; max-width: 420px; width: 100%; text-align: center; }
        .pay-card h1 { font-size: 1.3rem; margin-bottom: 6px; }
        .pay-amount { font-family: var(--font-display); font-size: 2rem; color: var(--caramel-dark); margin: 6px 0 18px; }
        .qr-frame { background: #fff; border: 1.5px solid var(--crumb); border-radius: var(--radius-md); padding: 16px; display: inline-block; margin-bottom: 16px; }
        .qr-frame img { width: 220px; height: 220px; display: block; }
        .pay-status { font-size: 0.9rem; color: var(--cocoa-fade); margin-bottom: 10px; }
        .pay-status.success { color: #4a7f57; font-weight: 600; }
        .pay-status.error { color: #a5442b; font-weight: 600; }
        .pay-methods { font-size: 0.8rem; color: var(--cocoa-fade); margin-top: 4px; }
        .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid var(--crumb); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 6px; vertical-align: -2px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .back-link { display: inline-block; margin-top: 20px; font-size: 0.85rem; color: var(--cocoa-fade); }
        .test-auth-btn { 
            display: block; 
            width: 100%; 
            margin: 16px 0 0 0; 
            padding: 10px 16px; 
            background: linear-gradient(135deg, #d4a574 0%, #c09562 100%);
            color: white; 
            border: none; 
            border-radius: var(--radius-md); 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.85rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .test-auth-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .test-auth-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .test-auth-btn.success {
            background: linear-gradient(135deg, #4a7f57 0%, #3d6548 100%);
        }
    </style>
</head>
<body class="pay-shell">

    <div class="pay-card">
        <div class="login-brand" style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:14px;">
            <span class="logo-mark" aria-hidden="true"></span>
            <strong>Crumb &amp; Cream</strong>
        </div>

        <?php if ($pageError): ?>
            <h1>Something's Not Right</h1>
            <p class="pay-status error"><?php echo htmlspecialchars($pageError); ?></p>
            <a href="index.php#contact" class="back-link">&larr; Back to site</a>

        <?php elseif ($order['payment_status'] === 'paid'): ?>
            <h1>Already Paid ✓</h1>
            <p class="pay-status success">This order has already been paid for. Thank you!</p>
            <a href="index.php" class="back-link">&larr; Back to site</a>

        <?php else: ?>
            <h1>Scan to Pay</h1>
            <p>Order #<?php echo (int) $orderId; ?> — <?php echo htmlspecialchars($order['size']); ?> x<?php echo (int) $order['quantity']; ?></p>
            <div class="pay-amount">₱<?php echo number_format((float) $order['amount'], 2); ?></div>

            <div class="qr-frame">
                <img src="<?php echo htmlspecialchars($qr['image_url']); ?>" alt="Scan this QR code to pay">
            </div>

            <p class="pay-status" id="payStatus"><span class="spinner"></span>Waiting for payment…</p>
            <p class="pay-methods">Scan with GCash, Maya, or your banking app — same as scanning to pay at any QR Ph counter.</p>

            <button id="testAuthBtn" onclick="testPayMongoAuth()" class="test-auth-btn">🔑 Test Connection</button>

            <a href="index.php" class="back-link">&larr; Back to site</a>
        <?php endif; ?>
    </div>

    <?php if (!$pageError && $order['payment_status'] !== 'paid'): ?>
    <script>
        (function () {
            var statusEl = document.getElementById('payStatus');
            var orderId = <?php echo (int) $orderId; ?>;
            var pollTimer = null;

            function poll() {
                fetch('check-payment-status.php?order_id=' + orderId)
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.status === 'paid') {
                            statusEl.className = 'pay-status success';
                            statusEl.textContent = 'Payment received — thank you!';
                            clearInterval(pollTimer);
                        } else if (data.status === 'expired' || data.status === 'failed') {
                            statusEl.className = 'pay-status error';
                            statusEl.textContent = 'This QR code expired or the payment failed. Refresh the page to get a new one.';
                            clearInterval(pollTimer);
                        }
                        // otherwise still pending — keep polling
                    })
                    .catch(function () { /* silent retry on next tick */ });
            }

            pollTimer = setInterval(poll, 4000);

            // Test Authorization button
            window.testPayMongoAuth = function() {
                var btn = document.getElementById('testAuthBtn');
                btn.disabled = true;
                btn.textContent = '⏳ Redirecting to PayMongo...';
                window.location.href = 'test-paymongo-auth.php?order_id=' + orderId;
            };
        })();
    </script>
    <?php endif; ?>

</body>
</html>