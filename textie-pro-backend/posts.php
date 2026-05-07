<?php

include("./connection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);
$user_id = stripslashes($data['user_id']);

$posts = [];
$matched_ids = [];

// Get user's interests
$user_sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $user_sql);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $interest_sql = "SELECT interest FROM interests WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $interest_sql);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $interest_result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($interest_result)) {
        $interest = $row['interest'];

        // Find users with same interest
        $match_sql = "SELECT DISTINCT user_id FROM interests WHERE interest = ? AND user_id != ? LIMIT 20";
        $stmt = mysqli_prepare($con, $match_sql);
        mysqli_stmt_bind_param($stmt, "ss", $interest, $user_id);
        mysqli_stmt_execute($stmt);
        $match_result = mysqli_stmt_get_result($stmt);

        while ($match = mysqli_fetch_assoc($match_result)) {
            $match_id = $match['user_id'];
            if (!in_array($match_id, $matched_ids)) {
                $matched_ids[] = $match_id;
            }
        }
    }

    // Fetch posts from matched users
    if (count($matched_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($matched_ids), '?'));
        $types = str_repeat('s', count($matched_ids));

        $stmt = mysqli_prepare($con, "SELECT * FROM posts WHERE user_id IN ($placeholders) ORDER BY date_created DESC LIMIT 20");
        mysqli_stmt_bind_param($stmt, $types, ...$matched_ids);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $post_user_id = $row['user_id'];
            $post_id = $row['id'];

            // Get user info
            $user_stmt = mysqli_prepare($con, "SELECT username, pp FROM users WHERE user_id = ? LIMIT 1");
            mysqli_stmt_bind_param($user_stmt, "s", $post_user_id);
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $user_data = mysqli_fetch_assoc($user_result);

            $likes = 0;
            $liked = false;

            $likes_qry = "select * from likes where post_id = '$post_id' ";
            $likes_result = mysqli_query($con, $likes_qry);
    
            if($likes_result -> num_rows > 0){
                while($likes_row = $likes_result -> fetch_assoc()){
                    $likes++;
                }
            }

            $likes_qry = "select * from likes where user_id = '$user_id' and post_id = '$post_id' ";
            $likes_result = mysqli_query($con, $likes_qry);
    
            if($likes_result -> num_rows > 0){
                $liked = true;
            }else{
                $liked = false;
            }

            $comments = 0;
            $comment_query = "select * from comments where post_id = '$post_id' ";
            $comment_result = mysqli_query($con, $comment_query);

            if($comment_result -> num_rows > 0){
                while($comment_row = $comment_result -> fetch_assoc()){
                    $comments++;
                }
            }

            $views = $row['views'];

            $views_query = "select * from views where post_id = '$post_id' and user_id = '$user_id' ";
            $views_result = mysqli_query($con, $views_query);

            if($views_result -> num_rows === 0){
                $update_views = "insert into views (user_id, post_id) values ('$user_id', '$post_id')";
                $views_result2 = mysqli_query($con, $update_views);

                if($views_result2){
                    
                }
            }

            $new_views = $views + 1;
            
            $update_post_views = "update posts set views = '$new_views' where id = '$post_id' ";
            $update_res = mysqli_query($con, $update_post_views);

            if(!$update_res){
                // error
            }




            $posts[] = [
                "id" => $row['id'],
                "user_id" => $post_user_id,
                "username" => $user_data['username'],
                "pp" => $user_data['pp'],
                "text" => $row['post_text'],
                "images" => $row['images'],
                "views" => $views,
                "likes" => $likes || 0,
                "liked" => $liked,
                "comments" => $comments,
                "shares" => $row['shares'],
                "date" => $row['date_created']
            ];
        }
    }
}

// If no posts found from matched users, get random posts
if (count($posts) === 0) {
    $qry = "SELECT * FROM posts WHERE user_id != ? ORDER BY RAND() LIMIT 10";
    $stmt = mysqli_prepare($con, $qry);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $post_user_id = $row['user_id'];
        $post_id = $row['id'];
        


        $user_stmt = mysqli_prepare($con, "SELECT username, pp FROM users WHERE user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($user_stmt, "s", $post_user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_data = mysqli_fetch_assoc($user_result);

        $likes = 0;
        $liked = false;

        $likes_qry = "select * from likes where post_id = '$post_id' ";
        $likes_result = mysqli_query($con, $likes_qry);

        if($likes_result -> num_rows > 0){
            while($likes_row = $likes_result -> fetch_assoc()){
                $likes++;
            }
        }

        $likes_qry = "select * from likes where user_id = '$user_id' and post_id = '$post_id' ";
        $likes_result = mysqli_query($con, $likes_qry);

        if($likes_result -> num_rows > 0){
            $liked = true;
        }else{
            $liked = false;
        }

        $comments = 0;
        $comment_query = "select * from comments where post_id = '$post_id' ";
        $comment_result = mysqli_query($con, $comment_query);

        if($comment_result -> num_rows > 0){
            while($comment_row = $comment_result -> fetch_assoc()){
                $comments++;
            }
        }



        $views = $row['views'];

        
        

        $views_query = "select * from views where post_id = '$post_id' and user_id = '$user_id' ";
        $views_result = mysqli_query($con, $views_query);

        if($views_result -> num_rows === 0){
            $update_views = "insert into views (user_id, post_id) values ('$user_id', '$post_id')";
            $views_result2 = mysqli_query($con, $update_views);

            if($views_result2){
                
            }
        }

        $new_views = $views + 1;
        
        $update_post_views = "update posts set views = '$new_views' where id = '$post_id' ";
        $update_res = mysqli_query($con, $update_post_views);

        if(!$update_res){
            // error
        }


        

        $posts[] = [
            "id" => $row['id'],
            "user_id" => $post_user_id,
            "username" => $user_data['username'],
            "pp" => $user_data['pp'],
            "text" => $row['post_text'],
            "images" => $row['images'],
            "views" => $views,
            "likes" => $likes,
            "liked" => $liked,
            "comments" => $comments,
            "shares" => $row['shares'],
            "date" => $row['date_created']
        ];
    }
}

shuffle($posts);
shuffle($posts);
shuffle($posts);
shuffle($posts);

echo json_encode($posts);

?>
