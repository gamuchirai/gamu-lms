# Proposed File Structure for Gamuchirai Kundhlande Dzidza LMS

## Executive Summary

This document proposes a scalable, secure, and maintainable file structure for the Dzidza LMS PHP application. The proposed structure follows industry best practices including separation of concerns, MVC-inspired patterns (without a full framework), proper security boundaries, and code reusability.

## Current Pain Points

1. **Mixed Concerns**: Business logic embedded in page files (register.php, login.php)
2. **Code Duplication**: Database queries repeated across files; similar validation logic scattered
3. **Security Gaps**: Direct database access without abstraction; SQL injection risks with string interpolation
4. **Limited Scalability**: Adding new features (courses, assignments, grading) requires editing multiple files
5. **Asset Organization**: CSS and JS not consistently organized by feature/module
6. **API Inconsistency**: API endpoints in `public/api/` lack standardized structure and authentication middleware

## Proposed Directory Structure

```
dzidza-lms/
│
├── .env                          # Environment variables (add to .gitignore)
├── .env.example                  # Template for environment setup
├── .gitignore                    # Git ignore rules
├── README.md                     # Project documentation
├── composer.json                 # PHP dependency manager (optional but recommended)
│
├── config/                       # Configuration files
│   ├── site_config.php           # Site constants (BASE_URL, APP_ENV) ✓ EXISTS
│   ├── db_config.php             # Database connection ✓ EXISTS
│   ├── mail_config.php           # Email/SMTP configuration (NEW)
│   ├── constants.php             # Application constants (roles, statuses, etc.) (NEW)
│   └── routes.php                # Optional: route definitions for cleaner URLs (NEW)
│
├── src/                          # Source code (business logic, models, services)
│   ├── Models/                   # Data models (User, Course, Assignment, etc.)
│   │   ├── User.php              # User model with CRUD methods
│   │   ├── Course.php            # Course model
│   │   ├── Assignment.php        # Assignment model
│   │   ├── Enrollment.php        # Student-course relationship
│   │   └── Model.php             # Base model class with common DB operations
│   │
│   ├── Services/                 # Business logic services
│   │   ├── AuthService.php       # Authentication logic (login, register, verify)
│   │   ├── EmailService.php      # Email sending with logging fallback
│   │   ├── ValidationService.php # Input validation and sanitization
│   │   ├── EnrollmentService.php # Course enrollment logic
│   │   └── GradingService.php    # Grading and assessment logic
│   │
│   ├── Controllers/              # Request handlers (thin layer)
│   │   ├── AuthController.php    # Login, register, verify, logout
│   │   ├── DashboardController.php
│   │   ├── UserController.php    # Manage users (admin)
│   │   ├── CourseController.php  # Course CRUD
│   │   └── AssignmentController.php
│   │
│   ├── Middleware/               # Request filters (security, auth checks)
│   │   ├── AuthMiddleware.php    # Check if user is logged in
│   │   ├── AdminMiddleware.php   # Check if user has admin role
│   │   ├── CsrfMiddleware.php    # CSRF token validation
│   │   └── RateLimitMiddleware.php # Rate limiting for APIs
│   │
│   ├── Helpers/                  # Utility functions
│   │   ├── Session.php           # Session management wrapper
│   │   ├── Response.php          # JSON/HTTP response helpers
│   │   ├── Validator.php         # Validation helper functions
│   │   └── Security.php          # CSRF, XSS protection, password helpers
│   │
│   └── Database/                 # Database management
│       ├── Connection.php        # PDO/MySQLi connection wrapper
│       ├── QueryBuilder.php      # Safe query building (prevent SQL injection)
│       ├── Migrations/           # Database version control
│       │   ├── 001_create_users_table.sql
│       │   ├── 002_create_courses_table.sql
│       │   └── migration_runner.php
│       └── Seeds/                # Test data for development
│           └── UserSeeder.php
│
├── public/                       # Publicly accessible files (web root)
│   ├── index.php                 # Front controller (routes all requests) (MODIFIED)
│   ├── .htaccess                 # Apache rewrite rules for clean URLs (NEW)
│   │
│   ├── views/                    # Frontend HTML/PHP templates (NEW ORGANIZATION)
│   │   ├── auth/                 # Authentication views
│   │   │   ├── login.php         # Login page
│   │   │   ├── register.php      # Registration page
│   │   │   └── verify_email.php  # Email verification
│   │   │
│   │   ├── dashboard/            # Dashboard views
│   │   │   ├── index.php         # Student/instructor dashboard
│   │   │   └── admin.php         # Admin dashboard
│   │   │
│   │   ├── admin/                # Admin management views
│   │   │   ├── manage_users.php  # User management ✓ EXISTS
│   │   │   ├── manage_courses.php
│   │   │   └── manage_settings.php
│   │   │
│   │   ├── courses/              # Course-related views
│   │   │   ├── index.php         # Course catalog
│   │   │   ├── view.php          # Single course view
│   │   │   └── enroll.php        # Enrollment page
│   │   │
│   │   ├── assignments/          # Assignment views
│   │   │   ├── index.php         # Assignment list
│   │   │   ├── view.php          # View assignment
│   │   │   └── submit.php        # Submit assignment
│   │   │
│   │   ├── layouts/              # Shared layout components
│   │   │   ├── header.php        # ✓ EXISTS (move from includes/)
│   │   │   ├── footer.php        # ✓ EXISTS (move from includes/)
│   │   │   ├── sidebar.php       # ✓ EXISTS (move from public/)
│   │   │   └── topbar.php        # ✓ EXISTS (move from public/)
│   │   │
│   │   └── errors/               # Error pages
│   │       ├── 403.php           # Forbidden
│   │       ├── 404.php           # Not found
│   │       └── 500.php           # Server error
│   │
│   ├── api/                      # RESTful API endpoints (REORGANIZED)
│   │   ├── v1/                   # API version 1
│   │   │   ├── auth/             # Authentication endpoints
│   │   │   │   ├── login.php
│   │   │   │   ├── logout.php
│   │   │   │   └── refresh.php
│   │   │   │
│   │   │   ├── users/            # User management endpoints
│   │   │   │   ├── index.php     # List users (GET)
│   │   │   │   ├── show.php      # Get single user (GET)
│   │   │   │   ├── store.php     # Create user (POST)
│   │   │   │   ├── update.php    # Update user (PUT/POST) ✓ update_student.php
│   │   │   │   ├── delete.php    # Delete user (DELETE) ✓ delete_student.php
│   │   │   │   └── toggle_status.php # ✓ toggle_student_status.php
│   │   │   │
│   │   │   ├── courses/          # Course endpoints
│   │   │   │   ├── index.php
│   │   │   │   ├── show.php
│   │   │   │   └── enroll.php
│   │   │   │
│   │   │   └── assignments/      # Assignment endpoints
│   │   │       ├── index.php
│   │   │       ├── submit.php
│   │   │       └── grade.php
│   │   │
│   │   └── middleware.php        # API authentication & validation (NEW)
│   │
│   └── assets/                   # Static assets (ORGANIZED BY TYPE/MODULE)
│       ├── css/
│       │   ├── app.css           # Global application styles
│       │   ├── dashboard.css     # ✓ EXISTS
│       │   ├── table.css         # ✓ EXISTS (shared table styles)
│       │   ├── login.css         # ✓ EXISTS
│       │   ├── components/       # Component-specific styles (NEW)
│       │   │   ├── modal.css
│       │   │   ├── cards.css
│       │   │   └── forms.css
│       │   └── vendor/           # Third-party CSS
│       │       └── fontawesome/
│       │
│       ├── js/
│       │   ├── app.js            # Global JavaScript
│       │   ├── modules/          # Feature modules (NEW)
│       │   │   ├── auth.js       # Authentication logic
│       │   │   ├── user-management.js # Extract from manage_users.php
│       │   │   ├── course.js
│       │   │   └── assignment.js
│       │   ├── components/       # Reusable UI components (NEW)
│       │   │   ├── modal.js      # Modal component
│       │   │   ├── tooltip.js    # Tooltip (if enhancing beyond CSS)
│       │   │   └── datepicker.js
│       │   └── vendor/           # Third-party libraries
│       │       ├── jquery.min.js (if needed)
│       │       └── chart.js
│       │
│       ├── img/                  # Images
│       │   ├── logo.png
│       │   ├── avatars/          # User profile pictures
│       │   └── icons/
│       │
│       └── uploads/              # User-uploaded files ✓ EXISTS
│           ├── assignments/      # Assignment submissions
│           ├── documents/        # Course materials
│           └── .htaccess         # Restrict direct access (NEW)
│
├── includes/                     # Legacy includes (MIGRATE TO src/)
│   ├── session_guard.php         # ✓ EXISTS → Move to src/Middleware/
│   ├── admin_guard.php           # ✓ EXISTS → Move to src/Middleware/
│   └── (other includes)          # Gradually migrate
│
├── logs/                         # Application logs
│   ├── email_log.txt             # ✓ EXISTS
│   ├── error_log.txt             # PHP errors (NEW)
│   ├── access_log.txt            # Request logging (NEW)
│   └── .htaccess                 # Deny web access (NEW)
│
├── storage/                      # Private file storage (NEW)
│   ├── cache/                    # Cache files
│   ├── sessions/                 # Session files
│   └── temp/                     # Temporary files
│
├── tests/                        # Unit and integration tests (NEW)
│   ├── Unit/
│   │   ├── UserModelTest.php
│   │   └── ValidationServiceTest.php
│   └── Integration/
│       └── AuthFlowTest.php
│
├── tools/                        # Development tools ✓ EXISTS
│   ├── generate_docx.py          # ✓ EXISTS
│   └── migrate.php               # Database migration runner (NEW)
│
└── docs/                         # Documentation (NEW)
    ├── API.md                    # API documentation
    ├── DATABASE.md               # Database schema documentation
    └── DEPLOYMENT.md             # Deployment guide
```

## Migration Strategy (Phased Approach)

### Phase 1: Foundation (Week 1-2)
**Goal**: Establish core structure without breaking existing functionality

1. **Create New Directories**
   - Create `src/`, `src/Models/`, `src/Services/`, `src/Helpers/`
   - Keep existing `public/` structure intact

2. **Build Base Classes**
   - `src/Database/Connection.php` - PDO wrapper with prepared statements
   - `src/Models/Model.php` - Base model with common CRUD operations
   - `src/Helpers/Session.php` - Session management wrapper
   - `src/Helpers/Response.php` - JSON response helpers

3. **Create User Model**
   - `src/Models/User.php` - Move user-related queries from scattered files
   - Use prepared statements exclusively (prevent SQL injection)
   - Methods: `findByEmail()`, `create()`, `update()`, `delete()`, `verify()`, etc.

4. **Extract Authentication Logic**
   - `src/Services/AuthService.php` - Consolidate login, register, verify logic
   - `src/Services/EmailService.php` - Centralize email sending with logging

### Phase 2: Security Hardening (Week 3)
**Goal**: Fix security vulnerabilities

1. **Replace String Interpolation with Prepared Statements**
   - Audit all SQL queries in `public/*.php`
   - Replace with Model methods or QueryBuilder

2. **Implement Middleware**
   - `src/Middleware/AuthMiddleware.php` - Replace `session_guard.php`
   - `src/Middleware/AdminMiddleware.php` - Replace `admin_guard.php`
   - `src/Middleware/CsrfMiddleware.php` - Add CSRF protection

3. **Input Validation**
   - `src/Services/ValidationService.php` - Centralized validation
   - `src/Helpers/Security.php` - XSS prevention, CSRF tokens

### Phase 3: Refactor Existing Pages (Week 4-5)
**Goal**: Migrate existing pages to use new structure

1. **Authentication Pages**
   - Refactor `public/login.php` to use `AuthService`
   - Refactor `public/register.php` to use `AuthService` and `User` model
   - Refactor `public/verify_email.php`
   - Move to `public/views/auth/` (optional in this phase)

2. **Dashboard**
   - Extract dashboard logic into `src/Controllers/DashboardController.php`
   - Use User model for data fetching

3. **User Management**
   - Refactor `public/manage_users.php` to use `UserController` and `User` model
   - Extract inline JavaScript to `public/assets/js/modules/user-management.js`
   - Refactor API endpoints (`api/update_student.php`, etc.) to use controllers

### Phase 4: New Features (Week 6+)
**Goal**: Add scalable course/assignment functionality

1. **Course Module**
   - Create `src/Models/Course.php`
   - Create `src/Controllers/CourseController.php`
   - Create views in `public/views/courses/`
   - API endpoints in `public/api/v1/courses/`

2. **Assignment Module**
   - Create `src/Models/Assignment.php`, `src/Models/Submission.php`
   - Create `src/Controllers/AssignmentController.php`
   - File upload handling with validation
   - Create views in `public/views/assignments/`

3. **Enrollment System**
   - Create `src/Models/Enrollment.php`
   - Create `src/Services/EnrollmentService.php`
   - Student-course relationship management

### Phase 5: Polish & Optimization (Ongoing)
1. Implement caching (query results, views)
2. Add comprehensive logging
3. Write unit tests for critical paths
4. API documentation
5. Performance optimization (lazy loading, pagination)

## Key Architectural Principles

### 1. Separation of Concerns
- **Models**: Data access and business rules
- **Services**: Complex business logic (orchestrate multiple models)
- **Controllers**: Handle HTTP requests, call services, return responses
- **Views**: Presentation logic only (no database queries)

### 2. Security by Default
```php
// ❌ CURRENT (Vulnerable)
$sql = "SELECT * FROM users WHERE email='$email'";

// ✅ PROPOSED (Safe)
$user = User::findByEmail($email); // Uses prepared statements internally
```

### 3. DRY (Don't Repeat Yourself)
```php
// ❌ CURRENT: Authentication check repeated in every page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// ✅ PROPOSED: Single middleware
AuthMiddleware::require(); // Reusable across all protected pages
```

### 4. Consistent API Structure
```
# Current (Inconsistent)
/public/api/update_student.php
/public/api/toggle_student_status.php
/public/api/delete_student.php

# Proposed (RESTful)
POST   /api/v1/users/123        (update)
PATCH  /api/v1/users/123/status (toggle)
DELETE /api/v1/users/123        (delete)
```

### 5. Configuration Management
```php
// All configs in one place, loaded once
require_once 'config/site_config.php';    // BASE_URL, APP_ENV
require_once 'config/db_config.php';      // Database connection
require_once 'config/constants.php';      // ROLE_STUDENT, ROLE_ADMIN, etc.
```

## Example: Refactored User Registration

### Current Code (register.php)
```php
// Mixed concerns, SQL injection risk, scattered logic
$email = $_POST['email'];
$check_sql = "SELECT sid FROM users WHERE email='$email'"; // ❌ Injection risk
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$sql = "INSERT INTO users (firstname, lastname, email, password, gender, dob, token, email_verified) 
        VALUES ('$firstname', '$lastname', '$email', '$password', '$gender', '$dob', '$token', 0)"; // ❌
$verify_link = BASE_URL . 'public/verify_email.php?token=' . $token;
// Email sending logic inline...
```

### Proposed Code (Refactored)
```php
// public/views/auth/register.php (or routed through index.php)
<?php
require_once '../../config/site_config.php';
require_once '../../src/Services/AuthService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authService = new AuthService();
    
    $result = $authService->register([
        'firstname' => $_POST['firstname'],
        'lastname'  => $_POST['lastname'],
        'email'     => $_POST['email'],
        'password'  => $_POST['password'],
        'gender'    => $_POST['gender'],
        'dob'       => $_POST['dob']
    ]);
    
    if ($result['success']) {
        Response::redirect('verify_email.php', 'Registration successful! Check your email.');
    } else {
        Response::error($result['message']);
    }
}
?>
<!-- HTML form here -->
```

```php
// src/Services/AuthService.php
class AuthService {
    private $userModel;
    private $emailService;
    private $validator;
    
    public function __construct() {
        $this->userModel = new User();
        $this->emailService = new EmailService();
        $this->validator = new ValidationService();
    }
    
    public function register(array $data): array {
        // Validate input
        $errors = $this->validator->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        // Check if email exists (using safe prepared statement)
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Generate token
        $token = random_int(100000, 999999);
        
        // Create user (safe insert with prepared statements)
        $userId = $this->userModel->create([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'gender'    => $data['gender'],
            'dob'       => $data['dob'],
            'token'     => $token,
            'email_verified' => 0
        ]);
        
        if (!$userId) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
        
        // Send verification email
        $this->emailService->sendVerificationEmail($data['email'], $data['firstname'], $token);
        
        return ['success' => true, 'user_id' => $userId];
    }
}
```

```php
// src/Models/User.php
class User extends Model {
    protected $table = 'users';
    
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }
    
    public function create(array $data): int|false {
        // Use parent Model's safe insert method with prepared statements
        return parent::insert($data);
    }
    
    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Other methods: update(), delete(), verify(), etc.
}
```

## Benefits of Proposed Structure

### 1. Security
- ✅ Prepared statements prevent SQL injection
- ✅ CSRF protection on all state-changing requests
- ✅ Input validation centralized and consistent
- ✅ XSS prevention with proper escaping helpers
- ✅ Rate limiting on API endpoints

### 2. Maintainability
- ✅ Single Responsibility Principle - each class has one job
- ✅ Easy to locate code (User logic → User model/service)
- ✅ Consistent patterns across the application
- ✅ Self-documenting structure

### 3. Scalability
- ✅ Add new features without touching existing code
- ✅ API versioning allows breaking changes (v1 → v2)
- ✅ Database migrations track schema changes
- ✅ Easy to add caching, queueing, etc.

### 4. Testability
- ✅ Business logic separated from HTTP/request handling
- ✅ Models and services can be unit tested
- ✅ Mock dependencies easily
- ✅ Integration tests for critical flows

### 5. Developer Experience
- ✅ Clear conventions reduce decision fatigue
- ✅ New developers onboard faster
- ✅ Code reuse reduces bugs
- ✅ IDE autocomplete works better with classes

## Configuration Files to Add

### .gitignore
```
.env
vendor/
storage/cache/*
storage/sessions/*
storage/temp/*
logs/*.txt
!logs/.gitkeep
node_modules/
*.log
.DS_Store
Thumbs.db
```

### .env.example
```
# Copy this file to .env and configure for your environment

APP_ENV=development
APP_DEBUG=true

# Live production domain
LIVE_DOMAIN=https://gamuchiraikundhlande.eagletechafrica.com/

# Local development domain
LOCAL_DOMAIN=http://localhost:8000/

# Database Configuration
DB_HOST=localhost
DB_NAME=dzidza_lms
DB_USER=root
DB_PASS=

# Email Configuration (optional - uses log fallback if not configured)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@dzidza-lms.test
MAIL_FROM_NAME="Dzidza LMS"

# Session Configuration
SESSION_LIFETIME=7200
SESSION_COOKIE_SECURE=false

# Security
CSRF_TOKEN_EXPIRY=3600
```

### composer.json (Optional but Recommended)
```json
{
    "name": "gamuchirai/dzidza-lms",
    "description": "Dzidza Learning Management System",
    "type": "project",
    "require": {
        "php": ">=8.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "DzidzaLMS\\": "src/"
        }
    }
}
```

## Database Improvements

### Use Migrations Instead of SQL Dumps
```php
// tools/migrate.php
// Run: php tools/migrate.php up

// Migration: 001_create_users_table.sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other'),
    dob DATE,
    role_id INT DEFAULT 1,
    active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    token VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role_id (role_id),
    FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Add Proper Indexes
- User email (for login lookups)
- User role_id (for role checks)
- Course instructor_id
- Enrollment student_id + course_id (composite)

## API Standards

### Request Format
```
POST /api/v1/users
Content-Type: application/json
Authorization: Bearer <token> (for future JWT auth)

{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com"
}
```

### Response Format
```json
{
    "success": true,
    "data": {
        "id": 123,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john@example.com"
    },
    "message": "User created successfully"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "email": ["Email is required", "Email must be valid"]
        }
    }
}
```

## Performance Considerations

1. **Query Optimization**
   - Use indexes on frequently queried columns
   - Implement pagination for large datasets
   - Cache expensive queries (user roles, course lists)

2. **Asset Optimization**
   - Minify CSS/JS for production
   - Use CDN for static assets
   - Implement browser caching headers

3. **Database Connection Pooling**
   - Reuse connections where possible
   - Close connections explicitly

4. **Lazy Loading**
   - Load related data only when needed
   - Implement infinite scroll for long lists

## Security Checklist

- [x] Use prepared statements (prevent SQL injection)
- [ ] Implement CSRF tokens on all forms
- [ ] Validate and sanitize all user input
- [ ] Use HTTPS in production (enforce in .htaccess)
- [ ] Hash passwords with bcrypt (already doing this ✓)
- [ ] Implement rate limiting on login/API endpoints
- [ ] Add CAPTCHA on registration/login (optional)
- [ ] Secure file upload validation (type, size, virus scan)
- [ ] Set secure session cookie flags (httpOnly, secure, sameSite)
- [ ] Implement proper error handling (don't expose stack traces)
- [ ] Use Content Security Policy headers
- [ ] Regular dependency updates (if using composer)

## Next Steps & Recommendations

### Immediate Actions (Can Start Now)
1. ✅ Create `.env` and `config/site_config.php` (DONE)
2. Create `.gitignore` and add `.env` to it
3. Create `src/` directory structure
4. Build base Model class with PDO and prepared statements
5. Create User model and migrate one page (e.g., login.php) as proof of concept

### Short Term (Next 2-4 Weeks)
1. Refactor authentication pages to use new structure
2. Implement middleware for session/admin checks
3. Migrate user management page to use controllers
4. Extract JavaScript to separate files

### Medium Term (1-2 Months)
1. Build course management module
2. Build assignment module
3. Implement proper API versioning
4. Add comprehensive validation

### Long Term (2-3 Months)
1. Add automated testing
2. Implement caching strategy
3. Add comprehensive logging
4. Create admin analytics dashboard
5. Mobile-responsive refinements

## Questions to Consider

1. **Framework vs. Custom**: Would you consider using a micro-framework like Slim or Flight for routing? (Keeps it lightweight but adds structure)

2. **Authentication**: Do you want to implement JWT tokens for API authentication, or stick with session-based auth?

3. **Frontend Framework**: Keep vanilla JS or consider Vue.js/Alpine.js for interactive components?

4. **Database**: Stick with MySQLi or migrate to PDO for better portability and prepared statement support?

5. **Composer**: Are you open to using Composer for dependency management and autoloading?

6. **Testing**: Priority on unit tests, or focus on functional testing first?

## Conclusion

This proposed structure provides:
- **Security**: Prepared statements, input validation, CSRF protection
- **Scalability**: Easy to add courses, assignments, quizzes, forums
- **Maintainability**: Clear separation of concerns, DRY principles
- **Developer Experience**: Consistent patterns, easy to understand

The migration can be done gradually (phase by phase) without breaking existing functionality. Start with foundation, then security, then refactor existing features, then add new features.

**Estimated Timeline**: 6-8 weeks for complete migration (working part-time)
**Risk Level**: Low (phased approach allows testing at each step)
**ROI**: High (prevents technical debt, enables faster feature development)

---

*This is a proposal document. Implementation details and timeline can be adjusted based on priorities and resources.*
