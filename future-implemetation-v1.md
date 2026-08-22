# Raspitajse.com — Future Implementation Plan v1

Last curated: 2026-08-22

This document describes the planned work **after the current staging stabilization/refactor program is complete**. It is intentionally separate from `CODEX_PROJECT_CONTEXT.md`, which explains the current architecture, verified audit findings, risks, and active recovery work.

The goal is to move Raspitajse.com from a technically stabilized WordPress platform into a controlled production release, then into product growth and ongoing development.

## 1. Stage 1 — Production release readiness

Do not move to production simply because staging is functional. Production readiness requires a deliberate release gate.

### Required preparation

- freeze the staging release candidate;
- confirm repository and staging runtime state are known and reproducible;
- prepare a final `staging -> production` code diff;
- identify any database/configuration changes separately from code changes;
- prepare a tested backup and rollback procedure;
- define a release window and owner/responsibility for rollback decisions;
- prohibit unrelated cleanup or feature work during the release window.

### Final pre-production regression

At minimum verify on staging:

- candidate registration, login, profile and dashboard;
- employer registration, login, profile and dashboard;
- candidate/employer role separation;
- job creation, editing, publishing and expiry lifecycle;
- applications and application statuses;
- package purchase and WooCommerce Paid Listings entitlements;
- employer access to candidate/profile data according to entitlement;
- private messaging/chat permissions;
- outbound mail transport and important templates using safe staging fixtures;
- WP-Cron and Action Scheduler health;
- checkout/payment integration without creating real unintended transactions;
- no staging-to-production URL, email or callback leakage.

### Production release gate

Production deployment should proceed only when:

- the release candidate is fixed and reviewed;
- rollback is practical and understood;
- all critical staging regression checks pass;
- no unresolved scheduler/mail/business-state recovery work remains;
- vendor customizations needed by the business have either been preserved or safely replaced;
- no unknown high-risk change is included in the production diff.

## 2. Stage 2 — Controlled production deployment

The first production release after this refactor should be treated as a controlled migration, not a normal casual deploy.

### Deployment principles

- deploy code deliberately and in small, known scope;
- do not combine deployment with unrelated database cleanup;
- do not perform automatic or ambiguous migrations without explicit review;
- verify production environment before any production-specific operation;
- use a documented rollback point;
- preserve user, order, application, message and payment data.

### Immediate production smoke tests

Immediately verify:

- homepage and primary navigation;
- candidate login/dashboard/profile;
- employer login/dashboard/profile;
- job listing/detail pages;
- job application path;
- package/entitlement visibility;
- WooCommerce checkout pages;
- messaging availability;
- mail transport health;
- cron/Action Scheduler health;
- PHP/application logs for new fatal errors.

If a critical invariant breaks, rollback should be preferred over live debugging of a broad release.

## 3. Stage 3 — Production soak period

After release, allow a stabilization period before beginning another large refactor.

### Monitor during soak

- PHP/application errors;
- WordPress scheduled events;
- Action Scheduler queue growth/failures;
- WooCommerce order/payment state handling;
- outbound email errors;
- messaging errors;
- candidate/employer authentication problems;
- unusual performance or cache behavior;
- unexpected staging/production cross-links;
- support reports that indicate a regression.

### Soak rule

Avoid large architecture changes during the soak period unless fixing a confirmed regression.

The purpose is to establish a known-good production baseline.

## 4. Stage 4 — Remove temporary recovery mechanisms

Temporary safety controls introduced during recovery must not become permanent accidental architecture.

### Scheduler recovery cleanup

After scheduler health is proven:

- establish a reliable long-term cron triggering strategy;
- verify traffic-independent execution where appropriate;
- confirm WP-Cron and Action Scheduler remain healthy over time;
- remove the temporary staging cron recovery guard only in a dedicated tested task;
- verify removing the guard does not create burst execution or duplicate processing.

### Communications cleanup

After owned transport is fully proven:

- complete inventory of remaining legacy child-theme/vendor email behavior;
- confirm all required behavior exists in Raspitajse-owned code;
- keep a rollback mechanism until confidence is high;
- physically remove legacy SMTP/mail transport only after parity testing;
- continue fail-safe environment separation for staging.

## 5. Stage 5 — Platform update and hardening

Once functional parity and production stability are established, harden the platform.

### WordPress/plugin/theme maintenance

- define a repeatable WordPress core update strategy;
- define plugin/theme update policy;
- review WP Job Board Pro and Paid Listings updates against known customizations;
- keep vendor customization removal separate from vendor upgrades when possible;
- test upgrades on staging before production.

### Security and permissions

- audit WordPress roles and capabilities;
- verify candidate/employer/admin access boundaries;
- review privileged AJAX/API endpoints;
- review upload/file permissions;
- review admin access restrictions;
- remove obsolete diagnostics/debug utilities;
- verify secrets remain outside version control;
- confirm backup/restore process works in practice.

### Performance

- audit LiteSpeed cache/object cache behavior;
- remove unnecessary cache-busting patterns;
- inspect expensive queries and repeated hooks;
- optimize assets and page loading;
- review Core Web Vitals after functional work is stable.

### Operational visibility

Establish useful monitoring for:

- PHP fatal errors;
- cron failures/backlog growth;
- Action Scheduler failures;
- mail transport failures;
- WooCommerce operational failures;
- deployment state;
- critical user-flow availability.

## 6. Stage 6 — Product and UX development

Once the platform is technically reliable, engineering priority should move from repairing WordPress internals to improving the Raspitajse product.

### Candidate experience

Prioritize:

- clear registration/onboarding;
- easy profile completion;
- transparent employer/job information;
- job search and filtering;
- application status tracking;
- notifications that are relevant and understandable;
- direct communication with employers;
- clear explanation of documents and next steps;
- mobile-first usability.

### Employer experience

Prioritize:

- simple employer onboarding;
- verified employer identity/status;
- clear job publishing workflow;
- candidate discovery/database access according to package;
- application management;
- messaging/contact tools;
- clear package/entitlement information;
- useful job-performance/application analytics where justified.

### Trust and safety positioning

Raspitajse.com should emphasize:

- verified employers;
- transparent employment conditions;
- safer overseas/seasonal employment discovery;
- direct employer/candidate communication;
- visible application status;
- document/HR support where included in the commercial package.

Do not prioritize superficial “AI” positioning ahead of trust, clarity and reliable workflows.

## 7. Stage 7 — Package and monetization refinement

Once core UX is stable, refine commercial structure using observed user behavior.

Potential areas:

- employer job-posting packages;
- candidate database access;
- premium employer visibility/features;
- Partner/support package benefits;
- document assistance;
- HR support;
- application-management tools;
- employer verification/value-added services.

Any entitlement change must be regression-tested against WordPress roles, WooCommerce orders and Paid Listings behavior.

## 8. Stage 8 — Growth and acquisition

After product reliability and positioning are strong, scale acquisition.

### Candidate acquisition

- SEO job/seasonal-work landing pages;
- country/location-specific content;
- practical guides for working abroad;
- alerts and re-engagement;
- referral mechanisms where useful;
- social/content distribution.

### Employer acquisition

- targeted outbound employer acquisition;
- clear employer landing pages;
- sector/country-specific outreach;
- simple employer onboarding demos;
- case studies/testimonials after enough real usage exists;
- conversion tracking from lead -> employer -> job post -> hire/application.

### Funnel analytics

Track meaningful product metrics rather than vanity traffic alone:

- candidate registration completion;
- profile completion;
- application conversion;
- employer registration completion;
- first job-post conversion;
- employer package conversion;
- candidate-employer interaction;
- repeat employer activity;
- alert engagement;
- successful application/hiring signals where measurable.

## 9. Stage 9 — Automation and AI where justified

Automation should be introduced after the underlying workflows and data are trustworthy.

Good future candidates include:

- candidate/job matching assistance;
- employer candidate recommendations;
- structured job-content quality checks;
- support/document guidance;
- anomaly detection for incomplete or suspicious job listings;
- operational summaries for admins;
- content assistance.

AI should augment clear business workflows rather than hide broken or ambiguous ones.

Every automated decision affecting candidate/employer visibility, application handling, employment opportunity or payment entitlement should remain explainable and testable.

## 10. Stage 10 — Continuous delivery model

After the major refactor and first controlled production release, move toward a sustainable engineering cycle.

Preferred lifecycle:

`small feature -> feature branch -> automated/static checks -> staging deploy -> targeted regression -> production release -> monitoring`

Avoid returning to:

- direct production edits;
- large untracked child-theme changes;
- vendor edits without ownership mapping;
- broad mixed refactors;
- undocumented server changes.

## 11. High-level implementation order

The intended sequence is:

1. finish current staging stabilization/refactor;
2. final regression and production release readiness;
3. controlled production deployment;
4. production soak;
5. remove temporary recovery mechanisms;
6. core/plugin/theme maintenance and hardening;
7. candidate/employer UX improvements;
8. package/monetization refinement;
9. acquisition and funnel analytics;
10. automation/AI where product data and workflows justify it;
11. ongoing small-batch delivery and optimization.

## 12. Definition of success

The technical program is complete when Raspitajse.com is no longer primarily consuming engineering effort on emergency WordPress cleanup.

A successful end state means:

- staging and production are clearly separated;
- production releases are reproducible and reversible;
- scheduled processing is reliable;
- communications are owned and testable;
- business logic is increasingly outside fragile vendor/theme locations;
- candidate/employer/payment/application behavior has regression coverage;
- plugin/core updates are manageable;
- monitoring catches operational regressions;
- most engineering time can be spent improving the product, employer acquisition, candidate experience and growth rather than repairing infrastructure.

## Related project documents

Before implementing items from this plan, Codex should also read:

- `AGENTS.md` — safety and operating rules;
- `CODEX_PROJECT_CONTEXT.md` — current technical audit and verified project state;
- `CODEX_WORKFLOW.md` — autonomous task/risk workflow;
- latest numbered report from `codex-reports` — current execution checkpoint.

This document is a roadmap, not blanket authorization to execute high-risk or production work autonomously.