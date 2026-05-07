<?php 

session_start();
require "./config_session.php";
if(isset($_SESSION['user_id'])){
    unset($_SESSION['user_id']);
    

}
header("Location: ../login.php");
die;


?>