# Zadatak 2.34 — Final live reconciliation and acceptance for Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.33
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Finish Zadatak 2.25 by reconciling the known recovery live state to the already integrated `origin/staging` commit, running the now-proven authoritative acceptance artifacts, and publishing exactly one final Zadatak 2.25 PASS report.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

This is the final execution task. Do not create another harness, branch, commit, merge, cherry-pick, rebase, push or diagnostic subtask.

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read the PASS reports for 2.31 and 2.33 and the BLOCKED 2.32 report.

`codex-tasks` is read-only to the executor. Production is forbidden.

## Authoritative state and artifacts

Expected Git/runtime state:

- `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live recovery commit and deploy marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- all five relevant live files byte-match recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending Action Scheduler: 6, fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- action 32733: complete, attempts 1;
- both scheduler guards active;
- staging administrator 141 retains `manage_options`;
- registration and staging mail safety remain enabled.

Authoritative artifacts:

- corrected integration smoke:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/integration-smoke.php`;
- corrected integration smoke SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- corrected finalizer:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/finalize.sh`;
- corrected finalizer SHA-256:
  `d0c53eef519883fd4c39d893e60bf146933509b421e1c974ee4766382ab099df`;
- authoritative static harness SHA-256:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`.

STOP before lock if any hash or expected state differs. Do not patch or regenerate an artifact in this task.

## Static preparation

Before lock:

1. Verify the exact Git and artifact state above.
2. Confirm `origin/staging` already equals the target. No Git integration or push is required or authorized.
3. Verify the recovery rollback snapshot and explicit five-path reconciliation allowlist.
4. Run only syntax/static validation that does not execute WordPress.
5. Prepare one uninterrupted finalizer invocation. No user pause after lock entry.

## One atomic locked operation

One process must hold both staging OS flock locks continuously through all phases, evidence persistence and final report publication.

### Phase A — recovery preflight and control

Verify all expected live hashes, fingerprints, guards, capabilities and counters directly.

Run the exact corrected recovery smoke before deployment. It must pass:

- `/packages/` HTTP 200 with expected template;
- `/checkout/` HTTP 200 with expected template;
- real WooCommerce admin order renderer entered once and completed;
- meaningful markup and owned billing hook once;
- in-memory order ID 0 and no persistence;
- zero unexpected diagnostics and all side-effect counters zero.

### Phase B — live hash reconciliation

Deploy from exact `origin/staging` SHA `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`.

Do not rely on the marker or Git delta alone. Compare actual live hashes and reconcile the explicit paths:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`;
- `wp-content/mu-plugins/raspitajse-staging-action-scheduler-drop-guard.php`.

Prove every live file byte-matches the corresponding target blob, no unrelated path changed, PHP lint passes, and the deploy marker becomes exactly `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`.

### Phase C — final acceptance

Run the authoritative static acceptance through the corrected caller. It must produce:

- exactly one valid JSON payload;
- 27 checks;
- zero failures;
- zero network/mail/payment calls;
- empty unexpected stderr;
- process exit exactly 0.

Then run the established guarded runtime acceptance and the exact same corrected three-part smoke from Phase A.

Prove:

- all required 2.25 commerce/package/checkout/HPOS/activation/immutability/DST/quota/RSD/invoice/purchase-transport/candidate-model/duplicate-hook/PII assertions pass;
- real admin renderer enters and completes;
- no fixture remains;
- pending AS remains 6 with the exact fingerprint;
- claims remain 0;
- action 32733 remains complete/attempts 1;
- business, owned-contract and non-allowlisted cron fingerprints remain exact;
- both scheduler guards and admin capability remain active;
- mail, PHPMailer, SMTP, external HTTP transport, payment/refund, broad scheduler runner, protected-action execution and production operations are all zero;
- `origin/staging`, all five live files and deploy marker converge on `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`.

## Failure and rollback

On any failure before deployment, leave the recovery state unchanged.

On any failure after deployment:

- restore all five files byte-for-byte from the verified recovery snapshot;
- restore marker `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- verify every rollback hash and protected fingerprint;
- do not alter Git history;
- publish one BLOCKED 2.34 report with the exact failing gate.

Do not retry or fix forward in the locked process.

## Final reporting

On PASS publish exactly one report with:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.34`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- integrated/deployed SHA `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- recovery and feature smoke results;
- renderer invocation proof;
- static and runtime acceptance totals and exit codes;
- final fingerprints and side-effect counters;
- five-path reconciliation proof;
- production untouched.

Release both locks only after evidence and report publication. Then STOP.
