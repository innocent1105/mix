<?php
   function check_login($con)
   {
      if(isset($_SESSION['user_id']))
      {
      $id = $_SESSION['user_id'];
      $query = "select * from users where user_id = '$id' limit 1";
      
      $result = mysqli_query($con,$query);

      if($result && mysqli_num_rows($result) > 0){
         $user_data = mysqli_fetch_assoc($result);
         return $user_data;
      }
      }
            // redirect to login

         header("Location: login.php");
         die;
      
   }

function random_num($length){
   $text = " ";
   if($length < 1000000 ){
      $length = 2000000;
   }

   $len = rand(1000000, $length);

   for ($i=0; $i < $len; $i++) { 
      $text .= rand(1000000,2000000);
      return $text;
   }
}






















