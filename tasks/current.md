# Zadatak 2.45 — Complete post-deploy business-journey acceptance

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.44
Roadmap milestone: NOW — Post-deploy business-journey acceptance
Target environment: staging
Production: FORBIDDEN

## Objective

Complete the acceptance matrix deferred by Task 2.43 without reopening completed Zadatak 2.25 and without blocking on unrelated, unexecuted vendor scheduler rows.

This is acceptance, not development or scheduler remediation. Do not modify application/vendor/core code, Git history, deployed files, marker, options, real users/passwords/emails, or production. Do not deploy or rollback.

PASS classification: `POSTDEPLOY_BUSINESS_JOURNEYS_ACCEPTED`.

## Mandatory preamble

Fetch fresh refs and read:

- `tasks/README.md`, `tasks/ROADMAP.md`, and this task;
- final 2.25 report `reports/20260914T103546Z-zadatak-2_25.md`;
- complete reports 2.43 and 2.44.

Require clean worktree, `origin/staging`, all five live files and marker at `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`; business/owned-contract/cron fingerprints equal the final 2.25 report; claims 0; AS 32733 complete/attempts 1; scheduler guards/admin capability/registration/mail safety active; both staging locks free.

Any application, business-state, protected-action, claim, deployed-file or marker drift is blocking. Do not repair it.

## Corrected scheduler gate

Do not require the global pending queue to equal the historical count of six.

Under both locks capture an authoritative dynamic T0 snapshot:

- the six protected KEEP IDs 32777, 32778, 32779, 32783, 32784 and 32848;
- vendor rows 32867 and 32868 if still present;
- every other pre-existing pending row.

Apply these rules:

1. The six protected KEEP rows/logs must match their final 2.25 state exactly.
2. ID 32867 remains `WPForms DROP / PROVEN`; leave it untouched.
3. ID 32868 remains observed AIOSEO sitemap maintenance with unresolved enqueue provenance; leave it untouched.
4. Any other pre-existing row may enter the observational vendor T0 set only when static/read-only metadata shows a named vendor owner, no business-object reference, attempts 0 and claim 0.
5. An unknown/Raspitajse-owned row, business-object reference, attempt or claim is BLOCKED.
6. Every pre-existing T0 row/log must remain byte/state-identical at T1.
7. Task-created fixture actions must be separately ledgered and removed by exact ID.
8. Do not execute, claim, cancel, delete, reschedule or normalize any pre-existing action.

Do not spend more than one bounded preflight attribution pass on unrelated vendor rows and do not create another scheduler archaeology task. Record observational rows in the report.

## Persistent guarded execution

Use one session-independent worker following the proven 2.41/2.42 transport. The same worker holds both OS locks through T0, tests, exact fixture cleanup, T1, report publication and remote verification.

Retain guards for mail/PHPMailer/SMTP, external HTTP, payment/refund/payment-complete paths, broad cron/Action Scheduler runners and protected callbacks. All side-effect counters must remain 0.

Only the exact known Elementor diagnostic SHA-256 `5fb64aa49cd5918e5d10b4940b0b8c5eba35a60e61a4c43bf4ff855cf7f6f83d` may be classified non-blocking. Every other PHP diagnostic/stderr blocks. Do not suppress diagnostics or expose credentials/PII.

## Acceptance matrix

### Registration and login infrastructure

Using browser-like local staging requests, require:

- registration and login pages HTTP 200 with expected forms;
- registration targets `?wjbp-ajax=wp_job_board_pro_ajax_registernew`;
- login targets `?wjbp-ajax=wp_job_board_pro_ajax_login`;
- no registration native POST fallback to `/register/`;
- required WP Job Board Pro assets, including the four remediated files, return 200, match live content and never return 403;
- invalid registration nonce returns expected security JSON and creates zero users/profiles;
- empty/invalid login returns expected CAPTCHA/validation JSON and creates no session;
- `users_can_register=1`.

Do not bypass CAPTCHA. Real interactive CAPTCHA login/registration may be reported NOT TESTED and does not block the automated infrastructure result. Never claim an authenticated browser session unless proven.

### Roles and dashboards

Without altering real users, prove:

- user 141 remains Administrator/`manage_options` and is permitted into `/wp-admin/`;
- AJAX exception is intact;
- candidate/employer roles do not gain `manage_options`;
- intended role dashboard routing/visibility works;
- candidate/employer cannot access restricted administrator pages;
- capability state is identical after testing.

Use in-memory identity switching. A persisted user fixture is forbidden unless absolutely required; if required, it must be uniquely prefixed, privately credentialed, never mailed, ledgered and exactly deleted.

### Packages, checkout, activation and quotas

Require:

- `/packages/` and `/checkout/` HTTP 200 with expected contracts;
- canonical in-memory `job_package` class/type;
- candidate/employer package eligibility separation;
- no real order/payment/refund/mail/external request.

Verify and run at most once the immutable runtime harness:

- path `/tmp/raspitajse-task-2.25-final-atomic.qafdp0/runtime-acceptance.php`;
- SHA-256 `1eaf8f57ee0e39debff7c9a6d6757659d986f2d680282b39a2e466da3d27fe1d`;
- require 35/35, zero failures, exit 0;
- require exact cleanup of all fixture posts/orders/meta and task-created AS rows/logs.

### Administration and final smoke

Require:

- WooCommerce HPOS admin order renderer entered/completed once;
- expected markup and owned billing hook once;
- packages, checkout and admin renderer final smoke PASS;
- no persistent fixture/session/role/capability change.

## Final invariants

T1 must equal T0 for:

- Git, five live files and marker;
- business, owned-contract and cron fingerprints;
- all six protected KEEP rows/logs;
- every observational pre-existing vendor row/log;
- claims and AS 32733;
- users/options/roles;
- registration, guards, mail safety and admin capability.

All task fixture ledger entries must be absent. Mail, PHPMailer, SMTP, payment, refund, unexpected external HTTP, broad-runner, protected-action and production counters must be 0.

## Failure

No fix-forward, deploy or scheduler remediation. Clean only exact task-created fixtures/actions/sessions, prove original state, publish one precise 2.45 BLOCKED report, release locks and STOP.

If the worker disappears, start no second acceptance worker. A cleanup-only holder may remove exact task fixtures and verify T0, then publish BLOCKED.

## Report

Publish exactly one report to `codex-reports`:

- Task: `Zadatak 2.45 — Complete post-deploy business-journey acceptance`;
- Result: PASS or BLOCKED;
- PASS classification `POSTDEPLOY_BUSINESS_JOURNEYS_ACCEPTED`;
- every matrix result and automated-vs-interactive distinction;
- protected and observational scheduler T0/T1 equality;
- fixture cleanup and zero counters;
- deployed SHA/marker unchanged;
- locks released;
- production untouched.

Verify remote report path, 40-hex commit, `origin/codex-reports` equality and non-empty readback before releasing locks. Then STOP.
