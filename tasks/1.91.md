# Zadatak 1.91 — Audit candidate-job legacy bridge retirement

Status: READY
Baseline: 57a6b8696a96e7157272f4e69e1e55616cf078d2
Previous task: 1.90
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or touching application source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Do not use application files inherited on `codex-tasks` as source truth. All application reads, searches, diffs and runtime inspection MUST use the declared `origin/staging` baseline.

Verify exact baseline first. If `origin/staging` is not exactly `57a6b8696a96e7157272f4e69e1e55616cf078d2`, STOP and report the mismatch.

Execute exactly this task. This task is **READ-ONLY for application source and staging business state**. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not infer, implement, or begin 1.92.

---

## 1. Context

Zadatak 1.90 is PASS.

Candidate→job alerts are now fully active through the Raspitajse-owned path:

`raspitajse_candidate_job_alert_evaluator`
→ owned evaluator
→ 1.88 delivery service/state/claim foundation
→ owned WPJBP-compatible query adapter
→ owned mailer
→ `candidate_alerts` SenderPolicy
→ owned Transport.

Current stable runtime contract after 1.90:

- owned hourly evaluator event: exactly 1;
- owned continuation event: normally 0;
- WPJBP daily event: exactly 1 because five unrelated legacy callbacks still use it;
- `wp_job_board_pro_email_daily_notices` callback count: exactly 5;
- owned candidate-job bridge callback on daily hook: 0;
- direct vendor candidate-job sender callback on daily hook: 0;
- `_job_alert_send_email_time`: read-only legacy compatibility evidence only;
- new candidate-job alert frequencies: daily / weekly / fortnightly / monthly;
- legacy biannually / annually remain compatibility-only;
- published global `job_alert` baseline: 0;
- published legacy `candidate_alert`: 4;
- Action Scheduler ID32733: `pending/0`.

One compatibility class from the earlier migration remains in source:

`Raspitajse_Communications_Candidate_Job_Alert_Bridge`

Current source evidence at the baseline shows:

- the bridge still defines `HOOK` and `PRIORITY` constants;
- `Bridge::boot()` registers `replace_vendor_callback` on `plugins_loaded` priority 100;
- `replace_vendor_callback()` can temporarily replace the vendor daily sender with the bridge sender;
- the owned evaluator registers `cutover_daily_hook()` on `plugins_loaded` priority 101;
- evaluator cutover then removes both the direct vendor sender and the bridge sender;
- evaluator currently references the bridge constants when removing those callbacks;
- main plugin bootstrap still calls `Bridge::boot()`.

The active delivery no longer uses `Bridge::send_job_alert_notice()`.

This task must determine whether the bridge class can now be retired safely and define the smallest exact follow-up implementation slice.

---

## 2. Goal

Perform a **read-only retirement audit** of the legacy candidate-job sender bridge.

Answer precisely:

1. Is any operation of `Raspitajse_Communications_Candidate_Job_Alert_Bridge` still required after the 1.89/1.90 cutover?
2. Which remaining behavior is true business/runtime necessity versus migration scaffolding?
3. Can the class and its `boot()` call be removed without allowing the vendor candidate-job daily sender to become active?
4. Where should the minimal vendor-callback suppression responsibility live after bridge retirement?
5. What exact implementation and regression matrix should 1.92 use?

Do **not** remove or modify the bridge in 1.91.

---

## 3. Classification requirement

Classify every remaining bridge responsibility as:

- `KEEP`
- `REDESIGN`
- `DROP`

At minimum classify separately:

- `Bridge::HOOK` constant;
- `Bridge::PRIORITY` constant;
- `Bridge::boot()`;
- `Bridge::replace_vendor_callback()`;
- `Bridge::send_job_alert_notice()`;
- main plugin `Bridge::boot()` invocation;
- evaluator dependency on bridge constants;
- temporary priority-100 bridge registration before evaluator priority-101 cutover.

Use business/runtime purpose, not historical existence, as the reason.

---

## 4. Full reference inventory

Search the exact `origin/staging` tree for all references to:

- `Raspitajse_Communications_Candidate_Job_Alert_Bridge`;
- `send_job_alert_notice` related to candidate→job alerts;
- `wp_job_board_pro_email_daily_notices`;
- the bridge `HOOK` / `PRIORITY` constants if referenced indirectly;
- vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` registration/removal.

For every reference report:

`File | symbol/call site | load/hook phase | current runtime purpose | required after cutover YES/NO | classification`

Do not limit the search to the communications plugin. Inspect WPJBP, theme/custom code and MU plugins as needed, read-only.

---

## 5. Exact registration timeline

Reconstruct the candidate-job daily sender registration/removal timeline from plugin load through `plugins_loaded` completion.

Report exact evidence for:

1. when/how WPJBP registers `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice`;
2. when the bridge registers `replace_vendor_callback`;
3. what exists on the daily hook immediately before priority 100;
4. what exists immediately after priority 100;
5. what evaluator `cutover_daily_hook()` does at priority 101;
6. final post-`plugins_loaded` callback graph.

Do not execute the daily hook.

Read-only hook inspection/bootstrap is allowed.

---

## 6. Intermediate priority-window risk

Explicitly determine whether the current priority-100 → priority-101 transition has any real runtime purpose or risk.

Audit whether any callback between/around those phases can synchronously execute:

`wp_job_board_pro_email_daily_notices`

before evaluator cutover completes.

Check at minimum:

- `plugins_loaded` callbacks near priorities 100 and 101;
- direct `do_action('wp_job_board_pro_email_daily_notices')` call sites;
- cron dispatch timing relative to plugin bootstrap;
- any custom/manual runner capable of invoking that hook during plugin loading.

Do not execute any runner or hook to prove this.

If no legitimate execution can occur before `plugins_loaded` completes, say so with evidence rather than assuming it.

---

## 7. Vendor suppression ownership

The active system still needs one invariant:

**The vendor candidate→job daily sender must never remain registered in the final runtime graph.**

Compare minimal ownership options:

### Option A — keep current bridge scaffolding

priority 100 vendor→bridge replacement, then priority 101 evaluator removes bridge.

### Option B — remove bridge and let evaluator directly remove only vendor callback

Evaluator owns its own daily-hook name/priority constants or exact local values.

### Option C — move suppression into another existing owned integration/security component

Only if there is a clear cohesion benefit.

### Option D — any other proven smaller mechanism

For each option evaluate:

- correctness;
- update safety;
- load-order assumptions;
- hidden dependency risk;
- complexity;
- rollback simplicity;
- whether it keeps unrelated five daily callbacks untouched.

Recommend the smallest correct option.

---

## 8. Constants ownership

Audit whether `HOOK = wp_job_board_pro_email_daily_notices` and `PRIORITY = 10` belong conceptually to the retired bridge or to active evaluator/cutover integration.

If bridge retirement is recommended, define exactly where those constants should move, or whether constants are unnecessary.

Do not duplicate magic values across multiple owned classes without reason.

---

## 9. SenderPolicy/Transport dependency check

Prove that active candidate→job delivery no longer requires:

`Raspitajse_Communications_Transport::with_sender_channel()`

through the bridge.

Distinguish this from whether `with_sender_channel()` is still needed by other legacy/current flows.

Do not remove or redesign Transport in this task.

Report:

- all current `with_sender_channel()` call sites;
- which are bridge-only;
- whether the method itself remains required elsewhere;
- whether bridge retirement has any effect on `candidate_alerts` SenderPolicy behavior.

---

## 10. Vendor source compatibility state

Review the earlier minimal vendor compatibility cleanup from 1.86 in:

`WP_Job_Board_Pro_Job_Alert::send_job_alert_notice()`

Determine whether the vendor file still contains any Raspitajse-specific edit made only to support the bridge era.

Classify each such remaining edit:

- still required by another runtime path;
- harmless but obsolete compatibility residue;
- safe later cleanup candidate;
- must remain untouched.

Do not edit vendor source in 1.91.

Do not broaden this into a full WPJBP sender refactor.

---

## 11. Active owned delivery isolation

Prove bridge retirement would not change any active 1.89/1.90 responsibility:

- owned hourly evaluator registration;
- due discovery;
- matching/query adapter;
- delivery state/claim;
- renderer/mailer;
- `candidate_alerts` SenderPolicy;
- Transport send path;
- four-frequency create UI;
- legacy frequency compatibility;
- legacy `_job_alert_send_email_time` lazy-read behavior.

Create a dependency map showing bridge edges versus active owned edges.

---

## 12. Five remaining daily callbacks

The following five are explicitly out of scope and must remain untouched:

- `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`;
- `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`;
- `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`;
- `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`;
- `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`.

Audit only enough to prove bridge retirement does not affect their hook registration.

Do not redesign or execute them.

---

## 13. Runtime read-only observation

Capture current staging state without running scheduled work:

- final `origin/staging` SHA and deploy marker;
- daily WPJBP event count/schedule;
- exact five-callback graph and priorities;
- owned evaluator event count/schedule;
- continuation event count;
- vendor candidate-job sender final registration count;
- bridge sender final registration count;
- bridge `replace_vendor_callback` registration on `plugins_loaded`;
- evaluator `cutover_daily_hook` registration on `plugins_loaded`;
- global `job_alert` count;
- legacy published `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Expected current final state remains:

- daily callback count 5;
- vendor candidate-job sender 0;
- bridge candidate-job sender 0;
- owned recurring event 1;
- continuation 0 normally;
- ID32733 `pending/0`.

---

## 14. No mutation requirement

Zadatak 1.91 is read-only for application/runtime state.

Do not:

- edit application source;
- create a feature branch for application changes;
- create an application commit;
- deploy application code;
- create fixture users/posts/alerts/jobs;
- change options/meta;
- schedule/unschedule cron;
- alter callbacks;
- invoke mail;
- invoke HTTP/payment paths.

The only expected write is the normal `codex-reports` report publication.

---

## 15. Scheduler safety

Forbidden:

- `wp cron event run`;
- broad WP-Cron execution;
- `do_action('wp_job_board_pro_email_daily_notices')`;
- direct owned evaluator execution;
- continuation hook execution;
- Action Scheduler runner;
- due actions;
- ID32733 execution.

Inspection only.

---

## 16. Production safety

Production touched/accessed:

**NO**.

Do not inspect production DB merely to look for legacy bridge behavior. Source/staging evidence is sufficient for this retirement decision.

---

## 17. Proposed 1.92 implementation

If the audit proves the bridge is obsolete, define one **small exact removal slice** for 1.92.

Preferred shape if supported by evidence:

- move/own the daily-hook callback identity needed by evaluator cutover;
- remove `Raspitajse_Communications_Candidate_Job_Alert_Bridge` class;
- remove `Bridge::boot()`;
- keep one owned final suppression of direct vendor candidate-job sender;
- keep final daily callback count exactly 5;
- do not touch evaluator delivery, matching, templates, SenderPolicy, employer→candidate or expiry flows.

But do not force that solution if audit evidence shows another dependency.

Give exact changed-file expectation and fixture/regression plan.

---

## 18. 1.92 acceptance matrix design

Design, but do not execute, the smallest convincing future matrix covering:

1. PHP lint/diff check;
2. bridge class absent from source if removal approved;
3. zero remaining class references;
4. final daily hook count 5;
5. vendor candidate-job sender 0;
6. bridge sender 0/nonexistent;
7. five unrelated daily callbacks unchanged;
8. owned hourly evaluator event exactly 1;
9. active candidate-job direct bounded service/evaluator smoke if actually necessary;
10. `candidate_alerts` SenderPolicy unchanged;
11. no `_job_alert_send_email_time` writes reintroduced;
12. legacy frequency compatibility unchanged;
13. candidate-job create UI four frequencies unchanged;
14. no AS change;
15. ID32733 `pending/0`;
16. SMTP/network/payment 0;
17. production NO.

Prefer static/runtime hook regression over a large destructive fixture because the expected change is compatibility-code retirement, not business behavior redesign.

---

## 19. STOP conditions

STOP and report instead of recommending removal if any of these are found:

- another active subsystem calls `Bridge::send_job_alert_notice()`;
- bridge replacement is required for a real runtime path before evaluator cutover;
- removing bridge would allow vendor candidate-job sender to survive final bootstrap;
- evaluator suppression cannot be made self-contained without changing unrelated daily callbacks;
- a hidden vendor/plugin load-order dependency cannot be proven safely;
- retirement would require redesigning delivery, matching, templates or SenderPolicy.

---

## 20. Final report

Publish:

**Zadatak 1.91 — Audit candidate-job legacy bridge retirement**

Report must include:

1. baseline/final SHA and proof application SHA unchanged;
2. complete bridge reference inventory;
3. KEEP / REDESIGN / DROP classification for each bridge responsibility;
4. exact vendor registration source/timing;
5. exact `plugins_loaded` priority timeline;
6. intermediate priority-window risk assessment;
7. all daily-hook execution call sites found;
8. vendor suppression ownership options comparison;
9. recommended minimal target ownership;
10. constants ownership recommendation;
11. `with_sender_channel()` dependency/call-site audit;
12. remaining vendor compatibility-edit assessment;
13. active owned delivery dependency map;
14. proof five unrelated daily callbacks remain independent;
15. current staging daily-hook graph;
16. current owned hourly/continuation cron state;
17. global `job_alert` baseline;
18. legacy candidate-alert count/fingerprint;
19. AS count/fingerprint;
20. ID32733 `pending/0`;
21. mail/SMTP/network/payment/cron/AS execution counters, expected all zero;
22. production touched/accessed YES/NO;
23. exact proposed Zadatak 1.92 implementation slice;
24. exact proposed 1.92 regression matrix.

Then STOP.

Do not edit or remove the bridge in 1.91.
Do not begin 1.92.
