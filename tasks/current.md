# Zadatak 2.51.2 — Real staging welcome-email delivery

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.1
Target: staging
Production: FORBIDDEN
Time budget: 15 minutes

## Goal

Send exactly one real candidate welcome email from staging to the existing configured staging safety recipient, which the owner has explicitly approved for this test.

This task proves dispatch through the real WordPress/PHPMailer transport. Inbox receipt remains a separate owner confirmation.

No application code, Git integration or deploy is authorized.

## Lean execution

Read only `tasks/README.md`, this task, the Task 2.51.1 latest report, and the exact staging mail-safety/template callbacks needed for this single send.

Do not create a harness, finalizer or evidence framework. Do not run broad mail, scheduler, database, HTTP or security inventories. Do not retest the other Task 2.51 messages. Stop if any command is silent for 10 minutes.

## Preflight

Confirm:

- `origin/staging`, clean local staging HEAD, live deploy marker and deployed Communications file are aligned at the declared baseline;
- WordPress reports the `staging` environment;
- the canonical login URL is still `/login-register/`;
- the staging mail configuration has a real transport configured;
- exactly one configured staging safety recipient resolves locally.

Never print, commit or report the recipient address.

Inspect the existing staging mail-safety implementation. Prefer its documented one-message/test bypass if present. If it has no safe process-local way to permit exactly one controlled message, return `BLOCKED: CONTROLLED_TRANSPORT_PATH_UNAVAILABLE`; do not persistently weaken or edit the mail guard.

## One controlled send

Reuse the already proven candidate welcome-email rendering path. A short process-local WP-CLI/PHP invocation is allowed; no generated script.

Requirements:

- create at most one synthetic candidate fixture only if the existing render path requires it;
- use clearly synthetic non-production data;
- generate the same welcome template proven in 2.51.1;
- require the staging login URL and reject production, package, pricing, cart, checkout and payment links before sending;
- enforce immediately before PHPMailer transport that To contains exactly the single configured staging safety recipient, with no Cc or Bcc;
- subject must include a short unique staging test token so the owner can identify it;
- call the real transport exactly once;
- no other email, external HTTP, payment, cron or Action Scheduler execution;
- remove any synthetic fixture by exact ID after the send and restore initial counts.

Do not include credentials, reset tokens, real candidate PII or production recipients.

## Result classification

- `SENT_AWAITING_INBOX_CONFIRMATION`: WordPress/PHPMailer accepted exactly one controlled message for transport and cleanup passed.
- `BLOCKED`: no message was sent, or recipient/transport/template/cleanup validation failed.
- Never claim inbox delivery merely from a successful `wp_mail()` return.

## Report

Publish one concise report containing:

- UTC send time and non-secret test token;
- From domain and transport type, without credentials;
- recipient count `1`, never the address;
- template/login-link validation;
- WordPress/PHPMailer acceptance result;
- send count, cleanup result and final Git/live/marker state;
- production untouched.

STOP after the report. Do not send a second message and do not start another task.
