# Zadatak 2.18 — Verify the user-provided official Superio 1.3.37 package and close target bundle/TGMPA/child-theme provenance

Status: READY
Baseline: 5416eb4d327fe44f503591d86577e210560339c1
Previous task: 2.17
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch fresh `origin/codex-tasks`, `origin/codex-reports`, and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before inspecting the supplied artifact, extracting ZIPs, bootstrapping WordPress, or doing any public research. Treat `codex-tasks` as READ-ONLY.

Read the final Zadatak 2.17 PARTIAL report in full and the final Zadatak 2.16 PASS report where needed for the accepted active WPJBP/Paid Listings state.

Verify fresh `origin/staging` is exactly:

`5416eb4d327fe44f503591d86577e210560339c1`

Verify the live staging deploy marker is the same commit and the primary staging worktree is clean/on `staging`. If any baseline differs, STOP and report the mismatch. Do not silently rebase or widen this task.

The user has supplied this exact local artifact path:

`/home/u601262303/repo/themeforest-NNoRVYjo-superio-job-board-wordpress-theme-wordpress-theme.zip`

Require that exact file to exist as a regular non-symlink file before continuing. If it is missing, replaced by a symlink, unreadable, empty, or materially changes while being inspected, STOP and report the exact bounded prerequisite. Do not search broadly for substitutes and do not use credentials or authenticated ThemeForest URLs.

Execute only Zadatak 2.18. This is a **read-only artifact/provenance and compatibility audit**. Expected application source changes: `0`. Expected staging deploys: `0`. Publish the final report through the existing `codex-reports` workflow and STOP. Do not begin 2.19 automatically.

---

## 1. Accepted context

Zadatak 2.17 established:

- active Superio parent `1.3.17`, child `1.0.0`;
- current official Superio target `1.3.37` from official ThemeForest/Apus changelog evidence;
- current staging WordPress `6.6.7`, WooCommerce `9.5.4`;
- active WP Job Board Pro `1.2.86` and Paid Listings `1.0.19` are the accepted normalized targets from Zadatak 2.16;
- current runtime Superio 1.3.17 contains historical bundled ZIPs including WPJBP `1.2.72`, Paid Listings `1.0.15`, Apus Framework `2.3`, Slider Revolution `6.7.18`, and WP Private Message `1.0.7`;
- active Apus Framework `2.3` and Slider Revolution `6.7.18` have confirmed security exposure and increase upgrade urgency;
- current parent runtime has 10 runtime-only archives and 230 EOL-only source/runtime differences, but no material common-file customization was proven after normalized comparison;
- parent `wp-content/themes/superio/` is outside the current normal staging deploy allowlist;
- target-side bundle/TGMPA/child-theme compatibility remained blocked only because no trusted 1.3.37 package bytes were available.

The purpose of this task is to close that artifact gap and produce a decision-grade target map. Do **not** update the theme in this task.

---

## 2. Hard no-mutation boundary

Do not install, update, switch, activate, deactivate, replace, delete, or deploy any theme or plugin.

Do not modify:

- `wp-content/themes/superio/`;
- `wp-content/themes/superio-child/`;
- WP Job Board Pro;
- Paid Listings;
- Apus Framework;
- Slider Revolution;
- WP Private Message;
- Elementor;
- WooCommerce;
- WordPress core;
- Raspitajse-owned plugins;
- `.gitattributes`;
- deployment scripts/allowlists/manifests/markers;
- database/options/business data;
- cron or Action Scheduler state;
- Hostinger scheduler.

Do not create an application feature branch, source commit, staging deploy, installer/upgrader action, TGMPA install/update action, plugin lifecycle action, broad WP-Cron run, Action Scheduler execution, or owned business-hook execution.

Production filesystem/database/runtime/scheduler access is forbidden.

Artifact extraction is allowed only into a task-private scratch directory outside the repository and web root. Extracted PHP must be treated as inert text/files and must not be executed or included by PHP/WordPress.

---

## 3. Supplied outer-package integrity and provenance

Inspect the exact supplied file in place, read-only.

Record only non-secret artifact metadata:

- exact path;
- byte size;
- modification timestamp if available;
- SHA-256;
- file type;
- ZIP CRC result;
- complete archive entry count;
- top-level layout;
- duplicate entry count;
- absolute/drive/path-traversal entry count;
- symlink entry count;
- encrypted-entry count if detectable.

Do not infer that the filename alone proves provenance. Classify it initially as `USER_PROVIDED_THEMEFOREST_CANDIDATE` and raise it to a trusted/eligible classification only if package structure, product/version markers, official documentation expectations, and target bytes are internally consistent.

The task may refresh official **public read-only** ThemeForest/Apus product/changelog/documentation evidence solely to confirm that `1.3.37` remains the official target and that the expected purchased-package layout is consistent. Do not log in, use tokens/cookies/licenses, or download a second authenticated package.

If the current official release is no longer `1.3.37`, do not silently treat the supplied package as current. Report the version drift and classify whether this package is historical but trustworthy or whether a newer official user-owned package is required.

---

## 4. Locate and verify the installable theme artifact

The outer ThemeForest package may contain documentation, licenses, child theme, plugin bundles, demo content, and one or more theme ZIPs.

Locate the exact installable Superio parent theme package expected by vendor documentation, typically `superio_theme.zip` or an equivalent unambiguous artifact.

For the chosen parent-theme ZIP:

- record its path inside the outer archive;
- SHA-256;
- archive root/layout;
- CRC/path traversal/duplicate/symlink/encryption checks;
- `style.css` theme name and exact version;
- any authoritative theme constants/version markers;
- file/dir counts;
- top-level tree fingerprint using a deterministic path+byte-hash method;
- presence and layout of `inc/plugins/` and other bundled package locations.

The parent package must identify itself as Superio `1.3.37` to satisfy the accepted target. If it does not, STOP the readiness conclusion and report the exact version found.

Also identify any bundled `superio-child` ZIP/package and record its version/hash/layout if present. Do not assume the bundled child should replace the active customized child theme.

---

## 5. Exact target parent-tree inventory

Extract the installable Superio 1.3.37 parent ZIP into task-private scratch only.

Produce a deterministic inventory sufficient for a future controlled replacement:

- exact target file count and directory count;
- target tree fingerprint;
- PHP file count and `php -l` result for all target PHP files, using inert local lint only;
- site-specific/Raspitajse marker scan;
- unexpected secrets/private-key/credential marker scan;
- production/staging hard-coded domain/path scan where relevant;
- executable/symlink/special-file findings;
- generated/cache/log/archive residue inside the parent package.

Do not edit official bytes to make lint/diff/EOL tools quiet.

Compare current source/runtime Superio 1.3.17 to the clean target 1.3.37 at a decision-grade level:

- added/deleted/changed path counts;
- material code/content changes vs EOL-only changes;
- current runtime-only archives that disappear, persist, or are replaced in target;
- any current common-file customization that the clean target would overwrite;
- changed interfaces/hook names/templates/classes/functions that Raspitajse-owned or child-theme code consumes.

Do not reverse-engineer irrelevant vendor internals. Focus on migration and compatibility seams.

---

## 6. Git EOL/canonical-byte round-trip audit

Because 2.14/2.15 proved that vendor package EOL can materially affect byte parity, explicitly test the Superio 1.3.37 target against the repository's current `.gitattributes` **without modifying `.gitattributes`**.

Using a task-private disposable Git worktree/index or equivalent non-mutating test, establish:

- how many target Superio files would change bytes under current Git clean/smudge rules;
- path/file-type distribution of EOL-sensitive files;
- whether normalized LF should be the canonical repository representation or whether exact official package bytes need a future narrow `wp-content/themes/superio/** -text !eol` exception;
- whether choosing one policy would create unnecessary churn or undermine deterministic source/runtime reconstruction.

This task may recommend an exact future `.gitattributes` rule, but must not change the repository.

The report must distinguish:

- `OFFICIAL_PACKAGE_BYTES` fingerprint;
- `CURRENT_GIT_CANONICAL` materialized fingerprint if different;
- proposed future canonical source policy and rationale.

---

## 7. Target bundled-plugin provenance matrix

Inventory every plugin ZIP/package included by Superio 1.3.37, especially:

- Apus Framework;
- Slider Revolution;
- WP Job Board Pro;
- WP Job Board Pro WC Paid Listings;
- WP Private Message;
- any newly added or removed bundled plugin.

For each target bundled plugin record:

- archive path;
- SHA-256;
- archive safety result;
- plugin slug/root;
- exact version from header/constant where possible;
- target package classification;
- current active staging version;
- relation: SAME / NEWER / OLDER / NOT ACTIVE / NOT CURRENTLY BUNDLED;
- security relevance from the accepted 2.17 advisory evidence, refreshed only if necessary;
- future action: `KEEP_ACTIVE`, `UPGRADE_SEPARATELY`, `DO_NOT_DOWNGRADE`, `TARGET_BUNDLE_ACCEPTABLE`, `BLOCKED`, or another precise classification.

Critical invariant:

**A Superio upgrade must never downgrade or overwrite the already accepted active WPJBP `1.2.86` or Paid Listings `1.0.19`.**

If the 1.3.37 bundle contains older versions, prove whether replacing the parent theme alone leaves active plugin directories untouched and identify every TGMPA/manual path that could still offer or trigger an older install. Future execution must explicitly suppress or avoid such downgrade paths.

For Apus Framework and Slider Revolution, determine whether the 1.3.37 bundled versions are beyond the confirmed vulnerable ranges from 2.17. If the package still bundles an affected version, classify the theme upgrade as insufficient to close that security exposure and specify the separate clean target requirement.

Do not install or execute any bundled plugin in this task.

---

## 8. Target TGMPA / plugin-management behavior

Inspect Superio 1.3.37 target source statically for TGMPA or equivalent bundled-plugin registration/update behavior.

For every local/commercial bundled plugin establish:

- registered slug/name/source path;
- required vs recommended;
- forced activation/deactivation flags if any;
- declared minimum/recommended version if any;
- whether update-required logic can consider an installed newer version outdated;
- whether source replacement can overwrite active plugin directories automatically on normal theme bootstrap;
- whether any admin action or bulk installer can reinstall older target bundle bytes;
- whether target theme activation or ordinary bootstrap mutates plugin installation state.

Produce an explicit downgrade-prevention rule set for the future controlled upgrade.

Any target behavior that can automatically overwrite active WPJBP 1.2.86 / Paid Listings 1.0.19 merely by deploying/bootstrapping the theme is a **critical blocker** unless a safe bounded mitigation can be proven without vendor hacks.

---

## 9. Child-theme compatibility mapping against exact 1.3.37 counterparts

Inventory all 22 current `superio-child` files and classify them against exact target counterparts where applicable.

At minimum cover the high-risk dependencies identified in 2.17, including:

- `template-paid-listings/choose-package-form.php`;
- `template-paid-listings/user-packages.php`;
- Elementor/Paid Listings user package widget override(s);
- any WPJBP templates or account/dashboard forms;
- current `functions.php` customizations;
- CSS/SCSS/JS dependencies on parent markup/classes;
- translation/localization overrides;
- copied parent functions/classes/templates that may now be stale.

For each child file classify:

- `KEEP_AS_IS`;
- `UPDATE_CHILD_OVERRIDE`;
- `REDESIGN_TO_OWNED_LAYER`;
- `DROP_OVERRIDE_USE_PARENT`;
- `TEST_ONLY`;
- `BLOCKED_BY_RUNTIME_UI_TEST`.

Record risk LOW / MEDIUM / HIGH / CRITICAL and exact target-side evidence.

Do not automatically copy target parent files into the child theme and do not modify the child theme in this audit.

---

## 10. Security and release-gap mapping

Using the exact target package plus existing 2.17 public evidence, produce a concrete mapping from `1.3.17 -> 1.3.37` for security-relevant surfaces:

- parent Superio CVE/security fixes;
- Apus Framework target version and CVE-2024-12296 status;
- Slider Revolution target version and CVE-2024-8107 / CVE-2025-9217 / CVE-2025-10249 status;
- WPJBP target-bundle version vs active accepted 1.2.86;
- Paid Listings target-bundle version vs active accepted 1.0.19;
- WP Private Message target-bundle version vs known advisory status;
- any additional target-bundled plugin with a known material advisory found in fresh reputable sources.

Do not claim that generic changelog words `Security` or `Vulnerability` map to a CVE without direct evidence.

Output a clear residual-risk table: what the Superio 1.3.37 upgrade would fix, what remains vulnerable/outdated even after that parent upgrade, and what must be handled as a separate plugin upgrade.

---

## 11. Deployment architecture and rollback readiness design

This task must design, but not execute, the future controlled Superio upgrade.

Determine the minimal source/deploy changes that a future implementation task would require, including:

- exact `wp-content/themes/superio/` deploy allowlist extension, if needed;
- deletion boundary so files removed upstream are removed without touching `superio-child` or unrelated themes;
- chosen EOL canonicalization policy;
- whether bundled ZIPs should remain committed as official target bytes or be excluded from source/runtime and why;
- source/runtime/tree parity definition for the parent theme;
- protection of active WPJBP/Paid Listings from bundled downgrade;
- whether Apus Framework/Slider Revolution must be upgraded in the same transaction, a prior transaction, or a later transaction based on exact target bundle versions/security;
- child-theme updates that must be included before parent switch/replacement vs can follow later;
- use of the already accepted secure staging DB backup primitive from 2.16;
- theme file backup/Git rollback point;
- runner lock and no-broad-cron boundary;
- no production touch.

A future upgrade must use clean whole-tree parent replacement rather than transplanting selected vendor hunks.

---

## 12. Required future acceptance matrix

Design a concrete post-upgrade matrix, at minimum covering:

1. exact Superio parent version/source/runtime/tree parity;
2. active stylesheet remains `superio-child` and parent template remains `superio`;
3. child override compatibility with exact 1.3.37 templates/classes;
4. WPJBP remains exactly active 1.2.86 unless a separately authorized newer clean target is introduced;
5. Paid Listings remains exactly active 1.0.19 unless separately authorized;
6. no TGMPA/bundle downgrade or reinstall action occurred;
7. Apus Framework/Slider Revolution security target state is explicitly accepted, not assumed;
8. Raspitajse alert/security/communications/candidate-expiry/job-expiry/SenderPolicy contracts remain exact;
9. Commerce/HPOS/30-day entitlement policy remains exact;
10. employer/candidate dashboards and account flows render without missing templates/classes;
11. job search/detail/submission/edit/application flows remain functional;
12. package listing/selection/purchase UI remains compatible;
13. Elementor widgets used by the site load without fatal/deprecated API break attributable to target;
14. WooCommerce templates and checkout/account surfaces remain compatible with Woo 9.5.4;
15. header/footer/navigation/mobile/menu/sticky-header/theme-options surfaces remain intact;
16. no unexpected DB/options/business mutation;
17. cron/Action Scheduler/protected ID32733 unchanged except explicitly justified target behavior;
18. real mail/network/payment effects remain zero during guarded acceptance;
19. HTTP UI checks that are blocked by Hostinger environment are classified as environment blockers rather than falsely passed;
20. complete rollback can restore DB + exact old parent/runtime/deploy state if any critical gate fails.

---

## 13. Decision classification

End with exactly one of these high-level outcomes:

- `READY_FOR_CONTROLLED_SUPERIO_STAGING_UPGRADE`
- `READY_AFTER_PRE_UPGRADE_CHILD_THEME_FIXES`
- `READY_AFTER_SEPARATE_BUNDLED_PLUGIN_SECURITY_UPGRADES`
- `BLOCKED_TARGET_ARTIFACT_INVALID_OR_WRONG_VERSION`
- `BLOCKED_CRITICAL_TGMPA_DOWNGRADE_RISK`
- `BLOCKED_UNRESOLVED_CHILD_THEME_INCOMPATIBILITY`
- `BLOCKED_OTHER_<precise_reason>`

A READY outcome must name the exact future target SHA-256/fingerprints and the exact set/order of components authorized for the implementation task. It is not permission to perform the upgrade inside 2.18.

---

## 14. Report requirements

The final report must include:

- result PASS/PARTIAL with decision classification;
- exact baseline/deploy state and zero-mutation accounting;
- supplied outer ZIP SHA-256 and provenance classification;
- chosen Superio parent ZIP SHA-256/version/tree fingerprint;
- target bundled-plugin version/hash matrix;
- TGMPA downgrade analysis;
- exact child-theme compatibility matrix;
- EOL/Git round-trip finding and recommended canonical policy;
- security residual-risk matrix;
- future deploy/backup/rollback architecture;
- exact future acceptance matrix;
- exactly one proposed next task;
- confirmation production was not touched.

Do not include secrets, credentials, SQL, user/order/application/message content, private ThemeForest account data, or PII.

## Stop

Publish the Zadatak 2.18 report through the existing `codex-reports` workflow and STOP. Do not start or create the proposed next task automatically.