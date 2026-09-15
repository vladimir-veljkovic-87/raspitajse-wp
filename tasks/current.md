# Zadatak 2.51.5 — Direct job-expiry smoke

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.4
Target: staging
Production: FORBIDDEN
Time budget: 10 minutes

## Goal

Prove only that the existing job-expiry callback removes an expired public job from the employer's active-job quota and is idempotent.

No application change, deploy or Git integration is authorized.

## Lean execution

Follow `tasks/README.md`. Reuse the callback already identified in 2.51.4:

`WP_Job_Board_Pro_Job_Listing::check_for_expired_jobs`

Do not search for another implementation, inspect historical scheduler reports, test job alerts, create a harness/finalizer, enumerate queues, run WP-Cron, or run Action Scheduler.

If execution cannot finish within 10 minutes, stop and publish the last checkpoint.

## Focused smoke

Confirm the declared baseline and staging environment. Before mutation, confirm there are no unrelated currently-due public jobs that this callback would alter. If any exist, return `BLOCKED: EXPIRY_CALLBACK_NOT_ISOLATABLE`.

Under the standard staging mutation lock:

1. create one synthetic employer and one synthetic public job with an expiry time in the past;
2. record exact fixture IDs and the employer's active-job count;
3. intercept any email before transport;
4. invoke the exact callback directly once;
5. verify the job is no longer active/public and the employer active-job count decreased by exactly one;
6. verify the Raspitajse three-active-job policy recognizes the released slot;
7. invoke the same callback once more and verify no further state or notification change;
8. delete only the recorded fixtures and confirm relevant initial counts return;
9. release the lock.

No real email, external HTTP, payment, broad scheduler execution, option change or unrelated record mutation is allowed.

## Result

- `PASS: DIRECT_JOB_EXPIRY_SMOKE` if expiry, quota release, idempotency and cleanup pass.
- Otherwise return one precise `BLOCKED` reason.

Publish one short report with callback ownership, before/after status and active count, idempotency, intercepted-mail count, cleanup and unchanged Git/live/marker baseline. STOP after the report.
