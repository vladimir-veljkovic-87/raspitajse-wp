# Zadatak 1.95 — Validate supplied WPJBP 1.2.73 vendor artifact and establish clean baseline

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.94
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting anything. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use the declared `origin/staging` baseline as application truth. Verify first that `origin/staging` is exactly:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

If not exact, STOP and report the mismatch.

Execute exactly this task, publish the final report through the existing `codex-reports` workflow, and STOP. Do not implement cleanup and do not begin 1.96.

---

## 1. Context

Zadatak 1.94 correctly stopped `PARTIAL` because no independently trustworthy WP Job Board Pro 1.2.73 package was locally available.

The owner has now explicitly supplied a local ZIP artifact at:

`/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip`

Treat that exact file as an **owner-provided candidate artifact**, not automatically as a trusted baseline. Its version, coherence, independence, and cleanliness must still be proven.

Do not rename, install, activate, move into WordPress, commit, or modify the ZIP.

The three target candidate-job vendor files remain:

1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`

Current application SHA remains `642c8c8efb51a56449fd7048c71d3216590d52bf`.

---

## 2. Goal

Validate whether the supplied ZIP is a trustworthy clean/original **WP Job Board Pro 1.2.73** package.

If it passes provenance/cleanliness checks:

- establish it as the clean comparison baseline;
- hash the archive and exact target files;
- produce exact clean-vendor → current-staging diffs for only the three target files;
- reconcile the unresolved 1.93/1.94 questions;
- classify every Raspitajse-specific semantic delta as `KEEP`, `REDESIGN`, or `DROP`;
- determine the smallest safe future cleanup slice;
- propose exactly one next implementation task, normally 1.96.

If it does not pass:

- explain precisely why;
- do not guess or substitute another internet package;
- do not recommend source cleanup;
- report `PARTIAL`/blocked and STOP.

No application source change in 1.95.

---

## 3. Strict scope and safety

Allowed:

- read the exact owner-supplied ZIP;
- SHA-256 hashing;
- archive listing and path-safety validation;
- extraction into a new task-private temp directory outside repo/runtime paths;
- reading plugin metadata/readme/changelog inside the extracted artifact;
- read-only Git/history/current staging comparisons;
- read-only staging runtime state inspection needed to protect known invariants.

Forbidden:

- downloading any package from internet/vendor/ThemeForest/Envato;
- package-manager/network fetches;
- installing or activating the ZIP;
- extracting over repo or WordPress directories;
- modifying the ZIP;
- application source writes/commits/branches/pushes/deploys;
- staging DB/post/meta/option/user mutations;
- cron/Action Scheduler execution;
- mail/payment execution;
- production filesystem/database/backups/network access.

The normal `codex-reports` publication is the only intended repository write.

---

## 4. Validate exact artifact identity

Inspect only:

`/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip`

Record:

- existence/readability;
- file size;
- modification timestamp as supporting metadata only;
- SHA-256;
- archive/container type;
- top-level archive layout.

Do not expose unrelated paths or secrets.

If the file is missing/unreadable, STOP.

---

## 5. Archive safety before extraction

Before extraction:

- list archive entries;
- reject absolute-path entries;
- reject `../` traversal entries;
- reject symlink/path tricks that could escape the temp directory;
- confirm there is a coherent plugin root.

Extract only to a new task-private temporary directory such as under `/tmp` or another approved temporary workspace.

Do not execute PHP from the archive.

Delete the temporary extraction after evidence collection.

---

## 6. Version proof

The artifact qualifies only if the extracted package proves:

- Plugin Name: `WP Job Board Pro`;
- Version: exactly `1.2.73`;
- plugin/version constants and readme/changelog, where present, are internally consistent;
- package is a coherent plugin payload rather than a copied subset.

If version is not exactly 1.2.73, report the actual evidence and STOP without clean-baseline comparison.

---

## 7. Independence / cleanliness proof

The owner-supplied ZIP is independent from the current repo by provenance, but still verify content cleanliness.

Search the extracted package narrowly for known Raspitajse customization signatures relevant to this audit, including:

- `raspitajse.com`;
- `stage.raspitajse.com`;
- project-specific hard-coded sender identities;
- known project/debug/sample signatures from 1.93;
- custom minute/test-frequency behavior where it would indicate the same customized payload;
- obvious repository/task-specific comments or logging.

Do not reject generic vendor strings merely because they differ from current staging.

If the package itself contains strong Raspitajse-specific candidate-job customizations, it cannot serve as a clean vendor baseline; explain and STOP.

---

## 8. Exact target-file existence and hashes

The accepted package must contain exactly corresponding paths for:

1. `includes/class-job-alert.php`
2. `includes/email-templates-default/html-job-alert-notice.php`
3. `templates/misc/my-jobs-alerts.php`

Compute SHA-256 for each extracted clean file and for each corresponding file from exact `origin/staging`.

Report:

`File | supplied clean artifact hash | current staging hash | identical YES/NO`

If a required file is missing, provenance is insufficient for this audit; STOP.

---

## 9. Exact clean-vendor → current diff

If and only if the artifact passes sections 4–8, diff only the three target files from supplied clean package → exact `origin/staging`.

For every semantic hunk report:

- clean vendor behavior;
- current staging behavior;
- known Git commit/task that introduced the current delta, when traceable;
- active runtime reachability;
- owned-system dependency;
- security/privacy implication;
- `KEEP`, `REDESIGN`, or `DROP` classification of the **Raspitajse-specific modification**;
- recommended future disposition.

Separate unrelated hunks. Distinguish formatting/comment churn from behavioral changes.

Do not classify vendor method existence itself as DROP merely because an owned layer suppresses it.

---

## 10. Resolve the previously unresolved `class-job-alert.php` questions

Using the accepted supplied baseline, determine vendor-original status for:

- `minute` frequency entry/logic;
- `biannually` label;
- candidate sender `From` header;
- `Reply-To` header;
- `Content-Type: text/html`;
- debug/error logging;
- `_job_alert_send_email_time` write behavior;
- any other semantic Raspitajse-specific delta in the file.

Important:

- a vendor-original behavior is not automatically safe to restore;
- do not reintroduce hard-coded sender identities, PII logging, unsafe test behavior, or other known regressions merely to match vendor source;
- if a vendor-original unsafe behavior exists, recommend keeping the safety policy in Raspitajse-owned code before any full vendor restoration.

---

## 11. Resolve default email-template provenance

For `includes/email-templates-default/html-job-alert-notice.php`, establish exactly what the clean package contains.

Classify every current Raspitajse/staging customization, including:

- site URLs;
- logo/icon/asset URLs;
- CTA URLs;
- visible domain strings;
- mailto target;
- malformed target/asset filename if still present;
- branding/content structure.

Determine whether the target architecture should:

- restore vendor default and move branding to owned/configurable email content;
- keep any compatibility hunk temporarily;
- or leave the vendor file untouched pending an owned template replacement.

No implementation in this task.

---

## 12. Resolve management-template localization provenance

For `templates/misc/my-jobs-alerts.php`, prove clean vendor labels and compare with current Serbian literals.

Determine whether the current customization should eventually move to:

- WordPress translations;
- child-theme/owned override;
- another Raspitajse-owned presentation layer.

Do not mechanically restore English if that would create a visible regression before a replacement exists.

---

## 13. Protect active owned candidate-job architecture

Read-only reconfirm current staging invariants:

- vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` final registration = 0;
- WPJBP daily hook has exactly 5 unrelated callbacks, all priority 10;
- owned hourly evaluator event exactly 1;
- continuation event normally 0;
- owned query adapter still intentionally uses WPJBP query infrastructure;
- owned mailer still intentionally uses WPJBP email/render helpers;
- candidate-job create UI still exposes the owned four-frequency policy while legacy getter compatibility remains;
- 1.84 owned mutation/security callbacks remain authoritative;
- `_job_alert_send_email_time` remains owned-read-only compatibility input;
- no active candidate-job delivery depends on vendor `send_job_alert_notice()`.

Do not execute any hook.

---

## 14. Cleanup option analysis

If clean baseline is accepted, compare these options:

### Option A — leave current vendor deltas untouched
Evaluate operational safety vs long-term vendor-fork/update cost.

### Option B — restore only proven obsolete Raspitajse-specific hunks
Prefer if shared vendor helpers still need current compatibility changes.

### Option C — restore one or more complete target vendor files to clean 1.2.73
Only if every current customization in that file is either obsolete or safely replaced by owned/theme/translation policy.

### Option D — staged two-step cleanup
First move remaining branding/localization/security behavior to owned layers, then restore vendor file in a later task.

Recommend one option with evidence.

---

## 15. Future implementation task design

Only if the supplied package passes provenance and a safe cleanup path is proven, propose exactly one small next implementation task (normally `Zadatak 1.96`).

The proposed task must specify:

- exact file(s)/hunk(s) to change;
- whether an owned replacement must be created before vendor restoration;
- exact staging baseline;
- no production;
- source/runtime parity;
- PHP lint + `git diff --check`;
- final vendor sender registration still 0;
- final daily graph still 5;
- hourly evaluator still 1;
- frequency/security/query/mailer regressions protected;
- `_job_alert_send_email_time` owned-read-only;
- protected job_alert/candidate_alert/AS fingerprints;
- ID32733 `pending/0`;
- mail/SMTP/network/payment/cron/AS execution counters expected 0 unless an explicitly bounded intercepted test is genuinely required.

Do not implement 1.96.

---

## 16. Staging protected-state observation

Capture read-only before/final:

- `origin/staging` SHA and deploy marker;
- environment = staging;
- daily cron event count/schedule/fingerprint;
- exact five daily callbacks;
- vendor sender registration count;
- owned hourly evaluator count/schedule/fingerprint;
- continuation count;
- published `job_alert` count/fingerprint;
- published legacy `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Expected stable values from 1.94:

- SHA `642c8c8efb51a56449fd7048c71d3216590d52bf`;
- daily callbacks 5;
- vendor sender 0;
- hourly evaluator 1;
- continuation 0 unless pre-existing bounded work proves otherwise;
- `job_alert` 0;
- `candidate_alert` 4;
- ID32733 `pending/0`.

Inspection only.

---

## 17. Zero-side-effect contract

Expected task effects:

- application source writes: 0;
- application commits/branches/pushes/deploys: 0;
- WordPress business/data mutations: 0;
- cron schedule mutations/executions: 0;
- fixtures: 0;
- `wp_mail`/PHPMailer/SMTP: 0/0/0;
- application/vendor HTTP: 0;
- payment: 0;
- AS mutations/executions: 0;
- ID32733 executions: 0.

Temporary extraction of the supplied ZIP is allowed only outside application paths and must be cleaned exactly.

Production touched/accessed: NO.

---

## 18. Final report

Publish:

**Zadatak 1.95 — Validate supplied WPJBP 1.2.73 vendor artifact and establish clean baseline**

Report must include:

1. PASS or precise PARTIAL/blocker;
2. baseline/final application SHA unchanged proof;
3. exact supplied artifact path and existence/readability;
4. archive size and SHA-256;
5. archive safety/listing result;
6. extraction temp path at coarse level and cleanup proof;
7. plugin name/version evidence;
8. independence/cleanliness decision;
9. exact hashes for all three clean files;
10. exact current staging hashes;
11. clean→current diff summary and per-semantic-hunk classifications;
12. resolved minute/biannually/sender/content-type/debug/send_email_time provenance;
13. resolved default email-template provenance;
14. resolved management-template localization provenance;
15. active vendor sender reachability/suppression proof;
16. owned WPJBP dependencies that must remain;
17. daily/hourly/continuation runtime state;
18. job_alert/candidate_alert protected fingerprints;
19. AS fingerprint and ID32733 `pending/0`;
20. mail/SMTP/network/payment/cron/AS counters;
21. production accessed/touched YES/NO;
22. recommended cleanup option;
23. exactly one proposed next small task if evidence supports one.

Then STOP. Do not begin 1.96.
