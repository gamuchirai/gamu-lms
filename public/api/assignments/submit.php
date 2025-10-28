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
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    $submission_text = trim($_POST['submission_text'] ?? '');

    if ($assignment_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
        exit;
    }

    // Check if user is enrolled in the course
    $stmt = $conn->prepare("SELECT c.id FROM courses c 
                            INNER JOIN assignments a ON c.id = a.course_id
                            INNER JOIN enrollments e ON c.id = e.course_id
                            WHERE a.id = ? AND e.user_id = ?");
    $stmt->bind_param("ii", $assignment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
        exit;
    }
    $stmt->close();

    // Check if assignment exists and get due date
    $stmt = $conn->prepare("SELECT due_date FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        exit;
    }
    
    $assignment = $result->fetch_assoc();
    $due_date = $assignment['due_date'];
    $status = 'pending';
    
    // Check if submission is late
    if ($due_date && strtotime($due_date) < time()) {
        $status = 'late';
    }
    $stmt->close();

    // Handle file upload
    $file_path = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../../public/assets/uploads/submissions/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = $_FILES['submission_file']['name'];
        $file_tmp = $_FILES['submission_file']['tmp_name'];
        $file_size = $_FILES['submission_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_ext = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_ext)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG']);
            exit;
        }

        // Validate file size (10MB max)
        if ($file_size > 10 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB']);
            exit;
        }

        // Generate unique filename
        $new_file_name = 'submission_' . $user_id . '_' . $assignment_id . '_' . time() . '.' . $file_ext;
        $file_path = '/public/assets/uploads/submissions/' . $new_file_name;
        $full_path = $upload_dir . $new_file_name;

        if (!move_uploaded_file($file_tmp, $full_path)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }

    // Check if submission already exists
    $stmt = $conn->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $assignment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing submission
        $stmt->close();
        $stmt = $conn->prepare("UPDATE assignment_submissions 
                                SET file_path = ?, submission_text = ?, submitted_at = NOW(), status = ?
                                WHERE assignment_id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $file_path, $submission_text, $status, $assignment_id, $user_id);
    } else {
        // Insert new submission
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, user_id, file_path, submission_text, status) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $assignment_id, $user_id, $file_path, $submission_text, $status);
    }

    if ($stmt->execute()) {
        // Create notification for instructor
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type)
                                      SELECT ci.user_id, 'New Assignment Submission', 
                                      CONCAT('A student has submitted assignment: ', a.title), 'info'
                                      FROM assignments a
                                      INNER JOIN course_instructors ci ON a.course_id = ci.course_id
                                      WHERE a.id = ?");
        if ($notif_stmt) {
            $notif_stmt->bind_param("i", $assignment_id);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        echo json_encode([
            'success' => true, 
            'message' => $status === 'late' ? 'Submission received (marked as late)' : 'Assignment submitted successfully',
            'status' => $status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to submit assignment: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log('[submit_assignment] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
