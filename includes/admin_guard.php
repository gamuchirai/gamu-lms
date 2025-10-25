<?php
// Admin guard: require session and ensure current user is an admin.
// Usage: include_once __DIR__ . '/admin_guard.php';

// Ensure session is active and user is logged in
require_once __DIR__ . '/session_guard.php';

// DB connection
require_once __DIR__ . '/../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['student_id'] ?? null;

// Prefer the session-stored role_name when available to avoid extra DB lookups
$role = $_SESSION['role_name'] ?? null;

// If role_name is not in session, fall back to DB lookup
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

// Redirect non-admins to the main dashboard
if ($role !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

?>
