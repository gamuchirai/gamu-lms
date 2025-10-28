<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
try {
    ob_start();
    require_once '../../config/db_config.php';
    ob_end_clean();

    if (!isset($_SESSION['user_id']) && !isset($_SESSION['student_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Resolve session user to actual users.id (enrollments.user_id references users.id)
    $user_id = null;
    $sess_user = $_SESSION['user_id'] ?? null;
    $sess_student = $_SESSION['student_id'] ?? null;

    if ($sess_user) {
        $chk = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        if ($chk) {
            $chk->bind_param('i', $sess_user);
            $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) $user_id = $sess_user;
            $chk->close();
        }
    }

    if (!$user_id && $sess_student) {
        $chk2 = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        if ($chk2) {
            $chk2->bind_param('i', $sess_student); $chk2->execute(); $chk2->store_result();
            if ($chk2->num_rows > 0) $user_id = $sess_student;
            $chk2->close();
        }
    }

    if (!$user_id && $sess_student) {
        $s = $conn->prepare("SELECT email FROM students WHERE sid = ? LIMIT 1");
        if ($s) {
            $s->bind_param('i', $sess_student); $s->execute(); $res = $s->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc(); $email = $row['email'];
                if ($email) {
                    $u = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                    if ($u) {
                        $u->bind_param('s', $email); $u->execute(); $u->store_result();
                        if ($u->num_rows > 0) { $u->bind_result($foundId); $u->fetch(); $user_id = $foundId; }
                        $u->close();
                    }
                }
            }
            $s->close();
        }
    }

    $user_id = $user_id ?? 0;
    $course_id = intval($_POST['course_id'] ?? 0);

    if ($course_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
        exit;
    }

    if ($user_id <= 0) {
        http_response_code(400);
        error_log('[unenroll_course] invalid resolved user_id from session: ' . json_encode([ 'sess_user'=>$sess_user, 'sess_student'=>$sess_student ]));
        echo json_encode(['success' => false, 'message' => 'Invalid user session. Please log in again.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
    if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
    $stmt->bind_param("ii", $user_id, $course_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to unenroll: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $ex) {
    http_response_code(500);
    error_log('[unenroll_course] ' . $ex->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
