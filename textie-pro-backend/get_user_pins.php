<?php
include("./connection.php");

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");



$data = json_decode(file_get_contents("php://input"), true);

$user_id = mysqli_real_escape_string($con,$data['user_id']);

try {
    $query = "SELECT * FROM pins where user_id = '$user_id'";
    $result = mysqli_query($con, $query);

    $pins = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pins[] = [
                "id" => $row['id'],
                "user_id" => $row['user_id'],
                "name" => $row['name'],
                "description" => $row['des'],
                "tag" => $row['tag'],
                "images" => $row['images'],
                "location" => $row['location'],
                "lat" => $row['lat'],
                "lng" => $row['lng'],
                "rating" => $row['rating'],
                "views" => $row['views'],
                "likes" => $row['likes'],
                "comments" => $row['comments'],
                "shares" => $row['shares'],
                "date_created" => $row['date_created']
            ];
        }
    }

    
    echo json_encode($pins);
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>
