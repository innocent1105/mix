<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST");

    file_put_contents("debug.txt", file_get_contents("php://input"));
 
    // Receive JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $user_id = rand(100, 10000) * 100;

    if(empty($email)){
        echo json_encode("error-1");
        exit();
    }else if(empty($password)){
        echo json_encode("error-3");
        exit();
    }

    $qry = "select * from users where email = '$email' limit 1";
    $result = mysqli_query($con, $qry);
    
    if($result -> num_rows > 0){
        while($row = $result -> fetch_assoc()){
            if(password_verify($password, $row['password'])){
                $user_id = $row['user_id'];
                $user_data = [
                    "user_id" => $user_id,
                    "email" => $row['email'],
                    "username" => $row['username'],
                    "status" => "success"
                ];
                echo json_encode($user_data);
                exit();
            }else{
                echo json_encode("wrong-password");
                exit();
            }
        }
    }else{
        echo json_encode("error-1");
        exit();
    }


?>
