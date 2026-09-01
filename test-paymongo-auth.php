<?php 
session_start(); 
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/paymongo.php';

$orderId = (int) ($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    die("No order specified.");
}

$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

$amount = (int) round($order['amount'] * 100);

/* |-------------------------------------------------------------------------- | PAYMONGO TEST SECRET KEY |-------------------------------------------------------------------------- */ 
$secret_key = PAYMONGO_SECRET_KEY; 

/* |-------------------------------------------------------------------------- | CREATE TEST DATA |-------------------------------------------------------------------------- */ 
$data = [ 
    "data" => [ 
        "attributes" => [ 
            "line_items" => [ 
                [ 
                    "name" => "Crumb & Cream Order #" . $orderId . " (" . $order['size'] . ")", 
                    "amount" => $amount, 
                    "currency" => "PHP", 
                    "quantity" => (int) $order['quantity'] 
                ] 
            ], 
            "payment_method_types" => [ "card", "gcash", "paymaya" ], 
            "success_url" => "http://localhost/crumb-cream-main/pay.php?order_id=" . $orderId . "&test_success=1", 
            "cancel_url" => "http://localhost/crumb-cream-main/pay.php?order_id=" . $orderId, 
            "reference_number" => "TEST-" . $orderId . "-" . time(), 
            "description" => "Test Order #" . $orderId 
        ] 
    ] 
]; 

/* |-------------------------------------------------------------------------- | SEND REQUEST TO PAYMONGO |-------------------------------------------------------------------------- */ 
$ch = curl_init( "https://api.paymongo.com/v2/checkout_sessions" ); 
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); 
curl_setopt( $ch, CURLOPT_POST, true ); 
curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 
    "Content-Type: application/json", 
    "Authorization: Basic " . base64_encode( $secret_key . ":" ) 
] ); 
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) ); 
$response = curl_exec($ch); 
$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE ); 
$curl_error = curl_error($ch); 
curl_close($ch); 

/* |-------------------------------------------------------------------------- | CURL ERROR |-------------------------------------------------------------------------- */ 
if ($response === false) { 
    die( "Connection error: " . $curl_error ); 
} 

/* |-------------------------------------------------------------------------- | DECODE RESPONSE |-------------------------------------------------------------------------- */ 
$result = json_decode( $response, true ); 

/* |-------------------------------------------------------------------------- | PAYMONGO ERROR |-------------------------------------------------------------------------- */ 
if ( $http_code < 200 || $http_code >= 300 ) { 
    echo "<h2>PayMongo Error</h2>"; 
    echo "<p>HTTP Status: " . $http_code . "</p>"; 
    echo "<pre>"; 
    print_r($result); 
    echo "</pre>"; 
    exit; 
} 

/* |-------------------------------------------------------------------------- | GET CHECKOUT SESSION |-------------------------------------------------------------------------- */ 
if ( !isset( $result["data"] ) ) { 
    echo "<h2>Invalid PayMongo Response</h2>"; 
    echo "<pre>"; 
    print_r($result); 
    echo "</pre>"; 
    exit; 
} 

$checkout_session = $result["data"]; 
$checkout_url = $checkout_session["attributes"]["checkout_url"]; 

/* |-------------------------------------------------------------------------- | REDIRECT TO PAYMONGO |-------------------------------------------------------------------------- */ 
header( "Location: " . $checkout_url ); 
exit; 
?>
