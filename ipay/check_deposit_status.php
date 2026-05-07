<?php
header('Content-Type: application/json');
require_once 'connection.php';
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$reference = $_GET['ref'] ?? '';
$SECRET_KEY = 'd17d75a7437f62d4ce3e80b31a0e575eb34745288f699b7ebe9dee98d22305e5'; 

if (!$reference) {
    echo json_encode(['success' => false, 'message' => 'Missing reference']);
    exit;
}

$client = new Client();

try {
    $response = $client->request(
        'GET',
        "https://api.lenco.co/access/v2/collections/status/{$reference}",
        [
            'headers' => [
                'Authorization' => "Bearer {$SECRET_KEY}",
                'Accept' => 'application/json',
            ]
        ]
    );

    $data = json_decode($response->getBody(), true);
    $status = $data['data']['status'] ?? 'pending';

    $pdo->beginTransaction();

    // 1. Lock transaction
    $stmt = $pdo->prepare(
        "SELECT t_id, user_id, amount, status 
         FROM transactions 
         WHERE reference = ? 
         FOR UPDATE"
    );
    $stmt->execute([$reference]);
    $tx = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tx) {
        throw new Exception("Transaction not found");
    }

    // 2. Update transaction status
    $stmt = $pdo->prepare(
        "UPDATE transactions SET status = ? WHERE reference = ?"
    );
    $stmt->execute([$status, $reference]);

    // 3. Credit wallet ONLY ONCE
    if ($status === 'successful' && $tx['status'] !== 'successful') {

        $stmt = $pdo->prepare(
            "SELECT wallet_id, balance 
             FROM wallets 
             WHERE user_id = ? 
             AND wallet_type = 'main' 
             AND status = 'active'
             FOR UPDATE"
        );
        $stmt->execute([$tx['user_id']]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            throw new Exception("Wallet not found");
        }

        $newBalance = $wallet['balance'] + $tx['amount'];

        $stmt = $pdo->prepare(
            "UPDATE wallets SET balance = ? WHERE wallet_id = ?"
        );
        $stmt->execute([$newBalance, $wallet['wallet_id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'status'  => $status
    ]);

} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
