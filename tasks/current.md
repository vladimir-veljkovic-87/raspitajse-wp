# Zadatak 2.11 — Audit the selective staging cron runner/guard for steady-state KEEP / REDESIGN / DROP

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.10
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before doing any work. `codex-tasks` is READ-ONLY.

Verify fresh `origin/staging` is exactly:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.11. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.12.

---

## 1. Context already accepted

Zadatak 2.10 PASS established:

`STEADY_STATE_SELECTIVE_STAGING_SCHEDULER_ACCEPTED`

The accepted staging scheduler architecture is:

- exactly one Hostinger/hPanel staging scheduler entry;
- cadence `*/15 * * * *`;
- final command invokes the zero-argument selective runner;
- `DISABLE_WP_CRON=true` remains intentional;
- no broad `/wp-cron.php`, `wp cron event run --due-now`, Action Scheduler queue runner, daemon/loop, or equivalent substitute;
- fixed owned hook order:
  1. `raspitajse_job_listing_expiry_evaluator`
  2. `raspitajse_employer_job_expiry_notice_evaluator`
  3. `raspitajse_candidate_job_alert_evaluator`.

Accepted 2.10 evidence also proved exact T0 -> one natural provider fire -> T1 attribution, unchanged non-allowlisted cron state, unchanged Action Scheduler/protected business state, no mail/SMTP/payment/external-network side effects, and production untouched.

Current staging tool files are:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`

Accepted file SHA-256 values from the scheduler arc:

- runner: `6237e827c8fd34f3d91b8ea442ecb827e2a4a62992c57994188a8a38da729ce4`
- guard: `c784b87500ce7af00be71e2b4aee8f35c3aec8a80175f72c3e0d5d6047720b8d`

This task does **not** assume either file should be removed. The purpose is to determine which responsibilities are permanent operational safety and which were temporary activation/proof scaffolding.

---

## 2. Goal

Perform a **read-only architecture and operational-cost audit** of the steady-state selective staging cron runner and guard.

For every distinct responsibility in the runner/guard, classify it as:

- **KEEP** — still required in steady-state because it materially prevents unsafe, broad, duplicate, stale, or mis-targeted execution;
- **REDESIGN** — the protection/business need remains, but the current implementation is heavier, coupled, duplicated, brittle, or inappropriate to execute every 15 minutes;
- **DROP** — activation/test/provenance scaffolding that no longer provides meaningful steady-state value once the selective scheduler has been accepted.

The task must answer:

1. What should remain on every natural 15-minute run?
2. What should move to deploy-time, CI, a separate health check, or explicit diagnostic tooling?
3. What can be removed entirely?
4. What is the simplest safe steady-state architecture that preserves the accepted scheduler guarantees?
5. Is any implementation follow-up actually justified, or should the current runner/guard simply remain unchanged?

This task is audit/design only. **Expected application/source changes: 0.**

---

## 3. Hard no-mutation boundary

Do not modify or remove:

- the Hostinger/hPanel scheduler entry;
- runner or guard files;
- WordPress core;
- WooCommerce;
- WP Job Board Pro;
- Paid Listings;
- Superio/theme code;
- Raspitajse Communications;
- Raspitajse Commerce;
- MU mail-safety;
- cron rows;
- Action Scheduler actions;
- database/business data;
- deployment marker or runtime code.

Do not create a feature branch, source commit, staging deploy, fixture, cron event, scheduler entry, or application mutation.

Do not manually invoke:

- the zero-argument runner;
- `--check-only` runner;
- any owned cron hook;
- broad WP-Cron;
- Action Scheduler queue processing.

The accepted natural Hostinger scheduler may continue operating normally while the audit runs. Do not pause, edit, recreate, or disable it.

Production scheduler/filesystem/database/runtime/WordPress access is forbidden.

---

## 4. Required source inspection

Read both tool files in full from fresh `origin/staging`, not from inherited files on `codex-tasks`:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- `tools/raspitajse-staging-owned-cron-guard.php`

Also inspect only the directly relevant owned Communications code for the three allowlisted evaluators and their scheduling/bootstrap contracts, enough to understand what the runner must protect. Do not broaden into a new Communications refactor.

Read the final 2.10 PASS report and the relevant 2.06/2.07 reports if needed to distinguish original activation-proof requirements from true ongoing requirements.

Do not reverse-engineer irrelevant vendor internals.

---

## 5. Mandatory responsibility inventory

At minimum, separately inventory and classify all responsibilities in these categories. Split further where the implementation has distinct responsibilities.

### A. Scheduler execution boundary

- fixed three-hook allowlist;
- fixed hook order;
- zero-argument production mode;
- strict rejection of arbitrary arguments;
- `--check-only` diagnostic mode;
- due-only semantics;
- exact-hook WP-CLI execution rather than broad cron execution.

### B. Concurrency and process safety

- nonblocking `flock` overlap prevention;
- per-hook timeout;
- whole-cycle watchdog/timeout;
- bounded cleanup of task-private temporary state;
- fail-closed behavior and structured terminal status.

### C. Environment/deploy/source gates

- staging-root identity/path check;
- deploy marker presence and HEAD parity;
- source branch allowlist;
- clean worktree requirement;
- required binary/primitives checks;
- runner/guard file assumptions;
- Communications source/runtime aggregate hash parity.

### D. Runtime side-effect guards

- `WP_HTTP_BLOCK_EXTERNAL` / pre-transport HTTP interception;
- unexpected HTTP attribution logic;
- `wp_mail` interception/counting;
- PHPMailer/SMTP interception/counting;
- payment-path interception/counting;
- whether these are permanent staging safety controls or activation-only measurement controls.

### E. Callback/event contract validation

- exact callback identity;
- callback priority/accepted-args verification;
- exactly one recurring event per owned hook;
- hourly/3600 recurrence verification;
- empty event args verification;
- contract fingerprinting.

### F. Broad-state/protected-state snapshots

- full cron fingerprint;
- non-allowlisted cron fingerprint;
- Action Scheduler pending fingerprint/count;
- protected ID32733 status/attempts;
- owned claim families;
- real-business-due counts;
- protected business aggregate/component fingerprints;
- candidate expiry footprint and retired legacy callback/sender assertions;
- any related heavy DB/query work performed as part of a normal runner cycle.

### G. Observability

- structured JSON result;
- per-hook status values;
- execution count;
- duration;
- safety counters;
- what minimum telemetry is useful for normal Hostinger `View Output` steady-state troubleshooting.

For each item report:

- current purpose;
- original scheduler-activation purpose if different;
- steady-state failure/risk it protects against;
- runtime cost/complexity characteristics;
- KEEP / REDESIGN / DROP;
- recommended steady-state location: every-run / deploy-time / CI / periodic health-check / manual diagnostic / remove;
- migration risk if changed.

---

## 6. Runtime cost and coupling analysis

Without manually running the runner, identify which operations are likely to dominate each 15-minute invocation, including at minimum:

- full filesystem tree hashing;
- WordPress bootstrap count;
- DB-wide or post/meta snapshot queries;
- cron-array serialization/fingerprinting;
- Action Scheduler inspection;
- protected business fingerprint generation;
- repeated source/runtime parity checks;
- HTTP/mail/payment guard registration and bookkeeping.

Use already recorded provider durations from the accepted scheduler reports as evidence where useful; do not invent benchmark numbers.

Distinguish:

- cheap deterministic safety gates appropriate for every run;
- expensive checks that may belong at deploy-time or periodic health checks;
- evidence-only counters that were valuable during activation but may not justify permanent cost.

Do not optimize merely for speed: safety guarantees take precedence where a meaningful failure mode still exists.

---

## 7. Required steady-state architecture recommendation

Produce one concrete recommended architecture, not only classifications.

The recommendation must preserve, unless the audit gives a specific stronger replacement:

- one external 15-minute staging scheduler;
- no request-driven WP-Cron;
- no broad cron runner;
- no Action Scheduler runner substitution;
- fixed Raspitajse-owned hook allowlist;
- no arbitrary hook/input execution surface;
- overlap protection;
- bounded execution time;
- staging-only targeting;
- fail-closed behavior on material safety mismatch;
- enough observable output to diagnose a failed natural run.

Explicitly define:

1. **every-run core** — exact responsibilities that should execute on every provider fire;
2. **deploy-time/CI gate** — checks better validated when staging is deployed or code changes;
3. **periodic/manual health check** — deeper fingerprints/snapshots worth retaining but not necessarily every 15 minutes;
4. **retired proof scaffolding** — checks/counters that can be dropped if no longer operationally useful.

If the current implementation is already the best tradeoff, say **KEEP AS-IS** and justify it. Do not manufacture a cleanup task merely because 2.11 exists.

---

## 8. Security and failure-mode analysis

For every proposed REDESIGN or DROP, state what could go wrong after removal and what compensating control prevents regression.

At minimum consider:

- wrong environment/path;
- stale or mismatched deployed Communications code;
- duplicate runner overlap;
- a developer accidentally widening the hook set;
- callback/event drift after future plugin/refactor changes;
- unexpected external network/mail/payment behavior on staging;
- broad cron/Action Scheduler execution accidentally reintroduced;
- scheduler still firing during a dirty/incomplete deployment;
- opaque failures in Hostinger `View Output`.

No recommendation may weaken safety merely to reduce runtime cost without naming and replacing the lost control.

---

## 9. Decision outcome

Finish with one of exactly these architecture outcomes:

### `KEEP_AS_IS`
Use when the current runner/guard is justified as the permanent staging steady-state implementation and cleanup would not materially improve simplicity/cost/risk.

### `REDESIGN_BOUNDED`
Use when specific responsibilities should move out of every-run execution or be simplified while preserving the accepted safety boundary. List the exact bounded changes that a future task may implement.

### `DROP_TEMPORARY_COMPONENT`
Use only if a whole temporary component can safely be removed without weakening the accepted steady-state guarantees. Prove why the remaining architecture is sufficient.

Do **not** implement the decision in 2.11.

---

## 10. Acceptance criteria

PASS requires:

- baseline/source/deploy context verified read-only;
- full runner and guard audited from `origin/staging`;
- all material responsibilities inventoried;
- every responsibility classified KEEP / REDESIGN / DROP;
- runtime-cost/coupling analysis completed without manual runner execution;
- one explicit steady-state architecture proposed;
- every REDESIGN/DROP has failure-mode and compensating-control analysis;
- one final architecture outcome selected from the three allowed values;
- scheduler mutations `0`;
- manual runner/hook/broad-cron/Action-Scheduler executions `0`;
- source changes/commits/deploys `0`;
- production touched `NO`.

If the audit cannot distinguish a responsibility safely, classify it conservatively as KEEP and record the evidence gap rather than deleting by assumption.

---

## 11. Final report

The report must include:

- result and final architecture outcome;
- live staging/source baseline and cleanliness;
- concise accepted scheduler context from 2.10;
- responsibility matrix with purpose, cost, KEEP/REDESIGN/DROP and destination;
- every-run core recommendation;
- deploy-time/CI recommendation;
- periodic/manual diagnostic recommendation;
- retired-proof-scaffolding recommendation;
- failure-mode/compensating-control table for every REDESIGN/DROP;
- whether an implementation follow-up is materially justified;
- scheduler/manual runner/hook/AS mutation counters, all zero;
- production touched NO;
- exactly one proposed next task, not created or started.

Next-task rule:

- if the outcome is `REDESIGN_BOUNDED` or `DROP_TEMPORARY_COMPONENT`, propose one bounded implementation task for that exact decision;
- if the outcome is `KEEP_AS_IS`, propose the next higher-value read-only task: **WP Job Board Pro security/upgrade readiness audit with fresh authoritative vulnerability/version verification**.

STOP after publishing the report. Do not begin 2.12.
