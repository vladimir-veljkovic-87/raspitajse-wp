# Zadatak 1.94 — Establish trustworthy WPJBP 1.2.73 vendor baseline provenance

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.93
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting any source/artifact. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Do not use application files inherited on `codex-tasks` as application truth. All application/source comparisons use the declared `origin/staging` baseline.

Verify exact baseline first. If `origin/staging` is not exactly `642c8c8efb51a56449fd7048c71d3216590d52bf`, STOP and report the mismatch.

Execute exactly this task. This task is **READ-ONLY for application source, staging WordPress state and production**. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not implement cleanup and do not begin 1.95.

---

## 1. Context

Zadatak 1.93 completed its reachable audit but correctly returned `PARTIAL` because repository history does not contain a trustworthy clean/original WP Job Board Pro 1.2.73 payload.

The earliest repository snapshot already contains Raspitajse-specific changes, including candidate-job alert test/debug/sender/template behavior. Therefore that snapshot cannot be used as proof of upstream vendor-original content.

1.93 identified exactly three later-modified candidate-job WPJBP files that require provenance before any vendor cleanup decision:

1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`

Known conclusions that must remain protected:

- do **not** mechanically revert 1.86 because that would reintroduce known hard-coded sender identities;
- do **not** restore removed PII/debug logging or minute/test frequency behavior;
- active candidate→job delivery is owned by Raspitajse and the vendor candidate-job sender is suppressed from the final runtime graph;
- vendor infrastructure may still be intentionally reused by owned query/render/UI compatibility paths;
- no cleanup is authorized until a trustworthy vendor baseline is established.

Current application/staging SHA remains:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

---

## 2. Goal

Establish whether an **already-authorized, local/licensed, independently trustworthy WP Job Board Pro 1.2.73 artifact** is available and can serve as a clean vendor baseline for the three exact files above.

If such an artifact exists:

- prove its provenance/version sufficiently;
- hash it;
- compare only the three identified files with current `origin/staging`;
- identify exact Raspitajse-specific deltas relative to that clean artifact;
- classify which future cleanup is safe;
- design one exact next implementation task.

If no trustworthy artifact is available:

- report the blocker precisely;
- do not guess;
- do not download arbitrary internet/plugin copies;
- do not recommend source cleanup;
- STOP.

---

## 3. Strict read-only / no-network scope

Allowed:

- repository history and Git objects;
- current staging application source, read-only;
- already-mounted/local workspace files;
- already-present local archives/backups/artifacts accessible to the execution environment;
- already-recorded checksums/manifests/documentation present locally or in the repository;
- hashing and temporary extraction of a **local artifact** into a task-private temporary directory outside application runtime paths.

Forbidden:

- downloading WPJBP from ThemeForest/Envato/vendor/public internet;
- logging into marketplaces/vendor accounts;
- HTTP requests to find a package or checksum;
- package-manager/network fetching;
- production filesystem or DB access;
- staging application source modification;
- staging WordPress DB/post/meta/option/user mutation;
- deployment;
- cron or Action Scheduler execution;
- mail/payment execution.

If the artifact is not already locally available, STOP instead of fetching one.

---

## 4. Artifact discovery boundaries

Search only authorized local locations that are already available to the execution session.

Examples may include, if they already exist and are readable:

- repository-local backup/archive directories;
- deployment/package artifacts already stored beside the repo;
- task workspace/download cache already mounted for this project;
- staging-host backup directories that are explicitly non-production and already part of the authorized staging workspace;
- local manifests/checksum records.

Do **not** recursively crawl unrelated user/system directories or secret stores.

Do not access production paths such as production `public_html`, production backups, production database dumps, or production-only credential areas.

Report every searched location at a coarse, non-secret level and whether a plausible artifact was found.

---

## 5. What qualifies as a trustworthy artifact

An artifact may be accepted as the vendor baseline only if all of the following are proven:

1. It is clearly WP Job Board Pro, not a theme copy or customized extracted staging tree.
2. Plugin version is exactly **1.2.73** from plugin metadata/readme/package contents.
3. It exists independently of the current customized repository payload.
4. Its provenance is credible, e.g. an archived licensed plugin ZIP, backup artifact with documented origin, or an independently recorded checksum/source package known to predate Raspitajse customization.
5. It is internally coherent: required plugin files/version metadata align and the package is not obviously a partial hand-copied subset.
6. It does not contain obvious Raspitajse customization signatures that would invalidate it as clean baseline.

Potential disqualifying signatures include, but are not limited to:

- `raspitajse.com` / `stage.raspitajse.com` branding in candidate-job vendor files where a vendor-clean artifact would not reasonably contain it;
- project-specific hard-coded sender identities;
- project-specific debug/sample IDs;
- custom minute/test frequency behavior known from the earliest customized repository snapshot;
- repository-specific comments/logging clearly introduced for Raspitajse.

Do not reject a package merely because WordPress/plugin metadata includes generic site-independent defaults. Use evidence.

---

## 6. Archive safety

If a local ZIP/archive is found:

- hash the archive before extraction using SHA-256;
- list its contents first;
- reject path traversal/absolute-path entries;
- extract only to a new task-private temporary directory;
- do not extract over the repo, staging runtime, plugin directory or any shared application path;
- do not execute any PHP from the archive;
- remove the temporary extraction after evidence capture unless policy requires preserving only non-sensitive hashes/path metadata.

No install/activation is allowed.

---

## 7. Version/provenance proof

For every plausible artifact, report without exposing secrets:

- coarse artifact location/path;
- filename;
- file size;
- SHA-256;
- package root layout;
- plugin header name;
- plugin version;
- any readme/changelog version evidence;
- timestamp metadata only as supporting evidence, never sole proof;
- why the artifact is or is not independent from the customized repository tree.

Do not print license keys, credentials, purchase codes or private account identifiers if any are present nearby.

If multiple plausible 1.2.73 artifacts exist, compare their hashes/content. If they disagree, do not arbitrarily choose one: explain the discrepancy and determine whether one has stronger provenance. If provenance remains ambiguous, STOP.

---

## 8. Exact three-file baseline hashes

For an accepted clean artifact, compute SHA-256 for exactly:

1. `includes/class-job-alert.php`
2. `includes/email-templates-default/html-job-alert-notice.php`
3. `templates/misc/my-jobs-alerts.php`

Also record current `origin/staging` SHA-256 for the corresponding files.

Report a table:

`File | clean artifact hash | current staging hash | identical YES/NO`

No application file modification.

---

## 9. Exact clean-vendor diff

If a trustworthy artifact is accepted, produce read-only diffs from clean artifact → current `origin/staging` for only the three files.

For every semantic hunk report:

- clean vendor behavior;
- current behavior;
- known repository commit/task that introduced the current delta, if traceable;
- active runtime reachability;
- owned-system dependency;
- security/privacy implication;
- `KEEP`, `REDESIGN`, or `DROP` classification of the **Raspitajse-specific delta**;
- safe future disposition.

Do not classify the existence of upstream vendor methods as DROP merely because owned code suppresses a sender. Classify only customization deltas.

---

## 10. Reconcile 1.93 conclusions against clean baseline

Explicitly revisit the important 1.93 findings using the accepted clean artifact:

### `class-job-alert.php`

Resolve whether each of these is vendor-original or Raspitajse-specific:

- `minute` frequency presence/absence;
- `biannually` label;
- sender `From` header;
- `Reply-To` header;
- `Content-Type: text/html`;
- debug/error logging;
- `_job_alert_send_email_time` vendor write behavior;
- comments/format churn only where relevant to exact restoration.

### `html-job-alert-notice.php`

Resolve whether the vendor baseline is generic/vendor-branded or already contains any site-specific placeholders/URLs.

Identify precisely which current Raspitajse/staging URLs, assets or malformed targets are customization.

### `my-jobs-alerts.php`

Resolve vendor-original labels and whether current Serbian literals are a direct vendor-file customization versus proper translation infrastructure.

Do not change anything.

---

## 11. Active architecture protection

Regardless of baseline findings, a future cleanup must not break the active Raspitajse-owned candidate-job architecture.

Read-only reconfirm:

- final vendor candidate-job sender registration = 0;
- WPJBP daily hook callbacks = exactly 5 unrelated callbacks;
- owned hourly evaluator event = exactly 1;
- continuation event normally 0;
- owned query adapter still uses intentional WPJBP query infrastructure;
- owned mailer still uses intentional WPJBP email/template helpers;
- candidate-job create UI still relies on the compatible frequency getter surface;
- 1.84 owned add/remove security callbacks remain authoritative;
- `_job_alert_send_email_time` remains owned-read-only compatibility input.

No hook execution.

---

## 12. Cleanup decision rules

If clean baseline is proven, apply these principles:

- Prefer restoring vendor files toward clean vendor-owned state where Raspitajse-specific deltas are obsolete.
- Do **not** restore known unsafe behavior solely because it appears in a baseline if that behavior is actually vendor-original but conflicts with current security/business architecture; instead classify whether an owned suppression/adaptation is required before restoration.
- Do not create a vendor fork simply to preserve cosmetic formatting/comments.
- Site branding/localization/business policy should preferably live in owned/theme/translation/configuration layers, not vendor core.
- Security/privacy hardening must not regress.

If a clean baseline contains hard-coded or otherwise unsafe upstream behavior, report that clearly. “Vendor-original” does not automatically mean “safe to restore.”

---

## 13. Outcome A — trustworthy clean artifact found

If provenance passes:

Result may be `PASS` even though no source is changed.

Final report must state:

- accepted artifact provenance;
- archive/package SHA-256;
- exact 3-file hashes;
- exact clean→current deltas;
- classifications;
- whether complete-file restoration is safe or only selected hunks should be considered;
- exactly one proposed next small implementation task, normally 1.95.

Do not implement 1.95.

---

## 14. Outcome B — no trustworthy artifact found

If no qualifying local artifact exists, or provenance remains ambiguous:

Result should be `PARTIAL`/blocked, not fabricated PASS.

Report:

- where you looked;
- what candidates were found, if any;
- why each candidate failed provenance/version/cleanliness requirements;
- exact blocker;
- current application/runtime remains unchanged;
- recommendation: obtain/provide an authorized original WPJBP 1.2.73 package or verified checksum artifact before vendor cleanup.

Do not propose a revert based on the customized Git baseline.

STOP.

---

## 15. Staging read-only observation

Capture current stable state without executing scheduled work:

- `origin/staging` SHA and deploy marker;
- environment exactly `staging`;
- WPJBP daily event count/schedule/fingerprint;
- exact five-callback daily-hook graph;
- direct vendor candidate-job sender registration count;
- owned hourly evaluator count/schedule/fingerprint;
- continuation event count;
- global `job_alert` count/fingerprint;
- published legacy `candidate_alert` count/fingerprint;
- pending Action Scheduler count/fingerprint;
- ID32733 status/attempts.

Expected from 1.92/1.93:

- application SHA `642c8c8ef51a56449fd7048c71d3216590d52bf`;
- daily callbacks 5;
- vendor candidate-job sender 0;
- owned hourly evaluator 1;
- continuation normally 0;
- `job_alert` 0;
- `candidate_alert` 4;
- ID32733 `pending/0`.

Inspection only.

---

## 16. Zero-side-effect contract

This task must cause:

- application source writes: 0;
- application commits: 0;
- application feature branches: 0;
- application pushes: 0;
- deploys: 0;
- WordPress business-state mutations: 0;
- cron schedule mutations: 0;
- fixtures: 0;
- mail attempts: 0;
- PHPMailer/SMTP: 0;
- application/vendor HTTP requests: 0;
- payment calls: 0;
- Action Scheduler mutations/executions: 0.

Temporary local extraction of an already-present archive into a task-private temp directory is allowed and must be cleaned exactly.

The normal `codex-reports` publication is the only intended repository write.

---

## 17. Scheduler / mail / payment safety

Forbidden:

- `wp cron event run`;
- broad WP-Cron runners;
- `do_action('wp_job_board_pro_email_daily_notices')`;
- vendor candidate-job sender execution;
- owned evaluator execution;
- continuation execution;
- Action Scheduler runner/due actions;
- ID32733 execution;
- `wp_mail`/real SMTP;
- payment operations.

---

## 18. Production safety

Production filesystem/database/backups/network access:

**FORBIDDEN**.

A potentially useful artifact located only in production or production backup storage is **not authorized for this task**. Report it only if its existence is already known from non-production metadata; do not access it.

---

## 19. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` remains.

This task should require only read-only filesystem/Git/hash/archive inspection. If a sandbox helper hits the known namespace failure, do not retry indefinitely. Use proven namespace-free read/hash/archive tools where safe.

Never use broad process kills.

---

## 20. Final report

Publish:

**Zadatak 1.94 — Establish trustworthy WPJBP 1.2.73 vendor baseline provenance**

Report must include:

1. result `PASS` or precise blocked/PARTIAL status;
2. baseline/final application SHA and proof unchanged;
3. searched authorized local artifact locations;
4. candidate artifacts found;
5. provenance decision for each candidate;
6. accepted artifact filename/coarse location if any;
7. accepted artifact SHA-256 if any;
8. exact WPJBP version proof;
9. archive safety/extraction proof;
10. exact clean hashes for the three target files if baseline accepted;
11. current staging hashes for the three target files;
12. exact clean→current diff summary;
13. reconciliation of 1.93 sender/frequency/debug/template/localization findings;
14. per-hunk KEEP / REDESIGN / DROP decisions if clean baseline accepted;
15. active runtime reachability/suppression proof;
16. owned WPJBP infrastructure dependencies that must remain;
17. current daily-hook graph and cron state;
18. owned hourly/continuation state;
19. `job_alert` and legacy `candidate_alert` protected fingerprints;
20. AS fingerprint and ID32733 `pending/0`;
21. mail/SMTP/network/payment/cron/AS counters, all expected 0;
22. temporary extraction cleanup proof;
23. production accessed/touched YES/NO;
24. exactly one proposed next small task if evidence supports one; otherwise exact blocker and required artifact next step.

Then STOP.

Do not modify WPJBP or owned application source.
Do not begin 1.95.
