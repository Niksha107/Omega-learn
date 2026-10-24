# Security notes

## Implemented controls

- Registration uses `password_hash()`; login verifies with `password_verify()` and regenerates the session ID on successful authentication.
- Database-backed user-controlled queries use prepared statements.
- Admin access checks are centralized in `admin_access.php`, with a 15-minute inactivity timeout.
- Notice management pages and mutating API methods require an admin session.
- Admin notice forms and AJAX writes validate a session CSRF token.
- Student CRUD delete uses POST; student and notice content is escaped or rendered as text.
- Notice poster uploads enforce a size limit and check the detected MIME type and image content.
- `.htaccess` disables directory listings and blocks direct HTTP access to local data and connection/helper files.
- `records.php` is restricted to administrators.
- Database connection details are logged server-side; visitors receive a generic failure message.

## Local configuration precautions

- Do not commit database passwords, `.env` files, session data, or `data.json`.
- Change the default local database account configuration before sharing the project.
- Use the CLI-only `create_admin.php` helper with local environment variables; never put an admin password in a URL or source file.
- Store only test data in the development database. Back up any contact submissions securely.
- Keep Apache and MySQL bound to local development use unless you have configured them securely.

## Remaining limitations

- The project has not had a full authenticated browser-based test of login, student CRUD, or admin notice CRUD.
- Existing public HTML pages include several links whose destination files are absent; see `TEST_REPORT.md`.
- Contact form submissions remain in a JSON file under the project directory. `.htaccess` and `.gitignore` mitigate HTTP exposure and accidental Git submission, but a private storage directory outside the document root would be preferable.
- Session cookie security settings depend on the local HTTP/HTTPS configuration and should be reviewed before deployment beyond localhost.
- This educational project has not undergone a production security assessment.
