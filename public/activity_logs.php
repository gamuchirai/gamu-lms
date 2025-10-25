<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include_once __DIR__ . '/../includes/admin_guard.php'; ?>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php include 'topbar.php'; ?>
            <div class="content-grid">
                <div style="padding:24px;">
                    <h2>Activity Logs</h2>
                    <p>This is a static placeholder for Activity Logs. Use the <code>activity_logs</code> table for real data.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
