<?php
session_start();
require_once __DIR__ . '/../../../config/db_config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid method']); exit; }
$course_id = intval($_POST['course_id'] ?? 0); $title = trim($_POST['title'] ?? ''); $description = trim($_POST['description'] ?? ''); $due_date = trim($_POST['due_date'] ?? null);
if ($course_id <=0 || $title === '') { echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }
$role = $_SESSION['role_name'] ?? '';$user_id = intval($_SESSION['user_id'] ?? 0);
if (!in_array($role, ['instructor','admin'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($role === 'instructor' && $check && $check->num_rows > 0) { $vr = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ? LIMIT 1"); $vr->bind_param('ii',$course_id,$user_id); $vr->execute(); $vr->store_result(); if ($vr->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; } $vr->close(); }

$stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)"); $stmt->bind_param('isss',$course_id,$title,$description,$due_date);
if ($stmt->execute()) echo json_encode(['success'=>true,'assignment_id'=>$stmt->insert_id]); else echo json_encode(['success'=>false,'message'=>'DB error: '.$conn->error]);
$stmt->close(); $conn->close();
