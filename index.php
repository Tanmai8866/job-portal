<?php
require_once 'config/session.php';
$page_title = 'Home - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container">
            <h1>Find Your Dream Job</h1>
            <p>Connect with top employers and discover opportunities that match your skills and aspirations.</p>
            <div style="margin-top: 2rem;">
                <a href="jobs.php" class="btn">Browse Jobs</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-secondary" style="margin-left: 1rem;">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="dashboard-grid">
                <div class="card">
                    <h3 style="color: #2563eb; margin-bottom: 1rem;">For Job Seekers</h3>
                    <p style="margin-bottom: 1.5rem;">Discover thousands of job opportunities from top companies. Create your profile and start applying today.</p>
                    <ul style="list-style: none; margin-bottom: 2rem;">
                        <li style="margin-bottom: 0.5rem;">✓ Browse jobs by category and location</li>
                        <li style="margin-bottom: 0.5rem;">✓ Apply with one click</li>
                        <li style="margin-bottom: 0.5rem;">✓ Track your applications</li>
                        <li style="margin-bottom: 0.5rem;">✓ Get job alerts</li>
                    </ul>
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php?role=job_seeker" class="btn">Join as Job Seeker</a>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 style="color: #2563eb; margin-bottom: 1rem;">For Employers</h3>
                    <p style="margin-bottom: 1.5rem;">Find the perfect candidates for your company. Post jobs and manage applications efficiently.</p>
                    <ul style="list-style: none; margin-bottom: 2rem;">
                        <li style="margin-bottom: 0.5rem;">✓ Post unlimited job openings</li>
                        <li style="margin-bottom: 0.5rem;">✓ Manage applications easily</li>
                        <li style="margin-bottom: 0.5rem;">✓ Search candidate profiles</li>
                        <li style="margin-bottom: 0.5rem;">✓ Analytics and insights</li>
                    </ul>
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php?role=employer" class="btn">Join as Employer</a>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 style="color: #2563eb; margin-bottom: 1rem;">Why Choose Us?</h3>
                    <p style="margin-bottom: 1.5rem;">We're committed to connecting the right people with the right opportunities.</p>
                    <ul style="list-style: none; margin-bottom: 2rem;">
                        <li style="margin-bottom: 0.5rem;">✓ Trusted by 1000+ companies</li>
                        <li style="margin-bottom: 0.5rem;">✓ Advanced matching algorithm</li>
                        <li style="margin-bottom: 0.5rem;">✓ Secure and private</li>
                        <li style="margin-bottom: 0.5rem;">✓ 24/7 customer support</li>
                    </ul>
                    <a href="contact.php" class="btn">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <?php if (isLoggedIn()): ?>
    <section style="background: white; padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; color: #1f2937;">Recent Job Postings</h2>
            
            <?php
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT j.*, u.name as company_name FROM jobs j 
                     JOIN users u ON j.employer_id = u.id 
                     ORDER BY j.created_at DESC LIMIT 6";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    <div class="job-meta">
                        <span class="job-tag"><?php echo htmlspecialchars($job['type']); ?></span>
                        <span class="job-tag"><?php echo htmlspecialchars($job['location']); ?></span>
                        <?php if ($job['salary']): ?>
                            <span class="job-tag">$<?php echo htmlspecialchars($job['salary']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="job-description">
                        <?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?>
                    </p>
                    <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="jobs.php" class="btn">View All Jobs</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>