# Zadatak 2.51.12 — Deploy proven job-alert fix

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.11
Target: staging
Production: FORBIDDEN
Time budget: 15 minutes

## Goal

Commit and deploy the already proven one-file job-alert placeholder fix, then run one post-deploy smoke through the normal live WordPress bootstrap.

Do not run another pre-deploy probe. Task 2.51.10 already proved `delivered`, one callback event, expected title and zero unresolved placeholders.

PASS classification: `JOB_ALERT_PLACEHOLDERS_DEPLOYED`.

## Exact recovery state

Require:

- branch `feature/task-2.51.9-job-alert-placeholders`;
- HEAD and `origin/staging` equal the declared baseline;
- exactly one uncommitted file:
  `wp-content/plugins/raspitajse-communications/includes/class-candidate-job-alert-integration.php`;
- working-copy SHA-256 `f51d8cd83a839da0a9ae15009ce2ed51045e252a8db58b54e4913ac4a5ecff11`;
- live SHA-256 `1f6417859e31fec3d66781de7ffe67e6b30807390e8d2ca91b22d66ce3a801e9`;
- marker equals the baseline.

Any deviation is BLOCKED. Do not alter the diff.

Reuse all prior lint, static, diff-scope and pre-deploy runtime evidence. Do not regenerate it.

## Commit and deploy

1. Commit the existing one-file diff and push the feature branch.
2. Under the standard staging mutation lock, deploy only that file and update the marker to the feature SHA.
3. Verify repository/live file hash equality and release the lock.

No generated finalizer.

## One post-deploy smoke only

Use normal staging WordPress/WP-CLI bootstrap so the deployed live Communications plugin loads naturally.

Do not manually `require`, include or load any repository plugin class/file. Fail if the expected live class is not already loaded.

Reuse the accepted 2.51.10 fixture semantics and corrected assertions:

- valid claim token;
- public result `delivered`;
- one callback email intercepted before transport;
- one recorded fixture job represented in adapter context/rendered content;
- expected title;
- HTML-decoded/canonicalized staging job host and path;
- no unresolved, production or paid-flow URL;
- exact-ID cleanup restores initial counts.

On smoke failure, restore the baseline file and marker under the lock and report BLOCKED. Do not retry.

On success, fast-forward `staging`, push without force, and verify clean local/origin/live/marker alignment at the feature SHA.

No real email, external HTTP, payment, cron/Action Scheduler runner, vendor/core/theme/configuration change or production access.

## Result

Return `PASS: JOB_ALERT_PLACEHOLDERS_DEPLOYED` or one precise `BLOCKED` reason with rollback status.

Publish one concise report with final SHA, changed file, post-deploy result, cleanup and final alignment. STOP after the report.
