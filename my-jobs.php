<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

if (!isEmployer()) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle job deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_job'])) {
    $job_id = intval($_POST['job_id']);
    
    // Verify job belongs to current user
    $query = "SELECT id FROM jobs WHERE id = ? AND employer_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$job_id, getUserId()]);
    
    if ($stmt->rowCount() > 0) {
        // Delete applications first (foreign key constraint)
        $query = "DELETE FROM applications WHERE job_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$job_id]);
        
        // Delete job
        $query = "DELETE FROM jobs WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$job_id]);
        
        $message = 'Job deleted successfully.';
    }
}

// Get user's jobs with application counts
$query = "SELECT j.*, 
          COUNT(a.id) as application_count,
          COUNT(CASE WHEN a.status = 'pending' THEN 1 END) as pending_count
          FROM jobs j 
          LEFT JOIN applications a ON j.id = a.job_id 
          WHERE j.employer_id = ? 
          GROUP BY j.id 
          ORDER BY j.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([getUserId()]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'My Jobs - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #1f2937;">My Job Postings</h1>
            <a href="post-job.php" class="btn">Post New Job</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="success-message" style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No jobs posted yet</h3>
                <p style="color: #9ca3af; margin-bottom: 2rem;">Start by posting your first job to attract talented candidates.</p>
                <a href="post-job.php" class="btn">Post Your First Job</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Applications</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                        <?php if ($job['salary']): ?>
                                            <br><small style="color: #6b7280;">$<?php echo number_format($job['salary']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="job-tag">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['type']))); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($job['location']); ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo $job['application_count']; ?></strong> total
                                        <?php if ($job['pending_count'] > 0): ?>
                                            <br><small style="color: #f59e0b;"><?php echo $job['pending_count']; ?> pending</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>" 
                                           class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                                            View
                                        </a>
                                        <a href="job-applications.php?job_id=<?php echo $job['id']; ?>" 
                                           class="btn" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                                            Applications (<?php echo $job['application_count']; ?>)
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" name="delete_job" 
                                                    style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; cursor: pointer;">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #f9fafb; border-radius: 8px;">
                <h3 style="color: #374151; margin-bottom: 1rem;">Job Management Tips</h3>
                <ul style="color: #6b7280; line-height: 1.6;">
                    <li>Review applications regularly to maintain candidate engagement</li>
                    <li>Update job descriptions if requirements change</li>
                    <li>Consider closing positions once filled to avoid unnecessary applications</li>
                    <li>Provide feedback to candidates when possible</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>