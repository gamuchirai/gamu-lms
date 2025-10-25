<div class="topbar">
    <div class="topbar-left">
        <h1>Welcome back, <?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?>!</h1>
        
    </div>
    <div class="topbar-right">
        <div class="topbar-icons">
            <div class="topbar-icon"><i class="fas fa-comment-dots"></i></div>
            <div class="topbar-icon"><i class="fas fa-bell"></i></div>
        </div>
        <div class="user-profile">
            <div class="user-avatar"><?php echo substr($_SESSION['firstname'], 0, 1) . substr($_SESSION['lastname'], 0, 1); ?></div>
            <div class="user-name"><?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?></div>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</div>