# Zadatak 1.90 — Candidate-job alert scoped frequency UI and post-cutover observation

Status: READY
Baseline: 0b00f2c5a8d191dcf7b6bc78b82e1bff8f680807
Previous task: 1.89
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Do not use application files inherited on `codex-tasks` as source truth. All application reads/diffs/work MUST use the declared `origin/staging` baseline.

Verify exact baseline first. If `origin/staging` is not exactly `0b00f2c5a8d191dcf7b6bc78b82e1bff8f680807`, STOP and report the mismatch.

Execute exactly this task. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not infer or begin 1.91.

---

## 1. Context

Zadatak 1.89 is PASS and candidate→job delivery is now actively owned by Raspitajse:

- dedicated hourly hook `raspitajse_candidate_job_alert_evaluator` exists exactly once;
- candidate→job callback was removed from `wp_job_board_pro_email_daily_notices`;
- the WPJBP daily hook still has exactly five unrelated legacy callbacks;
- owned query adapter/mailer/evaluator are active;
- `candidate_alerts` SenderPolicy remains canonical;
- `_job_alert_send_email_time` is legacy-read-only for lazy migration;
- new-alert backend validation allows only `daily`, `weekly`, `fortnightly`, `monthly`;
- legacy `biannually` and `annually` remain compatibility-only for existing records.

Known remaining issue from 1.89:

The current WPJBP frontend frequency getter still exposes all six legacy frequency choices. Backend correctly rejects `biannually` and `annually` for **new** candidate-job alerts. No safe global getter filter was used because it could break labels/behavior for legacy records and adjacent alert surfaces.

This task is a small UI-alignment + post-cutover observation slice only.

---

## 2. Goal

Implement the smallest update-safe **candidate-job-alert create-form-only** UI seam so new alert creation exposes exactly:

- daily;
- weekly;
- fortnightly;
- monthly.

Do not globally change the WPJBP frequency source of truth for existing records.

Also perform bounded read-only/post-cutover observation of the 1.89 active graph and owned scheduler state without broadly executing cron.

---

## 3. Scope boundaries

Prefer changes only in Raspitajse-owned code.

Allowed:

- `wp-content/plugins/raspitajse-communications/`;
- a Raspitajse-owned frontend integration asset/template seam only if proven necessary and update-safe.

Do NOT redesign:

- matching/query behavior;
- delivery state machine;
- mail templates/content;
- SenderPolicy;
- employer→candidate alerts;
- expiry notifications;
- WooCommerce;
- Action Scheduler;
- the five remaining WPJBP daily callbacks.

Do not modify WPJBP vendor code unless a create-form-only seam is impossible without a minimal compatibility change. If vendor edit appears necessary, first document exact evidence and prefer STOP over a broad vendor patch.

---

## 4. Discovery first

Before editing source, inspect the actual active candidate job-alert create UI path and answer:

- exact PHP template/render function that outputs the frequency control;
- exact getter/filter used to obtain choices;
- whether create and edit/view flows share the same getter;
- whether employer→candidate alerts reuse the same getter/filter;
- exact form/action/container identifiers that can safely scope a UI seam;
- whether there is already an owned render/filter hook suitable for create-only filtering.

Do not assume markup names from memory. Use current `origin/staging` source/runtime evidence.

If no reliable create-only seam exists, STOP with a precise recommendation rather than implementing a global filter/hack.

---

## 5. Approved new-alert UI contract

For candidate→job **new alert creation only**, UI must expose exactly four selectable values:

- `daily`
- `weekly`
- `fortnightly`
- `monthly`

Do not show:

- `biannually`
- `annually`

for a new alert.

Backend validation from 1.89 remains authoritative and must stay unchanged unless a small reuse/refactor is needed to avoid duplicate allowlists.

Prefer one Raspitajse-owned canonical allowlist shared by backend validation and the create-only UI seam if this can be done without broadening scope.

---

## 6. Legacy compatibility

Existing alert records containing `biannually` or `annually` must remain readable and processable by the 1.88/1.89 legacy compatibility path.

Do not:

- rewrite those values;
- hide their labels on existing-record display/edit views unless a safe fallback preserves their current value;
- delete historical metadata;
- invalidate already-created legacy alerts merely because new creation no longer offers those choices.

If the same form supports editing an existing legacy alert, it must not silently coerce its stored frequency. Either preserve/show the current legacy value in edit mode or keep this task strictly scoped to create-only UI.

---

## 7. No JavaScript authorization

Do not rely on JavaScript as the authorization/business-policy layer.

JavaScript may be used only as presentation enhancement if necessary. Backend 1.89 validation remains the enforcement boundary.

Prefer server-side scoped rendering/filtering over DOM removal after page load.

---

## 8. Update safety

The solution must survive WPJBP/plugin updates as far as practical.

Prefer:

1. owned WordPress/WPJBP filter/action seam;
2. owned wrapper/partial invoked from an existing safe extension point;
3. narrowly scoped owned JS only if server-side create-only filtering is unavailable;
4. vendor template modification only as last resort and only with explicit justification.

Do not globally replace `WP_Job_Board_Pro_Job_Alert::get_email_frequency()` semantics.

---

## 9. Post-cutover observation

Read-only observe after 1.89 and before/after this task:

- `origin/staging` / deploy marker;
- exact owned hourly event count and schedule;
- continuation event count;
- WPJBP daily event count;
- exact daily-hook callback graph/count;
- candidate-job bridge registration state;
- vendor candidate-job sender registration state;
- global published `job_alert` baseline;
- published legacy `candidate_alert` count/fingerprint;
- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected stable state:

- owned recurring event exactly 1;
- continuation events normally 0;
- WPJBP daily event exactly 1;
- daily callback count exactly 5;
- candidate-job bridge absent from daily hook;
- direct vendor candidate-job sender absent;
- ID32733 remains `pending/0`.

Do not execute those cron hooks merely to observe them.

---

## 10. Evaluator observation seam

1.89 emits:

`raspitajse_candidate_job_alert_evaluator_observation`

Inspect whether this is sufficient for PII-free operational observation.

Do not add persistent logging unless a concrete gap is proven.

Allowed observation fields remain only things like:

- alert ID;
- sanitized outcome/state code;
- counts;
- continuation flag.

Do not persist/log:

- recipient email;
- rendered mail body;
- candidate PII;
- raw saved query;
- SMTP credentials.

If no logging change is needed, explicitly report `KEEP AS-IS`.

---

## 11. Fixture safety

Use collision-resistant marker:

`codex-z1-90-<UTC>-<random>`

Only create disposable objects if needed to render/submit the exact create form or validate backend/UI consistency.

Before any fixture:

- staging environment check;
- mail safety/interception loaded;
- external HTTP guard if harness uses one;
- capture exact IDs immediately.

Do not execute the owned hourly event through broad cron.

Direct invocation of a narrowly scoped render/create handler is allowed if needed.

---

## 12. UI acceptance tests

Prove the actual candidate-job **new alert** UI contains exactly the four approved values.

Minimum assertions:

- `daily` present;
- `weekly` present;
- `fortnightly` present;
- `monthly` present;
- `biannually` absent from new-alert choices;
- `annually` absent from new-alert choices;
- no duplicate frequency options;
- labels remain non-empty;
- selected value round-trips through normal new-alert submission for each approved frequency or through a safe isolated handler-level equivalent.

Do not use OCR/screenshot text as primary proof if PHP/rendered HTML can be inspected directly.

---

## 13. Backend consistency regression

Reconfirm new-alert backend behavior:

- daily → accepted;
- weekly → accepted;
- fortnightly → accepted;
- monthly → accepted;
- biannually → rejected;
- annually → rejected;
- unknown value → rejected.

No real mail.

If frontend and backend derive from one owned canonical allowlist after this task, prove that relationship.

---

## 14. Legacy display compatibility fixture

Create or use disposable legacy fixture records only if necessary.

Prove existing `biannually` and `annually` values are not silently rewritten by the new UI seam.

If edit/view UI is touched by the implementation, prove their current stored legacy label/value remains representable.

If implementation truly affects create-only markup and cannot touch legacy display, record that exact isolation instead of adding unnecessary fixture complexity.

---

## 15. Regression: 1.89 active graph

After deploy assert:

- `raspitajse_candidate_job_alert_evaluator` recurring event exactly once;
- cadence remains `hourly`;
- daily WPJBP event remains exactly once;
- daily callback count remains exactly 5;
- all five identities/priorities unchanged;
- owned candidate-job bridge remains unregistered there;
- direct vendor candidate-job sender remains unregistered;
- no new Action Scheduler actions introduced.

---

## 16. Regression: delivery foundation/integration

Because this task should not redesign delivery, focused checks are enough:

- approved four frequency normalization still passes;
- legacy two frequencies still pass only with legacy compatibility flag/path;
- `candidate_alerts` sender channel still canonical;
- query adapter/mailer/evaluator classes load;
- no code path resumes writing `_job_alert_send_email_time`.

Do not rerun the full destructive 1.88/1.89 matrix unless changed code materially affects those components.

---

## 17. Counters

Report separately:

- controlled `wp_mail` attempts;
- PHPMailer attempts;
- SMTP attempts;
- WP HTTP API attempts;
- external network requests;
- payment calls;
- broad cron invocations;
- Action Scheduler runner/due actions.

Expected for this task:

- preferably `wp_mail = 0`;
- PHPMailer/SMTP/network/payment = 0;
- broad cron = 0;
- AS runner/due actions = 0.

If a mail path is unavoidable for a narrowly scoped regression, intercept before PHPMailer and justify it.

---

## 18. Cleanup

Exact IDs/marker only.

Remove all disposable:

- users/profiles;
- job_alerts;
- jobs/terms created for the fixture;
- marker meta;
- temporary options/claims;
- fixture-only continuation events if any.

Do not remove the legitimate owned recurring hourly event.

Final global `job_alert` must return to baseline.

No broad cleanup.

---

## 19. Safety

Forbidden:

- production access or mutation;
- `wp cron event run` broad execution;
- `do_action('wp_job_board_pro_email_daily_notices')`;
- broad WP-Cron runners;
- Action Scheduler runner/due actions;
- ID32733 execution;
- real SMTP;
- payment calls;
- employer→candidate migration;
- expiry subsystem refactor;
- global frequency getter behavior change that alters unrelated/legacy surfaces.

Production touched: **NO**.

Known host constraint remains `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC`. Do not retry known failing sandbox helpers indefinitely; use proven namespace-free Git/filesystem workflow.

---

## 20. Integration

If implementation is required and all acceptance checks pass:

- PHP lint all changed PHP;
- `git diff --check`;
- focused full diff review;
- scoped feature commit;
- staging deploy only;
- source/runtime parity;
- FF integration only;
- no force;
- clean final working tree.

If no code change is necessary because a safe existing scoped seam already produces the correct UI, document that with evidence and do not invent a commit.

If the only implementation path requires a global/vendor hack with unsafe legacy effects, STOP instead of integrating.

---

## 21. Final report

Publish:

**Zadatak 1.90 — Candidate-job alert scoped frequency UI and post-cutover observation**

Report must include:

1. baseline/final SHA;
2. exact changed files or explicit no-change result;
3. exact create-form render path discovered;
4. exact frequency getter/filter graph;
5. whether employer→candidate/legacy surfaces share it;
6. chosen create-only seam and why it is update-safe;
7. final new-alert UI options;
8. backend allowlist consistency;
9. legacy biannual/annual compatibility proof;
10. proof no global frequency semantics were changed;
11. owned hourly event state;
12. continuation event state;
13. WPJBP daily event state;
14. exact final five-callback graph;
15. bridge/vendor sender absence;
16. evaluator observation-hook assessment (`KEEP`, `REDESIGN`, or no change);
17. `send_email_time` remains read-only compatibility evidence;
18. focused 1.89 regression results;
19. mail/SMTP/network/payment/cron/AS counters;
20. legacy candidate-alert fingerprint;
21. job_alert cleanup baseline;
22. AS fingerprint;
23. ID32733 `pending/0`;
24. source/runtime hashes;
25. production touched YES/NO;
26. exactly one proposed next small task.

Then STOP.

Do not begin 1.91.
