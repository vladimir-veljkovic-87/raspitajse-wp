# Zadatak 2.38 — Resolve the runtime job_package fixture contract and its Action Scheduler residue

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.37
Target environment: staging
Production: FORBIDDEN
Finalization dependency: Zadatak 2.25

## Result required

Resolve the exact post-deploy runtime acceptance failure from Zadatak 2.37 without launching another full finalization attempt.

Determine whether `fixture_job_package_product=false` is an authoritative runtime-harness fixture defect or a real product regression. Correct the authoritative existing runtime harness only if the fixture is wrong, prove the affected commerce assertion chain in a focused guarded feature-code probe, and attribute/clean only any Action Scheduler rows proven to be residue of the failed fixture.

PASS classification: `JOB_PACKAGE_RUNTIME_CONTRACT_CORRECTED`.

Do not finalize Zadatak 2.25 in this task.

## Mandatory preamble and expected state

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md` and `tasks/README.md` in full. Read the PASS 2.31/2.33 reports and the complete 2.37 FAIL report/evidence.

Expected state:

- `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live recovery files and marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- claims: 0; AS 32733 complete/attempts 1;
- both scheduler guards active;
- current pending AS: 10 with fingerprint `bca972265123523927ebbb660ce560a5125fbf0caa8cfc9fbc1ba6a05e4e998e`;
- pending IDs: 32777, 32778, 32779, 32783, 32784, 32848, 32855, 32856, 32857, 32858.

STOP on an unaccounted difference. Production is forbidden. `codex-tasks` is read-only to the executor.

## Exact known failure

Authoritative runtime harness:

`/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`

Primary failure:

- line 97: `fixture_job_package_product`;
- expected: saved fixture product resolves through WooCommerce as type `job_package`;
- actual: false.

Dependent failures:

- `processing_creates_one_entitlement`;
- `activation_window_stamped`;
- `package_duration_separate_from_validity`;
- `package_available_before_boundary`;
- `package_expired_at_boundary`;
- `quota_exhaustion_independent`;
- `completed_retry_idempotent`.

The previous harness exited 1 with empty stderr. Do not treat dependent assertions as separate root causes until the product fixture contract is corrected.

Evidence:

`/tmp/raspitajse-task-2.25-final-atomic.qafdp0/atomic-evidence/runtime-acceptance.raw`

## Phase 1 — read-only attribution

Before any live change:

1. Record the authoritative runtime harness SHA-256 and inspect its complete fixture creation, product save, product-type assignment, cache invalidation, reload and cleanup order.
2. Inspect the currently loaded WooCommerce 11.1 and WP Job Board Pro Paid Listings product-type registration and the canonical application/admin creation path for a `job_package` product.
3. Determine:
   - the concrete class created before save;
   - product ID after save;
   - `product_type` taxonomy terms before/after save;
   - object-cache state and the class/type returned by a fresh `wc_get_product()`;
   - whether the harness assigns the type before it has a persistent product ID;
   - whether the harness incorrectly expects `WC_Product_Simple::set_props()` or save alone to persist a custom product type;
   - whether the plugin's custom product class/registration is loaded in WP-CLI.
4. Attribute exact pending IDs 32855–32858 read-only: hook, group, created/scheduled time, status/attempts/claim, hashed args and relation to the failed fixture product/order IDs. Do not expose PII or raw arguments.

Return `PRODUCT_REGRESSION` and STOP without mutation if a canonically created real `job_package` product cannot resolve correctly under the integrated feature code.

## Phase 2 — authoritative harness correction

If attribution proves `HARNESS_FIXTURE_DEFECT`:

- modify only the existing authoritative runtime harness or its actual generator;
- use the canonical WP Job Board Pro/WooCommerce product-type creation contract found in Phase 1;
- do not force a false result by mocking `get_type()`, filtering the assertion, hard-coding the expected string, or bypassing a real fresh `wc_get_product()` reload;
- preserve every business assertion and side-effect guard;
- preserve exact reversible fixture cleanup;
- record before/after SHA-256 and unified diff;
- PHP lint once;
- make the corrected artifact immutable before focused execution.

Do not modify WooCommerce, WP Job Board Pro, Paid Listings, theme, application commerce code or MU guards.

## Phase 3 — exact Action Scheduler residue decision

The six established KEEP IDs 32777, 32778, 32779, 32783, 32784 and 32848 must not be modified.

For IDs 32855–32858:

- if and only if each is individually proven to have been created by the failed 2.37 fixture and references only a deleted/nonexistent fixture object, save exact rollback rows and cancel/delete those exact IDs without executing callbacks;
- if any row is legitimate infrastructure or attribution is incomplete, do not modify it and classify it for a new accepted baseline;
- never run a queue or broad cleanup.

## Phase 4 — focused feature-code proof

Use one bounded process under both staging locks.

1. Save/verify the exact five-file recovery snapshot.
2. Temporarily reconcile the five allowlisted live paths to target `ec97a6f...`.
3. Run only the corrected focused runtime fixture chain sufficient to prove:
   - a persistent fixture product is canonically `job_package` after a fresh reload;
   - processing creates exactly one entitlement;
   - activation window is stamped once;
   - duration and validity remain separate;
   - before/at-boundary behavior passes;
   - quota exhaustion is independent;
   - completed retry is idempotent.
4. Clean every created fixture by exact ID and prove absence.
5. Restore all five live files and marker to recovery commit `e9d58c...` even on success.
6. Prove byte-level rollback and protected state before releasing locks.

Up to three focused fixture attempts are allowed inside this task, but no full static/runtime/final smoke or final 2.25 deployment is permitted.

## Mandatory acceptance

PASS requires:

- exact `HARNESS_FIXTURE_DEFECT` root cause;
- canonical corrected product fixture;
- fresh `wc_get_product()` resolves the saved fixture as type `job_package`;
- all eight affected assertions pass;
- no unrelated existing assertion is weakened;
- all fixture IDs and related rows are removed;
- IDs 32855–32858 are either exactly cleaned with proof or explicitly accepted with complete attribution;
- six established KEEP IDs remain unchanged;
- business/owned-contract/cron fingerprints unchanged;
- claims 0 and AS 32733 complete/attempts 1;
- all mail/SMTP/HTTP/payment/refund/broad-runner/protected-action counters 0;
- live files and marker restored to exact recovery SHA;
- corrected runtime harness path, diff and immutable SHA-256 recorded.

## Git, reporting and safety

- Do not update `origin/staging`.
- Do not change application Git history.
- Do not leave feature code deployed.
- Do not publish a final 2.25 report.
- Publish exactly one 2.38 report through `codex-reports` and STOP.
