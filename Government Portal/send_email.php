<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; 

    $recieversAddress = "mugwadiinnocent@gmail.com";
    $username = "Innocent Mugwadi";
  

    function send_email($recieversAddress, $username){
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

            $mail->setFrom('mugwadiinnocent@gmail.com', 'Your Name'); 
            $mail->addAddress($recieversAddress, $username);
            $mail->Subject = 'Online Citizen Service Portal';
            $mail->Body    = 'Hello '. $username .', this is your account creation comfirmation email.';

            // Send 
            $mail->send();
            echo 'Email sent successfully!';
        } catch (Exception $e) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    }

    send_email($recieversAddress, $username);

?>
