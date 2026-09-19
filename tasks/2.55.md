# Zadatak 2.55 — Close scheduler launch blockers and prove the final owned cron chain

Status: READY
Baseline: 4ad988463b89f9ad3c22f9f22c6766c6301f38eb
Previous task: 2.54
Target: staging
Production: FORBIDDEN
Time budget after READY: 30 minutes

## Required result

This task is complete only when all of the following are true on staging:

1. the Hostinger host cron trigger exists and is enabled every 15 minutes;
2. the stale Action Scheduler 32733 guard expectation is corrected to the accepted current baseline semantics;
3. the three Raspitajse-owned hourly cron hooks have one valid schedule and one valid owned callback each;
4. no competing legacy execution path is active;
5. the owned runner passes one authoritative `--check-only` run with zero prohibited side effects;
6. local staging, origin/staging, live deploy marker and runtime guard source are aligned to the final accepted SHA.

PASS classification:

`OWNED_SCHEDULER_LAUNCH_READY`

Do not return PASS for only fixing the guard or only detecting the host cron entry.

## Manual prerequisite — required before Codex may start

The repository workflow cannot create Hostinger's host cron entry from this environment.

Before this task becomes READY, the owner must create exactly one enabled Hostinger cron job with:

Schedule:

`*/15 * * * *`

Command:

`/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh`

Harmless stdout/stderr redirection is allowed.

Do not create:

- a second entry for the same runner;
- a generic `wp cron event run --all`;
- an Action Scheduler runner;
- a production runner.

Once the owner confirms that the Hostinger cron job is saved/enabled, update this task status to `READY` without changing the baseline or the rest of the task.

Codex MUST NOT execute while status is `WAITING_FOR_MANUAL_HOST_CRON`.

## Preconditions after READY

Read only:

- `tasks/README.md`;
- this task;
- final 2.54 report;
- `tools/raspitajse-staging-owned-cron-runner.sh`;
- `tools/raspitajse-staging-owned-cron-guard.php`;
- exact scheduler registration code for the three owned hooks.

Verify:

- local `staging`, `origin/staging`, live deploy marker = `4ad988463b89f9ad3c22f9f22c6766c6301f38eb`;
- clean worktree;
- staging environment;
- WordPress `7.1.1`;
- WPForms Lite `2.0.2`;
- standard staging locks initially free;
- exactly one effective enabled host cron entry invokes the owned runner every 15 minutes.

If the host cron entry is still absent, duplicated, or broader than the exact owned runner, STOP with one precise blocker and no mutation.

## Phase A — correct stale protected Action Scheduler guard semantics

Create one scoped feature branch from the exact baseline.

The source change is limited to:

`tools/raspitajse-staging-owned-cron-guard.php`

The current guard hard-codes Action Scheduler item 32733 as valid only when `pending/0`. The accepted current staging baseline from 2.53/2.54 is `complete/1`.

Do not merely replace one hard-coded historical value with another historical assumption if a safer baseline-preservation contract is available.

Implement the smallest robust correction such that:

- item 32733 must exist;
- the guard reports its actual status and attempts;
- check-only/deep snapshot can verify that the task itself did not mutate the item;
- the guard does not execute, cancel, delete, reschedule or repair the item;
- no generic Action Scheduler behavior is changed.

Preferred design: remove stale lifecycle-state policy from the static guard and treat 32733 as a protected observed row whose before/after value must remain identical during verification.

If that cannot be done without broadening scope, use the exact accepted baseline `complete/1` narrowly and document why.

Run:

- PHP syntax validation for the changed guard;
- focused static diff review;
- one disposable/no-mutation snapshot decode test if useful.

No application business code changes are authorized.

## Phase B — deploy the guard fix safely

Commit the guard-only change.

Deploy the exact feature diff using the approved staging deploy mechanism.

Verify:

- only the guard file changed in source/runtime;
- runtime guard byte parity equals source;
- deploy marker points to the feature SHA;
- no WordPress core/plugin/theme version changes;
- no DB mutation attributable to deploy.

Do not yet integrate to `staging`.

## Phase C — prove the full operational chain

Capture a small sanitized T0 projection:

- exact host cron entry count/schedule/command hash;
- three owned cron events: next timestamp, recurrence, interval;
- three owned callback fingerprints;
- continuation event count;
- exact current 32733 status/attempts;
- deploy marker;
- source/runtime guard hash;
- WordPress/WPForms versions.

Then run exactly once:

`/bin/bash tools/raspitajse-staging-owned-cron-runner.sh --check-only`

Require:

- exit 0;
- result `NOOP`;
- reason `check_only`;
- environment `staging`;
- exact three owned hooks present;
- hook statuses only `due_check_only` or `not_due`;
- executed_hooks = 0;
- zero unexpected HTTP;
- zero actual external network;
- zero mail/PHPMailer/SMTP;
- zero payment;
- no broad WP-Cron execution;
- no Action Scheduler execution.

Also prove:

- `DISABLE_WP_CRON=true`;
- runner flock overlap guard active;
- source HEAD/deploy marker check active;
- communications manifest/parity gate active;
- exactly the three owned runner hooks are executable;
- no continuation event;
- no competing scheduled legacy WP Job Board Pro job-alert/candidate-alert/job-expiry path.

Do not run the runner in normal execution mode.

## Phase D — protected equality and integration

Capture the same final projection.

PASS requires:

- host cron configuration unchanged from T0;
- all three owned schedules/callbacks unchanged;
- item 32733 exact status/attempt count unchanged from T0;
- no Action Scheduler count/state mutation attributable to this task;
- deploy marker/source/runtime guard parity correct;
- zero prohibited side effects.

If all gates pass:

1. fast-forward `staging` to the exact accepted guard-fix commit;
2. push without force;
3. deploy final `staging`;
4. rerun only the cheap static/source/runtime alignment checks, not a second runner health invocation;
5. verify local staging = origin/staging = live marker;
6. report:
   `PASS: OWNED_SCHEDULER_LAUNCH_READY`.

If any critical gate fails after feature deploy:

1. do not integrate the feature commit;
2. restore the baseline guard through the approved deploy path;
3. restore marker to baseline;
4. prove runtime/source baseline parity;
5. report one precise BLOCKED reason.

## Scope

Authorized mutation:

- `tools/raspitajse-staging-owned-cron-guard.php` only.

Forbidden:

- production;
- crontab/Hostinger cron modification by Codex;
- WordPress core/plugin/theme changes;
- scheduler callback business logic changes;
- broad WP-Cron execution;
- Action Scheduler execution/mutation;
- mail/payment/external network;
- fixture creation;
- user/post/order/application mutation;
- force push/history rewrite.

## Final report

Publish one concise report with:

- result/classification;
- baseline and final SHA;
- host cron trigger proof;
- exact guard change summary;
- three-row owned hook schedule/callback table;
- competing legacy path result;
- sanitized one-run `--check-only` result;
- 32733 before/after equality;
- zero-side-effect confirmation;
- final local/origin/live marker alignment;
- production touched: NO.

Verify remote report and STOP.
