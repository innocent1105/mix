<?php

    include("./connection.php");

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST");

    // Receive JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $userId = $data['user_id'];
    $recieverId = $data['otherUser'];
    $message = $data['message'];
    $date = $data['date'];



    $qry = "insert into messages (sender, reciever, message, message_type, status) values ('$userId', '$recieverId', '$message', 'text', 'sent')";
    $result = mysqli_query($con, $qry);
    


    $del_query = "DELETE FROM chats 
        WHERE (sender = '$userId' AND reciever = '$recieverId') 
        OR (sender = '$recieverId' AND reciever = '$userId')
    ";
    $result = mysqli_query($con, $del_query);

    $qry = "insert into chats (sender, reciever, message, message_type, status) values ('$userId', '$recieverId', '$message', 'text', 'sent')";
    $result = mysqli_query($con, $qry);
    
    if($result){
        
        echo json_encode("sent");
    }else{
        echo json_encode("error");
    }


?>
