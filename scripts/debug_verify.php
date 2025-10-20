<?php
require_once __DIR__ . '/../config/db_config.php';
$token = $argv[1] ?? '362846';
action:
$action = $argv[2] ?? 'select';

echo "Checking token: $token (action=$action)\n";

// Prepared select
$stmt = $conn->prepare("SELECT sid, firstname, lastname, email, email_verified, token FROM students WHERE token = ? LIMIT 1");
if (! $stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit(1);
}
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($sid, $firstname, $lastname, $email, $email_verified, $db_token);
    $stmt->fetch();
    echo "Found row:\n";
    echo " sid: $sid\n";
    echo " name: $firstname $lastname\n";
    echo " email: $email\n";
    echo " email_verified: $email_verified\n";
    echo " token (db): '" . $db_token . "'\n";
} else {
    echo "No row found with that token.\n";
}

if ($action === 'update') {
    echo "Attempting to update email_verified for token $token...\n";
    $up = $conn->prepare("UPDATE students SET email_verified = 1, token = NULL WHERE token = ? AND email_verified = 0");
    if (! $up) {
        echo "Update prepare failed: " . $conn->error . "\n";
        exit(1);
    }
    $up->bind_param('s', $token);
    $up->execute();
    echo "Update affected_rows: " . $up->affected_rows . "\n";
    if ($up->affected_rows > 0) {
        echo "Update successful.\n";
    } else {
        echo "No rows updated. Possibly already verified or token mismatch.\n";
    }
}

$conn->close();

?>