<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'];
    $post_id = $data['post_id'];

    $comments = [];

    $qry = "select * from comments where post_id = '$post_id'";
    $result = mysqli_query($con, $qry);

    if($result -> num_rows > 0){
        while($row = $result -> fetch_assoc()){
            $id = $row['id'];
            $comment_user_id = $row['user_id'];
            $comment = $row['comment'];
            $date_created = $row['date_created'];

            $user_qry = "select * from users where user_id = '$comment_user_id' limit 1";
            $res = mysqli_query($con, $user_qry);
            if($res -> num_rows > 0){
                while($user = $res->fetch_assoc()){
                    $username = $user['username'];
                    $fullname = $user['fullname'];
                    $email = $user['email'];
                    $pp = $user['pp'];

                    $comment_user = [
                        "user_id" => $comment_user_id,
                        "comment_id" => $id,
                        "username" => $username,
                        "fullname" => $fullname,
                        "email" => $email,
                        "pp" => $pp,
                        "comment" => $comment,
                        "date" => $date_created,
                    ];

                    array_push($comments, $comment_user);
                }
            }

        }

        echo json_encode($comments);
        exit();
    }else{
        echo json_encode([]);
    }

    exit();
?>
