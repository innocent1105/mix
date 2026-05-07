<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

$auth_user_id = mysqli_real_escape_string($con, $data['user_id']);
$preferences = $data['preferences'] ?? [];

// if (!is_array($preferences) || empty($auth_user_id)) {
//     echo json_encode(["error", "Invalid input"]);
//     exit;
// }

// Optional: Clear previous preferences
mysqli_query($con, "DELETE FROM interests WHERE user_id = '$auth_user_id'");

// Prepare the insert statement
$stmt = $con->prepare("INSERT INTO interests (user_id, interest) VALUES (?, ?)");

if (!$stmt) {
    echo json_encode(["error", "Statement preparation failed"]);
    exit;
}

foreach ($preferences as $pref) {
    $pref_clean = mysqli_real_escape_string($con, $pref);
    $stmt->bind_param("ss", $auth_user_id, $pref_clean);

    if (!$stmt->execute()) {
        echo json_encode(["error", "Failed to save: $pref_clean"]);
        $stmt->close();
        exit;
    }
}

$stmt->close();
echo json_encode(["success", "saved"]);
exit;
