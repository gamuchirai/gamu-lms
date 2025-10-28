<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please enter email and password'); window.location.href = 'login.html';</script>";
        exit;
    }

    // Use prepared statement to avoid SQL injection
    // Join with user_roles to get the role name from the database
    $stmt = $conn->prepare("SELECT u.id, u.firstname, u.lastname, u.password, u.email_verified, u.active, u.role_id, ur.role AS role_name FROM users u LEFT JOIN user_roles ur ON u.role_id = ur.id WHERE u.email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['email_verified'] == 0) {
            echo "<script>alert('Please verify your email before logging in.'); window.location='verify_email.php';</script>";
            exit;
        }

        if ($row['active'] == 0) {
            echo "<script>alert('Account suspended. Contact admin.'); window.location='login.html';</script>";
            exit;
        }

        if (password_verify($password, $row['password'])) {
            // Set session user id and name
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['loggedin'] = true;

            // Store role id and role name from DB in session
            $_SESSION['role_id'] = isset($row['role_id']) ? (int)$row['role_id'] : null;
            // role_name comes from the joined user_roles table; fall back to 'student' if empty
            $_SESSION['role_name'] = !empty($row['role_name']) ? $row['role_name'] : 'student';

            // Also set legacy student_id for compatibility when role is student
            if ($_SESSION['role_name'] === 'student') {
                $_SESSION['student_id'] = $row['id'];
            }

            // Redirect based on role_name
            if ($_SESSION['role_name'] === 'admin') {
                header("Location: /views/admin/dashboard.php");
                exit();
            } elseif ($_SESSION['role_name'] === 'instructor') {
                header("Location: /views/instructor/dashboard.php");
                exit();
            } else {
                header("Location: /views/student/dashboard.php");
                exit();
            }
        } else {
            echo "<script>alert('Invalid password'); window.location.href = 'login.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid email'); window.location.href = 'login.html';</script>";
    }
    $stmt->close();
} else {
    echo "<script>alert('Form not submitted'); window.location.href = 'login.html';</script>";
}
?>