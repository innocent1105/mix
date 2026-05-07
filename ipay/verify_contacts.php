

<?php
require_once "connection.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST");

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['phone_numbers']) || !is_array($input['phone_numbers'])) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid payload"
        ]);
        exit;
    }

    $normalized = [];
    foreach ($input['phone_numbers'] as $number) {
        $clean = preg_replace('/\D+/', '', $number); 

         // 9 digits → add leading zero
        if (strlen($clean) === 9) {
            $clean = '0' . $clean;
        }

        if (strlen($clean) >= 11) {
            $normalized[] = $clean;
        }
    }

    $normalized = array_values(array_unique($normalized));

    if (empty($normalized)) {
        echo json_encode([
            "success" => true,
            "matched_users" => [],
            "unmatched_numbers" => []
        ]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($normalized), '?'));

    $sql = "
        SELECT 
            user_id,
            public_id,
            username,
            fullname,
            phone_number,
            avatar
        FROM users
        WHERE REPLACE(REPLACE(REPLACE(phone_number, '+', ''), ' ', ''), '-', '') 
        IN ($placeholders)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($normalized);
    $matchedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchedNumbers = array_map(
        fn($u) => preg_replace('/\D+/', '', $u['phone_number']),
        $matchedUsers
    );

    $unmatched = array_values(array_diff($normalized, $matchedNumbers));

    echo json_encode([
        "success" => true,
        "matched_users" => $matchedUsers,
        "unmatched_numbers" => $unmatched
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
