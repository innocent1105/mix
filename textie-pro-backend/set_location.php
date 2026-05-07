<?php

include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = mysqli_real_escape_string($con, $data['user_id']);
$address = $data['address'];

$country = $address['country'];
$province = $address['province'];
$subregion = $address['subregion'];
$city = $province . $address['city']. ", " . $subregion;


$stmt = $con->prepare("UPDATE users SET country=?, city=? WHERE user_id=?");
$stmt->bind_param("ssi", $country, $city, $user_id);
if ($stmt->execute()) {
    echo json_encode(["success"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}


exit;
