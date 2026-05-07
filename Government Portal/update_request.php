<?php
    require "./header.php";
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 

    if($user_account == "citizen"){
        header("Location: ./user_request.php");
        die;
    }
    $admin_username = $user_data['user_name'];

   

    

    if(isset($_GET['id'])){
        $request_id = stripslashes($_GET['id']);

        $query = "select * from requests where request_id = '$request_id' limit 1";
        $result = mysqli_query($con, $query);

        if($result -> num_rows > 0){
            while($row = $result->fetch_assoc()){
                $requesting_user_id = $row['user_id'];
                $request_id = $row['request_id'];
                $request_type = $row['request_type'];
                $request_status = $row['request_status'];

                // get user data
                $qry = "select * from users where user_id = '$requesting_user_id' limit 1";
                $res = mysqli_query($con, $qry);
                if($res -> num_rows > 0){
                    while($row = $res -> fetch_assoc()){
                        $username = $row['user_name'];
                        $email = $row['email'];
                        $province = $row['province'];
                                    
                    }
                }
            }      
        }
        

    }else{
        // header("Location: ./index.php");
    }

    // response
    $response = "";
    if(isset($_GET['response'])){
        $response = stripslashes($_GET['response']);
        if($response == "success"){
            $response = `
                <div class=" mt-4 border border-green-900 p-2 text-green-700 bg-green-50 flex justify-between">
                    Request status updated successfully. <br> An Email has been sent to the citizen.
                </div>  
            `;
        }else if($response == "error"){
            $response = `
                <div class=" mt-4 border border-red-900 p-2 text-red-700 bg-red-50 flex justify-between">
                    Error - Failed to send Email. Please connect to the internet.
                </div>  
            `;
        }else{
            $response = `
                <div class=" mt-4 border border-red-900 p-2 text-red-700 bg-red-50 flex justify-between">
                    Error - Failed to Update request status.
                </div>  
            `;
        }
    }





    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $status = $_POST['status'];
        $qry = "update requests set user_id = '$requesting_user_id ', request_type = '$request_type', request_status = '$status', request_id = '$request_id' where request_id = '$request_id'";
        $result = mysqli_query($con, $qry);

      
        $permit_number = random_num(50);
        if($result){
            if($status == "approved"){
                $request_number = random_num(500);
                if($request_type == "certificate"){

                    $sql = "select * from certificates where request_id = '$request_id' limit 1";
                    $res = mysqli_query($con, $sql);
                    if($res->num_rows == 0){
                        $query = "insert into certificates (request_id, certificate_number, issued_by) values ('$request_id', '$request_number', '$admin_username')";
                        $result = mysqli_query($con, $query);
                        if($result){
                            // success
                        }else{
                            header("Location: ./update_request.php?id=$request_id & response=error4");
                            die;
                        }
                    }   
                }else{
                    $sql = "select * from permits where request_id = '$request_id' limit 1";
                    $res = mysqli_query($con, $sql);
                    if($res->num_rows == 0){
                        $query = "insert into permits (request_id, permit_number, issued_by) values ('$request_id', '$request_number', '$admin_username')";
                        $result = mysqli_query($con, $query);
                        if($result){
                            // success

                        }else{
                            header("Location: ./update_request.php?id=$request_id & response=error3");
                            die;
                        }
                    }else{
                        header("Location: ./update_request.php?id=$request_id & response=error2");
                        die;
                    } 
                }
            } 
            
            if($status == "rejected" || $status == "pending"){
                if($request_type == "permit"){
                    $qry = "select * from permits where request_id = '$request_id' limit 1";
                    $res = mysqli_query($con, $qry);
                    if($res -> num_rows > 0){
                        $qry = "delete from permits where request_id = '$request_id'";
                        $res = mysqli_query($con, $qry);
                        if($res){
                            // echo "deleted"; success
                        }else{
                            // echo "failed to delete";
                        }
                    }else{
                        // echo "no such record";
                    }
                }else{
                    $qry = "select * from certificates where request_id = '$request_id' limit 1";
                    $res = mysqli_query($con, $qry);
                    if($res -> num_rows > 0){
                        $qry = "delete from certificates where request_id = '$request_id'";
                        $res = mysqli_query($con, $qry);
                        if($res){
                            // echo "deleted";
                        }else{
                            // echo "failed to delete";
                        }
                    }else{
                        // echo "no such record";
                    }
                }
            }




            // send notification
            $note_name = "$request_type";
            $note_des = "";

        

            if($request_type == "permit"){
                $note_des = "Your ". $request_category ." permit has been " . $status . ". $reason";
            }else{
                $note_des = "Your ".  $request_category . " certificate has been " . $status . ". $reason";
            }

            $qry = "insert into notifications (user_id, note_type, category, request_id, notification_name, notification_description, status) values ('$requesting_user_id', '$request_type', '$request_category', '$request_id' ,'$note_name', '$note_des', '$status')";
            $result3 = mysqli_query($con, $qry);

            if(!$result3){
                echo "an error occurred - No notification";
            }
 






            // send email
            $recieversAddress = $email;
            $username = $username;
          

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

                if($status == 'pending'){
                    $mail->Body = '
                        <p>Hello, <b>' . htmlspecialchars($username) . '</b>,</p>
                        <div style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                            Your request for <b>' . $request_type . ' is now '. $status .'.</b>
                            <br><br>
                            You can view the status from the Online Citizen Service Portal.
                        </div>
                        <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
                    ';
                }else{
                    $mail->Body = '
                    <p>Hello, <b>' . htmlspecialchars($username) . '</b>,</p>
                    <div style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                        Your request for <b>' . $request_type . ' has been '. $status .'.</b>
                        <br><br>
                        You can view the status from the Online Citizen Service Portal.
                    </div>
                    <p>Best Regards,<br><b>Online Citizen Service Portal Team</b></p>
                ';
                }
                $mail->AltBody = "Hello, $username,\n\nThank you for choosing Online Citizen Service Portal.\n\nAlways remember to keep your password safe and do not share it with anyone.\n\nBest Regards,\nOnline Citizen Service Portal Team";

                        
                // Send 
                $mail->send();
                header("Location: ./update_request.php?id=$request_id & response=success");
            }catch(Exception $e) {
                // header("Location: ./update_request.php?id=$request_id & response=error");
                echo "Email could not be sent. Error: {$mail->ErrorInfo}";
            }

        }else{
            header("Location: ./update_request.php?response = failed");
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
</head>
<body>
    <?php include "./architecture/top_bar2.php" ?>
    <div class="mt-18 w-full flex flex-col md:flex-row gap-2 justify-end">

        <div class="main-dashboard w-full md:w-full bg-white p-20 md:pr-6 border pt-6 top-0">
            <div class="header text-md text-gray-500 font-medium">Citizen Requests - Update Request</div>
            
            <?php echo $response ?>

            <div class="request-info w-full md:w-full p-4 mt-8 border">
                <div class="header-text text-lg font-medium text-gray-800"><?php echo $request_type ?> request</div>
                <form action="" method="POST" class="w-full pt-5">
                    <label class="text-xs text-gray-500 px-1">Citizen name</label>
                    <div class="bg-gray-100 mb-4 mt-1 w-full rounded-md p-2 text-gray-700 text-sm">
                        <?php echo $username ?>
                    </div>

                    <label class="text-xs text-gray-500 px-1">Email</label>
                    <div class="bg-gray-100 mb-4 mt-1 w-full rounded-md p-2 text-gray-700 text-sm">
                        <?php echo $email ?>
                    </div>

                    <label class="text-xs text-gray-500 px-1">Request</label>
                    <div class="bg-gray-100 mb-4 mt-1 w-full rounded-md p-2 text-gray-700 text-sm">
                        <?php echo $request_type?>
                    </div>

                    <div class="border border-green-500 border-2 p-4 rounded-md">
                        <label class="text-xs text-gray-500 px-1">Status</label>
                        <div class="bg-gray-100 mt-1 w-full rounded-md text-gray-700 text-sm">
                            <select name="status" id="status" class="bg-gray-100 w-full p-2 rounded-md cursor-pointer">
                                <option value="<?php echo $request_status?>"><?php echo $request_status?></option>
                                <?php if($request_status == "pending"): ?>
                                    <option value="approved">Approve request</option>
                                    <option value="rejected">Reject request</option>
                                <?php elseif($request_status == "approved"): ?>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Reject request</option>
                                <?php else: ?>
                                    <option value="approved">Approve request</option>
                                    <option value="pending">Pending</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="p-2 px-4 mt-5 rounded text-white bg-green-700 focus:bg-green-900 transition-all hover:bg-green-800 border-none font-medium text-sm w-full md:w-auto">Update Request</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
