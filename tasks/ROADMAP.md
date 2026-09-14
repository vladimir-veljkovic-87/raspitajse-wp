# Raspitajse technical roadmap

Updated: 2026-09-14
Application baseline: `origin/staging@ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`
Production: FORBIDDEN unless a later task explicitly authorizes a release

## Purpose

This file is the high-level source of truth for why numbered Codex tasks exist and what phase comes next. Detailed execution instructions remain in `tasks/current.md` and immutable `tasks/<id>.md`.

The roadmap follows one product rule: migrate Raspitajse business needs, data and user outcomes—not vendor implementation for its own sake.

## Decision rules

Every legacy behavior is classified:

- `KEEP` — required business or operational behavior;
- `REDESIGN` — the business need remains, but implementation should move to a cleaner Raspitajse-owned layer;
- `DROP` — irrelevant vendor telemetry, announcements, abandoned behavior or unnecessary maintenance.

A completed milestone is not reopened without concrete regression evidence.

Action Scheduler gates use two layers:

1. Raspitajse-owned/business actions, claims, protected IDs and side effects are strict blockers.
2. Unrelated vendor actions are captured as an observational T0 set and must remain unexecuted and byte/state-identical at T1. Their mere presence or a changed global queue count does not block unrelated business acceptance.

Unknown actions that reference business objects, become claimed/attempted, or cannot be safely guarded remain blocking. Broad queue execution and bulk deletion are always forbidden.

## Status

### DONE

#### Staging safety foundation

- staging-only execution and production prohibition;
- scheduler DROP/async guards;
- mail, SMTP, external HTTP and payment interception for fixtures;
- exact cleanup/evidence/report workflow;
- administrator capability fix;
- registration enabled and WP Job Board Pro asset permissions remediated.

#### Zadatak 2.25 — Remaining custom commerce cleanup

Status: `DONE / PASS`

- integrated and deployed SHA: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- static acceptance: 27/27;
- runtime acceptance: 35/35;
- packages, checkout and WooCommerce HPOS admin renderer smoke: PASS;
- final report: `reports/20260914T103546Z-zadatak-2_25.md`;
- production untouched.

Tasks 2.26–2.42 are recovery, diagnostic and finalization history for this completed milestone. They are not open product work.

#### Zadatak 2.44 — Scheduler attribution checkpoint

Status: `COMPLETE / PARTIAL ATTRIBUTION`

- ID 32867: WPForms vendor notification fetch; `DROP / PROVEN`;
- ID 32868: AIOSEO image-sitemap maintenance; vendor owner and purpose proven, exact enqueue source unresolved;
- both remain pending, unexecuted and unchanged;
- neither is part of Raspitajse business state;
- no further vendor-internal investigation blocks the user-journey acceptance milestone.

### NOW

#### Zadatak 2.45 — Post-deploy business-journey acceptance

Run the previously deferred registration/login, role/dashboard, packages/checkout, activation/quota and admin acceptance against the deployed 2.25 code.

Vendor scheduler rows are observed and protected from execution; they do not redefine the Raspitajse business baseline and do not block acceptance merely by existing.

Exit condition: one PASS/BLOCKED report with exact cleanup, zero side effects and no production access.

### NEXT

After 2.45, choose one Raspitajse-owned subsystem from the existing inventory based on business value and migration risk. Before implementation, publish a short bounded discovery task that states:

- business outcome;
- current owner/dependencies;
- KEEP/REDESIGN/DROP decision;
- target Raspitajse-owned implementation;
- staging acceptance and rollback boundary.

Do not return to 2.25 or scheduler archaeology unless 2.45 produces direct business-regression evidence.

### LATER

- remediate the WPForms vendor-announcement scheduling source and then remove only its proven exact pending residue;
- decide whether AIOSEO image-sitemap scheduling is retained/configured or redesigned, independently of business acceptance;
- perform real interactive CAPTCHA registration/login with controlled credentials if product sign-off requires it;
- production release requires a separate explicit task and approval.

## Task discipline

- `tasks/current.md` is the only executable pointer.
- One task must map to exactly one roadmap milestone.
- Reports go only to `codex-reports`.
- Codex stops after the report and never invents the next task.
- Incidental vendor behavior is recorded but does not automatically expand scope.
