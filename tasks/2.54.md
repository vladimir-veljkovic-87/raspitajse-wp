# Zadatak 2.54 — Prove owned scheduler / cron launch readiness

Status: READY
Baseline: 4ad988463b89f9ad3c22f9f22c6766c6301f38eb
Previous task: 2.53.1
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes
Mode: read-only operational verification

## Required result

This task is complete only if staging has one proven operational background-execution path for the three Raspitajse-owned hourly jobs, with no competing legacy execution path and no prohibited side effects.

PASS classification:

`OWNED_SCHEDULER_LAUNCH_READY`

Do not turn this into a scheduler refactor, callback regression suite, or generic cron inventory. If the owned execution chain is not operational, return one precise BLOCKED reason.

## Scope

Verify only the launch-critical chain:

host cron -> `tools/raspitajse-staging-owned-cron-runner.sh` -> exact owned hooks -> current Raspitajse-owned callbacks.

The expected owned hooks are exactly:

- `raspitajse_job_listing_expiry_evaluator`
- `raspitajse_employer_job_expiry_notice_evaluator`
- `raspitajse_candidate_job_alert_evaluator`

Do not execute broad WP-Cron. Do not execute Action Scheduler. Do not create fixtures. Do not send mail.

## Preconditions

Read only:

- `tasks/README.md`;
- this task;
- `tools/raspitajse-staging-owned-cron-runner.sh`;
- its guard file;
- only the owned scheduler-registration/callback code needed to verify the three hooks.

Verify fresh:

- local `staging`, `origin/staging`, live deploy marker = `4ad988463b89f9ad3c22f9f22c6766c6301f38eb`;
- source worktree clean;
- staging environment active;
- WordPress still `7.1.1`;
- WPForms Lite still `2.0.2`;
- both staging mutation/runner locks are initially free.

If the baseline has changed unexpectedly, STOP rather than silently rebasing.

## 1. Prove host trigger

Read the current shell user's cron configuration without modifying it.

PASS requires one effective enabled entry equivalent to:

`*/15 * * * * /bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh`

Accept harmless shell redirection differences, but require:

- every 15 minutes;
- exact owned runner path;
- no second competing entry for this runner;
- no broad `wp cron event run --all`, generic WordPress cron runner, or Action Scheduler runner in the same user crontab.

Do not edit crontab in this task.

## 2. Prove anti-double-run posture

Verify:

- `DISABLE_WP_CRON` is true in staging;
- the runner has its nonblocking flock/overlap guard;
- the runner requires source HEAD = deploy marker;
- the runner verifies the communications manifest/parity before execution;
- the runner contains only the exact three expected hooks;
- continuation execution is absent.

Do not inspect secrets or print config values unrelated to these booleans/contracts.

## 3. Prove schedule registration and callback authority

Using one bounded WordPress bootstrap with mail/payment/external-HTTP guards loaded, inspect the three owned events.

PASS requires for each hook:

- exactly one next scheduled event;
- hourly recurrence / 3600-second contract;
- callable Raspitajse-owned callback is registered;
- no duplicate callback authority for the same business purpose;
- no legacy WP Job Board Pro candidate/job alert or job-expiry callback remains scheduled as a competing path.

Only inspect the exact retired legacy hooks already referenced by the owned implementation. Do not inventory every cron event on the site.

Verify the current callback/event contract fingerprint matches the runner's expected contract.

## 4. One authoritative runner health check

Run exactly once:

`/bin/bash tools/raspitajse-staging-owned-cron-runner.sh --check-only`

Do not run normal execution mode in this task.

Require:

- exit 0;
- JSON result `NOOP`;
- reason `check_only`;
- environment `staging`;
- exact three hook names;
- each status is only `due_check_only` or `not_due`;
- zero executed hooks;
- zero unexpected external HTTP;
- zero actual external network;
- zero mail/PHPMailer/SMTP;
- zero payment;
- no source/runtime/DB mutation attributable to the check.

A hook being due is not a failure because check-only must not execute it.

## 5. Protected-state equality

Capture before/after only the small protected projection needed for this task:

- three owned cron events: timestamp, recurrence and callback fingerprint;
- continuation event count;
- Action Scheduler total pending/complete counts only as a drift check;
- protected Action Scheduler item 32733 exact current status/attempt count;
- deploy marker;
- WordPress/WPForms versions.

PASS requires exact before/after equality.

Important: do not assume historical `pending/0` for Action Scheduler 32733. The accepted 2.53 baseline reports it as `complete/1`; verify the actual baseline first and require this task not to change it.

Do not execute, cancel, delete, reschedule or repair any Action Scheduler row.

## Side-effect limits

Required totals:

- owned cron callbacks executed: 0;
- broad WP-Cron execution: 0;
- Action Scheduler execution: 0;
- mail/PHPMailer/SMTP: 0;
- payment/refund: 0;
- actual external network: 0;
- user/post/order/application mutation: 0;
- source/deploy mutation: 0;
- production access: 0.

## Result

### PASS

Return:

`PASS: OWNED_SCHEDULER_LAUNCH_READY`

only when the host trigger, anti-double-run controls, exact three schedules/callbacks, check-only runner and protected-state equality all pass.

### BLOCKED

Return one precise blocker if any required link in the chain is missing or inconsistent.

Do not implement a fix in this verification task. Do not create a new framework. Do not retry the runner more than once.

## Report

Publish one concise report containing:

- result/classification;
- baseline/final SHA;
- host cron entry PASS/BLOCKED;
- `DISABLE_WP_CRON` PASS/BLOCKED;
- three-row owned hook table: schedule, recurrence, callback authority;
- legacy competing-path result;
- exact sanitized `--check-only` result;
- protected before/after equality;
- zero-side-effect confirmation;
- production touched: NO.

Verify the remote report and STOP. Do not begin another task.
