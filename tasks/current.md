# Zadatak 2.16 — Establish a verified restorable staging DB backup primitive and resume the controlled WPJBP upgrade

Status: READY
Baseline: 77d3a1019e0248a2abacd607fd508ed6868da70b
Previous task: 2.15
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and the existing remote feature branch `origin/feature/z2-15-wpjbp-upgrade`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, running database tooling, creating task-private files, bootstrapping WordPress, deploying, or changing any source/runtime state. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.13 PASS report, Zadatak 2.14 PARTIAL report, and Zadatak 2.15 PARTIAL report in full.

Require all of these facts before proceeding:

- fresh `origin/staging` is exactly `77d3a1019e0248a2abacd607fd508ed6868da70b`;
- live staging deploy marker is the same commit;
- primary staging worktree is clean and on `staging`;
- `origin/feature/z2-15-wpjbp-upgrade` exists and is exactly `92f151b6f4024fe8f241ffd2d076e1f6544886a5`;
- that feature commit has parent exactly `77d3a1019e0248a2abacd607fd508ed6868da70b`.

If any of those facts differ, STOP and report the mismatch. Do not silently rebuild, rebase, amend, force-push, or widen the feature.

Execute only Zadatak 2.16. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.17 automatically.

---

## 1. Accepted context

Zadatak 2.15 proved all source/provenance work needed for the upgrade and then stopped at the database-backup boundary.

Accepted and already-proven feature state:

- feature branch: `feature/z2-15-wpjbp-upgrade`;
- feature SHA: `92f151b6f4024fe8f241ffd2d076e1f6544886a5`;
- exact vendor targets:
  - WP Job Board Pro `1.2.86`;
  - WP Job Board Pro WC Paid Listings `1.0.19`;
- pinned official ZIP SHA-256 values:
  - WPJBP: `692fbf75f36391524e76db51805c4c79200880436bc9446685b4e9cce19a3f63`;
  - Paid Listings: `26aebee83ec333302f201fa1782346414064004af4cdb8e24732555d5f640467`;
- exact two-path EOL preservation was implemented in root `.gitattributes` only for:
  - `wp-content/plugins/wp-job-board-pro/** -text !eol`;
  - `wp-content/plugins/wp-job-board-pro-wc-paid-listings/** -text !eol`;
- full ZIP extraction -> worktree -> Git index -> committed clean checkout parity passed;
- WPJBP target tree: 1,413 files, fingerprint `23c55f5bba87fc2fc58dbb858c3543a0b45d4e1f5b0fa90d74e569eff034668e`;
- Paid Listings target tree: 179 files, fingerprint `d936377ea8d818ede21cd384568ad93c4092f37e09e83df2be2ed92683a9a6a3`;
- 28 EOL-sensitive official files survived the Git round trip byte-for-byte;
- PHP lint passed for 1,185 target PHP files with zero failures;
- no Raspitajse/site marker was present in the clean target vendor trees;
- no Raspitajse-owned compatibility change was needed at source/static stage.

Do not redo or alter that accepted source work unless a fresh verification demonstrates it is no longer identical to the accepted feature commit.

The only blocker from 2.15 was:

`BLOCKED_NO_RESTORABLE_STAGING_DB_BACKUP`

The prior trusted `wp db export` path returned exit 255 and produced no SQL file or useful stderr under both the documented consistency flags and a minimal invocation. No runtime deploy or mutation occurred.

This task first establishes a safe, verified staging database backup primitive. Only after that gate passes may it resume the remaining controlled upgrade acceptance from 2.15.

---

## 2. Goal

Complete two bounded phases in one task:

### Phase A — backup prerequisite

Establish a consistent, non-empty, integrity-checked, restorable staging database backup using a safe local primitive that does not expose credentials or PII and does not depend on the broken `wp db export` wrapper path.

### Phase B — resume the controlled staging upgrade

If and only if Phase A passes, resume the already-prepared feature `92f151...`, deploy the exact clean WPJBP `1.2.86` and Paid Listings `1.0.19` trees to staging through the approved deploy path, run the security/compatibility acceptance inherited from 2.15, integrate only after acceptance, or rollback completely on any critical failure.

PASS requires both phases to pass.

---

## 3. Hard safety boundaries

Production is forbidden. Do not access or mutate production filesystem, database, WordPress runtime, scheduler, or external services.

Do not change or upgrade:

- Superio parent or child theme;
- WooCommerce;
- WordPress core;
- Elementor or unrelated plugins;
- Hostinger scheduler configuration;
- Raspitajse business rules unless a narrowly proven compatibility fix is required by the target vendor version and remains inside the authorization inherited from 2.15.

Do not use:

- WordPress plugin updater/installer;
- plugin deactivate/reactivate as a migration shortcut;
- broad WP-Cron;
- `wp cron event run --due-now` or `--all`;
- Action Scheduler queue runner;
- manual execution of the three owned business hooks;
- continuation runner;
- live exploit attempts;
- live privileged registration attempts;
- real mail, SMTP, payment, refund, or external WordPress HTTP transport.

The existing human-owned Hostinger `*/15 * * * *` selective runner must remain unchanged. Use its existing shared lock during the runtime-critical boundary as required by 2.15.

---

## 4. Phase A — diagnose the broken wrapper without credential exposure

Do not blindly loop or repeatedly retry the already-failed `wp db export` commands.

Perform a bounded diagnostic sufficient to establish why the wrapper path is unusable or whether the underlying dump client itself is still usable.

Inspect, without exposing secrets:

- WP-CLI version and `wp db export` implementation/help relevant to client selection;
- availability and versions of `mariadb-dump`, `mysqldump`, `mariadb`, and `mysql` binaries;
- whether WP-CLI resolves a dump binary/path incorrectly;
- staging `DB_HOST` shape only as a sanitized classification such as socket / hostname / hostname+port — do not report the actual hostname if it is sensitive;
- whether the prior exit 255 occurs before or after invoking a dump client, using sanitized process/exit evidence only.

Do not print, log, echo, shell-trace, commit, upload, or report:

- DB password;
- DB username if it is identifying/private;
- database name if it is private;
- raw `wp-config.php` secrets;
- SQL rows/content;
- customer/user/order/application/message data.

If a diagnostic mode might print connection arguments or secrets, do not use it.

---

## 5. Authorized secure backup primitive

If `wp db export` remains unusable, this task explicitly authorizes a **task-private direct local database-client dump** using `mariadb-dump` or `mysqldump`, provided all requirements below are met.

### Credential handling

Preferred mechanism:

1. obtain the already-configured staging DB connection values from the staging WordPress configuration in-process without printing them;
2. write a task-private temporary client option file such as a MySQL `[client]` defaults file with mode `0600` inside a mode `0700` task-private directory;
3. pass it only via `--defaults-extra-file=<task-private-path>` or an equally non-observable local mechanism;
4. never place the password directly in command-line arguments, shell history, report text, environment dumps, or Git;
5. delete the credentials file immediately after the dump/validation steps that require it.

Do not use a workaround that copies credentials into repository files or permanent user config.

### Dump properties

Use a local client invocation appropriate to the detected server/client flavor. The dump must be logically consistent and suitable for rollback. Prefer, where supported:

- `--single-transaction`;
- `--quick`;
- `--skip-lock-tables`;
- `--add-drop-table`;
- `--hex-blob`;
- an explicit safe character set compatible with the live DB;
- `--no-tablespaces` when needed to avoid unrelated privilege requirements;
- triggers where normally included by the client.

Do not request routines/events unless the site demonstrably uses them or the required privilege exists; lack of unrelated global privileges must not invalidate an otherwise complete WordPress data/schema dump.

Do not use `--force` to hide dump errors.

### Backup file handling

The backup must:

- live only in a task-private, non-web-accessible host directory;
- directory mode `0700`, SQL file mode `0600`;
- never be committed, uploaded to GitHub, attached to reports, copied into the public web root, or exposed to browser access;
- have a recorded SHA-256 in the report;
- be retained only for the duration of the controlled upgrade/rollback window in this task, then securely removed after final PASS or after a completed rollback.

The report may contain backup timestamp, byte size, SHA-256, structural counts, and sanitized tool versions. It must not contain SQL content or PII.

---

## 6. Mandatory backup integrity gate

A zero-byte or merely successful exit code is not enough.

Before any new vendor code is deployed, prove all of the following:

- dump command exit status `0`;
- backup file exists and is non-empty;
- SHA-256 computed successfully;
- SQL file has no obvious truncation indicator;
- expected schema-definition and data sections are present;
- live staging table count can be compared to dumped table-definition count or an equally strong structural completeness measure without reporting private row data;
- every live WordPress table expected from the selected staging database is represented in the dump, using a sanitized count/hash comparison rather than printing sensitive table content;
- no dump error/warning indicates skipped tables or failed reads;
- a compatible local restore client is available;
- dump can be parsed/read to completion by standard local tooling without syntax/truncation failure.

If the DB account safely permits creating and dropping a **separate isolated temporary database** on the same server, an actual restore rehearsal is authorized and preferred:

- use a random task-private database name unrelated to production;
- restore the dump into that isolated database;
- compare schema/table counts and selected aggregate counts only;
- do not expose data;
- drop the temporary database immediately;
- never point WordPress at it.

However, do **not** require CREATE DATABASE privilege if it is not available. Do not weaken safety by restoring into or overwriting the real staging database merely to rehearse restoration. In the absence of isolated-DB privilege, successful standard dump + complete structural/integrity validation is sufficient for this task's rollback gate.

If no safe complete backup can be produced, STOP with `BLOCKED_NO_RESTORABLE_STAGING_DB_BACKUP`, leave staging unchanged, keep the feature unintegrated, and report the exact bounded blocker/human prerequisite.

---

## 7. Re-verify the accepted feature before runtime work

After the backup gate passes and before deploy:

- verify `origin/feature/z2-15-wpjbp-upgrade` still equals `92f151b6f4024fe8f241ffd2d076e1f6544886a5`;
- verify its parent is the exact live staging baseline;
- verify changed paths remain restricted to root `.gitattributes` plus the two exact plugin trees;
- verify the two `.gitattributes` exceptions are exact and no broader EOL rule exists;
- verify target headers/constants remain WPJBP `1.2.86` and Paid Listings `1.0.19`;
- verify target tree fingerprints remain the accepted 2.15 fingerprints;
- verify the required three historical WPJBP files still have the clean vendor hashes from 2.15;
- verify no Raspitajse/site markers exist in either target vendor tree.

Do not fetch or substitute a newer package/version inside this task. If official package bytes/version have changed from the pinned accepted target and fresh provenance revalidation is required, STOP rather than silently upgrade to a different vendor release.

---

## 8. Runtime-critical boundary and T0

Before deployment:

- acquire the existing selective-runner shared lock nonblocking and keep it through deploy and initial post-deploy acceptance;
- if the lock cannot be acquired because a natural provider cycle is running, wait only a bounded reasonable interval or STOP; do not kill the scheduler process;
- do not edit the Hostinger scheduler;
- capture one guarded sanitized T0 snapshot using the existing deep diagnostic capability;
- require mail safety, WP HTTP preemption, payment guards, staging identity, `DISABLE_WP_CRON=true`, exact three owned scheduler contracts, continuation absence, and protected Action Scheduler state;
- capture the protected business/options/security/Commerce fingerprints previously used in 2.15, without raw IDs, recipients, queries, message bodies, or PII.

T0 runtime versions must still be WPJBP `1.2.73` and Paid Listings `1.0.16`.

If T0 differs materially from the accepted 2.15 stop state in a way not explained by legitimate user activity, STOP and reconcile before deployment.

---

## 9. Controlled deploy

Deploy only the already-proven feature through the approved Raspitajse staging deployment path, consistent with `tasks/README.md` and the 2.15 contract.

Requirements:

- no manual copy around deploy guards;
- no WordPress updater;
- no Superio update in the same transaction;
- no unrelated source files;
- keep WPJBP and Paid Listings active; do not toggle activation merely to force migrations;
- source/runtime/deploy marker/manifest must remain coherent according to the approved deployment process;
- deployed vendor trees must remain byte-for-byte equal to the committed/pinned target trees.

If deployment requires an activation-only migration, STOP for explicit authorization instead of deactivating/reactivating.

---

## 10. First guarded bootstrap and migration accounting

After deploy, run one guarded bootstrap with all staging side-effect protections active.

Prove:

- no fatal error;
- no unexpected PHP warning/notice attributable to the new vendor versions in the bounded acceptance path;
- target plugins remain active;
- source/runtime header and constants are exactly `1.2.86` / `1.0.19`;
- any vendor-required option/schema migration is individually identified and technically justified;
- no user/order/application/message/job/candidate/employer/package business state changes merely from bootstrap;
- WP HTTP/mail/SMTP/payment/refund transport remains blocked/zero as applicable.

If a migration is unexpected, broad, destructive, or cannot be reconciled to the target version, rollback.

---

## 11. Mandatory security and integration acceptance

Re-run the material acceptance inherited from 2.13/2.15. At minimum prove:

1. deployed WPJBP and Paid Listings vendor trees match the pinned official target trees exactly;
2. no Raspitajse patch remains inside either vendor tree;
3. the CVE-2024-12213 registration fix is present in target source and an isolated no-real-user harness proves caller-selected privileged roles cannot become arbitrary WordPress roles; do not create a live staging user;
4. every owned alert-management route remains authoritative with exact nonce/role/profile/capability/ownership behavior and zero vendor mutation callback where owned replacement is expected;
5. alert REST remains disabled where intended;
6. owned candidate→job evaluator remains authoritative; vendor job-alert sender registration remains zero;
7. employer→candidate alert sender and creation surfaces remain retired;
8. candidate automatic time expiry remains disabled;
9. owned job listing expiry and employer pre-expiry notification remain authoritative; retired vendor expiry callbacks remain absent;
10. selective runner still exposes exactly the same three owned hourly zero-arg hooks; no continuation or broad vendor cron surface appears;
11. SenderPolicy/Transport mapping remains exact and caller-provided From/Reply-To does not regain authority;
12. candidate/employer dashboard and alert/application/package template dependencies load without missing class/method/fatal errors under active Superio `1.3.17`;
13. job search/filter/detail, employer/candidate profile, submission/edit and application contracts required by current Raspitajse code remain compatible in guarded non-destructive tests;
14. Paid Listings package listing/selection/standalone purchase transport and template loader contracts remain compatible without making a real payment;
15. canonical package entitlement remains quota + immutable 30-calendar-day validity, separate from listing duration;
16. Raspitajse Commerce employer lookup, checkout/order company fields, HPOS CRUD and processed/cancelled bridge contracts remain exact;
17. no unexpected vendor heartbeat/update-check behavior is introduced into runtime acceptance.

Use fixtures, isolated mocks, static/runtime callback inspection, and existing safe test harnesses. Do not send real mail/payment/network traffic and do not mutate protected real business records merely to prove compatibility.

---

## 12. T1 reconciliation

Capture a guarded T1 and compare it to T0.

PASS requires:

- target runtime versions exact;
- source/runtime/deploy state coherent;
- no unexpected protected business mutation;
- no unexpected options/schema mutation beyond individually documented target migration;
- non-allowlisted cron unchanged except a specifically explained vendor-required migration if any;
- exact three owned scheduler event/callback contracts retained;
- continuation rows zero;
- Action Scheduler protected state unchanged unless a target-required migration is both expected and non-executing;
- ID32733 remains protected and unexecuted;
- no broad cron, Action Scheduler, owned manual hook, mail, SMTP, payment, refund, or external WordPress HTTP execution;
- backup file still exists and checksum still matches until acceptance is complete.

---

## 13. Integration and rollback decision

### If every acceptance gate passes

- integrate the exact accepted feature to `staging` only according to the repository workflow, fast-forward/no history rewrite;
- ensure runtime deploy marker and source match the final accepted staging SHA;
- re-prove clean staging worktree and exact target plugin versions/tree fingerprints;
- release the runner lock;
- remove the task-private DB backup and credential artifacts only after final acceptance is recorded and no rollback is pending;
- publish PASS.

### Rollback triggers

Immediately rollback on any fatal/migration error, target version/hash/tree mismatch, CVE-fix failure, unexpected vendor route/sender, missing or duplicate owned callback, candidate-expiry regression, package/HPOS/30-day-policy regression, cron/Action Scheduler drift, unexpected mail/network/payment effect, protected business mutation, or unrecoverable frontend/API contract failure.

### Rollback order

- keep the runner lock;
- prevent application requests as required by the existing maintenance/deploy boundary;
- restore both previous plugin/source/runtime state and the exact pre-upgrade database backup if any DB/options/schema/data migration occurred;
- file-only rollback is permitted only if guarded evidence proves no DB/options/schema/data mutation occurred;
- restore deploy marker/manifest coherently through the approved path;
- run one guarded read-only bootstrap and re-prove old versions, owned security/communications/scheduler contracts, Commerce/package fingerprints, Action Scheduler/ID32733, and side-effect zeros;
- release the lock only after rollback consistency passes;
- remove task-private backup/credential artifacts after rollback verification.

Never run old plugin code against a partially migrated new database state.

---

## 14. Source-change boundary

The expected application source for this task is the already-existing feature commit `92f151...`.

Do not add new application/business code merely to work around the backup problem.

A task-private backup helper/script outside Git is allowed.

If a persistent Raspitajse-owned backup tool is clearly necessary for future operations, do **not** add it opportunistically inside this upgrade transaction. Complete or block this task first and propose a separate tooling task unless the absence of such a persistent tool makes safe completion impossible.

No changes to Superio, WooCommerce, WordPress core, or unrelated plugins are authorized.

---

## 15. Acceptance criteria

PASS requires all of the following:

- baseline and feature refs exact;
- secure staging DB backup created before runtime change;
- backup non-empty, checksum/integrity/structural completeness verified;
- no secrets or SQL/PII exposed;
- accepted vendor feature unchanged and byte-parity preserved;
- T0 captured under shared lock;
- controlled staging deploy completed through approved path;
- WPJBP runtime `1.2.86` and Paid Listings runtime `1.0.19`;
- exact official vendor tree parity at runtime;
- CVE fix and owned security boundary accepted;
- communications/scheduler retirement and exact three-hook contract accepted;
- Commerce/HPOS/package/30-day entitlement compatibility accepted;
- Superio `1.3.17` compatibility checks required by this upgrade accepted without upgrading Superio;
- T1 reconciled with no unexplained protected mutation;
- source/runtime/deploy/staging integration coherent and clean;
- mail/SMTP/payment/refund/external WordPress HTTP real side effects zero;
- broad cron/Action Scheduler/manual owned-hook execution zero;
- Hostinger scheduler mutation zero;
- production touched NO;
- task-private DB credential and backup artifacts removed after successful final acceptance or completed rollback.

If any hard criterion cannot be proved, do not report PASS.

---

## 16. Final report

Report at minimum:

- result and classification;
- exact refs/baseline/feature/final staging SHA;
- backup primitive selected and why `wp db export` was not used if still broken;
- sanitized dump/restore client versions;
- backup byte size and SHA-256, integrity/table-count evidence, and whether isolated restore rehearsal was possible;
- confirmation that no credential/SQL/PII was exposed;
- T0 summary;
- deploy/version/vendor-tree parity evidence;
- any bounded vendor migration;
- CVE/security acceptance;
- communications/alerts/expiry/scheduler acceptance;
- Commerce/HPOS/package/entitlement acceptance;
- Superio 1.3.17 compatibility result;
- T1 reconciliation;
- rollback status;
- cleanup of backup/credential scratch;
- scheduler/manual runner/broad cron/Action Scheduler/mail/network/payment counters;
- production touched NO;
- exactly one proposed next task, not created or started.

If PASS, propose exactly one next task:

**Zadatak 2.17 — Superio 1.3.17 → 1.3.37 security/upgrade readiness audit with bundled-plugin and child-theme compatibility mapping.**

If blocked before upgrade completion, propose only the smallest prerequisite needed to close the actual blocker.

STOP after publishing the report. Do not begin the next task.
