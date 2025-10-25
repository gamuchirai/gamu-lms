<?php
// Delete student from the system
session_start();
require_once '../../config/db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify admin role
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$role = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT ur.role FROM users u LEFT JOIN user_roles ur ON u.role_id = ur.id WHERE u.id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role_result);
    if ($stmt->fetch()) {
        $role = $role_result;
    }
    $stmt->close();
}

if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

// Process the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    
    // Validate input
    if ($student_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        exit();
    }
    
    // Prevent admin from deleting themselves
    if ($student_id === $user_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
        exit();
    }
    
    // Delete student (cascade will handle related records due to foreign key constraints)
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role_id = 1");
    $delete_stmt->bind_param("i", $student_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found or already deleted']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $delete_stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
