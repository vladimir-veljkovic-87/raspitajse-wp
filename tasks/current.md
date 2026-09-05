# Zadatak 2.10 — Reconcile provider history for owned-cron timestamp advances before steady-state scheduler closure

Status: READY
Baseline: 43ac33e6e96fdc0062233e6419cfb761633113c5
Previous task: 2.09
Target environment: staging
Production: FORBIDDEN

## Mandatory preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Treat `codex-tasks` as read-only.

Fresh `origin/staging` must equal the declared baseline exactly. Otherwise STOP and report the mismatch. Execute only 2.10 and do not begin 2.11.

## Context

The staging-only selective runner and guard are already integrated and accepted. The fixed runner order remains:

1. `raspitajse_job_listing_expiry_evaluator`
2. `raspitajse_employer_job_expiry_notice_evaluator`
3. `raspitajse_candidate_job_alert_evaluator`

The human-operated Hostinger scheduler is intended to remain exactly one staging entry at `*/15 * * * *`, invoking the zero-argument selective runner. Phase A check-only passed. A later natural Phase B fire also reported PASS, with only employer-expiry executed and all transport/payment counters zero.

2.09 stayed PARTIAL only because final reconciliation compared against an old 2.07 timestamp baseline. All three owned timestamps had advanced since then, so the two additional advances could not be attributed to the single supplied provider output.

The last accepted protected state from 2.09 showed:

- callback/event contract fingerprint unchanged: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint unchanged: `875972ba451addb368e78d86b4f3df94e7627037911ac617e620c2cdba6c1e71`;
- Action Scheduler pending `7`, fingerprint `1e06ffd60f1323db49bf194e8a3c8cdd7ea055c09a9554cda01c33aff1f32143`;
- ID32733 `pending/0`;
- real due business work `0/0/0`;
- owned claims `0/0/0`;
- protected business aggregate `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc` unchanged;
- production untouched.

Historical owned timestamps at the last 2.09 reconciliation were job `1788611986`, employer `1788613152`, candidate `1788611463`. Re-read live values; do not use these historical values as the new attribution baseline.

## Goal

Close the attribution gap by using a fresh forward observation window:

`fresh guarded T0 snapshot -> first natural Hostinger scheduler fire -> fresh guarded T1 snapshot`.

PASS requires proving that each owned timestamp changes if and only if that same first provider fire reports the hook as `executed`.

This is evidence/reconciliation only. Do not redesign or mutate the scheduler.

## Scheduler boundary

Do not create, delete, disable, edit, duplicate, or reschedule the existing Hostinger entry. Do not switch back to check-only. Do not manually invoke the runner or any cron hook. Do not use broad WP-Cron or Action Scheduler as a substitute.

If the human operator reports that the staging scheduler changed before the observation window, STOP with `PARTIAL — BLOCKED_SCHEDULER_STATE_CHANGED`.

## Stage A — establish T0

Fully guarded and read-only verify before T0:

- source/origin/deploy parity exact at the baseline;
- worktree clean; runtime is staging;
- runner and guard are tracked/source-clean and syntax/lint pass;
- Communications source/runtime parity exact;
- `DISABLE_WP_CRON=true`, `doing_cron` clear, staging mail safety loaded;
- callback/event contract fingerprint unchanged;
- non-allowlisted cron fingerprint unchanged;
- each owned hook has exactly one valid callback and one hourly/3600/empty-args event;
- continuation rows `0`;
- legacy daily callback/event `0/0`;
- legacy shared-expiry callback/event `0/0`;
- both retired vendor sender callbacks `0`;
- candidate auto-expiry disabled;
- Action Scheduler pending/fingerprint unchanged and ID32733 `pending/0`;
- protected business aggregate unchanged;
- real due work for job expiry / employer notice / candidate alerts = `0/0/0`;
- owned claims = `0/0/0`.

All WordPress/WP-CLI evidence processes must load the proven pre-bootstrap guard.

Capture a sanitized T0 containing:

- current timestamp and event fingerprint for each owned hook;
- full and non-allowlisted cron fingerprints;
- Action Scheduler pending/fingerprint and ID32733 state;
- protected business aggregate;
- due-work and claim counts;
- snapshot UTC time.

Do not mutate state.

### Mandatory first STOP

After T0, publish `PARTIAL — READY_FOR_HUMAN_OBSERVATION` and STOP.

The report must instruct the human operator to make no scheduler changes, wait for the very next natural Hostinger fire only, capture the complete `View Output` JSON, and return it promptly. Do not manually trigger anything.

## Stage B — resume with first-fire provider output

On resume, fetch fresh refs and re-read this READY task and README.

The controlling user message must supply the complete `View Output` JSON for the first natural scheduler fire after T0. If the output is missing, incomplete, ambiguous, or more than one natural scheduler interval may have elapsed without complete intermediate evidence, do not guess. Establish a new guarded T0 and return `PARTIAL — READY_FOR_HUMAN_OBSERVATION` again.

Provider output must identify result/environment/mode/reason/executed_hooks, all three per-hook statuses, HTTP attempt/intercept/unexpected/external counters, and mail/PHPMailer/SMTP/payment counters.

Required provider safety: staging normal-run mode; no fail-closed/error; unexpected HTTP `0`; actual external network `0`; wp_mail/PHPMailer/SMTP/payment `0/0/0/0`.

## Forward attribution rule

Immediately after receiving valid first-fire evidence, take one fully guarded T1 snapshot.

For each hook independently:

- provider status `not_due` => T1 timestamp and event fingerprint must equal T0 exactly;
- provider status `executed` => timestamp must show exactly one normal recurrence advancement, with callback identity/recurrence/args unchanged;
- no hook may advance more than once;
- `executed_hooks` must equal the number of hooks that advanced exactly once;
- no owned event may disappear, duplicate, or change recurrence/args.

Do not use the old 2.07 timestamps for attribution. Only T0 -> first natural provider fire -> T1 is authoritative.

If attribution fails, report `PARTIAL — BLOCKED_FORWARD_TIMESTAMP_ATTRIBUTION` and STOP without scheduler mutation.

## Final PASS gates

PASS also requires:

- source/origin/deploy/runtime parity unchanged;
- callback/event contract unchanged;
- non-allowlisted cron fingerprint unchanged;
- any full-cron change explained only by exactly attributed owned timestamp movement;
- Action Scheduler pending/fingerprint unchanged, ID32733 `pending/0`, AS executions/mutations `0`;
- continuation/vendor/legacy executions `0`;
- due work final `0/0/0`, owned claims `0/0/0`;
- protected business aggregate and component fingerprints unchanged;
- actual external network/mail/SMTP/payment/order/refund/business mutations `0`;
- human-confirmed final scheduler remains exactly one enabled `*/15 * * * *` selective-runner entry;
- production access/touch `NO`.

No fixture is authorized. Expected source changes: `0`. Do not create a no-op application commit.

If PASS, conclude exactly:

`STEADY_STATE_SELECTIVE_STAGING_SCHEDULER_ACCEPTED`

Then propose exactly one next task, do not create/start it, publish through `codex-reports`, and STOP.

Known `HOST_NAMESPACE_PRESSURE / bwrap ENOSPC` circuit-breaker rules remain in force: on first confirmed signature, do not retry the same sandbox helper; use the proven namespace-free path.
