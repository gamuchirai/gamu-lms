<?php
session_start();
require_once '../../config/db_config.php';

header('Content-Type: application/json');

// Only admins can assign instructors
if (!isset($_SESSION['user_id']) || ($_SESSION['role_name'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$course_id = intval($_POST['course_id'] ?? 0);
$instructor_id = intval($_POST['instructor_id'] ?? 0);

if ($course_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid course id']);
    exit;
}

// Ensure the mapping table exists
$create_sql = "CREATE TABLE IF NOT EXISTS course_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_course_user (course_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($create_sql);

// If instructor_id is 0 or empty, remove any existing mapping
if ($instructor_id <= 0) {
    $del = $conn->prepare("DELETE FROM course_instructors WHERE course_id = ?");
    $del->bind_param('i', $course_id);
    if ($del->execute()) {
        echo json_encode(['success' => true, 'message' => 'Instructor unassigned']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unassign instructor: ' . $conn->error]);
    }
    $del->close();
    $conn->close();
    exit;
}

// Verify the user is actually an instructor
$vr = $conn->prepare("SELECT u.id FROM users u JOIN user_roles r ON u.role_id = r.id WHERE u.id = ? AND r.role = 'instructor' LIMIT 1");
$vr->bind_param('i', $instructor_id);
$vr->execute();
$vr->store_result();
if ($vr->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Selected user is not an instructor']);
    $vr->close();
    $conn->close();
    exit;
}
$vr->close();

// Remove any existing mapping for course, then insert the new mapping
$del2 = $conn->prepare("DELETE FROM course_instructors WHERE course_id = ?");
$del2->bind_param('i', $course_id);
$del2->execute();
$del2->close();

$ins = $conn->prepare("INSERT INTO course_instructors (course_id, user_id) VALUES (?, ?)");
$ins->bind_param('ii', $course_id, $instructor_id);
if ($ins->execute()) {
    echo json_encode(['success' => true, 'message' => 'Instructor assigned']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign instructor: ' . $conn->error]);
}
$ins->close();
$conn->close();
