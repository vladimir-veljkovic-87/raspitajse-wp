# Zadatak 2.51.7 — Attribute zero-event job alert

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.6
Target: staging
Production: FORBIDDEN
Time budget: 15 minutes

## Goal

Determine exactly why the single Task 2.51.6 invocation of the owned job-alert delivery service produced zero mail events.

Avoid another blind retry. If the cause is only an incorrect synthetic fixture, run one corrected focused probe in this task. If it is product code or configuration, report the exact failing condition and smallest repair without implementing it.

## Lean investigation

Follow `tasks/README.md`. Read only:

- the exact Task 2.51.6 report and retained focused evidence, if still present;
- `Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::process`;
- its directly called owned query/mail adapter methods;
- the minimum vendor field/status definitions needed to understand matching.

Do not inspect unrelated mail types, expiry, cron queues, Action Scheduler, historical reports or global database state. Do not create a harness/finalizer or broad trace.

List the ordered early-return/matching conditions and identify the first condition not satisfied by the 2.51.6 fixture. Maximum one small read-only diagnostic query before deciding.

## Conditional corrected probe

Only if the first failing condition proves the application is correct and the 2.51.6 fixture was malformed:

- make the smallest fixture correction;
- explicitly include every auto-created related profile/post in the cleanup ledger;
- run the exact delivery callback once;
- intercept mail before transport;
- verify one intended candidate event, matching job title and staging link, with no unresolved/production/paid-flow URL;
- clean every recorded fixture by exact ID and restore initial counts.

No second correction or retry is allowed.

If the cause is product code/configuration, do not mutate fixtures or application state after attribution.

## Boundaries

No application change, deploy, Git integration, setting/schedule mutation, real email, external HTTP, payment, expiry callback or broad scheduler runner.

## Result

Return exactly one:

- `PASS: JOB_ALERT_HARNESS_CORRECTED`;
- `BLOCKED: JOB_ALERT_PRODUCT_DEFECT`;
- `BLOCKED: JOB_ALERT_CONFIGURATION_DEFECT`;
- `BLOCKED: JOB_ALERT_CAUSE_NOT_PROVEN`.

Publish one concise report with the exact first failing condition, classification, any corrected-probe result, cleanup and unchanged Git/live/marker baseline. STOP after the report.
