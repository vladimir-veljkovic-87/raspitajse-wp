# Zadatak 2.32 — Final atomic recovery and completion of Zadatak 2.25

Status: READY
Baseline: 29cadafbe9d88d256807a682c3926f8d8540bff2
Previous task: 2.31
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Complete Zadatak 2.25 in one final fail-closed atomic operation using the already prepared combined candidate and the renderer harness proven by Zadatak 2.31.

This is an execution/finalization task, not another diagnostic task. Do not redesign or reconstruct the smoke harness. Do not create another numbered follow-up from this task.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

PASS requires the combined source to be fast-forward integrated into `staging`, the actual live staging files to be reconciled to that integrated commit despite the known marker/content split, all guarded acceptance to pass, and exactly one final completion report for Zadatak 2.25 to be published.

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and all referenced feature refs. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read the PASS reports for 2.27, 2.28, 2.29 and 2.31 and the latest failed 2.25 atomic evidence.

`codex-tasks` is read-only to the executor. Production is forbidden.

## Authoritative prepared inputs

Use these inputs; do not regenerate them:

- combined final candidate branch: `feature/z2-25-final-atomic`;
- combined candidate SHA: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- current `origin/staging`: `29cadafbe9d88d256807a682c3926f8d8540bff2`;
- candidate is a verified four-commit fast-forward descendant of that staging SHA;
- corrected authoritative smoke harness:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/integration-smoke.php`;
- required corrected harness SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- authoritative finalizer SHA-256:
  `b450ad7a485ece90d84df198307be61a44ca803b36ba0e98ee55c3fcb7eb9485`;
- 2.31 evidence root:
  `/tmp/raspitajse-task-2.31-admin-renderer/evidence`.

If either authoritative script hash differs, STOP. Do not patch, reconstruct or reinterpret it in this task.

## Accounted recovery state

The following split state is expected:

- live deploy marker: `73f43334ecc14adcf5704fcead243a67bca4c71f`;
- live async-dispatch and targeted DROP guards correspond to the accepted 2.29 runtime;
- the three original 2.25 live files remain in the accepted recovery form rather than the source contents already present in Git staging;
- live child `functions.php` includes the approved `manage_options` admin capability hunk while preserving the recovery/T0 commerce content;
- staging user 141 remains Administrator with `manage_options`;
- protected business fingerprint:
  `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint:
  `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint:
  `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending Action Scheduler count: 6;
- pending fingerprint:
  `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- action 32733: `complete`, attempts 1.

Recover the accepted exact live file hashes from the 2.27–2.31 reports/evidence and verify them directly. Any unaccounted difference is a blocker.

Do not apply the obsolete original 2.25 assumptions of seven pending actions, action 32733 pending/attempts zero, WooCommerce 9.5.4, or an uncorrected renderer harness. Current WooCommerce 11.1.0 is the accepted staging runtime for this recovery.

## Static preparation before lock

Perform only bounded read-only preparation:

1. Verify the candidate SHA, clean worktree, direct fast-forward ancestry, exact candidate diff and expected owned paths.
2. Verify the candidate contains:
   - the complete already-integrated 2.25 commerce implementation;
   - the accepted async-dispatch guard;
   - the accepted targeted DROP guard;
   - the one approved `manage_options` capability change.
3. Verify the corrected harness and finalizer hashes above.
4. PHP/shell lint the existing authoritative artifacts once.
5. Prepare an explicit live reconciliation allowlist and exact preflight rollback copies.

No user pause, confirmation request or context boundary is allowed after lock acquisition.

## One atomic locked operation

One process must hold both staging OS flock locks continuously through preflight, recovery control smoke, Git integration, live reconciliation, feature acceptance, evidence publication and final report publication.

### Phase A — direct preflight

Directly verify all accounted recovery-state hashes and fingerprints. Verify both scheduler guards at runtime and prove the broad async dispatcher is absent at shutdown. Verify user 141 admin capability. Capture exact live rollback copies and hashes.

### Phase B — corrected recovery control smoke

While the recovery code is still live, run the exact corrected harness with SHA-256 `88dd1d...`.

It must prove:

- `/packages/`: HTTP 200 and expected template;
- `/checkout/`: HTTP 200 and expected template;
- real `WC_Meta_Box_Order_Data::output()`: entered exactly once and completed;
- meaningful order markup and the owned billing hook exactly once;
- in-memory order remains ID 0 and is never saved;
- canonical WooCommerce admin helper and HPOS screen context from 2.31;
- consistent `HTTP_HOST` and `SERVER_NAME`;
- zero unexpected stderr or structured PHP diagnostics;
- all mail, PHPMailer, SMTP, HTTP transport, payment/refund, broad-runner and protected-action counters remain zero;
- all protected fingerprints remain exact.

STOP before integration if this control fails. Do not modify the harness.

### Phase C — integrate

Only after Phase B passes:

1. Fast-forward local `staging` and `origin/staging` to exactly `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`.
2. Do not merge with a merge commit, rebase, reset, force-push or rewrite history.
3. Verify local and remote staging resolve to the same exact SHA.

### Phase D — reconcile the actual live tree

The existing deploy marker cannot be used as proof that live contents match Git. Do not derive the deploy set only from the Git difference between the old staging ref, marker and candidate.

Compare actual live file hashes with the integrated candidate blobs and reconcile every mismatched approved path required for the final state, including the three historically rollbacked 2.25 files:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`;
- `wp-content/mu-plugins/raspitajse-staging-action-scheduler-drop-guard.php`.

Use the accepted deployment workflow, but force real hash reconciliation for this explicit allowlist. Do not copy unrelated vendor/core files. Do not treat mtimes as content changes.

After deployment prove:

- all five live files byte-match the corresponding integrated candidate blobs;
- no other live path changed;
- PHP lint passes for PHP paths;
- deploy marker is exactly the integrated SHA.

### Phase E — final acceptance

Run the established 2.25 static and guarded runtime acceptance and the exact same corrected three-part smoke used in Phase B.

Prove at minimum:

- all established 2.25 commerce/package/checkout/HPOS/activation/immutability/DST/quota/RSD/invoice/purchase-transport/candidate-model/duplicate-hook/PII assertions pass;
- real admin renderer is entered and completes;
- both scheduler guards remain active;
- pending remains 6 with exact baseline fingerprint;
- claims remain 0;
- action 32733 remains complete/attempts 1;
- business, owned-contract and non-allowlisted cron fingerprints remain exact;
- candidate/job communications, employer/candidate retirement and owned scheduler contracts remain unchanged;
- no fixture remains;
- mail, PHPMailer, SMTP, external HTTP transport, payment/refund, broad scheduler runner, protected-action execution and production operations are all zero;
- local staging, `origin/staging`, live deployed files and marker converge on the integrated candidate SHA.

## Failure and rollback contract

If any failure occurs before integration, leave Git and live state unchanged and publish a concise BLOCKED report.

If a failure occurs after integration:

- do not rewrite Git history or force-push;
- restore the exact five-file preflight live recovery snapshot;
- restore the preflight marker value and publish an explicit recovery manifest describing the known split state;
- verify rollback hashes and all protected fingerprints;
- save evidence and publish BLOCKED.

Never claim rollback without byte-level proof.

## Reporting

On PASS publish exactly one final completion report with:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.32`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- integrated/deployed SHA;
- recovery control and feature smoke results;
- renderer invocation proof;
- static/runtime acceptance totals;
- final fingerprints and side-effect counters;
- exact deployed path reconciliation;
- confirmation that prior recovery guards/candidate cleanup/DROP remediation remain effective;
- production untouched.

The diagnostic 2.27–2.31 reports are not final 2.25 completion reports.

Release both locks only after evidence and the final report are durably published. Then STOP.
