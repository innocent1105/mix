<?php
    require "../db.php";

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $stmt = $conn->prepare("INSERT INTO offices (province_id, name, description, email, contact) values ( ?,?,?,?,?)");
        $stmt->bind_param("sssss", $province_id ,$office_name, $office_des, $office_email, $office_number);
        
        $province_id = stripslashes($_POST['province_id']);
        $office_name = stripslashes($_POST['office_name']);
        $office_des = stripslashes($_POST['description']);
        // $office_img = stripslashes($_POST['image']);
        $office_email = stripslashes($_POST['email']);
        $office_number = stripslashes($_POST['contact']);

        if ($stmt->execute()) {
            $response = "success";
            header("Location: ../add_office.php?response=$response");
        } else {
            // echo "Error: " . $stmt->error;
            $response = "error";
            header("Location: ../add_office.php?response=$response");
        }

        $stmt->close();
        $mysqli->close();
    }
?>
            











































