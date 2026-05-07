<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'connection.php';

$input = json_decode(file_get_contents("php://input"), true);
$phone_number = trim($input['phone_number'] ?? '');

if ($phone_number === '') {
    echo json_encode([
        "success" => false,
        "message" => "Phone number is required"
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number LIKE ? LIMIT 10");
$stmt->execute([$phone_number . "%"]);

$all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$all_rows) {
    echo json_encode(["success" => false, "message" => "No users found."]);
    exit;
}

$found_users = [];
foreach ($all_rows as $row) {
    $found_users[] = [
        "p_id"     => $row['public_id'],
        "username" => $row['username'],
        "fullname" => $row['fullname'],
        "phone_number"   => $row['phone_number'],
        "avatar"   => $row['avatar']
    ];
}

echo json_encode([
    "success" => true,
    "message" => "Found",
    "users"   => $found_users
]);

exit();
$found_users->free();


































