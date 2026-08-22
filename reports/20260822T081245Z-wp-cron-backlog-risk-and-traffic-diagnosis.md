# Codex Execution Report

- Task: WP-Cron backlog risk and traffic diagnosis
- Result: PARTIAL
- Recorded at (UTC): 2026-08-22T08:12:45Z
- Source branch: staging
- Source HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Source working tree clean: YES
- Staging deploy marker: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- Staging environment: staging

## Task report

## Summary

- Completed a read-only risk classification of all 44 overdue WP-Cron events and all 15 pending Action Scheduler actions.
- Produced planning-only recovery groups and a five-phase incremental recovery plan.
- Staging traffic history was SKIPPED because no access log reliably isolated to stage.raspitajse.com was accessible.
- Accessible user-level scripts contained no scheduled staging cron runner, but Hostinger/server scheduler configuration remains UNKNOWN because the user crontab command and hosting scheduler were unavailable.
- No cron event, queue action, email, HTTP request, deployment, file edit, or database mutation was performed.

## Runtime state

- Source branch: staging
- Source HEAD: 872d498727c77061b12d461f53d1ac35f8190c14
- Deployment marker observed: 27cd48f44f3ac541f96cde6d2a5aeb6034fafa3f
- WordPress environment: staging
- Raspitajse Communications plugin: active
- Communications takeover option: 1
- Communications transport: enabled
- Staging mail-safety: loaded
- Repository: clean

## Task A — all overdue WP-Cron events

Risk is conservative. Anything involving email, job/candidate/employer/application/user state, WooCommerce order/state/payment/refund, or unknown business state is HIGH.

| # | Hook | Scheduled GMT | Recurrence | Owner/component | Callback | Likely side effects | Risk | Safe later |
|---:|---|---|---|---|---|---|---|---|
| 1 | action_scheduler_run_queue | 2026-05-14 19:40:14 | every_minute | Action Scheduler, Hostinger-vendored runner | YES | Runs an unbounded mixture of queued actions and can fan out more work | HIGH | NO |
| 2 | woocommerce_cancel_unpaid_orders | 2026-05-14 19:48:19 | one-time | WooCommerce | YES | Cancels unpaid orders and changes order/business state | HIGH | NO |
| 3 | litespeed_task_lqip | 2026-05-14 19:53:03 | litespeed_filter | LiteSpeed Cache placeholder/LQIP | YES | Generates or updates image placeholder/cache data; consumes CPU/network | LOW | YES |
| 4 | wc_admin_process_orders_milestone | 2026-05-14 20:04:35 | hourly | WooCommerce Admin OrderMilestones | YES | Reads order counts and may create an admin note | HIGH | WITH PREPARATION |
| 5 | wc_admin_unsnooze_admin_notes | 2026-05-14 20:05:33 | hourly | WooCommerce Admin Notes | YES | Updates admin-note state | MEDIUM | WITH PREPARATION |
| 6 | woocommerce_cleanup_sessions | 2026-05-14 20:07:50 | twicedaily | WooCommerce session handler | YES | Deletes expired session/cart records | MEDIUM | WITH PREPARATION |
| 7 | wp_privacy_delete_old_export_files | 2026-05-14 20:07:58 | hourly | WordPress privacy tools | YES | Deletes expired personal-data export files | MEDIUM | WITH PREPARATION |
| 8 | jetpack_clean_nonces | 2026-05-14 20:39:13 | hourly | Jetpack | YES | Removes expired Jetpack nonces | LOW | YES |
| 9 | puc_cron_check_updates-hostinger-ai-assistant | 2026-05-14 21:27:25 | twicedaily | Plugin Update Checker / Hostinger AI Assistant | YES | Remote update-metadata check and option/cache writes | LOW | YES |
| 10 | puc_cron_check_updates-wp-job-board-pro | 2026-05-14 22:16:04 | twicedaily | Plugin Update Checker / WP Job Board Pro | YES | Remote update-metadata check and option/cache writes | LOW | YES |
| 11 | puc_cron_check_updates-wp-job-board-pro-wc-paid-listings | 2026-05-14 22:37:27 | twicedaily | Plugin Update Checker / Job Board Paid Listings | YES | Remote update-metadata check and option/cache writes | LOW | YES |
| 12 | woocommerce_scheduled_sales | 2026-05-15 00:00:00 | daily | WooCommerce | YES | Starts/ends scheduled product sales and changes commerce state | HIGH | NO |
| 13 | wp_version_check | 2026-05-15 02:04:33 | twicedaily | WordPress core | YES | Remote version check and transient/option writes | LOW | YES |
| 14 | wp_update_plugins | 2026-05-15 02:04:33 | twicedaily | WordPress core | YES | Remote plugin-update check and transient writes; does not install updates | LOW | YES |
| 15 | wp_update_themes | 2026-05-15 02:04:33 | twicedaily | WordPress core | YES | Remote theme-update check and transient writes; does not install updates | LOW | YES |
| 16 | wp_update_user_counts | 2026-05-15 02:05:14 | twicedaily | WordPress multisite/core user counts | YES | Recalculates stored user counts | HIGH | WITH PREPARATION |
| 17 | woocommerce_marketplace_cron_fetch_promotions | 2026-05-15 02:05:25 | twicedaily | WooCommerce Marketplace | YES | Fetches remote promotions and updates cache/options | LOW | YES |
| 18 | woocommerce_cleanup_personal_data | 2026-05-15 02:08:00 | daily | WooCommerce privacy erasure | YES | Erases or anonymizes customer/order personal data under configured policy | HIGH | NO |
| 19 | woocommerce_tracker_send_event | 2026-05-15 02:08:00 | daily | WooCommerce Tracks/telemetry | NO | Intended telemetry send; current callback is absent | MEDIUM | WITH PREPARATION |
| 20 | puc_cron_check_updates-hostinger-easy-onboarding | 2026-05-15 03:37:29 | twicedaily | Plugin Update Checker / Hostinger Easy Onboarding | YES | Remote update-metadata check and option/cache writes | LOW | YES |
| 21 | woocommerce_cleanup_logs | 2026-05-15 05:07:50 | daily | WooCommerce logger | YES | Deletes logs beyond retention | MEDIUM | WITH PREPARATION |
| 22 | woocommerce_cleanup_rate_limits | 2026-05-15 05:07:50 | daily | WooCommerce rate limiting | NO | Intended deletion of expired rate-limit records; current callback absent | LOW | WITH PREPARATION |
| 23 | elementor/tracker/send_event | 2026-05-15 08:52:36 | daily | Elementor tracker | YES | Sends telemetry and updates tracking state | MEDIUM | WITH PREPARATION |
| 24 | wc_admin_daily | 2026-05-15 08:53:22 | daily | WooCommerce Admin | YES | Umbrella daily processing with potentially changing WooCommerce admin/business state | HIGH | NO |
| 25 | wp_job_board_pro_delete_old_previews | 2026-05-15 08:53:57 | daily | WP Job Board Pro | YES | Deletes old preview job/candidate/employer content | HIGH | NO |
| 26 | wp_site_health_scheduled_check | 2026-05-15 14:04:33 | weekly | WordPress Site Health | YES | Runs health checks and stores results | LOW | YES |
| 27 | recovery_mode_clean_expired_keys | 2026-05-15 14:04:33 | daily | WordPress Recovery Mode | YES | Deletes expired recovery-mode keys | MEDIUM | WITH PREPARATION |
| 28 | jetpack_v2_heartbeat | 2026-05-15 14:04:34 | daily | Jetpack | YES | Remote heartbeat and connection/state updates | MEDIUM | WITH PREPARATION |
| 29 | monsterinsights_feature_feedback_checkin | 2026-05-15 14:04:34 | daily | MonsterInsights | YES | Updates feature-feedback state/cache | LOW | YES |
| 30 | wp_scheduled_delete | 2026-05-15 14:05:25 | daily | WordPress core, WooCommerce HPOS, PDF invoice cleanup | YES | Permanently deletes trashed content/orders and temporary invoice files | HIGH | NO |
| 31 | delete_expired_transients | 2026-05-15 14:05:25 | daily | WordPress core | YES | Deletes expired transient cache entries | LOW | YES |
| 32 | wp_job_board_pro_email_daily_notices | 2026-05-15 14:07:06 | wpie_daily | WP Job Board Pro | YES | Sends admin/employer/candidate expiry notices and job/candidate alerts; updates notice timing/meta | HIGH | NO |
| 33 | wp_scheduled_auto_draft_delete | 2026-05-15 14:29:00 | daily | WordPress core and WooCommerce | YES | Deletes old auto-draft posts and auto-draft orders | HIGH | NO |
| 34 | monsterinsights_cache_daily_cleanup | 2026-05-15 15:03:23 | daily | MonsterInsights | YES | Prunes plugin cache | LOW | YES |
| 35 | wpforms_email_summaries_cron | 2026-05-18 00:00:00 | one-time | WPForms Email Summaries | YES | Sends email summary and updates summary scheduling/state | HIGH | WITH PREPARATION |
| 36 | wpforms_weekly_entries_count_cron | 2026-05-18 00:00:00 | one-time | WPForms | YES | Reads entry data and stores aggregate counts | MEDIUM | WITH PREPARATION |
| 37 | monsterinsights_usage_tracking_cron | 2026-05-21 07:35:00 | weekly | MonsterInsights tracking | NO | Intended telemetry; current callback absent | MEDIUM | WITH PREPARATION |
| 38 | monsterinsights_feature_feedback_clear_expired | 2026-05-21 14:04:34 | weekly | MonsterInsights | YES | Clears expired feature-feedback records/cache | LOW | YES |
| 39 | run_weekly_partner_astra | 2026-05-21 14:04:37 | weekly | Astra/partner integration | NO | Intended partner task is unknown in current runtime because callback is absent | HIGH | NO |
| 40 | wp_delete_temp_updater_backups | 2026-05-21 14:05:14 | weekly | WordPress core updater | YES | Deletes old temporary update backup files | MEDIUM | WITH PREPARATION |
| 41 | woocommerce_geoip_updater | 2026-05-22 02:08:50 | fifteendays | WooCommerce MaxMind GeoIP | YES | Downloads/replaces GeoIP data and updates metadata | MEDIUM | WITH PREPARATION |
| 42 | monsterinsights_product_feed_monthly_check | 2026-06-02 00:00:00 | monthly | MonsterInsights | NO | Intended product-feed check; current callback absent | LOW | WITH PREPARATION |
| 43 | monsterinsights_charitable_notice_cron | 2026-06-03 00:00:00 | monsterinsights_monthly | MonsterInsights | NO | Intended notice-state update; current callback absent | LOW | WITH PREPARATION |
| 44 | monsterinsights_email_summaries_cron | 2026-06-05 00:00:00 | monsterinsights_monthly | MonsterInsights Email Summaries | NO | Intended email-summary flow; current callback absent | HIGH | NO |

### SAFE FIRST — planning list only

These have registered callbacks, LOW risk, and narrowly scoped cache/maintenance/update-check behavior:

- litespeed_task_lqip
- jetpack_clean_nonces
- puc_cron_check_updates-hostinger-ai-assistant
- puc_cron_check_updates-wp-job-board-pro
- puc_cron_check_updates-wp-job-board-pro-wc-paid-listings
- puc_cron_check_updates-hostinger-easy-onboarding
- wp_version_check
- wp_update_plugins
- wp_update_themes
- woocommerce_marketplace_cron_fetch_promotions
- wp_site_health_scheduled_check
- monsterinsights_feature_feedback_checkin
- delete_expired_transients
- monsterinsights_cache_daily_cleanup
- monsterinsights_feature_feedback_clear_expired

### PREPARE FIRST — planning list only

- wc_admin_unsnooze_admin_notes
- woocommerce_cleanup_sessions
- wp_privacy_delete_old_export_files
- woocommerce_tracker_send_event
- woocommerce_cleanup_logs
- woocommerce_cleanup_rate_limits
- elementor/tracker/send_event
- recovery_mode_clean_expired_keys
- jetpack_v2_heartbeat
- wpforms_weekly_entries_count_cron
- monsterinsights_usage_tracking_cron
- wp_delete_temp_updater_backups
- woocommerce_geoip_updater
- monsterinsights_product_feed_monthly_check
- monsterinsights_charitable_notice_cron
- All callback-absent events require owner/version verification before deciding whether to remove, replace, or run them.
- wpforms_email_summaries_cron is HIGH but can move to a controlled mail-safety test only after recipient and side-effect preparation.

### QUARANTINE — planning list only

- action_scheduler_run_queue
- woocommerce_cancel_unpaid_orders
- wc_admin_process_orders_milestone
- woocommerce_scheduled_sales
- wp_update_user_counts
- woocommerce_cleanup_personal_data
- wc_admin_daily
- wp_job_board_pro_delete_old_previews
- wp_scheduled_delete
- wp_job_board_pro_email_daily_notices
- wp_scheduled_auto_draft_delete
- run_weekly_partner_astra
- monsterinsights_email_summaries_cron

## Task B — all pending Action Scheduler actions

Arguments are shown only as sanitized types and shape.

| ID | Hook | Group | Scheduled GMT | Recurrence | Owner/component | Sanitized args | Risk | Safe later |
|---:|---|---|---|---|---|---|---|---|
| 32713 | aioseo_image_sitemap_scan | aioseo | 2026-05-14 19:47:38 | one-time | All in One SEO image sitemap | empty list | MEDIUM | WITH PREPARATION |
| 32676 | aioseo_ai_update_credits | aioseo | 2026-05-15 10:47:36 | every 86400 seconds | All in One SEO AI credits | empty list | MEDIUM | WITH PREPARATION |
| 32677 | woocommerce_cleanup_draft_orders | none | 2026-05-15 10:47:36 | every 86400 seconds | WooCommerce | empty list | HIGH | NO |
| 32678 | wpforms_process_forms_locator_scan | wpforms | 2026-05-15 10:47:36 | every 86400 seconds | WPForms forms locator | map with one integer | MEDIUM | WITH PREPARATION |
| 32679 | wpforms_process_purge_spam | wpforms | 2026-05-15 10:47:36 | every 86400 seconds | WPForms spam purge | map with one integer | HIGH | NO |
| 32680 | aioseo_cache_prune | aioseo | 2026-05-15 10:47:36 | every 86400 seconds | All in One SEO cache | empty list | LOW | YES |
| 32681 | action_scheduler_run_recurring_actions_schedule_hook | ActionScheduler | 2026-05-15 10:47:36 | every 86400 seconds | Action Scheduler | empty list | MEDIUM | WITH PREPARATION |
| 32521 | wpforms_builder_help_cache_update | wpforms | 2026-05-18 04:39:10 | every 604800 seconds | WPForms builder-help cache | map with one integer | LOW | YES |
| 32522 | wpforms_admin_addons_cache_update | wpforms | 2026-05-18 04:39:10 | every 604800 seconds | WPForms addons cache | map with one integer | LOW | YES |
| 32523 | wpforms_admin_builder_templates_cache_update | wpforms | 2026-05-18 04:39:10 | every 604800 seconds | WPForms template cache | map with one integer | LOW | YES |
| 32571 | wpo_ips_semaphore_lock_cleanup | none | 2026-05-18 23:19:13 | every 604800 seconds | WooCommerce PDF Invoices/Packing Slips semaphore | empty list | LOW | WITH PREPARATION |
| 32600 | wpforms_email_summaries_fetch_info_blocks | wpforms | 2026-05-19 15:45:05 | every 604800 seconds | WPForms Email Summaries | map with one null | MEDIUM | WITH PREPARATION |
| 32672 | aioseo_report_summary | aioseo | 2026-05-21 08:22:06 | every 604800 seconds | All in One SEO report summary | map with one string | HIGH | WITH PREPARATION |
| 32199 | aioseo_report_summary | aioseo | 2026-06-02 19:18:21 | every 2592000 seconds | All in One SEO report summary | map with one string | HIGH | WITH PREPARATION |
| 32714 | action_scheduler/migration_hook | action-scheduler-migration | 2026-08-21 23:47:41 | one-time | Action Scheduler migration | empty list | HIGH | NO |

Current callback notes:

- Registered callbacks were identified for the Action Scheduler recurring scheduler and migration, AIOSEO image/AI/cache/report actions, WooCommerce draft cleanup, the three WPForms cache refresh actions, and the invoice semaphore cleanup.
- No current callback was found for wpforms_process_forms_locator_scan, wpforms_process_purge_spam, or wpforms_email_summaries_fetch_info_blocks. Their records must not be treated as safe merely because execution would currently be a no-op.

### Counts by hook

| Hook | Count |
|---|---:|
| aioseo_report_summary | 2 |
| action_scheduler/migration_hook | 1 |
| action_scheduler_run_recurring_actions_schedule_hook | 1 |
| aioseo_ai_update_credits | 1 |
| aioseo_cache_prune | 1 |
| aioseo_image_sitemap_scan | 1 |
| woocommerce_cleanup_draft_orders | 1 |
| wpforms_admin_addons_cache_update | 1 |
| wpforms_admin_builder_templates_cache_update | 1 |
| wpforms_builder_help_cache_update | 1 |
| wpforms_email_summaries_fetch_info_blocks | 1 |
| wpforms_process_forms_locator_scan | 1 |
| wpforms_process_purge_spam | 1 |
| wpo_ips_semaphore_lock_cleanup | 1 |

### Counts by group

| Group | Count |
|---|---:|
| wpforms | 6 |
| aioseo | 5 |
| none | 2 |
| ActionScheduler | 1 |
| action-scheduler-migration | 1 |

## Task C — staging traffic evidence

Result: SKIPPED.

- No accessible access log could be reliably isolated to stage.raspitajse.com.
- The available shared error log contained no staging-host-isolated request records and is not a suitable access-history source.
- Production-adjacent/shared traffic was not inspected or inferred.

Therefore the following cannot be determined safely:

- Earliest available staging request: SKIPPED
- Latest staging request: SKIPPED
- Staging HTTP traffic after 2026-05-14: SKIPPED
- Existence of wp-cron.php requests: SKIPPED
- Last wp-cron.php request: SKIPPED
- Whether traffic or wp-cron requests stopped around May 14–15: SKIPPED

No HTTP request was made.

## Task D — external scheduled-task evidence

| Scope | Evidence | Result |
|---|---|---|
| Accessible repository deployment/tools scripts | No staging wp-cron.php, staging wp cron, or Action Scheduler runner invocation found | ABSENT |
| Accessible user-level executable/config locations | No matching staging cron invocation found | ABSENT |
| User crontab | crontab utility/configuration not accessible in this environment | UNKNOWN |
| Hostinger scheduled-task panel/server scheduler | Not accessible from this task | UNKNOWN |

Overall conclusion: no external runner is PRESENT in accessible user-level evidence; a hosting-level runner cannot be ruled in or out.

## Task E — refined root cause

### ROOT CAUSE

| Candidate | Confidence | Assessment |
|---|---|---|
| No reliable cron dispatch reaches staging | HIGH | Best system-level explanation: unrelated WP-Cron families all stopped advancing together while the stored cron array remains valid. This describes the failure mechanism without claiming which trigger is missing. |
| Staging inactivity combined with traffic-triggered WP-Cron | MEDIUM | Plausible if staging received no requests, but traffic history could not be isolated safely. |
| Missing external/server cron | MEDIUM | No runner exists in accessible scripts, but Hostinger scheduler state is unavailable. |
| Loopback/spawn failure | LOW to MEDIUM | Plausible when traffic exists but spawning fails; no isolated access records or permitted loopback test were available. |
| Resource/process limits | LOW | The account has restrictive limits, but no staging-isolated evidence ties a resource failure to the May stoppage. |
| Plugin failure as the global cause | LOW | The backlog spans core and multiple unrelated plugins. Callback-absent orphan events are local plugin/version drift, not a credible system-wide cause. |
| Action Scheduler stall as an independent root cause | LOW | Its WP-Cron runner is itself overdue, so the scheduler stall is better explained downstream of missing WordPress cron dispatch. |

### CONSEQUENCES

- 44 of 44 stored WP-Cron events are overdue.
- 15 of 15 pending Action Scheduler actions are overdue.
- Maintenance, update metadata, cache cleanup, telemetry, security-key cleanup, and scheduler maintenance are stale.
- Email/report/alert work is backlogged.
- Order, privacy, deletion, job-board, and user-related tasks could cause a dangerous burst of business-state changes if a general runner is enabled.
- Action Scheduler can continue accepting scheduled records while its runner remains stalled; the recent migration action demonstrates that queue creation and queue execution are distinct.

## Task F — incremental recovery plan

This is a plan only. Nothing below was executed.

### Phase 1 — safe housekeeping events

Exact initial candidates:

- wp_version_check
- wp_update_plugins
- wp_update_themes
- delete_expired_transients
- jetpack_clean_nonces
- wp_site_health_scheduled_check
- monsterinsights_cache_daily_cleanup
- monsterinsights_feature_feedback_checkin
- monsterinsights_feature_feedback_clear_expired
- woocommerce_marketplace_cron_fetch_promotions
- The four puc_cron_check_updates-* events
- litespeed_task_lqip, last and under resource observation

Prerequisites:

- Reconfirm staging environment, repository/deploy marker, communications takeover, and mail safety.
- Capture fresh cron and database-table fingerprints plus a bounded log timestamp.
- Reconfirm each callback and inspect current plugin version behavior.
- Run exactly one named event at a time; never run due-now or the general cron runner.
- Monitor process/memory limits for LQIP and remote-call events.

Success criteria:

- Only the selected event advances/reschedules or completes.
- No email or business record is touched.
- No PHP/PHPMailer/plugin error and no unexpected new queue fan-out.
- Resource use returns to baseline before the next candidate.

Stop/rollback conditions:

- Stop on mail activity, order/job/user/form changes, unexpected scheduled actions, repeated remote failures, or resource pressure.
- Restore only explicitly captured cache/option state if a candidate has an unexpected narrow write; do not broadly roll back the database.

### Phase 2 — low-risk Action Scheduler actions

Exact initial candidates:

- ID 32680 aioseo_cache_prune
- ID 32521 wpforms_builder_help_cache_update
- ID 32522 wpforms_admin_addons_cache_update
- ID 32523 wpforms_admin_builder_templates_cache_update
- ID 32571 wpo_ips_semaphore_lock_cleanup only after proving no active invoice generation/lock
- ID 32713 aioseo_image_sitemap_scan only with a bounded post set and metadata snapshot
- ID 32676 aioseo_ai_update_credits only after confirming outbound API behavior and option/cache scope

Keep prepared but do not run initially:

- ID 32681 recurring-action scheduler hook because it can create/reschedule more work
- IDs 32678 and 32600 until their missing current callbacks are explained
- IDs 32677, 32679, 32672, 32199, and 32714 remain quarantined

Prerequisites:

- Snapshot the selected action row, relevant cache/options or metadata, and Action Scheduler logs.
- Confirm callback registration, exact affected tables, and external calls.
- Execute by exact action ID, one at a time, never the queue runner.

Success criteria:

- Selected action alone completes and, if recurring, creates exactly one expected successor.
- No email, order, form entry, job, user, or payment state changes.
- Queue size does not unexpectedly grow.

Stop/rollback conditions:

- Stop on fan-out, unexpected record types, missing callback, remote/API errors, or resource pressure.
- Use the action-specific cache/metadata snapshot only; do not requeue broadly.

### Phase 3 — controlled email/report events under staging mail safety

Exact candidates after preparation:

- wpforms_email_summaries_cron
- Action IDs 32672 and 32199 aioseo_report_summary
- monsterinsights_email_summaries_cron only after its absent callback and current feature status are resolved

The WP Job Board daily notice hook is excluded from this phase because it mixes email with candidate/employer/job-alert state and belongs in Phase 4.

Prerequisites:

- Reconfirm environment=staging, communications active, takeover=1, transport enabled, and mail-safety loaded in a fresh process.
- Prove the final recipient is only the configured staging inbox.
- Inspect intended sender/subject and metadata writes before execution.
- Establish a new log boundary and run one exact event/action with a unique staging subject where supported.

Success criteria:

- Exactly one protected staging message per approved test.
- Recipient is only the configured staging inbox and subject has exactly one staging prefix.
- Expected report scheduling metadata only; no business records altered.
- No warning, deprecation, PHPMailer, communications, or mail-safety error.

Stop/rollback conditions:

- Stop immediately on any real-user recipient, duplicate prefix, multiple sends, SMTP error, or unexpected metadata/business change.
- Disable only the specific report schedule/action if explicitly approved; retain mail safety.

### Phase 4 — business-state-changing jobs only with fixtures/rollback

Quarantined candidates:

- woocommerce_cancel_unpaid_orders
- woocommerce_scheduled_sales
- woocommerce_cleanup_personal_data
- wc_admin_process_orders_milestone
- wc_admin_daily
- wp_scheduled_delete
- wp_scheduled_auto_draft_delete
- wp_update_user_counts
- wp_job_board_pro_delete_old_previews
- wp_job_board_pro_email_daily_notices
- ID 32677 woocommerce_cleanup_draft_orders
- ID 32679 wpforms_process_purge_spam
- ID 32714 action_scheduler/migration_hook
- action_scheduler_run_queue remains prohibited as a recovery shortcut

Prerequisites:

- Separate approval for each business domain.
- Dedicated staging-only fixture records with exact IDs, no copied private data, and no real recipients.
- Database and file backup scoped to staging, documented restore steps, affected-row preview, and mail-safety verification.
- For migration, vendor/version compatibility review and a staging restore point.

Success criteria:

- Only named fixtures change.
- Exact expected transitions/deletions/messages occur once.
- No unrelated jobs, applications, candidates, employers, users, orders, payments, refunds, forms, or files change.

Stop/rollback conditions:

- Stop on any non-fixture ID, unexpected recipient, destructive scope expansion, payment/refund integration, queue fan-out, or migration inconsistency.
- Restore the scoped staging backup/fixtures according to the preapproved rollback plan.

### Phase 5 — establish reliable ongoing staging cron triggering

Plan:

- Only after Phases 1–4 drain or quarantine dangerous backlog, configure one staging-scoped Hostinger scheduled task that targets the exact staging WordPress path.
- Prefer a lock-protected, resource-bounded WP-CLI runner that first records status and runs only approved due work; do not point any task at production or the parent public_html directory.
- Avoid enabling both traffic spawning and an external runner without a duplicate-run/locking review.
- Add monitoring for last successful staging cron dispatch, overdue-event count, pending-action count, failures, runtime, and resource-limit errors.
- Start at a conservative interval and increase only after stable observations.

Prerequisites:

- Hosting scheduler access and explicit review of the exact command/path.
- Backlog reduced to known safe steady-state records.
- Locking and duplicate-run behavior tested.
- Alert destination contains no private data and cannot send to real users.
- Any wp-config change requires a separate reviewed task; none is assumed here.

Success criteria:

- Consecutive scheduled runs advance normal WP-Cron timestamps.
- Action Scheduler pending age/count remains bounded.
- No duplicate execution, email burst, business-state surprise, or resource-limit error.
- Staging-only logs provide an auditable last-success timestamp.

Stop/rollback conditions:

- Disable the staging scheduler on overlap, queue growth, timeout, lock contention, resource exhaustion, unexpected mail, or business changes.
- Restore the previous scheduler configuration; do not touch production.

## Final integrity

- Cron array unchanged: PASS — 44 events and identical SHA-256 fingerprint ed3d49ab6d00db1db21466738f9a315db08e86c7761ca864fe853377240a08a0.
- Action Scheduler unchanged: PASS — the same 15 pending IDs, hooks, groups, schedules, recurrences, and sanitized argument shapes remained; zero Action Scheduler log rows appeared after the task boundary.
- Cron executed: NO.
- Queue action executed: NO.
- Email sent: NO.
- HTTP request made: NO.
- Database modified: NO.
- Files modified: NO.
- Repository clean: PASS.
- Communications takeover remains enabled: PASS.
- Staging mail-safety loaded: PASS.
- Production touched: NO.

## Warnings / limitations

- The first final integrity command was blocked before execution by the local sandbox namespace limit (ENOSPC). The same read-only checks succeeded outside that exhausted namespace; no application/runtime error was observed.
- Traffic-history findings are PARTIAL because a staging-only access log was unavailable.
- External scheduler evidence is PARTIAL because Hostinger/server scheduling configuration was inaccessible.
