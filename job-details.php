<?php
require_once 'config/session.php';
require_once 'config/database.php';

$job_id = intval($_GET['id'] ?? 0);
if (!$job_id) {
    header('Location: jobs.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get job details
$query = "SELECT j.*, u.name as company_name, u.email as company_email FROM jobs j 
          JOIN users u ON j.employer_id = u.id WHERE j.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: jobs.php');
    exit();
}

// Check if user already applied (for job seekers)
$already_applied = false;
if (isLoggedIn() && isJobSeeker()) {
    $query = "SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$job_id, getUserId()]);
    $already_applied = $stmt->rowCount() > 0;
}

// Handle job application
$application_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    error_log("DEBUG: Form submission received in job-details.php");
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    if (!isJobSeeker()) {
        $application_message = 'Only job seekers can apply for jobs.';
    } elseif ($already_applied) {
        $application_message = 'You have already applied for this job.';
    } else {
        $resume_link = trim($_POST['resume_link'] ?? '');
        
        $query = "INSERT INTO applications (job_id, seeker_id, resume_link, status, applied_at) 
                  VALUES (?, ?, ?, 'pending', NOW())";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$job_id, getUserId(), $resume_link])) {
            $application_message = 'Application submitted successfully!';
            $already_applied = true;
        } else {
            $application_message = 'Failed to submit application. Please try again.';
        }
    }
}

$page_title = htmlspecialchars($job['title']) . ' - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <div style="margin-bottom: 2rem;">
            <a href="jobs.php" style="color: #2563eb; text-decoration: none;">← Back to Jobs</a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Job Details -->
            <div class="card">
                <div style="margin-bottom: 2rem;">
                    <h1 style="color: #1f2937; margin-bottom: 1rem;"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <h2 style="color: #2563eb; font-size: 1.5rem; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($job['company_name']); ?>
                    </h2>
                    
                    <div class="job-meta" style="margin-bottom: 2rem;">
                        <span class="job-tag"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['type']))); ?></span>
                        <span class="job-tag"><?php echo htmlspecialchars($job['location']); ?></span>
                        <?php if ($job['salary']): ?>
                            <span class="job-tag">$<?php echo htmlspecialchars(number_format($job['salary'])); ?></span>
                        <?php endif; ?>
                        <span class="job-tag">Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?></span>
                    </div>
                </div>

                <div style="margin-bottom: 3rem;">
                    <h3 style="color: #1f2937; margin-bottom: 1rem;">Job Description</h3>
                    <div style="line-height: 1.8; color: #374151;">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>
                </div>

                <?php if (isLoggedIn() && isJobSeeker()): ?>
                    <!-- Application Form -->
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 2rem;">
                        <h3 style="color: #1f2937; margin-bottom: 1rem;">Apply for this Job</h3>
                        
                        <?php if ($application_message): ?>
                            <div class="<?php echo $already_applied && strpos($application_message, 'successfully') !== false ? 'success' : 'error'; ?>-message" 
                                 style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; 
                                        background: <?php echo $already_applied && strpos($application_message, 'successfully') !== false ? '#d1fae5' : '#fee2e2'; ?>; 
                                        color: <?php echo $already_applied && strpos($application_message, 'successfully') !== false ? '#065f46' : '#991b1b'; ?>;">
                                <?php echo htmlspecialchars($application_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$already_applied): ?>
<form method="POST" action="">
    <input type="hidden" name="apply" value="1" />
    <div class="form-group">
        <label for="resume_link">Resume/Portfolio Link (Optional)</label>
        <input type="url" id="resume_link" name="resume_link" class="form-control" 
               placeholder="https://example.com/your-resume.pdf">
        <small style="color: #6b7280;">Provide a link to your resume, portfolio, or LinkedIn profile</small>
    </div>
    <button type="submit" class="btn">Submit Application</button>
</form>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; background: #f9fafb; border-radius: 8px;">
                                <p style="color: #6b7280; margin-bottom: 1rem;">You have already applied for this job.</p>
                                <a href="my-applications.php" class="btn btn-secondary">View My Applications</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 2rem; text-align: center;">
                        <h3 style="color: #1f2937; margin-bottom: 1rem;">Ready to Apply?</h3>
                        <p style="color: #6b7280; margin-bottom: 2rem;">Join our platform to apply for this job and discover more opportunities.</p>
                        <div>
                            <a href="register.php?role=job_seeker" class="btn">Create Account</a>
                            <a href="login.php" class="btn btn-secondary" style="margin-left: 1rem;">Login</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="color: #1f2937; margin-bottom: 1rem;">Company Information</h3>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #374151;">Company:</strong><br>
                        <span style="color: #6b7280;"><?php echo htmlspecialchars($job['company_name']); ?></span>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #374151;">Location:</strong><br>
                        <span style="color: #6b7280;"><?php echo htmlspecialchars($job['location']); ?></span>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #374151;">Job Type:</strong><br>
                        <span style="color: #6b7280;"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['type']))); ?></span>
                    </div>
                    <?php if ($job['salary']): ?>
                        <div style="margin-bottom: 1rem;">
                            <strong style="color: #374151;">Salary:</strong><br>
                            <span style="color: #6b7280;">$<?php echo htmlspecialchars(number_format($job['salary'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                // Get similar jobs
                $query = "SELECT j.*, u.name as company_name FROM jobs j 
                          JOIN users u ON j.employer_id = u.id 
                          WHERE j.id != ? AND (j.type = ? OR j.location = ?) 
                          ORDER BY j.created_at DESC LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->execute([$job_id, $job['type'], $job['location']]);
                $similar_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (!empty($similar_jobs)): ?>
                    <div class="card">
                        <h3 style="color: #1f2937; margin-bottom: 1rem;">Similar Jobs</h3>
                        <?php foreach ($similar_jobs as $similar_job): ?>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0;">
                                <h4 style="margin-bottom: 0.5rem;">
                                    <a href="job-details.php?id=<?php echo $similar_job['id']; ?>" 
                                       style="color: #2563eb; text-decoration: none; font-size: 1rem;">
                                        <?php echo htmlspecialchars($similar_job['title']); ?>
                                    </a>
                                </h4>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($similar_job['company_name']); ?>
                                </p>
                                <div style="display: flex; gap: 0.5rem;">
                                    <span class="job-tag" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $similar_job['type']))); ?>
                                    </span>
                                    <span class="job-tag" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($similar_job['location']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php';?>