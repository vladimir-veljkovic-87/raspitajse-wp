# Zadatak 2.51.11 — Finalize proven job-alert fix

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.10
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes

## Goal

Finalize the already proven one-file job-alert placeholder fix. Task 2.51.10 reached `delivered`, intercepted exactly one callback email, contained the expected title and had no unresolved placeholders. Only two harness assertions were invalid.

PASS classification: `JOB_ALERT_PLACEHOLDERS_DEPLOYED`.

## Required recovery state

Accept only:

- branch `feature/task-2.51.9-job-alert-placeholders`;
- HEAD and `origin/staging` equal the declared baseline;
- exactly one uncommitted owned file:
  `wp-content/plugins/raspitajse-communications/includes/class-candidate-job-alert-integration.php`;
- working-copy SHA-256 `f51d8cd83a839da0a9ae15009ce2ed51045e252a8db58b54e4913ac4a5ecff11`;
- live SHA-256 `1f6417859e31fec3d66781de7ffe67e6b30807390e8d2ca91b22d66ce3a801e9`;
- deploy marker equals the baseline.

Any deviation is BLOCKED. Do not reset, recreate or modify the product diff.

Reuse all passed 2.51.9/2.51.10 validation. Do not rerun lint, static tests, diagnosis or diff review.

## Correct only the probe assertions

Reuse the exact accepted fixture construction and valid claim token from 2.51.10.

Correct the two harness assertions only:

1. Do not read `selected_job_ids` from the public service result because it is not exposed. Prove the single selected job from the intercepted owned mail-adapter callback context/arguments and the one rendered fixture title.
2. HTML-decode and canonicalize the rendered job URL before comparison with the canonical staging permalink. Ignore harmless entity encoding and trailing-slash representation; require the same staging host and exact job path.

Do not change application code or fixture semantics.

## One pre-deploy probe

Run once and require:

- public status `delivered`;
- exactly one callback email event;
- exactly one recorded fixture job represented in adapter context/content;
- expected job title and canonicalized staging job link;
- no unresolved, production or paid-flow URL;
- exact-ID cleanup restores initial counts.

## Commit, deploy and verify

If it passes:

1. commit and push the existing one-file diff;
2. deploy only that owned file and marker under the standard staging mutation lock;
3. release the lock;
4. run the same corrected post-deploy probe once;
5. on failure, restore the baseline file and marker under the lock and report BLOCKED;
6. on success, fast-forward `staging`, push without force, and verify clean Git/live/marker alignment.

No generated finalizer, real email, external HTTP, payment, broad scheduler execution, vendor/core/theme/configuration change or production access.

## Result

Return `PASS: JOB_ALERT_PLACEHOLDERS_DEPLOYED` or one precise `BLOCKED` reason with rollback status.

Publish one concise report with final SHA, one changed file, pre/post event and canonical-link result, cleanup and final alignment. STOP after the report.
