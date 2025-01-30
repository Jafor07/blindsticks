<?php
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('your_secret_key');

$token = $_POST['token'];

try {
    $charge = \Stripe\Charge::create([
        'amount' => 9999, // Amount in cents
        'currency' => 'usd',
        'description' => 'Smart Blindstick Purchase',
        'source' => $token,
    ]);
    echo json_encode(['success' => true]);
} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>