<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();

if (!isEmployer()) {
    header('Location: dashboard.php');
    exit();
}

$job_id = intval($_GET['job_id'] ?? 0);
if (!$job_id) {
    header('Location: my-jobs.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Verify job belongs to current user
$query = "SELECT title FROM jobs WHERE id = ? AND employer_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$job_id, getUserId()]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: my-jobs.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['pending', 'accepted', 'rejected'])) {
        $query = "UPDATE applications SET status = ? WHERE id = ? AND job_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$new_status, $application_id, $job_id]);
        $message = 'Application status updated successfully.';
    }
}

// Get applications for this job
$query = "SELECT a.*, u.name, u.email FROM applications a 
          JOIN users u ON a.seeker_id = u.id 
          WHERE a.job_id = ? 
          ORDER BY a.applied_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Job Applications - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <div style="margin-bottom: 2rem;">
            <a href="my-jobs.php" style="color: #2563eb; text-decoration: none;">← Back to My Jobs</a>
        </div>

        <div style="margin-bottom: 2rem;">
            <h1 style="color: #1f2937; margin-bottom: 0.5rem;">Applications for</h1>
            <h2 style="color: #2563eb; font-size: 1.5rem;"><?php echo htmlspecialchars($job['title']); ?></h2>
        </div>

        <?php if (isset($message)): ?>
            <div class="success-message" style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($applications)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No applications yet</h3>
                <p style="color: #9ca3af;">Applications will appear here once job seekers start applying for this position.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Resume/Portfolio</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($app['name']); ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" 
                                       style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($app['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($app['resume_link']): ?>
                                        <a href="<?php echo htmlspecialchars($app['resume_link']); ?>" 
                                           target="_blank" style="color: #2563eb; text-decoration: none;">
                                            View Resume →
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                style="padding: 0.25rem 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 0.875rem;">
                                            <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>
                                            <option value="accepted" <?php echo $app['status'] === 'accepted' ? 'selected' : ''; ?>>
                                                Accepted
                                            </option>
                                            <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>
                                                Rejected
                                            </option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #f9fafb; border-radius: 8px;">
                <h3 style="color: #374151; margin-bottom: 1rem;">Application Management</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong style="color: #2563eb;">Total Applications:</strong>
                        <span><?php echo count($applications); ?></span>
                    </div>
                    <div>
                        <strong style="color: #f59e0b;">Pending:</strong>
                        <span><?php echo count(array_filter($applications, fn($app) => $app['status'] === 'pending')); ?></span>
                    </div>
                    <div>
                        <strong style="color: #10b981;">Accepted:</strong>
                        <span><?php echo count(array_filter($applications, fn($app) => $app['status'] === 'accepted')); ?></span>
                    </div>
                    <div>
                        <strong style="color: #ef4444;">Rejected:</strong>
                        <span><?php echo count(array_filter($applications, fn($app) => $app['status'] === 'rejected')); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>