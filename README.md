# Fiji Web Directory

A secure, feature-rich web directory application built with PHP, featuring business listings, user management, and a protected admin panel.

---

## 🚀 Features

### 👥 User Features
- **User Registration & Login** – Secure authentication with strong password policies
- **Business Listings** – Browse, search, and submit new listings
- **Reviews & Ratings** – Share feedback on listed businesses
- **Password Reset** – Email-based reset using PHPMailer + Gmail SMTP
- **Contact System** – Send messages to directory administrators

### 🛡️ Admin/Staff Panel
- **Separate Staff Portal** – Isolated admin area (`staff.php`)
- **Dashboard** – Overview of listings, users, and reviews
- **Content Management** – Approve, edit, or remove listings and reviews
- **User Management** – Monitor and manage user accounts
- **Message Center** – Handle user inquiries and contact forms

### 🔒 Security Highlights
- **SQL Injection Prevention** – Parameterized queries and prepared statements
- **XSS Protection** – Output encoding and input sanitization
- **CSRF Protection** – Token-based form validation
- **Secure Sessions** – HttpOnly cookies, SameSite policies, session timeouts
- **File Upload Safety** – Type verification, size limits, random filenames
- **Error Handling** – No sensitive data leakage; errors logged internally
- **Password Policies** – 12+ characters with complexity requirements
- **Account Lockout** – Rate limiting after failed login attempts

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+, MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Libraries**: PHPMailer (for email)
- **Security**: Custom session management, input validation, secure headers
- **Tools**: XAMPP, Composer, Git

---

## 📦 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Shaniya18/web_directory.git
   cd web_directory

2. **Install dependencies**
   ```bash
   composer install

3. **Set up the database**
   
   Create a MySQL database named web_directory
   Update database credentials in config.php

4. **Configure environment**

  For email (password resets), add your Gmail and App Password in config.php 
  Ensure proper file permissions for logs/ directory

5. **Run locally**

   Start Apache & MySQL via XAMPP
   Visit: http://localhost/web_directory
   Staff panel: http://localhost/web_directory/staff.php

## 🛡️ Security Hardening
📋 View Full Security Hardening Report

### Security Features Implemented
- **Authentication Security** – Strong password policies, account lockout, password history tracking
- **SQL Injection Prevention** – Parameterized queries and input validation
- **XSS Protection** – Output encoding across all user outputs
- **CSRF Protection** – Token validation on all state-changing forms
- **Session Security** – Enhanced cookie settings with HttpOnly and SameSite
- **File Upload Security** – Type verification, size limits, random filenames
- **Error Handling** – Secure error disclosure without information leakage

## 📁 Project Structure
 ```bash

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
 ```

## 🔐 Security Implementation

### Authentication & Session Security
- **Separate Authentication Systems** – User and staff areas completely isolated
- **Strong Password Policies** – 12+ characters with complexity requirements
- **Account Lockout** – 5 failed attempts trigger 15-minute lockout
- **Password History** – Prevents reuse of last 5 passwords
- **Secure Sessions** – HttpOnly cookies, SameSite policies, timeout management

### Input Validation & Data Protection
- **Comprehensive Input Sanitization** – Across all forms and user inputs
- **SQL Injection Prevention** – Prepared statements and parameterized queries
- **XSS Protection** – Output encoding for all user-generated content
- **CSRF Protection** – Token validation on all state-changing forms
- **File Upload Security** – Type verification, size limits, random filenames

### Application Architecture
- **Physical Separation** – Complete isolation between user and staff areas
- **No Privilege Escalation** – Independent authentication systems
- **Secure Error Handling** – No sensitive information leakage
- **Security Headers** – X-Frame-Options, X-XSS-Protection, etc.

---

## 📊 Core Components

### Key Controllers
- **AuthController** – Handles user login, registration, password reset
- **UserController** – Manages user profiles and account settings
- **StaffAuthController** – Secure staff authentication with enhanced protections
- **ListingController** – Business listing creation, editing, and display
- **SearchController** – Advanced search functionality with security filters

### Data Models
- **User** – User accounts with secure password hashing
- **Listing** – Business listings with approval workflow
- **Category** – Business categorization system
- **Review** – User reviews and ratings with moderation
## 📧 Email System

### PHPMailer Integration
- **Password Reset Functionality** – Secure tokens with expiration
- **HTML & Plain-Text Templates** – Professional email formatting
- **Gmail SMTP Integration** – Secure email delivery with App Passwords
- **Error Logging** – Comprehensive tracking for email delivery issues

---

## 🎯 Usage Guide

### For Regular Users
1. **Register Account** – Create secure credentials with strong password
2. **Browse Listings** – Search and filter business listings by category
3. **Submit Listings** – Add new businesses for admin review
4. **Write Reviews** – Share feedback and ratings
5. **Password Reset** – Use email-based recovery if needed

### For Staff/Administrators
1. **Access Staff Panel** – Login via `staff.php`
2. **Manage Content** – Approve, edit, or remove business listings
3. **User Management** – Monitor and manage user accounts
4. **Review System** – Moderate user reviews and ratings
5. **Message Center** – Handle user inquiries and contact forms

---

## 🐛 Troubleshooting

### Common Issues & Solutions

**Database Connection Issues**
- Verify MySQL service is running in XAMPP
- Check database credentials in `config.php`
- Ensure `web_directory` database exists

**Email Delivery Problems**
- Confirm Gmail App Password is correct
- Verify SMTP settings in `config.php`
- Check internet connection for SMTP access

**Session & Login Issues**
- Clear browser cookies and cache
- Check `logs/php_errors.log` for specific errors
- Verify session directory permissions

### Development Debugging
Enable debug mode in `config.php` for troubleshooting:
```php
define('SMTP_DEBUG', 2);
ini_set('display_errors', 1);
``` 
👩‍💻 Author
Shaniya Saloni Sen
Software Developer | Security-Focused Web Applications

Built with comprehensive security practices and real-world application development following OWASP guidelines.
