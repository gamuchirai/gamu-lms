# Gamuchirai Kundhlande Dzidza LMS

A comprehensive Learning Management System (LMS) built with PHP and MySQL, designed to manage courses, students, assignments, grades, and discussions.

## Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [License](#license)

## Features

- **User Management**
  - Multi-role user system (Students, Admins, Instructors)
  - User registration and email verification
  - Password management with secure hashing

- **Course Management**
  - Create and manage courses
  - Organize lessons within courses
  - Track student enrollments

- **Assignments & Grades**
  - Create assignments with due dates
  - Track student submissions
  - Grade assignments and provide feedback

- **Discussion Forum**
  - Course-specific discussions
  - Reply to discussion threads
  - Collaborative learning environment

- **Activity Tracking**
  - Log all user actions
  - Track user engagement
  - Generate activity reports

- **Gamification**
  - Badge system for achievements
  - Reward student accomplishments
  - Motivate learning progress

## System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache or Nginx
- **Browser**: Modern browser with JavaScript enabled

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/gamuchirai/gamu-lms.git
   cd gamu-lms
   ```

2. **Install dependencies** (if using Composer)
   ```bash
   composer install
   ```

3. **Copy configuration file**
   ```bash
   cp config/db_config.php.live config/db_config.php
   ```

4. **Update database credentials** in `config/db_config.php`

## Database Setup

1. **Create database**
   ```bash
   mysql -u root -p < newdb.sql
   ```

2. **Run migration queries**
   ```bash
   mysql -u root -p your_database < migration.sql
   ```

3. **Verify tables**
   ```sql
   SHOW TABLES;
   ```

## Project Structure

```
gamu-lms/
├── assets/
│   ├── css/              # Stylesheets
│   │   ├── dashboard.css
│   │   ├── login.css
│   │   └── style.css
│   └── img/              # Images and icons
├── config/
│   ├── db_config.php     # Database configuration
│   └── db_config.php.live # Live environment config
├── includes/
│   ├── header.php        # Page header template
│   ├── footer.php        # Page footer template
│   └── session_guard.php # Authentication middleware
├── public/
│   ├── index.php         # Home page
│   ├── login.php         # Login page
│   ├── register.php      # Registration page
│   ├── dashboard.php     # Student dashboard
│   ├── logout.php        # Logout handler
│   ├── update_task.php   # Task update API
│   ├── verify_email.php  # Email verification
│   └── assets/           # Public assets
├── tools/
│   └── generate_docx.py  # Document generation tool
├── logs/
│   └── email_log.txt     # Email activity logs
├── db.sql                # Original database dump
├── newdb.sql             # New database schema
└── README.md             # This file
```

## Configuration

### Database Configuration

Edit `config/db_config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'gamuchirai_db');
?>
```

### Email Configuration

Update email settings in relevant PHP files for:
- Email verification
- Password reset
- Notifications

## Usage

### Accessing the Application

1. Start your web server
2. Navigate to `http://localhost/gamu-lms/` or your configured domain
3. Log in with your credentials

### User Roles

- **Student**: Can view courses, submit assignments, participate in discussions
- **Instructor**: Can create courses, assignments, and grade submissions
- **Admin**: Full system access and management

### Creating a Course

1. Log in as Instructor/Admin
2. Navigate to Courses
3. Click "Create New Course"
4. Fill in course details
5. Add lessons and assignments

### Enrolling Students

1. Go to Course Details
2. Click "Enroll Students"
3. Select students from the list
4. Confirm enrollment

## API Endpoints

### Tasks
- `GET /public/dashboard.php` - View all tasks
- `POST /public/update_task.php` - Update task completion status

### Users
- `POST /public/register.php` - Register new user
- `POST /public/login.php` - User login
- `GET /public/logout.php` - User logout

### Email Verification
- `GET /public/verify_email.php?token=xxx` - Verify email address

## Database Schema

### Core Tables

- **users** - All system users (students, instructors, admins)
- **user_roles** - Role definitions
- **courses** - Course information
- **enrollments** - Student-Course relationships

### Learning Tables

- **lessons** - Course lessons
- **assignments** - Course assignments
- **grades** - Assignment grades for students

### Interaction Tables

- **discussions** - Discussion threads
- **discussion_replies** - Replies to discussions
- **activity_logs** - User activity tracking

### Gamification Tables

- **badges** - Achievement badges
- **user_badges** - User badge awards

### Legacy Tables

- **student_tasks** - Legacy task tracking (being phased out)
- **students** - Legacy student data (being migrated to users)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Development Notes

### Migration from Old Schema

The system is transitioning from a students-only schema to a multi-role users schema:
- All student data has been migrated to the `users` table
- The `students` table is maintained for backward compatibility
- Use the `users` table for all new functionality
- The `student_tasks` table will be replaced by lessons and assignments

### Future Improvements

- [ ] RESTful API
- [ ] Mobile app support
- [ ] Advanced analytics dashboard
- [ ] Real-time notifications
- [ ] Automated grading
- [ ] Video lesson support

## License

This project is proprietary. All rights reserved.

## Support

For issues, questions, or feedback, please contact:
- **Author**: Gamuchirai Kundhlande
- **Email**: gkundhlande@gmail.com
- **Repository**: https://github.com/gamuchirai/gamu-lms

---

**Last Updated**: October 24, 2025

## Starting server

Start the PHP development server from the **root directory** (not from public/):

```bash
php -S localhost:8000
```

Then access (both URL formats work):
- Registration: `http://localhost:8000/public/index.html` or `http://localhost:8000/`
- Login: `http://localhost:8000/public/login.html` or `http://localhost:8000/login.html`
- Email Log (for verification tokens): `http://localhost:8000/public/view_email_log.php` or `http://localhost:8000/view_email_log.php`

**Verification URLs work automatically:**
- Both `http://localhost:8000/verify_email.php?token=XXX` and `http://localhost:8000/public/verify_email.php?token=XXX` work!
- The root `index.php` handles routing and redirects automatically

**Note:** When you register, check the email log to get your verification link since mail() won't work locally without SMTP configuration. After clicking the verification link, you'll be automatically logged in!