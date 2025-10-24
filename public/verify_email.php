<?php
session_start(); // Start session for auto-login
require_once __DIR__ . '/../config/db_config.php';

$logFile = __DIR__ . '/../logs/email_log.txt';

// If token provided, attempt verification
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    // Log incoming verification attempts for debugging
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli';
    file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY HIT token={$token} ip={$ip} uri={$_SERVER['REQUEST_URI']}\n", FILE_APPEND | LOCK_EX);

    // Prepared select - get user details for auto-login
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email_verified FROM users WHERE token = ? LIMIT 1");
    if (! $stmt) {
        file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare select: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
        echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
        exit;
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $firstname, $lastname, $email_verified);
        $stmt->fetch();

        if ($email_verified == 1) {
            echo "<script>alert('Email already verified! You can now log in.'); window.location='login.html';</script>";
            exit;
        }

        // Update to mark verified
        $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1, token = NULL WHERE token = ? AND email_verified = 0");
        if (! $update_stmt) {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare update: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
            exit;
        }
        $update_stmt->bind_param('s', $token);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY SUCCESS token={$token} user_id={$user_id}\n", FILE_APPEND | LOCK_EX);
            
            // Auto-login: Set session variables
            $_SESSION['student_id'] = $user_id;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['lastname'] = $lastname;
            $_SESSION['loggedin'] = true;
            
            // Redirect to dashboard
            echo "<script>alert('Email verified successfully! Welcome, {$firstname}!'); window.location='dashboard.php';</script>";
            exit;
        } else {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY WARN no rows updated for token={$token}; db_error=" . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
            exit;
        }

    } else {
        file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY FAIL token not found: {$token}\n", FILE_APPEND | LOCK_EX);
        echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
        exit;
    }

} else {
    // No token provided -> show resend form (styled like login page)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="/public/static/favicon.ico">
    <title>Email Verification - Dzidza LMS</title>
    <link rel="stylesheet" href="/assets/css/login.css?v=4.3">
    <style>
        /* Adjustments for verification page */
        .logo-container h3 { 
            color: #ffffff; 
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .logo { 
            width: 220px;
            height: auto;
            margin-bottom: 1rem;
        }
        main { 
            text-align: center;
        }
        main p {
            margin-bottom: 1.5rem;
            color: #4b5563;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <h3>Email Verification Required</h3>
            <img src="/public/assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="logo">
        </div>
    </header>
    <main>
        <p>Please check your email for the verification link. If you didn't receive it, enter your email below to resend.</p>
        
        <form action="resend_verification.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Enter your email to resend verification</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit">Resend Verification Email</button>
            <p>Already verified? <a href="login.html">Log in here</a></p>
            <p>Need to register? <a href="register.html">Sign up here</a></p>
        </form>
    </main>
    <footer>
        <p>&copy; 2025 Dzidza LMS</p>
    </footer>
</body>
</html>