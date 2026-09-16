# Zadatak 2.51.13 — Deploy job-alert fix with correct recipient assertions

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Feature SHA: e40619b99563871889caffbd082f0da9d1cd25d3
Previous task: 2.51.12
Target: staging
Production: FORBIDDEN
Time budget: 15 minutes

## Goal

Redeploy the already committed and runtime-proven one-file job-alert fix, using recipient assertions that respect staging mail-safety rewriting.

No application code change or new commit is authorized.

PASS classification: `JOB_ALERT_PLACEHOLDERS_DEPLOYED`.

## Exact starting state

Require:

- branch `feature/task-2.51.9-job-alert-placeholders`;
- local and remote feature aligned and clean at the declared Feature SHA;
- `origin/staging`, live owned file and deploy marker remain at the declared Baseline after the successful 2.51.12 rollback;
- feature changes exactly one owned Communications file.

Any deviation is BLOCKED. Do not modify the feature commit.

Reuse all passed implementation, static and runtime evidence. No pre-deploy probe.

## Deploy

Deploy only the committed changed owned file from the Feature SHA and update the marker under the standard staging mutation lock. Verify live/feature file hash equality and release the lock.

## One post-deploy smoke

Use normal live WordPress bootstrap; do not manually load repository classes.

Reuse the proven 2.51.12 fixture and all passed assertions. Correct only recipient validation:

1. At the owned adapter/business context, verify the intended recipient identity/address corresponds to the synthetic candidate.
2. At the final mail-safety/transport boundary, verify To contains exactly the single configured staging safety recipient, with no Cc/Bcc.
3. Never require the final rewritten transport recipient to equal the synthetic candidate address.
4. Never print or report either address.

Also require:

- public result `delivered`;
- one callback and one adapter-context event;
- one selected fixture job;
- expected title and canonicalized staging job link;
- no unresolved, production or paid-flow URL;
- real transport count zero;
- exact-ID cleanup restores initial counts.

Do not retry.

On failure, restore the baseline file and marker under the lock and report BLOCKED. On success, fast-forward `staging` to the Feature SHA, push without force, and verify clean local/origin/live/marker alignment.

No real email, external HTTP, payment, cron/Action Scheduler runner, vendor/core/theme/configuration change or production access.

## Result

Return `PASS: JOB_ALERT_PLACEHOLDERS_DEPLOYED` or one precise `BLOCKED` reason with rollback status.

Publish one concise report with final SHA, changed file, business/final recipient assertion results without addresses, post-deploy event result, cleanup and final alignment. STOP after the report.
