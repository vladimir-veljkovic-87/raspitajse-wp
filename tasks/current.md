# Zadatak 2.24 — Diagnose the Commit B deploy-parity failure correctly, fix only the parity measurement/transfer contract if needed, and complete the Superio vendor upgrade in one bounded transaction

Status: READY
Baseline: 6d2e78ad6f866b486c303bfda050a4afec5f90e8
Previous task: 2.23
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `origin/feature/z2-21-superio-upgrade`.

Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full before any mutation, backup, deploy, WordPress bootstrap, or integration. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.23 PARTIAL report in full. Read 2.21/2.20/2.18 only where needed for accepted Commit B provenance, child compatibility, backup/rollback, and acceptance contracts.

Verify:

- `origin/staging` is exactly `6d2e78ad6f866b486c303bfda050a4afec5f90e8`;
- live staging deploy marker is the same SHA;
- primary staging worktree is clean/on `staging`;
- `origin/feature/z2-21-superio-upgrade` is exactly `bb34d768d4edec8050975405874197dd1269d28f`;
- Commit B parent is exactly the staging baseline;
- Commit A→B changes only the three authorized roots:
  - `wp-content/themes/superio/**`
  - `wp-content/plugins/apus-framework/**`
  - `wp-content/plugins/revslider/**`.

If any of these differ, STOP. Do not rebase, rebuild, amend, or replace Commit B.

Execute only Zadatak 2.24. Publish the final report through `codex-reports` and STOP.

---

## Why this task is different

Do not repeat the previous task-fragmentation pattern.

The goal is to resolve the actual deploy-parity question and, if it is proven to be a measurement-contract problem rather than a real file-byte divergence, continue directly through the full upgrade acceptance and integration in this same task.

Do not create a separate diagnostic task followed by another retry task unless a real unresolved blocker remains.

Accepted facts from 2.23:

- ambient MariaDB auth works;
- full logical backup succeeded with 84/84 table parity;
- rollback snapshots and protected-state capture work;
- Commit B changed deploy completed;
- counts and modes matched for all three target roots;
- aggregate source/runtime byte-tree hashes differed for all three roots;
- no target-vendor WordPress bootstrap occurred before rollback;
- rollback restored exact T0 state;
- staging remains safely at Commit A.

Because all three roots showed aggregate hash mismatch while file counts and modes matched, first determine whether the prior tree-hash comparison itself was root/path/algorithm dependent before assuming rsync changed bytes.

---

## Phase 1 — Read-only forensic parity proof before touching staging runtime

Do not deploy Commit B yet.

Build deterministic inventories for each of the three Commit B source roots directly from the exact Git commit bytes. The canonical per-file identity format is:

`relative_path_from_root + NUL + sha256(file_bytes) + LF`

Rules:

- relative paths must start below the compared root and must not include repository absolute paths, staging target absolute paths, temporary directory names, branch/worktree names, or root-specific prefixes;
- include regular files only in the byte inventory;
- sort bytewise by relative path;
- separately record regular-file executable/non-executable mode map using the same root-relative paths;
- do not use mtimes, inode numbers, owners, absolute paths, directory metadata, filesystem allocation, or host-specific metadata in byte parity.

Then perform a private, non-web, no-WordPress transfer rehearsal using the exact file-copy semantics of `deployment/deploy-staging.sh` into disposable directories. Compare source→rehearsal-target with BOTH:

1. exact per-file map diff;
2. corrected root-relative aggregate digest of that exact map.

Require for all three roots:

- identical file count;
- exact per-file relative path set;
- exact SHA-256 for every file;
- exact mode map;
- corrected aggregate digest identical source/target.

If this rehearsal fails for actual file bytes, identify the precise relative paths and classify the transfer defect. You may modify `deployment/deploy-staging.sh` only if a real transfer bug is proven, and only with the smallest staging-only fix required. Test that fix in the disposable rehearsal until exact per-file parity passes. Do not alter vendor payloads.

If rehearsal per-file parity passes but the previous aggregate method would still differ when source and target live under different absolute roots, classify the prior failure as a measurement bug. In that case, do not modify deploy tooling merely to satisfy a bad hash; use the corrected root-relative parity contract for the live transaction below.

If the mismatch cannot be explained or corrected safely without altering vendor bytes, STOP with evidence and no live vendor deploy.

---

## Phase 2 — Backup, lock, snapshots, T0

Only after Phase 1 proves an exact source→target parity method:

1. verify ambient MariaDB auth with one bounded `SELECT 1`;
2. resolve only DB_NAME through the previously accepted narrow mechanism; never inspect/copy auth configuration;
3. create a fresh full logical dump using the successful 2.23 ambient-auth `mariadb-dump` method;
4. require exit 0, non-empty dump, clean stderr, completion marker, SHA-256, 84/84 exact live/dump table-set parity;
5. retain the dump through acceptance/rollback;
6. acquire the existing selective-runner shared lock nonblocking;
7. create exact rollback snapshots of current runtime Superio/Apus/RevSlider preserving bytes and modes;
8. capture fresh sanitized T0 protected state using the accepted 2.23 projection.

No production access. No credential inspection. No broad cron/AS. No real mail/payment/network transport.

---

## Phase 3 — Exact Commit B live deploy and corrected immediate parity gate

Deploy the unchanged existing feature tip only through:

`deployment/deploy-staging.sh changed feature/z2-21-superio-upgrade`

If Phase 1 required an authorized minimal deploy-script fix, that fix must first be separately reviewed, committed from current staging, integrated to staging, and the existing Commit B relationship must be preserved without rewriting vendor bytes. If preserving the exact existing Commit B is impossible, STOP rather than rewriting history silently.

Immediately after deploy and BEFORE WordPress bootstrap, run the corrected parity contract from Phase 1 against each live target root.

PASS requires:

- exact relative file path set;
- exact SHA-256 for every regular file;
- exact executable-mode map;
- exact corrected root-relative aggregate digest;
- expected file counts;
- parent `js/phone-field.js` absent;
- child-owned phone asset unchanged.

Do not fail merely because an aggregate hash implementation includes different absolute root paths. The gate is actual root-relative per-file byte identity.

If even one actual file differs, capture a bounded list of differing relative paths, sizes, source/runtime hashes and EOL classification where relevant, then rollback immediately before any target-vendor bootstrap.

---

## Phase 4 — Complete the upgrade acceptance in this same task if live per-file parity passes

Do not stop after proving parity. Continue directly.

Run the same guarded first bootstrap, vendor migration accounting, security acceptance, compatibility acceptance, scheduler/business-state checks, and side-effect checks required by 2.21/2.23, with these target versions:

- Superio `1.3.37`;
- Apus Framework `2.5`;
- Slider Revolution `6.7.41`;
- WPJBP remains `1.2.86`;
- Paid Listings remains `1.0.19`;
- WP Private Message remains `1.0.7`;
- WooCommerce remains `9.5.4`;
- Elementor remains `3.25.11`;
- WordPress remains `6.6.7`;
- child remains active and unchanged.

Mandatory checks include, at minimum:

- no fatal/warning regression attributable to the upgraded vendor stack in bounded acceptance;
- child `phone-field-js` loads exactly once from child path with canonical hash/version and no parent phone-field dependency;
- candidate/employer registration and dashboard template/field contracts load;
- package child overrides and canonical 30-day entitlement policy harness pass;
- job search/detail/submit/edit/profile/application template contracts load without real mutations;
- candidate→job owned evaluator remains authoritative;
- employer→candidate remains retired;
- candidate age auto-expiry remains disabled;
- owned job expiry/pre-expiry callbacks remain authoritative;
- exact three owned hourly scheduler hooks remain unchanged; continuation absent;
- protected AS ID 32733 remains pending/attempts 0;
- Raspitajse Commerce employer lookup/HPOS declarations remain compatible;
- no WPJBP/Paid/WP Private Message downgrade/reinstall;
- no TGMPA/updater/theme-switch/deactivate-reactivate path executed;
- Apus 2.5 remediation boundary for prior arbitrary-option-update issue remains present;
- RevSlider exact 6.7.41 active copy only;
- bounded WPJBP registration privilege-escalation regression harness passes;
- SenderPolicy/Transport authority unchanged;
- protected business fingerprint unchanged except explicitly justified vendor-owned bookkeeping only;
- mail/SMTP/payment/refund actual sends/execution zero;
- actual external WordPress network zero unless guard-intercepted and blocked before transport.

Known Hostinger/LiteSpeed HTTP 403 before WordPress is not a product regression; use internal/template/runtime acceptance and record the limitation once.

---

## Phase 5 — Integration or rollback

If every critical gate passes:

1. fast-forward `staging` to the exact accepted vendor commit path without rebuilding vendor payloads;
2. deploy from final `staging` so source HEAD / `origin/staging` / deploy marker converge;
3. rerun corrected root-relative per-file parity on all three target roots;
4. capture final protected-state projection;
5. verify worktree clean, protected scheduler/business state intact, target versions exact;
6. remove DB backup and rollback snapshots only after final PASS proof;
7. report `PASS` with classification `SUPERIO_VENDOR_STACK_UPGRADE_ACCEPTED`.

If any critical gate fails after live deploy:

1. do not integrate Commit B;
2. return tracked paths toward Commit A through the approved deploy contract;
3. restore exact T0 filesystem snapshots under the lock;
4. restore DB only if task-caused vendor DB state changed;
5. prove exact T0 runtime/protected-state restoration;
6. clean temporary backup/snapshots only after rollback proof;
7. report PARTIAL with one precise blocker.

Never leave staging in a mixed or partially upgraded state.

---

## Source-mutation scope

Preferred source mutation count: ZERO.

Only if Phase 1 proves a real bug in `deployment/deploy-staging.sh`, one minimal staging-only fix to that file is authorized. No vendor source byte changes are authorized. No child, WPJBP, Paid Listings, WP Private Message, WooCommerce, Elementor, WordPress core, Raspitajse business logic, scheduler configuration, `.gitattributes`, or `.gitignore` changes are authorized.

Do not create another task merely because the old aggregate hash was wrong. If actual per-file parity is proven and all acceptance passes, finish the upgrade here.

Production remains FORBIDDEN throughout.