# Zadatak 2.06 — Implement a fail-closed staging external selective allowlist runner for approved Raspitajse cron hooks

Status: READY
Baseline: 544a31171132a3ce95323162df2519ac0135840a
Previous task: 2.05
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or modifying source. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Verify that fresh `origin/staging` is exactly:

`544a31171132a3ce95323162df2519ac0135840a`

If it differs, STOP and report the mismatch. Do not silently rebase this task.

Execute only Zadatak 2.06. Work on one scoped feature branch from exact `origin/staging`. Integrate to `staging` only after the implementation and acceptance evidence pass. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.07 automatically.

---

## 1. Context already established

Zadatak 2.05 completed PASS and proved that each approved Raspitajse-owned WP-Cron event can be invoked safely through the normal exact WP-CLI event path, sequentially and at most once, while broad cron remains unsafe.

Approved owned hooks are exactly:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

2.05 proved:

- exact invocation count `1/1/1`;
- all three remain exactly one callback + one `hourly` event;
- no broad `/wp-cron.php`, `--due-now`, `--all`, Action Scheduler, continuation, vendor/core cron or arbitrary hook execution;
- no business mutations because due business work was `0`;
- `wp_mail=0`, PHPMailer/SMTP `0`, payment `0`, actual external network `0`;
- Action Scheduler pending `7`, protected ID32733 `pending/0` unchanged;
- only the three owned recurring timestamps advanced normally;
- `DISABLE_WP_CRON=true` remains correct;
- preferred architecture from 2.04 is `EXTERNAL_SELECTIVE_ALLOWLIST_RUNNER`.

The current application staging SHA did not change in 2.05 because it was an execution-only validation.

This task implements the runner itself. It does **not** activate Hostinger/hPanel cron or any server scheduler.

---

## 2. Goal

Implement a small Raspitajse-owned, staging-only external runner that can later be called by a server scheduler and that:

- has a fixed internal allowlist of only the three approved hooks;
- never accepts an arbitrary hook from command-line/user input;
- runs only an allowlisted event that is actually due/overdue;
- executes due allowlisted hooks sequentially in deterministic order, each at most once per runner process;
- uses the normal exact WP-CLI cron event path rather than reproducing WordPress recurrence logic;
- fails closed if callback/event identity, environment, runtime parity, safety guards or protected-state preconditions differ;
- cannot reach broad WP-Cron, Action Scheduler, candidate-job continuation, legacy expiry hooks, vendor/core scheduled hooks, payment/refund/order mutation paths, or arbitrary external application HTTP;
- has bounded runtime and overlap protection;
- emits sanitized machine-readable/operationally useful results without PII/secrets.

The implementation must be suitable for a later real staging scheduler activation without becoming a demo/test-only path.

---

## 3. Owned source boundary

Prefer a narrow operational implementation under `tools/`, for example:

- `tools/raspitajse-staging-owned-cron-runner.sh`
- an optional small companion PHP guard/verifier under `tools/` if that is materially cleaner than embedding PHP in shell.

Do not modify WordPress core, WP Job Board Pro, Paid Listings, WooCommerce, Superio parent/child theme, Raspitajse Commerce, or vendor plugin code.

Do not modify the three owned evaluator implementations unless a concrete defect blocks the runner. If such a defect is discovered, STOP/PARTIAL and report it rather than broadening 2.06 silently.

Do not create a generic cron framework. This is a narrow staging operational boundary for exactly three already-approved hooks.

The existing staging deployment contract/path constants may be reused where appropriate. Do not add secrets or credentials.

---

## 4. Runner interface and fixed allowlist

The operational runner must have **no arbitrary hook parameter**.

Preferred interface:

- ordinary invocation with no positional arguments performs one bounded selective cycle;
- any unexpected positional argument or unsupported option exits fail-closed before WordPress execution;
- if a diagnostic/check-only mode is implemented, it must be a fixed enumerated mode and must not allow a caller to supply a hook name, PHP callback, command, path, or arbitrary WP-CLI argument.

The internal execution order must be fixed:

1. job-listing expiry;
2. employer job-expiry notice;
3. candidate→job alert evaluator.

Each can execute at most once in one runner process.

Never construct the hook name from untrusted input.

---

## 5. Staging/environment and parity gate

Before executing any hook, fail closed unless all applicable checks pass:

- WordPress environment is exactly `staging`;
- `DISABLE_WP_CRON` remains effectively `true`;
- staging mail-safety MU control is loaded;
- expected staging site root is the known staging root, not production;
- deployment state marker exists and refers to the source currently intended for staging;
- inspected Raspitajse Communications source/runtime parity is exact for the deployed application code;
- repository/source state used by the runner is clean enough to make the executed code unambiguous;
- production path/domain/database is not selected.

The runner must not perform `git fetch`, network update checks, or repository mutation as part of its steady-state cycle.

Do not hard-code production paths or make production a supported mode.

---

## 6. Exact callback/event contract gate

Immediately before any execution, prove all three allowlisted registrations are still the approved contracts.

For every allowlisted hook require:

- callback count exactly `1`;
- exact expected owned callback identity;
- expected priority/accepted-args contract;
- scheduled event count exactly `1`;
- recurrence exactly `hourly`;
- interval exactly `3600`;
- event args exactly empty;
- no duplicate event row.

Also require before every execution cycle:

- candidate→job continuation event count `0`;
- vendor candidate→job sender registration `0`;
- employer→candidate vendor sender registration `0`;
- legacy daily expiry callback/event `0/0`;
- legacy shared expiry callback/event `0/0`;
- candidate automatic expiry remains disabled.

If any identity/count/recurrence/args invariant differs, execute **nothing** and exit nonzero with a sanitized reason code.

Do not rely only on hook names; callback identity matters.

---

## 7. Due-event semantics

The runner must inspect the scheduled timestamp for each allowlisted hook.

- Execute an allowlisted hook only when its single scheduled event is due or overdue relative to the current WordPress/server clock.
- Future events are a clean no-op and must not be forced early.
- Use the normal exact WP-CLI event command for the one fixed hook; do not call `/wp-cron.php`, `wp cron event run --due-now`, `--all`, or manually reproduce recurrence state.
- After an executed recurring event, verify it still exists exactly once, remains hourly with empty args, and its next timestamp moved forward normally.
- A no-op cycle must not mutate cron rows.

The runner must not repair, reschedule, unschedule or otherwise touch non-allowlisted cron rows.

---

## 8. Overlap lock and bounded runtime

Implement a host-local fail-closed overlap guard appropriate for one staging server.

Preferred behavior:

- nonblocking lock acquisition;
- a second concurrent runner exits without executing any hook;
- kernel-backed `flock` is preferred if available because crash cleanup is automatic;
- if the required locking primitive is unavailable, fail closed rather than running unlocked;
- no broad process killing;
- no PID guessing.

Bound runtime:

- per-hook child execution must have a hard timeout (historical validated value: 45 seconds is acceptable);
- the full cycle must have a bounded upper limit;
- timeout or unexpected child failure stops later hook execution and returns nonzero;
- do not continue after an unexpected partial failure.

Prove overlap behavior in a task-private test without invoking a second business hook execution.

---

## 9. Action Scheduler hard deny

The runner must never invoke:

- `action_scheduler_run_queue`;
- Action Scheduler CLI runner/queue runner;
- any due-action command;
- protected action ID32733;
- arbitrary Action Scheduler hook input.

Before and after acceptance prove:

- pending Action Scheduler count unchanged from current protected state;
- sanitized pending fingerprint unchanged;
- ID32733 remains `pending/0`;
- AS execution/claim/cancel/reschedule/cleanup counters `0`.

If the protected AS state differs unexpectedly during preflight, fail closed before owned hook execution.

---

## 10. Continuation / broad cron / arbitrary execution hard deny

Categorically reject and never invoke:

- `/wp-cron.php`;
- `wp cron event run --due-now`;
- `wp cron event run --all`;
- arbitrary `wp cron event run <user-input>`;
- candidate-job continuation hook/event;
- legacy daily expiry hook;
- legacy shared expiry hook;
- vendor/core scheduled hooks.

The runner must not expose a generic passthrough for extra WP-CLI arguments.

Static audit and tests must prove there is no string-concatenation/input path that can turn this tool into a generic cron runner.

---

## 11. Mail boundary

The staging runner must support the fact that two approved owned evaluators may legitimately create application mail when real staging business work is due in the future.

Therefore:

- do not globally redesign or disable the owned Transport path;
- require staging mail-safety to be loaded before any hook execution;
- preserve SenderPolicy/Transport ownership (`CHANNEL_JOB_EXPIRY` and candidate-alert channel as already implemented);
- production SMTP must remain unreachable because this tool is staging-only;
- no caller-supplied From/Reply-To override;
- no real SMTP delivery in acceptance.

For 2.06 acceptance, do not create real recipient-facing business work. `wp_mail`, PHPMailer and SMTP should remain `0` unless the task uses an explicitly disposable, fully intercepted fixture authorized by this specification. Prefer zero mail.

---

## 12. HTTP/network guard

The three owned evaluators do not require external application HTTP to perform their approved business logic.

The runner must fail closed against unexpected actual external application network transport during the selective cycle.

It is acceptable to reuse the proven 2.05 staging guard approach that blocks/intercepts WordPress HTTP before transport. Known LiteSpeed CLI-shutdown HTTP attempts may be observed only if deterministically intercepted before network transport.

Acceptance must report separately:

- WP HTTP API attempts;
- guard-intercepted attempts;
- actual external network requests.

Required actual external network requests: `0`.

Do not disable repository/control-plane Git traffic outside the application runtime guard when publishing the report.

---

## 13. Payment/order/refund hard deny

The runner must not permit the approved cron hooks to reach payment/order/refund mutation paths.

Use task/runtime guards sufficient to fail closed on unexpected WooCommerce payment/order/refund lifecycle callbacks during acceptance.

Before/final prove the protected WooCommerce order/refund fingerprint unchanged.

Required acceptance:

- payment transport/charge attempts `0`;
- order lifecycle mutations `0`;
- refund lifecycle mutations `0`.

Do not refactor WooCommerce standard lifecycle.

---

## 14. Operational output

Produce concise sanitized output suitable for a future server scheduler log.

At minimum include machine-readable or deterministic fields for:

- overall result: `PASS`, `NOOP`, `LOCKED`, or fail-closed error;
- environment;
- start/end or duration;
- for each fixed hook: `not_due`, `executed`, `skipped_after_failure`, or fail-closed status;
- count of executed hooks;
- no PII, emails, job titles, post IDs, order IDs, alert IDs, URLs with secrets, raw cron args, mail bodies, tokens or credentials.

Errors must be sanitized reason codes/messages, not raw dumps of WordPress data.

No debug logging containing business data.

---

## 15. Acceptance strategy

### A. Static/source validation

At minimum:

- `bash -n` on changed shell script(s);
- `php -l` on changed PHP helper(s), if any;
- `git diff --check`;
- executable bit correct for the operational shell runner;
- no secrets/PII/hard-coded user IDs/emails/production URLs;
- no vendor/core/theme changes;
- no generic hook passthrough;
- no broad cron command in executable path.

Use `shellcheck` only if already available; do not install packages.

### B. Pure/task-private fail-closed tests

Without touching production or business records, prove at least:

1. unexpected positional argument is rejected before WordPress cron execution;
2. unsupported mode/option is rejected;
3. overlap lock blocks a second concurrent invocation without hook execution;
4. synthetic/mocked callback mismatch is rejected;
5. synthetic/mocked event-count/recurrence/args mismatch is rejected;
6. arbitrary/non-allowlisted hook cannot be injected;
7. Action Scheduler and continuation cannot be selected;
8. timeout/error stops later hooks.

These may use task-private mocks/harnesses. Do not add a production backdoor solely to make tests possible.

### C. Staging runtime smoke

Immediately before any real runner invocation, take the same sanitized protected-state/due-work gate used in 2.05.

If any real business work is due for any of the three owned evaluators, do **not** execute that real work in 2.06 merely to test the wrapper. Use check-only/no-op acceptance plus task-private tests and report why. Do not create fake real recipients.

If real business due work is `0` and one or more allowlisted cron event rows are naturally due/overdue, one ordinary runner cycle may execute those due owned events through the exact normal path. Each fixed hook still max once. Future events must remain untouched.

Do not force a future cron event early just to obtain an execution proof; 2.05 already proved the exact event execution path.

After any runtime cycle prove exact cleanup/invariants and no business mutation.

---

## 16. Protected business/runtime invariants

Before/final prove sanitized unchanged business state for at least:

- candidates count/status/fingerprint;
- candidate expiry-meta footprint;
- historical published `candidate_alert` count/fingerprint;
- published `job_alert` count/fingerprint;
- job listing count/status/fingerprint;
- employers and employer users count/fingerprint;
- job package/entitlement count/status/fingerprint;
- WooCommerce orders status/fingerprint;
- refunds count/fingerprint;
- candidate-alert creation remains fail-closed;
- employer→candidate vendor sender `0`;
- candidate→job vendor sender `0`;
- legacy daily callback/event `0/0`;
- legacy shared expiry callback/event `0/0`;
- three owned callback/event pairs remain `1/1 hourly`;
- candidate-job continuation event `0`;
- candidate auto-expiry disabled;
- all owned claim families return to pre-task state;
- Action Scheduler pending count/fingerprint unchanged;
- ID32733 remains `pending/0`.

If a naturally due owned cron event is executed, the only expected persistent cron mutation is that exact event's normal recurring timestamp advancement. Non-allowlisted cron fingerprint must remain unchanged.

---

## 17. Deployment/integration boundary

This is an operational-tool implementation task.

- Work from one scoped feature branch based on exact baseline.
- Keep application runtime changes at zero unless a blocker is explicitly reported.
- A `changed` staging deployment may be used after acceptance to advance the staging deploy marker to the final commit even if the new `tools/` file itself is executed from the repository rather than copied into `wp-content` by the deploy allowlist.
- Prove the deployed application subtree remains byte-identical where no application file changed.
- Fast-forward `staging` only after acceptance passes.
- Final `origin/staging`, local staging and deploy marker must refer to the same final task commit.

Do not activate or edit Hostinger/hPanel cron, user crontab, systemd timers, or any external scheduler in 2.06.

---

## 18. Safety counters

Expected task-wide unless a specifically authorized no-business runtime cycle advances due owned cron timestamps:

- production access/touch: `NO`;
- broad WP-Cron runs: `0`;
- non-allowlisted cron executions: `0`;
- Action Scheduler executions/mutations: `0`;
- candidate-job continuation executions: `0`;
- real business mutations: `0`;
- `wp_mail`: preferably `0`;
- PHPMailer/SMTP: `0`;
- actual external application network: `0`;
- payment/order/refund mutations: `0`;
- arbitrary command/hook execution: `0`.

Any unexpected side effect is a stop condition.

---

## 19. HOST_NAMESPACE_PRESSURE

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` applies.

Use the proven namespace-free Git/filesystem/WP-CLI path when needed. If a sandbox-dependent helper returns the known signature, apply the circuit breaker immediately and do not loop retries or use broad process kills.

The operational runner itself must not depend on Codex sandbox tooling.

---

## 20. PASS criteria

PASS only if all are true:

- a Raspitajse-owned staging selective runner exists in source;
- fixed allowlist is exactly the three approved hooks;
- no arbitrary hook/CLI passthrough exists;
- only due/overdue allowlisted events can execute;
- fixed sequential order and max-once-per-cycle behavior are proven;
- overlap lock and bounded timeout behavior are proven;
- staging/environment/runtime/callback/event guards fail closed;
- broad cron, Action Scheduler, continuation, legacy/vendor/core cron are unreachable from the runner;
- staging mail-safety is mandatory and no real SMTP occurs;
- actual external application network remains `0` in acceptance;
- payment/order/refund mutations remain `0`;
- protected business/AS state unchanged;
- any natural owned-event timestamp advancement is exact and limited to executed allowlisted rows;
- no Hostinger/server scheduler is activated;
- source quality checks pass;
- final staging/source/deploy-marker integration is unambiguous;
- production remains untouched.

PARTIAL/FAIL rather than weakening a guard if the host lacks a required safe primitive or a runtime invariant cannot be proven.

---

## 21. Final report

Publish:

**Zadatak 2.06 — Implement a fail-closed staging external selective allowlist runner for approved Raspitajse cron hooks**

Report must include:

1. PASS/PARTIAL/FAIL and exact meaning;
2. baseline, feature/final commit, `origin/staging`, deploy marker;
3. exact changed files/diffstat;
4. runner command/interface and fixed allowlist;
5. environment/runtime parity guards;
6. callback/event contract validation;
7. due-only semantics;
8. overlap/timeout behavior;
9. fail-closed test matrix;
10. any staging runtime smoke execution, exact hook counts and cron timestamp changes;
11. non-allowlisted cron fingerprint before/final;
12. Action Scheduler pending fingerprint and ID32733 before/final;
13. mail/PHPMailer/SMTP counters;
14. HTTP API/interception/actual-network counters;
15. payment/order/refund counters;
16. protected business fingerprints before/final;
17. scheduler activation changes = `0`;
18. production = `NO ACCESS / NO TOUCH`;
19. warnings/tooling notes;
20. exactly one proposed next small task and STOP.

The likely next slice, if PASS, is a separately authorized staging scheduler activation/observation task using this runner. Do not activate it in 2.06.
