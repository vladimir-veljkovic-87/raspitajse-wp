# Codex Tasks Workflow

This branch is the canonical specification/control plane for Codex work on Raspitajse.com.

## Branch roles

- `staging` — application code and staging integration only.
- `codex-tasks` — task specifications and workflow rules only.
- `codex-reports` — Codex execution reports only.

`codex-tasks` MUST NOT be merged into `staging` and MUST NOT be used as an application-source baseline.

Because `codex-tasks` was created from repository history, it may contain inherited application files from the branch point. Those inherited files are **non-authoritative and may be stale**. Only `tasks/**` on `origin/codex-tasks` is authoritative for task specifications and workflow rules. All application/source reads, diffs, feature branches, tests, and integration work MUST use the declared `origin/staging` baseline, not application files inherited on `codex-tasks`.

Codex SHOULD read task files directly from the remote ref (for example `git show origin/codex-tasks:tasks/current.md` and `git show origin/codex-tasks:tasks/README.md`) rather than checking out `codex-tasks` into the application working tree. If a temporary checkout/worktree is used, it must be separate, read-only, and must never become the source of application changes.

## Ownership and write rules

- Codex MUST treat `codex-tasks` as **READ-ONLY**.
- Codex MUST NOT create commits on, push to, force-update, rebase, merge into, delete files from, or otherwise modify `codex-tasks`.
- Codex MUST NOT edit `tasks/current.md`, task history files, this README, or any other file on `codex-tasks`.
- Task specifications are authored/updated outside the Codex execution session and are immutable execution input once marked `READY`.
- Codex application work MUST start from the exact baseline stated in the task, normally a fresh `origin/staging`.
- Codex application changes MUST go to a scoped feature branch and may reach `staging` only through the task's explicit validation/integration contract.
- Codex reports MUST use the existing `codex-reports` workflow. Reports do not belong on `codex-tasks` or `staging`.

## Canonical task pointer

`tasks/current.md` is the only task entry point Codex should need.

When `tasks/current.md` has `Status: READY`, Codex must:

1. Fetch `origin/codex-tasks` and `origin/staging`.
2. Read `tasks/current.md` **from `origin/codex-tasks` in full** before planning or modifying source.
3. Read `tasks/README.md` from `origin/codex-tasks`; its workflow and safety rules are mandatory.
4. Verify that the current `origin/staging` SHA matches the task's declared `Baseline` exactly unless the task explicitly defines a safe mismatch procedure.
5. Execute exactly the stated task and its safety/acceptance contract.
6. Never broaden scope merely because adjacent defects are discovered. Record them and STOP if the task says so.
7. Publish the final execution report through the existing `codex-reports` workflow.
8. STOP after the task. Do not infer or begin the next task.

If `Status` is anything other than `READY`, Codex MUST NOT begin implementation from this branch.

## Baseline mismatch rule

If `origin/staging` does not equal the declared task baseline:

- do not silently rebase the task;
- do not execute against a different SHA;
- do not alter `codex-tasks`;
- inspect whether the mismatch is already accounted for by the task contract;
- otherwise STOP and report the exact mismatch.

## Safety defaults

Unless a task explicitly authorizes otherwise:

- production is forbidden;
- production data must not be accessed or mutated;
- broad WP-Cron runners are forbidden;
- broad Action Scheduler runners/due-action execution are forbidden;
- protected/unclassified actions must not be executed;
- real SMTP/mail transport is forbidden during fixtures unless the task explicitly authorizes it;
- payment execution is forbidden;
- external/vendor requests must not be made merely to prove irrelevant behavior;
- staging changes must be bounded, attributable, and exactly cleaned up;
- no force push/reset/destructive cleanup without explicit task authorization;
- do not modify vendor/core code unless the task explicitly permits and justifies it;
- preserve existing protected-state fingerprints and report any intentional change.

## Raspitajse refactoring rules

- Migrate business needs, data, and user outcomes; do not reproduce legacy/vendor implementation 1:1.
- For legacy functions/hooks/cron/actions, use `KEEP`, `REDESIGN`, or `DROP` based on business purpose.
- Keep necessary Raspitajse business logic in Raspitajse-owned layers where practical.
- Use WooCommerce, WP Job Board Pro, Superio, and other vendor systems as infrastructure where they provide value; do not refactor vendor subsystems without a concrete need.
- Do not remove a legacy implementation that still serves a required business function until its replacement is implemented, tested, and confirmed.
- Prefer small, verifiable, staging-first slices with explicit diffs, fixtures, cleanup, and audit trail.

## Execution hygiene

- Read the entire task before implementation.
- Respect explicit STOP boundaries.
- Do not start a numbered follow-up task unless `tasks/current.md` is updated to that task with `Status: READY`.
- If a helper/command is known to fail under `HOST_NAMESPACE_PRESSURE`/`bwrap ENOSPC`, do not retry it indefinitely; use the proven namespace-free workflow stated by the task or STOP with a precise blocker.
- If a process appears hung, identify the exact PID/process before terminating anything; never use broad kills.
- Never invent acceptance evidence. If a check was not run, report it as missing/not run.
- Keep reports free of recipient email addresses, rendered mail bodies, candidate PII, plaintext saved queries, credentials, and secrets.

## Task history

When a task is prepared, keep both:

- `tasks/current.md` — current executable task pointer/specification.
- `tasks/<task-id>.md` — immutable historical copy of the same READY specification, e.g. `tasks/1.90.md`.

After a task is completed, `tasks/current.md` may remain as historical evidence until the next task is prepared. Codex must never decide on its own that a new task is ready.
