# Zadatak 2.51.8 — Capture job-alert failure category

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.7
Target: staging
Production: FORBIDDEN
Time budget: 15 minutes

## Goal

Identify the exact saved inner failure behind the 2.51.7 job-alert result `retryable_failed`.

Do not perform another blind acceptance retry and do not implement a repair.

## Lean attribution

Follow `tasks/README.md`. Read only the 2.51.7 report/retained focused evidence, the exact delivery service failure paths, and the directly used owned adapters.

First try to prove a unique cause read-only by matching the corrected fixture inputs to the ordered exception/failure branches. If one exact cause is proven, publish it without creating fixtures.

Do not inspect unrelated emails, expiry, global logs, cron/Action Scheduler queues, or historical reports. Do not create a harness/finalizer or broad trace.

## One diagnostic probe only if needed

If static attribution is not unique, run one minimal diagnostic probe:

- use a valid 32-character claim token;
- recreate only the minimum synthetic candidate, alert and matching job;
- record the auto-created candidate profile and every fixture ID before callback execution;
- intercept mail before transport;
- invoke `Raspitajse_Communications_Candidate_Job_Alert_Delivery_Service::process` exactly once;
- immediately read the owned alert-delivery aggregate/record before cleanup and capture the sanitized `failure_category`, retry state and non-secret exception class/message hash;
- then clean every fixture by exact ID and restore initial counts.

Do not retry or correct the fixture in this task.

No real email, external HTTP, payment, expiry callback, broad scheduler execution, code/deploy/Git/configuration change or production access.

## Result

Return exactly one:

- `PASS: JOB_ALERT_FAILURE_ATTRIBUTED` with the exact first failing condition and classification as harness, product or configuration;
- `BLOCKED: JOB_ALERT_FAILURE_NOT_OBSERVABLE` if the owned state still cannot expose it safely.

The report must give the smallest recommended next action, cleanup result and unchanged Git/live/marker baseline. STOP after the report.
