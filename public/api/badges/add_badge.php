<?php
session_start();
require_once '../../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role_name'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Name and description are required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO badges (name, description) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $description);

if ($stmt->execute()) {
    $badge_id = $stmt->insert_id;
    echo json_encode(['success' => true, 'badge_id' => $badge_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add badge: ' . $conn->error]);
}

$stmt->close();
$conn->close();
