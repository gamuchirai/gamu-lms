<?php
session_start();
require_once '../../../config/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Mark all notifications as read for this user
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET `read` = 1 
        WHERE user_id = ? AND `read` = 0
    ");
    
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read',
            'updated_count' => $stmt->affected_rows
        ]);
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
    ]);
}

$conn->close();
?>