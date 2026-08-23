# Codex Host Namespace Resilience

Purpose: prevent Hostinger/Codex bubblewrap namespace exhaustion (`ENOSPC`) from turning a small safe task into a long retry loop.

This policy is subordinate to `AGENTS.md`. It never authorizes production access, weaker guards, broader WordPress mutations, or bypassing an approval boundary.

## 1. Known failure signature

The recurring host-resource failure is the Codex patch/sandbox helper failing to create a Linux namespace and returning a message containing one of:

- `bwrap` + `No space left on device`;
- `bwrap` + `ENOSPC`;
- namespace creation failure with the same ENOSPC result.

Do not automatically interpret that signature as a full filesystem condition. Run the low-cost host diagnostic when needed:

```bash
bash tools/codex-host-diagnose.sh
```

For a single explicit namespace probe:

```bash
bash tools/codex-host-diagnose.sh --probe-namespace
```

The probe is diagnostic only. It must not be looped.

## 2. ENOSPC circuit breaker

When the known namespace ENOSPC signature occurs:

1. classify it as `HOST_NAMESPACE_PRESSURE`;
2. do not repeat the same `apply_patch`/bubblewrap helper more than once in that task;
3. do not wait for host capacity to clear inside the task;
4. switch immediately to the approved namespace-free fallback appropriate to the file type;
5. if the fallback cannot complete safely, publish `PARTIAL` and stop.

A repeated namespace failure is not a reason to keep a Codex task running for tens of minutes or hours.

Recommended preparation time budget: if host tooling prevents a bounded task from reaching its actual validation/execution phase within roughly 10 minutes, stop and report rather than repeatedly rebuilding the same temporary harness.

## 3. Repository-file fallback

For an intended repository edit on a clean `feature/*` branch, batch the intended changes into one normal unified Git patch and use:

```bash
cat /tmp/change.patch | bash tools/codex-git-apply.sh
```

To validate without applying:

```bash
cat /tmp/change.patch | bash tools/codex-git-apply.sh --check-only
```

`codex-git-apply.sh` intentionally does not call `apply_patch` or bubblewrap. It requires:

- a `feature/*` branch;
- a clean working tree before application;
- a valid patch that passes `git apply --check`;
- no absolute/traversal, `.git`, `wp-config.php`, or `public_html` patch target path;
- `git diff --check` after application.

After using it, still run all task-specific syntax/tests and inspect the complete diff before commit/push.

Do not use this helper to edit `staging`, `main`, baseline branches, production paths, or runtime files.

## 4. Temporary task-file fallback

Temporary guards/harnesses must live in a fresh Raspitajse-owned directory such as:

```text
/tmp/raspitajse-task-<task-id>.<random>/
```

If `apply_patch` hits namespace ENOSPC while correcting a temporary file, prefer one deterministic rewrite instead of repeated patch attempts.

For a complete corrected replacement of an existing temporary file:

```bash
cat corrected-guard.php | bash tools/codex-tmp-rewrite.sh \
  /tmp/raspitajse-task-<id>.<random>/guard.php \
  <expected-pre-edit-sha256>
```

The helper is restricted to `/tmp/raspitajse-*`, refuses symlinks, optionally checks the exact old SHA-256, writes atomically, and prints only path/hash metadata.

For a truly trivial one-line private `/tmp` correction, a direct deterministic `python3`, `perl`, or `sed` edit is also acceptable only when all are true:

- the target is a fresh task-private `/tmp` copy, never repository/runtime/production state;
- the expected old text/line is first verified exactly;
- the edit is inspected afterwards;
- the complete file receives its required syntax/static check before use;
- no safety condition or allowlist is broadened as part of the edit.

Do not reuse temporary guards from an interrupted task. Rebuild them in a new task-private directory.

## 5. Reduce namespace/process pressure proactively

For Hostinger work:

- batch related repository edits into one patch rather than many patch-helper calls;
- keep one task-private temporary directory per attempt;
- avoid nested shell/sandbox layers when a direct command is sufficient;
- avoid parallel jobs, watchers, dev servers and background processes;
- keep `TOKIO_WORKER_THREADS=1` where already required;
- do not repeatedly probe namespace capacity;
- clean up task-private temporary files when they are no longer needed, except when a rollback/evidence contract requires retaining them until the report is published.

## 6. Safety invariants remain unchanged

Namespace resilience changes the editing mechanism, not the authorization model.

ENOSPC never permits:

- production access or deployment;
- broad cron/Action Scheduler execution;
- bypassing mail/cron/request/database guards;
- skipping rollback preparation;
- exposing secrets;
- writing directly to `staging` instead of using a feature branch for code changes;
- executing an `APPROVAL_REQUIRED`, `UNKNOWN`, or `FORBIDDEN` action without the required decision.

If a namespace-free fallback would make the operation less bounded or less auditable, do not use it. Publish `PARTIAL` and stop.

## 7. Reporting

When namespace ENOSPC occurs, the numbered report should state:

- whether disk/inode diagnostics were normal or unavailable;
- whether the failure matched the known namespace signature;
- number of patch-helper attempts (maximum one after classification);
- whether a namespace-free fallback was used;
- exact files affected by that fallback;
- syntax/diff validation result;
- whether any WordPress/runtime mutation occurred;
- `Production touched: NO`.

Use `HOST_NAMESPACE_PRESSURE` as the concise stop/failure classification so future sessions can distinguish this infrastructure issue from an application bug.
