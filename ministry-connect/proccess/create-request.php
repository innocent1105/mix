<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $requester_ministry_id = $_SESSION['ministry_id'];
    $target_ministry_id = $_POST['target_ministry_id'];
    $request_type = $_POST['request_type'];
    $purpose = $_POST['purpose'];
    $description = $_POST['description'] ?? '';
    $record_id = $_POST['record_id'] ?? '';
    
    try {
        // Create the request
        $stmt = $pdo->prepare("INSERT INTO request 
                              (requester_ministry_id, target_ministry_id, requester_user_id, 
                               request_type, purpose, description, record_id, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$requester_ministry_id, $target_ministry_id, $user_id, 
                       $request_type, $purpose, $description, $record_id]);
        
        $request_id = $pdo->lastInsertId();
        
        // Log the activity
        logActivity($pdo, $user_id, 'create_request', 
                   "Created new data request #$request_id to ministry ID $target_ministry_id", 
                   $request_id);
        
        // Redirect to requests page with success message
        $_SESSION['success'] = "Request created successfully!";
        header("Location: ../pages/requests.php");
        exit();
        
    } catch (PDOException $e) {
        // Handle error
        $_SESSION['error'] = "Error creating request: " . $e->getMessage();
        header("Location: ../pages/new-request.php");
        exit();
    }
} else {
    header("Location: ../pages/new-request.php");
    exit();
}
?>