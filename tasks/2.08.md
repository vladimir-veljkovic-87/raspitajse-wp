# Zadatak 2.08 — Evaluate and, only under a new explicit READY contract, configure the external selective staging scheduler for the guarded owned-cron runner

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.07
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, inspecting scheduler state, or changing anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.08. This is a staging operational/scheduler task, not a broad cron cleanup and not an application refactor. Do not begin 2.09 automatically.

Publish the final report through the existing `codex-reports` workflow and STOP.

---

## 1. Context already established

Zadatak 2.06 implemented the staging-only fail-closed selective runner:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`

Zadatak 2.07 closed the only auxiliary HTTP-evidence gap and completed PASS with no source or scheduler mutation.

The runner's fixed internal allowlist is exactly, in this order:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

2.07 re-proved:

- all three owned hooks remain exactly one callback + one hourly event;
- runner `--check-only` executes zero hooks even if an owned row is due;
- full guarded acceptance WP-CLI process accounting was `7 guarded / 0 unguarded`;
- actual external application/vendor network requests `0`;
- `wp_mail=0`, PHPMailer/SMTP `0`;
- payment/order/refund mutations `0`;
- Action Scheduler pending `7`, protected ID32733 `pending/0`;
- business mutations `0`;
- scheduler activation `0`;
- production untouched.

Current 2.07 baseline fingerprints/invariants:

- full WP-Cron fingerprint: `da348772f0fe9f307c39c1cb4e526719bff6b6e5e47dd75b20614c43c59579ca`;
- non-allowlisted cron fingerprint: `875972ba451addb368e78d86b4f3df94e7627037911ac617e620c2cdba6c1e71`;
- callback/event contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- Action Scheduler pending fingerprint: `1e06ffd60f1323db49bf194e8a3c8cdd7ea055c09a9554cda01c33aff1f32143`;
- protected business aggregate fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- runner SHA-256: `6237e827c8fd34f3d91b8ea442ecb827e2a4a62992c57994188a8a38da729ce4`;
- guard SHA-256: `c784b87500ce7af00be71e2b4aee8f35c3aec8a80175f72c3e0d5d6047720b8d`.

`DISABLE_WP_CRON=true` is intentional and must remain enabled. Broad request-driven or broad server-triggered WordPress cron remains forbidden.

---

## 2. Goal

Evaluate the staging account's real external scheduler/control-plane capability and, **only if every gate in this task passes**, configure exactly one staging scheduler entry that invokes the already-validated selective runner.

The desired steady-state architecture is:

- one external staging scheduler entry;
- cadence exactly every 15 minutes (`*/15 * * * *`) or the provider's semantically identical 15-minute interval;
- one fixed absolute runner command;
- runner due-only behavior decides whether any of the three owned hourly rows execute;
- no arbitrary hook input;
- no `/wp-cron.php`;
- no `wp cron event run --due-now` or `--all`;
- no Action Scheduler runner;
- no continuation hook;
- no vendor/core cron execution;
- no production scheduler or runtime access.

This task may leave the scheduler enabled at the end **only after** the two-phase activation proof below passes.

---

## 3. Exact scheduler command

The final enabled scheduler command must be equivalent to the following fixed absolute command and must not contain arbitrary parameters, substitutions, pipes, curl/wget, inline PHP, broad WP-CLI commands, or user-controlled input:

`/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh`

For the first verification phase only, the same scheduler entry must temporarily use:

`/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh --check-only`

If the provider requires a different canonical bash path, prove the exact executable path first and use only that fixed path. Do not wrap the command in `/wp-cron.php`, a web request, a generic hook argument, or a broad cron runner.

Do not configure email delivery of cron output to any real recipient. Prefer provider-native execution history/status for observation. Do not persist raw business/PII output.

---

## 4. Scheduler/control-plane discovery gate

Before any scheduler mutation, read-only inspect the scheduler interfaces actually available to the staging hosting account.

Allowed preferred control planes:

1. Hostinger/hPanel scheduled task/cron interface, if directly and safely observable/mutable from the permitted environment;
2. an account-level user cron facility clearly owned by this staging account, if directly observable and safely mutable.

Do **not** substitute any of the following if the intended hosting scheduler is unavailable:

- root/system crontab;
- systemd timers;
- GitHub Actions schedule;
- external web-cron service;
- HTTP request to `wp-cron.php`;
- WordPress request-driven spawning;
- Action Scheduler as the trigger boundary;
- an ad-hoc long-running daemon/loop/nohup process.

If no authorized scheduler control plane is safely observable and mutable, do not improvise. Report `PARTIAL` with classification `BLOCKED_NO_AUTHORIZED_SCHEDULER_CONTROL_PLANE`, include the exact sanitized command/cadence that a human would need to configure, and STOP with zero scheduler mutation.

Do not request, print, store, or expose hosting credentials, API tokens, passwords, session cookies or secrets.

---

## 5. Existing scheduler inventory and conflict gate

Before mutation, capture a sanitized inventory of staging-account scheduler entries that could call WordPress, WP-CLI, this repository, or the owned runner.

For each relevant visible entry report only:

- enabled/disabled state;
- cadence;
- sanitized command shape/path;
- whether it targets staging;
- classification: exact desired runner / broad WordPress cron / unrelated / unknown.

Never print secrets or unrelated private command arguments.

Rules:

- If no relevant entry exists: proceed to create exactly one new staging entry.
- If exactly one disabled entry already matches the desired selective runner and cadence: it may be reused after all gates pass.
- If exactly one enabled entry already matches the desired selective runner and cadence: do not create a duplicate; validate it through the two-phase proof if the control plane allows safe temporary check-only conversion and exact restoration.
- If any broad staging WordPress cron trigger exists (`wp-cron.php`, `--due-now`, `--all`, Action Scheduler runner, or equivalent), do **not** silently disable/delete/replace it in 2.08. Report `PARTIAL` with classification `BLOCKED_CONFLICTING_BROAD_SCHEDULER` and STOP with zero mutation to that entry.
- If duplicate or ambiguous relevant entries exist, STOP and report `PARTIAL` rather than guessing ownership.

Unrelated scheduler entries are read-only and must not be changed.

---

## 6. Exact pre-activation parity and state gate

Before creating/enabling/editing the scheduler entry, prove all of the following:

- local source staging HEAD = fresh `origin/staging` = declared baseline;
- deploy marker = same SHA;
- source worktree clean;
- runtime environment = staging;
- runner and guard files are tracked/source-clean and match the 2.07 SHA-256 values;
- Communications source/runtime parity exact;
- `DISABLE_WP_CRON=true` remains effective;
- staging mail-safety loaded/configured;
- `doing_cron` clear;
- full WP-Cron fingerprint equals the 2.07 baseline value above;
- non-allowlisted cron fingerprint equals the 2.07 baseline value above;
- callback/event contract fingerprint equals the 2.07 baseline value above;
- Action Scheduler pending count is `7` and pending fingerprint equals the 2.07 baseline value;
- ID32733 is exactly `pending/0`;
- protected business aggregate fingerprint equals the 2.07 baseline value;
- candidate-job continuation rows `0`;
- legacy daily callback/event `0/0`;
- legacy shared-expiry callback/event `0/0`;
- vendor candidate→job sender `0`;
- employer→candidate vendor sender `0`;
- candidate auto-expiry remains disabled.

If any of these differ before activation, do not normalize or repair state in this task. Report `PARTIAL` with classification `BLOCKED_PREACTIVATION_STATE_DRIFT` and STOP.

All WordPress/WP-CLI diagnostics used for acceptance must be guarded before bootstrap using the 2.07-proven HTTP/runtime guard. No unguarded read-only WP-CLI shortcut is allowed.

---

## 7. Real-business due-work gate

Immediately before each scheduler phase, fully guarded and read-only prove:

- published expired job listings due for status transition: `0`;
- published jobs due for employer expiry notice/retry: `0`;
- published candidate-job alerts currently due: `0`;
- owned claim families all `0`;
- continuation rows `0`.

If any real owned business work is due, do not activate or switch the scheduler into normal mode. Report `PARTIAL` with classification `BLOCKED_REAL_DUE_BUSINESS_WORK` and leave/restore scheduler state to its exact pre-task state.

Do not create business fixtures merely to make scheduler activation pass.

---

## 8. Two-phase scheduler activation — mandatory

### Phase A — scheduled check-only proof

After all gates pass:

1. Create or edit exactly one staging scheduler entry at exactly 15-minute cadence.
2. Configure it temporarily with the exact `--check-only` command from section 3.
3. Keep every unrelated scheduler entry untouched.
4. Observe **one natural scheduler-fired execution** through provider-native history/status or another deterministic control-plane observation.
5. Do not manually invoke the scheduler entry as a substitute for the natural fire.
6. The observed run must show:
   - runner mode `check` / `NOOP` or equivalent successful check-only result;
   - hook executions `0`;
   - no cron row advancement;
   - actual external application/vendor network `0`;
   - `wp_mail=0`, PHPMailer/SMTP `0`;
   - payment/order/refund mutation `0`;
   - Action Scheduler execution/mutation `0`;
   - protected business state unchanged.

Observation window: at most 20 minutes. Do not wait indefinitely.

If the natural check-only fire cannot be deterministically observed within the bounded window, restore/remove only the exact entry created/modified by this task to its pre-task state, verify rollback, report `PARTIAL` with classification `BLOCKED_SCHEDULER_FIRE_NOT_OBSERVED`, and STOP.

### Phase B — normal selective runner proof

Only after Phase A passes:

1. Re-run the full guarded parity/protected-state and real-due-work gates immediately.
2. Change only that exact scheduler entry from `--check-only` to the fixed zero-argument normal runner command.
3. Keep cadence exactly 15 minutes.
4. Observe **one natural scheduler-fired normal execution** within a maximum 20-minute window.
5. Do not use `/wp-cron.php`, `--due-now`, `--all`, a manual exact event call, or a manual runner invocation as a substitute for this scheduler-fire proof.

Acceptance of the natural normal fire:

- runner starts in normal `run` mode;
- it executes only fixed allowlisted owned hooks that are actually due at that moment, each at most once and in fixed order;
- `executed_hooks` may be `0..3` depending on due timestamps;
- real business due-work must remain `0`, so business mutations/messages remain `0`;
- if owned recurring timestamps advance, only timestamps for hooks actually executed may change and only by normal WordPress recurrence behavior;
- non-allowlisted cron fingerprint must remain identical;
- no Action Scheduler execution;
- no continuation execution;
- no mail/SMTP;
- no payment/order/refund mutation;
- actual external application/vendor network `0`;
- protected business fingerprint unchanged.

If Phase B natural fire cannot be observed or any acceptance invariant fails, disable/remove or restore only this task's scheduler entry to its exact pre-task state, prove rollback, report PARTIAL/FAIL as appropriate, and STOP. Do not leave an unverified scheduler enabled.

---

## 9. Final enabled scheduler state

PASS requires exactly one enabled staging scheduler entry at the end with:

- cadence: every 15 minutes;
- command: fixed zero-argument selective runner only;
- staging target only;
- no output email recipient;
- no arbitrary parameters;
- no duplicate runner scheduler;
- no broad WordPress cron scheduler created by this task;
- no production scheduler change.

The scheduler may remain enabled only after both Phase A and Phase B natural-fire proofs pass.

Report a sanitized scheduler-entry fingerprint/identifier if the provider exposes one, but do not report secrets or opaque credentials.

---

## 10. Cron and protected-state final proof

Final proof must include:

- total WP-Cron event count;
- full cron fingerprint before/final;
- non-allowlisted fingerprint before/final;
- callback/event contract fingerprint before/final;
- all three owned callback/event pairs remain `1/1`, hourly, 3600, empty args;
- exact owned hook timestamps before/final and whether each changed because the natural Phase B runner executed it;
- continuation rows `0`;
- legacy daily `0/0`;
- legacy shared expiry `0/0`;
- vendor alert senders `0`;
- candidate auto-expiry disabled;
- Action Scheduler pending count/fingerprint unchanged;
- ID32733 `pending/0`;
- protected business aggregate fingerprint unchanged;
- all owned claims returned to exact before state.

Any non-allowlisted cron mutation is FAIL unless proven to be an independent external change that occurred before this task's mutation gate, in which case rollback scheduler state and report PARTIAL rather than masking it.

---

## 11. Mail / HTTP / payment safety

Across every guarded WordPress process and both natural scheduler fires:

- application `wp_mail`: `0`;
- PHPMailer: `0`;
- SMTP: `0`;
- uncontrolled recipients: `0`;
- WP HTTP attempts fully accounted for;
- every application/vendor HTTP attempt intercepted before transport by the existing guard;
- unexpected HTTP attempts: `0`;
- actual external application/vendor network requests: `0`;
- payment/charge attempts: `0`;
- order lifecycle mutations: `0`;
- refund lifecycle mutations: `0`.

Scheduler control-plane API/UI traffic is operational control-plane traffic and must be reported separately from WordPress application runtime network evidence.

---

## 12. Rollback discipline

Before any scheduler mutation, record the exact sanitized pre-task state of the one scheduler entry to be created/reused/modified.

On any failure after mutation:

- if this task created the entry: disable/delete exactly that entry and verify it is absent/disabled;
- if this task modified a pre-existing exact selective-runner entry: restore its exact prior enabled state, command and cadence;
- never alter unrelated scheduler entries;
- never use broad process kills;
- verify no scheduler process/lock remains unexpectedly active;
- re-prove protected business, cron and Action Scheduler state after rollback.

Do not leave a partially configured or check-only scheduler entry enabled after a failed task unless that exact enabled state existed before the task.

---

## 13. Source-change boundary

Default expected application/source changes: `0`.

Do not modify the runner, guard, Communications, Commerce, vendor plugins, themes, WordPress core or deployment scripts in 2.08.

If scheduler activation exposes a real runner defect requiring source changes, rollback/disable the scheduler entry, report `PARTIAL`, and propose a separate source-fix task. Do not broaden 2.08 into another runner implementation task.

Do not create a no-op application commit merely to record scheduler state.

---

## 14. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` still applies.

If encountered:

- apply the circuit breaker once;
- do not repeatedly retry the same sandbox helper;
- continue with proven namespace-free Git/filesystem/WP-CLI/control-plane methods;
- do not use broad process kills;
- bounded scheduler observation windows remain 20 minutes per phase.

Host pressure is not permission to skip scheduler ownership, rollback, HTTP guard or protected-state gates.

---

## 15. Acceptance criteria

PASS requires all of the following:

1. Exact baseline/source/deploy/runtime gate passes.
2. Authorized staging scheduler control plane is observable and mutable.
3. No conflicting broad/duplicate scheduler entry blocks activation.
4. All 2.07 cron/AS/protected fingerprints match before mutation.
5. Real owned due business work is `0` immediately before both phases.
6. Exactly one 15-minute staging scheduler entry is used.
7. Phase A natural `--check-only` fire is observed and executes `0` hooks.
8. Phase A leaves cron/protected state unchanged.
9. Phase B natural normal runner fire is observed.
10. Phase B executes only due allowlisted hooks `0..3`, each at most once, with business mutations/messages `0`.
11. Non-allowlisted cron state is unchanged.
12. Action Scheduler pending fingerprint unchanged and ID32733 remains `pending/0`.
13. `wp_mail=0`, PHPMailer/SMTP `0`.
14. Actual external application/vendor network requests `0`.
15. Payment/order/refund mutations `0`.
16. Protected business fingerprint unchanged.
17. Exactly one verified normal selective scheduler entry remains enabled at 15-minute cadence.
18. No broad WP-Cron/request-driven scheduler is enabled by this task.
19. Production access/touch is `NO`.
20. Any task-private artifacts are cleaned.

Result guidance:

- `PASS`: scheduler is configured, both natural-fire phases passed, and the final selective entry remains enabled.
- `PARTIAL`: safe blocker prevented activation/verification, or scheduler was rolled back cleanly after an incomplete proof.
- `FAIL`: an unsafe or unauthorized mutation/execution occurred, rollback failed, protected state changed, or forbidden production/broad-runner behavior occurred.

---

## 16. Final report

Publish:

**Zadatak 2.08 — Evaluate and, only under a new explicit READY contract, configure the external selective staging scheduler for the guarded owned-cron runner**

Report must include:

1. result PASS/PARTIAL/FAIL and exact classification;
2. baseline/source/origin/deploy/runtime parity;
3. scheduler control plane discovered and ownership proof, sanitized;
4. before scheduler inventory and conflict decision;
5. exact final cadence and sanitized command shape;
6. whether entry was created, reused, enabled or modified;
7. Phase A natural-fire evidence and duration;
8. Phase B natural-fire evidence and duration;
9. exact owned hook execution counts from Phase B;
10. full/non-allowlisted cron fingerprints before/final;
11. Action Scheduler pending fingerprint and ID32733 before/final;
12. mail/HTTP/network/payment counters;
13. protected business aggregate fingerprint before/final;
14. final scheduler enabled/disabled state;
15. rollback evidence if anything failed;
16. source/deploy changes count;
17. production access/touch = NO;
18. exactly one proposed next task, not created or started.

Do not include passwords, tokens, hosting session data, raw PII, emails, raw mail bodies, user IDs, order IDs or secret-bearing scheduler payloads.

STOP after publishing and verifying the report. Do not begin 2.09.