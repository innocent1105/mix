<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

$auth_user_id = mysqli_real_escape_string($con, $data['user_id']);
$followed_user_id = mysqli_real_escape_string($con, $data['followed_user_id']);

$qry = "SELECT * FROM followers 
        WHERE user_id = '$auth_user_id' 
        AND followed_user_id = '$followed_user_id' 
        LIMIT 1";

$res = mysqli_query($con, $qry);

if ($res->num_rows === 0) {
    echo json_encode("non");
} else {
    echo json_encode("followed");
}

exit();
