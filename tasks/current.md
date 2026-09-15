# Zadatak 2.51 — Lean transactional email smoke

Status: READY
Baseline: 11fbf2014026a5e4486665a62bb42b4e636d9940
Previous task: 2.50
Target: staging
Production: FORBIDDEN
Time budget: 25 minutes
Mode: controlled staging fixtures; no application-code change; no real email transport

## Goal

Prove that the launch-critical transactional email events generate valid messages with correct staging links and resolved business data.

PASS classification: `TRANSACTIONAL_EMAIL_RENDER_SMOKE_PASS`.

Background cron and Action Scheduler behavior are intentionally excluded and will be handled in a separate lean task.

## Lean rules

Read only `tasks/README.md`, this task and the exact mail callbacks/services invoked. Do not read historical reports, inventory unrelated vendor mail, create a broad mail framework or generate a finalizer script.

Use the existing staging mail-safety layer and one small temporary probe. Every message must be intercepted at `pre_wp_mail` before PHPMailer/SMTP. Never use or print a real recipient address, reset token, password, full message body or candidate PII.

No feature branch, code edit, commit, push or deploy is authorized. If a product defect is found, report it and stop; repair belongs to a separate small task.

## Preflight

Verify:

- Git staging, live owned files and deploy marker equal the baseline;
- clean worktree and staging environment;
- staging mail-safety MU plugin is active;
- `pre_wp_mail` interception is installed before any probe;
- both staging locks are free;
- initial counts for users, profiles, jobs and applications.

Do not inspect or gate on unrelated scheduler rows.

## Controlled fixtures

Create only the minimum synthetic employer, candidate, profiles, one job and one application context required by the existing services. Use reserved non-deliverable domains and random credentials that are never printed or saved.

Ledger every created ID before moving to the next event. Hold the staging mutation lock only during fixture creation/change and exact cleanup.

Use WordPress/WP Job Board Pro/Raspitajse APIs, not raw SQL.

## Email events

Trigger each event once through its normal service or closest side-effect-free public callback:

1. account registration/welcome for a synthetic user;
2. password-reset request for that synthetic user;
3. candidate application notification to the owning employer;
4. application confirmation/status message to the candidate, if this is part of the currently enabled launch behavior;
5. job submission/publication notification, if currently enabled.

For every enabled event assert:

- exactly the intended recipient role is selected;
- subject and body are non-empty;
- no unresolved `{{placeholder}}`, raw template token or PHP diagnostic remains;
- staging URLs use the canonical staging host and expected path;
- no pricing, package, checkout, payment or production URL appears;
- only the minimum expected mail API call is made;
- `pre_wp_mail` blocks transport before PHPMailer/SMTP.

An event that is intentionally disabled is PASS only when its owning setting/hook proves that decision; do not enable options merely for the test.

Report recipients only as role labels plus salted hashes. Report body/subject only as hashes and boolean assertions.

## Cleanup and side effects

Delete all fixture application, job, profiles and users by ledgered IDs using normal APIs. Verify initial counts are restored.

Required final state:

- PHPMailer/SMTP transport: 0;
- payment/gateway: 0;
- external HTTP transport: 0;
- cron/Action Scheduler execution or mutation: 0;
- new PHP notice/warning/error/fatal: 0;
- code, options, source/live files and deploy marker unchanged.

If cleanup fails, perform at most one bounded correction using only ledgered IDs. Never use broad deletion.

## Result and report

PASS requires all enabled launch email events to render correctly, all transport to be intercepted and exact cleanup.

BLOCKED requires one exact event, callback, unresolved field, wrong route/recipient role or cleanup failure. Do not implement a fix in this task.

Publish one short report with:

- result/classification and baseline;
- one row per email event: enabled/disabled, intended role, render PASS/BLOCKED, transport blocked;
- placeholder/link/content assertions without PII;
- fixture types and sanitized IDs;
- before/after counts;
- side-effect counters;
- exact blocker if any;
- locks released, no code/deploy change and production untouched.

If a command has no output for 10 minutes, stop. Maximum one correction for probe syntax/bootstrap.

Verify the remote report and STOP. Do not begin the scheduler follow-up task.
