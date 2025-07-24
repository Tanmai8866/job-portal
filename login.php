<?php
require_once 'config/session.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login - Job Board Portal';
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

        
                    Your career journey continues here.
                </p>
            </div>
            <div class="bubble1"></div>
            <div class="bubble2"></div>
            <div class="bubble3"></div>
        </div>
        <div class="form-container">
            <div class="card">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #1f2937;">Welcome Back</h2>
                
                <?php if ($error): ?>
                    <div class="error-message" style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Login</button>
                </form>

                <p style="text-align: center; color: #6b7280;">
                    Don't have an account? <a href="register.php" style="color: #2563eb; font-weight: 600;">Register here</a>
                </p>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
