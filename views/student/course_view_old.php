<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php
require_once '../config/db_config.php';

$course_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;

if ($course_id <= 0) {
    header('Location: courses.php');
    exit;
}

// Check if user is enrolled
$stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$is_enrolled = $result->num_rows > 0;
$stmt->close();

if (!$is_enrolled) {
    header('Location: courses.php');
    exit;
}

// Fetch course details
$stmt = $conn->prepare("SELECT id, title, description FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Fetch lessons
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM lessons WHERE course_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$lessons = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch assignments
$stmt = $conn->prepare("SELECT a.id, a.title, a.description, a.due_date, a.created_at,
                        (SELECT grade FROM grades WHERE user_id = ? AND assignment_id = a.id) as grade
                        FROM assignments a 
                        WHERE a.course_id = ? 
                        ORDER BY a.due_date ASC");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$assignments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch discussions
$stmt = $conn->prepare("SELECT d.id, d.title, d.content, d.created_at, u.firstname, u.lastname,
                        (SELECT COUNT(*) FROM discussion_replies WHERE discussion_id = d.id) as reply_count
                        FROM discussions d
                        JOIN users u ON d.user_id = u.id
                        WHERE d.course_id = ?
                        ORDER BY d.created_at DESC
                        LIMIT 10");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$discussions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.course-header {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    padding: 32px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
}
.course-header h1 {
    margin: 0 0 12px 0;
    font-size: 32px;
}
.course-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}
.tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 24px;
}
.tab {
    padding: 12px 24px;
    background: none;
    border: none;
    font-size: 15px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}
.tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}
.tab:hover {
    color: #3b82f6;
    background: #f9fafb;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
.content-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 4px solid #3b82f6;
}
.content-card h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
    color: #1f2937;
}
.content-card p {
    margin: 0;
    color: #6b7280;
    line-height: 1.6;
}
.content-meta {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    font-size: 13px;
    color: #9ca3af;
}
.badge-due {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.badge-due.upcoming {
    background: #fef3c7;
    color: #92400e;
}
.badge-due.overdue {
    background: #fee2e2;
    color: #991b1b;
}
.badge-grade {
    background: #d1fae5;
    color: #065f46;
}
</style>

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div style="padding: 20px;">
                <div class="course-header">
                    <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                </div>

                <div class="tabs">
                    <button class="tab active" onclick="showTab('lessons')">
                        <i class="fas fa-book"></i> Lessons (<?php echo count($lessons); ?>)
                    </button>
                    <button class="tab" onclick="showTab('assignments')">
                        <i class="fas fa-tasks"></i> Assignments (<?php echo count($assignments); ?>)
                    </button>
                    <button class="tab" onclick="showTab('discussions')">
                        <i class="fas fa-comments"></i> Discussions (<?php echo count($discussions); ?>)
                    </button>
                </div>

                <!-- Lessons Tab -->
                <div id="lessons-tab" class="tab-content active">
                    <?php if (count($lessons) > 0): ?>
                        <?php foreach ($lessons as $lesson): ?>
                        <div class="content-card">
                            <h3><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($lesson['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>
                            <div class="content-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($lesson['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book"></i>
                            <p>No lessons available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Assignments Tab -->
                <div id="assignments-tab" class="tab-content">
                    <?php if (count($assignments) > 0): ?>
                        <?php foreach ($assignments as $assignment): ?>
                        <div class="content-card">
                            <h3><i class="fas fa-clipboard-check"></i> <?php echo htmlspecialchars($assignment['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                            <div class="content-meta">
                                <span><i class="fas fa-calendar"></i> Due: <?php echo date('d M Y', strtotime($assignment['due_date'])); ?></span>
                                <?php
                                $due_date = new DateTime($assignment['due_date']);
                                $today = new DateTime();
                                if ($assignment['grade'] !== null) {
                                    echo '<span class="badge-due badge-grade"><i class="fas fa-check"></i> Graded: ' . htmlspecialchars($assignment['grade']) . '</span>';
                                } elseif ($due_date < $today) {
                                    echo '<span class="badge-due overdue"><i class="fas fa-exclamation-triangle"></i> Overdue</span>';
                                } else {
                                    echo '<span class="badge-due upcoming"><i class="fas fa-clock"></i> Upcoming</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-tasks"></i>
                            <p>No assignments available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Discussions Tab -->
                <div id="discussions-tab" class="tab-content">
                    <?php if (count($discussions) > 0): ?>
                        <?php foreach ($discussions as $discussion): ?>
                        <div class="content-card">
                            <h3><i class="fas fa-comment-dots"></i> <?php echo htmlspecialchars($discussion['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($discussion['content'])); ?></p>
                            <div class="content-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($discussion['firstname'] . ' ' . $discussion['lastname']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($discussion['created_at'])); ?></span>
                                <span><i class="fas fa-reply"></i> <?php echo $discussion['reply_count']; ?> replies</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <p>No discussions yet. Start a conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.closest('.tab').classList.add('active');
}
</script>

</body>
</html>
