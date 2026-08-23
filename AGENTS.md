# Raspitajse Codex Operating Rules

This repository is connected to a live Hostinger account. Treat server operations as production-adjacent and follow these rules exactly.

## Scope

- Work only in this repository: `/home/u601262303/repo/raspitajse-wp`.
- Allowed Git branches for active code work: `feature/*` and `staging`.
- `codex-reports` is a reporting-only exception. Never use it for application code work and never merge it into any code branch.
- Never modify, force-update, reset, merge into, deploy from, or push to `main`.
- Never modify the immutable baseline branches `baseline/production-2026-08` or `baseline/staging-2026-08`.
- For any code change, create or use a `feature/*` branch based on current `origin/staging` unless the task explicitly says otherwise.
- Do not commit code changes directly on `staging`; use `feature/*`, test, then fast-forward/merge only after validation.

## Required project context

Before planning a substantial task, read:

- `CODEX_PROJECT_CONTEXT.md` — current architectural map, verified audit findings, risk areas and roadmap;
- `CODEX_WORKFLOW.md` — autonomous task planning/execution/reporting loop and approval boundaries;
- `CODEX_HOST_RESILIENCE.md` — mandatory Hostinger namespace/ENOSPC circuit-breaker and namespace-free editing fallbacks;
- `STAGING_MUTATION_POLICY.md` — authoritative staging state classes, fixture rules and PASS/PARTIAL/FAIL mutation semantics;
- `STAGING_DEFINITION_OF_DONE.md` — formal exit criteria for the current staging technical-refactor phase.

These documents are subordinate to this file and to current repository/runtime evidence. If an older context statement conflicts with current evidence, current evidence wins and the context should be updated when appropriate.

When the user starts an autonomous Codex session, follow `CODEX_WORKFLOW.md`: determine the next safe numbered task, execute only clearly bounded staging-safe work, publish a numbered report after each task, and continue within the same session until an approval/safety boundary is reached.

For staging mutations, protect business state strictly while allowing only technical housekeeping or disposable-fixture mutations that are explicitly classified, bounded and measurable under `STAGING_MUTATION_POLICY.md`. Production remains forbidden regardless of staging mutation class.

## Server boundaries

The only WordPress runtime that Codex may modify or deploy to is staging:

`/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`

Production is forbidden. In particular, do not modify, sync, delete, rename, chmod recursively, or deploy to:

`/home/u601262303/domains/raspitajse.com/public_html`

except for the exact `public_html_stage` subtree above.

Never run recursive commands against the parent `public_html` directory. Never use globs or broad rsync targets that could include production.

## Database and secrets

- Never access, query, export, import, modify, truncate, migrate, search/replace, or otherwise operate on the production database.
- Staging database operations are allowed only when required by the task and must be explicitly scoped through the staging WordPress path.
- Never print, copy, expose, commit, or summarize secrets, passwords, API keys, SMTP credentials, salts, private keys, or database credentials.
- Do not display the contents of any `wp-config.php`.
- Do not modify production `wp-config.php`.
- Do not modify staging `wp-config.php` unless the task explicitly requires it and the change is reviewed first.

## WordPress paths

Staging WordPress root:

`/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`

When using WP-CLI, always scope it explicitly:

`wp --path=/home/u601262303/domains/raspitajse.com/public_html/public_html_stage ...`

Before any mutating WP-CLI command, verify:

`wp --path=/home/u601262303/domains/raspitajse.com/public_html/public_html_stage eval 'echo wp_get_environment_type();'`

The expected result is `staging`. If it is not exactly `staging`, stop immediately.

## Deployment

Use only:

`deployment/deploy-staging.sh`

Default deployment mode is:

`deployment/deploy-staging.sh changed <feature-or-staging-branch>`

- Prefer `changed` for normal feature testing and staging updates.
- Do not run `full` unless the task explicitly requires a full reconciliation or a missing deployment marker makes it necessary.
- Never create or use a production deployment script without explicit user approval.
- Never use legacy deployment scripts outside this repository.
- If any deploy safety check fails, stop. Do not bypass guards or manually copy around them.

## Required checks before deployment

Before deploying changed PHP or shell files:

- repository must be clean before branch/reset operations;
- fetch `origin` and confirm the intended branch exists remotely;
- confirm current branch is `feature/*` or `staging`;
- run `php -l` on every changed PHP file;
- run `bash -n` on every changed shell script;
- inspect the Git diff for unexpected files, deletes, secrets, generated files, or production paths.

For risky sync operations, use a dry-run first and inspect all `*deleting` lines.

## Required checks after deployment

After a staging deploy:

- verify the deployment command completed successfully;
- verify staging environment is still `staging`;
- run targeted smoke/sanity checks for the changed behavior;
- for mail-related changes, verify the staging mail-safety MU plugin remains loaded;
- if a test fails, stop and report the failure before making additional broad changes.

Staging mail-safety check:

`wp --path=/home/u601262303/domains/raspitajse.com/public_html/public_html_stage eval 'echo wp_get_environment_type() . PHP_EOL; echo function_exists("raspitajse_staging_mail_safety_is_staging") ? "mail-safety=loaded" : "mail-safety=MISSING";'`

## Mail safety

- Never intentionally send staging email to real users, candidates, employers, customers, or arbitrary addresses.
- Preserve the staging mail-safety MU plugin and its fail-closed behavior.
- Do not weaken, bypass, remove, or reorder staging mail-safety controls unless the task explicitly concerns that mechanism and includes a safe test plan.

## Legacy/refactor policy

Current legacy business logic exists in the Superio child theme and WP Job Board Pro vendor files. During refactoring:

- classify historical behavior as `KEEP`, `CHANGE`, or `DROP` before spending parity/refactor effort;
- preserve parity only for behavior classified `KEEP`;
- implement `CHANGE` behavior against an explicit new acceptance criterion;
- do not spend tasks reproducing behavior classified `DROP`;
- move reusable custom business logic into Raspitajse-owned plugins incrementally;
- do not restore clean vendor files until required `KEEP`/`CHANGE` behavior is implemented and tested;
- target update-safe vendor code, especially WP Job Board Pro and Paid Listings;
- do not combine unrelated cleanup with behavior-changing refactors;
- prefer small, reversible commits.

Target custom plugins include `raspitajse-communications` and `raspitajse-job-importer`.

The exact technical completion gate is in `STAGING_DEFINITION_OF_DONE.md`; exhaustive cosmetic cleanup is not required when it does not affect safety, updateability or release confidence.

## Git discipline

- Never use `git push --force` on shared branches.
- Never rewrite `staging`, `main`, or baseline history.
- Do not delete branches unless explicitly requested.
- Keep commits focused and descriptive.
- Before merging/fast-forwarding to `staging`, compare the feature branch to `staging` and verify the diff contains only intended files.

## Codex task numbering

Use numbered task titles for autonomous work:

`Zadatak N.M — Short descriptive title`

- `N.0` starts a new workstream;
- `N.1`, `N.2`, ... continue that workstream;
- continuing an older workstream keeps its original major number;
- never reuse a published Task ID for a different action.

The detailed selection rules are in `CODEX_WORKFLOW.md`.

## Codex execution reporting

After every Codex Remote task, publish a structured execution report to the dedicated `codex-reports` branch using:

`bash tools/codex-report.sh`

The reporting branch is an audit channel only:

- never merge `codex-reports` into `staging`, `main`, a baseline branch, or a feature branch;
- never deploy from `codex-reports`;
- never place application code changes on `codex-reports`;
- use the helper rather than checking out `codex-reports` in the primary repository worktree;
- the helper uses a temporary Git worktree and must leave the source branch/worktree unchanged;
- a task-level instruction such as "do not commit" or "do not push" applies to the code/source branch; publishing the report-only commit to `codex-reports` is permitted unless the task explicitly says not to publish a report.

A normal numbered report invocation is:

```bash
cat <<'REPORT' | bash tools/codex-report.sh "Zadatak 3.0 — Short task title" PASS
## Summary
- What was done

## Validation
- PASS/FAIL/SKIPPED checks

## Runtime state
- Branch and HEAD
- Deployed SHA if applicable
- Relevant staging state

## Warnings / errors
- None, or sanitized details

## Safety
- Production touched: NO
REPORT
```

Allowed report results are `PASS`, `FAIL`, `PARTIAL`, and `SKIPPED`.

Every report should state, when applicable:

- task outcome;
- source branch and exact HEAD;
- changed files and commit SHA;
- deployed SHA or confirmation that no deploy occurred;
- tests/checks with PASS/FAIL/SKIPPED;
- relevant staging runtime state after the task;
- warnings/errors encountered;
- whether rollback was completed when required;
- `Production touched: NO`.

Result classification must follow `STAGING_MUTATION_POLICY.md`: expected bounded technical housekeeping does not itself make a task `FAIL`; an unapproved business/safety mutation or unbounded integrity failure does.

Never place secret values, credentials, private keys, tokens, SMTP credentials, database credentials, `wp-config.php` contents, private user data, CV contents, message contents, or unnecessary personal email addresses in reports. Refer to protected values generically, for example `configured staging inbox`.

If report publication fails, do not alter production or weaken safety controls to make it succeed. Leave the source task state intact and report the publication failure in the Codex UI.

## Autonomous approval boundaries

Autonomy never means unlimited permission. Stop for approval when the next action could affect protected non-fixture business state, including candidate/employer/application/message state, order/payment/refund/subscription state, password changes, real recipients, destructive/ambiguous DB changes, personal-data deletion, safety-guard removal, or unknown side effects.

Controlled disposable staging fixtures and classified bounded technical staging housekeeping may proceed autonomously only under `STAGING_MUTATION_POLICY.md` and the exact current task contract.

Broad cron/Action Scheduler processing remains approval-required or forbidden while recovery rules say so; technical-housekeeping classification does not authorize an unclassified broad runner.

Production work, main/baseline rewrites, secret exposure, force-pushes, forbidden broad cron execution and safety bypasses remain forbidden, not merely approval-required.

## Resource limits and namespace resilience on Hostinger

This Hostinger account has restrictive process/thread and Linux namespace limits. Keep Codex/Tokio worker usage low. Do not start unnecessary background processes, watchers, dev servers, or parallel jobs. Avoid commands that fan out into many processes.

The recurring `bwrap` / `apply_patch` `ENOSPC` failure is a known host namespace-pressure condition. Handle it according to `CODEX_HOST_RESILIENCE.md`.

Mandatory circuit breaker:

- after the first confirmed namespace-ENOSPC signature, classify it as `HOST_NAMESPACE_PRESSURE`;
- do not keep retrying the same patch/sandbox helper and do not wait inside the task for host capacity to recover;
- use `bash tools/codex-git-apply.sh` for a safe repository patch on a clean `feature/*` branch;
- use `bash tools/codex-tmp-rewrite.sh` or a deterministic inspected edit only for fresh Raspitajse-owned `/tmp` task files;
- `bash tools/codex-host-diagnose.sh` may be used for low-cost diagnostics; the optional namespace probe must not be looped;
- if the namespace-free fallback cannot preserve the exact task safety contract, publish `PARTIAL` and stop.

ENOSPC is never permission to weaken an execution guard, broaden a path/endpoint/database allowlist, skip rollback, touch production, or bypass an approval boundary.

## Stop conditions

Stop and ask for review instead of improvising if:

- a command would touch production;
- the environment cannot be proven to be staging;
- a deployment wants to delete unexpected files;
- secrets would need to be printed or copied;
- a database operation is ambiguous about staging vs production;
- the repository is unexpectedly dirty;
- a safety guard blocks an action;
- an actual mutation exceeds the exact state class/allowed scope defined under `STAGING_MUTATION_POLICY.md`;
- the requested change conflicts with these rules;
- the next autonomous action is `APPROVAL_REQUIRED`, `UNKNOWN`, or `FORBIDDEN` under `CODEX_WORKFLOW.md`;
- a confirmed host namespace ENOSPC condition cannot be handled by the bounded namespace-free fallback in `CODEX_HOST_RESILIENCE.md`.

Safety takes precedence over speed.
