# Zadatak 2.36 — Normalize the complete already-integrated recovery contract and finish Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.35
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Finish Zadatak 2.25 without another one-gate-at-a-time loop.

Before execution, audit the entire proven finalizer for every stale assumption from the former pre-integration state. Derive one immutable recovery-aware finalizer whose complete contract matches the already integrated Git target and the currently deployed recovery commit. Validate the whole derived contract before acquiring locks, then execute it once.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

No application code changes, Git integration, Git push or new diagnostic task are authorized.

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read PASS reports 2.31/2.33 and BLOCKED reports 2.32/2.34/2.35.

Production is forbidden. `codex-tasks` is read-only to the executor.

## Exact current state

- integrated target and `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- current live recovery commit and marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- five live allowlisted files byte-match recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS: 6, fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- AS 32733: complete, attempts 1;
- both scheduler guards and administrator capability active.

Any unaccounted runtime difference is a blocker.

## Proven artifact chain

- original corrected finalizer SHA-256:
  `d0c53eef519883fd4c39d893e60bf146933509b421e1c974ee4766382ab099df`;
- Task 2.35 already-integrated derived finalizer:
  `/tmp/raspitajse-task-2.35-final.sdtptr/finalize-already-integrated.sh`;
- required Task 2.35 derived SHA-256:
  `7cb893991dc480be02eacc6d5871edf2f40600fc2c879c2d83de43354aed97ee`;
- corrected smoke:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/integration-smoke.php`;
- required smoke SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- static harness SHA-256:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`.

Verify this chain before deriving anything. Never edit these source artifacts in place.

## Complete pre-lock contract audit

Copy the exact Task 2.35 derived finalizer to a new private task directory as `finalize-recovery-aware.sh`.

Before patching, inspect the entire script—not only the first failing line—and produce a contract table for every occurrence or use of:

- baseline, feature, predeploy, recovery, target and deploy SHA variables;
- local/remote branch assertions;
- live marker assertions and marker writes;
- live-file source and rollback paths;
- evidence directory creation/rotation;
- report-count assertions;
- pending count/fingerprint and AS 32733 expectations;
- static/runtime/smoke artifact hashes;
- integration/push functions or calls;
- failure rollback destinations.

Classify every entry as already correct, stale pre-integration assumption, or unrelated invariant.

Do not enter the lock until this complete table proves there are no undiscovered stale state gates.

## Authorized semantic normalization

Apply only the following recovery-contract normalization to the copied finalizer:

1. Git target:
   - `origin/staging` and deployment target are `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
   - integration and push remain absent;
   - Git operations during execution remain read-only.

2. Recovery source:
   - every preflight marker assertion representing the currently deployed state must expect `e9d58c62713fbed895a0d174b3fbeb33ee48c957`, not obsolete `73f43334...`;
   - every five-file preflight/rollback source representing the current live recovery state must use the verified recovery commit/snapshot `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
   - successful deployment marker target remains `ec97a6f...`;
   - failure rollback marker becomes `e9d58c...`.

3. Protected runtime:
   - pending AS expectation is 6 with fingerprint `3c7068c5...`;
   - claims expectation is 0;
   - AS 32733 expectation is complete/attempts 1;
   - business/owned-contract/cron expectations are the exact values stated above.

4. Proven harness corrections:
   - retain scalar integer validation for `checks: 27`;
   - retain the exact corrected smoke/static artifact hashes;
   - do not modify either harness.

5. Evidence:
   - preserve every existing evidence directory;
   - before lock, atomically move the prior active `atomic-evidence` directory, if the finalizer requires a fresh path, to a unique timestamped sibling archive;
   - verify the archived directory and any existing manifest remain readable;
   - authorize creation of exactly one fresh `atomic-evidence` directory for this invocation;
   - never delete or overwrite prior evidence.

Only variable values, assertions, phase routing and evidence-path initialization necessary for these five semantic normalizations may change. All acceptance, rollback, side-effect and fail-closed behavior must remain intact.

## Derived-artifact validation

Before lock:

- mode 0700;
- `bash -n` PASS;
- save full unified diff against the Task 2.35 derived source;
- calculate and record SHA-256;
- inspect all executable Git commands and prove there is no push, merge, rebase, cherry-pick, reset, checkout/switch, update-ref, commit or branch mutation;
- prove all contract-table entries now match the exact current state or target;
- prove no obsolete state SHA remains in an executable assertion/path except in comments or archived evidence labels;
- verify the derived hash again immediately before execution.

The derived finalizer becomes immutable after validation.

## One atomic execution

One process must hold both staging OS flock locks continuously through direct preflight, recovery control smoke, live reconciliation, final acceptance, evidence/report publication and release.

### Recovery control

Directly verify the exact state above and run the corrected smoke against current recovery live code:

- `/packages/` PASS/200;
- `/checkout/` PASS/200;
- real WooCommerce admin renderer entered and completed once;
- owned billing hook once;
- in-memory order ID 0, never saved;
- zero diagnostics and all side-effect counters zero;
- fingerprints unchanged.

### Live reconciliation

Deploy from exact integrated target `ec97a6f...`. Do not use marker/Git delta alone. Hash-reconcile these five paths:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`;
- `wp-content/mu-plugins/raspitajse-staging-action-scheduler-drop-guard.php`.

Prove all five match target blobs, no unrelated file changed, PHP lint passes and marker becomes exact target SHA.

### Final acceptance

Run the authoritative static suite through the corrected caller and require one JSON document, 27 checks, zero failures, exit 0, zero network/mail/payment calls and empty unexpected stderr.

Run the established guarded runtime acceptance and the same corrected feature smoke.

Require:

- all established 2.25 commerce/package/checkout/HPOS/activation/immutability/DST/quota/RSD/invoice/purchase-transport/candidate-model/duplicate-hook/PII assertions pass;
- real renderer completes;
- no fixture remains;
- all exact fingerprints and AS state remain unchanged;
- both scheduler guards and admin capability remain active;
- all mail/SMTP/HTTP/payment/refund/broad-runner/protected-action/production counters remain zero;
- `origin/staging`, five live files and marker converge on `ec97a6f...`.

## Failure and rollback

Before deployment: preserve recovery state.

After deployment: restore the exact five-file recovery snapshot and marker `e9d58c...`, prove byte hashes and fingerprints, do not alter Git, preserve all evidence, and publish one precise BLOCKED report.

No retry or fix-forward in the locked process.

## Reporting

On PASS publish exactly one final report:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.36`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- integrated/deployed SHA `ec97a6f...`;
- derived finalizer diff/path/hash and complete contract audit;
- control/feature smoke, renderer, static/runtime results;
- final fingerprints/counters and five-path convergence;
- production untouched.

Release locks only after durable evidence and report publication. Then STOP.
