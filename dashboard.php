<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

if (isEmployer()) {
    // Employer Dashboard
    $user_id = getUserId();
    
    // Get stats
    $query = "SELECT COUNT(*) as total_jobs FROM jobs WHERE employer_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $total_jobs = $stmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    $query = "SELECT COUNT(*) as total_applications FROM applications a 
              JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $total_applications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];

    // Get recent jobs
    $query = "SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $recent_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent applications
    $query = "SELECT a.*, j.title as job_title, u.name as applicant_name, u.email as applicant_email 
              FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              JOIN users u ON a.seeker_id = u.id 
              WHERE j.employer_id = ? 
              ORDER BY a.applied_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $recent_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Job Seeker Dashboard
    $user_id = getUserId();
    
    // Get stats
    $query = "SELECT COUNT(*) as total_applications FROM applications WHERE seeker_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $total_applications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];

    $query = "SELECT COUNT(*) as pending_applications FROM applications WHERE seeker_id = ? AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $pending_applications = $stmt->fetch(PDO::FETCH_ASSOC)['pending_applications'];

    // Get recent applications
    $query = "SELECT a.*, j.title as job_title, u.name as company_name 
              FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              JOIN users u ON j.employer_id = u.id 
              WHERE a.seeker_id = ? 
              ORDER BY a.applied_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $recent_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recommended jobs (simple algorithm based on recent applications)
    $query = "SELECT DISTINCT j.*, u.name as company_name FROM jobs j 
              JOIN users u ON j.employer_id = u.id 
              WHERE j.id NOT IN (SELECT job_id FROM applications WHERE seeker_id = ?) 
              ORDER BY j.created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $recommended_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Dashboard - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <h1 style="margin-bottom: 2rem; color: #1f2937;">
            Welcome back, <?php echo htmlspecialchars(getUserName()); ?>!
        </h1>

        <?php if (isEmployer()): ?>
            <!-- Employer Dashboard -->
            <div class="dashboard-grid">
                <div class="stats-card card">
                    <div class="stats-number"><?php echo $total_jobs; ?></div>
                    <div class="stats-label">Total Jobs Posted</div>
                </div>
                <div class="stats-card card">
                    <div class="stats-number"><?php echo $total_applications; ?></div>
                    <div class="stats-label">Total Applications</div>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 1rem; color: #2563eb;">Quick Actions</h3>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="post-job.php" class="btn">Post New Job</a>
                        <a href="my-jobs.php" class="btn btn-secondary">Manage Jobs</a>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; color: #1f2937;">Recent Job Postings</h3>
                    <?php if (empty($recent_jobs)): ?>
                        <p style="color: #6b7280;">No jobs posted yet. <a href="post-job.php" style="color: #2563eb;">Post your first job</a></p>
                    <?php else: ?>
                        <?php foreach ($recent_jobs as $job): ?>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0;">
                                <h4 style="margin-bottom: 0.5rem;">
                                    <a href="job-details.php?id=<?php echo $job['id']; ?>" style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </a>
                                </h4>
                                <p style="color: #6b7280; font-size: 0.875rem;">
                                    Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top: 1rem;">
                            <a href="my-jobs.php" style="color: #2563eb; font-weight: 600;">View all jobs →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; color: #1f2937;">Recent Applications</h3>
                    <?php if (empty($recent_applications)): ?>
                        <p style="color: #6b7280;">No applications yet.</p>
                    <?php else: ?>
                        <?php foreach ($recent_applications as $app): ?>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0;">
                                <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($app['applicant_name']); ?></h4>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    Applied for: <?php echo htmlspecialchars($app['job_title']); ?>
                                </p>
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top: 1rem;">
                            <a href="job-applications.php" style="color: #2563eb; font-weight: 600;">View all applications →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Job Seeker Dashboard -->
            <div class="dashboard-grid">
                <div class="stats-card card">
                    <div class="stats-number"><?php echo $total_applications; ?></div>
                    <div class="stats-label">Applications Submitted</div>
                </div>
                <div class="stats-card card">
                    <div class="stats-number"><?php echo $pending_applications; ?></div>
                    <div class="stats-label">Pending Applications</div>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 1rem; color: #2563eb;">Quick Actions</h3>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="jobs.php" class="btn">Browse Jobs</a>
                        <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; color: #1f2937;">My Applications</h3>
                    <?php if (empty($recent_applications)): ?>
                        <p style="color: #6b7280;">No applications yet. <a href="jobs.php" style="color: #2563eb;">Browse jobs</a> to get started!</p>
                    <?php else: ?>
                        <?php foreach ($recent_applications as $app): ?>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0;">
                                <h4 style="margin-bottom: 0.5rem;">
                                    <a href="job-details.php?id=<?php echo $app['job_id']; ?>" style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($app['job_title']); ?>
                                    </a>
                                </h4>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($app['company_name']); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                    <span style="color: #6b7280; font-size: 0.875rem;">
                                        <?php echo date('M j, Y', strtotime($app['applied_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top: 1rem;">
                            <a href="my-applications.php" style="color: #2563eb; font-weight: 600;">View all applications →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; color: #1f2937;">Recommended Jobs</h3>
                    <?php if (empty($recommended_jobs)): ?>
                        <p style="color: #6b7280;">No recommendations available.</p>
                    <?php else: ?>
                        <?php foreach ($recommended_jobs as $job): ?>
                            <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0;">
                                <h4 style="margin-bottom: 0.5rem;">
                                    <a href="job-details.php?id=<?php echo $job['id']; ?>" style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </a>
                                </h4>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($job['company_name']); ?>
                                </p>
                                <div style="display: flex; gap: 0.5rem;">
                                    <span class="job-tag"><?php echo htmlspecialchars($job['type']); ?></span>
                                    <span class="job-tag"><?php echo htmlspecialchars($job['location']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top: 1rem;">
                            <a href="jobs.php" style="color: #2563eb; font-weight: 600;">View all jobs →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>