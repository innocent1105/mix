<?php

session_start();
    include("dbconnect.php");
    include("functions.php");

$user_data = check_login($con);
  
// echo "configuration successful";
?>