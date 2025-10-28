<?php
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

$role = $_SESSION['role_name'] ?? 'student';
$user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;
$use_student_layout = ($role === 'student');

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    $redirectTarget = $use_student_layout
        ? '/views/student/courses.php'
        : ($role === 'instructor' ? '/views/instructor/courses.php' : '/views/admin/manage_courses.php');
    header("Location: {$redirectTarget}");
    exit();
}

// Fetch course details
$stmt = $conn->prepare("SELECT c.*, 
                   COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, ''))), ''), u.email, 'Instructor') AS instructor_name
               FROM courses c 
               LEFT JOIN course_instructors ci ON c.id = ci.course_id 
               LEFT JOIN users u ON ci.user_id = u.id 
               WHERE c.id = ? LIMIT 1");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($use_student_layout) {
    require_once __DIR__ . '/../../includes/student_topbar.php';
} else {
    require_once __DIR__ . '/../../includes/header.php';
}

if (!$course) {
    if ($use_student_layout) {
        echo '<div class="student-content"><div class="student-container"><p>Course not found.</p></div></div>';
        echo '</body></html>';
    } else {
        echo "<p>Course not found.</p>";
        include __DIR__ . '/../../includes/footer.php';
    }
    exit();
}

// Check if user is enrolled (for students)
$is_enrolled = false;
if ($role === 'student') {
    $stmt = $conn->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $is_enrolled = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Fetch lessons
$stmt = $conn->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_num ASC, created_at ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch assignments
$stmt = $conn->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch enrolled students (only for instructors/admins)
$enrolled_students = [];
if (in_array($role, ['instructor', 'admin'])) {
    $stmt = $conn->prepare("SELECT u.id,
                                   COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, ''))), ''), u.email, CONCAT('Student ', u.id)) AS full_name,
                                   u.email,
                                   e.enrolled_at,
                                   e.progress 
                            FROM users u 
                            JOIN enrollments e ON u.id = e.user_id 
                            WHERE e.course_id = ? 
                            ORDER BY e.enrolled_at DESC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $enrolled_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/table.css">

<style>
.view-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}
.course-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.course-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 12px;
}
.course-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    opacity: 0.95;
}
.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 30px;
}
.tab {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}
.tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}
.tab:hover {
    color: #3b82f6;
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    border-left: 4px solid #3b82f6;
}
.content-card h3 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 18px;
}
.content-card p {
    margin: 0;
    color: #6b7280;
    line-height: 1.6;
}
.enrollment-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #10b981;
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
</style>

<?php if ($use_student_layout): ?>
<div class="student-content">
    <div class="student-container">
<?php endif; ?>

<div class="view-container">
    <div class="course-header">
        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
        <div class="course-meta">
            <?php if ($course['instructor_name']): ?>
                <span>👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></span>
            <?php endif; ?>
            <?php if ($role === 'student' && $is_enrolled): ?>
                <span class="enrollment-badge">✓ Enrolled</span>
            <?php endif; ?>
        </div>
        <?php if ($course['description']): ?>
            <p style="margin-top: 16px; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                <?php echo nl2br(htmlspecialchars($course['description'])); ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="tabs">
        <button class="tab active" onclick="switchTab(event, 'lessons')">📚 Lessons</button>
        <button class="tab" onclick="switchTab(event, 'assignments')">📝 Assignments</button>
        <?php if (in_array($role, ['instructor', 'admin'])): ?>
            <button class="tab" onclick="switchTab(event, 'students')">👥 Students (<?php echo count($enrolled_students); ?>)</button>
        <?php endif; ?>
    </div>

    <div id="lessons-tab" class="tab-content active">
        <?php if (empty($lessons)): ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                <div style="font-size: 18px; font-weight: 500; color: #6b7280;">No lessons yet</div>
            </div>
        <?php else: ?>
            <?php foreach ($lessons as $lesson): ?>
                <div class="content-card">
                    <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($lesson['content'] ?? '')); ?></p>
                    <?php if (!empty($lesson['file_path'] ?? '')): ?>
                        <a href="<?php echo htmlspecialchars($lesson['file_path']); ?>" target="_blank" 
                           style="display: inline-block; margin-top: 12px; color: #3b82f6; text-decoration: none;">
                            📎 Download Material
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="assignments-tab" class="tab-content">
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                <div style="font-size: 18px; font-weight: 500; color: #6b7280;">No assignments yet</div>
            </div>
        <?php else: ?>
            <?php foreach ($assignments as $assignment): ?>
                <div class="content-card">
                    <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($assignment['description'] ?? '')); ?></p>
                    <div style="margin-top: 12px; font-size: 14px; color: #6b7280;">
                        <strong>Due:</strong> <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                        <?php if (!empty($assignment['max_score'] ?? '')): ?>
                            | <strong>Points:</strong> <?php echo htmlspecialchars($assignment['max_score']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (in_array($role, ['instructor', 'admin'])): ?>
    <div id="students-tab" class="tab-content">
        <?php if (empty($enrolled_students)): ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
                <div style="font-size: 18px; font-weight: 500; color: #6b7280;">No enrolled students</div>
            </div>
        <?php else: ?>
            <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.06);">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Enrolled Date</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolled_students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['enrolled_at'])); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo min(100, intval($student['progress'] ?? 0)); ?>%; 
                                                        background: #3b82f6; height: 100%;"></div>
                                        </div>
                                        <span style="font-size: 13px; color: #6b7280; min-width: 40px;">
                                            <?php echo intval($student['progress'] ?? 0); ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($use_student_layout): ?>
    </div>
</div>
<?php endif; ?>

<script>
function switchTab(evt, tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const activeContent = document.getElementById(tabName + '-tab');
    if (activeContent) {
        activeContent.classList.add('active');
    }
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    }
}
</script>
<?php if ($use_student_layout): ?>
</body>
</html>
<?php else: ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php endif; ?>
