<?php 
session_start(); 

$secret_key = "sk_test_woycr8FXewQo6CABEkViMjYj"; 

$data = [ 
    "data" => [ 
        "attributes" => [ 
            "line_items" => [ 
                [ 
                    "name" => "Test", 
                    "amount" => 10000, 
                    "currency" => "PHP", 
                    "quantity" => 1 
                ] 
            ], 
            "payment_method_types" => [ "card", "gcash", "paymaya" ], 
            "success_url" => "http://localhost/crumb-cream-main/pay.php", 
            "cancel_url" => "http://localhost/crumb-cream-main/pay.php", 
            "reference_number" => "TEST-" . date("YmdHis") . "-" . rand(1000, 9999), 
            "description" => "Test" 
        ] 
    ] 
]; 

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

if ($response === false) { 
    die( "Connection error: " . $curl_error ); 
} 

$result = json_decode( $response, true ); 

echo "<h2>HTTP Code: " . $http_code . "</h2>";
echo "<pre>";
print_r($result);
echo "</pre>";

if ( $http_code >= 200 && $http_code < 300 && isset( $result["data"] ) ) {
    echo "<h3 style='color: green;'>✓ SUCCESS - Authorization is working!</h3>";
} else {
    echo "<h3 style='color: red;'>✗ FAILED</h3>";
}
