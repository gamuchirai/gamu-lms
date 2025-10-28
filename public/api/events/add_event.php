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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');

if (empty($title) || empty($description) || empty($event_date)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO events (title, description, event_date) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $description, $event_date);

if ($stmt->execute()) {
    $event_id = $stmt->insert_id;
    echo json_encode(['success' => true, 'event_id' => $event_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add event: ' . $conn->error]);
}

$stmt->close();
$conn->close();
