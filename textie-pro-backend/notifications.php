<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["error" => "user_id is required"]);
    exit();
}

$user_id = mysqli_real_escape_string($con, $data['user_id']);

$notifications = [];

$sql = "SELECT * FROM notifications 
        WHERE user_id = '$user_id' 
     
        ORDER BY date_created DESC 
        LIMIT 40";

$res = mysqli_query($con, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $note = [
            "type"        => $row['note_type'],
            "message"     => $row['note_message'],
            "date"        => $row['date_created'],
            "post_id"     => $row['post_id'],
            "other_user"  => $row['other_user'],
            "status"      => $row['status']
        ];

     
        $other_user_id = mysqli_real_escape_string($con, $row['other_user']);
        $user_qry = "SELECT username, pp FROM users WHERE user_id = '$other_user_id' LIMIT 1";
        $user_res = mysqli_query($con, $user_qry);

        if ($user_res && mysqli_num_rows($user_res) > 0) {
            $user_row = mysqli_fetch_assoc($user_res);
            $note["username"] = $user_row['username'];
            $note["profile"]  = $user_row['pp'];
        } else {
            $note["username"] = null;
            $note["profile"]  = null;
        }

        $notifications[] = $note;

        $update_sql = "UPDATE notifications SET status = 'seen' WHERE user_id = '$user_id' AND status != 'seen'";
        mysqli_query($con, $update_sql);

    }
}

echo json_encode($notifications);
exit();
?>
