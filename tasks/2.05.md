# Zadatak 2.05 — Bounded selective staging trigger validation for approved Raspitajse-owned cron hooks

Status: READY
Baseline: 544a31171132a3ce95323162df2519ac0135840a
Previous task: 2.04
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or executing runtime validation. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`544a31171132a3ce95323162df2519ac0135840a`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.05. This is a **bounded staging runtime validation**, not an application implementation task. Do not modify application source, do not create an application feature commit, and do not deploy unless an unexpected source/runtime mismatch requires STOP instead.

Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin or create 2.06 automatically.

---

## 1. Context and authorization boundary

Zadatak 2.04 completed PASS with final readiness classification:

`READY_FOR_SELECTIVE_VALIDATION`

The audit proved:

- broad WP-Cron remains unsafe;
- `DISABLE_WP_CRON=true` and request-driven spawning is disabled;
- staging currently has a large overdue vendor/core/protected WP-Cron backlog;
- `action_scheduler_run_queue` is overdue and can reach seven pending Action Scheduler actions, including protected ID32733;
- `/wp-cron.php` and `wp cron event run --due-now` are therefore forbidden;
- exactly three Raspitajse-owned hourly hooks are approved for narrow validation;
- all three owned hooks had zero real due business work at the end of 2.04;
- preferred future steady-state architecture is `EXTERNAL_SELECTIVE_ALLOWLIST_RUNNER` after this bounded validation.

This task authorizes **only one exact sequential invocation of each approved owned cron event**, under fail-closed guards, provided all preconditions still pass immediately before execution.

The three and only three allowlisted hooks are:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

No other hook may be executed.

---

## 2. Goal

Prove that the staging runtime can safely trigger each approved Raspitajse-owned recurring event in isolation using the normal exact-event WP-Cron execution path, while preserving all protected state and without allowing broad cron, Action Scheduler, real mail, external application HTTP or payment activity.

PASS requires all of the following:

1. All preconditions pass before the first invocation.
2. Each of the three exact allowlisted hooks is invoked **at most once**, sequentially.
3. No non-allowlisted WP-Cron hook executes.
4. Each owned event remains exactly one `hourly` recurring event afterward.
5. The expected cron row for each invoked event advances/reschedules normally; no duplicate is created.
6. All three owned business evaluators observe zero due real business work, so no real business record is mutated and no user-visible message is attempted.
7. Action Scheduler pending state, including ID32733 `pending/0`, is unchanged.
8. Real mail/PHPMailer/SMTP, external application HTTP, payment and production activity remain zero.
9. Candidate/job/alert/employer/package protected fingerprints remain unchanged.
10. No application source or deployment change is made.

If any prerequisite or invariant differs, fail closed and STOP before invoking the affected hook or any later hook.

---

## 3. Absolute forbidden execution paths

Do **not** execute, directly or indirectly:

- HTTP `/wp-cron.php`;
- `wp cron event run --due-now`;
- `wp cron event run --all`;
- broad `wp cron event run` without one exact allowlisted hook;
- `wp cron event run action_scheduler_run_queue`;
- Action Scheduler queue runner or due actions;
- ID32733;
- candidate→job continuation hook;
- any vendor/core cron event;
- legacy `wp_job_board_pro_email_daily_notices`;
- legacy `wp_job_board_pro_check_for_expired_jobs`;
- any payment/order/refund callback;
- any production scheduler/runtime.

Do not toggle `DISABLE_WP_CRON`.

Do not repair, drain, reschedule, unschedule or clean any non-allowlisted cron backlog.

Do not use this task as an excuse to execute “safe-looking” core/vendor hooks.

---

## 4. Exact source/runtime parity gate

Before any hook execution, prove:

- local/source staging HEAD = fresh `origin/staging` = declared baseline;
- staging deploy marker = same SHA;
- Raspitajse Communications source/runtime SHA parity is exact;
- runtime environment = `staging`;
- staging mail-safety control is loaded;
- `DISABLE_WP_CRON=true` remains effective;
- production access/touch = NO.

If any parity/environment gate fails, report `BLOCKED`/`FAIL` and do not execute any hook.

---

## 5. Pre-execution protected snapshots

Before the first invocation, capture deterministic sanitized snapshots/fingerprints for:

### WP-Cron

- full event count;
- full unique-hook count;
- canonical full-cron fingerprint;
- canonical fingerprint of **all non-allowlisted cron rows**;
- exact event row/fingerprint for each of the three allowlisted hooks;
- callback count/fingerprint for each allowlisted hook;
- legacy daily callback/event `0/0`;
- legacy shared-expiry callback/event `0/0`;
- candidate→job continuation scheduled event count `0` unless an unexpected pre-existing event causes STOP.

### Action Scheduler

- pending count;
- sanitized pending fingerprint;
- ID32733 status/attempts.

Expected historical state:

- pending count `7`;
- ID32733 `pending/0`.

### Protected business state

Capture sanitized counts/fingerprints for:

- candidates and status distribution;
- candidate expiry-meta footprint;
- historical published `candidate_alert`;
- published `job_alert`;
- job listings and statuses;
- employers;
- employer users;
- job packages/entitlements.

No PII, raw IDs, email addresses, alert filter values, message bodies or serialized sensitive values may be printed in the report.

---

## 6. Revalidate exact allowlist identity immediately before execution

For each exact allowlisted hook, prove immediately before its invocation:

- callback count = exactly `1`;
- callback identity is the expected Raspitajse-owned evaluator;
- callback priority/accepted-args contract matches the accepted implementation;
- recurring event count = exactly `1`;
- recurrence = `hourly`;
- event args count = `0`;
- no duplicate event row exists;
- callback/event fingerprints match the accepted 2.04/2.03 identity or are explained solely by timestamp advancement from an earlier invocation in this same task.

Expected callbacks:

- `raspitajse_job_listing_expiry_evaluator` → `Raspitajse_Communications_Job_Listing_Expiry::run`
- `raspitajse_employer_job_expiry_notice_evaluator` → `Raspitajse_Communications_Employer_Job_Expiry_Notification::run`
- `raspitajse_candidate_job_alert_evaluator` → `Raspitajse_Communications_Candidate_Job_Alert_Evaluator::run`

If any callback/event identity differs, STOP. Do not try to repair it in this task.

---

## 7. Real-business due-work gate — must remain zero

This task does **not** authorize real business mutation or a real user-facing notification.

Immediately before each hook is run, independently calculate its current due work read-only without invoking the evaluator:

### A. Job listing expiry

For `raspitajse_job_listing_expiry_evaluator`:

- published `job_listing` rows with a valid canonical `_job_expiry_date` strictly before the current WordPress site-calendar date must equal `0`.

### B. Employer job-expiry notice

For `raspitajse_employer_job_expiry_notice_evaluator`:

- published jobs with canonical `_job_expiry_date` equal to tomorrow in the site calendar must equal `0`;
- retryable current-revision notice state must equal `0`;
- terminal/current-revision state requiring no send may be counted, but there must be no row that would attempt delivery now.

### C. Candidate→job alerts

For `raspitajse_candidate_job_alert_evaluator`:

- published `job_alert` count must equal `0`;
- owned due-alert count must equal `0`;
- active claim count must equal `0`;
- continuation scheduled event count must equal `0`.

If **any** real due work is non-zero, STOP before running that hook and report PARTIAL/BLOCKED. Do not create fixtures to bypass the gate and do not run against real business records.

The behavior of each subsystem was already fixture-tested in earlier tasks; 2.05 validates trigger isolation, not business logic again.

---

## 8. Mail, network and payment fail-closed guards

Before execution, establish/prove fail-closed staging guards around the three selective runs.

Requirements:

- staging mail-safety is active;
- a pre-PHPMailer boundary prevents real delivery if an unexpected `wp_mail` path is reached;
- PHPMailer/SMTP transport attempt count must remain `0`;
- external application/vendor HTTP must be blocked or intercepted and counted;
- payment/order/refund calls must be blocked or otherwise fail closed before any external/payment side effect;
- no caller may disable those guards for the duration of the task.

The expected application mail-attempt count for 2.05 is `0`, because due work must be zero. Any application `wp_mail` attempt is unexpected: STOP immediately after preserving evidence and do not run remaining hooks.

Any unexpected external HTTP or payment-path attempt is also immediate STOP.

Use an existing proven staging guard mechanism or a task-private, non-persistent equivalent. Do not add permanent application source solely for this validation.

---

## 9. Selective execution method

Preferred execution path is the normal exact WP-Cron event API through WP-CLI, using **one exact hook name at a time**, for example conceptually:

`wp cron event run <EXACT_ALLOWLISTED_HOOK>`

This is authorized only after all gates above pass.

Do not use `--due-now`, `/wp-cron.php`, or an unrestricted runner.

Execute in this exact order, with a complete post-step verification after each one:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

Maximum invocation count:

- job listing expiry hook: `1`;
- employer expiry notice hook: `1`;
- candidate→job hook: `1`;
- total owned event invocations: maximum `3`.

If the environment cannot safely guard the exact WP-Cron event command while proving isolation, do **not** substitute a broad runner. A task-private direct evaluator harness may be used only to diagnose the blocker; it does not satisfy full selective-trigger validation and therefore the task must report PARTIAL rather than PASS unless the exact event path is also validated.

---

## 10. Per-hook post-execution verification

Immediately after each one exact invocation, before proceeding to the next hook, prove:

### Event row

- the invoked hook still has exactly one recurring event;
- recurrence remains `hourly`;
- args remain empty;
- next timestamp advanced/rescheduled normally relative to the pre-run row;
- no duplicate event exists;
- no unrelated cron row changed.

Do not require the next timestamp to equal a hand-calculated constant; record the core-generated value and prove it is a valid single hourly recurrence.

### Callback/runtime

- callback identity/count unchanged;
- no continuation event unexpectedly appeared;
- no legacy/vendor callback reappeared.

### Side effects

- business protected snapshot unchanged;
- no application mail attempt;
- PHPMailer/SMTP `0`;
- external application/vendor HTTP `0`;
- payment/order/refund `0`;
- Action Scheduler pending count/fingerprint unchanged;
- ID32733 still `pending/0`;
- any owned claim created transiently by the evaluator is absent/released after return.

If any invariant fails, STOP and do not run later hooks.

---

## 11. Cron fingerprint expectations

Because three exact recurring events are intentionally invoked, their event timestamps are expected to advance. Therefore the **full cron fingerprint is expected to change**.

PASS requires:

- total cron event count unchanged except for no more than the normal same-row recurring replacement semantics of the three allowlisted events;
- each allowlisted hook final event count exactly `1`;
- each allowlisted hook final recurrence `hourly` and args `[]`;
- canonical fingerprint of **all non-allowlisted cron rows** exactly unchanged before/final;
- protected overdue `action_scheduler_run_queue` cron row unchanged;
- no vendor/core/protected event executed, deleted, rescheduled or repaired;
- legacy daily remains `0/0`;
- legacy shared expiry remains `0/0`.

Report before/final exact allowlisted event timestamps and sanitized fingerprints so the intentional difference is auditable.

---

## 12. Action Scheduler hard protection

Throughout the task:

- do not invoke `action_scheduler_run_queue`;
- do not invoke any Action Scheduler runner;
- do not run due actions;
- do not claim/cancel/reschedule/clean pending actions;
- do not execute ID32733.

Before each owned hook and at final state prove:

- pending count unchanged from pre-task snapshot;
- pending fingerprint unchanged;
- ID32733 remains exact pre-task status/attempts, historically `pending/0`.

Any Action Scheduler mutation is task failure unless it can be proven to be a non-business read-only observation, which should not occur here.

---

## 13. Protected product invariants

Final state must preserve:

- candidate profiles unchanged;
- candidate auto-expiry disabled;
- candidate expiry-meta footprint unchanged;
- candidate admin/self expiry senders absent;
- job admin legacy expiry sender absent;
- legacy employer job-expiry sender absent;
- employer→candidate vendor sender `0`;
- new `candidate_alert` creation remains fail-closed;
- historical published `candidate_alert` unchanged;
- candidate→job vendor sender `0`;
- published `job_alert` unchanged;
- candidate→job continuation event `0`;
- package entitlement policy/data unchanged;
- listing expiry still consumes canonical `_job_expiry_date` only;
- employer expiry notice still uses owned Transport + `CHANNEL_JOB_EXPIRY` only;
- no WooCommerce order/payment lifecycle change.

---

## 14. Source/deployment boundary

Expected application source effects for 2.05:

- application source writes: `0`;
- application commits: `0`;
- application pushes: `0`;
- staging deploys: `0`.

Task-private guard/harness/report files outside application source may be created if necessary and must be removed after verification.

Do not turn this runtime validation into implementation of the future steady-state runner.

Do not modify Hostinger/hPanel scheduler, user crontab, systemd timers or any external scheduler in 2.05.

---

## 15. Expected safety counters

For PASS, final counters must show:

- exact owned cron event invocations: `3` total, each allowlisted hook exactly once;
- non-allowlisted cron hook executions: `0`;
- broad cron runner executions: `0`;
- `/wp-cron.php` requests: `0`;
- Action Scheduler runner/due-action executions: `0`;
- ID32733 executions: `0`;
- candidate→job continuation executions: `0`;
- application `wp_mail` attempts: `0`;
- PHPMailer/SMTP attempts: `0`;
- external application/vendor HTTP: `0`;
- payment/order/refund calls: `0`;
- business-record mutations: `0`;
- application source/deploy changes: `0`;
- production access/touch: `NO`.

Cron-row rescheduling of the three exact recurring owned events through normal WordPress cron semantics is the only intended persistent runtime mutation.

---

## 16. Failure / stop conditions

STOP immediately and report PARTIAL/FAIL if any of the following occurs:

- baseline/source/runtime mismatch;
- runtime not staging;
- mail-safety/guard unavailable;
- request-driven cron unexpectedly enabled;
- allowlisted callback/event count or identity mismatch;
- real due business work becomes non-zero;
- any non-allowlisted cron hook executes;
- any mail attempt occurs;
- PHPMailer/SMTP is reached;
- any external application/vendor HTTP occurs;
- any payment/order/refund path is reached;
- Action Scheduler state changes;
- ID32733 differs from pre-task state;
- continuation event appears;
- a claim remains after evaluator return;
- a duplicate owned recurring event appears;
- any non-allowlisted cron row changes;
- any protected business fingerprint changes;
- production is accessed/touched.

Do not “fix forward” within this task after an unexpected execution-side effect.

---

## 17. HOST_NAMESPACE_PRESSURE

Known Hostinger `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains applicable.

Use the proven namespace-free Git/filesystem/WP-CLI/runtime inspection path. If a sandbox helper fails with the known signature, apply the circuit breaker immediately and do not loop retries or use broad process kills.

---

## 18. Final report

Publish:

**Zadatak 2.05 — Bounded selective staging trigger validation for approved Raspitajse-owned cron hooks**

Report must include:

1. result PASS/PARTIAL/FAIL and exact meaning;
2. baseline/source/origin/deploy/runtime parity;
3. proof no application source change/commit/deploy occurred;
4. exact pre-execution allowlist and proof no other hook was authorized;
5. pre/final full cron event counts;
6. pre/final non-allowlisted cron fingerprint;
7. per-owned-hook callback identity/fingerprint;
8. per-owned-hook before/final event timestamp, count, recurrence, args and fingerprint;
9. exact invocation order and count;
10. read-only due-work gate result immediately before each invocation;
11. proof business due work was zero and business fingerprints stayed unchanged;
12. proof no continuation event appeared;
13. exact mail/PHPMailer/SMTP counters;
14. exact external HTTP/payment counters;
15. Action Scheduler pending count/fingerprint and ID32733 before/final;
16. claim state before/final for owned evaluators;
17. proof no non-allowlisted cron event changed or executed;
18. proof legacy daily/shared-expiry remain `0/0`;
19. final three owned scheduler states, each `1/1 hourly`;
20. production touched YES/NO, expected NO;
21. any HOST_NAMESPACE_PRESSURE/tooling warnings;
22. final readiness conclusion for implementing `EXTERNAL_SELECTIVE_ALLOWLIST_RUNNER`;
23. exactly one proposed next task if PASS, but do not create or start it.

Do not print PII, raw recipient addresses, alert query values, mail bodies, secrets or payment details.

STOP after report publication.