# Zadatak 2.17 — Superio 1.3.17 → current official release security/upgrade readiness audit with bundled-plugin and child-theme compatibility mapping

Status: READY
Baseline: 5416eb4d327fe44f503591d86577e210560339c1
Previous task: 2.16
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before doing any inspection, WordPress bootstrap, public research, artifact handling, or source analysis. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.16 PASS report in full. Read the final 2.13 report where it materially helps with Superio/WPJBP bundle provenance.

Verify fresh `origin/staging` is exactly:

`5416eb4d327fe44f503591d86577e210560339c1`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean/on `staging`. If any baseline differs, STOP and report the mismatch. Do not silently rebase or widen this task.

Execute only Zadatak 2.17. This is a **read-only security/upgrade-readiness audit**. Expected application source changes: `0`. Expected staging deploys: `0`. Do not begin 2.18 automatically.

---

## 1. Accepted current state

Zadatak 2.16 PASS established the new accepted staging baseline:

- staging/source/deploy commit: `5416eb4d327fe44f503591d86577e210560339c1`;
- WP Job Board Pro active target: `1.2.86`;
- WP Job Board Pro WC Paid Listings active target: `1.0.19`;
- both active vendor trees are normalized to the pinned clean vendor packages and source/runtime parity passed;
- Raspitajse alert/security/communications/package/HPOS acceptance passed;
- Superio parent remained `1.3.17`;
- Superio child remained `1.0.0`;
- WordPress remained `6.6.7`;
- WooCommerce remained `9.5.4`;
- Hostinger scheduler remained unchanged;
- production was not touched.

Prior read-only evidence also found that the current parent-Superio source/runtime trees are not trivially identical: runtime contains additional bundled plugin ZIPs that are absent from the repository tree, and parent Superio is outside the normal staging deploy allowlist. Re-verify the current facts rather than relying on historical counts alone.

A public ThemeForest/AI-overview observation suggested Superio `1.3.37` as the latest release and public changelog entries between `1.3.17` and that version include security/vulnerability/register/application/WooCommerce-related changes. **Treat that as a lead only. Re-verify the current official version and changelog from fresh authoritative sources.**

---

## 2. Goal

Produce a decision-grade answer to whether and how Raspitajse should upgrade the Superio parent theme from the currently active `1.3.17` to the current official release.

The audit must answer:

1. What exact Superio parent and child-theme versions are present in source, runtime, and active WordPress state?
2. What is the current official/latest Superio release today, with authoritative version/date/changelog evidence?
3. Are there known Superio theme vulnerabilities or security fixes affecting `1.3.17`, and is the prospective target outside the affected ranges?
4. What exact bundled plugin versions/packages ship with the target Superio release, especially WP Job Board Pro and Paid Listings?
5. Could a Superio theme upgrade accidentally downgrade, overwrite, re-register, or otherwise interfere with the now-accepted WPJBP `1.2.86` / Paid Listings `1.0.19` runtime?
6. Which child-theme overrides and Raspitajse custom dependencies are sensitive to parent-theme changes?
7. Which parent-theme files or behaviors have site-specific/runtime divergence today, and what is the correct post-upgrade ownership model?
8. What WordPress/WooCommerce/Elementor/WPJBP compatibility requirements must be proven on staging?
9. Is a clean trustworthy target Superio artifact available? If not, what exact user-provided official artifact is required?
10. What exact controlled staging upgrade, backup, acceptance, rollback, and deployment-boundary plan should the next implementation task use?

Final decision must be one of:

- `READY_FOR_CONTROLLED_STAGING_SUPERIO_UPGRADE`;
- `BLOCKED_MISSING_TRUSTED_SUPERIO_ARTIFACT`;
- `BLOCKED_UNRESOLVED_SECURITY_OR_VERSION_PROVENANCE`;
- `BLOCKED_HIGH_RISK_CHILD_THEME_OR_BUNDLED_PLUGIN_CONFLICT`;
- or another precise fail-closed blocker if evidence requires it.

Do not perform the theme upgrade in 2.17.

---

## 3. Hard no-mutation boundary

Do not modify, install, update, downgrade, activate, deactivate, replace, delete, or restore:

- Superio parent theme;
- Superio child theme;
- WP Job Board Pro;
- Paid Listings;
- WooCommerce;
- WordPress core;
- Elementor or other plugins;
- Raspitajse Communications/Commerce;
- cron/Action Scheduler state;
- deployment manifests/markers;
- database/business data;
- Hostinger scheduler configuration.

Do not create an implementation feature branch or staging deployment.

Do not run:

- WordPress theme/plugin updater;
- theme activation/switching;
- activation/migration hooks;
- broad WP-Cron;
- Action Scheduler queue runner;
- owned business hooks;
- real mail/SMTP/payment/refund;
- live exploit/security PoCs against staging.

Production filesystem/database/runtime/WordPress/scheduler access is forbidden.

If a WordPress bootstrap is needed for active-state/callback/template inspection, use a fully guarded read-only invocation with WP HTTP/mail/payment protections and report sanitized counters.

---

## 4. Fresh official version/changelog research

Fresh public read-only control-plane research is authorized.

Preferred evidence hierarchy:

1. official ThemeForest/Envato Superio item metadata/changelog;
2. official ApusThemes Superio documentation/release/update metadata;
3. official package metadata already legitimately available locally;
4. CVE/CNA/NVD/Wordfence/Patchstack/WPScan or comparable reputable security indexes;
5. secondary sources only as corroboration.

Rules:

- no account login, purchase, token, cookie, license-key, signed-URL or credential use;
- do not scrape private/authenticated package endpoints;
- do not contact vendor;
- do not trigger WordPress runtime update checks/heartbeats;
- record retrieval date/time, source role and exact claim supported;
- explicitly report disagreements between sources;
- if ThemeForest anti-bot blocks direct retrieval, use other authoritative/publicly available evidence and report the limitation rather than inventing facts.

Re-verify rather than assume:

- current latest Superio version;
- release/update date;
- public compatibility claims for WordPress/WooCommerce/Elementor if stated;
- all changelog entries from `1.3.18` through the current target, highlighting security/vulnerability/register/user-role/application/job submission/WooCommerce/Elementor/WPJBP-related entries.

Do not infer bundled plugin versions from the theme version number alone.

---

## 5. Current source/runtime/active-state verification

Establish from fresh evidence:

- parent theme path and source `style.css` version;
- deployed runtime parent `style.css` version/hash;
- child theme path, source/runtime version/hash;
- active WordPress `template` and `stylesheet` values;
- current parent-theme source file count/tree fingerprint;
- current parent-theme runtime file count/tree fingerprint;
- exact bounded inventory of source/runtime-only files relevant to upgrade provenance, especially `inc/plugins/*.zip`, updater/TGMPA/bundled-plugin metadata and generated/cache-like artifacts;
- whether current parent runtime divergence is intentional packaging residue, host-generated data, manual/vendor artifact residue, or an unresolved mutation.

Do not crawl unrelated home directories. Restrict inventory to the repository, deployed Superio parent/child roots, reasonable theme package/artifact locations, and known vendor-artifacts locations.

Classify each material runtime/source divergence as:

- `KEEP_RUNTIME_ONLY_ARTIFACT`;
- `NORMALIZE_ON_THEME_UPGRADE`;
- `OWNED_OVERRIDE_REQUIRED`;
- `UNKNOWN_BLOCKER`.

---

## 6. Security audit for current Superio 1.3.17

Search current authoritative/reputable vulnerability sources for Superio-specific vulnerabilities and relevant bundled-component advisories.

For every relevant advisory record report:

- CVE/advisory ID;
- affected product exactly as published;
- vulnerability class;
- affected version range;
- fixed/patched version if proven;
- CVSS/severity;
- authentication requirement;
- publication/latest-modification date;
- whether current `1.3.17` is affected;
- whether the prospective target is affected;
- confidence: CONFIRMED / CORROBORATED / CONFLICTING / UNVERIFIED.

Pay special attention to public Superio changelog entries labelled or implying:

- `Security`;
- `Vulnerability`;
- `Fixed Vulnerability`;
- `register user` / registration changes;
- any authentication/authorization/role handling changes.

Do not assume a changelog line itself maps to a CVE. Correlate only when authoritative evidence supports it.

Also distinguish clearly between:

- a Superio parent-theme vulnerability;
- a bundled WPJBP vulnerability;
- a bundled Paid Listings vulnerability;
- another bundled plugin issue.

The recently fixed WPJBP CVE must not be double-counted as a theme vulnerability merely because Superio bundles WPJBP.

---

## 7. Target artifact and bundled-plugin provenance

Determine whether a trustworthy official target Superio package can be inspected today.

Inspect, in order:

1. reasonable known local official ThemeForest/Envato/Superio package locations;
2. repository/runtime Superio bundled package locations;
3. public unauthenticated official vendor package endpoints only if provenance is unambiguous and access is allowed without login/license bypass.

For every candidate package:

- path/source;
- SHA-256;
- archive root/layout;
- version markers;
- ZIP safety/path traversal/duplicate/symlink result;
- provenance classification: `OFFICIAL_CLEAN`, `HISTORICAL_ONLY`, `CUSTOMIZED_CONTAMINATED`, `UNKNOWN`;
- eligibility for a future controlled upgrade.

If the current official target package requires a user-owned ThemeForest download and no clean package is already available, **do not bypass authentication**. Conclude `BLOCKED_MISSING_TRUSTED_SUPERIO_ARTIFACT` and state exactly what file/version the user must provide.

If a clean target package is available, inspect it text-only/read-only and determine exact bundled plugin archives/versions, especially:

- WP Job Board Pro;
- WP Job Board Pro WC Paid Listings;
- Elementor-related companions if bundled/required;
- other TGMPA-required/recommended plugins that could be changed by theme upgrade.

For each bundled plugin compare target-bundle version to the currently active staging version and classify:

- SAME;
- NEWER;
- OLDER — downgrade hazard;
- NOT BUNDLED;
- UNKNOWN.

**Critical gate:** a future Superio upgrade must never downgrade the accepted active WPJBP `1.2.86` or Paid Listings `1.0.19`, nor reintroduce contaminated vendor copies. The audit must identify exactly how the theme's TGMPA/bundled-plugin logic behaves when a bundled ZIP is older than an already-active plugin.

---

## 8. Parent-theme diff and upgrade-risk map

If a clean target package is available, compare current parent `1.3.17` to the target package at a decision-grade level.

Do not reverse-engineer every vendor file. Focus on material surfaces:

- `functions.php` / bootstrap;
- theme setup and constants;
- TGMPA/bundled-plugin registration;
- update-checker/updater behavior;
- WPJBP integration/template loader;
- WooCommerce templates/hooks;
- Elementor widgets/templates;
- authentication/register/login flows;
- job/candidate/employer dashboards;
- job submission/edit/application flows;
- package purchase views;
- email/template helpers only where theme-owned;
- AJAX/REST registration where theme participates;
- assets/markup/classes depended upon by child theme/custom CSS/JS.

Report meaningful additions/removals/renames/signature/markup changes and migration risk LOW/MEDIUM/HIGH/CRITICAL.

If no clean target package is available, report exactly which parts of the diff cannot be proven and do not fabricate them from changelog wording.

---

## 9. Child-theme and Raspitajse dependency inventory

Inventory all current child-theme and Raspitajse-owned dependencies on Superio parent behavior.

At minimum inspect:

- child-theme template overrides and whether corresponding parent files changed/vanished in target;
- child `functions.php` hooks/filters;
- child CSS/JS selectors tightly coupled to parent markup;
- WooCommerce template overrides in child theme;
- WPJBP template overrides under parent/child theme;
- login/register/dashboard/navigation overrides;
- job/candidate/employer/profile/listing/application/package templates;
- direct `superio_*`, Apus framework, Elementor widget, Redux/theme-option or parent helper calls from owned/custom code;
- any current business logic still incorrectly living in child theme instead of owned plugin layers.

For every material dependency classify:

- KEEP compatibility contract;
- REDESIGN into Raspitajse-owned plugin/layer;
- UPDATE CHILD OVERRIDE;
- DROP obsolete override;
- TEST ONLY.

Include target file/function/template counterpart if known and risk level.

Flag stale child overrides where the parent target materially changed the same template since `1.3.17`.

Do not move code in this task.

---

## 10. Compatibility matrix after WPJBP 1.2.86 normalization

The future Superio upgrade must preserve the accepted state from 2.16.

Audit exact risk and required acceptance for:

- WPJBP `1.2.86` remaining active and vendor-clean;
- Paid Listings `1.0.19` remaining active and vendor-clean;
- all 12 owned alert-management AJAX route variants remaining authoritative;
- alert REST staying disabled as intended;
- candidate→job owned evaluator remaining authoritative and vendor sender zero;
- employer→candidate surfaces remaining retired;
- candidate auto-expiry remaining disabled;
- owned job expiry and employer pre-expiry hooks remaining authoritative;
- fixed three-hook selective scheduler contract remaining unchanged;
- SenderPolicy/mail safety remaining authoritative;
- Commerce HPOS employer/order bridge remaining exact;
- canonical quota + immutable 30-day entitlement policy remaining separate from job-listing duration;
- current candidate/employer/job/application/package frontend flows continuing to render under target parent theme;
- WooCommerce `9.5.4` compatibility;
- WordPress `6.6.7` compatibility;
- current Elementor version/runtime compatibility and any target minimum/recommended version if official metadata publishes one.

Do not accept “theme says compatible” as sufficient. Define runtime acceptance that proves each material contract.

---

## 11. Deployment/source-of-truth architecture for the future upgrade

Because the current parent theme is outside the normal staging deploy allowlist and current source/runtime trees are not identical, the audit must design the correct reproducible deployment path before implementation.

Answer explicitly:

- should the clean parent Superio tree become Git-tracked source-of-truth at upgrade time?;
- which runtime-only bundled ZIPs, if any, should be tracked, ignored, or normalized away?;
- does the current deploy script need a narrowly scoped Superio allowlist extension for the future task?;
- how will source/runtime exact parity be proved after upgrade?;
- what EOL policy, if any, is required for official theme bytes? Do not assume the WPJBP vendor exception automatically applies to Superio;
- how will target package SHA/fingerprint be pinned and verified through Git round-trip and deployment?;
- how will the existing child theme remain untouched except for separately justified compatibility fixes?

Do not implement deploy-script or `.gitattributes` changes in this audit.

---

## 12. Required future controlled staging-upgrade plan

Produce an executable next-task plan with explicit gates.

### Pre-upgrade

Require at minimum:

- exact official target Superio version and clean package SHA;
- fresh staging/source/deploy baseline;
- secure restorable staging DB backup using the now-proven `mariadb-dump` primitive from 2.16;
- Git/file rollback point for parent/child theme;
- sanitized T0 business/options/roles/cron/AS/Communications/Commerce fingerprints;
- target package ZIP safety and Git EOL/round-trip proof;
- exact child-override compatibility map;
- bundled-plugin downgrade protection;
- runner shared-lock acquisition for runtime-critical window;
- mail/network/payment safeguards;
- production forbidden.

### Execution

- staging only;
- replace only the parent Superio tree from the clean pinned package plus separately reviewed minimal owned/child compatibility fixes if required;
- do not overwrite the child theme with vendor defaults;
- do not install/downgrade bundled WPJBP/Paid Listings automatically;
- do not use WordPress theme updater as the implementation mechanism unless a future task explicitly proves it preserves source/deploy parity; prefer the approved Git/deploy path;
- no theme switch/activation cycle merely to force migrations;
- no broad cron/Action Scheduler;
- no real mail/payment/external WordPress HTTP.

### Post-upgrade acceptance

Define at least:

1. source/runtime parent version and exact target tree parity;
2. child theme remains active and byte-stable except explicitly approved fixes;
3. no fatal/new attributable PHP warning during guarded bootstrap;
4. no unexpected DB/options/business migration;
5. WPJBP `1.2.86` and Paid Listings `1.0.19` remain exact, active and vendor-clean;
6. TGMPA/bundled-plugin logic does not downgrade or overwrite active newer plugins;
7. owned alert/security/expiry/scheduler/SenderPolicy contracts remain exact;
8. Commerce/HPOS/30-day policy remains exact;
9. candidate/employer dashboards and job/application/package views render correctly;
10. register/login/reset/account flows remain secure and functional;
11. job search/filter/detail, employer/candidate profiles, submission/edit/application flows remain functional;
12. WooCommerce cart/checkout/account/package screens relevant to Raspitajse render without theme-template regressions;
13. Elementor-powered critical pages/widgets render without missing classes/widgets/deprecations attributable to target;
14. responsive/mobile critical layouts have no blocking regression;
15. cron/non-allowlisted cron/Action Scheduler protected state reconciles exactly except individually documented expected migration;
16. no mail/SMTP/payment/external WP HTTP side effects during acceptance;
17. protected business fingerprints unchanged except specifically authorized migrations;
18. rollback is triggered by any critical compatibility/security/source-parity failure.

### Rollback

Plan must restore as one consistent unit when necessary:

- pre-upgrade parent theme source/runtime;
- matching deploy marker/manifest state;
- staging DB snapshot if any theme/plugin option/schema/data migration occurred;
- child theme state;
- active plugin/version state.

Never restore old parent files over partially migrated DB state without proving file-only rollback is sufficient.

---

## 13. Report requirements

Final report must contain, without PII/secrets:

- PASS/PARTIAL and exact readiness classification;
- verified current parent/child/runtime versions;
- verified current official target version/date;
- authoritative source ledger;
- current security/advisory matrix;
- target artifact/provenance result;
- bundled-plugin exact version matrix;
- current active-versus-bundled downgrade/conflict analysis;
- parent source/runtime divergence inventory;
- child-theme override/dependency matrix;
- Raspitajse-owned dependency matrix;
- deployment/source-of-truth recommendation;
- exact pre-upgrade/acceptance/rollback plan;
- all evidence gaps and blockers;
- confirmation that source/deploy/runtime/business state was not mutated;
- production touched: NO.

Do not publish raw credentials, SQL, user/order/application/message data, email addresses, saved queries, tokens, cookies, or authenticated URLs.

---

## 14. Exactly one proposed next task

If readiness is proven, propose exactly one next task similar to:

**Zadatak 2.18 — Controlled Superio staging upgrade to the pinned verified target with child-theme/bundled-plugin compatibility acceptance and rollback protection.**

If blocked, propose exactly one prerequisite task that resolves the blocker instead.

Do not create or execute the next task.

## Stop

Publish the final Zadatak 2.17 report through `codex-reports` and STOP.