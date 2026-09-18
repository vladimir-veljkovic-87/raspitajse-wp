# Zadatak 2.53 — Add bounded core/WPForms deploy support and complete both patch updates in one transaction

Status: READY
Baseline: e40619b99563871889caffbd082f0da9d1cd25d3
Previous task: 2.52.2
Target: staging
Production: FORBIDDEN
Time budget: 60 minutes

## Required result

This task is complete only when staging is running and verified on:

- WordPress core `7.1.1`;
- WPForms Lite `2.0.2`;

with Git, live runtime, and deploy marker aligned to the final accepted staging SHA.

Do not return PASS for deploy-tool preparation alone, package download alone, or partial update progress. If the final versions are not both active and accepted, return one precise BLOCKED result.

## Starting facts

Read `tasks/README.md` and the final Zadatak 2.52.2 report before mutation.

Verify fresh:

- `origin/staging == e40619b99563871889caffbd082f0da9d1cd25d3`;
- local staging worktree is clean;
- live deploy marker equals the same baseline;
- live WordPress is still `7.1`;
- live WPForms Lite is still `2.0.1.1`;
- both updates are still available/relevant.

If any baseline/version assumption changed, STOP with one precise blocker rather than silently rebasing the task.

The 2.52.2 blocker is already known: `deployment/deploy-staging.sh` cannot deploy WordPress core paths or `wp-content/plugins/wpforms-lite`. Resolve that blocker inside this task and then continue directly through both updates. Do not split this into another deploy-tool-only task followed by another update task.

## Phase A — bounded deploy capability

Create one scoped feature branch from the exact baseline.

The first feature commit may modify only `deployment/deploy-staging.sh` and must add a narrowly scoped, reusable staging deployment path for:

1. WordPress core package files;
2. `wp-content/plugins/wpforms-lite/**`.

Do NOT add repository root `.` to the general allowlist. Do NOT create a mechanism capable of syncing arbitrary top-level files.

### WordPress core path contract

The deploy script must permit only canonical WordPress core package paths:

- `wp-admin/**`;
- `wp-includes/**`;
- top-level regular files that are actually part of the official WordPress core package being deployed.

Explicitly forbid at minimum:

- `wp-config.php` and `wp-config-sample.php` unless the latter is actually part of the official package diff and the task proves it is safe;
- `.htaccess`;
- `.user.ini`;
- any credential/auth file;
- `wp-content/**` except the separately authorized WPForms Lite root;
- server/deployment state files;
- arbitrary untracked root files.

Prefer deriving the allowed top-level core file set from the verified official package inventory rather than maintaining a broad wildcard.

### WPForms path contract

Allow only:

`wp-content/plugins/wpforms-lite/**`

with the same symlink, deletion, checksum, source-existence, and target-boundary protections used by the existing guarded deploy code.

### Required deploy-tool properties

The new path must:

- work for both forward deploy and rollback;
- support changed, added, and deleted tracked files;
- never follow a symlinked target root;
- never write outside the staging site root;
- preserve the existing communications manifest/parity behavior and current vendor reconciliation behavior;
- leave all existing allowlisted behavior unchanged;
- refuse any target path outside the explicit core/WPForms contract;
- update the deploy marker only after the complete deployment succeeds.

Run `bash -n` and a disposable non-web rehearsal proving that representative add/change/delete operations for core and WPForms are accepted while representative forbidden paths are rejected.

Do not touch production.

## Phase B — establish rollback-capable deploy tooling

Commit the deploy-tool change separately as Commit A.

Review Commit A diff and require it contains only `deployment/deploy-staging.sh`.

Fast-forward `staging` to Commit A and push without force. This is intentionally permitted before the package update so that the accepted deploy mechanism remains available for rollback if the package deployment fails.

Run the normal staging deploy once from Commit A. No application/core/plugin bytes should change in this step; only Git/live marker alignment should advance to Commit A.

Verify:

- local/origin staging = Commit A;
- deploy marker = Commit A;
- WordPress still `7.1`;
- WPForms Lite still `2.0.1.1`;
- worktree clean.

If Commit A cannot be integrated/deployed safely, STOP. Do not attempt the package updates.

## Phase C — official package update in the same task

From exact Commit A create/continue the same scoped feature branch for Commit B.

Download only the official target packages from authoritative WordPress.org sources:

- WordPress core `7.1.1`;
- WPForms Lite `2.0.2`.

External network access is authorized only as required to retrieve and verify these two official packages/checksums. Do not use vendor updaters inside WordPress admin and do not update anything else.

Record package provenance and SHA-256 without printing secrets.

Apply packages to the repository working copy with these rules:

- no bundled theme updates;
- no unrelated plugin changes;
- no `wp-config.php` changes;
- no changes outside canonical core files and `wp-content/plugins/wpforms-lite/**`;
- remove files only when the official target package proves they were removed;
- Git remains the source of truth.

Require the final feature diff relative to Commit A to contain only:

- canonical WordPress core 7.1.1 package changes;
- WPForms Lite 2.0.2 package changes.

Verify official integrity/checksums where available, run one bounded PHP syntax pass over changed PHP files, and run a strict diff-scope check.

Commit package changes separately as Commit B and push the feature branch.

## Phase D — rollback readiness before live package mutation

Before deploying Commit B:

- acquire the standard staging mutation/runner lock;
- retain exact rollback copies or a verified restorable inventory of the live/tracked core and WPForms paths affected by Commit B;
- capture the current deploy marker and installed versions;
- determine whether `7.1 → 7.1.1` changes WordPress DB schema version.

If a DB schema change is required, create and verify a full staging DB backup before deployment using the already proven ambient MariaDB auth path. If no schema change is required, explicitly record `DB_BACKUP_NOT_REQUIRED_NO_SCHEMA_CHANGE`.

Do not inspect credential files. No production access.

## Phase E — deploy both updates and verify the actual result

Deploy exact Commit B using only the newly accepted `deployment/deploy-staging.sh` path.

Immediately verify:

- live WordPress version = `7.1.1`;
- live WPForms Lite version = `2.0.2`;
- all changed core/WPForms files have exact source/runtime byte parity;
- deleted package files are absent;
- forbidden root/config files were not touched;
- deploy marker = Commit B feature SHA before integration.

If the core update requires the standard WordPress DB upgrade, run only that required upgrade under the held lock after the verified backup. Do not run unrelated migrations.

## Phase F — one focused acceptance pass only

Run one bounded acceptance pass. Do not grow a new broad harness and do not repeat unrelated suites.

Required checks:

- WordPress bootstrap completes without a new fatal/error/warning attributable to these updates;
- public home, `/login-register/`, and jobs page succeed where the environment permits WordPress handling;
- administrator wp-admin bootstrap and plugin screen load;
- WPForms Lite plugin loads and remains active;
- if at least one existing published WPForms form exists, render/bootstrap one existing form without submitting it; otherwise record `WPFORM_RENDER_NOT_APPLICABLE`;
- existing Raspitajse owned plugins and active child theme remain loaded;
- no plugin/theme activation changes except none;
- no real email/SMTP, form submission, payment/refund, broad cron, or Action Scheduler execution;
- protected Action Scheduler item 32733 remains pending with attempts 0;
- no unexpected external WordPress HTTP transport beyond the two already completed package-download/checksum operations outside the runtime acceptance;
- no production access.

Known Hostinger/LiteSpeed pre-WordPress HTTP 403 is not by itself a product regression; use internal bootstrap/template evidence when applicable and record that limitation once.

Do not rerun quota, applications, expiry, communications, package-entitlement, or job-alert suites unless a direct regression from these two patch updates is observed.

## Phase G — integration or exact rollback

### PASS path

Only if both target versions and every critical acceptance gate pass:

1. fast-forward `staging` from Commit A to exact Commit B and push without force;
2. deploy final `staging`;
3. verify local staging = origin/staging = live deploy marker;
4. reverify WordPress `7.1.1`, WPForms Lite `2.0.2`, and exact source/runtime parity for the changed target paths;
5. remove rollback material only after final proof;
6. publish:
   `PASS: PATCH_UPDATES_DEPLOYED`.

### Failure path

If package deploy, DB upgrade, parity, or focused acceptance fails:

1. do not integrate Commit B;
2. use the now-integrated Commit A deploy mechanism to restore the exact Commit A core/WPForms runtime state and marker;
3. restore DB only if this task changed DB schema/state and restoration is required;
4. prove WordPress `7.1`, WPForms Lite `2.0.1.1`, source/runtime baseline parity, and clean protected state;
5. keep the validated Commit A deploy-tool improvement on staging unless Commit A itself is the cause of the failure;
6. publish one precise BLOCKED reason and STOP.

Never leave staging in a mixed core/plugin state.

## Scope

Authorized source changes:

- Commit A: `deployment/deploy-staging.sh` only.
- Commit B: official WordPress core 7.1.1 package diff plus `wp-content/plugins/wpforms-lite/**` 2.0.2 package diff only.

Forbidden:

- production;
- themes;
- Superio/Apus/RevSlider;
- WP Job Board Pro / Paid Listings;
- Raspitajse-owned business code;
- communications/commerce behavior changes;
- scheduler configuration changes;
- other plugins;
- broad WordPress updater/TGMPA actions;
- force push/history rewrite.

## Final report

Publish one concise final report containing:

- result;
- Commit A SHA;
- Commit B/final staging SHA when PASS;
- old/new WordPress and WPForms versions;
- official package provenance/hashes;
- exact changed scope;
- deploy-tool validation result;
- DB schema/backup status;
- focused smoke result;
- rollback status if used;
- final local/origin/live/marker alignment;
- production touched: NO.

STOP after the report.