<?php
/**
 * Role-based guard: require session and ensure current user has one of the allowed roles.
 * Usage: 
 *   $allowed_roles = ['admin', 'instructor'];
 *   include_once __DIR__ . '/../includes/role_guard.php';
 */

// Ensure session is active and user is logged in
require_once __DIR__ . '/session_guard.php';

// DB connection
require_once __DIR__ . '/../config/db_config.php';

// Check if allowed_roles is set
if (!isset($allowed_roles) || !is_array($allowed_roles) || empty($allowed_roles)) {
    // Default to requiring admin if not specified
    $allowed_roles = ['admin'];
}

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
            $_SESSION['role_name'] = $role; // Cache in session
        }
        $stmt->close();
    }
}

// Check if user's role is in the allowed roles list
if (!in_array($role, $allowed_roles, true)) {
    // Redirect based on actual role
    if ($role === 'admin') {
        header('Location: /views/admin/dashboard.php');
    } elseif ($role === 'instructor') {
        header('Location: /views/instructor/dashboard.php');
    } else {
        header('Location: /views/student/dashboard.php');
    }
    exit();
}
?>
