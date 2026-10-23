# 💼 CareerPulse — Job & Internship Management System

A modern, responsive, and secure web application built using *PHP 8.2 (PDO)* and *MySQL/MariaDB, featuring **Role-Based Access Control (RBAC)* that distinctly separates *Administrator* management capabilities from a *Candidate (Job Seeker)* application portal.

---

## 🌟 Key Features

### 👑 Administrator Role
- *Executive Dashboard:* Live KPI cards for Total Users, Active Jobs, Active Internships, and Candidate Applications (with pending review badge alerts).
- *User Management (Full CRUD):* Add, view, edit (roles, emails, passwords), and safely delete administrators or standard candidates.
- *Job & Internship Management (Full CRUD):* Create, browse, search, filter, edit, and delete vacancies across companies and locations.
- *Candidate Application Management:*
  - View all candidate submissions across all listings.
  - Filter by vacancy and by status (Pending, Reviewed, Accepted, Rejected).
  - Search by applicant name, email, or job title.
  - Detailed candidate modal displaying phone, portfolio/resume links, and pitch statements.
  - One-click application status updating (Pending → Under Review → Accepted / Rejected).

### 👤 Candidate / User Role
- *Candidate Portal Dashboard:* Personalized dashboard showing available jobs, active internships, submitted applications, and accepted offers.
- *Explore Vacancies:* Search by title, company, or location, and filter by position type (Job vs Internship).
- *One-Click Application (apply.php):* Submit applications with direct phone contact, personal statement/pitch, and portfolio/resume web link.
- *Duplicate Prevention:* Unique constraint prevents submitting multiple applications to the same vacancy.
- *My Applications Tracker (my_applications.php):* Track review outcomes in real time (Pending 🟡, Under Review 🔵, Accepted 🟢, Rejected 🔴) with the option to view your submitted note or withdraw pending applications.
- *Candidate Self-Registration (register.php):* Public registration form with immediate auto-login.

---

## 🛠️ Technology Stack

- *Backend:* PHP 8.2 (PDO MySQL with prepared statements)
- *Database:* MySQL 8.0 / MariaDB 10.4
- *Frontend:* HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- *Web Server:* Apache 2.4 (via XAMPP)
- *Security:* 100% Prepared Statements (SQL Injection Protection), htmlspecialchars() (XSS Mitigation), Bcrypt Hashing (password_hash), Session Regeneration (Anti-Fixation)

---

## 📂 Project Structure

```text
├── assets/
│   └── css/
│       └── style.css            # Custom CSS styling
├── config/
│   └── database.php             # Database connection & RBAC helper functions
├── includes/
│   ├── header.php               # HTML head & Bootstrap assets
│   ├── navbar.php               # Dynamic role-aware navigation bar
│   └── footer.php               # HTML footer & scripts
├── jobs/
│   ├── index.php                # Browse, search & filter postings
│   ├── create.php               # Add new job/internship (Admin)
│   ├── edit.php                 # Edit existing listing (Admin)
│   ├── delete.php               # Delete listing confirmation (Admin)
│   └── view.php                 # Detailed view & apply action
├── users/
│   ├── index.php                # View & search users (Admin)
│   ├── create.php               # Add user/admin (Admin)
│   ├── edit.php                 # Edit user details/password (Admin)
│   └── delete.php               # Delete user confirmation (Admin)
├── applications/
│   ├── apply.php                # Submit application form (Candidate)
│   ├── my_applications.php      # Candidate application tracker
│   └── index.php                # Review & update applications (Admin)
├── dashboard.php                # Role-aware dashboard (Admin vs User)
├── login.php                    # Authentication page
├── logout.php                   # Secure session destroyer
├── register.php                 # Public candidate registration
├── database.sql                 # Complete database schema & seed data
├── test_suite.php               # Comprehensive automated test suite (25 tests)
└── README.md                    # Project documentation
