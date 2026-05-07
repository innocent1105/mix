<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $post_id = $data['post_id'];
    $comment = $data['comment'];


    $qry = "insert into comments (user_id, post_id, comment) values ('$user_id', '$post_id', '$comment')";
    $result = mysqli_query($con, $qry);

    if($result){
        echo json_encode("success");
    }else{
        echo json_encode("error");
    }

    exit();
?>
