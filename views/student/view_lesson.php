<?php
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role_name'] ?? 'student';

if ($lesson_id <= 0) {
    header('Location: /public/courses.php');
    exit();
}

// Fetch lesson details with course info
$stmt = $conn->prepare("SELECT l.*, c.title as course_title, c.id as course_id 
                        FROM lessons l 
                        JOIN courses c ON l.course_id = c.id 
                        WHERE l.id = ? LIMIT 1");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lesson) {
    echo "<p>Lesson not found.</p>";
    exit();
}

// Check if user is enrolled or is instructor/admin
$has_access = false;
if ($role === 'admin') {
    $has_access = true;
} elseif ($role === 'instructor') {
    $stmt = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $lesson['course_id'], $user_id);
    $stmt->execute();
    $has_access = $stmt->get_result()->num_rows > 0;
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $lesson['course_id'], $user_id);
    $stmt->execute();
    $has_access = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

if (!$has_access) {
    echo "<p>You don't have access to this lesson. Please enroll in the course first.</p>";
    exit();
}
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<style>
.lesson-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}
.lesson-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.breadcrumb {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 16px;
}
.breadcrumb a {
    color: #3b82f6;
    text-decoration: none;
}
.breadcrumb a:hover {
    text-decoration: underline;
}
.lesson-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
.lesson-meta {
    font-size: 14px;
    color: #6b7280;
}
.lesson-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    line-height: 1.8;
    font-size: 16px;
    color: #374151;
}
.lesson-content h2 {
    color: #1f2937;
    margin-top: 32px;
    margin-bottom: 16px;
}
.lesson-content p {
    margin-bottom: 16px;
}
.file-attachment {
    background: #f3f4f6;
    padding: 16px 20px;
    border-radius: 8px;
    margin-top: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.file-attachment a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}
.file-attachment a:hover {
    text-decoration: underline;
}
.navigation-buttons {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}
.nav-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.nav-btn.primary {
    background: #3b82f6;
    color: white;
}
.nav-btn.primary:hover {
    background: #2563eb;
}
.nav-btn.secondary {
    background: #e5e7eb;
    color: #1f2937;
}
.nav-btn.secondary:hover {
    background: #d1d5db;
}
</style>

<div class="lesson-container">
    <div class="lesson-header">
        <div class="breadcrumb">
            <a href="/public/courses.php">Courses</a> / 
            <a href="/views/student/view_course.php?id=<?php echo $lesson['course_id']; ?>">
                <?php echo htmlspecialchars($lesson['course_title']); ?>
            </a> / 
            <?php echo htmlspecialchars($lesson['title']); ?>
        </div>
        <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
        <div class="lesson-meta">
            📚 <?php echo htmlspecialchars($lesson['course_title']); ?>
        </div>
    </div>

    <div class="lesson-content">
        <?php echo nl2br(htmlspecialchars($lesson['content'] ?? 'No content available.')); ?>
        
        <?php if ($lesson['file_path']): ?>
            <div class="file-attachment">
                <span style="font-size: 24px;">📎</span>
                <div>
                    <div style="font-weight: 500; color: #1f2937;">Lesson Material</div>
                    <a href="<?php echo htmlspecialchars($lesson['file_path']); ?>" target="_blank" download>
                        Download File
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="navigation-buttons">
        <button class="nav-btn secondary" onclick="history.back()">
            ← Back to Course
        </button>
        <?php if ($role === 'student'): ?>
            <button class="nav-btn primary" onclick="markAsComplete()">
                ✓ Mark as Complete
            </button>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsComplete() {
    // TODO: Implement progress tracking
    alert('Progress tracking will be implemented soon!');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
