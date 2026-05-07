<?php

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

$phone    = trim($input['phone_number'] ?? '');
$password = trim($input['password'] ?? '');

$sender_id = trim($input['sender_id'] ?? '');
$reciever_id = trim($input['reciever_id'] ?? '');
$username = trim($input['username'] ?? '');
$amount = trim($input['amount'] ?? '');
$network = trim($input['network'] ?? '');

if (strlen($phone) != 10 || strlen($password) < 5) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid mobile number or PIN format."
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT * from users where public_id = ? limit 1");
$stmt->execute([$sender_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials."
    ]);
    exit;
}

echo json_encode($sender_id);





















