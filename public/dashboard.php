<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>

    <div class="sidebar">
        <ul>
            <li><a href="#">Dashboard Home</a></li>
            <li><a href="#">Courses</a></li>
            <li><a href="#">Assignments</a></li>
            <li><a href="#">Profile</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Welcome, <?php echo $_SESSION['firstname']; ?>!</h2>
        <p>This is your student dashboard. Here you can access your courses, assignments, and more.</p>
    </div>

<?php include '../includes/footer.php'; ?>