# Zadatak 2.22 — Resume the blocked Superio vendor-stack upgrade from a credential-neutral DB-backup gate and complete exact Commit B acceptance

Status: READY
Baseline: 6d2e78ad6f866b486c303bfda050a4afec5f90e8
Previous task: 2.21
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `origin/feature/z2-21-superio-upgrade`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before any backup discovery, WordPress bootstrap, runtime snapshot, deploy, restore test, or integration action. Treat `codex-tasks` as READ-ONLY.

Read the latest Zadatak 2.21 PARTIAL report in full. Read the final Zadatak 2.20 PASS and Zadatak 2.18 PASS reports where needed for child-theme compatibility and pinned vendor provenance. Read Zadatak 2.16 PASS only for the previously accepted staging backup-validation/rollback evidence model; do **not** copy its credential-staging mechanism.

Verify fresh `origin/staging` is exactly:

`6d2e78ad6f866b486c303bfda050a4afec5f90e8`

Verify the live staging deploy marker is the same commit and the primary worktree is clean/on `staging`.

Verify fresh `origin/feature/z2-21-superio-upgrade` is exactly:

`bb34d768d4edec8050975405874197dd1269d28f`

and that its single parent is exactly the staging baseline above.

If any SHA, parent, deploy marker, branch identity, or clean-worktree condition differs, STOP and report the mismatch. Do not rebase, amend, rebuild, cherry-pick, merge unrelated work, or manufacture a replacement vendor commit.

Execute only Zadatak 2.22. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin the post-upgrade Woo/legacy cleanup automatically.

---

## 1. Accepted state from Zadatak 2.21

Treat the following as already accepted unless a fresh bounded check contradicts them.

### Safe integrated control commit

`origin/staging` intentionally contains only Commit A from 2.21:

`6d2e78ad6f866b486c303bfda050a4afec5f90e8`

Commit A:

- parent: `9df2850034117cacdc8dd45078da5c3d1d3c034c`;
- changed path exactly `deployment/deploy-staging.sh`;
- only adds these three roots to the existing staging deploy allowlist/path validator:
  - `wp-content/themes/superio`
  - `wp-content/plugins/apus-framework`
  - `wp-content/plugins/revslider`;
- control deploy passed with zero WordPress runtime file changes;
- this control commit remains accepted even if the vendor upgrade is not completed.

### Exact vendor Commit B

The already-published, statically accepted vendor commit is:

`bb34d768d4edec8050975405874197dd1269d28f`

on:

`origin/feature/z2-21-superio-upgrade`

Its parent must remain Commit A exactly.

Commit B changes only these three roots:

- `wp-content/themes/superio`
- `wp-content/plugins/apus-framework`
- `wp-content/plugins/revslider`

It contains the pinned target stack:

- Superio `1.3.37`;
- Apus Framework `2.5`;
- Slider Revolution `6.7.41`.

No source rebuilding or content editing is authorized in 2.22. The exact existing Commit B is the only acceptable vendor payload.

### Pinned provenance

User-provided Superio installable artifact:

`/home/u601262303/repo/themeforest-NNoRVYjo-superio-job-board-wordpress-theme-wordpress-theme.zip`

Pinned SHA-256:

`0d172d4151faddef101a2ff9a6b1c1a8a1c41653018283afbdd0dd6d038cf5b6`

Accepted canonical target trees:

- Superio official tree: `019ba653e5c27dbdc0c98f2651ee41daeb32014ce6275c44b278f63904a5619f`;
- Superio Git/LF-canonical tree: `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e`;
- Apus bundled ZIP: `a0506a74d80c6e8af553d9bead31f528208eed0485755d8890e36f64aa7792f4`;
- Apus Git-canonical tree: `e436bc2237fda3cf6938a1cdb715e7de5d2c0a7a942ca0acf832735efc4c1491`;
- RevSlider bundled ZIP: `f0ff13e70cac3c0ffcecfbea1a78be483ed0d00de9db90ee60e0252b23a5b175`;
- RevSlider Git-canonical tree: `0102ac80aba0f36f9cec7ff3a8f794a0a22c430072fcaadd416901f5a70fb8c9`.

Previously accepted lint/provenance gates:

- Superio PHP: 367/367 PASS;
- Apus PHP: 497/497 PASS;
- RevSlider PHP: 148/148 PASS;
- no material official-to-Git difference after accepted LF canonicalization;
- six accidental executable Superio asset modes normalized to regular `100644` in Commit B;
- exact bundled WPJBP/Paid/WP Private Message archive identities were revalidated but those installed plugin trees are **not** part of the deployment.

### Pre-upgrade runtime remains old

Because 2.21 stopped before Commit B deploy, runtime must still be:

- Superio `1.3.17`;
- Apus Framework `2.3`;
- Slider Revolution `6.7.18`;
- WP Job Board Pro `1.2.86`;
- Paid Listings `1.0.19`;
- WP Private Message `1.0.7`;
- WooCommerce `9.5.4`;
- Elementor `3.25.11`;
- WordPress `6.6.7`;
- active stylesheet/template: `superio-child` / `superio`.

The child-owned phone asset from 2.20 must remain exact:

`wp-content/themes/superio-child/assets/js/phone-field.js`

SHA-256:

`a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`

### Pre-upgrade rollback fingerprints from 2.21

Before any new deploy, recreate fresh snapshots and verify the live old runtime still matches these accepted byte-tree fingerprints:

- Superio old runtime: `1ba110601d29609ad8403bb96e89d882290e9cd44917153234c72fad3ff61b51`;
- Apus old runtime: `a2ec130283ec47b077d2c6e7cb2034688cc06dea01fe7cf12102f1beb488518b`;
- RevSlider old runtime: `22e3157ded5cd0dd337b0d598f0a1f434edd1e3447b4d11399652ee3801e33dc`.

Accepted old runtime mode-tree fingerprints:

- Superio: `ccce288f2a3be3cdef14625752536fe234317d01dda1083322c80e7b3304aa93`;
- Apus: `0feb7f8b2cf16faed36a3c3bbf0328c59e0fd9b26cf6687d0bd0063c0a539a67`;
- RevSlider: `1e1589e1063a9729298cb983fc9aca3f80e454a3f07af6d62e8ab998b2b949fc`.

If a fresh runtime fingerprint differs, STOP before deploy and identify the drift. Do not overwrite unexplained runtime changes.

---

## 2. Goal and scope

Resume the exact already-prepared 2.21 transaction without rebuilding it:

1. satisfy the hard pre-deploy database-backup gate **without reading, copying, printing, exporting, persisting, or restaging database credentials**;
2. recreate exact pre-upgrade filesystem rollback snapshots;
3. capture guarded T0 protected state;
4. deploy exact Commit B through the approved staging deploy script;
5. account for any vendor bootstrap/schema/option migration;
6. run full compatibility, security, package, communications, child-theme and downgrade-prevention acceptance;
7. either:
   - fast-forward `staging` from Commit A to exact Commit B after PASS, or
   - restore the exact old runtime/database state and keep `origin/staging` at Commit A after a critical failure.

This task authorizes **no new application/source commit**. If source changes are required to make the upgrade work, STOP and report the exact incompatibility for a separate task.

---

## 3. Credential-neutral database backup contract

The previous 2.21 blocker came from attempting to populate a private MariaDB defaults file with credentials. That mechanism is explicitly **not authorized** here.

### Absolute prohibitions

Do not:

- read or print DB password values from `wp-config.php`, environment files, process environment, shell history, or any secret store;
- `cat`, `grep`, parse, regex-extract, copy, echo, printf, serialize, encode, or otherwise restage DB credentials;
- place a DB password in command-line arguments, an environment variable such as `MYSQL_PWD`, stdin, a temporary file, a here-doc, process substitution, shell variable, report, log, or patch;
- create a task-private `[client]` defaults file containing copied credentials;
- weaken or circumvent the security reviewer/policy that blocked 2.21;
- use production credentials or production DB backup paths.

Database name/table names may be obtained through an already-authenticated WordPress bootstrap when needed, but do not include sensitive identifiers in the final report; report counts/fingerprints instead.

### Allowed credential-neutral backup primitives

Try only the following bounded mechanisms, in this order.

#### A. WP-CLI managed export — one bounded attempt

A single attempt with the installed WP-CLI is allowed because Codex does not manually extract or restage credentials.

Use a private destination outside the repository and outside the web root, under a task-owned `0700` directory, with resulting dump mode forced to `0600`.

Use a timeout and capture only sanitized exit/status diagnostics. Do not print command internals that could expose configuration.

If `wp db export` fails with the already-known disabled-process/`exec()`/exit-255 signature, classify it once and **do not retry**.

#### B. Existing client-authentication path — no secret inspection

If A is unavailable, one credential-neutral direct-client attempt is allowed **only if the server account already has a usable MariaDB client authentication profile or socket-based authentication that works without supplying credentials in this task**.

Rules:

- do not open or print any client option file that may contain credentials;
- do not copy an option file;
- do not use `--password`, `-p...`, `MYSQL_PWD`, or generated defaults files;
- a bounded `SELECT 1` connectivity test using the existing client authentication context is allowed;
- if that connectivity succeeds, `/usr/bin/mariadb-dump` may use that same existing authentication context to create the private dump;
- if it fails, do not probe passwords or alternate credentials.

If neither A nor B works without credential manipulation, STOP with:

`BLOCKED_CREDENTIAL_NEUTRAL_DB_BACKUP_UNAVAILABLE`

Do not deploy Commit B. Do not invent a PHP SQL-dumper, custom backup format, weak copy, or partial-table substitute merely to make progress.

---

## 4. Backup acceptance gate

A backup is accepted only when all applicable checks pass.

Required properties:

- created from the staging database only;
- regular non-symlink file;
- stored outside repository and web root in task-private `0700` directory;
- file permission `0600`;
- non-empty and plausibly complete logical dump;
- SHA-256 and byte size recorded in sanitized evidence;
- exact current staging table-set fingerprint captured through an authenticated WordPress/bootstrap query;
- dump structural table-set equals the fresh current table-set exactly;
- no truncation/error footer/signature detected;
- logical export is consistent for InnoDB (`--single-transaction`/equivalent where supported), quick/streamed rather than table-locking where supported;
- triggers/events/routines included where the selected primitive supports them; if none exist, record the zero count rather than inventing content;
- no backup content, row values, PII, credentials, user emails, orders, jobs, applications, messages or mail bodies printed to the report.

Fresh table count is expected to be close to the previously validated 84-table state, but do not hard-code 84 as truth. Compare the dump against the fresh live table-set fingerprint.

### Restore capability gate

Before deploy, also prove the corresponding restore path exists **without credential restaging**:

- if WP-CLI produced the dump, verify the installed WP-CLI import command is available in the same staging context;
- if existing MariaDB client authentication produced the dump, verify `/usr/bin/mariadb` can perform a bounded read-only `SELECT 1` using the same existing authentication context.

A destructive restore rehearsal against the live DB is forbidden. A temporary-database restore rehearsal is optional only if an already-available isolated database can be created without broad privileges or credential handling. Lack of `CREATE DATABASE` permission is not itself a blocker if the structural/integrity gate and restore client path are otherwise sound, matching the accepted 2.16 evidence model.

If a dump exists but no credential-neutral restore path is available, classify:

`BLOCKED_DB_BACKUP_NOT_RESTORABLE_BY_ALLOWED_PATH`

and STOP before vendor deploy.

---

## 5. Recreate exact filesystem rollback snapshots

After the database backup gate passes, acquire the existing selective-runner shared lock nonblocking. If a natural scheduler run owns it, wait only a bounded interval or STOP; do not kill the scheduler.

Create task-private, non-web-accessible snapshots of the exact live pre-upgrade runtime roots:

- `wp-content/themes/superio`
- `wp-content/plugins/apus-framework`
- `wp-content/plugins/revslider`

Requirements:

- preserve regular-file bytes, directory structure and relevant modes;
- do not follow symlinks;
- verify snapshot byte-tree and mode-tree equal the live pre-upgrade root before continuing;
- verify the live pre-upgrade fingerprints equal the accepted 2.21 values in Section 1;
- snapshots are rollback material only and must never be committed or published.

Also reconfirm before deploy:

- child theme runtime is unchanged from 2.20;
- WPJBP runtime tree remains accepted `23c55f5bba87fc2fc58dbb858c3543a0b45d4e1f5b0fa90d74e569eff034668e`;
- Paid Listings runtime tree remains accepted `d936377ea8d818ede21cd384568ad93c4092f37e09e83df2be2ed92683a9a6a3`;
- WP Private Message remains byte/path-stable from the 2.21 pre-deploy capture;
- no updater/TGMPA action has run between 2.21 and this resume.

Any unexplained drift: STOP before deploy.

---

## 6. Guarded T0 state

With the shared lock held, capture sanitized T0 evidence sufficient to prove that the vendor replacement does not alter unrelated Raspitajse state.

At minimum capture/fingerprint:

- environment identity is staging;
- `DISABLE_WP_CRON=true`;
- mail safety loaded;
- external WordPress HTTP preemption available;
- business-state fingerprint covering candidates, employers, jobs, applications, packages/entitlements, Woo orders needed by recent accepted tasks, and message state without dumping PII;
- owned cron/event/callback fingerprint and timestamps;
- non-allowlisted cron fingerprint;
- continuation/claim state;
- Action Scheduler state and protected ID `32733` status/attempts;
- active theme/plugin versions;
- selected schema/table-set fingerprint;
- selected option-name/value fingerprints for vendor namespaces sufficient to account for migrations without reporting sensitive values.

Do not manually run owned cron hooks, broad WP-Cron, the selective runner, continuation runner, or Action Scheduler.

Do not send mail, invoke PHPMailer/SMTP transport, execute payments/refunds, or make application external HTTP calls.

---

## 7. Exact Commit B pre-deploy verification

Immediately before deploy, reverify the remote feature branch still points to exact Commit B:

`bb34d768d4edec8050975405874197dd1269d28f`

and parent remains exact Commit A.

Reverify the Commit A → Commit B diff changes only the three authorized vendor roots and contains no child-theme, WPJBP, Paid Listings, WP Private Message installed-tree, Raspitajse-owned, WooCommerce, Elementor, WordPress core, scheduler or deployment-script change.

Do not amend Commit B.

Do not run WordPress theme/plugin updater APIs.

Do not invoke TGMPA install/update/bulk.

Do not switch the theme.

Do not deactivate/reactivate plugins.

The READY task is explicit human authorization to deploy this exact already-published Commit B once all backup/snapshot/T0 gates pass. No additional feature-branch publication is required or authorized.

---

## 8. Vendor deploy

Deploy only with the existing approved script and exact remote feature branch:

`deployment/deploy-staging.sh changed feature/z2-21-superio-upgrade`

No manual vendor copying during the forward deployment.

After deploy, require source/runtime path-byte parity against Commit B for the three roots, using the accepted Git/LF-canonical trees and expected regular modes.

Expected active vendor versions after first guarded bootstrap:

- Superio parent `1.3.37`;
- Apus Framework `2.5`;
- Slider Revolution `6.7.41`.

Expected invariants:

- stylesheet remains `superio-child`;
- template remains `superio`;
- child theme was not replaced or modified;
- old parent `superio/js/phone-field.js` is absent after clean 1.3.37 replacement;
- active `phone-field-js` still resolves exclusively to the child-owned asset;
- WPJBP remains `1.2.86` and byte-stable;
- Paid Listings remains `1.0.19` and byte-stable;
- WP Private Message remains `1.0.7` and byte-stable;
- no bundled archive caused downgrade/reinstall of an already-accepted installed plugin.

If file parity or version identity fails, do not continue into broad testing. Enter rollback.

---

## 9. Guarded first bootstrap and migration accounting

Bootstrap WordPress only under staging identity with mail and external-HTTP protections active.

Allow normal initialization of the already-active replaced theme/plugins, but never call updater/installer/activation APIs.

Capture migration/accounting evidence around the first and second guarded bootstrap.

Allowed task-caused DB changes are only vendor-version/schema/option changes that can be attributed to the Superio 1.3.37 / Apus 2.5 / RevSlider 6.7.41 replacement.

Examples of namespaces that may legitimately move if vendor code requires it include:

- RevSlider-owned tables/options/transients;
- Apus Framework-owned options/cache/schema metadata;
- Superio/theme-owned options/transients/version metadata.

Do not assume a namespace change is valid merely because its name looks vendor-like; inspect purpose at a sanitized structural level.

Hard rollback triggers include any unexplained task-caused mutation to:

- WordPress users/roles/capabilities outside a documented security migration;
- candidate/employer/job/application/message business data;
- package/entitlement ownership/quota/activation/expiry data;
- WooCommerce orders/payments/refunds/customer state;
- alert ledgers/claims/sends;
- cron or Action Scheduler execution state caused by manually executing jobs;
- mail queue/transport state;
- external network side effects.

Second guarded bootstrap should be idempotent with respect to required vendor migrations. Unbounded repeated migration/update behavior is a failure.

---

## 10. Mandatory compatibility and security acceptance

Run bounded non-destructive acceptance after the vendor stack reaches a stable guarded bootstrap.

### A. Theme/child compatibility

Prove:

- child remains active;
- child `functions.php` remains byte-stable from 2.20;
- child phone asset remains exact SHA `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- `phone-field-js` registry source is the child path, dependency remains `intl-tel-input-js`, deterministic version remains `a9a53425350b`, footer group remains unchanged;
- no active reference to removed parent `/superio/js/phone-field.js` exists;
- child Paid Listings overrides load without fatal and still call accepted Raspitajse Commerce view-model/package flows;
- child custom package UI template and Elementor paid-listings widget load without missing method/class/template fatal;
- high-risk child CSS/template intersections identified in 2.18 at least render/initialize at the strongest safe internal level available.

### B. Candidate/employer registration and dashboard surfaces

Using internal guarded rendering/registry inspection rather than real account creation, prove:

- candidate registration renders current phone field contract;
- employer registration renders current phone field contract;
- candidate dashboard field registry resolves;
- employer dashboard field registry resolves;
- representative phone field resolves;
- no new PHP fatal, missing class/method, or missing parent asset error appears.

Do not create real users or submit real registration forms.

### C. Paid Listings / package entitlement regression

Re-run the accepted bounded package/entitlement compatibility harnesses sufficient to prove:

- canonical entitlement ownership/meta contract remains intact;
- activation/30-day validity rules remain intact;
- quota/duration rules remain intact;
- standalone dashboard purchase entry still uses owned `package-purchase.js` behavior;
- no vendor target/bundle downgrade or reinstall changes WPJBP/Paid Listings active byte trees;
- no order/payment execution is performed.

### D. Communications and scheduler regression

Prove without sending:

- candidate→job owned alert security/management contract remains intact;
- candidate→job evaluator/scheduler callback registration remains intact;
- employer→candidate alerts remain retired;
- job listing expiry evaluator and one-day employer notice callback contracts remain intact;
- SenderPolicy remains loaded;
- communications source/runtime/manifest parity remains coherent;
- owned scheduler events remain exactly one each as accepted;
- protected AS ID `32733` remains `pending`, attempts `0` unless legitimate unrelated natural state change is explicitly reconciled;
- no broad cron/AS runner invoked.

### E. Apus security gate

Prove target Apus Framework is `2.5` and retains the static/runtime boundary already identified in 2.21 for the formerly vulnerable import AJAX path:

- nonce check present;
- `manage_options` capability requirement present;
- request values sanitized/validated as target code intends.

Do not run a live exploit, create privileged users, or weaken capabilities to test it.

### F. Slider Revolution security/version gate

Prove active Slider Revolution is `6.7.41`, which is beyond the previously cited affected ranges through `6.7.37`.

Do not perform external update/license calls merely to prove version state.

### G. Downgrade prevention / TGMPA

Prove during guarded bootstrap/acceptance:

- no TGMPA installer/upgrader/bulk action executed;
- WPJBP `1.2.86` was not replaced by bundled copy;
- Paid Listings `1.0.19` was not replaced by bundled copy;
- WP Private Message installed tree was not reinstalled;
- no activation/deactivation/theme-switch lifecycle action occurred;
- no vendor updater made an external request.

### H. General bootstrap integrity

Run bounded symbol/class/template/bootstrap checks sufficient to detect missing required PHP symbols caused by the version change.

A host-layer HTTP 403 before WordPress is not a product regression and must not trigger repeated HTTP retries. Use internal bootstrap/render evidence where host HTTP remains blocked.

---

## 11. T1/final reconciliation

After compatibility acceptance, capture T1 and final protected evidence.

Reconcile against T0:

- business fingerprint unchanged;
- package/entitlement business state unchanged;
- jobs/candidates/employers/applications/messages unchanged;
- Woo order/payment/refund state unchanged;
- owned cron/AS state unchanged except explicitly justified natural time passage with no task execution;
- protected AS `32733` attempts remain `0`;
- mail/PHPMailer/SMTP counters remain zero;
- payment/refund counters remain zero;
- actual external WordPress network remains zero;
- only explicitly accounted vendor schema/options/version changes are allowed in DB.

If an unexplained critical delta exists, rollback.

---

## 12. Rollback contract

### Failure before Commit B deploy

No runtime rollback is required. Keep `origin/staging` and deploy marker on Commit A. Delete temporary backup/snapshot material after the blocker report if no longer needed.

### Failure after Commit B deploy but before integration

`origin/staging` must remain Commit A.

Under the shared lock:

1. determine whether vendor bootstrap caused any DB mutation relative to T0;
2. if DB mutation occurred, restore the accepted logical backup using the same credential-neutral restore path that passed the pre-deploy gate;
3. restore the exact three old vendor runtime roots from the verified pre-upgrade snapshots, including historical runtime bytes/modes and runtime-only payloads;
4. prove all three restored byte-tree and mode-tree fingerprints equal the accepted pre-upgrade values;
5. prove old versions again read Superio `1.3.17`, Apus `2.3`, RevSlider `6.7.18`;
6. prove child/WPJBP/Paid/WP Private Message/business/scheduler state equals T0;
7. restore the staging deploy marker/communications manifest coherently to Commit A only after exact rollback parity is proven.

Manual filesystem restoration is authorized **only as rollback from a failed Commit B runtime deployment**, only for the three snapshotted vendor roots, and only from the task-created verified snapshots. It is not an authorized forward-deploy method.

If rollback itself cannot be proven exact, STOP immediately with a critical inconsistent-staging classification; do not continue testing or integrate.

### PASS integration

If and only if all acceptance passes:

- fast-forward `staging` from exact Commit A to exact Commit B only;
- push `staging` once normally, no force/history rewrite;
- run final `deployment/deploy-staging.sh changed staging` for marker/manifest coherence; a no-op file sync is acceptable if runtime already equals Commit B;
- require final `origin/staging`, source HEAD, deploy marker and all three runtime vendor trees to identify exact Commit B/canonical target state;
- primary staging worktree ends clean/on `staging`.

After final PASS evidence is captured, securely remove the temporary DB dump and rollback snapshots. Retain only sanitized hashes/counts in the report.

---

## 13. PASS criteria

PASS requires all of the following:

1. exact baseline Commit A verified on `origin/staging` and deploy marker;
2. exact existing Commit B verified on the feature branch with exact parent Commit A;
3. no new application/source commit created;
4. a credential-neutral, private, structurally complete and restore-capable staging DB backup passes the hard gate;
5. exact pre-upgrade filesystem snapshots pass old-runtime byte/mode fingerprints;
6. Commit B deploys only through the approved staging deploy script;
7. runtime target versions are Superio `1.3.37`, Apus `2.5`, RevSlider `6.7.41`;
8. all three runtime trees match exact accepted Commit B canonical content/modes;
9. child theme remains active and byte-stable;
10. owned phone-field asset/enqueue remains correct after parent replacement;
11. WPJBP `1.2.86`, Paid Listings `1.0.19`, and WP Private Message `1.0.7` installed trees are not downgraded/reinstalled;
12. package/entitlement regression acceptance passes;
13. candidate/employer registration/dashboard bounded compatibility passes;
14. communications/job-expiry/scheduler contracts pass without sends/execution;
15. Apus security gate passes;
16. RevSlider target-version security gate passes;
17. no TGMPA/updater/switch/deactivate/reactivate lifecycle action occurs;
18. vendor DB migration changes are bounded, attributable and idempotent;
19. unrelated business/DB/cron/AS state remains unchanged;
20. mail/network/payment/refund side effects remain zero;
21. Hostinger scheduler unchanged;
22. production untouched;
23. final `origin/staging`, source HEAD, deploy marker and runtime identify exact Commit B;
24. temporary sensitive backup/snapshot artifacts are removed only after final acceptance.

Final PASS decision string:

`SUPERIO_VENDOR_STACK_CONTROLLED_UPGRADE_ACCEPTED`

Credential-neutral backup unavailable before deploy:

`BLOCKED_CREDENTIAL_NEUTRAL_DB_BACKUP_UNAVAILABLE`

Backup present but no allowed restore path:

`BLOCKED_DB_BACKUP_NOT_RESTORABLE_BY_ALLOWED_PATH`

Use a narrower truthful classification for any other failure.

---

## 14. Next task rule

If PASS, propose exactly one next task:

**Zadatak 2.23 — Re-baseline the remaining Raspitajse custom WooCommerce and legacy child-theme findings after the completed vendor upgrades, classify KEEP / REDESIGN / DROP, and select the next bounded implementation slice.**

If this task is BLOCKED/PARTIAL, do **not** propose the business-cleanup task as executable next work. Propose only the narrow corrective prerequisite needed to complete this exact vendor transaction.

Do not create or execute the next task automatically.

---

## 15. Report and STOP

Publish through `codex-reports` using the established workflow.

Report must include, without secrets/PII:

- task result and decision classification;
- initial/final `origin/staging` SHA;
- exact feature Commit B SHA and parent;
- backup primitive classification (`WP_CLI_MANAGED`, `EXISTING_CLIENT_AUTH`, or blocked), dump size/hash, table-set counts/fingerprint and restore-path proof without credentials;
- pre-upgrade snapshot byte/mode fingerprints;
- deploy/integration/rollback status;
- exact runtime vendor versions/tree fingerprints;
- exact installed WPJBP/Paid/WP Private Message preservation evidence;
- child-theme/phone-field evidence;
- migration accounting summary using only sanitized names/counts/fingerprints;
- package/registration/communications/security acceptance outcomes;
- T0/T1/final protected-state reconciliation;
- mail/network/payment/refund counters;
- scheduler touched YES/NO;
- production touched YES/NO;
- temporary backup/snapshot cleanup status;
- exactly one proposed next task according to Section 14.

Never report credentials, secret option values, DB dump contents, raw business rows, user emails, candidate PII, order/customer details, saved-query plaintext, mail bodies, or private recipient data.

Then STOP.