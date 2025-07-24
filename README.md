# Job Board Portal

A complete job board web application built with vanilla HTML, CSS, JavaScript, PHP, and MySQL. This platform allows job seekers to search and apply for jobs, while employers can post jobs and manage applications.

## Features

### For Job Seekers
- User registration and authentication
- Browse and search job listings
- Filter jobs by type and location
- Apply for jobs with optional resume links
- Track application status
- Personal dashboard with application history

### For Employers
- Post unlimited job openings
- Manage job postings (edit/delete)
- View and manage job applications
- Update application status (pending/accepted/rejected)
- Analytics dashboard with application statistics

### General Features
- Responsive design for all devices
- Clean, modern user interface
- Secure authentication with password hashing
- Search functionality with filters
- Pagination for job listings
- Contact form for user inquiries

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Styling**: Custom CSS with Flexbox/Grid
- **Security**: PDO prepared statements, password hashing

## Installation

1. **Clone or download the project files**

2. **Set up your web server**
   - Ensure PHP 7.4+ and MySQL 5.7+ are installed
   - Place files in your web server directory (e.g., `htdocs` for XAMPP)

3. **Create the database**
   - Import the `database.sql` file into your MySQL server
   - This will create the database and sample data

4. **Configure database connection**
   - Edit `config/database.php`
   - Update the database credentials:
     ```php
     private $host = 'localhost';
     private $db_name = 'job_board';
     private $username = 'your_username';
     private $password = 'your_password';
     ```

5. **Access the application**
   - Open your web browser and navigate to your local server
   - Example: `http://localhost/job-board-portal`

## Default Login Credentials

The sample data includes these test accounts:

**Employer Account:**
- Email: `employer@example.com`
- Password: `password`

**Job Seeker Account:**
- Email: `seeker@example.com`
- Password: `password`

## File Structure

```
job-board-portal/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── database.php
│   └── session.php
├── includes/
│   ├── header.php
│   └── footer.php
├── index.php
├── register.php
├── login.php
├── logout.php
├── dashboard.php
├── jobs.php
├── job-details.php
├── post-job.php
├── my-jobs.php
├── job-applications.php
├── my-applications.php
├── contact.php
├── database.sql
└── README.md
```

## Key Features Explained

### Authentication System
- Secure user registration with role selection
- Password hashing using PHP's `password_hash()`
- Session management for user state
- Role-based access control

### Job Management
- Employers can post, edit, and delete jobs
- Rich job descriptions with multiple fields
- Job categorization by type and location
- Salary information (optional)

### Application System
- One-click job applications
- Optional resume/portfolio links
- Application status tracking
- Employer application management

### Search & Filtering
- Real-time search functionality
- Filter by job type and location
- Pagination for large result sets
- Responsive grid layout

## Security Features

- PDO prepared statements prevent SQL injection
- Password hashing for secure authentication
- Session-based authentication
- Input validation and sanitization
- CSRF protection through form validation

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Customization

### Styling
- Edit `assets/css/style.css` to customize the appearance
- CSS variables are used for consistent theming
- Responsive breakpoints can be adjusted

### Database Schema
- Modify `database.sql` to add new fields
- Update PHP files accordingly for new functionality

### Features
- Add email notifications by implementing PHP mail functions
- Integrate file upload for resume attachments
- Add advanced search filters
- Implement job alerts and notifications

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists and is accessible

2. **Session Issues**
   - Ensure PHP sessions are enabled
   - Check file permissions for session storage
   - Clear browser cookies if needed

3. **Styling Issues**
   - Check CSS file path in header
   - Ensure web server serves CSS files correctly
   - Clear browser cache

## Future Enhancements

- Email notifications for applications
- File upload for resumes
- Advanced search with salary ranges
- Company profiles and ratings
- Job alerts and saved searches
- Admin panel for platform management
- API endpoints for mobile app integration

## License

This project is open source and available under the MIT License.

## Support

For questions or issues, please use the contact form in the application or refer to the FAQ section.