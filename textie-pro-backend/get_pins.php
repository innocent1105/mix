<?php
include("./connection.php");

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

$data = json_decode(file_get_contents("php://input"), true);

$user = $data['user_id'];

try {

    
    $pins = [];

    $likes = 0;
    $isLiked = false;

    $query = "SELECT * FROM pins limit 50";
    $result = mysqli_query($con, $query);


    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $post_id = $row['id'];
            $user_id = $row['user_id'];
            $user_query = "select * from users where user_id = '$user_id' limit 1";
            $user_res= mysqli_query($con, $user_query);

            if($user_res && mysqli_num_rows($user_res) > 0){
                while( $user_row = mysqli_fetch_assoc($user_res)){
                    $username = $user_row['username'];
                    $full_name = $user_row['fullname'];
                    $profile_pic = $user_row['pp'];

                    $if_liked = "select * from likes where user_id = '$user' and post_id = '$post_id'";
                    $_if_liked_result = mysqli_query($con, $if_liked);
                
                    if($_if_liked_result -> num_rows > 0){
                        while($like_row = $_if_liked_result -> fetch_assoc()){
                            $isLiked = true;
                        }
                    }


                    $likes_ = "select * from likes where post_id = '$post_id'";
                    $likes_result = mysqli_query($con, $if_liked);
                
                    if($likes_result -> num_rows > 0){
                        while($like_row = $likes_result -> fetch_assoc()){
                            $likes++;
                        }
                    }


                    $comments_ = "select * from comments where post_id = '$post_id'";
                    $comments_result = mysqli_query($con, $comments_);
                    $comments = 0;
                    if($comments_result -> num_rows > 0){
                        while($comment_row = $comments_result -> fetch_assoc()){
                            $comments++;
                        }
                    }

                    $add_view = "update pins set views = views + 1 where id = '$post_id'";
                    mysqli_query($con, $add_view);






                    $pins[] = [
                        "id" => $row['id'],
                        "user_id" => $row['user_id'],
                        "username" => $username,
                        "fullname" => $full_name,
                        "profile" => $profile_pic,
                        "name" => $row['name'],
                        "description" => $row['des'],
                        "tag" => $row['tag'],
                        "images" => $row['images'],
                        "location" => $row['location'],
                        "lat" => $row['lat'],
                        "lng" => $row['lng'],
                        "rating" => $row['rating'],
                        "views" => $row['views'],
                        "likes" => $likes,
                        "liked" => $isLiked,
                        "comments" => $comments,
                        "shares" => $row['shares'],
                        "date_created" => $row['date_created']
                    ];

                }
            }



            
        }
    }

    shuffle($pins);
    echo json_encode($pins);
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>
