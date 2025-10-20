<?php 
require_once '../config/db_config.php';

if (isset($_GET['token'])) {
    
    $token = $conn->real_escape_string($_GET['token']);

    // Check if token exists
    $sql = "SELECT sid, email_verified FROM students WHERE token='$token' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check if already verified
        if ($row['email_verified'] == 1) {
            echo "<script>alert('Email already verified! You can now log in.'); window.location='login.html';</script>";
            exit;
        }

        // Verify the email
        $update_sql = "UPDATE students SET email_verified=1, token=NULL WHERE token='$token'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "<script>alert('Email verified successfully! You can now log in.'); window.location='login.html';</script>";
        } else {
            echo "<script>alert('Error verifying email. Please try again.'); window.location='verify_email.php';</script>";
        }

    } else {
        echo "<script>alert('Invalid or expired verification link.'); window.location='verify_email.php';</script>";
    }

} else {
    // Show resend form if no token provided
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - RITA Africa LMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        form {
            width: 100%;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 15px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: #667eea;
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