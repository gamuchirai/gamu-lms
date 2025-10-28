<?php
/**
 * Notification System Test Script
 * 
 * This script tests the notification system by creating sample notifications
 * for the currently logged-in user.
 * 
 * Usage: Login as a student, then run this script once to populate test notifications.
 */

session_start();
require_once '../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Please login first to test notifications.");
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

echo "<h2>Notification System Test</h2>";
echo "<p>Testing for user: <strong>$username</strong> (ID: $user_id)</p>";

// Create test notifications
$test_notifications = [
    [
        'title' => 'Welcome to the LMS!',
        'message' => 'Your account has been successfully created. Start exploring courses now.',
        'type' => 'success'
    ],
    [
        'title' => 'New Assignment Available',
        'message' => 'Your instructor has posted a new assignment in Web Development 101.',
        'type' => 'info'
    ],
    [
        'title' => 'Assignment Due Soon',
        'message' => 'Your assignment "Database Design Project" is due in 2 days.',
        'type' => 'warning'
    ],
    [
        'title' => 'Quiz Graded',
        'message' => 'Your score for "HTML & CSS Quiz" is 85%. Great job!',
        'type' => 'success'
    ],
    [
        'title' => 'Submission Received',
        'message' => 'Your assignment submission for "Final Project" has been received successfully.',
        'type' => 'info'
    ],
    [
        'title' => 'System Maintenance',
        'message' => 'The system will be under maintenance on Sunday from 2AM-4AM.',
        'type' => 'warning'
    ]
];

echo "<h3>Creating Test Notifications...</h3>";
echo "<ul>";

$stmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, type, `read`, created_at) 
    VALUES (?, ?, ?, ?, 0, NOW())
");

$success_count = 0;
foreach ($test_notifications as $notif) {
    $stmt->bind_param("isss", $user_id, $notif['title'], $notif['message'], $notif['type']);
    
    if ($stmt->execute()) {
        echo "<li>✅ Created: <strong>{$notif['title']}</strong> ({$notif['type']})</li>";
        $success_count++;
    } else {
        echo "<li>❌ Failed: <strong>{$notif['title']}</strong> - " . $stmt->error . "</li>";
    }
}

echo "</ul>";

$stmt->close();

// Get current notification count
$count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND `read` = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$result = $count_stmt->get_result();
$count_row = $result->fetch_assoc();
$unread_count = $count_row['count'];
$count_stmt->close();

echo "<h3>Summary</h3>";
echo "<p>✅ Successfully created <strong>$success_count</strong> test notifications.</p>";
echo "<p>📬 You now have <strong>$unread_count</strong> unread notifications.</p>";

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Go to any student page (e.g., <a href='/public/dashboard.php'>Dashboard</a>)</li>";
echo "<li>Click the bell icon in the top navigation</li>";
echo "<li>You should see all $success_count test notifications</li>";
echo "<li>Click on a notification to mark it as read</li>";
echo "<li>Click 'Mark all as read' to clear the badge</li>";
echo "</ol>";

echo "<h3>API Endpoints Created</h3>";
echo "<ul>";
echo "<li><code>/public/api/notifications/list.php</code> - Get all notifications</li>";
echo "<li><code>/public/api/notifications/mark_read.php</code> - Mark single notification as read</li>";
echo "<li><code>/public/api/notifications/mark_all_read.php</code> - Mark all as read</li>";
echo "<li><code>/public/api/notifications/unread_count.php</code> - Get unread count</li>";
echo "</ul>";

echo "<p><a href='/public/dashboard.php'>← Back to Dashboard</a></p>";

$conn->close();
?>