# Zadatak 2.09 — Establish an authorized observable Hostinger/hPanel scheduler control-plane session and rerun the guarded two-phase selective scheduler activation

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.08
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, probing any hosting control plane, bootstrapping WordPress, or changing anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.09. Do not begin 2.10 automatically.

Publish the final report through the existing `codex-reports` workflow and STOP.

---

## 1. Context already established

Zadatak 2.06 implemented the staging-only fail-closed selective runner:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`

The runner's fixed internal allowlist is exactly, in this order:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

Zadatak 2.07 PASS closed the auxiliary HTTP evidence gap and proved fully guarded `--check-only` acceptance with:

- `7 guarded / 0 unguarded` WordPress/WP-CLI processes;
- hook executions `0`;
- cron state unchanged;
- actual external application/vendor network `0`;
- `wp_mail=0`, PHPMailer/SMTP `0`;
- payment/order/refund mutations `0`;
- Action Scheduler pending `7`, ID32733 `pending/0`;
- protected business state unchanged;
- scheduler activation `0`;
- production untouched.

Zadatak 2.08 restarted correctly and stopped **PARTIAL / BLOCKED_NO_AUTHORIZED_SCHEDULER_CONTROL_PLANE** at the mandatory discovery gate. It proved:

- baseline/source/deploy/tool parity passed;
- account-level `crontab` command unavailable;
- Hostinger CLI unavailable;
- hPanel CLI unavailable;
- HAPI CLI unavailable;
- no authenticated Hostinger/hPanel API, app connector, or other permitted scheduler control surface was available in that execution environment;
- scheduler inventory was therefore not observable;
- scheduler mutations `0`;
- WordPress/WP-CLI processes `0`;
- runner/hook executions `0`;
- source/deploy/business mutations `0`;
- production untouched.

The exact desired scheduler configuration from 2.08 remains:

- cadence: every 15 minutes, semantically equivalent to `*/15 * * * *`;
- Phase A command:
  `/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh --check-only`
- Phase B/final command, only after Phase A passes:
  `/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh`
- cron output email recipient: none.

`DISABLE_WP_CRON=true` is intentional and must remain enabled. Broad request-driven or broad server-triggered WordPress cron remains forbidden.

---

## 2. Goal

Establish or consume **one authorized, observable, staging-account-owned Hostinger/hPanel scheduler control-plane session** without exposing credentials, then rerun the complete guarded two-phase scheduler activation that 2.08 could not begin.

Success means leaving exactly one verified staging scheduler entry enabled at 15-minute cadence, targeting only the fixed Raspitajse selective runner, after both natural-fire proofs pass.

If no authorized observable/mutable hosting scheduler control plane can be established safely, do not improvise. Report `PARTIAL` with a precise blocker and sanitized human handoff, and STOP with zero scheduler mutation.

---

## 3. Authorization and credential boundary

This task explicitly authorizes **staging scheduler control-plane discovery and mutation only** after every gate passes.

Production scheduler, production filesystem, production database, production runtime, production WordPress and production hosting configuration remain FORBIDDEN.

Do not request, print, persist, copy, scrape, infer, or expose:

- Hostinger/hPanel password;
- API token;
- session cookie;
- browser storage value;
- 2FA secret/code;
- SSH private key;
- hosting account secret;
- unrelated scheduler command payloads containing secrets.

Allowed control-plane sources are only:

1. an already-authorized Hostinger/hPanel session/control surface explicitly available to this execution environment without revealing credentials;
2. an official account-level scheduler API/CLI already authenticated by the environment through opaque credentials that Codex cannot read or print;
3. an account-level user cron interface clearly owned by this staging hosting account, if directly observable and safely mutable.

If establishing the control plane would require the user to paste a password/token/cookie/2FA code into the task or terminal, STOP. Do not solicit it.

Do not use root/system cron, systemd, GitHub Actions schedule, external web-cron, `/wp-cron.php`, request-driven WP-Cron, Action Scheduler, a daemon/loop/nohup process, or any other substitute.

---

## 4. Control-plane establishment gate

Before any WordPress bootstrap or scheduler mutation:

1. Re-prove fresh baseline/source/deploy/tool parity.
2. Read-only probe only the permitted scheduler/control-plane interfaces.
3. Confirm execution identity is the staging hosting account user, sanitized.
4. Confirm the chosen control plane can both:
   - enumerate relevant scheduler entries;
   - create/edit/enable/disable/delete exactly one staging-owned entry.
5. Confirm control-plane operations can be observed through provider-native history/status or another deterministic account-level execution record sufficient for natural-fire proof.
6. Confirm no secret material needs to be surfaced to Codex.

If any of 2–6 cannot be proven, result must be:

`PARTIAL — BLOCKED_NO_AUTHORIZED_SCHEDULER_CONTROL_PLANE`

Report exactly what safe operator action is missing, without asking for secrets, and STOP with zero scheduler mutation and zero WordPress runtime execution.

Do not continue merely because a filesystem or shell workaround exists.

---

## 5. Existing scheduler inventory and conflict gate

Once an authorized control plane is available, read-only inventory staging-account scheduler entries that could target:

- WordPress;
- WP-CLI;
- this repository;
- the selective runner;
- Action Scheduler.

For relevant entries report sanitized only:

- enabled/disabled;
- cadence;
- command shape/path;
- staging target yes/no;
- classification: exact desired runner / broad WordPress cron / unrelated / unknown.

Rules:

- no relevant entry: may create exactly one new staging entry after all gates pass;
- exactly one disabled exact runner entry at 15 minutes: may reuse;
- exactly one enabled exact runner entry: do not duplicate; perform safe Phase A conversion/restoration as specified below;
- broad staging trigger (`wp-cron.php`, `--due-now`, `--all`, Action Scheduler queue runner, equivalent): do not silently disable/delete/replace; report `PARTIAL — BLOCKED_CONFLICTING_BROAD_SCHEDULER` and STOP;
- duplicate/ambiguous relevant entries: report `PARTIAL — BLOCKED_AMBIGUOUS_SCHEDULER_OWNERSHIP` and STOP.

Unrelated entries are read-only and must not change.

---

## 6. Exact pre-activation parity/state gate

Before scheduler mutation, fully guarded and read-only prove:

- local staging HEAD = fresh `origin/staging` = baseline;
- deploy marker = baseline;
- source worktree clean;
- runtime environment = staging;
- runner tracked/source-clean, SHA-256 `6237e827c8fd34f3d91b8ea442ecb827e2a4a62992c57994188a8a38da729ce4`;
- guard tracked/source-clean, SHA-256 `c784b87500ce7af00be71e2b4aee8f35c3aec8a80175f72c3e0d5d6047720b8d`;
- Communications source/runtime parity exact;
- `DISABLE_WP_CRON=true` effective;
- staging mail-safety loaded/configured;
- `doing_cron` clear;
- callback/event contract fingerprint `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93` unchanged;
- non-allowlisted cron fingerprint `875972ba451addb368e78d86b4f3df94e7627037911ac617e620c2cdba6c1e71` unchanged;
- Action Scheduler pending count `7` and fingerprint `1e06ffd60f1323db49bf194e8a3c8cdd7ea055c09a9554cda01c33aff1f32143` unchanged;
- ID32733 exactly `pending/0`;
- protected business aggregate fingerprint `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc` unchanged;
- continuation rows `0`;
- legacy daily callback/event `0/0`;
- legacy shared-expiry callback/event `0/0`;
- vendor candidate→job sender `0`;
- employer→candidate vendor sender `0`;
- candidate auto-expiry disabled.

The full WP-Cron fingerprint may differ from the historical 2.07 value only if the three owned recurring timestamps advanced independently after 2.07; if so, prove that the entire difference is limited to those three owned timestamp rows and that all callback/event contracts remain identical. Any other drift is `PARTIAL — BLOCKED_PREACTIVATION_STATE_DRIFT`.

All acceptance WP-CLI/WordPress diagnostics must load the proven pre-bootstrap HTTP/runtime guard. Unguarded WP-CLI is forbidden.

---

## 7. Real-business due-work gate

Immediately before each scheduler phase, fully guarded and read-only prove:

- expired published job listings due for transition: `0`;
- employer expiry notices/retries currently due: `0`;
- published candidate-job alerts currently due: `0`;
- all owned claim families `0`;
- continuation rows `0`.

If any real owned business work is due, do not activate/switch the scheduler. Restore the exact pre-task scheduler state if anything had been changed, report `PARTIAL — BLOCKED_REAL_DUE_BUSINESS_WORK`, and STOP.

Do not create business fixtures to make activation pass.

---

## 8. Scheduler mutation boundary

Only after sections 4–7 pass may the task mutate scheduler state.

Exactly one staging entry is authorized.

Required cadence:

`*/15 * * * *`

or provider-native semantically identical 15-minute interval.

No output email recipient.

No shell interpolation, pipes, curl/wget, inline PHP, dynamic parameters, arbitrary hook input, environment-secret expansion, or broad WP-CLI command.

Track an exact sanitized pre-task scheduler snapshot so rollback can restore only this task's entry and nothing else.

---

## 9. Phase A — natural scheduled check-only proof

Configure exactly one staging scheduler entry with:

`/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh --check-only`

Then:

1. Observe one **natural provider-fired** execution within at most 20 minutes.
2. Do not manually trigger the scheduler entry or manually invoke the runner as a substitute.
3. Provider/control-plane evidence must deterministically tie the observed run to this exact entry.
4. Reconcile the runner's sanitized result and protected-state evidence.

PASS requirements for Phase A:

- successful `check`/`NOOP` result;
- hook executions `0`;
- cron rows/timestamps unchanged;
- Action Scheduler executions/mutations `0`;
- ID32733 unchanged `pending/0`;
- `wp_mail=0`, PHPMailer/SMTP `0`;
- payment/order/refund mutations `0`;
- actual external application/vendor network `0`;
- protected business fingerprint unchanged;
- unrelated scheduler entries unchanged.

If natural fire is not observed within 20 minutes, or evidence is ambiguous, restore/remove only this task's scheduler entry to exact pre-task state, verify rollback, report `PARTIAL — BLOCKED_SCHEDULER_FIRE_NOT_OBSERVED`, and STOP.

---

## 10. Phase B — natural normal selective-runner proof

Only after Phase A PASS:

1. Re-run sections 6 and 7 immediately.
2. Change only the exact verified entry to:

`/bin/bash /home/u601262303/repo/raspitajse-wp/tools/raspitajse-staging-owned-cron-runner.sh`

3. Keep cadence exactly 15 minutes and output email disabled.
4. Observe one natural provider-fired execution within at most 20 minutes.
5. Do not manually trigger it or substitute a manual runner/exact hook call.

PASS requirements for Phase B:

- runner starts in normal mode;
- only the fixed three allowlisted owned hooks are reachable;
- each due hook executes at most once, fixed order;
- `executed_hooks` may be `0..3` according to natural due timestamps;
- real owned business due-work remains `0`, therefore business mutations/messages `0`;
- only timestamps of owned hooks actually executed may advance, by normal WordPress recurrence behavior;
- non-allowlisted cron fingerprint unchanged;
- Action Scheduler execution/mutation `0`;
- continuation execution `0`;
- `wp_mail=0`, PHPMailer/SMTP `0`;
- payment/order/refund mutation `0`;
- actual external application/vendor network `0`;
- protected business fingerprint unchanged;
- unrelated scheduler entries unchanged.

If Phase B cannot be observed or any invariant fails, disable/remove/restore only this task's entry to exact pre-task state, prove rollback, report PARTIAL/FAIL as appropriate, and STOP. Do not leave an unverified scheduler enabled.

---

## 11. Final steady-state requirements

Whole-task PASS requires all of the following:

- exactly one staging-owned selective scheduler entry enabled;
- cadence exactly every 15 minutes;
- final command exactly the zero-argument runner command;
- provider-native observable control plane remains available;
- Phase A natural fire PASS;
- Phase B natural fire PASS;
- broad WP-Cron trigger count created by this task `0`;
- Action Scheduler trigger count created by this task `0`;
- no continuation scheduler;
- no duplicate runner scheduler;
- no output email recipient;
- `DISABLE_WP_CRON=true` unchanged;
- source/deploy/runtime parity intact;
- protected business state unchanged except normal owned cron timestamp advancement explicitly proven in Phase B;
- production untouched.

Do not change application source merely to record scheduler state. If no source change is needed, staging SHA should remain unchanged.

---

## 12. Rollback and failure handling

Before first scheduler mutation, capture enough sanitized identity to restore the exact pre-task state.

On any failure after mutation:

- stop further phases immediately;
- disable/delete newly created entry or restore reused entry exactly to its prior enabled state/command/cadence;
- do not modify unrelated entries;
- verify rollback through the same authorized control plane;
- verify no task-owned process/lock/temporary scheduler remains;
- re-prove protected WordPress/business invariants if WordPress had been bootstrapped;
- report rollback result explicitly.

If rollback cannot be proven, result is FAIL and the final report must identify the unresolved staging scheduler state without exposing secrets.

---

## 13. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` still applies.

On first confirmed signature:

- activate the documented circuit breaker;
- do not retry the same sandbox helper repeatedly;
- use proven namespace-free Git/filesystem/WP-CLI methods;
- do not wait for host capacity;
- do not use broad process kills;
- if a bounded task-private child exceeds its documented timeout, inspect and terminate only that exact child/process group.

This host issue does not authorize bypassing scheduler or safety gates.

---

## 14. Source quality / no broad refactor

Expected source changes: `0`.

Do not modify:

- WordPress core;
- WooCommerce;
- WP Job Board Pro;
- Paid Listings;
- Superio/theme code;
- Raspitajse Commerce;
- Raspitajse Communications behavior;
- MU mail-safety;
- runner/guard unless an unavoidable defect is independently proven before scheduler mutation.

If a runner/guard defect is discovered, do not mix a redesign into scheduler activation. Roll back any scheduler mutation, report PARTIAL/FAIL, and propose a separate fix task.

---

## 15. Final report

Publish:

**Zadatak 2.09 — Establish an authorized observable Hostinger/hPanel scheduler control-plane session and rerun the guarded two-phase selective scheduler activation**

Report must include, sanitized:

- Result: PASS / PARTIAL / FAIL;
- exact blocker classification if not PASS;
- source/origin/deploy SHA parity;
- selected authorized scheduler control-plane type, without credentials;
- scheduler inventory/conflict result;
- pre-task scheduler state;
- scheduler mutation count;
- Phase A natural-fire evidence and counters;
- Phase B natural-fire evidence and counters;
- final scheduler cadence/command shape/enabled state;
- rollback result if used;
- guarded/unguarded WP-CLI counts;
- WP HTTP attempts/intercepts/unexpected/actual external network;
- mail/SMTP counters;
- payment/order/refund counters;
- Action Scheduler pending/fingerprint and ID32733 state;
- protected business fingerprint before/final;
- cron non-allowlisted fingerprint before/final;
- production access/touch = NO;
- temporary artifacts cleaned.

Propose exactly one next task. Do not create or start it.

**STOP.**
