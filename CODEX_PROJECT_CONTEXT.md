# Raspitajse.com — Codex Project Context and Technical Audit

Last curated: 2026-08-22

This file is a **working map**, not a substitute for inspecting the current repository, staging runtime, and the latest `codex-reports` report. It captures the important conclusions already established so a new Codex session understands what the project is, what has been verified, what is dangerous, and what still needs to be done.

If this file conflicts with current code/runtime evidence, **current evidence wins** and the discrepancy must be reported.

## 1. Product and business context

Raspitajse.com is a regional WordPress job platform focused on candidates from Serbia / Croatia / Bosnia / North Macedonia and employers offering seasonal or overseas work.

Core user groups and flows:

- candidate registration, profile, job discovery and applications;
- employer registration, employer profile and job publishing;
- employer/candidate communication and private messaging;
- candidate/employer dashboards;
- packages, WooCommerce checkout and paid listing entitlements;
- job alerts and other scheduled notifications;
- role/package-based visibility and permissions.

The business positioning is moving away from a generic “AI job platform” narrative toward **safe, transparent overseas employment**, verified employers, clear conditions, direct communication, application status visibility and support. Content/UX work must not casually alter underlying roles, job logic, payment logic or database behavior.

## 2. Main technical stack

Current stack includes:

- WordPress;
- Superio theme + Superio child theme;
- WP Job Board Pro;
- WP Job Board Pro WooCommerce Paid Listings;
- WooCommerce;
- WP Private Message;
- Elementor;
- LiteSpeed / LiteSpeed object cache on staging;
- Raspitajse-owned custom plugins and MU plugins.

Important Raspitajse-owned components include:

- `wp-content/plugins/raspitajse-communications/`
- `wp-content/mu-plugins/raspitajse-staging-mail-safety.php`
- `wp-content/mu-plugins/raspitajse-staging-cron-recovery-guard.php`
- repository deployment/reporting tooling.

## 3. Repository and environment model

Repository:

`vladimir-veljkovic-87/raspitajse-wp`

Branch model:

- `main` = old production history; do not use for active work;
- `baseline/production-2026-08` = immutable production baseline;
- `baseline/staging-2026-08` = immutable staging baseline;
- `staging` = integration branch for current staging code;
- `feature/*` = normal code work;
- `codex-reports` = reporting-only audit branch, never merged or deployed.

Normal flow:

`feature/* -> validation -> staging -> stage.raspitajse.com -> test -> later production decision`

Production is intentionally excluded from current autonomous Codex work.

Staging WordPress root:

`/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`

Production parent/root is forbidden for Codex operations except that AGENTS.md may name it as a boundary. Never deploy, recursively operate on, or mutate production.

Approved staging deployment script:

`deployment/deploy-staging.sh`

The deploy marker is stored separately under `/home/u601262303/deploy-state/` and must be used by the approved script rather than bypassed.

## 4. Critical architecture audit

### 4.1 Too much custom business logic lives in legacy/vendor locations

Historically, substantial project-specific logic accumulated in:

- Superio child theme `functions.php`;
- WP Job Board Pro plugin classes/templates;
- environment-specific theme/plugin edits.

Known child-theme customizations have included role/product body classes, wp-admin restrictions, job/candidate email customization, intlTelInput assets, custom job sidebar/AJAX behavior, SMTP-related hooks and diagnostic utilities.

**Strategic direction:** move Raspitajse-owned business logic into Raspitajse-owned plugins incrementally.

Do not “clean” or restore vendor files merely because they differ from upstream. First identify every custom behavior, implement equivalent owned behavior, test parity, then remove the vendor customization in a separate reversible step.

### 4.2 Vendor/plugin update risk

Because WP Job Board Pro and related files contain historical customization, blind plugin updates or restoration of clean vendor versions can silently remove business behavior.

Before modifying vendor code:

1. compare with baseline/current staging;
2. identify why the customization exists;
3. locate all callers and related hooks;
4. reproduce behavior in owned code where practical;
5. test candidate/employer/job/payment/mail flows;
6. only then remove the legacy/vendor implementation.

### 4.3 Environment-specific hardcoding is a recurring risk

Past diffs showed production/staging URL and mail-specific substitutions in theme/plugin code. Candidate/employer login/registration links and asset URLs have historically been vulnerable to hardcoding.

When touching URLs, redirects, forms, mail senders, callbacks or API endpoints, explicitly verify that behavior is environment-aware and does not send staging users or requests to production.

## 5. Communications / outbound email status

A Raspitajse-owned communications transport was introduced to replace legacy mail transport safely.

Current intended architecture:

- `raspitajse-communications` owns PHPMailer/SMTP transport behavior when enabled;
- legacy child-theme mail transport remains physically present for fallback/rollback but is gated off when the owned transport is active;
- staging mail-safety MU plugin rewrites/guards recipients and fails closed;
- staging communications takeover is currently enabled during the soak/recovery work.

A compatibility issue was fixed where legacy WP Job Board Pro code could pass `attachments = null` into `wp_mail()`. The owned transport normalizes only this null case without editing the vendor plugin.

Verified safe staging mail coverage has included plain text, HTML, custom `From`, attachments, WP Job Board Pro wrapper behavior and Woo rendering/interception.

### Email risk tiers

Treat mail-triggering behavior by risk, not merely by whether staging recipient rewriting exists.

**Low-risk / rendering or transport validation** can include generic controlled `wp_mail`, template rendering, PHPMailer configuration and intercepted Woo previews.

**Fixture-required** flows include contact-form mail, candidate/employer contact flows, expiry notices, Woo invoices and report emails. Use disposable staging fixtures and verify state transitions.

**High-risk / approval-required** includes applications, acknowledgements, approve/reject flows, invites, meetings, job alerts, registrations, password resets, private messages, orders/payments/refunds/subscriptions, broad Action Scheduler execution and job-board daily notices.

Mail safety protects recipients; it does **not** protect against business-state mutation.

### Password reset special warning

The WP Job Board Pro password-reset path has historically replaced the password before mailing. Never test this on an existing real/staging account that matters. Use an explicitly disposable fixture only after approval.

## 6. WP-Cron and Action Scheduler audit — current workstream 1

A major staging issue was discovered: the staging scheduler had a long-standing backlog.

Initial verified state:

- 44 / 44 WP-Cron events were overdue;
- 15 / 15 Action Scheduler pending actions were overdue;
- cron array structure was valid;
- timezone was correct;
- `DISABLE_WP_CRON` was not originally configured globally;
- no ordinary DB-resident stale lock was initially visible;
- the Action Scheduler queue was stalled as a consequence/symptom.

A LiteSpeed object-cache-backed `doing_cron` transient was later proven to be a **stale constant lock**. The same token persisted for more than 10,000 seconds, far beyond the 60-second lock timeout.

Recovery completed so far:

- temporary staging-only MU recovery guard deployed;
- that guard provides `DISABLE_WP_CRON=true` on staging so normal traffic cannot accidentally spawn the entire backlog;
- stale `doing_cron` was cleared in isolation and remained clear;
- first three approved low-risk housekeeping hooks were executed individually and passed:
  - `delete_expired_transients`
  - `jetpack_clean_nonces`
  - `monsterinsights_cache_daily_cleanup`
- high-risk schedules remained unchanged;
- all 15 Action Scheduler pending actions remained unchanged during those steps.

**Do not remove the staging cron recovery guard yet.**

**Do not run:**

- `wp cron event run --due-now`;
- broad Action Scheduler queue processing;
- `action_scheduler_run_queue` until every pending action intended for execution has been classified/approved.

Likely next cron work is **workstream 1.4**, continuing only with another small pre-classified low-risk batch, with before/after fingerprints and stop conditions.

Before trusting these counts as current, read the latest numbered report from `codex-reports`.

## 7. High-risk scheduled/business hooks

The following categories are not autonomous “housekeeping” simply because they are cron-driven:

- `wp_job_board_pro_email_daily_notices` and job alerts;
- `woocommerce_cancel_unpaid_orders`;
- WooCommerce scheduled sales/order/payment/refund/subscription behavior;
- personal-data cleanup that could remove user/customer data;
- jobs affecting candidates, employers, applications or messages;
- unknown callbacks or callbacks with private/business arguments;
- broad Action Scheduler queue execution.

These require fixtures, rollback understanding, or explicit approval.

## 8. Candidate/employer/payment behavior that must be preserved

Regression testing during refactor should cover at least:

- candidate registration/login/profile/dashboard;
- employer registration/login/profile/dashboard;
- role separation (`administrator`, `employer`, `candidate`) and any intended multi-role behavior;
- job create/edit/publish/expiry lifecycle;
- candidate job application lifecycle and statuses;
- employer access to candidate/profile data based on entitlements;
- private messaging/chat permissions;
- package purchase and WooCommerce Paid Listings entitlements;
- role/package-based visibility of menus, profile sections and features;
- checkout/payment-state integration without creating real payments;
- job alerts and scheduled notices with disposable fixtures only.

The site has historically used candidate/employer-specific links and dashboards, so generic WordPress assumptions are unsafe.

## 9. Other historical findings worth re-checking when relevant

These are **audit leads**, not automatic tasks. Verify current code before changing anything.

### intlTelInput / phone field

Historically a custom phone-field script was enqueued more than once and outside the appropriate WordPress enqueue hook, with `time()` cache busting. If still present, normalize it to normal enqueue hooks, a single dependency chain and stable file versioning such as `filemtime()`.

### Leaflet / GoogleMutant map integration

Historically the job-board map used `L.gridLayer.googleMutant(...)` while the corresponding local plugin was not loaded, causing a runtime error. Re-check only when map work is in scope.

### Staging hardcoded URL/email substitutions

Past staging diffs included environment-specific URL and sender substitutions. Any future cleanup must ensure staging isolation is preserved before removing those edits.

## 10. Refactor roadmap — priority order

This is the strategic order, not permission to execute everything automatically.

### Priority A — finish safe staging scheduler recovery

- continue small low-risk WP-Cron batches;
- classify remaining cron hooks by side effect;
- classify Action Scheduler backlog by hook/group/arguments shape;
- recover mail/report tasks only with mail safety + fixtures;
- recover business-state tasks only with explicit fixtures/rollback approval;
- establish a reliable ongoing staging cron trigger only after backlog is controlled;
- finally remove the temporary cron recovery guard in a dedicated tested task.

### Priority B — complete communications ownership

- keep owned transport and staging mail safety stable;
- inventory every remaining legacy email path;
- add fixture-based coverage for important templates/wrappers;
- move remaining custom mail behavior out of child/vendor code;
- physically remove legacy mail transport only after parity/rollback confidence.

### Priority C — extract legacy child-theme business logic

Classify child-theme customizations into:

1. presentation-only theme behavior;
2. environment/config behavior;
3. reusable business logic that belongs in owned plugins;
4. obsolete diagnostics/debug code.

Move category 3 incrementally into small Raspitajse-owned plugins/modules. Avoid a single large rewrite.

### Priority D — remove vendor customizations safely

For WP Job Board Pro / Paid Listings customizations:

- map every diff to a business requirement;
- implement owned hook/filter/template override where possible;
- test parity;
- then revert/remove vendor modification one concern at a time.

### Priority E — full staging regression suite

Before production consideration, verify:

- candidate flows;
- employer flows;
- job flows;
- packages/checkout entitlements;
- messaging;
- email templates/transport;
- cron and Action Scheduler;
- staging isolation and no real-user outbound effects.

### Priority F — platform hardening after functional parity

Only after the critical refactor/recovery work is stable:

- plugin/core update strategy;
- LiteSpeed/performance checks;
- security/hardening;
- SEO/indexing controls for staging;
- content/landing-page repositioning while preserving application logic.

## 11. What “autonomous” means on this project

Codex may autonomously proceed only when the next action is clearly bounded, staging-only, reversible and low-risk under `AGENTS.md` and `CODEX_WORKFLOW.md`.

A task being technically easy does not make it low-risk.

Examples usually suitable for autonomous work:

- repository inspection and read-only diagnosis;
- feature-branch refactors with tests and no deployment;
- syntax/static checks;
- narrow staging deployment through the approved script after explicit task authorization;
- already-classified low-risk cron housekeeping under the recovery guard;
- documentation and test improvements.

Examples requiring approval or a stop:

- production;
- real-user effects;
- order/payment/refund/subscription mutation;
- application/candidate/employer/message mutation;
- password resets;
- broad cron or Action Scheduler execution;
- destructive or ambiguous DB operations;
- unknown callbacks/side effects;
- weakening mail/cron safety controls;
- secrets or `wp-config.php` exposure;
- branch/history rewriting.

## 12. Evidence hierarchy for Codex

When deciding what to do, use this order:

1. `AGENTS.md` safety rules;
2. current repository and branch state;
3. current staging runtime evidence;
4. latest numbered report on `codex-reports`;
5. `CODEX_WORKFLOW.md` orchestration rules;
6. this project context/audit document;
7. older reports/history only when needed.

This file describes **why** the project is being changed and the known risk map. The latest report describes **where the work currently stopped**.
