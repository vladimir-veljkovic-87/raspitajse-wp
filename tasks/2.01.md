# Zadatak 2.01 — Establish an owned job-only expiry boundary and retire legacy candidate expiry paths

Status: READY
Baseline: d9a095893b3751051135333e08dd3a0148db6d9b
Previous task: 2.00
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact application truth from `origin/staging` and verify first that it is exactly:

`d9a095893b3751051135333e08dd3a0148db6d9b`

If it differs, STOP and report the mismatch.

Execute only Zadatak 2.01. Work on a scoped feature branch from exact `origin/staging`, integrate to `staging` only after all acceptance criteria pass, publish the final report through the existing `codex-reports` workflow, and STOP. Do not begin 2.02 automatically.

---

## 1. Context and decisions already made

Zadatak 2.00 completed PASS and established these business decisions:

- candidate profiles must **not auto-expire because of time**;
- candidate admin expiry notice: **DROP**;
- candidate self expiry notice: **DROP**;
- job admin expiry notice: **DROP**;
- employer job expiry notice: **REDESIGN**, but email delivery is explicitly deferred to a later task;
- published job listing expiry remains a required business outcome because listing duration is distinct from package-entitlement validity;
- the legacy shared expiry boundary is not suitable to KEEP.

Current relevant runtime truth from 2.00:

- `wp_job_board_pro_email_daily_notices` has exactly four callbacks, all priority `10`:
  1. `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
  2. `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
  3. `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
  4. `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`
- legacy employer→candidate alert sender = `0`;
- candidate→job vendor sender = `0`;
- `wp_job_board_pro_check_for_expired_jobs` has exactly two registered callbacks but **zero scheduled events**:
  - `WP_Job_Board_Pro_Candidate::check_for_expired_candidates`
  - `WP_Job_Board_Pro_Job_Listing::check_for_expired_jobs`
- candidate expiry data footprint: 2 candidate posts, no expiry meta, legacy due count `0`;
- job footprint: 0 job posts;
- `resume_duration=0`;
- global `submission_duration=30`, but Paid Listings overrides job duration from package/listing duration where applicable;
- candidate notice helper contains a real `$wpdb` scope defect if reached;
- legacy notice senders have no delivery ledger/idempotency and do not use owned SenderPolicy channels;
- existing `job_expiry` SenderPolicy channel exists but is **not to be used for mail in this task**.

This task is the implementation slice that creates the owned **job-only** expiry boundary and permanently removes candidate expiry from active runtime behavior.

Do not revisit the 2.00 business decisions.

---

## 2. Goal

After this task, staging must satisfy all of the following:

1. Candidate time-based expiry cannot automatically change a candidate profile status.
2. Both candidate expiry notice callbacks are absent from the final daily graph.
3. The job-admin expiry notice callback is absent.
4. The legacy employer job-expiry notice callback is absent; no replacement email sender is introduced yet.
5. Both legacy shared expiry check callbacks are absent from `wp_job_board_pro_check_for_expired_jobs`.
6. Candidate expiry calculation is forced to no automatic duration from a Raspitajse-owned layer without mutating historical candidate records/meta.
7. One Raspitajse-owned, dedicated job-listing expiry evaluator exists with its own ensured schedule.
8. The owned evaluator expires only published jobs whose authoritative `_job_expiry_date` is in the past.
9. Existing WPJBP/Paid Listings package-derived job expiry-date creation remains authoritative and unchanged.
10. Package entitlement validity remains separate and unchanged.
11. No employer expiry email is sent or implemented in 2.01.
12. No production access/touch.

---

## 3. Implementation ownership / vendor boundary

Implement in a Raspitajse-owned plugin/layer, preferably the existing communications or another already-established Raspitajse-owned operational component if that is cleaner.

Do **not** modify:

- WordPress core;
- WP Job Board Pro vendor source;
- WP Job Board Pro WooCommerce Paid Listings vendor source;
- WooCommerce vendor/core;
- Superio parent theme.

Do not copy vendor expiry methods into owned code 1:1. Preserve the business outcome, not the legacy implementation.

If a required suppression cannot be achieved cleanly with exact callbacks/filters after vendor registration, STOP rather than editing vendor files.

---

## 4. Retire all four legacy expiry notice callbacks

After vendor registration, remove exactly these priority-10 callbacks from `wp_job_board_pro_email_daily_notices`:

- `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
- `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
- `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
- `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`

Do not replace any of them in this task.

Acceptance after final bootstrap:

- final callback count on `wp_job_board_pro_email_daily_notices` = `0`;
- each of the four exact legacy callbacks registration count = `0`;
- employer→candidate alert sender remains `0`;
- candidate→job vendor sender remains `0`.

The existing legacy daily recurring event may remain scheduled unchanged in 2.01. Do not execute it. Do not unschedule it merely because the hook becomes empty; final orphan-scheduler cleanup belongs to a later scheduler task unless required for correctness.

---

## 5. Retire the legacy shared expiry-check boundary

After vendor registration, remove exactly both callbacks from:

`wp_job_board_pro_check_for_expired_jobs`

at priority `10`:

- `WP_Job_Board_Pro_Candidate::check_for_expired_candidates`
- `WP_Job_Board_Pro_Job_Listing::check_for_expired_jobs`

Acceptance:

- final legacy shared-expiry callback count = `0`;
- candidate checker registration = `0`;
- legacy job checker registration = `0`;
- scheduled event count for the legacy shared hook remains `0` unless a pre-existing external state unexpectedly differs; do not create or execute that legacy event;
- no alternate owned/manual caller invokes either removed vendor method.

Do not execute the broad legacy hook.

---

## 6. Candidate auto-expiry must be impossible through automatic duration calculation

The product decision is final: **candidate profiles do not auto-expire because of age/time**.

Add a Raspitajse-owned late filter on the vendor candidate-expiry calculation boundary so the effective automatic candidate duration is always disabled/zero regardless of:

- global `resume_duration`;
- a candidate package duration;
- vendor defaults;
- future configuration drift.

Requirements:

- do not delete or rewrite existing `_candidate_expiry_date` meta;
- do not bulk-republish expired candidates;
- do not mutate the two existing staging candidate posts;
- do not introduce a replacement candidate expiry state;
- historical/manual expiry metadata, if it ever exists, must not be able to trigger automatic status mutation because the legacy candidate checker is removed;
- report exact filter name, priority and final effective candidate expiry calculation result.

If a future profile-freshness product is desired, it must be a separate product/state model and is outside 2.01.

---

## 7. Preserve canonical job expiry-date writers

Do **not** replace or alter the existing source of `_job_expiry_date`.

Preserve current behavior where:

- WPJBP creates/maintains the listing expiry date on publish-like transitions;
- Paid Listings can override listing duration from `_job_package_duration` / entitlement-product duration;
- listing-based subscriptions may intentionally represent no automatic expiry;
- package entitlement validity and listing lifetime remain independent clocks.

The owned evaluator must **consume** the existing `_job_expiry_date`; it must not invent a second expiry date, table, duration source or entitlement-derived clock.

Do not alter Raspitajse Commerce's 30-calendar-day package entitlement validity rules.

---

## 8. Owned job-listing expiry evaluator

Introduce one dedicated owned evaluator for published job listings.

Recommended hook name:

`raspitajse_job_listing_expiry_evaluator`

A different Raspitajse-owned name is acceptable only if clearly justified in the report.

### Eligibility

A job is due only when all are true:

- post type = `job_listing`;
- current post status = `publish`;
- `_job_expiry_date` exists and is a valid non-empty `Y-m-d` calendar date;
- expiry date is **strictly before** the current site-local calendar date.

Do not expire a job merely because package entitlement later became invalid.

Do not expire jobs with:

- missing expiry date;
- empty expiry date;
- malformed expiry date;
- expiry date today;
- future expiry date;
- non-published status.

### Calendar semantics

Use explicit WordPress timezone/calendar APIs (`wp_timezone()`, `wp_date()` or equivalent) rather than legacy adjusted timestamps plus raw PHP `date()`.

### Bounded execution

The evaluator must be bounded. Use a small explicit batch limit, recommended `50` jobs per run.

Query must be deterministic, e.g. oldest expiry date first and stable ID tie-breaker.

No unbounded full-table scan followed by unlimited writes.

### Idempotency / concurrency

Implement a small owned claim/lock boundary so overlapping evaluator workers cannot perform the same expiry transition concurrently.

Requirements:

- atomic/owned claim mechanism;
- bounded TTL, recommended around 10 minutes;
- exact-token release where applicable;
- stale claim can recover automatically;
- a second overlapping worker must exit without mutations;
- before each transition, re-read/revalidate current post status and expiry date so stale query results cannot expire a job that changed meanwhile.

Do not build a large queue subsystem.

### Transition

For an eligible job, use the normal WordPress status-transition API so ordinary WordPress/WPJBP lifecycle hooks still observe a legitimate:

`publish → expired`

transition.

Do not update the posts table directly solely to bypass lifecycle hooks.

No email is sent from this evaluator.

---

## 9. Dedicated schedule with self-healing ensure behavior

The owned evaluator must have its own schedule and must not depend on plugin activation-only scheduling.

Use a simple built-in recurrence unless a strong reason exists otherwise. **Hourly** is preferred because it is already a standard WordPress schedule and allows a bounded backlog to drain without an unbounded run, while calendar eligibility remains date-based.

Requirements:

- exactly one recurring event for the owned job-expiry hook;
- schedule = `hourly` if using the preferred design;
- ensure-on-bootstrap/request behavior using `wp_next_scheduled()` or equivalent so a missing event can be recreated without plugin reactivation;
- do not create duplicates on repeated bootstraps;
- do not execute the event merely to prove it exists;
- do not use Action Scheduler for this evaluator.

On staging `DISABLE_WP_CRON` may remain true; this task only needs to prove the event is correctly registered/scheduled and that explicit bounded fixture invocation of the **owned evaluator only** works.

---

## 10. Bounded staging fixture

A deterministic disposable staging fixture is required unless a concrete environment blocker makes safe fixture mutation impossible.

Never use real/historical business records.

At minimum create isolated disposable job listings sufficient to prove:

1. one `publish` job with a past valid `_job_expiry_date` becomes `expired`;
2. one `publish` job expiring **today** remains `publish`;
3. one `publish` job with a future expiry date remains `publish`;
4. one `publish` job with empty/missing expiry remains `publish`;
5. one already non-published/expired fixture is not incorrectly transitioned;
6. an overlapping claim attempt cannot run a second mutation pass;
7. a second normal evaluator run after the first does not re-transition the already expired fixture.

Use only the owned evaluator/function/hook for execution. Do **not** execute:

- `wp_job_board_pro_check_for_expired_jobs`;
- `wp_job_board_pro_email_daily_notices`;
- candidate→job evaluator;
- broad WP-Cron runner;
- Action Scheduler runner.

Capture before/after fixture-only status and cleanup all fixture posts/meta exactly.

After cleanup, the real staging job footprint/fingerprint must equal the pre-task business-data footprint.

No email should be attempted during fixture execution.

---

## 11. Candidate preservation proof

Before and after implementation/fixture, capture sanitized candidate counts/status/expiry-meta fingerprint.

Expected historical state from 2.00:

- total candidates `2`;
- `1 publish`, `1 pending`;
- no candidate expiry meta;
- legacy due count `0`.

Acceptance:

- candidate posts/status/meta unchanged;
- no automatic candidate expiry event exists;
- candidate expiry checker registration `0`;
- both candidate expiry notice registrations `0`;
- effective automatic candidate expiry calculation = disabled/empty.

Do not execute the known broken vendor candidate due helper.

---

## 12. Employer job-expiry email intentionally deferred

2.00 classified employer job-expiry notification as REDESIGN, but **2.01 must not implement it**.

Specifically:

- legacy `send_employer_expiring_notice` must be removed from runtime registration;
- do not add an owned mailer/evaluator for employer expiry notices;
- do not call `SenderPolicy::job_expiry`;
- do not send test mail;
- do not modify job-expiry email templates/content/options;
- do not decide final employer notification cadence in this task.

The job expiry status lifecycle must become sound first. A later task may design/implement employer notification against this owned lifecycle.

---

## 13. Existing communications/package protected invariants

After deployment prove unchanged unless explicitly listed otherwise:

- employer→candidate candidate-alert vendor sender = `0`;
- candidate-alert new creation remains fail-closed;
- historical published `candidate_alert` count remains `4` with unchanged fingerprint;
- candidate→job vendor sender = `0`;
- owned candidate→job hourly evaluator count = `1`;
- candidate→job continuation count = `0` unless independently pre-existing;
- published `job_alert` remains `0`;
- owned candidate-job four-value new-create frequency policy remains intact;
- Raspitajse Commerce entitlement policy remains unchanged;
- package entitlement validity data is not migrated or recalculated.

Do not execute either alert delivery subsystem.

---

## 14. Action Scheduler / protected scheduler state

Capture before/final:

- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected historical state:

- pending Action Scheduler count `7`;
- ID32733 `pending/0`.

No AS mutation or execution is authorized.

The new job expiry evaluator must use WP-Cron, not Action Scheduler.

---

## 15. Mail / network / payment safety

Expected throughout task:

- real `wp_mail`: `0`;
- PHPMailer transport: `0`;
- SMTP: `0`;
- legacy expiry notice callback executions: `0`;
- candidate alert sender executions: `0`;
- candidate→job evaluator executions: `0`;
- external application/vendor HTTP: `0`;
- payment calls: `0`.

Do not execute any legacy notice callback even with staging mail interception.

---

## 16. Source quality / static validation

For every changed PHP file:

- `php -l` PASS.

For final patch:

- `git diff --check` PASS;
- no debug/PII logging;
- no secrets;
- no hard-coded user IDs/emails;
- no production URLs introduced;
- no vendor/core/parent-theme changes;
- no broad refactor;
- no second source of truth for listing expiry.

Report exact changed files and diffstat.

---

## 17. Staging integration and deploy

Follow `tasks/README.md` exactly:

- create one scoped feature branch from exact baseline;
- implement the narrow owned expiry boundary;
- run static/runtime fixture acceptance before integration;
- fast-forward/integrate to `staging` only after acceptance passes;
- deploy only to staging;
- prove final `origin/staging`, local staging, deploy marker and runtime source parity;
- production remains untouched.

If source/deploy/runtime parity cannot be proven, STOP with PARTIAL/FAIL.

---

## 18. Expected final runtime state after PASS

Expected final state:

- `wp_job_board_pro_email_daily_notices` callback count = `0`;
- legacy daily event may still exist unchanged but is not executed;
- `wp_job_board_pro_check_for_expired_jobs` callback count = `0`;
- legacy shared expiry scheduled event count = `0`;
- candidate admin expiry notice registration = `0`;
- candidate self expiry notice registration = `0`;
- job admin expiry notice registration = `0`;
- legacy employer job-expiry notice registration = `0`;
- legacy candidate expiry checker registration = `0`;
- legacy job expiry checker registration = `0`;
- effective automatic candidate expiry duration = disabled/empty;
- owned job-expiry evaluator callback = exactly `1`;
- owned job-expiry recurring event = exactly `1`, preferably `hourly`;
- evaluator batch bound is explicit and deterministic;
- package-derived `_job_expiry_date` writer path unchanged;
- disposable past-due job fixture proves `publish → expired`;
- today/future/empty/nonpublished fixtures do not transition;
- all fixture records cleaned exactly;
- real staging candidate/job business-data fingerprints restored/unchanged after cleanup;
- employer job-expiry mail = not implemented / not sent;
- candidate→job owned evaluator remains exactly `1` hourly and unexecuted;
- `candidate_alert=4` unchanged;
- `job_alert=0` unchanged;
- Action Scheduler protected state unchanged;
- ID32733 remains `pending/0`;
- mail/SMTP/payment/broad-cron/AS executions = `0`;
- production touched = NO.

---

## 19. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

Prefer namespace-free Git/filesystem/WP-CLI workflows already proven in previous tasks. If a sandbox helper returns the known signature, do not retry it repeatedly. Never use broad process kills.

---

## 20. Final report

Publish:

**Zadatak 2.01 — Establish an owned job-only expiry boundary and retire legacy candidate expiry paths**

Report must include at minimum:

1. PASS/PARTIAL/FAIL and exact meaning;
2. declared baseline, feature commit and final staging SHA;
3. deploy marker/runtime parity;
4. exact changed files/diffstat;
5. proof no vendor/core/parent-theme changes;
6. before/final daily callback graph and legacy daily event state;
7. before/final shared-expiry callback graph/event state;
8. candidate automatic-expiry suppression implementation/filter and effective result;
9. owned evaluator hook/schedule/ensure behavior;
10. owned evaluator query eligibility, batch cap, ordering and timezone semantics;
11. claim/lock/idempotency behavior;
12. fixture cases and exact results;
13. fixture cleanup and restored business-data fingerprints;
14. proof package/listing expiry-date source remains unchanged;
15. proof entitlement validity remains separate/unchanged;
16. proof employer job-expiry email remains deferred and no mail was attempted;
17. candidate-alert/candidate→job protected invariants;
18. Action Scheduler/ID32733 protected state;
19. mail/network/payment/production safety counters;
20. exactly one proposed next small task, likely the owned employer job-expiry notification design/implementation if 2.01 passes, but do not start it.

STOP after publishing the report.