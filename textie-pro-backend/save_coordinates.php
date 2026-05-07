<?php

include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);


$address = $data['address'];
$user_id = mysqli_real_escape_string($con, $address['user_id']);

$country = $address['country'];
$province = $address['province'];
$subregion = $address['region'];
$city = $address['region'];
$lng = $address['lng'];
$lat = $address['lat'];

$stmt = $con->prepare("UPDATE users SET country=?, city=?, lng=?, lat=? WHERE user_id=?");
$stmt->bind_param("ssssi", $country, $city,$lng, $lat, $user_id);
if ($stmt->execute()) {
    echo json_encode(["success from server"]);
} else {
    echo json_encode(["save coord error " => $stmt->error]);
}


exit;
