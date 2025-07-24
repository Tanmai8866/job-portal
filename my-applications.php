<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

if (!isJobSeeker()) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user's applications
$query = "SELECT a.*, j.title as job_title, j.location, j.type, j.salary, u.name as company_name 
          FROM applications a 
          JOIN jobs j ON a.job_id = j.id 
          JOIN users u ON j.employer_id = u.id 
          WHERE a.seeker_id = ? 
          ORDER BY a.applied_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'My Applications - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <h1 style="margin-bottom: 2rem; color: #1f2937;">My Job Applications</h1>

        <?php if (empty($applications)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No applications yet</h3>
                <p style="color: #9ca3af; margin-bottom: 2rem;">Start applying for jobs to see your applications here.</p>
                <a href="jobs.php" class="btn">Browse Jobs</a>
            </div>
        <?php else: ?>
            <!-- Summary Cards -->
            <div class="dashboard-grid" style="margin-bottom: 3rem;">
                <div class="stats-card card">
                    <div class="stats-number"><?php echo count($applications); ?></div>
                    <div class="stats-label">Total Applications</div>
                </div>
                <div class="stats-card card">
                    <div class="stats-number">
                        <?php echo count(array_filter($applications, fn($app) => $app['status'] === 'pending')); ?>
                    </div>
                    <div class="stats-label">Pending</div>
                </div>
                <div class="stats-card card">
                    <div class="stats-number">
                        <?php echo count(array_filter($applications, fn($app) => $app['status'] === 'accepted')); ?>
                    </div>
                    <div class="stats-label">Accepted</div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="jobs-grid">
                <?php foreach ($applications as $app): ?>
                    <div class="job-card">
                        <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 class="job-title">
                                    <a href="job-details.php?id=<?php echo $app['job_id']; ?>" 
                                       style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($app['job_title']); ?>
                                    </a>
                                </h3>
                                <p class="job-company"><?php echo htmlspecialchars($app['company_name']); ?></p>
                            </div>
                            <span class="status-badge status-<?php echo $app['status']; ?>">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </div>

                        <div class="job-meta">
                            <span class="job-tag"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $app['type']))); ?></span>
                            <span class="job-tag"><?php echo htmlspecialchars($app['location']); ?></span>
                            <?php if ($app['salary']): ?>
                                <span class="job-tag">$<?php echo htmlspecialchars(number_format($app['salary'])); ?></span>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; color: #6b7280;">
                                <span>Applied: <?php echo date('M j, Y', strtotime($app['applied_at'])); ?></span>
                                <?php if ($app['resume_link']): ?>
                                    <a href="<?php echo htmlspecialchars($app['resume_link']); ?>" 
                                       target="_blank" style="color: #2563eb; text-decoration: none;">
                                        View Resume →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Application Tips -->
            <div class="card" style="margin-top: 3rem;">
                <h3 style="color: #2563eb; margin-bottom: 1rem;">Application Tips</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Follow Up</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            If you haven't heard back within a week, consider sending a polite follow-up email to the employer.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Keep Applying</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Don't put all your eggs in one basket. Continue applying to multiple positions that match your skills.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Update Your Profile</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Keep your resume and portfolio links updated to make the best impression on employers.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>