<?php 
    include("dbconnect.php");

    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 


    function send_email($recieversAddress){
        $mail = new PHPMailer(true);
        $system_email = "mugwadiinnocent@gmail.com";
        
        try {

            $mail->isSMTP();                                
            $mail->Host       = 'smtp.gmail.com';           
            $mail->SMTPAuth   = true;                  
            $mail->Username   = $system_email;     
            $mail->Password   = 'kemz bvyj ectc bife';       
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;                    

            $mail->isHTML(true);
            $mail->setFrom('mugwadiinnocent@gmail.com', 'Citizen Portal'); 
            $mail->addAddress($recieversAddress);
            $mail->Subject = 'Online Citizen Service Portal';
            $mail->Body = '
                <p>Hello, you have successfully changed your password.<b></b>,</p>
                <div style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                    Thank you for choosing <b>Online Citizen Service Portal</b>.<br><br>
                    Always remember to keep your password safe and do not share it with anyone.<br><br>
                </div>
                <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
            ';
            $mail->AltBody = "Hello,\n\nThank you for choosing Online Citizen Service Portal.\n\nAlways remember to keep your password safe and do not share it with anyone.\n\nBest Regards,\nOnline Citizen Service Portal Team";

                    
            // Send 
            $mail->send();
        } catch (Exception $e) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    }





    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $user_id = $_GET['id'];
        $password = stripslashes($_POST['password']);

        $password = password_hash($password, true);
        $qry = "update users set password = '$password' where user_id = '$user_id' ";
        $result = mysqli_query($con, $qry);

        if($result){
            // send email
            send_email($recieversAddress);
            header("Location: ./login.php");
            die;
        }else{
            
        }
    }













    if($_SERVER['REQUEST_METHOD'] == "GET"){
        if(isset($_GET['id'])){
            $user_id = $_GET['id'];
          
            
            $query = "select * from password_resets where user_id = '$user_id'";
            $result = mysqli_query($con, $query);

            if($result -> num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $status = $row['token_status'];
                    if(empty($status)){
                    ?>
                        
                        <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Password Reset</title>
                        <script src="https://cdn.tailwindcss.com"></script>
                    </head>
                    <body class="flex justify-center items-center h-screen bg-gray-100">
                        <div id="box" class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm text-center">
                            <div class="text-lg font-bold">Password Reset</div>
                            <div class="text-sm text-gray-500 mb-4">Create a new password.</div>
                            <br> 
                            <form action="" method="post" class="space-y-4">
                                <label for="password" class="block text-left text-gray-700">Password</label>
                                <input type="password" name="password" id="password" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="Enter your new password">
                                <input type="submit" value="Submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 cursor-pointer">
                            </form>
                        </div>
                    </body>
                    </html>

                    <?php
                    }else{
                        header("Location: ./login.php");
                        die;
                    }
                }
            }
        }
    }

?>


<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="sign-up.css">
</head>
<body>
   
    <div id="box" class="form-box">
    <div class="log-text">Password Reset</div>
    <div class="mini-text">Create a new password.</div>
    <br> 
        <form action="" method="post">
            <label for="email">Password</label><br>
            <input type="password" name="password" id=""><br>
            <input type="submit" value="Submit" class="login-btn"><br>
         
        </form>

    </div>
</body>
</html> -->
































