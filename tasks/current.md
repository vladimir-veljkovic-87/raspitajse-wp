# Zadatak 2.51.9 — Fix owned job-alert placeholders

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.8
Target: staging
Production: FORBIDDEN
Time budget: 25 minutes

## Goal

Fix the proven owned job-alert integration defect: Raspitajse placeholders remain unresolved because the vendor `job_alert_notice` renderer does not declare them.

PASS classification: `JOB_ALERT_PLACEHOLDERS_DEPLOYED`.

## Scope

Change only the Raspitajse-owned Communications plugin. Do not modify vendor/core/theme files or stored template options.

In the owned job-alert mail adapter, explicitly substitute only the exact owned placeholders already computed and passed by the delivery service, including `{{job_data}}` and the existing `{{newest_*}}` values, before the unresolved-placeholder guard.

Requirements:

- preserve normal vendor variables and rendering;
- preserve localization, escaping and existing HTML behavior;
- do not introduce hardcoded staging/production hosts;
- do not remove or weaken the unresolved-placeholder validation;
- unknown placeholders must still fail closed;
- no payment/package/checkout content.

Prefer a small deterministic owned substitution helper in the existing file over extending vendor declarations globally.

## Lean implementation

Follow `tasks/README.md`. Read only this task, the 2.51.8 report, and the directly involved delivery/mail-adapter code.

Create a feature branch from the exact baseline. No large harness, finalizer, scheduler inventory or historical fingerprint suite.

Run:

1. PHP lint and one focused static assertion proving exact owned placeholders are handled while an unknown placeholder remains rejected.
2. One focused pre-deploy direct job-alert probe with the minimal synthetic candidate/alert/job fixture:
   - valid claim token;
   - exactly one matching job selected;
   - exactly one candidate email event intercepted before transport;
   - owned placeholders resolved;
   - expected job title and staging job link present;
   - no unresolved, production or paid-flow URL;
   - exact-ID cleanup restores initial counts.

Do not run expiry, other email templates, WP-Cron or Action Scheduler.

## Deploy and integration

After the focused probe passes:

1. push the feature branch;
2. use the standard changed-file staging deployment for only the modified owned file under the staging mutation lock;
3. verify repository/live hash equality and marker;
4. release the lock;
5. run one post-deploy focused job-alert probe with mail intercepted;
6. if it fails, restore the baseline file and marker under the lock and report BLOCKED;
7. if it passes, fast-forward `staging` and push without force;
8. verify clean Git/live/marker alignment.

No real email, external HTTP, payment or broad scheduler execution.

## Result

- `PASS: JOB_ALERT_PLACEHOLDERS_DEPLOYED` if pre/post probes, cleanup and alignment pass.
- Otherwise return one precise `BLOCKED` reason and rollback status.

Publish one concise report with changed file, final SHA, resolved-placeholder evidence, event count, cleanup, final alignment and production untouched. STOP after the report.
