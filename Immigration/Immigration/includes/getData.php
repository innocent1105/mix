<?php 
    require "../db.php";

    $offices = [];


    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $data = $_POST['province'];
        
        $stmt = $conn->prepare("SELECT * FROM provinces WHERE name = ?");
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        if ($row) {
            $id = $row['id'];
            
            $stmt = $conn->prepare("SELECT * FROM offices WHERE province_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = $result->fetch_assoc();
            
            $offices = [];
            while ($row = $result->fetch_assoc()) {
                $offices[] = $row;
                // echo $row['name'] . "<br>";
            }

            $offices = json_encode($offices);
            echo $offices;

            exit;
        } else {
            echo json_encode("No province found.");
        }
    }else{
        header("Location: ../index.php");
    }


