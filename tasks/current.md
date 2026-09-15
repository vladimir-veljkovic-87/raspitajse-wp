# Zadatak 2.51.3 — Retry one real staging welcome email

Status: READY
Baseline: 7e8ff8bc0978fd72fe941e76e5490c86912137ef
Previous task: 2.51.2
Target: staging
Production: FORBIDDEN
Time budget: 10 minutes

## Goal

Retry the one real candidate welcome-email delivery that Task 2.51.2 stopped before `wp_mail()`.

The prior failure is a harness-only token case mismatch. No message was sent, and SMTP, the single configured staging safety recipient, template, login path, Git/live alignment and clean state already passed.

No code change, deploy, Git integration or persistent configuration change is authorized.

## Lean retry

Read only `tasks/README.md`, this task and the exact 2.51.2 report. Do not repeat broad preflight, template inventories, other email tests, scheduler checks or security checks. Do not create a harness/finalizer/evidence framework.

Reconfirm only:

- HEAD, `origin/staging` and deploy marker equal the declared baseline;
- WordPress environment is `staging`;
- one configured staging safety recipient and SMTP configuration still resolve.

Never print or report the recipient or credentials.

## Correct the process-local token assertion

Generate the non-secret token as exactly eight uppercase hexadecimal characters, for example:

`strtoupper(bin2hex(random_bytes(4)))`

Validate it with `/^[A-F0-9]{8}$/`. This is test-process logic only; do not modify application code.

## One controlled send

Perform exactly one invocation using the already proven welcome-email render path and the existing process-local safety controls:

- final To is the single configured staging safety recipient; Cc/Bcc are empty;
- template contains the staging `/login-register/` URL and no production or paid-flow URL;
- subject contains the token;
- real `wp_mail()`/PHPMailer SMTP transport is called once;
- no external HTTP, payment, cron or Action Scheduler execution;
- if a synthetic fixture is required, remove it by exact ID and restore initial counts.

Do not retry a failed transport in this task.

## Result

- `SENT_AWAITING_INBOX_CONFIRMATION`: exactly one message was accepted for transport and cleanup passed.
- `BLOCKED`: transport was not called/accepted or a safety/cleanup assertion failed.

A successful `wp_mail()` return is not proof of inbox receipt. Report UTC send time, the non-secret token, transport acceptance, send count, cleanup and final baseline state. Do not include the recipient address or message body.

STOP after one concise report. Do not start another task.
