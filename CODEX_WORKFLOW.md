# Autonomous Codex Workflow

Purpose: allow one Codex Remote session to plan, execute, validate and report a sequence of **safe staging tasks** without requiring a human to hand-write every next prompt.

This workflow never overrides `AGENTS.md`. Safety rules always win.

## 1. Required startup sequence

At the beginning of an autonomous session:

1. read `AGENTS.md`;
2. read `CODEX_PROJECT_CONTEXT.md`;
3. read this file;
4. fetch `origin`;
5. fetch the reporting branch without checking it out in the primary worktree;
6. inspect `reports/latest.md` from `origin/codex-reports`;
7. inspect current `origin/staging` and repository cleanliness;
8. determine the active workstream and the next allowed task.

Suggested read-only report command:

```bash
git fetch --prune origin "+refs/heads/codex-reports:refs/remotes/origin/codex-reports"
git show origin/codex-reports:reports/latest.md
```

Never merge or deploy `codex-reports`.

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

Current known workstreams at the time this file was introduced:

- Workstream `1.x` — staging WP-Cron / Action Scheduler recovery;
- Workstream `2.x` — autonomous Codex orchestration infrastructure.

Therefore after `Zadatak 2.0`, returning to cron recovery should normally resume at `Zadatak 1.4`, not `2.1`.

Before selecting an ID, inspect existing numbered reports when needed to avoid collisions.

## 3. Autonomous loop

Within one Codex session, repeat this loop:

### STEP A — Observe

- read latest numbered report;
- inspect current branch/HEAD/runtime required by that workstream;
- identify what the previous task proved, changed and intentionally left untouched;
- identify the smallest useful next step.

### STEP B — Classify risk

Assign one of these classes:

#### `AUTONOMOUS_SAFE`

Can proceed without additional approval when all scope is explicit.

Typical examples:

- read-only inspection/diagnosis;
- documentation;
- feature-branch code refactor with tests and no runtime mutation;
- syntax/static checks;
- targeted regression tests using already-approved disposable fixtures;
- already-classified low-risk staging housekeeping under active safety guards;
- narrowly scoped deployment explicitly allowed by the current workstream and `AGENTS.md`.

#### `APPROVAL_REQUIRED`

Stop before executing. Publish/report the proposed next step and why approval is required.

Includes:

- candidate/employer/application/message state mutation;
- order/payment/refund/subscription state mutation;
- password reset/change;
- mail to real or uncontrolled recipients;
- fixture creation that could collide with real-like data;
- destructive or broad DB changes;
- personal-data deletion;
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

If risk cannot be confidently classified, treat it as `APPROVAL_REQUIRED` and stop.

### STEP C — Define the task before executing

For every autonomous task, write an internal execution contract with:

- Task ID and exact title;
- objective;
- allowed mutations;
- explicitly forbidden mutations;
- preconditions;
- exact validation checks;
- stop conditions;
- rollback/recovery expectation where applicable;
- expected report result.

Keep the task small enough that a failure has a narrow blast radius.

### STEP D — Execute

- use the correct branch model;
- use exact staging paths;
- use approved deployment tooling only;
- run preconditions again immediately before mutation;
- execute only the action described by the current task;
- never silently broaden scope because another issue is noticed.

Unexpected findings become a future task or a stop condition.

### STEP E — Validate

Validate both:

1. the intended effect happened;
2. unrelated/high-risk state did **not** change.

For risky subsystems use fingerprints/counts/IDs before and after rather than relying on absence of visible errors.

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
- pre/post state evidence;
- tests and validation;
- unexpected findings;
- stop reason;
- rollback state;
- `Production touched: NO`.

### STEP G — Decide whether to continue

After publishing, re-read the resulting report if necessary and choose:

- `CONTINUE` — next step is `AUTONOMOUS_SAFE`;
- `STOP_FOR_APPROVAL` — next step is `APPROVAL_REQUIRED` or `UNKNOWN`;
- `STOP_FAILURE` — safety invariant or validation failed;
- `WORKSTREAM_COMPLETE` — current objective is complete.

If continuing, assign the next appropriate Task ID and repeat the loop.

## 4. Hard stop gates

Stop the autonomous loop immediately if any of the following occurs:

- environment cannot be proven to be staging;
- repository is unexpectedly dirty;
- source branch/HEAD differs unexpectedly;
- deployment output references unexpected deletion/path;
- production path or production database could be affected;
- a required guard is missing/inactive;
- cron/mail/Action Scheduler state changes outside approved scope;
- a real-user recipient or private data would be exposed/affected;
- order/payment/application/user state could change without an approved fixture;
- a callback/queue item cannot be confidently classified;
- a secret would need to be printed;
- rollback/recovery cannot be understood before mutation;
- test/validation result is ambiguous after a mutation.

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
- no user/business-state mutation is hidden in the merge/deploy.

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
- execute only individually approved/pre-classified hooks;
- fingerprint HIGH-risk cron schedules before/after each mutation;
- keep Action Scheduler unchanged unless the task explicitly targets one classified action;
- stop if `doing_cron` unexpectedly reappears or unrelated schedules move.

Action Scheduler actions must be individually/class-wise classified before execution. Mail safety does not make business-state actions safe.

The cron recovery guard can be removed only in a dedicated task after backlog recovery and ongoing trigger strategy are validated.

## 8. Mail special mode

When a task can cause email:

- verify staging mail-safety first;
- verify communications takeover state;
- classify whether the flow changes business/user state before mail;
- use only disposable fixtures when required;
- never infer safety only from recipient rewriting;
- stop if real/uncontrolled recipients can be reached.

## 9. Project-context maintenance

`CODEX_PROJECT_CONTEXT.md` is a living technical audit.

Codex may update it autonomously when a task **proves** an important durable architectural fact, completes a major roadmap item, or invalidates an old finding.

When updating it:

- keep it concise;
- distinguish verified current state from historical audit leads;
- do not copy full reports into it;
- do not include secrets/private data;
- reference report Task IDs rather than duplicating large evidence tables.

## 10. Session-end output

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

## 11. Autonomous session objective

The goal is not to maximize the number of tasks completed in one run.

The goal is to make **small, auditable progress with preserved safety invariants** until the next step genuinely requires a human decision.
