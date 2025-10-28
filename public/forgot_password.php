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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, firstname FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token_hash, $expiry, $email);
            $stmt->execute();
            
            // In a real application, you would send an email here
            // For now, we'll show the reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/public/reset_password.php?token=" . $token;
            
            $success_message = "Password reset instructions have been sent to your email. 
                                <br><br><strong>For testing:</strong> <a href='$reset_link' style='color: #667eea;'>Click here to reset password</a>";
        } else {
            // Don't reveal if email exists or not for security
            $success_message = "If an account exists with that email, password reset instructions have been sent.";
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
    <title>Forgot Password - Dzidza LMS</title>
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
        
        .forgot-container {
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
    <div class="forgot-container">
        <div class="logo">
            <img src="/public/assets/img/Dzidzaa.png" alt="Dzidza LMS">
            <h1>Forgot Password</h1>
            <p>Enter your email to reset your password</p>
        </div>

        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="back-link">
            <a href="/public/login.php">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
