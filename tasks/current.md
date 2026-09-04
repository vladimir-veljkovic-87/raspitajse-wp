# Zadatak 2.00 — Audit remaining job/candidate expiry notices and expiry lifecycle decisions

Status: READY
Baseline: d9a095893b3751051135333e08dd3a0148db6d9b
Previous task: 1.99
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting source/runtime. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact application truth from `origin/staging` and verify first that it is exactly:

`d9a095893b3751051135333e08dd3a0148db6d9b`

If it differs, STOP and report the exact mismatch.

Execute only Zadatak 2.00, publish the final report through the existing `codex-reports` workflow, and STOP. This is a read-only audit. Do not implement suppression/redesign and do not begin 2.01 automatically.

---

## 1. Context and binding business decisions

Zadatak 1.99 retired employer→candidate `candidate_alert` delivery and creation. The final WPJBP daily hook now intentionally retains exactly four unrelated expiry-notice callbacks:

1. `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
2. `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
3. `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
4. `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`

Candidate→job alerts remain on the owned hourly evaluator and are out of scope except as protected invariants.

The owner has made a binding current product decision:

**Candidate profiles do NOT auto-expire merely because time passes.**

Do not reinterpret this as a request to invent a candidate expiry window. A future stale-profile confirmation/reminder product may be considered separately, but it is not the same as legacy candidate expiry and must not be assumed here.

Job listings are a different business object. Their listing duration/expiry may remain a legitimate product rule and must be audited independently from candidate profiles.

The purpose of 2.00 is to decide, with current source/runtime evidence, what to KEEP / REDESIGN / DROP for the remaining expiry lifecycle and notifications.

---

## 2. Goal

Answer conclusively:

1. What exact lifecycle makes a `job_listing` expire today, and is that lifecycle still required by Raspitajse package/job-duration rules?
2. What exact lifecycle can make a `candidate` expire today, whether it is registered/scheduled/reachable, and whether it conflicts with the owner decision that candidate profiles do not auto-expire?
3. What configuration/options gate each of the four remaining daily notice callbacks?
4. Which callbacks are operationally active, dormant-by-config, or dangerous-if-enabled?
5. What actual staging job/candidate expiry data exists, without exposing PII?
6. Who receives each notice, what content/template is used, and what sender/header path is used?
7. Does any retained notice use the Raspitajse SenderPolicy/Transport architecture, especially the existing `job_expiry` channel?
8. Are there duplicate-send, success-state, timezone, batching, or privacy issues?
9. For each of the four callbacks, choose exactly one: `KEEP`, `REDESIGN`, or `DROP`.
10. Separately classify the underlying **job expiry lifecycle** and **candidate expiry lifecycle** as `KEEP`, `REDESIGN`, or `DROP`.
11. If the evidence justifies one small next task, propose exactly one bounded 2.01 task; otherwise state that no implementation task is justified yet.

No implementation in 2.00.

---

## 3. Strict read-only scope

Allowed:

- read `origin/staging` source and Git history;
- read-only WordPress bootstrap/WP-CLI/database inspection on staging;
- inspect options/configuration without printing secrets;
- inspect registered hooks and scheduled cron events **without executing them**;
- inspect job/candidate counts, statuses, expiry-meta shape and sanitized fingerprints;
- inspect email subject/content configuration structurally without printing recipient PII or full rendered bodies;
- inspect Raspitajse communications SenderPolicy/Transport code and active registrations.

Forbidden:

- application source changes;
- application commits/branches/pushes/deploys;
- post/meta/option/user mutation;
- job/candidate/profile status changes;
- expiry-date changes;
- cron schedule mutation or hook execution;
- Action Scheduler runner/action execution;
- `wp_mail`, PHPMailer or SMTP;
- payment calls;
- external application/vendor HTTP;
- production filesystem/database/backups/network access.

Only normal `codex-reports` publication may write to the repository.

---

## 4. Exact source inventory

At minimum inspect:

- `wp-content/plugins/wp-job-board-pro/includes/class-candidate.php`
- `wp-content/plugins/wp-job-board-pro/includes/class-job_listing.php`
- `wp-content/plugins/wp-job-board-pro/includes/class-email.php`
- relevant WPJBP option/settings definitions for candidate/job expiry and notice toggles;
- relevant cron/scheduler registration code for expiry checks;
- relevant default/configured email templates for the four notices;
- `wp-content/plugins/raspitajse-communications/` SenderPolicy/Transport and current retirement/evaluator registrations;
- package/job-duration logic only where necessary to establish whether job expiry is a genuine Raspitajse business requirement.

For each relevant unit report:

`Path | responsibility | runtime reachability | vendor/owned/theme | business disposition relevance`

Do not broaden into unrelated candidate/job features.

---

## 5. Reconfirm final daily graph after 1.99

Read-only prove after plugin bootstrap:

- recurring `wp_job_board_pro_email_daily_notices` event count = exactly `1`;
- exact schedule/interval/timestamp/fingerprint;
- final callback count = exactly `4`;
- exact callbacks are the four listed in section 1, each once at priority `10`;
- employer→candidate `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice` registration remains `0`;
- candidate→job vendor sender remains `0`;
- owned candidate→job hourly evaluator remains exactly `1`;
- continuation remains `0` unless independently pre-existing.

Do not execute any of these hooks.

Also determine whether any separate cron hook, Action Scheduler action, init/request path, or external runner can invoke:

- `WP_Job_Board_Pro_Candidate::check_for_expired_candidates`
- `WP_Job_Board_Pro_Job_Listing::check_for_expired_jobs`

Report exact registration/reachability and schedule, if any. Do not run them.

---

## 6. Candidate expiry lifecycle — binding owner decision

Trace the full candidate expiry lifecycle from profile creation/update through any `expiry_date` calculation and eventual status transition.

Document exactly:

- current `resume_duration` / equivalent option value and its meaning;
- where candidate expiry dates are calculated/written;
- whether existing candidate posts actually contain expiry-date meta;
- whether `check_for_expired_candidates()` is registered anywhere;
- whether it can change `publish → expired` automatically under current runtime;
- whether any separate delete/trash-old-expired-candidates path is reachable;
- whether profile editing/reactivation recalculates expiry;
- current counts by sanitized status/expiry bucket.

Important source-safety question:

`WP_Job_Board_Pro_Candidate::get_expiring_candidates()` currently appears to reference `$wpdb` without declaring/importing it in that method. Verify this exact fact from current source and explain the consequence if either candidate expiry-notice callback were enabled and reached the helper. Do **not** execute the defective path merely to prove the failure.

Business rule for classification:

- automatic candidate expiry based only on elapsed time conflicts with the current owner decision and therefore cannot be classified KEEP as a product outcome;
- a possible future “please confirm your profile is still current” reminder is a separate product and must not be used to justify retaining legacy expiry machinery.

Choose a final disposition for:

- candidate auto-expiry lifecycle;
- admin candidate-expiry notice;
- candidate self-expiry notice.

---

## 7. Job expiry lifecycle

Trace the job-listing expiry lifecycle independently.

Document exactly:

- where `_...expiry_date` is calculated/written for jobs;
- how duration is derived from WPJBP/package/product data;
- interaction with the Raspitajse package rules already implemented in `raspitajse-commerce` (package entitlement validity is separate from the duration of an already-published job);
- current package/job durations if source/configuration makes them authoritative;
- whether `check_for_expired_jobs()` is registered/reachable;
- exact `publish → expired` behavior;
- whether old expired jobs can be trashed and whether that path is enabled/reachable;
- current staging job counts/statuses and expiry-date buckets, sanitized and without titles/owners/PII.

Explicitly separate these two concepts:

1. package entitlement validity — time window in which an employer may consume package ads;
2. individual published job listing duration — how long the published job remains active.

Determine whether automatic **job listing expiry** is a legitimate business requirement even if expiry-email notices are disabled or redesigned.

Choose a final disposition for the underlying job expiry lifecycle.

---

## 8. Four notice callbacks — configuration and due semantics

For each callback inspect exact option keys and current values:

- enable/disable flag;
- days-before-expiry value;
- template subject/content source;
- configured content non-empty YES/NO;
- recipient resolution;
- query used to select expiring objects;
- date/timezone calculation;
- whether selection is exact-date only, range-based, or otherwise;
- whether repeated daily execution can resend the same notice;
- whether any persistent sent ledger/marker exists;
- whether `wp_mail` result is checked;
- what happens after mail failure;
- batching/result limits;
- behavior if recipient cannot be resolved.

Do not send mail.

For each callback report:

`Callback | enabled now | days | due objects now (count only) | recipient category | delivery-state model | active/dormant | major defects | KEEP/REDESIGN/DROP`

---

## 9. Sender / email template / privacy audit

Trace the exact mail production path for all four notices.

Document:

- From behavior;
- Reply-To behavior;
- Content-Type;
- recipient source;
- subject/content renderer;
- configured-content vs default-template fallback;
- key variables made available to the template;
- links/CTAs;
- whether any candidate/employer personal data beyond what is necessary is exposed;
- whether admin notices contain data that adds operational value.

Determine whether any callback currently enters Raspitajse SenderPolicy/Transport.

Specifically inspect the existing `job_expiry` SenderPolicy channel introduced earlier and state:

- whether it is currently used by these vendor callbacks;
- whether it is suitable for a future owned employer job-expiry notice if that outcome is retained;
- whether admin notices should use `system` instead if retained.

Do not create a new sender channel in this task.

---

## 10. Business-value decision per notice

Classify each callback separately, based on actual current data/config and product value:

### Candidate admin expiry notice
Given that candidate profiles do not auto-expire, determine whether this notice has any valid business purpose. Do not retain it merely because vendor code exists.

### Candidate self expiry notice
Same rule: do not tell candidates their profile is “expiring” if Raspitajse has decided profiles do not auto-expire. A future stale-data confirmation reminder must be treated as a new product.

### Job admin expiry notice
Determine whether notifying site administrators before each job expires provides meaningful operational value or only noise/duplicate vendor behavior.

### Employer job expiry notice
Determine whether notifying the employer that a published job is approaching its actual listing end provides useful business value. If yes but implementation/sender/state is weak, prefer `REDESIGN` rather than preserving vendor mail logic.

For each choose exactly one:

- `KEEP` — current outcome and implementation are sufficiently sound;
- `REDESIGN` — business outcome is worth retaining, but should move to a Raspitajse-owned implementation;
- `DROP` — business outcome is unnecessary/misleading/risky.

Do not choose REDESIGN automatically; justify it with product value.

---

## 11. If job employer notice is retained, define only a bounded target design

If `send_employer_expiring_notice` is classified `REDESIGN`, provide a concise target design only, not implementation:

- owned scheduler/evaluator boundary;
- canonical job-expiry source;
- deterministic “N days before actual expiry” window;
- one notice per job/window;
- idempotency/claim behavior;
- no notice for already expired/unpublished/cancelled jobs;
- recipient resolved from canonical employer/user ownership;
- SenderPolicy `job_expiry` channel;
- transport result handling;
- no package-entitlement confusion;
- no duplicate notices after delayed cron runs;
- template/content ownership in Raspitajse-owned/configured layer.

State any business decision still needed, especially the preferred days-before-expiry value, only if current configuration/product evidence does not already resolve it.

---

## 12. Actual staging data footprint

Read-only capture sanitized counts/fingerprints for:

### Candidates
- total candidate posts by relevant status;
- published candidates with expiry meta;
- empty expiry meta;
- already-past expiry values;
- future expiry values by coarse age bucket;
- candidate records that legacy notice code would consider due today, count only.

### Jobs
- total job listings by relevant status;
- published jobs with expiry meta;
- expired jobs;
- past/future/empty expiry values by coarse bucket;
- jobs the admin/employer notice code would consider due today, count only.

Do not output raw post IDs, titles, owner names/emails, candidate names, or exact private data.

If zero live jobs means a notice cannot be product-validated from staging usage, say so explicitly rather than inventing demand.

---

## 13. Protected-state observation

Capture before/final read-only state and prove unchanged:

- `origin/staging` SHA and deploy marker;
- environment exactly staging;
- staging mail safety loaded;
- daily event count/schedule/fingerprint;
- exact four daily callbacks/fingerprint;
- candidate-alert sender remains `0`;
- candidate→job vendor sender `0`;
- candidate→job hourly evaluator `1`;
- continuation `0` unless independently pre-existing;
- published `job_alert` count/fingerprint;
- published historical `candidate_alert` count/fingerprint;
- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected from 1.99:

- staging SHA `d9a095893b3751051135333e08dd3a0148db6d9b`;
- daily callbacks `4`;
- employer→candidate sender `0`;
- candidate→job vendor sender `0`;
- owned hourly evaluator `1`;
- continuation `0`;
- `job_alert` `0`;
- historical `candidate_alert` `4`;
- ID32733 `pending/0`.

If any expected value differs, investigate read-only and report the cause. Do not repair it in this task.

---

## 14. Zero-side-effect contract

Expected effects:

- application source writes: `0`;
- application commits/branches/pushes/deploys: `0`;
- WordPress post/meta/option/user/business mutations: `0`;
- job/candidate/profile mutations: `0`;
- cron schedule mutations/executions: `0`;
- fixtures: `0`;
- `wp_mail` / PHPMailer / SMTP: `0 / 0 / 0`;
- application/vendor HTTP: `0`;
- payment calls: `0`;
- Action Scheduler mutations/executions: `0`;
- ID32733 executions: `0`.

Production filesystem/database/backups/network/application: **NO ACCESS / NO TOUCH**.

---

## 15. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

Prefer namespace-free Git/filesystem/WP-CLI read paths already proven safe. If a helper fails with the known signature, do not retry indefinitely. Never use broad process kills.

---

## 16. Final report

Publish:

**Zadatak 2.00 — Audit remaining job/candidate expiry notices and expiry lifecycle decisions**

Report must include:

1. PASS/PARTIAL and exact meaning;
2. baseline/final SHA proof;
3. exact final four-callback daily graph and event state;
4. separate registration/reachability of `check_for_expired_candidates` and `check_for_expired_jobs`;
5. source inventory;
6. candidate expiry lifecycle and current configuration/data footprint;
7. explicit verification of the `$wpdb` defect or proof it does not exist;
8. job expiry lifecycle and package-vs-listing-duration distinction;
9. exact current notice option values for all four callbacks;
10. sanitized due-object counts;
11. recipient/sender/template/content-path audit;
12. SenderPolicy/Transport usage and `job_expiry` channel relevance;
13. per-notice KEEP / REDESIGN / DROP decision with reasons;
14. candidate auto-expiry lifecycle KEEP / REDESIGN / DROP;
15. job listing expiry lifecycle KEEP / REDESIGN / DROP;
16. if employer job-expiry notice is REDESIGN, bounded owned target design;
17. protected job_alert/candidate_alert/AS fingerprints and ID32733 `pending/0`;
18. zero mail/SMTP/network/payment/cron/AS execution counters;
19. production accessed/touched YES/NO;
20. exactly one proposed next small task only if evidence justifies it.

Then STOP. Do not begin 2.01 automatically.
