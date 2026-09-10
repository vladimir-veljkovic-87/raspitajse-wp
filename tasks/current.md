# Zadatak 2.25 — Complete the remaining Raspitajse-owned WooCommerce/custom commerce cleanup and leave staging with one authoritative implementation per required business behavior

Status: READY
Baseline: 31d06a71c15e4d8c048f3c5a0de9f5e750557d74
Previous task: 2.24
Target environment: staging
Production: FORBIDDEN

## Result required

This task is successful only if the remaining in-scope legacy/custom WooCommerce and package-related Raspitajse code is resolved on staging so that every still-required business behavior has exactly one authoritative implementation, obsolete/duplicate code is removed, and the accepted package/checkout behavior still passes end-to-end guarded acceptance.

Do not report PASS for inventory, analysis, partial migration, cleanup preparation, or a safely aborted attempt. PASS means the target staging state is actually implemented, deployed, verified, and left active.

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read the final 2.24 PASS report and the earlier custom-Woo/package audit and implementation reports needed to recover the accepted KEEP / REDESIGN / DROP decisions and existing Raspitajse Commerce/package ownership.

Verify fresh `origin/staging` and the live deploy marker are exactly `31d06a71c15e4d8c048f3c5a0de9f5e750557d74`, the staging worktree is clean/on `staging`, and the upgraded vendor stack remains Superio 1.3.37 / Apus 2.5 / RevSlider 6.7.41. If baseline or deploy marker differs, STOP.

Execute only 2.25. `codex-tasks` is read-only. Production is forbidden.

## Scope and governing architecture

WooCommerce remains infrastructure. Do not refactor WooCommerce core or reproduce its standard order/payment/privacy/session lifecycle. Do not modify WooCommerce vendor code.

Raspitajse-owned commerce/business logic belongs in Raspitajse-owned code, primarily the existing owned commerce/package layers where appropriate. Legacy theme/functions.php or ad-hoc custom hooks are references for business purpose only, not architecture to preserve.

Use the established classification rule for every in-scope legacy unit: KEEP, REDESIGN, or DROP. Required business behavior may be moved before old code is removed; never delete a required behavior first.

The accepted package model remains canonical:
- Paid Listings entitlement is authoritative;
- package ownership uses the accepted `job_package` / `_wjbpwpl_user_id` model;
- package ad usage window is 30 calendar days from first successful processing/completed activation and is immutable once started;
- pending/on-hold must not start the clock;
- package entitlement validity is separate from individual listing duration;
- standalone package purchase UI uses the already accepted owned transport, not a global interceptor;
- employer checkout/HPOS support remains in the owned Raspitajse Commerce implementation.

Do not regress any of those accepted rules.

## Work to complete in this task

Perform one bounded discovery-to-implementation transaction, not a chain of analysis-only follow-up tasks.

1. Inventory all remaining Raspitajse-authored WooCommerce/package/checkout/order hooks and helpers still active outside the accepted owned implementation. Include child-theme/functions.php, custom snippets/files, mu-plugins/owned plugins, and any repository/runtime custom code that participates in the same business behaviors. Exclude unchanged vendor/core internals except as dependency references.

2. Reconcile that inventory against the prior custom-Woo audit and all already-completed migrations. For each remaining active unit, determine business purpose and classify KEEP / REDESIGN / DROP. If an earlier audit item is already superseded by an owned implementation, prove that and treat the old implementation as removable duplicate unless another required side effect remains.

3. In the same task, implement the target architecture:
- move still-required Raspitajse business behavior into the appropriate owned layer when it is not already there;
- retain only genuinely necessary thin compatibility/bootstrap glue in the child/theme layer;
- remove duplicate, dead, abandoned, telemetry/debug, unsafe logging, vendor-notice, obsolete checkout/package, or superseded hooks;
- do not create a second source of truth for package entitlement, ownership, validity, order status, or checkout state;
- do not modify vendor plugin/theme/core files merely to simplify migration.

4. Remove legacy implementations only after their replacements/authoritative equivalents are proven in the same transaction.

5. Deploy the accepted feature and leave staging on the new integrated commit only after every critical acceptance gate below passes.

If discovery reveals a genuinely unrelated subsystem that cannot be safely resolved without broadening scope, document it but do not let it block completion of the commerce result unless it is an actual dependency of the required behaviors.

## Mandatory acceptance

Before integration capture a sanitized T0 protected-state projection. Use existing staging guards: no real SMTP/mail, no payment/refund execution, no external WordPress HTTP merely to prove behavior, no broad WP-Cron or Action Scheduler runner. Protected AS ID 32733 must remain pending/attempts 0.

The authoritative guarded acceptance must prove at minimum:

- WordPress bootstrap and relevant frontend/admin template loading have no fatal regression;
- WooCommerce remains 9.5.4 and core/vendor files are untouched;
- Superio 1.3.37 / Apus 2.5 / RevSlider 6.7.41 remain exact and functional;
- WPJBP 1.2.86 and Paid Listings 1.0.19 remain exact and are not reinstalled/downgraded;
- HPOS compatibility declarations and employer lookup/checkout behavior still pass;
- employer and candidate package purchase/access behavior uses the intended owned paths;
- package activation clock starts only on accepted successful order states, never pending/on-hold, and cannot be reset by later transitions;
- 30-calendar-day validity including DST/boundary cases passes the accepted policy harness;
- quota/entitlement and individual job listing duration remain separate;
- standalone package purchase UI/transport still works without a real payment;
- checkout contains no PII/debug logging regression;
- no duplicate callback remains registered for any migrated business event;
- no legacy hook can independently mutate the same package entitlement/validity/ownership state after cutover;
- candidate→job communications, employer→candidate retirement, job expiry/pre-expiry callbacks, and the exact three owned hourly scheduler hooks remain unchanged;
- protected business fingerprint is unchanged except for explicitly created reversible test fixtures, which must be cleaned exactly;
- mail sends 0, SMTP/PHPMailer transports 0, payment/refund execution 0, broad cron/AS execution 0, production operations 0.

Use bounded reversible fixtures where needed. Clean all task-created orders/users/packages/options/transients after assertions and prove cleanup.

## Git and deployment contract

Create one scoped feature branch from exact baseline. Prefer a small number of cohesive commits, but do not split discovery and completion into separate numbered tasks. Application mutations may include only Raspitajse-owned/custom paths and narrowly necessary tests/deployment metadata. Vendor/core trees are read-only for this task.

Review exact changed paths, run syntax/static checks, then deploy through the accepted staging deploy workflow. Integrate into `staging` only after acceptance passes. Re-deploy final staging so source HEAD, `origin/staging`, and deploy marker converge.

If a critical acceptance failure occurs after mutation, rollback the task-authored source/runtime changes to the exact T0 state and do not integrate a partial result.

## PASS definition

PASS classification: `REMAINING_CUSTOM_COMMERCE_CLEANUP_COMPLETED`.

PASS requires all of the following simultaneously:
- all remaining in-scope legacy/custom commerce units classified and resolved;
- every required business behavior has one authoritative implementation;
- superseded/obsolete implementations are removed from active runtime;
- accepted package, checkout, HPOS and purchase behavior passes;
- no duplicate mutating hooks remain;
- final staging is deployed on the integrated commit and clean;
- protected state and side-effect counters pass.

Anything short of that is not task completion. Publish the final report through `codex-reports` and STOP. Do not start legacy-theme cleanup or scheduler cleanup automatically.