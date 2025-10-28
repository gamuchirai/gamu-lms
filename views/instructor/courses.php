<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role_name'] ?? 'instructor';
$courses = [];

// Polyfill: provide mb_strimwidth if mbstring extension is not installed.
// Some PHP deployments don't have mbstring enabled; this fallback
// provides a safe approximation (character-aware when mb_* funcs exist).
if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($str, $start, $width, $trimmarker = '') {
        // Use multibyte-aware functions if available
        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            $len = mb_strlen($str);
            if ($len <= $width) {
                return $str;
            }
            $trimlen = max(0, $width - mb_strlen($trimmarker));
            return mb_substr($str, $start, $trimlen) . $trimmarker;
        }

        // Fallback to single-byte functions
        if (strlen($str) <= $width) {
            return $str;
        }
        $trimlen = max(0, $width - strlen($trimmarker));
        return substr($str, $start, $trimlen) . $trimmarker;
    }
}

$hasMapping = false;
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $hasMapping = true;
    $stmt = $conn->prepare("SELECT c.id,
                                   c.title,
                                   c.description,
                                   c.created_at,
                                   (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS lesson_count,
                                   (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) AS assignment_count,
                                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrollment_count
                            FROM courses c
                            INNER JOIN course_instructors ci ON ci.course_id = c.id
                            WHERE ci.user_id = ?
                            GROUP BY c.id
                            ORDER BY c.title ASC");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $courses = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
}

if (!$hasMapping && $role === 'admin') {
    $result = $conn->query("SELECT c.id,
                                   c.title,
                                   c.description,
                                   c.created_at,
                                   (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS lesson_count,
                                   (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) AS assignment_count,
                                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrollment_count
                            FROM courses c
                            ORDER BY c.title ASC");
    if ($result) {
        $courses = $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
<link rel="stylesheet" href="/assets/css/table.css?v=1.0">

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                <div class="students-table-wrapper">
                    <div class="page-header" style="padding:16px 20px; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <h2 style="margin:0; font-size:22px; color:#111827;">My Courses</h2>
                            <span style="font-size:13px; color:#6b7280;">Overview of courses assigned to you</span>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <a class="btn-modal btn-save" href="dashboard.php">Back to Dashboard</a>
                            <a class="btn-modal" href="/views/admin/manage_courses.php">Manage All Courses</a>
                        </div>
                    </div>

                    <?php if (count($courses) > 0): ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrolled</th>
                                <th>Lessons</th>
                                <th>Assignments</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#111827;">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </div>
                                    <?php if (!empty($course['description'])): ?>
                                    <div style="font-size:13px; color:#6b7280; max-width:360px;">
                                        <?php echo htmlspecialchars(mb_strimwidth($course['description'], 0, 120, '...')); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo intval($course['enrollment_count'] ?? 0); ?></td>
                                <td><?php echo intval($course['lesson_count'] ?? 0); ?></td>
                                <td><?php echo intval($course['assignment_count'] ?? 0); ?></td>
                                <td><?php echo date('d M Y', strtotime($course['created_at'])); ?></td>
                                <td style="display:flex; flex-direction:column; gap:6px;">
                                    <a class="btn-action" href="course.php?course_id=<?php echo intval($course['id']); ?>">Manage Course</a>
                                    <a class="btn-action" href="course.php?course_id=<?php echo intval($course['id']); ?>#lessons">View Lessons</a>
                                    <a class="btn-action" href="course.php?course_id=<?php echo intval($course['id']); ?>#assignments">View Assignments</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No courses assigned yet. Contact an administrator to be added to a course.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
