# Student Tasks Feature - Setup Instructions

## Overview
This feature adds a dynamic "Upcoming" section to the student dashboard that displays tasks (assignments and tests) from the database.

## Database Setup

### Step 1: Import the SQL file
Run the following SQL file to create the `student_tasks` table and add sample data:

```bash
# If using MySQL command line:
mysql -u your_username -p u376937047_gamuchirai_db < student_tasks.sql

# Or import via phpMyAdmin:
# 1. Open phpMyAdmin
# 2. Select the database: u376937047_gamuchirai_db
# 3. Click "Import"
# 4. Choose file: student_tasks.sql
# 5. Click "Go"
```

### What the table includes:
- **id**: Primary key (auto-increment)
- **student_id**: Foreign key to students table
- **title**: Task name/description
- **due_date**: When the task is due
- **task_type**: Either 'assignment' or 'test'
- **created_at**: Timestamp of when task was created

## Features Implemented

### 1. Dynamic Task Display
- Shows up to 4 upcoming tasks
- Automatically filters by logged-in student
- Only shows tasks with due dates today or in the future
- Orders tasks by due date (soonest first)

### 2. Smart Formatting
- Date format: "20 Oct" (day + abbreviated month)
- Task type badges: 
  - "Assignment" (default styling)
  - "Test" (special red styling)

### 3. Empty State
- Shows "No upcoming tasks" message when no tasks are found

## Files Modified

1. **public/dashboard.php**
   - Added database query to fetch student tasks
   - Replaced hardcoded HTML with dynamic PHP loop
   - Added date formatting and conditional styling

2. **student_tasks.sql** (NEW)
   - Table structure
   - Sample data for testing

## Testing

1. Import the SQL file
2. Start the PHP server: `php -S localhost:8000`
3. Login with: gkundhlande@gmail.com
4. Check the "Upcoming" section on the dashboard
5. You should see 4 upcoming tasks displayed

## Future Enhancements

- Add ability to mark tasks as completed
- Create admin interface to add/edit tasks
- Add email notifications for upcoming tasks
- Filter by task type (show only assignments or tests)
- Add task details/description field
