<?php
session_start();
require_once '../../config/db_config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid method']); exit; }
$assignment_id = intval($_POST['assignment_id'] ?? 0); $title = trim($_POST['title'] ?? ''); $description = trim($_POST['description'] ?? ''); $due_date = trim($_POST['due_date'] ?? null);
if ($assignment_id <=0 || $title === '') { echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }
$role = $_SESSION['role_name'] ?? ''; $user_id = intval($_SESSION['user_id'] ?? 0); if (!in_array($role, ['instructor','admin'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
// verify assignment and ownership
$stmt = $conn->prepare("SELECT course_id FROM assignments WHERE id = ? LIMIT 1"); $stmt->bind_param('i',$assignment_id); $stmt->execute(); $res = $stmt->get_result(); if (!$res || $res->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Assignment not found']); exit; } $row = $res->fetch_assoc(); $course_id = intval($row['course_id']); $stmt->close();
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'"); if ($role === 'instructor' && $check && $check->num_rows > 0) { $vr = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ? LIMIT 1"); $vr->bind_param('ii',$course_id,$user_id); $vr->execute(); $vr->store_result(); if ($vr->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; } $vr->close(); }
$u = $conn->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ? WHERE id = ?"); $u->bind_param('sssi',$title,$description,$due_date,$assignment_id); if ($u->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'message'=>'DB error: '.$conn->error]); $u->close(); $conn->close();
