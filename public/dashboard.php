<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=2.0">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-feather"></i>
                <span>Quyl.</span>
            </div>
            <nav>
                <ul>
                    <li><a href="#" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="#"><i class="fas fa-list-check"></i> Chapter</a></li>
                    <li><a href="#"><i class="fas fa-circle-question"></i> Help</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <ul>
                    <li><a href="#"><i class="fas fa-circle-question"></i> FAQ</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- TOPBAR -->
            <div class="topbar">
                <div class="topbar-left">
                    <h1>Dashboard</h1>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search here...">
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-icons">
                        <div class="topbar-icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="topbar-icon"><i class="fas fa-bell"></i></div>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">DP</div>
                        <div class="user-name">Dipu paul</div>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>

            <!-- CONTENT GRID -->
            <div class="content-grid">
                <div>
                    <!-- STATS CARDS -->
                    <div class="stats-cards">
                        <div class="stat-card lessons">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div class="stat-number">42</div>
                            <div class="stat-label">Lessons</div>
                            <div class="stat-subtitle">of 71 completed</div>
                        </div>
                        <div class="stat-card assignments">
                            <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                            <div class="stat-number">08</div>
                            <div class="stat-label">Assignments</div>
                            <div class="stat-subtitle">of 24 completed</div>
                        </div>
                        <div class="stat-card tests">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-number">03</div>
                            <div class="stat-label">Tests</div>
                            <div class="stat-subtitle">of 15 completed</div>
                        </div>
                    </div>

                    <!-- COURSES SECTION -->
                    <div class="section">
                        <div class="section-header">
                            <i class="fas fa-book-open"></i>
                            <h2 class="section-title">My Courses</h2>
                        </div>
                        <div class="section-tabs">
                            <div class="section-tab active">Active</div>
                            <div class="section-tab">Completed</div>
                        </div>
                        <div class="courses-list">
                            <div class="course-item">
                                <div class="course-icon design"><i class="fas fa-palette"></i></div>
                                <div class="course-name">Web Design: Form Figma to we...</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                            <div class="course-item">
                                <div class="course-icon html"><i class="fas fa-code"></i></div>
                                <div class="course-name">Html Basics</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                            <div class="course-item">
                                <div class="course-icon python"><i class="fas fa-snake"></i></div>
                                <div class="course-name">Data with python</div>
                                <div class="course-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                </div>
                                <div class="course-meta">
                                    <span><i class="fas fa-book-open"></i> 15</span>
                                    <span><i class="fas fa-tasks"></i> 6</span>
                                    <span><i class="fas fa-comment"></i> 3</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDEBAR -->
                <div class="right-sidebar">
                    <!-- UPCOMING -->
                    <div class="upcoming-section">
                        <h3 class="upcoming-title">Upcoming</h3>
                        <div class="upcoming-list">
                            <div class="upcoming-item">
                                <div class="upcoming-date">29 Sept</div>
                                <div class="upcoming-name">Practical theory</div>
                                <span class="upcoming-tag">Assignments</span>
                            </div>
                            <div class="upcoming-item">
                                <div class="upcoming-date">29 Sept</div>
                                <div class="upcoming-name">Practical theory I</div>
                                <span class="upcoming-tag test">Test</span>
                            </div>
                            <div class="upcoming-item">
                                <div class="upcoming-date">29 Sept</div>
                                <div class="upcoming-name">Practical theory 2</div>
                                <span class="upcoming-tag">Assignments</span>
                            </div>
                            <div class="upcoming-item">
                                <div class="upcoming-date">29 Sept</div>
                                <div class="upcoming-name">Practical theory 3</div>
                                <span class="upcoming-tag">Assignments</span>
                            </div>
                        </div>
                    </div>

                    <!-- CALENDAR -->
                    <div class="calendar-section">
                        <h3 class="calendar-title">Status</h3>
                        <div class="calendar">
                            <div class="calendar-day-header">Mo</div>
                            <div class="calendar-day-header">Tu</div>
                            <div class="calendar-day-header">We</div>
                            <div class="calendar-day-header">Th</div>
                            <div class="calendar-day-header">Fr</div>
                            <div class="calendar-day-header">Sa</div>
                            <div class="calendar-day-header">Su</div>
                            
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>
                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day">9</div>
                            <div class="calendar-day">10</div>
                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day">13</div>
                            <div class="calendar-day">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day">16</div>
                            <div class="calendar-day">17</div>
                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day active">23</div>
                            <div class="calendar-day">24</div>
                            <div class="calendar-day">25</div>
                            <div class="calendar-day">26</div>
                            <div class="calendar-day active">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>