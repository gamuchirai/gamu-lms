<?php
// Update student information
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

// Process the update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    
    // Validate inputs
    if ($student_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        exit();
    }
    
    if (empty($firstname) || empty($lastname) || empty($email) || empty($gender) || empty($dob)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Check if email is already used by another student
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already in use by another user']);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    
    // Update student information
    $update_stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, gender = ?, dob = ? WHERE id = ? AND role_id = 1");
    $update_stmt->bind_param("sssssi", $firstname, $lastname, $email, $gender, $dob, $student_id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or student not found']);
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
