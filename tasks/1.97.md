# Zadatak 1.97 — Validate exact WPJBP 1.2.73 baseline and close candidate-job vendor residue thread

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.96
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact `origin/staging` application baseline:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

If `origin/staging` differs, STOP and report the mismatch.

Execute only 1.97, publish the final report through the existing `codex-reports` workflow, and STOP. Do not implement cleanup and do not begin 1.98.

---

## 1. Context

Zadatak 1.96 is PASS and concluded that WPJBP 1.2.66 was useful only as a historical reference, not as an authoritative 1.2.73 baseline.

The owner has now explicitly supplied an **exact WP Job Board Pro 1.2.73 package** in `/home/u601262303/repo/vendor-artifacts/` and wants this candidate-job vendor-residue thread resolved conclusively.

The active Raspitajse candidate→job architecture is already owned and stable:

`raspitajse_candidate_job_alert_evaluator`
→ owned evaluator
→ delivery/state/claim
→ owned query adapter
→ owned mailer
→ `candidate_alerts` SenderPolicy
→ owned Transport.

The vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` remains suppressed from the final daily-hook graph.

This task is the final **authoritative provenance/decision audit** for the remaining candidate-job vendor deltas. It must determine whether any cleanup has enough value to justify a final small implementation task, or whether this thread should be permanently closed with the current vendor residue left untouched.

No application source modification in 1.97.

---

## 2. Exact supplied artifact discovery

Preferred exact path:

`/home/u601262303/repo/vendor-artifacts/wp-job-board-pro-1.2.73.zip`

If that exact file exists, inspect only that file.

If it does not exist, perform a **non-recursive filename-level inspection only** of `/home/u601262303/repo/vendor-artifacts/` and identify the newly supplied candidate as follows:

- ignore the already-known 1.2.66 file `/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip` whose SHA-256 is `fdf862722f2258c0cccc707a7f91f7055b3d468db7dfb144177bf001fa3e2190`;
- consider only regular `.zip` files in that exact directory;
- inspect candidate plugin metadata read-only;
- accept exactly one candidate only if both plugin header and `WP_JOB_BOARD_PRO_PLUGIN_VERSION` say `1.2.73`;
- if none or more than one candidate qualifies, STOP and report the ambiguity.

Do not rename, overwrite, move, install, activate, commit or modify either ZIP.

---

## 3. Goal

Using the owner-supplied exact 1.2.73 package as the authoritative version-matched reference, answer conclusively:

1. Is the supplied archive a coherent, independent WP Job Board Pro **1.2.73** package?
2. Does it contain obvious Raspitajse-specific customization signatures that disqualify it as a clean vendor reference?
3. What is the exact clean 1.2.73 → current staging diff for the three target candidate-job files?
4. Which current differences are definitely Raspitajse-specific?
5. Which differences are required by active Raspitajse business behavior/security/compatibility?
6. Which are obsolete/dormant residue?
7. Is there any meaningful and safe cleanup worth implementing now?
8. Should this thread end with:
   - one final narrowly-scoped implementation task, or
   - **no cleanup**, permanently parking this vendor-residue topic and moving to employer→candidate alerts?

The final recommendation must optimize for maintainability, update safety and business value — not vendor parity for its own sake.

---

## 4. Exact target files

Compare only these three application vendor files:

1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`

Against the corresponding package paths:

1. `includes/class-job-alert.php`
2. `includes/email-templates-default/html-job-alert-notice.php`
3. `templates/misc/my-jobs-alerts.php`

Related files may be read only when necessary to prove runtime reachability or active dependency. Do not broaden into a full WPJBP plugin audit.

---

## 5. Archive identity, integrity and safety

Before any comparison:

- record exact accepted artifact path;
- size and mtime as supporting metadata only;
- compute SHA-256;
- verify ZIP integrity/CRC;
- list entries;
- require one coherent plugin root;
- reject absolute paths, drive paths, `../` traversal, duplicate entries and symlink escapes;
- require one plugin main file and all three target files exactly once.

Extract only into a fresh task-private mode-0700 directory under `/tmp`.

Never execute PHP from the archive.

Do not extract over repository, staging runtime, WordPress plugin path or shared application paths.

Delete exact temp extraction/diff artifacts after evidence collection and prove cleanup.

---

## 6. Exact version and clean-reference gate

The archive may be treated as the authoritative version-matched reference only if:

- Plugin Name = `WP Job Board Pro`;
- plugin header Version = exactly `1.2.73`;
- `WP_JOB_BOARD_PRO_PLUGIN_VERSION` = exactly `1.2.73`;
- package is coherent, not a three-file subset;
- it is owner-supplied outside current repo/runtime;
- target files do not contain strong Raspitajse-specific signatures that prove it is merely another customized site copy.

Narrowly scan the extracted target files/package metadata for obvious disqualifiers such as:

- `raspitajse.com` / `stage.raspitajse.com`;
- Raspitajse-specific sender addresses;
- project-specific sample IDs/debug strings;
- Serbian literals that match known project customizations where generic vendor copy is expected;
- task/repository-specific comments.

Do not reject generic vendor strings or translations without evidence.

If the archive fails the version or cleanliness gate, report `PARTIAL`, explain exactly why and STOP. Do not fall back to 1.2.66.

---

## 7. Exact authoritative hashes and diffs

If accepted, compute SHA-256 for all three package files and all three exact `origin/staging` files.

Report:

`File | clean 1.2.73 hash | current staging hash | identical YES/NO`

Then produce exact read-only clean 1.2.73 → current staging diffs for only those files.

For every semantic hunk record:

- clean 1.2.73 behavior;
- current staging behavior;
- Git commit/task attribution where available;
- active runtime reachability;
- owned-system dependency;
- security/privacy implication;
- business/user-facing implication;
- classification of the **Raspitajse customization** as `KEEP`, `REDESIGN`, or `DROP`;
- recommended disposition.

Formatting/comment-only churn must be identified separately.

---

## 8. `class-job-alert.php` final provenance questions

Resolve authoritatively against exact 1.2.73:

- canonical frequency keys and labels;
- whether `minute` exists;
- `biannually` label and days;
- vendor `From` behavior;
- vendor `Reply-To` behavior;
- HTML `Content-Type` behavior;
- active `error_log` or debug behavior;
- `_job_alert_send_email_time` write behavior;
- vendor query/result-cap logic;
- duplicate/idempotency scaffolding;
- sender rendering variables/content args;
- vendor add/remove mutation behavior and localized strings.

Explicitly reconcile historical commits:

- `66f61889`;
- `60217120`;
- `043fcc59`;
- `e3e3c458`;
- any other proven relevant commit.

Do **not** recommend reintroducing hard-coded sender identities, PII logging or unsafe test/debug behavior merely to match vendor source. If exact vendor 1.2.73 contains an unsafe behavior, state that an owned suppression/adaptation must remain.

---

## 9. Default email-template final provenance

For `includes/email-templates-default/html-job-alert-notice.php`, resolve exact clean 1.2.73 structure/content versus current staging:

- branding;
- site URLs;
- logo/icon/asset URLs;
- CTAs;
- visible domain;
- mailto target;
- placeholders;
- template structure;
- malformed target/asset substitutions already observed historically.

Also verify current runtime behavior:

- whether configured email content is non-empty and therefore normally shadows/falls back over this vendor default;
- exactly what condition could make this vendor file user-visible again.

Decision rule:

- never introduce a visible email regression merely to make vendor source clean;
- if branding should move to owned/configured content first, classify `REDESIGN` and do not recommend immediate vendor restore unless replacement is already proven complete.

---

## 10. Management-template final provenance

For `templates/misc/my-jobs-alerts.php`:

- resolve exact clean 1.2.73 labels/structure;
- confirm current Serbian literals and their commit attribution;
- prove whether Superio/current theme override shadows this plugin template;
- identify the correct long-term placement: WordPress translations, theme override or owned presentation layer.

Do not recommend restoring English if that would cause any live fallback regression before a proper translation/override exists.

---

## 11. Active architecture protection

Read-only reconfirm current staging after plugin bootstrap:

- direct vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` final registration = 0;
- WPJBP daily hook = exactly 5 unrelated callbacks, all priority 10;
- owned hourly candidate-job evaluator event = exactly 1;
- continuation = 0 unless pre-existing bounded work proves otherwise;
- owned evaluator/delivery/query/mailer/SenderPolicy/Transport classes load;
- owned query adapter deliberately uses WPJBP query/filter infrastructure;
- owned mailer deliberately uses WPJBP email/render helpers;
- global frequency getter compatibility remains available while new candidate-job create policy is four values;
- 1.84 owned add/remove mutation/security callbacks remain authoritative and vendor mutation callbacks absent;
- `_job_alert_send_email_time` is read-only input in owned code;
- no active candidate-job delivery depends on vendor sender execution.

Do not execute daily/evaluator/continuation hooks.

---

## 12. Decision framework — finish this thread

Choose exactly one final conclusion:

### Conclusion A — final cleanup is materially worthwhile
Use only if exact 1.2.73 proof shows a small bounded change materially improves updateability/maintainability without UX/security regression.

If so, propose exactly one final implementation task, normally **1.98**, with exact files/hunks and replacement prerequisites. Do not implement it in 1.97.

### Conclusion B — keep current residue and close permanently
Use if remaining differences are dormant, shadowed, configured-fallback-only, security-hardening, or too low-value/risky to justify changing.

If so:

- explicitly mark the candidate→job vendor-residue thread **CLOSED / PARKED BY DECISION**;
- propose **no future WPJBP candidate-job cleanup task**;
- name the next business subsystem as the still-active employer→candidate `candidate_alert` matching/delivery lifecycle;
- do not manufacture cleanup work.

The owner has explicitly asked to finish this topic rather than keep extending provenance work indefinitely.

---

## 13. Protected staging observation

Capture read-only before/final:

- `origin/staging` SHA and deploy marker;
- environment exactly staging;
- mail-safety loaded;
- WPJBP daily event count/schedule/fingerprint;
- exact five daily callbacks;
- vendor candidate-job sender registration count;
- owned hourly evaluator event count/schedule/fingerprint;
- continuation count;
- published `job_alert` count/fingerprint;
- published legacy `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Expected baseline:

- staging SHA `642c8c8efb51a56449fd7048c71d3216590d52bf`;
- daily callbacks 5;
- vendor sender 0;
- hourly evaluator 1;
- continuation 0;
- `job_alert` 0;
- `candidate_alert` 4;
- ID32733 `pending/0`.

No hooks/runners executed.

---

## 14. Zero-side-effect contract

This task must cause:

- application source writes: 0;
- application commits/branches/pushes/deploys: 0;
- WordPress post/meta/option/user/business mutations: 0;
- cron schedule mutations/executions: 0;
- fixtures: 0;
- `wp_mail` / PHPMailer / SMTP: 0 / 0 / 0;
- application/vendor HTTP: 0;
- payment calls: 0;
- Action Scheduler mutations/executions: 0;
- ID32733 executions: 0.

Allowed writes are only task-private temporary extraction/diff files that are removed exactly, plus normal `codex-reports` publication.

No external package downloads or marketplace logins are needed because the exact package is owner-supplied locally.

Production filesystem/database/backups/network/application: FORBIDDEN.

---

## 15. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

Use namespace-free read/hash/archive/Git paths already proven safe. Do not retry known failing sandbox helpers indefinitely. Never use broad process kills.

---

## 16. Final report

Publish:

**Zadatak 1.97 — Validate exact WPJBP 1.2.73 baseline and close candidate-job vendor residue thread**

Report must include:

1. PASS/PARTIAL and exact meaning;
2. baseline/final application SHA unchanged proof;
3. exact accepted 1.2.73 artifact path;
4. archive size/SHA-256/integrity/path-safety;
5. exact plugin header/version constant proof;
6. independence/cleanliness decision;
7. exact three clean 1.2.73 hashes;
8. exact three current staging hashes;
9. exact semantic diff inventory;
10. per-hunk KEEP / REDESIGN / DROP;
11. final `class-job-alert.php` provenance conclusions;
12. final default email-template conclusions;
13. final management-template localization conclusions;
14. historical commit attribution;
15. active vendor sender suppression/reachability proof;
16. owned WPJBP dependencies that must remain;
17. final daily/hourly/continuation state;
18. protected `job_alert` / `candidate_alert` fingerprints;
19. AS fingerprint and ID32733 `pending/0`;
20. mail/SMTP/network/payment/cron/AS counters;
21. temp extraction/diff cleanup proof;
22. production accessed/touched YES/NO;
23. explicit **Conclusion A or B**;
24. if A: exactly one final small implementation task;
25. if B: explicit statement that this vendor-residue thread is CLOSED/PARKED and next subsystem is employer→candidate alerts.

Then STOP. Do not begin 1.98 automatically.
