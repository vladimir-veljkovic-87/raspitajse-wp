# Zadatak 1.99 — Safely deactivate employer→candidate alert delivery and new creation

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.98
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact application truth from `origin/staging` and verify first that it is exactly:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

If it differs, STOP and report the mismatch.

Execute only Zadatak 1.99. Work on a scoped feature branch from exact `origin/staging`, integrate to `staging` only after all acceptance criteria pass, publish the final report through the existing `codex-reports` workflow, and STOP. Do not begin 2.00 automatically.

---

## 1. Context and decision already made

Zadatak 1.98 completed PASS and classified the employer→candidate `candidate_alert` product **DROP**.

Evidence supporting DROP is already established and must not be re-litigated in this implementation task:

- there are exactly four published legacy `candidate_alert` objects on staging;
- zero are owned by a currently valid employer + canonical employer-profile pair;
- three are admin-owned legacy objects and one is orphaned/missing-owner;
- two saved queries are empty/unconstrained, two currently match zero candidates;
- active legacy delivery can fetch only one candidate and its so-called “best candidate” heuristic cannot meaningfully rank alternatives;
- cadence/state semantics are defective: first-send frequency bypass, attempt-as-success state, no reliable retry/idempotency/claiming, concurrency duplication risk;
- current employer-facing content implies AI matching although no model/AI inference exists;
- the producer overreads personal candidate data and has a concrete cross-recipient content-reuse defect;
- no genuine staging employer adoption or paid candidate-database entitlement is implemented in this path;
- normal candidate browse/search already covers the deterministic filtering use case more completely.

The business outcome may be revisited later only as a **new product discovery**, not as preservation or migration of these four legacy alerts.

This task therefore implements the smallest safe retirement slice.

---

## 2. Goal

Safely retire active employer→candidate alert delivery and new alert creation while preserving historical auditability and all unrelated systems.

After this task:

1. `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice` must no longer be registered on the final `wp_job_board_pro_email_daily_notices` graph.
2. New employer→candidate `candidate_alert` creation must fail closed server-side.
3. The two known candidate-alert creation surfaces must no longer offer new alert creation to users.
4. Existing owner-only alert removal must continue to work.
5. Existing REST restriction must remain.
6. The four historical `candidate_alert` records and their metadata must remain byte-for-byte unchanged.
7. No final notification email is sent.
8. Candidate search/browse remains unchanged.
9. Candidate→job owned alert delivery remains unchanged.

Do not build a replacement employer→candidate matching product.

---

## 3. Implementation ownership / file policy

Prefer Raspitajse-owned implementation.

Expected primary ownership:

- `wp-content/plugins/raspitajse-communications/`

Theme-safe presentation changes may use an existing Raspitajse-owned child-theme layer only if necessary to hide a presentation surface.

Do **not** modify:

- WordPress core;
- WooCommerce/vendor core;
- WP Job Board Pro vendor source;
- Superio parent-theme vendor source.

If a creation surface cannot be safely hidden without editing WPJBP vendor code or the Superio parent theme, STOP and report the exact blocker instead of introducing a vendor fork.

Keep the patch narrow. Do not refactor unrelated communications code.

---

## 4. Exact daily-hook suppression

The active pre-task final daily-hook graph has exactly five callbacks at priority `10`:

1. `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
2. `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
3. `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`
4. `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
5. `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`

Implement an owned exact suppression after vendor registration that removes **only**:

`WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`

from:

`wp_job_board_pro_email_daily_notices`

at priority `10`.

Do not remove, re-register, wrap, reorder or otherwise alter the other four callbacks.

Do not execute the daily hook.

Acceptance after bootstrap:

- final daily callback count = exactly `4`;
- the candidate-alert vendor callback registration count = `0`;
- the four unrelated callbacks remain exactly once each at priority `10`;
- the recurring `wpie_daily` cron event itself remains exactly one scheduled event and is not unscheduled/rescheduled.

Do not replace the dropped callback with an owned sender/evaluator.

---

## 5. Fail closed new `candidate_alert` creation

Zadatak 1.84 made `Raspitajse_Communications_Alert_Security` authoritative for candidate-alert mutations. Preserve that security architecture.

For employer→candidate candidate alerts only:

- keep the exact active add routes owned by the security layer;
- preserve existing login/role/capability/nonce/type/payload validation boundaries;
- after sufficient validation to remain fail-closed and predictable, return an explicit inactive/retired-feature error response;
- do not create a post;
- do not update alert meta;
- do not silently fall through to vendor add handlers;
- vendor add callbacks must remain absent;
- anonymous add routes must remain fail closed;
- no new `candidate_alert` may be created through any of the six known AJAX route variants.

The response should be user-safe and non-PII. Do not claim temporary unavailability if the product is intentionally retired.

Do not broaden this behavior to candidate→job `job_alert` creation.

---

## 6. Preserve owner-only removal and historical auditability

Existing candidate-alert remove routes remain supported for cleanup by the rightful owner where ownership is valid.

Preserve:

- authentication;
- nonce validation;
- exact `candidate_alert` type validation;
- positive integer ID validation;
- exact `post_author === current user` ownership;
- no admin override through frontend removal;
- vendor remove callbacks absent;
- owned security callback authoritative;
- `candidate_alert.show_in_rest = false`.

Do not disable the remove handler merely because create is retired.

Do not bulk-delete, trash, unpublish, rewrite, migrate, relabel or mutate any of the four historical alerts.

Historical records must remain inspectable for auditability.

---

## 7. Hide the two exact creation surfaces

Zadatak 1.98 identified these creation surfaces:

1. candidate-search page form from WPJBP `templates/loop/candidate/candidates-alert-form.php`;
2. optional candidate-alert widget from `includes/widgets/class-widget-candidate-alert-form.php` / `templates/widgets/candidate-alert-form.php` when placed.

Hide/disable these surfaces using owned hooks/filters/template resolution/widget registration controls or child-theme-safe mechanisms.

Requirements:

- no visible “create candidate alert” form on ordinary candidate search/browse;
- no active widget creation surface when the candidate-alert widget is configured/placed;
- ordinary candidate search/browse and its filters remain unchanged;
- do not hide candidate profiles, browse results, filters, contact entitlement or unrelated widgets;
- do not edit WPJBP vendor templates or Superio parent templates.

If one surface is already unreachable under current theme/configuration, prove that and still ensure it cannot become active through the normal registered surface after this task.

Do not use JavaScript-only hiding as the authorization boundary. Server-side creation must already fail closed.

---

## 8. Management/read-only transition UX

Do not remove the historical management/listing surface if doing so would prevent auditability or owner deletion.

The active Superio override currently provides the employer candidate-alert management table.

For this task, minimum acceptable behavior is:

- historical alerts may still be listed;
- owner-only removal may remain available;
- no create/new-alert CTA is exposed from that management context;
- no UI text promises that historical alerts are still actively sending.

If the current management page already meets these minimums after the creation surfaces are hidden, do not redesign it.

If a tiny owned/child-theme-safe copy change is necessary to avoid falsely claiming active delivery, make only that bounded presentation change and report it separately.

Do not add a large retirement UX redesign.

---

## 9. Historical data preservation proof

Before any source change, capture a sanitized deterministic fingerprint of all published `candidate_alert` records and relevant meta shape, without printing raw IDs, emails, names, saved values or PII.

Expected count: `4`.

After implementation + deployment + bounded tests, prove:

- published `candidate_alert` count remains exactly `4`;
- sanitized fingerprint is byte-for-byte/logically identical to before;
- post status unchanged;
- post author unchanged;
- query meta unchanged;
- frequency meta unchanged;
- `_candidate_alert_send_email_time` unchanged;
- all other existing candidate-alert meta unchanged.

No fixture may reuse or mutate the four historical records.

---

## 10. Bounded creation/removal fixture

Create a task-private staging fixture only if needed to prove server behavior. It must be isolated and cleaned exactly.

Preferred fixture goals:

### New-create fail-closed

Prove an otherwise valid authenticated employer candidate-alert create request reaches the owned retirement boundary and:

- returns the intended inactive-feature error;
- creates zero posts;
- writes zero candidate-alert meta;
- triggers zero vendor add callback;
- sends zero mail;
- causes zero external HTTP/payment activity.

### Removal preserved

If practical without touching historical records, create a temporary fixture alert through direct controlled fixture setup **outside the retired public create path**, then prove the existing owner-only remove handler can remove only that fixture and rejects a non-owner. Clean the fixture exactly.

If fixture creation would require unsafe/broad mutation, do not force it; static/runtime callback proof plus existing 1.84 accepted security evidence may be used instead. Explain the choice.

Never use a historical alert for destructive testing.

---

## 11. Candidate→job protected invariants

This task must not regress the already-owned candidate→job subsystem.

After deployment, prove:

- direct vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` registration = `0`;
- owned hourly `raspitajse_candidate_job_alert_evaluator` event count = exactly `1`;
- evaluator schedule remains `hourly`;
- continuation event count = `0` unless a pre-existing bounded event independently exists;
- `job_alert` published count remains `0`;
- candidate-job new-create frequency UI remains the owned four values: daily/weekly/fortnightly/monthly;
- legacy getter compatibility remains available;
- `_job_alert_send_email_time` remains owned-read-only compatibility input;
- SenderPolicy/Transport behavior for candidate alerts remains unchanged.

Do not execute the owned candidate→job evaluator or continuation hook.

---

## 12. Daily scheduler / unrelated notices protection

The daily recurring event remains because four unrelated callbacks still depend on it.

Do not:

- unschedule `wp_job_board_pro_email_daily_notices`;
- execute it;
- change its recurrence;
- change job expiry or candidate expiry callbacks;
- decide retention of those four unrelated callbacks in this task.

Acceptance:

- daily recurring event count/schedule unchanged;
- final callback graph exactly `4` unrelated callbacks;
- employer→candidate candidate-alert callback `0`.

---

## 13. Action Scheduler / protected state

Before/final, capture sanitized protected state:

- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected from 1.98 historical state:

- ID32733 = `pending/0`.

Do not execute Action Scheduler runners, due actions or ID32733.

No AS action should be added for retired candidate-alert delivery.

---

## 14. Mail / network / payment safety

This task must not send an employer candidate-alert email.

Expected counters:

- real `wp_mail`: `0`;
- PHPMailer transport: `0`;
- SMTP: `0`;
- candidate-alert vendor sender executions: `0`;
- candidate→job evaluator executions: `0`;
- external application/vendor HTTP: `0`;
- payment calls: `0`.

If a bounded fixture uses the WordPress mail API accidentally, treat that as a failure unless intercepted by the existing staging mail-safety boundary and explicitly expected in the test design. Prefer no mail API call at all.

---

## 15. Source quality / validation

For every changed PHP file:

- `php -l` PASS.

For the final patch:

- `git diff --check` PASS;
- no debug logging;
- no PII logging;
- no secrets;
- no hard-coded user IDs/emails;
- no production-only URLs introduced;
- no vendor/core modifications;
- no JS-only security mechanism;
- no unrelated refactor.

Report exact changed files and diffstat.

---

## 16. Staging integration and deploy

Follow the repository workflow from `tasks/README.md`.

- branch from exact baseline;
- implement narrow owned retirement slice;
- run static tests before integration;
- integrate to `staging` only after acceptance;
- deploy only to staging;
- prove source/deploy parity;
- production remains untouched.

If deploy marker/runtime source cannot be proven equal to final `staging` SHA, STOP and report PARTIAL/FAIL rather than claiming PASS.

---

## 17. Final expected runtime state

Expected after PASS:

- final daily callbacks: `4`;
- exact remaining daily callbacks:
  1. `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
  2. `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
  3. `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
  4. `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`
- employer→candidate vendor sender registration: `0`;
- candidate→job vendor sender: `0`;
- owned candidate→job hourly evaluator: `1`;
- continuation: `0` unless independently pre-existing;
- published `job_alert`: `0`;
- published historical `candidate_alert`: `4`, fingerprint unchanged;
- candidate-alert REST: false;
- new candidate-alert create route: fail closed;
- candidate-alert owner-only remove route: preserved;
- two candidate-alert creation UI surfaces: inactive/hidden;
- ordinary candidate search/browse: unchanged;
- ID32733: `pending/0`;
- mail/SMTP/payment/cron-runner/AS executions: `0`.

---

## 18. Zero-side-effect boundaries outside intended source/deploy

Intended effects:

- narrow application source changes in Raspitajse-owned layers;
- one scoped feature commit/branch;
- fast-forward integration to `staging` after acceptance;
- staging deployment;
- optionally isolated task fixture with exact cleanup.

Forbidden effects:

- production changes/access;
- historical candidate-alert mutation;
- candidate/profile/job business-data mutation outside exact cleaned fixture;
- cron hook execution;
- Action Scheduler execution;
- real mail;
- payment;
- external vendor/application HTTP;
- vendor/core source changes.

---

## 19. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

Use previously proven namespace-free Git/filesystem/WP-CLI paths when needed. If a sandbox helper fails with the known signature, do not retry indefinitely. Never use broad process kills.

---

## 20. Final report

Publish:

**Zadatak 1.99 — Safely deactivate employer→candidate alert delivery and new creation**

Report must include:

1. result PASS/PARTIAL/FAIL and exact meaning;
2. declared baseline SHA;
3. feature branch/commit and final staging SHA;
4. staging deploy marker/runtime parity;
5. exact changed files + diffstat;
6. proof no vendor/core/parent-theme files changed;
7. exact owned callback suppression implementation;
8. before/final daily callback graph and fingerprint;
9. proof daily recurring event remains scheduled unchanged;
10. proof employer→candidate vendor sender registration `1→0`;
11. new create routes fail closed and create zero posts/meta;
12. owner-only remove routes remain authoritative;
13. REST remains false;
14. creation form/widget surfaces are inactive/hidden;
15. ordinary candidate search/browse remains available;
16. historical `candidate_alert` count/fingerprint before/final, expected `4` unchanged;
17. candidate→job vendor sender `0` and owned hourly evaluator `1`;
18. continuation count;
19. `job_alert` count/fingerprint;
20. AS count/fingerprint and ID32733 `pending/0`;
21. PHP lint + `git diff --check`;
22. fixture design/results/cleanup if used;
23. mail/PHPMailer/SMTP/network/payment/cron/AS execution counters;
24. production accessed/touched YES/NO;
25. explicit statement that the legacy employer→candidate alert product is now **retired from active delivery and new creation**, while historical records remain preserved for audit/removal.

Then STOP. Do not begin a replacement matching product or Zadatak 2.00 automatically.
