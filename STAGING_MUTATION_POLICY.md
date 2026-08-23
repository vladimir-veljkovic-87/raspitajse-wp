# Raspitajse Staging Mutation Policy

Purpose: let Codex move faster on staging without weakening protection of business data or production.

Core principle:

> Protect BUSINESS STATE strictly. Allow TECHNICAL STAGING HOUSEKEEPING when it is explicitly classified, bounded and measurable. Production remains forbidden for autonomous Codex work.

This policy supplements `AGENTS.md` and `CODEX_WORKFLOW.md`. If there is a conflict, the stricter safety rule wins.

## 1. State classes

Every staging mutation must be classified before execution.

### A. Protected business state

Examples:

- candidate/employer/user accounts and roles;
- applications and application statuses;
- private messages/chat state;
- jobs and business-facing job state when not an explicit fixture;
- orders, payments, refunds, subscriptions and entitlement state;
- passwords/authentication state;
- personal data, CV/profile content and user-generated content;
- real-recipient email state or uncontrolled outbound communication.

Rules:

- do not mutate real/non-fixture business state autonomously merely because the environment is staging;
- require explicit approval when the task intentionally changes protected business state unless an already-approved disposable-fixture contract makes the exact mutation autonomous-safe;
- never use recipient rewriting as proof that a business-state flow is safe;
- destructive personal-data operations remain approval-required even on staging unless they target an explicitly disposable fixture with proven scope.

### B. Disposable staging fixture state

Disposable fixtures may be created, changed and deleted autonomously when all are true:

- staging environment is proven immediately before mutation;
- the fixture has an unmistakable test identifier/prefix or other collision-resistant marker;
- creation and expected lifecycle are part of the task contract;
- queries/IDs used for mutation are exact enough that non-fixture data cannot match;
- no real payment is initiated;
- no uncontrolled/real recipient can receive email;
- cleanup is scoped only to the created fixture family;
- before/after validation proves non-fixture business fingerprints/counts did not move unexpectedly.

Examples can include test candidates, employers, jobs, applications, WPForms forms and safe WooCommerce test-order states when the exact test strategy prevents real charges or uncontrolled external effects.

### C. Technical staging housekeeping

Technical state may be mutated autonomously when the exact behavior is classified and bounded.

Examples that can qualify:

- cache/transient maintenance;
- plugin-owned housekeeping metadata;
- expected scheduler lifecycle/audit records;
- old Action Scheduler log cleanup when the source contract and deletion criteria are understood;
- temporary task/guard files;
- bounded cache/index helper files;
- plugin-owned technical status markers with no business meaning;
- expected recurring successor creation for an individually classified scheduler action.

Technical housekeeping is `AUTONOMOUS_SAFE` only when:

1. the exact owning subsystem is known;
2. the write/delete criteria are understood;
3. scope is finite and measurable;
4. it cannot select protected business data by accident;
5. business-state fingerprints/critical invariants are protected where relevant;
6. the task has a clear stop condition if scope differs from the classification.

A technical mutation is not safe merely because the staging site currently has few/no users.

### D. Unknown or broad state

If a mutation cannot be confidently classified, or its selector/callback can broaden beyond the expected set, classify it `UNKNOWN`/`APPROVAL_REQUIRED` and stop before mutation.

Broad queue runners, broad cron execution, unknown cleanup callbacks and ambiguous SQL remain outside autonomous-safe housekeeping until specifically classified.

## 2. Scheduler-specific rules

For WP-Cron and Action Scheduler:

- prefer exact hook/ID execution over broad runners while recovery mode is active;
- do not use `wp cron event run --due-now` during the protected backlog-recovery phase;
- do not use a broad Action Scheduler queue runner merely to reach one target action;
- expected status/audit/successor writes for an individually classified action may be allowed in its execution contract;
- expected technical history cleanup may be allowed when its exact source-level behavior is known and it does not change pending/business state;
- deletion of historical technical logs is not automatically a `FAIL` if it was pre-classified as allowed housekeeping;
- pending-action drift, execution of an unapproved action, or mutation of protected business state remains a hard stop.

Do not retroactively re-label a historical report. Apply this policy prospectively from the commit that introduces it.

## 3. Snapshot and rollback proportionality

Use rollback effort proportional to state value and recoverability.

### Protected business state

Before mutation, capture enough exact state to prove/perform rollback when practical. A count/hash alone is insufficient when the task could delete or overwrite meaningful records.

### Disposable fixture state

Track exact fixture IDs/markers and expected cleanup. Do not snapshot unrelated staging data merely to delete a fixture created by the same bounded task.

### Technical housekeeping

Do not require expensive raw-row backups for disposable audit/cache/history data unless the task specifically needs reversibility of those rows.

Counts, IDs, hashes and source-contract evidence are normally sufficient when loss of the technical history has no product/business consequence and the deletion itself is allowed by the task contract.

If a technical artifact is needed for debugging/audit of the current recovery work, preserve it explicitly before approving cleanup.

## 4. Result semantics

Use report results according to impact, not mere database movement.

### PASS

Use `PASS` when:

- the intended task completed;
- protected business/safety invariants held;
- any additional mutation was already classified as allowed technical housekeeping or fixture lifecycle behavior;
- validation is unambiguous.

Expected bounded housekeeping does not downgrade a task from PASS simply because rows/files/options changed.

A WordPress HTTP API call that is deterministically intercepted before transport does not by itself downgrade a task from PASS. Evaluate external effects using the transport semantics in section 6A.

### PARTIAL

Use `PARTIAL` when:

- the intended task did not fully complete;
- safety/business invariants remained protected;
- rollback/restoration is complete where required, or remaining technical-state change is understood and allowed;
- continuing requires a corrected contract/new task rather than a safety incident response.

### FAIL

Use `FAIL` when:

- protected business data changed outside the approved contract;
- a production boundary was crossed;
- an unapproved scheduler/business action executed;
- an unknown/unbounded mutation occurred and integrity can no longer be confidently established;
- a required rollback of valuable/protected state failed;
- a safety guard was bypassed/weakened or a critical validation invariant was violated;
- an actual external network request occurred outside the exact approved transport contract.

Do not use `FAIL` solely because harmless technical staging housekeeping occurred if that housekeeping is already classified and bounded by this policy/task contract.

## 5. KEEP / CHANGE / DROP rule for legacy behavior

Before investing in parity work, classify historical behavior:

- `KEEP`: preserve it and test parity before removing the old implementation;
- `CHANGE`: replace it intentionally against a new acceptance criterion;
- `DROP`: remove/ignore it when safe; do not spend tasks reproducing it.

Direct vendor customization is not automatically `KEEP`. It must map to a current business requirement.

The long-term target is update-safe vendor code with Raspitajse behavior in owned plugins/hooks/filters/template overrides where practical.

## 6. External effects

Technical classification does not automatically authorize external effects.

- real/uncontrolled email remains prohibited on staging;
- external HTTP calls require endpoint/disclosure classification when material;
- payments/charges are never treated as disposable technical housekeeping;
- auth/password changes are protected business state;
- production identity/data/endpoints must not be used accidentally from staging.

## 6A. WordPress HTTP API vs actual network transport

Do not use the phrase `HTTP attempt` as a synonym for external network activity. For guarded WordPress HTTP tasks, distinguish these layers:

1. **WP HTTP API attempt** — code entered `wp_remote_get()`, `wp_remote_post()`, `wp_remote_request()` or an equivalent WordPress HTTP API path. This proves application intent only; it does not prove that the server contacted a remote host.
2. **Guard-intercepted request** — a task guard observed and deterministically short-circuited/blocked the request before WordPress selected or invoked a network transport. This is not an external network request.
3. **Approved transport attempt** — the task contract explicitly allows network transport to an exact endpoint/host with an exact disclosure contract.
4. **Actual external network request** — TCP/TLS/HTTP transport was allowed to leave the server toward a remote destination, whether it succeeded or failed at the application layer.
5. **Unexpected external network request** — actual transport occurred outside the exact endpoint, redirect, method, credential/disclosure or request-count contract.

For tasks involving the WordPress HTTP API, reports and guards should expose these counters when technically applicable:

```text
WP HTTP API attempts
Guard-intercepted before transport
Approved transport attempts
Actual external network requests
Unexpected external network requests
```

Rules:

- `WP HTTP API attempts > 0` is evidence, not a failure condition by itself.
- A request proven to be guard-intercepted before transport counts as **zero actual external network requests**.
- For an offline/no-network test, the invariant is `Actual external network requests = 0`; WP HTTP API attempts may be nonzero only when every attempt is deterministically intercepted before transport.
- Do not write a test contract that merely requires `HTTP attempts = 0` when the real safety objective is no external transport.
- For an explicitly approved external integration test, approved transport may occur only to the exact classified endpoint/host, with the exact allowed method, redirects and disclosures. Any cross-host or otherwise unapproved transport is a hard failure.
- Credential/token values may be present in process memory as part of an intercepted request, but must never be printed, logged, copied to reports or written to task artifacts.
- If a task cannot establish whether transport occurred, do not claim `network requests = 0`. Stop as `PARTIAL`/`UNKNOWN` when safety invariants are otherwise preserved; if a protected credential, personal data or production identity may actually have been disclosed outside the approved contract, treat it as a real safety failure.
- Local/plugin hook observation is not external disclosure. The safety boundary for this classification is whether network transport left the staging server, while existing secret-handling rules still apply inside the process.

This distinction is prospective. Historical reports keep their original result even if they used the older ambiguous `HTTP attempts` wording.

## 7. Production rule

This policy applies to staging only.

It does not authorize:

- production deployment;
- production DB reads/writes for autonomous tasks;
- copying staging DB wholesale to production;
- production user/business mutation;
- weakening production safeguards.

Production release is a separate explicitly approved workstream and should follow the code-first/data-only-when-proven-necessary strategy in `STAGING_DEFINITION_OF_DONE.md`.

## 8. Task contract requirement

Before any mutating staging task, the internal contract should state:

- state class: protected business / disposable fixture / technical housekeeping / unknown;
- exact allowed mutations;
- exact forbidden mutations;
- external effects allowed/forbidden;
- for HTTP-capable tasks, whether actual network transport is forbidden or explicitly approved and the exact endpoint/disclosure contract;
- preconditions;
- before/after evidence;
- stop condition;
- rollback expectation proportional to the state class.

If the actual runtime mutation or external transport exceeds the classified contract, stop and create a new diagnosis/classification task rather than silently broadening scope.
