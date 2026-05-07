<?php
   $serverName = "localhost";
   $userName = "root";
   $password = "";
   $dbName = "dmbs";

   if(!$con = mysqli_connect($serverName,$userName,$password,$dbName)){
     die("failed to connect");
   }

   
   
?>
