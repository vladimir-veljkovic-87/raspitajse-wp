# Zadatak 2.46 — Read-only free-access and three-job-quota impact map

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.44
Supersedes before execution: 2.45
Roadmap phase: NOW — Free launch architecture
Target environment: staging
Production: FORBIDDEN

## Product contract

Raspitajse launches as a free service:

- no payment, checkout, paid subscription or package purchase is required;
- an employer may have at most 3 simultaneously active public job listings;
- candidates use profile/CV/search/application without purchasing a package;
- existing WooCommerce products/orders/history must be preserved;
- future monetization is out of scope.

## Objective

Produce the authoritative read-only impact map and implementation contract for Tasks 2.47 and 2.48.

Do not modify code, Git history, database, options, users, roles, files, deploy marker, scheduler rows or production. Do not execute cron/Action Scheduler callbacks, payment, mail or external vendor requests.

PASS classification: `FREE_ACCESS_IMPACT_MAP_COMPLETE`.

## Required reads

Fetch fresh refs and read `tasks/README.md`, `tasks/ROADMAP.md`, this task, final report 2.25, reports 2.43–2.44, and the complete deployed/Git code relevant to registration, roles, packages, checkout, job submission, entitlement activation, quota checks and redirects.

Require clean worktree and Git/live/marker at `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`. Record current versions and active dependencies. Use both staging locks for any authoritative database snapshot.

## Inventory

Create a file/function/hook inventory for:

- package/product discovery and pricing pages;
- cart/checkout/payment redirects and menu/CTA links;
- WooCommerce Paid Listings integration;
- order-status activation and entitlement storage;
- candidate/employer package requirements;
- job-post submission authorization;
- active-job counting and status transitions;
- quota decrement/increment logic;
- expiry/cancellation behavior;
- registration/default-role behavior;
- admin exemptions;
- dashboards/templates/shortcodes/widgets that expose paid flow;
- emails and scheduler actions coupled to purchases/packages;
- existing product/order/user entitlement data that must remain preserved.

For each item record:

- exact file/class/function/hook;
- current business purpose;
- caller and data dependency;
- side effects;
- user-facing surface;
- `KEEP`, `REDESIGN` or `DROP`;
- proposed Raspitajse-owned target;
- migration risk and test requirement.

Do not treat vendor internals as authoritative requirements.

## Three-active-job rule

Define an exact implementation contract, including:

- authoritative employer identity/profile relation;
- exact job post type and statuses;
- “active” means publicly visible and not expired;
- draft, pending-review, expired, rejected and trash do not count;
- editing does not allocate a new slot;
- publish/unpublish/expiry/trash/restore transitions consume or release exactly once;
- fourth active listing is rejected before public publication;
- entered content is preserved as a draft/pending item where technically safe;
- concurrency/race protection prevents two simultaneous submissions from exceeding three;
- administrator moderation is not blocked;
- no employer can affect another employer’s count;
- existing employers receive free access without order/package creation;
- candidates do not require entitlement purchase.

Identify the best Raspitajse-owned enforcement points and explain why they cover UI, REST/AJAX, direct form and status-transition paths.

## Free-journey migration

Specify exactly how to:

- remove package/cart/checkout/payment from public navigation and redirects;
- stop purchase-based access checks from blocking candidate/employer features;
- preserve WooCommerce historical rows and avoid destructive migration;
- keep WooCommerce only where an actual dependency remains;
- prevent accidental gateway/payment/email execution;
- preserve a future monetization seam without keeping launch-time purchase friction;
- migrate existing users deterministically and idempotently;
- roll back safely.

No implementation is authorized in this task.

## Acceptance design for 2.47/2.48

Define deterministic tests for:

1. employers with 0, 1 and 2 active jobs may publish;
2. exactly 3 active jobs blocks a fourth;
3. draft/pending/expired/rejected/trash counts are correct;
4. editing an active job does not double-count;
5. unpublish/expiry/trash frees a slot;
6. restore/republish consumes one slot;
7. concurrent publish attempts cannot produce four active jobs;
8. admin moderation works;
9. cross-employer isolation works;
10. existing/new employers require no order/package;
11. candidates require no package;
12. public menus/pages/forms contain no paid checkout dependency;
13. existing products/orders remain unchanged;
14. zero payment/mail/external transport/scheduler side effects.

Fixtures must be ledgered and exactly cleaned. Business/protected fingerprints must remain stable except for explicitly expected fixture rows.

## Scheduler scope

Do not re-investigate or mutate 32867/32868. Record them as observational vendor rows per the roadmap. They must not block this read-only business impact map merely by existing.

## Deliverables and report

Publish exactly one report to `codex-reports` containing:

- complete inventory table;
- KEEP/REDESIGN/DROP decisions;
- exact three-active-job contract;
- proposed files/hooks/components for 2.47 and 2.48;
- data preservation/migration/rollback plan;
- acceptance matrix;
- unresolved product decisions, if any;
- confirmation of zero mutation/callback/payment/mail/external request;
- locks released and production untouched.

Result is PASS only when the implementation contract is precise enough for Codex to implement without rediscovering scope. Otherwise BLOCKED with the exact missing fact.

Verify remote report path/commit and non-empty readback, then STOP.
