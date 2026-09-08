# Zadatak 2.19 — Relocate the parent-path phone-field asset into the Superio child/Raspitajse-owned layer and prove the pre-upgrade child compatibility gate

Status: READY
Baseline: 5416eb4d327fe44f503591d86577e210560339c1
Previous task: 2.18
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, creating a feature branch/worktree, changing source, bootstrapping WordPress, or deploying. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.18 PASS report in full. Read the final Zadatak 2.16 PASS report only where needed for the accepted staging safety/runner/deploy boundary.

Verify fresh `origin/staging` is exactly:

`5416eb4d327fe44f503591d86577e210560339c1`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean/on `staging`. If any baseline differs, STOP and report the mismatch. Do not silently rebase, merge unrelated work, or widen scope.

Execute only Zadatak 2.19. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin the Superio parent/plugin upgrade task automatically.

---

## 1. Accepted facts from Zadatak 2.18

Treat these as authoritative accepted evidence unless a fresh bounded verification contradicts them:

- official clean user-provided Superio target: `1.3.37`;
- supplied installable artifact: `/home/u601262303/repo/themeforest-NNoRVYjo-superio-job-board-wordpress-theme-wordpress-theme.zip`;
- artifact SHA-256: `0d172d4151faddef101a2ff9a6b1c1a8a1c41653018283afbdd0dd6d038cf5b6`;
- official target parent path+byte tree SHA-256: `019ba653e5c27dbdc0c98f2651ee41daeb32014ce6275c44b278f63904a5619f`;
- Git/LF-canonical target parent tree SHA-256: `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e`;
- target Superio `1.3.37` removes `wp-content/themes/superio/js/phone-field.js`;
- current child `wp-content/themes/superio-child/functions.php` explicitly enqueues that parent path;
- current parent phone-field asset is 3,887 bytes with SHA-256:
  `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- this parent-path dependency is the only mandatory child blocker identified before the controlled Superio parent upgrade;
- all 22 child-theme files were inventoried; no other child file requires a pre-upgrade source change in this prerequisite;
- WPJBP `1.2.86` and Paid Listings `1.0.19` remain accepted clean active trees and must not be touched;
- future parent upgrade target also includes Apus Framework `2.5` and Slider Revolution `6.7.41`, but **those upgrades are not part of this task**.

Decision from 2.18:

`READY_AFTER_PRE_UPGRADE_CHILD_THEME_FIXES`

This task closes only that prerequisite.

---

## 2. Goal

Make the phone-field JavaScript a Raspitajse-owned child-theme asset so the active child no longer depends on a parent file that Superio 1.3.37 deletes.

The result must be behavior-preserving relative to the accepted current asset:

1. copy the exact current accepted `phone-field.js` bytes into the child-owned asset tree;
2. change only the child enqueue ownership/path and its cache versioning;
3. keep the current script handle, dependency order, footer loading behavior, intl-tel-input CSS/JS registration, selectors, validation behavior, and business behavior unchanged;
4. prove the current registration/dashboard phone-field surfaces still resolve to the owned child asset;
5. deploy and integrate only after bounded staging acceptance passes.

This is **not** a phone-field rewrite, international-phone-library upgrade, CDN cleanup, general child-theme cleanup, or Superio upgrade.

---

## 3. Exact authorized source scope

Application source changes are restricted to exactly these child-theme paths:

1. existing:
   `wp-content/themes/superio-child/functions.php`
2. new:
   `wp-content/themes/superio-child/assets/js/phone-field.js`

Expected functional change inside `functions.php`: only the `phone-field-js` enqueue ownership/path and deterministic version value needed for the relocation.

Do not modify any other part of `functions.php`, even if unrelated legacy/debug/business code looks undesirable. In particular, do not use this task to clean logging, SMTP fallback code, body-class rules, admin test code, package logic, mail templates, or any other child-theme function.

Do not modify:

- `wp-content/themes/superio/**`;
- any plugin tree;
- `.gitattributes`;
- `deployment/deploy-staging.sh`;
- runner/guard tooling;
- WordPress core;
- WooCommerce;
- Elementor;
- Hostinger scheduler configuration.

The existing staging deploy allowlist already includes `wp-content/themes/superio-child`; no deploy allowlist change is authorized or required.

If the needed behavior cannot be completed within these two source paths, STOP and report the exact blocker rather than widening scope.

---

## 4. Canonical asset relocation

Before copying, verify the tracked source parent asset and deployed runtime parent asset both exist as regular non-symlink files and both have the accepted SHA-256:

`a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`

If source/runtime differ from each other or from the accepted hash, STOP and reconcile before mutation.

Create:

`wp-content/themes/superio-child/assets/js/phone-field.js`

Requirements:

- exact byte-for-byte copy of the accepted current parent asset;
- regular non-executable file;
- no JS content edits in this task;
- source file SHA-256 after Git round-trip must remain the accepted hash above;
- deployed runtime child asset SHA-256 must match source exactly.

Do not preserve or create a second Raspitajse copy inside the parent theme. The existing parent file remains untouched during 2.19 and will disappear naturally only when the later clean Superio 1.3.37 parent replacement occurs.

---

## 5. Enqueue ownership correction

In the existing `enqueue_phone_field_scripts()` block, preserve all current behavior except ownership/path and unstable per-request cache versioning for `phone-field-js`.

Required resulting contract for `phone-field-js`:

- handle remains `phone-field-js`;
- source resolves under the active child theme, using `get_stylesheet_directory_uri()` and the new child asset path;
- dependency remains exactly `intl-tel-input-js`;
- footer loading remains `true`;
- it must not resolve through `get_template_directory_uri()`;
- it must not request `/wp-content/themes/superio/js/phone-field.js`;
- replace the current `time()` version with a deterministic content-tied value. Preferred accepted value is the first 12 hex characters of the pinned SHA-256: `a9a53425350b`, or an equivalently deterministic explicit value tied to these exact bytes. Do not use `time()`, random values, or per-request hashing.

Do not change the current intl-tel-input version, CDN URLs, script/style handles, dependencies, locale behavior, selectors, validation messages, or form-submission behavior in this prerequisite.

If a separate defect is discovered in the JS itself, record it for a later task; do not edit the JS here unless the task would otherwise be impossible, in which case STOP for explicit authorization.

---

## 6. Static acceptance before deploy

Before any staging runtime change, prove on the scoped feature branch:

- changed path inventory contains exactly the two authorized child paths;
- `functions.php` PHP lint PASS;
- new child JS hash equals the accepted `a9a534...add22` SHA-256;
- the `phone-field-js` enqueue resolves to child ownership, not parent ownership;
- no other `phone-field-js` registration or `/js/phone-field.js` parent-path enqueue remains active in tracked Raspitajse source;
- script handle/dependency/footer contract is unchanged;
- no current parent/vendor/plugin file changed;
- `git diff --check` has no new issue in the two changed paths.

Use a normal scoped feature branch from the exact staging baseline. Suggested branch name:

`feature/z2-19-owned-phone-field`

Do not integrate into `staging` before runtime acceptance.

---

## 7. Guarded staging deployment boundary

This source-only prerequisite is not expected to write DB/schema/options/business state and therefore does not require a new DB backup solely for this change. It **does** require the existing staging safety boundary and rollback discipline.

Before feature deploy:

- acquire the existing selective-runner shared lock nonblocking; if a natural run owns it, wait only a bounded interval or STOP, do not kill the scheduler;
- capture a sanitized T0 state sufficient to prove accepted business/cron/Action Scheduler/mail/transport state remains unchanged;
- require staging identity, `DISABLE_WP_CRON=true`, mail safety and HTTP/payment protections as applicable;
- do not manually run the selective runner, any owned cron hook, broad WP-Cron, continuation runner, or Action Scheduler action.

Deploy only through:

`deployment/deploy-staging.sh changed <scoped-feature-branch>`

Do not manually copy files around the approved deploy path.

The current parent Superio remains `1.3.17` throughout this task. Do not delete the parent `js/phone-field.js` in 2.19.

---

## 8. Mandatory runtime compatibility proof

After feature deploy and before integration, run bounded non-destructive acceptance with all side-effect protections active.

### A. WordPress enqueue registry

Prove after the relevant enqueue hooks:

- `phone-field-js` is registered/enqueued exactly once by the intended child code;
- its `src` resolves to the child path ending in `/superio-child/assets/js/phone-field.js`;
- its dependency list contains `intl-tel-input-js` exactly as before;
- it remains footer-loaded;
- its version is deterministic and not request-time based;
- no active enqueue points at `/superio/js/phone-field.js`.

### B. Asset runtime parity

Prove:

- child source/runtime asset both exist as regular files;
- source/runtime SHA-256 both equal `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- the existing parent copy remains unchanged on Superio 1.3.17 during this prerequisite.

### C. Registration/dashboard selector compatibility

Use guarded non-destructive template/source/runtime inspection to prove that the unchanged copied JS still has the expected current targets for the Raspitajse candidate/employer phone flows, including where applicable:

- `.phone-with-flags`;
- `.cmb2-id--employer-phone`;
- `#_employer_phone`;
- dashboard body class `page-template-page-dashboard`;
- representative phone selector `#custom-text-3318838`.

Validate the current candidate registration, employer registration, candidate dashboard, employer dashboard, and representative-phone surfaces at the strongest safe level available without creating real users, sending forms, or making external browser/network calls.

A real authenticated browser E2E is not required in 2.19 if the host environment prevents it. In that case, accepted proof is:

- exact unchanged JS bytes;
- exact enqueue registry contract;
- exact live/template selector availability mapping for the current forms;
- no missing class/method/template fatal during guarded bootstrap;
- child asset source/runtime parity.

Do not treat Hostinger/LiteSpeed environment-layer HTTP 403 as a product regression if it occurs before WordPress, and do not loop on blocked HTTP requests.

### D. Future-parent independence proof

Using the accepted 2.18 target evidence (and, only if useful, a bounded read-only check of the pinned supplied artifact), prove:

- Superio 1.3.37 has no parent `js/phone-field.js`;
- the child enqueue no longer depends on that parent path;
- removing/replacing the parent with clean 1.3.37 will therefore not remove the script URL now used by the child.

Do not install or deploy Superio 1.3.37 in this task.

---

## 9. T1, integration, and rollback

After acceptance, capture sanitized T1/final evidence.

Require unchanged protected state for at least:

- business aggregates used by recent accepted tasks;
- candidate/employer/job/package/application/message state;
- owned cron contracts/timestamps except legitimate natural time passage with no manual execution;
- non-allowlisted cron fingerprint;
- Action Scheduler pending state and protected ID 32733 attempts `0`;
- alert/security/SenderPolicy/Commerce contracts materially unrelated to this source-only change;
- mail/SMTP/payment/refund/external WordPress HTTP side-effect counters remain zero.

If critical acceptance fails:

- restore the exact prior child source/runtime through Git + approved staging deploy;
- keep `origin/staging` at the original baseline;
- prove rollback parity/state;
- report PARTIAL/FAIL and STOP.

If all acceptance passes:

- fast-forward `staging` to the accepted scoped feature commit only;
- use the approved deploy path as required so `origin/staging`, source HEAD, deploy marker, runtime child tree and Communications manifest are coherent;
- primary staging worktree must end clean.

No force push, history rewrite, unrelated merge, or production operation.

---

## 10. PASS criteria

PASS requires all of the following:

1. only the two authorized child paths changed;
2. child asset is exact accepted phone-field bytes/hash;
3. `phone-field-js` loads from the child-owned path with unchanged dependency/footer behavior;
4. no active parent-path phone-field enqueue remains;
5. per-request `time()` versioning for this script is removed in favor of deterministic versioning;
6. current registration/dashboard selector contract remains compatible at the bounded non-destructive acceptance level;
7. future Superio 1.3.37 parent deletion of `js/phone-field.js` can no longer break the child enqueue;
8. Superio parent remains 1.3.17 and unchanged in this task;
9. Apus Framework, Slider Revolution, WPJBP, Paid Listings, WP Private Message, WooCommerce, WordPress core and Elementor remain unchanged;
10. staging business/DB/cron/Action Scheduler state has no task-caused mutation;
11. mail/network/payment side effects remain zero;
12. Hostinger scheduler unchanged;
13. production untouched;
14. final `origin/staging`, source HEAD, deploy marker and runtime accepted child files are coherent and clean.

Final decision should state one of:

- `PRE_UPGRADE_CHILD_COMPATIBILITY_GATE_PASSED`
- `BLOCKED_PRE_UPGRADE_CHILD_COMPATIBILITY`
- or a narrower truthful failure classification.

---

## 11. Exactly one proposed next task

If PASS, propose exactly one next task:

**Zadatak 2.20 — Controlled Superio 1.3.37 + Apus Framework 2.5 + Slider Revolution 6.7.41 staging upgrade with pinned provenance, downgrade prevention, backup, rollback, and full compatibility acceptance.**

Do not create or execute 2.20 automatically.

---

## 12. Report and stop

Publish the final report through `codex-reports` using the established workflow.

The report must include:

- exact initial/final staging SHAs and deploy marker;
- exact changed paths and final feature/integrated commit;
- child asset source/runtime SHA-256;
- exact enqueue source/dependency/version/footer evidence;
- registration/dashboard compatibility evidence and any environmental limitation;
- T0/T1 protected-state reconciliation;
- mail/network/payment counters;
- rollback status;
- production touched: YES/NO;
- exactly one proposed next task.

Do not include PII, secrets, raw DB rows, mail bodies, private recipient data, or credentials.

Then STOP.