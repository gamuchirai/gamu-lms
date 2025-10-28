<?php
session_start();
require_once __DIR__ . '/../../../config/db_config.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role_name'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($title) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Title and description are required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
$stmt->bind_param("ss", $title, $description);

if ($stmt->execute()) {
    $course_id = $stmt->insert_id;
    echo json_encode(['success' => true, 'course_id' => $course_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add course: ' . $conn->error]);
}

$stmt->close();
$conn->close();
