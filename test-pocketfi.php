<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$apiKey = env('POCKETFIL_PUBLIC_KEY'); // Try using public key!
$businessId = env('POCKETFIL_BUSINESS_ID');
$baseUrl = env('POCKETFIL_API_URL', 'https://api.pocketfi.ng/api/v1');

echo "Testing PocketFi API with Public Key...\n";
echo "API Key (Public): " . substr($apiKey, 0, 10) . "...\n";
echo "Business ID: " . $businessId . "\n";
echo "Base URL: " . $baseUrl . "\n\n";

$payload = [
    'first_name' => 'Test',
    'last_name' => 'User',
    'phone' => '08000000000',
    'business_id' => $businessId,
    'email' => 'test@example.com',
    'redirect_link' => 'https://example.com/callback',
    'amount' => '100',
];

echo "Request Payload:\n";
print_r($payload);

echo "\nSending request to " . $baseUrl . "/checkout/request\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/checkout/request');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Test with Bearer token using public key
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nResponse Code: $httpCode\n";
echo "Response Body:\n";
echo $response;
echo "\n";
