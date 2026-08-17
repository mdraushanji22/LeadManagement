# Lead Management System (LMS)

A complete, professional, and responsive **Lead Management System** built with **Core PHP 8+ and MySQL**. Manage the complete lifecycle of leads from creation to follow-up, conversion, or lost status with full activity history tracking.

---

## Features

- **Authentication System** - Secure PHP session-based login with role-based access control
- **Admin Dashboard** - Full statistics with all leads, follow-ups, and overdue tracking
- **User Dashboard** - Personalized view of assigned leads and follow-ups
- **Lead Management** - Complete CRUD with create, view, edit, soft delete
- **Lead Assignment** - Admin can assign/reassign leads to team members
- **Follow-up Management** - Add, complete, cancel, and reschedule follow-ups
- **Activity Timeline** - Complete audit trail for every lead action
- **Search & Filters** - Search by name/company/mobile/email with status, source, priority, and date range filters
- **Server-side Pagination** - Configurable records per page (10, 25, 50, 100)
- **User Management** - Admin can add, edit, activate/deactivate, and delete users
- **Role-Based Access Control** - Admin and User roles with enforced server-side authorization
- **Responsive Design** - Bootstrap 5 UI that works on desktop, tablet, and mobile
- **Security** - CSRF protection, XSS prevention, SQL injection protection via PDO prepared statements
- **Soft Delete** - Leads are soft-deleted, preserving data integrity

---

## Technology Stack

### Backend
- Core PHP 8+
- MySQL
- PHP PDO (database connection)
- PHP Sessions (authentication)
- `password_hash()` / `password_verify()` (password security)

### Frontend
- HTML5
- CSS3
- Bootstrap 5.3
- Bootstrap Icons
- JavaScript

---

## Requirements

- **PHP** 8.0 or higher
- **MySQL** 5.7+ or MariaDB 10.3+
- **XAMPP** / WAMP / MAMP / LAMP (for local development)
- Web browser (Chrome, Firefox, Edge, Safari)

---

## Installation & Setup

### Step 1: Clone or Download

Place the project folder `lead-management-system` inside your web server's document root:

```
# For XAMPP:
C:\xampp\htdocs\LeadManagement

# For Linux/Mac:
/var/www/html/LeadManagement
```

### Step 2: Start Services

Start Apache and MySQL from your XAMPP/WAMP control panel.

### Step 3: Create Database

Open phpMyAdmin (`http://localhost/phpmyadmin`) and import the SQL file:

1. Click **Import** tab
2. Choose file: `database/lead_management.sql`
3. Click **Go** to execute

Or via MySQL command line:

```sql
mysql -u root -p < database/lead_management.sql
```

### Step 4: Configure Database Connection

Edit `config/database.php` if your MySQL credentials differ:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lead_management');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Step 5: Access the Application

Open your browser and navigate to:

```
http://localhost/LeadManagement
```

You will be redirected to the login page.

---

## Demo Login Credentials

### Admin
- **Email:** admin@example.com
- **Password:** Admin@123

### User
- **Email:** user@example.com
- **Password:** User@123

---

## Project Structure

```
LeadManagement/
├── config/
│   └── database.php              # Database configuration & PDO connection
├── includes/
│   ├── auth.php                   # Authentication & authorization helpers
│   ├── functions.php              # Common utility functions
│   ├── header.php                 # HTML head section
│   ├── sidebar.php                # Sidebar navigation
│   ├── navbar.php                 # Top navigation bar
│   └── footer.php                 # Common footer & scripts
├── admin/
│   ├── dashboard.php              # Admin dashboard with stats
│   ├── users/
│   │   ├── index.php              # List all users
│   │   ├── create.php             # Add new user
│   │   ├── edit.php               # Edit user
│   │   └── delete.php             # Delete user (POST)
│   └── leads/
│       ├── index.php              # List all leads (search, filter, paginate)
│       ├── create.php             # Add new lead
│       ├── edit.php               # Edit lead
│       ├── view.php               # Lead details + follow-ups + timeline
│       ├── delete.php             # Soft delete lead (POST)
│       └── assign.php             # Assign/reassign lead
├── user/
│   ├── dashboard.php              # User dashboard with personal stats
│   └── leads/
│       ├── index.php              # List assigned leads
│       ├── view.php               # Lead details + follow-ups + timeline
│       ├── edit.php               # Edit assigned lead
│       └── followup.php           # My follow-ups list
├── auth/
│   ├── login.php                  # Login page
│   └── logout.php                 # Logout handler
├── ajax/
│   ├── leads.php                  # AJAX lead search endpoint
│   ├── followups.php              # Follow-up CRUD handler
│   └── dashboard.php              # Dashboard stats endpoint
├── assets/
│   ├── css/style.css              # Custom styles
│   ├── js/app.js                  # Custom JavaScript
│   └── images/                    # Image assets
├── database/
│   └── lead_management.sql        # Complete SQL with schema + demo data
├── index.php                      # Root entry point (redirect)
└── README.md                      # This file
```

---

## Key Features Explained

### Lead Lifecycle
```
Lead Created → New → Contacted → Follow Up → Interested → Converted
                                                       → Not Interested → Lost
```

### Follow-up Types
- Call, Email, WhatsApp, Meeting

### Follow-up Status
- Pending, Completed, Cancelled

### Lead Sources
- Website, Referral, Facebook, Instagram, Google, LinkedIn, Advertisement, Cold Call, Other

### Priority Levels
- Low, Medium, High, Urgent

---

## Security Features

| Feature | Implementation |
|---------|---------------|
| Password Hashing | `password_hash()` with `PASSWORD_DEFAULT` |
| SQL Injection | PDO prepared statements on all queries |
| XSS Protection | `htmlspecialchars()` on all output |
| CSRF Protection | Token-based CSRF on all POST forms |
| Session Security | `session_regenerate_id(true)` after login |
| Authorization | Server-side role checks on every protected page |
| Soft Delete | Leads archived via `deleted_at` timestamp |
| Input Validation | Server-side validation on all form submissions |

---

## Troubleshooting

### Database Connection Failed
- Ensure MySQL is running
- Check credentials in `config/database.php`
- Verify the `lead_management` database exists

### Blank Page
- Check PHP error logging in `php.ini`
- Ensure PHP 8.0+ is installed
- Set `display_errors = On` in `php.ini` for development

### Login Not Working
- Ensure the SQL file was imported correctly
- Verify password hashes in the `users` table
- Clear browser cookies and try again

### Styles Not Loading
- Ensure the `assets/` folder exists and has correct permissions
- Check browser console for 404 errors

### 404 Errors
- Ensure your web server document root points to the correct folder
- Check that `mod_rewrite` is enabled (Apache)
- Verify all file paths match the project structure

---

## License

This project is open source and available for educational and commercial use.
