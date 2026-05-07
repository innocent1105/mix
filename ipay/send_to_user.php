<?php

header('Content-Type: application/json');
require_once 'connection.php';

$input = json_decode(file_get_contents("php://input"), true);

$sender_public_id   = trim($input['sender_id'] ?? '');
$receiver_public_id = $input['receiver_id'];
$amount             = (float) ($input['amount'] ?? 0);
$password   = trim($input['password'] ?? '');

$fee = 0.85;

if ($amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid transfer amount.'
    ]);
    exit;
}


try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE public_id = ? LIMIT 1");
    $stmt->execute([$sender_public_id]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);

    $expo_token = $sender['expo_push_token'] ?? null;

    if (!$sender) {
        throw new Exception('Sender not found.');
    }

    if (!password_verify($password, $sender['password'])) {
        throw new Exception('Incorrect password. Please try again.');
    }


    $stmt->execute([$receiver_public_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiver) {
        throw new Exception('Receiver not found.');
    }

    if ($sender['user_id'] == $receiver['user_id']) {
        throw new Exception('Cannot transfer to self.');
    }

    $receiver_expo_token = $receiver['expo_push_token'] ?? null;

    $stmt = $pdo->prepare("
        SELECT * FROM wallets 
        WHERE user_id = ? AND status = 'active'
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$sender['user_id']]);
    $senderWallet = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $amount + $fee;
    if (!$senderWallet || $senderWallet['balance'] < $total) {
        throw new Exception('Insufficient balance.');
    }

    $stmt->execute([$receiver['user_id']]);
    $receiverWallet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiverWallet) {
        throw new Exception('Receiver wallet not found.');
    }

    // 6️⃣ CREATE TRANSACTION RECORD
    $reference = 'TX_' . uniqid();

    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (user_id, wallet_id, t_type, sender_id, receiver_id, amount, status, reference)
        VALUES (?, ?, 'transfer', ?, ?, ?, 'pending', ?)
    ");
    $stmt->execute([
        $sender['user_id'],
        $senderWallet['wallet_id'],
        $sender['user_id'],
        $receiver['user_id'],
        $amount,
        $reference
    ]);

    $transactionId = $pdo->lastInsertId();

    // 7️⃣ LEDGER — SENDER DEBIT
    $stmt = $pdo->prepare("
        INSERT INTO ledger_entries
        (transaction_id, wallet_id, entry_type, amount, balance_before, balance_after, description)
        VALUES (?, ?, 'debit', ?, ?, ?, ?)
    ");
    $stmt->execute([
        $transactionId,
        $senderWallet['wallet_id'],
        $amount,
        $senderWallet['balance'],
        $senderWallet['balance'] - $amount,
        'User transfer debit'
    ]);

    // 8️⃣ LEDGER — RECEIVER CREDIT
    $stmt->execute([
        $transactionId,
        $receiverWallet['wallet_id'],
        $amount,
        $receiverWallet['balance'],
        $receiverWallet['balance'] + $amount,
        'User transfer credit'
    ]);

    // 9️⃣ UPDATE WALLET BALANCES
    $stmt = $pdo->prepare("UPDATE wallets SET balance = ? WHERE wallet_id = ?");
    $stmt->execute([
        $senderWallet['balance'] - $amount - $fee,
        $senderWallet['wallet_id']
    ]);
    $stmt->execute([
        $receiverWallet['balance'] + $amount,
        $receiverWallet['wallet_id']
    ]);

    $stmt = $pdo->prepare("UPDATE transactions SET status = 'successful' WHERE t_id = ?");
    $stmt->execute([$transactionId]);

    $pdo->commit();


    // $payload = [
    //     'to' => $expo_token,
    //     'sound' => 'default',
    //     'title' => 'Transfer Successful',
    //     'body' => 'Successfully sent to ' . $receiver['fullname'],
    // ];

    // $ch = curl_init('https://exp.host/--/api/v2/push/send');
    // curl_setopt_array($ch, [
    //     CURLOPT_POST => true,
    //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POSTFIELDS => json_encode($payload),
    // ]);

    // $response = curl_exec($ch);
    // curl_close($ch);



    // $payload = [
    //     'to' => $receiver_expo_token,
    //     'sound' => 'default',
    //     'title' => 'Received Funds',
    //     'body' => 'You have received ' . number_format($amount, 2) . ' from ' . $sender['fullname'],
    // ];

    // $ch = curl_init('https://exp.host/--/api/v2/push/send');
    // curl_setopt_array($ch, [
    //     CURLOPT_POST => true,
    //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POSTFIELDS => json_encode($payload),
    // ]);

    // $response = curl_exec($ch);
    // curl_close($ch);
    







    echo json_encode([
        'success' => true,
        'message' => 'Transfer successful.',
        'reference' => $reference
    ]);

} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
