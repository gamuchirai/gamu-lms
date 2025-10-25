<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php
// Admin dashboard metrics
require_once '../config/db_config.php';

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

// Recent Admin Activities (using activity_logs table)
$sql_activities = "SELECT action as activity, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5";
$result_activities = $conn->query($sql_activities);
$recent_activities = $result_activities ? $result_activities->fetch_all(MYSQLI_ASSOC) : [];
?>

    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- TOPBAR -->
            <?php include 'topbar.php'; ?>

            <!-- CONTENT GRID -->
            <div class="content-grid">
                <div style="flex:2; min-width:0;">
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
</body>
</html>