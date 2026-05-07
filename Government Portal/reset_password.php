<?php
    // session_start();
    include("dbconnect.php");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 


    // send email
    function send_email($recieversAddress, $username, $token){
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
            $mail->addAddress($recieversAddress, $username);
            $mail->Subject = 'Online Citizen Service Portal';
            $mail->Body = '
                <p>Hello, <b>' . htmlspecialchars($username) . '</b>,</p>
                <div style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                    Your password reset OTP : <br><br>
                    <b>' . $token .'<b> 

                    <br><br>
                    Always remember to keep your password safe and do not share it with anyone.<br><br>
                </div>
                <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
            ';
            $mail->AltBody = "Hello, $username,\n\nThank you for choosing Online Citizen Service Portal. Your account has been created successfully.\n\nAlways remember to keep your password safe and do not share it with anyone.\n\nBest Regards,\nOnline Citizen Service Portal Team";

                    
            // Send 
            $mail->send();
        } catch (Exception $e) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    }






    $error = "";

    function random_num($length){
        $text = " ";
        if($length < 10000 ){
           $length = 2000;
        }
     
        $len = rand(1000000, $length);
     
        for ($i=0; $i < $len; $i++) { 
           $text .= rand(100,2000);
           return $text;
        }
    }

     
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $email = stripslashes($_POST['email']);
        
        // check email
        $qry = "select * from users where email ='$email' limit 1";
        $res = mysqli_query($con, $qry);
        if($res -> num_rows > 0){
            while($row = $res->fetch_assoc()){
                $user_id = $row['user_id'];
                $user_name = $row['user_name'];
                $token = random_num(5);
                
                // check tokens
                $sql2 = "select * from password_resets where user_id = '$user_id'";
                $res2 = mysqli_query($con, $sql2);
                if($res2 -> num_rows > 0){
                    $deleteQuery = "delete from password_resets where user_id = '$user_id'";
                    $deleteResult = mysqli_query($con, $deleteQuery);
                    if($deleteResult){
                        // echo "deleted";
                    }else{
                        // echo "failed to delete";       
                    }
                }


                $sql = "insert into password_resets (user_id, email, token) values ('$user_id', '$email', '$token')";
                $result = mysqli_query($con, $sql);
                if($result){
                    // email

                    $recieversAddress = $email;
                    $username = $user_name;
                    send_email($recieversAddress, $username, $token);

                    header("Location: ./enter_token.php?tk={$user_id}");
                }else{
                    echo "failed to send";
                }
            }
        }else{
            echo "no account matches your email";
        }
    }







?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="./css/tailwind.css">
</head>
<body class="flex justify-center items-center h-screen bg-gray-100">
    <div id="box" class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm text-center">
        <div class="text-lg font-bold">Enter email</div>
        <div class="text-sm text-gray-500 mb-4">An OTP will be sent to the Email you provide below.</div>
        <br>
        <?php echo isset($error) ? $error : ''; ?>
        <form action="" method="post" class="space-y-4">
            <label for="email" class="block text-left text-gray-700">Email</label>
            <input type="text" name="email" placeholder="Enter your email" id="email" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
            <input type="submit" value="Send OTP" class="w-full bg-green-700 text-white py-2 rounded-lg hover:bg-green-900 cursor-pointer transition-all">
        </form>
    </div>
</body>
</html>





























