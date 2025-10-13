
<?php include '../includes/session_guard.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/logo.png" alt="Logo" class="logo" />
        </div>
        <nav>
            <ul>
                <li><a href="#"><span class="icon home"></span> Home</a></li>
                <li><a href="#"><span class="icon courses"></span> Courses</a></li>
                <li><a href="#"><span class="icon assignments"></span> Assignments</a></li>
                <li><a href="#"><span class="icon profile"></span> Profile</a></li>
                <li><a href="logout.php"><span class="icon logout"></span> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main class="main-content">
        <div class="topbar">
            <h2>Good morning, <?php echo $_SESSION['firstname']; ?>!</h2>
            <div class="search-bar">
                <input type="text" placeholder="Search..." />
                <span class="icon search"></span>
            </div>
        </div>
        <div class="stats-cards">
            <div class="card courses">
                <h3>Active Courses</h3>
                <div class="card-value">5 <span class="trend up">↑ 1 new</span></div>
                <div class="card-chart"></div>
            </div>
            <div class="card assignments">
                <h3>Assignments Due</h3>
                <div class="card-value">2 <span class="trend down">↓ 1 completed</span></div>
                <div class="card-chart"></div>
            </div>
            <div class="card progress">
                <h3>Progress</h3>
                <div class="card-value">80% <span class="trend up">↑ 5%</span></div>
                <div class="card-chart"></div>
            </div>
        </div>
        <section class="lms-section">
            <h3>My Courses</h3>
            <table class="lms-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th>Next Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Web Development</td><td>Ms. Smith</td><td>Ongoing</td><td>Project 2 (Oct 15)</td>
                    </tr>
                    <tr>
                        <td>Data Science</td><td>Mr. Lee</td><td>Ongoing</td><td>Quiz 3 (Oct 18)</td>
                    </tr>
                    <tr>
                        <td>UI/UX Design</td><td>Ms. Patel</td><td>Ongoing</td><td>Assignment 1 (Oct 20)</td>
                    </tr>
                    <tr>
                        <td>Mobile Apps</td><td>Mr. Brown</td><td>Ongoing</td><td>Lab 2 (Oct 22)</td>
                    </tr>
                    <tr>
                        <td>Python Basics</td><td>Ms. Green</td><td>Ongoing</td><td>Homework 4 (Oct 25)</td>
                    </tr>
                </tbody>
            </table>
        </section>
        <section class="lms-section">
            <h3>Announcements</h3>
            <ul class="announcements">
                <li><strong>Oct 12:</strong> New course materials uploaded for Data Science.</li>
                <li><strong>Oct 10:</strong> Assignment 1 deadline extended for UI/UX Design.</li>
                <li><strong>Oct 8:</strong> Python Basics quiz results released.</li>
            </ul>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>