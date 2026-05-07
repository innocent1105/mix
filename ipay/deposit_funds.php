<?php
header('Content-Type: application/json');
require_once 'connection.php';
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$input = json_decode(file_get_contents("php://input"), true);

$SECRET_KEY = 'd17d75a7437f62d4ce3e80b31a0e575eb34745288f699b7ebe9dee98d22305e5'; 
$AMOUNT     = (float)($input['amount'] ?? 0);
$PHONE      = $input['phone'] ?? '';
$p_id   = $input['p_id'] ?? ''; 
$REFERENCE  = 'TXN_' . uniqid();

if ($AMOUNT <= 0 || empty($PHONE)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$client = new Client();

$stmt = $pdo->prepare("SELECT * FROM users WHERE public_id = ? LIMIT 1");
$stmt->execute([$p_id]);
$user_ = $stmt->fetch(PDO::FETCH_ASSOC);

$expo_token = $user_['expo_push_token'] ?? null;

function detectTelco(string $phone): string{
    $phone = preg_replace('/\D/', '', $phone);

    if (strlen($phone) !== 10) {
        return 'UNKNOWN';
    }

    $prefix = substr($phone, 0, 3);

    return match ($prefix) {
        '096', '076' => 'MTN',
        '097', '077' => 'AIRTEL',
        '095', '075' => 'ZAMTEL',
        default => 'UNKNOWN',
    };
}


$telco = detectTelco($PHONE);

try {
    $response = $client->request('POST', 'https://api.lenco.co/access/v2/collections/mobile-money', [
        'headers' => [
            'Authorization' => 'Bearer ' . $SECRET_KEY,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ],
        'json' => [
            'amount'    => $AMOUNT,
            'reference' => $REFERENCE,
            'phone'     => $PHONE,
            'operator'  => $telco, 
            'country'   => 'zm',
            'bearer'    => 'merchant',
        ]
    ]);

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE public_id = ? LIMIT 1");
    $stmt->execute([$p_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $user_id = $user['user_id'];

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id,sender_id, receiver_id, amount, reference, t_type, status) 
    VALUES (?, ?,?,?,?, 'deposit', 'pending')");
    $stmt->execute([$user_id, $PHONE, $user_id, $AMOUNT, $REFERENCE]);





    
    $payload = [
        'to' => $expo_token,
        'sound' => 'default',
        'title' => 'Transaction Initiated',
        'body' => 'A deposit of ' . number_format($AMOUNT, 2) . ' has been initiated. Please complete the payment on your phone.',
    ];

    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    curl_close($ch);





    echo json_encode([
        'success' => true,
        'status' => 'pending', 
        'message' => 'Please check your phone for the PIN prompt',
        'reference' => $REFERENCE
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not initiate collection'
    ]);
}