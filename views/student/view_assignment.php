<?php
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role_name'] ?? 'student';

if ($assignment_id <= 0) {
    header('Location: /public/courses.php');
    exit();
}

// Fetch assignment details with course info
$stmt = $conn->prepare("SELECT a.*, c.title as course_title, c.id as course_id 
                        FROM assignments a 
                        JOIN courses c ON a.course_id = c.id 
                        WHERE a.id = ? LIMIT 1");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    echo "<p>Assignment not found.</p>";
    exit();
}

// Check if user is enrolled or is instructor/admin
$has_access = false;
if ($role === 'admin') {
    $has_access = true;
} elseif ($role === 'instructor') {
    $stmt = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $assignment['course_id'], $user_id);
    $stmt->execute();
    $has_access = $stmt->get_result()->num_rows > 0;
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $assignment['course_id'], $user_id);
    $stmt->execute();
    $has_access = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

if (!$has_access) {
    echo "<p>You don't have access to this assignment. Please enroll in the course first.</p>";
    exit();
}

// Check if student has submitted (for students only)
$submission = null;
if ($role === 'student') {
    // TODO: Fetch from submissions table when implemented
    // For now, just placeholder
}

// Calculate time remaining
$due_date = strtotime($assignment['due_date']);
$now = time();
$time_diff = $due_date - $now;
$is_overdue = $time_diff < 0;
$days_remaining = abs(floor($time_diff / 86400));
$hours_remaining = abs(floor(($time_diff % 86400) / 3600));
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<style>
.assignment-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 20px;
}
.assignment-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.breadcrumb {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 16px;
}
.breadcrumb a {
    color: #3b82f6;
    text-decoration: none;
}
.breadcrumb a:hover {
    text-decoration: underline;
}
.assignment-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 16px;
}
.assignment-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.meta-item {
    background: #f9fafb;
    padding: 16px;
    border-radius: 8px;
}
.meta-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.meta-value {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-badge.upcoming {
    background: #dbeafe;
    color: #1e40af;
}
.status-badge.overdue {
    background: #fee2e2;
    color: #991b1b;
}
.assignment-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    line-height: 1.8;
    font-size: 16px;
    color: #374151;
    margin-bottom: 30px;
}
.submission-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}
.upload-area:hover {
    border-color: #3b82f6;
    background: #f9fafb;
}
.upload-area input[type="file"] {
    display: none;
}
.submit-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 20px;
}
.submit-btn:hover {
    background: #2563eb;
}
.submit-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}
</style>

<div class="assignment-container">
    <div class="assignment-header">
        <div class="breadcrumb">
            <a href="/public/courses.php">Courses</a> / 
            <a href="/views/student/view_course.php?id=<?php echo $assignment['course_id']; ?>">
                <?php echo htmlspecialchars($assignment['course_title']); ?>
            </a> / 
            <?php echo htmlspecialchars($assignment['title']); ?>
        </div>
        <h1 class="assignment-title">📝 <?php echo htmlspecialchars($assignment['title']); ?></h1>
        
        <div class="assignment-meta">
            <div class="meta-item">
                <div class="meta-label">Due Date</div>
                <div class="meta-value">
                    <?php echo date('M d, Y g:i A', $due_date); ?>
                </div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Time Remaining</div>
                <div class="meta-value">
                    <?php if ($is_overdue): ?>
                        <span class="status-badge overdue">Overdue</span>
                    <?php else: ?>
                        <span class="status-badge upcoming">
                            <?php echo $days_remaining; ?>d <?php echo $hours_remaining; ?>h
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($assignment['max_score']): ?>
            <div class="meta-item">
                <div class="meta-label">Points</div>
                <div class="meta-value"><?php echo $assignment['max_score']; ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="assignment-content">
        <h2 style="margin-top: 0; color: #1f2937;">Instructions</h2>
        <?php echo nl2br(htmlspecialchars($assignment['description'] ?? 'No instructions provided.')); ?>
    </div>

    <?php if ($role === 'student'): ?>
    <div class="submission-section">
        <h2 style="margin-top: 0; color: #1f2937;">Your Submission</h2>
        
        <?php if ($submission): ?>
            <div style="background: #f0fdf4; border: 1px solid #86efac; padding: 20px; border-radius: 8px;">
                <div style="color: #15803d; font-weight: 600; margin-bottom: 8px;">✓ Submitted</div>
                <div style="color: #166534; font-size: 14px;">
                    Submitted on <?php echo date('M d, Y g:i A', strtotime($submission['submitted_at'])); ?>
                </div>
            </div>
        <?php else: ?>
            <form id="submissionForm" enctype="multipart/form-data">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx,.txt,.zip">
                    <div style="font-size: 48px; margin-bottom: 12px;">📤</div>
                    <div style="font-size: 16px; font-weight: 500; color: #1f2937; margin-bottom: 8px;">
                        Click to upload your work
                    </div>
                    <div style="font-size: 14px; color: #6b7280;">
                        Supported formats: PDF, DOC, DOCX, TXT, ZIP
                    </div>
                </div>
                <div id="fileName" style="margin-top: 12px; font-size: 14px; color: #6b7280;"></div>
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    Submit Assignment
                </button>
            </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
const fileInput = document.getElementById('fileInput');
const fileName = document.getElementById('fileName');
const submitBtn = document.getElementById('submitBtn');

fileInput?.addEventListener('change', function(e) {
    if (this.files.length > 0) {
        fileName.textContent = '📄 ' + this.files[0].name;
        submitBtn.disabled = false;
    } else {
        fileName.textContent = '';
        submitBtn.disabled = true;
    }
});

document.getElementById('submissionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!fileInput.files.length) {
        alert('Please select a file to upload');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('assignment_id', <?php echo $assignment_id; ?>);
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';
    
    // TODO: Implement submission API endpoint
    fetch('/public/api/assignments/submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Assignment submitted successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit assignment'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Assignment';
        }
    })
    .catch(error => {
        alert('Submission feature coming soon!');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Assignment';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
