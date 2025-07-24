<?php
require_once 'config/session.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$selected_role = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['job_seeker', 'employer'])) {
        $error = 'Please select a valid role.';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email address is already registered.';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Register - Job Board Portal';
include 'includes/header.php';
?>
<head>
    <link rel="stylesheet" href="assets/css/register-style.css">
</head>
<main>
    <div class="register-container">
        <div class="left-side">
            <div class="branding">
                <h1 class="site-name">
                    Job<span class="highlight">X</span>
                </h1>
                <h3 class="site-subname">TALENT</h3>
                <p class="welcome-text">
                   
                    Your career journey starts here.
                </p>
            </div>
            <div class="bubble1"></div>
            <div class="bubble2"></div>
            <div class="bubble3"></div>
        </div>
        <div class="form-container">
            <div class="card">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #1f2937;">Create Your Account</h2>
                
                <?php if ($error): ?>
                    <div class="error-message" style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message" style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
                        <?php echo htmlspecialchars($success); ?>
                        <br><a href="login.php" style="color: #065f46; font-weight: bold;">Click here to login</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="role">I am a:</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Select your role</option>
                            <option value="job_seeker" <?php echo ($selected_role == 'job_seeker' || ($_POST['role'] ?? '') == 'job_seeker') ? 'selected' : ''; ?>>
                                Job Seeker
                            </option>
                            <option value="employer" <?php echo ($selected_role == 'employer' || ($_POST['role'] ?? '') == 'employer') ? 'selected' : ''; ?>>
                                Employer
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Create Account</button>
                </form>

                <p style="text-align: center; color: #6b7280;">
                    Already have an account? <a href="login.php" style="color: #2563eb; font-weight: 600;">Login here</a>
                </p>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
