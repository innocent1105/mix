<?php
// login.php

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

$phone    = trim($input['mobile_number'] ?? '');
$password = trim($input['password'] ?? '');


if (
    strlen($phone) < 10 ||
    strlen($password) !== 6
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid mobile number or PIN format."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT public_id, password FROM users WHERE phone_number = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid credentials."
        ]);
        exit;
    }

    $pinHash = $user['password'];

    if (password_verify($password, $pinHash)) {
        
        $payload = [
            'iss' => 'ipay-api',           // Issuer
            'aud' => 'ipay-client',          // Audience
            'iat' => time(),                 // Issued at time
            'exp' => time() + (3600 * 24),   // Expiration time (24 hours)
            'sub' => $user['public_id']      // Subject (User Public ID)
        ];

        $token = base64_encode(json_encode($payload));

        // wont use token as token for now

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "token" => $user['public_id'], 
            "public_id" => $user['public_id']
        ]);

    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid credentials."
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An internal server error occurred during login."
    ]);
}