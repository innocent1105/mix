<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $liked_user_id = $data['liked_user'];

    $qry = "select * from matches where user_id = '$user_id' and other_user = '$liked_user_id' ";
    $result = mysqli_query($con, $qry);

    if($result->num_rows == 0){
        $qry = "insert into matches (user_id, other_user) values ('$user_id', '$liked_user_id')";
        $result = mysqli_query($con, $qry);
        if($result){
            echo json_encode("liked");
        }else{
            echo json_encode("error");
        }
    }else{
        echo json_encode("already liked");
    }


    exit();
?>
