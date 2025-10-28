<?php 
$allowed_roles = ['student'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$student_id = $_SESSION['student_id'] ?? 0;

// Get enrolled courses count
$sql_courses = "SELECT COUNT(*) as count FROM enrollments WHERE user_id = ?";
$stmt = $conn->prepare($sql_courses);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get pending assignments count
$sql_assignments = "SELECT COUNT(*) as count 
                    FROM assignments a
                    INNER JOIN enrollments e ON a.course_id = e.course_id
                    WHERE e.user_id = ? 
                    AND a.due_date >= CURDATE()
                    AND a.id NOT IN (SELECT assignment_id FROM grades WHERE user_id = ?)";
$stmt = $conn->prepare($sql_assignments);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$pending_assignments = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get average grade
$sql_avg = "SELECT AVG(grade) as avg_grade FROM grades WHERE user_id = ?";
$stmt = $conn->prepare($sql_avg);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_result = $stmt->get_result()->fetch_assoc();
$avg_grade = $avg_result['avg_grade'] ? number_format($avg_result['avg_grade'], 1) : 'N/A';
$stmt->close();

// Get badges count
$sql_badges = "SELECT COUNT(*) as count FROM user_badges WHERE user_id = ?";
$stmt = $conn->prepare($sql_badges);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$badges_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get upcoming assignments
$sql_upcoming = "SELECT a.id, a.title, a.due_date, c.title as course_title
                 FROM assignments a
                 INNER JOIN courses c ON a.course_id = c.id
                 INNER JOIN enrollments e ON a.course_id = e.course_id
                 WHERE e.user_id = ? 
                 AND a.due_date >= CURDATE()
                 AND a.id NOT IN (SELECT assignment_id FROM grades WHERE user_id = ?)
                 ORDER BY a.due_date ASC
                 LIMIT 5";
$stmt = $conn->prepare($sql_upcoming);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$upcoming_assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get recent grades
$sql_recent = "SELECT g.grade, g.graded_at, a.title as assignment_title, c.title as course_title
               FROM grades g
               INNER JOIN assignments a ON g.assignment_id = a.id
               INNER JOIN courses c ON a.course_id = c.id
               WHERE g.user_id = ?
               ORDER BY g.graded_at DESC
               LIMIT 5";
$stmt = $conn->prepare($sql_recent);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_grades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get enrolled courses
$sql_enrolled = "SELECT c.id, c.title, c.description,
                 (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count
                 FROM courses c
                 INNER JOIN enrollments e ON c.id = e.course_id
                 WHERE e.user_id = ?
                 ORDER BY e.enrolled_at DESC
                 LIMIT 4";
$stmt = $conn->prepare($sql_enrolled);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<?php include __DIR__ . '/../../includes/student_topbar.php'; ?>

<div class="student-content">
    <div class="student-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($_SESSION['firstname']); ?>! 👋</h1>
            <p class="page-subtitle">Here's what's happening with your learning today</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Enrolled Courses</div>
                    <div class="stat-value"><?php echo $courses_count; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Pending Assignments</div>
                    <div class="stat-value"><?php echo $pending_assignments; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Average Grade</div>
                    <div class="stat-value"><?php echo $avg_grade; ?>%</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Badges Earned</div>
                    <div class="stat-value"><?php echo $badges_count; ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- My Courses -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-book-open"></i>
                        My Courses
                    </h2>
                    <a href="/views/student/courses.php" class="btn btn-secondary">View All</a>
                </div>
                
                <?php if (count($enrolled_courses) > 0): ?>
                <div style="display: grid; gap: 16px;">
                    <?php foreach ($enrolled_courses as $course): ?>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($course['title']); ?>
                            </h3>
                            <p style="font-size: 14px; color: #64748b; margin-bottom: 8px;">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...
                            </p>
                            <div style="font-size: 13px; color: #94a3b8;">
                                <i class="fas fa-book"></i> <?php echo $course['lesson_count']; ?> Lessons
                            </div>
                        </div>
                        <a href="/views/student/view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                            Continue <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-book" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>No enrolled courses yet. Browse available courses to get started!</p>
                    <a href="/views/student/courses.php" class="btn btn-primary" style="margin-top: 16px;">
                        Browse Courses
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Assignments -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-clock"></i>
                        Upcoming
                    </h2>
                </div>
                
                <?php if (count($upcoming_assignments) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($upcoming_assignments as $assignment): 
                        $days_left = ceil((strtotime($assignment['due_date']) - time()) / 86400);
                        $urgency_class = $days_left <= 2 ? 'danger' : ($days_left <= 5 ? 'orange' : 'green');
                    ?>
                    <div style="border-left: 3px solid var(--<?php echo $urgency_class; ?>); padding: 12px; background: #f8fafc; border-radius: 4px;">
                        <div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($assignment['title']); ?>
                        </div>
                        <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($assignment['course_title']); ?>
                        </div>
                        <div style="font-size: 12px; color: #<?php echo $urgency_class === 'danger' ? 'ef4444' : ($urgency_class === 'orange' ? 'f59e0b' : '10b981'); ?>;">
                            <i class="fas fa-calendar"></i> Due in <?php echo $days_left; ?> day<?php echo $days_left != 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>All caught up! No upcoming assignments.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Grades -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-star"></i>
                    Recent Grades
                </h2>
                <a href="/views/student/grades.php" class="btn btn-secondary">View All</a>
            </div>
            
            <?php if (count($recent_grades) > 0): ?>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px; text-align: left; font-size: 14px; color: #64748b; font-weight: 600;">Assignment</th>
                            <th style="padding: 12px; text-align: left; font-size: 14px; color: #64748b; font-weight: 600;">Course</th>
                            <th style="padding: 12px; text-align: center; font-size: 14px; color: #64748b; font-weight: 600;">Grade</th>
                            <th style="padding: 12px; text-align: left; font-size: 14px; color: #64748b; font-weight: 600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_grades as $grade): 
                            $grade_color = $grade['grade'] >= 70 ? '#10b981' : ($grade['grade'] >= 50 ? '#f59e0b' : '#ef4444');
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px; font-size: 14px; color: #0f172a;">
                                <?php echo htmlspecialchars($grade['assignment_title']); ?>
                            </td>
                            <td style="padding: 12px; font-size: 14px; color: #64748b;">
                                <?php echo htmlspecialchars($grade['course_title']); ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span style="display: inline-block; padding: 4px 12px; background: <?php echo $grade_color; ?>; color: white; border-radius: 12px; font-size: 14px; font-weight: 600;">
                                    <?php echo number_format($grade['grade'], 1); ?>%
                                </span>
                            </td>
                            <td style="padding: 12px; font-size: 14px; color: #64748b;">
                                <?php echo date('M d, Y', strtotime($grade['graded_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p>No grades yet. Complete assignments to see your grades here!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
:root {
    --danger: #ef4444;
    --orange: #f59e0b;
    --green: #10b981;
}
</style>

</body>
</html>
