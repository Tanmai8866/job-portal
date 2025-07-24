<?php
require_once 'config/session.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send an email here
        // For this demo, we'll just show a success message
        $success = 'Thank you for your message! We will get back to you soon.';
        
        // Clear form data
        $_POST = [];
    }
}

$page_title = 'Contact Us - Job Board Portal';
include 'includes/header.php';
?>

<main>
    <div class="container" style="padding: 2rem 0;">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="text-align: center; margin-bottom: 3rem; color: #1f2937;">Contact Us</h1>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
                <div class="card">
                    <h3 style="color: #2563eb; margin-bottom: 1rem;">Get in Touch</h3>
                    <p style="color: #6b7280; margin-bottom: 2rem;">
                        Have questions about our platform? Need help with your account? We're here to help!
                    </p>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Email</h4>
                        <p style="color: #6b7280;">support@jobboard.com</p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Phone</h4>
                        <p style="color: #6b7280;">+1 (555) 123-4567</p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Office Hours</h4>
                        <p style="color: #6b7280;">Monday - Friday: 9:00 AM - 6:00 PM EST</p>
                    </div>
                </div>

                <div class="card">
                    <h3 style="color: #2563eb; margin-bottom: 1rem;">Send us a Message</h3>
                    
                    <?php if ($error): ?>
                        <div class="error-message" style="margin-bottom: 1rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success-message" style="margin-bottom: 1rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Select a subject</option>
                                <option value="general" <?php echo ($_POST['subject'] ?? '') === 'general' ? 'selected' : ''; ?>>
                                    General Inquiry
                                </option>
                                <option value="technical" <?php echo ($_POST['subject'] ?? '') === 'technical' ? 'selected' : ''; ?>>
                                    Technical Support
                                </option>
                                <option value="account" <?php echo ($_POST['subject'] ?? '') === 'account' ? 'selected' : ''; ?>>
                                    Account Issues
                                </option>
                                <option value="feedback" <?php echo ($_POST['subject'] ?? '') === 'feedback' ? 'selected' : ''; ?>>
                                    Feedback
                                </option>
                                <option value="partnership" <?php echo ($_POST['subject'] ?? '') === 'partnership' ? 'selected' : ''; ?>>
                                    Partnership Opportunities
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" required rows="6" 
                                      placeholder="Please describe your inquiry in detail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card">
                <h3 style="color: #2563eb; margin-bottom: 2rem;">Frequently Asked Questions</h3>
                
                <div style="display: grid; gap: 2rem;">
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">How do I create an account?</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Click on the "Register" button in the top navigation and choose whether you're a job seeker or employer. 
                            Fill out the required information and you'll be ready to start using the platform.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Is it free to post jobs?</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Yes! Employers can post unlimited job openings for free. We believe in connecting great talent 
                            with great opportunities without barriers.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">How do I apply for jobs?</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Create a job seeker account, browse available positions, and click "Apply" on jobs that interest you. 
                            You can optionally include a link to your resume or portfolio.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 0.5rem;">Can I edit or delete my job postings?</h4>
                        <p style="color: #6b7280; line-height: 1.6;">
                            Yes! Employers can manage their job postings from their dashboard. You can view applications, 
                            update job details, or remove postings that are no longer active.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>