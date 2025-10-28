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

// Check if notification_id is provided
if (!isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

$notification_id = intval($_POST['notification_id']);

try {
    // Mark notification as read (verify it belongs to this user)
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET `read` = 1 
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Notification not found or already read'
            ]);
        }
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to mark notification as read: ' . $e->getMessage()
    ]);
}

$conn->close();
?>