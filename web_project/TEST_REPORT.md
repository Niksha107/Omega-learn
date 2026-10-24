# Test report

Tests were run against the local XAMPP application at `http://localhost/web_project/`. Tests marked **NOT RUN** were not performed and must not be treated as passed.

| Test ID | Feature | Preconditions | Steps | Expected result | Actual result | Status |
| --- | --- | --- | --- | --- | --- | --- |
| T01 | Homepage and navigation | Apache running | Request homepage and inspect primary navigation | Homepage responds and links navigate to existing pages | Homepage and main navigation routes returned HTTP 200. Unsupported course, notes, and project destinations were changed to clear non-clickable listings. | PASS |
| T02 | Successful registration | Database running; unique test identity | Submit valid registration through signup form | Account is created with a password hash | Not executed; use a disposable test account. | NOT RUN |
| T03 | Duplicate registration | T02 test account exists | Submit the same username/email again | Duplicate is rejected without exposing SQL details | Not executed. | NOT RUN |
| T04 | Successful login | Registered test account | Sign in with valid credentials | Session starts and user reaches role-appropriate dashboard | Not executed. | NOT RUN |
| T05 | Invalid login | Login page available | Submit an incorrect password | Generic failure; no authenticated session | A unique invalid test identity redirected to the generic invalid-credentials URL. | PASS |
| T06 | Timeout and logout | Authenticated test account | Wait over 15 minutes or logout; revisit protected page | Session is destroyed and protected page requires login | Not executed. | NOT RUN |
| T07 | Admin access | Admin test account and anonymous browser | Open admin dashboard and notice management | Admin is admitted; anonymous user is redirected | Anonymous notice admin/add/edit/delete routes returned HTTP 302. Admin login was not tested. | NOT RUN |
| T08 | Student access and admin denial | Student test account | Open student dashboard and then an admin-only route | Student dashboard works; admin route returns 403 | Not executed. | NOT RUN |
| T09 | Create student record | Admin test account | Create a clearly marked test student | Record appears in student list | Not executed. | NOT RUN |
| T10 | View student record | T09 test record | Open student list and locate test record | Record displays correctly with escaped fields | Not executed. | NOT RUN |
| T11 | Update student record | T09 test record | Edit a test field and save | Updated data is displayed | Not executed. | NOT RUN |
| T12 | Delete student record | Disposable test record only | Confirm test record ID, submit delete, verify it is absent | Only the confirmed test record is deleted | Not executed; no database records were deleted. | NOT RUN |
| T13 | Registration required fields | Signup form available | Submit an empty registration POST | Request redirects to signup with a validation message; no account is inserted | Empty registration redirected to signup with an error. Student CRUD invalid-input checks remain unrun. | PASS |
| T14 | SQL injection and XSS | Test environment only | Use harmless input probes in test fields and verify displayed output | Queries remain parameterized; injected markup is displayed as text | Not executed with payloads. Public notice rendering uses text nodes; code inspection only. | NOT RUN |
| T15 | Notices API JSON | Local XAMPP app; `notices.json` contains notices | GET `notice_api.php` | Valid JSON contains notices from the project notice file | Returned HTTP 200 JSON with all 15 entries from `notices.json`; database rows were not changed. | PASS |
| T16 | Database failure handling | Isolated test configuration | Temporarily use an invalid local DB setting, then restore it | Generic message for visitors; detailed error only in server log | Not executed; configuration was not changed. | NOT RUN |
| T17 | Responsive layout | Browser with desktop/mobile viewport | Inspect homepage and notice board at both widths | Content remains usable without unintended horizontal overflow | Not verified; browser viewport automation was unavailable. | NOT RUN |
| T18 | Accessibility basics | Browser and keyboard | Navigate forms and controls by keyboard; inspect labels/focus/errors | Controls have labels, visible focus, and understandable errors | Not executed. | NOT RUN |
| T19 | Direct protected-page access | Logged-out browser | Open admin notice/student/contact-record pages and submit anonymous API mutation | Pages redirect; API mutation is rejected | Notice and student admin pages and contact records returned HTTP 302; anonymous API write returned HTTP 401. | PASS |
| T20 | Static links, PHP syntax, and key routes | Local application running | Scan local references; request key pages; lint PHP | No missing local references or PHP syntax errors; key public routes respond | All PHP files passed syntax checks. Static local link scan found no missing targets; key public pages returned HTTP 200; DSA CSS and JavaScript are inside valid tags. | PASS |
| T21 | Public notices interaction | Browser; JSON-backed notice board | Search DSA, then filter the results to Notes | Search and category filter combine correctly and render matching cards | Search returned two DSA notices; applying the Notes filter returned the matching DSA Notes notice. | PASS |
| T22 | JavaScript syntax | Node.js available | Run `node --check script.js` and check AJAX inline script | No syntax errors | Both checks passed after the fix. | PASS |
| T23 | Notice API CSRF | Local PHP session | Submit a mutation with a bad token, then valid token and invalid notice ID | Bad token returns 403; valid token reaches input validation without changing records | Bad token returned 403; valid token with invalid ID returned 400. No rows changed. | PASS |
| T24 | Registration/invalid login | Local database running | Submit empty registration and invalid login credentials | Registration is rejected with feedback; invalid login gives a generic failure | Empty registration redirects to signup with a validation message; invalid login redirects with the generic invalid-credentials code. | PASS |
| T25 | Private files and diagnostics | Apache `.htaccess` enabled | Request runtime JSON, DB helpers, diagnostics, and admin setup over HTTP | Private files denied; admin setup cannot run over HTTP | Private files returned 403; unauthenticated admin setup, notice/student pages, and records redirected; admin setup endpoint is blocked. | PASS |
| T26 | JSON-backed notice pagination | Local XAMPP app; `notices.json` contains 15 entries | Open `notices.html`; navigate pages 1, 2, and 3 | Three numbered pages show five notices apiece; Next is disabled on page 3 | Verified in browser: page 1 showed five notices; page 2 showed five beginning with “New Python Course Added”; page 3 showed five beginning with “JavaScript DOM Notes Added”; Next was disabled on page 3. | PASS |

## Unavailable page features

The project does not contain individual C/C++/web/Python/AI course pages, separate notes pages, project demo pages, or a payment/checkout page. The dead links were removed rather than creating dummy pages or implying that checkout works. The DSA course page remains available, and its course inquiry action opens the existing contact form. The store is a catalogue only; order processing is not implemented.

## Manual end-to-end test instructions

1. Register a disposable student account with a unique email/username; verify duplicate registration is rejected.
2. Log in as that student, verify the student dashboard, try an admin route (expect 403), then log out and revisit the dashboard.
3. Provision a separate disposable admin account using the CLI-only helper. Log in and verify the admin dashboard.
4. Create a clearly labeled test student, view it, edit it, and delete only that test record after checking its ID.
5. Use `notice_admin.php` to open the notice editor. Create a clearly marked test notice, verify it appears on `notices.html`, edit it, then delete only that test notice.
6. Test invalid/empty form values. Use harmless text resembling HTML and verify it is displayed literally.
7. Repeat checks at a narrow mobile viewport and keyboard-only; record any failures before submission.

No test notice, student, or user record was created or deleted during the automated checks.
