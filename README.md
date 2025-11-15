
Fiji Web Directory
A secure, feature-rich web directory application built with PHP, featuring business listings, user management, and a protected admin panel.

🚀 Features
👥 User Features
User Registration & Login – Secure authentication with strong password policies

Business Listings – Browse, search, and submit new listings

Reviews & Ratings – Share feedback on listed businesses

Password Reset – Email-based reset using PHPMailer + Gmail SMTP

Contact System – Send messages to directory administrators

🛡️ Admin/Staff Panel
Separate Staff Portal – Isolated admin area (staff.php)

Dashboard – Overview of listings, users, and reviews

Content Management – Approve, edit, or remove listings and reviews

User Management – Monitor and manage user accounts

Message Center – Handle user inquiries and contact forms

🔒 Security Highlights
SQL Injection Prevention – Parameterized queries and prepared statements

XSS Protection – Output encoding and input sanitization

CSRF Protection – Token-based form validation

Secure Sessions – HttpOnly cookies, SameSite policies, session timeouts

File Upload Safety – Type verification, size limits, random filenames

Error Handling – No sensitive data leakage; errors logged internally

Password Policies – 12+ characters with complexity requirements

Account Lockout – Rate limiting after failed login attempts

🛠️ Tech Stack
Backend: PHP 8.2+, MySQL

Frontend: HTML, CSS, JavaScript

Libraries: PHPMailer (for email)

Security: Custom session management, input validation, secure headers

Tools: XAMPP, Composer, Git

📦 Installation
Clone the repository

bash
git clone https://github.com/Shaniya18/web_directory.git
cd web_directory
Install dependencies

bash
composer install
Set up the database

Create a MySQL database named web_directory

Update database credentials in config.php

Configure environment

For email (password resets), add your Gmail and App Password in config.php

Ensure proper file permissions for logs/ directory

Run locally

Start Apache & MySQL via XAMPP

Visit: http://localhost/web_directory

Staff panel: http://localhost/web_directory/staff.php

📁 Project Structure
text
web_directory/
├── controllers/              # Request handlers
│   ├── AuthController.php           # User authentication
│   ├── UserController.php           # User management
│   ├── StaffAuthController.php      # Staff authentication
│   ├── StaffDashboardController.php # Admin panel
│   ├── ListingController.php        # Business listings
│   ├── CategoryController.php       # Category management
│   ├── SearchController.php         # Search functionality
│   └── ContactController.php        # Contact forms
├── models/                  # Database models
│   ├── User.php                    # User data
│   ├── Listing.php                 # Business listings
│   ├── Category.php                # Categories
│   ├── Review.php                  # User reviews
│   ├── Contact.php                 # Contact messages
│   └── PasswordReset.php           # Password reset tokens
├── views/                   # Templates
│   ├── auth/                       # Authentication pages
│   ├── user/                       # User profile pages
│   ├── staff/                      # Admin panel pages
│   ├── listing/                    # Listing pages
│   ├── category/                   # Category pages
│   ├── search/                     # Search results
│   ├── partials/                   # Reusable components
│   └── errors/                     # Error pages
├── includes/               # Core utilities
│   ├── Database.php               # Database connection
│   ├── SessionManager.php         # Session handling
│   ├── Router.php                 # URL routing
│   ├── EmailService.php           # PHPMailer integration
│   └── helpers.php                # Helper functions
├── public/                 # Frontend assets
│   ├── style.css                  # Main stylesheet
│   └── script.js                  # Client-side scripts
├── logs/                   # Application logs
│   ├── php_errors.log             # PHP errors
│   ├── security.log               # Security events
│   └── error.log                  # General errors
├── vendor/                 # Composer dependencies
├── config.php              # Application configuration
├── index.php               # Main entry point
├── staff.php               # Staff/admin entry point
└── .htaccess               # Apache configuration
🔐 Security Implementation
This project was built with a security-first approach, including:

Authentication & Session Security
Separate user and staff authentication systems

Strong password policies (12+ characters with complexity)

Account lockout after 5 failed attempts (15-minute cooldown)

Password history tracking (prevents reuse of last 5 passwords)

Secure session management with HttpOnly cookies and SameSite policies

Input Validation & Data Protection
Comprehensive input sanitization across all forms

SQL injection prevention through prepared statements

XSS protection via output encoding

CSRF tokens on all state-changing forms

Secure file upload validation with type checking

Application Architecture
Physical separation between user and staff areas

Independent authentication systems with no privilege escalation

Comprehensive error handling without information leakage

Security headers (X-Frame-Options, X-XSS-Protection, etc.)

📊 Key Controllers & Models
Core Controllers
AuthController - Handles user login, registration, password reset

UserController - Manages user profiles and account settings

StaffAuthController - Secure staff authentication with enhanced protections

ListingController - Business listing creation, editing, and display

SearchController - Advanced search functionality with security filters

Data Models
User - User accounts with secure password hashing

Listing - Business listings with approval workflow

Category - Business categorization system

Review - User reviews and ratings with moderation

📧 Email System
The application uses PHPMailer for secure email delivery:

Password reset functionality with secure tokens

HTML and plain-text email templates

Gmail SMTP integration with App Passwords

Comprehensive error logging for email delivery issues

🎯 Usage
For Users
Register an account with secure credentials

Browse or search business listings by category

Submit new business listings for review

Write reviews and rate existing businesses

Use password reset if needed

For Staff/Admins
Access staff panel via staff.php

Manage user accounts and permissions

Review and approve business listings

Monitor user reviews and contact messages

View system analytics and reports

👩‍💻 Author
Shaniya Saloni Sen
Software Developer | Security-Focused Web Applications

Built with a focus on secure coding practices and real-world web application development following OWASP guidelines.
