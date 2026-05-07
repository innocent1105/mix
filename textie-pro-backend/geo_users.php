<?php
include("./connection.php");

error_reporting(0); 
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);
$user_id = isset($data['user_id']) ? stripslashes($data['user_id']) : null;

$users = [];

$qry = "SELECT user_id, username, email, pp, lat, lng, city FROM users WHERE user_id != '$user_id'";
$result = mysqli_query($con, $qry);

if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $users[] = [
            "id" => (int)$row['user_id'],
            "name" => $row['username'],
            "email" => $row['email'],
            "avatar" => $row['pp'], 
            "position" => [
                (float)$row['lat'],
                (float)$row['lng']
            ],
            "city" => $row['city']
        ];
    }
}


echo json_encode($users);
exit;
?>
