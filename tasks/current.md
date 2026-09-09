# Zadatak 2.21 — Controlled Superio 1.3.37 + Apus Framework 2.5 + Slider Revolution 6.7.41 staging upgrade with pinned provenance, downgrade prevention, secure backup, rollback, and full compatibility acceptance

Status: READY
Baseline: 9df2850034117cacdc8dd45078da5c3d1d3c034c
Previous task: 2.20
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, extracting vendor artifacts, creating branches, changing source, taking backups, bootstrapping WordPress, or deploying. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.20 PASS report and final Zadatak 2.18 PASS report in full. Read Zadatak 2.16 PASS only where needed for the accepted secure staging DB-backup primitive and rollback/safety boundary.

Verify fresh `origin/staging` is exactly:

`9df2850034117cacdc8dd45078da5c3d1d3c034c`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean/on `staging`. If any baseline differs, STOP and report the mismatch. Do not silently rebase, rebuild against another SHA, or widen scope.

Execute only Zadatak 2.21. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.22 automatically.

---

## 1. Accepted authoritative facts

Treat these as accepted unless a fresh bounded verification contradicts them.

### Current accepted staging state

- application/source/deploy baseline: `9df2850034117cacdc8dd45078da5c3d1d3c034c`;
- active template / stylesheet: `superio` / `superio-child`;
- Superio parent: `1.3.17`;
- Superio child: `1.0.0`;
- Apus Framework: `2.3`;
- Slider Revolution: `6.7.18`;
- WP Job Board Pro: `1.2.86`;
- WP Job Board Pro WC Paid Listings: `1.0.19`;
- WP Private Message: `1.0.7`;
- WooCommerce: `9.5.4`;
- Elementor: `3.25.11`;
- WordPress: `6.6.7`.

Zadatak 2.20 PASS established that the child no longer depends on the parent `js/phone-field.js` path:

- owned child asset: `wp-content/themes/superio-child/assets/js/phone-field.js`;
- canonical SHA-256: `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- enqueue handle remains `phone-field-js`;
- source resolves through `get_stylesheet_directory_uri()`;
- deterministic version: `a9a53425350b`;
- no active parent `/superio/js/phone-field.js` enqueue remains.

### Pinned official Superio target

User-provided artifact:

`/home/u601262303/repo/themeforest-NNoRVYjo-superio-job-board-wordpress-theme-wordpress-theme.zip`

Accepted identity:

- artifact SHA-256: `0d172d4151faddef101a2ff9a6b1c1a8a1c41653018283afbdd0dd6d038cf5b6`;
- target Superio version: `1.3.37`;
- target parent: 615 regular files;
- target parent PHP lint previously passed 367/367;
- official package-byte tree SHA-256: `019ba653e5c27dbdc0c98f2651ee41daeb32014ce6275c44b278f63904a5619f`;
- accepted Git/LF-canonical parent tree SHA-256: `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e`;
- current repository EOL policy is retained; **do not add a Superio `-text !eol` exception**.

Pinned target bundled plugin archives:

- Apus Framework `2.5` — `inc/plugins/apus-framework.zip`
  - SHA-256 `a0506a74d80c6e8af553d9bead31f528208eed0485755d8890e36f64aa7792f4`;
- Slider Revolution `6.7.41` — `inc/plugins/revslider.zip`
  - SHA-256 `f0ff13e70cac3c0ffcecfbea1a78be483ed0d00de9db90ee60e0252b23a5b175`;
- WP Job Board Pro `1.2.86`
  - SHA-256 `692fbf75f36391524e76db51805c4c79200880436bc9446685b4e9cce19a3f63`;
- Paid Listings `1.0.19`
  - SHA-256 `26aebee83ec333302f201fa1782346414064004af4cdb8e24732555d5f640467`;
- WP Private Message `1.0.7`
  - SHA-256 `fcb1a3f39f447f63fe420be6d89297634ffd8052b4af332c88a9720734b1a5bc`.

Accepted active clean plugin tree fingerprints that must remain unchanged:

- WPJBP `1.2.86`: `23c55f5bba87fc2fc58dbb858c3543a0b45d4e1f5b0fa90d74e569eff034668e`;
- Paid Listings `1.0.19`: `d936377ea8d818ede21cd384568ad93c4092f37e09e83df2be2ed92683a9a6a3`.

Zadatak 2.18 established that target TGMPA does not automatically downgrade installed WPJBP/Paid Listings and ordinary theme bootstrap does not run plugin replacement. Nevertheless **no TGMPA install/update/bulk action is authorized**.

---

## 2. Goal and upgrade transaction

Perform one controlled staging security/compatibility upgrade transaction:

1. normalize the Superio parent source to the exact pinned `1.3.37` target;
2. replace the active Apus Framework whole tree with the exact pinned `2.5` bundle;
3. replace the active Slider Revolution whole tree with the exact pinned `6.7.41` bundle;
4. keep the active Superio child unchanged;
5. keep WPJBP `1.2.86`, Paid Listings `1.0.19`, and WP Private Message `1.0.7` active directories untouched;
6. explicitly prevent TGMPA/updater reinstall/downgrade paths;
7. prove the Raspitajse security, package, communications, dashboard, registration, submission, application, WooCommerce, and scheduler contracts still work;
8. rollback completely if any critical acceptance fails.

This is a vendor infrastructure upgrade, not a Raspitajse business-logic refactor.

---

## 3. Hard safety boundaries

Production is forbidden. Do not access or mutate production filesystem, database, scheduler, mail, payment, or network state.

Do not change or upgrade:

- `wp-content/themes/superio-child/**`;
- WP Job Board Pro active plugin tree;
- Paid Listings active plugin tree;
- WP Private Message active plugin tree;
- WooCommerce;
- WordPress core;
- Elementor;
- Raspitajse Communications business logic;
- Raspitajse Commerce business logic;
- Hostinger scheduler configuration.

Do not use:

- WordPress theme/plugin updater or installer;
- theme switch;
- plugin deactivate/reactivate cycle;
- TGMPA single/bulk install/update actions;
- broad WP-Cron;
- `wp cron event run --due-now` / `--all`;
- Action Scheduler runner or arbitrary due-action execution;
- continuation runner;
- real SMTP/mail transport;
- real payment/refund execution;
- external/vendor HTTP merely to prove behavior;
- live exploit attempts or live privileged registrations.

Keep existing mail, external WordPress HTTP, payment/refund, and scheduler protections active during runtime acceptance.

Known `HOST_NAMESPACE_PRESSURE` / `bwrap ENOSPC` guidance remains binding: once the known signature is confirmed, do not loop on sandbox-dependent helpers; use the already proven namespace-free/direct Git/filesystem workflow or STOP precisely.

---

## 4. Exact authorized source scope

This task authorizes changes only inside:

1. `deployment/deploy-staging.sh` — exact narrow staging deploy-scope extension only;
2. `wp-content/themes/superio/**` — whole clean target parent replacement;
3. `wp-content/plugins/apus-framework/**` — whole clean target bundle replacement;
4. `wp-content/plugins/revslider/**` — whole clean target bundle replacement.

No other application path may change.

Do **not** modify `.gitattributes` or `.gitignore`.

Because global `*.zip` ignore rules exist, this task explicitly authorizes `git add -f` **only** for the ten official ZIP files that belong inside the pinned Superio 1.3.37 parent tree:

- `wp-content/themes/superio/inc/plugins/apus-framework.zip`;
- `wp-content/themes/superio/inc/plugins/revslider.zip`;
- `wp-content/themes/superio/inc/plugins/wp-job-board-pro.zip`;
- `wp-content/themes/superio/inc/plugins/wp-job-board-pro-wc-paid-listings.zip`;
- `wp-content/themes/superio/inc/plugins/wp-private-message.zip`;
- the exact five official demo slider ZIPs under `wp-content/themes/superio/inc/vendors/one-click-demo-import/default/` proven by 2.18 (`slider-1.zip`, `slider-5.zip`, `slider-6.zip`, `slider-11.zip`, `slider-20.zip`).

No other ignored archive may be force-added.

---

## 5. Two-commit feature structure and explicit publication authorization

Use one scoped feature branch:

`feature/z2-21-superio-upgrade`

Create it from exact baseline `9df2850034117cacdc8dd45078da5c3d1d3c034c`.

The feature must contain exactly two ordered commits:

### Commit A — deploy-scope control commit

Change only `deployment/deploy-staging.sh`.

Add exactly these roots to both the `ALLOWLIST` and `is_allowed_path()` contract:

- `wp-content/themes/superio`;
- `wp-content/plugins/apus-framework`;
- `wp-content/plugins/revslider`.

Do not change any other deploy behavior, target path, production scope, branch policy, state-marker behavior, removed-plugin rules, rsync semantics, manifest behavior, or existing allowlist entry.

### Commit B — pinned vendor replacement

Parent must be Commit A.

Change only the three vendor roots:

- `wp-content/themes/superio/**`;
- `wp-content/plugins/apus-framework/**`;
- `wp-content/plugins/revslider/**`.

No deployment script change in Commit B.

**This READY task is explicit human authorization to publish the exact scoped feature branch to `origin/feature/z2-21-superio-upgrade` after all static gates below pass. No additional publication approval is required if the branch parent/commit structure, exact changed-path scope, and pinned hashes all match this contract.**

Push once normally. No force push, amend-after-publication, rebase, or history rewrite.

After publication and before any vendor runtime deploy, this task also explicitly authorizes a fast-forward of `origin/staging` from the original baseline to **Commit A only**, followed by `deployment/deploy-staging.sh changed staging`. That control step must change no WordPress runtime file because the only baseline→A source diff is the deploy script outside the runtime allowlisted WordPress paths; it exists solely to make the rollback/deploy mechanism authoritative for the three newly allowed roots.

If the baseline→A staging fast-forward or control deploy does anything else, STOP.

---

## 6. Re-verify and prepare the pinned vendor source before publication

Do all artifact extraction in a mode-0700 task-private non-web directory. Never edit the user-provided outer artifact.

Reverify:

- outer artifact exists as regular non-symlink file;
- SHA-256 is exactly `0d172d4151faddef101a2ff9a6b1c1a8a1c41653018283afbdd0dd6d038cf5b6`;
- ZIP CRC/path/traversal/duplicate/symlink/encryption safety still passes;
- target `style.css` and `SUPERIO_THEME_VERSION` are exactly `1.3.37`;
- exact embedded Apus and RevSlider archive hashes equal the pinned values above;
- embedded WPJBP/Paid archives still equal the already accepted pinned clean packages;
- no Raspitajse/staging/host contamination marker exists in extracted target vendor trees.

Do not fetch or substitute a newer Superio/plugin package in this task. If the supplied artifact differs, STOP.

### Parent canonicalization

Build the target parent from the supplied official ZIP, then prove:

- official package-byte tree remains `019ba653e5c27dbdc0c98f2651ee41daeb32014ce6275c44b278f63904a5619f`;
- after the repository's existing Git EOL round trip, the 615-file source tree is exactly the accepted canonical tree `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e`;
- all ZIP/binary payload bytes remain exact;
- any text changes caused by Git are EOL-only;
- the six package assets previously identified with accidental executable mode are normalized to regular non-executable Git mode; no content byte is changed for that normalization.

Do not preserve the removed parent `js/phone-field.js`; 2.20 already made the child independent.

### Apus / Slider canonicalization

For each pinned embedded archive independently:

1. safely extract its single plugin root;
2. compute and report a deterministic official-extraction path+byte tree SHA-256 using the same sorted `relative_path + NUL + sha256(bytes) + LF` algorithm accepted in 2.18;
3. pass the extracted tree through the repository's current Git attributes in a disposable index/checkout;
4. compute and report a deterministic Git-canonical tree SHA-256;
5. prove any official→Git content differences are EOL-only, with binary bytes unchanged;
6. use the Git-canonical tree as the source/runtime parity target;
7. record exact file counts and target versions.

Do not invent or predeclare these two tree fingerprints; derive them from the already pinned archive bytes and report them.

If Git changes non-EOL content or any archive fails safety/provenance, STOP.

### Static quality gates

Before branch publication:

- `php -l` every PHP file in target Superio, target Apus, and target RevSlider; zero failures required;
- target headers/constants exactly Superio `1.3.37`, Apus `2.5`, RevSlider `6.7.41`;
- child source remains byte-stable from 2.20;
- static scan finds no new missing parent class/function/method required by the active child or owned Raspitajse integration;
- no active source change exists in WPJBP/Paid/WP Private Message;
- `git diff --check` is clean for task-authored textual changes; do not "fix" immutable vendor whitespace if doing so would break the pinned canonical tree;
- Commit A path set is exactly one file; Commit B path set is exactly the three vendor roots.

---

## 7. Pre-runtime control step, secure backup, and exact rollback material

After feature publication and static acceptance:

1. fast-forward `staging` to Commit A only;
2. run `deployment/deploy-staging.sh changed staging` using Commit A;
3. verify source HEAD / `origin/staging` / deploy marker are Commit A and WordPress runtime versions/files are still the pre-upgrade state;
4. verify protected business/scheduler/mail/payment state did not change.

Then, before Commit B is deployed, acquire the existing selective-runner shared lock nonblocking. If a natural run owns it, wait only a bounded reasonable interval or STOP; do not kill the scheduler.

While the lock is held, create both rollback layers below.

### A. Fresh staging DB backup

Reuse the proven Zadatak 2.16 direct `/usr/bin/mariadb-dump` primitive, not the broken `wp db export` wrapper.

Requirements:

- task-private 0700 directory;
- task-private 0600 `[client]` defaults file populated in-process without printing credentials;
- password never in command arguments, report, shell trace, Git, or permanent config;
- logically consistent dump (`--single-transaction`, `--quick`, `--skip-lock-tables`, drop statements, hex blobs, safe charset, no tablespaces where needed, triggers as supported);
- exit status 0;
- non-empty SQL file mode 0600;
- SHA-256 recorded;
- structural completeness checked against the current live staging table set/count without printing row contents;
- no truncation/skipped-table/read error;
- compatible restore client available.

The staging DB account previously lacked CREATE DATABASE privilege. Do not waste time retrying an isolated restore rehearsal unless a fresh privilege check safely proves that changed; the accepted structural/integrity gate is sufficient.

Retain the SQL backup only through the acceptance/rollback window, then remove it after final PASS or completed rollback.

### B. Exact pre-upgrade runtime snapshots

Create task-private non-web exact filesystem snapshots of these three deployed runtime roots only:

- `wp-content/themes/superio/`;
- `wp-content/plugins/apus-framework/`;
- `wp-content/plugins/revslider/`.

Preserve file bytes, relative paths, and executable-mode metadata sufficiently to restore the exact pre-upgrade runtime trees, including the known historical parent CRLF/runtime-only archive divergence. Record deterministic path+byte fingerprints and file counts, not file contents.

These snapshots are **rollback-only**. They must never be committed, uploaded, or served publicly.

This task explicitly authorizes exact `rsync`/filesystem restoration from these private snapshots **only after a critical failure** and only under the shared lock. Normal deployment must still use the approved deploy script.

Capture a guarded sanitized T0 after backup/snapshots and immediately before vendor deploy. Reverify T0 after backup creation if necessary so it is the authoritative pre-deploy state.

---

## 8. T0 protected-state requirements

At T0 prove at minimum:

- environment is staging;
- `DISABLE_WP_CRON=true`;
- mail safety loaded;
- external WordPress HTTP is preempted;
- payment/refund execution guards remain active;
- active template/stylesheet and all current versions match the accepted pre-upgrade state;
- child phone-field asset/enqueue from 2.20 is intact;
- protected business fingerprint captured without PII;
- exact owned cron hook/callback contract captured;
- non-allowlisted cron fingerprint captured;
- continuation remains absent;
- Action Scheduler summary captured and protected ID `32733` remains `pending` with attempts `0`;
- WPJBP/Paid clean tree fingerprints match their accepted values;
- current WP Private Message active-tree fingerprint captured;
- source/runtime fingerprints of the three upgrade roots captured;
- sanitized vendor-owned DB surfaces for Apus/RevSlider are inventoried sufficiently to account for legitimate upgrade bookkeeping/schema changes later.

Do not manually execute scheduler/business hooks to create evidence.

---

## 9. Controlled Commit B deploy

With the lock still held, deploy the published feature tip (Commit B) only through:

`deployment/deploy-staging.sh changed feature/z2-21-superio-upgrade`

Requirements:

- primary worktree must use the reviewed feature version of `deployment/deploy-staging.sh` whose allowlist contains only the exact three new roots plus all existing accepted entries;
- no manual copy around a successful normal deploy;
- no updater/TGMPA/theme switch/plugin activation shortcut;
- target parent/APUS/RevSlider source trees must be exactly the reviewed Commit B canonical trees;
- deleted target paths must be deleted by the bounded deploy diff, including parent `js/phone-field.js`;
- source/runtime target parity must be rechecked immediately after deploy and before ordinary acceptance;
- child theme and protected plugin trees must not be touched by the Commit B source diff.

If deploy fails part-way, STOP normal acceptance and enter the rollback contract immediately.

---

## 10. First guarded bootstrap and migration accounting

After successful file deploy, run one guarded WordPress bootstrap with all side-effect protections active and the shared scheduler lock still held.

Prove:

- no fatal error;
- no unexpected PHP warning/notice attributable to target versions in the bounded acceptance path;
- template remains `superio`, stylesheet remains `superio-child`;
- runtime versions are exactly Superio `1.3.37`, Apus `2.5`, RevSlider `6.7.41`;
- WPJBP remains `1.2.86`; Paid remains `1.0.19`; WP Private Message remains `1.0.7`;
- WooCommerce, Elementor, and WordPress core remain unchanged;
- no theme/plugin activation/deactivation occurred;
- no TGMPA install/update/bulk action occurred;
- no real mail/network/payment transport occurred.

### Allowed vendor migration boundary

A version update may legitimately create/update **vendor-owned Apus/RevSlider bookkeeping/options/schema** on bootstrap. Such changes are allowed only if all of the following are true:

- exact changed table/option/schema surfaces are identified without reporting private values;
- each changed surface is attributable to Apus Framework or Slider Revolution update logic;
- no Raspitajse business/user/order/job/candidate/employer/application/message/package/communications data changes;
- no unrelated WordPress/WooCommerce/plugin state changes;
- the migration is consistent with the target version and covered by the fresh DB backup.

Do not manually write DB version/options to manufacture acceptance.

Any unexplained, broad, destructive, or cross-domain DB mutation is a critical failure and requires rollback.

---

## 11. Mandatory provenance, security, downgrade-prevention acceptance

All must pass.

### Vendor provenance/parity

1. Superio source/runtime version exactly `1.3.37`.
2. Superio source/runtime canonical 615-file tree equals `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e` under the accepted path+byte algorithm.
3. Outer ZIP and official package tree hashes remain the pinned provenance anchors.
4. Apus source/runtime exactly `2.5` and equal the newly derived Git-canonical target tree fingerprint.
5. RevSlider source/runtime exactly `6.7.41` and equal its newly derived Git-canonical target tree fingerprint.
6. WPJBP active source/runtime tree remains exactly `23c55f5bba87fc2fc58dbb858c3543a0b45d4e1f5b0fa90d74e569eff034668e`.
7. Paid active source/runtime tree remains exactly `d936377ea8d818ede21cd384568ad93c4092f37e09e83df2be2ed92683a9a6a3`.
8. WP Private Message active directory is byte/path-stable from T0; its same-version target bundle inside the parent is **not installed**.

### Downgrade/lifecycle prevention

9. No TGMPA updater/install action executed.
10. No WordPress updater executed.
11. No active WPJBP/Paid/WP Private Message file was overwritten from a parent bundle.
12. No plugin/theme deactivate/reactivate cycle occurred.

### Security target checks

13. Apus `2.5` is beyond the accepted CVE-2024-12296 affected `<=2.4` range; statically inspect the target code path relevant to the prior arbitrary option update/privilege-escalation advisory and confirm the remediation boundary is present. Use an isolated no-real-user harness only if it can be done without creating privileged users or side effects.
14. RevSlider `6.7.41` is beyond the accepted affected ranges through `6.7.37` for the previously identified file-read/authorization issues and beyond `6.7.18` for the SVG stored-XSS issue; verify exact target version/provenance and no older active copy is loaded.
15. Re-run the bounded WPJBP registration privilege-escalation regression harness from 2.16/2.18 because theme registration surfaces changed; do not create a real staging user.
16. Owned alert management routes remain authoritative and alert REST remains disabled where intended.
17. SenderPolicy/Transport authority remains unchanged.

---

## 12. Mandatory Raspitajse compatibility acceptance

Use guarded, non-destructive internal/template/runtime tests. Do not make real submissions, payments, emails, or external browser requests merely to prove rendering.

At minimum prove:

1. child theme remains active and all child PHP files lint/load without missing parent class/function/method fatal;
2. owned child `phone-field-js` remains registered/enqueued exactly once from `/superio-child/assets/js/phone-field.js`, hash `a9a534...add22`, version `a9a53425350b`, and no parent phone-field URL exists;
3. candidate registration and employer registration render/load at the strongest safe internal level and their phone-field contracts remain available;
4. candidate dashboard and employer dashboard template/field registries load, including representative phone field;
5. the three high-risk package child overrides remain loadable against the target parent and accepted WPJBP/Paid APIs:
   - `template-paid-listings/choose-package-form.php`;
   - `template-paid-listings/user-packages.php`;
   - `inc/vendors/elementor/wc-paid-listings-widgets/user_packages.php`;
6. canonical package entitlement still means quota + immutable 30-calendar-day validity and remains separate from individual listing duration; run the accepted non-payment package policy harness;
7. standalone package purchase UI/transport contract remains compatible without a real payment;
8. job search/filter/detail template and query contracts load;
9. candidate/employer profile templates load;
10. job submit/edit template contracts load without creating/editing a real listing;
11. application template/handler contracts load without submitting an application;
12. owned job expiry and employer pre-expiry notification callbacks remain authoritative;
13. candidate automatic age expiry remains disabled;
14. employer→candidate alert creation/sender remains retired;
15. candidate→job owned evaluator remains authoritative and vendor sender remains absent;
16. selective runner still exposes exactly the same three owned zero-argument hourly hooks; continuation remains absent;
17. Raspitajse Commerce employer lookup/HPOS declarations remain compatible;
18. WooCommerce checkout/package template loading relevant to current Raspitajse flows has no missing template/class/method fatal under Woo `9.5.4`;
19. changed Superio WooCommerce override surfaces do not introduce a fatal/outdated-contract break detectable by the installed Woo template loader/status APIs;
20. custom mobile-scroll JS / child style dependencies are statically mapped to target markup/assets sufficiently to detect missing file/selector dependencies; record any browser-only visual risk separately rather than silently editing child code.

If external HTTP to the site is blocked by the known Hostinger/LiteSpeed environment layer before WordPress, do not loop and do not classify that as a product regression. Use internal rendering/template/registry acceptance and record the limitation.

---

## 13. Scheduler, business-state, and side-effect acceptance

Capture T1 and final projections under the same guard/lock contract.

Require:

- protected Raspitajse business fingerprint unchanged from T0;
- candidate/employer/job/package/application/message/communications business state unchanged;
- exact owned cron event/callback contract unchanged;
- non-allowlisted cron fingerprint unchanged;
- no continuation event/callback appears;
- protected AS ID `32733` remains pending with attempts `0`;
- no task invocation of runner/owned cron/broad cron/AS;
- mail/PHPMailer/SMTP sends: zero;
- payment/refund execution: zero;
- actual external WordPress network: zero.

Guard-intercepted plugin update/license-check HTTP attempts may be non-zero after a vendor upgrade. If they occur, inventory sanitized destination class/purpose/count and prove all were blocked before transport. Any unguarded/direct external transport is a critical failure.

No production operation is allowed.

---

## 14. Rollback contract

Do not integrate Commit B into `staging` until all critical acceptance passes.

If any critical deploy/bootstrap/security/compatibility/business-state gate fails:

1. keep `origin/staging` at Commit A;
2. if the deploy marker advanced to Commit B, run `deployment/deploy-staging.sh changed staging` from Commit A to return the normal tracked diff toward the pre-upgrade source state;
3. regardless of whether the feature deploy completed or failed part-way, restore the three exact pre-upgrade runtime snapshots for Superio/Apus/RevSlider under the shared lock using the explicitly authorized rollback-only filesystem restore;
4. verify their runtime fingerprints equal the pre-T0 snapshot fingerprints exactly, including the historical Superio CRLF/runtime-only archive state;
5. if and only if task-caused vendor DB migration/state changed, restore the fresh DB backup using the proven direct `mariadb` client/defaults-file mechanism; never restore production and never restore an unrelated backup;
6. guarded-bootstrap the restored old runtime and prove Superio `1.3.17`, Apus `2.3`, RevSlider `6.7.18`, WPJBP/Paid/child state, scheduler contracts, business fingerprint, and transport guards are restored;
7. deploy marker and Communications manifest must end coherent with Commit A;
8. remove task-private backup/snapshots only after rollback acceptance completes;
9. report PARTIAL/FAIL with the exact blocker and STOP.

Do not force-push staging back to the original baseline. Commit A is an intentional safe staging deploy-control improvement and may remain if the vendor transaction is rolled back.

---

## 15. PASS integration and cleanup

Only after every required acceptance passes:

1. fast-forward `origin/staging` from Commit A to Commit B only;
2. run `deployment/deploy-staging.sh changed staging` so final source HEAD, `origin/staging`, deploy marker and Communications manifest are coherent;
3. re-run final provenance/version/runtime parity and protected-state checks;
4. require primary staging worktree clean/on `staging`;
5. remove task-private extracted copies, DB credential file, DB backup, and runtime rollback snapshots;
6. leave the user-provided original Superio artifact unchanged at its supplied path;
7. leave feature history intact; no force push/history rewrite.

Final PASS classification:

`CONTROLLED_SUPERIO_SECURITY_UPGRADE_ACCEPTED`

PASS requires at minimum:

- Superio `1.3.37` active parent with child still active;
- Apus `2.5` active/exact;
- RevSlider `6.7.41` active/exact;
- WPJBP/Paid active clean trees unchanged;
- WP Private Message active tree unchanged;
- no TGMPA/updater lifecycle action;
- all mandatory Raspitajse compatibility/security gates pass;
- no unauthorized business/scheduler/mail/payment/network side effect;
- final staging/source/deploy state coherent and clean;
- production untouched.

---

## 16. Exactly one proposed next task

If PASS, propose exactly one next task:

**Zadatak 2.22 — Re-baseline the remaining Raspitajse custom WooCommerce/legacy child-theme findings after the vendor upgrades and select the next bounded KEEP/REDESIGN/DROP implementation slice.**

This proposed follow-up should be read-only discovery/reconciliation first. Do not create or execute it automatically.

---

## 17. Report and STOP

Publish the final report through `codex-reports`.

The report must include, sanitized and without secrets/PII:

- initial baseline, Commit A, Commit B, final source/staging/deploy-marker SHAs;
- exact feature branch and two-commit parent chain;
- exact changed-path/root scope for each commit;
- user-provided artifact/hash revalidation;
- parent official and Git-canonical tree hashes;
- newly derived Apus/Rev official-extraction and Git-canonical tree hashes/file counts;
- exact target/current versions;
- backup size/SHA/structural validation and deletion status;
- runtime snapshot fingerprints and deletion status;
- T0/T1/final protected-state reconciliation;
- vendor-owned DB migration accounting, if any;
- TGMPA/updater lifecycle evidence;
- security acceptance evidence;
- child/package/job/profile/submission/application/Woo compatibility evidence and environmental limitations;
- WPJBP/Paid/WP Private Message preservation evidence;
- mail/network/payment counters;
- rollback status;
- production touched: YES/NO;
- exactly one proposed next task.

Then STOP.