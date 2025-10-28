<?php
session_start();
require_once '../../config/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid method']); exit; }

$course_id = intval($_POST['course_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($course_id <= 0 || $title === '') { echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }

$role = $_SESSION['role_name'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);
if (!in_array($role, ['instructor','admin'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

// If instructor, ensure assignment
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($role === 'instructor' && $check && $check->num_rows > 0) {
    $vr = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ? LIMIT 1");
    $vr->bind_param('ii', $course_id, $user_id); $vr->execute(); $vr->store_result();
    if ($vr->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
    $vr->close();
}

$stmt = $conn->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $course_id, $title, $content);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'lesson_id'=>$stmt->insert_id]);
} else {
    echo json_encode(['success'=>false,'message'=>'DB error: '.$conn->error]);
}
$stmt->close(); $conn->close();
