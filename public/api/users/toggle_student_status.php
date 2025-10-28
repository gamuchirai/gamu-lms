<?php
// Toggle student active/suspended status
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

// Process the toggle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $active = intval($_POST['active'] ?? 0);
    
    // Validate inputs
    if ($student_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        exit();
    }
    
    if ($active !== 0 && $active !== 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit();
    }
    
    // Update student status
    $update_stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ? AND role_id = 1");
    $update_stmt->bind_param("ii", $active, $student_id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            $status_text = $active === 1 ? 'activated' : 'suspended';
            echo json_encode(['success' => true, 'message' => "Student $status_text successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found or no change needed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $update_stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
