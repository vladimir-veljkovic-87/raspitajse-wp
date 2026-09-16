# Zadatak 2.52.1 — Lean dependency and version baseline

Status: READY
Baseline: e40619b99563871889caffbd082f0da9d1cd25d3
Previous task: 2.51.13
Target: staging
Production: FORBIDDEN
Time budget: 20 minutes

## Goal

Create a concise, current read-only dependency/version baseline for the production-readiness security phase.

Do not update, activate, deactivate, install, remove or modify anything.

## Lean scope

Follow `tasks/README.md`. Read only current staging metadata and directly relevant plugin/theme headers.

Record:

- WordPress, PHP and database versions;
- active parent/child theme names and versions, including their parent relationship;
- active plugins with installed version, owner/source, update mechanism and currently reported update availability;
- required Raspitajse-owned/MU plugins and their status;
- inactive plugins/themes as KEEP, REVIEW or REMOVE CANDIDATE based only on current launch need;
- whether WordPress currently reports an available core, plugin or theme update;
- obvious version/dependency incompatibilities reported by WordPress/plugin metadata.

Use current WordPress update metadata. At most one normal read-only update-information refresh is allowed if cached data is absent or stale; record that it occurred. Do not install any update and do not contact unrelated services.

## Boundaries

Do not:

- run vulnerability, malware, filesystem-integrity or recursive permission scans;
- hash whole vendor trees;
- inspect production;
- change options, transients beyond WordPress's normal update-information refresh, files, database content, schedules or Git;
- retest business journeys, email, expiry, job alerts, payment or scheduler behavior;
- create a harness/finalizer or large evidence archive.

## Output

Produce one compact table with:

`component | active | installed version | update available | source/owner | launch classification | note`

Clearly separate:

1. confirmed update candidates;
2. inactive removal candidates;
3. components requiring vendor-license/manual update verification;
4. no-action dependencies.

## Result

- `PASS: DEPENDENCY_BASELINE_RECORDED` if the inventory is complete and no immediate compatibility blocker is reported.
- `BLOCKED: CRITICAL_DEPENDENCY_MISMATCH` only for a concrete current incompatibility preventing launch.
- Do not treat the mere existence of an update as a blocker.

Publish one concise report with the matrix, recommended next bounded task, unchanged Git/live/marker SHA and production untouched. STOP after the report.
