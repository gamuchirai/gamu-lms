<?php 
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

// Determine layout based on role
$user_role = $_SESSION['role_name'] ?? '';
$use_student_layout = ($user_role === 'student');

if ($use_student_layout) {
    require_once __DIR__ . '/../../includes/student_topbar.php';
} else {
    require_once __DIR__ . '/../../includes/header.php';
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;
$is_admin = ($_SESSION['role_name'] ?? '') === 'admin';

if ($is_admin) {
    // Admin view: All assignments
    $sql = "SELECT a.id, a.title, a.description, a.due_date, a.created_at, c.title as course_title,
            (SELECT COUNT(*) FROM grades WHERE assignment_id = a.id) as submission_count
            FROM assignments a
            JOIN courses c ON a.course_id = c.id
            ORDER BY a.due_date DESC";
    $result = $conn->query($sql);
    $assignments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} else {
    // Student view: Only assignments from enrolled courses
    $sql = "SELECT a.id, a.title, a.description, a.due_date, a.created_at, c.title as course_title,
            g.grade, g.graded_at
            FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            LEFT JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
            ORDER BY a.due_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.assignments-list {
    padding: 20px;
}
.assignment-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 4px solid #3b82f6;
    transition: transform 0.2s;
}
.assignment-card:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.assignment-card.overdue {
    border-left-color: #ef4444;
}
.assignment-card.completed {
    border-left-color: #10b981;
}
.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}
.assignment-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 4px 0;
}
.assignment-course {
    font-size: 13px;
    color: #6b7280;
}
.assignment-description {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 12px;
}
.assignment-meta {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #9ca3af;
    flex-wrap: wrap;
}
.assignment-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.status-pending {
    background: #fef3c7;
    color: #92400e;
}
.status-graded {
    background: #d1fae5;
    color: #065f46;
}
.status-overdue {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<?php if ($use_student_layout): ?>
<div class="student-content">
    <div class="student-container">
<?php else: ?>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
<?php endif; ?>
            <div class="assignments-list">
                <h2 style="margin: 0 0 24px 0; font-size: 24px; color: #1f2937;">
                    <i class="fas fa-tasks"></i> 
                    <?php echo $is_admin ? 'All Assignments' : 'My Assignments'; ?>
                </h2>

                <?php if (count($assignments) > 0): ?>
                    <?php
                    $today = new DateTime();
                    foreach ($assignments as $assignment):
                        $due_date = new DateTime($assignment['due_date']);
                        $is_overdue = $due_date < $today && (!isset($assignment['grade']) || $assignment['grade'] === null);
                        $is_graded = isset($assignment['grade']) && $assignment['grade'] !== null;
                        
                        $card_class = 'assignment-card';
                        if ($is_graded) {
                            $card_class .= ' completed';
                        } elseif ($is_overdue) {
                            $card_class .= ' overdue';
                        }
                    ?>
                    <div class="<?php echo $card_class; ?>">
                        <div class="assignment-header">
                            <div>
                                <h3 class="assignment-title"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <div class="assignment-course">
                                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($assignment['course_title']); ?>
                                </div>
                            </div>
                            <?php if (!$is_admin): ?>
                                <?php if ($is_graded): ?>
                                    <span class="assignment-status status-graded">
                                        <i class="fas fa-check"></i> Graded: <?php echo htmlspecialchars($assignment['grade']); ?>
                                    </span>
                                <?php elseif ($is_overdue): ?>
                                    <span class="assignment-status status-overdue">
                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                    </span>
                                <?php else: ?>
                                    <span class="assignment-status status-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="assignment-description">
                            <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                        </div>
                        
                        <div class="assignment-meta">
                            <span><i class="fas fa-calendar"></i> Due: <?php echo date('d M Y', strtotime($assignment['due_date'])); ?></span>
                            <span><i class="fas fa-clock"></i> Posted: <?php echo date('d M Y', strtotime($assignment['created_at'])); ?></span>
                            <?php if ($is_admin): ?>
                                <span><i class="fas fa-users"></i> <?php echo $assignment['submission_count']; ?> submissions</span>
                            <?php elseif ($is_graded): ?>
                                <span><i class="fas fa-check-circle"></i> Graded on: <?php echo date('d M Y', strtotime($assignment['graded_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tasks"></i>
                        <p>No assignments available.</p>
                    </div>
                <?php endif; ?>
            </div>
<?php if ($use_student_layout): ?>
    </div>
</div>
<?php else: ?>
        </div>
    </main>
</div>
<?php endif; ?>

</body>
</html>
