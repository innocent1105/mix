<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *"); // Allow from all domains (for dev)
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");

    // Receive JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $username = $data['username'] ?? '';
    $phone = $data['phone'] ?? '';
    $gender = $data['gender'] ?? '';
    $password = $data['password'] ?? '';

    $user_id = rand(100, 10000) * 100;

    if(empty($email)){
        echo json_encode("error-1");
        exit();
    }else if(empty($username)){
        echo json_encode("error-2");
        exit();

    }else if(empty($password)){
        echo json_encode("error-3");
        exit();
    }

    $password = password_hash($password, true);

    $qry = "insert into users (user_id, username, email, phone_number, password, gender) values ('$user_id', '$username', '$email', '$phone','$password','$gender')";
    $result = mysqli_query($con, $qry);
    
    if($result){
        echo json_encode("success");
    }else{
        echo json_encode("error");
    }


?>
