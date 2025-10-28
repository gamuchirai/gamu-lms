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

// Fetch discussions from enrolled courses
$sql = "SELECT d.id, d.title, d.content, d.created_at, 
        u.firstname, u.lastname, c.title as course_title,
        (SELECT COUNT(*) FROM discussion_replies WHERE discussion_id = d.id) as reply_count
        FROM discussions d
        JOIN users u ON d.user_id = u.id
        JOIN courses c ON d.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        ORDER BY d.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$discussions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.discussions-container {
    padding: 20px;
    max-width: 900px;
}
.discussion-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 4px solid #8b5cf6;
    transition: transform 0.2s;
}
.discussion-card:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.discussion-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}
.discussion-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 4px 0;
}
.discussion-course {
    font-size: 13px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 4px;
}
.discussion-content {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 12px;
}
.discussion-meta {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #9ca3af;
    flex-wrap: wrap;
}
.reply-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 12px;
    background: #ede9fe;
    color: #5b21b6;
    font-size: 12px;
    font-weight: 600;
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
            <div class="discussions-container">
                <h2 style="margin: 0 0 24px 0; font-size: 24px; color: #1f2937;">
                    <i class="fas fa-comments"></i> Course Discussions
                </h2>

                <?php if (count($discussions) > 0): ?>
                    <?php foreach ($discussions as $discussion): ?>
                    <div class="discussion-card">
                        <div class="discussion-header">
                            <div>
                                <h3 class="discussion-title"><?php echo htmlspecialchars($discussion['title']); ?></h3>
                                <div class="discussion-course">
                                    <i class="fas fa-book"></i> 
                                    <?php echo htmlspecialchars($discussion['course_title']); ?>
                                </div>
                            </div>
                            <span class="reply-badge">
                                <i class="fas fa-reply"></i> <?php echo $discussion['reply_count']; ?>
                            </span>
                        </div>
                        
                        <div class="discussion-content">
                            <?php echo nl2br(htmlspecialchars($discussion['content'])); ?>
                        </div>
                        
                        <div class="discussion-meta">
                            <span>
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($discussion['firstname'] . ' ' . $discussion['lastname']); ?>
                            </span>
                            <span>
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d M Y, H:i', strtotime($discussion['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No discussions yet. Enroll in courses to see discussions!</p>
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
