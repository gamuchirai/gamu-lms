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

// Check if user is admin
$is_admin = ($_SESSION['role_name'] ?? '') === 'admin';

if ($is_admin) {
    // Admin view: Fetch all badges
    $sql = "SELECT b.id, b.name, b.description, b.created_at,
            (SELECT COUNT(*) FROM user_badges WHERE badge_id = b.id) as awarded_count
            FROM badges b 
            ORDER BY b.created_at DESC";
    $result = $conn->query($sql);
    $badges = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} else {
    // Student view: Fetch user's badges
    $user_id = $_SESSION['user_id'] ?? $_SESSION['student_id'] ?? 0;
    $sql = "SELECT b.id, b.name, b.description, ub.awarded_at
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ?
            ORDER BY ub.awarded_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $badges = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Fetch available badges (not yet earned)
    $sql_available = "SELECT b.id, b.name, b.description
            FROM badges b
            WHERE b.id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
            ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($sql_available);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $available_badges = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">
<style>
.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px;
}
.badge-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid transparent;
}
.badge-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.badge-card.earned {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
}
.badge-card.locked {
    opacity: 0.6;
    background: #f9fafb;
}
.badge-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    margin: 0 auto 16px;
}
.badge-card.earned .badge-icon {
    background: linear-gradient(135deg, #10b981, #059669);
}
.badge-card.locked .badge-icon {
    background: #9ca3af;
}
.badge-name {
    font-size: 18px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 8px;
    color: #1f2937;
}
.badge-description {
    font-size: 14px;
    color: #6b7280;
    text-align: center;
    line-height: 1.5;
}
.badge-status {
    display: inline-block;
    margin-top: 12px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-status.earned {
    background: #d1fae5;
    color: #065f46;
}
.badge-status.locked {
    background: #f3f4f6;
    color: #6b7280;
}
.badge-date {
    font-size: 12px;
    color: #9ca3af;
    text-align: center;
    margin-top: 8px;
}
.section-title-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.section-title-bar h2 {
    margin: 0;
    font-size: 24px;
    color: #1f2937;
}
</style>

<?php if ($use_student_layout): ?>
<div class="student-content">
    <div class="student-container">
<?php else: ?>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
<?php endif; ?>
            <div class="manage-students-container">
                <?php if ($is_admin): ?>
                    <!-- Admin View -->
                    <div class="students-table-wrapper">
                        <div class="page-header" style="padding:16px 20px;">
                            <h2 style="margin:0; font-size:20px; color:#111827;">Manage Badges</h2>
                            <button class="btn-modal btn-save" onclick="openAddModal()" style="padding:10px 20px;">
                                <i class="fas fa-plus"></i> Add Badge
                            </button>
                        </div>
                        
                        <?php if (count($badges) > 0): ?>
                        <div class="table-controls">
                            <div class="control-row">
                                <input type="search" id="searchInput" placeholder="Search badges by name or description" />
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
                                    <th>Badge Name</th>
                                    <th>Description</th>
                                    <th>Awarded Count</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="badgesTableBody">
                                <!-- Table rows will be rendered by JavaScript -->
                            </tbody>
                        </table>

                        <div class="pagination-controls" id="paginationControls"></div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-award"></i>
                            <p>No badges found. Create your first badge!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Student View -->
                    <div style="padding:0;">
                        <?php if (count($badges) > 0): ?>
                        <div class="section-title-bar">
                            <h2><i class="fas fa-trophy"></i> My Badges (<?php echo count($badges); ?>)</h2>
                        </div>
                        <div class="badges-grid">
                            <?php foreach ($badges as $badge): ?>
                            <div class="badge-card earned">
                                <div class="badge-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></div>
                                <div class="badge-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                                <div style="text-align:center;">
                                    <span class="badge-status earned">Earned</span>
                                </div>
                                <div class="badge-date">Earned on <?php echo date('d M Y', strtotime($badge['awarded_at'])); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (count($available_badges) > 0): ?>
                        <div class="section-title-bar" style="margin-top:40px;">
                            <h2><i class="fas fa-lock"></i> Available Badges (<?php echo count($available_badges); ?>)</h2>
                        </div>
                        <div class="badges-grid">
                            <?php foreach ($available_badges as $badge): ?>
                            <div class="badge-card locked">
                                <div class="badge-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></div>
                                <div class="badge-description"><?php echo htmlspecialchars($badge['description']); ?></div>
                                <div style="text-align:center;">
                                    <span class="badge-status locked">Locked</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (count($badges) === 0 && count($available_badges) === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-award"></i>
                            <p>No badges available yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
<?php if ($use_student_layout): ?>
    </div>
</div>
<?php else: ?>
        </div>
    </main>
</div>
<?php endif; ?>

<?php if ($is_admin): ?>
<!-- Add Badge Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Badge</h3>
            <button class="close-modal" onclick="closeAddModal()">&times;</button>
        </div>
        <form id="addBadgeForm">
            <div class="modal-body">
                <div class="form-group-modal">
                    <label for="add_name">Badge Name</label>
                    <input type="text" id="add_name" name="name" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Add Badge</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Badge Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Badge</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editBadgeForm">
            <div class="modal-body">
                <input type="hidden" id="edit_badge_id" name="badge_id">
                
                <div class="form-group-modal">
                    <label for="edit_name">Badge Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
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
const badgesData = <?php echo json_encode($badges); ?>;

let filteredBadges = [...badgesData];
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
    const tbody = document.getElementById('badgesTableBody');
    tbody.innerHTML = '';

    perPage = parseInt(document.getElementById('perPageSelect').value, 10);
    const start = (currentPage - 1) * perPage;
    const pageItems = filteredBadges.slice(start, start + perPage);

    if (pageItems.length === 0 && currentPage > 1) {
        currentPage = Math.max(1, currentPage - 1);
        return renderTable();
    }

    for (const b of pageItems) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-badge-id', b.id);

        const nameTd = document.createElement('td');
        nameTd.style.fontWeight = '600';
        nameTd.textContent = b.name;
        tr.appendChild(nameTd);

        const descTd = document.createElement('td');
        descTd.textContent = b.description;
        tr.appendChild(descTd);

        const countTd = document.createElement('td');
        countTd.textContent = b.awarded_count || 0;
        tr.appendChild(countTd);

        const createdTd = document.createElement('td');
        createdTd.textContent = formatDate(b.created_at);
        tr.appendChild(createdTd);

        const actionsTd = document.createElement('td');
        const actionWrap = document.createElement('div');
        actionWrap.className = 'action-buttons cell-actions';
        const inner = document.createElement('div');
        inner.className = 'actions-container';

        const editBtn = document.createElement('button');
        editBtn.className = 'btn-action btn-edit action-button';
        editBtn.setAttribute('data-tooltip','Edit');
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
        editBtn.onclick = () => openEditModal(b.id);
        inner.appendChild(editBtn);

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-action btn-delete action-button';
        deleteBtn.setAttribute('data-tooltip','Delete');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.onclick = () => deleteBadge(b.id);
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
    const total = filteredBadges.length;
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

    filteredBadges = badgesData.filter(b => {
        return q === '' || b.name.toLowerCase().includes(q) || (b.description || '').toLowerCase().includes(q);
    });
    currentPage = 1;
    renderTable();
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('addBadgeForm').reset();
}

function openEditModal(badgeId) {
    const b = badgesData.find(x => Number(x.id) === Number(badgeId));
    if (!b) return alert('Badge not found');

    document.getElementById('edit_badge_id').value = b.id;
    document.getElementById('edit_name').value = b.name;
    document.getElementById('edit_description').value = b.description;

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editBadgeForm').reset();
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target === addModal) closeAddModal();
    if (event.target === editModal) closeEditModal();
}

document.getElementById('addBadgeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/public/api/badges/add_badge.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newBadge = {
                id: data.badge_id,
                name: formData.get('name'),
                description: formData.get('description'),
                created_at: new Date().toISOString(),
                awarded_count: 0
            };
            badgesData.unshift(newBadge);
            applyFiltersAndSearch();
            closeAddModal();
            alert('Badge added successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
});

document.getElementById('editBadgeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/public/api/badges/update_badge.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const bid = Number(formData.get('badge_id'));
            const idx = badgesData.findIndex(x => Number(x.id) === bid);
            if (idx > -1) {
                badgesData[idx].name = formData.get('name');
                badgesData[idx].description = formData.get('description');
            }
            applyFiltersAndSearch();
            closeEditModal();
            alert('Badge updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
});

function deleteBadge(badgeId) {
    if (!confirm('Are you sure you want to delete this badge?')) return;

    fetch('/public/api/badges/delete_badge.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `badge_id=${badgeId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = badgesData.findIndex(x => Number(x.id) === Number(badgeId));
            if (idx > -1) badgesData.splice(idx,1);
            applyFiltersAndSearch();
            alert('Badge deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
}

document.getElementById('searchInput').addEventListener('input', () => { applyFiltersAndSearch(); });
document.getElementById('perPageSelect').addEventListener('change', () => { perPage = parseInt(document.getElementById('perPageSelect').value,10); currentPage = 1; renderTable(); });

applyFiltersAndSearch();
</script>
<?php endif; ?>

</body>
</html>
