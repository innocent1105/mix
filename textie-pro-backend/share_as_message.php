<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Get JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No JSON received"]);
    exit;
}

$userId        = $data['user_id'] ?? null;
$postId        = $data['post_id'] ?? null;
$content       = $data['content'] ?? "";
$selectedUsers = $data['selectedUsers'] ?? [];

// If no message content, mark as "Attachment"
if (empty($content)) {
    $content = "Attachment";
}

// Final response array
$response = [
    "sent_to" => [],
    "errors"  => []
];

// Loop through each selected user
foreach ($selectedUsers as $receiverId) {

    // --- 1. Check if chat exists ---
    $checkQuery = "SELECT * FROM chats 
                   WHERE (sender = '$userId' AND reciever = '$receiverId') 
                   OR (sender = '$receiverId' AND reciever = '$userId')";
    $checkResult = mysqli_query($con, $checkQuery);

    if ($checkResult && mysqli_num_rows($checkResult) === 0) {
        // Create new chat record
        $insertChat = "INSERT INTO chats (sender, reciever, message, message_type, status) 
                       VALUES (?, ?, ?, 'text', 'sent')";
        $stmt = mysqli_prepare($con, $insertChat);
        mysqli_stmt_bind_param($stmt, "sss", $userId, $receiverId, $content);
        if (!mysqli_stmt_execute($stmt)) {
            $response['errors'][] = "Failed to insert chat for user $receiverId";
            continue;
        }
    } else {
        // Update existing chat
        $updateChat = "UPDATE chats 
                       SET message = ?, message_type = 'text', status = 'sent' 
                       WHERE (sender = ? AND reciever = ?) OR (sender = ? AND reciever = ?)";
        $stmt = mysqli_prepare($con, $updateChat);
        mysqli_stmt_bind_param($stmt, "sssss", $content, $userId, $receiverId, $receiverId, $userId);
        if (!mysqli_stmt_execute($stmt)) {
            $response['errors'][] = "Failed to update chat for user $receiverId";
            continue;
        }
    }

    // --- 2. Insert into messages table ---
    $insertMsg = "INSERT INTO messages (sender, reciever, message, message_type, status, attachment_name) 
                  VALUES ('$userId', '$receiverId', '$content', 'text', 'sent', '$postId')";
    if (!mysqli_query($con, $insertMsg)) {
        $response['errors'][] = "Failed to insert message for user $receiverId";
        continue;
    }

    // --- 3. Add to success list ---
    $response['sent_to'][] = $receiverId;
}

echo json_encode($response);
?>
