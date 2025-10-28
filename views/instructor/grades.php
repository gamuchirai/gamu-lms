<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role_name'] ?? 'instructor';
$grades = [];

$hasMapping = false;
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $hasMapping = true;
}

$sqlBase = "SELECT g.id,
                   g.grade,
                   g.feedback,
                   g.graded_at,
                   a.title AS assignment_title,
                   c.title AS course_title,
                   u.firstname,
                   u.lastname,
                   u.email
            FROM grades g
            INNER JOIN assignments a ON g.assignment_id = a.id
            INNER JOIN courses c ON a.course_id = c.id
            INNER JOIN users u ON g.user_id = u.id";

if ($hasMapping) {
    $sql = $sqlBase . "
            INNER JOIN course_instructors ci ON ci.course_id = c.id
            WHERE ci.user_id = ?
            ORDER BY g.graded_at DESC, a.title ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $grades = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} elseif ($role === 'admin') {
    $sql = $sqlBase . " ORDER BY g.graded_at DESC, a.title ASC";
    $result = $conn->query($sql);
    if ($result) {
        $grades = $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
<link rel='stylesheet' href='/assets/css/table.css?v=1.0'>

<div class='dashboard-wrapper'>
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class='main-content'>
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class='content-grid'>
            <div class='manage-students-container'>
                <div class='students-table-wrapper'>
                    <div class='page-header' style='padding:16px 20px; display:flex; flex-direction:column; gap:8px;'>
                        <div style='display:flex; align-items:center; gap:12px;'>
                            <h2 style='margin:0; font-size:22px; color:#111827;'>Grades & Feedback</h2>
                            <span style='font-size:13px; color:#6b7280;'>Recent grades submitted for your courses</span>
                        </div>
                        <div style='display:flex; gap:8px; flex-wrap:wrap;'>
                            <a class='btn-modal btn-save' href='dashboard.php'>Back to Dashboard</a>
                            <a class='btn-modal' href='assignments.php'>Assignments</a>
                        </div>
                    </div>

                    <?php if (count($grades) > 0): ?>
                    <table class='students-table'>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Assignment</th>
                                <th>Grade</th>
                                <th>Feedback</th>
                                <th>Graded On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $row): ?>
                            <?php
                                $nameParts = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
                                $studentName = $nameParts !== '' ? $nameParts : ($row['email'] ?? 'Student');
                            ?>
                            <tr>
                                <td>
                                    <div style='font-weight:600; color:#111827;'><?php echo htmlspecialchars($studentName); ?></div>
                                    <div style='font-size:12px; color:#6b7280;'><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['course_title'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['assignment_title'] ?? ''); ?></td>
                                <td><?php echo $row['grade'] !== null ? htmlspecialchars($row['grade']) : '<span style="color:#9ca3af;">Pending</span>'; ?></td>
                                <td>
                                    <?php if (!empty($row['feedback'])): ?>
                                        <span><?php echo nl2br(htmlspecialchars($row['feedback'])); ?></span>
                                    <?php else: ?>
                                        <span style='color:#9ca3af;'>No feedback</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['graded_at'] ? date('d M Y, H:i', strtotime($row['graded_at'])) : '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class='empty-state'>
                        <i class='fas fa-chart-line'></i>
                        <p>No grades recorded yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
