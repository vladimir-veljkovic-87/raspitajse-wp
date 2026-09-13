# Zadatak 2.33 — Correct the authoritative static acceptance process exit contract before resuming 2.25 live deployment

Status: READY
Baseline: ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4
Previous task: 2.32
Target environment: staging
Production: FORBIDDEN
Finalization dependency: Zadatak 2.25

## Result required

End the new finalization loop by identifying and correcting the authoritative 2.25 static acceptance process that emitted a complete passing JSON payload but returned exit code 255.

This task must prove both sides of the command contract:

1. the unmodified positive suite reports all 27 checks passing and returns process exit code 0;
2. a safe, non-mutating deliberate negative self-test is detected and returns a nonzero process exit code.

PASS classification: `STATIC_ACCEPTANCE_EXIT_CONTRACT_CORRECTED`.

Do not deploy or finalize Zadatak 2.25 in this task.

## Mandatory preamble and expected recovery state

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, `origin/staging`, and the referenced recovery branch. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read the PASS 2.31 report and BLOCKED 2.32 report.

The following state is expected and accounted for:

- `origin/staging`: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live deploy marker: `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- live files byte-match the accepted recovery commit `e9d58c62713fbed895a0d174b3fbeb33ee48c957`;
- local recovery branch: `feature/z2-25-live-recovery`;
- business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- owned-contract fingerprint: `655c645e8e12f8c31e5ec95a499d7759b1214f1698b47921c8253e5284ad6c93`;
- non-allowlisted cron fingerprint: `145d4732c3f1a9765281791a8c26fcf41a96cd7df521e133e7963537432e4e70`;
- pending Action Scheduler count/fingerprint: 6 / `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- action 32733: complete, attempts 1;
- both scheduler guards and admin capability remain active.

STOP on an unaccounted difference. Do not repair or deploy the split state in this task.

Authoritative evidence root:

`/tmp/raspitajse-task-2.25-final-atomic.qafdp0/atomic-evidence`

Relevant 2.32 files include `failure-state.txt`, `static-acceptance.json`, `static-acceptance.stderr`, the invoked static harness/process source and the authoritative finalizer.

## Known failure

The post-deploy static acceptance invocation returned process exit code 255 while:

- its complete JSON payload contained 27 checks;
- reported failures were zero;
- reported network, mail and payment calls were zero;
- stderr was empty.

The live feature deployment was then correctly rolled back. Git staging remains integrated.

Do not reinterpret exit 255 as success merely because the JSON passed. Find and correct the actual process-exit cause.

## Work to complete

### 1. Reconstruct the exact process boundary

Without changing anything, document:

- the exact command and executable used;
- PHP and WP-CLI versions;
- shell options, pipeline/command-substitution context and all relevant `PIPESTATUS` values;
- the authoritative static harness path and SHA-256;
- the caller/finalizer path and SHA-256;
- every explicit `exit`, `die`, `WP_CLI::halt`, exception catch, shutdown callback and shell trap that can affect the final status;
- whether the 255 originates in PHP, WP-CLI, a shutdown callback, the shell wrapper, or status coercion;
- whether the JSON is emitted before a later failure.

Capture the exact exit status directly, not through a helper that swallows or rewrites it.

### 2. Correct the authoritative source

Locate the authoritative existing static harness or its generator/caller. Apply the smallest correction there.

Do not:

- change application, theme, plugin, WooCommerce or WordPress core behavior;
- remove the mandatory exit-zero gate;
- convert every exit to zero;
- accept 255 based only on JSON;
- suppress PHP diagnostics;
- create a second parallel harness.

If the source is intentionally materialized in the existing finalization task directory, update that exact authoritative artifact and record path, diff and SHA-256. If the defect is in a tracked test/tool source, make only a scoped test/tool commit derived from integrated staging; do not deploy it in this task.

### 3. Focused positive proof

Under both staging safety locks, run only the corrected authoritative static acceptance command in a fresh process.

It must:

- emit exactly one valid complete JSON result;
- execute all expected 27 checks;
- report zero failures;
- report zero network/mail/payment calls;
- produce empty unexpected stderr;
- return process exit code exactly 0;
- preserve every recovery fingerprint and side-effect counter.

### 4. Negative exit-contract proof

Use a documented, safe, non-mutating test-only mechanism to force exactly one synthetic assertion failure without modifying application or persistent state.

The negative proof must:

- report the forced failure in structured output;
- return a nonzero exit code;
- not execute network, mail, payment, cron or Action Scheduler callbacks;
- leave every protected fingerprint byte-identical;
- be removed/disabled after the proof so the authoritative positive suite remains unchanged.

Do not simulate the negative result by wrapping the command with `false` or manually choosing a shell exit code. The harness assertion/result path itself must cause the nonzero exit.

### 5. Bounded execution

At most three focused invocations are permitted: one attribution reproduction if essential, one positive proof and one negative proof. Do not start the full 2.25 deploy/finalization.

## Mandatory acceptance

PASS requires:

- exact root cause of exit 255;
- minimal authoritative correction;
- positive 27/27 payload with process exit 0;
- synthetic negative payload with process exit nonzero;
- no unexpected stderr or PHP diagnostics;
- business, owned-contract, cron and pending-AS fingerprints unchanged;
- claims 0 and action 32733 complete/attempts 1;
- all mail/PHPMailer/SMTP/HTTP/payment/refund/broad-runner/protected-action counters 0;
- corrected authoritative path, diff and SHA-256 recorded for exact reuse;
- Git staging and live recovery state otherwise unchanged.

## STOP conditions

Return `BLOCKED` if the 255 source cannot be proven or correcting it would require application/vendor changes.

Return `INTEGRITY_BLOCKER` on any persistent-state drift.

Do not respond by launching another Zadatak 2.25 deployment attempt.

## Git, reporting and safety

- `codex-tasks` is read-only to the executor.
- Production is forbidden.
- Do not update `origin/staging`.
- Do not deploy or change the live marker.
- Do not modify Action Scheduler rows.
- Do not run broad WP-Cron or Action Scheduler queues.
- Publish exactly one final 2.33 report through `codex-reports` and STOP.
