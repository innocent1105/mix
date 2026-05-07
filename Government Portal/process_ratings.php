<?php 
    require "./userconfig.php";

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $data = json_decode($_POST['data'], true);
        $user_id = $data['user_id'];
        $rating = $data['rating'];
        $comment = $data['comment'];

        $sql = "insert into ratings (user_id, rating, comment) values ('$user_id', '$rating', '$comment')";
        $result = mysqli_query($con, $sql);

        if(!$result){
            echo "error";
        }else{
            echo "success";
        }
    }



