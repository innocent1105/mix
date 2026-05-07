<?php
    require "./header.php";
    // echo $user_account;
    if($user_account == "citizen"){
        header("Location: ./user_request.php");
        die;
    }
    header("Location: dashboard.php");
?>