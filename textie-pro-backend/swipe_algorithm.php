<?php

include("./connection.php");

header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Receive JSON
$data = json_decode(file_get_contents("php://input"), true);
$user_id = stripslashes($data['user_id']);

$interests = [];
$users = [];
$matched_ids = []; // Track IDs to avoid duplicates

// Get user info
$user_sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $user_sql);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);

    // Get user's interests
    $interest_sql = "SELECT interest FROM interests WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $interest_sql);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $interest_result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($interest_result)) {
        $interest = $row['interest'];

        // Find users with same interest
        $match_interest_sql = "SELECT user_id FROM interests WHERE interest = ? AND user_id != ? LIMIT 10";
        $stmt = mysqli_prepare($con, $match_interest_sql);
        mysqli_stmt_bind_param($stmt, "ss", $interest, $user_id);
        mysqli_stmt_execute($stmt);
        $matches_result = mysqli_stmt_get_result($stmt);

        while ($match = mysqli_fetch_assoc($matches_result)) {
            $match_id = $match['user_id'];
            if (in_array($match_id, $matched_ids)) continue; // Skip duplicates

            $match_user_sql = "SELECT * FROM users WHERE user_id = ? AND gender != ? LIMIT 1";
            $stmt = mysqli_prepare($con, $match_user_sql);
            mysqli_stmt_bind_param($stmt, "ss", $match_id, $user['gender']);
            mysqli_stmt_execute($stmt);
            $match_user_result = mysqli_stmt_get_result($stmt);

            if ($match_user_result && mysqli_num_rows($match_user_result) > 0) {
                $match_user = mysqli_fetch_assoc($match_user_result);

                $users[] = [
                    'user_id' => $match_user['user_id'],
                    'username' => $match_user['username'],
                    'fullname' => $match_user['fullname'],
                    'email' => $match_user['email'],
                    'phone_number' => $match_user['phone_number'],
                    'pp' => $match_user['pp'],
                    'dob' => $match_user['dob'],
                    'gender' => $match_user['gender'],
                    'occupation' => $match_user['occupation'],
                    'city' => $match_user['city'],
                    'date_created' => date('d M , h:i A', strtotime($match_user['date_created']))
                ];
 
                $matched_ids[] = $match_user['user_id'];
            }
        }
    }

    // If no matches found, pick 10 random users from interests (excluding current user)
    if (count($users) === 0) {
        $random_sql = "SELECT DISTINCT user_id FROM interests WHERE user_id != ? ORDER BY RAND() LIMIT 10";
        $stmt = mysqli_prepare($con, $random_sql);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $random_result = mysqli_stmt_get_result($stmt);

        $post_id = 1;
        while ($random = mysqli_fetch_assoc($random_result)) {
            $random_id = $random['user_id'];

            $user_sql = "SELECT * FROM users WHERE user_id = ? AND gender != ?  LIMIT 1";
            $stmt = mysqli_prepare($con, $user_sql);
            mysqli_stmt_bind_param($stmt, "ss", $random_id, $user['gender']);
            mysqli_stmt_execute($stmt);
            $rand_user_result = mysqli_stmt_get_result($stmt);

            if ($rand_user_result && mysqli_num_rows($rand_user_result) > 0) {
                $match_user = mysqli_fetch_assoc($rand_user_result);

                $users[] = [
                    'id' => $post_id,
                    'user_id' => $match_user['user_id'],
                    'username' => $match_user['username'],
                    'fullname' => $match_user['fullname'],
                    'email' => $match_user['email'],
                    'phone_number' => $match_user['phone_number'],
                    'pp' => $match_user['pp'],
                    'dob' => $match_user['dob'],
                    'gender' => $match_user['gender'],
                    'occupation' => $match_user['occupation'],
                    'city' => $match_user['city'],
                    'date_created' => date('d M , h:i A', strtotime($match_user['date_created']))
                ];

                $post_id++;
            }
        }
    }

    shuffle($users);

    echo json_encode($users);
} else {
    echo json_encode(['error' => 'User not found']);
}
