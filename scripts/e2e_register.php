<?php
require_once __DIR__ . '/../config/db_config.php';

$timestamp = time();
$email = "e2e_{$timestamp}@example.com";
$firstname = 'E2E';
$lastname = 'Tester';
$password = password_hash('Password123!', PASSWORD_DEFAULT);
$gender = 'Other';
dob: $dob = '1990-01-01';
$token = random_int(100000, 999999);

// Insert user
$stmt = $conn->prepare("INSERT INTO students (firstname, lastname, email, password, gender, dob, token, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
if (! $stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit(1);
}
$stmt->bind_param('sssssss', $firstname, $lastname, $email, $password, $gender, $dob, $token);
if ($stmt->execute()) {
    echo "Inserted test user: $email\n";
    echo "Token: $token\n";
    // Log the email so the web flow can read it
    $log_entry = "[" . date('Y-m-d H:i:s') . "] E2E Created: $email | Token: $token | Link: http://localhost:8000/verify_email.php?token=$token\n";
    file_put_contents(__DIR__ . '/../logs/email_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
} else {
    echo "Insert failed: " . $stmt->error . "\n";
}
$conn->close();
?>