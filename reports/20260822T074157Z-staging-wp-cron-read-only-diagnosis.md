# Codex Execution Report

- Task: Staging WP-Cron read-only diagnosis
- Result: PARTIAL
- Recorded at (UTC): 2026-08-22T07:41:57Z
- Source branch: staging
- Source HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Source working tree clean: YES
- Staging deploy marker: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- Staging environment: staging

## Task report

## Summary

- Completed a second read-only diagnosis of staging WP-Cron and Action Scheduler.
- Classification: **A — almost all events are stale**; specifically, 44 of 44 WP-Cron events are overdue.
- The evidence indicates a systemic cron trigger/runner failure or absence, not a selected-plugin scheduling fault.
- PARTIAL is used only because external cron configuration could not be inspected: `crontab` is unavailable and standard system cron files returned no accessible data.

## Baseline

- Branch: `staging`
- HEAD: `872d498727c77061b12d461f53d1ac35f8190c14`
- Working tree: clean
- Deployment marker: `27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f`
- Deployment performed: NO
- Environment: staging
- Communications plugin: active
- Transport option: `1`
- Communications transport: enabled
- Staging mail-safety: loaded

## Clock and safe configuration

- Server local/UTC time: `2026-08-22 07:39:16 UTC`
- WordPress local/GMT time: `2026-08-22 07:39:19`
- WordPress timezone: `+00:00`; `gmt_offset=0`
- Site/home hostname: `stage.raspitajse.com`
- WordPress: `6.6.5`; PHP: `8.2.30`
- `DISABLE_WP_CRON`: not defined
- `ALTERNATE_WP_CRON`: not defined
- `WP_CRON_LOCK_TIMEOUT`: 60 seconds

## Event summary

| Metric | Value |
|---|---:|
| Timestamp buckets | 36 |
| Total events | 44 |
| Overdue events | 44 (100%) |
| Overdue timestamp buckets | 36 |
| Earliest timestamp | 2026-05-14 19:40:14 UTC |
| Latest timestamp | 2026-06-05 18:31:49 UTC |
| Oldest overdue age | about 3 months / 8,596,783 seconds |
| Oldest overdue hook | `action_scheduler_run_queue` |
| Cron array structure | Valid |

Each overdue hook has a count of exactly 1:

`action_scheduler_run_queue`, `delete_expired_transients`, `elementor/tracker/send_event`, `jetpack_clean_nonces`, `jetpack_v2_heartbeat`, `litespeed_task_lqip`, `monsterinsights_cache_daily_cleanup`, `monsterinsights_charitable_notice_cron`, `monsterinsights_email_summaries_cron`, `monsterinsights_feature_feedback_checkin`, `monsterinsights_feature_feedback_clear_expired`, `monsterinsights_product_feed_monthly_check`, `monsterinsights_usage_tracking_cron`, `puc_cron_check_updates-hostinger-ai-assistant`, `puc_cron_check_updates-hostinger-easy-onboarding`, `puc_cron_check_updates-wp-job-board-pro`, `puc_cron_check_updates-wp-job-board-pro-wc-paid-listings`, `recovery_mode_clean_expired_keys`, `run_weekly_partner_astra`, `wc_admin_daily`, `wc_admin_process_orders_milestone`, `wc_admin_unsnooze_admin_notes`, `woocommerce_cancel_unpaid_orders`, `woocommerce_cleanup_logs`, `woocommerce_cleanup_personal_data`, `woocommerce_cleanup_rate_limits`, `woocommerce_cleanup_sessions`, `woocommerce_geoip_updater`, `woocommerce_marketplace_cron_fetch_promotions`, `woocommerce_scheduled_sales`, `woocommerce_tracker_send_event`, `wp_delete_temp_updater_backups`, `wp_job_board_pro_delete_old_previews`, `wp_job_board_pro_email_daily_notices`, `wp_privacy_delete_old_export_files`, `wp_scheduled_auto_draft_delete`, `wp_scheduled_delete`, `wp_site_health_scheduled_check`, `wp_update_plugins`, `wp_update_themes`, `wp_update_user_counts`, `wp_version_check`, `wpforms_email_summaries_cron`, `wpforms_weekly_entries_count_cron`.

### Specified hooks

| Hook | Due GMT | Recurrence | Runtime callback |
|---|---|---|---|
| `action_scheduler_run_queue` | 2026-05-14 19:40:14 | Every minute | Yes |
| `woocommerce_cancel_unpaid_orders` | 2026-05-14 19:48:19 | One-shot | Yes |
| `wp_job_board_pro_email_daily_notices` | 2026-05-15 14:07:06 | Daily | Yes |
| `wpforms_email_summaries_cron` | 2026-05-18 00:00:00 | One-shot | Yes |
| `monsterinsights_email_summaries_cron` | 2026-06-05 18:31:49 | One-shot/orphaned | No current callback |

## Raw cron array and lock

- Structure: valid
- Timestamp buckets: 36
- All 36 timestamps are earlier than current UTC time
- Oldest timestamp hook: `action_scheduler_run_queue`
- `doing_cron` raw transient option: absent
- Cron lock timeout option: absent
- Stale lock: ruled out

## External cron

- `crontab -l` failed because the `crontab` executable is unavailable.
- `/etc/crontab` and `/etc/cron.d` yielded no accessible listing or WordPress/Action Scheduler matches.
- A real Hostinger scheduled task could not be confirmed.

## Logs

### Current/recent evidence

- August 2026 cron/loopback/spawn/lock/resource symptom matches: 0
- August 2026 fatal/parse/error entries: 0
- This does not establish successful loopback spawning; an inactive staging site or silent spawn failure remains possible.

### Historical evidence

- The staging debug file contains 20 May 2026 PHP problem entries, but all 20 reference the non-staging path and appear copied from another runtime.
- One historical `wp-cron.php` trace exists, also referencing the non-staging path.
- These historical records are not reliable evidence for the current staging runtime.

## Scheduling ownership

| Hook | Owner | Expected recurrence | Owner active | Registration code | Dependency |
|---|---|---|---|---|---|
| `wp_job_board_pro_email_daily_notices` | WP Job Board Pro | Daily | Yes | Present | WP-Cron traffic/spawn |
| `wpforms_email_summaries_cron` | WPForms | One-shot with later request-driven scheduling | Yes | Present | WP-Cron and subsequent requests |
| `monsterinsights_email_summaries_cron` | MonsterInsights | Monthly when enabled | Yes | Present; current callback absent | WP-Cron when enabled |
| `action_scheduler_run_queue` | WooCommerce Action Scheduler | Every minute, with eligible async admin dispatch | Yes | Present | WP-Cron or async request |
| `woocommerce_cancel_unpaid_orders` | WooCommerce | One-shot; callback reschedules itself | Yes | Present | WP-Cron callback execution |

MonsterInsights has an orphaned event under current settings, but that isolated condition cannot explain the system-wide stall.

## Action Scheduler

| Metric | Value |
|---|---:|
| Runtime present | Yes |
| Actions table present | Yes |
| Pending | 15 |
| Overdue pending | 15 |
| Oldest pending | 2026-05-14 19:47:38 GMT |
| Oldest pending age | About 3 months |
| Execution log rows created during this diagnosis | 0 |

Queue processing is also stalled. Its oldest pending action is approximately seven minutes newer than the oldest overdue WP-Cron queue-runner event.

## Diagnosis ranked by confidence

### HIGH

1. **Systemic cron runner absence/failure** — every unrelated core and plugin event is overdue.
2. **Action Scheduler backlog/stall** — all 15 pending actions are overdue and its minute runner is the oldest stale WP-Cron event.

### MEDIUM

3. **Staging inactivity/no qualifying HTTP traffic** — default traffic-driven WP-Cron is enabled, but staging may receive little or no traffic.
4. **Missing real server cron** — none could be confirmed due unavailable system tooling.
5. **Loopback/spawn failure** — plausible, but direct testing was correctly prohibited and logs provide no current proof.

### LOW

6. **PHP/resource failure** — no current staging evidence.
7. **Plugin scheduling bug** — one orphaned plugin event exists, but cannot explain all 44 stale hooks.
8. **`DISABLE_WP_CRON`** — not defined.
9. **Stale cron lock** — no lock exists.
10. **Timezone interpretation** — all clocks agree in UTC.
11. **Cron array corruption** — structure and integrity hash are valid.

## Recommended next steps — not performed

1. Inspect Hostinger control-panel scheduled tasks for a staging-scoped real cron.
2. Review staging HTTP access logs and Hostinger process/resource logs around May 14–15.
3. Inventory all overdue hooks and all 15 pending actions for mail, order, alert, application, or other business effects before enabling any runner.
4. Plan incremental maintenance-window recovery; do not run all due events or the complete Action Scheduler queue blindly.
5. With explicit approval and safeguards, diagnose loopback spawning without allowing the backlog to execute.
6. Configure a staging-only server cron or repair spawning only after risky hooks are quarantined/reviewed.
7. Review and retire the orphaned MonsterInsights event if the feature remains disabled.

## Final integrity and safety

- Initial cron hash: `ed3d49ab6d00db1db21466738f9a315db08e86c7761ca864fe853377240a08a0`
- Final cron hash: `ed3d49ab6d00db1db21466738f9a315db08e86c7761ca864fe853377240a08a0`
- Cron array unchanged: PASS
- Action Scheduler counts unchanged: 15 pending / 15 overdue
- Cron events executed: 0
- Action Scheduler actions executed: 0
- Files/database/wp-config modified: NO
- Deployment: NO
- Communications soak state preserved: PASS
- Repository clean at expected branch/HEAD: PASS
- Production touched: NO
