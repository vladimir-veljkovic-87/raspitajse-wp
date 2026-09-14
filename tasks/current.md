# Zadatak 2.48 — Remove paid journey from public UI

Status: READY
Baseline: 7ce577b5802bcea143c693d426febf743d1ab340
Previous task: 2.47
Roadmap phase: NOW — Free launch architecture
Target environment: staging
Production: FORBIDDEN

## Product contract

Raspitajse launches as a free service:

- employers and candidates must not encounter package purchase, pricing, cart, checkout, order-pay or payment requirements;
- employers use the free submission flow and the deployed limit of 3 active public jobs;
- candidates use profile/CV/search/application without a package;
- WooCommerce and Paid Listings may remain installed as historical infrastructure;
- all existing products, orders, entitlements, pages and stored configuration must be preserved;
- future monetization remains possible but is outside launch scope.

## Objective

Remove the paid journey from all public candidate/employer surfaces and direct public paid routes while preserving historical commercial data and wp-admin read access.

PASS classification: `FREE_PUBLIC_JOURNEY_DEPLOYED`.

Task 2.47 is application-complete at staging SHA `7ce577b5802bcea143c693d426febf743d1ab340`. Its PARTIAL label was caused only by post-integration evidence-directory permissions. Do not rerun or reimplement its quota acceptance unless a direct regression is observed.

## Mandatory reads and preflight

Fetch fresh refs and read in full:

- `tasks/README.md`;
- `tasks/ROADMAP.md`;
- this task;
- final reports 2.46 and 2.47;
- the deployed free-access policy and complete current code for package/pricing/cart/checkout/payment navigation, redirects, widgets, templates, scripts and commerce boot registration.

Require before mutation:

- clean worktree;
- local staging and `origin/staging` at the exact baseline;
- live owned files and deploy marker aligned to the same baseline;
- staging environment identity;
- no active staging mutation lock holder;
- Task 2.47 free access, three-job quota hooks and admin capability still registered;
- read-only T0 counts/fingerprints for products `1728/1729/1730`, three historical orders and entitlement IDs `8900,9826,9829,9832,9835,9838,9841,9844,9847,9850`;
- business, role/profile, owned-contract, cron, protected Action Scheduler and claims fingerprints.

Do not require a stale global pending Action Scheduler count. Rows `32867` and `32868` remain observational vendor state: record only, never execute, mutate or use as an unrelated blocker.

Create a scoped feature branch from the exact baseline. No reset, rebase, force-push or vendor/core modification.

## Authoritative implementation direction

Implement the UI/routing policy in a Raspitajse-owned layer, preferably a dedicated class such as:

`wp-content/plugins/raspitajse-commerce/includes/class-raspitajse-free-launch-ui-policy.php`

Boot it from the existing Raspitajse Commerce bootstrap and reuse the Task 2.47 free-launch feature seam. A different owned class boundary is acceptable only if the final report explains why it is smaller and clearer.

Requirements:

- idempotent boot and no duplicate hooks;
- no modification of WordPress, WooCommerce, Superio, WP Job Board Pro, Paid Listings or Elementor vendor files;
- no database deletion or destructive content migration;
- no hidden PHP warning suppression;
- localized user-facing text;
- no PII in logs/evidence;
- rollback is code-only.

## Public UI behavior

### Navigation and calls to action

Remove paid destinations from rendered public desktop and mobile journeys:

- pricing page ID `36`;
- packages page ID `1699`;
- cart page ID `8606`;
- checkout page ID `8607`;
- menu item ID `1712`;
- header mini-cart/cart buttons;
- package purchase buttons/forms;
- package Elementor widgets and package dashboard blocks;
- package chooser/process steps and any remaining checkout CTA;
- order-pay links exposed to ordinary candidate/employer visitors.

Do not delete these pages, menu rows, Elementor content or historical templates. Hide/filter them at runtime in the owned free-launch policy so rollback is deterministic.

Preserve useful non-paid navigation:

- employer “Post Job” actions must lead to submit page ID `1694`;
- employer account/manage-job actions may lead to dashboard page ID `1653`;
- candidate registration/profile/CV/search/application links remain;
- login, logout and password-reset links remain;
- administrators retain normal wp-admin historical access.

Resolve URLs from canonical WordPress page IDs/options. Do not hardcode a new domain or staging hostname. Desktop and mobile rendering must use the same policy.

### Submit and dashboard surfaces

The employer submission wizard must begin with the normal free submission step and must not render:

- choose-package;
- process-package;
- product price;
- package radio/select controls;
- add-to-cart;
- checkout/payment instructions;
- package entitlement requirement.

Replace an existing package dashboard block with a simple localized free-plan quota summary only if an owned injection point already exists safely. It may show “used of 3 active jobs” and link to submit/manage jobs, using the authoritative Task 2.47 policy. Do not copy or fork a vendor template solely for this enhancement. If no safe owned injection exists, remove the paid block and record quota-summary UI as part of Task 2.50; absence of an optional summary is not a blocker.

### Direct route protection

For ordinary frontend requests in free-launch mode:

- pricing/packages/cart/checkout/order-pay/add-payment-method routes must not expose or execute a purchase flow;
- redirect employer-relevant paid destinations to submit page `1694` or dashboard `1653`;
- redirect neutral/non-employer paid destinations to a safe canonical public page;
- use same-origin WordPress redirects and prevent loops;
- preserve HTTP method safety: never transform an untrusted payment POST into a state-changing destination;
- do not invoke gateway, cart, order or payment callbacks during redirect handling.

Explicitly exempt and preserve:

- wp-admin product/order/history views for authorized administrators;
- WordPress AJAX, REST, CLI and cron contexts unless an exact public purchase endpoint must be denied;
- webhook/callback routes only to the extent needed to avoid corrupting historical provider state; do not execute or test real gateway callbacks;
- login, registration, password reset and job/candidate application endpoints.

Direct access may return a safe redirect or an intentional non-success response; it must never produce a redirect loop, PHP diagnostic or paid form.

## Commerce callback and asset policy

In free-launch mode:

- do not enqueue or localize `package-purchase.js`;
- do not boot public checkout prefill, checkout-field mutation, purchase currency preview/conversion, NBS exchange-rate HTTP, public payment-instruction, thank-you or invoice-send callbacks;
- detach only exact Paid Listings package/cart/order callbacks required to expose or execute the public purchase journey;
- retain read-only historical product/order/entitlement registration;
- retain the guarded WooCommerce/HPOS admin order renderer used for historical inspection;
- do not deactivate WooCommerce or Paid Listings;
- do not change gateway settings or delete gateway/order metadata.

Hook inspection must prove both sides: prohibited public callbacks are absent, required historical/admin callbacks remain.

## Scope boundaries

In scope:

- owned runtime filtering of navigation, widgets, scripts and paid pages;
- safe redirects/denials for direct public paid routes;
- conditional commerce bootstrap/hook detachment;
- guarded staging integration/deploy;
- deterministic server-rendered and browser-like HTTP acceptance.

Out of scope:

- deleting pages, menu records, products, orders, entitlements or plugins;
- redesigning Elementor page content unrelated to paid controls;
- theme/plugin upgrades;
- real CAPTCHA/session E2E — Task 2.49;
- full three-job browser lifecycle — Task 2.50;
- real transactional email — Task 2.51;
- production.

Do not broaden scope for unrelated vendor diagnostics or scheduler rows.

## Required static tests

Before any deploy, prove:

- PHP lint and focused static harness PASS;
- owned UI policy boots exactly once;
- the Task 2.47 quota policy remains unchanged unless a minimal compatibility edit is explicitly justified;
- exact prohibited hooks/assets are removed only in free-launch mode;
- historical admin/read hooks remain;
- canonical page IDs/options are used instead of hardcoded domains;
- redirects are same-origin, loop-safe and method-safe;
- admin/AJAX/REST/CLI/cron exclusions are explicit;
- no vendor/core file changed;
- no deletion/migration SQL;
- no real mail, SMTP, payment, external HTTP or broad scheduler runner in the test path.

## Guarded staging acceptance

Use canonical staging locks for authoritative snapshots and fixture/database work. Intercept mail, PHPMailer, SMTP, gateway/payment calls, external HTTP transport, broad cron/Action Scheduler runners and protected actions before execution.

Prove at minimum:

1. anonymous desktop and mobile primary navigation contain no pricing, packages, cart, checkout, order-pay or purchase CTA;
2. logged-in employer navigation contains Post Job and dashboard/manage links, with no paid CTA;
3. candidate navigation/profile/search/application surfaces contain no package requirement;
4. submit page `1694` renders the normal free flow without package steps, product controls or checkout redirect;
5. dashboard page `1653` contains no purchase/package widget; an optional owned quota summary, if implemented, reports the authoritative count correctly;
6. direct GET requests to pages `36/1699/8606/8607` and WooCommerce order-pay/add-payment-method endpoints are safely redirected or denied with no loop;
7. POST-like probes to paid endpoints cannot create cart/session/order/payment state and do not get converted into an unsafe state-changing redirect;
8. `package-purchase.js`, NBS conversion request and public checkout/payment/invoice-send callbacks are not loaded or called;
9. authorized administrator can still open wp-admin products and all three historical orders through read-only renderers;
10. the three products, three orders and ten entitlements remain count/hash/status identical to T0;
11. Task 2.47 free employer/candidate hook contract and three-job quota focused regression PASS;
12. registration, login, password reset, job view/search and application endpoints are not accidentally redirected by the paid-route policy;
13. no required CSS/JS asset returns 403/404 and no tested page produces a new PHP notice/warning/error/fatal;
14. after exact cleanup, business, role/profile, owned-contract, cron, protected AS and claims fingerprints equal T0 except independently occurring observational vendor rows;
15. mail, PHPMailer, SMTP, payment/gateway, external HTTP transport, broad runner, protected-action execution and unexpected scheduler counters are all zero.

Use browser-like HTTP and server-rendered DOM assertions. A real authenticated browser session is not required in this task, but HTML status alone is insufficient: inspect final destination, redirects, DOM links/forms/scripts and guarded counters.

Do not execute broad cron/Action Scheduler runners, real gateway callbacks or real email.

If a harness/bootstrap defect occurs, capture the exact sanitized cause once and correct only the harness. Do not alter product behavior to satisfy an invalid fixture and do not loop speculative retries.

## Integration and staging deployment

After static and pre-deploy guarded acceptance pass:

1. commit and push the scoped feature branch;
2. under one uninterrupted final staging mutation lock, revalidate source/live/marker and protected T0;
3. deploy the proven feature commit by actual file hashes;
4. verify only authorized owned files changed and the marker truthfully names the deployed SHA;
5. execute the same guarded post-deploy acceptance;
6. clean exact ledgered fixtures and prove final state/counters;
7. fast-forward `staging` to the proven commit and push without force;
8. verify local staging, `origin/staging`, live owned files and marker align;
9. save evidence safely: directories must remain traversable (`0700`) and regular sensitive files may be `0600`;
10. publish and verify the remote report, then release the mutation lock.

Do not apply `chmod 600` to evidence directories. This explicitly prevents recurrence of the Task 2.47 procedural failure.

If a post-deploy gate fails before integration, restore changed live owned files and the marker to the verified baseline, clean exact fixtures and leave `origin/staging` unchanged.

After integration, never rewrite Git history. Report exact state and stop if a late procedural failure occurs.

## Evidence and report

Publish exactly one final report to `codex-reports` containing:

- result/classification and final SHA;
- exact changed-file/diff summary;
- static and all 15 acceptance results;
- desktop/mobile/menu/CTA matrix;
- direct-route status/redirect matrix;
- prohibited versus preserved hook/asset matrix;
- Task 2.47 regression result;
- historical product/order/entitlement T0/T1/final equality;
- protected fingerprints and all side-effect counters;
- deployed hashes and truthful marker;
- evidence permission verification;
- rollback result if invoked;
- confirmation that no historical data was deleted, Task 2.49 was not started and production was untouched.

PASS requires all gates PASS, a clean worktree, exact source/live/marker alignment, verified remote report readback and released locks.

On PASS or BLOCKED, STOP. Do not begin Task 2.49.
