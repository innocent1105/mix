<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

require_once "connection.php";

// Read POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$token = $input['token'];

// Fetch user by public_id
$stmt = $pdo->prepare("SELECT * FROM users WHERE public_id = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Collect fields to update
$fields = ['fullname', 'username', 'email', 'phone_number', 'student_id', 'gender'];
$updateData = [];

foreach ($fields as $field) {
    if (isset($input[$field]) && $input[$field] !== '') {
        $updateData[$field] = $input[$field];
    }
}

// Handle avatar base64
if (isset($input['avatar']) && preg_match('/^data:image\/(\w+);base64,/', $input['avatar'], $type)) {
    $data = substr($input['avatar'], strpos($input['avatar'], ',') + 1);
    $data = base64_decode($data);

    if ($data === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid image data']);
        exit;
    }

    $ext = strtolower($type[1]); // jpg, png, etc
    $filename = uniqid('avatar_') . '.' . $ext;
    $filepath = __DIR__ . '/avatars/' . $filename;

    if (!file_put_contents($filepath, $data)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save avatar']);
        exit;
    }

    // Delete old avatar if it exists
    if (!empty($user['avatar']) && file_exists(__DIR__ . '/avatars/' . $user['avatar'])) {
        @unlink(__DIR__ . '/avatars/' . $user['avatar']);
    }

    $updateData['avatar'] = $filename;
}

// Build query dynamically
if (!empty($updateData)) {
    $set = implode(", ", array_map(fn($k) => "$k = :$k", array_keys($updateData)));
    $updateData['public_id'] = $user['public_id']; // use public_id instead of id
    $stmt = $pdo->prepare("UPDATE users SET $set WHERE public_id = :public_id");
    $stmt->execute($updateData);
}

// Fetch updated user
$stmt = $pdo->prepare("SELECT public_id, fullname, username, email, phone_number, student_id, gender, avatar FROM users WHERE public_id = ?");
$stmt->execute([$user['public_id']]);
$updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'Profile updated successfully',
    'updated_user' => $updatedUser
]);
