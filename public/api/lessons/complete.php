<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../../config/db_config.php';

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $user_id = intval($_SESSION['user_id']);
    $lesson_id = intval($_POST['lesson_id'] ?? 0);

    if ($lesson_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid lesson ID']);
        exit;
    }

    // Check if user is enrolled in the course
    $stmt = $conn->prepare("SELECT c.id, c.title FROM courses c 
                            INNER JOIN lessons l ON c.id = l.course_id
                            INNER JOIN enrollments e ON c.id = e.course_id
                            WHERE l.id = ? AND e.user_id = ?");
    $stmt->bind_param("ii", $lesson_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
        exit;
    }
    
    $course = $result->fetch_assoc();
    $course_id = $course['id'];
    $stmt->close();

    // Check if already marked as complete
    $stmt = $conn->prepare("SELECT id, completed FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->bind_param("ii", $user_id, $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $progress = $result->fetch_assoc();
        $stmt->close();
        
        if ($progress['completed']) {
            // Already completed, toggle to incomplete
            $stmt = $conn->prepare("UPDATE lesson_progress SET completed = 0, completed_at = NULL WHERE user_id = ? AND lesson_id = ?");
            $stmt->bind_param("ii", $user_id, $lesson_id);
            $stmt->execute();
            $stmt->close();
            
            // Update course progress
            updateCourseProgress($conn, $user_id, $course_id);
            
            echo json_encode(['success' => true, 'completed' => false, 'message' => 'Lesson marked as incomplete']);
        } else {
            // Mark as complete
            $stmt = $conn->prepare("UPDATE lesson_progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND lesson_id = ?");
            $stmt->bind_param("ii", $user_id, $lesson_id);
            $stmt->execute();
            $stmt->close();
            
            // Update course progress
            updateCourseProgress($conn, $user_id, $course_id);
            
            echo json_encode(['success' => true, 'completed' => true, 'message' => 'Lesson completed!']);
        }
    } else {
        // Insert new progress record
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
        $stmt->bind_param("ii", $user_id, $lesson_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Update course progress
            updateCourseProgress($conn, $user_id, $course_id);
            
            echo json_encode(['success' => true, 'completed' => true, 'message' => 'Lesson completed!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to mark lesson as complete']);
        }
        $stmt->close();
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log('[complete_lesson] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function updateCourseProgress($conn, $user_id, $course_id) {
    // Calculate progress percentage
    $stmt = $conn->prepare("SELECT 
                            (SELECT COUNT(*) FROM lessons WHERE course_id = ?) as total_lessons,
                            (SELECT COUNT(*) FROM lesson_progress lp 
                             INNER JOIN lessons l ON lp.lesson_id = l.id 
                             WHERE l.course_id = ? AND lp.user_id = ? AND lp.completed = 1) as completed_lessons");
    $stmt->bind_param("iii", $course_id, $course_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    $total = $data['total_lessons'];
    $completed = $data['completed_lessons'];
    $progress = $total > 0 ? ($completed / $total) * 100 : 0;
    
    // Update enrollment progress
    $stmt = $conn->prepare("UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("dii", $progress, $user_id, $course_id);
    $stmt->execute();
    $stmt->close();
}
?>
