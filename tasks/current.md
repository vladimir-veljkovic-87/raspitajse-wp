# Zadatak 2.51.1 — Fix welcome-email login link

Status: READY
Baseline: 11fbf2014026a5e4486665a62bb42b4e636d9940
Previous task: 2.51
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes

## Goal

Resolve the single Task 2.51 blocker: the enabled candidate registration/welcome email lacks the canonical staging login link.

PASS classification: `WELCOME_EMAIL_LOGIN_LINK_DEPLOYED`.

Do not retest password reset, employer application notification or candidate application confirmation; they already passed.

## Lean rules

Read only `tasks/README.md`, this task, the Task 2.51 report and the exact login-page/email-template code and settings needed for this defect.

Do not create a large harness or finalizer script. Do not use broad scheduler, database, mail or security inventories. If a command has no output for 10 minutes, stop. Maximum one correction for a probe error.

## Establish the correct URL first

Before changing code, determine the canonical login URL from current WordPress/WP Job Board Pro page settings and confirm its final HTTP destination.

Do not assume `/login-register/` merely because the previous probe expected it. Record the actual configured page ID, generated URL and redirect destination without exposing secrets.

If the welcome email already contains the actual canonical login URL, make no product change and report the previous assertion as a harness defect.

## Minimal repair

If the actual canonical login URL is missing:

- implement the smallest update-safe fix in an existing Raspitajse-owned plugin;
- prefer an existing email-content filter or owned mail adapter;
- use the existing generated `login_url` value or canonical WordPress URL helper;
- do not hardcode the staging host or a production domain;
- do not edit WP Job Board Pro, Superio, WordPress, WooCommerce or other vendor/core files;
- do not mutate the stored vendor template option unless no owned filter exists and the report explains why; Git-managed owned code is preferred;
- preserve the current subject, other body content and localization;
- do not add package, pricing, checkout or payment content.

Create a feature branch from the exact baseline. The diff should be limited to the minimum owned file(s).

## Focused test

Test only candidate registration/welcome rendering with one synthetic candidate fixture:

- message is generated once;
- intended role is candidate;
- canonical login URL is present and points to staging;
- no unresolved placeholder, production URL or paid-commerce link;
- `pre_wp_mail` intercepts before PHPMailer/SMTP;
- no real email, external HTTP, payment or scheduler execution;
- fixture is removed by exact ID and initial counts return.

Do not repeat the other Task 2.51 messages.

## Deploy and integrate

After lint and the focused test pass:

1. push the feature branch;
2. acquire the staging mutation lock only for changed-file deployment and marker update;
3. deploy the exact owned changed file(s), verify hashes and release the lock;
4. run one post-deploy welcome-email render check;
5. if it fails, reacquire the lock, restore baseline file(s)/marker and report BLOCKED;
6. if it passes, fast-forward `staging` and push without force;
7. verify Git/live/marker alignment and publish the short report outside the lock.

No generated orchestration script.

## Report

Publish one concise report containing:

- actual canonical login URL path;
- whether the previous assertion was valid;
- exact changed files and final SHA, or no-change conclusion;
- pre/post welcome-email result;
- transport counters and fixture cleanup;
- final Git/live/marker state;
- locks released and production untouched.

STOP after PASS or BLOCKED. Do not start another task.
