<?php 
require_once '../config/db_config.php';
if (isset($_GET['token'])) {

$token = $_GET['token'];

$sql = "SELECT * FROM students WHERE token='$token' LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

$conn->query("UPDATE students SET email_verified=1, token=NULL WHERE token='$token'");

echo "<script>alert('Email verified successfully! You can now log in.');
window.location='login.html' ;< /script>";

    } else {

        echo "<script>alert('Invalid or expired verification link.'); window.location='register.html' ;</script>";

    }

} else {

    echo "<h3>Please check your email for the verification link .< /h3>

<form action='resend_verification.php' method='POST'>

<input type='email' name='email' placeholder='Enter your email to resend link' required>

<button type='submit'>Resend Verification Email</button>

</form>";

}

    ?>