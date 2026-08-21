# Raspitajse Codex Operating Rules

This repository is connected to a live Hostinger account. Treat server operations as production-adjacent and follow these rules exactly.

## Scope

- Work only in this repository: `/home/u601262303/repo/raspitajse-wp`.
- Allowed Git branches for active work: `feature/*` and `staging`.
- Never modify, force-update, reset, merge into, deploy from, or push to `main`.
- Never modify the immutable baseline branches `baseline/production-2026-08` or `baseline/staging-2026-08`.
- For any code change, create or use a `feature/*` branch based on current `origin/staging` unless the task explicitly says otherwise.
- Do not commit code changes directly on `staging`; use `feature/*`, test, then fast-forward/merge only after validation.

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

- preserve existing behavior before removing legacy implementations;
- move custom business logic into Raspitajse-owned plugins incrementally;
- do not restore clean vendor files until equivalent custom behavior is implemented and tested;
- do not combine unrelated cleanup with behavior-changing refactors;
- prefer small, reversible commits.

Target custom plugins include `raspitajse-communications` and `raspitajse-job-importer`.

## Git discipline

- Never use `git push --force` on shared branches.
- Never rewrite `staging`, `main`, or baseline history.
- Do not delete branches unless explicitly requested.
- Keep commits focused and descriptive.
- Before merging/fast-forwarding to `staging`, compare the feature branch to `staging` and verify the diff contains only intended files.

## Resource limits on Hostinger

This Hostinger account has restrictive process/thread limits. Keep Codex/Tokio worker usage low. Do not start unnecessary background processes, watchers, dev servers, or parallel jobs. Avoid commands that fan out into many processes.

## Stop conditions

Stop and ask for review instead of improvising if:

- a command would touch production;
- the environment cannot be proven to be staging;
- a deployment wants to delete unexpected files;
- secrets would need to be printed or copied;
- a database operation is ambiguous about staging vs production;
- the repository is unexpectedly dirty;
- a safety guard blocks an action;
- the requested change conflicts with these rules.

Safety takes precedence over speed.