<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = Illuminate\Support\Facades\Http::get('https://5sim.net/v1/guest/prices?country=russia');
$data = $res->json();
echo json_encode(array_slice($data['russia'] ?? [], 0, 2));
