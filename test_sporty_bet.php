<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SportyBetCode;

$code = SportyBetCode::create([
    'code' => 'TESTCODE123',
    'amount' => 5000,
    'description' => 'Test description'
]);

if ($code) {
    echo "Successfully created code: " . $code->code . "\n";
} else {
    echo "Failed to create code.\n";
}

$allCodes = SportyBetCode::all();
echo "Total codes: " . $allCodes->count() . "\n";
foreach ($allCodes as $c) {
    echo "- " . $c->code . " (Amount: " . $c->amount . ")\n";
}
