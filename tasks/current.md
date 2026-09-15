# Zadatak 2.51.4 — Lean background-business smoke

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.3
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes

## Goal

Prove two launch-relevant outcomes on staging:

1. an expired public job stops consuming one of the employer's three active slots;
2. a matching candidate job alert creates one correct email event.

No application change or deployment is authorized.

## Lean execution

Follow `tasks/README.md`. Read only the directly responsible code and current settings.

This is not a global scheduler audit:

- do not run any complete cron or queue runner;
- do not inventory or investigate unrelated scheduled work;
- invoke only the two directly responsible business callbacks;
- use one short static ownership check and one focused runtime smoke;
- do not create a large harness, finalizer, evidence framework or historical fingerprint comparison;
- stop if a callback cannot be isolated safely.

## Acceptance

Use the smallest synthetic fixtures needed and remove them by exact recorded IDs.

Expiry:

- one expired public job leaves the active/public set;
- one employer slot becomes available;
- repeating the same callback causes no additional state transition.

Job alert:

- one matching alert event is generated for the intended candidate;
- rendered content contains the correct staging job data/link;
- no production or paid-flow link is present;
- mail is intercepted before transport, so no real email is sent;
- verify the implementation's actual duplicate-prevention boundary without inventing a stricter rule.

All fixture counts must return to their initial values. External HTTP, payment, real email and broad scheduler executions must remain zero.

Do not modify production, application code, settings, schedules or unrelated records.

## Result

Return one of:

- `PASS: BUSINESS_SCHEDULER_SMOKE`;
- `BLOCKED: EXPIRY_CALLBACK_DEFECT`;
- `BLOCKED: JOB_ALERT_CALLBACK_DEFECT`;
- `BLOCKED: CALLBACK_NOT_SAFELY_ISOLATABLE`;
- `BLOCKED: OWNED_BACKGROUND_REDESIGN_REQUIRED`.

Publish one concise report containing callback ownership, both business outcomes, intercepted-mail count, fixture cleanup and unchanged final staging baseline. STOP after the report.
