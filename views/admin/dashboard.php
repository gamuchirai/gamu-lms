<?php 
$allowed_roles = ['admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

// Total Users
$sql_users = "SELECT COUNT(*) as total FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users ? $result_users->fetch_assoc()['total'] : 0;

// Active Courses (count all courses, since there is no 'active' column)
$sql_courses = "SELECT COUNT(*) as total FROM courses";
$result_courses = $conn->query($sql_courses);
$active_courses = $result_courses ? $result_courses->fetch_assoc()['total'] : 0;

// Pending Approvals (users with email_verified = 0)
$sql_pending = "SELECT COUNT(*) as total FROM users WHERE email_verified = 0";
$result_pending = $conn->query($sql_pending);
$pending_approvals = $result_pending ? $result_pending->fetch_assoc()['total'] : 0;

$sql_activities = "SELECT action as activity, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5";
$result_activities = $conn->query($sql_activities);
$recent_activities = $result_activities ? $result_activities->fetch_all(MYSQLI_ASSOC) : [];
// Recent Admin Activities (using activity_logs table)
$sql_activities = "SELECT action as activity, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5";
$result_activities = $conn->query($sql_activities);
$recent_activities = $result_activities ? $result_activities->fetch_all(MYSQLI_ASSOC) : [];

// Recent User Registrations (last 5)
$sql_recent_users = "SELECT firstname, lastname, email, created_at FROM users ORDER BY created_at DESC LIMIT 5";
$result_recent_users = $conn->query($sql_recent_users);
$recent_users = $result_recent_users ? $result_recent_users->fetch_all(MYSQLI_ASSOC) : [];
?>

    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../../public/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- TOPBAR -->
            <?php include __DIR__ . '/../../public/topbar.php'; ?>

            <!-- CONTENT GRID -->
            <div class="content-grid">
                <div style="flex:2; min-width:0; display:flex; flex-direction:column;">
                    <!-- ADMIN METRICS CARDS -->
                    <div class="stats-cards">
                        <div class="stat-card users">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-number"><?php echo $total_users; ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="stat-card courses">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div class="stat-number"><?php echo $active_courses; ?></div>
                            <div class="stat-label">Active Courses</div>
                        </div>
                        <div class="stat-card pending">
                            <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                            <div class="stat-number"><?php echo $pending_approvals; ?></div>
                            <div class="stat-label">Pending Approvals</div>
                        </div>
                    </div>

                    <!-- RECENT USER REGISTRATIONS TABLE -->
                    <div class="section" style="margin-top:32px;">
                        <div class="section-header">
                            <i class="fas fa-user-plus"></i>
                            <h2 class="section-title">Recent User Registrations</h2>
                        </div>
                        <div class="recent-users-table-wrapper">
                            <table class="recent-users-table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Name</th>
                                        <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Email</th>
                                        <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Registered At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_users) > 0): ?>
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td style="padding:8px; border-bottom:1px solid #f0f0f0;">
                                                    <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                                </td>
                                                <td style="padding:8px; border-bottom:1px solid #f0f0f0;">
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </td>
                                                <td style="padding:8px; border-bottom:1px solid #f0f0f0;">
                                                    <?php echo date('d M Y H:i', strtotime($user['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" style="padding:8px;">No recent registrations.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="right-sidebar" style="flex:1; min-width:280px; max-width:400px; margin-left:32px;">
                    <!-- RECENT ADMIN ACTIVITIES -->
                    <div class="section">
                        <div class="section-header">
                            <i class="fas fa-history"></i>
                            <h2 class="section-title">Recent Admin Activities</h2>
                        </div>
                        <div class="activities-list">
                            <?php if (count($recent_activities) > 0): ?>
                                <ul>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <li>
                                            <span class="activity-text"><?php echo htmlspecialchars($activity['activity']); ?></span>
                                            <span class="activity-date"><?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div>No recent activities.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Handle task completion checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.task-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const taskId = this.getAttribute('data-task-id');
                    const completed = this.checked ? 1 : 0;
                    
                    // Send AJAX request to update task
                    fetch('update_task.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${taskId}&completed=${completed}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the stat cards with new data
                            if (data.stats) {
                                // Update lessons
                                const lessonsCard = document.querySelector('.stat-card.lessons');
                                lessonsCard.querySelector('.stat-number').textContent = data.stats.lessons.completed;
                                lessonsCard.querySelector('.stat-subtitle').textContent = `of ${data.stats.lessons.total} completed`;
                                
                                // Update assignments
                                const assignmentsCard = document.querySelector('.stat-card.assignments');
                                const assignmentsCompleted = String(data.stats.assignments.completed).padStart(2, '0');
                                assignmentsCard.querySelector('.stat-number').textContent = assignmentsCompleted;
                                assignmentsCard.querySelector('.stat-subtitle').textContent = `of ${data.stats.assignments.total} completed`;
                                
                                // Update tests
                                const testsCard = document.querySelector('.stat-card.tests');
                                const testsCompleted = String(data.stats.tests.completed).padStart(2, '0');
                                testsCard.querySelector('.stat-number').textContent = testsCompleted;
                                testsCard.querySelector('.stat-subtitle').textContent = `of ${data.stats.tests.total} completed`;
                            }
                            
                            // Optional: Remove the item from upcoming list if checked
                            if (completed === 1) {
                                const taskItem = this.closest('.upcoming-item');
                                taskItem.style.opacity = '0.5';
                                setTimeout(() => {
                                    taskItem.remove();
                                    // Check if no more tasks
                                    const remainingTasks = document.querySelectorAll('.upcoming-item').length;
                                    if (remainingTasks === 0) {
                                        const upcomingList = document.querySelector('.upcoming-list');
                                        upcomingList.innerHTML = '<div class="upcoming-item"><div class="upcoming-name">No upcoming tasks</div></div>';
                                    }
                                }, 500);
                            }
                        } else {
                            alert('Failed to update task: ' + data.message);
                            // Revert checkbox state
                            this.checked = !this.checked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the task');
                        // Revert checkbox state
                        this.checked = !this.checked;
                    });
                });
            });
        });
    </script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>