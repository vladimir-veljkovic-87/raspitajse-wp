# Zadatak 2.51.6 — Direct job-alert smoke

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.5
Target: staging
Production: FORBIDDEN
Time budget: 10 minutes

## Goal

Prove only that one matching candidate job alert produces one correct email event through the existing isolated delivery callback.

No application change, deploy or Git integration is authorized.

## Lean execution

Follow `tasks/README.md`. Reuse the exact `Delivery_Service::process` alert callback and unique filter-title seam already identified during 2.51.4.

Do not search for another implementation, retest expiry, inspect historical scheduler reports, create a harness/finalizer, enumerate queues, run WP-Cron, or run Action Scheduler.

If execution cannot finish within 10 minutes, stop and publish the last checkpoint.

## Focused smoke

Confirm only the declared baseline and staging environment.

Under the standard staging mutation lock:

1. create the minimum synthetic candidate, one alert and one matching public job;
2. record exact fixture IDs and initial relevant counts;
3. intercept mail before PHPMailer/SMTP;
4. invoke the exact alert delivery callback directly once for the recorded alert;
5. verify exactly one email event for the intended candidate role;
6. verify subject/body contain the matching job title and correct staging job link;
7. reject unresolved placeholders, production URLs and package/pricing/cart/checkout/payment links;
8. record the implementation's existing retry/deduplication marker or boundary without invoking the callback a second time;
9. delete only recorded fixtures, restore initial counts and release the lock.

Real email, external HTTP, payment, expiry processing, broad scheduler execution, settings changes and unrelated record mutations must remain zero.

## Result

- `PASS: DIRECT_JOB_ALERT_SMOKE` if rendering, recipient role, link validation and cleanup pass.
- Otherwise return one precise `BLOCKED` reason; do not implement a repair in this task.

Publish one short report with callback ownership, event count, content/link result, mail interception, retry/deduplication boundary, cleanup and unchanged Git/live/marker baseline. STOP after the report.
