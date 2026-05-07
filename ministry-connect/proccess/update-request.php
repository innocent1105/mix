<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuthentication();

if (isset($_GET['id']) && isset($_GET['status'])) {
    $request_id = $_GET['id'];
    $status = $_GET['status'];
    $user_id = $_SESSION['user_id'];
    $responded_by = $user_id;
    
    // Validate status
    $valid_statuses = ['pending', 'approved', 'rejected', 'archived'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status";
        header("Location: ../pages/requests.php");
        exit();
    }
    
    try {
        // Update the request
        $stmt = $pdo->prepare("UPDATE request 
                              SET status = ?, responded_by = ?, date_responded = NOW() 
                              WHERE id = ? AND target_ministry_id = ?");
        $stmt->execute([$status, $responded_by, $request_id, $_SESSION['ministry_id']]);
        
        if ($stmt->rowCount() > 0) {
            // Log the activity
            logActivity($pdo, $user_id, 'update_request', 
                       "Updated request #$request_id status to $status", 
                       $request_id);
            
            $_SESSION['success'] = "Request $status successfully!";
        } else {
            $_SESSION['error'] = "Unable to update request. You may not have permission.";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating request: " . $e->getMessage();
    }
}

// Redirect back to the previous page
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: ../pages/requests.php");
}
exit();
?>