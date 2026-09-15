# Zadatak 2.49 — Lean authentication and role smoke

Status: READY
Baseline: 11fbf2014026a5e4486665a62bb42b4e636d9940
Previous task: 2.48.1
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes
Mode: read-only

## Goal

Confirm that staging authentication endpoints, required assets and WordPress role/capability routing are ready for a short manual browser check.

This task does not attempt to automate real CAPTCHA, passwords, email delivery or interactive browser sessions.

PASS classification: `AUTH_ROLE_SERVER_SMOKE_PASS`.

## Minimal reads

Read only:

- `tasks/README.md`;
- this task;
- the active child-theme wp-admin redirect function;
- the WP Job Board Pro registration/login handler entry points;
- the deployed Raspitajse free-access/UI policies only where needed to exclude redirect regressions.

Do not read historical reports or create a new test framework.

## Preconditions

Verify:

- local staging, `origin/staging`, live owned files and deploy marker equal the baseline;
- worktree is clean;
- staging environment is active;
- `users_can_register=1`;
- required roles exist: administrator, employer and candidate.

This is read-only. No staging lock is required because no database or file mutation is allowed.

## Focused checks

Use existing WP-CLI, curl and WordPress APIs directly. No generated scripts longer than a small temporary probe.

1. `/login/`, `/register/`, password-reset entry, candidate dashboard, employer dashboard and `/wp-admin/` return expected non-5xx responses.
2. WP Job Board Pro `main.js` and the four previously repaired registration assets return HTTP 200.
3. Login and registration AJAX endpoints exist and return valid structured validation responses for deliberately incomplete/invalid requests; create no account and establish no session.
4. Administrator capability check:
   - an existing administrator has `manage_options`;
   - the child-theme rule permits that administrator to reach wp-admin;
   - no hardcoded user-ID authorization is present.
5. Employer capability/routing check:
   - an existing employer resolves to the employer profile;
   - can access employer dashboard/submission authorization;
   - cannot obtain `manage_options` or administrator-only access.
6. Candidate capability/routing check:
   - an existing candidate resolves to the candidate profile;
   - can access candidate dashboard/profile/application authorization;
   - cannot access employer or administrator-only operations.
7. AJAX remains exempt from the non-admin wp-admin redirect.
8. Free-launch redirects do not capture login, register, password reset, dashboard or application endpoints.
9. No new PHP notice/warning/error/fatal appears during the probes.
10. No database/file/options/user mutation, mail, SMTP, payment, external HTTP transport or scheduler execution occurs.

Do not display usernames, emails, password hashes, reset tokens, cookies, nonces or other PII/secrets in output or report.

## Result handling

PASS if all server checks above succeed. A missing real browser/CAPTCHA/password test is expected and must be reported as `MANUAL_BROWSER_CHECK_REQUIRED`, not as a Codex failure.

BLOCKED only for an actual endpoint, asset, capability, role mapping or redirect defect. Diagnose the exact defect but do not implement a fix in this read-only task.

## Report

Publish one short report containing:

- PASS/BLOCKED and classification;
- baseline confirmation;
- a ten-row check table;
- any actual blocker;
- the following manual checklist:
  1. admin login and wp-admin;
  2. employer login/dashboard/logout;
  3. candidate login/dashboard/logout;
  4. one password-reset request to a controlled inbox;
  5. registration form with real CAPTCHA, without completing account creation unless explicitly approved;
- zero-mutation/side-effect confirmation;
- production untouched.

If any command has no output for 10 minutes, stop and report the checkpoint. Do not retry with a new harness.

Verify the remote report and STOP. Do not begin Task 2.50.
