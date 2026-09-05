# Zadatak 2.12 — Implement the bounded lightweight steady-state runner/guard redesign and separate deep diagnostic health checks

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.11
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning, creating a feature branch/worktree, bootstrapping WordPress, or changing source. `codex-tasks` is READ-ONLY.

Verify fresh `origin/staging` is exactly:

`43ac33e6e96fdc0062233e6419cfb761633113c5`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.12. Publish all task-state/final reports through the existing `codex-reports` workflow. Do not begin 2.13 automatically.

---

## 1. Accepted context

Zadatak 2.10 PASS established:

`STEADY_STATE_SELECTIVE_STAGING_SCHEDULER_ACCEPTED`

The accepted scheduler architecture is immutable for this task:

- exactly one human-owned Hostinger/hPanel staging scheduler entry;
- cadence `*/15 * * * *`;
- command invokes the zero-argument selective runner;
- `DISABLE_WP_CRON=true` remains intentional;
- no broad `/wp-cron.php`, `wp cron event run --due-now`, `--all`, Action Scheduler queue runner, daemon/loop, GitHub Actions scheduler, system cron substitute, or equivalent broad trigger;
- fixed owned hook order:
  1. `raspitajse_job_listing_expiry_evaluator`
  2. `raspitajse_employer_job_expiry_notice_evaluator`
  3. `raspitajse_candidate_job_alert_evaluator`.

Zadatak 2.11 PASS selected architecture outcome:

`REDESIGN_BOUNDED`

2.11 concluded:

- KEEP the runner and guard as the permanent staging execution boundary;
- do **not** remove either component;
- KEEP the fixed three-hook allowlist/order, zero-argument normal mode, arbitrary-argument rejection, due-only semantics, exact-hook execution, overlap lock, per-hook timeout, whole-cycle watchdog, staging targeting, deploy-marker/HEAD parity, HTTP blocking, mail/SMTP/payment telemetry, and fail-closed structured output;
- REDESIGN the normal 15-minute path so activation-grade global snapshots do not run on every provider fire;
- target normal-path process shape approximately `2 + N` WordPress bootstraps, where `N` is the number of actually executed owned hooks, instead of current `5 + 2N`;
- move deep global cron/Action Scheduler/protected-business/provenance checks to explicit diagnostic/health tooling rather than deleting the capability;
- remove fixed historical ID32733 and real-business-due-zero gating from the normal 15-minute execution path;
- tighten normal scheduled mode to staging branch only; feature-branch use may exist only for explicit diagnostics/testing and must never become the scheduled steady-state path;
- resolve the candidate-alert continuation-policy mismatch without widening the fixed scheduler contract;
- clarify runtime telemetry so `actual_external_network=0` is not presented as proof beyond the guarded WP HTTP transport scope;
- add bounded sanitized business-progress telemetry sufficient to identify backlog/`has_more` without IDs, recipients, query text, mail bodies, or PII.

This task implements exactly that bounded redesign. It is not a general Communications refactor and not a cron subsystem rewrite.

---

## 2. Goal

Implement a lightweight permanent staging execution path that preserves the accepted safety boundary while moving deep activation/regression evidence out of every natural 15-minute run.

Success means:

1. the existing Hostinger scheduler command and cadence remain unchanged;
2. the zero-argument runner still exposes only the same three owned hooks in the same order;
3. every normal natural cycle uses one lightweight guarded preflight, one process for each actually due/executed hook, and one lightweight guarded final verification — target `2 + N` WordPress bootstraps;
4. expensive/global diagnostics remain available through explicit no-business-execution diagnostic tooling;
5. candidate-alert continuation behavior no longer creates a scheduler surface outside the fixed three-hook contract when invoked by this selective runner;
6. one post-deploy **natural Hostinger fire** proves the redesigned steady-state path before final PASS.

Do not optimize away a meaningful safety control merely to reduce process count.

---

## 3. Hard scheduler/control-plane boundary

Do **not** edit, delete, disable, recreate, duplicate, or manually trigger the Hostinger/hPanel scheduler entry.

Do not change its cadence or command.

Do not create any second scheduler entry, temporary scheduler entry, web-cron, root/system cron, systemd timer, Action Scheduler runner, GitHub Actions schedule, daemon/loop/nohup substitute, or request-driven WP-Cron path.

The existing scheduler may naturally fire while implementation work is happening. The task must be safe under that fact:

- until staging integration, the accepted baseline runner remains active;
- during any short source/deploy mismatch window, existing fail-closed source/deploy parity must prevent business execution;
- after staging deployment, the first natural fire of the redesigned runner becomes acceptance evidence.

Codex must not use manual normal-runner execution as a substitute for that natural-fire proof.

Production scheduler/filesystem/database/runtime/WordPress access is forbidden.

---

## 4. Source scope

Expected source scope is bounded to Raspitajse-owned files required by the 2.11 decision.

Primary expected files/components:

- `tools/raspitajse-staging-owned-cron-runner.sh`;
- `tools/raspitajse-staging-owned-cron-guard.php`;
- one new or clearly separated Raspitajse-owned deep diagnostic/health tool if needed;
- the minimum directly relevant `raspitajse-communications` candidate-job-alert evaluator code required to suppress continuation creation specifically under the selective-runner execution context and expose sanitized progress telemetry;
- narrowly scoped test/fixture files for these components.

Do not modify:

- WordPress core;
- WooCommerce core/vendor files;
- WP Job Board Pro vendor files;
- Paid Listings vendor files;
- Superio/theme vendor files;
- unrelated Communications behavior;
- Raspitajse Commerce business semantics;
- existing candidate/job/package business rules;
- Hostinger scheduler configuration.

If the redesign appears to require broader vendor/application changes, STOP and report the architectural blocker rather than broadening scope.

---

## 5. Feature-branch and staging-integration rules

Start implementation from the exact fresh baseline on a scoped feature branch/worktree according to `tasks/README.md`.

Because the live Hostinger scheduler targets the staging repository path, do not leave the shared scheduled worktree checked out on a feature branch during implementation. Prefer an isolated worktree/path for feature development if available through the proven namespace-free workflow.

The accepted live staging worktree must remain on `staging` until bounded integration.

Do not deploy feature-branch code into the live staging runtime as an ad hoc test.

Integrate to `staging` only after source/static/private acceptance passes and after capturing the required pre-integration protected/runtime snapshot defined below.

Fast-forward staging only; no force push or history rewrite.

---

## 6. Required steady-state runner redesign

### 6.1 Permanent every-run controls — KEEP

The normal zero-argument path must still enforce on every natural provider fire:

- exact staging root identity and no redirect/symlink to another target;
- normal scheduled mode only when repository branch is exactly `staging`;
- clean scheduled worktree;
- current HEAD equals deploy marker;
- runtime environment is staging;
- `DISABLE_WP_CRON=true`;
- staging mail-safety is loaded/configured;
- fixed literal allowlist of exactly the same three owned hooks;
- fixed literal hook order;
- empty scheduler/cron event args;
- exactly one recurring event per owned hook;
- expected hourly/3600 recurrence;
- exact callback identity, priority, accepted-args shape;
- due-only behavior; future events must not be forced;
- exact-hook execution only, never broad cron execution;
- nonblocking `flock` overlap protection;
- 45-second per-hook timeout or equally strict existing bound;
- 180-second whole-cycle watchdog or equally strict existing bound;
- permanent WP HTTP pre-transport blocking for the runner context;
- unexpected WP HTTP attribution counter;
- wp_mail/PHPMailer/SMTP telemetry;
- payment/order/refund-path guard/telemetry already owned by the guard;
- fail closed on material environment/contract mismatch;
- structured sanitized JSON terminal output.

Do not weaken these controls.

### 6.2 Bootstrap/process shape

Redesign the normal run so it no longer performs repeated deep snapshots before and after each hook.

Target normal-cycle shape:

- one lightweight guarded preflight WordPress bootstrap;
- one exact-hook WP-CLI process for each hook that is actually due and executed;
- one lightweight guarded final verification WordPress bootstrap;
- therefore approximately `2 + N` WordPress bootstraps for `N=0..3`.

A materially equivalent implementation is acceptable if it preserves safety and is demonstrably simpler, but if normal mode still requires the old `5 + 2N` deep-snapshot architecture, result cannot be PASS without a documented reason that invalidates the 2.11 design.

The preflight must determine due/not-due state and capture the minimum targeted owned-event state needed for final attribution.

The final verification must prove:

- only hooks reported executed advanced according to normal recurrence;
- not-due hooks did not advance in that cycle;
- no owned callback/event duplication/disappearance/args/recurrence drift;
- no forbidden continuation event was created;
- runtime safety counters are internally consistent.

Do not require global business state to be zero in order for steady-state work to execute.

---

## 7. Separate deep diagnostic health capability

Preserve deep diagnostic capability, but remove it from the normal scheduled path.

Create or clearly separate an explicit diagnostic mode/tool that performs the expensive checks identified by 2.11, including as applicable:

- full cron fingerprint;
- non-allowlisted cron fingerprint;
- Action Scheduler pending count/fingerprint;
- protected historical AS state such as ID32733 for audit visibility, but **not** as a normal-run gate;
- owned claim family counts/staleness;
- protected business aggregate/component fingerprints;
- candidate-expiry footprint;
- retired legacy callback/sender assertions;
- complete Communications source/runtime tree or manifest verification;
- other deep activation/regression evidence already implemented in the existing guard.

Rules:

- diagnostic mode must be read-only with respect to business/cron/AS execution;
- it must not execute any owned hook;
- it must not run broad cron or Action Scheduler;
- it must be explicit/manual or deploy/health-check tooling, not a second scheduled 15-minute job;
- `--check-only` may remain as a safe explicit diagnostic interface if its semantics are clear and no business hook executes;
- do not create a new scheduler for deep diagnostics in this task.

If functionality is split into a new file, keep it Raspitajse-owned under `tools/` with a clear name and no vendor coupling.

---

## 8. Deploy/runtime parity redesign

The current normal path recursively hashes both Communications source and runtime trees every 15 minutes. Remove that heavy full-tree traversal from every-run execution only if an equally strong bounded deploy/runtime integrity mechanism replaces it.

Preferred architecture from 2.11:

- retain HEAD/deploy-marker equality every run;
- establish an atomic deployment manifest or equivalent deterministic Communications deployment fingerprint at deploy/integration time;
- verify the lightweight manifest/fingerprint every normal run without recursively traversing the full tree;
- retain explicit full-tree hashing in the deep diagnostic health path.

Do not create a second source of business truth. This manifest is deployment integrity metadata only.

The manifest must be:

- generated deterministically from the deployed owned Communications artifact;
- tied to the staging deployment/commit identity;
- written atomically only after runtime deployment verification passes;
- fail-closed if missing, malformed, stale, or inconsistent with the current deploy marker;
- free of secrets/PII.

If the repository already has a better Raspitajse-owned deploy-state primitive, reuse it rather than inventing parallel machinery.

If no safe atomic manifest mechanism can be implemented within this bounded task, keep the full-tree check rather than weakening parity, and report the exact blocker. Do not silently drop source/runtime verification.

---

## 9. Candidate-alert continuation-policy fix

2.11 identified a real mismatch:

- the candidate-job-alert evaluator can create a continuation event when more work remains;
- the accepted selective scheduler contract permits only the three fixed owned hourly hooks;
- the current guard treats continuation events as forbidden;
- no approved external runner consumes such continuation events.

Resolve this in Raspitajse-owned code without adding another scheduler surface.

Required behavior when the candidate evaluator is invoked under the selective staging runner context:

- do not create/schedule a continuation event;
- preserve bounded batch processing and existing idempotency/ledger/claim guarantees;
- expose sanitized progress such as selected/processed/succeeded/failed/`has_more` counts as appropriate;
- if more work remains, leave it for later normal passes of the existing owned candidate-alert hook rather than creating a new cron entry;
- no candidate IDs, emails, saved-query text, job IDs, recipients, rendered mail bodies, or PII in runner/provider output.

Outside the selective-runner context, do not change continuation behavior unless the code proves the continuation surface is now entirely retired and a broader change is explicitly required to preserve one consistent production design. If broader semantics are unclear, scope the suppression to the runner context and report the remaining follow-up question.

Do not weaken candidate-alert once-ever delivery ledger, config revision, claims, retry/idempotency, or matching semantics.

---

## 10. Observability contract

Preserve the stable top-level provider-facing JSON concepts:

- `result`;
- `environment`;
- `mode`;
- `reason`;
- `duration_seconds`;
- `executed_hooks`;
- fixed-order per-hook statuses;
- safety counters.

You may add versioned/additive fields, but do not break the existing fields without a compelling compatibility reason.

Clarify network telemetry:

- retain hard WP HTTP pre-transport blocking;
- do not imply that `actual_external_network=0` proves absence of every possible raw socket/network mechanism unless such coverage is actually implemented;
- prefer an additive sanitized field/name that explicitly states WP HTTP transport was blocked/intercepted, while keeping old output compatibility where practical.

Add only minimum business-progress telemetry useful for diagnosing bounded processing, for example per-hook aggregate counts such as:

- selected;
- processed;
- succeeded;
- failed;
- has_more.

Only emit aggregates actually supported by the callback/result contract. Do not invent success counts from absence of errors.

No PII/secrets/raw queries/payloads in output or reports.

---

## 11. Normal-path responsibilities to remove or move

The normal 15-minute path should no longer fail solely because of these activation/history diagnostics:

- fixed protected Action Scheduler ID32733 `pending/0` state;
- global Action Scheduler pending fingerprint/count stability;
- protected business aggregate/component fingerprint equality;
- real-business-due count equals zero;
- all owned claim families globally equal zero;
- full global cron fingerprint equality unrelated to targeted owned events;
- non-allowlisted cron fingerprint equality as a per-fire business gate;
- repeated candidate-expiry/legacy-retirement evidence on every hook transition.

Move those checks into deep diagnostic/deploy/periodic health tooling as defined above.

Important: removing a normal-run gate does **not** authorize executing broad cron, Action Scheduler, vendor senders, candidate auto-expiry, payment, or production behavior. Existing execution boundaries remain fixed.

---

## 12. Static/private acceptance before staging integration

Before touching staging, prove the redesign in an isolated/source-only way.

At minimum validate:

- shell syntax and PHP lint;
- fixed allowlist/order unchanged and duplicated literals remain consistent or are consolidated safely;
- zero-argument normal mode accepted;
- arbitrary args/options/injection strings rejected before WordPress execution;
- `--check-only`/deep diagnostic executes no business hook;
- normal scheduled mode rejects feature branch/non-staging branch;
- feature diagnostic contract, if retained, cannot become normal scheduled execution;
- overlap lock behavior;
- fail-closed missing guard/manifest/deploy mismatch cases;
- preflight classification for 0/1/2/3 due hooks;
- exact fixed execution order;
- child error and timeout stop later hooks;
- final targeted timestamp verification catches executed/not-due mismatch, duplicate event, changed args, wrong recurrence, wrong callback;
- no broad cron/AS command path exists;
- HTTP/mail/payment guard matrix remains effective;
- candidate runner-context continuation suppression produces no continuation cron row and preserves bounded `has_more` semantics;
- deep diagnostic mode remains no-execution/read-only;
- structured output is sanitized and schema-compatible.

Use fixtures/mocks/private harnesses that do not send real mail, payments or external HTTP and do not mutate real staging business data.

Do not manually run the live zero-argument staging runner as a test.

---

## 13. Pre-integration live staging acceptance gate

Immediately before staging integration/deployment, perform one fully guarded read-only live snapshot using the accepted safety mechanism. Do not execute business hooks.

Prove at minimum:

- fresh `origin/staging` still equals baseline and source worktree is clean;
- current runtime/deploy marker corresponds to baseline;
- runtime environment staging;
- `DISABLE_WP_CRON=true`;
- current accepted three callback/event contracts valid;
- continuation rows `0`;
- legacy daily/shared-expiry callbacks/events `0`;
- retired vendor candidate-job and employer-candidate senders `0`;
- candidate auto-expiry disabled;
- Action Scheduler current state captured for comparison but not forced to historical immutability;
- ID32733 status/attempts recorded for protected comparison;
- protected business aggregate/component fingerprints captured;
- current owned event timestamps captured;
- no current runner lock/process conflict that would make deployment unsafe.

If material protected/business drift is found, STOP before integration.

Natural owned timestamp movement caused by the already-accepted scheduler is not itself drift; attribute by hook/event shape and current time rather than comparing to stale historical timestamp constants.

---

## 14. Staging integration and deployment

After all pre-integration gates pass:

1. integrate the scoped implementation into `staging` through the repository workflow;
2. deploy only the changed Raspitajse-owned runtime files required by this task;
3. update any new deploy-integrity manifest and the existing deploy marker atomically/in the proven safe order;
4. verify source/runtime parity for the changed owned files;
5. keep the Hostinger scheduler untouched;
6. do not manually invoke the normal runner after deployment.

If a natural scheduler fire lands during the short HEAD/marker/runtime transition, the runner must fail closed rather than partially execute. Report any observed fail-closed provider/runtime evidence if available; do not treat safe fail-closed as business regression.

Do not modify production.

---

## 15. Post-deploy two-stage acceptance

Because Codex does not have the authorized hPanel provider-output surface, final acceptance is explicitly two-stage.

### Stage A — implementation/deploy readiness

After deployment, take a fresh guarded read-only T0 snapshot of the redesigned runtime and prove:

- staging source HEAD = fresh `origin/staging` = deploy marker;
- changed runtime files match source;
- runner/guard/deep diagnostic syntax/lint and deployment integrity pass;
- fixed callback/event contracts valid;
- no continuation row;
- no business/mail/payment/external side effect from diagnostics;
- protected business state unchanged from the pre-integration snapshot except legitimate owned cron timestamp movement attributable to natural scheduler fires during deployment;
- Action Scheduler not executed by this task;
- production untouched.

Then publish:

`PARTIAL — READY_FOR_HUMAN_NATURAL_FIRE_OBSERVATION`

and STOP.

The report must include the exact final staging SHA, T0 owned timestamps, expected redesigned output schema, and instruct the human operator to return the **first complete natural Hostinger View Output JSON after that T0**. Do not ask the human to change or manually trigger the scheduler.

### Stage B — resume after human supplies first natural provider output

On resume:

- fetch fresh refs and re-read this task/README;
- confirm `origin/staging` still equals the Stage A final staging SHA; otherwise STOP with mismatch;
- accept only the complete first natural provider JSON after T0 as human/provider evidence;
- take one fully guarded read-only T1 snapshot promptly;
- reconcile T0 -> provider fire -> T1.

PASS requires:

- provider `result=PASS`, `environment=staging`, `mode=run`;
- redesigned runner reports the expected lightweight execution path/process count or equivalent observability proving the old `5 + 2N` deep path is gone;
- only the fixed three hooks are represented, in fixed order;
- each reported `executed` hook advances exactly one hourly recurrence;
- each reported `not_due` hook remains unchanged for that observed fire window;
- no continuation event created;
- callback/event shape remains exact;
- provider HTTP/mail/PHPMailer/SMTP/payment counters satisfy the guard contract;
- no unexpected WP HTTP transport escapes;
- protected business fingerprints unchanged except legitimate business mutations only if real due work existed and the task contract explicitly proves them safe. For this acceptance window, prefer/require no real business side-effect fixture; do not fabricate business work;
- no broad cron or AS execution;
- production untouched.

If provider output is ERROR/LOCKED/ambiguous, or T0/T1 attribution fails, do not improvise. Report PARTIAL/FAIL with exact safe blocker. Do not mutate the Hostinger scheduler as a recovery shortcut.

---

## 16. Protected behavior invariants

Throughout implementation and acceptance preserve:

- candidate profiles do not auto-expire by age;
- employer→candidate alerts remain retired;
- candidate→job owned evaluator remains the only active candidate job-alert sender path;
- job listing expiry remains separate from package entitlement expiry;
- package 30-day consumption validity remains separate from published-job duration;
- SenderPolicy and mail-safety behavior remain intact;
- no real SMTP transport during tests;
- no payment/charge/refund execution;
- ID32733 is never executed;
- no broad Action Scheduler runner;
- no production access.

If any of these invariants would need to change, STOP and report scope conflict.

---

## 17. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` still applies.

On first confirmed signature:

- activate the documented circuit breaker;
- do not repeatedly retry the same sandbox-dependent helper;
- use proven namespace-free Git/filesystem/WP-CLI methods;
- do not wait for host capacity;
- do not use broad process kills;
- if a bounded task-private child exceeds its timeout, inspect and terminate only that exact child/process group.

This does not authorize bypassing scheduler, source, deploy, runtime, or safety gates.

---

## 18. Acceptance criteria

Final PASS requires all of the following:

- bounded source scope only;
- runner and guard retained;
- Hostinger scheduler untouched and still exactly one `*/15` zero-argument runner entry by human/provider context;
- fixed three-hook allowlist/order unchanged;
- no arbitrary execution surface;
- no broad cron/AS path;
- normal mode staging-only;
- overlap/timeouts/watchdog/fail-closed preserved;
- lightweight preflight/final architecture implemented;
- normal process shape approximately `2 + N` and old repeated deep snapshots removed from natural path;
- deep diagnostic health capability preserved separately and executes no business hook;
- source/runtime deployment integrity remains fail-closed without per-fire full-tree traversal, or full-tree traversal retained with documented blocker if no safe replacement exists;
- fixed ID32733/global AS/protected-business/real-due-zero checks no longer gate the normal 15-minute path;
- candidate selective-runner continuation mismatch resolved with no continuation scheduler surface;
- provider JSON remains structured/sanitized and network scope wording is accurate;
- static/private tests PASS;
- staging integration/deploy PASS;
- Stage A guarded T0 PASS;
- first natural Hostinger fire observed by human/provider and Stage B T0/fire/T1 reconciliation PASS;
- no real external network/mail/SMTP/payment side effects;
- Action Scheduler execution `0`;
- production touched `NO`.

If Stage A is complete but human natural-fire evidence has not yet been supplied, result must be PARTIAL/READY as specified, not PASS.

---

## 19. Final report requirements

Final report must include:

- result and whether Stage A only or whole task PASS;
- baseline, feature SHA if used, final staging SHA, deploy marker and source/runtime parity;
- exact files changed and why;
- before/after normal runner process/bootstrap model;
- every-run core retained;
- deep diagnostic checks moved out of normal path;
- deploy-integrity mechanism and failure behavior;
- candidate continuation-policy implementation and tests;
- output schema/telemetry changes;
- static/private test matrix and counts;
- pre-integration protected snapshot summary;
- Stage A T0 and, on final resume, provider/T1 attribution;
- current Action Scheduler count/fingerprint and ID32733 status/attempts for evidence only, with AS executions `0`;
- protected business fingerprint before/after;
- HTTP/mail/SMTP/payment counters;
- scheduler mutations `0`;
- production touched `NO`;
- cleanup status;
- exactly one proposed next task, not created or started.

If whole-task PASS is achieved, the proposed next task should leave the cron/scheduler implementation arc and move to the higher-value next area:

**WP Job Board Pro security/upgrade readiness audit with fresh authoritative vulnerability/version verification.**

Do not implement that upgrade in 2.12.

STOP after publishing the report. Do not begin 2.13.