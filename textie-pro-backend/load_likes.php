<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $post_id = $data['post_id'];

    $likes = 0;
    $isLiked = false;

    $qry = "select * from likes where post_id = '$post_id'";
    $result = mysqli_query($con, $qry);

    if($result -> num_rows > 0){
        while($row = $result -> fetch_assoc()){
            $likes++;
        }
    }

    $qry = "select * from likes where user_id = '$user_id' and post_id = '$post_id'";
    $result = mysqli_query($con, $qry);

    if($result -> num_rows > 0){
        while($row = $result -> fetch_assoc()){
            $isLiked = true;
        }
    }

    echo json_encode([
        "likes" => $likes,
        "isLiked" => $isLiked
    ]);

    exit();
?>
