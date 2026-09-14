# Zadatak 2.47 — Implement free access and three-active-job quota

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.46
Roadmap phase: NOW — Free launch architecture
Target environment: staging
Production: FORBIDDEN

## Product contract

Raspitajse launches as a free service:

- employers and candidates require no paid package, order or checkout;
- one canonical employer may have at most **3 simultaneously active public job listings**;
- employees linked to the same employer share those three slots;
- historical WooCommerce products, orders and job-package entitlements remain preserved;
- future monetization and removal of public paid UI belong to later work.

## Objective

Implement and prove the Raspitajse-owned free-access policy and concurrency-safe three-active-job quota defined by the PASS report for Task 2.46.

PASS classification: `FREE_ACCESS_THREE_JOB_QUOTA_DEPLOYED`.

This task may change only application code required for the owned policy, its bootstrap and focused tests. It may integrate and deploy to staging only after every required gate passes. Production must not be accessed or changed.

## Mandatory reads and preflight

Fetch fresh refs and read in full:

- `tasks/README.md`;
- `tasks/ROADMAP.md`;
- this task;
- the final Task 2.46 report;
- the final Task 2.25 report;
- the complete current source files that will be modified and all vendor callbacks named by 2.46.

Before application mutation require:

- clean worktree;
- `origin/staging`, local staging, live deploy marker and the six protected deployed files at baseline `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- staging environment identity;
- no active staging mutation lock holder;
- read-only hashes/counts for products `1728/1729/1730`, the three historical orders and entitlement IDs `8900,9826,9829,9832,9835,9838,9841,9844,9847,9850`;
- zero employers currently above three active jobs;
- claims count and Raspitajse-owned/protected Action Scheduler, cron, business, role/profile and owned-contract fingerprints.

Do not require a stale global pending Action Scheduler count. Rows `32867` and `32868` are observational vendor state: record but do not investigate, claim, delete, execute or allow them to block this business task merely by existing.

Create a scoped feature branch from the exact baseline. Do not rebase, reset or force-push. Do not modify WordPress core, Superio, WP Job Board Pro, Paid Listings, WooCommerce or other vendor files.

## Authorized owned implementation

### Component and feature seam

Add:

`wp-content/plugins/raspitajse-commerce/includes/class-raspitajse-free-job-access-policy.php`

Require and boot it from:

`wp-content/plugins/raspitajse-commerce/raspitajse-commerce.php`

Requirements:

- explicit free-launch feature seam, enabled by default for this release and filterable through a Raspitajse-owned filter;
- idempotent boot: no duplicate hooks when boot is invoked more than once;
- no new plugin activation, option migration or bulk user/data migration;
- use WordPress/WooCommerce APIs and parameterized SQL where appropriate;
- no vendor/core edits and no hidden warning suppression;
- all user messages use localization functions;
- logs/reason codes contain no PII.

### Free employer submission

After Paid Listings has registered its hooks, detach only the exact public package-authorization and purchase callbacks proven by 2.46.

At maximum controlled priority:

- remove the `wjbp-choose-packages` and `wjbp-process-packages` submit steps;
- prevent a normal free submission/relist from becoming `pending_payment`;
- prevent package validation, package consumption, cart mutation and checkout redirect from authorizing a job;
- preserve the normal WP Job Board Pro preview/moderation/publication flow;
- preserve historical product/order/entitlement registration and read-only admin access;
- create no product, order, cart item, entitlement, package marker or purchase email.

Do not broadly remove WooCommerce or Paid Listings hooks. Detach only callbacks identified in the 2.46 impact map and prove exact hook presence/absence after boot.

### Free candidate access

Make free policy explicit so option drift cannot silently restore payment requirements:

- candidate profile/CV/search/application must not require a package;
- resume/candidate creation must not become `pending_payment`;
- no application/view/contact package counter may increment;
- existing authentication, authorization, privacy, duplicate-application and moderation checks remain intact;
- this task does not weaken CAPTCHA, role or ownership checks.

### Canonical employer identity

For quota purposes, use the canonical owning WordPress employer user:

1. resolve `_job_employer_posted_by` employer profile through the WP Job Board Pro user relation;
2. for a new direct-form submission use the plugin’s canonical employer/user mapping;
3. accept `post_author` only when it resolves to a valid employer profile;
4. map employee accounts to their parent employer so they share the same quota;
5. reject ambiguous, invalid or cross-employer ownership fail-closed for non-admin publication.

One employer must never consume, release or inspect another employer’s quota through forged IDs, author values or meta.

### Exact active-job definition

Post type: exactly `job_listing`.

A row consumes one slot only when:

- `post_status = publish`; and
- `_job_expiry_date` is absent/blank or is a valid canonical `Y-m-d` date that is today or later in the WordPress site timezone.

A valid date earlier than today is expired and does not count. An invalid non-empty expiry value fails closed for non-admin publication and must produce a repairable reason, not be silently treated as available capacity.

The following never consume a slot: draft, preview, pending, pending-review/pending_approve, pending_payment, expired, cancelled, denied/rejected variants, trash, revisions and autosaves.

An edit of an already active job excludes its own post ID. Publish to a non-counted status or valid expiry releases capacity implicitly through the authoritative query. Restore/relist/republish must pass the same publish gate.

### Authoritative enforcement and concurrency

Early UI checks may show `used`, `remaining` and the localized quota message, but they are not the security boundary.

The authoritative boundary must cover frontend form, wp-admin, REST, AJAX and ordinary `wp_insert_post()` / `wp_update_post()` publication paths:

- enforce on an owned `wp_insert_post_data` filter for `job_listing`;
- for every non-admin requested transition to `publish`, acquire a MySQL advisory lock derived from a SHA-256 of the current blog ID plus canonical employer user ID;
- use the existing `$wpdb` connection and parameterized lock calls;
- use a bounded timeout; lock failure must fail closed;
- keep request-local lock ownership so recursive saves cannot double-acquire or release another owner’s lock;
- while holding the lock, recount authoritative active rows excluding the current job ID;
- release through `wp_after_insert_post` and a shutdown fallback, with proof that no lock remains;
- never use a mutable numeric quota counter as authority.

When the employer already has three active jobs:

- public publication must not occur;
- keep the attempted job in a non-public draft state wherever WordPress persistence permits;
- preserve submitted title/content and safe submitted metadata;
- expose an owned non-PII reason code and localized explanation;
- keep the public active count exactly three;
- produce no mail, external HTTP, payment, scheduler action, order, cart or entitlement side effect.

The approved administrative bypass is exactly `current_user_can('manage_options')`. It must be explicit and testable, must not change job ownership, and must not reintroduce hardcoded user IDs.

## Scope boundaries

In scope:

- owned free-access policy;
- package-gate detachment required for functional free submission/candidate access;
- exact three-job quota;
- deterministic static/runtime/concurrency tests;
- guarded staging deploy and integration if all gates pass.

Out of scope:

- deleting or deactivating WooCommerce/Paid Listings;
- deleting or rewriting products, orders or entitlements;
- removing pricing/packages/cart/checkout pages, menus, widgets or CTAs — Task 2.48;
- real-browser CAPTCHA/session journeys — Task 2.49;
- full application/job lifecycle browser journey — Task 2.50;
- live transactional email acceptance — Task 2.51;
- theme/plugin upgrades;
- production.

An incidental vendor warning, scheduler row or unrelated defect must be classified by actual business impact. Do not expand scope or begin another numbered task.

## Required static tests

Run syntax/lint and a focused static harness proving at least:

- component boots once and hooks once;
- only intended package/candidate callbacks are detached;
- historical admin/read callbacks remain;
- paid submit steps and `pending_payment` authorization path are neutralized in free mode;
- no vendor file changed;
- no hardcoded user-ID admin rule;
- parameterized advisory-lock queries, bounded timeout and shutdown release exist;
- no broad cron/Action Scheduler runner, payment call, real mail transport or external request exists in the test path.

Static validation must complete before any staging deployment.

## Guarded runtime acceptance

Use both canonical staging locks for every authoritative fixture/database phase. Build an exact fixture ledger before creating rows. Intercept mail, PHPMailer, SMTP, payment, external HTTP, broad cron/AS runners and protected actions before transport/execution.

Use isolated, exact-ID fixtures and prove:

1. separate employers with 0, 1 and 2 active unexpired jobs can publish one, resulting in 1, 2 and 3;
2. an employer at exactly 3 cannot publish a fourth; it remains non-public with content preserved and a localized quota reason;
3. blank/future expiry counts, past expiry does not, and invalid non-empty expiry fails closed;
4. draft, preview, pending, pending_approve, pending_payment, expired, cancelled, denied/rejected and trash do not count;
5. repeated edits of one active job do not change the count;
6. publish→draft/pending, expiry and trash each free a slot; retries are idempotent;
7. restore/relist/republish consumes exactly one slot and fails when no slot exists;
8. two synchronized independent publication workers starting at count 2 result in exactly one third active job and one non-public job, never four; record advisory-lock acquire/release evidence without secrets;
9. admin `manage_options` moderation bypass works without changing canonical ownership;
10. forged/cross-employer profile, author and job IDs are rejected without changing either employer;
11. existing and fixture new employers submit without package/order/entitlement; existing and fixture candidate profile/CV/search/application eligibility is free without package counters;
12. exact hook inspection proves cart/checkout/order-paid/package-consumption callbacks required for submission are not reached;
13. product/order/entitlement counts and fingerprints remain identical to T0;
14. after exact cleanup, business, role/profile, cron, owned-contract, protected AS and claims state return exactly to T0, except independently occurring observational vendor rows;
15. final counters for mail, PHPMailer, SMTP, payment, external HTTP transport, broad runners and protected-action execution are all zero.

Do not execute broad WP-Cron or Action Scheduler runners. Do not use real recipient addresses. Do not create or complete real payments/orders.

The runtime harness must distinguish a harness/bootstrap defect from a product defect and report the exact sanitized exception/stack once. It must not loop through speculative retries or modify product code merely to satisfy a defective fixture.

## Integration and staging deployment

After static and pre-deploy runtime tests pass:

1. commit and push the scoped feature branch;
2. under one uninterrupted final staging mutation lock, verify baseline/live/marker and protected fingerprints again;
3. deploy the feature commit by comparing actual live hashes; do not skip because of a stale marker;
4. verify only the authorized owned files changed and marker truthfully names the deployed feature SHA;
5. run the same guarded acceptance against deployed code;
6. clean fixtures by exact ledgered IDs and prove final fingerprints/counters;
7. only then fast-forward `staging` to the already-proven feature commit and push without force;
8. verify local staging, `origin/staging`, live owned files and deploy marker all identify the same commit;
9. publish the report, then release the lock.

If any post-deploy gate fails before staging integration:

- restore only the changed live owned files from the verified baseline copies;
- set the deploy marker truthfully back to the baseline SHA;
- do not change `origin/staging`;
- clean only ledgered fixtures;
- save evidence and report `BLOCKED`.

If a failure occurs after successful fast-forward integration, do not rewrite Git history. Stop and report the exact source/live/marker state and required recovery; do not improvise a reset or force-push.

## Evidence and report

Publish exactly one final report to `codex-reports`. It must include:

- result and classification;
- feature and final staging SHA;
- exact changed-file/diff summary;
- static test results;
- all 15 runtime acceptance results;
- canonical identity and active-count rules proven;
- concurrency evidence;
- hook detach/preservation matrix;
- T0/T1/final historical product/order/entitlement and protected fingerprints;
- fixture ledger and exact cleanup;
- all side-effect counters;
- deployed-file hashes and truthful marker;
- rollback result if invoked;
- confirmation that 2.48 UI work was not performed;
- locks released and production untouched.

PASS requires:

- all required tests PASS;
- no unexplained application or protected-state drift;
- clean worktree;
- local staging, `origin/staging`, live owned files and marker aligned to one integrated commit;
- non-empty remote report readback.

On PASS or BLOCKED, verify the remote report path/commit and STOP. Do not start Task 2.48.
