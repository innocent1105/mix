<?php
header('Content-Type: application/json');
require_once 'connection.php';

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if (!isset($data['reference'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

$reference = $data['reference'];
$status    = strtolower($data['status']); // success | failed

try {
    $pdo->beginTransaction();

    // Lock transaction
    $stmt = $pdo->prepare("
        SELECT * FROM transactions 
        WHERE reference = ? 
        LIMIT 1 
        FOR UPDATE
    ");
    $stmt->execute([$reference]);
    $tx = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tx) {
        throw new Exception('Transaction not found');
    }

    // Idempotency check
    if ($tx['status'] !== 'pending') {
        $pdo->commit();
        echo json_encode(['success' => true]);
        exit;
    }

    if ($status === 'success') {

        // Get wallet
        $stmt = $pdo->prepare("
            SELECT * FROM wallets 
            WHERE user_id = ? 
            LIMIT 1 
            FOR UPDATE
        ");
        $stmt->execute([$tx['user_id']]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            throw new Exception('Wallet not found');
        }

        // Ledger credit
        $stmt = $pdo->prepare("
            INSERT INTO ledger_entries
            (transaction_id, wallet_id, entry_type, amount, balance_before, balance_after, description)
            VALUES (?, ?, 'credit', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tx['t_id'],
            $wallet['wallet_id'],
            $tx['amount'],
            $wallet['balance'],
            $wallet['balance'] + $tx['amount'],
            'Mobile money deposit'
        ]);

        // Update wallet balance
        $stmt = $pdo->prepare("
            UPDATE wallets 
            SET balance = ? 
            WHERE wallet_id = ?
        ");
        $stmt->execute([
            $wallet['balance'] + $tx['amount'],
            $wallet['wallet_id']
        ]);

        // Mark transaction success
        $stmt = $pdo->prepare("
            UPDATE transactions 
            SET status = 'success' 
            WHERE t_id = ?
        ");
        $stmt->execute([$tx['t_id']]);

    } else {
        // Failed
        $stmt = $pdo->prepare("
            UPDATE transactions 
            SET status = 'failed' 
            WHERE t_id = ?
        ");
        $stmt->execute([$tx['t_id']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false]);
}
