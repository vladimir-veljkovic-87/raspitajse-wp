# Zadatak 1.92 — Retire candidate-job legacy bridge scaffolding

Status: READY
Baseline: 57a6b8696a96e7157272f4e69e1e55616cf078d2
Previous task: 1.91
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Do not use application files inherited on `codex-tasks` as source truth. All application reads/diffs/work MUST use the declared `origin/staging` baseline.

Verify exact baseline first. If `origin/staging` is not exactly `57a6b8696a96e7157272f4e69e1e55616cf078d2`, STOP and report the mismatch.

Execute exactly this task. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not infer or begin 1.93.

---

## 1. Context

Zadatak 1.91 is PASS and proved the legacy candidate-job sender bridge is now migration scaffolding only.

Approved retirement conclusions from 1.91:

- `Raspitajse_Communications_Candidate_Job_Alert_Bridge::boot()` — DROP;
- `replace_vendor_callback()` — DROP;
- `send_job_alert_notice()` — DROP;
- main `Bridge::boot()` invocation — DROP;
- bridge `HOOK` and `PRIORITY` ownership — REDESIGN into evaluator-local compatibility constants;
- evaluator dependency on bridge constants — REDESIGN to self-contained evaluator ownership;
- temporary priority-100 vendor→bridge swap — DROP;
- invariant that the direct WPJBP candidate→job daily sender is absent — KEEP, owned by evaluator cutover.

Active candidate→job delivery already runs through:

`raspitajse_candidate_job_alert_evaluator`
→ owned evaluator
→ delivery service/state/claim
→ owned query adapter
→ owned mailer
→ `candidate_alerts` SenderPolicy
→ owned Transport.

The bridge is absent from the active delivery path.

Current runtime baseline:

- owned hourly evaluator event exactly 1;
- continuation event normally 0;
- WPJBP daily event exactly 1;
- WPJBP daily callback graph exactly 5 unrelated callbacks;
- direct vendor candidate-job sender final registration 0;
- bridge candidate-job sender final registration 0;
- global `job_alert` count 0;
- legacy published `candidate_alert` count 4;
- ID32733 `pending/0`;
- production untouched.

---

## 2. Goal

Retire the obsolete candidate-job bridge scaffolding while preserving the final runtime invariant:

**The direct vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` callback must not remain registered on `wp_job_board_pro_email_daily_notices`.**

The evaluator must become fully self-contained for this compatibility suppression.

No business behavior redesign is allowed.

---

## 3. Expected exact source changes

Modify only these two owned files unless a hard blocker is proven:

1. `wp-content/plugins/raspitajse-communications/raspitajse-communications.php`
2. `wp-content/plugins/raspitajse-communications/includes/class-candidate-job-alert-integration.php`

Expected changes:

### In `raspitajse-communications.php`

- remove the entire `Raspitajse_Communications_Candidate_Job_Alert_Bridge` class;
- remove `Raspitajse_Communications_Candidate_Job_Alert_Bridge::boot();`;
- do not modify Transport/SenderPolicy/security/frequency UI semantics.

### In `class-candidate-job-alert-integration.php`

- give `Raspitajse_Communications_Candidate_Job_Alert_Evaluator` ownership of the legacy daily-hook identity and priority;
- preferred private constants:
  - `LEGACY_DAILY_HOOK = 'wp_job_board_pro_email_daily_notices'`
  - `LEGACY_DAILY_PRIORITY = 10`
- keep `cutover_daily_hook()` on `plugins_loaded` priority 101;
- remove only the exact vendor callback:
  - `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice`
  - on the exact legacy daily hook
  - at priority 10;
- remove the now-impossible second removal of `Bridge::send_job_alert_notice`;
- do not add any replacement callback to the WPJBP daily hook.

If exact naming differs for code-style reasons, preserve the same ownership/semantics.

---

## 4. Scope boundaries

Do NOT change:

- delivery service/state machine;
- query adapter;
- matching/filter semantics;
- renderer/mailer/templates;
- SenderPolicy;
- Transport behavior;
- alert security handlers;
- candidate-job frequency UI;
- employer→candidate alerts;
- candidate/job expiry notifications;
- WooCommerce;
- Action Scheduler;
- WPJBP vendor source;
- Superio/theme/MU plugins;
- cron schedules/cadence;
- legacy `_job_alert_send_email_time` behavior.

This is compatibility-code retirement only.

---

## 5. Vendor suppression invariant

After bootstrap, runtime must satisfy:

- direct vendor candidate-job sender registrations on `wp_job_board_pro_email_daily_notices`: **0**;
- bridge sender registrations: **class nonexistent / 0**;
- remaining daily callback count: **5**;
- all five remaining callbacks retain exact identity and priority 10.

Do not use `remove_all_actions()`.

Only exact callback removal is allowed.

---

## 6. Plugins-loaded timing

Retain evaluator cutover registration at:

`plugins_loaded` priority **101**.

Reason established by 1.91:

- WPJBP initializes/registers the vendor job-alert daily sender earlier at priority 10;
- priority 101 is already proven to run after vendor registration;
- no temporary bridge at priority 100 is needed.

Do not move cutover earlier unless exact evidence proves no load-order regression; the default requirement is to keep 101 unchanged.

---

## 7. Static reference cleanup

After edit, search exact staging source and prove:

- zero references to `Raspitajse_Communications_Candidate_Job_Alert_Bridge`;
- zero bridge bootstrap calls;
- zero evaluator references to bridge constants;
- zero bridge callback-removal references;
- evaluator owns exactly one canonical legacy daily-hook definition and one canonical priority definition;
- active delivery still has its own `with_sender_channel(candidate_alerts, ...)` call.

Do not remove `Transport::with_sender_channel()`; 1.91 proved active delivery still uses it.

---

## 8. Vendor compatibility edit remains untouched

Do not revert or modify the earlier WPJBP vendor sender-header cleanup from 1.86.

It remains harmless defense-in-depth compatibility residue and is explicitly outside 1.92.

No vendor file should appear in the diff.

---

## 9. Preflight runtime snapshot

Before source mutation capture read-only:

- `origin/staging` SHA;
- deploy marker;
- environment exactly `staging`;
- mail-safety loaded;
- WPJBP daily cron event count/fingerprint;
- exact five daily callbacks/priorities;
- owned hourly evaluator event count/fingerprint;
- owned continuation event count;
- global `job_alert` count/fingerprint;
- legacy published `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Do not execute any hook/runner to inspect this state.

---

## 10. Static acceptance

Required before deploy:

- exact diff contains only the two expected owned files;
- bridge class removed cleanly;
- evaluator self-contained;
- PHP lint both changed files PASS;
- `git diff --check` PASS;
- focused full diff review PASS;
- no unrelated formatting churn.

If any WPJBP/theme/MU/security/delivery/frequency file changes unexpectedly, STOP.

---

## 11. Staging deploy

If static checks PASS:

- scoped feature branch from exact baseline;
- commit only the two-file change;
- deploy to staging using the repository's existing changed-file deployment workflow;
- source/runtime hash parity required;
- no production deploy;
- no force update.

If deployment validation fails, redeploy the exact previous staging SHA and STOP.

No DB rollback should be needed because this slice must not mutate business data.

---

## 12. Post-deploy hook regression

Read-only runtime bootstrap inspection after deploy must prove:

### WPJBP daily hook

Exactly 5 callbacks, all priority 10:

1. `WP_Job_Board_Pro_Candidate::send_admin_expiring_notice`
2. `WP_Job_Board_Pro_Candidate::send_candidate_expiring_notice`
3. `WP_Job_Board_Pro_Candidate_Alert::send_candidate_alert_notice`
4. `WP_Job_Board_Pro_Job_Listing::send_admin_expiring_notice`
5. `WP_Job_Board_Pro_Job_Listing::send_employer_expiring_notice`

Assert:

- direct vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` = 0;
- bridge callback = nonexistent/0;
- callback count = 5.

Do not execute the daily hook.

---

## 13. Owned scheduler regression

Assert after deploy:

- `raspitajse_candidate_job_alert_evaluator` recurring event exactly 1;
- cadence remains `hourly`;
- evaluator callback registered exactly once;
- continuation event count remains 0 unless pre-existing bounded work proves otherwise;
- existing WPJBP daily cron event remains exactly 1 and unchanged;
- no Action Scheduler action introduced.

Do not run cron.

---

## 14. Active candidate-job smoke

Because this slice removes only dormant compatibility scaffolding, prefer a **non-destructive/static + direct bounded integration smoke**, not a large 1.88/1.89 destructive matrix.

Minimum prove:

- evaluator class loads;
- delivery service loads;
- query adapter loads;
- mailer loads;
- frequency UI classes load;
- `candidate_alerts` SenderPolicy still resolves normally on staging;
- active delivery source still calls `Transport::with_sender_channel(CHANNEL_CANDIDATE_ALERTS, ...)`;
- no owned code reintroduces `_job_alert_send_email_time` writes.

If a direct evaluator/service invocation is not necessary to prove the bridge removal, do not create fixtures merely to generate mail.

Expected `wp_mail` attempts for this task: **0**.

---

## 15. Frequency compatibility regression

Focused non-destructive validation:

- current/new frequencies: `daily`, `weekly`, `fortnightly`, `monthly` accepted;
- legacy compatibility path: `biannually`, `annually` still accepted only with legacy compatibility enabled;
- new-create UI canonical four-frequency behavior source remains unchanged;
- no bridge retirement code touches frequency policy/UI.

---

## 16. Legacy delivery state compatibility

Confirm source/runtime:

- `_job_alert_send_email_time` remains read-only legacy input for lazy initialization;
- no owned update/delete write to that key is introduced;
- delivered-job ledger/state/claim semantics unchanged.

No legacy data migration in 1.92.

---

## 17. Protected state after deploy

Capture post-deploy and compare:

- global `job_alert` count/fingerprint unchanged;
- legacy published `candidate_alert` count/fingerprint unchanged;
- WPJBP daily cron event unchanged;
- owned hourly event remains exactly 1;
- continuation event state stable;
- pending AS count/fingerprint unchanged;
- ID32733 remains `pending/0`.

No fixtures should normally exist in this task.

---

## 18. Counters

Report separately:

- application source commits;
- deploy count;
- staging DB/post/meta/option mutations caused by task;
- `wp_mail` attempts;
- PHPMailer attempts;
- SMTP attempts;
- WP HTTP API attempts;
- actual external network requests;
- payment calls;
- broad WP-Cron invocations;
- exact WPJBP daily-hook invocations;
- owned evaluator/continuation hook invocations;
- Action Scheduler runner/due actions;
- ID32733 executions.

Expected operational side-effect counters:

- business DB/data mutations: 0;
- mail/PHPMailer/SMTP: 0/0/0;
- network/payment: 0/0;
- cron hook executions: 0;
- AS runner/due actions: 0;
- ID32733 executions: 0.

---

## 19. Safety

Forbidden:

- production access or mutation;
- broad WP-Cron execution;
- `wp cron event run`;
- `do_action('wp_job_board_pro_email_daily_notices')`;
- direct scheduled evaluator execution unless a narrowly justified smoke is truly required;
- Action Scheduler runner/due actions;
- ID32733 execution;
- real SMTP;
- external/vendor requests just to prove behavior;
- payment calls;
- employer→candidate migration;
- expiry sender changes;
- vendor/core edits.

Production touched: **NO**.

Known host constraint remains `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC`. Do not retry known failing sandbox helpers indefinitely. Use proven namespace-free Git/filesystem workflow. If a process appears hung, identify the exact PID before terminating anything; never use broad kills.

---

## 20. Integration

Only if all checks PASS:

- source/runtime parity PASS;
- runtime hook graph PASS;
- protected state PASS;
- feature commit integrated to `staging` by fast-forward only;
- no force;
- final staging worktree clean;
- final `origin/staging` SHA equals deploy marker/runtime payload SHA.

If any invariant fails, STOP and do not integrate a partial retirement.

---

## 21. Final report

Publish:

**Zadatak 1.92 — Retire candidate-job legacy bridge scaffolding**

Report must include:

1. baseline/final SHA;
2. exact changed files;
3. exact removed bridge symbols;
4. evaluator-owned legacy hook/priority implementation;
5. proof zero bridge references remain;
6. proof no WPJBP/vendor/theme/MU changes;
7. final `plugins_loaded` cutover ownership/timing;
8. exact final daily-hook graph/count;
9. direct vendor candidate-job sender registration count;
10. bridge sender/class registration status;
11. owned hourly evaluator event state;
12. continuation event state;
13. WPJBP daily cron event state;
14. active delivery dependency smoke;
15. `candidate_alerts` SenderPolicy regression;
16. frequency compatibility regression;
17. `_job_alert_send_email_time` read-only proof;
18. global `job_alert` baseline/fingerprint;
19. legacy `candidate_alert` count/fingerprint;
20. AS count/fingerprint;
21. ID32733 `pending/0`;
22. mail/SMTP/network/payment/cron/AS counters;
23. source/runtime hashes;
24. cleanup/no-fixture proof;
25. production touched/accessed YES/NO;
26. exactly one proposed next small task.

Then STOP.

Do not begin 1.93.
