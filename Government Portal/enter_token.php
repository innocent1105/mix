<?php

    include("dbconnect.php");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 


    $error = "";

    if(isset($_GET['tk'])){
        $user_id = $_GET['tk'];
    }

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $token = $_POST['token'];
        
        $query = "select * from password_resets where user_id = '$user_id'";
        $result = mysqli_query($con, $query);

        if($result -> num_rows > 0){
            while($row = $result->fetch_assoc()){
                if($token == $row['token']){
                    header("Location: ./password.php?id=$user_id");
                }else{
                    $error =  $error = `
                        <div class="error-box border bg-red-50 border-red-500 text-red-500 p-2">
                            Wrong token, please enter the digits sent to your email address.
                        </div>
                    `;
                    echo $error;
                }
            }
        }else{
            echo "wrong user id";
        }
    }
    

    if($error == "wrong_token"){
    //    echo "error is ok";
    }else{
        // echo "no error found";
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center h-screen bg-gray-100">
    <div id="box" class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm text-center">
        <div class="text-lg font-bold">Enter OTP</div>
        <div class="text-sm text-gray-500 mb-4">An OTP has been sent to your Email. Check your email inbox.</div>
        <br> 
        <?php echo isset($error) ? $error : ''; ?>
        
        <form action="" method="post" class="space-y-4">
            <label for="token" class="block text-left text-gray-700">OTP</label>
            <input type="text" name="token" id="token" placeholder="Enter your OTP here" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="submit" value="Submit" class="w-full bg-green-700 text-white ring-green-700 py-2 rounded-lg hover:bg-green-600 cursor-pointer">
        </form>
    </div>
</body>
</html>















