<?php 
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

// Determine layout based on role
$user_role = $_SESSION['role_name'] ?? '';
$use_student_layout = ($user_role === 'student');

if ($use_student_layout) {
    require_once __DIR__ . '/../../includes/student_topbar.php';
} else {
    require_once __DIR__ . '/../../includes/header.php';
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;

// Fetch all grades for the student
$sql = "SELECT g.grade, g.graded_at, a.title as assignment_title, c.title as course_title
        FROM grades g
        JOIN assignments a ON g.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE g.user_id = ?
        ORDER BY g.graded_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$total_grades = count($grades);
$sum = 0;
$highest = 0;
$lowest = 100;
foreach ($grades as $grade) {
    $sum += floatval($grade['grade']);
    $highest = max($highest, floatval($grade['grade']));
    $lowest = min($lowest, floatval($grade['grade']));
}
$average = $total_grades > 0 ? round($sum / $total_grades, 2) : 0;
if ($total_grades === 0) {
    $lowest = 0;
}
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.grades-container {
    padding: 20px;
}
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.stat-box {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    text-align: center;
}
.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #3b82f6;
    margin-bottom: 8px;
}
.stat-label {
    font-size: 14px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.grade-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left: 4px solid #10b981;
}
.grade-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    color: #1f2937;
}
.grade-course {
    font-size: 13px;
    color: #6b7280;
}
.grade-date {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}
.grade-score {
    font-size: 36px;
    font-weight: 700;
    color: #10b981;
}
.grade-score.excellent {
    color: #10b981;
}
.grade-score.good {
    color: #3b82f6;
}
.grade-score.average {
    color: #f59e0b;
}
.grade-score.poor {
    color: #ef4444;
}
</style>

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="grades-container">
                <h2 style="margin: 0 0 24px 0; font-size: 24px; color: #1f2937;">
                    <i class="fas fa-graduation-cap"></i> My Grades
                </h2>

                <?php if ($total_grades > 0): ?>
                <!-- Statistics -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $average; ?>%</div>
                        <div class="stat-label">Average Grade</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $highest; ?>%</div>
                        <div class="stat-label">Highest Grade</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $lowest; ?>%</div>
                        <div class="stat-label">Lowest Grade</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $total_grades; ?></div>
                        <div class="stat-label">Total Assignments</div>
                    </div>
                </div>

                <!-- Grades List -->
                <h3 style="margin: 0 0 16px 0; font-size: 18px; color: #1f2937;">Grade History</h3>
                <?php foreach ($grades as $grade): 
                    $score = floatval($grade['grade']);
                    $score_class = 'excellent';
                    if ($score < 90) $score_class = 'good';
                    if ($score < 70) $score_class = 'average';
                    if ($score < 50) $score_class = 'poor';
                ?>
                <div class="grade-card">
                    <div class="grade-info">
                        <h3><?php echo htmlspecialchars($grade['assignment_title']); ?></h3>
                        <div class="grade-course">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($grade['course_title']); ?>
                        </div>
                        <div class="grade-date">
                            <i class="fas fa-calendar"></i> Graded on <?php echo date('d M Y', strtotime($grade['graded_at'])); ?>
                        </div>
                    </div>
                    <div class="grade-score <?php echo $score_class; ?>">
                        <?php echo $score; ?>%
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-graduation-cap"></i>
                        <p>No grades available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
