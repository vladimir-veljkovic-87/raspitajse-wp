# Codex Execution Reports

This branch is a reporting-only audit channel for Raspitajse Codex Remote tasks.

- Never merge this branch into `staging`, `main`, or any baseline branch.
- Never store application code changes here.
- Reports must not contain credentials, secrets, private keys, tokens, database credentials, SMTP credentials, or private user data.
- `reports/latest.md` points to the most recently published execution report.
- Timestamped reports under `reports/` provide the append-only task history.

Reports are normally published by `tools/codex-report.sh` from a code branch without changing that branch's working tree.
