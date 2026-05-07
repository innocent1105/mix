<?php 
    require "./header.php";
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 

    $request_type = $response_type = $_GET['response'] ?? "";
  


    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $user_id = $user_data['user_id'];
        $request_type = stripslashes($_POST['request-type']);
        $request_category1 = stripslashes($_POST['request-category1']);
        $request_category2 = stripslashes($_POST['request-category2']);

        $request_category = "";

        if($request_type == "permit"){
            $request_category = $request_category1;
        }else{
            $request_category = $request_category2;
        }


        $request_id = random_num(5);
        $qry = "INSERT INTO requests (user_id, request_type, request_category ,request_id,request_description) VALUES ('$user_id', '$request_type', '$request_category','$request_id' ,'')";
        $result = mysqli_query($con, $qry);
        if($result){
            
            // email
            $recieversAddress = $user_data['email'];
            $username = $user_data['user_name'];
                // request email
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
                            Your submission of <b>' . $request_type . '</b> was successful. An Email will be sent to you once the request status is updated.<br><br>
                            Always remember to keep your password safe and do not share it with anyone.<br><br>
                        </div>
                        <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
                    ';
                    $mail->AltBody = "Hello, $username,\n\nThank you for choosing Online Citizen Service Portal.\n\nAlways remember to keep your password safe and do not share it with anyone.\n\nBest Regards,\nOnline Citizen Service Portal Team";

                            
                    // Send 
                    $mail->send();
                    // echo 'Email sent successfully!';
                    header("Location: ./process_request.php?response = '$request_type'");
                    die;
                } catch (Exception $e) {
                    $error2 = '
                        <div class=" p-2 border border-red-600 bg-red-50 mt-10 mb-2 ">
                            Failed to submit, check your internet connection and try again.
                        </div>
                    ';

                    echo $error2;
                }
        


            // echo "success";
        }else{
            $error = "Error : Failed to submit request";
            header("Location: ./user_request.php?response='$error'");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
</head>
<body class="bg-gray-100 p-5 ">

    <?php include "./architecture/top_bar2.php" ?>

    <div class="w-full flex flex-col md:flex-row gap-2 justify-end">

        <div class="main-dashboard w-full bg-white p-4  border  top-0">
            <div class="success-message mt-8 border p-4">
                <div class="header-text text-2xl text-gray-800">Request submitted successfully</div>
                <div class="mini-text py-2 text-sm text-gray-600">Your submission of <?php echo $request_type ?> request is successful. An email of the status of the request will be sent to you once the status has been updated.</div>
            
                <div class="flex flex-col md:flex-row mt-14 justify-end gap-2">
                    <a href="./user_request.php">
                        <button value="submit" type="submit" class="p-2 px-4 rounded text-blue-700 focus:bg-gray-100 transition-all hover:bg-gray-100 border-none font-medium text-sm w-full md:w-auto">New request</button>
                    </a>
                    <a href="./requests.php">
                        <button value="submit" type="submit" class="p-2 px-4 rounded text-white bg-blue-700 focus:bg-blue-900 transition-all hover:bg-blue-800 border-none font-medium text-sm w-full md:w-auto">Check request status</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
