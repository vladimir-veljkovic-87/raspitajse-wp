# Codex Execution Report

- Task: Staging WP-Cron read-only diagnosis
- Result: PARTIAL
- Recorded at (UTC): 2026-08-22T00:57:36Z
- Source branch: staging
- Source HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Source working tree clean: YES
- Staging deploy marker: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- Staging environment: staging

## Task report

## Summary

- Completed a read-only diagnosis of the staging WP-Cron and Action Scheduler systems.
- Classification: **A — almost all cron events are stale**. In fact, 44 of 44 scheduled events are overdue.
- Evidence points to a systemic absence/failure of cron triggering rather than an individual plugin scheduling defect.
- Result is PARTIAL only because the account has no `crontab` command and standard system cron files were unavailable, so external cron configuration could not be confirmed.

## Baseline and safety

- Source branch: `staging`
- Source HEAD: `872d498727c77061b12d461f53d1ac35f8190c14`
- Working tree: clean
- Staging deploy marker: `27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f`
- Deployment performed: NO
- WordPress environment: `staging`
- Communications plugin: active
- Communications transport option: `1`
- Communications transport enabled: true
- Staging mail-safety: loaded
- Cron event executed: NO
- Action Scheduler action executed: NO
- Database/files/wp-config modified: NO
- Production touched: NO

## Clock and configuration evidence

- Server local time: `2026-08-22 00:52:36 UTC`
- Server UTC time: `2026-08-22 00:52:36 UTC`
- WordPress local/GMT time: `2026-08-22 00:52:38`
- WordPress timezone: `+00:00`; `gmt_offset=0`
- WordPress version: `6.6.5`
- PHP version: `8.2.30`
- Home/site hostname: `stage.raspitajse.com`
- `DISABLE_WP_CRON`: not defined
- `ALTERNATE_WP_CRON`: not defined
- `WP_CRON_LOCK_TIMEOUT`: `60` seconds
- Incorrect timezone interpretation is ruled out.

## WP-Cron inventory

| Metric | Value |
|---|---:|
| Timestamp buckets | 36 |
| Total scheduled events | 44 |
| Overdue events | 44 (100%) |
| Overdue timestamp buckets | 36 |
| Earliest timestamp | 2026-05-14 19:40:14 UTC |
| Latest timestamp | 2026-06-05 18:31:49 UTC |
| Oldest overdue age | about 3 months / 8,572,371 seconds |
| Oldest hook | `action_scheduler_run_queue` |
| Cron array structure | valid |

Each overdue hook has exactly one scheduled event:

`action_scheduler_run_queue`, `delete_expired_transients`, `elementor/tracker/send_event`, `jetpack_clean_nonces`, `jetpack_v2_heartbeat`, `litespeed_task_lqip`, `monsterinsights_cache_daily_cleanup`, `monsterinsights_charitable_notice_cron`, `monsterinsights_email_summaries_cron`, `monsterinsights_feature_feedback_checkin`, `monsterinsights_feature_feedback_clear_expired`, `monsterinsights_product_feed_monthly_check`, `monsterinsights_usage_tracking_cron`, `puc_cron_check_updates-hostinger-ai-assistant`, `puc_cron_check_updates-hostinger-easy-onboarding`, `puc_cron_check_updates-wp-job-board-pro`, `puc_cron_check_updates-wp-job-board-pro-wc-paid-listings`, `recovery_mode_clean_expired_keys`, `run_weekly_partner_astra`, `wc_admin_daily`, `wc_admin_process_orders_milestone`, `wc_admin_unsnooze_admin_notes`, `woocommerce_cancel_unpaid_orders`, `woocommerce_cleanup_logs`, `woocommerce_cleanup_personal_data`, `woocommerce_cleanup_rate_limits`, `woocommerce_cleanup_sessions`, `woocommerce_geoip_updater`, `woocommerce_marketplace_cron_fetch_promotions`, `woocommerce_scheduled_sales`, `woocommerce_tracker_send_event`, `wp_delete_temp_updater_backups`, `wp_job_board_pro_delete_old_previews`, `wp_job_board_pro_email_daily_notices`, `wp_privacy_delete_old_export_files`, `wp_scheduled_auto_draft_delete`, `wp_scheduled_delete`, `wp_site_health_scheduled_check`, `wp_update_plugins`, `wp_update_themes`, `wp_update_user_counts`, `wp_version_check`, `wpforms_email_summaries_cron`, `wpforms_weekly_entries_count_cron`.

### Priority hooks

| Hook | Next run GMT | Recurrence | Runtime callback |
|---|---|---|---|
| `action_scheduler_run_queue` | 2026-05-14 19:40:14 | Every minute | Registered |
| `woocommerce_cancel_unpaid_orders` | 2026-05-14 19:48:19 | One-shot | Registered |
| `wp_job_board_pro_email_daily_notices` | 2026-05-15 14:07:06 | Daily | Registered |
| `wpforms_email_summaries_cron` | 2026-05-18 00:00:00 | One-shot | Registered |
| `monsterinsights_email_summaries_cron` | 2026-06-05 18:31:49 | One-shot/orphaned | Not currently registered |

## Cron lock

- Raw `_transient_doing_cron` option: absent
- Timeout option: absent
- Active cron lock: NO
- Stale lock: NO

## External/system cron

- `crontab -l`: inaccessible because the `crontab` executable is not installed/available.
- Standard `/etc/crontab` and `/etc/cron.d` inspection returned no accessible entries or WordPress/Action Scheduler matches.
- Therefore a real server cron could not be confirmed and may be missing.

## Action Scheduler

| Metric | Value |
|---|---:|
| Runtime present | Yes |
| Actions table present | Yes |
| Pending actions | 15 |
| Overdue pending actions | 15 |
| Oldest pending action | 2026-05-14 19:47:38 GMT |
| Oldest pending age | about 3 months |
| New scheduler log rows during diagnosis | 0 |

The queue also appears stalled. Its oldest pending action is seven minutes after the oldest WP-Cron event, and the minute-level queue runner is itself the oldest overdue WP-Cron hook.

## Scheduling ownership

| Hook | Owner/source | Expected scheduling | Plugin state | Code/callback state | Trigger dependency |
|---|---|---|---|---|---|
| `wp_job_board_pro_email_daily_notices` | WP Job Board Pro `maybe_schedule_cron_jobs()` | Recurring daily | Active | Scheduling code and callbacks present | Requires WP-Cron spawning/runner |
| `wpforms_email_summaries_cron` | WPForms `Emails\\Summaries` | One-shot; next event scheduled by later plugin initialization after prior event is consumed | Active | Code and callback present | Requires WP-Cron plus subsequent request traffic |
| `monsterinsights_email_summaries_cron` | MonsterInsights email summaries | Monthly when enabled | Active | Code exists, but current callback and recurrence are absent; old one-shot event appears orphaned | Would require WP-Cron when enabled |
| `action_scheduler_run_queue` | WooCommerce Action Scheduler `QueueRunner` | Every minute plus eligible async admin dispatch | Active through WooCommerce | Runner code/callback present | Depends on WP-Cron or async request; WP-CLI inspection had `is_admin=no` |
| `woocommerce_cancel_unpaid_orders` | WooCommerce `wc_cancel_unpaid_orders()` | One-shot; callback reschedules itself before processing | Active | Code/callback present | Requires the overdue callback to run |

## Log evidence

### Current/recent

- No August 2026 staging log matches for cron spawn failures, loopback failures, cURL cron errors, lock errors, resource exhaustion, or fatal PHP errors.
- Absence of log evidence does not prove successful loopback spawning; WordPress may fail silently or the staging site may receive no qualifying HTTP traffic.

### Historical

- The staging debug file contains May 14 warnings from candidate-alert processing and an unrelated Bulk Delete fatal error.
- Historical entries use the non-staging path and appear to have been copied into the staging log. They cannot reliably be attributed to the current staging runtime.
- A January `wp-cron.php` fatal trace is also present, but it references the non-staging path and a then-missing callback. It is historical context only, not evidence of the current staging failure.
- No trustworthy staging-specific log evidence directly identifies the spawn failure mechanism.

## Likely causes ranked by confidence

### HIGH confidence

1. **Systemic cron runner absence/failure.** All 44 core and plugin events are overdue, across unrelated owners and recurrence types. This rules out a selected-plugin-only problem.
2. **Action Scheduler queue processing is stalled as a consequence or parallel symptom.** All 15 pending actions are overdue and its WP-Cron runner is the oldest stale event.

### MEDIUM confidence

3. **Staging inactivity/no qualifying web traffic.** Default WP-Cron is enabled but traffic-driven spawning may simply not occur on an inactive staging hostname.
4. **No real server cron.** No external cron could be confirmed; the account lacks the `crontab` utility and standard cron files were unavailable.
5. **Loopback/spawn failure.** Plausible because normal HTTP traffic should spawn due cron, but prohibited HTTP/cron tests and lack of reliable logs prevent confirmation.

### LOW confidence

6. **PHP/resource failure.** No current staging-specific fatal/resource evidence; historical failures are copied/non-staging and unrelated.
7. **Plugin scheduling bug.** MonsterInsights has one orphaned event, but unrelated WordPress core, WooCommerce, Job Board, WPForms, and maintenance hooks are all equally stale.
8. **`DISABLE_WP_CRON`.** Explicitly not defined.
9. **Stale cron lock.** No lock exists.
10. **Incorrect timezone.** Server and WordPress clocks agree in UTC.
11. **Cron array corruption.** Structure is valid and its integrity hash remained unchanged.

## Recommended next steps — not performed

1. Check Hostinger control-panel scheduled tasks for the staging hostname and confirm whether a staging-scoped real cron exists.
2. Review staging web/access logs and Hostinger process-limit logs around May 14–15 to distinguish inactivity from loopback/resource failure.
3. Before enabling any runner, review all 44 overdue hooks and all 15 pending Action Scheduler actions for mail, order, application, alert, and other business side effects.
4. Plan a maintenance-window recovery that preserves staging mail safety and processes low-risk hooks incrementally rather than running all due events at once.
5. With explicit approval and safeguards, perform a loopback diagnostic that cannot execute the backlog, or temporarily quarantine high-risk hooks before testing spawn behavior.
6. After root cause is confirmed, configure a staging-scoped server cron or repair loopback spawning, then reschedule stale recurring events deliberately.
7. Inspect and retire the orphaned MonsterInsights event if its feature remains disabled.
8. Validate Action Scheduler backlog hook-by-hook before any queue run.

## Final integrity

- Initial cron SHA-256: `ed3d49ab6d00db1db21466738f9a315db08e86c7761ca864fe853377240a08a0`
- Final cron SHA-256: `ed3d49ab6d00db1db21466738f9a315db08e86c7761ca864fe853377240a08a0`
- Cron array unchanged: PASS
- Action Scheduler pending/overdue counts unchanged: 15/15
- Action Scheduler log rows created during diagnosis: 0
- Repository clean at expected branch/HEAD: PASS
- Communications soak state preserved: PASS
- No cron/queue hook executed: PASS
- Production touched: NO
