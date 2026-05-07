<?php

include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = mysqli_real_escape_string($con, $data['user_id']);
$age_range = mysqli_real_escape_string($con, $data['age_range']);

// Check if user already has an age_range
$sql = "SELECT * FROM age_range WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $sql = "DELETE FROM age_range WHERE user_id = '$user_id' LIMIT 1";
    mysqli_query($con, $sql);
}

// Insert new age_range
$qry = "INSERT INTO age_range (user_id, age) VALUES ('$user_id', '$age_range')";
$res = mysqli_query($con, $qry);

if ($res) {
    echo json_encode(['success' => 'saved']);
} else {
    echo json_encode(['error' => 'not saved']);
}

exit;
