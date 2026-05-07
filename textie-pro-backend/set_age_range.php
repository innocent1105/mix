<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *"); 
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");

    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = mysqli_real_escape_string($con, $data['user_id']);
    $age_range = mysqli_real_escape_string($con, $data['age_range']);

    $qry = "UPDATE users SET dob='$age_range' WHERE user_id = '$user_id'";
    $res = mysqli_query($con, $qry);

    if($res){
        echo json_encode([
            "status" => "success"
        ]);
    }else{
        echo json_encode([
            "status" => "error",
            "message" => "Failed to upload image"
        ]);
    } 
    exit;
