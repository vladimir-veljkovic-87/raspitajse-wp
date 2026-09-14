# Zadatak 2.39 — Prove the corrected job_package runtime fixture and remove exact fixture scheduler residue

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.38
Target environment: staging
Production: FORBIDDEN
Finalization dependency: Zadatak 2.25

## Result required

Complete the execution and cleanup portions intentionally not run in Zadatak 2.38:

1. remove only the four already-proven pending Action Scheduler rows left by deleted Task 2.37 fixtures;
2. prove the corrected canonical `job_package` fixture under the integrated feature code;
3. ensure the authoritative runtime harness cleans any Action Scheduler rows created by its own disposable fixtures;
4. restore live staging to the exact recovery commit after the focused proof.

PASS classification: `JOB_PACKAGE_RUNTIME_CONTRACT_CORRECTED`.

Do not perform final Zadatak 2.25 deployment or reporting in this task.

## Mandatory preamble and expected state

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and `feature/z2-25-live-recovery`. Read `tasks/current.md`, `tasks/README.md`, and the complete 2.37/2.38 reports.

Expected:

- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live five-file recovery state and marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- cron: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending AS: 10 / `bca972265123523927ebbb660ce560a5125fbf0caa8cfc9fbc1ba6a05e4e998e`;
- claims 0; AS 32733 complete/attempts 1;
- scheduler guards and admin capability active.

Pending KEEP IDs that must remain unchanged:

`32777, 32778, 32779, 32783, 32784, 32848`.

Proven Task 2.37 fixture residue:

- 32855 and 32858: `woocommerce_run_product_attribute_lookup_update_callback`, group `woocommerce-db-updates`, reference deleted product 11469;
- 32856 and 32857: `wc-admin_import_orders`, group `wc-admin-data`, reference deleted orders 11470/11471;
- all attempts 0, claim 0.

STOP on any unaccounted difference.

## Authoritative corrected runtime harness

Path:

`/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`

Required SHA-256:

`e29758118e94d34de915fceca5ece6a11ece14e43344d934e6572ddefa067d41`

The proven product-fixture correction instantiates:

`WP_Job_Board_Pro_Wc_Paid_Listings_Product_Type_Package(0)`

and no longer assigns the product type through a post-save raw taxonomy workaround.

Verify this exact state before further modification.

## Authoritative fixture cleanup correction

Before lock, inspect the harness's existing fixture ledger and cleanup path. Extend only that cleanup path so it records and removes Action Scheduler actions created exclusively by the current disposable product/order fixtures.

Requirements:

- capture the initial AS ID set before fixture creation;
- after business assertions and normal fixture cleanup, identify only newly created actions;
- accept only hooks `woocommerce_run_product_attribute_lookup_update_callback` and `wc-admin_import_orders`;
- require expected groups, attempts 0, claim 0 and arguments referencing only the current recorded fixture product/order IDs;
- back up exact rows/logs before removal;
- remove by exact action ID through the Action Scheduler store without running callbacks;
- fail closed on every unexpected hook, group, claim, attempt or argument;
- prove no task-created AS row remains;
- do not modify pre-existing actions.

Do not weaken any assertion or side-effect guard. Do not modify application, vendor, theme, WooCommerce, Paid Listings or MU-plugin code.

Record the minimal diff, PHP lint and new immutable runtime-harness SHA-256.

## One focused locked operation

One process holds both staging OS locks continuously through cleanup, temporary feature reconciliation, runtime proof, fixture cleanup, recovery restoration and evidence persistence.

### A. Exact old-residue cleanup

After revalidating all four IDs against the 2.38 attribution, save exact rollback rows/logs and remove only 32855–32858 by exact ID. Do not execute callbacks.

Prove the six KEEP IDs remain byte-identical and pending returns to:

- count 6;
- fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`.

### B. Temporary target reconciliation

Save and verify the five-file recovery snapshot. Temporarily reconcile these paths to integrated target `ec97a6f...` by actual hashes:

- `wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`;
- `wp-content/themes/superio-child/functions.php`;
- `wp-content/themes/superio-child/style.css`;
- both staging scheduler guard MU plugins.

Do not change `origin/staging`.

### C. Corrected runtime proof

Run the corrected authoritative runtime acceptance under all transport/payment/scheduler guards.

Require:

- all 35 checks pass;
- saved fixture product freshly reloads as the Paid Listings custom class and type `job_package`;
- all previously failed eight assertions pass;
- every product/order/entitlement/package/user/meta fixture is deleted by exact ID;
- every newly created fixture-linked Action Scheduler row is removed by the new exact cleanup;
- six KEEP actions remain unchanged;
- pending returns to count 6 and exact fingerprint;
- business/owned-contract/cron fingerprints unchanged;
- claims 0 and AS 32733 complete/1;
- mail/PHPMailer/SMTP/external HTTP/payment/refund/broad-runner/protected-action counters all 0;
- empty unexpected stderr/PHP diagnostics.

### D. Mandatory live recovery restoration

Whether the proof passes or fails, restore all five live files and deploy marker to exact recovery commit `e9d58c...`.

Prove byte hashes, protected fingerprints, AS state and free locks before reporting.

## Failure rules

If exact attribution or cleanup cannot be proven, do not delete anything.

If runtime fails, restore live recovery state and preserve exact fixture/AS rollback evidence. Do not start final 2.25 deployment.

No retry outside a maximum of three focused runtime fixture attempts within this task.

## Reporting

Publish exactly one 2.39 report with:

- old residue cleanup IDs/proof;
- corrected runtime harness diff/path/SHA;
- 35-check result and exit code;
- fresh `job_package` class/type proof;
- complete fixture and AS cleanup;
- final six KEEP IDs/fingerprint;
- recovery file/marker restoration;
- fingerprints and side-effect counters;
- production untouched.

Then STOP.
