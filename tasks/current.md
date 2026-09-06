# Zadatak 2.15 — Authorize deterministic Git EOL preservation for pinned vendor packages and resume the controlled WPJBP staging upgrade

Status: READY
Baseline: 77d3a1019e0248a2abacd607fd508ed6868da70b
Previous task: 2.14
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, creating a worktree, downloading artifacts, changing `.gitattributes`, staging files, bootstrapping WordPress, or touching runtime. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.13 PASS report and the final Zadatak 2.14 PARTIAL report in full.

Verify fresh `origin/staging` is exactly:

`77d3a1019e0248a2abacd607fd508ed6868da70b`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean. If any baseline differs, STOP and report the mismatch. Do not silently rebase or widen scope.

Execute only Zadatak 2.15. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.16 automatically.

---

## 1. Accepted context

Zadatak 2.13 PASS concluded:

`READY_FOR_CONTROLLED_STAGING_UPGRADE`

The accepted exact upgrade targets are:

- WP Job Board Pro `1.2.86` from the official unauthenticated ApusThemes package;
- WP Job Board Pro WC Paid Listings `1.0.19` from the official unauthenticated ApusThemes package.

Pinned package SHA-256 values:

- WPJBP `1.2.86`: `692fbf75f36391524e76db51805c4c79200880436bc9446685b4e9cce19a3f63`;
- Paid Listings `1.0.19`: `26aebee83ec333302f201fa1782346414064004af4cdb8e24732555d5f640467`.

The current staging versions remain:

- WPJBP `1.2.73`;
- Paid Listings `1.0.16`.

The current WPJBP version is affected by `CVE-2024-12213`; the exact clean `1.2.86` target is beyond every relevant published fixed/affected threshold verified in 2.13 and statically contains the corrected registration role/nonce logic.

Zadatak 2.14 correctly stopped before any runtime mutation with:

`BLOCKED_EXACT_VENDOR_BYTE_PARITY_BY_REPOSITORY_EOL_POLICY`

2.14 proved before stopping:

- both official package hashes still matched;
- archive safety/root/version/constant validation passed;
- prepared WPJBP and Paid Listings trees were byte-for-byte identical to the official extracted packages before Git staging;
- all 1,185 PHP files linted successfully;
- all changed vendor paths were inside the two authorized plugin roots;
- Raspitajse/site-specific markers in the clean target vendor trees were zero;
- staging/source/runtime/business state remained unchanged;
- no commit, push, DB backup/write, WordPress bootstrap, deploy, scheduler mutation, mail, payment, or production action occurred.

The sole blocker was repository EOL policy. Root `.gitattributes` currently forces LF normalization globally and for common text extensions. The official target packages contain 28 text files whose raw official bytes include CRLF and would be changed by the current Git clean filter.

This task explicitly authorizes the narrow repository policy exception required to preserve the two pinned vendor package trees byte-for-byte and then resumes the controlled staging upgrade from the beginning.

---

## 2. Goal

Complete two tightly coupled objectives:

1. establish a deterministic, path-scoped Git EOL policy that preserves the exact official bytes for only the two pinned vendor plugin trees through `git add -> commit -> fresh checkout -> approved deploy`;
2. after that gate is proven, resume and complete the controlled staging upgrade to WPJBP `1.2.86` and Paid Listings `1.0.19` with the full security, compatibility, protected-state, and rollback acceptance defined by 2.13/2.14.

The EOL exception is not a general repository policy change and must not weaken LF normalization for Raspitajse-owned code or any other vendor/theme/plugin.

---

## 3. Newly authorized `.gitattributes` scope

The only newly authorized non-vendor source change is the root repository file:

`.gitattributes`

Add exactly two path-scoped rules **after the existing generic text/binary rules**:

```gitattributes
wp-content/plugins/wp-job-board-pro/** -text !eol
wp-content/plugins/wp-job-board-pro-wc-paid-listings/** -text !eol
```

Purpose:

- `-text` explicitly disables Git EOL conversion for those two vendor trees;
- `!eol` removes the inherited repository `eol=lf` attribute for those paths;
- all other repository paths retain the existing LF policy unchanged.

Do not:

- change or remove any existing global `.gitattributes` rule;
- add exceptions for any other directory;
- use a global `core.autocrlf`, `core.eol`, or repository-local config workaround as the persistent solution;
- use `.git/info/attributes` as the committed solution;
- add a nested `.gitattributes` inside either vendor plugin tree, because that would contaminate exact vendor-tree parity;
- use `binary`/`-diff` merely to suppress review visibility;
- use `git add --renormalize` on the vendor trees;
- manually copy bytes around the approved Git/deploy path.

If the exact two rules above do not preserve official bytes under the host Git version, STOP with `BLOCKED_EOL_PRESERVATION_FAILED`. Do not broaden the policy by improvisation.

---

## 4. Canonical vendor-byte parity contract

For this task, `OFFICIAL_CLEAN` means the committed and deployed vendor trees are byte-for-byte equivalent to the exact pinned official package trees for every file path and file content, with no Raspitajse-added file inside either vendor root.

Metadata such as ZIP timestamps is not part of the parity contract; repository/runtime file path set and raw file bytes are.

The parity contract must be proven at all of these boundaries:

1. official ZIP -> task-private extracted tree;
2. extracted tree -> feature working tree before Git staging;
3. Git index after staging;
4. committed feature tree materialized into a fresh clean checkout/worktree;
5. deployed staging runtime tree;
6. final staging source tree after integration.

Use the same deterministic path+content tree-fingerprint method at each boundary. Also verify file counts/path sets.

For the 28 EOL-sensitive files identified by the fresh package inspection, prove individually that raw file hashes survive Git staging and fresh checkout. Include the previously demonstrated representative path `wp-content/plugins/wp-job-board-pro/assets/admin/functions.js` in the proof, but validate all affected paths rather than only the example.

Use `git check-attr text eol -- <path>` or equivalent to prove the vendor paths resolve to the intended no-conversion state after the new root rules. Verify a representative Raspitajse-owned PHP/JS file still resolves to the existing LF-normalized policy.

If Git index or fresh-checkout bytes differ from the official package at any vendor path, STOP before DB backup/runtime mutation.

---

## 5. Hard scope boundary

Authorized source changes are only:

1. root `.gitattributes` with the exact two rules above;
2. complete replacement of `wp-content/plugins/wp-job-board-pro/` by the pinned official `1.2.86` tree;
3. complete replacement of `wp-content/plugins/wp-job-board-pro-wc-paid-listings/` by the pinned official `1.0.19` tree;
4. a **minimal Raspitajse-owned compatibility fix only if post-upgrade acceptance proves one is strictly required** and the business requirement cannot be preserved without it.

A compatibility fix, if required, must live in an existing Raspitajse-owned layer and must never patch either new vendor tree. Before making such a fix, report the exact failing owned contract in task notes and keep the change minimal.

Do not modify or upgrade:

- Superio or Superio Child;
- WooCommerce;
- WordPress core;
- Elementor or unrelated plugins;
- Hostinger scheduler configuration;
- production files/database/runtime/scheduler;
- vendor packages other than the two exact targets.

Do not transplant historical Raspitajse/vendor hunks into the clean target trees. The three historically customized WPJBP files must remain the exact clean `1.2.86` vendor files.

---

## 6. Fresh official artifact gate

Re-fetch only the already authorized public ApusThemes metadata/package endpoints for these two products.

Before changing source, require again:

- vendor metadata still reports WPJBP `1.2.86` and Paid Listings `1.0.19`;
- WPJBP ZIP SHA-256 exactly `692fbf75f36391524e76db51805c4c79200880436bc9446685b4e9cce19a3f63`;
- Paid Listings ZIP SHA-256 exactly `26aebee83ec333302f201fa1782346414064004af4cdb8e24732555d5f640467`;
- archive CRC PASS;
- exactly one expected plugin root per package;
- zero absolute/drive/traversal paths;
- zero duplicate paths;
- zero symlinks;
- exact header and version constant values;
- no Raspitajse/site-specific marker in the clean package trees.

If a same-version vendor artifact has changed bytes, STOP for provenance reconciliation. Do not accept a new hash automatically.

Do not use WordPress update APIs, plugin installers/upgraders, cached update transients, ThemeForest bundled ZIPs, historical local ZIPs, reseller mirrors, authenticated downloads, or license/account credentials.

Task-private downloaded/extracted artifacts must be protected and removed after they are no longer required for final parity/rollback evidence.

---

## 7. Feature preparation and pre-runtime acceptance

Create one scoped feature branch/worktree from exact fresh `origin/staging`. Keep the primary scheduled worktree on `staging` until bounded integration.

Sequence:

1. add only the two authorized `.gitattributes` rules;
2. mechanically replace both vendor roots from the exact official extracted packages;
3. stage `.gitattributes` first so the intended attributes are authoritative, then stage the two vendor trees;
4. prove index byte parity before commit;
5. review the entire staged diff and scope;
6. commit the feature only after all pre-runtime gates pass;
7. materialize a fresh clean checkout/worktree from the feature commit and re-prove exact vendor byte parity there.

Mandatory static checks:

- shell/PHP syntax for any owned tooling touched;
- `php -l` for every PHP file in both target vendor trees, using bounded local parallelism if useful;
- no failed PHP lint;
- target plugin headers/constants exactly `1.2.86` / `1.0.19`;
- exact official file counts/path sets/tree fingerprints after Git round-trip;
- the three historically modified WPJBP files exactly match the clean target hashes established in 2.13;
- zero Raspitajse marker inside both new vendor roots;
- no secrets/private keys/production paths introduced;
- staged path set contains only `.gitattributes`, the two exact vendor roots, and any separately justified minimal owned compatibility fix;
- any `git diff --check` warning caused solely by unchanged official vendor whitespace is recorded as vendor provenance evidence, not “fixed”; any warning outside the two clean vendor roots is a FAIL.

Do not edit official package whitespace or line endings to make lint/diff tools quieter.

If a deterministic host/tooling limitation appears, use the already proven shell/PHP/unzip/zipinfo/hash primitives. Do not install Python merely because `python3` is absent. Do not repeatedly retry a known namespace-pressure mechanism; use the documented namespace-free path once and fail closed if it cannot proceed.

---

## 8. Pre-upgrade staging safety gates

Only after the committed Git round-trip parity gate passes may the task approach staging runtime.

Before deploy:

1. verify fresh `origin/staging` is still the required baseline and has not advanced;
2. verify the primary staging source worktree remains clean and on `staging`;
3. verify staging deploy marker/runtime are still the accepted pre-upgrade state;
4. verify mail fail-closed safety, WP HTTP pre-transport blocking, SMTP/payment/refund guards, and production boundary;
5. acquire the existing selective-runner shared lock and prove no runner is active; hold that lock through deploy, guarded bootstrap, T1 acceptance, and final consistent integration/rollback decision;
6. do not edit, disable, recreate, or manually trigger the Hostinger scheduler; a natural overlap while the lock is held must fail locked and execute no owned hook;
7. capture sanitized T0 deep/protected state using existing read-only diagnostic primitives, including:
   - active plugin versions/state;
   - WPJBP/Paid Listings relevant option/settings fingerprints;
   - roles/capability shape relevant to employer/candidate registration;
   - relevant WPJBP post/status counts;
   - candidate/job/employer/application/alert/package/order/refund protected fingerprints/counts;
   - package entitlement/30-day-policy state;
   - cron/full and non-allowlisted cron state;
   - three owned scheduler callback/event contracts;
   - Action Scheduler pending count/fingerprint and ID32733 state;
   - Communications/Commerce callback and security contracts;
   - zero owned claims/continuation where expected;
8. take a **restorable, consistent staging database backup immediately before runtime change** using an existing trusted local backup primitive. Do not print DB credentials or data. Record only a sanitized backup identifier/path class, checksum, timestamp, permissions, and integrity check.

If a consistent/restorable DB backup cannot be established, STOP before deploy with `BLOCKED_NO_RESTORABLE_STAGING_DB_BACKUP`.

The backup must remain available through the final acceptance/rollback decision. If T0->T1 proves no DB/schema/options migration occurred, it may be securely removed after final PASS. If a vendor-required DB migration is accepted, retain the protected backup and report a sanitized identifier for explicit later cleanup.

---

## 9. Controlled staging deploy

Deploy only through the approved Raspitajse staging deployment path, using the feature branch and existing deployment guards. Do not manually copy plugin files to runtime and do not use the WordPress plugin updater.

The `.gitattributes` file is repository policy metadata; it does not need to be copied into WordPress runtime merely to preserve vendor bytes. Do not weaken deploy allowlists to publish unrelated root files.

Keep both plugins active. Do not deactivate/reactivate them merely to trigger migration.

After files are deployed but before normal application use:

- verify runtime vendor path set/file counts/tree fingerprints exactly equal the official package trees and fresh-checkout feature source;
- verify runtime versions are exactly WPJBP `1.2.86` and Paid Listings `1.0.19`;
- verify no extra Raspitajse file/patch exists inside either vendor root;
- run one fully guarded WordPress bootstrap with mail/network/payment protection and record any bounded vendor-required option/schema migration;
- if an activation-only migration is required, STOP before activation and report the explicit authorization need rather than toggling plugins blindly;
- if an unexpected business/user/order/application/message/listing mutation occurs, rollback immediately.

No broad WP-Cron, Action Scheduler queue runner, manual owned-hook execution, continuation runner, or plugin update/heartbeat API is authorized.

---

## 10. Mandatory post-upgrade security and compatibility acceptance

All material gates below must pass before final integration.

### A. Vendor/security state

- WPJBP header/constant/runtime active version = `1.2.86`.
- Paid Listings header/constant/runtime active version = `1.0.19`.
- Source, clean-checkout, runtime and official-package vendor tree fingerprints/path sets are exact.
- The three historical WPJBP custom files are exact clean vendor files; no site patch is reapplied.
- Static source plus an isolated **no-real-user/no-live-registration** test proves the `process_register` privilege-escalation path cannot accept an arbitrary privileged caller-selected role and requires the intended candidate/employer role/nonce flow.
- No exploit request, real staging user creation, role escalation, or production test is allowed.

### B. Alert/security boundary

- Every Raspitajse-owned alert-management route remains authoritative with exact role/profile/capability/nonce/ownership/type/ID checks.
- Vendor/anonymous mutation callbacks that should be replaced remain absent; no newly introduced equivalent bypass route exists.
- Alert REST management remains disabled where intended.
- Owned candidate->job evaluator/event/callback remains exact; vendor candidate-job sender registration remains zero.
- Employer->candidate sender and new candidate-alert creation surfaces remain retired.
- Candidate automatic time expiry remains disabled.
- Owned job-listing expiry and employer pre-expiry notification callbacks/events/policies remain exact; retired vendor expiry notice/checker callbacks remain absent.
- SenderPolicy/transport channel mappings, caller-independent From/Reply-To behavior, staging redirection, and HTML behavior remain exact.

### C. Scheduler boundary

- The selective runner still exposes exactly the accepted three owned hooks in the fixed order.
- Each owned event remains exactly one hourly/3600 zero-argument event with the exact callback/priority/accepted-args contract.
- No continuation event, vendor daily sender event, broad cron surface, second scheduler, or Action Scheduler substitute is introduced.
- Do not manually execute the runner or owned hooks for acceptance.

### D. Paid Listings / Commerce / package policy

Re-prove compatibility of:

- package listing and selection;
- standalone package purchase transport;
- order processing/cancellation marker bridge;
- entitlement creation/activation/revocation;
- quota consumption;
- canonical immutable 30-calendar-day entitlement validity;
- separation of package validity from individual job listing duration;
- availability/exhausted/expired/revoked outcomes;
- template loader integration;
- canonical employer lookup;
- checkout prefill/company fields;
- HPOS-safe order CRUD and processed/cancelled bridge.

Use existing isolated fixtures/harnesses. No real payment, refund, SMTP, or external HTTP.

### E. WPJBP/Superio compatibility

The target must remain compatible with the currently active Superio `1.3.17` / child theme boundary for the contracts Raspitajse presently uses.

At minimum verify without business mutation:

- candidate/employer dashboards load through relevant PHP/template contracts without fatal/missing class/method/deprecated API failure;
- job-alert management views/contracts remain renderable;
- application views remain compatible;
- package views remain compatible;
- job search/filter/detail contracts work;
- employer/candidate profile contracts work;
- job submission/edit contracts work;
- application core contracts work;
- configured/owned localized content still wins where intended.

If external HTTP/browser access is blocked by the already-known Hostinger/LiteSpeed/hcdn environment-layer 403 before WordPress, do not classify that unchanged infrastructure block as a plugin regression. Use guarded internal runtime/template/API evidence and explicitly report the HTTP limitation. A new WordPress-level fatal/regression is a FAIL.

Do not upgrade Superio in this task.

---

## 11. T1 reconciliation and protected-state acceptance

Capture T1 with the same sanitized deep/protected diagnostic basis as T0.

PASS requires:

- no fatal/migration error or unexpected PHP warning attributable to the upgrade;
- active source/runtime versions and exact clean vendor parity as above;
- protected business fingerprints unchanged except a specifically identified vendor-required technical migration authorized by this task;
- no candidate/job/employer/application/message/order/refund/listing business mutation;
- no real mail/PHPMailer/SMTP/payment/refund effect;
- every WordPress HTTP attempt preempted/attributed by the staging guard; no unexpected runtime external request;
- non-allowlisted cron unchanged except a precisely explained vendor-required non-executing registration change, if any;
- three owned scheduler contracts unchanged;
- no broad cron, continuation or Action Scheduler execution;
- Action Scheduler protected state, including ID32733, remains acceptable and attempts remain unchanged unless a vendor-required non-executing metadata change is explicitly proven;
- package/HPOS/30-day policy fingerprints/fixtures pass;
- alert/security/communications cutovers remain authoritative.

Any unexplained protected-state mutation is a rollback trigger.

---

## 12. Integration and final staging consistency

Only after all post-upgrade acceptance passes:

1. fast-forward/integrate the accepted feature commit(s) to `staging` according to `tasks/README.md`;
2. push/update `origin/staging` only through the accepted project workflow;
3. ensure the primary staging worktree returns to clean `staging` at the accepted final commit;
4. use the approved deployment path to make deploy marker/manifest/runtime/source consistent with that final staging commit;
5. re-prove source/runtime/official vendor tree parity after final integration;
6. re-prove the runner/deploy manifest boundary still passes its lightweight/deep read-only contract as applicable without manually firing business hooks;
7. release the shared runner lock only after the final consistent state passes.

Do not change the Hostinger `*/15 * * * *` scheduler entry.

A natural provider fire after lock release is not required for PASS unless the actual upgrade changes the accepted scheduler contract. If scheduler contract drift is observed, STOP/rollback rather than inventing a new scheduler acceptance path.

---

## 13. Mandatory rollback triggers

Rollback immediately after any runtime mutation if any of these occur:

- vendor package/version/tree/hash mismatch;
- EOL byte-parity loss after commit/checkout/deploy;
- fatal or migration error;
- unexpected schema/options/business mutation;
- registration security fix failure;
- new or surviving unauthorized vendor alert mutation route;
- owned callback missing/duplicated or priority/args contract drift;
- candidate->job vendor sender reappears;
- employer->candidate or candidate auto-expiry retirement regresses;
- job-expiry ownership regresses;
- package/entitlement/HPOS/30-day policy regression;
- cron/Action Scheduler/continuation/broad-runner drift;
- unexpected runtime network/mail/SMTP/payment/refund effect;
- protected business mutation;
- unrecoverable WPJBP/Superio compatibility failure.

Rollback order:

1. keep the shared runner lock held;
2. prevent normal staging application use during restore using only an existing approved staging maintenance/deploy boundary;
3. restore the previous WPJBP/Paid Listings source/runtime trees and baseline repository state through the approved Git/deploy path;
4. if any DB/options/schema/data migration/write occurred or cannot be disproven, restore the exact consistent pre-upgrade DB backup before booting old plugin code; file-only rollback is allowed only when guarded evidence proves no DB migration/write occurred;
5. restore deploy marker/manifest to the matching accepted baseline commit through approved deployment tooling;
6. run one guarded read-only verification and re-prove baseline active versions, hashes, alert/security graph, scheduler contracts, non-allowlisted cron, Action Scheduler/ID32733, mail/network/payment zeros, package/HPOS/30-day-policy state, and protected business fingerprints;
7. release the runner lock only after rollback consistency passes.

Report rollback as part of 2.15; do not leave a mixed-version or mixed-schema state.

---

## 14. Acceptance criteria

Final PASS requires all of the following:

- exact baseline verified before work;
- exact two-line `.gitattributes` exception only, with no global EOL-policy weakening;
- official package hashes/versions/provenance revalidated fresh;
- Git index and fresh-checkout round-trip prove exact official vendor bytes for all files, including all EOL-sensitive paths;
- official WPJBP `1.2.86` and Paid Listings `1.0.19` trees committed with no site patches;
- static/lint/scope acceptance passes;
- consistent restorable staging DB backup established before runtime mutation;
- shared runner lock held across deploy/acceptance/integration decision;
- approved deployment path only;
- guarded bootstrap succeeds;
- source/runtime/official vendor parity exact;
- CVE registration fix proven safely without real-user/exploit mutation;
- alert/security/communications cutovers pass;
- three-hook scheduler contract passes with no broad execution;
- Paid Listings/Commerce/HPOS/package/30-day entitlement compatibility passes;
- WPJBP/Superio 1.3.17 compatibility checks pass at the contract/runtime level;
- T0->T1 protected state reconciles with no unexplained mutation;
- mail/SMTP/payment/refund/unexpected external runtime side effects zero;
- final `staging`, `origin/staging`, source worktree, runtime deploy marker/manifest and accepted commit consistent;
- scheduler configuration mutations `0`;
- production touched `NO`.

If any mandatory gate cannot be proven, result must be PARTIAL/BLOCKED or rollback-complete FAIL as appropriate. Do not declare PASS based only on plugin version numbers.

---

## 15. Final report requirements

Report at minimum:

- result and final classification;
- original/final staging SHA and deploy marker;
- exact `.gitattributes` diff and proof that non-vendor LF policy remains unchanged;
- fresh official metadata/package versions and SHA-256 values;
- ZIP safety results;
- EOL-sensitive file count and exact Git index/fresh-checkout/runtime parity result;
- official/source/runtime tree file counts and fingerprints for both plugins;
- final active plugin versions;
- PHP lint/static acceptance totals;
- DB backup status and sanitized identifier/retention decision;
- shared runner lock/T0/T1 evidence;
- any bounded vendor migration observed;
- CVE fix acceptance;
- alert/security/communications acceptance;
- scheduler contract acceptance;
- Paid Listings/Commerce/HPOS/30-day policy acceptance;
- Superio 1.3.17 compatibility evidence and any unchanged host-layer HTTP limitation;
- protected-state reconciliation;
- HTTP/mail/SMTP/payment/refund/cron/AS execution counters;
- rollback invoked YES/NO and, if yes, full restoration evidence;
- final source/runtime/deploy cleanliness and production touched NO;
- exactly one proposed next task, not created or started.

Next-task rule:

- if 2.15 PASS: propose **Zadatak 2.16 — Superio 1.3.17 -> 1.3.37 security/upgrade readiness audit, including bundled WPJBP/Paid Listings provenance and child-theme compatibility**;
- if 2.15 stops at a human-only UI observation boundary after all technical gates pass: propose one bounded completion/reconciliation task for that boundary instead;
- if 2.15 is blocked or rolled back: propose only the narrow prerequisite needed to resolve the blocker.

STOP after publishing the report. Do not begin the proposed next task automatically.
