# Zadatak 2.52.2 — Rollback-backed patch updates

Status: READY
Baseline: e40619b99563871889caffbd082f0da9d1cd25d3
Previous task: 2.52.1
Target: staging
Production: FORBIDDEN
Time budget: 30 minutes

## Goal

Update only these confirmed staging patch candidates:

- WordPress core `7.1 → 7.1.1`;
- WPForms Lite `2.0.1.1 → 2.0.2`.

Preserve Git as the source of truth and provide a verified rollback.

Do not update Twenty Twenty-Three, themes, licensed vendor packages or any other plugin.

## Lean preflight

Follow `tasks/README.md`. Confirm:

- clean Git/live/marker alignment at the declared baseline;
- both exact target updates are still reported;
- sufficient disk space;
- whether core and WPForms files are Git-tracked and the existing repository update/deploy convention.

If either target changed/disappeared, or the update cannot be represented reproducibly in Git, return BLOCKED rather than updating live-only.

Create one feature branch from the baseline.

## Backup and update

Before mutation, retain verified rollback copies of the current tracked core and WPForms files. Export the staging database only if the target update requires a database schema upgrade; otherwise record that no DB upgrade is required.

Apply official target packages to the repository working copy. Reject unexpected files, bundled themes/plugins or unrelated changes. The feature diff must contain only WordPress core 7.1.1 and WPForms Lite 2.0.2 package changes.

Run only:

- core/package checksum or official integrity verification;
- PHP syntax checks for changed PHP files using one bounded command;
- diff scope check.

Commit and push the feature branch.

## Deploy and focused smoke

Under the standard staging mutation lock:

1. deploy only the exact feature diff and update the marker;
2. confirm installed core/plugin versions and repository/live equality;
3. release the lock.

Run one focused smoke:

- public home, `/login-register/`, and jobs page return successful responses;
- wp-admin bootstrap/plugin screen loads for an administrator context;
- one existing WPForms form/bootstrap path loads if a published form exists; if none exists, record `NOT_APPLICABLE`;
- no new PHP fatal/error/warning attributable to the updates;
- no real email, external form submission, payment, cron or Action Scheduler execution.

Do not repeat quota, application, expiry or job-alert suites.

## Rollback and integration

If deploy or smoke fails:

- restore the exact baseline files and marker under the lock;
- restore the database only if it was changed;
- verify baseline versions and report BLOCKED;
- do not alter Git history.

If all checks pass:

- fast-forward `staging` to the feature commit and push without force;
- verify clean local/origin/live/marker alignment.

## Result

Return:

- `PASS: PATCH_UPDATES_DEPLOYED`; or
- one precise `BLOCKED` reason with rollback status.

Publish one concise report with old/new versions, changed scope, final SHA, smoke results, DB-upgrade status, final alignment and production untouched. STOP after the report.
