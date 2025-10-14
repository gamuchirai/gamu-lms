<?php
session_start();
require_once '../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get the task ID and completed status from the request
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$completed = isset($_POST['completed']) ? intval($_POST['completed']) : 0;
$student_id = $_SESSION['student_id'];

if ($task_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

// Update the task completion status (only for tasks belonging to the logged-in student)
$sql = "UPDATE student_tasks 
        SET completed = ? 
        WHERE id = ? AND student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $completed, $task_id, $student_id);

if ($stmt->execute()) {
    // Get updated stats
    $sql_lessons = "SELECT COUNT(*) as total, SUM(completed) as completed 
                    FROM student_tasks 
                    WHERE student_id = ? AND task_type = 'lesson'";
    $stmt_stats = $conn->prepare($sql_lessons);
    $stmt_stats->bind_param("i", $student_id);
    $stmt_stats->execute();
    $lessons = $stmt_stats->get_result()->fetch_assoc();
    $stmt_stats->close();
    
    $sql_assignments = "SELECT COUNT(*) as total, SUM(completed) as completed 
                        FROM student_tasks 
                        WHERE student_id = ? AND task_type = 'assignment'";
    $stmt_stats = $conn->prepare($sql_assignments);
    $stmt_stats->bind_param("i", $student_id);
    $stmt_stats->execute();
    $assignments = $stmt_stats->get_result()->fetch_assoc();
    $stmt_stats->close();
    
    $sql_tests = "SELECT COUNT(*) as total, SUM(completed) as completed 
                  FROM student_tasks 
                  WHERE student_id = ? AND task_type = 'test'";
    $stmt_stats = $conn->prepare($sql_tests);
    $stmt_stats->bind_param("i", $student_id);
    $stmt_stats->execute();
    $tests = $stmt_stats->get_result()->fetch_assoc();
    $stmt_stats->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task updated successfully',
        'stats' => [
            'lessons' => [
                'completed' => $lessons['completed'] ?? 0,
                'total' => $lessons['total'] ?? 0
            ],
            'assignments' => [
                'completed' => $assignments['completed'] ?? 0,
                'total' => $assignments['total'] ?? 0
            ],
            'tests' => [
                'completed' => $tests['completed'] ?? 0,
                'total' => $tests['total'] ?? 0
            ]
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update task']);
}

$stmt->close();
$conn->close();
?>
