# Zadatak X.XX — <title>

Status: READY
Baseline: <exact origin/staging SHA>
Previous task: <X.XX>
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` from `origin/codex-tasks` in full before planning or modifying source.

Read `tasks/README.md` from `origin/codex-tasks`; its workflow and safety rules are mandatory.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, delete from, or otherwise write to `codex-tasks`.

Verify that `origin/staging` equals the exact `Baseline` above. If it does not and this task contains no explicit safe mismatch procedure, STOP and report the mismatch. Do not silently execute against a different SHA.

Execute exactly this task and only this task. Publish the final execution report through the existing `codex-reports` workflow and STOP. Do not infer or begin the next task.

---

## 1. Context

<Previous result, current architecture, relevant invariants.>

## 2. Goal

<Exact business/technical outcome.>

## 3. Scope

<Allowed files/subsystems and explicit exclusions.>

## 4. Baseline verification

<Read-only state that must be captured before mutation.>

## 5. Implementation contract

<Exact implementation rules.>

## 6. Safety constraints

Unless explicitly overridden here, the persistent rules from `tasks/README.md` apply.

## 7. Staging fixture / validation

<Bounded fixture, interception, assertions, cleanup.>

## 8. Regression checks

<Previous task/subsystem invariants to re-check.>

## 9. Integration

- Feature branch from exact baseline.
- PHP/static validation as applicable.
- Focused diff review.
- Staging only.
- Fast-forward integration only when acceptance passes.
- No force update.
- Clean working tree.

## 10. Final report

Publish through the existing `codex-reports` workflow.

Report must include:

- baseline/final SHA;
- changed files;
- implementation summary;
- validation/fixture results;
- protected-state before/after;
- external-effect counters;
- cleanup proof;
- production touched YES/NO;
- one recommended next small task only.

STOP after this task.
