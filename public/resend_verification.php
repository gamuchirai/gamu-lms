<?php 
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        echo "<script>alert('Please enter a valid email address.'); window.location.href = 'verify_email.php';</script>";
        exit;
    }

    $sql = "SELECT * FROM students WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['email_verified'] == 1) {
            echo "<script>alert('Email already verified. You can log in.'); window.location.href = 'login.html';</script>";
            exit;
        }

        $newToken = random_int(100000, 999999);
        $update_sql = "UPDATE students SET token='$newToken' WHERE email='$email'";

        if ($conn->query($update_sql)) {
            $link = "http://localhost:8000/verify_email.php?token=$newToken";
            $subject = "Resend Verification - Dzidza LMS";
            $message = "Hi, here is your new verification code: $newToken\n\nClick below to verify:\n$link";
            @mail($email, $subject, $message);

            echo "<script>alert('Verification link resent successfully. Check your inbox.');
            window.location='verify_email.php' ;</script>";
        } else {
            echo "<script>alert('Error updating token. Please try again.'); window.location.href = 'verify_email.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found. Please register first.'); window.location.href = 'register.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'verify_email.php';</script>";
}
?>