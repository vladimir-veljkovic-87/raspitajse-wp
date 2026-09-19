# Zadatak 2.56 — Final launch-critical staging E2E gate

Status: READY
Baseline: ff42719f218e440b168b57690b70a2402ee66809
Previous task: 2.55
Target: staging
Production: FORBIDDEN
Time budget: 45 minutes
Mode: controlled staging fixtures; no application-code changes

## Required result

This task is the final server-side launch-critical end-to-end gate for staging.

PASS classification:

`LAUNCH_CRITICAL_E2E_READY`

PASS means the currently intended launch journey works end-to-end on staging with exact fixture cleanup and no prohibited side effects:

1. authentication/role routing is intact;
2. an employer can publish jobs up to the free launch limit and the fourth active job is blocked;
3. a candidate can apply once and duplicate application is rejected;
4. employer/candidate authorization boundaries hold;
5. launch-critical transactional messages render correctly while transport remains blocked on staging;
6. the already-proven owned scheduler remains configured and healthy without being executed by this task;
7. staging returns to its exact pre-fixture business state.

Do not modify application code to make this task pass. If a product defect is found, report one precise BLOCKED reason and STOP after cleanup.

## Lean execution rules

Read only:

- `tasks/README.md`;
- this task;
- the exact currently active auth/routing, free-access/quota, application and communications code required by the journey;
- final 2.55 report only to confirm the scheduler gate already passed.

Do not read broad historical reports, rebuild prior test frameworks, inventory unrelated vendor hooks, or add new persistent test tooling.

Use one bounded temporary probe/harness only if needed. Maximum one correction for a probe/bootstrap mistake.

If any command has no useful output for 10 minutes, stop and report the checkpoint. Do not iterate indefinitely.

## Preconditions

Before fixtures, verify:

- local `staging`, `origin/staging`, live deploy marker = `ff42719f218e440b168b57690b70a2402ee66809`;
- clean worktree;
- staging environment;
- WordPress `7.1.1`;
- WPForms Lite `2.0.2`;
- active child theme remains `superio-child`;
- Raspitajse Communications and Raspitajse Commerce load;
- both staging mutation/owned-runner locks are free;
- Hostinger owned-runner evidence still exists from the accepted scheduler setup;
- no currently running owned-runner process overlaps this test.

If baseline or environment differs, STOP before mutation.

## Safety setup

Before creating fixtures:

- acquire the standard staging mutation lock;
- install the existing staging mail/payment/external-HTTP guards;
- create an exact ledger of every temporary user/profile/job/application ID;
- capture a compact T0 projection:
  - user/profile/job/application counts;
  - active plugin/theme set hash;
  - three owned cron event/callback fingerprints;
  - Action Scheduler item 32733 status/attempts;
  - deploy marker;
  - source HEAD.

Use reserved non-deliverable email domains and random credentials held only in memory. Never print passwords, reset tokens, cookies, nonces, raw email bodies, or user PII.

Do not use raw SQL to create/delete business fixtures. Use normal WordPress/WP Job Board Pro/Raspitajse APIs.

## Journey A — authentication and role boundaries

Create one synthetic employer and one synthetic candidate with canonical reciprocal profile relationships.

Verify:

- administrator role/capability routing remains intact using an existing admin context without exposing identity;
- employer resolves to its employer profile and may access employer dashboard/submission authorization;
- candidate resolves to its candidate profile and may access candidate dashboard/application authorization;
- employer cannot obtain `manage_options` or candidate-only authority;
- candidate cannot obtain `manage_options` or employer-only authority;
- AJAX remains exempt from non-admin wp-admin redirect;
- login/register/password-reset/dashboard/application entry routes are not captured by launch redirects.

HTTP probe each only once where applicable:

- home;
- login/register entry;
- jobs listing;
- candidate dashboard route;
- employer dashboard route.

No CAPTCHA automation and no real password-reset email. If real CAPTCHA/browser interaction is required, record `MANUAL_BROWSER_CHECK_REQUIRED` rather than failing the server-side gate.

## Journey B — employer free-launch quota

Using the synthetic employer and the currently active free-launch policy:

1. create and publish three valid unexpired job listings through the normal submission/service path;
2. confirm all three are public, owned by the employer and require no order/package entitlement;
3. create a fourth valid job and request publication;
4. require that the fourth remains non-public/draft and exposes the owned quota reason;
5. unpublish one of the first three;
6. publish the previously blocked fourth;
7. require final active count for that employer = exactly three.

Do not create cart, checkout, order, package entitlement or payment state.

If the currently active product policy no longer uses a three-active-job free limit, STOP with `BLOCKED: LAUNCH_POLICY_DRIFT` rather than silently changing the expected behavior.

## Journey C — candidate application

Using the synthetic candidate:

1. apply once to one published fixture job through the normal application path;
2. require exactly one application connected to the correct candidate/job/employer;
3. repeat the same application attempt;
4. require no second application;
5. require the owning employer can read the application;
6. require a different employer context cannot read/change the first employer's job/application.

Create a second temporary employer only if required for the isolation check; ledger and clean it exactly.

Do not upload real CVs or files. Use the minimum valid synthetic data accepted by the current application path.

## Journey D — transactional message rendering, transport blocked

Trigger only the minimum enabled launch-critical message producers for the synthetic fixtures, using their normal service/callback paths:

- account registration/welcome;
- password-reset rendering/request path if safely interceptable without a real reset delivery;
- employer notification for candidate application;
- candidate application confirmation/status notification if currently enabled;
- job submission/publication notification if currently enabled.

For each enabled event require:

- intended recipient role is correct;
- subject/body are non-empty;
- no unresolved template placeholder remains;
- links point to staging, not production;
- no pricing/package/checkout/payment link appears unless intentionally part of the current launch policy;
- `pre_wp_mail` intercepts before PHPMailer/SMTP transport.

An intentionally disabled event is PASS only when current hook/setting proves it is disabled. Do not enable features merely for this test.

Report only role labels, hashes and boolean assertions. Never report raw bodies or addresses.

## Journey E — scheduler continuity check

Do NOT run normal scheduler execution.

Verify only:

- the exact Hostinger owned runner configuration is still present/evidenced;
- `DISABLE_WP_CRON=true`;
- exactly three owned hourly events/callbacks remain;
- no continuation event;
- no competing legacy job-alert/job-expiry path;
- item 32733 has the same status/attempts as T0.

Do not invoke `--check-only` again unless the current source/deploy alignment cannot otherwise be proven. The scheduler gate already passed in 2.55 and should not be re-tested unnecessarily.

## Cleanup

Cleanup is part of PASS, not an optional finalizer.

Delete only ledgered fixture objects through normal APIs in dependency-safe order:

1. applications;
2. jobs;
3. employer/candidate profile posts;
4. temporary users.

If a second employer was created, remove it too.

After cleanup require:

- all T0 user/profile/job/application counts restored;
- no fixture posts/users/meta remain;
- no cart/session/order/package-entitlement residue attributable to the task;
- owned cron fingerprints unchanged;
- Action Scheduler item 32733 unchanged;
- plugin/theme set unchanged;
- source HEAD and deploy marker unchanged;
- worktree clean.

If exact cleanup fails, make at most one bounded correction using only ledgered IDs. Never use broad deletion.

## Side-effect limits

Required final counters/state:

- real SMTP/PHPMailer transport: 0;
- payment/gateway/refund execution: 0;
- actual external WordPress HTTP transport: 0;
- broad WP-Cron runner: 0;
- Action Scheduler execution: 0;
- owned scheduler callback execution attributable to this task: 0;
- production access: 0;
- source/deploy mutation: 0;
- unexpected order/package-entitlement rows: 0.

Expected mutations are only the ledgered temporary staging fixtures and ordinary metadata needed for the journey, followed by exact cleanup.

## Result handling

### PASS

Return:

`PASS: LAUNCH_CRITICAL_E2E_READY`

only if every enabled journey above passes, cleanup is exact and all prohibited side-effect counters remain zero.

### BLOCKED

Return one precise business or cleanup blocker, for example:

- auth/role routing failure;
- quota enforcement failure;
- application/authorization failure;
- transactional render/recipient/link failure;
- scheduler continuity drift;
- cleanup failure.

Do not fix application code inside this task.

## Final report

Publish one concise report containing:

- result/classification;
- baseline/final SHA and deploy marker;
- auth/role PASS table;
- quota journey PASS table;
- application journey PASS table;
- transactional email render table;
- scheduler continuity result;
- before/after fixture counts;
- zero-side-effect counters;
- exact cleanup proof;
- `MANUAL_BROWSER_CHECK_REQUIRED` items, if any;
- production touched: NO.

Verify the remote report and STOP. Do not begin UI/legal/cutover work automatically.
