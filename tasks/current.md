# Zadatak 2.50 — Lean job lifecycle and application smoke

Status: READY
Baseline: 11fbf2014026a5e4486665a62bb42b4e636d9940
Previous task: 2.49
Target: staging
Production: FORBIDDEN
Time budget: 30 minutes
Mode: controlled staging fixtures, no application-code change

## Goal

Prove one essential job-board journey using temporary staging fixtures:

1. employer creates and publishes jobs within the free limit;
2. the fourth active job is blocked;
3. candidate applies once;
4. duplicate application is rejected;
5. employer sees only authorized applicant data;
6. freeing a slot allows the blocked job to publish.

PASS classification: `JOB_APPLICATION_CORE_SMOKE_PASS`.

This task validates server/business behavior. It does not automate a visual browser or CAPTCHA session. Manual browser confirmation remains a separate short owner check.

## Lean rules

Read only `tasks/README.md`, this task and the exact job/application functions invoked by the probe. Reuse the deployed Task 2.47 quota policy. Do not read historical reports, inventory unrelated hooks, rebuild the concurrency test or create a deployment/finalizer script.

No feature branch, code edit, commit, push or deploy is authorized. If a product defect is found, report its exact failing step and stop; fixing it is a separate small task.

If a command gives no output for 10 minutes, stop. Maximum one correction for a probe syntax/bootstrap mistake.

## Preflight

Verify only:

- local staging, `origin/staging`, live owned files and marker equal the baseline;
- clean worktree and staging environment;
- Task 2.47 free-access/quota and Task 2.48 free-journey policies are active;
- both staging locks are free;
- record initial counts for users, employer/candidate profiles, jobs and applications.

Do not inspect or gate on unrelated vendor scheduler rows.

## Fixtures and safety

Use one synthetic employer and one synthetic candidate with reserved non-deliverable email domains. Generate random credentials in memory and never print or save them.

Before creation:

- install existing mail/payment/external-HTTP guards;
- create a simple exact-ID ledger;
- acquire the staging mutation lock.

Use normal WordPress/WP Job Board Pro APIs. Do not use raw SQL for creation or deletion. Do not run broad WP-Cron or Action Scheduler.

Keep the lock only while fixture rows are being created/changed/cleaned. Report writing and Git operations happen outside it.

## Focused journey

Perform these checks:

1. Create the temporary employer and candidate with correct roles and reciprocal profile relations.
2. Confirm neither user has or needs an order, product or job-package entitlement.
3. As the employer, create three valid unexpired `job_listing` posts and request publication. All three must become public and belong to that employer.
4. Create a fourth job with identifiable synthetic content and request publication. It must remain non-public/draft, preserve its content and expose the owned quota reason.
5. As the candidate, apply to one published job through the normal application service/handler with only the minimum valid fixture data.
6. Confirm exactly one application links the correct candidate, job and employer.
7. Repeat the same application attempt. The system must not create a second application.
8. Confirm the employer can read the application for its own job.
9. Create a second temporary employer only if required for the authorization check; otherwise use an isolated ownership-context probe. A different employer must not read or change the first employer’s application/job.
10. Unpublish one of the three active jobs, then republish the previously blocked fourth job. Final active count must be exactly three.
11. Confirm no cart, checkout, order, entitlement, payment or scheduler row was created.
12. Clean all fixture applications, jobs, profiles and users by exact ledgered IDs using normal APIs. Verify final counts return to preflight values.

Admin moderation and concurrency were already proven in Task 2.47 and must not be repeated.

## Side effects

Expected database mutations are limited to ledgered fixture users/profiles/jobs/applications and their ordinary metadata, followed by exact cleanup.

Required final counters:

- real mail/PHPMailer/SMTP transport: 0;
- payment/gateway: 0;
- external HTTP transport: 0;
- broad cron/Action Scheduler runner: 0;
- unexpected order, entitlement or scheduler rows: 0;
- new PHP notice/warning/error/fatal: 0.

If cleanup fails, keep the lock, report the exact remaining IDs and perform only one bounded cleanup correction. Never use broad deletion.

## Result and report

PASS requires all 12 checks, exact cleanup, restored initial counts and zero prohibited side effects.

BLOCKED means one concrete business assertion or cleanup step failed. Do not modify production code during this task.

Publish one short report containing:

- result/classification and baseline;
- a 12-row PASS/BLOCKED table;
- fixture object types and sanitized IDs;
- before/after counts;
- side-effect counters;
- any exact defect;
- locks released;
- no code/deploy/production change;
- manual-browser reminder for employer job form and candidate Apply button.

Verify the remote report and STOP. Do not begin Task 2.51.
