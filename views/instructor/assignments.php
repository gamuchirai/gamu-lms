<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role_name'] ?? 'instructor';
$assignments = [];

$hasMapping = false;
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $hasMapping = true;
}

$hasSubmissions = false;
$checkSubs = $conn->query("SHOW TABLES LIKE 'assignment_submissions'");
if ($checkSubs && $checkSubs->num_rows > 0) {
    $hasSubmissions = true;
}

$submissionCountExpr = $hasSubmissions
    ? '(SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id)'
    : '0';

$gradeCountExpr = '(SELECT COUNT(*) FROM grades WHERE assignment_id = a.id)';

if ($hasMapping) {
    $sql = "SELECT a.id,
                   a.title,
                   a.due_date,
                   a.created_at,
                   a.course_id,
                   c.title AS course_title,
                   {$submissionCountExpr} AS submission_count,
                   {$gradeCountExpr} AS graded_count
            FROM assignments a
            INNER JOIN courses c ON a.course_id = c.id
            INNER JOIN course_instructors ci ON ci.course_id = c.id
            WHERE ci.user_id = ?
            GROUP BY a.id
            ORDER BY a.due_date DESC, a.title ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $assignments = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} elseif ($role === 'admin') {
    $sql = "SELECT a.id,
                   a.title,
                   a.due_date,
                   a.created_at,
                   a.course_id,
                   c.title AS course_title,
                   {$submissionCountExpr} AS submission_count,
                   {$gradeCountExpr} AS graded_count
            FROM assignments a
            INNER JOIN courses c ON a.course_id = c.id
            GROUP BY a.id
            ORDER BY a.due_date DESC, a.title ASC";
    $result = $conn->query($sql);
    if ($result) {
        $assignments = $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
<link rel='stylesheet' href='/assets/css/table.css?v=1.0'>
<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.status-badge.success { background: #dcfce7; color: #166534; }
.status-badge.danger { background: #fee2e2; color: #b91c1c; }
.status-badge.muted { background: #e5e7eb; color: #374151; }
</style>

<div class='dashboard-wrapper'>
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class='main-content'>
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class='content-grid'>
            <div class='manage-students-container'>
                <div class='students-table-wrapper'>
                    <div class='page-header' style='padding:16px 20px; display:flex; flex-direction:column; gap:8px;'>
                        <div style='display:flex; align-items:center; gap:12px;'>
                            <h2 style='margin:0; font-size:22px; color:#111827;'>Assignments Overview</h2>
                            <span style='font-size:13px; color:#6b7280;'>Assignments across your courses</span>
                        </div>
                        <div style='display:flex; gap:8px; flex-wrap:wrap;'>
                            <a class='btn-modal btn-save' href='dashboard.php'>Back to Dashboard</a>
                            <a class='btn-modal' href='courses.php'>View Courses</a>
                        </div>
                    </div>

                    <?php if (count($assignments) > 0): ?>
                    <table class='students-table'>
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Submissions</th>
                                <th>Graded</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                            <?php
                                $dueDate = $assignment['due_date'] ? strtotime($assignment['due_date']) : null;
                                $today = strtotime('today');
                                $statusLabel = 'No due date';
                                $statusClass = 'status-badge muted';
                                if ($dueDate) {
                                    if ($dueDate < $today) {
                                        $statusLabel = 'Overdue';
                                        $statusClass = 'status-badge danger';
                                    } else {
                                        $statusLabel = 'Upcoming';
                                        $statusClass = 'status-badge success';
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <div style='font-weight:600; color:#111827;'>
                                        <?php echo htmlspecialchars($assignment['title']); ?>
                                    </div>
                                    <div style='font-size:12px; color:#6b7280;'>Created <?php echo date('d M Y', strtotime($assignment['created_at'])); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($assignment['course_title'] ?? ''); ?></td>
                                <td>
                                    <?php if ($assignment['due_date']): ?>
                                        <?php echo date('d M Y', strtotime($assignment['due_date'])); ?>
                                    <?php else: ?>
                                        <span style='color:#9ca3af;'>TBA</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo intval($assignment['submission_count'] ?? 0); ?></td>
                                <td><?php echo intval($assignment['graded_count'] ?? 0); ?></td>
                                <td><span class='<?php echo $statusClass; ?>'><?php echo $statusLabel; ?></span></td>
                                <td style='display:flex; flex-direction:column; gap:6px;'>
                                    <a class='btn-action' href='course.php?course_id=<?php echo intval($assignment['course_id']); ?>#assignments'>Manage</a>
                                    <a class='btn-action' href='/public/api/assignments/submit.php?assignment_id=<?php echo intval($assignment['id']); ?>' target='_blank'>Submission API</a>
                                    <a class='btn-action' href='#' onclick='alert("Grading interface coming soon"); return false;'>Grade Submissions</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class='empty-state'>
                        <i class='fas fa-tasks'></i>
                        <p>No assignments found for your courses.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
