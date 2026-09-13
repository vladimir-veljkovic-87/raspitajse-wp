# Zadatak 2.37 — Execute the fully normalized recovery finalizer and complete Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.36
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Apply all already-proven recovery-contract normalizations plus one narrow report-helper quiet-mode correction, validate the complete orchestration contract before lock entry, and finish Zadatak 2.25 in this task.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

No application code changes, Git integration, Git push to application branches, new application commit, or further diagnostic task are authorized.

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md` and `tasks/README.md` in full. Read PASS reports 2.31/2.33 and BLOCKED reports 2.32/2.34/2.35/2.36, including the complete 2.36 contract audit.

Production is forbidden. `codex-tasks` is read-only to the executor.

## Exact state

- integrated target / `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live recovery commit and marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- five live allowlisted files match recovery commit `e9d58c...`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS: 6 / `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0; AS 32733: complete/attempts 1;
- scheduler guards and administrator capability active.

STOP on an unaccounted difference.

## Proven artifacts

- Task 2.35 derived finalizer:
  `/tmp/raspitajse-task-2.35-final.sdtptr/finalize-already-integrated.sh`;
- required SHA-256:
  `7cb893991dc480be02eacc6d5871edf2f40600fc2c879c2d83de43354aed97ee`;
- complete 2.36 contract audit:
  `/tmp/raspitajse-task-2.36-contract-audit.FeFK8J/contract-audit.md`;
- corrected smoke SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- static harness SHA-256:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`;
- report helper source: `tools/codex-report.sh` from exact target commit `ec97a6f...`;
- report helper Git blob SHA: `1fc9848edf873473d9963764285b510c428652fb`.

Do not edit any source artifact in place.

## Pre-lock derivation

Create one private task directory. Derive two immutable artifacts there.

### A. Recovery-aware finalizer

Copy the exact Task 2.35 derived finalizer and apply all recovery normalizations authorized by 2.36:

- target and `origin/staging` remain `ec97a6f...`;
- no integration or application push;
- current live/preflight/rollback marker and five-file recovery source are `e9d58c...`, replacing obsolete `73f43334...` wherever they represent deployed preflight/rollback state;
- successful deploy marker target remains `ec97a6f...`;
- pending count/fingerprint, claims and AS 32733 use the exact current values above;
- scalar `checks: 27` correction remains;
- previous evidence is rotated to a unique sibling archive and never deleted or overwritten;
- exactly one fresh atomic evidence directory is created.

Apply the complete 2.36 audit table, not only the first occurrence. Prove no executable stale-state assertion remains.

### B. Quiet report helper

Copy `tools/codex-report.sh` from exact target commit to a private `codex-report-quiet.sh`.

The only permitted helper changes are adding Git quiet flags to the four successful operational commands:

- `git fetch --quiet ...`;
- `git worktree add --quiet ...`;
- `git ... commit --quiet ...`;
- `git ... push --quiet ...`.

Preserve:

- `set -Eeuo pipefail`;
- every argument, secret scan, source-branch validation and cleanup;
- commit/push behavior to `codex-reports`;
- exit codes;
- stdout publication receipt;
- error stderr;
- all failure paths.

Do not redirect or suppress stderr globally. Git `--quiet` may suppress successful progress only; genuine Git errors must still produce nonzero exit and stderr.

Change the derived finalizer to invoke this exact private quiet helper. Keep the existing requirement that helper exit code is 0, stderr is empty, the expected report path/commit receipt exists, remote report count is exactly one, and the published report is readable from `origin/codex-reports`.

## Complete static validation

Before lock:

- verify source hashes/blob;
- modes 0700;
- `bash -n` both derived files;
- save unified diffs against both sources;
- compute and record both derived SHA-256 values;
- prove finalizer diff contains only the complete 2.36 recovery normalization and helper-path substitution;
- prove helper diff contains only the four quiet flags;
- prove no application Git mutation command exists in the finalizer;
- prove the report helper still pushes only to `codex-reports`;
- use the 2.36 contract table to verify every state, marker, evidence, report and rollback entry;
- verify both hashes again immediately before execution.

The derived artifacts become immutable after validation.

## One atomic execution

One process holds both staging OS locks continuously through all phases and final report publication. No user pause after lock entry.

### Recovery control

Directly verify exact state/fingerprints and run corrected control smoke:

- packages PASS/200;
- checkout PASS/200;
- real WooCommerce admin renderer entered/completed once;
- owned billing hook once;
- in-memory order ID 0 and not persisted;
- zero diagnostics and all side-effect counters zero.

### Live reconciliation

Deploy from exact integrated target `ec97a6f...` by actual hash comparison, not marker/Git delta, for:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`;
- `wp-content/mu-plugins/raspitajse-staging-action-scheduler-drop-guard.php`.

Prove all five target hashes, no unrelated change, PHP lint, and marker `ec97a6f...`.

### Final acceptance

Require:

- static suite: one JSON, 27 checks, zero failures, exit 0, empty unexpected stderr;
- complete guarded runtime 2.25 acceptance;
- identical corrected feature smoke including real renderer;
- no remaining fixtures;
- exact business/owned-contract/cron/pending fingerprints;
- claims 0 and AS 32733 complete/1;
- guards and admin capability active;
- mail/PHPMailer/SMTP/external HTTP/payment/refund/broad-runner/protected-action/production counters all zero;
- `origin/staging`, five live files and marker converge on `ec97a6f...`.

## Failure and rollback

Before deploy: preserve recovery state.

After deploy: restore five recovery files and marker `e9d58c...`, prove hashes/fingerprints, do not change Git, preserve evidence and publish one BLOCKED report.

No retry or fix-forward in the locked process.

## Final report

On PASS publish exactly one report through the private quiet helper:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.37`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- target SHA, derived artifact hashes/diffs;
- all control/static/runtime/smoke evidence;
- final fingerprints, counters and five-path convergence;
- production untouched.

Release locks only after report is remotely verified. Then STOP.
