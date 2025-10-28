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

$badge_id = intval($_POST['badge_id'] ?? 0);

if ($badge_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid badge ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM badges WHERE id = ?");
$stmt->bind_param("i", $badge_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete badge: ' . $conn->error]);
}

$stmt->close();
$conn->close();
