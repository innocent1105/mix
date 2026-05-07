<?php
require_once 'connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$userId = $input['public_id'] ?? null;
$token  = $input['token'] ?? null;

if (!$userId || !$token) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE users SET expo_push_token = ? WHERE public_id = ?"
);
$stmt->execute([$token, $userId]);

echo json_encode(['success' => true]);
exit;