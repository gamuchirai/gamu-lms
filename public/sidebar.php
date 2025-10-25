<?php
// Sidebar with dynamic links based on user role
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db_config.php';

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
        <img src="./assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="sidebar-logo-img">
    </div>
    <nav>
        <ul>
            <?php if ($role === 'admin'): ?>
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-chart-line"></i> Admin Dashboard</a></li>
                <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                <li><a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a></li>
                <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
                <li><a href="activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a></li>
            <?php else: ?>
                <li><a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
                <li><a href="grades.php"><i class="fas fa-graduation-cap"></i> Grades</a></li>
                <li><a href="discussions.php"><i class="fas fa-comments"></i> Discussions</a></li>
                <li><a href="badges.php"><i class="fas fa-award"></i> Badges</a></li>
            <?php endif; ?>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <ul>
            <li><a href="#"><i class="fas fa-circle-question"></i> FAQ</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</aside>