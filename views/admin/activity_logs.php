<?php 
$allowed_roles = ['admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
?>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/../../public/sidebar.php'; ?>
        <main class="main-content">
            <?php include __DIR__ . '/../../public/topbar.php'; ?>
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
