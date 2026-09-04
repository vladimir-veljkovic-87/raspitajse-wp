# Zadatak 2.03 — Retire the orphan legacy daily expiry schedule after final owned-scheduler regression

Status: READY
Baseline: d09abafb154d1e4e004696361aa28c4c7f1920e7
Previous task: 2.02
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`d09abafb154d1e4e004696361aa28c4c7f1920e7`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.03. Work on one scoped feature branch from exact `origin/staging`, integrate to `staging` only after all acceptance criteria pass, publish the final report through the existing `codex-reports` workflow, and STOP. Do not begin 2.04 automatically.

---

## 1. Context already established

Zadatak 2.01 and 2.02 completed the expiry migration:

- candidate time-based auto-expiry is retired;
- all legacy candidate/job expiry notice callbacks are absent;
- the legacy shared expiry checker callbacks are absent;
- job listing status expiry is owned by `raspitajse_job_listing_expiry_evaluator`;
- employer pre-expiry notification is owned by `raspitajse_employer_job_expiry_notice_evaluator`;
- both owned evaluators are hourly, self-healing, bounded and independent;
- candidate→job alert delivery remains separately owned and hourly;
- no active business callback remains on the legacy daily expiry-notice hook.

Final 2.02 runtime truth:

- `wp_job_board_pro_email_daily_notices` callback count = `0`;
- one old recurring WP-Cron event for that hook still exists;
- legacy event recurrence = `wpie_daily`, interval `86400`, args `[]`;
- this event is now orphaned: it has no business callback to execute;
- `wp_job_board_pro_check_for_expired_jobs` callback/event count = `0/0`;
- `raspitajse_job_listing_expiry_evaluator` = exactly one callback + one hourly event;
- `raspitajse_employer_job_expiry_notice_evaluator` = exactly one callback + one hourly event;
- candidate→job owned evaluator remains exactly one callback + one hourly event;
- Action Scheduler pending count = `7`;
- ID32733 = `pending/0`.

The purpose of 2.03 is **only** to remove the now-useless legacy daily schedule and make that retirement durable without disturbing any owned scheduler.

Do not revisit expiry business decisions in this task.

---

## 2. Goal

After PASS:

1. `wp_job_board_pro_email_daily_notices` still has exactly `0` callbacks.
2. Scheduled events for `wp_job_board_pro_email_daily_notices` are exactly `0`.
3. The legacy daily event cannot be silently recreated during ordinary bootstrap while the hook remains callback-free.
4. The owned job-status expiry evaluator remains exactly `1/hourly`.
5. The owned employer pre-expiry notification evaluator remains exactly `1/hourly`.
6. The owned candidate→job evaluator remains exactly `1/hourly`.
7. No evaluator, legacy cron hook, broad WP-Cron runner, Action Scheduler runner or mail path is executed.
8. No business data is mutated.
9. Production is untouched.

This is a scheduler-retirement task, not a lifecycle redesign.

---

## 3. Ownership and allowed source surface

Implement the durable retirement in a Raspitajse-owned layer, preferably:

`wp-content/plugins/raspitajse-communications/`

Keep the patch extremely narrow.

Do **not** modify:

- WordPress core;
- WP Job Board Pro vendor source;
- WP Job Board Pro Paid Listings vendor source;
- WooCommerce core/vendor;
- Superio parent theme;
- Raspitajse Commerce unless a concrete blocker proves it necessary;
- cron storage directly with ad-hoc SQL when WordPress scheduler APIs can perform the exact retirement.

Do not copy or edit the vendor activation/deactivation scheduler implementation.

---

## 4. Exact legacy event to retire

Target hook only:

`wp_job_board_pro_email_daily_notices`

Before source mutation, prove at runtime after plugin bootstrap:

- callback count = exactly `0`;
- scheduled event count = exactly `1`;
- recurrence is `wpie_daily`;
- interval is `86400`;
- args are `[]`;
- capture a sanitized canonical event fingerprint.

If the hook unexpectedly contains any callback or the schedule shape materially differs, STOP and report PARTIAL/FAIL rather than deleting an event whose business purpose is no longer proven.

Do not execute the event.

---

## 5. Durable owned retirement behavior

Implement one small owned bootstrap-time retirement boundary that runs only **after** vendor callbacks and the existing Raspitajse expiry suppressions have been registered/applied.

Required behavior:

1. Inspect the final callback graph for `wp_job_board_pro_email_daily_notices`.
2. Only when its final callback count is exactly `0`, clear all scheduled instances of that exact hook through the normal WordPress cron API.
3. Do not clear any other hook.
4. Be idempotent: repeated bootstraps with no legacy event produce no additional mutation and do not create anything.
5. If any callback later reappears on the legacy hook, fail safe by **not** clearing a schedule merely on assumption; do not mask a new/unclassified producer. Report this condition if encountered during task validation.
6. Do not add a replacement daily schedule.
7. Do not alter the owned hourly schedulers.

An implementation within the existing expiry boundary or one tiny dedicated scheduler-retirement component is acceptable. Avoid a generic cron cleanup framework.

The intended one-time staging state mutation is removal of the orphan legacy WP-Cron event. That exact scheduler mutation is authorized by this task.

---

## 6. Owned scheduler regression — mandatory

Before and after retirement, capture canonical sanitized rows/fingerprints for these owned WP-Cron events:

### Job status expiry

Hook:

`raspitajse_job_listing_expiry_evaluator`

Required final state:

- callback count = `1`;
- event count = `1`;
- recurrence = `hourly`;
- interval = `3600`;
- args = `[]`;
- callback identity remains `Raspitajse_Communications_Job_Listing_Expiry::run` at priority `10`;
- before/final event row logically identical unless bootstrap created a previously missing event under its already-approved self-healing contract; if that happens, report it precisely instead of hiding it.

### Employer expiry notice

Hook:

`raspitajse_employer_job_expiry_notice_evaluator`

Required final state:

- callback count = `1`;
- event count = `1`;
- recurrence = `hourly`;
- interval = `3600`;
- args = `[]`;
- callback identity remains `Raspitajse_Communications_Employer_Job_Expiry_Notification::run` at priority `10`;
- delivery state/claim schema and retry contract are unchanged.

### Candidate→job evaluator

Preserve the already-owned candidate→job evaluator:

- vendor candidate→job sender registration = `0`;
- owned evaluator callback count = `1`;
- owned recurring event count = `1`;
- recurrence = `hourly`;
- continuation event count = `0` unless a pre-existing independently justified state exists.

Do not execute any of these three owned evaluators.

---

## 7. Legacy expiry regression

Final runtime must prove:

- `wp_job_board_pro_email_daily_notices` callbacks = `0`;
- `wp_job_board_pro_email_daily_notices` events = `0`;
- `wp_job_board_pro_check_for_expired_jobs` callbacks = `0`;
- `wp_job_board_pro_check_for_expired_jobs` events = `0`;
- candidate automatic expiry calculation remains disabled by the existing owned late filter;
- no legacy candidate/job expiry sender/checker is re-registered.

Do not execute either legacy hook.

---

## 8. No business-data fixture is needed

This task should not need job/candidate/employer fixtures because it changes scheduler ownership only.

Do not create business-data fixtures merely to prove cron deletion.

Instead use bounded bootstrap/runtime inspection to prove:

1. legacy event exists before retirement;
2. the owned retirement boundary removes it;
3. a second bootstrap leaves it absent;
4. all three owned hourly scheduler rows remain correct;
5. no callback execution occurred.

If a source-level test helper is needed, keep it task-private and do not persist application/business state.

---

## 9. Protected business state

Capture sanitized before/final fingerprints for the already-protected state and prove no drift:

- candidate profiles and statuses;
- historical published `candidate_alert` count/fingerprint;
- published `job_alert` count/fingerprint;
- existing jobs count/fingerprint;
- employers/employer-users where already covered by the existing harness;
- job packages/entitlement records;
- candidate-alert retirement state;
- candidate→job alert state.

Expected current high-level invariants from 2.02 include:

- candidate profiles count `2` (`publish=1`, `pending=1`);
- historical published `candidate_alert` count `4`;
- published `job_alert` count `0`;
- existing jobs count `0`.

Do not fail solely because a sanitized hash implementation differs from a previous task; compare before/final within this task and explain the canonicalization used.

No business record mutation is authorized.

---

## 10. Action Scheduler protection

Before/final capture:

- pending Action Scheduler count and sanitized fingerprint;
- ID32733 status/attempts.

Expected historical state:

- pending = `7`;
- ID32733 = `pending/0`.

Do not execute, cancel, reschedule, claim or mutate any Action Scheduler action.

This task uses only WP-Cron scheduler APIs for the exact orphan hook.

---

## 11. Mail / network / payment safety

Expected counters for the entire task:

- `wp_mail` attempts = `0`;
- PHPMailer = `0`;
- SMTP = `0`;
- external application/vendor HTTP = `0`;
- payment calls = `0`;
- broad WP-Cron runner executions = `0`;
- legacy daily-hook executions = `0`;
- shared legacy expiry-hook executions = `0`;
- job-status evaluator executions = `0`;
- employer-notice evaluator executions = `0`;
- candidate→job evaluator executions = `0`;
- Action Scheduler runner/due-action executions = `0`.

Do not send even an intercepted test email in 2.03; mail behavior was already proven in 2.02.

---

## 12. Static/source validation

For every changed PHP file:

- `php -l` PASS.

For the final patch:

- `git diff --check` PASS;
- no debug or PII logging;
- no secrets;
- no hard-coded user IDs/emails;
- no production URLs;
- no vendor/core/parent-theme changes;
- no broad scheduler cleanup abstraction;
- no unrelated refactor.

Report exact changed files and diffstat.

---

## 13. Staging integration and deploy

Follow `tasks/README.md` exactly.

- branch from exact baseline;
- implement the narrow owned retirement boundary;
- static validation before deploy;
- deploy only to staging;
- validate the exact authorized scheduler mutation and all protected scheduler graphs;
- integrate to `staging` only after acceptance passes;
- final local/origin staging SHA, deploy marker and changed runtime file hashes must agree.

Production remains forbidden.

If source/runtime/deploy parity cannot be proven, do not claim PASS.

---

## 14. Final expected runtime state

After PASS:

- legacy daily hook callbacks: `0`;
- legacy daily scheduled events: `0`;
- legacy shared expiry callbacks/events: `0/0`;
- owned job-status expiry callback/event: `1 / 1 hourly`;
- owned employer expiry-notice callback/event: `1 / 1 hourly`;
- owned candidate→job callback/event: `1 / 1 hourly`;
- candidate→job continuation: `0` unless independently pre-existing;
- candidate auto-expiry: disabled;
- employer→candidate vendor sender: `0`;
- candidate→job vendor sender: `0`;
- candidate-alert new creation: still retired/fail-closed;
- Action Scheduler pending count/fingerprint unchanged;
- ID32733: `pending/0`;
- business fingerprints unchanged;
- mail/SMTP/network/payment/evaluator executions: `0`;
- production touched: NO.

---

## 15. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` may still occur.

If the known sandbox signature occurs, classify it once and switch to previously proven namespace-free Git/filesystem/WP-CLI paths. Do not loop retries and do not use broad process kills.

---

## 16. Final report

Publish:

**Zadatak 2.03 — Retire the orphan legacy daily expiry schedule after final owned-scheduler regression**

The report must include:

1. PASS/PARTIAL/FAIL and exact meaning;
2. declared baseline, feature branch/commit and final staging SHA;
3. deploy marker/runtime parity;
4. exact changed files + diffstat;
5. proof no vendor/core/parent-theme file changed;
6. exact implementation of the durable legacy-schedule retirement boundary;
7. before/final legacy daily callback count and event row/fingerprint;
8. proof legacy daily event changed exactly `1 → 0` and remains `0` after a second bootstrap;
9. before/final owned job-status evaluator callback/event row/fingerprint;
10. before/final owned employer-notice evaluator callback/event row/fingerprint;
11. before/final owned candidate→job callback/event/continuation state;
12. legacy shared-expiry callback/event state;
13. candidate auto-expiry disabled proof;
14. protected business fingerprints before/final;
15. Action Scheduler count/fingerprint and ID32733 before/final;
16. mail/network/payment/runner/evaluator execution counters;
17. static validation results;
18. production untouched proof;
19. exactly one proposed next task, but do not create/start it.

Do not infer or execute the next task.
