<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php
// Fetch all student users (role_id = 1 for student)
require_once '../config/db_config.php';

$sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.gender, u.dob, u.active, u.created_at 
        FROM users u 
        WHERE u.role_id = 1 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
$students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<link rel="stylesheet" href="assets/css/manage_users.css?v=1.0">

<div class="dashboard-wrapper">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <?php include 'topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                <div class="page-header">
                    <h2><i class="fas fa-users"></i> Manage Students</h2>
                </div>

                <?php if (count($students) > 0): ?>
                <div class="students-table-wrapper">
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
                            <?php foreach ($students as $student): ?>
                            <tr data-student-id="<?php echo $student['id']; ?>">
                                <td class="student-name"><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
                                <td class="student-email"><?php echo htmlspecialchars($student['email']); ?></td>
                                <td class="student-gender"><?php echo htmlspecialchars($student['gender']); ?></td>
                                <td class="student-dob"><?php echo date('d M Y', strtotime($student['dob'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($student['created_at'])); ?></td>
                                <td class="student-status-cell">
                                    <span class="status-badge <?php echo $student['active'] ? 'status-active' : 'status-suspended'; ?>">
                                        <?php echo $student['active'] ? 'Active' : 'Suspended'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick="openEditModal(<?php echo $student['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($student['active']): ?>
                                        <button class="btn-action btn-suspend" onclick="toggleSuspend(<?php echo $student['id']; ?>, 0)">
                                            <i class="fas fa-ban"></i> Suspend
                                        </button>
                                        <?php else: ?>
                                        <button class="btn-action btn-activate" onclick="toggleSuspend(<?php echo $student['id']; ?>, 1)">
                                            <i class="fas fa-check"></i> Activate
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-delete" onclick="deleteStudent(<?php echo $student['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
// Open edit modal and populate with student data
function openEditModal(studentId) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    
    // Extract current data from table row
    const name = row.querySelector('.student-name').textContent.trim().split(' ');
    const email = row.querySelector('.student-email').textContent.trim();
    const gender = row.querySelector('.student-gender').textContent.trim();
    const dobText = row.querySelector('.student-dob').textContent.trim();
    
    // Convert date format from "25 Oct 2025" to "2025-10-25"
    const dobParts = dobText.split(' ');
    const months = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
    const dobFormatted = `${dobParts[2]}-${String(months[dobParts[1]]).padStart(2,'0')}-${dobParts[0].padStart(2,'0')}`;
    
    // Populate form fields
    document.getElementById('edit_student_id').value = studentId;
    document.getElementById('edit_firstname').value = name[0];
    document.getElementById('edit_lastname').value = name.slice(1).join(' ');
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_gender').value = gender;
    document.getElementById('edit_dob').value = dobFormatted;
    
    // Show modal
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editStudentForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
}

// Handle edit form submission
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('api/update_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the student.');
    });
});

// Toggle suspend/activate
function toggleSuspend(studentId, newStatus) {
    const action = newStatus === 1 ? 'activate' : 'suspend';
    const confirmMsg = `Are you sure you want to ${action} this student?`;
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    fetch('api/toggle_student_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `student_id=${studentId}&active=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI without reload
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            const statusCell = row.querySelector('.student-status-cell');
            const actionButtons = row.querySelector('.action-buttons');
            
            // Update status badge
            statusCell.innerHTML = newStatus === 1 
                ? '<span class="status-badge status-active">Active</span>'
                : '<span class="status-badge status-suspended">Suspended</span>';
            
            // Update action buttons
            const editBtn = actionButtons.querySelector('.btn-edit');
            const deleteBtn = actionButtons.querySelector('.btn-delete');
            
            actionButtons.innerHTML = '';
            actionButtons.appendChild(editBtn);
            
            if (newStatus === 1) {
                actionButtons.innerHTML += `
                    <button class="btn-action btn-suspend" onclick="toggleSuspend(${studentId}, 0)">
                        <i class="fas fa-ban"></i> Suspend
                    </button>
                `;
            } else {
                actionButtons.innerHTML += `
                    <button class="btn-action btn-activate" onclick="toggleSuspend(${studentId}, 1)">
                        <i class="fas fa-check"></i> Activate
                    </button>
                `;
            }
            
            actionButtons.appendChild(deleteBtn);
            
            alert(`Student ${action}d successfully!`);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the student status.');
    });
}

// Delete student
function deleteStudent(studentId) {
    if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        return;
    }
    
    fetch('api/delete_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            row.remove();
            
            // Check if table is empty
            const tbody = document.getElementById('studentsTableBody');
            if (tbody.children.length === 0) {
                location.reload();
            }
            
            alert('Student deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the student.');
    });
}
</script>

</body>
</html>
