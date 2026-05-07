<?php

    require "./header.php";
    $user_id = $user_data['user_id'];

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        $id = $_GET['id'];
        
        $qry = "delete from notifications where id = '$id'";
        $res = mysqli_query($con, $qry);

        if(!$res){
            echo "an error occurred - please try again";
        }else{
            header("Location: ./notifications.php");
        }
    }

?>