<?php
$allowed_roles = ['instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$course_id = intval($_GET['course_id'] ?? 0);
if ($course_id <= 0) {
    echo "<div style='padding:24px;'><h2>Invalid course ID</h2></div>";
    exit;
}

$userId = intval($_SESSION['user_id']);

// Verify instructor has access to this course
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($role === 'instructor' && $check && $check->num_rows > 0) {
    $vr = $conn->prepare("SELECT 1 FROM course_instructors WHERE course_id = ? AND user_id = ? LIMIT 1");
    $vr->bind_param('ii', $course_id, $userId);
    $vr->execute();
    $vr->store_result();
    if ($vr->num_rows === 0) {
        echo "<div style='padding:24px;'><h2>Access Denied</h2><p>You are not assigned to this course.</p></div>";
        exit;
    }
    $vr->close();
}

// Fetch course details
$stmt = $conn->prepare("SELECT id, title, description FROM courses WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo "<div style='padding:24px;'><h2>Course not found</h2></div>";
    exit;
}
$course = $res->fetch_assoc();
$stmt->close();
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.tabs { display:flex; gap:8px; border-bottom:2px solid #e5e7eb; margin-bottom:20px; }
.tabs button { padding:12px 24px; background:transparent; border:none; cursor:pointer; font-size:15px; font-weight:500; color:#6b7280; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
.tabs button.active { color:#3b82f6; border-bottom-color:#3b82f6; }
.tabs button:hover { color:#3b82f6; background:#f3f4f6; }
.tab-content { display:none; }
.tab-content.active { display:block; }
.lesson-card, .assignment-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-bottom:12px; }
.lesson-card h3, .assignment-card h3 { margin:0 0 8px 0; font-size:16px; color:#111827; }
.lesson-card p, .assignment-card p { margin:4px 0; font-size:14px; color:#6b7280; }
.lesson-card .actions, .assignment-card .actions { margin-top:12px; display:flex; gap:8px; }
.empty-state { text-align:center; padding:40px; color:#9ca3af; }
.empty-state i { font-size:48px; margin-bottom:16px; }
</style>

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                <div class="students-table-wrapper">
                    <div class="page-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h2 style="margin:0; font-size:20px; color:#111827;"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p style="margin:4px 0 0 0; font-size:14px; color:#6b7280;"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <a class="btn-modal btn-cancel" href="instructor_dashboard.php">Back to Dashboard</a>
                    </div>

                    <div class="tabs">
                        <button class="tab-btn active" onclick="showTab('lessons')"><i class="fas fa-book"></i> Lessons</button>
                        <button class="tab-btn" onclick="showTab('assignments')"><i class="fas fa-tasks"></i> Assignments</button>
                    </div>

                    <!-- Lessons Tab -->
                    <div id="lessons-tab" class="tab-content active">
                        <div style="margin-bottom:16px;">
                            <button class="btn-modal btn-save" onclick="openAddLessonModal()">
                                <i class="fas fa-plus"></i> Add Lesson
                            </button>
                        </div>
                        <div id="lessons-container"></div>
                    </div>

                    <!-- Assignments Tab -->
                    <div id="assignments-tab" class="tab-content">
                        <div style="margin-bottom:16px;">
                            <button class="btn-modal btn-save" onclick="openAddAssignmentModal()">
                                <i class="fas fa-plus"></i> Add Assignment
                            </button>
                        </div>
                        <div id="assignments-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Lesson Modal -->
<div id="addLessonModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Lesson</h3>
            <button class="close-modal" onclick="closeAddLessonModal()">&times;</button>
        </div>
        <form id="addLessonForm">
            <div class="modal-body">
                <div class="form-group-modal">
                    <label for="add_lesson_title">Lesson Title</label>
                    <input type="text" id="add_lesson_title" name="title" required>
                </div>
                <div class="form-group-modal">
                    <label for="add_lesson_content">Content</label>
                    <textarea id="add_lesson_content" name="content" rows="8" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddLessonModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Add Lesson</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div id="editLessonModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Lesson</h3>
            <button class="close-modal" onclick="closeEditLessonModal()">&times;</button>
        </div>
        <form id="editLessonForm">
            <div class="modal-body">
                <input type="hidden" id="edit_lesson_id" name="lesson_id">
                <div class="form-group-modal">
                    <label for="edit_lesson_title">Lesson Title</label>
                    <input type="text" id="edit_lesson_title" name="title" required>
                </div>
                <div class="form-group-modal">
                    <label for="edit_lesson_content">Content</label>
                    <textarea id="edit_lesson_content" name="content" rows="8" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeEditLessonModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Assignment</h3>
            <button class="close-modal" onclick="closeAddAssignmentModal()">&times;</button>
        </div>
        <form id="addAssignmentForm">
            <div class="modal-body">
                <div class="form-group-modal">
                    <label for="add_assignment_title">Assignment Title</label>
                    <input type="text" id="add_assignment_title" name="title" required>
                </div>
                <div class="form-group-modal">
                    <label for="add_assignment_description">Description</label>
                    <textarea id="add_assignment_description" name="description" rows="5" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;"></textarea>
                </div>
                <div class="form-group-modal">
                    <label for="add_assignment_due">Due Date</label>
                    <input type="date" id="add_assignment_due" name="due_date" style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:6px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddAssignmentModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Add Assignment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div id="editAssignmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Assignment</h3>
            <button class="close-modal" onclick="closeEditAssignmentModal()">&times;</button>
        </div>
        <form id="editAssignmentForm">
            <div class="modal-body">
                <input type="hidden" id="edit_assignment_id" name="assignment_id">
                <div class="form-group-modal">
                    <label for="edit_assignment_title">Assignment Title</label>
                    <input type="text" id="edit_assignment_title" name="title" required>
                </div>
                <div class="form-group-modal">
                    <label for="edit_assignment_description">Description</label>
                    <textarea id="edit_assignment_description" name="description" rows="5" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;"></textarea>
                </div>
                <div class="form-group-modal">
                    <label for="edit_assignment_due">Due Date</label>
                    <input type="date" id="edit_assignment_due" name="due_date" style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:6px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeEditAssignmentModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const courseId = <?php echo $course_id; ?>;

function showTab(name) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById(name + '-tab').classList.add('active');
}

// Lessons
function loadLessons() {
    fetch(`/public/api/lessons/list.php?course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('lessons-container');
            if (!data.success || !data.lessons || data.lessons.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-book-open"></i><p>No lessons yet. Add your first lesson!</p></div>';
                return;
            }
            container.innerHTML = data.lessons.map(l => `
                <div class="lesson-card">
                    <h3>${escapeHtml(l.title)}</h3>
                    <p>${escapeHtml(l.content).substring(0, 200)}${l.content.length > 200 ? '...' : ''}</p>
                    <div class="actions">
                        <button class="btn-action btn-edit" onclick="openEditLessonModal(${l.id})"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn-action btn-delete" onclick="deleteLesson(${l.id})"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => { console.error(err); alert('Failed to load lessons'); });
}

function openAddLessonModal() {
    document.getElementById('addLessonModal').style.display = 'block';
}

function closeAddLessonModal() {
    document.getElementById('addLessonModal').style.display = 'none';
    document.getElementById('addLessonForm').reset();
}

function openEditLessonModal(lessonId) {
    fetch(`/public/api/lessons/list.php?course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            const lesson = data.lessons?.find(l => Number(l.id) === Number(lessonId));
            if (!lesson) return alert('Lesson not found');
            document.getElementById('edit_lesson_id').value = lesson.id;
            document.getElementById('edit_lesson_title').value = lesson.title;
            document.getElementById('edit_lesson_content').value = lesson.content;
            document.getElementById('editLessonModal').style.display = 'block';
        });
}

function closeEditLessonModal() {
    document.getElementById('editLessonModal').style.display = 'none';
    document.getElementById('editLessonForm').reset();
}

document.getElementById('addLessonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('course_id', courseId);
    fetch('/public/api/lessons/add.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadLessons();
                closeAddLessonModal();
                alert('Lesson added successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
});

document.getElementById('editLessonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/public/api/lessons/update.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadLessons();
                closeEditLessonModal();
                alert('Lesson updated successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
});

function deleteLesson(lessonId) {
    if (!confirm('Are you sure you want to delete this lesson?')) return;
    const formData = new FormData();
    formData.append('lesson_id', lessonId);
    fetch('/public/api/lessons/delete.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadLessons();
                alert('Lesson deleted successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
}

// Assignments
function loadAssignments() {
    fetch(`/public/api/assignments/list.php?course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('assignments-container');
            if (!data.success || !data.assignments || data.assignments.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-tasks"></i><p>No assignments yet. Create your first assignment!</p></div>';
                return;
            }
            container.innerHTML = data.assignments.map(a => `
                <div class="assignment-card">
                    <h3>${escapeHtml(a.title)}</h3>
                    <p>${escapeHtml(a.description || 'No description')}</p>
                    <p style="font-size:13px; color:#9ca3af;"><i class="fas fa-calendar"></i> Due: ${a.due_date || 'No due date'}</p>
                    <div class="actions">
                        <button class="btn-action btn-edit" onclick="openEditAssignmentModal(${a.id})"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn-action btn-delete" onclick="deleteAssignment(${a.id})"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => { console.error(err); alert('Failed to load assignments'); });
}

function openAddAssignmentModal() {
    document.getElementById('addAssignmentModal').style.display = 'block';
}

function closeAddAssignmentModal() {
    document.getElementById('addAssignmentModal').style.display = 'none';
    document.getElementById('addAssignmentForm').reset();
}

function openEditAssignmentModal(assignmentId) {
    fetch(`/public/api/assignments/list.php?course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            const assignment = data.assignments?.find(a => Number(a.id) === Number(assignmentId));
            if (!assignment) return alert('Assignment not found');
            document.getElementById('edit_assignment_id').value = assignment.id;
            document.getElementById('edit_assignment_title').value = assignment.title;
            document.getElementById('edit_assignment_description').value = assignment.description || '';
            document.getElementById('edit_assignment_due').value = assignment.due_date || '';
            document.getElementById('editAssignmentModal').style.display = 'block';
        });
}

function closeEditAssignmentModal() {
    document.getElementById('editAssignmentModal').style.display = 'none';
    document.getElementById('editAssignmentForm').reset();
}

document.getElementById('addAssignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('course_id', courseId);
    fetch('/public/api/assignments/add.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadAssignments();
                closeAddAssignmentModal();
                alert('Assignment added successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
});

document.getElementById('editAssignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/public/api/assignments/update.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadAssignments();
                closeEditAssignmentModal();
                alert('Assignment updated successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
});

function deleteAssignment(assignmentId) {
    if (!confirm('Are you sure you want to delete this assignment?')) return;
    const formData = new FormData();
    formData.append('assignment_id', assignmentId);
    fetch('/public/api/assignments/delete.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadAssignments();
                alert('Assignment deleted successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => { console.error(err); alert('An error occurred'); });
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Modal close on outside click
window.onclick = function(event) {
    const modals = ['addLessonModal', 'editLessonModal', 'addAssignmentModal', 'editAssignmentModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target === modal) modal.style.display = 'none';
    });
}

// Initial load
loadLessons();
loadAssignments();
</script>

</body>
</html>
