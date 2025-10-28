<?php 
$allowed_roles = ['admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db_config.php';

$sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.gender, u.dob, u.active, u.created_at 
        FROM users u 
        WHERE u.role_id = 1 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
$students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                

                <?php if (count($students) > 0): ?>
                <div class="students-table-wrapper">
                    <div class="page-header" style="padding:16px 20px;">
                        <h2 style="margin:0; font-size:20px; color:#111827;">Manage Students</h2>
                    </div>
                    <div class="table-controls">
                        <div class="control-row">
                            <input type="search" id="searchInput" placeholder="Search students by name or email" />
                            <select id="statusFilter">
                                <option value="all">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <!-- Table rows will be rendered by JavaScript -->
                        </tbody>
                    </table>

                    <div class="pagination-controls" id="paginationControls"></div>
                </div>
                <?php else: ?>
                <div class="students-table-wrapper">
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <p>No students found in the system.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Edit Student Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit Student</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editStudentForm">
            <div class="modal-body">
                <input type="hidden" id="edit_student_id" name="student_id">
                
                <div class="form-group-modal">
                    <label for="edit_firstname">First Name</label>
                    <input type="text" id="edit_firstname" name="firstname" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_lastname">Last Name</label>
                    <input type="text" id="edit_lastname" name="lastname" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_gender">Gender</label>
                    <select id="edit_gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_dob">Date of Birth</label>
                    <input type="date" id="edit_dob" name="dob" required>
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
// Embed students data from PHP
const studentsData = <?php echo json_encode($students); ?>;

let filteredStudents = [...studentsData];
let currentPage = 1;
let perPage = parseInt(document.getElementById('perPageSelect')?.value || 10, 10);

const monthsShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr; // fallback
    const day = String(d.getDate()).padStart(2,'0');
    const mon = monthsShort[d.getMonth()];
    const year = d.getFullYear();
    return `${day} ${mon} ${year}`;
}

function renderTable() {
    const tbody = document.getElementById('studentsTableBody');
    tbody.innerHTML = '';

    perPage = parseInt(document.getElementById('perPageSelect').value, 10);
    const start = (currentPage - 1) * perPage;
    const pageItems = filteredStudents.slice(start, start + perPage);

    if (pageItems.length === 0 && currentPage > 1) {
        currentPage = Math.max(1, currentPage - 1);
        return renderTable();
    }

    for (const s of pageItems) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-student-id', s.id);

        const nameTd = document.createElement('td');
        nameTd.className = 'student-name';
        nameTd.textContent = `${s.firstname} ${s.lastname}`;
        tr.appendChild(nameTd);

        const emailTd = document.createElement('td');
        emailTd.className = 'student-email';
        emailTd.textContent = s.email;
        tr.appendChild(emailTd);

        const genderTd = document.createElement('td');
        genderTd.className = 'student-gender';
        genderTd.textContent = s.gender;
        tr.appendChild(genderTd);

        const dobTd = document.createElement('td');
        dobTd.className = 'student-dob';
        dobTd.textContent = formatDate(s.dob);
        tr.appendChild(dobTd);

        const regTd = document.createElement('td');
        regTd.textContent = formatDate(s.created_at);
        tr.appendChild(regTd);

        const statusTd = document.createElement('td');
        statusTd.className = 'student-status-cell';
        const span = document.createElement('span');
        span.className = `status-badge ${s.active ? 'status-active' : 'status-suspended'}`;
        span.textContent = s.active ? 'Active' : 'Suspended';
        statusTd.appendChild(span);
        tr.appendChild(statusTd);

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
        editBtn.onclick = () => openEditModal(s.id);
        inner.appendChild(editBtn);

        // Suspend / Activate
        const statusBtn = document.createElement('button');
        statusBtn.className = 'btn-action ' + (s.active ? 'btn-suspend' : 'btn-activate') + ' action-button';
        statusBtn.setAttribute('data-tooltip', s.active ? 'Suspend' : 'Activate');
        statusBtn.setAttribute('aria-label', s.active ? 'Suspend' : 'Activate');
        statusBtn.innerHTML = s.active ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>';
        statusBtn.onclick = () => toggleSuspend(s.id, s.active ? 0 : 1);
        inner.appendChild(statusBtn);

        // Delete
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-action btn-delete action-button';
        deleteBtn.setAttribute('data-tooltip','Delete');
        deleteBtn.setAttribute('aria-label','Delete');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.onclick = () => deleteStudent(s.id);
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
    const total = filteredStudents.length;
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

    // show up to 7 pages (current in middle)
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
    const status = document.getElementById('statusFilter').value;

    filteredStudents = studentsData.filter(s => {
        const matchesQuery = q === '' || (`${s.firstname} ${s.lastname}`.toLowerCase().includes(q) || (s.email || '').toLowerCase().includes(q));
        const matchesStatus = status === 'all' || (status === 'active' && Number(s.active) === 1) || (status === 'suspended' && Number(s.active) === 0);
        return matchesQuery && matchesStatus;
    });
    currentPage = 1;
    renderTable();
}

// Open edit modal and populate with student data (uses studentsData)
function openEditModal(studentId) {
    const s = studentsData.find(x => Number(x.id) === Number(studentId));
    if (!s) return alert('Student not found');

    document.getElementById('edit_student_id').value = s.id;
    document.getElementById('edit_firstname').value = s.firstname;
    document.getElementById('edit_lastname').value = s.lastname;
    document.getElementById('edit_email').value = s.email;
    document.getElementById('edit_gender').value = s.gender;
    // ensure yyyy-mm-dd for input
    const dobVal = s.dob ? (new Date(s.dob)).toISOString().slice(0,10) : '';
    document.getElementById('edit_dob').value = dobVal;

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editStudentForm').reset();
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) closeEditModal();
}

// Handle edit form submission
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('api/users/update_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // if API returns updated student use it, else update local fields from form
            const updated = data.student || Object.fromEntries(formData.entries());
            const sid = Number(formData.get('student_id'));
            const idx = studentsData.findIndex(x => Number(x.id) === sid);
            if (idx > -1) {
                studentsData[idx].firstname = updated.firstname || formData.get('firstname');
                studentsData[idx].lastname = updated.lastname || formData.get('lastname');
                studentsData[idx].email = updated.email || formData.get('email');
                studentsData[idx].gender = updated.gender || formData.get('gender');
                studentsData[idx].dob = updated.dob || formData.get('dob');
            }
            applyFiltersAndSearch();
            closeEditModal();
            alert('Student updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while updating the student.'); });
});

// Toggle suspend/activate (updates studentsData and re-renders)
function toggleSuspend(studentId, newStatus) {
    const action = newStatus === 1 ? 'activate' : 'suspend';
    if (!confirm(`Are you sure you want to ${action} this student?`)) return;

    fetch('api/users/toggle_student_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `student_id=${studentId}&active=${newStatus}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = studentsData.findIndex(x => Number(x.id) === Number(studentId));
            if (idx > -1) studentsData[idx].active = Number(newStatus);
            applyFiltersAndSearch();
            alert(`Student ${action}d successfully!`);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while updating the student status.'); });
}

// Delete student (updates studentsData and re-renders)
function deleteStudent(studentId) {
    if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) return;

    fetch('api/users/delete_student.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `student_id=${studentId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = studentsData.findIndex(x => Number(x.id) === Number(studentId));
            if (idx > -1) studentsData.splice(idx,1);
            applyFiltersAndSearch();
            alert('Student deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred while deleting the student.'); });
}

// Wire up controls
document.getElementById('searchInput').addEventListener('input', () => { applyFiltersAndSearch(); });
document.getElementById('statusFilter').addEventListener('change', () => { applyFiltersAndSearch(); });
document.getElementById('perPageSelect').addEventListener('change', () => { perPage = parseInt(document.getElementById('perPageSelect').value,10); currentPage = 1; renderTable(); });

// Initial render
applyFiltersAndSearch();
</script>

</body>
</html>
