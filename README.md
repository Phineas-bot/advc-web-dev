# CIMEN Limited HRMS

Human Resource Management System for CIMEN Limited, a cement and construction company.

## Features

- Role-based access control for Super Admin, Admin, and Employee
- Employee registration and login
- Remember-me session persistence
- Forgot password and password reset workflow
- Department management
- Department record CRUD with 10 custom fields
- Employee and admin registries
- PDF export for employees, department records, and search results
- Bootstrap 5 responsive UI
- CSRF protection, prepared statements, password hashing, and XSS-safe output

## Folder Structure

```text
project-root/
├── assets/
├── config/
├── database/
├── docs/
├── includes/
├── modules/
├── pdf/
├── uploads/
├── index.php
├── login.php
├── register.php
├── forgot-password.php
├── reset-password.php
├── dashboard.php
├── profile.php
└── logout.php
```

## Installation Guide

1. Copy the project folder into your Apache or Nginx web root.
2. Create a MySQL database named `cimen_hrms`.
3. Import `database/cimen_hrms.sql` into MySQL.
4. Update `config/database.php` if your DB host, username, or password differ.
5. Ensure the `uploads/` directory is writable by the web server.
6. Open the app in your browser and sign in with the default Super Admin account.

## Default Super Admin

- Username: `superadmin`
- Password: `Admin@123`

## Sample Screenshots

Add production screenshots to `assets/images/` and document them here:

- `assets/images/homepage.png` - Public homepage
- `assets/images/login.png` - Authentication screen
- `assets/images/dashboard-super-admin.png` - Super Admin dashboard
- `assets/images/dashboard-admin.png` - Admin dashboard
- `assets/images/department-page.png` - Department record management screen

Database import and local setup

- Import the provided SQL into your local MySQL server:

```powershell
# From the project root (Windows PowerShell)
# Update the values as needed or use the .env file below
mysql -u root -p cimen_hrms < database/cimen_hrms.sql
```

- You can also use the included PowerShell helper script to attempt an import or print exact commands:

```powershell
.\scripts\import-db.ps1
```

Environment (database) configuration

- Copy `.env.example` to `.env` or set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` before running.

Logo and screenshots

- Place company logo and screenshots in `assets/images/`. The app will use `assets/images/logo.svg` if present; otherwise the text brand is shown in the navbar.

If you want me to attempt the import now, say so and I'll check for a `mysql` client and run the script.

## Database ER Diagram

See [docs/erd.md](docs/erd.md).

## PDF Export

The application includes a lightweight FPDF-compatible writer in `pdf/fpdf.php` and a controller in `pdf/export.php` for generating reports.

Available export targets:

- Employee list
- Department records
- Search results

## Security Notes

- All DB interactions use PDO prepared statements.
- Passwords are hashed with `password_hash()`.
- Forms include CSRF tokens.
- Output is escaped with `htmlspecialchars()` through helper functions.
- Role checks are enforced on dashboard, registry, and department pages.

## Deployment Instructions

### Shared Hosting or VPS

1. Upload the files to the server.
2. Create the MySQL database and import `database/cimen_hrms.sql`.
3. Set the correct DB credentials in `config/database.php`.
4. Make sure PHP 8+ and MySQL 8+ are installed.
5. Enable `mbstring` and `pdo_mysql`.
6. Point the web root to the project folder.

### Apache Example

```apache
DocumentRoot /var/www/html/cimen-hrms
```

### Nginx Example

```nginx
root /var/www/html/cimen-hrms;
index index.php;
```

## Notes

- The forgot-password flow simulates email delivery by showing the generated reset link in development.
- The Super Admin account cannot be deleted by the UI and is protected by a database trigger.
- For a real mail setup, replace the simulated reset display with SMTP integration.
