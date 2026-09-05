# Zadatak 2.13 — WP Job Board Pro security/upgrade readiness audit with fresh authoritative vulnerability/version verification

Status: READY
Baseline: 77d3a1019e0248a2abacd607fd508ed6868da70b
Previous task: 2.12
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before doing any inspection, WordPress bootstrap, public research, artifact handling, or source analysis. Treat `codex-tasks` as READ-ONLY.

Verify fresh `origin/staging` is exactly:

`77d3a1019e0248a2abacd607fd508ed6868da70b`

If it differs, STOP and report the mismatch. Do not silently rebase or widen this task.

Read the final Zadatak 2.12 PASS report. Read the relevant 1.93–1.97 vendor-provenance reports as needed to distinguish proven facts from historical assumptions.

Execute only Zadatak 2.13. This task is audit/readiness only. **Expected source changes: 0. Expected staging deploys: 0.** Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.14 automatically.

---

## 1. Accepted context

Zadatak 2.12 PASS established:

`LIGHTWEIGHT_STEADY_STATE_SELECTIVE_RUNNER_ACCEPTED`

The scheduler/communications execution boundary is not in scope for redesign here. The accepted staging scheduler remains one human-owned `*/15 * * * *` zero-argument selective runner with `DISABLE_WP_CRON=true` and the fixed three owned hooks. Do not mutate it.

Historical WP Job Board Pro evidence from 1.93–1.97 established, subject to fresh verification in this task:

- the site has been operating with a WP Job Board Pro 1.2.x lineage and prior inspections identified `1.2.73` as the apparent current version;
- an artifact named as 1.2.66 was a valid historical older package only;
- a later locally supplied artifact named as 1.2.73 failed clean-vendor provenance because it contained Raspitajse-specific/customized content and a backup-style root layout;
- therefore that 1.2.73 artifact must **not** be treated as a clean upstream reference or upgrade package;
- candidate→job vendor cleanup was intentionally parked until a future WPJBP upgrade/normalization step;
- three known later-modified WPJBP files requiring explicit upgrade treatment are:
  1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
  2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
  3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`.

A prior public-source concern suggested a critical unauthenticated privilege-escalation advisory and a possible patched threshold around `1.2.85`. **That is not a binding fact for this task. Re-verify it from fresh authoritative/current sources.** Public advisory metadata has changed over time and may contain conflicting product/version wording, so the report must distinguish exact affected ranges, product slugs/names, patched-version confidence, and source freshness rather than copying a stale summary.

---

## 2. Goal

Produce a decision-grade, read-only security and upgrade-readiness audit for the currently active WP Job Board Pro installation.

The task must answer all of the following:

1. What exact WP Job Board Pro version is present in source, deployed runtime, and active WordPress state on staging?
2. Is that exact version currently affected by any known published security vulnerability, especially unauthenticated privilege escalation? What are the exact CVE/advisory IDs, affected ranges, patched versions, severity, and confidence?
3. What is the latest **official/vendor-supported** WP Job Board Pro version that can be established today, and from what authoritative source?
4. Is there a clean, trustworthy upgrade artifact already available locally or from a clearly official unauthenticated source? If not, what exact artifact must the user obtain?
5. Which Raspitajse-owned components, hooks, templates, customizations, post types/meta contracts, and vendor-file changes could be affected by upgrading WPJBP?
6. Which current vendor customizations should disappear during normalization, which business needs must remain in Raspitajse-owned code, and which compatibility dependencies require testing?
7. What exact staging upgrade procedure, acceptance matrix, rollback plan, and safety gates are required before any future implementation task may upgrade the plugin?
8. Is the project READY for a controlled staging upgrade, or is it blocked by artifact provenance, version/security evidence, or unresolved compatibility risk?

Do not perform the upgrade in 2.13.

---

## 3. Hard no-mutation boundary

Do not modify, install, update, downgrade, deactivate, reactivate, replace, delete, or restore WP Job Board Pro or any other plugin/theme.

Do not modify:

- WordPress core;
- WooCommerce;
- WP Job Board Pro;
- WP Job Board Pro Paid Listings;
- Superio/theme vendor code;
- child-theme code;
- Raspitajse Communications;
- Raspitajse Commerce;
- MU mail safety;
- deployment manifests/markers;
- cron rows;
- Action Scheduler rows;
- database/business data;
- Hostinger scheduler configuration.

Do not create a feature branch or staging deployment. Do not run an installer, upgrader, database migration, activation hook, plugin update check, broad WP-Cron, Action Scheduler queue runner, or owned business hook.

Do not exploit or proof-of-concept the suspected registration vulnerability against staging. Do not create users, attempt administrator registration, change roles/capabilities, or submit malicious registration requests. Security verification in this task is source/advisory/readiness analysis only.

Production filesystem/database/runtime/WordPress/scheduler access is forbidden.

---

## 4. Public research authorization and source quality

Fresh public **read-only control-plane research is explicitly authorized for this task** because authoritative vulnerability/version verification is the purpose of the audit.

Allowed:

- official ApusThemes/WP Job Board Pro/Superio product or changelog pages;
- ThemeForest/Envato public product metadata/changelog pages;
- CVE.org/MITRE/CNA records;
- NVD;
- Wordfence Threat Intelligence;
- Patchstack;
- other reputable vulnerability databases only as corroboration;
- public unauthenticated release metadata or a clearly official public artifact URL if one exists.

Rules:

- this authorization applies to Codex/control-plane research only, **not WordPress runtime outbound HTTP**;
- do not trigger a WordPress/plugin update check or vendor heartbeat to discover versions;
- do not log in to Envato/ThemeForest/ApusThemes, use stored credentials/tokens, accept license terms, purchase anything, change an account, or contact the vendor;
- do not expose query strings, signed URLs, license keys, tokens, cookies, account data, or secrets in the report;
- if an official package requires authentication or a user-owned download, report the exact artifact requirement instead of bypassing access controls;
- a public unauthenticated official ZIP may be downloaded only to task-private scratch for read-only inspection if provenance is unambiguous; do not install or execute it, and remove scratch after reporting;
- record source URL/domain, retrieval date/time, source role (vendor/CNA/NVD/secondary), and what exact claim each source supports;
- prefer primary vendor/CNA records over aggregators, but explicitly report disagreements rather than silently reconciling them;
- if public network access is unavailable, do not invent current version/advisory facts: report the evidence gap.

Historical local metadata, cached WordPress update transients, reseller/download sites, filenames, and archive names are **not authoritative evidence of the latest official version**.

---

## 5. Mandatory current-version and active-state verification

Verify the current WPJBP installation from fresh staging/source evidence.

At minimum establish:

- source plugin root/path;
- source main plugin header `Version` and any authoritative version constant(s);
- deployed runtime plugin header/version constant(s);
- source/runtime relevant file parity or explain any expected deployment difference;
- whether `wp-job-board-pro` is active in staging WordPress state;
- current companion Paid Listings version/status where directly relevant to upgrade compatibility;
- Superio version only to the extent needed to determine whether WPJBP is bundled/coupled to the theme release.

If WordPress bootstrap is needed for active-state verification, use a **fully guarded read-only invocation** that blocks WP HTTP transport and mail/payment side effects. Do not run update APIs or mutation commands. Report guarded process count and intercepted/unexpected HTTP/mail/payment counters.

If the source header, runtime header, version constant, active-state metadata, theme bundle metadata, or cached update metadata disagree, treat the disagreement as a finding and establish which source is authoritative for the actually executing code.

Do not rely on the historical claim `1.2.73` without fresh verification.

---

## 6. Mandatory vulnerability/advisory verification

Search current authoritative sources for **all known relevant WP Job Board Pro vulnerabilities**, not only the previously suspected one.

For every relevant advisory report:

- CVE/advisory ID;
- title/vulnerability class;
- affected product name and slug exactly as published;
- vulnerable version range;
- patched/fixed version, if proven;
- CVSS/severity;
- authentication/privilege requirement;
- publication and latest modification dates;
- source/CNA/vendor identity;
- whether the currently verified staging version falls in the affected range;
- confidence: CONFIRMED / CORROBORATED / CONFLICTING / UNVERIFIED.

Special handling for the prior privilege-escalation concern:

- independently verify whether `CVE-2024-12213` is the correct record;
- verify whether the affected range is `< 1.2.85`, `<= 1.2.76`, another range, or source-dependent;
- verify the oldest confirmed patched version and distinguish “confirmed patched in X” from “all later versions proven safe”;
- explicitly inspect current CNA/Wordfence affected-version data and current NVD text/configuration;
- if a current source mentions a different-looking version such as a `2.x` range, determine whether it refers to the same WP Job Board Pro product, a related non-Pro plugin, Superio theme packaging, or inconsistent advisory metadata. **Do not merge different product/version lines by assumption.**

Also determine whether there are other advisories affecting the currently installed version or the prospective target version.

Do not perform exploit validation against the site.

---

## 7. Latest official version and clean-artifact provenance

Establish the latest official/vendor-supported WP Job Board Pro version with the highest confidence available today.

Evidence hierarchy:

1. official ApusThemes/WP Job Board Pro or Superio release/changelog/package metadata;
2. official ThemeForest/Envato Superio bundle/changelog metadata where WPJBP is distributed as a bundled plugin;
3. signed/official package metadata already legitimately present locally;
4. reputable third-party vulnerability/version metadata only as corroboration, not the sole basis for `latest official`.

Report:

- latest official version proved;
- source and release/update date if available;
- whether the release is standalone or bundled with a specific Superio release;
- PHP/WordPress/WooCommerce/other minimum requirements if published;
- whether a clean package is available for the future staging upgrade.

Inspect these historical local artifact facts without changing them:

- `/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip` was previously identified as historical `1.2.66`;
- `/home/u601262303/repo/vendor-artifacts/wp-job-board-pro-1.2.73.zip` was previously identified as contaminated/customized and must not be promoted to clean provenance merely because its filename/version matches.

Also inspect only reasonable known local/package locations for a newer clean artifact, such as the repository vendor-artifacts area and Superio bundled-plugin/package locations. Do not perform an unbounded home-directory crawl.

For every candidate ZIP/package used as evidence, record:

- exact path/source;
- SHA-256;
- plugin header/constant version;
- archive root/layout;
- ZIP safety/path traversal result;
- provenance classification: OFFICIAL_CLEAN / HISTORICAL_ONLY / CUSTOMIZED_CONTAMINATED / UNKNOWN;
- whether it is eligible for a future controlled upgrade.

If no clean official target artifact is available, do **not** block the audit itself. Conclude that the implementation upgrade is blocked pending user acquisition of the exact official package.

---

## 8. Raspitajse/WPJBP dependency and customization inventory

Inventory the **current dependencies that matter to an upgrade** without reverse-engineering irrelevant vendor internals.

Search current owned/source code for direct dependencies on WPJBP and Paid Listings, including as applicable:

- `WP_Job_Board_Pro_*` classes/static methods/functions;
- WPJBP hooks/actions/filters;
- custom AJAX/REST callback replacement/removal;
- post types such as jobs, employers, candidates, alerts, packages;
- WPJBP meta keys consumed by Raspitajse-owned code;
- template overrides under child theme/custom layers;
- package/Paid Listings integration points;
- candidate/job alert management/security adapters;
- owned job-expiry and notification cutovers;
- candidate auto-expiry disablement;
- legacy vendor sender suppression;
- cron/scheduler assumptions relevant to plugin bootstrap;
- employer/profile lookup dependencies;
- dashboard/frontend templates directly tied to WPJBP markup or methods.

Do not treat every WPJBP internal symbol as a migration requirement. Report only dependencies that Raspitajse still uses or deliberately suppresses.

For each material dependency classify:

- KEEP compatibility contract;
- REDESIGN after upgrade if vendor internals changed;
- DROP obsolete vendor customization;
- TEST ONLY because owned code should already isolate it.

Include migration risk: LOW / MEDIUM / HIGH / CRITICAL.

---

## 9. Vendor-file normalization inventory

Revisit the three known customized WPJBP files and find any additional **current site-specific vendor-file modifications that materially affect upgrade behavior**.

Known required files:

1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`

For each, establish:

- what site-specific behavior is still present today;
- whether that behavior is reachable or already superseded by Raspitajse-owned code;
- whether a clean vendor upgrade should overwrite/remove it;
- whether any surviving business/UI requirement must first exist in an owned layer;
- whether a child-theme/template override, owned plugin adapter, or no replacement is the correct post-upgrade location.

Use git history/current code and clean-artifact comparison only where provenance is trustworthy. Do not use the contaminated 1.2.73 archive as a clean baseline.

If exact upstream diff cannot be proven without a clean same-version artifact, state that limitation. It is sufficient to identify site-specific current behavior and upgrade overwrite risk without fabricating a vendor-original diff.

The target architecture remains: vendor WPJBP files should return to clean vendor ownership during the controlled upgrade wherever Raspitajse business behavior is already owned elsewhere.

---

## 10. Security-boundary compatibility review

Pay special attention to whether a newer WPJBP can coexist with the Raspitajse security/communications boundary already accepted.

At minimum evaluate the future upgrade risk for:

- alert add/remove AJAX and admin-AJAX endpoints replaced by the owned alert-management security adapter;
- role/profile/capability/nonce/ownership checks introduced in 1.84;
- REST alert management remaining disabled where intended;
- candidate→job vendor sender remaining retired while the owned candidate evaluator continues;
- employer→candidate alerts remaining retired;
- legacy daily expiry notices remaining retired;
- candidate auto-expiry remaining disabled;
- owned job listing expiry and employer pre-expiry notification hooks remaining authoritative;
- fixed selective staging cron runner retaining exactly its three owned hooks;
- SenderPolicy channels and caller-independent From/Reply-To behavior;
- package/Paid Listings entitlement flow and the canonical 30-day consumption policy;
- HPOS-safe Raspitajse Commerce employer/order logic;
- current frontend/dashboard template expectations.

Do not assume a newer vendor version preserves hook names, callback priorities, class signatures, template structures, post/meta contracts, or bundled Paid Listings compatibility. Identify exact contracts that the future staging upgrade must prove.

---

## 11. Required controlled staging-upgrade plan

Design the future implementation task, but do not execute it.

The plan must specify:

### Pre-upgrade gates

- exact trusted target package/version and SHA-256;
- source/staging/deploy baseline clean;
- database backup requirement because plugin updates may perform option/schema/data migrations;
- plugin/theme file backup or Git-reconstructable rollback state;
- sanitized snapshots/fingerprints for relevant plugin options, WPJBP post/status counts, owned alerts/jobs/employers/candidates/packages/orders, cron/Action Scheduler state, and accepted Communications/Commerce contracts;
- mail/network/payment safety enabled;
- current Hostinger selective scheduler handled safely during the short deploy window without broad cron execution;
- no production touch.

### Upgrade execution boundary

- staging only;
- clean vendor package only;
- no manual edits to the new vendor tree to “restore” legacy patches;
- vendor customizations must be normalized, with needed behavior supplied by owned layers;
- no broad plugin/theme mass update;
- no broad cron/Action Scheduler execution;
- one bounded source/deploy path with rollback point.

### Post-upgrade acceptance matrix

At minimum include:

1. plugin loads/activates without fatal or migration error;
2. installed/runtime version equals exact target;
3. vendor tree matches trusted package for files not intentionally excluded by deployment mechanics;
4. registration/security boundary no longer accepts caller-selected privileged role according to source/isolated safe test evidence;
5. owned alert-management security endpoints remain authoritative and forbidden paths stay forbidden;
6. candidate→job owned evaluator remains active and vendor sender remains suppressed;
7. employer→candidate alerts remain retired;
8. candidate auto-expiry remains disabled;
9. owned job expiry + employer pre-expiry notification remain exact;
10. fixed three-hook selective runner callbacks/events remain valid and no new continuation/broad cron surface appears;
11. SenderPolicy remains intact;
12. employer/candidate dashboards and alert UI/templates render without fatal/deprecated API breakage;
13. job search/detail/profile/application core WPJBP flows remain functional;
14. Paid Listings/package selection/purchase/entitlement integration remains compatible;
15. canonical 30-day entitlement and listing-duration separation remains unchanged;
16. Raspitajse Commerce employer/order HPOS behavior remains unchanged;
17. no unexpected cron/Action Scheduler/vendor heartbeat behavior is introduced;
18. no real mail/SMTP/payment/external runtime side effects during acceptance;
19. protected business state changes only where the vendor migration explicitly and correctly requires it;
20. full staging E2E follow-up remains possible after the upgrade.

### Rollback

Define rollback triggers and order. A file-only rollback is insufficient if the plugin performs DB migrations. Require restoring a consistent database + plugin/source/runtime state when necessary, then re-proving the accepted scheduler/security/communications fingerprints.

Do not execute backups or rollback in this audit unless a strictly read-only inventory of existing backup capability is needed.

---

## 12. Risk matrix

Produce a concise risk matrix covering at least:

- current-version security exposure;
- clean target artifact availability/provenance;
- version gap size and changelog uncertainty;
- vendor customized files overwritten by upgrade;
- changed hooks/classes/templates/API contracts;
- Paid Listings companion compatibility;
- possible DB migration/options changes;
- alert/security adapter compatibility;
- cron/scheduler callback drift;
- frontend/dashboard/template regressions;
- rollback completeness.

For each: likelihood, impact, evidence, mitigation, and whether it blocks a controlled staging upgrade.

---

## 13. Decision outcome

Finish with exactly one of these outcomes:

### `READY_FOR_CONTROLLED_STAGING_UPGRADE`
Use only when the current security/version state is understood, a specific target version is justified, a clean trustworthy target artifact is available, material compatibility dependencies are inventoried, and the upgrade/rollback acceptance plan is sufficient.

### `BLOCKED_NEEDS_CLEAN_VENDOR_ARTIFACT`
Use when the target version/security need is sufficiently established but no clean trustworthy package eligible for upgrade is available. State the exact official package/version the user must obtain and from where.

### `BLOCKED_SECURITY_OR_VERSION_PROVENANCE`
Use when current vulnerability range, patched version, latest official version, or product/version identity cannot be established with sufficient confidence. State the exact missing authoritative evidence.

### `BLOCKED_COMPATIBILITY_RISK`
Use when a material Raspitajse dependency cannot yet be safely mapped/tested and must be resolved before upgrade execution. State one bounded follow-up needed.

### `NO_UPGRADE_REQUIRED`
Use only if fresh evidence proves the currently installed version is already an appropriate supported/security-fixed target and there is no material version/security reason to upgrade. This outcome requires unusually strong evidence; do not use it merely because an official latest version cannot be found.

If multiple blockers exist, choose the blocker that must be resolved first and list secondary blockers separately.

---

## 14. Acceptance criteria

PASS for the **audit** requires all of the following even if the architecture outcome is a BLOCKED state:

- fresh baseline `77d3a1019e0248a2abacd607fd508ed6868da70b` verified;
- source/runtime/active WPJBP version verified from current evidence;
- current relevant vulnerability records researched from fresh authoritative sources;
- product/version/advisory conflicts explicitly resolved or reported;
- latest official/vendor-supported version established or the exact provenance gap reported;
- local/candidate artifact provenance classified without trusting contaminated history;
- material Raspitajse/WPJBP dependencies inventoried;
- three known vendor-customized files explicitly assessed plus any additional material upgrade-sensitive vendor customizations found;
- security/communications/package/cron compatibility risks assessed;
- exact future staging upgrade acceptance and rollback plan produced;
- one decision outcome selected from section 13;
- source changes/commits/deploys `0/0/0`;
- plugin install/update/deactivate/reactivate `0/0/0/0`;
- database/business mutations `0`;
- scheduler/manual cron/Action Scheduler executions `0`;
- exploit/registration/role-change tests `0`;
- real mail/SMTP/payment `0`;
- WordPress runtime external HTTP `0` (control-plane public research is separately authorized and must be listed by source);
- production touched `NO`.

A BLOCKED readiness outcome is not an audit failure if the blocker is evidenced precisely. Do not manufacture readiness.

---

## 15. Final report

The report must include:

- result and one decision outcome;
- exact source/staging/deploy baseline and cleanliness;
- current WPJBP source/runtime/active version evidence;
- companion Paid Listings/Superio coupling facts where relevant;
- advisory table with IDs, affected/patched ranges, severity, dates, sources, current-version applicability, and confidence;
- explicit analysis of any `1.2.76` / `1.2.85` / `2.x` or other conflicting version wording discovered;
- latest official version evidence and confidence;
- artifact inventory with SHA-256/provenance/eligibility;
- Raspitajse dependency/customization matrix;
- assessment of the three known customized vendor files;
- risk matrix;
- exact controlled staging-upgrade plan and acceptance matrix;
- rollback plan including DB-migration considerations;
- public research sources/domains and retrieval timestamps, without secrets;
- guarded WordPress inspection counters if any bootstrap was used;
- all mutation/execution counters required by acceptance criteria;
- production touched NO;
- exactly one proposed next task, not created or started.

Next-task rule:

- if `READY_FOR_CONTROLLED_STAGING_UPGRADE`: propose **Zadatak 2.14 — Controlled WP Job Board Pro staging upgrade and vendor normalization with pre/post security/compatibility acceptance**;
- if `BLOCKED_NEEDS_CLEAN_VENDOR_ARTIFACT`: propose one narrow artifact acquisition/provenance-verification task after the user supplies the official package;
- if `BLOCKED_SECURITY_OR_VERSION_PROVENANCE`: propose one narrow authoritative-version/advisory evidence-resolution task;
- if `BLOCKED_COMPATIBILITY_RISK`: propose one narrow compatibility-resolution task for the first blocker;
- if `NO_UPGRADE_REQUIRED`: propose the next higher-value read-only audit of remaining Raspitajse custom Woo/legacy child-theme business code.

STOP after publishing the report. Do not begin 2.14.