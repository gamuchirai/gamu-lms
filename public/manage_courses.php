<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php
// Fetch all courses
require_once '../config/db_config.php';

// load available instructors
$instructors = [];
$instr_q = "SELECT u.id, u.firstname, u.lastname, u.email FROM users u JOIN user_roles r ON u.role_id = r.id WHERE r.role = 'instructor' ORDER BY u.firstname";
$instr_res = $conn->query($instr_q);
if ($instr_res) $instructors = $instr_res->fetch_all(MYSQLI_ASSOC);

// Fetch all courses (don't reference course_instructors directly to avoid runtime errors if the table is missing)
$sql = "SELECT c.id, c.title, c.description, c.created_at,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
    (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
    (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count
    FROM courses c
    ORDER BY c.created_at DESC";
$result = $conn->query($sql);
$courses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// If a course_instructors mapping table exists, fetch mappings and attach instructor_id to each course row
$map = [];
$check = $conn->query("SHOW TABLES LIKE 'course_instructors'");
if ($check && $check->num_rows > 0) {
    $mres = $conn->query("SELECT course_id, user_id FROM course_instructors");
    if ($mres) {
        while ($r = $mres->fetch_assoc()) {
            $map[$r['course_id']] = $r['user_id'];
        }
    }
}

if (!empty($map) && !empty($courses)) {
    foreach ($courses as &$c) {
        $c['instructor_id'] = isset($map[$c['id']]) ? $map[$c['id']] : null;
    }
    unset($c);
} else {
    // ensure key exists to avoid JS notices
    foreach ($courses as &$c) { $c['instructor_id'] = null; } unset($c);
}
?>

<link rel="stylesheet" href="../assets/css/table.css?v=1.0">

<div class="dashboard-wrapper">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <?php include 'topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                <div class="students-table-wrapper">
                    <div class="page-header" style="padding:16px 20px;">
                        <h2 style="margin:0; font-size:20px; color:#111827;">Manage Courses</h2>
                        <button class="btn-modal btn-save" onclick="openAddModal()" style="padding:10px 20px;">
                            <i class="fas fa-plus"></i> Add Course
                        </button>
                    </div>
                    
                    <?php if (count($courses) > 0): ?>
                    <div class="table-controls">
                        <div class="control-row">
                            <input type="search" id="searchInput" placeholder="Search courses by title or description" />
                            <label for="perPageSelect">Per page:</label>
                            <select id="perPageSelect">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Instructor</th>
                                <th>Description</th>
                                <th>Enrollments</th>
                                <th>Lessons</th>
                                <th>Assignments</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody">
                            <!-- Table rows will be rendered by JavaScript -->
                        </tbody>
                    </table>

                    <div class="pagination-controls" id="paginationControls"></div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <p>No courses found. Add your first course!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Course Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Course</h3>
            <button class="close-modal" onclick="closeAddModal()">&times;</button>
        </div>
        <form id="addCourseForm">
            <div class="modal-body">
                <div class="form-group-modal">
                    <label for="add_title">Course Title</label>
                    <input type="text" id="add_title" name="title" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" rows="4" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
                <div class="form-group-modal">
                    <label for="add_instructor">Assign Instructor (optional)</label>
                    <select id="add_instructor" name="instructor_id" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
                        <option value="0">-- None --</option>
                        <?php foreach ($instructors as $ins): ?>
                            <option value="<?php echo $ins['id']; ?>"><?php echo htmlspecialchars($ins['firstname'].' '.$ins['lastname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Add Course</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Course Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Course</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editCourseForm">
            <div class="modal-body">
                <input type="hidden" id="edit_course_id" name="course_id">
                
                <div class="form-group-modal">
                    <label for="edit_title">Course Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="4" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
                <div class="form-group-modal">
                    <label for="edit_instructor">Assign Instructor (optional)</label>
                    <select id="edit_instructor" name="instructor_id" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
                        <option value="0">-- None --</option>
                        <?php foreach ($instructors as $ins): ?>
                            <option value="<?php echo $ins['id']; ?>"><?php echo htmlspecialchars($ins['firstname'].' '.$ins['lastname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Embed courses and instructors data from PHP
const coursesData = <?php echo json_encode($courses); ?>;
const instructorsData = <?php echo json_encode($instructors); ?>;

let filteredCourses = [...coursesData];
let currentPage = 1;
let perPage = parseInt(document.getElementById('perPageSelect')?.value || 10, 10);

const monthsShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const day = String(d.getDate()).padStart(2,'0');
    const mon = monthsShort[d.getMonth()];
    const year = d.getFullYear();
    return `${day} ${mon} ${year}`;
}

function renderTable() {
    const tbody = document.getElementById('coursesTableBody');
    tbody.innerHTML = '';

    perPage = parseInt(document.getElementById('perPageSelect').value, 10);
    const start = (currentPage - 1) * perPage;
    const pageItems = filteredCourses.slice(start, start + perPage);

    if (pageItems.length === 0 && currentPage > 1) {
        currentPage = Math.max(1, currentPage - 1);
        return renderTable();
    }

    for (const c of pageItems) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-course-id', c.id);

        const titleTd = document.createElement('td');
        titleTd.style.fontWeight = '600';
        titleTd.textContent = c.title;
        tr.appendChild(titleTd);

        const instrTd = document.createElement('td');
        instrTd.textContent = '';
        if (c.instructor_id) {
            const instr = instructorsData.find(i => Number(i.id) === Number(c.instructor_id));
            if (instr) instrTd.textContent = instr.firstname + ' ' + instr.lastname;
        }
        tr.appendChild(instrTd);

        const descTd = document.createElement('td');
        descTd.textContent = c.description;
        tr.appendChild(descTd);

        const enrollTd = document.createElement('td');
        enrollTd.textContent = c.enrollment_count || 0;
        tr.appendChild(enrollTd);

        const lessonTd = document.createElement('td');
        lessonTd.textContent = c.lesson_count || 0;
        tr.appendChild(lessonTd);

        const assignTd = document.createElement('td');
        assignTd.textContent = c.assignment_count || 0;
        tr.appendChild(assignTd);

        const createdTd = document.createElement('td');
        createdTd.textContent = formatDate(c.created_at);
        tr.appendChild(createdTd);

        const actionsTd = document.createElement('td');
        const actionWrap = document.createElement('div');
        actionWrap.className = 'action-buttons cell-actions';
        const inner = document.createElement('div');
        inner.className = 'actions-container';

        // Edit
        const editBtn = document.createElement('button');
        editBtn.className = 'btn-action btn-edit action-button';
        editBtn.setAttribute('data-tooltip','Edit');
        editBtn.setAttribute('aria-label','Edit');
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.onclick = () => openEditModal(c.id);
        inner.appendChild(editBtn);

        // Delete
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-action btn-delete action-button';
        deleteBtn.setAttribute('data-tooltip','Delete');
        deleteBtn.setAttribute('aria-label','Delete');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.onclick = () => deleteCourse(c.id);
        inner.appendChild(deleteBtn);

        actionWrap.appendChild(inner);
        actionsTd.appendChild(actionWrap);
        tr.appendChild(actionsTd);

        tbody.appendChild(tr);
    }

    renderPagination();
}

function renderPagination() {
    const container = document.getElementById('paginationControls');
    container.innerHTML = '';
    const total = filteredCourses.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));

    const info = document.createElement('div');
    info.className = 'pagination-info';
    info.textContent = `Showing ${Math.min((currentPage-1)*perPage+1, total)} - ${Math.min(currentPage*perPage, total)} of ${total}`;
    container.appendChild(info);

    const nav = document.createElement('div');
    nav.className = 'pagination-nav';

    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.disabled = currentPage <= 1;
    prev.onclick = () => { currentPage = Math.max(1, currentPage-1); renderTable(); };
    nav.appendChild(prev);

    const maxButtons = 7;
    let start = Math.max(1, currentPage - Math.floor(maxButtons/2));
    let end = Math.min(totalPages, start + maxButtons - 1);
    if (end - start < maxButtons -1) start = Math.max(1, end - maxButtons + 1);

    for (let p = start; p <= end; p++) {
        const btn = document.createElement('button');
        btn.textContent = p;
        if (p === currentPage) btn.className = 'active';
        btn.onclick = (() => { const page = p; return () => { currentPage = page; renderTable(); }; })();
        nav.appendChild(btn);
    }

    const next = document.createElement('button');
    next.textContent = 'Next';
    next.disabled = currentPage >= totalPages;
    next.onclick = () => { currentPage = Math.min(totalPages, currentPage+1); renderTable(); };
    nav.appendChild(next);

    container.appendChild(nav);
}

function applyFiltersAndSearch() {
    const q = document.getElementById('searchInput').value.trim().toLowerCase();

    filteredCourses = coursesData.filter(c => {
        return q === '' || c.title.toLowerCase().includes(q) || (c.description || '').toLowerCase().includes(q);
    });
    currentPage = 1;
    renderTable();
}

// Add modal
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('addCourseForm').reset();
}

// Edit modal
function openEditModal(courseId) {
    const c = coursesData.find(x => Number(x.id) === Number(courseId));
    if (!c) return alert('Course not found');

    document.getElementById('edit_course_id').value = c.id;
    document.getElementById('edit_title').value = c.title;
    document.getElementById('edit_description').value = c.description;
    // set the instructor select if present
    if (document.getElementById('edit_instructor')) {
        document.getElementById('edit_instructor').value = c.instructor_id ? c.instructor_id : 0;
    }

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editCourseForm').reset();
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target === addModal) closeAddModal();
    if (event.target === editModal) closeEditModal();
}

// Handle add form submission
document.getElementById('addCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('api/courses/add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newCourse = {
                id: data.course_id,
                title: formData.get('title'),
                description: formData.get('description'),
                created_at: new Date().toISOString(),
                enrollment_count: 0,
                lesson_count: 0,
                assignment_count: 0,
                instructor_id: null
            };
            const selectedInstr = parseInt(document.getElementById('add_instructor')?.value || 0, 10);
            coursesData.unshift(newCourse);
            applyFiltersAndSearch();

            // If an instructor was selected, assign via API
            if (selectedInstr > 0) {
                fetch('api/courses/assign_instructor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `course_id=${data.course_id}&instructor_id=${selectedInstr}`
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        // update local model
                        const idx = coursesData.findIndex(x => Number(x.id) === Number(data.course_id));
                        if (idx > -1) coursesData[idx].instructor_id = selectedInstr;
                        applyFiltersAndSearch();
                    }
                }).catch(() => {});
            }

            closeAddModal();
            alert('Course added successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while adding the course.'); });
});

// Handle edit form submission
document.getElementById('editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('api/courses/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cid = Number(formData.get('course_id'));
            const idx = coursesData.findIndex(x => Number(x.id) === cid);
            if (idx > -1) {
                coursesData[idx].title = formData.get('title');
                coursesData[idx].description = formData.get('description');
            }

            const selectedInstr = parseInt(document.getElementById('edit_instructor')?.value || 0, 10);
            // assign/unassign instructor
            fetch('api/courses/assign_instructor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `course_id=${cid}&instructor_id=${selectedInstr}`
            }).then(r => r.json()).then(resp => {
                if (resp.success && idx > -1) {
                    coursesData[idx].instructor_id = selectedInstr > 0 ? selectedInstr : null;
                }
                applyFiltersAndSearch();
            }).catch(() => { applyFiltersAndSearch(); });

            closeEditModal();
            alert('Course updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while updating the course.'); });
});

// Delete course
function deleteCourse(courseId) {
    if (!confirm('Are you sure you want to delete this course? This will also delete all related lessons, assignments, and enrollments.')) return;

    fetch('api/courses/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `course_id=${courseId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = coursesData.findIndex(x => Number(x.id) === Number(courseId));
            if (idx > -1) coursesData.splice(idx,1);
            applyFiltersAndSearch();
            alert('Course deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while deleting the course.'); });
}

// Wire up controls
document.getElementById('searchInput').addEventListener('input', () => { applyFiltersAndSearch(); });
document.getElementById('perPageSelect').addEventListener('change', () => { perPage = parseInt(document.getElementById('perPageSelect').value,10); currentPage = 1; renderTable(); });

// Initial render
applyFiltersAndSearch();
</script>

</body>
</html>
