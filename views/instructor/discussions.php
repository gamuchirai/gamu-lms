<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role_name'] ?? 'instructor';
$discussions = [];

$hasMapping = false;
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $hasMapping = true;
    $stmt = $conn->prepare("SELECT d.id,
                                   d.title,
                                   d.content,
                                   d.created_at,
                                   c.title AS course_title,
                                   u.firstname,
                                   u.lastname,
                                   (SELECT COUNT(*) FROM discussion_replies WHERE discussion_id = d.id) AS reply_count
                            FROM discussions d
                            INNER JOIN courses c ON c.id = d.course_id
                            INNER JOIN course_instructors ci ON ci.course_id = c.id
                            LEFT JOIN users u ON u.id = d.user_id
                            WHERE ci.user_id = ?
                            ORDER BY d.created_at DESC");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $discussions = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
}

if (!$hasMapping && $role === 'admin') {
    $result = $conn->query("SELECT d.id,
                                   d.title,
                                   d.content,
                                   d.created_at,
                                   c.title AS course_title,
                                   u.firstname,
                                   u.lastname,
                                   (SELECT COUNT(*) FROM discussion_replies WHERE discussion_id = d.id) AS reply_count
                            FROM discussions d
                            INNER JOIN courses c ON c.id = d.course_id
                            LEFT JOIN users u ON u.id = d.user_id
                            ORDER BY d.created_at DESC");
    if ($result) {
        $discussions = $result->fetch_all(MYSQLI_ASSOC);
    }
}
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

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="discussions-container">
                <div class="page-header" style="margin-bottom:24px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <h2 style="margin:0; font-size:22px; color:#111827;">Course Discussions</h2>
                        <span style="font-size:13px; color:#6b7280;">Threads across your assigned courses</span>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
                        <a class="btn-modal btn-save" href="dashboard.php">Back to Dashboard</a>
                        <a class="btn-modal" href="/public/discussions.php">Open Discussions Hub</a>
                    </div>
                </div>

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
                                <i class="fas fa-reply"></i> <?php echo intval($discussion['reply_count'] ?? 0); ?>
                            </span>
                        </div>

                        <div class="discussion-content">
                            <?php echo nl2br(htmlspecialchars($discussion['content'])); ?>
                        </div>

                        <div class="discussion-meta">
                            <span>
                                <i class="fas fa-user"></i>
                                <?php
                                    $names = trim(($discussion['firstname'] ?? '') . ' ' . ($discussion['lastname'] ?? ''));
                                    echo htmlspecialchars($names !== '' ? $names : 'System');
                                ?>
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
                        <p>No discussions yet for your assigned courses.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
