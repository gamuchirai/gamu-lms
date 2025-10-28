<?php 
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;

// Fetch enrolled courses
$sql_enrolled = "SELECT c.id, c.title, c.description,
                (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
                (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count,
                (SELECT COUNT(*) FROM discussions WHERE course_id = c.id) as discussion_count
                FROM courses c
                JOIN enrollments e ON c.id = e.course_id
                WHERE e.user_id = ?
                ORDER BY e.enrolled_at DESC";
$stmt = $conn->prepare($sql_enrolled);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$enrolled_courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch available courses (not enrolled)
$sql_available = "SELECT c.id, c.title, c.description,
                (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
                (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
                FROM courses c
                WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)
                ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql_available);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$available_courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<?php include __DIR__ . '/../../includes/student_topbar.php'; ?>

<style>
.courses-section {
    padding: 20px;
}
.section-title {
    font-size: 24px;
    color: #1f2937;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}
.course-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid transparent;
}
.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.course-card.enrolled {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
}
.course-title {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 12px;
}
.course-description {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 16px;
    min-height: 60px;
}
.course-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #6b7280;
}
.course-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}
.course-actions {
    display: flex;
    gap: 8px;
}
.btn-course {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-enroll {
    background: #3b82f6;
    color: white;
}
.btn-enroll:hover {
    background: #2563eb;
}
.btn-view {
    background: #10b981;
    color: white;
}
.btn-view:hover {
    background: #059669;
}
.btn-unenroll {
    background: #ef4444;
    color: white;
    flex: 0 0 auto;
    padding: 10px;
}
.btn-unenroll:hover {
    background: #dc2626;
}
</style>

<div class="student-content">
    <div class="student-container">
        <div class="page-header">
            <h1 class="page-title">My Courses</h1>
            <p class="page-subtitle">Browse and manage your enrolled courses</p>
        </div>

        <div class="courses-section">
                <?php if (count($enrolled_courses) > 0): ?>
                <h2 class="section-title">
                    <i class="fas fa-book-open"></i>
                    My Courses (<?php echo count($enrolled_courses); ?>)
                </h2>
                <div class="courses-grid">
                    <?php foreach ($enrolled_courses as $course): ?>
                    <div class="course-card enrolled">
                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                        <div class="course-description"><?php echo htmlspecialchars($course['description']); ?></div>
                        <div class="course-meta">
                            <span><i class="fas fa-book"></i> <?php echo $course['lesson_count']; ?> Lessons</span>
                            <span><i class="fas fa-tasks"></i> <?php echo $course['assignment_count']; ?> Assignments</span>
                            <span><i class="fas fa-comments"></i> <?php echo $course['discussion_count']; ?> Discussions</span>
                        </div>
                        <div class="course-actions">
                            <button class="btn-course btn-view" onclick="window.location.href='view_course.php?id=<?php echo $course['id']; ?>'">
                                <i class="fas fa-play-circle"></i> Continue Learning
                            </button>
                            <button class="btn-course btn-unenroll" onclick="unenrollCourse(<?php echo $course['id']; ?>)" title="Unenroll from course">
                                <i class="fas fa-times-circle"></i> Unenroll
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (count($available_courses) > 0): ?>
                <h2 class="section-title" style="margin-top:40px;">
                    <i class="fas fa-search"></i>
                    Available Courses (<?php echo count($available_courses); ?>)
                </h2>
                <div class="courses-grid">
                    <?php foreach ($available_courses as $course): ?>
                    <div class="course-card">
                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                        <div class="course-description"><?php echo htmlspecialchars($course['description']); ?></div>
                        <div class="course-meta">
                            <span><i class="fas fa-book"></i> <?php echo $course['lesson_count']; ?> Lessons</span>
                            <span><i class="fas fa-tasks"></i> <?php echo $course['assignment_count']; ?> Assignments</span>
                            <span><i class="fas fa-users"></i> <?php echo $course['enrollment_count']; ?> Students</span>
                        </div>
                        <div class="course-actions">
                            <button class="btn-course btn-enroll" onclick="enrollCourse(<?php echo $course['id']; ?>)">
                                <i class="fas fa-plus"></i> Enroll Now
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (count($enrolled_courses) === 0 && count($available_courses) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>No courses available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function enrollCourse(courseId) {
    if (!confirm('Do you want to enroll in this course?')) return;

    fetch('/public/api/enrollment/enroll.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `course_id=${courseId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Successfully enrolled!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
}

function unenrollCourse(courseId) {
    if (!confirm('Are you sure you want to unenroll from this course?')) return;

    fetch('/public/api/enrollment/unenroll.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `course_id=${courseId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Successfully unenrolled!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
}
</script>

</body>
</html>
