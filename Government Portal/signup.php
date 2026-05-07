<?php
    session_start();
    include("dbconnect.php");
    include("functions.php");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 

    $error = "";

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $email = stripslashes($_POST['email']);
        $user_name = stripslashes($_POST['user_name']);
        $password = stripslashes($_POST['password']);
        $account_type = "citizen";

        if(!empty($email) && !empty($password) && !is_numeric($email)){
            // save to db
            $user_id = random_num(1000000);
            $password = password_hash($password, true);
            $query = "insert into users (user_id,user_name,email,password,account_type) values ('$user_id','$user_name','$email','$password','$account_type')";

            mysqli_query($con,$query);
         

            // email

            $recieversAddress = $email;
            $username = $user_name;

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
                            Thank you for choosing <b>Online Citizen Service Portal</b>. Your account has been created successfully.<br><br>
                            Always remember to keep your password safe and do not share it with anyone.<br><br>
                        </div>
                        <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
                    ';
                    $mail->AltBody = "Hello, $username,\n\nThank you for choosing Online Citizen Service Portal. Your account has been created successfully.\n\nAlways remember to keep your password safe and do not share it with anyone.\n\nBest Regards,\nOnline Citizen Service Portal Team";

                            
                    // Send 
                    $mail->send();
                    // echo 'Email sent successfully!';

                   

                    header("Location: login.php");
                    die;
                } catch (Exception $e) {
                    $error = "failed to send email";
                }
            
        }else{
            $error = '<p class="error-bar">Please enter valid information in all fields.</p>';
        }
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="./css/tailwind.css">
</head>
<style>
    body{
        background-image: url("Images/people.jpg");
        background-size: cover;
    }
</style>
<body class=" flex items-center justify-center min-h-screen p-4">
    <?php include "./architecture/top_bar.php" ?>
    <div id="form-box" class="form-box bg-white p-8 rounded-lg shadow-lg w-full max-w-md md:max-w-lg lg:max-w-xl">
        <div class="log-text text-3xl font-bold text-center mb-6 text-green-800">Sign Up</div>
        <?php echo $error ?>
        <form action="" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-green-700 font-medium">Email</label>
                <input type="text" name="email" id="email" placeholder="Enter your email address" 
                       class="w-full px-4 py-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <div>
                <label for="user_name" class="block text-green-700 font-medium">Full Name</label>
                <input type="text" name="user_name" id="user_name" placeholder="What is your full name?"
                       class="w-full px-4 py-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <div>
                <label for="password" class="block text-green-700 font-medium">Password</label>
                <input type="password" name="password" id="password" placeholder="Password"
                       class="w-full px-4 py-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <input type="submit" value="Signup" 
                   class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition cursor-pointer">
            <div class="text-center">
                <p class="text-green-700">Already have an account? 
                    <a href="login.php" class="text-green-500 hover:underline">Login here.</a>
                </p>
            </div>
        </form>
    </div>

</body>
</html>

