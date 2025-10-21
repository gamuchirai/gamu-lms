<?php
require_once __DIR__ . '/../config/db_config.php';

$logFile = __DIR__ . '/../logs/email_log.txt';

// If token provided, attempt verification
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    // Log incoming verification attempts for debugging
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli';
    file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY HIT token={$token} ip={$ip} uri={$_SERVER['REQUEST_URI']}\n", FILE_APPEND | LOCK_EX);

    // Prepared select
    $stmt = $conn->prepare("SELECT sid, email_verified FROM students WHERE token = ? LIMIT 1");
    if (! $stmt) {
        file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare select: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
        echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
        exit;
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($sid, $email_verified);
        $stmt->fetch();

        if ($email_verified == 1) {
            echo "<script>alert('Email already verified! You can now log in.'); window.location='login.html';</script>";
            exit;
        }

        // Update to mark verified
        $update_stmt = $conn->prepare("UPDATE students SET email_verified = 1, token = NULL WHERE token = ? AND email_verified = 0");
        if (! $update_stmt) {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare update: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
            exit;
        }
        $update_stmt->bind_param('s', $token);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY SUCCESS token={$token} sid={$sid}\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Email verified successfully! You can now log in.'); window.location='login.html';</script>";
            exit;
        } else {
            file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] VERIFY WARN no rows updated for token={$token}; db_error=" . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
            exit;
        }

    } else {
        echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
        exit;
    }

}

// No token provided -> show resend form (styled like login page)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="./static/favicon.ico">
    <title>Email Verification - Dzidza LMS</title>
    <link rel="stylesheet" href="../assets/css/login.css?v=4.0">
    <style>
        /* Small adjustments to reuse login styles */
        .logo-container h3 { color: #ffffff; margin-bottom: 0.5rem; }
        .logo { width: 220px; }
        main { text-align: center; }
    </style>
</head>
<body>
    <header></header>
    <main>
        <div class="logo-container">
            <h3>Email Verification Required</h3>
            <img src="./assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="logo">
        </div>

        <form action="resend_verification.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Enter your email to resend verification</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit">Resend Verification Email</button>
            <p class="login-form">Already verified? <a href="login.html">Log in here</a></p>
            <p class="login-form">Need to register? <a href="register.html">Sign up here</a></p>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 Dzidza LMS</p>
    </footer>
</body>
</html>
<?php
require_once '../config/db_config.php';

// If token provided, attempt verification
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepared select
    $stmt = $conn->prepare("SELECT sid, email_verified FROM students WHERE token = ? LIMIT 1");
    if (! $stmt) {
        file_put_contents(__DIR__ . '/../logs/email_log.txt', "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare select: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
        echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
        exit;
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($sid, $email_verified);
        $stmt->fetch();

        if ($email_verified == 1) {
            echo "<script>alert('Email already verified! You can now log in.'); window.location='login.html';</script>";
            exit;
        }

        // Update to mark verified
        $update_stmt = $conn->prepare("UPDATE students SET email_verified = 1, token = NULL WHERE token = ? AND email_verified = 0");
        if (! $update_stmt) {
            file_put_contents(__DIR__ . '/../logs/email_log.txt', "[".date('Y-m-d H:i:s')."] VERIFY ERROR prepare update: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Server error. Please try again later.'); window.location='verify_email.php';</script>";
            exit;
        }
        $update_stmt->bind_param('s', $token);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            echo "<script>alert('Email verified successfully! You can now log in.'); window.location='login.html';</script>";
            exit;
        } else {
            file_put_contents(__DIR__ . '/../logs/email_log.txt', "[".date('Y-m-d H:i:s')."] VERIFY WARN no rows updated for token=$token; db_error=" . $conn->error . "\n", FILE_APPEND | LOCK_EX);
            echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
            exit;
        }

    } else {
        echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
        exit;
    }

} // No token provided -> show resend form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="./static/favicon.ico">
    <title>Email Verification - Dzidza LMS</title>
    <link rel="stylesheet" href="../assets/css/login.css?v=4.0">
    <style>
        /* Small adjustments to reuse login styles */
        .logo-container h3 { color: #ffffff; margin-bottom: 0.5rem; }
        .logo { width: 220px; }
        main { text-align: center; }
    </style>
    </head>
<body>
    <header></header>
    <main>
        <div class="logo-container">
            <h3>Email Verification Required</h3>
            <img src="./assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="logo">
        </div>

        <form action="resend_verification.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Enter your email to resend verification</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit">Resend Verification Email</button>
            <p class="login-form">Already verified? <a href="login.html">Log in here</a></p>
            <p class="login-form">Need to register? <a href="register.html">Sign up here</a></p>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 Dzidza LMS</p>
    </footer>
</body>
</html>
<?php 
require_once '../config/db_config.php';
} else {
    // Show resend form if no token provided
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="./static/favicon.ico">
    <title>Email Verification - Dzidza LMS</title>
    <link rel="stylesheet" href="../assets/css/login.css?v=4.0">
    <style>
        /* Small adjustments to reuse login styles */
        .logo-container h3 { color: #ffffff; margin-bottom: 0.5rem; }
        .logo { width: 220px; }
        main { text-align: center; }
    </style>
</head>
<body>
    <header></header>
    <main>
        <div class="logo-container">
            <h3>Email Verification Required</h3>
            <img src="./assets/img/Dzidzaa.png" alt="Dzidzaa Logo" class="logo">
        </div>

        <form action="resend_verification.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Enter your email to resend verification</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <button type="submit">Resend Verification Email</button>
            <p class="login-form">Already verified? <a href="login.html">Log in here</a></p>
            <p class="login-form">Need to register? <a href="register.html">Sign up here</a></p>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 Dzidza LMS</p>
    </footer>
</body>
</html>
<?php
}
        
        button {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .links {
            margin-top: 20px;
            color: #666;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Email Verification Required</h3>
        <p>Please check your email for the verification link. If you didn't receive it, enter your email below to resend.</p>
        
        <form action="resend_verification.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">Resend Verification Email</button>
        </form>
        
        <div class="links">
            <p>Already verified? <a href="login.html">Log in here</a></p>
            <p>Need to register? <a href="register.html">Sign up here</a></p>
        </div>
    </div>
</body>
</html>
<?php
}
?>