# Views Folder Structure and Role Guard Implementation

## Views Folder Structure

The application now has a dedicated `views/` folder for role-specific view pages:

```
views/
├── admin/          # Admin-only pages (future: manage_users, manage_courses, etc.)
├── instructor/     # Instructor pages (future: dashboard, course management)
└── student/        # Student pages with course content views
    ├── view_course.php      # Course overview with lessons, assignments, and enrolled students
    ├── view_lesson.php      # Individual lesson view with content and materials
    └── view_assignment.php  # Assignment view with submission interface
```

## Role Guard System

### New Role Guard (`includes/role_guard.php`)

Replaces the old `admin_guard.php` with a flexible role-based access control system.

**Usage:**
```php
<?php
// Define allowed roles before including the guard
$allowed_roles = ['admin', 'instructor'];
require_once __DIR__ . '/../includes/role_guard.php';
?>
```

**Features:**
- Accepts array of allowed roles
- Checks session-stored `role_name`
- Falls back to DB lookup if role not in session
- Redirects unauthorized users to their appropriate dashboard:
  - Admin → `/public/admin_dashboard.php`
  - Instructor → `/public/instructor_dashboard.php`
  - Student → `/public/dashboard.php`

**Examples:**

Admin-only page:
```php
$allowed_roles = ['admin'];
require_once __DIR__ . '/../includes/role_guard.php';
```

Instructor and Admin:
```php
$allowed_roles = ['admin', 'instructor'];
require_once __DIR__ . '/../includes/role_guard.php';
```

All authenticated users:
```php
$allowed_roles = ['admin', 'instructor', 'student'];
require_once __DIR__ . '/../includes/role_guard.php';
```

## View Pages

### 1. Course View (`views/student/view_course.php`)

**Access:** All authenticated users (students, instructors, admins)

**Features:**
- Course header with title, description, instructor name
- Enrollment badge for students
- Tabbed interface:
  - **Lessons Tab**: Displays all course lessons with content and file attachments
  - **Assignments Tab**: Shows assignments with due dates and point values
  - **Students Tab** (Instructors/Admins only): Lists enrolled students with:
    - Student name and email
    - Enrollment date
    - Progress percentage with visual progress bar

**Usage:**
```
/views/student/view_course.php?id=<course_id>
```

### 2. Lesson View (`views/student/view_lesson.php`)

**Access:** Enrolled students, assigned instructors, and admins

**Features:**
- Breadcrumb navigation (Courses → Course → Lesson)
- Full lesson content display
- File attachment downloads
- "Mark as Complete" button (students only, functionality pending)
- Back to course navigation

**Access Control:**
- Checks enrollment status for students
- Verifies instructor assignment via `course_instructors` table
- Admin bypass

**Usage:**
```
/views/student/view_lesson.php?id=<lesson_id>
```

### 3. Assignment View (`views/student/view_assignment.php`)

**Access:** Enrolled students, assigned instructors, and admins

**Features:**
- Breadcrumb navigation
- Assignment title and instructions
- Metadata display:
  - Due date with countdown
  - Time remaining (days/hours)
  - Overdue status badge
  - Maximum points
- File upload interface (students):
  - Drag-and-drop upload area
  - Supported formats: PDF, DOC, DOCX, TXT, ZIP
  - Submit button
- Submission status display (if already submitted)

**Access Control:**
- Same pattern as lesson view
- Checks enrollment/instructor assignment/admin role

**Usage:**
```
/views/student/view_assignment.php?id=<assignment_id>
```

## Next Steps

### Recommended Migrations

1. **Move existing pages to views folders:**
   ```
   public/admin_dashboard.php → views/admin/dashboard.php
   public/manage_users.php → views/admin/manage_users.php
   public/manage_courses.php → views/admin/manage_courses.php
   public/instructor_dashboard.php → views/instructor/dashboard.php
   public/instructor_course.php → views/instructor/course.php
   public/dashboard.php → views/student/dashboard.php
   ```

2. **Update all internal links** to point to new paths

3. **Replace `admin_guard.php` includes** with `role_guard.php` + `$allowed_roles` declarations

4. **Update sidebar.php** navigation links to use new view paths

5. **Implement assignment submission endpoint:**
   - Create `public/api/assignments/submit.php`
   - Handle file uploads with validation
   - Store submission records in database
   - Update `view_assignment.php` to display submission status

6. **Add progress tracking:**
   - Implement "Mark as Complete" functionality in lessons
   - Update enrollment progress percentage
   - Track completed lessons per student
