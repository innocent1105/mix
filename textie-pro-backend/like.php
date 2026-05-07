<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $post_id = $data['post'];


    $qry = "select * from likes where user_id = '$user_id' and post_id = '$post_id' limit 1";
    $result = mysqli_query($con, $qry);

    if($result -> num_rows > 0){
        $qry = "delete from likes where user_id = '$user_id' and post_id = '$post_id'";
        $result = mysqli_query($con, $qry);

        if($result){
            echo json_encode("unliked");    
        }
    }else{
        
        $sql = "insert into likes (user_id, post_id) values ('$user_id', '$post_id')";
        $result = mysqli_query($con, $sql);

        if($result){
            echo json_encode("liked");    
        }else{
            echo json_encode("error");
        }
    }




    exit();
?>
