# Zadatak 2.07 — Close the auxiliary WP-CLI HTTP evidence gap with one fully guarded check-only observation before scheduler activation

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.06
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.07. This task is intentionally narrow. Do not activate any Hostinger/hPanel cron, crontab, systemd timer, request-driven WP-Cron or other external scheduler. Do not begin 2.08 automatically.

Publish the final report through the existing `codex-reports` workflow and STOP.

---

## 1. Context already established

Zadatak 2.06 implemented and integrated the staging-only fail-closed selective owned cron runner.

Final integrated/deployed staging SHA from 2.06:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

2.06 implementation:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`

The runner is intentionally limited to exactly these owned hooks, in this order:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

2.06 acceptance itself passed:

- fixed allowlist only;
- no arbitrary hook/path/callback passthrough;
- due-only semantics;
- deterministic sequential execution;
- overlap lock;
- per-hook timeout and whole-cycle bound;
- callback/event/environment/runtime guards;
- broad cron forbidden;
- Action Scheduler forbidden;
- continuation forbidden;
- no vendor/core cron execution;
- one naturally due staging cycle executed exactly `1/1/1`;
- real business due-work was `0`;
- business mutations `0`;
- `wp_mail=0`, PHPMailer/SMTP `0`;
- payment/order/refund mutations `0`;
- Action Scheduler pending `7` unchanged;
- protected ID32733 remained `pending/0`;
- production untouched.

2.06 was reported **PARTIAL only because of an auxiliary transport-evidence gap**:

- two mandatory environment checks and one sanitized role-count WP-CLI diagnostic were run outside the runtime HTTP guard;
- therefore those three auxiliary WP-CLI processes could not be retroactively classified as actual external-network `0`;
- no protected/business drift occurred;
- all instrumented acceptance paths showed WP HTTP API attempts intercepted before transport and actual external network `0`.

This task exists only to close that evidence gap before any scheduler activation is considered.

---

## 2. Goal

Produce one complete, fully guarded, reproducible staging observation proving that **all WP-CLI bootstraps needed for the runner's preflight/check-only path are covered by the same pre-transport HTTP guard**, with:

- zero cron hook execution;
- zero Action Scheduler execution;
- zero business mutation;
- zero mail transport;
- zero payment/order/refund mutation;
- zero actual external application/vendor network transport;
- complete transport accounting for every WP-CLI process used by the observation;
- exact protected-state parity before/final.

The intended success result is to remove the only stated reason 2.06 was PARTIAL.

Do not redesign or broadly refactor the runner if the existing implementation can be proven correct with a narrow guard/path correction or task-private fully guarded observation.

---

## 3. Execution boundary — CHECK-ONLY ONLY

For the entire 2.07 runtime validation:

- use only the runner's fixed `--check-only` mode and task-private read-only supporting observations that are fully HTTP-instrumented;
- do **not** run the normal execution mode;
- do **not** execute any of the three owned cron hooks;
- do **not** execute any continuation hook;
- do **not** call `/wp-cron.php`;
- do **not** call `wp cron event run --due-now`;
- do **not** call `wp cron event run --all`;
- do **not** execute any exact cron event directly in this task;
- do **not** run or claim Action Scheduler;
- do **not** execute ID32733;
- do **not** repair, reschedule, add, delete or advance any cron row;
- do **not** create disposable business fixtures unless an unavoidable blocker is proven and explicitly reported; default is zero fixtures and zero business writes.

If any hook execution occurs, result cannot be PASS.

---

## 4. Baseline / parity gate

Before any WordPress bootstrap, prove:

- local source staging HEAD = fresh `origin/staging` = declared baseline;
- staging deploy marker = same SHA;
- source worktree clean;
- runtime environment identifies as staging;
- Communications source/runtime parity exact;
- the two 2.06 runner/guard files exist at expected paths and are source-clean;
- `DISABLE_WP_CRON=true` remains effective;
- request-driven cron remains disabled;
- staging mail-safety is loaded/configured;
- production access/touch = NO.

If source/runtime/deploy parity is not exact, STOP and report BLOCKED/PARTIAL rather than observing stale runtime.

---

## 5. HTTP guard requirement

The core requirement is that **every WordPress/WP-CLI bootstrap process used for acceptance evidence is instrumented before WordPress can perform application/vendor HTTP transport**.

Use the existing proven guard mechanism where possible. Before each relevant WordPress bootstrap:

- establish `WP_HTTP_BLOCK_EXTERNAL=true` or the task's already-proven equivalent before WordPress bootstrap;
- install a pre-transport WordPress HTTP guard at the earliest reliable boundary available for this environment;
- count sanitized WP HTTP API attempts;
- classify/intercept them before transport;
- record `actual_external_network=0` only when deterministically established;
- fail closed on an unexpected caller/path that is not the already-understood staging-local LiteSpeed CLI shutdown purge behavior;
- do not allow the observation harness itself to create an uninstrumented WP-CLI process.

The final report must enumerate **every WordPress/WP-CLI process used in acceptance evidence** and state whether it was guarded. PASS requires all such processes to be guarded.

Git fetch/report publication traffic is control-plane traffic and should remain distinguished from application runtime network evidence.

---

## 6. Narrow source-change policy

Prefer **no source change** if the evidence gap can be closed by a task-private fully guarded observation against the integrated 2.06 runner.

If and only if the integrated 2.06 runner itself starts auxiliary WP-CLI bootstraps outside its own guard, make the smallest Raspitajse-owned correction required under `tools/` so all runner preflight/check-only WP-CLI bootstraps share the same guard boundary.

Allowed source scope if needed:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`
- at most one small Raspitajse-owned helper under `tools/` if materially necessary.

Forbidden source changes:

- WordPress core;
- WooCommerce;
- WP Job Board Pro;
- Paid Listings;
- Superio/theme code;
- Raspitajse Commerce;
- Raspitajse Communications application behavior;
- MU mail-safety behavior;
- deployment target/path logic;
- business rules;
- entitlement rules;
- cron evaluator behavior.

Do not broaden 2.07 into runner redesign.

---

## 7. Required guarded observation

Run exactly one final acceptance observation of:

`bash tools/raspitajse-staging-owned-cron-runner.sh --check-only`

under complete HTTP instrumentation.

Expected behavioral result:

- command exits successfully;
- zero owned hook execution;
- zero non-owned hook execution;
- zero cron row advancement;
- each owned hook remains exactly one callback + one hourly event;
- hooks may report `not_due`/`due` as read-only state, but **no execution may occur in check-only mode regardless of due state**;
- no Action Scheduler interaction;
- no continuation execution;
- no broad cron path;
- no arbitrary input path;
- no scheduler activation.

If the runner's check-only mode would execute due hooks, STOP and report FAIL because that violates the intended public interface established in 2.06.

---

## 8. Auxiliary diagnostics must use same guard

Any environment check, role-count diagnostic, cron inventory read, protected fingerprint read or similar WordPress/WP-CLI diagnostic used for acceptance must run through the same pre-transport guard.

Specifically close the exact 2.06 gap:

- the two mandatory environment checks that were previously uninstrumented;
- the sanitized employer-role/user-count diagnostic that was previously uninstrumented.

You may redesign the acceptance harness to avoid separate WP-CLI processes entirely if that is cleaner and safer, but do not lose the required evidence.

Do not run an unguarded `wp ...` command merely because it is read-only.

---

## 9. Cron invariants

Before and final, prove:

- full cron event count and fingerprint;
- non-allowlisted cron fingerprint;
- all three owned event rows count `1` each;
- all three owned rows remain `hourly`, interval `3600`, args `[]`;
- owned callback count/identity/fingerprint unchanged;
- candidate-job continuation event count `0` unless independently pre-existing;
- legacy daily callback/event `0/0`;
- legacy shared-expiry callback/event `0/0`;
- vendor candidate→job sender `0`;
- employer→candidate vendor sender `0`;
- candidate auto-expiry remains disabled.

Because check-only must not mutate scheduling, PASS requires the full cron canonical state to remain identical except for clock-derived presentation fields excluded from canonicalization.

---

## 10. Action Scheduler hard protection

Before/final prove:

- pending count remains `7` unless a truly pre-existing independent change is detected before execution, in which case stop and report mismatch;
- pending fingerprint unchanged;
- ID32733 remains `pending/0`;
- AS runner execution count `0`;
- due action executions `0`;
- claims/cancels/reschedules/cleanup `0`.

Any attempt by the runner/check-only observation to reach Action Scheduler is FAIL.

---

## 11. Mail / notification safety

Across all WordPress processes used for acceptance:

- application `wp_mail` attempts: expected `0`;
- PHPMailer attempts: `0`;
- SMTP attempts: `0`;
- real/uncontrolled recipients: `0`.

Keep staging mail-safety loaded. If any mail path appears unexpectedly, intercept before PHPMailer, stop and report FAIL/PARTIAL as appropriate.

Do not render or report private mail body/recipient data.

---

## 12. HTTP / network acceptance

Final evidence must clearly distinguish:

- WP HTTP API attempt count;
- guard-intercepted pre-transport count;
- known staging-local LiteSpeed CLI shutdown purge attempts, if present;
- unexpected HTTP attempt count;
- actual external network request count.

PASS requires:

- every application-runtime WP HTTP attempt is accounted for;
- every such attempt is intercepted before transport or otherwise proven non-external by a deterministic guard;
- unexpected attempts `0`;
- actual external application/vendor network requests `0`;
- no uninstrumented WP-CLI bootstrap remains in the acceptance evidence set.

Do not claim zero network merely because a command was read-only.

---

## 13. Payment/order/refund hard guard

Across all acceptance processes:

- payment transport/charge attempts `0`;
- order lifecycle mutation calls `0`;
- refund lifecycle mutation calls `0`;
- order/refund fingerprints unchanged.

No payment test is authorized in 2.07.

---

## 14. Protected business-state invariants

Before/final prove unchanged, sanitized only:

- candidates count/status/fingerprint;
- candidate expiry-meta footprint;
- historical published `candidate_alert` count/fingerprint;
- published `job_alert` count/fingerprint;
- job listing count/status/fingerprint;
- employers fingerprint;
- WPJBP employer-user count/fingerprint using the corrected proven role selector from 2.06;
- package/entitlement count/status/fingerprint;
- WooCommerce order status/fingerprint;
- refund count/fingerprint;
- all owned claim-family counts return to exact before state;
- candidate-alert creation remains fail-closed;
- package entitlement behavior unchanged;
- listing expiry source remains canonical `_job_expiry_date` only;
- employer expiry notice transport remains owned SenderPolicy/Transport with `CHANNEL_JOB_EXPIRY`.

No raw emails, user IDs, order IDs, tokens or PII in the report.

---

## 15. Runner interface and fail-closed regression

Re-prove without executing hooks:

- no args => normal mode exists but is **not invoked in 2.07**;
- only `--check-only` is accepted for this task's runtime acceptance;
- unexpected positional arguments fail;
- unsupported options fail;
- arbitrary hook input impossible;
- fixed internal allowlist remains exactly three hooks;
- fixed execution order unchanged;
- lock and timeout configuration unchanged unless a narrowly justified evidence-only fix is necessary;
- no code path to `--due-now`, `--all`, `/wp-cron.php`, Action Scheduler runner, continuation hook or vendor/core cron.

Use static inspection/private mocks where needed; do not execute business hooks.

---

## 16. Source quality if code changes are necessary

If source changes are made:

- `bash -n` changed shell files;
- `php -l` changed PHP files;
- `git diff --check`;
- preserve executable mode of the shell runner;
- no secrets/PII/debug dumps/hard-coded private IDs/emails;
- no production URLs/paths except existing safe environment checks where already canonical;
- one focused feature branch from exact baseline;
- integrate to staging only after all acceptance criteria pass;
- deploy staging only;
- final source/origin/staging/deploy/runtime parity exact.

If no source change is necessary, do not create a no-op application commit merely to record the task.

---

## 17. Scheduler activation remains forbidden

Do not create/modify/enable:

- Hostinger/hPanel scheduled tasks;
- user crontab;
- system cron;
- systemd timers;
- HTTP cron services;
- GitHub Actions scheduler;
- WordPress request spawning.

2.07 only closes the evidence gap. Scheduler activation, if later authorized, belongs to a separate READY task.

---

## 18. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` still applies.

If encountered:

- apply the documented circuit breaker immediately;
- do not retry the same sandbox helper repeatedly;
- continue with proven namespace-free Git/filesystem/WP-CLI methods;
- do not use broad process kills;
- if a bounded task-private child stalls, inspect and terminate only that exact child/process group if its documented timeout is exceeded.

---

## 19. Acceptance criteria

PASS requires all of the following:

1. Exact baseline/deploy/runtime parity gate passes.
2. Final `--check-only` observation runs exactly once.
3. Cron hook execution count is `0` across all hooks.
4. No cron row is advanced/rescheduled/added/deleted by 2.07.
5. Every acceptance-evidence WP-CLI bootstrap is HTTP-instrumented.
6. The exact three previously uninstrumented diagnostic needs are now covered by the guard or eliminated by a fully guarded consolidated observation.
7. WP HTTP attempts are fully accounted for.
8. Actual external application/vendor network requests `0`.
9. `wp_mail=0`, PHPMailer/SMTP `0`.
10. Payment/order/refund mutations `0`.
11. Action Scheduler executions/mutations `0`; pending/fingerprint unchanged; ID32733 `pending/0`.
12. Protected business fingerprints unchanged.
13. Runner fixed allowlist/interface/fail-closed properties unchanged.
14. Scheduler activation `0`.
15. Production access/touch `NO`.
16. Temporary task artifacts cleaned exactly.

If any acceptance WP-CLI process remains unguarded, result must not be PASS.

---

## 20. Final report

Publish:

**Zadatak 2.07 — Close the auxiliary WP-CLI HTTP evidence gap with one fully guarded check-only observation before scheduler activation**

Report must include:

- PASS/PARTIAL/FAIL and exact meaning;
- baseline and final staging SHA;
- whether source changes were necessary;
- exact changed files/diffstat if any;
- exact count of WordPress/WP-CLI processes used for acceptance;
- guarded vs unguarded process count;
- `--check-only` invocation count;
- total cron hook execution count;
- before/final cron fingerprints;
- Action Scheduler pending/fingerprint + ID32733;
- mail/SMTP counters;
- WP HTTP API attempt/intercept/unexpected/actual-external counters;
- payment/order/refund counters;
- protected business-state parity;
- cleanup proof;
- production NO;
- whether the 2.06 evidence gap is fully closed;
- exactly one proposed next task, but do not create or start it.

If PASS, the natural next task may evaluate/authorize **staging scheduler activation for the already-proven selective runner**, but do not activate anything in 2.07.

STOP after report publication and verification.
