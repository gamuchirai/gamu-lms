<?php
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (!empty($_POST['website'])) {
        http_response_code(400);
        exit("Invalid submission detected.");
    }

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];

    // Check if email already exists
    $check_sql = "SELECT sid FROM students WHERE email='$email'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Email already registered. Please use a different email or log in.'); window.location.href = 'register.php';</script>";
        exit;
    }

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];

    $token = random_int(100000, 999999);

    $sql = "INSERT INTO students (firstname, lastname, email, password, gender, dob, token, email_verified) VALUES ('$firstname', '$lastname', '$email', '$password', '$gender', '$dob', '$token', 0)"; 

    $verify_link = "http://localhost:8000/verify_email.php?token=" . $token; 
    $subject = "Verify Your Email - Dzidza LMS"; 
    $message = "Hi $firstname,\n\nPlease verify your account using this code: $token\nor click the link below:\n$verify_link\n\nThank you,\nDzidza LMS"; 
    @mail($email, $subject, $message);  // Suppress mail warning for local testing

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration successful! Check your email for verification.'); window.location.href = 'verify_email.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>