<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$userId = intval($_SESSION['user_id']);
$courses = [];

// If course_instructors table exists, use it to find assigned courses
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $stmt = $conn->prepare("SELECT c.id, c.title, c.description, c.created_at,
        (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
        (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
        FROM courses c JOIN course_instructors ci ON ci.course_id = c.id
        WHERE ci.user_id = ? ORDER BY c.title ASC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $courses = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Fallback: no mapping table exists - instructors may not have explicit assignments
    // Show all courses for admin, or none for instructor
    if ($role === 'admin') {
        $res = $conn->query("SELECT c.id, c.title, c.description, c.created_at,
            (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
            (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
            FROM courses c ORDER BY c.title ASC");
        if ($res) $courses = $res->fetch_all(MYSQLI_ASSOC);
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
                    <div class="page-header" style="padding:16px 20px; display:flex; gap:12px; align-items:center;">
                        <h2 style="margin:0; font-size:20px; color:#111827;">Instructor Dashboard</h2>
                        <div style="margin-left:auto; display:flex; gap:8px;">
                            <a class="btn-modal btn-save" href="manage_courses.php">All Courses</a>
                        </div>
                    </div>

                    <?php if (count($courses) > 0): ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Lessons</th>
                                <th>Assignments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $c): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($c['title']); ?></td>
                                <td><?php echo intval($c['lesson_count']); ?></td>
                                <td><?php echo intval($c['assignment_count']); ?></td>
                                <td>
                                    <a class="btn-action" href="course.php?course_id=<?php echo $c['id']; ?>">Manage</a>
                                    <a class="btn-action" href="course.php?course_id=<?php echo $c['id']; ?>#lessons">Lessons</a>
                                    <a class="btn-action" href="course.php?course_id=<?php echo $c['id']; ?>#assignments">Assignments</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No assigned courses found.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
