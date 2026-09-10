# Zadatak 2.23 — Use the newly provisioned ambient MariaDB auth path to create/verify a full staging DB backup, then resume and complete the unchanged Superio Commit B transaction

Status: READY
Baseline: 6d2e78ad6f866b486c303bfda050a4afec5f90e8
Previous task: 2.22
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `origin/feature/z2-21-superio-upgrade`.

Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full before any DB command, runtime snapshot, WordPress bootstrap, deploy, restore action, or integration. Treat `codex-tasks` as READ-ONLY.

Read the latest Zadatak 2.22 PARTIAL report in full. Read Zadatak 2.21 PARTIAL and the final Zadatak 2.20 PASS / Zadatak 2.18 PASS where needed for the accepted Commit B structure, child compatibility, pinned provenance, and rollback/acceptance contract.

Verify fresh `origin/staging` is exactly:

`6d2e78ad6f866b486c303bfda050a4afec5f90e8`

Verify fresh `origin/feature/z2-21-superio-upgrade` is exactly:

`bb34d768d4edec8050975405874197dd1269d28f`

and that its single parent is exactly the staging baseline above.

Verify live deploy marker equals the staging baseline and primary worktree is clean/on `staging`.

If any SHA, parent, marker, or cleanliness check differs, STOP. Do not rebase, rebuild, amend, cherry-pick, or manufacture a replacement vendor commit.

Execute only Zadatak 2.23. Publish the final report through `codex-reports` and STOP. Do not begin post-upgrade Woo/legacy work automatically.

---

## 1. Newly available prerequisite

A human operator has provisioned MariaDB client authentication for the staging shell account outside the repository and web root. The exact auth file contents are out of scope and MUST NOT be read, copied, printed, parsed, moved, committed, uploaded, or restaged by Codex.

A manual smoke check already demonstrated:

- `/usr/bin/mariadb --batch --skip-column-names -e 'SELECT 1;'` returns `1`;
- `/usr/bin/mariadb-dump --single-transaction --quick --skip-lock-tables --no-data <staging-db>` exits `0`;
- schema smoke dump is non-empty and contains 84 `CREATE TABLE` statements.

Treat the manual evidence only as justification to retry the previously blocked ambient-auth path. Reverify it yourself in a bounded credential-neutral way before any vendor deploy.

Hard rule: do not inspect `~/.my.cnf` or any other option/auth file. Do not use `--password`, `MYSQL_PWD`, environment credential extraction, stdin secrets, `wp-config.php` DB_USER/DB_PASSWORD reads, or any custom credential staging.

The staging database name may be obtained only through a narrowly scoped non-secret mechanism, for example `wp config get DB_NAME --type=constant` if that command succeeds without exposing other config, or an equivalently narrow read that returns only DB_NAME. Do not print the DB name in the report.

---

## 2. Preserve exact Commit B

Do not create any source commit in this task unless a rollback-only report artifact mechanism requires it; application source must remain unchanged until the existing Commit B is integrated after PASS acceptance.

Reverify:

- Commit B SHA exactly `bb34d768d4edec8050975405874197dd1269d28f`;
- parent exactly `6d2e78ad6f866b486c303bfda050a4afec5f90e8`;
- A→B changed roots exactly:
  - `wp-content/themes/superio/**`
  - `wp-content/plugins/apus-framework/**`
  - `wp-content/plugins/revslider/**`
- no child, WPJBP, Paid Listings, WP Private Message, WooCommerce, Elementor, WordPress core, scheduler, or Raspitajse-owned business source diff.

Do not rebuild or re-extract vendor payloads merely to create new hashes. Use the accepted 2.21 provenance unless a fresh bounded check contradicts it.

---

## 3. Credential-neutral DB backup gate

Before filesystem snapshots, vendor deploy, or WordPress bootstrap, prove ambient auth works without inspecting credentials.

### A. Ambient auth smoke

Run exactly one bounded read-only client check equivalent to:

`/usr/bin/mariadb --batch --skip-column-names -e 'SELECT 1;'`

Require exit 0 and output exactly `1` after normal whitespace normalization. Do not print connection details or client-option contents.

If it fails, STOP with `BLOCKED_AMBIENT_DB_AUTH_REGRESSED`.

### B. Resolve only DB_NAME

Resolve the staging DB name through a narrowly scoped non-secret command that exposes only DB_NAME. Do not read or report DB_USER, DB_PASSWORD, DB_HOST, salts, keys, or full `wp-config.php` content.

If DB_NAME cannot be resolved without widening secret access, STOP before vendor deploy.

### C. Create full logical backup

Create a fresh task-private mode-0700 directory outside repository and web root. The SQL file must be mode-0600.

Use `/usr/bin/mariadb-dump` through the ambient auth path only. Required logical consistency options must include at minimum:

- `--single-transaction`
- `--quick`
- `--skip-lock-tables`
- `--hex-blob`
- `--default-character-set=utf8mb4`
- `--skip-tz-utc` only if required by current client/server compatibility
- `--no-tablespaces` if required by privileges/client behavior
- triggers/routines/events only if accessible and relevant; do not fail solely because unsupported privileged metadata is unavailable if normal WordPress table data/schema is fully captured.

Do not use `--all-databases`.

Require:

- exit status 0;
- non-empty dump;
- SHA-256 recorded;
- dump retained through the entire deploy/acceptance/rollback window;
- no stderr indicating skipped tables, truncation, read errors, permission-denied table loss, or corruption.

### D. Structural completeness

Using credential-neutral read-only queries, compare live staging table set/count with the logical dump structurally without printing table names or row contents in the report.

Require exact table-set/count parity. The previously observed manual schema count of 84 is a useful sanity check, but fresh authoritative evidence controls.

Do not inspect application rows/PII.

### E. Restore capability

Prove `/usr/bin/mariadb` can authenticate through the same ambient path and that the dump is syntactically/structurally suitable for restoration.

Do not attempt `CREATE DATABASE` unless a bounded privilege check clearly shows it is available. The staging account historically lacked CREATE DATABASE privilege; if still true, a destructive restore rehearsal is not required. Structural completeness + successful client authentication + valid dump syntax is sufficient, consistent with the previously accepted rollback model.

If any backup gate fails, delete task-private dump/diagnostics and STOP before Commit B deploy.

---

## 4. Filesystem rollback snapshots and T0

Only after the DB backup gate passes, acquire the existing selective-runner shared lock nonblocking. If owned by a natural run, wait a bounded reasonable interval or STOP. Never kill the scheduler.

Under the lock, create exact task-private non-web rollback snapshots of:

- deployed `wp-content/themes/superio/`
- deployed `wp-content/plugins/apus-framework/`
- deployed `wp-content/plugins/revslider/`

Preserve bytes, paths, and executable-mode metadata. Record deterministic file counts and path+byte/mode fingerprints only, not contents.

Capture guarded sanitized T0 immediately before Commit B deploy. Reuse the full protected-state contract from Zadatak 2.21, including:

- environment staging;
- `DISABLE_WP_CRON=true`;
- mail safety/external HTTP/payment guards active;
- active theme/plugin versions still pre-upgrade;
- child phone-field asset/enqueue exact;
- protected business fingerprint;
- exact owned cron callback/event contract;
- non-allowlisted cron fingerprint;
- continuation absent;
- Action Scheduler summary and protected ID 32733 pending/attempts 0;
- WPJBP/Paid accepted clean fingerprints;
- WP Private Message T0 fingerprint;
- three vendor-root runtime fingerprints;
- sanitized vendor-owned DB surfaces sufficient for later migration accounting.

No manual scheduler/business hooks.

---

## 5. Deploy exact Commit B

With lock and rollback material in place, deploy only:

`deployment/deploy-staging.sh changed feature/z2-21-superio-upgrade`

No manual vendor copy, no updater, no TGMPA install/update/bulk, no theme switch, no plugin deactivate/reactivate.

Immediately prove source/runtime parity for the three target roots and exact versions:

- Superio `1.3.37`
- Apus Framework `2.5`
- Slider Revolution `6.7.41`

Confirm deleted parent `js/phone-field.js` is gone and child-owned phone asset remains intact.

If deployment fails or parity is wrong, enter rollback immediately.

---

## 6. Bootstrap, migration accounting, security and compatibility acceptance

Run the guarded first bootstrap and then the full acceptance contract defined in Zadatak 2.21. That contract remains binding and must not be weakened.

At minimum require all of the following:

- no fatal attributable to target vendor versions;
- child theme active;
- exact target vendor versions loaded;
- WPJBP 1.2.86 / Paid 1.0.19 / WP Private Message 1.0.7 unchanged;
- Woo 9.5.4 / Elementor 3.25.11 / WP core unchanged;
- no updater/TGMPA/install/deactivate/reactivate lifecycle action;
- Apus 2.5 remediation boundary for prior privilege-escalation issue remains present;
- RevSlider 6.7.41 exact provenance/loaded-copy check;
- bounded WPJBP registration privilege-escalation regression harness PASS;
- owned alert-management authority / retired REST / SenderPolicy unchanged;
- child phone-field registration from `/superio-child/assets/js/phone-field.js`, version `a9a53425350b`, no parent phone URL;
- candidate/employer registration and dashboard field/template contracts load;
- three high-risk Paid Listings child overrides load against target parent and accepted Paid APIs;
- package entitlement/quota + immutable 30-day validity harness PASS;
- standalone package-purchase UI/transport contract compatible without payment;
- job search/detail/submit/edit contracts load without real mutation;
- candidate/employer profile templates load;
- application contracts load without real submission;
- owned job expiry/pre-expiry callbacks remain authoritative;
- candidate age auto-expiry remains disabled;
- employer→candidate alerts remain retired;
- candidate→job owned evaluator remains authoritative/vendor sender absent;
- selective runner still exposes exactly the same three owned zero-argument hourly hooks; continuation absent;
- Raspitajse Commerce employer lookup/HPOS declarations remain compatible;
- Woo checkout/package relevant template contracts load;
- changed Superio Woo override surfaces have no fatal/outdated-contract break detectable by installed Woo status/template APIs;
- mobile-scroll/style dependencies statically mapped; browser-only visual risk reported separately, not silently fixed.

### Vendor DB migration accounting

Compare T0→T1 DB changes at a sanitized structural/option-key level only. Permit only narrowly attributable Apus/RevSlider bookkeeping/schema/version changes. Any unexplained mutation of Raspitajse business/user/order/job/candidate/employer/application/message/package/communications data is critical failure.

No row values, email addresses, saved queries, or PII in report.

### Side effects

Require zero real SMTP/mail, zero payment/refund, zero actual external WordPress network. Guard-intercepted vendor HTTP may be inventoried by sanitized purpose/count only.

---

## 7. Rollback contract

Do not integrate Commit B into `staging` until every critical gate passes.

On any critical failure:

1. keep `origin/staging` at Commit A;
2. use approved changed deploy from `staging` as appropriate to return tracked paths toward Commit A state;
3. under the shared lock restore the exact three pre-upgrade filesystem snapshots regardless of partial/full vendor deploy state;
4. verify runtime fingerprints exactly equal T0 snapshot fingerprints;
5. if and only if task-caused vendor DB mutation occurred, restore the fresh logical backup using `/usr/bin/mariadb` through ambient authentication only; do not inspect credentials;
6. guarded-bootstrap restored old runtime and prove pre-upgrade versions, child/WPJBP/Paid state, scheduler contracts, business fingerprint, and transport guards;
7. remove backup/snapshots only after rollback is proven complete;
8. report PARTIAL/FAIL precisely and STOP.

Production restore is forbidden.

---

## 8. PASS integration and cleanup

Only after all acceptance passes:

1. fast-forward `staging` exactly from Commit A to existing Commit B `bb34d768d4edec8050975405874197dd1269d28f`;
2. push `staging` once normally, no force;
3. run `deployment/deploy-staging.sh changed staging`;
4. verify source HEAD / `origin/staging` / deploy marker all equal Commit B;
5. verify final runtime target parity and protected-state projections;
6. verify worktree clean;
7. release shared lock;
8. securely delete task-private DB backup, filesystem snapshots, and diagnostics only after final acceptance is complete.

Final PASS classification:

`SUPERIO_VENDOR_STACK_UPGRADE_ACCEPTED`

The final report must record sanitized backup size/hash/table-count parity, target versions/fingerprints, protected-state equality, zero forbidden side effects, and production NO. It must not expose DB name, credentials, auth-file path contents, PII, mail bodies, or row data.

## Exactly one proposed next task on PASS

**Zadatak 2.24 — Re-baseline remaining Raspitajse custom WooCommerce / legacy child-theme findings after the accepted vendor upgrades and select the next bounded KEEP / REDESIGN / DROP implementation slice.**

Propose only. Do not begin automatically.
