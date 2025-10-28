<?php
session_start();
// Use __DIR__ to reliably include the project's config file from this location.
// The file lives at <project_root>/config/db_config.php; from
// public/api/assignments this is three levels up.
require_once __DIR__ . '/../../../config/db_config.php';
header('Content-Type: application/json');
$course_id = intval($_GET['course_id'] ?? 0);
if ($course_id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid course_id']); exit; }
$role = $_SESSION['role_name'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);
if (!in_array($role, ['instructor','admin','student'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($role === 'instructor' && $check && $check->num_rows > 0) {
    $vr = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ? LIMIT 1"); $vr->bind_param('ii',$course_id,$user_id); $vr->execute(); $vr->store_result();
    if ($vr->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
    $vr->close();
}

$stmt = $conn->prepare("SELECT id, title, description, due_date, created_at FROM assignments WHERE course_id = ? ORDER BY created_at DESC"); $stmt->bind_param('i',$course_id); $stmt->execute(); $res = $stmt->get_result();
$assigns = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close(); $conn->close();
echo json_encode(['success'=>true,'assignments'=>$assigns]);
