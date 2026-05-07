<?php
// register.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');
require_once 'connection.php';

$input = json_decode(file_get_contents("php://input"), true);

$fullname = trim($input['username'] ?? '');
$phone    = trim($input['mobile_number'] ?? '');
$password = trim($input['password'] ?? '');
$gender   = $input['gender'] ?? 'other';
$expo_token   = $input['expo_token'] ?? 'token';

if (
    strlen($fullname) < 4 ||
    strlen($phone) < 10 ||
    strlen($password) !== 6
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid registration data"
    ]);
    exit;
}

$username = strtolower(preg_replace('/\s+/', '', $fullname));
$publicId = uniqid($username);
$email    = $username . "@Payreece.com";
$pinHash  = password_hash($password, PASSWORD_BCRYPT);

$avatar = "";



$stmt = $pdo->prepare("
    SELECT avatar 
    FROM avatars 
    ORDER BY RAND() 
    LIMIT 1
");

$stmt->execute();
$avatar_ = $stmt->fetch();

$avatar = $avatar_['avatar'] ?? "";


try {
    $pdo->beginTransaction();

    // Create user
    $stmt = $pdo->prepare("
        INSERT INTO users (
            public_id,
            username,
            fullname,
            phone_number,
            password,
            email,
            gender,
            expo_push_token,
            avatar
        ) VALUES (?, ?, ?,?, ?, ?,?,?,?)
    ");

    $stmt->execute([
        $publicId,
        $username,
        $fullname,
        $phone,
        $pinHash,
        $email,
        $gender,
        $expo_token,
        $avatar
    ]);

    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO wallets (user_id, wallet_type)
        VALUES (?, 'main')
    ");
    $stmt->execute([$userId]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Registration successful",
        "data" => [
            "public_id" => $publicId
        ]
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();

    echo json_encode([
        "success" => false,
        "message" => "This number is already registered or registration failed"
    ]);
}
