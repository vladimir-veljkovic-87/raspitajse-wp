# Zadatak 2.51.10 — Complete job-alert placeholder fix

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.9
Target: staging
Production: FORBIDDEN
Time budget: 25 minutes

## Goal

Complete the already implemented 2.51.9 owned placeholder fix without reimplementing it. The prior probe failed on a malformed job fixture before reaching the renderer.

PASS classification: `JOB_ALERT_PLACEHOLDERS_DEPLOYED`.

## Required recovery state

Accept only this exact starting state:

- branch `feature/task-2.51.9-job-alert-placeholders`;
- HEAD and `origin/staging` equal the declared baseline;
- exactly one uncommitted file:
  `wp-content/plugins/raspitajse-communications/includes/class-candidate-job-alert-integration.php`;
- working-copy SHA-256 `f51d8cd83a839da0a9ae15009ce2ed51045e252a8db58b54e4913ac4a5ecff11`;
- live file SHA-256 `1f6417859e31fec3d66781de7ffe67e6b30807390e8d2ca91b22d66ce3a801e9`;
- deploy marker equals the baseline.

Any other state is BLOCKED. Do not reset, discard, recreate or broaden the product diff.

Reuse the existing PASS results for PHP lint, static placeholder assertions and diff scope. Do not rerun them.

## Correct only the test fixture

Reuse the exact job/candidate/alert fixture construction from the retained 2.51.8 diagnostic probe, which successfully selected one job and reached `raspitajse_cja_invalid_rendered_mail`.

Do not reuse the rejected 2.51.9 job fixture. Do not change application code to accommodate a fixture.

Use a valid 32-character claim token and include every auto-created related post in the exact-ID cleanup ledger.

## Focused pre-deploy probe

Run the corrected callback probe once:

- exactly one matching published job is selected;
- exactly one candidate email event is intercepted before transport;
- `{{job_data}}` and all existing `{{newest_*}}` placeholders are resolved;
- unknown placeholders remain fail-closed;
- expected job title and staging job link are present;
- no unresolved, production or paid-flow URL;
- cleanup restores initial counts.

No other email, expiry, cron or scheduler test.

## Commit, deploy and verify

If the pre-deploy probe passes:

1. commit and push the existing one-file feature diff;
2. deploy only that changed owned file and marker under the standard staging mutation lock;
3. release the lock;
4. run the same corrected focused post-deploy probe once with mail intercepted;
5. if it fails, restore the baseline file and marker under the lock and report BLOCKED;
6. if it passes, fast-forward `staging`, push without force, and verify clean Git/live/marker alignment.

No generated finalizer. No real email, external HTTP, payment, broad scheduler execution, vendor/core/theme/configuration change or production access.

## Result

- `PASS: JOB_ALERT_PLACEHOLDERS_DEPLOYED`;
- otherwise one precise `BLOCKED` reason and rollback status.

Publish one concise report with final SHA, changed file, pre/post event result, placeholder validation, cleanup and final alignment. STOP after the report.
