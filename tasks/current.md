# Codex Task Pointer

Status: WAITING_FOR_PREVIOUS_REPORT
Current in-progress task: 1.89
Next task: 1.90
Baseline: TO_BE_SET_AFTER_1.89_REPORT
Previous task: 1.89
Target environment: staging
Production: FORBIDDEN

## Execution gate

This file is **not READY** yet.

Codex MUST NOT begin Zadatak 1.90 from this file until this document is replaced with a complete task specification and the header says:

`Status: READY`

The 1.90 baseline must be set only after Zadatak 1.89 has finished, its `codex-reports` result has been reviewed, and the final `origin/staging` SHA has been verified.

## Mandatory workflow rules

Fetch `origin/codex-tasks` and `origin/staging`. Read `tasks/current.md` from `origin/codex-tasks` in full.

`codex-tasks` is READ-ONLY execution input for Codex. Codex MUST NOT modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

When this task later becomes `READY`, execute exactly the task using the stated staging baseline and safety constraints. Do not silently substitute another baseline SHA. If `origin/staging` does not match the declared baseline and the task does not explicitly authorize that mismatch, STOP and report the exact mismatch.

Application work belongs on a scoped feature branch based on the declared baseline. Staging integration is allowed only when the task's acceptance contract explicitly permits it and all required validations pass.

Publish the final execution report through the existing `codex-reports` workflow and STOP after the task. Do not infer, prepare, or execute the next task.

The complete persistent workflow and safety rules are in `tasks/README.md` on `origin/codex-tasks` and are mandatory for every task.
