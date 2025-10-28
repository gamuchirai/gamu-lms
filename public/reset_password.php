<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role_name'] ?? 'student';
    header("Location: /views/{$role}/dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/db_config.php';

$success_message = '';
$error_message = '';
$token_valid = false;
$token = $_GET['token'] ?? '';

// Verify token
if ($token) {
    $token_hash = hash('sha256', $token);
    $stmt = $conn->prepare("SELECT id, email, firstname FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_valid = true;
        $user = $result->fetch_assoc();
    } else {
        $error_message = 'Invalid or expired reset token. Please request a new password reset.';
    }
    $stmt->close();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            $success_message = 'Password reset successfully! You can now login with your new password.';
            $token_valid = false; // Hide the form
        } else {
            $error_message = 'Failed to reset password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Dzidza LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            height: 60px;
            margin-bottom: 10px;
        }
        
        .logo h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .logo p {
            font-size: 14px;
            color: #6b7280;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">
            <img src="/public/assets/img/Dzidzaa.png" alt="Dzidza LMS">
            <h1>Reset Password</h1>
            <p>Enter your new password</p>
        </div>

        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
        <div class="back-link">
            <a href="/public/login.php">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        </div>
        <?php elseif ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php if (!$token_valid): ?>
        <div class="back-link">
            <a href="/public/forgot_password.php">
                <i class="fas fa-redo"></i> Request New Reset Link
            </a>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($token_valid && !$success_message): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> New Password
                </label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="Enter new password">
                <small>Must be at least 8 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Reset Password
            </button>
        </form>

        <div class="back-link">
            <a href="/public/login.php">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
