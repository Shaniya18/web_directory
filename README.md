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
