# Zadatak 2.14 — Controlled WP Job Board Pro staging upgrade and vendor normalization with pre/post security/compatibility acceptance

Status: READY
Baseline: 77d3a1019e0248a2abacd607fd508ed6868da70b
Previous task: 2.13
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, creating a feature branch/worktree, downloading packages, bootstrapping WordPress, taking backups, or changing source. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.13 PASS report in full. Its decision was:

`READY_FOR_CONTROLLED_STAGING_UPGRADE`

Verify fresh `origin/staging` is exactly:

`77d3a1019e0248a2abacd607fd508ed6868da70b`

If it differs, STOP and report the mismatch. Do not silently rebase, widen scope, or auto-select a newer plugin version.

Execute only Zadatak 2.14. Publish all task-state/final reports through the existing `codex-reports` workflow and STOP. Do not begin 2.15 automatically.

---

## 1. Goal

Perform one controlled, reversible **staging-only** upgrade transaction that:

1. replaces the current customized WP Job Board Pro `1.2.73` tree with the exact clean official ApusThemes `1.2.86` tree;
2. upgrades the synchronized companion WP Job Board Pro WC Paid Listings `1.0.16` to exact clean official `1.0.19`;
3. normalizes WPJBP vendor ownership by removing Raspitajse-specific patches from the vendor tree rather than transplanting them into the new version;
4. preserves all required Raspitajse business/security behavior through Raspitajse-owned code, configured options, translations, and legitimate overrides;
5. proves the CVE registration fix, alert/security cutovers, scheduler boundary, communications behavior, package/entitlement behavior, HPOS bridge, and critical frontend/dashboard compatibility on staging;
6. rolls back completely if any critical gate fails.

This is not a general WPJBP refactor, theme upgrade, WooCommerce upgrade, scheduler redesign, or production change.

---

## 2. Pinned official targets and provenance gate

The only authorized upgrade targets for this task are:

### WP Job Board Pro

- target version: `1.2.86`
- official metadata: `https://www.apusthemes.com/themeplugins/wp-job-board-pro.json`
- official package: `https://www.apusthemes.com/themeplugins/wp-job-board-pro.zip`
- required ZIP SHA-256: `692fbf75f36391524e76db51805c4c79200880436bc9446685b4e9cce19a3f63`
- required archive root: `wp-job-board-pro`

### WP Job Board Pro WC Paid Listings

- target version: `1.0.19`
- official metadata: `https://www.apusthemes.com/themeplugins/wp-job-board-pro-wc-paid-listings.json`
- official package: `https://www.apusthemes.com/themeplugins/wp-job-board-pro-wc-paid-listings.zip`
- required ZIP SHA-256: `26aebee83ec333302f201fa1782346414064004af4cdb8e24732555d5f640467`

Before any source mutation:

- re-fetch metadata and packages anonymously over HTTPS into task-private scratch;
- require metadata to still identify exactly `1.2.86` and `1.0.19`;
- require both ZIP hashes to match exactly the pinned values above;
- re-run CRC, path traversal, absolute-path, duplicate-path, symlink, root-layout, header, and version-constant checks;
- do not use WordPress update APIs, plugin installers, cached update transients, ThemeForest login, license tokens, signed URLs, or reseller packages;
- if either vendor endpoint now serves a different version or the same version with different bytes, STOP with `BLOCKED_TARGET_PROVENANCE_CHANGED` and do not upgrade.

Historical/local packages remain ineligible:

- old `1.2.66` artifact: historical only;
- contaminated/customized `1.2.73` artifact: never a clean baseline;
- Superio bundled WPJBP `1.2.72` and Paid Listings `1.0.15`: stale historical packages only.

Do not auto-upgrade to a newer version than the pinned target even if one becomes available during execution.

---

## 3. Hard safety boundaries

Production filesystem/database/runtime/WordPress/scheduler access is forbidden.

Do not modify or upgrade:

- WordPress core;
- WooCommerce;
- Superio parent theme;
- Superio child theme except for a narrowly necessary Raspitajse-owned compatibility override explicitly justified by acceptance failure;
- any unrelated plugin;
- Hostinger/hPanel scheduler configuration;
- broad WP-Cron or Action Scheduler execution paths.

Do not:

- call `/wp-cron.php`;
- run `wp cron event run --due-now`, `--all`, or equivalent broad cron;
- run the Action Scheduler queue;
- manually execute the three owned scheduler hooks;
- create or execute a continuation runner;
- send real mail/SMTP;
- perform real payments/refunds;
- allow unexpected WordPress-runtime external HTTP;
- submit a malicious live registration request or create a privileged test user;
- deactivate/reactivate either target plugin merely to force migrations.

The existing accepted human-owned Hostinger scheduler must remain exactly one `*/15 * * * *` zero-argument selective runner. Do not edit, delete, disable, recreate, duplicate, or manually trigger it.

---

## 4. Allowed source scope

The intended source mutation is the clean replacement of exactly these vendor trees:

- `wp-content/plugins/wp-job-board-pro/`
- `wp-content/plugins/wp-job-board-pro-wc-paid-listings/`

The replacement must come from the exact clean official package trees, including vendor additions and deletions. Do not hand-merge selected hunks.

A **minimal Raspitajse-owned compatibility change** is allowed only if post-upgrade acceptance identifies a concrete regression in an already accepted Raspitajse business/security requirement and the fix can be implemented cleanly outside vendor code without changing business semantics. Such a change must be:

- narrowly scoped;
- independently tested;
- explicitly listed in the report;
- not a workaround for an unknown vendor failure.

If compatibility requires broad Communications/Commerce/theme redesign or business-rule changes, rollback and STOP rather than expanding this task.

No manual edits may remain inside either upgraded vendor tree after normalization.

---

## 5. Feature branch and static preparation

Create one scoped feature branch/worktree from the exact fresh staging baseline, using the repository workflow in `tasks/README.md`.

Before touching live staging runtime:

1. import the exact clean WPJBP `1.2.86` tree and exact clean Paid Listings `1.0.19` tree into the feature branch;
2. prove source tree contents correspond to the extracted official package trees;
3. record deterministic target tree fingerprints and file counts;
4. run `php -l` across all PHP files in both target trees;
5. inspect the complete source diff for scope, unexpected deletions, generated files, secrets, production-only paths, and non-target changes;
6. verify the three historically modified WPJBP files now equal the clean target hashes:
   - `includes/class-job-alert.php` → `c3eeebe04b2b224664de53d3c22ce7dc8a015d8a27cb4f5984fe65dba01fd431`
   - `includes/email-templates-default/html-job-alert-notice.php` → `e3d31eeedf312ee1a3beffc1402f1edf49347f46d8692a38344ce44ebb7bece4`
   - `templates/misc/my-jobs-alerts.php` → `1f251234d315e9eb7b25b54e57a37b48279223a565d1b8ee3c9049f1020155cc`
7. prove no Raspitajse/site-specific marker or patch remains in the target vendor trees except content that is genuinely upstream in the official packages.

Do not deploy until all static/provenance gates pass.

---

## 6. Mandatory rollback point before deploy

Before the staging runtime is changed, establish a complete reversible rollback point.

Requirements:

- current source/`origin/staging`/deploy marker still exactly baseline `77d3a1019e0248a2abacd607fd508ed6868da70b`;
- current WPJBP and Paid Listings source/runtime trees still match the accepted pre-upgrade state;
- create a restricted staging database backup immediately before the change using an existing safe staging backup/export primitive;
- backup storage must be access-restricted and must not be committed or exposed in reports;
- report only sanitized backup identity/checksum/size/timestamp, never DB credentials, SQL contents, PII, recipients, messages, queries, or payment data;
- if a trustworthy complete DB backup cannot be obtained, STOP before any upgrade with `BLOCKED_NO_RESTORABLE_STAGING_BACKUP`;
- Git baseline is the authoritative file/source rollback point for the old plugin trees.

If the upgrade produces any database/options/schema migration, the DB backup must remain available until the task reaches final PASS or a completed rollback. A file-only rollback is not sufficient after migration.

---

## 7. Scheduler lock and T0 protected snapshot

Immediately before live staging deploy/first new-plugin bootstrap:

- acquire the existing selective runner's shared/nonblocking lock using its existing lock primitive/path;
- prove no runner cycle is active;
- hold the lock through deployment and initial acceptance/reconciliation;
- do not alter the Hostinger schedule; any natural overlap must fail locked and execute zero owned hooks.

While the lock is held, take a guarded **read-only T0 snapshot** without invoking the zero-argument runner or any owned hook.

T0 must include sanitized fingerprints/counts for at least:

- active plugin versions/state;
- relevant WPJBP settings/options by key/presence/hash only, not content bodies;
- role/capability shape relevant to candidate/employer registration;
- relevant post types/status counts;
- candidates, employers, jobs, applications, alerts, packages, orders/refunds and canonical entitlement state;
- WPJBP/Paid Listings meta-state relevant to Commerce integration;
- owned Communications and Commerce callback contracts;
- three owned scheduler events/callbacks;
- continuation-event count;
- full/non-allowlisted cron fingerprints;
- Action Scheduler pending count/fingerprint and ID32733 status/attempts;
- owned claim state;
- protected business aggregate/component fingerprints;
- candidate auto-expiry footprint;
- configured job-alert subject/content/template presence/hash only;
- mail/network/payment guard counters.

No PII or raw business payload may appear in the report.

---

## 8. Controlled staging deployment

Deploy the prepared feature branch only through the approved staging deployment path:

`deployment/deploy-staging.sh changed <feature-branch>`

Rules:

- staging only;
- keep both plugins active; do not toggle activation;
- do not update Superio;
- do not mass-update plugins;
- do not run the WordPress plugin installer/upgrader;
- do not bypass deploy marker/manifest/source parity controls;
- do not manually copy vendor files around failed deployment guards.

After file deployment, perform exactly one fully guarded WordPress bootstrap sufficient to load the active plugin set and detect runtime/vendor migration behavior.

Instrumentation must fail closed for:

- WordPress HTTP transport;
- wp_mail/PHPMailer/SMTP;
- payment/refund paths.

Record any DB/options/schema write attributable to the vendor bootstrap. If an activation-only migration is required, STOP for a new explicit authorization; do not deactivate/reactivate the plugins.

Any unexpected user/order/application/message/listing mutation is an immediate rollback trigger.

---

## 9. Security acceptance — CVE-2024-12213

The old `1.2.73` source was confirmed vulnerable to unauthenticated privilege escalation in the registration path. The target must prove the remediation without attacking staging.

PASS requires both:

1. static target-source proof that the registration handler:
   - respects registration enablement;
   - validates the intended candidate/employer nonce/security path;
   - maps request intent to only the fixed candidate/employer roles;
   - does not pass arbitrary caller-selected privileged role input into user creation;
2. an isolated no-real-user/no-live-request test proving caller-selected privileged roles cannot reach user creation and only the two intended role paths can proceed after the required validation.

Do not create administrator users, mutate roles, or submit a malicious request to the actual staging registration endpoint.

Any failure here requires rollback.

---

## 10. Owned alert/security/communications acceptance

After the clean vendor tree is active, prove the existing Raspitajse-owned boundary still wins over vendor registrations.

Required:

- every intended alert add/remove AJAX/admin-AJAX route has exactly one owned mutation callback and zero vendor mutation callbacks;
- role/profile/read-capability/nonce/ownership/type/ID/allowlist/frequency/sanitization controls from the owned alert-security adapter remain effective;
- alert REST management remains disabled where intended;
- candidate→job owned evaluator/event/callback remains exact;
- vendor candidate→job sender registration count is zero;
- employer→candidate sender remains retired;
- employer→candidate creation surface/widget/new alert creation remains retired;
- candidate automatic age/time expiry remains disabled;
- owned job-listing expiry and employer pre-expiry notice callbacks/events/policies remain exact;
- all retired vendor daily/expiry senders/checkers remain absent;
- SenderPolicy channel mapping, caller-independent From/Reply-To, staging recipient safety/redirection, and HTML handling remain exact;
- no unexpected vendor heartbeat/update/telemetry callback is introduced into the accepted scheduler/communications boundary.

Use guarded/isolated fixtures. Real mail/SMTP must remain zero.

---

## 11. Scheduler acceptance

The selective scheduler architecture must remain unchanged by the plugin upgrade.

Prove:

- exactly the same three owned hooks exist in the same fixed order:
  1. `raspitajse_job_listing_expiry_evaluator`
  2. `raspitajse_employer_job_expiry_notice_evaluator`
  3. `raspitajse_candidate_job_alert_evaluator`;
- each has exactly one expected hourly/3600 zero-argument event and exact callback contract;
- continuation event count remains zero;
- legacy daily/shared vendor callbacks/events remain retired;
- no broad cron runner or Action Scheduler runner is introduced;
- non-allowlisted cron changes are zero unless individually proven to be a necessary, safe vendor migration artifact;
- Action Scheduler executions remain zero and ID32733 remains unchanged unless a previously documented invariant explicitly permits otherwise.

Do not manually execute the runner or any owned hook as part of acceptance.

---

## 12. Paid Listings / Commerce / package acceptance

The synchronized Paid Listings upgrade must not alter the accepted Raspitajse entitlement architecture.

Prove compatibility for:

- paid package listing and selection;
- standalone dashboard package-purchase transport;
- native Paid Listings package selection where retained;
- order processing/cancellation integration;
- entitlement creation and canonical owner/product/order/quota/duration snapshots;
- quota consumption;
- canonical 30-calendar-day package validity beginning at first successful `processing` or `completed` activation;
- `pending`/`on-hold` not starting the entitlement clock;
- immutable `valid_until = activated_at + 30 days`;
- package validity remaining separate from individual job listing duration;
- expired/exhausted/revoked/available outcomes;
- Raspitajse Commerce canonical employer lookup;
- checkout prefill and order company fields;
- HPOS CRUD behavior;
- processed/cancelled marker bridge required by vendor Paid Listings behavior;
- template loader compatibility.

No real payment/refund may occur. Use isolated fixtures/mocks and clean them completely if any staging fixture records are created. Persistent business fingerprints must return to T0 unless an explicitly accepted vendor migration requires otherwise.

Any entitlement/HPOS/package regression requires rollback.

---

## 13. Frontend/dashboard compatibility acceptance

The target vendor versions have stale published WordPress compatibility headers and the active Superio parent is `1.3.17`, so staging compatibility must be demonstrated rather than assumed.

At minimum verify, through actual staging-safe runtime/render/query checks and HTTP smoke only where the environment permits:

- candidate dashboard;
- employer dashboard;
- job-alert management;
- application views;
- package views;
- job search/filter/detail;
- employer profile;
- candidate profile;
- job submission/edit path;
- core application flow;
- relevant localized templates and labels;
- no missing class/method/function;
- no fatal error;
- no new PHP warning/deprecated API failure attributable to the upgrade.

Known Hostinger/LiteSpeed/hcdn environment-layer HTTP 403 must not be misclassified as a WordPress regression. If public HTTP is blocked before WordPress, use WP-native guarded render/query/template smoke as the primary compatibility evidence and report the environment limitation explicitly.

If the upgrade exposes a small Raspitajse-owned localization/override gap caused by removing old vendor patches, a narrow owned fix is allowed under Section 4. Do not re-patch vendor files.

---

## 14. Vendor normalization acceptance

PASS requires exact clean vendor ownership after upgrade:

- WPJBP source/runtime header and constant exactly `1.2.86`;
- Paid Listings source/runtime header and constant exactly `1.0.19`;
- source and runtime target trees match the clean official package trees byte-for-byte, excluding only explicitly documented non-file deployment metadata outside those trees;
- no Raspitajse-specific patch remains in either vendor tree;
- the three previously customized WPJBP files match the pinned clean target hashes;
- old hard-coded sender/debug/minute/test-mode/staging-URL/malformed-fallback/localization hacks are not reintroduced into vendor code;
- any required Raspitajse branding/localization/business behavior exists only in owned/configured/translation/legitimate override layers.

If exact clean vendor-tree parity cannot be achieved, rollback.

---

## 15. T1 reconciliation

Before releasing the scheduler lock, take a guarded read-only T1 snapshot equivalent to T0.

T0 → T1 must prove:

- expected plugin version/tree changes only;
- any vendor-required option/schema migration is individually documented and technically correct;
- no user/order/application/message/listing business mutation;
- protected business aggregate/component fingerprints unchanged except an explicitly accepted vendor-required migration component;
- owned callback/security/scheduler contracts unchanged;
- non-allowlisted cron stable except any individually justified migration row;
- Action Scheduler execution count remains zero;
- ID32733 state/attempts remain protected;
- no continuation event;
- mail/PHPMailer/SMTP/payment remain zero;
- every WP HTTP attempt is blocked/preempted and explained, with no unexpected runtime transport;
- source HEAD, deploy marker, runtime vendor trees, and deployment integrity state are consistent.

Only after T1 PASS may the runner lock be released.

A natural Hostinger fire is **not required** for 2.14 unless the task discovers a scheduler-contract uncertainty that cannot be proven read-only. If such uncertainty exists, stop at a human-observation boundary instead of manufacturing a manual fire.

---

## 16. Rollback triggers and order

Rollback immediately on any of the following:

- package provenance/hash/version mismatch;
- deployment/source/runtime tree mismatch;
- fatal/migration error;
- registration security-fix failure;
- unexpected vendor route or missing/duplicate owned route;
- candidate/vendor sender retirement failure;
- candidate auto-expiry reactivation;
- job-expiry policy regression;
- scheduler/cron/Action Scheduler drift outside allowed evidence;
- unexpected mail/SMTP/network/payment activity;
- protected business mutation;
- package/entitlement/HPOS/30-day-policy regression;
- unrecoverable dashboard/frontend compatibility failure;
- inability to establish a consistent T1.

Rollback procedure:

1. keep the selective runner lock held;
2. keep staging in the bounded maintenance/change window and prevent normal application bootstrap during restore as far as the approved staging tooling allows;
3. restore the previous WPJBP and Paid Listings source/runtime state from the accepted Git baseline;
4. if any DB/options/schema migration occurred or cannot be conclusively excluded, restore the exact pre-upgrade DB backup as part of the same rollback unit;
5. restore/verify deploy marker and manifest through the approved deployment path, never by bypassing guards;
6. run one guarded read-only verification proving old versions, source/runtime parity, callback/security/scheduler contracts, cron/AS state, package/HPOS policy fingerprints, and protected business fingerprints are back at the accepted pre-upgrade state;
7. release the runner lock only after rollback verification passes;
8. publish a PARTIAL/FAIL report with the exact blocker and STOP.

Never run old code against a partially migrated new database.

---

## 17. Integration and final branch state

Only after all source/static/runtime/T1 acceptance gates pass:

- fast-forward/integrate the scoped feature branch to `staging` according to `tasks/README.md`;
- ensure `origin/staging`, local staging HEAD, deploy marker, and live runtime are aligned to the accepted final SHA;
- source working tree must be clean;
- do not leave a feature branch checked out in the shared scheduled worktree;
- clean task-private package scratch and temporary harnesses;
- retain or remove the restricted DB backup according to whether a migration occurred and the repository's existing safe operational practice; never expose its contents.

No production deploy.

---

## 18. Acceptance result

Final PASS requires all of the following:

- exact baseline verified before work;
- exact official packages/hash provenance re-verified;
- complete pre-upgrade rollback point created;
- clean vendor source replacement completed on a scoped feature branch;
- WPJBP `1.2.86` and Paid Listings `1.0.19` deployed to staging only;
- both vendor trees exact/clean with no Raspitajse patch residue;
- CVE registration remediation proved without a live exploit;
- alert security/communications ownership preserved;
- candidate/employer sender and expiry retirement rules preserved;
- fixed three-hook selective scheduler contract preserved;
- SenderPolicy preserved;
- Paid Listings / Commerce / HPOS / 30-day entitlement behavior preserved;
- critical dashboard/search/profile/application/package rendering contracts pass on the actual staging runtime or are proved through WP-native guarded smoke where environment HTTP is externally blocked;
- T0→T1 protected business state reconciled;
- no broad cron or Action Scheduler execution;
- no manual owned-hook execution;
- no real mail/SMTP/payment;
- no unexpected WordPress-runtime external HTTP;
- scheduler mutations `0`;
- production touched `NO`;
- final source/runtime/deploy state clean and aligned.

If a critical acceptance gate fails, rollback rather than accepting a partial vendor upgrade.

---

## 19. Final report

The final report must include:

- result: PASS / PARTIAL / FAIL;
- final classification, preferably `CONTROLLED_WPJBP_STAGING_UPGRADE_ACCEPTED` on PASS;
- original baseline and final staging SHA;
- package source/version/hash provenance;
- pre-upgrade backup status without exposing private data;
- feature/source diff scope and vendor tree fingerprints;
- version/source/runtime/active-state proof;
- CVE remediation proof;
- alert/security/communications acceptance;
- scheduler/cron/AS acceptance;
- Paid Listings/Commerce/HPOS/entitlement acceptance;
- frontend/dashboard compatibility evidence and any environment-layer limitation;
- T0/T1 protected-state reconciliation;
- all safety counters;
- any bounded owned compatibility change made outside vendor code;
- rollback status if invoked;
- production touched NO;
- exactly one proposed next task, not created or started.

If PASS, the proposed next task should move back to the remaining Raspitajse custom Woo/legacy cleanup rather than extending the WPJBP upgrade arc unless a concrete post-upgrade normalization blocker remains.

STOP after publishing the report.