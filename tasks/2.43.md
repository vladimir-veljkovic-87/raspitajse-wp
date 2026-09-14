# Zadatak 2.43 — Post-deploy staging business-journey acceptance

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.42
Target environment: staging
Production: FORBIDDEN

## Objective

Verify the final Zadatak 2.25 staging deployment across registration/login infrastructure, role access, packages/checkout, package activation and quota behavior, and WordPress administration.

This is acceptance, not development. Do not modify application, theme, plugin, vendor or WordPress core code. Do not merge, commit or push application code, deploy, rollback, change real users/passwords/emails, or touch production.

PASS classification: `POSTDEPLOY_BUSINESS_JOURNEYS_ACCEPTED`.

## Mandatory preflight

Fetch fresh refs and read `tasks/current.md`, `tasks/README.md`, the final 2.25 report `reports/20260914T103546Z-zadatak-2_25.md`, and the relevant login/registration remediation reports.

Require:

- clean worktree;
- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- all five deployed files and marker equal that SHA;
- Superio 1.3.37 and WooCommerce 11.1.0 unless a later documented change explains otherwise;
- registration enabled and staging mail safety loaded;
- both scheduler guards active;
- user 141 remains Administrator with `manage_options`;
- pending AS 6 / fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- KEEP IDs exactly 32777, 32778, 32779, 32783, 32784, 32848;
- claims 0; AS 32733 complete/attempts 1;
- business `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- cron `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- both staging locks free.

Unexplained drift is BLOCKED. Do not repair it.

## Execution and safety

Use one persistent session-independent worker following the proven Task 2.41/2.42 transport. The same worker holds both staging OS locks through testing, exact cleanup, evidence, report publication and remote verification. Prove PID/session independence and kernel lock ownership before tests. Codex may only poll read-only; no parallel staging process.

Before fixtures, install/retain guards that:

- intercept mail, PHPMailer and SMTP before transport;
- intercept external HTTP while permitting only necessary local staging requests;
- block payment, refund and payment-complete side effects;
- block broad WP-Cron/Action Scheduler runners and protected callbacks;
- record all counters.

Only the exact known Elementor `E_DEPRECATED` signature SHA-256 `5fb64aa49cd5918e5d10b4940b0b8c5eba35a60e61a4c43bf4ff855cf7f6f83d` may be non-blocking. Every other stderr/PHP diagnostic blocks. Do not suppress diagnostics.

Never expose usernames, email addresses, passwords, reset tokens, cookies, nonces or PII in evidence/reports.

## Acceptance matrix

### 1. Registration and login infrastructure

Using browser-like local staging requests, prove:

- registration and actual login pages return HTTP 200 and contain expected forms;
- registration uses `?wjbp-ajax=wp_job_board_pro_ajax_registernew`;
- login uses `?wjbp-ajax=wp_job_board_pro_ajax_login`;
- registration does not fall back to native POST on `/register/`;
- required WP Job Board Pro assets, including the four remediated assets, return HTTP 200, match live content and never return 403;
- dependency ordering does not imply an asset-loading JavaScript failure;
- an invalid registration nonce returns expected security JSON and creates zero users/profiles;
- an empty/invalid login returns expected CAPTCHA/validation JSON and creates no session;
- `users_can_register=1`.

Do not bypass CAPTCHA. If a real interactive CAPTCHA login/registration is unavailable, report that part as NOT TESTED, while independently grading routing/assets/handler rejection. Never claim a browser-authenticated session unless actually proven.

### 2. Role and dashboard access

Do not alter real users.

Use in-memory current-user switching where sufficient. If a persisted fixture is strictly required, use one uniquely prefixed staging-only user with random credentials held privately, no real recipient, a complete ledger and exact deletion.

Prove:

- user 141 has `manage_options` and the deployed redirect rule permits Administrator access to `/wp-admin/`;
- AJAX exception is intact;
- candidate and employer roles do not gain `manage_options`;
- their intended dashboard routing/visibility works;
- neither role can enter restricted administrator pages;
- role/capability state is byte-equivalent after testing.

### 3. Packages and checkout

Prove:

- `/packages/` HTTP 200 with expected template/content contract;
- `/checkout/` HTTP 200 with expected field contract;
- a fresh in-memory canonical `job_package` resolves to the paid-listings package class/type;
- candidate and employer package eligibility remains separated;
- no real order, payment, refund, external request or mail occurs.

### 4. Activation and quota behavior

Reuse exactly:

- `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`;
- SHA-256 `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`.

Verify its hash and run it at most once. Do not change or replace it.

Require 35/35, zero failures and exit 0, including:

- processing/completed-only activation and pending/on-hold exclusion;
- immutable activation and correct boundaries;
- duration/validity separation;
- independent quota exhaustion;
- completed retry idempotence;
- exact removal of all fixture posts/orders/meta and fixture-created AS actions/logs;
- six KEEP actions/logs unchanged;
- no scheduler callback or queue runner.

### 5. WordPress/WooCommerce administration

Prove without changing credentials:

- administrator access contract for user 141 remains valid;
- WooCommerce HPOS admin order renderer completes once;
- expected markup and owned billing hook occur once;
- no persistent product/order/user/profile/session/role/capability fixture remains.

## Final smoke and invariants

Run the identical packages, checkout and admin-renderer smoke and require PASS.

After exact cleanup, require equality with pre-test state:

- Git, all five deployed files and marker remain `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- business/owned-contract/cron/pending fingerprints unchanged;
- pending 6; six KEEP IDs unchanged;
- claims 0; AS 32733 complete/attempts 1;
- registration enabled, mail safety loaded, guards active and admin capability intact;
- all fixture ledger entries absent;
- mail, PHPMailer, SMTP, payment, refund, unexpected external HTTP, broad-runner, protected-action and production counters all 0.

## Failure handling

No application fix, deploy, rollback, retry, password/option repair or drift cleanup is authorized.

On failure, remove only exact task-created fixtures/actions/sessions, prove original protected/deployed state, preserve evidence, publish one precise 2.43 BLOCKED report, release locks and STOP.

If the worker disappears, do not start another acceptance worker. After proving locks free, a cleanup-only process may remove exact task fixtures and verify original state, then publish BLOCKED.

## Report

Publish exactly one report to `codex-reports`:

- Task: `Zadatak 2.43 — Post-deploy staging business-journey acceptance`;
- Result: PASS or BLOCKED;
- PASS classification `POSTDEPLOY_BUSINESS_JOURNEYS_ACCEPTED`;
- live/marker SHA and every matrix result;
- explicit separation of automated checks from unavailable real browser/CAPTCHA tests;
- fixture/AS cleanup and final fingerprint/counter proof;
- locks released;
- production untouched.

Verify remote report path, 40-hex commit, `origin/codex-reports` equality and non-empty readback before releasing locks. Then STOP.
