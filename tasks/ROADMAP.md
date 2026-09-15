# Raspitajse production-readiness roadmap

Updated: 2026-09-15
Current application baseline: `origin/staging@7e8ff8bc0978fd72fe941e76e5490c86912137ef`
Target: free public launch
Production: FORBIDDEN until the explicit go-live task is approved

## 1. Product decisions

### Launch business model

- Candidate and employer registration are free.
- No payment gateway, paid checkout, paid subscription or paid package is required at launch.
- One employer may have at most **3 simultaneously active public job listings**.
- Draft, pending-review, expired, rejected and trashed listings do not consume an active slot.
- Editing an existing active listing does not consume another slot.
- When an active listing expires or is unpublished, its slot becomes available again.
- Administrators may manage listings without the employer quota blocking administrative operations.
- Candidates may create a profile/CV, search and apply without purchasing a package. Application abuse is controlled through validation, CAPTCHA/rate limits and moderation—not payment.
- Existing WooCommerce orders/products/history are preserved. No destructive migration is allowed.
- WooCommerce may remain installed as infrastructure while dependencies are retired, but cart/checkout/payment must not be part of the public free-launch journey.
- Future monetization remains possible, but it is outside the launch scope.

### Ownership direction

- The “three active jobs” rule is a Raspitajse business rule and must live in a Raspitajse-owned layer.
- Vendor theme/plugin code may provide UI and storage infrastructure, but must not become the authoritative owner of the quota.
- Legacy paid-package behavior is classified per hook/function as `KEEP`, `REDESIGN` or `DROP`.
- Required behavior is not removed until its free replacement is implemented, tested and confirmed.

## 2. Definition of production-ready staging

Staging is production-ready only when every mandatory launch gate below is PASS against one frozen release-candidate commit.

### Gate A — Source, environment and deploy integrity

- Git is the source of truth; staging contains no undocumented manual code delta.
- WordPress core, active theme, child theme and required plugins have a recorded compatibility/version matrix.
- Parent-theme updates do not overwrite child-theme/Raspitajse-owned code.
- Environment-specific secrets, domains, mail, CAPTCHA and cache settings are documented.
- Staging and production configuration differences are explicit and intentional.
- File ownership/modes are verified; no `777`, no inaccessible tracked assets.
- Deploy is changed-only, reproducible and produces a truthful marker.
- Rollback restores the previous release without changing Git history.
- Production data is never used for unsafe fixtures.

Exit: two consecutive staging deploy/rollback rehearsals from Git with identical hashes and no manual file edits.

### Gate B — Free access and employer quota

- New and existing employers receive free access without an order or package purchase.
- Employers with 0–2 active listings may create another listing.
- An employer with 3 active listings is blocked before a fourth becomes public.
- The rejection is clear, localized and does not lose entered draft data.
- Draft/pending/expired/rejected/trash statuses are counted correctly.
- Publish, unpublish, expiry, deletion and status transitions release/consume slots exactly once.
- Concurrent submissions cannot bypass the limit.
- Administrators can moderate/manage listings without corrupting employer quota.
- Candidates do not require packages for profile/CV/search/application.
- Package/cart/checkout/payment CTAs and redirects are absent from the free public journey.
- Existing paid order/product/history data remains intact and readable.

Exit: deterministic unit/runtime tests plus real-browser employer journey proving the fourth active listing is blocked and a freed slot can be reused.

### Gate C — Authentication, roles and real browser journeys

Candidate:

- register with real CAPTCHA;
- receive/complete the intended verification or welcome flow;
- login/logout/reset password;
- create/edit profile and CV;
- search/view jobs;
- apply once and see the correct status;
- cannot enter employer/admin-only areas.

Employer:

- register/login/reset password;
- create/edit company profile;
- create draft, submit and manage jobs;
- see applicants through authorized UI;
- cannot access another employer’s jobs/applicants;
- quota behavior matches Gate B.

Administrator:

- secure wp-admin access;
- user, employer, candidate, job and moderation operations;
- no hardcoded user-ID authorization;
- least-privilege capability checks.

Exit: Chrome/Edge plus Safari or equivalent mobile-browser run, with screenshots/network evidence and zero new PHP fatal/warning/error. Automated handler tests do not replace real CAPTCHA/session testing.

### Gate D — Core job-board business behavior

- Job creation, moderation, publication, editing, expiry and archive behavior are correct.
- Candidate application links the correct candidate, job and employer.
- Duplicate application behavior is intentional.
- Employer can view only authorized applicant data.
- Job alerts preserve the required business purpose and use a Raspitajse-owned/safe implementation.
- Expiry/notification workflows are idempotent and do not resend endlessly.
- Search/filter/location behavior works for launch content.
- Empty states and validation messages are usable and localized.

Exit: one controlled end-to-end candidate/employer/admin fixture journey with exact cleanup and audit evidence.

### Gate E — Email and background processing

- Registration, password reset, application, employer notification, job alert and expiry messages are inventoried as KEEP/REDESIGN/DROP.
- Required emails reach controlled real inboxes using production-like configuration.
- From/reply-to/domain authentication and bounce handling are verified.
- Templates contain correct data and no unintended PII.
- Business cron/Action Scheduler hooks have an owner, idempotency rule, retry policy and monitoring.
- Raspitajse-owned/protected actions are strict gates.
- Unrelated vendor actions are observed separately and cannot block unrelated business acceptance merely by existing.
- No broad queue runner is used in acceptance fixtures.
- WPForms vendor announcement action 32867 remains a separately planned DROP remediation.
- AIOSEO image-sitemap action 32868 remains a separately documented vendor-maintenance decision.

Exit: each required business event is triggered once in isolation, produces one expected outcome, and leaves no unexplained business action/claim.

### Gate F — Security and privacy

- HTTPS everywhere; secure cookies and production debug settings.
- Strong unique administrator credentials and 2FA.
- No shared/unnecessary administrators.
- Secrets are outside Git and rotated before launch where necessary.
- Dashboard file editor disabled.
- Unused plugins/themes removed after dependency proof.
- Active dependencies have no known critical/high unresolved vulnerability.
- Login, registration, reset and submission endpoints have CAPTCHA/rate limiting and enumeration protection.
- Uploads/CVs enforce type, size, authorization and non-executable storage behavior.
- Database/file permissions and wp-config protection are verified.
- Logs/reports do not expose passwords, tokens, email contents, CVs or candidate PII.
- Privacy notice, cookie behavior, consent, retention, export and deletion flows cover candidate CV/profile/application data.
- Legal text is reviewed for Serbia, Croatia/EU, Bosnia and other enabled launch markets.

Exit: security checklist PASS, vulnerability scan reviewed, privacy/data-lifecycle test PASS, and zero unresolved P0/P1 issue.

### Gate G — Performance, accessibility, localization and SEO

- Critical pages: home, jobs, job detail, register, login, candidate dashboard, employer dashboard and job submission.
- No 4xx/5xx or missing required assets.
- With the expected initial traffic, a bounded load test sustains at least 25 concurrent users without 5xx or data corruption.
- Mobile Core Web Vitals targets: LCP <= 2.5s, INP <= 200ms, CLS <= 0.1 on critical public pages.
- Dynamic uncached registration/dashboard/submission pages meet documented response-time budgets.
- WCAG 2.2 AA checks cover keyboard, focus, labels, form errors, contrast, reflow and status messages.
- Supported launch languages are complete and no critical UI string falls back unexpectedly. Planned content locales are Serbian, Croatian and English unless the launch scope is changed.
- Staging remains non-indexable; production robots/canonical/sitemap behavior is validated at release.
- AIOSEO behavior is tested as SEO infrastructure, not as a blocker for unrelated commerce/auth flows.

Exit: performance, accessibility, localization and SEO reports contain no launch-blocking defect.

### Gate H — Backup, recovery and observability

- Automated database and files/media backups exist outside the live web root.
- At least one full restore is performed on an isolated environment.
- Recommended initial targets: RPO <= 24 hours, RTO <= 4 hours, application rollback <= 15 minutes.
- Monitoring covers uptime, SSL, HTTP 5xx, PHP fatal, failed login bursts, mail failure, business scheduler failure, disk and database capacity.
- Alerts have a real owner and tested destination.
- A launch-day operational checklist and incident/rollback contacts exist.

Exit: restore drill PASS and a synthetic alert is received by the responsible operator.

### Gate I — Release candidate and go/no-go

- One release-candidate SHA is frozen.
- Zero open P0/P1 defects.
- P2 defects require documented impact, workaround, owner and explicit acceptance.
- All mandatory gates A–H are PASS.
- Production backup and rollback inputs are verified immediately before release.
- Production deployment is a separate explicitly approved task.
- Post-deploy smoke covers public pages, auth, job submission, application, admin and background health without creating unintended mail/payment/actions.

Exit: signed go/no-go report followed by a separately authorized production deployment.

## 3. Roadmap phases and task sequence

### DONE

#### Foundation and Zadatak 2.25

- staging safety/guard/evidence workflow;
- admin capability and registration infrastructure remediation;
- WP Job Board Pro asset permission remediation;
- Zadatak 2.25 custom commerce cleanup deployed at `ec97a6f76a3d393bac5e2629b977cf6a1fbe9bf4`;
- static 27/27, runtime 35/35 and three smoke targets PASS;
- production untouched.

Tasks 2.26–2.42 are closed recovery/finalization history. Do not reopen them without direct regression evidence.

#### Scheduler observation

- 32867: WPForms vendor announcement fetch, `DROP / PROVEN`;
- 32868: AIOSEO image-sitemap maintenance, vendor owner/purpose proven and enqueue provenance unresolved;
- neither is Raspitajse business state;
- both remain unexecuted and are not allowed to block unrelated launch gates merely by existing.

### SUPERSEDED BEFORE EXECUTION

#### Zadatak 2.45

The prior paid-package/checkout-oriented acceptance specification is superseded by the explicit free-launch decision. Do not execute `tasks/2.45.md`.

### NOW — Free launch architecture

#### Zadatak 2.46 — Read-only free-access impact map

Status: completed — PASS.

Inventory every current dependency on packages, checkout, orders and paid-listing entitlements; locate the current job-post limit/status counting paths; classify each behavior; define the smallest safe migration to free access with three active jobs per employer.

No code or staging mutation.

#### Zadatak 2.47 — Implement free access and three-active-job quota

Status: application completed and deployed at `7ce577b5802bcea143c693d426febf743d1ab340`. Static/runtime/concurrency and protected-state gates passed. The execution report is procedural PARTIAL only because evidence-directory permissions caused the original holder to exit after successful integration; do not rerun without direct regression evidence.

Implement the Raspitajse-owned policy on a feature branch, with concurrency-safe enforcement, status-transition tests, existing-user behavior and no public checkout dependency.

#### Zadatak 2.48 — Remove paid journey from public UI

Status: completed — PASS at `11fbf2014026a5e4486665a62bb42b4e636d9940` through lean recovery Task 2.48.1.

Remove/hide package, pricing, cart, checkout and payment redirects/CTAs from candidate/employer journeys while preserving historical WooCommerce data and required infrastructure.

### NEXT — Business journey proof

#### Zadatak 2.49 — Lean authentication and role smoke

Status: server smoke completed — PASS. Real CAPTCHA, password and browser-session checks remain a short owner-operated manual checklist.

Candidate/employer/admin endpoint, asset, role mapping, profile/dashboard and authorization smoke.

#### Zadatak 2.50 — Lean job lifecycle and application smoke

Status: server/business smoke completed — PASS. Visual browser confirmation remains a short owner check.

Employer creates three active jobs, fourth is blocked, candidate applies once, duplicate/cross-employer access is rejected, and a freed slot is reused.

#### Zadatak 2.51 — Lean transactional email smoke

Status: four-message smoke completed with one blocker: the welcome email lacked the asserted canonical login link. Lean repair continues as Task 2.51.1; the other three enabled messages must not be retested.

Required transactional messages are checked for recipient role, resolved data and staging links without sending real email.

#### Zadatak 2.51.1 — Welcome-email login link

Status: completed — PASS at `7e8ff8bc0978fd72fe941e76e5490c86912137ef`. The candidate welcome email now includes the dynamic canonical `/login-register/` URL through Raspitajse-owned code.

#### Zadatak 2.51.2 — Real staging welcome-email delivery

Status: current. Send exactly one welcome message through the real staging transport to the configured controlled safety recipient. Inbox receipt requires owner confirmation.

### RELEASE READINESS

#### Zadatak 2.52 — Dependency, update and security gate

WordPress/theme/plugin compatibility, vulnerabilities, file permissions, admin/2FA, secrets, uploads and hardening.

#### Zadatak 2.53 — Privacy and data lifecycle

Consent, retention, export/delete, CV/application access and legal-content checklist.

#### Zadatak 2.54 — Performance, accessibility, localization and SEO

Core Web Vitals, bounded load, WCAG 2.2 AA, SR/HR/EN content, robots/canonical/sitemap.

#### Zadatak 2.55 — Backup, restore and monitoring drill

Full restore, rollback timing, uptime/error/capacity/business-job alerts.

#### Zadatak 2.56 — Release rehearsal

Freeze candidate SHA; deploy, smoke, rollback and redeploy on staging without manual drift.

#### Zadatak 2.57 — Production go/no-go

Consolidate Gates A–I. No production mutation.

### PRODUCTION — EXPLICIT APPROVAL REQUIRED

#### Zadatak 2.58 — Production deployment

Created only after 2.57 PASS and explicit user approval. Includes verified backup, deploy, smoke, monitoring and rollback boundary.

## 4. Severity and blocking policy

- `P0`: security/data-loss/site-wide outage — always blocking.
- `P1`: critical launch journey broken, authorization failure, duplicate/corrupt business action — always blocking.
- `P2`: material defect with safe workaround — requires explicit acceptance.
- `P3`: cosmetic/minor issue — may be deferred with owner.

A vendor diagnostic or scheduler row is not automatically P1. Severity is based on user/business impact and containment.

## 5. Task discipline

- New tasks follow the mandatory lean execution policy in `tasks/README.md`: one bounded outcome, minimal relevant reads, reuse of passed evidence, no generated monolithic finalizers, and locks only around actual staging mutation.
- `tasks/current.md` is the sole executable pointer.
- Every task maps to one roadmap phase and has explicit exit criteria.
- Completed milestones remain closed.
- Incidental findings do not expand scope automatically.
- Application changes use feature branches from the exact declared staging baseline.
- Reports go only to `codex-reports`.
- Production is forbidden unless the task explicitly authorizes it.
- Codex stops after publishing the report and never invents the next task.
