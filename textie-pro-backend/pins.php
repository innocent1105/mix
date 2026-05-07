<?php
include("./connection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $des = $_POST['des'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';
    $location = $_POST['location'] ?? '';

    // if (!$user_id || !$name || !$des || !$lat || !$lng) {
    //     http_response_code(400);
    //     echo json_encode(["error" => "Missing required fields"]);
    //     exit;
    // }

    $image_url = "";
    if (isset($_FILES["image"])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image_url = $filename;
        }
    }

    $sql = "INSERT INTO pins (user_id, name, des, tag, images, location, lng, lat, rating, views, likes, comments, shares) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', '0', '0')";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $user_id, $name, $des, $tag, $image_url, $location, $lng, $lat);
    $res = mysqli_stmt_execute($stmt);

    if ($res) {
        echo json_encode([
            "success" => true,
            "message" => "Pin saved successfully",
            "data" => [
                "id" => mysqli_insert_id($con),
                "user_id" => $user_id,
                "name" => $name,
                "des" => $des,
                "tag" => $tag,
                "images" => $image_url,
                "location" => $location,
                "lng" => $lng,
                "lat" => $lat
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save pin"]);
    }
}
?>
