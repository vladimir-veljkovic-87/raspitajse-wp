# Zadatak 1.96 — Compare WPJBP 1.2.66 as a non-authoritative upstream reference

Status: READY
Baseline: 642c8c8efb51a56449fd7048c71d3216590d52bf
Previous task: 1.95
Target environment: staging
Production: FORBIDDEN

## Mandatory execution preamble

Fetch `origin/codex-tasks` and `origin/staging`.

Read `tasks/current.md` and `tasks/README.md` **from `origin/codex-tasks` in full** before planning or inspecting source/artifacts. `tasks/README.md` is mandatory policy.

`codex-tasks` is READ-ONLY. Do not modify, commit to, push to, merge into, rebase, force-update, or otherwise write to `codex-tasks`.

Use exact `origin/staging` application baseline:

`642c8c8efb51a56449fd7048c71d3216590d52bf`

If `origin/staging` differs, STOP and report the mismatch.

Execute only 1.96, publish the final report through the existing `codex-reports` workflow, and STOP. Do not implement cleanup and do not begin 1.97.

---

## 1. Context

Zadatak 1.95 proved the owner-supplied artifact at:

`/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip`

is a coherent, independent WP Job Board Pro package, but version **1.2.66**, not 1.2.73.

It therefore cannot be called a clean authoritative 1.2.73 baseline. However, the owner explicitly authorizes using it as a **historical upstream reference** to reduce uncertainty, provided no automatic revert or cleanup decision is based only on version 1.2.66.

Public web research performed outside Codex also found no trustworthy detailed public changelog for WP Job Board Pro 1.2.66 → 1.2.73. Public vulnerability sources do confirm that 1.2.66 through 1.2.76 belonged to the same affected range for CVE-2024-12213, which is useful context but does not prove candidate-job files were unchanged.

This task must therefore combine:

1. exact local 1.2.66 artifact comparison;
2. repository history/provenance;
3. bounded public release/changelog/security evidence;
4. active runtime reachability;

and clearly separate **proven facts** from **inference**.

---

## 2. Goal

Use WPJBP 1.2.66 as a non-authoritative upstream reference and answer:

1. For the three candidate-job files, what is identical between clean-looking 1.2.66 and current 1.2.73-derived staging?
2. What changed between 1.2.66 and current staging?
3. Which differences are definitely Raspitajse-specific because Git history proves their introduction?
4. Which differences could be legitimate upstream 1.2.67–1.2.73 changes and therefore remain unresolved?
5. Does public changelog/release/security evidence indicate any material candidate-job subsystem change between 1.2.66 and 1.2.73?
6. Can any future cleanup be justified with high confidence despite the version mismatch?
7. If not, which residue should simply be left alone and which larger migration topic should be pursued next instead?

No application source changes in this task.

---

## 3. Exact target files

Compare only these three files unless a related file is required to explain a specific hunk:

1. `wp-content/plugins/wp-job-board-pro/includes/class-job-alert.php`
2. `wp-content/plugins/wp-job-board-pro/includes/email-templates-default/html-job-alert-notice.php`
3. `wp-content/plugins/wp-job-board-pro/templates/misc/my-jobs-alerts.php`

The supplied archive paths are:

1. `includes/class-job-alert.php`
2. `includes/email-templates-default/html-job-alert-notice.php`
3. `templates/misc/my-jobs-alerts.php`

Do not broaden into a full plugin diff.

---

## 4. Artifact handling

Reuse the exact owner-supplied archive:

`/home/u601262303/repo/vendor-artifacts/wp-job-board-pro.zip`

Expected SHA-256 from 1.95:

`fdf862722f2258c0cccc707a7f91f7055b3d468db7dfb144177bf001fa3e2190`

Before use:

- verify the SHA-256 is unchanged;
- verify plugin header and version constant still show 1.2.66;
- list archive entries and reuse the safe extraction rules from 1.95;
- extract only into a new task-private temp directory;
- never execute PHP from the archive;
- clean the temp directory exactly afterward.

If the ZIP hash changed, STOP.

---

## 5. Public web research — allowed but bounded

The owner explicitly authorizes **read-only public web research** for version-history evidence.

Allowed public targets:

- ApusThemes official documentation/product pages;
- ThemeForest product/changelog pages;
- public release notes/changelog mirrors that quote or preserve vendor release notes;
- Wordfence/NVD/CVE/VulDB security advisories;
- cached/indexed public pages that mention exact WP Job Board Pro versions 1.2.66–1.2.73.

Forbidden:

- downloading plugin ZIPs/binaries from the internet;
- logging into ThemeForest/Envato/vendor accounts;
- bypassing paywalls/authentication;
- using pirated/nulled package sites as source artifacts;
- installing any remote package;
- sending credentials, license keys or project secrets.

Public web evidence is supporting evidence only unless it is clearly an authoritative vendor changelog.

For every useful web source record:

- URL/domain;
- page title;
- whether it is vendor/marketplace/security/third-party;
- exact relevant version statement;
- confidence level.

Do not overstate absence of evidence as proof that no changes occurred.

---

## 6. Version interval evidence

Search specifically for version-level evidence for:

- 1.2.66
- 1.2.67
- 1.2.68
- 1.2.69
- 1.2.70
- 1.2.71
- 1.2.72
- 1.2.73

Determine whether any public source mentions changes to:

- job alerts;
- email alerts;
- frequency options;
- sender headers;
- email templates;
- alert management UI;
- localization;
- `_job_alert_send_email_time` or equivalent delivery state;
- candidate/job matching;
- registration/security that could indirectly affect these files.

If no detailed changelog exists, say exactly that.

Also record the known security fact that public advisories treat versions through at least 1.2.76 as affected by the same privilege-escalation issue. This may support continuity of one unrelated security behavior, but must **not** be used as proof that candidate-job files were byte-identical.

---

## 7. Exact 1.2.66 → current staging diff

Produce read-only exact diffs for the three target files.

For every semantic hunk classify its evidence status as one of:

### A — `CONFIRMED_RASPITAJSE`
Git history proves the current delta was introduced by a Raspitajse/project commit after the earliest repository import.

### B — `LIKELY_RASPITAJSE`
The 1.2.66 vendor-reference behavior plus current project history strongly suggests customization, but exact 1.2.73 upstream provenance is not independently proven.

### C — `POSSIBLE_UPSTREAM_1_2_67_TO_1_2_73`
The current behavior differs from 1.2.66 and repository history cannot prove whether the difference came from an upstream update or project customization.

### D — `UNCHANGED_FROM_1_2_66`
The relevant semantic behavior is identical between the supplied 1.2.66 artifact and current staging.

For each hunk report:

- 1.2.66 behavior;
- current staging behavior;
- repo-history evidence;
- public version-history evidence;
- active runtime reachability;
- security/privacy/business implication;
- evidence status A/B/C/D;
- KEEP / REDESIGN / DROP recommendation for the **Raspitajse business outcome**, not blind vendor parity.

---

## 8. `class-job-alert.php` focused questions

Resolve as far as evidence permits:

- Is `minute` frequency present in 1.2.66?
- What exact `biannually` label exists in 1.2.66?
- Does 1.2.66 contain hard-coded `From`?
- Does 1.2.66 contain hard-coded `Reply-To`?
- Does 1.2.66 contain HTML `Content-Type`?
- Does 1.2.66 contain active `error_log`/debug behavior?
- Does 1.2.66 write `_job_alert_send_email_time`?
- Does 1.2.66 use the same job-query/result-cap logic as current vendor sender?
- Which current differences are directly attributable to known Raspitajse commits 66f61889, 60217120, 043fcc59, e3e3c458 or other identified history?

Important safety rule:

Even if a bad behavior exists in 1.2.66, that does not make it desirable. Never recommend reintroducing hard-coded sender identities, PII/debug logging, unsafe test frequency behavior, or other known regressions merely because they appear in the historical vendor reference.

---

## 9. Email-template focused questions

For `html-job-alert-notice.php` compare exact structure and semantic content:

- branding;
- URLs;
- logo/icon assets;
- CTAs;
- visible domain;
- mailto target;
- placeholders;
- template structure.

Determine which current staging substitutions are definitely introduced by project commit history.

If the 1.2.66 template is generic/vendor-branded and current is Raspitajse-branded, that is strong evidence of project customization, but still distinguish whether the project customization predates the Git repository import.

Do not recommend visible-email regression. If future cleanup is desirable, prefer moving branding into owned/configured email content before restoring vendor files.

---

## 10. Management-template localization focused questions

For `my-jobs-alerts.php` compare exact UI labels and structure.

Determine:

- whether 1.2.66 uses English literals;
- whether current Serbian literals are proven project changes in Git history;
- whether current theme overrides shadow the vendor template;
- whether a future cleanup should use WordPress translations or an owned/theme override rather than vendor edits.

No source edits.

---

## 11. Cross-check current active architecture

Read-only reconfirm:

- direct vendor `WP_Job_Board_Pro_Job_Alert::send_job_alert_notice` final registration = 0;
- final WPJBP daily graph = exactly 5 unrelated callbacks at priority 10;
- owned hourly candidate-job evaluator event = exactly 1;
- continuation = normally 0;
- active candidate-job delivery goes through owned evaluator/delivery/query/mailer/SenderPolicy/Transport;
- owned query adapter deliberately uses WPJBP query infrastructure;
- owned mailer deliberately uses WPJBP email/render helpers;
- global getter still supports legacy compatibility while new create UI exposes four frequencies;
- 1.84 owned mutation/security callbacks remain authoritative;
- `_job_alert_send_email_time` is read-only in owned delivery;
- no active candidate-job behavior depends on vendor sender execution.

Do not execute daily/evaluator/continuation hooks.

---

## 12. Decision framework

At the end, choose one of these conclusions:

### Conclusion A — enough evidence for a small safe cleanup
Only if the exact hunk is `CONFIRMED_RASPITAJSE`, active owned behavior does not depend on it, and removing it cannot restore unsafe behavior.

### Conclusion B — evidence useful, but not enough for vendor restoration
Use 1.2.66 to document provenance, but keep current files unchanged because 1.2.67–1.2.73 upstream uncertainty remains.

### Conclusion C — no meaningful cleanup value
If the active architecture is already safe and remaining vendor differences are dormant/cosmetic/low-value, explicitly recommend parking this vendor-baseline thread and moving to the next business subsystem.

Do not manufacture a cleanup task merely to achieve vendor parity.

---

## 13. Staging protected-state observation

Read-only capture before/final:

- `origin/staging` SHA and deploy marker;
- environment = staging;
- daily event count/schedule/fingerprint;
- exact 5 daily callbacks;
- vendor candidate-job sender registration count;
- owned hourly evaluator event count/schedule/fingerprint;
- continuation count;
- published `job_alert` count/fingerprint;
- published legacy `candidate_alert` count/fingerprint;
- pending AS count/fingerprint;
- ID32733 status/attempts.

Expected baseline:

- staging SHA `642c8c8efb51a56449fd7048c71d3216590d52bf`;
- daily callbacks 5;
- vendor sender 0;
- hourly evaluator 1;
- continuation 0;
- `job_alert` 0;
- `candidate_alert` 4;
- ID32733 `pending/0`.

---

## 14. Zero-side-effect contract

Application/source/runtime effects must be:

- source writes: 0;
- application commits/branches/pushes/deploys: 0;
- WordPress business/data mutations: 0;
- cron schedule mutations/executions: 0;
- fixtures: 0;
- `wp_mail` / PHPMailer / SMTP: 0 / 0 / 0;
- payment calls: 0;
- Action Scheduler mutations/executions: 0;
- ID32733 executions: 0.

Allowed network activity is limited to bounded **public documentation/changelog/security research** plus ordinary Git control-plane/report publication. No application/vendor API calls and no package downloads.

Production filesystem/database/backups/application: FORBIDDEN.

---

## 15. Final report

Publish:

**Zadatak 1.96 — Compare WPJBP 1.2.66 as a non-authoritative upstream reference**

Report must include:

1. result PASS/PARTIAL with meaning;
2. baseline/final application SHA unchanged;
3. supplied ZIP SHA/version re-verification;
4. archive safety/temp cleanup proof;
5. public version-history sources searched and findings;
6. whether a detailed 1.2.66→1.2.73 changelog was found;
7. exact three-file 1.2.66/current hashes;
8. exact semantic diff inventory;
9. A/B/C/D evidence classification for each semantic hunk;
10. focused answers for minute/biannually/sender/content-type/debug/send_email_time;
11. email-template findings;
12. management-template localization findings;
13. repository-history attribution;
14. active runtime reachability/suppression proof;
15. owned WPJBP dependencies to keep;
16. final daily/hourly/continuation state;
17. protected job_alert/candidate_alert/AS fingerprints;
18. ID32733 `pending/0`;
19. mail/SMTP/payment/cron/AS counters;
20. production accessed/touched YES/NO;
21. explicit final conclusion A/B/C;
22. exactly one proposed next small task **only if justified**; otherwise explicitly recommend parking this vendor-cleanup thread and name the next business subsystem to audit.

Then STOP. Do not begin 1.97.
