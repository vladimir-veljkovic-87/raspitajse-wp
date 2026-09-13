# Zadatak 2.35 — Execute the already-integrated final recovery path and complete Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.34
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Correct the orchestration-contract mismatch identified by Zadatak 2.34 and complete the final live reconciliation and acceptance for Zadatak 2.25 in this same task.

The application code is already integrated. This task must not perform any Git integration or push. It may derive one immutable already-integrated finalizer from the exact proven finalizer using only the narrowly authorized orchestration diff below, validate it before lock entry, and execute it once.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

Do not create another diagnostic task or another application branch.

## Mandatory preamble and expected state

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read PASS reports 2.31/2.33 and BLOCKED reports 2.32/2.34.

Expected state:

- `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live recovery commit and marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- all five relevant live files match recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS: 6, fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- action 32733: complete, attempts 1;
- both scheduler guards and admin capability active.

STOP on any unaccounted difference.

Production is forbidden. `codex-tasks` is read-only to the executor.

## Proven source artifacts

- source finalizer:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/finalize.sh`;
- required source finalizer SHA-256:
  `d0c53eef519883fd4c39d893e60bf146933509b421e1c974ee4766382ab099df`;
- corrected integration smoke:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/integration-smoke.php`;
- smoke SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- static harness SHA-256:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`.

The source finalizer is not executable for the current state because it expects the pre-integration staging SHA and performs a push. Do not execute or modify it in place.

## Authorized already-integrated finalizer derivation

Before lock entry:

1. Copy the exact source finalizer to a new private task-specific path named `finalize-already-integrated.sh`.
2. Preserve the source file unchanged.
3. Apply only this semantic diff to the copy:
   - change the required `origin/staging` precondition from `29cadafbe9d88d256807a682c3926f8d8540bff2` to `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
   - replace the `integrate_push` phase/call with read-only assertions that local target source and `origin/staging` both resolve to exactly `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
   - proceed directly from passed recovery control smoke to live hash reconciliation/deploy;
   - retain the corrected scalar check `(int)($x["checks"] ?? 0) !== 27`;
   - update phase labels only where required to describe the already-integrated path.
4. No other control flow, gate, rollback, deployment, acceptance or reporting behavior may change.

Materialize the copy with mode 0700, run `bash -n`, save a unified diff against the source and calculate its SHA-256.

Fail closed unless static inspection proves the derived finalizer contains no executable Git mutation command, including:

- `git push`;
- merge, rebase, cherry-pick, reset, checkout/switch or update-ref mutation;
- branch creation/deletion;
- commit creation.

Read-only `git rev-parse`, `git diff`, `git status`, `git show` and ancestry verification remain permitted.

Also fail closed if the diff touches anything outside the authorized semantic changes above.

The derived artifact becomes immutable after this validation. Record its path and SHA-256 before lock entry and verify the same hash immediately before execution.

## One uninterrupted atomic operation

One process must hold both staging OS flock locks continuously through preflight, control smoke, live reconciliation, final acceptance, evidence persistence and final report publication. No user pause is permitted after lock entry.

### Phase A — direct recovery preflight

Verify all expected Git/live hashes, protected fingerprints, guard state, admin capability and side-effect counters directly.

Verify the immutable derived finalizer hash and that `origin/staging` is already the exact target. No Git mutation is permitted.

### Phase B — corrected recovery control smoke

Run the exact corrected smoke before live deployment. Require:

- `/packages/` HTTP 200 and expected template;
- `/checkout/` HTTP 200 and expected template;
- real WooCommerce admin renderer entered once and completed;
- meaningful markup and owned billing hook once;
- in-memory order ID 0 and no persistence;
- zero unexpected PHP/stderr diagnostics;
- all transport/payment/scheduler side-effect counters zero;
- every protected fingerprint unchanged.

### Phase C — live hash reconciliation

Deploy from exact integrated source SHA `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`.

Do not rely on the current marker or Git delta. Compare actual hashes and reconcile the explicit five paths:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`;
- `wp-content/mu-plugins/raspitajse-staging-action-scheduler-drop-guard.php`.

Prove all five byte-match the target blobs, no unrelated file changed, PHP lint passes, and marker becomes the exact target SHA.

### Phase D — final acceptance

Run the authoritative static acceptance through the corrected caller and require:

- one complete JSON payload;
- 27 checks, zero failures;
- exit code exactly 0;
- zero network/mail/payment calls;
- empty unexpected stderr.

Run the established guarded runtime acceptance and the identical corrected three-part feature smoke.

Require all established 2.25 behavior checks, real renderer completion, exact fixture cleanup, both scheduler guards, admin capability, and:

- business, owned-contract and cron fingerprints unchanged;
- pending AS remains 6 with exact fingerprint;
- claims 0;
- action 32733 complete/attempts 1;
- mail/PHPMailer/SMTP/external HTTP/payment/refund/broad-runner/protected-action counters all 0;
- production operations 0;
- `origin/staging`, all five live files and marker converge on the exact target SHA.

## Failure and rollback

Before deployment: leave recovery state unchanged.

After deployment:

- restore the exact verified five-file recovery snapshot;
- restore marker `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- prove rollback hashes and protected fingerprints;
- do not alter Git;
- publish a single precise BLOCKED report.

No retry or fix-forward is permitted in the locked operation.

## Final report

On PASS publish exactly one report with:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.35`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- integrated/deployed SHA;
- derived finalizer path, authorized diff and SHA-256;
- recovery and feature smoke results;
- renderer proof;
- static/runtime acceptance totals and process exit codes;
- final fingerprints/counters;
- five-path reconciliation;
- production untouched.

Release both locks only after durable evidence and report publication. Then STOP.
