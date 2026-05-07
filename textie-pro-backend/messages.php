<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'];
$other_user_id = $data['otherUser'];

$messages = [];

$qry = "SELECT * FROM chats 
        WHERE (sender = '$user_id' AND reciever = '$other_user_id') 
        OR (sender = '$other_user_id' AND reciever = '$user_id')";
$result = mysqli_query($con, $qry);

if ($result && $result->num_rows > 0) {
    $updateChat = "UPDATE chats 
    SET status = 'seen' 
    WHERE sender = '$other_user_id' 
      AND reciever = '$user_id'
      AND status != 'seen'";

    $update_result = mysqli_query($con, $updateChat);
}



$query = "SELECT * FROM messages 
          WHERE (sender = '$user_id' AND reciever = '$other_user_id') 
          OR (sender = '$other_user_id' AND reciever = '$user_id') 
          ORDER BY date_created ASC";
$result = mysqli_query($con, $query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            "id"           => $row['id'],
            "type"         => "text",
            "text"         => $row['message'],
            "fromMe"       => ($row['sender'] == $user_id),
            "to"           => $row['reciever'],
            "message_type" => $row['message_type'],
            "status"       => $row['status'],
            "date"         => $row['date_created']
        ];
    }
}

$updateDelivered = "UPDATE messages 
                    SET status = 'seen' 
                    WHERE sender = '$other_user_id' 
                    AND reciever = '$user_id' 
                    ";
mysqli_query($con, $updateDelivered);

echo json_encode($messages);
exit;
?>
