<?php
    include("./connection.php");

    header("Access-Control-Allow-Origin: *"); 
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");

    $data = json_decode(file_get_contents("php://input"), true);

    $auth_user_id = mysqli_real_escape_string($con, $data['user_id']);
    $followed_user_id = mysqli_real_escape_string($con, $data['followed_user_id']);

    $qry = "select * from followers where user_id = '$auth_user_id' and followed_user_id = '$followed_user_id' limit 1";
    $res = mysqli_query($con, $qry);

    if($res -> num_rows > 0){
        $qry = "delete from followers where user_id = '$auth_user_id' and followed_user_id = '$followed_user_id' limit 1";
        $res = mysqli_query($con, $qry);

        if($res){
            echo json_encode("unfollowed");
        }
    }else{
        $qry = "insert into followers (user_id, followed_user_id) values ('$auth_user_id', '$followed_user_id')";
        $res = mysqli_query($con, $qry);
        if($res){
            echo json_encode("followed");
        }
    }

    exit();

