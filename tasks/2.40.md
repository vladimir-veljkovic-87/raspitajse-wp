# Zadatak 2.40 — Final live deployment and completion of Zadatak 2.25

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.39
Target environment: staging
Production: FORBIDDEN
Finalizes: Zadatak 2.25

## Result required

Perform the single remaining final live deployment and acceptance using only artifacts already proven by Tasks 2.31, 2.33, 2.37 and 2.39.

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

Do not introduce a new harness, architecture, diagnostic task, feature branch, application commit, merge or push. `origin/staging` is already integrated.

## Mandatory preamble

Fetch fresh refs and read `tasks/current.md`, `tasks/README.md`, and complete reports 2.37–2.39.

Expected state:

- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live recovery files/marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned contract: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- cron: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS: 6 / `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims 0; AS 32733 complete/attempts 1;
- six KEEP IDs: 32777, 32778, 32779, 32783, 32784, 32848;
- scheduler guards and admin capability active.

STOP on any unaccounted difference. Production is forbidden.

## Immutable proven artifacts

- recovery-aware finalizer proven through control smoke, deployment, static acceptance and entry into runtime:
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
- corrected runtime harness SHA-256:
  `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`.

Verify all paths/hashes before derivation.

## Only authorized pre-lock derivation

Copy the exact recovery-aware finalizer to a fresh private task directory. Do not edit the source.

Inspect whether it embeds the previous runtime-harness hash. If present, replace only that expected value with `1eaf8f57...`. If it does not embed a runtime hash, make no such change.

Replace only final report metadata `Control specification: Zadatak 2.37` with `Control specification: Zadatak 2.40`.

No other finalizer logic or gate may change.

Copy the exact quiet report helper unchanged. Preserve its SHA-256.

Before lock:

- save finalizer diff and SHA-256;
- `bash -n` both files;
- prove diff is limited to the optional runtime hash and report-control metadata;
- prove no Git application mutation commands exist;
- prove helper can push only to `codex-reports`;
- preserve/rotate every existing active evidence directory to a unique readable sibling archive without deletion or overwrite;
- prepare exactly one fresh evidence destination;
- verify all hashes immediately before execution.

## One atomic execution

One process holds both staging OS locks continuously through report publication.

Run the derived finalizer once.

It must complete:

1. Exact recovery preflight and corrected control smoke:
   - packages 200/PASS;
   - checkout 200/PASS;
   - real admin renderer entered/completed once;
   - owned billing hook once;
   - no persistent fixture or side effect.

2. Actual-hash live reconciliation from integrated target `ec97a6f...` for the three differing 2.25 files while confirming both guard files already match target.

3. Static acceptance:
   - 27/27;
   - zero failures;
   - process exit 0;
   - empty unexpected stderr.

4. Corrected runtime acceptance using exact SHA `1eaf8f57...`:
   - 35/35;
   - process exit 0;
   - canonical freshly reloaded `job_package`;
   - all fixture and fixture-created AS rows removed;
   - six KEEP AS rows unchanged.

5. Identical corrected post-deploy smoke and final invariants:
   - all three smoke targets pass;
   - business/contract/cron/pending fingerprints exact;
   - claims 0; AS 32733 complete/1;
   - guards/admin capability intact;
   - all mail/SMTP/HTTP/payment/refund/broad-runner/protected-action/production counters 0;
   - all five live files and marker equal `ec97a6f...`.

## Failure and rollback

Before deployment, leave recovery unchanged.

After deployment, restore all five live files and marker to exact recovery commit `e9d58c...`, prove hashes/fingerprints, preserve evidence, do not change Git, and publish one precise BLOCKED report.

No retry or fix-forward inside the locked process.

## Final report

On PASS publish exactly one final report through the proven quiet helper:

- Task: `Zadatak 2.25 — final recovery completion`;
- Control specification: `Zadatak 2.40`;
- Result: PASS;
- classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`;
- integrated/deployed SHA `ec97a6f...`;
- control/static/runtime/feature smoke results;
- final fingerprints/counters and five-file convergence;
- production untouched.

Release locks only after remote report verification. Then STOP.
