# Autonomous Codex Workflow

Purpose: allow one Codex Remote session to plan, execute, validate and report a sequence of **safe staging tasks** without requiring a human to hand-write every next prompt.

This workflow never overrides `AGENTS.md`. Safety rules always win.

## 1. Required startup sequence

At the beginning of an autonomous session:

1. read `AGENTS.md`;
2. read `CODEX_PROJECT_CONTEXT.md`;
3. read `STAGING_MUTATION_POLICY.md`;
4. read `STAGING_DEFINITION_OF_DONE.md`;
5. read this file;
6. read `CODEX_HOST_RESILIENCE.md`;
7. fetch `origin`;
8. fetch the reporting branch without checking it out in the primary worktree;
9. inspect `reports/latest.md` from `origin/codex-reports`;
10. inspect current `origin/staging` and repository cleanliness;
11. determine the active workstream and the next allowed task.

Suggested read-only report command:

```bash
git fetch --prune origin "+refs/heads/codex-reports:refs/remotes/origin/codex-reports"
git show origin/codex-reports:reports/latest.md
```

Never merge or deploy `codex-reports`.

If the latest report contains `HOST_NAMESPACE_PRESSURE`, `bwrap` + `ENOSPC`, or an equivalent known namespace failure, apply the circuit breaker in `CODEX_HOST_RESILIENCE.md` before preparing the next task. Do not begin by retrying the same patch helper.

Use `STAGING_DEFINITION_OF_DONE.md` to prioritize work that actually moves the technical staging phase toward completion. Do not create parity/cleanup tasks for non-blocking legacy behavior until it is classified `KEEP`, `CHANGE`, or `DROP`.

## 2. Task numbering

Numbered task titles are mandatory for autonomous tasks.

Format:

`Zadatak N.M — Short descriptive title`

Rules:

- `N.0` starts a new workstream;
- `N.1`, `N.2`, ... continue that same workstream;
- continuing an older workstream keeps its original major number even if another workstream was created later;
- choose a new major number only for a genuinely new area of work;
- never reuse an already-published Task ID for a different action.

Current known workstreams:

- Workstream `1.x` — staging WP-Cron / Action Scheduler recovery;
- Workstream `2.x` — autonomous Codex orchestration infrastructure;
- Workstream `3.x` — Hostinger/Codex namespace resilience and host-resource tooling;
- Workstream `4.x` — staging Definition of Done / mutation-governance policy.

A new infrastructure/governance workstream does not renumber the scheduler recovery stream. Returning to scheduler recovery continues at the next unused `1.x` ID.

Before selecting an ID, inspect existing numbered reports when needed to avoid collisions.

## 3. Autonomous loop

Within one Codex session, repeat this loop:

### STEP A — Observe

- read latest numbered report;
- inspect current branch/HEAD/runtime required by that workstream;
- identify what the previous task proved, changed and intentionally left untouched;
- identify the smallest useful next step;
- prefer work that advances a blocker in `STAGING_DEFINITION_OF_DONE.md` over cosmetic/perfectionist cleanup.

### STEP B — Classify risk and state

Assign one risk class and, for mutations, one staging state class from `STAGING_MUTATION_POLICY.md`.

#### `AUTONOMOUS_SAFE`

Can proceed without additional approval when all scope is explicit.

Typical examples:

- read-only inspection/diagnosis;
- documentation;
- feature-branch code refactor with tests and no runtime mutation;
- syntax/static checks;
- targeted regression tests using policy-compliant disposable fixtures;
- already-classified bounded technical staging housekeeping under active safety guards;
- exact scheduler lifecycle/audit/housekeeping writes explicitly permitted by a classified task contract;
- narrowly scoped deployment explicitly allowed by the current workstream and `AGENTS.md`.

#### `APPROVAL_REQUIRED`

Stop before executing. Publish/report the proposed next step and why approval is required.

Includes:

- protected non-fixture candidate/employer/application/message state mutation;
- protected non-fixture job/content mutation when it has business meaning;
- order/payment/refund/subscription state mutation outside an approved safe fixture strategy;
- password reset/change;
- mail to real or uncontrolled recipients;
- fixture creation whose selector/identity could collide with non-fixture data;
- destructive or broad DB changes;
- personal-data deletion outside an exact disposable-fixture contract;
- enabling a global/background runner after a backlog;
- removal/weakening of safety guards;
- actions with material unknown side effects.

#### `FORBIDDEN`

Do not propose execution as an autonomous next step.

Includes:

- production mutation/deployment/database access;
- `main` or baseline history changes;
- force-push/shared-history rewrite;
- revealing secrets or `wp-config.php` contents;
- `wp cron event run --due-now` while backlog recovery rules forbid it;
- broad/unclassified Action Scheduler processing;
- bypassing deployment, mail or cron guards;
- commands that could recursively include production paths.

#### `UNKNOWN`

If risk or mutation state cannot be confidently classified, treat it as `APPROVAL_REQUIRED` and stop.

`HOST_NAMESPACE_PRESSURE` is an infrastructure condition, not a risk class. It must be handled with the namespace-resilience circuit breaker while preserving the original task's risk classification.

### STEP C — Define the task before executing

For every autonomous task, write an internal execution contract with:

- Task ID and exact title;
- objective;
- risk class;
- mutation state class when applicable: protected business / disposable fixture / technical housekeeping / unknown;
- allowed mutations;
- explicitly forbidden mutations;
- external effects allowed/forbidden;
- preconditions;
- exact validation checks;
- stop conditions;
- rollback/recovery expectation proportional to the state class;
- expected report result.

Keep the task small enough that a failure has a narrow blast radius.

For tasks likely to build temporary guards/harnesses, also define the namespace-free correction path up front so a later patch-helper ENOSPC does not cause an improvised retry loop.

### STEP D — Execute

- use the correct branch model;
- use exact staging paths;
- use approved deployment tooling only;
- run preconditions and staging-state classification again immediately before mutation;
- execute only the action described by the current task;
- allow only the technical-housekeeping/fixture mutations explicitly listed in the contract;
- never silently broaden scope because another issue is noticed.

Unexpected findings become a future task or a stop condition.

#### Namespace-resilient editing rule

If the Codex `apply_patch`/bubblewrap helper reports the known namespace `ENOSPC` signature:

1. classify it as `HOST_NAMESPACE_PRESSURE`;
2. do not repeat the same helper more than once in that task;
3. do not wait for capacity to recover inside the task;
4. for repository edits on a clean `feature/*` branch, use the validated patch through `bash tools/codex-git-apply.sh`;
5. for fresh task-private `/tmp/raspitajse-*` files, use `bash tools/codex-tmp-rewrite.sh` or a deterministic inspected one-line edit as defined in `CODEX_HOST_RESILIENCE.md`;
6. rerun the complete syntax/static/diff validation after the fallback;
7. if the fallback cannot preserve the exact execution contract, publish `PARTIAL` and stop.

Never use namespace pressure as justification to loosen a guard, broaden an allowlist, skip rollback preparation, or change an approval boundary.

### STEP E — Validate

Validate both:

1. the intended effect happened;
2. protected business/safety state did **not** change outside the contract;
3. any technical housekeeping or disposable-fixture mutation stayed within its classified bounds.

For risky subsystems use fingerprints/counts/IDs before and after rather than relying on absence of visible errors.

Use rollback evidence proportional to state value as defined in `STAGING_MUTATION_POLICY.md`. Do not require expensive raw backups of disposable audit/cache history unless that history is needed for the current task/recovery investigation.

If a namespace-free edit fallback was used, validation must additionally prove the exact intended file set, run `git diff --check` for repository edits, and run the complete syntax/static checks for any temporary executable guard/harness before loading it.

### STEP F — Publish report

After every autonomous task, publish with:

```bash
bash tools/codex-report.sh "Zadatak N.M — Exact title" PASS
```

(or `PARTIAL`, `FAIL`, `SKIPPED` as appropriate, normally with a structured body piped on stdin).

The numbered helper must produce:

`reports/<UTC>-zadatak-N_M.md`

The report should include when applicable:

- exact Task ID/title;
- source branch/HEAD;
- changed files/commit;
- deployment SHA or `NO DEPLOY`;
- state/risk classification;
- pre/post state evidence;
- tests and validation;
- expected technical-housekeeping/fixture mutations;
- unexpected findings;
- stop reason;
- rollback state;
- `Production touched: NO`.

Result semantics come from `STAGING_MUTATION_POLICY.md`:

- `PASS` may include expected, pre-classified bounded technical housekeeping;
- `PARTIAL` is appropriate when the intended task is incomplete but safety/business invariants remain protected;
- `FAIL` is reserved for violated protected/safety invariants, unapproved execution, unbounded/unknown integrity loss, failed required rollback, or equivalent real failure conditions.

Do not retroactively rewrite older reports merely because this policy changes prospective classification.

For `HOST_NAMESPACE_PRESSURE`, also report whether the namespace-free fallback was used and whether any WordPress/runtime mutation had started before the infrastructure failure.

### STEP G — Decide whether to continue

After publishing, re-read the resulting report if necessary and choose:

- `CONTINUE` — next step is `AUTONOMOUS_SAFE`;
- `STOP_FOR_APPROVAL` — next step is `APPROVAL_REQUIRED` or `UNKNOWN`;
- `STOP_FAILURE` — a protected/safety invariant or required validation failed;
- `WORKSTREAM_COMPLETE` — current objective is complete.

If continuing, assign the next appropriate Task ID and repeat the loop.

A host namespace failure that has already hit its circuit breaker should not be retried repeatedly in the same session. Continue only if the next task can proceed without depending on the exhausted helper; otherwise stop cleanly.

## 4. Hard stop gates

Stop the autonomous loop immediately if any of the following occurs:

- environment cannot be proven to be staging;
- repository is unexpectedly dirty;
- source branch/HEAD differs unexpectedly;
- deployment output references unexpected deletion/path;
- production path or production database could be affected;
- a required guard is missing/inactive;
- cron/mail/Action Scheduler state changes outside the exact classified/allowed task scope;
- a real-user recipient or private data would be exposed/affected;
- protected non-fixture order/payment/application/user state changes without approval;
- a disposable fixture selector can no longer be proven isolated;
- a callback/queue item cannot be confidently classified;
- a secret would need to be printed;
- rollback/recovery proportional to the state value cannot be understood before mutation;
- test/validation result is ambiguous after a protected/business mutation;
- `HOST_NAMESPACE_PRESSURE` prevents both the normal helper and the approved namespace-free fallback from completing safely.

Do not “fix forward” through a hard stop. Preserve evidence and report.

## 5. Branch and merge autonomy

### Feature work

Normal code changes must use a `feature/*` branch based on current `origin/staging`.

Codex may create/commit/push a feature branch autonomously for `AUTONOMOUS_SAFE` repository-only work.

### Staging merge

A feature may be fast-forwarded/merged into `staging` autonomously only when all are true:

- task explicitly permits integration;
- diff contains only intended files;
- feature is current with `staging` or merge is otherwise clearly safe and non-rewriting;
- required syntax/tests pass;
- no production deployment occurs;
- change is low-risk/reversible;
- no protected business-state mutation is hidden in the merge/deploy.

If any condition is uncertain, leave the feature branch ready and `STOP_FOR_APPROVAL`.

Never force-update `staging`.

## 6. Deployment autonomy

Deployment is not implied by a code task.

Use only `deployment/deploy-staging.sh` when the current task explicitly includes staging deployment.

Before deployment:

- inspect diff;
- run required syntax checks;
- prove staging environment;
- verify repository cleanliness;
- verify the changed paths are within deployment allowlists.

After deployment:

- verify deployment marker;
- verify staging environment;
- run targeted smoke checks;
- verify subsystem safety controls remain active.

Never create a production deployment path as part of autonomous work.

## 7. Cron / Action Scheduler special mode

While the temporary staging cron recovery guard is active:

- normal traffic-triggered cron must remain disabled;
- do not request `wp-cron.php` casually;
- never use `--due-now`;
- execute only individually approved/pre-classified hooks/actions;
- fingerprint protected/high-risk schedules before/after each mutation;
- broad queue runners remain forbidden/unapproved while recovery rules say so;
- exact scheduler lifecycle/audit/successor writes may be allowed by an individual execution contract;
- plugin-owned technical history cleanup may be allowed only after its source/delete criteria are understood and classified under `STAGING_MUTATION_POLICY.md`;
- stop if `doing_cron` unexpectedly reappears or protected/unclassified schedules move.

Action Scheduler actions must be individually/class-wise classified before execution. Mail safety does not make business-state actions safe.

The cron recovery guard can be removed only in a dedicated task after backlog recovery and ongoing trigger strategy are validated.

## 8. Disposable fixture special mode

Controlled staging fixtures may be autonomous-safe when they satisfy `STAGING_MUTATION_POLICY.md`.

For each fixture family:

- use unmistakable collision-resistant test markers;
- capture exact created IDs/identifiers;
- prevent real payments and uncontrolled recipients;
- validate non-fixture business state remained unchanged;
- clean up only exact fixture records when cleanup is part of the task;
- do not turn a disposable-fixture permission into permission to modify pre-existing staging business data.

## 9. Mail special mode

When a task can cause email:

- verify staging mail-safety first;
- verify communications takeover state;
- classify whether the flow changes protected business/user state before mail;
- use only policy-compliant disposable fixtures when required;
- never infer safety only from recipient rewriting;
- stop if real/uncontrolled recipients can be reached.

## 10. Legacy/refactor decision rule

Before reproducing historical behavior, classify it as:

- `KEEP` — parity required before old implementation removal;
- `CHANGE` — implement/test the new intended behavior instead of legacy parity;
- `DROP` — do not spend refactor effort reproducing it.

The target is update-safe vendor code with critical custom behavior in Raspitajse-owned plugins/hooks/filters/template overrides where practical.

Do not make exhaustive cosmetic cleanup a blocker unless it affects a criterion in `STAGING_DEFINITION_OF_DONE.md`.

## 11. Project-context maintenance

`CODEX_PROJECT_CONTEXT.md` is a living technical audit.

Codex may update it autonomously when a task **proves** an important durable architectural fact, completes a major roadmap item, or invalidates an old finding.

When updating it:

- keep it concise;
- distinguish verified current state from historical audit leads;
- do not copy full reports into it;
- do not include secrets/private data;
- reference report Task IDs rather than duplicating large evidence tables;
- align roadmap/status language with `STAGING_DEFINITION_OF_DONE.md` and `STAGING_MUTATION_POLICY.md`.

## 12. Host resource resilience

`CODEX_HOST_RESILIENCE.md` is the authoritative operating procedure for the known Hostinger namespace exhaustion condition.

Use these tools instead of repeated sandbox retries:

```bash
bash tools/codex-host-diagnose.sh
cat change.patch | bash tools/codex-git-apply.sh
cat corrected-file | bash tools/codex-tmp-rewrite.sh /tmp/raspitajse-task-.../file <expected-sha256>
```

Operational rules:

- batch safe repository changes into one patch/helper invocation;
- prefer one fresh task-private temporary directory per attempt;
- never run namespace probes in a loop;
- avoid parallel/background work;
- if task preparation is stuck on host tooling for roughly 10 minutes rather than the actual task, trip the circuit breaker and report instead of waiting for an hour;
- a successful namespace-free edit still requires all normal validation before any staging/runtime mutation.

## 13. Staging completion / release boundary

`STAGING_DEFINITION_OF_DONE.md` is the formal technical exit gate.

When its blockers are satisfied, create a dedicated final staging verification task/report that records the evidence and may declare staging `TECHNICALLY_READY`.

`TECHNICALLY_READY` does **not** authorize production deployment. Production release remains a separate explicitly approved workstream using a code-first strategy and only narrowly proven DB/data changes.

## 14. Session-end output

When the autonomous loop stops, the final user-facing result should be compact and include:

- last completed Task ID/result;
- what changed;
- current branch/HEAD/deployed SHA if relevant;
- why execution stopped;
- next recommended Task ID/title;
- whether human approval is required;
- exact latest report path;
- explicit confirmation that production was not touched.

Do not ask the user to paste report contents when the report is available from the repository.

## 15. Autonomous session objective

The goal is not to maximize the number of tasks completed in one run.

The goal is to make **small, auditable progress with preserved business/safety invariants** toward `STAGING_DEFINITION_OF_DONE.md` until the next step genuinely requires a human decision.

Host-resource resilience is part of that objective: fail fast, use the bounded namespace-free fallback, validate, and continue only when safety remains equivalent.
