# Zadatak 2.42 — Exact Elementor diagnostic allowlist refresh and final completion of Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.41
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

In one task, narrowly refresh the already-approved Elementor deprecation signature and immediately execute the complete persistent finalization of Zadatak 2.25.

Do not stop after diagnostic attribution. Do not create another diagnostic task or new test harness. Do not modify or suppress Elementor, WordPress, WooCommerce, vendor code, application code, PHP warning behavior, Git history, database business data, scheduler KEEP rows, or production.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

## Proven Task 2.41 blocker

Task 2.41 proved that the persistent transport works, both locks were kernel-owned by the expected worker, and all recovery invariants passed. It stopped before deployment only because the known approved Elementor `E_DEPRECATED` diagnostic is now emitted from:

- exact file: `wp-content/plugins/elementor/includes/api.php`;
- exact line: `176`;
- exact current diagnostic SHA-256: `5fb64aa49cd5918e5d10b4940b0b8c5eba35a60e61a4c43bf4ff855cf7f6f83d`.

The existing narrow allowlist identifies the earlier canonical source line 164 and its earlier exact hash.

## Mandatory preflight and exact diagnostic proof

Fetch fresh refs. Read `tasks/current.md`, `tasks/README.md`, and complete reports 2.39–2.41.

Before any mutation or lock entry, read only:

- the existing approved diagnostic allowlist/signature used by the immutable finalizer;
- Task 2.41 captured stderr and classification evidence in `/tmp/raspitajse-task-2.41.CKaGfm` and `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/atomic-evidence`;
- the current Elementor source surrounding exact line 176.

Prove all of the following:

1. severity/type is exactly the previously approved `E_DEPRECATED`;
2. normalized message text is byte-identical to the previously approved message;
3. source file path is byte-identical;
4. only the source line and resulting full diagnostic hash changed;
5. current source line 176 is the same Elementor deprecation call/site represented by the earlier approved line 164;
6. there is exactly one such diagnostic and no additional stderr/PHP diagnostic.

If any condition differs, publish one precise 2.42 BLOCKED report and STOP. Do not broaden the allowlist.

## Expected recovery state

Require:

- clean worktree;
- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- all five live files and truthful deploy marker equal recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS count 6 and fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- KEEP IDs exactly 32777, 32778, 32779, 32783, 32784, 32848;
- claims 0; AS 32733 complete/attempts 1;
- both scheduler guards and admin capability active;
- both staging locks free;
- no final Zadatak 2.25 PASS report exists.

STOP on unaccounted drift. Do not repair it.

## Immutable proven artifacts

Verify exact paths and SHA-256 values:

- source finalizer:
  `/tmp/raspitajse-task-2.37-final.RXqbnD/finalize-recovery-aware.sh`;
- source finalizer:
  `18f0730a8f9ce3639eedf09df60ec7a00df10130450803824589bff19a1322f4`;
- quiet report helper:
  `/tmp/raspitajse-task-2.37-final.RXqbnD/codex-report-quiet.sh`;
- helper:
  `da1da6f089e686137a20ff7e80ee17fac4bdd22e83fcc7b77b25c701f60c6dee`;
- corrected integration smoke:
  `88dd1d7916a3805accae7825ec7b75dc15e1f12777f6b9c53f5939afbe2e83c0`;
- corrected static harness:
  `f8239178888a276eb9c2efe9f1fbe11549c2a371c3a92815ff4b072db00c2b3e`;
- corrected runtime harness:
  `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`;
- runtime harness:
  `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`.

Do not edit immutable source artifacts.

## Only authorized derivation

Create one fresh private Task 2.42 directory.

Derive one execution copy from the exact immutable source finalizer. The only permitted semantic/textual differences are:

1. replace the old exact Elementor diagnostic tuple with the new exact tuple:
   - same severity;
   - same complete normalized message;
   - same exact file path;
   - exact line 176;
   - exact SHA-256 `5fb64aa49cd5918e5d10b4940b0b8c5eba35a60e61a4c43bf4ff855cf7f6f83d`;
2. if the source embeds an older runtime-harness hash, replace only it with `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`;
3. replace final report control metadata from Task 2.37 to Task 2.42.

Use an exact tuple/hash comparison. No wildcard path, line range, substring-only matcher, broad regex, severity-only allowance, stderr discard, `@`, `error_reporting` change, display/logging change, or warning suppression is allowed. Every other diagnostic remains blocking.

Copy the quiet report helper byte-for-byte. Save the complete diff and SHA-256 values. Prove the diff contains only the authorized changes. Run `bash -n` once. Prove no application/Git-history mutation and that the helper can push exactly one report only to `codex-reports`.

## Persistent execution transport

Reuse the proven Task 2.41 session-independent launcher behavior, with fresh Task 2.42 paths/status files. Do not redesign it.

The launcher must use a detached/session-independent worker, redirected stdin, private stdout/stderr, recorded PID/start/session/process-group/status/phase/exit data, and `exec` into the derived finalizer.

Before allowing deployment, prove the worker is alive, session-independent and the kernel owner of both exact staging locks. The launcher must not acquire staging locks, invoke WP-CLI, mutate staging or retry.

After launch, perform only read-only polling of worker/status/log/evidence. Do not launch a second worker or parallel staging command. Do not end while status is RUNNING.

## One final atomic execution

The same persistent worker holds both locks continuously and runs the derived finalizer exactly once:

1. Recovery preflight and corrected control smoke:
   - packages 200/PASS;
   - checkout 200/PASS;
   - real WooCommerce admin renderer entered/completed once;
   - owned billing hook once;
   - only the newly authorized exact Elementor diagnostic may be classified non-blocking;
   - no persistent fixture or side effect.

2. Actual-hash reconciliation to target `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`:
   - deploy the three differing 2.25 files;
   - confirm two scheduler guards already match target;
   - five live files and marker equal target.

3. Static acceptance: 27/27, zero failures, exit 0, no unapproved stderr/PHP diagnostic.

4. Runtime acceptance using exact harness SHA `1eaf8f57...`:
   - 35/35, zero failures, exit 0;
   - canonical freshly reloaded `job_package`;
   - all fixture posts/orders/meta and exact fixture-created AS rows/logs removed;
   - six KEEP rows/logs unchanged;
   - no callback or queue runner.

5. Identical corrected feature smoke and final invariants:
   - all three smoke targets PASS;
   - only the exact approved Elementor diagnostic may appear;
   - business/contract/cron/pending fingerprints exact;
   - pending 6, claims 0, AS 32733 complete/1;
   - guards/admin capability intact;
   - all mail, PHPMailer, SMTP, unexpected HTTP/external transport, payment, refund, broad-runner, protected-action execution and production counters 0;
   - all five live files and marker equal `ec97a6f...`.

6. Persist evidence and verified SHA-256 manifest.

7. Publish exactly one final report through the unchanged quiet helper:
   - Task: `Zadatak 2.25 — final recovery completion`;
   - Control specification: `Zadatak 2.42`;
   - Result: PASS;
   - classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
   - integrated/deployed SHA `ec97a6f...`;
   - exact narrow diagnostic allowance and proof;
   - control/static/runtime/feature-smoke results;
   - final fingerprints/counters/cleanup/five-file convergence;
   - production untouched.

Verify report path, 40-hex report commit, equality with `origin/codex-reports`, and non-empty remote report before releasing locks.

## Failure and recovery

No retry, fix-forward or separate follow-up diagnosis inside this task.

Before deployment, leave recovery unchanged.

After deployment, the same worker must restore all five live files and marker to exact recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`, verify every protected invariant/counter, preserve evidence, publish one precise 2.42 BLOCKED report, then release locks.

If the worker disappears, do not declare PASS or start another finalizer. After proving both locks free, a recovery-only process may restore/verify exact `e9d58c...` without acceptance or deployment, then publish one BLOCKED infrastructure report.

Production is forbidden in every path. STOP after the terminal report.
