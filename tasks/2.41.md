# Zadatak 2.41 — Persistent operational finalization of Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.40
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Complete the already-proven Zadatak 2.25 finalization without another diagnostic or harness iteration.

Task 2.40 failed only because holder PID 2060260 disappeared after the successful changed-only deploy and before acceptance. Recovery was completed correctly. This task changes only the execution transport: run the existing finalizer in a persistent, session-independent worker that cannot be terminated by a Codex message/turn boundary.

Do not create or modify application code, a test harness, Git history, feature branches, commits, merges, pushes to staging/main, WordPress core, vendor code, database business data, or Action Scheduler KEEP rows.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

## Mandatory preflight

Fetch fresh refs. Read `tasks/current.md`, `tasks/README.md`, and complete reports 2.39 and 2.40.

Expected state:

- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- all five live files and deploy marker equal recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- clean worktree;
- business fingerprint `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending Action Scheduler count 6 and fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- KEEP IDs exactly 32777, 32778, 32779, 32783, 32784, 32848;
- claims 0; AS 32733 complete/attempts 1;
- both scheduler guards active and admin capability preserved;
- both staging locks free;
- no final Zadatak 2.25 PASS report exists.

STOP and publish one precise 2.41 BLOCKED report for any unaccounted difference. Do not repair drift.

## Immutable proven artifacts

Verify before use:

- recovery-aware source finalizer:
  `/tmp/raspitajse-task-2.37-final.RXqbnD/finalize-recovery-aware.sh`;
- source finalizer SHA-256:
  `18f0730a8f9ce3639eedf09df60ec7a00df10130450803824589bff19a1322f4`;
- quiet report helper:
  `/tmp/raspitajse-task-2.37-final.RXqbnD/codex-report-quiet.sh`;
- quiet helper SHA-256:
  `da1da6f089e686137a20ff7e80ee17fac4bdd22e83fcc7b77b25c701f60c6dee`;
- corrected integration smoke SHA-256:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- corrected static harness SHA-256:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`;
- corrected runtime harness:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`;
- runtime harness SHA-256:
  `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`.

Do not edit any immutable source artifact.

## Only authorized preparation

Create one fresh private Task 2.41 directory.

Derive one execution copy of the recovery-aware finalizer. Its only permitted textual differences from the immutable source are:

1. if the source embeds an earlier expected runtime-harness hash, replace only that hash with `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`;
2. replace final report control metadata from Task 2.37 to Task 2.41.

Copy the quiet report helper byte-for-byte.

Save and verify the exact diff and SHA-256 values. Run `bash -n` once on the derived finalizer and helper. Prove there are no application/Git-history mutations and that the helper can push only one report to `codex-reports`.

A minimal operational launcher/status wrapper is allowed, but it must not change finalizer logic. It must:

- start the finalizer with a session-independent mechanism such as `setsid` plus `nohup`, or an equivalent mechanism proven to survive shell/chat turn termination;
- redirect stdin and persist stdout/stderr privately;
- record worker PID, process start identity, session/process-group identity, phase/status, terminal exit code and timestamps;
- use `exec` so the recorded worker becomes the finalizer process;
- never acquire a staging lock itself, run WordPress/WP-CLI, mutate staging, or retry the finalizer;
- never expose secrets or PII.

Before allowing deployment, prove the worker is alive, session-independent, and is the kernel owner of both expected locks. If persistence or exact lock ownership cannot be proven, stop the worker before any deploy and publish BLOCKED.

After launch, Codex may only poll PID/status/log/evidence files read-only. Do not start a second finalizer and do not run parallel staging commands. Do not end the execution while the recorded worker status is RUNNING.

## One persistent atomic worker

The single worker must hold both staging OS locks continuously from preflight through remote report verification and run the derived finalizer exactly once.

Required sequence:

1. Verify exact recovery state and run corrected control smoke:
   - `/packages/` HTTP 200/PASS;
   - `/checkout/` HTTP 200/PASS;
   - real WooCommerce admin renderer entered and completed once;
   - owned billing hook once;
   - no persistent fixture or side effect.

2. Reconcile actual live hashes to integrated target `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`:
   - deploy the three differing 2.25 files;
   - confirm both guard files already match target;
   - all five live files and truthful marker must equal target.

3. Static acceptance:
   - 27/27, zero failures, exit 0;
   - no unexpected stderr or PHP diagnostic.

4. Corrected runtime acceptance using exact harness SHA `1eaf8f57...`:
   - 35/35, zero failures, exit 0;
   - freshly reloaded canonical `job_package`;
   - all fixture posts/orders/meta and exact fixture-created AS actions/logs removed;
   - six KEEP rows/logs unchanged;
   - no callback or queue runner.

5. Identical corrected feature smoke and final invariants:
   - all three smoke targets PASS;
   - business/contract/cron/pending fingerprints exact;
   - pending 6, claims 0, AS 32733 complete/1;
   - both scheduler guards and admin capability intact;
   - mail, PHPMailer, SMTP, unexpected HTTP/external transport, payment, refund, broad runner, protected-action execution and production counters all 0;
   - all five live files and marker equal `ec97a6f...`.

6. Persist evidence and verified SHA-256 manifest.

7. Publish exactly one final report through the unchanged quiet helper:
   - Task: `Zadatak 2.25 — final recovery completion`;
   - Control specification: `Zadatak 2.41`;
   - Result: PASS;
   - classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
   - integrated and deployed SHA `ec97a6f...`;
   - control/static/runtime/feature smoke results;
   - final fingerprints, counters, fixture cleanup and five-file convergence;
   - production untouched.

Verify the remote report path, 40-hex report commit, `origin/codex-reports` equality and non-empty remote report before releasing locks.

## Failure and recovery

No retry and no fix-forward.

If failure occurs before deployment, leave recovery unchanged.

If failure occurs after deployment, the same worker must restore all five live files and marker to exact recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`, verify every protected invariant and counter, preserve evidence, publish exactly one precise 2.41 BLOCKED report, then release locks.

If the persistent worker itself disappears unexpectedly, do not declare PASS and do not start another finalizer. After proving both locks are free, a separate recovery-only process may restore and verify exact `e9d58c...` state without running acceptance or deployment, then publish one BLOCKED report documenting the infrastructure interruption.

Production is forbidden in every path. STOP after the terminal report.
