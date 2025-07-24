<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

if (!isEmployer()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $type = $_POST['type'];
    $salary = trim($_POST['salary']);

    // Validation
    if (empty($title) || empty($description) || empty($location) || empty($type)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($title) < 3) {
        $error = 'Job title must be at least 3 characters long.';
    } elseif (strlen($description) < 50) {
        $error = 'Job description must be at least 50 characters long.';
    } elseif (!in_array($type, ['full_time', 'part_time', 'contract', 'internship'])) {
        $error = 'Please select a valid job type.';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $salary_value = !empty($salary) ? intval($salary) : null;

        $query = "INSERT INTO jobs (employer_id, title, description, location, type, salary, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([getUserId(), $title, $description, $location, $type, $salary_value])) {
            $success = 'Job posted successfully!';
            // Clear form data
            $_POST = [];
        } else {
            $error = 'Failed to post job. Please try again.';
        }
    }
}

$page_title = 'Post a Job - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem; color: #1f2937;">Post a New Job</h1>

            <div class="card">
                <?php if ($error): ?>
                    <div class="error-message" style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message" style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
                        <?php echo htmlspecialchars($success); ?>
                        <br><a href="my-jobs.php" style="color: #065f46; font-weight: bold;">View your posted jobs</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Job Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required 
                               placeholder="e.g. Senior Software Developer"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="type">Job Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="">Select job type</option>
                            <option value="full_time" <?php echo ($_POST['type'] ?? '') === 'full_time' ? 'selected' : ''; ?>>
                                Full Time
                            </option>
                            <option value="part_time" <?php echo ($_POST['type'] ?? '') === 'part_time' ? 'selected' : ''; ?>>
                                Part Time
                            </option>
                            <option value="contract" <?php echo ($_POST['type'] ?? '') === 'contract' ? 'selected' : ''; ?>>
                                Contract
                            </option>
                            <option value="internship" <?php echo ($_POST['type'] ?? '') === 'internship' ? 'selected' : ''; ?>>
                                Internship
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" class="form-control" required 
                               placeholder="e.g. New York, NY or Remote"
                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="salary">Annual Salary (Optional)</label>
                        <input type="number" id="salary" name="salary" class="form-control" 
                               placeholder="e.g. 75000" min="0"
                               value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>">
                        <small style="color: #6b7280;">Enter annual salary in USD (numbers only)</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Job Description *</label>
                        <textarea id="description" name="description" class="form-control" required 
                                  rows="10" placeholder="Provide a detailed description of the job role, responsibilities, requirements, and qualifications..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <small style="color: #6b7280;">Minimum 50 characters required</small>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn">Post Job</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <h3 style="color: #2563eb; margin-bottom: 1rem;">Tips for Writing Great Job Posts</h3>
                <ul style="color: #6b7280; line-height: 1.8;">
                    <li><strong>Be specific:</strong> Include clear job requirements and qualifications</li>
                    <li><strong>Highlight benefits:</strong> Mention salary, benefits, and company culture</li>
                    <li><strong>Use keywords:</strong> Include relevant skills and technologies</li>
                    <li><strong>Be honest:</strong> Accurately describe the role and expectations</li>
                    <li><strong>Include growth opportunities:</strong> Mention career development prospects</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>