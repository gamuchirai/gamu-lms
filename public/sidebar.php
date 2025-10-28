<?php
// Sidebar with dynamic links based on user role
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['student_id'] ?? null;

// Prefer session role_name when available
$role = $_SESSION['role_name'] ?? null;

// Fallback to DB lookup if role not present in session
if (empty($role) && $user_id) {
    $stmt = $conn->prepare("SELECT ur.role FROM users u LEFT JOIN user_roles ur ON u.role_id = ur.id WHERE u.id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($role_result);
        if ($stmt->fetch()) {
            $role = $role_result;
        }
        $stmt->close();
    }
}
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="/public/assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="sidebar-logo-img">
    </div>
    <nav>
        <ul>
            <?php if ($role === 'admin'): ?>
                <li><a href="/views/admin/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Admin Dashboard</a></li>
                <li><a href="/views/admin/manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                <li><a href="/views/admin/manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a></li>
                <li><a href="/views/admin/activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a></li>
            <?php elseif ($role === 'instructor'): ?>
                <li><a href="/views/instructor/dashboard.php" class="active"><i class="fas fa-chalkboard-teacher"></i> My Courses</a></li>
                <li><a href="/views/instructor/courses.php"><i class="fas fa-book"></i> Browse Courses</a></li>
                <li><a href="/views/instructor/assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
                <li><a href="/views/instructor/grades.php"><i class="fas fa-graduation-cap"></i> Grades</a></li>
                <li><a href="/views/instructor/discussions.php"><i class="fas fa-comments"></i> Discussions</a></li>
                <li><a href="/views/instructor/badges.php"><i class="fas fa-award"></i> Badges</a></li>
                <li><a href="/views/instructor/events.php"><i class="fas fa-calendar"></i> Events</a></li>
            <?php else: ?>
                <!-- Student role should not see this sidebar - they use top navigation -->
                <li><a href="/views/student/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="/views/student/courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="/views/student/assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
                <li><a href="/views/student/grades.php"><i class="fas fa-graduation-cap"></i> Grades</a></li>
                <li><a href="/views/student/discussions.php"><i class="fas fa-comments"></i> Discussions</a></li>
                <li><a href="/views/student/badges.php"><i class="fas fa-award"></i> Badges</a></li>
                <li><a href="/views/student/events.php"><i class="fas fa-calendar"></i> Events</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $role === 'student' ? '/views/student/profile.php' : '#'; ?>"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <ul>
            <li><a href="#"><i class="fas fa-circle-question"></i> FAQ</a></li>
            <li><a href="/public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</aside>