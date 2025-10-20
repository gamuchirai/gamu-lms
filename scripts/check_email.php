<?php
require_once __DIR__ . '/../config/db_config.php';
$email = $argv[1] ?? null;
if (! $email) {
    echo "Usage: php check_email.php user@example.com\n";
    exit(1);
}
$stmt = $conn->prepare("SELECT sid, firstname, email, email_verified, token FROM students WHERE email = ? LIMIT 1");
if (! $stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit(1);
}
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($sid, $firstname, $email, $email_verified, $token);
    $stmt->fetch();
    echo "sid: $sid\n";
    echo "firstname: $firstname\n";
    echo "email: $email\n";
    echo "email_verified: $email_verified\n";
    echo "token: " . ($token ?? 'NULL') . "\n";
} else {
    echo "No user found with email $email\n";
}
$conn->close();
?>