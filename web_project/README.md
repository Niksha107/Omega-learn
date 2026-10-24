# Omega Learn StudentHub

Omega Learn is a student learning portal with public course and resource pages, account registration and login, role-based dashboards, student record management, a notice board, and an AJAX notices API.

## Features

- Public home, course summaries, notes/project listings, a product catalogue, and contact page.
- Student registration with server-side validation and password hashing.
- Login/logout with PHP sessions and student/admin dashboards.
- Admin student-record CRUD.
- Database-backed notices, public filtering/search, poster uploads, and an admin AJAX editor.
- Admin-only student and notice management endpoints.
- Audit-log display on the admin dashboard.

The individual notes and project demo pages and a store checkout are not included. These listings are intentionally non-clickable rather than linking to pages that do not exist or suggesting that purchases can be completed.

## Technology

- HTML5, CSS3, JavaScript
- PHP with PDO and MySQLi
- MySQL/MariaDB through XAMPP

## Prerequisites

- Windows with XAMPP (Apache, PHP, MySQL/MariaDB)
- PHP extensions `pdo_mysql`, `mysqli`, `fileinfo`, and `gd`
- A modern browser

## Installation

1. Copy the project folder to `C:\xampp\htdocs\web_project`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Use the existing `omega_learn` database if it already contains the project tables or records. **Do not import the schema over an existing database.** For a new, empty database only, create `omega_learn` using `utf8mb4` and import [`database/omega_learn_schema.sql`](database/omega_learn_schema.sql). The export contains table definitions only; it does not contain user or student records and has no `DROP TABLE` statements.
5. Configure the local database account in `db.php` and `db_mysqli.php`. Keep real passwords out of Git. The default XAMPP `root` account may have an empty password; use a local-only account with a password where possible.
6. Open `http://localhost/web_project/`.

The Apache `.htaccess` file disables directory listings and blocks direct access to the local JSON data, PHP configuration/helper files, and diagnostic scripts. Diagnostic and admin setup scripts also refuse HTTP execution. If your Apache configuration disables `.htaccess` overrides, enable the appropriate `AllowOverride` setting for this directory and restart Apache.

### Optional Apache virtual host

The `http://localhost/web_project/` URL does not require a virtual host. If your practical requires one:

1. Back up `C:\xampp\apache\conf\extra\httpd-vhosts.conf` before editing it.
2. Ensure `httpd.conf` includes `conf/extra/httpd-vhosts.conf`.
3. Add this virtual host to the end of `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName omega-learn.local
    DocumentRoot "C:/xampp/htdocs/web_project"
    <Directory "C:/xampp/htdocs/web_project">
        Options FollowSymLinks
        AllowOverride All
        Require local
    </Directory>
    ErrorLog "logs/omega-learn-error.log"
    CustomLog "logs/omega-learn-access.log" common
</VirtualHost>
```

4. Open Notepad as Administrator and add `127.0.0.1 omega-learn.local` to `C:\Windows\System32\drivers\etc\hosts`.
5. Restart Apache and visit `http://omega-learn.local/`. If Apache fails to start, restore the backup and check the Apache error log and port 80.

### Admin account setup

New registrations receive the database's default `student` role. Do not expose `create_admin.php` over HTTP; it is CLI-only. Create the intended account through registration, then promote that existing account from a local PowerShell session:

```powershell
$env:OMEGA_ADMIN_EMAIL = 'the-existing-account@example.com'
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\web_project\create_admin.php'
Remove-Item Env:OMEGA_ADMIN_EMAIL
```

This helper changes only the account role; it does not change the password. Use an account you control and avoid saving sensitive values in shell history.

## Routes

| Route | Purpose |
| --- | --- |
| `index.html` | Public homepage |
| `courses.html`, `notes.html`, `project.html`, `store.html` | Public learning and store pages |
| `signup.html` / `register.php` | Student registration |
| `login.html` / `login.php` | Login |
| `student.php` | Student dashboard |
| `admin.php` | Admin dashboard |
| `students.php` | Admin student record list |
| `notices.html` | Public JSON-backed notice board with five notices per page |
| `notice_admin.php` | Redirects admins to notice management |
| `notice_ajax.html` | Admin editor for the notices in `notices.json` |
| `notice_api.php` | Reads and updates `notices.json`; writes require admin and CSRF checks |
| `contact.html` | Contact form |

## Testing

Review [`TEST_REPORT.md`](TEST_REPORT.md) for executed checks and manual test instructions. A syntax check can be run from PowerShell:

```powershell
Set-Location 'C:\xampp\htdocs\web_project'
$php = 'C:\xampp\php\php.exe'
Get-ChildItem -Filter '*.php' | ForEach-Object { & $php -l $_.FullName }
node --check .\script.js
```

Do not test deletion against real student or notice data. Use a clearly identified test record and verify the ID before deleting it.

### Troubleshooting

- **Apache does not start:** Check whether another service is using port 80/443; review the XAMPP Apache error log. Restore any virtual-host backup if the problem began after configuration changes.
- **MySQL does not start:** Review the XAMPP MySQL log and check for a conflicting MySQL/MariaDB service. Do not delete or replace the data directory to troubleshoot.
- **Database connection failed:** Confirm MySQL is running and that the database host, name, username, and password in both connection files match the local setup. Visitors receive a generic error; see Apache/PHP logs for details.
- **Unknown database or missing table:** Check phpMyAdmin for the selected database and expected tables. Import the schema-only SQL only into a new, empty database; do not drop/recreate a database containing records.
- **Blank page or HTTP 500:** Check PHP/Apache logs and run the PHP syntax checks above. Verify the required PHP extensions are enabled.
- **`.htaccess` returns HTTP 500:** Confirm Apache `mod_authz_core` is enabled and `.htaccess` overrides are allowed for this directory. Do not remove the private-file protections before deployment.

## Project structure

```text
web_project/
  database/                 Schema-only MySQL export
  admin_access.php          Admin session and CSRF helpers
  db.php                    PDO database connection
  db_mysqli.php             MySQLi database connection
  notice_api.php            Notices JSON API
  notice_*.php              Admin notice management
  student*.php, students.php Admin student management
  *.html                    Public pages and AJAX editor
  script.js                 Shared UI and public notice-board behavior
  style.css                 Shared styling
```

Runtime contact submissions are stored in `data.json` for the existing contact/records feature. The file is ignored by Git and denied to direct HTTP requests; the records page is admin-only. Back it up securely if you need to retain submissions.

Public notices and notice administration use `notices.json` as their shared data source. Keep this project file writable by the local PHP/Apache account if you want admins to add, edit, or delete notices. The existing MySQL `notices` rows are left untouched and are no longer used by the notice board.

## Security considerations

- Passwords are stored using PHP's password hashing and verification APIs.
- User-controlled database operations use prepared statements.
- Admin notice and student-management routes require an admin session; state-changing admin forms/API requests use CSRF tokens.
- Notice text is rendered as text, not trusted HTML.
- Database connection failures do not reveal server exception details to visitors.
- Set a strong local database password and keep it out of source control.
- The project is a local educational application, not a production-ready service. Review and test all routes before exposing it to a network.

## Screenshots checklist

- [ ] Homepage and navigation on desktop
- [ ] Public notice board with search and category filter
- [ ] Registration validation and successful registration
- [ ] Student dashboard and logout
- [ ] Admin dashboard and audit-log section
- [ ] Student list plus add/edit screens
- [ ] Notice management and public notice board after an admin change
- [ ] Mobile-width view of the homepage and notices

## GitHub repository

Repository URL: `<add your GitHub repository URL after creating it>`

Initialize and submit only after checking the staged files for private data:

```powershell
git init
git add .
git status
git diff --cached
git commit -m "Prepare Omega Learn StudentHub practical submission"
git remote add origin <your-private-or-approved-repository-url>
git push -u origin main
```

This workspace currently has no Git repository configured. Do not publish contact submissions, credentials, or real student data.
