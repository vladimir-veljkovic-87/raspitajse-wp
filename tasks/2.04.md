# Zadatak 2.04 — Read-only audit of steady-state staging WP-Cron trigger readiness after expiry scheduler cleanup

Status: READY
Baseline: 544a31171132a3ce95323162df2519ac0135840a
Previous task: 2.03
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting runtime. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`544a31171132a3ce95323162df2519ac0135840a`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.04. This is a **strictly read-only audit**. Do not create a feature branch unless repository policy mechanically requires one for report generation; do not modify application source, WordPress options, cron storage, database business state, Action Scheduler state, mail state, server scheduler state or deployment state.

Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.05 automatically.

---

## 1. Context already established

Zadatak 2.03 completed PASS and left the expiry/alert scheduler state intentionally simplified:

- legacy `wp_job_board_pro_email_daily_notices`: callback count `0`, event count `0`;
- legacy `wp_job_board_pro_check_for_expired_jobs`: callback count `0`, event count `0`;
- owned job-status expiry evaluator: exactly `1` callback + `1 hourly` event;
- owned employer job-expiry notification evaluator: exactly `1` callback + `1 hourly` event;
- owned candidate→job evaluator: exactly `1` callback + `1 hourly` event;
- candidate→job continuation event: `0`;
- candidate auto-expiry remains disabled;
- both legacy alert senders remain absent;
- new employer→candidate `candidate_alert` creation remains retired/fail-closed;
- Action Scheduler pending count remained `7`;
- ID32733 remained `pending/0`;
- production remained untouched.

Historical staging evidence also shows `DISABLE_WP_CRON=true`, so browser/request-triggered WordPress cron spawning is not the steady-state trigger mechanism.

This task does **not** run cron. Its purpose is to determine whether a future narrow steady-state trigger validation can be authorized safely, and exactly what that authorization boundary must be.

---

## 2. Goal

Produce a complete, sanitized readiness decision for staging WP-Cron execution after the expiry scheduler cleanup.

The report must answer:

1. What recurring/single WP-Cron events currently exist on staging?
2. Which are overdue, current, future, duplicated or orphaned?
3. Which hooks have callbacks, and what would each callback do if run?
4. Which due hooks are Raspitajse-owned and already classified versus vendor/core/unclassified?
5. Is any broad runner (`wp cron event run --due-now`, HTTP wp-cron trigger, server cron calling wp-cron broadly, or equivalent) safe now? The default assumption is **NO until proven otherwise**.
6. Is there an existing external/server trigger for staging? If so, what exact command/URL/interval does it use and is it active?
7. What exact recovery/safety prerequisites must be satisfied before any later execution task?
8. What is the narrowest safe next validation slice?

Final classification must be exactly one of:

- `READY_FOR_SELECTIVE_VALIDATION`
- `BLOCKED`
- `NOT_READY`

Do not implement the runner or execute any scheduled hook in 2.04.

---

## 3. Strict zero-execution boundary

Do **not** execute any of the following, directly or indirectly:

- `wp cron event run ...`;
- `wp cron event run --due-now`;
- `wp cron test` if it causes spawning/execution rather than pure inspection;
- HTTP requests to `wp-cron.php`;
- `do_action()` for scheduled business hooks;
- owned job-status expiry evaluator;
- owned employer job-expiry notification evaluator;
- owned candidate→job evaluator;
- candidate→job continuation hook;
- Action Scheduler runner, queue runner or due actions;
- ID32733;
- mail sender callbacks;
- payment callbacks;
- external/vendor application callbacks.

Do not temporarily toggle `DISABLE_WP_CRON`.

Do not add, delete, reschedule, unschedule or repair any cron event in this task.

Inspection commands must be read-only.

---

## 4. Current application/runtime parity gate

Before runtime audit, prove:

- local/source staging HEAD = `origin/staging` = declared baseline;
- staging deploy marker = same SHA;
- inspected Raspitajse Communications source/runtime file parity;
- WordPress environment = staging;
- production access/touch = NO.

If source/runtime parity is not exact, report `BLOCKED` and do not continue into a readiness conclusion based on stale runtime.

---

## 5. Full WP-Cron inventory

Capture a sanitized deterministic inventory of **all currently scheduled WP-Cron event rows** without running them.

For every unique hook report at minimum:

- hook name;
- event count;
- recurrence name or single-event status;
- interval if recurring;
- next timestamp;
- next timestamp as UTC and WordPress-site-calendar time;
- relative state: overdue / due-now / future;
- oldest overdue age where applicable;
- args shape/count only, without printing sensitive values;
- callback count on the hook after normal plugin bootstrap;
- callback identities in sanitized class/function form;
- whether duplicate event rows exist for the same logical recurring hook.

Provide:

- total cron event count;
- total unique hook count;
- count of overdue events;
- count of recurring vs single events;
- canonical full cron fingerprint before/final.

Do not print secrets, tokens, emails, URLs containing secrets, raw serialized option payloads or business PII.

---

## 6. Classify every scheduled hook

For every currently scheduled hook, classify it as one of:

- `OWNED_APPROVED` — Raspitajse-owned hook whose behavior and invariants are already classified and bounded;
- `CORE_LOW_RISK` — WordPress core maintenance hook with understood standard purpose and no Raspitajse custom side-effect concern;
- `VENDOR_CLASSIFIED` — vendor hook whose business purpose/side effects are already explicitly classified in prior work;
- `UNCLASSIFIED` — side effects or business purpose not yet sufficiently proven;
- `PROTECTED_DO_NOT_RUN` — known protected or dangerous path that must not be executed by a broad trigger.

For each hook summarize business/technical effect if executed:

- read-only;
- business-state mutation;
- mail/notification;
- external HTTP/vendor call;
- payment/order mutation;
- Action Scheduler queue work;
- cleanup/deletion;
- unknown.

If a hook delegates into Action Scheduler, WooCommerce maintenance, vendor telemetry/update checks, cleanup, mail, payment, external HTTP or an unclassified custom callback, make that explicit.

Do not assume a WordPress/Woo/vendor cron hook is safe merely because it is common.

---

## 7. Re-prove the three owned hourly schedulers

Without executing them, prove current callback/event identity and configuration for:

### A. Job-status expiry

`raspitajse_job_listing_expiry_evaluator`

Expected:

- callback count `1`;
- recurring event count `1`;
- recurrence `hourly`;
- batch size `50`;
- claim TTL `600`;
- evaluator consumes canonical `_job_expiry_date` only;
- no mail path.

### B. Employer job-expiry notice

`raspitajse_employer_job_expiry_notice_evaluator`

Expected:

- callback count `1`;
- recurring event count `1`;
- recurrence `hourly`;
- due window remains exactly tomorrow in site calendar;
- max attempts `3`;
- backoff remains `15/60` minutes;
- claim TTL `600`;
- send path remains only owned Transport + `CHANNEL_JOB_EXPIRY`.

### C. Candidate→job alert evaluator

`raspitajse_candidate_job_alert_evaluator`

Expected:

- callback count `1`;
- recurring event count `1`;
- recurrence `hourly`;
- continuation scheduled event count `0` unless independently pre-existing;
- vendor candidate→job sender remains `0`.

Capture callback/event fingerprints and compare to 2.03 where possible.

---

## 8. Due-work / backlog assessment for owned hooks

Read-only calculate what each owned evaluator **would currently select** if invoked now, without calling the evaluator itself and without mutating claims/state.

Report sanitized counts only:

### Job-status expiry

- currently published jobs with valid canonical `_job_expiry_date` strictly before today;
- count that would fit first batch (`<=50`);
- whether backlog exceeds one batch.

### Employer job-expiry notice

- currently published jobs whose canonical expiry date is tomorrow;
- delivered/current-revision count;
- retryable-failed current-revision count;
- terminal-failed current-revision count;
- never-attempted count;
- invalid/missing canonical employer-recipient count;
- no recipient values.

### Candidate→job

- published `job_alert` count;
- count currently due by owned cadence if derivable read-only;
- count with any existing active claim/continuation state if applicable;
- do not render messages or evaluate mail delivery.

The point is to know whether a future trigger would be a no-op, a bounded mutation, or a user-visible communication event.

---

## 9. WP-Cron spawning/trigger configuration

Read-only inspect the active staging trigger configuration.

At minimum determine:

- effective `DISABLE_WP_CRON` value;
- `ALTERNATE_WP_CRON` if defined;
- `WP_CRON_LOCK_TIMEOUT` if defined;
- whether WordPress request spawning is therefore enabled/disabled;
- whether a repository/deployment script contains an explicit cron runner;
- whether the staging account exposes a user-level crontab or other directly inspectable scheduler entry that calls WordPress cron.

If a user-level/system scheduler is inspectable safely, report only:

- whether an entry exists;
- cadence;
- sanitized command shape;
- whether it targets staging specifically;
- whether it is broad (`--due-now` / wp-cron.php) or selective.

Do not modify crontab, hPanel scheduler, systemd timers or any server scheduler.

Do not access production scheduler configuration.

If Hostinger/hPanel scheduler truth is not visible from the shell/repository, state `UNKNOWN` rather than guessing.

---

## 10. Broad-runner safety analysis

Explicitly evaluate these potential trigger modes without executing them:

1. HTTP request to `/wp-cron.php`;
2. WP-CLI `wp cron event run --due-now`;
3. WP-CLI run of one exact hook;
4. direct invocation of one owned evaluator through a task-private harness;
5. server cron invoking a narrow wrapper that only runs allowlisted owned hooks.

For each mode classify:

- `SAFE_FOR_FUTURE_BOUNDED_TEST`
- `UNSAFE`
- `UNKNOWN`

Explain why.

A broad runner is **UNSAFE** if any currently due/overdue hook is `UNCLASSIFIED` or `PROTECTED_DO_NOT_RUN`, or if it could transitively trigger Action Scheduler/vendor/external/payment/mail work outside an approved fixture boundary.

Do not recommend a broad runner merely because the three Raspitajse hooks are individually bounded.

---

## 11. Action Scheduler interaction audit

Read-only prove current Action Scheduler protected state:

- pending count;
- sanitized pending fingerprint;
- ID32733 status/attempts;
- whether any WP-Cron event/callback can trigger Action Scheduler queue processing;
- whether that event is scheduled/due/overdue;
- whether a broad WP-Cron trigger could therefore execute pending AS work.

Expected historical state:

- pending count `7`;
- ID32733 `pending/0`.

Do not run, claim, cancel, reschedule or inspect payload content containing PII/secrets beyond what is necessary for sanitized classification.

If a broad WP-Cron trigger can reach Action Scheduler, that is a mandatory safety-gate finding.

---

## 12. Mail / network / payment side-effect reachability

Read-only identify currently scheduled hooks that could reach:

- `wp_mail` / owned Transport;
- PHPMailer/SMTP;
- WordPress HTTP API/external vendor requests;
- WooCommerce order/payment/refund paths;
- data deletion/cleanup.

Report hook names and high-level path only.

Do not send mail or perform HTTP/payment calls.

For owned employer expiry notice, distinguish:

- application `wp_mail` attempt through Transport;
- staging mail-safety interception;
- real SMTP delivery.

Do not treat interception as authorization to run it against real staging business records.

---

## 13. Recovery and rollback prerequisites for a later validation task

Define the exact safety prerequisites a future trigger-validation task must satisfy before any execution is authorized.

At minimum decide whether the future task needs:

- exact baseline/deploy parity gate;
- pre/post full cron fingerprint;
- pre/post Action Scheduler fingerprint + ID32733;
- pre/post candidate/job/alert/package fingerprints;
- no-real-mail guard;
- HTTP/network guard;
- payment guard;
- task-private disposable fixture only;
- exact hook allowlist;
- maximum number of hook invocations;
- exact cleanup verification;
- claim cleanup checks;
- stop-on-unexpected-callback behavior;
- prohibition on broad `--due-now`.

If any additional prerequisite is needed, specify it.

---

## 14. Decision on steady-state trigger architecture

Based on evidence, state the preferred architecture for staging steady-state triggering, but do not implement it.

Choose one:

- `EXTERNAL_BROAD_WP_CRON`
- `EXTERNAL_SELECTIVE_ALLOWLIST_RUNNER`
- `REQUEST_DRIVEN_WP_CRON`
- `OTHER`
- `BLOCKED_PENDING_MORE_CLASSIFICATION`

The recommendation must account for:

- `DISABLE_WP_CRON`;
- unclassified/protected scheduled hooks;
- the three owned hourly evaluators;
- Action Scheduler reachability;
- future maintainability/self-healing schedules;
- avoiding demo/test-only special cases.

Do not propose changing WooCommerce standard lifecycle merely to make cron simpler.

---

## 15. Protected business/runtime invariants

Before/final prove unchanged:

- candidates count/status/fingerprint;
- candidate expiry-meta footprint;
- historical published `candidate_alert` count/fingerprint;
- published `job_alert` count/fingerprint;
- job count/status/fingerprint;
- employer/employer-user count fingerprint, sanitized;
- job-package/entitlement count/status/fingerprint;
- candidate-alert create remains fail-closed;
- employer→candidate vendor sender remains `0`;
- candidate→job vendor sender remains `0`;
- legacy daily callback/event remains `0/0`;
- legacy shared-expiry callback/event remains `0/0`;
- three owned hourly callback/event pairs remain `1/1`;
- candidate→job continuation event remains `0` unless pre-existing state differs;
- Action Scheduler pending count/fingerprint unchanged;
- ID32733 remains `pending/0`.

No fixtures are required for 2.04. Prefer zero database writes of any kind.

---

## 16. Safety counters

Expected for the entire task:

- source writes: `0`;
- application commits/pushes/deploys: `0`;
- WordPress option writes: `0`;
- cron add/delete/reschedule: `0`;
- cron hook executions: `0`;
- Action Scheduler mutations/executions: `0`;
- `wp_mail`: `0`;
- PHPMailer/SMTP: `0`;
- external application/vendor HTTP: `0`;
- payment calls: `0`;
- business-data mutations: `0`;
- production access/touch: `NO`.

If any inspection helper unexpectedly mutates runtime, stop and report FAIL/PARTIAL rather than continuing.

---

## 17. HOST_NAMESPACE_PRESSURE

Known Hostinger `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` still applies.

Use proven namespace-free read-only Git/filesystem/WP-CLI/database inspection paths where needed. If a sandbox helper fails with the known signature, do not retry indefinitely and do not use broad process kills.

---

## 18. Final report

Publish:

**Zadatak 2.04 — Read-only audit of steady-state staging WP-Cron trigger readiness after expiry scheduler cleanup**

Report must include:

1. result PASS/PARTIAL/FAIL and exact meaning;
2. final readiness classification: `READY_FOR_SELECTIVE_VALIDATION`, `BLOCKED` or `NOT_READY`;
3. baseline/source/deploy/runtime parity proof;
4. full sanitized WP-Cron inventory and fingerprint;
5. hook-by-hook classification table;
6. overdue/due/future counts and backlog summary;
7. exact three owned hourly callback/event proofs;
8. read-only due-work counts for the three owned evaluators;
9. effective WP-Cron constants and request-spawn state;
10. discovered external/server trigger evidence or explicit UNKNOWN;
11. broad-runner safety analysis for the five trigger modes;
12. Action Scheduler reachability and protected-state proof;
13. mail/network/payment side-effect reachability;
14. exact future validation recovery/guard prerequisites;
15. preferred steady-state trigger architecture decision;
16. protected business/runtime fingerprints before/final;
17. safety counters proving zero execution/mutation;
18. production accessed/touched NO;
19. exactly one proposed next task, if and only if evidence supports it.

If the evidence supports a next step, propose only a narrow task such as:

**Zadatak 2.05 — Bounded selective staging trigger validation for approved Raspitajse-owned cron hooks**

The proposal must explicitly forbid broad due-now execution and must remain uncreated/unstarted.
