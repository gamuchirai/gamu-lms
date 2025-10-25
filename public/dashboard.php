<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php
// Fetch stats and upcoming tasks for the logged-in student
require_once '../config/db_config.php';

$student_id = $_SESSION['student_id'];

// Get stats for lessons
$sql_lessons = "SELECT 
                COUNT(*) as total,
                SUM(completed) as completed
                FROM student_tasks 
                WHERE student_id = ? AND task_type = 'lesson'";
$stmt = $conn->prepare($sql_lessons);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$lessons_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get stats for assignments
$sql_assignments = "SELECT 
                    COUNT(*) as total,
                    SUM(completed) as completed
                    FROM student_tasks 
                    WHERE student_id = ? AND task_type = 'assignment'";
$stmt = $conn->prepare($sql_assignments);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$assignments_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get stats for tests
$sql_tests = "SELECT 
              COUNT(*) as total,
              SUM(completed) as completed
              FROM student_tasks 
              WHERE student_id = ? AND task_type = 'test'";
$stmt = $conn->prepare($sql_tests);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$tests_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch upcoming tasks (not completed and due today or later)
$sql = "SELECT id, title, due_date, task_type, completed 
        FROM student_tasks 
        WHERE student_id = ? AND due_date >= CURDATE() AND completed = 0
        ORDER BY due_date ASC 
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
                <div>
                    <!-- STATS CARDS -->
                    <div class="stats-cards">
                        <div class="stat-card lessons">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div class="stat-number"><?php echo $lessons_stats['completed'] ?? 0; ?></div>
                            <div class="stat-label">Lessons</div>
                            <div class="stat-subtitle">of <?php echo $lessons_stats['total'] ?? 0; ?> completed</div>
                        </div>
                        <div class="stat-card assignments">
                            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                            <div class="stat-number"><?php echo str_pad($assignments_stats['completed'] ?? 0, 2, '0', STR_PAD_LEFT); ?></div>
                            <div class="stat-label">Assignments</div>
                            <div class="stat-subtitle">of <?php echo $assignments_stats['total'] ?? 0; ?> completed</div>
                        </div>
                        <div class="stat-card tests">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-number"><?php echo str_pad($tests_stats['completed'] ?? 0, 2, '0', STR_PAD_LEFT); ?></div>
                            <div class="stat-label">Tests</div>
                            <div class="stat-subtitle">of <?php echo $tests_stats['total'] ?? 0; ?> completed</div>
                        </div>
                    </div>

                    <!-- COURSES SECTION -->
                    <div class="section">
                        <div class="section-header">
                            <i class="fas fa-book-open"></i>
                            <h2 class="section-title">My Courses</h2>
                        </div>
                        <div class="section-tabs">
                            <div class="section-tab active">Active</div>
                            <div class="section-tab">Completed</div>
                        </div>
                        <div class="courses-list">
                            <div class="course-item">
                                <div class="course-icon design"><i class="fas fa-palette"></i></div>
                                <div class="course-name">Web Design: Form Figma to we...</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                            <div class="course-item">
                                <div class="course-icon html"><i class="fas fa-code"></i></div>
                                <div class="course-name">Html Basics</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                            <div class="course-item">
                                <div class="course-icon python"><i class="fas fa-snake"></i></div>
                                <div class="course-name">Data with python</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDEBAR -->
                <div class="right-sidebar">
                    <!-- UPCOMING -->
                    <div class="upcoming-section">
                        <h3 class="upcoming-title">Upcoming</h3>
                        <div class="upcoming-list">
                            <?php if (count($upcoming_tasks) > 0): ?>
                                <?php foreach ($upcoming_tasks as $task): ?>
                                    <?php
                                    // Format the date (e.g., "29 Sept")
                                    $date = new DateTime($task['due_date']);
                                    $formatted_date = $date->format('d M');
                                    
                                    // Determine the CSS class and label for the tag
                                    $tag_class = '';
                                    $tag_label = '';
                                    
                                    switch($task['task_type']) {
                                        case 'lesson':
                                            $tag_class = 'lesson';
                                            $tag_label = 'Lesson';
                                            break;
                                        case 'test':
                                            $tag_class = 'test';
                                            $tag_label = 'Test';
                                            break;
                                        case 'assignment':
                                        default:
                                            $tag_class = '';
                                            $tag_label = 'Assignment';
                                            break;
                                    }
                                    ?>
                                    <div class="upcoming-item" data-task-id="<?php echo $task['id']; ?>">
                                        <div class="upcoming-checkbox">
                                            <input type="checkbox" 
                                                   id="task-<?php echo $task['id']; ?>" 
                                                   class="task-checkbox"
                                                   data-task-id="<?php echo $task['id']; ?>"
                                                   <?php echo $task['completed'] ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="upcoming-content">
                                            <div class="upcoming-date"><?php echo $formatted_date; ?></div>
                                            <div class="upcoming-name"><?php echo htmlspecialchars($task['title']); ?></div>
                                            <span class="upcoming-tag <?php echo $tag_class; ?>"><?php echo $tag_label; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="upcoming-item">
                                    <div class="upcoming-name">No upcoming tasks</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CALENDAR -->
                    <div class="calendar-section">
                        <h3 class="calendar-title">Status</h3>
                        <div class="calendar">
                            <div class="calendar-day-header">Mo</div>
                            <div class="calendar-day-header">Tu</div>
                            <div class="calendar-day-header">We</div>
                            <div class="calendar-day-header">Th</div>
                            <div class="calendar-day-header">Fr</div>
                            <div class="calendar-day-header">Sa</div>
                            <div class="calendar-day-header">Su</div>
                            
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>
                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day">9</div>
                            <div class="calendar-day">10</div>
                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day">13</div>
                            <div class="calendar-day">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day">16</div>
                            <div class="calendar-day">17</div>
                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day active">23</div>
                            <div class="calendar-day">24</div>
                            <div class="calendar-day">25</div>
                            <div class="calendar-day">26</div>
                            <div class="calendar-day active">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
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