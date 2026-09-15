# Zadatak 2.48.1 — Lean completion of free public journey

Status: READY
Baseline staging: 7ce577b5802bcea143c693d426febf743d1ab340
Existing feature branch: feature/task-2.48-free-public-ui
Previous task: 2.48 (interrupted by usage limit before deploy)
Target: staging
Production: FORBIDDEN
Time budget: 30 minutes

## Goal

Finish the already implemented Task 2.48 without repeating completed work.

Expected remote feature diff from baseline:

- only `wp-content/plugins/raspitajse-commerce/includes/class-raspitajse-free-launch-ui-policy.php`;
- only `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- five commits ahead of baseline when this recovery task was authored.

The earlier run already recorded:

- static acceptance PASS;
- guarded pre-deploy acceptance PASS with 83 assertions;
- feature branch pushed;
- no staging deploy or integration;
- no database fixtures or mutations.

## Lean execution rules

Read only `tasks/README.md`, this task, the two changed files and the existing local PASS artifacts if present. Do not reread historical task reports or regenerate the 83-assertion harness.

Do not create a large finalizer script. Do not use `codex-tmp-rewrite.sh`. Do not hold an OS lock during Git fetch/push, HTTP smoke tests, evidence formatting or report publication.

If any command/helper produces no output for 10 minutes, stop it and report the exact checkpoint. Maximum one correction for a harness/command error.

## Preflight

Read-only verify:

1. no stale Task 2.48 process and both staging locks are free;
2. `origin/staging`, live deployed owned files and marker remain at the baseline;
3. the remote feature branch is based directly on the baseline and changes only the two authorized files;
4. worktree has no unrelated change;
5. both changed PHP files pass lint;
6. if the previous static/runtime artifacts exist, validate their recorded PASS/result/hash only; do not rerun them.

Any unrelated source, live or marker drift is BLOCKED.

## Deploy

Use existing proven deploy/copy helpers. No generated orchestration program.

1. Acquire the staging mutation lock only for the live mutation.
2. Reconfirm live hashes and marker.
3. Save baseline copies of the two authorized live files.
4. Deploy exactly the two feature files and set the marker to the full feature SHA.
5. Verify deployed hashes.
6. Release the lock immediately.

Do not modify the database, WordPress options, pages, menus, products, orders, entitlements, vendor files or theme files.

## Focused smoke

Run one bounded smoke suite:

- `/`, `/login/`, `/register/`, `/jobs/`, `/user-dashboard/`, `/submit-job/` return the expected non-5xx result;
- public HTML contains no links/forms/scripts for pricing, packages, cart, checkout, order-pay or add-payment-method;
- submit-job still exposes the free job path;
- direct GET to pricing/packages/cart/checkout is safely redirected or denied without a loop;
- a POST probe to a paid route is rejected and creates no cart/order/payment state;
- wp-admin historical order renderer remains registered;
- Task 2.47 free-access/quota hook remains registered;
- PHP error delta is zero;
- mail, payment, external HTTP and broad scheduler execution counters are zero.

No fixture creation, broad Action Scheduler/WP-Cron runner, real mail or payment.

If smoke fails, reacquire the mutation lock, restore the two baseline files and baseline marker, verify rollback, release the lock and report BLOCKED.

## Integrate

If smoke passes:

1. verify live files and marker still match the feature SHA;
2. fast-forward `staging` to the feature branch and push without force;
3. verify local staging, `origin/staging`, live hashes and marker align;
4. publish one short report to `codex-reports`.

The report needs only: result, final SHA, two-file diff, reused pre-deploy PASS evidence, focused smoke table, zero-side-effect confirmation, final alignment, locks released and production untouched.

PASS classification: `FREE_PUBLIC_JOURNEY_DEPLOYED`.

STOP after PASS or BLOCKED. Do not begin Task 2.49.
