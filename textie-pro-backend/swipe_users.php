<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *"); 
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");

    // Receive JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = stripslashes($data['user_id']);
    
    $users = [];

    $sql = "select * from users";
    $result = mysqli_query($con, $sql);

    if($result -> num_rows > 0) { 
        while($row = $result -> fetch_assoc()){ 
            $user = [];
            $user_id = $row['user_id'];
            $username = $row['username'];
            $profile_picture = $row['pp'];
            $time = $row['date_created'];

            // time 
            $time = date('d M , h:i A', strtotime($time));

            $user = [$user_id, $username, $profile_picture, $time];
            array_push($users, $user);
        }

        echo json_encode($users);
    }














