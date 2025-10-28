<?php 
$allowed_roles = ['student', 'instructor', 'admin'];
require_once __DIR__ . '/../../includes/role_guard.php';
require_once __DIR__ . '/../../config/db_config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$success_message = '';
$error_message = '';

// Fetch user data
$stmt = $conn->prepare("SELECT u.*, r.role as role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address';
    } else {
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Email already in use by another account';
        } else {
            // Update user
            $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, gender = ?, dob = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $firstname, $lastname, $email, $gender, $dob, $user_id);
            
            if ($stmt->execute()) {
                $success_message = 'Profile updated successfully!';
                $_SESSION['firstname'] = $firstname;
                $_SESSION['lastname'] = $lastname;
                $user['firstname'] = $firstname;
                $user['lastname'] = $lastname;
                $user['email'] = $email;
                $user['gender'] = $gender;
                $user['dob'] = $dob;
            } else {
                $error_message = 'Failed to update profile';
            }
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Failed to change password';
            }
            $stmt->close();
        } else {
            $error_message = 'Current password is incorrect';
        }
    }
}
?>
<?php include __DIR__ . '/../../includes/student_topbar.php'; ?>

<div class="student-content">
    <div class="student-container">
        <div class="breadcrumb">
            <a href="/views/student/dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <span>/</span>
            <span>My Profile</span>
        </div>

        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your account information and settings</p>
        </div>

        <?php if ($success_message): ?>
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user"></i>
                        Profile Information
                    </h2>
                </div>
                
                <form method="POST" action="">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                First Name
                            </label>
                            <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Last Name
                            </label>
                            <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Email Address
                            </label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Gender
                            </label>
                            <select name="gender" required
                                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Date of Birth
                            </label>
                            <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-lock"></i>
                        Change Password
                    </h2>
                </div>
                
                <form method="POST" action="">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Current Password
                            </label>
                            <input type="password" name="current_password" required
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                New Password
                            </label>
                            <input type="password" name="new_password" required minlength="8"
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                            <small style="font-size: 12px; color: #64748b;">Minimum 8 characters</small>
                        </div>

                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px;">
                                Confirm New Password
                            </label>
                            <input type="password" name="confirm_password" required minlength="8"
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Account Information
                </h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div>
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">Account Type</div>
                    <div style="font-size: 16px; font-weight: 600; color: #0f172a;"><?php echo ucfirst(htmlspecialchars($user['role_name'])); ?></div>
                </div>
                <div>
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">Member Since</div>
                    <div style="font-size: 16px; font-weight: 600; color: #0f172a;"><?php echo date('F Y', strtotime($user['created_at'])); ?></div>
                </div>
                <div>
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">Account Status</div>
                    <div style="font-size: 16px; font-weight: 600; color: <?php echo $user['active'] ? '#10b981' : '#ef4444'; ?>;">
                        <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
