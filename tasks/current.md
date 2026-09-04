# Zadatak 2.02 — Implement owned employer job-expiry notification on the job-only lifecycle

Status: READY
Baseline: 862c5e5c47f4172807bb898ee6253c15b178a32d
Previous task: 2.01
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact application truth from `origin/staging` and verify first that it is exactly:

`862c5e5c47f4172807bb898ee6253c15b178a32d`

If it differs, STOP and report the mismatch.

Execute only Zadatak 2.02. Work on one scoped feature branch from exact `origin/staging`, integrate to `staging` only after all acceptance criteria pass, publish the final report through the existing `codex-reports` workflow, and STOP. Do not begin 2.03 automatically.

---

## 1. Context and fixed decisions

Zadatak 2.00 classified employer job-expiry notification as **REDESIGN**. Zadatak 2.01 then established the prerequisite owned job-only expiry boundary and retired the legacy notice/lifecycle callbacks.

Current required runtime truth from 2.01:

- `origin/staging` / deploy marker = `862c5e5c47f4172807bb898ee6253c15b178a32d`;
- legacy `wp_job_board_pro_email_daily_notices` callback count = `0`;
- all four legacy expiry notice callbacks = `0`;
- legacy shared expiry callbacks = `0`;
- candidate automatic expiry calculation is forced disabled by the owned late filter;
- owned job expiry hook = `raspitajse_job_listing_expiry_evaluator`;
- owned job expiry evaluator callback count = exactly `1`;
- owned job expiry event count = exactly `1`, schedule `hourly`;
- job expiry evaluator batch size = `50`, global claim TTL = `600` seconds;
- owned evaluator consumes canonical `_job_expiry_date` and expires only `publish` jobs whose valid `Y-m-d` expiry date is strictly before the current WordPress site-calendar date;
- no replacement expiry email sender currently exists;
- `Raspitajse_Communications_Sender_Policy::CHANNEL_JOB_EXPIRY` already exists;
- staging job-expiry sender resolves to the approved staging system identity through SenderPolicy;
- `Raspitajse_Communications_Transport::send()` is the approved semantic-channel transport boundary;
- package-entitlement validity and published-listing lifetime are separate clocks and must remain separate.

Do not restore or reuse the legacy WPJBP employer notice sender/template/state model.

---

## 2. Goal

Implement one Raspitajse-owned employer notification that warns the canonical employer that a published job listing is approaching its listing expiry.

After PASS:

1. Exactly one owned employer job-expiry notification evaluator exists.
2. It uses the canonical `_job_expiry_date`; it does not derive expiry from package entitlement validity.
3. The notification window is deterministic and calendar-based.
4. A job/expiry-date pair can be successfully delivered at most once.
5. Failed/interrupted attempts remain retryable without duplicate successful delivery.
6. Overlapping workers cannot send the same job/expiry-date concurrently.
7. Recipient resolution is canonical, validated and employer-owned.
8. Sending occurs only through `Raspitajse_Communications_Transport::send()` with `CHANNEL_JOB_EXPIRY`.
9. Subject/body are Raspitajse-owned and do not depend on WPJBP vendor expiry templates/options.
10. The 2.01 job status expiry evaluator remains unchanged in behavior and schedule.
11. Candidate expiry/admin notices remain retired.
12. No production access/touch.

---

## 3. Scope and ownership

Prefer implementation inside:

- `wp-content/plugins/raspitajse-communications/`

A small owned class/file plus one owned template file is acceptable and preferred over enlarging the main plugin file if that keeps responsibilities clear.

Do **not** modify:

- WordPress core;
- WP Job Board Pro vendor source;
- WP Job Board Pro WooCommerce Paid Listings vendor source;
- WooCommerce vendor/core;
- Superio parent theme;
- legacy WPJBP expiry email templates;
- Raspitajse Commerce entitlement policy except read-only inspection if needed.

Do not recreate the old vendor callback 1:1.

---

## 4. Notification calendar semantics

Use the WordPress site timezone/calendar APIs (`current_datetime()`, `wp_timezone()`, immutable date objects or equivalent).

For 2.02, the business notification window is exactly:

**one site-calendar day before the listing's canonical `_job_expiry_date`**.

A job is initially due for notification only when:

- post type = `job_listing`;
- post status = `publish`;
- `_job_expiry_date` is a valid real `Y-m-d` date;
- expiry date equals **tomorrow** in the WordPress site timezone;
- the same job + exact expiry-date revision has not already been successfully delivered.

Do not notify for:

- missing/empty/malformed expiry date;
- expiry date today;
- past expiry date;
- expiry more than one site-calendar day away;
- non-published job;
- already successfully delivered same job/expiry-date revision.

If the job expiry date later changes, the new date is a new notification revision and may become eligible independently when it reaches its own one-day-before window.

Do not use raw PHP `date()` against a legacy adjusted timestamp.

---

## 5. Scheduling and evaluator boundary

Create one dedicated owned notification hook, for example:

`raspitajse_employer_job_expiry_notice_evaluator`

Use one simple recurring WP-Cron event. `hourly` is preferred.

Requirements:

- exactly one registered callback at priority `10`;
- exactly one recurring hourly event;
- self-healing `ensure_schedule()` behavior on bootstrap/request, analogous in safety to 2.01;
- repeated bootstrap must not create duplicates;
- deactivation clears only this component's own event;
- do not use the empty legacy daily hook;
- do not use the legacy shared expiry hook;
- do not use Action Scheduler;
- do not execute a broad WP-Cron runner.

The status-expiry evaluator from 2.01 must remain a separate hook/event and must not be merged with email delivery.

---

## 6. Bounded selection

Notification evaluation must be bounded and deterministic.

Use an explicit batch cap, recommended `50` jobs per run.

Selection order should be stable, for example expiry date then post ID.

Before attempting each send, re-read and revalidate:

- post type;
- `publish` status;
- current exact `_job_expiry_date`;
- current site-calendar eligibility;
- current delivery state for that exact expiry-date revision.

Stale query results must not send a notification after the job was unpublished, expired, deleted, or had its expiry date changed.

---

## 7. Canonical employer recipient

Resolve the recipient from the job's canonical employer ownership relation. Reuse the already-established canonical Raspitajse/WPJBP employer mapping where available; do not reintroduce the previously identified reverse-meta employer lookup defect.

Recipient rules:

1. Resolve the authoritative employer user/profile for the job.
2. Require a valid employer user/profile relationship, not an arbitrary admin or unrelated post author.
3. Prefer the canonical employer profile email if present and valid.
4. A fallback to the canonical employer account email is acceptable only when it belongs to the same validated employer identity and the profile email is absent/invalid.
5. Validate with `is_email()` before send.
6. If no canonical valid recipient exists, fail that record safely and leave it retryable only if a later data correction could make it deliverable.

Do not send to:

- site admin merely as fallback;
- unrelated post author;
- candidate email;
- hard-coded address.

Do not log raw recipient email, user ID, profile ID or other PII in the report.

---

## 8. Delivery state and idempotency

Implement owned per-job/per-expiry-date delivery state. Do not rely on the legacy `_job_alert_send_email_time` or vendor expiry options.

The state model must distinguish at minimum:

- never attempted / eligible;
- in-flight claim;
- failed/retryable attempt;
- successfully delivered.

Successful delivery identity is:

`job + exact canonical _job_expiry_date`

Requirements:

- once Transport reports successful delivery for a revision, future evaluator runs must not send that same revision again;
- failed sends must **not** be marked delivered;
- a changed expiry date is treated as a new revision;
- stale in-flight state can recover automatically;
- state must remain bounded and local to the job/notification purpose;
- do not create a new database table for this slice unless an unavoidable concrete blocker is proven first.

Use Raspitajse-owned meta keys/namespaces. Do not overwrite vendor expiry meta.

---

## 9. Concurrency claim and retry behavior

Prevent concurrent duplicate sends for the same revision.

A small atomic claim mechanism is required. It may be per-job revision or a global evaluator claim plus exact per-revision state, provided concurrency is actually proven safe.

Requirements:

- atomic claim acquisition;
- collision-resistant ownership token where applicable;
- bounded stale-claim TTL, recommended `600` seconds;
- exact-token release/recovery;
- second overlapping worker cannot send the same revision;
- stale claim can recover after TTL;
- claim artifacts are removed/settled after success/failure.

Retry policy must be bounded. For transient Transport/mail failure, implement a small capped retry state with deterministic backoff; recommended contract for this task:

- maximum `3` attempts for one job/expiry-date revision;
- attempts occur only while the listing is still `publish` and still has the same expiry date;
- retries must not extend beyond the last active day represented by that expiry-date revision;
- no infinite retry loop;
- no immediate tight-loop retry inside one evaluator invocation.

If recipient data is invalid/missing, record a safe retryable failure state without sending; do not invent a recipient.

---

## 10. Owned template/content

Do not use the WPJBP `employer_notice_expiring_listing` vendor template or its option body as authoritative content.

Create a Raspitajse-owned template/rendering boundary with a narrow explicit view model.

Minimum content fields:

- job title;
- canonical listing expiry date formatted in the site timezone;
- job/listing URL if safely available;
- employer dashboard / my-jobs URL only if a canonical existing helper can produce it safely.

Do not expose:

- candidate data;
- package entitlement validity date as if it were job expiry;
- order/payment data;
- internal IDs;
- admin edit URLs;
- secrets or debug values.

Requirements:

- HTML escaped appropriately;
- subject escaped/sanitized;
- translatable strings / WordPress i18n, not a vendor-branded hard-coded blob;
- no claim that AI generated or selected the expiry;
- copy must clearly describe listing expiry, not account/package expiry.

The report may include subject/template fingerprints and variable names, but not raw recipient addresses or a full rendered private email body.

---

## 11. SenderPolicy / Transport boundary

Every real application send attempt must go only through:

`Raspitajse_Communications_Transport::send( Raspitajse_Communications_Sender_Policy::CHANNEL_JOB_EXPIRY, ... )`

Acceptance:

- no direct vendor `WP_Job_Board_Pro_Email::wp_mail()` usage in the new feature;
- no direct unmanaged `wp_mail()` call from the new evaluator/mailer;
- no caller-provided From/Reply-To override;
- SenderPolicy supplies the semantic sender contract;
- staging sender contract resolves successfully;
- unsupported/missing production sender configuration remains fail-closed per existing policy;
- do not change SMTP credentials or global transport architecture.

---

## 12. Isolated no-real-mail staging fixture

A deterministic staging fixture is required, using only disposable task-private jobs/employer identity.

Do not use real/historical business records.

Intercept mail **before PHPMailer/SMTP** using a task-private bounded mechanism such as `pre_wp_mail` so the application path through `Transport::send()` is exercised but no real transport occurs.

The fixture must prove at minimum:

1. tomorrow-expiry published job + valid canonical employer is selected once;
2. captured send uses `CHANNEL_JOB_EXPIRY` sender headers/policy;
3. successful intercepted delivery marks only that job/expiry-date revision delivered;
4. second evaluator run does not send it again;
5. today expiry is not selected as a new initial notice;
6. future >1 day, past, malformed, missing/empty and non-published jobs are not selected;
7. changing the delivered fixture to a different future expiry date creates a new revision, but it is not eligible until that new date reaches tomorrow;
8. simulated Transport/mail failure does not mark delivered and increments bounded attempt state;
9. a later eligible retry can succeed and then becomes idempotent;
10. overlapping claim attempt cannot produce duplicate send;
11. stale claim recovery works;
12. missing/invalid canonical recipient produces zero mail attempt and safe state.

No PHPMailer/SMTP network attempt is allowed.

The fixture may invoke only the **owned notification evaluator** directly. Do not execute broad cron, legacy notice hooks, the 2.01 status evaluator, candidate→job evaluator or Action Scheduler runner.

Cleanup all disposable posts/users/profiles/meta/options/claims exactly. After cleanup, real staging job/employer/package/candidate fingerprints must equal pre-fixture business state.

Do not print fixture emails or IDs in the report.

---

## 13. Preserve 2.01 job expiry lifecycle

The existing `Raspitajse_Communications_Job_Listing_Expiry` behavior is protected.

Final proof must show:

- status evaluator hook unchanged;
- callback count = `1`;
- schedule count = `1`, `hourly`;
- batch size remains `50`;
- claim TTL remains `600` unless there is a compelling bug fix explicitly required by this task (otherwise no change);
- candidate expiry filter remains late and effective;
- legacy daily callbacks remain `0`;
- legacy shared expiry callbacks remain `0`;
- no employer mail is sent from the status evaluator itself;
- `_job_expiry_date` writer path remains vendor/Paid Listings-owned and unchanged.

Do not combine status expiry and email notification into one callback.

---

## 14. Package entitlement separation

Prove unchanged:

- Raspitajse Commerce package entitlement validity remains the existing 30-calendar-day consumption rule;
- notification eligibility never queries entitlement valid-until as a substitute for `_job_expiry_date`;
- a package expiring does not itself trigger this email;
- a published job's listing duration remains independent from unused package validity.

No entitlement recalculation or migration.

---

## 15. Existing alert / historical protected state

Before/final prove unchanged:

- employer→candidate vendor sender = `0`;
- new `candidate_alert` creation remains retired/fail-closed;
- historical published `candidate_alert` count = `4`, fingerprint unchanged;
- candidate→job vendor sender = `0`;
- owned candidate→job evaluator event/callback = exactly `1 hourly`;
- candidate→job continuation = `0` unless independently pre-existing;
- published `job_alert` = `0`;
- candidate profile statuses/expiry footprint unchanged.

Do not execute either alert-delivery subsystem.

---

## 16. Action Scheduler / legacy scheduler protection

Capture before/final:

- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts;
- legacy daily event state;
- legacy shared expiry event state.

Expected historical protected state from 2.01:

- pending AS count `7`;
- ID32733 = `pending/0`;
- legacy daily event still exists but has zero callbacks;
- legacy shared expiry event count = `0`.

Do not run, mutate or clean Action Scheduler in 2.02.

Do not clean the orphan legacy daily WP-Cron event here; that belongs to later final scheduler cleanup.

---

## 17. Mail/network/payment safety

Authorized test behavior is limited to **intercepted** `wp_mail` calls produced by the new `Transport::send()` path in the task-private fixture.

Required counters/meaning:

- real external email deliveries: `0`;
- PHPMailer SMTP sends: `0`;
- application external HTTP: `0`;
- payment calls: `0`;
- legacy expiry sender executions: `0`;
- candidate→job evaluator executions: `0`;
- job status expiry evaluator executions: `0` during this fixture;
- broad WP-Cron runner executions: `0`;
- Action Scheduler runner/due-action executions: `0`.

Report intercepted `wp_mail` attempt counts only as sanitized numbers; no addresses or full bodies.

---

## 18. Static/source quality

For every changed PHP file:

- `php -l` PASS.

For final patch:

- `git diff --check` PASS;
- no debug/PII logging;
- no secrets;
- no hard-coded user IDs/emails;
- no production URLs introduced;
- no vendor/core/parent-theme modifications;
- no second listing-expiry source of truth;
- no entitlement-expiry conflation;
- no direct unmanaged sender override;
- no unrelated refactor.

Report exact changed files and diffstat.

---

## 19. Staging integration/deploy

Follow `tasks/README.md` exactly.

- branch from exact baseline;
- implement one focused owned slice;
- run static and isolated fixture acceptance;
- integrate to `staging` only after acceptance passes;
- deploy only to staging;
- prove final local staging, `origin/staging`, deploy marker and runtime source parity;
- production remains untouched.

If runtime/deploy parity cannot be proven equal to final staging SHA, report PARTIAL/FAIL rather than PASS.

---

## 20. Final expected runtime state

After PASS, expected runtime includes:

- owned job status expiry evaluator: `1`, hourly;
- owned employer job-expiry notification evaluator: `1`, hourly;
- candidate automatic expiry disabled;
- legacy daily expiry notice callbacks: `0`;
- legacy shared expiry callbacks: `0`;
- legacy employer expiry sender: `0`;
- canonical notification source = `_job_expiry_date`;
- initial notification window = exactly one site-calendar day before expiry date;
- notification sends only through `Transport::send(CHANNEL_JOB_EXPIRY, ...)`;
- successful delivery idempotent per job + exact expiry date;
- failed delivery retryable with max 3 attempts and no tight loop;
- no duplicate overlapping delivery;
- no real SMTP/email delivered during fixture;
- historical candidate/candidate-alert/job-alert/package data unchanged after cleanup;
- pending AS protected state unchanged;
- ID32733 remains `pending/0`;
- production touched: NO.

---

## 21. Final report

Publish:

**Zadatak 2.02 — Implement owned employer job-expiry notification on the job-only lifecycle**

Report must include:

1. PASS/PARTIAL/FAIL and exact meaning;
2. declared baseline, feature branch/commit, final staging SHA;
3. deploy marker/runtime parity;
4. exact changed files + diffstat;
5. proof no vendor/core/parent-theme files changed;
6. notification hook/event names, callback counts and schedule;
7. exact one-day-before calendar semantics and timezone handling;
8. query batch cap/order and per-record revalidation;
9. canonical employer recipient resolution rules and sanitized fixture proof;
10. owned delivery-state keys/model and per-expiry-date revision identity;
11. concurrency claim TTL/ownership/stale recovery proof;
12. max-attempt/backoff retry proof;
13. owned template location/view-model and sanitized subject/body fingerprints;
14. proof all sends route only through `Transport::send(CHANNEL_JOB_EXPIRY)`;
15. intercepted no-real-mail fixture matrix and counters;
16. proof successful second run is idempotent;
17. proof failed attempt remains retryable and later success marks delivered once;
18. proof invalid recipient causes zero send attempt;
19. proof 2.01 job status evaluator behavior/schedule unchanged;
20. proof package entitlement validity remains separate;
21. candidate/candidate-alert/job-alert protected fingerprints;
22. Action Scheduler + ID32733 before/final state;
23. mail/SMTP/network/payment/cron/AS execution counters;
24. cleanup proof;
25. production touched: NO;
26. exactly one proposed next task, but do not create or start it.

STOP after publishing the report.