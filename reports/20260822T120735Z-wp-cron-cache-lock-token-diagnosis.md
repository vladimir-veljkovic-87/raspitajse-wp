# Codex Execution Report

- Task: WP-Cron cache lock token diagnosis
- Result: PASS
- Recorded at (UTC): 2026-08-22T12:07:35Z
- Source branch: staging
- Source HEAD: ffb4239cd82c078787ed527f7f2adcdd78a908f6
- Source working tree clean: YES
- Staging deploy marker: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- Staging environment: staging

## Task report

## Summary

- The cache-aware doing_cron transient is a STALE CONSTANT LOCK.
- The exact same numeric token remained present across all four guarded samples.
- Its age increased normally from approximately 10,114 to 10,228 seconds, far beyond the 60-second classification threshold.
- get_transient and wp_cache_get returned identical values at every sample.
- No evidence of token refresh, normal expiry, or cyclic spawning was observed.

## Runtime and repository

- Branch: staging
- HEAD: ffb4239cd82c078787ed527f7f2adcdd78a908f6
- Repository: clean
- Every WordPress bootstrap used a process-local DISABLE_WP_CRON=true guard.
- DISABLE_WP_CRON was not persisted.
- No deployment occurred.

## Cache-aware samples

Token:

1787390148.9507479667663574218750

| Requested sample | Current microtime | Actual elapsed from T0 | State | Token age seconds | Previous-token comparison | get_transient vs wp_cache_get |
|---|---:|---:|---|---:|---|---|
| T0 | 1787400262.648383 | 0.000 | ACTIVE | 10113.697635 | Initial | MATCH |
| T+30 | 1787400305.489176 | 42.841 | ACTIVE | 10156.538428 | SAME | MATCH |
| T+65 | 1787400346.044264 | 83.396 | ACTIVE | 10197.093516 | SAME | MATCH |
| T+100 | 1787400376.901414 | 114.253 | ACTIVE | 10227.950666 | SAME | MATCH |

Command and approval overhead shifted the samples later than the nominal offsets. The full observation window exceeded 100 seconds and preserved the required ordering.

At every sample:

- wp_cache_get group: transient
- Cache key found: YES
- Cache state: ACTIVE
- Cache token exactly matched get_transient

## Classification

| Classification | Result | Evidence |
|---|---|---|
| STALE CONSTANT LOCK | YES | Same token persisted; age was already over 10,000 seconds and continued increasing |
| REPEATED REFRESH | NO | Token never changed to a newer value |
| NORMAL EXPIRY | NO | Lock never became clear |
| CYCLIC SPAWN | NO | No clear-then-new-token sequence occurred |

## Before and after integrity

| State | Before | After | Result |
|---|---|---|---|
| Cron option row count | 1 | 1 | UNCHANGED |
| Cron option byte length | 7735 | 7735 | UNCHANGED |
| Cron option SHA-256 | f92e52b11ce875cb9984af0c5e2f436f502c0aabd462ea4ae3d991977c2405fc | f92e52b11ce875cb9984af0c5e2f436f502c0aabd462ea4ae3d991977c2405fc | UNCHANGED |
| Action Scheduler pending count | 15 | 15 | UNCHANGED |
| Pending-row sanitized SHA-256 | a34b974069fcf901c99167c47d01b9685e71b063499a7eeae90764027f87e18b | a34b974069fcf901c99167c47d01b9685e71b063499a7eeae90764027f87e18b | UNCHANGED |
| Action Scheduler log count | 576 | 576 | UNCHANGED |
| Action Scheduler maximum log ID | 97093 | 97093 | UNCHANGED |

Pending IDs remained exactly:

- 32199
- 32521
- 32522
- 32523
- 32571
- 32600
- 32672
- 32676
- 32677
- 32678
- 32679
- 32680
- 32681
- 32713
- 32714

## Staging log delta

- Start offset: 4142149
- End offset: 4142149
- Delta bytes: 0
- PHP fatal/error markers: 0
- Cron or wp-cron.php markers: 0
- Email, PHPMailer, mail-safety, or communications markers: 0
- Action Scheduler markers: 0

## Safety

- Cron event executed: NO
- Action Scheduler executed: NO
- wp-cron.php requested: NO
- doing_cron deleted or modified: NO
- Cache flushed: NO
- Database write command issued: NO
- Email sent: NO
- Deployment performed: NO
- Application file modified: NO
- wp-config.php modified: NO
- Production touched: NO

## Future clearance determination

SAFE TO CLEAR ONLY doing_cron TRANSIENT IN A FUTURE TASK: YES

This determination applies only to the isolated doing_cron transient. A future mutating task should re-read the token immediately before clearing it, confirm it is still the same stale token or equivalently stale, confirm no wp-cron process is active, clear only doing_cron, and then verify that cron and Action Scheduler remain unchanged before any separately approved recovery event.
