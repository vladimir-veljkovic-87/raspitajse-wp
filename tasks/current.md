# Zadatak 2.44 — Read-only attribution of Action Scheduler IDs 32867 and 32868

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.43
Target environment: staging
Production: FORBIDDEN

## Objective

Attribute exactly the two new pending Action Scheduler rows `32867` and `32868` that blocked Task 2.43.

This task is strictly read-only. Do not execute, claim, cancel, delete, reschedule, update or recreate any action. Do not run a queue runner, due-action runner, WP-Cron event, callback, fixture, smoke test, deployment or acceptance matrix. Do not modify Git, application code, WordPress options, business data, users, sessions, files, deploy marker or production.

PASS classification: `ACTION_SCHEDULER_DRIFT_ATTRIBUTED`.

## Mandatory preamble

Fetch fresh refs and read:

- `tasks/current.md` and `tasks/README.md`;
- final Zadatak 2.25 report `reports/20260914T103546Z-zadatak-2_25.md`;
- complete Task 2.43 report and its evidence at `/tmp/raspitajse-task-2.43.cP7kIL`;
- relevant reports/evidence for Tasks 2.29, 2.37, 2.39 and 2.42 where scheduler fixture IDs, KEEP/DROP actions and cleanup are recorded.

Require:

- clean worktree;
- `origin/staging=ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- all five live files and deploy marker equal that SHA;
- both staging locks free before entry;
- no scheduler runner/lock-holder active;
- claims 0 and AS 32733 complete/attempts 1;
- business, owned-contract and non-allowlisted cron fingerprints equal the final 2.25 report;
- six protected KEEP IDs 32777, 32778, 32779, 32783, 32784, 32848 remain byte/state-identical;
- current pending snapshot contains those six plus exactly 32867 and 32868.

If additional unexplained drift appears, record it but do not expand scope or mutate anything. The task may continue attribution of 32867/32868 only if their exact rows remain unchanged and can be isolated safely; otherwise publish BLOCKED.

## Read-only lock and access model

Use one short-lived process holding both staging OS locks during the authoritative T0 snapshot, attribution reads, T1 snapshot, evidence persistence and report publication.

Prefer raw read-only database queries and static filesystem/Git reads that do not bootstrap WordPress.

If WordPress bootstrap is absolutely required for a metadata name lookup:

- install guards before bootstrap;
- block action scheduling, async dispatch, cron spawning, mail, SMTP, HTTP transport, payment and callbacks;
- do not call scheduler APIs that can write;
- prove before/after database equality;
- treat any attempted write or side effect as BLOCKED.

No parallel staging process is allowed.

## Exact row evidence

For each ID 32867 and 32868, capture a sanitized evidence row containing:

- exact action ID;
- hook;
- status;
- group ID and resolved group slug/name;
- scheduled local and GMT timestamps;
- creation/enqueue timestamp when available;
- schedule type/interval/recurrence in sanitized form;
- priority;
- attempts;
- claim ID;
- SHA-256 of raw arguments;
- SHA-256 of extended arguments;
- SHA-256 of the complete canonicalized database row;
- related Action Scheduler log row IDs, timestamps and sanitized message classification;
- SHA-256 of each unredacted private log row.

Store raw arguments/log payload only in private mode-0600 evidence if required for attribution. Never print or publish raw serialized arguments, email addresses, names, tokens, URLs containing secrets, candidate/employer profile data, rendered mail, order/customer data or other PII.

Prove neither action has been claimed or attempted during this task.

## Attribution

For each action independently:

1. Decode arguments only in private evidence.
2. Identify every referenced object ID/type without publishing private content.
3. Check read-only whether referenced posts/orders/users/options/fixtures exist and whether they match previously recorded fixture ledgers.
4. Correlate creation/scheduled timestamps with Task 2.42 execution, its runtime fixture creation/cleanup, report publication and any documented staging request.
5. Compare hook, group and argument hashes against:
   - Task 2.39/2.42 fixture-created actions;
   - the six KEEP actions;
   - Task 2.29 DROP actions;
   - known WooCommerce/WP Job Board Pro infrastructure actions.
6. Search the exact deployed and Git source statically for:
   - hook registration;
   - scheduling/enqueue call;
   - group assignment;
   - argument construction;
   - cancellation/cleanup behavior.
7. Identify the responsible owner/layer: Raspitajse-owned code, WooCommerce, WP Job Board Pro, another named vendor, Task fixture, or unknown.
8. Determine the exact trigger: runtime fixture, page/bootstrap request, order/status transition, plugin maintenance, manual action or unknown.
9. State why the rows were created after the final 2.25 six-action snapshot and why they survived cleanup, using evidence rather than inference.

No external/vendor request may be made merely to prove attribution.

## Classification and recommendation

Assign each ID one evidence-backed classification:

- `KEEP`: required business/operational action;
- `REDESIGN`: business need remains but scheduling/ownership should move to a Raspitajse-owned implementation;
- `DROP`: irrelevant vendor/telemetry/maintenance behavior or proven disposable fixture residue.

Also assign one provenance confidence:

- `PROVEN`;
- `HIGH_CONFIDENCE_INFERENCE`;
- `UNRESOLVED`.

PASS requires PROVEN attribution for both IDs. Otherwise result is BLOCKED.

For each row state exactly one follow-up recommendation, without performing it:

- accept into a newly documented legitimate baseline;
- delete that exact ID only as proven fixture residue;
- separately remediate the scheduling source;
- leave untouched pending further evidence.

Do not recommend broad scheduler execution or bulk deletion.

## T0/T1 equality gate

Capture authoritative T0 immediately after both locks are held and T1 after all attribution reads.

Require exact equality for:

- complete rows and logs of IDs 32867 and 32868;
- all six KEEP rows/logs;
- pending count, ID list and fingerprint;
- claims and AS 32733;
- business, owned-contract and cron fingerprints;
- users/options/roles;
- all five live file hashes and marker;
- Git state;
- mail/SMTP/HTTP/payment/broad-runner/protected-action counters at 0.

No staging mutation is acceptable, including timestamp-only scheduler changes.

## Report

Publish exactly one report to `codex-reports`:

- Task: `Zadatak 2.44 — Read-only Action Scheduler drift attribution`;
- Result: PASS or BLOCKED;
- classification on PASS: `ACTION_SCHEDULER_DRIFT_ATTRIBUTED`;
- one sanitized attribution table for IDs 32867 and 32868;
- owner, exact trigger, referenced-object disposition, KEEP/REDESIGN/DROP and confidence for each;
- T0/T1 equality;
- exact recommended next action;
- confirmation that Task 2.43 was not resumed;
- no callbacks/runners/mutations;
- locks released;
- production untouched.

Verify the remote report path, 40-hex commit, `origin/codex-reports` equality and non-empty readback before releasing locks. Then STOP.
