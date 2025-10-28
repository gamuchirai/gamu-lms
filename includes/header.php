<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// expose role name to header for UI tweaks
$__role_name = $_SESSION['role_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css?v=2.0">
</head>
<body class="role-<?php echo htmlspecialchars(strtolower($__role_name ?: 'guest')); ?>">
