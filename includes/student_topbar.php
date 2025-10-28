<?php
// Student Top Navigation Bar
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$firstname = $_SESSION['firstname'] ?? 'Student';
$lastname = $_SESSION['lastname'] ?? '';
$role_name = $_SESSION['role_name'] ?? 'student';

// Get unread notification count
$unread_count = 0;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND `read` = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $unread_count = $result->fetch_assoc()['count'];
    }
    $stmt->close();
}

// Get initials for avatar
$initials = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/student_layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="student-topnav">
    <a href="/views/student/dashboard.php" class="topnav-brand">
        <img src="/public/assets/img/Dzidzaa.png" alt="Dzidza LMS">
        <span>Dzidza LMS</span>
    </a>

    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topnav-menu" id="topnavMenu">
        <a href="/views/student/dashboard.php" class="topnav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/views/student/courses.php" class="topnav-link <?php echo $current_page === 'courses.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Courses
        </a>
        <a href="/views/student/assignments.php" class="topnav-link <?php echo $current_page === 'assignments.php' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i> Assignments
        </a>
        <a href="/views/student/grades.php" class="topnav-link <?php echo $current_page === 'grades.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Grades
        </a>
        <a href="/views/student/discussions.php" class="topnav-link <?php echo $current_page === 'discussions.php' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> Discussions
        </a>
        <a href="/views/student/badges.php" class="topnav-link <?php echo $current_page === 'badges.php' ? 'active' : ''; ?>">
            <i class="fas fa-trophy"></i> Badges
        </a>
    </div>

    <div class="topnav-right">
        <div class="topnav-notifications" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
            <span class="notification-badge" id="notifBadge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
            
            <!-- Notifications Dropdown -->
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notifications-header">
                    <h3>Notifications</h3>
                    <button onclick="markAllAsRead(event)" class="mark-all-read">Mark all as read</button>
                </div>
                <div class="notifications-list" id="notificationsList">
                    <div class="loading">Loading notifications...</div>
                </div>
            </div>
        </div>

        <div class="topnav-profile" onclick="toggleProfileMenu()">
            <div class="topnav-avatar"><?php echo $initials; ?></div>
            <div class="topnav-profile-info">
                <div class="topnav-name"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></div>
                <div class="topnav-role"><?php echo ucfirst(htmlspecialchars($role_name)); ?></div>
            </div>
            <i class="fas fa-chevron-down"></i>
            
            <div class="profile-dropdown" id="profileDropdown">
                <a href="/views/student/profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="/views/student/settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="/public/logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('topnavMenu');
    menu.classList.toggle('show');
}

function toggleProfileMenu() {
    event.stopPropagation();
    const dropdown = document.getElementById('profileDropdown');
    const notifDropdown = document.getElementById('notificationsDropdown');
    dropdown.classList.toggle('show');
    if (notifDropdown) notifDropdown.classList.remove('show');
}

function toggleNotifications() {
    event.stopPropagation();
    const dropdown = document.getElementById('notificationsDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    } else {
        dropdown.classList.add('show');
        if (profileDropdown) profileDropdown.classList.remove('show');
        loadNotifications();
    }
}

function loadNotifications() {
    const container = document.getElementById('notificationsList');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    fetch('/public/api/notifications/list.php')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.notifications && data.notifications.length > 0) {
                container.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item ${notif.read == 0 ? 'unread' : ''}" 
                         onclick="markAsRead(${notif.id})" data-id="${notif.id}">
                        <div class="notification-icon ${notif.type}">
                            <i class="fas fa-${getNotificationIcon(notif.type)}"></i>
                        </div>
                        <div class="notification-title">${escapeHtml(notif.title)}</div>
                        <div class="notification-message">${escapeHtml(notif.message)}</div>
                        <div class="notification-time">
                            <i class="far fa-clock"></i> ${formatTime(notif.created_at)}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="notifications-empty">
                        <i class="far fa-bell-slash"></i>
                        <p>No notifications yet</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div class="notifications-empty">Failed to load notifications</div>';
        });
}

function markAsRead(notifId) {
    event.stopPropagation();
    
    fetch('/public/api/notifications/mark_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `notification_id=${notifId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${notifId}"]`);
            if (item) item.classList.remove('unread');
            updateBadgeCount();
        }
    })
    .catch(err => console.error(err));
}

function markAllAsRead(e) {
    e.stopPropagation();
    
    fetch('/public/api/notifications/mark_all_read.php', {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateBadgeCount();
        }
    })
    .catch(err => console.error(err));
}

function updateBadgeCount() {
    fetch('/public/api/notifications/unread_count.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    const notifBtn = document.querySelector('.topnav-notifications');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.id = 'notifBadge';
                    newBadge.textContent = data.count;
                    notifBtn.appendChild(newBadge);
                }
            } else {
                if (badge) badge.remove();
            }
        })
        .catch(err => console.error(err));
}

function getNotificationIcon(type) {
    const icons = {
        'info': 'info-circle',
        'success': 'check-circle',
        'warning': 'exclamation-triangle',
        'error': 'times-circle'
    };
    return icons[type] || 'bell';
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const notifDropdown = document.getElementById('notificationsDropdown');
    const profile = event.target.closest('.topnav-profile');
    const notifBtn = event.target.closest('.topnav-notifications');
    
    if (!profile && dropdown) {
        dropdown.classList.remove('show');
    }
    
    if (!notifBtn && notifDropdown) {
        notifDropdown.classList.remove('show');
    }
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('topnavMenu');
    const toggle = event.target.closest('.mobile-menu-toggle');
    const menuItem = event.target.closest('.topnav-menu');
    
    if (!toggle && !menuItem && menu) {
        menu.classList.remove('show');
    }
});

// Check for new notifications every 30 seconds
setInterval(updateBadgeCount, 30000);
</script>
