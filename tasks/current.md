# Zadatak 2.20 — Resolve phone-field EOL provenance and complete the pre-upgrade child compatibility gate

Status: READY
Baseline: 5416eb4d327fe44f503591d86577e210560339c1
Previous task: 2.19
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, creating a feature branch/worktree, changing source, bootstrapping WordPress, or deploying. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.18 PASS report and the latest Zadatak 2.19 PARTIAL report in full. Read the final Zadatak 2.16 PASS report only where needed for the accepted staging safety/runner/deploy boundary.

Verify fresh `origin/staging` is exactly:

`5416eb4d327fe44f503591d86577e210560339c1`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean/on `staging`. If any baseline differs, STOP and report the mismatch. Do not silently rebase, merge unrelated work, or widen scope.

Execute only Zadatak 2.20. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin the Superio parent/plugin upgrade automatically.

---

## 1. Accepted facts and reason for this corrective task

Zadatak 2.18 PASS established:

- official clean user-provided Superio target: `1.3.37`;
- supplied artifact: `/home/u601262303/repo/themeforest-NNoRVYjo-superio-job-board-wordpress-theme-wordpress-theme.zip`;
- artifact SHA-256: `0d172d4151faddef101a2ff9a6b1c1a8a1c41653018283afbdd0dd6d038cf5b6`;
- official target parent path+byte tree SHA-256: `019ba653e5c27dbdc0c98f2651ee41daeb32014ce6275c44b278f63904a5619f`;
- Git/LF-canonical target parent tree SHA-256: `b597e629d86c602e4246ac812a8b615ffeac81617b2835eea752c4a228d5881e`;
- target Superio `1.3.37` removes `wp-content/themes/superio/js/phone-field.js`;
- current child `wp-content/themes/superio-child/functions.php` explicitly enqueues that parent path;
- the tracked source parent phone-field asset is 3,887 bytes with SHA-256:
  `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- this parent-path dependency is the only mandatory child blocker identified before the controlled Superio parent upgrade;
- all 22 child-theme files were inventoried; no other child file requires a pre-upgrade source change in this prerequisite;
- WPJBP `1.2.86` and Paid Listings `1.0.19` remain accepted clean active trees and must not be touched;
- the later controlled theme transaction targets Superio `1.3.37`, Apus Framework `2.5`, and Slider Revolution `6.7.41`, but **those upgrades are not part of this task**.

Zadatak 2.19 PARTIAL then proved an overly strict raw-byte precondition blocked the relocation:

- tracked source parent asset: 3,887 bytes, SHA-256 `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- deployed runtime parent asset: 3,989 bytes, SHA-256 `acf25cf17d3656054ec52db3b600fe118e9dc93572cdfed3741dc25890f4285e`;
- no source/runtime mutation occurred;
- staging remained on the baseline above.

Zadatak 2.17/2.18 had already established widespread historical CRLF/LF source/runtime normalization differences in the Superio parent with no material common-file customization proven after EOL normalization.

This task explicitly redefines the canonical precondition for **this one asset only**: raw source/runtime bytes are not required to match if, and only if, the runtime difference is proven to be purely line-ending representation with zero material byte difference after deterministic EOL normalization.

---

## 2. Goal

Close the pre-upgrade child compatibility blocker safely:

1. prove whether the source/runtime `phone-field.js` mismatch is EOL-only;
2. if and only if it is EOL-only, designate the tracked LF source bytes as the canonical Raspitajse-owned asset;
3. copy those exact canonical bytes into `superio-child/assets/js/phone-field.js`;
4. change only the child enqueue ownership/path and deterministic cache versioning;
5. prove current registration/dashboard phone-field compatibility and independence from the parent file;
6. deploy, accept, and integrate only the bounded child-theme change.

If any material difference remains after EOL normalization, STOP with `BLOCKED_PHONE_FIELD_MATERIAL_DIVERGENCE`. Do not choose source or runtime arbitrarily and do not edit the parent theme.

---

## 3. Exact EOL-provenance gate

Before creating a feature branch mutation, inspect these two existing files only:

- tracked source: `wp-content/themes/superio/js/phone-field.js` at exact baseline;
- deployed staging runtime: the corresponding `wp-content/themes/superio/js/phone-field.js`.

Both must be regular, non-symlink files. Record sanitized size/hash/line-ending statistics only; do not print the file body into the report.

### Required raw evidence

Confirm the source still has:

- bytes: `3887`;
- SHA-256: `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`.

Confirm the runtime still has:

- bytes: `3989`;
- SHA-256: `acf25cf17d3656054ec52db3b600fe118e9dc93572cdfed3741dc25890f4285e`.

If either side changed from these accepted facts, STOP and report `BLOCKED_PHONE_FIELD_PROVENANCE_CHANGED` rather than guessing.

### Deterministic EOL-only proof

Use a byte-preserving local comparison with exactly this logical normalization for comparison purposes only:

1. replace every CRLF byte pair (`0D 0A`) with LF (`0A`);
2. then replace any remaining lone CR (`0D`) with LF (`0A`);
3. do not trim whitespace, decode/re-encode strings, alter tabs/spaces, or perform any other transformation.

Prove all of the following:

- tracked source contains no unexpected binary/NUL content;
- runtime contains no unexpected binary/NUL content;
- raw size delta is exactly explainable by line-ending bytes;
- after the normalization above, runtime normalized byte count equals the tracked source byte count;
- normalized runtime SHA-256 equals the tracked source SHA-256 exactly:
  `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- byte-for-byte comparison of normalized runtime against tracked source is exact;
- there is no other differing byte, insertion, deletion, whitespace edit, JS token change, selector change, comment change, or data change.

Prefer also recording counts of LF, CRLF, and lone-CR line endings on each side. Do not treat `--ignore-space-at-eol` or a visual diff alone as sufficient proof.

If this gate passes, classify the mismatch:

`EOL_ONLY_RUNTIME_CRLF_VARIANT`

and designate the tracked source bytes as:

`CANONICAL_OWNED_PHONE_FIELD_ASSET`

Do **not** modify, normalize, deploy, or reconcile the current parent runtime file in this task. The parent `1.3.17` runtime may remain with its historical CRLF representation until the later clean parent replacement.

If this gate fails, STOP with `BLOCKED_PHONE_FIELD_MATERIAL_DIVERGENCE`; source changes `0`, deploys `0`.

---

## 4. Exact authorized source scope after the EOL gate passes

Application source changes are restricted to exactly these child-theme paths:

1. existing:
   `wp-content/themes/superio-child/functions.php`
2. new:
   `wp-content/themes/superio-child/assets/js/phone-field.js`

No parent-theme source change is authorized.

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

Do not use this task to clean unrelated legacy/debug/business code in child `functions.php`.

The existing staging deploy allowlist already contains `wp-content/themes/superio-child`; no deployment allowlist change is authorized or required.

If the required behavior cannot be completed within exactly the two child paths above, STOP rather than widening scope.

---

## 5. Canonical child asset relocation

Create:

`wp-content/themes/superio-child/assets/js/phone-field.js`

Requirements:

- copy the **tracked canonical source bytes**, not the raw CRLF runtime bytes;
- exact byte-for-byte content SHA-256 must be:
  `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- regular non-executable file;
- no JS content edits in this task;
- Git index and fresh checkout must retain the same canonical hash;
- deployed runtime child asset must match the committed source hash exactly.

Do not preserve/create another Raspitajse copy inside the parent theme. The existing parent file remains untouched during this task and will disappear only when the later clean Superio 1.3.37 parent replacement occurs.

---

## 6. Enqueue ownership correction

In the existing `enqueue_phone_field_scripts()` block inside child `functions.php`, preserve all behavior except the `phone-field-js` ownership/path and its unstable per-request cache version.

Required resulting contract:

- handle remains exactly `phone-field-js`;
- source uses `get_stylesheet_directory_uri()` and resolves to:
  `/wp-content/themes/superio-child/assets/js/phone-field.js`;
- dependency remains exactly `intl-tel-input-js`;
- footer loading remains `true`;
- it must no longer use `get_template_directory_uri()` for this script;
- it must not request `/wp-content/themes/superio/js/phone-field.js`;
- replace `time()` with deterministic version string:
  `a9a53425350b`.

Do not change:

- intl-tel-input version `17.0.8`;
- current CDN URLs;
- style/script handles;
- dependency graph;
- initial country/preferred countries;
- selectors;
- validation messages;
- submit behavior;
- JS code itself.

Any other discovered defect is report-only for a later task.

---

## 7. Feature branch and static acceptance

Create a normal scoped feature branch/worktree from the exact baseline. Suggested name:

`feature/z2-20-owned-phone-field-eol`

Before deploy, prove:

- changed path inventory contains exactly the two authorized child paths;
- `functions.php` PHP lint PASS;
- child JS source hash equals `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- Git index and a fresh clean checkout retain that exact JS hash;
- `phone-field-js` source now resolves through child ownership;
- no active tracked Raspitajse enqueue points to `/superio/js/phone-field.js`;
- handle, dependency, footer, CDN dependency behavior are unchanged;
- version is exactly deterministic `a9a53425350b`, not `time()`/random/request-time based;
- no parent/vendor/plugin file changed;
- `git diff --check` has no new issue in the two changed paths.

Do not integrate before runtime acceptance.

---

## 8. Guarded staging deployment boundary

This prerequisite is source/asset-only and is not expected to write DB/schema/options/business state. A new DB backup is not required solely for this task.

Before feature deploy:

- acquire the existing selective-runner shared lock nonblocking; if a natural cycle owns it, wait only a bounded interval or STOP; never kill the scheduler;
- capture sanitized T0 evidence sufficient to prove accepted business/cron/Action Scheduler/mail/transport state;
- require staging identity, `DISABLE_WP_CRON=true`, mail safety, WP HTTP protection and payment protections as applicable;
- do not manually run the selective runner, any owned cron hook, broad WP-Cron, continuation runner, Action Scheduler queue/action, real mail, real payment/refund, or external WordPress HTTP transport.

Deploy only through:

`deployment/deploy-staging.sh changed <scoped-feature-branch>`

Do not manually copy files around the approved deploy boundary.

Superio parent must remain active at `1.3.17` and untouched throughout this task.

---

## 9. Mandatory runtime compatibility acceptance

After feature deploy and before integration, with all guards active:

### A. WordPress enqueue registry

Prove after the relevant enqueue hooks:

- `phone-field-js` is registered/enqueued exactly once by the intended child code;
- its `src` resolves to `/superio-child/assets/js/phone-field.js`;
- dependency list is exactly compatible with the pre-change contract and includes `intl-tel-input-js` as before;
- footer loading is unchanged;
- version is exactly `a9a53425350b`;
- no active enqueue points at `/superio/js/phone-field.js`.

Do not fetch the CDN or external script during the test; inspect registry/contracts under HTTP preemption.

### B. Asset parity

Prove:

- committed child asset and deployed child asset both exist as regular files;
- source/runtime child SHA-256 are both exactly `a9a53425350b8f33ee2a91fe4cc0ce83f66fae626984e0db4371af04622add22`;
- existing parent source/runtime files were not changed by the task;
- the historical parent raw source/runtime EOL difference may still exist and is explicitly acceptable because it was proven EOL-only before mutation.

### C. Registration/dashboard selector compatibility

Using guarded non-destructive source/template/runtime inspection, prove the unchanged child asset still targets the current Raspitajse phone-field surfaces, including where applicable:

- `.phone-with-flags`;
- `.cmb2-id--employer-phone`;
- `#_employer_phone`;
- dashboard body class `page-template-page-dashboard`;
- representative-phone selector `#custom-text-3318838`.

Validate candidate registration, employer registration, candidate dashboard, employer dashboard, and representative-phone surfaces at the strongest safe non-mutating level available.

Do not create real users, submit real forms, send mail, or make external browser/network calls. If Hostinger/LiteSpeed/hcdn blocks frontend HTTP before WordPress, classify that as environment-layer evidence and use guarded internal/template/API inspection instead; do not retry in a loop.

### D. Future-parent independence

Using accepted 2.18 provenance, prove:

- Superio 1.3.37 contains no parent `js/phone-field.js`;
- child runtime now references only the child-owned path;
- replacing the parent with clean 1.3.37 therefore cannot remove the script URL used by the child.

Do not install/deploy Superio 1.3.37 here.

---

## 10. T1, integration, and rollback

After acceptance, capture sanitized T1/final evidence.

Require no task-caused protected-state mutation for at least:

- business aggregates used by recent accepted tasks;
- candidate/employer/job/package/application/message state;
- three owned cron contracts and callback shapes;
- non-allowlisted cron fingerprint;
- Action Scheduler pending state and protected ID `32733` attempts `0`;
- alert/security/SenderPolicy/Commerce contracts materially unrelated to this child source change;
- mail/PHPMailer/SMTP/payment/refund/external WordPress HTTP side-effect counters remain zero.

Legitimate natural clock passage alone is not a failure if no business hook is manually executed and protected state remains reconciled.

If critical acceptance fails:

- restore the exact prior child source/runtime using Git + approved staging deploy;
- keep `origin/staging` at the original baseline;
- prove rollback parity/protected state;
- report PARTIAL/FAIL and STOP.

If all acceptance passes:

- fast-forward `staging` to the accepted scoped feature commit only;
- use the approved deploy path as required so `origin/staging`, source HEAD, deploy marker, runtime child tree and Communications manifest are coherent;
- primary staging worktree ends clean.

No force push, history rewrite, unrelated merge, or production operation.

---

## 11. PASS criteria

PASS requires all of the following:

1. source/runtime parent `phone-field.js` mismatch is conclusively proven `EOL_ONLY_RUNTIME_CRLF_VARIANT` with normalized runtime bytes exactly equal to tracked source;
2. if that EOL-only proof does not pass, no mutation occurs and the task does not PASS;
3. only the two authorized child paths change;
4. child asset is exact canonical tracked source bytes/hash `a9a534...add22`;
5. `phone-field-js` loads from the child-owned path with unchanged dependency/footer behavior;
6. no active parent-path phone-field enqueue remains;
7. per-request `time()` versioning for this script is replaced by deterministic `a9a53425350b`;
8. current registration/dashboard selector contract remains compatible at the bounded non-destructive level;
9. future Superio 1.3.37 deletion of parent `js/phone-field.js` can no longer break the child enqueue;
10. current Superio parent remains `1.3.17` and unchanged;
11. Apus Framework, Slider Revolution, WPJBP, Paid Listings, WP Private Message, WooCommerce, WordPress core and Elementor remain unchanged;
12. staging DB/business/cron/Action Scheduler state has no task-caused mutation;
13. mail/network/payment side effects remain zero;
14. Hostinger scheduler unchanged;
15. production untouched;
16. final `origin/staging`, source HEAD, deploy marker and runtime accepted child files are coherent and clean.

Final decision must be one of:

- `PRE_UPGRADE_CHILD_COMPATIBILITY_GATE_PASSED`
- `BLOCKED_PHONE_FIELD_MATERIAL_DIVERGENCE`
- `BLOCKED_PHONE_FIELD_PROVENANCE_CHANGED`
- `BLOCKED_PRE_UPGRADE_CHILD_COMPATIBILITY`
- or a narrower truthful failure classification.

---

## 12. Exactly one proposed next task

If PASS, propose exactly one next task:

**Zadatak 2.21 — Controlled Superio 1.3.37 + Apus Framework 2.5 + Slider Revolution 6.7.41 staging upgrade with pinned provenance, downgrade prevention, secure backup, rollback, and full compatibility acceptance.**

Do not create or execute 2.21 automatically.

If blocked, propose only the narrow prerequisite required by the proven blocker.

---

## 13. Report and stop

Publish the final report through `codex-reports` using the established workflow.

The report must include:

- exact initial/final staging SHAs and deploy marker;
- raw source/runtime parent asset sizes/hashes and line-ending statistics;
- normalization algorithm and normalized comparison result/hash;
- EOL-only or material-divergence classification;
- exact changed child paths and feature/integrated commit if any;
- child asset source/Git-round-trip/runtime SHA-256;
- enqueue source/dependency/version/footer evidence;
- registration/dashboard compatibility evidence and any environment limitation;
- T0/T1 protected-state reconciliation;
- mail/network/payment counters;
- rollback status;
- production touched: YES/NO;
- exactly one proposed next task.

Do not include PII, secrets, raw DB rows, mail bodies, private recipient data, or credentials.

Then STOP.