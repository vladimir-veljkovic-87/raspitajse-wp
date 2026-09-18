# Zadatak 2.53.1 — Deploy already-validated WordPress/WPForms patch commit

Status: READY
Baseline / Commit A: `8d774c1e831d754c7e3f7e57842f61471a43f811`
Prepared feature / Commit B: `4ad988463b89f9ad3c22f9f22c6766c6301f38eb`
Feature branch: `feature/task-2.53-core-wpforms-patch`
Previous task: 2.53
Target: staging
Production: FORBIDDEN
Time budget: 25 minutes

## Goal

Finish the already prepared update so staging runs:

- WordPress `7.1.1`;
- WPForms Lite `2.0.2`;

and align local `staging`, `origin/staging`, live bytes, and deploy marker to exact Commit B.

This is a lean recovery task. Reuse the validated artifacts and evidence from Zadatak 2.53. Do not redownload packages, rebuild Commit B, repeat the 4,049-file lint/checksum suite, or create a new generated harness/finalizer.

## Accepted prior evidence

The final 2.53 report is authoritative for:

- Commit A deploy support and rehearsal: PASS;
- Commit B scope: only official WordPress 7.1.1 core paths and `wp-content/plugins/wpforms-lite/**`;
- official package hashes and integrity/checksums: PASS;
- changed-PHP syntax and package inventory parity: PASS;
- DB schema version unchanged at `61833`;
- no DB backup/upgrade required;
- rollback snapshot/readiness established.

Action Scheduler item `32733` is now accepted as historical baseline:

- status: `complete`;
- attempts: `1`;
- completion predates Zadatak 2.53.

Do not change, restore, reschedule, or execute this item. Require only that its status and attempt count remain `complete/1` before and after this task.

## Preflight — one short pass

Fetch `staging`, the feature branch, and `codex-reports`. Read the final 2.53 report.

Require:

- clean worktree;
- local/origin `staging` and live deploy marker = exact Commit A;
- live WordPress = `7.1`;
- live WPForms Lite = `2.0.1.1`;
- feature branch remote contains exact Commit B and Commit B is a direct descendant of Commit A;
- Commit B diff scope remains limited to the two already-approved package roots;
- Action Scheduler `32733` = `complete/1`;
- standard staging mutation/runner lock is free.

Do not inspect unrelated scheduler rows or rebuild broad fingerprints. Any unexpected mismatch is one precise BLOCKED result.

Before live mutation, acquire the existing staging lock and retain/verify rollback material for the exact affected core/WPForms paths plus the current marker. Use the already integrated deploy mechanism; do not author another deploy script.

## Deploy and acceptance

Deploy exact Commit B using `deployment/deploy-staging.sh`. The existing marker at Commit A must not cause a skip.

Verify once:

1. live WordPress = `7.1.1`;
2. live WPForms Lite = `2.0.2`;
3. exact source/live byte parity for Commit B changed paths and required deletions;
4. `wp-config.php`, `.htaccess`, and `.user.ini` state unchanged;
5. WordPress bootstrap succeeds;
6. home, `/login-register/`, and jobs route succeed using one bounded request each;
7. wp-admin/plugin bootstrap succeeds;
8. WPForms Lite is active and loads; if an existing published form exists, render one without submission, otherwise record `WPFORM_RENDER_NOT_APPLICABLE`;
9. Raspitajse-owned plugins and active child theme load;
10. no new update-attributable PHP fatal/error/warning;
11. Action Scheduler `32733` remains exactly `complete/1`.

Do not send email, submit forms, run payment/refund, cron, Action Scheduler, quota, application, expiry, communications, entitlement, or job-alert suites. Do not add a broad HTTP/security harness.

## PASS path

If the single acceptance pass succeeds:

1. fast-forward local `staging` from Commit A to exact Commit B;
2. push `staging` without force;
3. run only the required final marker/parity verification;
4. require local staging = origin staging = deploy marker = Commit B;
5. require live versions `7.1.1` and `2.0.2`;
6. release the lock after evidence/report write;
7. publish `PASS: PATCH_UPDATES_DEPLOYED`.

## Failure path

If deployment or acceptance fails:

1. do not integrate Commit B;
2. restore the exact Commit A core/WPForms live state and marker using the retained rollback material and existing deploy support;
3. verify WordPress `7.1`, WPForms Lite `2.0.1.1`, and marker Commit A;
4. leave Git history unchanged;
5. publish one precise BLOCKED reason and stop.

Never leave a mixed core/plugin runtime.

## Scope and efficiency rules

Allowed:

- deploy/integrate exact Commit B;
- rollback only affected WordPress core/WPForms paths and marker;
- one concise report.

Forbidden:

- production;
- scheduler mutation/execution;
- package redownload/rebuild;
- source-code changes;
- new commits other than fast-forwarding exact Commit B to staging and the report commit;
- other plugins/themes;
- force push/rebase/reset;
- generated finalizer or new broad test framework;
- repeating already accepted 2.53 validation.

STOP after one report.
