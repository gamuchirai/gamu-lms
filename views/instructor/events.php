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

$sql = "SELECT id, title, description, event_date, created_at FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
$events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<link rel="stylesheet" href="/assets/css/table.css?v=1.0">

<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../../public/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/../../public/topbar.php'; ?>
        <div class="content-grid">
            <div class="manage-students-container">
                <div class="students-table-wrapper">
                    <div class="page-header" style="padding:16px 20px;">
                        <h2 style="margin:0; font-size:20px; color:#111827;">System Events</h2>
                        <?php if ($is_admin): ?>
                        <button class="btn-modal btn-save" onclick="openAddModal()" style="padding:10px 20px;">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($events) > 0): ?>
                    <div class="table-controls">
                        <div class="control-row">
                            <input type="search" id="searchInput" placeholder="Search events by title or description" />
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
                                <th>Event Title</th>
                                <th>Description</th>
                                <th>Event Date</th>
                                <th>Created</th>
                                <?php if ($is_admin): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="eventsTableBody">
                            <!-- Table rows will be rendered by JavaScript -->
                        </tbody>
                    </table>

                    <div class="pagination-controls" id="paginationControls"></div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar"></i>
                        <p>No events scheduled.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if ($is_admin): ?>
<!-- Add Event Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Event</h3>
            <button class="close-modal" onclick="closeAddModal()">&times;</button>
        </div>
        <form id="addEventForm">
            <div class="modal-body">
                <div class="form-group-modal">
                    <label for="add_title">Event Title</label>
                    <input type="text" id="add_title" name="title" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
                
                <div class="form-group-modal">
                    <label for="add_event_date">Event Date</label>
                    <input type="date" id="add_event_date" name="event_date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Add Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Event</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editEventForm">
            <div class="modal-body">
                <input type="hidden" id="edit_event_id" name="event_id">
                
                <div class="form-group-modal">
                    <label for="edit_title">Event Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; font-family:inherit;" required></textarea>
                </div>
                
                <div class="form-group-modal">
                    <label for="edit_event_date">Event Date</label>
                    <input type="date" id="edit_event_date" name="event_date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
const eventsData = <?php echo json_encode($events); ?>;
const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

let filteredEvents = [...eventsData];
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
    const tbody = document.getElementById('eventsTableBody');
    tbody.innerHTML = '';

    perPage = parseInt(document.getElementById('perPageSelect').value, 10);
    const start = (currentPage - 1) * perPage;
    const pageItems = filteredEvents.slice(start, start + perPage);

    if (pageItems.length === 0 && currentPage > 1) {
        currentPage = Math.max(1, currentPage - 1);
        return renderTable();
    }

    for (const e of pageItems) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-event-id', e.id);

        const titleTd = document.createElement('td');
        titleTd.style.fontWeight = '600';
        titleTd.textContent = e.title;
        tr.appendChild(titleTd);

        const descTd = document.createElement('td');
        descTd.textContent = e.description;
        tr.appendChild(descTd);

        const dateTd = document.createElement('td');
        dateTd.textContent = formatDate(e.event_date);
        tr.appendChild(dateTd);

        const createdTd = document.createElement('td');
        createdTd.textContent = formatDate(e.created_at);
        tr.appendChild(createdTd);

        if (isAdmin) {
            const actionsTd = document.createElement('td');
            const actionWrap = document.createElement('div');
            actionWrap.className = 'action-buttons cell-actions';
            const inner = document.createElement('div');
            inner.className = 'actions-container';

            const editBtn = document.createElement('button');
            editBtn.className = 'btn-action btn-edit action-button';
            editBtn.setAttribute('data-tooltip','Edit');
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            editBtn.onclick = () => openEditModal(e.id);
            inner.appendChild(editBtn);

            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'btn-action btn-delete action-button';
            deleteBtn.setAttribute('data-tooltip','Delete');
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.onclick = () => deleteEvent(e.id);
            inner.appendChild(deleteBtn);

            actionWrap.appendChild(inner);
            actionsTd.appendChild(actionWrap);
            tr.appendChild(actionsTd);
        }

        tbody.appendChild(tr);
    }

    renderPagination();
}

function renderPagination() {
    const container = document.getElementById('paginationControls');
    container.innerHTML = '';
    const total = filteredEvents.length;
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

    filteredEvents = eventsData.filter(e => {
        return q === '' || e.title.toLowerCase().includes(q) || (e.description || '').toLowerCase().includes(q);
    });
    currentPage = 1;
    renderTable();
}

<?php if ($is_admin): ?>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('addEventForm').reset();
}

function openEditModal(eventId) {
    const e = eventsData.find(x => Number(x.id) === Number(eventId));
    if (!e) return alert('Event not found');

    document.getElementById('edit_event_id').value = e.id;
    document.getElementById('edit_title').value = e.title;
    document.getElementById('edit_description').value = e.description;
    const dateVal = e.event_date ? (new Date(e.event_date)).toISOString().slice(0,10) : '';
    document.getElementById('edit_event_date').value = dateVal;

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editEventForm').reset();
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target === addModal) closeAddModal();
    if (event.target === editModal) closeEditModal();
}

document.getElementById('addEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/public/api/events/add_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newEvent = {
                id: data.event_id,
                title: formData.get('title'),
                description: formData.get('description'),
                event_date: formData.get('event_date'),
                created_at: new Date().toISOString()
            };
            eventsData.unshift(newEvent);
            applyFiltersAndSearch();
            closeAddModal();
            alert('Event added successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
});

document.getElementById('editEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/public/api/events/update_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const eid = Number(formData.get('event_id'));
            const idx = eventsData.findIndex(x => Number(x.id) === eid);
            if (idx > -1) {
                eventsData[idx].title = formData.get('title');
                eventsData[idx].description = formData.get('description');
                eventsData[idx].event_date = formData.get('event_date');
            }
            applyFiltersAndSearch();
            closeEditModal();
            alert('Event updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
});

function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) return;

    fetch('/public/api/events/delete_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `event_id=${eventId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = eventsData.findIndex(x => Number(x.id) === Number(eventId));
            if (idx > -1) eventsData.splice(idx,1);
            applyFiltersAndSearch();
            alert('Event deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('An error occurred.'); });
}
<?php endif; ?>

document.getElementById('searchInput').addEventListener('input', () => { applyFiltersAndSearch(); });
document.getElementById('perPageSelect').addEventListener('change', () => { perPage = parseInt(document.getElementById('perPageSelect').value,10); currentPage = 1; renderTable(); });

applyFiltersAndSearch();
</script>

</body>
</html>
