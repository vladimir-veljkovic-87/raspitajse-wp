# Zadatak 1.98 — Audit employer→candidate `candidate_alert` lifecycle and decide retention

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.97
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact application truth from `origin/staging` and verify first that it is exactly:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

If it differs, STOP and report the mismatch.

Execute only Zadatak 1.98, publish the final report through the existing `codex-reports` workflow, and STOP. Do not implement redesign and do not begin 1.99.

---

## 1. Context

The candidate→job alert subsystem has already been redesigned into a Raspitajse-owned delivery architecture. The remaining WPJBP vendor-residue provenance thread is parked; do not reopen it in this task.

The still-active opposite-direction feature is **employer→candidate alerts**, represented by WPJBP `candidate_alert` objects and the daily callback:

`WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`

Earlier communications audit found four published legacy `candidate_alert` objects on staging and identified serious concerns in the legacy employer→candidate implementation, including:

- employer/candidate lookup behavior that may rely on the wrong profile/meta relation;
- result selection apparently constrained to a very small set rather than a robust candidate result set;
- heuristic salary/experience matching while user-facing copy claims AI-like matching;
- unnecessary candidate personal-data fields such as birthdate/email being available to rendering logic;
- delivery cadence/state semantics inherited from legacy WPJBP behavior;
- unclear business value and no explicit owner decision yet on whether this feature should remain.

Zadatak 1.84 already moved candidate-alert add/remove mutation security into the Raspitajse-owned communications security layer. **Do not undo or bypass that work.**

This task is therefore a **read-only business + technical audit** whose purpose is to make the employer→candidate retention decision evidence-based before any redesign or removal.

---

## 2. Goal

Answer conclusively, from current source + staging state:

1. What business outcome does employer→candidate `candidate_alert` currently provide?
2. Who can create/manage these alerts now, and what ownership/security rules are authoritative?
3. How does the daily delivery callback determine which alerts are due?
4. How are matching candidates selected, ranked and capped?
5. What candidate fields are read, rendered or potentially exposed?
6. Does the implementation actually perform AI matching, or only deterministic/heuristic filtering?
7. What delivery-state/idempotency semantics exist, and can candidates be repeatedly re-sent?
8. What is the exact current data footprint of the four legacy alerts, without exposing PII?
9. Is the feature materially valuable for Raspitajse employers?
10. Should the business outcome be classified `KEEP`, `REDESIGN`, or `DROP`?
11. If retained, what should a Raspitajse-owned target architecture look like?
12. If dropped, what exact migration/deactivation behavior would safely preserve auditability and avoid accidental mail?

No implementation in 1.98.

---

## 3. Strict read-only scope

Allowed:

- read `origin/staging` source and Git history;
- inspect the WPJBP candidate-alert class/templates/query helpers;
- inspect Raspitajse-owned communications/security/SenderPolicy code;
- read-only WP-CLI/database queries on staging necessary to understand counts/status/meta shape;
- inspect registered hooks and scheduled events **without executing them**;
- inspect relevant configuration/options without printing secrets or recipient PII;
- produce sanitized fingerprints/counts and structural summaries.

Forbidden:

- source changes;
- application commits/branches/pushes/deploys;
- post/meta/option/user mutation;
- candidate/job/profile mutation;
- alert creation/deletion/update;
- cron schedule mutation or hook execution;
- Action Scheduler runner/action execution;
- `wp_mail`, PHPMailer or SMTP;
- payment calls;
- external application/vendor HTTP;
- production filesystem/database/backups/network access.

The normal `codex-reports` publication is the only intended repository write.

---

## 4. Exact source inventory

Identify all source paths participating in employer→candidate alert behavior, including at minimum:

- `WP_Job_Board_Pro_Candidate_Alert` class and its exact registration on `wp_job_board_pro_email_daily_notices`;
- candidate-alert query/matching helpers;
- candidate-alert email/template rendering paths;
- frequency getter/options used by this feature;
- Superio/theme template surfaces if they affect candidate-alert creation/management;
- Raspitajse-owned alert security callbacks from 1.84;
- SenderPolicy/Transport surfaces currently available for an `employer_alerts` channel;
- any custom Raspitajse code still changing candidate-alert behavior.

For every relevant source unit state:

`Path | responsibility | active/dormant | vendor/owned/theme | KEEP/REDESIGN/DROP relevance`

Do not broaden into unrelated candidate profile features.

---

## 5. Hook and scheduler graph

Read-only prove the current final registration graph after plugin bootstrap:

- WPJBP daily hook event count/schedule/fingerprint;
- exact five final daily callbacks and priorities;
- exact registration count for `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`;
- whether any Raspitajse wrapper/bridge exists around employer→candidate delivery;
- whether any other cron/action path can invoke candidate-alert delivery;
- whether Action Scheduler participates in this feature.

Do **not** execute `wp_job_board_pro_email_daily_notices`, the candidate-alert callback, owned evaluator hooks, or any scheduler runner.

Explain whether the current feature is traffic/cron dependent and whether the existing staging cron guard changes its effective behavior.

---

## 6. Current legacy alert data footprint

Inspect all published `candidate_alert` posts on staging read-only.

Expected historical count: `4`, but report actual current count.

For each alert, do **not** expose raw IDs, emails, names, saved search values or other PII. Instead report a sanitized structural row such as:

- deterministic sanitized fingerprint/token;
- post status;
- owner account existence YES/NO;
- owner role category (employer/admin/other/missing) without username/email;
- whether a canonical employer profile resolves;
- frequency key;
- whether saved query/filter payload exists and its structural type/keys only;
- legacy send/due state presence and age bucket, not exact personal timestamps unless necessary;
- whether alert appears due under current vendor rules;
- obvious orphan/inconsistent state flags.

Report aggregate counts for:

- valid employer-owned alerts;
- admin-owned legacy alerts;
- missing-owner alerts;
- malformed/empty queries;
- frequencies in use;
- currently due vs not due according to vendor logic.

No mutation and no recipient exposure.

---

## 7. Creation, ownership and mutation security

Reconfirm 1.84 behavior specifically for employer→candidate alerts:

- exact create/add routes;
- exact delete/remove routes;
- auth/anon admin-AJAX registrations;
- nonce/capability checks;
- authoritative `post_author` ownership;
- canonical employer-profile resolution;
- type/id validation;
- allowlisted payload fields;
- frequency validation;
- nested unknown-field handling;
- owner-only deletion and any admin override policy;
- REST exposure status.

Prove whether the old wrong employer-candidate lookup/meta relation is still reachable in **mutation/security** code or only in legacy delivery/matching code.

Do not change callbacks.

---

## 8. Due/cadence semantics

Document exact legacy delivery timing behavior:

- supported frequencies and day values;
- how `send_email_time` or equivalent state is interpreted;
- whether an unsent alert is immediately due;
- whether due calculation drifts from actual attempt time;
- whether failed `wp_mail` advances state;
- whether empty-result runs advance state;
- whether repeated callback executions in the same day/window can resend;
- whether there is locking/claiming/concurrency protection;
- whether missed periods are coalesced or replayed;
- timezone assumptions.

Compare these semantics to the Raspitajse-owned candidate→job delivery architecture only to identify reusable patterns. **Do not assume both products should have identical business cadence.**

Classify each timing behavior as acceptable, defective, or business-decision-dependent.

---

## 9. Matching/query pipeline

Trace employer→candidate matching end-to-end from one alert to final candidate set.

Document:

- where the saved query comes from;
- how alert owner maps to employer profile;
- all WP_Query/WPJBP query args generated;
- status/publication/visibility constraints;
- location/category/skill/experience/salary/education/language or other filters;
- any custom scoring/ranking;
- result ordering;
- result limit/cap;
- pagination behavior;
- whether only one candidate can effectively be selected;
- whether already-delivered candidates are excluded;
- whether result history/ledger exists;
- whether the same candidate may be sent repeatedly across periods.

Explicitly verify the earlier suspicion that the implementation uses the wrong employer→candidate relation/meta and state the exact consequence if true.

Do not run a real mail send. Pure read-only query inspection/counting is allowed if it does not expose PII.

---

## 10. “AI matching” claim audit

Find every employer-facing string/template/configuration that claims or implies:

- AI matching;
- smart matching;
- recommended candidates;
- personalized ranking.

Then compare that claim to actual implementation.

Classify the current matching engine as one of:

- deterministic filtering only;
- deterministic filtering + heuristics;
- explicit scoring/ranking;
- actual model/AI inference.

If there is no real AI/model inference, state that clearly and identify which copy is misleading.

Do not invent a replacement model. If retention is recommended, define the minimum honest product language for the current capability and separately describe what a future AI-powered version would require at architecture level.

---

## 11. Candidate privacy / data-minimization audit

Trace every candidate field read or passed into email/template rendering for employer alerts.

For each field classify:

`Field category | used for matching | used for rendering | necessary YES/NO | privacy risk | disposition`

Pay special attention to:

- direct email address;
- birthdate/date of birth;
- phone/contact data;
- exact address/location;
- salary expectations;
- work history/experience;
- profile image;
- public profile URL;
- any user/account identifiers.

Determine whether employers receive data they could not otherwise access through the normal candidate profile/entitlement rules.

Do not print actual field values.

Any unnecessary sensitive/personal field should be classified `DROP` from future delivery payload even if legacy code currently reads it.

---

## 12. Email producer / sender / template audit

Trace exact employer→candidate email production:

- subject generation;
- sender From/Reply-To headers;
- HTML Content-Type;
- template source and fallback path;
- placeholders/variables;
- number of candidates rendered;
- branding/localization;
- links/CTAs;
- whether candidate direct contact data is embedded;
- whether `wp_mail` result is checked;
- when delivery state is updated relative to transport result.

Determine whether this active vendor path currently uses the Raspitajse `employer_alerts` SenderPolicy channel. If not, classify sender migration as `REDESIGN` if feature retention is recommended.

Do not attempt any mail.

---

## 13. Business-value decision

Based on source + live data, answer whether employer→candidate alerts are worth retaining for Raspitajse.

Evaluate at least:

- actual number/quality of current alerts;
- whether valid employer users are actually using the feature;
- whether current matching delivers meaningful candidates;
- overlap with employer candidate-search/browse features;
- overlap with any planned paid access to worker database;
- privacy implications;
- maintenance/cron/email complexity;
- honesty of the current “AI” claim;
- strategic value of proactively notifying employers about candidates.

Choose exactly one classification:

### `KEEP`
Only if current behavior is already operationally/business sound and requires little change.

### `REDESIGN`
If the business outcome is valuable but legacy implementation should be replaced by Raspitajse-owned delivery/matching/state/privacy logic.

### `DROP`
If the feature has low business value, little genuine usage, or creates disproportionate privacy/maintenance risk.

Do not choose REDESIGN merely because candidate→job was redesigned. The direction has a different product purpose and must stand on its own evidence.

---

## 14. Target architecture if REDESIGN is recommended

If and only if final classification is `REDESIGN`, produce a bounded target design covering:

- owned evaluator/scheduler boundary;
- alert ownership/config revision;
- cadence model;
- deterministic due windows;
- locking/claims;
- matching/query adapter;
- result cap and ordering;
- delivery ledger/idempotency policy;
- data-minimized presentation DTO;
- SenderPolicy `employer_alerts` channel;
- transport success/failure state transitions;
- empty-result behavior;
- retry behavior;
- legacy lazy migration;
- treatment of the four existing alerts;
- coexistence/cutover with the existing daily WPJBP callback;
- security callbacks from 1.84 remaining authoritative.

Do not copy candidate→job architecture 1:1 where the business semantics differ.

State all business decisions still required before implementation.

---

## 15. Safe retirement design if DROP is recommended

If and only if final classification is `DROP`, specify the smallest safe future retirement plan:

- suppress/remove only the exact vendor daily candidate-alert delivery callback;
- preserve unrelated five/daily hook behavior correctly accounting for the callback removal;
- disable/hide new employer candidate-alert creation surfaces;
- preserve historical alert objects read-only unless a separate deletion/migration decision is made;
- do not send any final notification automatically;
- do not delete candidate data;
- retain auditability;
- keep 1.84 security behavior safe during transition;
- identify whether legacy alerts should become inactive via owned state or simply unreachable from runtime.

No retirement implementation in 1.98.

---

## 16. Protected current-state observation

Capture before/final read-only state:

- `origin/staging` SHA and deploy marker;
- environment exactly staging;
- staging mail-safety loaded;
- WPJBP daily cron event count/schedule/fingerprint;
- exact five daily callbacks and priorities;
- employer→candidate vendor callback registration count;
- candidate→job vendor sender registration remains 0;
- owned candidate→job hourly evaluator count/schedule/fingerprint;
- continuation count;
- published `job_alert` count/fingerprint;
- published `candidate_alert` count/fingerprint;
- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected historical state:

- staging SHA `642c8c8efb51a56449fd7048c71d3216590d52bf`;
- daily callbacks `5` including employer→candidate candidate-alert callback;
- candidate→job vendor sender `0`;
- owned hourly evaluator `1`;
- continuation `0`;
- `job_alert` `0`;
- `candidate_alert` `4`;
- ID32733 `pending/0`.

If counts differ, investigate read-only and report why. Do not repair/mutate.

---

## 17. Zero-side-effect contract

Expected effects:

- application source writes: `0`;
- application commits/branches/pushes/deploys: `0`;
- WordPress post/meta/option/user/business mutations: `0`;
- alert mutations: `0`;
- cron schedule mutations/executions: `0`;
- fixtures: `0`;
- `wp_mail` / PHPMailer / SMTP: `0 / 0 / 0`;
- application/vendor HTTP: `0`;
- payment calls: `0`;
- Action Scheduler mutations/executions: `0`;
- ID32733 executions: `0`.

Production accessed/touched: **NO**.

---

## 18. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

Prefer namespace-free Git/filesystem/WP-CLI read paths already proven safe. If a sandbox helper fails with the known signature, do not retry indefinitely. Never use broad process kills.

---

## 19. Final report

Publish:

**Zadatak 1.98 — Audit employer→candidate `candidate_alert` lifecycle and decide retention**

Report must include:

1. PASS/PARTIAL and exact meaning;
2. baseline/final application SHA unchanged;
3. source inventory and active/dormant graph;
4. exact daily hook/scheduler graph;
5. sanitized four-alert data footprint and anomalies;
6. creation/ownership/security findings;
7. due/cadence semantics;
8. matching/query pipeline and ranking/cap findings;
9. exact verdict on whether matching is AI, heuristic or deterministic;
10. privacy/data-minimization table;
11. email producer/sender/template findings;
12. exact `wp_mail`/state-update/idempotency semantics;
13. employer usage/business-value assessment;
14. final `KEEP`, `REDESIGN`, or `DROP` decision with rationale;
15. if REDESIGN: target architecture + unresolved business decisions;
16. if DROP: safe retirement design;
17. exact candidate-alert callback registration and protected candidate→job invariants;
18. `job_alert` / `candidate_alert` / AS sanitized fingerprints;
19. ID32733 `pending/0` or observed read-only state;
20. mail/SMTP/network/payment/cron/AS counters, all expected 0;
21. production accessed/touched YES/NO;
22. exactly one proposed next small task **only if the decision is sufficiently supported**; otherwise state the exact business decision needed before implementation.

Then STOP. Do not begin 1.99 automatically.
