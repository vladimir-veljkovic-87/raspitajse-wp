# Zadatak 2.31 — Definitively diagnose and correct the WooCommerce admin order renderer smoke harness before any further 2.25 finalization attempt

Status: READY
Baseline: 29cadafbe9d88d256807a682c3926f8d8540bff2
Previous task: 2.30
Target environment: staging
Production: FORBIDDEN

## Result required

End the repeated Zadatak 2.25 finalization loop by establishing one authoritative, reusable WooCommerce admin order renderer smoke setup that passes independently on the current recovery runtime before it is used by another atomic finalization attempt.

This task is not successful for attribution alone. It must identify the exact PHP Error behind failure hash `e2576da614a922965cac0706e97db5f55691254e4f66c0bafe380d0064cb0d4d`, correct the authoritative existing smoke harness or its generator, and prove the focused renderer contract end to end.

PASS classification: `FOCUSED_ADMIN_RENDERER_HARNESS_CORRECTED`.

Do not begin, integrate, deploy, or report completion of Zadatak 2.25 in this task.

## Mandatory preamble and accounted split state

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`. Read `tasks/current.md` and `tasks/README.md` from `origin/codex-tasks` in full. Read the final reports for 2.27, 2.28, 2.29 and 2.30.

The following state is expected and explicitly accounted for:

- `origin/staging`: `29cadafbe9d88d256807a682c3926f8d8540bff2`;
- prepared combined 2.25 candidate: `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- live staging deploy marker: `73f43334ecc14adcf5704fcead243a67bca4c71f`;
- current protected business fingerprint: `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- pending Action Scheduler count: 6;
- pending fingerprint: `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims: 0;
- action 32733: complete, attempts 1;
- async-dispatch and targeted DROP guards: active;
- staging administrator user 141 retains `manage_options`.

STOP on any unaccounted deviation. Do not “repair” the split state in this task.

## Known prior failure

The first renderer smoke failed before renderer entry because calling `WC_Order::set_status('completed')` caused `maybe_set_date_paid()` to evaluate `woocommerce_payment_complete_order_status`, which the payment guard correctly blocked.

The subsequent corrected smoke failed with PHP `Error`, hash:

`e2576da614a922965cac0706e97db5f55691254e4f66c0bafe380d0064cb0d4d`

The second error has not yet been attributed. Do not guess, allowlist, suppress, or weaken a guard based on the hash.

Existing evidence root:

`/tmp/raspitajse-task-2.25-final-atomic.qafdp0/atomic-evidence`

## Work to complete in this task

### 1. Attribute the exact Error before changing the harness

Read the existing evidence and extract a sanitized record containing:

- Error class and complete message;
- originating file and line;
- sanitized stack trace;
- the last completed harness phase;
- whether `WC_Meta_Box_Order_Data::output()` was entered;
- actual renderer method signature from Reflection;
- runtime values/types for the order object, current user, current screen, request context and required WooCommerce globals without PII or secrets.

Determine the root cause as one of:

- incorrect WordPress/WooCommerce admin lifecycle setup;
- invalid in-memory order state;
- wrong renderer invocation contract;
- missing admin dependency/bootstrap;
- product regression.

### 2. Locate the authoritative harness source

Identify the existing authoritative source or generator that produces the admin renderer portion of the 2.25 finalization smoke. Correct that source. Do not fix only a disposable copied command while leaving the next generated harness broken.

Do not create a parallel test system. If the authoritative harness is intentionally materialized under the existing task directory, update that exact harness and record its path and SHA-256. If it has a tracked source/generator, modify only the minimum necessary test/tool file on a scoped branch based on the prepared candidate; do not modify application, theme, WooCommerce, WordPress core, or other vendor code.

### 3. Build a semantically valid focused renderer fixture

The focused probe must:

- use an unsaved `WC_Order` with ID 0;
- never call `save()`, checkout, payment completion, stock mutation, email dispatch or refund logic;
- avoid entering `woocommerce_payment_complete_order_status` through fixture construction;
- use staging administrator user 141 and prove the required capabilities;
- establish the canonical WooCommerce HPOS admin order screen lifecycle and a non-null correct current screen;
- provide internally consistent `HTTP_HOST`, `SERVER_NAME`, `REQUEST_URI`, `pagenow`, `GET` and `REQUEST` state;
- call the real `WC_Meta_Box_Order_Data::output()` using its reflected current signature;
- restore prior user, screen, globals and request state after the assertion;
- retain all existing mail, PHPMailer, SMTP, HTTP, payment, broad-runner and protected-action guards.

Do not weaken or bypass the payment guard. Construct the fixture so no payment path is invoked.

### 4. Iterate only inside this focused task

This task may perform up to three bounded focused diagnostic/correction probes if needed. It must not start the full 2.25 atomic finalization between probes.

Each probe must run under the staging safety locks, must not mutate persistent business/Action Scheduler state, and must preserve the exact before/after fingerprints. Stop immediately if any probe causes an unplanned persistent mutation or if the root cause would require product/vendor changes.

Do not publish a separate numbered report after each focused probe. Publish one final 2.31 report.

## Mandatory acceptance

PASS requires all of the following in a fresh process:

- `WC_Meta_Box_Order_Data::output()` is demonstrably entered exactly once;
- renderer invocation completes without Throwable;
- meaningful admin order markup is produced;
- the intended Raspitajse owned billing/render hook is invoked exactly once;
- the order remains ID 0 and no order/customer/item row is created;
- no option, transient, user, candidate, package, order, refund or scheduler row is modified;
- business fingerprint remains `f3f7ce8a46e82e9164a85132f72eed50a1ca9daff026f939062197f0cf1537cc`;
- pending Action Scheduler remains 6 with fingerprint `3c7068c59c2a1e43d6d6ecec1bf1d73abc835a24cfaf1c8b72f8bfd9e937c6e9`;
- claims remain 0 and action 32733 remains complete/attempts 1;
- mail, PHPMailer, SMTP, HTTP transport, payment/refund, broad scheduler runner and protected-action execution counters are all 0;
- stderr and structured PHP notices/warnings/errors/fatals are empty, except only an already-approved exact Elementor deprecation signature if it occurs;
- the corrected authoritative harness/generator path, diff and SHA-256 are recorded so the next 2.25 attempt can reuse it without reconstruction.

A mere HTTP status, a caught exception, skipped renderer, mocked renderer, or markup produced without the real renderer is not PASS.

## STOP conditions

Return `PRODUCT_REGRESSION` and STOP if the real renderer fails after canonical context is established and the cause lies in application/vendor behavior rather than the harness.

Return `BLOCKED` with one exact blocker if safe focused reproduction is impossible. Do not respond by launching another 2.25 finalization attempt.

## Git, reporting and safety

- `codex-tasks` is read-only to the executor.
- Production is forbidden.
- Do not integrate or update `staging`.
- Do not change the live deploy marker.
- Do not deploy application/theme/vendor code.
- Do not run broad WP-Cron or Action Scheduler queues.
- Do not execute mail, SMTP, HTTP transport, payment or refund operations.
- Keep reports free of PII, credentials and protected values.
- Publish exactly one final 2.31 report through `codex-reports` and STOP.
