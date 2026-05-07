<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');
require_once 'connection.php';

$input = json_decode(file_get_contents("php://input"), true);

$token = stripslashes($input['token']);

if(empty($token)){
    echo json_encode([
        "success" => "false",
        "message" => "invalid token"
    ]);
}



$stmt = $pdo->prepare("
    SELECT 
        user_id,
        public_id,
        username,
        fullname,
        email,
        account_type,
        gender,
        avatar
    FROM users
    WHERE public_id = ?
    LIMIT 1
");
$stmt->execute([$token]);

$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

$user_name    = $userData['username'];
$full_name    = $userData['fullname'];
$email        = $userData['email'];
$account_type = $userData['account_type'];
$gender       = $userData['gender'];
$avatar       = $userData['avatar'];



try {
    $stmt = $pdo->prepare("
        SELECT 
            u.public_id, 
            u.phone_number,
            w.balance, 
            w.currency, 
            w.wallet_type
        FROM users u
        JOIN wallets w ON u.user_id = w.user_id
        WHERE u.public_id = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $walletData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$walletData) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User data or wallet not found."]);
        exit;
    }

    $transactions = [];

    $qry = "SELECT user_id FROM users WHERE public_id = ? LIMIT 1";
    $stmt = $pdo->prepare($qry);
    $stmt->execute([$token]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    $user_id = $user['user_id'];


   $qry = "
        SELECT *
        FROM transactions
        WHERE (sender_id = ? OR receiver_id = ?)
        AND status = 'successful'
        ORDER BY created_at DESC
        LIMIT 8
    ";


    $stmt = $pdo->prepare($qry);
    $stmt->execute([$user_id, $user_id]);

    $transactions_ = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $other_sender_name = "";
    $other_sender_number = "";

    foreach ($transactions_ as $transaction) {
        $other_sender_name = "";
        $other_sender_number = "";
        $is_sender = false;
        if($user_id == $transaction['sender_id']){
            $is_sender = true;
            
            $sender_qry = "SELECT fullname, phone_number FROM users WHERE user_id = ? LIMIT 1";
            $stmt = $pdo->prepare($sender_qry);
            
            $stmt->execute([$transaction['receiver_id']]); 
            
            $sender_data = $stmt->fetch(PDO::FETCH_ASSOC); 
            
            if ($sender_data) {
                $other_sender_name = $sender_data['fullname'];
                $other_sender_number = $sender_data['phone_number'];
            }
        }else{
            $is_sender = false;
            $sender_qry = "SELECT fullname, phone_number FROM users WHERE user_id = ? LIMIT 1";
            $stmt = $pdo->prepare($sender_qry);
            
            $stmt->execute([$transaction['sender_id']]); 
            
            $sender_data = $stmt->fetch(PDO::FETCH_ASSOC); 
            
            if ($sender_data) {
                $other_sender_name = $sender_data['fullname'];
                $other_sender_number = $sender_data['phone_number'];
            }
        };

        $fee = 0.85;
        $total = 0;
        $amount = $transaction['amount'];
        $total = $amount + $fee;

        $transactions[] = [
            'transaction_id' => $transaction['t_id'],
            'amount'         => $transaction['amount'],
            'fee' => $fee,
            'total' => $total,
            'status'         => $transaction['status'],
            'date'           => $transaction['created_at'],
            'sender_id'      => $transaction['sender_id'],
            'receiver_id'    => $transaction['receiver_id'],
            'reference'      => $transaction['reference'],
            'type'    => $transaction['t_type'],
            'from_me'    => $is_sender,
            'date'    => $transaction['created_at'],
            'other_sender_name' => $other_sender_name,
            'other_sender_number' => $other_sender_number 
        ];
    }



    if($avatar == null || $avatar == ""){
        $avatar = "avatar1.jpg";
    }



    // http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "User data fetched successfully.",
        "data" => [
            "public_id" => $walletData['public_id'],
            "phone_number" => $walletData['phone_number'],
            "full_name" => $full_name,
            "username" => $user_name,
            "email" => $email,
            "account_type" => $account_type,
            "gender" => $gender,
            "avatar" => $avatar,
            "wallet" => [
                "type" => $walletData['wallet_type'],
                "balance" => $walletData['balance'],
                "currency" => $walletData['currency'],
            ],
            "transactions" => $transactions
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: Could not fetch data."
    ]);
}


















