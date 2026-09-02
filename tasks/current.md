# Zadatak 1.93 — Audit candidate-job vendor compatibility residue

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.92
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting application source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Do not use application files inherited on `codex-tasks` as source truth. All application/source/history reads MUST use the declared `origin/staging` baseline and repository history.

Verify exact baseline first. If `origin/staging` is not exactly `642c8c8efb51a56449fd7048c71d3216590d52bf`, STOP and report the mismatch.

Execute exactly this task. This task is **READ-ONLY for application source and staging business/runtime state**. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not infer, implement, or begin 1.94.

---

## 1. Context

Zadatak 1.92 is PASS.

Candidate→job alerts are now fully owned by Raspitajse without the legacy bridge:

`raspitajse_candidate_job_alert_evaluator`
→ owned evaluator
→ owned delivery service/state/claim
→ owned WPJBP-compatible query adapter
→ owned mailer
→ `candidate_alerts` SenderPolicy
→ owned Transport.

Final 1.92 runtime invariants:

- `origin/staging` / deploy SHA: `642c8c8efb51a56449fd7048c71d3216590d52bf`;
- bridge class/source/runtime references: 0;
- direct vendor candidate-job sender registration: 0;
- WPJBP daily hook contains exactly 5 unrelated callbacks at priority 10;
- owned hourly evaluator event exactly 1;
- continuation event normally 0;
- global published `job_alert` baseline 0;
- published legacy `candidate_alert` count 4;
- pending Action Scheduler count 7;
- ID32733 `pending/0`;
- production untouched.

One known compatibility residue remains in WPJBP vendor source from the earlier sender migration. Zadatak 1.86 removed project-added/hard-coded candidate-job `From` / staging `Reply-To` behavior from the vendor candidate-job sender while preserving HTML content type. Since the vendor candidate-job sender is now permanently suppressed from the active runtime graph, we need to determine whether any Raspitajse-specific candidate-job delta still exists in WPJBP vendor code and whether it should remain, be restored to vendor baseline, or be left untouched as defense-in-depth.

Do not assume the 1.86 sender-header edit is the only residue. Prove the complete state from repository history.

---

## 2. Goal

Perform a **read-only vendor compatibility residue audit** focused only on the WPJBP candidate→job alert subsystem.

Answer precisely:

1. What exact Raspitajse-specific modifications currently remain in WPJBP candidate-job alert vendor files compared with the clean/vendor baseline available in repository history?
2. Which of those modifications are still reachable by any active runtime path?
3. Which are required for business behavior, security, compatibility or defense-in-depth?
4. Which are obsolete migration residue that can safely be removed/reverted in a later small task?
5. Can the target architecture return the relevant WPJBP candidate-job vendor file(s) to an unmodified/vendor-owned state without changing the active owned candidate-job system?
6. What exact smallest implementation slice should 1.94 execute, if any?

No source edit in 1.93.

---

## 3. Scope

Primary target:

- WPJBP candidate→job alert implementation, especially the file/class that defines `WP_Job_Board_Pro_Job_Alert` and `send_job_alert_notice()`.

Inspect related files only as needed to establish history/reachability:

- WPJBP plugin bootstrap/registration;
- Raspitajse communications evaluator/delivery/query/mailer;
- child-theme/custom code if it historically modified candidate-job alert behavior;
- relevant Git history/commits that introduced or removed vendor deltas.

Do NOT broaden into:

- employer→candidate alert redesign;
- candidate/job expiry notices;
- general WPJBP refactor;
- WooCommerce;
- Superio redesign;
- SenderPolicy redesign;
- matching algorithm redesign;
- cron architecture redesign;
- production.

---

## 4. Determine the authoritative comparison baseline

Use repository history first. Do not download external plugin packages merely to prove vendor behavior.

Identify the best available pre-customization/vendor baseline for every currently modified candidate-job WPJBP file.

Preferred evidence order:

1. repository commit immediately before the first Raspitajse/custom modification to that exact file;
2. an earlier repository snapshot known to contain the original plugin payload;
3. only if repository history is insufficient, document the gap and STOP rather than making an unverifiable external comparison.

For each compared file report:

- current path;
- current staging blob/hash;
- chosen baseline commit/blob/hash;
- why that baseline is authoritative enough;
- first commit that introduced each Raspitajse-specific delta;
- later commits that changed/reverted it.

Do not call vendor/external services.

---

## 5. Complete candidate-job vendor delta inventory

Produce the exact current diff between the chosen clean/vendor baseline and current `origin/staging` for candidate-job alert vendor files.

For every hunk classify:

- original vendor behavior;
- current behavior;
- introducing task/commit if identifiable;
- business purpose;
- current active reachability;
- dependency/side effect;
- `KEEP`, `REDESIGN`, or `DROP`;
- recommended future disposition.

Do not summarize several unrelated hunks into one classification.

If a changed line is only formatting/comment churn, classify it explicitly.

---

## 6. 1.86 sender-header residue audit

Audit the exact sender-related hunk changed during 1.86.

Prove from history:

- what hard-coded/project-added `From` behavior existed before 1.86;
- what hard-coded/project-added `Reply-To` behavior existed before 1.86;
- what 1.86 removed;
- what remains now;
- whether `Content-Type: text/html` remains vendor-original, Raspitajse-added, or independently required;
- whether any other sender/header mutation remains in the vendor method.

Then classify the **current** vendor state, not the historical bad state.

Important distinction:

- reverting 1.86 mechanically may reintroduce bad hard-coded headers;
- restoring the file to a clean pre-Raspitajse/vendor baseline may be different from reverting the 1.86 commit.

Make this distinction explicit.

---

## 7. Active runtime reachability

Prove whether current runtime can reach the vendor candidate-job sender after 1.92.

Audit:

- vendor registration source;
- evaluator exact suppression at `plugins_loaded` priority 101;
- final daily hook graph;
- any direct calls to `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice()` anywhere in repository application code;
- any reflection/call_user_func/dynamic callback construction that could target it;
- manual/custom cron runners;
- WP-CLI/custom tools in repository;
- tests/fixtures that intentionally call vendor sender.

Do not execute the sender or daily hook.

Report reachability as:

- active business runtime;
- administrative/manual tooling;
- test-only;
- dead/unreachable under current architecture.

---

## 8. Other vendor methods in `WP_Job_Board_Pro_Job_Alert`

Do not assume the whole class is obsolete because `send_job_alert_notice()` is suppressed.

Inventory which methods of the vendor Job Alert class are still used for infrastructure/UI/data compatibility, such as:

- frequency getter;
- widget/form helpers;
- alert query/template helpers;
- add/remove methods replaced by owned security handlers;
- any class initialization registrations.

For each relevant method state:

- still used by owned/current UI or compatibility path;
- vendor mutation callback suppressed/replaced;
- dead candidate-job delivery-only code;
- must remain because other vendor methods/classes depend on it.

Goal is to avoid incorrectly treating the whole file/class as removable.

---

## 9. Owned-system dependency check

Create a dependency map proving what the active owned candidate-job system still deliberately reuses from WPJBP.

At minimum inspect:

- `WP_Job_Board_Pro_Query::get_posts()` and filter machinery used by owned query adapter;
- email template/options and `WP_Job_Board_Pro_Email::render_email_vars()` reused by owned mailer;
- `WP_Job_Board_Pro_Job_Alert::get_email_frequency()` or other getter use by renderer/UI compatibility;
- widget/template infrastructure used by 1.90 scoped frequency UI;
- canonical job data/helpers.

Distinguish:

**vendor infrastructure we intentionally KEEP**

from

**vendor candidate-job sender implementation we no longer use**.

---

## 10. Candidate-job mutation/security compatibility

Reconfirm read-only that 1.84 still owns candidate-job alert mutation endpoints and vendor mutation callbacks remain absent.

Do not rerun destructive fixtures.

Report:

- exact candidate-job add/remove route registrations;
- owned vs vendor callback counts;
- whether any vendor-file residue identified in this task is still required by the owned security handlers;
- REST exposure state.

Do not modify security code.

---

## 11. Frequency/UI compatibility dependency

1.90 intentionally keeps the global vendor frequency getter compatible with six legacy labels while exposing only four choices in the new candidate-job create UI.

Audit whether restoring/removing any vendor candidate-job file delta could affect:

- `daily`;
- `weekly`;
- `fortnightly`;
- `monthly`;
- legacy `biannually`;
- legacy `annually`;
- candidate-job create-only scoped frequency UI;
- employer→candidate/legacy surfaces that share the getter.

Do not propose a vendor-file cleanup that silently breaks this compatibility.

---

## 12. Template/render compatibility dependency

Owned mailer reuses WPJBP email/template infrastructure but not the vendor outer sender loop.

Identify exactly which vendor template/helper behavior the owned mailer still depends on.

Determine whether any current vendor delta is located in shared template/render helpers that owned mailer still calls.

If yes, classify cautiously and explain why it cannot be removed merely because vendor sender is suppressed.

Do not render/send mail in this task.

---

## 13. `send_email_time` compatibility

Confirm `_job_alert_send_email_time` status remains:

- legacy read-only input for owned lazy initialization;
- not canonical delivery state;
- not written by owned delivery;
- possibly still written only inside the suppressed vendor sender implementation.

If vendor code contains the old write path, classify it as reachable/unreachable and explain whether restoring vendor baseline changes anything in active owned behavior.

Do not mutate historical meta.

---

## 14. Dead-code versus vendor-owned-code principle

Apply this rule explicitly:

We do **not** delete vendor methods merely because Raspitajse no longer calls them if removing them would create a permanent fork or complicate updates.

Preferred target architecture is:

- vendor files as close to vendor-owned/original as practical;
- Raspitajse business policy in Raspitajse-owned layers;
- exact runtime suppression/adaptation from owned code where needed.

Therefore a currently unreachable vendor method may still be `KEEP AS VENDOR` even if its business path is disabled.

Classify **Raspitajse-specific vendor modifications**, not vendor code existence itself.

---

## 15. Future cleanup options

Compare at least these options if residue exists:

### Option A — leave current vendor delta untouched

Evaluate defense-in-depth value vs permanent vendor fork cost.

### Option B — restore only obsolete Raspitajse-specific hunk(s) to clean vendor baseline

Evaluate exact source/runtime effect and update safety.

### Option C — restore the complete candidate-job vendor file to its clean repository baseline

Only if every current Raspitajse-specific delta in that file is proven obsolete and no owned/current path depends on it.

### Option D — keep one minimal compatibility delta

Only if a concrete active dependency proves it necessary.

Recommend one option with evidence.

Do not implement it in 1.93.

---

## 16. Read-only staging observation

Capture without executing scheduled work:

- `origin/staging` SHA / deploy marker;
- WPJBP daily event count/schedule/fingerprint;
- exact final five-callback daily-hook graph;
- direct vendor candidate-job sender registration count;
- owned hourly evaluator event count/schedule/fingerprint;
- continuation event count;
- global published `job_alert` count/fingerprint;
- published legacy `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Expected stable state from 1.92:

- daily callbacks = 5;
- vendor candidate-job sender = 0;
- owned hourly evaluator = 1;
- continuation normally = 0;
- `job_alert` = 0;
- `candidate_alert` = 4;
- ID32733 = `pending/0`.

Inspection only.

---

## 17. No mutation requirement

This task must cause:

- application source writes: 0;
- application commits: 0;
- feature branches for application changes: 0;
- deploys: 0;
- WordPress post/meta/option/user mutations: 0;
- fixtures: 0;
- cron schedule mutations: 0;
- mail attempts: 0;
- HTTP application/vendor requests: 0;
- payment calls: 0;
- AS mutations: 0.

The normal `codex-reports` publication is the only intended write.

---

## 18. Scheduler safety

Forbidden:

- `wp cron event run`;
- broad WP-Cron runner;
- `do_action('wp_job_board_pro_email_daily_notices')`;
- direct vendor candidate-job sender invocation;
- owned evaluator execution;
- continuation execution;
- Action Scheduler runner/due actions;
- ID32733 execution.

Read-only inspection only.

---

## 19. Production safety

Production filesystem/database/network access:

**FORBIDDEN**.

Do not inspect production to determine vendor residue. Git history + staging source/runtime are authoritative for this task.

---

## 20. If repository history is ambiguous

If you cannot establish a trustworthy clean/vendor baseline for a changed file:

- do not fetch arbitrary internet/plugin copies;
- do not guess what vendor-original was;
- classify the comparison as unresolved;
- explain exactly which commit/history gap prevents proof;
- recommend no cleanup for that hunk until provenance is established.

Do not turn uncertainty into a code change proposal.

---

## 21. Proposed 1.94 implementation

Only if the audit proves one or more Raspitajse-specific vendor hunks are safely obsolete, define one small exact 1.94 implementation slice.

Preferred shape if evidence supports it:

- restore only obsolete Raspitajse-specific candidate-job vendor delta to the proven clean/vendor repository baseline;
- make no behavioral change to owned evaluator/delivery/query/mailer/frequency/security;
- no DB migration;
- no cron execution;
- no employer→candidate changes;
- no production.

If the correct conclusion is **KEEP current vendor state**, then propose a different single small task instead of manufacturing cleanup work.

Do not implement 1.94.

---

## 22. 1.94 acceptance matrix design

If cleanup is recommended, design but do not execute the smallest future matrix covering:

1. exact baseline SHA;
2. exact file/hunk diff only;
3. PHP lint + `git diff --check`;
4. source/runtime parity after staging deploy;
5. vendor candidate-job sender final registration remains 0;
6. final daily callback graph remains exactly 5;
7. owned hourly evaluator remains exactly 1;
8. active owned classes load;
9. `candidate_alerts` SenderPolicy unchanged;
10. query adapter/mailer dependencies still resolve;
11. candidate-job create UI still shows exactly four new frequencies;
12. legacy frequency compatibility remains intact;
13. 12 owned security mutation routes remain owned and vendor callbacks absent;
14. `_job_alert_send_email_time` remains owned-read-only;
15. `job_alert` baseline unchanged;
16. legacy `candidate_alert` fingerprint unchanged;
17. AS fingerprint unchanged;
18. ID32733 `pending/0`;
19. mail/SMTP/network/payment/cron/AS execution counters 0;
20. production NO.

Prefer static/runtime-hook regression over destructive fixtures unless the exact changed hunk requires more.

---

## 23. STOP conditions

STOP without cleanup recommendation if:

- clean/vendor provenance is not trustworthy;
- a current vendor delta is still used by owned UI/query/mailer/security compatibility;
- restoring the vendor baseline would reintroduce a known unsafe sender/header behavior that was not originally vendor behavior;
- cleanup would require broad WPJBP redesign;
- cleanup would affect employer→candidate or expiry flows;
- the exact effect cannot be proven without external vendor requests or production access.

---

## 24. Final report

Publish:

**Zadatak 1.93 — Audit candidate-job vendor compatibility residue**

Report must include:

1. baseline/final application SHA and proof unchanged;
2. authoritative comparison baseline(s) and provenance;
3. exact current candidate-job WPJBP vendor delta inventory;
4. commit/task history for each delta;
5. KEEP / REDESIGN / DROP classification per hunk;
6. exact 1.86 sender-header history/current residue analysis;
7. active runtime reachability of vendor sender;
8. all direct/dynamic/manual vendor sender call sites;
9. vendor Job Alert class method-use inventory;
10. active owned dependency map to WPJBP infrastructure;
11. candidate-job security/mutation dependency status;
12. frequency/UI compatibility dependency status;
13. template/render dependency status;
14. `_job_alert_send_email_time` vendor/owned reachability status;
15. vendor-owned-code vs Raspitajse-delta classification conclusion;
16. comparison of cleanup options A/B/C/D as applicable;
17. one recommended target state;
18. current staging daily-hook graph;
19. owned hourly/continuation cron state;
20. global `job_alert` baseline/fingerprint;
21. legacy `candidate_alert` count/fingerprint;
22. AS count/fingerprint;
23. ID32733 `pending/0`;
24. source/DB/mail/network/payment/cron/AS mutation/execution counters, expected zero;
25. production accessed/touched YES/NO;
26. exactly one proposed next small task;
27. exact future acceptance matrix if a cleanup task is proposed.

Then STOP.

Do not edit vendor or owned application source in 1.93.
Do not begin 1.94.
