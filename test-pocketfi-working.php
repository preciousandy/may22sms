<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\PocketFiService;

$service = new PocketFiService();

if (!$service->isConfigured()) {
    die("Service not configured!\n");
}

echo "Testing PocketFi Checkout Creation...\n";

$result = $service->createCheckout([
    'first_name' => 'Test',
    'last_name' => 'User',
    'phone' => '08000000000',
    'business_id' => config('services.pocketfi.business_id'),
    'email' => 'test@example.com',
    'redirect_link' => 'https://example.com/callback',
    'amount' => '100',
]);

print_r($result);
