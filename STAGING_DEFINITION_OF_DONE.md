# Raspitajse Staging Definition of Done

Purpose: define when the current technical refactor/recovery phase on `stage.raspitajse.com` is complete enough to freeze a release candidate and begin a separate production-readiness/release decision.

This document is an exit criterion, not permission to deploy to production. Production remains outside autonomous Codex scope.

## 1. Guiding standard

The target is a pragmatic middle ground:

- critical business behavior must be stable, testable and under Raspitajse ownership;
- vendor code must be update-safe enough that normal plugin maintenance no longer depends on preserving undocumented direct edits;
- scheduler, mail, deployment and rollback paths must be understood and reliable;
- unnecessary legacy behavior does not need to be reproduced merely because it exists today;
- cosmetic/perfectionist cleanup that does not materially affect safety, maintainability or release confidence is not a blocker.

The staging refactor is done when the criteria below are satisfied with current evidence and no unresolved critical/high-risk regression remains.

## 2. Scheduler and background processing

Required before staging is technically done:

- WP-Cron and Action Scheduler backlog recovery is controlled and understood;
- no unclassified broad `--due-now` or broad queue execution is required to keep staging healthy;
- recurring scheduler behavior has a reliable ongoing trigger strategy;
- `doing_cron`/locking behavior is healthy under the chosen trigger strategy;
- mail/business-state scheduled jobs have fixture-backed or otherwise bounded validation where needed;
- expected plugin-owned technical housekeeping is classified under `STAGING_MUTATION_POLICY.md`;
- the temporary staging cron recovery guard is removed only in a dedicated task after the ongoing scheduler strategy is proven;
- post-removal validation confirms no backlog reappears unexpectedly and high-risk/business schedules behave as intended.

## 3. Communications ownership

Required:

- Raspitajse-owned communications code is the authoritative custom mail transport/behavior layer;
- staging mail safety remains fail-closed and prevents uncontrolled recipients;
- important mail wrappers/templates have controlled staging coverage;
- candidate/employer/job/application/package-related mail paths are classified by business-state risk;
- custom mail behavior that must remain is no longer dependent on undocumented vendor edits;
- obsolete legacy mail behavior is classified `DROP` and does not need parity work;
- any retained fallback is explicit, disabled/gated as intended, and has a known removal/rollback strategy.

## 4. Legacy behavior classification

Before reproducing or moving historical custom behavior, classify it as one of:

- `KEEP` — behavior is still required and must be preserved during refactor;
- `CHANGE` — the requirement remains, but current behavior should be intentionally replaced with a documented new behavior;
- `DROP` — behavior is obsolete/unwanted and must not consume parity/refactor effort.

Rules:

- do not spend tasks reproducing `DROP` behavior;
- `CHANGE` behavior must have an explicit acceptance criterion rather than legacy parity;
- only `KEEP` behavior requires parity before the legacy implementation is removed;
- presentation-only cleanup may remain in the child theme when it is truly theme-specific and update-safe.

## 5. Raspitajse business logic ownership

Required for critical behavior:

- candidate/employer roles, access rules and dashboard behavior that are classified `KEEP` or `CHANGE` are owned/documented outside fragile vendor edits where practical;
- job lifecycle, application lifecycle/status behavior, messaging permissions and package/entitlement behavior are understood and regression-tested;
- reusable business logic is moved incrementally from the Superio child theme/vendor files into Raspitajse-owned plugins/modules;
- environment/config behavior is not hard-coded to staging or production identities;
- obsolete diagnostics/debug/custom helpers that are safe to leave temporarily are not blockers, but must not affect runtime safety or updateability.

This definition does not require a total rewrite of every historical child-theme function before the first production release.

## 6. Vendor update safety

The target is clean/update-safe vendor ownership, especially for WP Job Board Pro and WP Job Board Pro WooCommerce Paid Listings.

Required:

- every remaining direct vendor customization is inventoried and mapped to `KEEP`, `CHANGE` or `DROP`;
- `KEEP`/`CHANGE` behavior is implemented through Raspitajse-owned hooks, filters, modules or documented template overrides wherever supported;
- behavior parity/acceptance tests pass before a corresponding vendor edit is removed;
- vendor files are restored as close as reasonably possible to the intended upstream/plugin version;
- remaining unavoidable vendor diffs, if any, are explicitly documented with update risk and a maintenance procedure;
- a future plugin update can be evaluated without reverse-engineering undocumented production-specific patches.

## 7. Disposable staging fixtures and regression coverage

Controlled disposable fixtures are allowed under `STAGING_MUTATION_POLICY.md`.

Before staging is technically done, regression coverage must exercise the important flows using disposable staging data where mutation is required:

- candidate registration/login/profile/dashboard;
- employer registration/login/profile/dashboard;
- role separation and intended multi-role/admin behavior;
- job create/edit/publish/expiry lifecycle;
- candidate application and status lifecycle;
- employer candidate/profile access according to entitlement;
- messaging/private-message permissions;
- package purchase/entitlement logic without a real payment;
- checkout/payment-state integration using a safe non-real-payment strategy;
- controlled email transport/template paths;
- job alerts/scheduled notices with disposable fixtures;
- WP-Cron and Action Scheduler steady-state behavior.

Fixtures must be identifiable, staging-only, collision-resistant and removable without touching non-fixture data.

## 8. Production-critical hardening

The following are blockers before a production release decision:

- verified backup procedure and a credible restore path;
- documented code rollback procedure;
- WordPress/plugin/theme update strategy;
- basic filesystem/role/permission/security baseline;
- staging isolation and `noindex`/search-engine protection;
- PHP/WordPress error logging and basic operational monitoring strategy;
- LiteSpeed/object-cache sanity checks for critical flows;
- no unresolved staging/production hard-coded URLs, recipients or environment identities in critical paths;
- scheduler and mail safety/trigger strategy documented;
- critical regression suite passes on the frozen staging release candidate.

The following are normally not blockers unless evidence shows a material production risk:

- perfect Core Web Vitals scores;
- exhaustive asset/minification tuning;
- cosmetic CSS cleanup unrelated to critical UX;
- removal of every harmless historical helper/comment;
- SEO/content polish of every page;
- product/UX improvements not required for current functional parity.

## 9. Release/data strategy

The default release strategy is **code first, database only when proven necessary**.

Before production consideration:

- freeze an exact staging release-candidate commit;
- compare code/config/runtime assumptions explicitly;
- do not plan a wholesale staging-database copy to production;
- every required DB migration must be narrowly scoped, documented and have known validation/rollback behavior;
- prefer idempotent/re-runnable migrations when practical;
- production deployment, DB mutation and release timing remain a separate human-approved workstream.

## 10. Technical completion gate

Staging may be declared `TECHNICALLY_READY` only when all are true:

1. scheduler recovery and steady-state trigger strategy are complete;
2. critical communications behavior is Raspitajse-owned and regression-covered;
3. critical legacy behavior has been classified `KEEP / CHANGE / DROP` and required ownership migration is complete;
4. vendor code is update-safe to the agreed standard;
5. critical candidate/employer/job/application/package/messaging/mail flows pass staging regression;
6. production-critical hardening items above are complete;
7. no unresolved critical/high-risk defect or ambiguous business-state mutation remains;
8. repository/staging release-candidate state is reproducible and documented;
9. backup/rollback strategy is proven sufficiently for the release plan;
10. production has not been modified as part of declaring staging ready.

A dedicated final staging task/report should record the evidence for this gate. Declaring `TECHNICALLY_READY` does not authorize production deployment.
