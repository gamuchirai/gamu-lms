<?php 
require_once '../config/db_config.php';
// Ensure site constants (BASE_URL, APP_ENV) are available
require_once __DIR__ . '/../config/site_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $conn->real_escape_string($_POST['email']);

    // First, check if email exists and get verification status
    $check_sql = "SELECT id, firstname, email_verified FROM users WHERE email='$email' LIMIT 1";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        
        // Check if already verified
        if ($row['email_verified'] == 1) {
            echo "<script>alert('Your email is already verified. Please log in.'); window.location='login.html';</script>";
            exit;
        }

        // Generate new token
        $newToken = random_int(100000, 999999);
        $firstname = $row['firstname'];

        // Update token for this specific email
        $update_sql = "UPDATE users SET token='$newToken' WHERE email='$email' AND email_verified=0";

        if ($conn->query($update_sql) === TRUE) {
            // Send verification email
            // Build absolute link using BASE_URL
            $link = BASE_URL . 'public/verify_email.php?token=' . $newToken;
            $subject = "Resend Verification - Dzidza LMS";
            $message = "Hi $firstname,\n\nHere is your new verification code: $newToken\n\nOr click the link below to verify:\n$link\n\nThank you,\nDzidza LMS";

            // Attempt to send; if mail() isn't configured, write to local log for debugging
            $mail_sent = false;
            try {
                $mail_sent = @mail($email, $subject, $message);
            } catch (Exception $e) {
                $mail_sent = false;
            }

            // Append message to local log so developer can retrieve the token and link
            $log_entry = "[" . date('Y-m-d H:i:s') . "] Resend To: $email | Subject: $subject\n$message\n\n";
            file_put_contents(__DIR__ . '/../logs/email_log.txt', $log_entry, FILE_APPEND | LOCK_EX);

            echo "<script>alert('Verification link resent successfully. Check your inbox (or logs/email_log.txt for local testing).'); window.location='verify_email.php';</script>";
        } else {
            echo "<script>alert('Error resending verification. Please try again.'); window.location='verify_email.php';</script>";
        }

    } else {
        // Email not found in database
        echo "<script>alert('Email not found. Please register first.'); window.location='register.html';</script>";
    }

} else {
    // If accessed directly without POST
    header("Location: verify_email.php");
    exit;
}
?>