-- Job Board Portal Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS job_board;
USE job_board;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('job_seeker', 'employer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    type ENUM('full_time', 'part_time', 'contract', 'internship') NOT NULL,
    salary INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    seeker_id INT NOT NULL,
    resume_link VARCHAR(500) NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, seeker_id)
);

-- Insert sample data
INSERT INTO users (name, email, password, role) VALUES
('John Employer', 'employer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer'),
('Jane Seeker', 'seeker@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker'),
('Tech Corp', 'hr@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer'),
('Alice Developer', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker');

INSERT INTO jobs (employer_id, title, description, location, type, salary) VALUES
(1, 'Senior Software Developer', 'We are looking for an experienced software developer to join our team. You will be responsible for developing and maintaining web applications using modern technologies.\n\nRequirements:\n- 5+ years of experience in web development\n- Proficiency in JavaScript, PHP, and MySQL\n- Experience with modern frameworks\n- Strong problem-solving skills\n- Excellent communication skills\n\nWe offer competitive salary, health benefits, and flexible working arrangements.', 'New York, NY', 'full_time', 95000),
(3, 'Frontend Developer', 'Join our dynamic team as a Frontend Developer! You will work on creating beautiful and responsive user interfaces for our web applications.\n\nWhat you will do:\n- Develop responsive web applications\n- Collaborate with designers and backend developers\n- Optimize applications for maximum speed and scalability\n- Write clean, maintainable code\n\nRequirements:\n- 3+ years of frontend development experience\n- Expert knowledge of HTML, CSS, and JavaScript\n- Experience with React or Vue.js\n- Understanding of responsive design principles', 'San Francisco, CA', 'full_time', 85000),
(1, 'Marketing Intern', 'Great opportunity for students or recent graduates to gain hands-on experience in digital marketing.\n\nYou will:\n- Assist with social media management\n- Help create marketing content\n- Support email marketing campaigns\n- Analyze marketing metrics\n- Learn from experienced marketing professionals\n\nPerfect for:\n- Marketing students or recent graduates\n- Someone passionate about digital marketing\n- Detail-oriented individuals\n- Creative thinkers', 'Remote', 'internship', 25000),
(3, 'Data Analyst', 'We are seeking a detail-oriented Data Analyst to join our growing analytics team.\n\nResponsibilities:\n- Analyze large datasets to identify trends and insights\n- Create reports and dashboards\n- Work with stakeholders to understand data requirements\n- Ensure data quality and accuracy\n- Present findings to management\n\nRequirements:\n- Bachelor\'s degree in Statistics, Mathematics, or related field\n- 2+ years of experience in data analysis\n- Proficiency in SQL and Excel\n- Experience with data visualization tools\n- Strong analytical and problem-solving skills', 'Chicago, IL', 'full_time', 70000),
(1, 'Part-time Customer Support', 'We need a friendly and helpful customer support representative to assist our customers.\n\nDuties:\n- Respond to customer inquiries via email and chat\n- Resolve customer issues promptly and professionally\n- Maintain customer records\n- Escalate complex issues to senior staff\n- Provide product information and guidance\n\nIdeal candidate:\n- Excellent communication skills\n- Patient and empathetic\n- Previous customer service experience preferred\n- Available to work flexible hours\n- Comfortable with technology', 'Remote', 'part_time', 35000);

INSERT INTO applications (job_id, seeker_id, resume_link, status) VALUES
(1, 2, 'https://example.com/jane-resume.pdf', 'pending'),
(2, 4, 'https://example.com/alice-portfolio.com', 'accepted'),
(3, 2, NULL, 'pending'),
(4, 4, 'https://example.com/alice-resume.pdf', 'rejected');