# Codex Execution Report

- Task: WP-Cron lock behavior diagnosis
- Result: PARTIAL
- Recorded at (UTC): 2026-08-22T09:15:49Z
- Source branch: staging
- Source HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Source working tree clean: YES
- Staging deploy marker: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- Staging environment: staging

## Task report

## Summary

- Completed read-only passive sampling and the single authorized WP-CLI bootstrap control.
- No cron event or Action Scheduler action was executed.
- The database rows _transient_doing_cron and _transient_timeout_doing_cron were absent at every sample.
- The stored cron option fingerprint and all 15 pending Action Scheduler records remained unchanged.
- A LiteSpeed external object-cache drop-in is present. Therefore DB-only option-row sampling cannot prove whether a doing_cron transient existed only in object cache.
- Result is PARTIAL because lock visibility is restricted by that external-cache path and command overhead shifted some nominal sample times.

## Git baseline

- git fetch origin --prune: completed
- Branch: staging
- HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Reset exactly to origin/staging: completed
- Repository: clean

## Sampling method

- Staging WordPress path: /home/u601262303/domains/raspitajse.com/public_html/public_html_stage
- Table prefix was obtained using the non-bootstrap database command and was not derived from or exposed with credentials.
- Passive samples used wp db query only; they did not fully bootstrap WordPress or plugins.
- Process samples used the current user's process table and emitted only PID, elapsed age, and a sanitized process type.
- No command requested wp-cron.php.
- No lock row or cache entry was deleted or modified deliberately.

## Passive doing_cron samples

The passive window was restarted after an interrupted read-only wait. The table below uses the restarted T0. Nominal timing drift is reported explicitly.

| Requested sample | Actual UTC | Actual elapsed | Active DB lock | Lock token | Lock age | Changed since prior | Timeout row |
|---|---|---:|---|---|---|---|---|
| T0 | 2026-08-22 09:07:17.038855 | 0 s | NO | ABSENT | N/A | Initial | ABSENT |
| T+30 | 2026-08-22 09:08:04.802069 | 47.8 s | NO | ABSENT | N/A | NO | ABSENT |
| T+70 | 2026-08-22 09:08:50.159154 | 93.1 s | NO | ABSENT | N/A | NO | ABSENT |
| T+130 | 2026-08-22 09:09:50.944291 | 153.9 s | NO | ABSENT | N/A | NO | ABSENT |

A preliminary sample before the restarted window, at 2026-08-22 08:58:42.960039, also had no lock or timeout value. Its initial SQL status label mishandled an empty result, but the returned token and timeout fields were both NULL; corrected SQL was used for the restarted window.

## Process observations

| Sample | Observation |
|---|---|
| T0 | No PHP, WP-CLI, wp-cron.php, staging cron HTTP client, or Action Scheduler process observed |
| Actual T+47.8 | No relevant process observed |
| Actual T+93.1 | PID 962354, generic PHP, age 21 seconds |
| Actual T+153.9 | PID 962354, generic PHP, age 88 seconds |
| Pre-control | PID 962354, generic PHP, age 128 seconds |
| Post-control late sample | PID 962354, generic PHP, age 206 seconds |
| Final identity check | PID 962354, process type lsphp, age 305 seconds; no command line emitted |

No sampled process was classified as WP-CLI, wp-cron.php, Action Scheduler, or a curl/wget staging cron request. Snapshot sampling cannot exclude a process that started and ended between samples.

## DB-level cron and Action Scheduler state

| State | T0 | Final |
|---|---|---|
| Cron option row count | 1 | 1 |
| Cron serialized byte length | 7735 | 7735 |
| Cron SHA-256 | f92e52b11ce875cb9984af0c5e2f436f502c0aabd462ea4ae3d991977c2405fc | f92e52b11ce875cb9984af0c5e2f436f502c0aabd462ea4ae3d991977c2405fc |
| Action Scheduler pending count | 15 | 15 |
| Pending-row sanitized SHA-256 | a34b974069fcf901c99167c47d01b9685e71b063499a7eeae90764027f87e18b | a34b974069fcf901c99167c47d01b9685e71b063499a7eeae90764027f87e18b |

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

No external process changed the stored cron option or the pending-action set during observation.

## Staging logs

Only the staging-specific file wp-content/debug.log was inspected.

- Sampling start byte offset: 4142149
- Final byte offset: 4142149
- Delta: 0 bytes
- Cron or wp-cron.php markers: 0
- Loopback markers: 0
- PHP fatal/error markers: 0
- Timeout markers: 0
- Resource-limit markers: 0
- Action Scheduler markers: 0
- wp_mail, PHPMailer, mail-safety, or communications markers: 0

No production or shared production traffic log was inspected.

## WP-CLI bootstrap control

### Preconditions

- No WordPress CLI, wp-cron.php, Action Scheduler, or staging cron HTTP-client process was observed.
- Pre-control DB sample at 2026-08-22 09:10:42.942229:
  - Lock row: ABSENT
  - Timeout row: ABSENT

### Authorized command

- Ran exactly one fully bootstrapped read-only command:
  wp --path=/home/u601262303/domains/raspitajse.com/public_html/public_html_stage eval 'echo wp_get_environment_type();'
- Output: staging

### Post-control observations

| Requested sample | Actual UTC | DB lock row | Timeout row | Cron fingerprint | Pending actions |
|---|---|---|---|---|---|
| Approximately +2 | 2026-08-22 09:11:16.355995 | ABSENT | ABSENT | unchanged | 15, unchanged |
| Nominal +10, completed late and also covers +30 | 2026-08-22 09:11:53.083283 | ABSENT | ABSENT | unchanged | 15, unchanged |
| Final DB sample | 2026-08-22 09:13:28.955232 | ABSENT | ABSENT | unchanged | 15, unchanged |

The second post-control sample completed approximately 39 seconds after the first because of command/approval overhead. No database lock row, cron change, queue change, process evidence, or log evidence correlated with the bootstrap.

### Visibility limitation

- Staging has a LiteSpeed Cache object-cache drop-in.
- WordPress transients can be held outside wp_options when an external object cache is active.
- The task intentionally prohibited another fully bootstrapped introspection command, direct cache mutation, and lock deletion.
- Therefore this control rules out a database-resident lock and cron execution, but cannot conclusively rule out a short cache-only lock.

## Diagnosis ranking

| Hypothesis | Confidence | Evidence |
|---|---|---|
| A. Persistent/stuck cron process | LOW | No DB lock row during more than 150 seconds of passive observation, no wp-cron.php or scheduler process observed, and no cron/queue fingerprint change. External-cache visibility prevents absolute exclusion, but persistence is unsupported. |
| B. Repeated spawn attempts that fail | MEDIUM | The prior Phase 1A observation saw a lock at roughly 59 seconds of age while cron and queue state stayed unchanged, which is consistent with an unsuccessful spawn lock. No repeated lock was observed in this passive window. |
| C. Short normal locks that expire | HIGH | The earlier lock was transient rather than persistent: it existed near 59 seconds of age, later disappeared without intervention, and left cron and queue state unchanged. The standard short lock/expiry pattern best fits the available evidence, though its trigger is unresolved. |
| D. WP-CLI bootstrap itself triggers spawn attempts | MEDIUM | The controlled bootstrap produced no DB lock, state change, process evidence, or log delta. However, the earlier lock appeared at nearly the same time as a fully bootstrapped WP-CLI baseline, and a cache-only lock would be invisible to DB sampling. Evidence is mixed. |
| E. Another unknown external trigger | MEDIUM | A staging web request, LiteSpeed/PHP worker, hosting mechanism, or other external trigger could explain the earlier short lock. No staging-isolated request log was available, and the generic lsphp process could not be attributed to cron. |

## Refined conclusion

- The Phase 1A lock does not look persistent or stuck.
- It most likely represented a short-lived lock that expired normally without executing the overdue cron backlog.
- The source of that lock remains unresolved between a failed spawn attempt associated with a bootstrap/request and another external trigger.
- The current DB-only evidence does not support claiming that WP-CLI definitely creates the lock.
- Because the LiteSpeed external object-cache drop-in can hide transient state from wp_options, a future proof-quality experiment would require an approved read-only cache-aware observation mechanism that does not itself bootstrap WordPress or alter the lock.

## Final integrity

- Cron event explicitly executed: NO
- Action Scheduler action executed: NO
- wp-cron.php requested by this task: NO
- Email sent: NO
- Cron backlog intentionally modified: NO
- Stored cron fingerprint unchanged: PASS
- Action Scheduler pending count and IDs unchanged: PASS
- Lock deleted or changed deliberately: NO
- Database write issued: NO
- Application file modified: NO
- Deployment performed: NO
- Repository clean: PASS
- Branch and HEAD unchanged: PASS
- Communications plugin active in DB state: YES
- Communications takeover option: 1
- WordPress environment from the one authorized control: staging
- Production touched: NO

## Warnings / limitations

- PARTIAL due to external object-cache visibility and shifted sample timings.
- The initial T+30 wait was interrupted by user steering; the passive window was restarted from a new T0.
- No credentials, private values, serialized cron content, command lines, or production logs were emitted.
