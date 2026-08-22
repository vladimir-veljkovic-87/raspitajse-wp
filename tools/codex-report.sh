#!/usr/bin/env bash
set -Eeuo pipefail

REPO_DIR="/home/u601262303/repo/raspitajse-wp"
REPORT_BRANCH="codex-reports"
STAGING_ROOT="/home/u601262303/domains/raspitajse.com/public_html/public_html_stage"
STATE_FILE="/home/u601262303/deploy-state/raspitajse-staging.commit"

TITLE="${1:-}"
RESULT="${2:-}"
TMP_REPORT=""
WORKTREE_DIR=""

usage() {
    cat <<'EOF'
Usage:
  cat report.md | bash tools/codex-report.sh "Zadatak 1.0 — Short task title" PASS

When a title starts with "Zadatak N.M", the report file is named:
  reports/YYYYMMDDTHHMMSSZ-zadatak-N_M.md

Result must be one of: PASS, FAIL, PARTIAL, SKIPPED
EOF
}

fail() {
    echo "ERROR: $*" >&2
    exit 1
}

cleanup() {
    if [[ -n "${WORKTREE_DIR}" && -d "${WORKTREE_DIR}" ]]; then
        git -C "${REPO_DIR}" worktree remove --force "${WORKTREE_DIR}" >/dev/null 2>&1 || true
    fi

    if [[ -n "${TMP_REPORT}" ]]; then
        rm -f -- "${TMP_REPORT}"
    fi
}

trap cleanup EXIT

[[ -n "${TITLE}" && -n "${RESULT}" ]] || { usage; exit 2; }

case "${RESULT}" in
    PASS|FAIL|PARTIAL|SKIPPED) ;;
    *) fail "Unsupported result '${RESULT}'. Expected PASS, FAIL, PARTIAL, or SKIPPED." ;;
esac

[[ -d "${REPO_DIR}/.git" ]] || fail "Repository not found: ${REPO_DIR}"
cd "${REPO_DIR}"

SOURCE_BRANCH="$(git branch --show-current)"
SOURCE_HEAD="$(git rev-parse HEAD)"

case "${SOURCE_BRANCH}" in
    staging|feature/*) ;;
    *) fail "Reports may only be published from staging or feature/* source branches. Current branch: ${SOURCE_BRANCH:-DETACHED}" ;;
esac

if [[ -z "$(git status --porcelain=v1 --untracked-files=all)" ]]; then
    SOURCE_CLEAN="YES"
else
    SOURCE_CLEAN="NO"
fi

DEPLOYED_SHA="unavailable"
if [[ -f "${STATE_FILE}" ]]; then
    DEPLOYED_SHA="$(tr -d '[:space:]' < "${STATE_FILE}")"
    [[ -n "${DEPLOYED_SHA}" ]] || DEPLOYED_SHA="empty"
fi

STAGING_ENVIRONMENT="unavailable"
if command -v wp >/dev/null 2>&1 && [[ -d "${STAGING_ROOT}" ]]; then
    STAGING_ENVIRONMENT="$(wp --path="${STAGING_ROOT}" --exec='if (!defined("DISABLE_WP_CRON")) { define("DISABLE_WP_CRON", true); }' eval 'echo wp_get_environment_type();' 2>/dev/null || true)"
    [[ -n "${STAGING_ENVIRONMENT}" ]] || STAGING_ENVIRONMENT="unavailable"
fi

if [[ -t 0 ]]; then
    BODY="No additional task details were provided."
else
    BODY="$(cat)"
fi

TITLE="${TITLE//$'\n'/ }"
TASK_ID=""
TASK_FILE_ID=""

if [[ "${TITLE}" =~ ^[Zz]adatak[[:space:]]+([0-9]+)\.([0-9]+) ]]; then
    TASK_ID="${BASH_REMATCH[1]}.${BASH_REMATCH[2]}"
    TASK_FILE_ID="${BASH_REMATCH[1]}_${BASH_REMATCH[2]}"
fi

UTC_ISO="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
UTC_FILE="$(date -u +%Y%m%dT%H%M%SZ)"

if [[ -n "${TASK_FILE_ID}" ]]; then
    SLUG="zadatak-${TASK_FILE_ID}"
else
    SLUG="$(printf '%s' "${TITLE}" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//' | cut -c1-80)"
    [[ -n "${SLUG}" ]] || SLUG="task"
fi

REPORT_NAME="${UTC_FILE}-${SLUG}.md"

TASK_ID_LINE=""
if [[ -n "${TASK_ID}" ]]; then
    TASK_ID_LINE="- Task ID: ${TASK_ID}"
fi

TMP_REPORT="$(mktemp)"
cat > "${TMP_REPORT}" <<EOF
# Codex Execution Report

- Task: ${TITLE}
${TASK_ID_LINE}
- Result: ${RESULT}
- Recorded at (UTC): ${UTC_ISO}
- Source branch: ${SOURCE_BRANCH}
- Source HEAD: ${SOURCE_HEAD}
- Source working tree clean: ${SOURCE_CLEAN}
- Staging deploy marker: ${DEPLOYED_SHA}
- Staging environment: ${STAGING_ENVIRONMENT}

## Task report

EOF
printf '%s\n' "${BODY}" >> "${TMP_REPORT}"

# Refuse a small set of high-confidence secret patterns. This is a backstop,
# not a substitute for the AGENTS.md rule that reports must never contain secrets.
if grep -Eiq -- '-----BEGIN [A-Z0-9 ]*PRIVATE KEY-----|SMTP_PASS[[:space:]]*[:=]|DB_PASSWORD[[:space:]]*[:=]|AWS_SECRET_ACCESS_KEY[[:space:]]*[:=]|AUTH_KEY[[:space:]]*[:=]|SECURE_AUTH_KEY[[:space:]]*[:=]|gh[pousr]_[A-Za-z0-9_]{20,}|sk-[A-Za-z0-9]{20,}' "${TMP_REPORT}"; then
    fail "Report appears to contain secret material. Refusing to publish."
fi

git fetch --prune origin "+refs/heads/${REPORT_BRANCH}:refs/remotes/origin/${REPORT_BRANCH}"
git show-ref --verify --quiet "refs/remotes/origin/${REPORT_BRANCH}" \
    || fail "Remote reporting branch origin/${REPORT_BRANCH} does not exist."

WORKTREE_DIR="/tmp/raspitajse-codex-reports.$$"
git worktree add --detach "${WORKTREE_DIR}" "origin/${REPORT_BRANCH}" >/dev/null

mkdir -p "${WORKTREE_DIR}/reports"
cp -- "${TMP_REPORT}" "${WORKTREE_DIR}/reports/${REPORT_NAME}"
cp -- "${TMP_REPORT}" "${WORKTREE_DIR}/reports/latest.md"

git -C "${WORKTREE_DIR}" add -- reports

if git -C "${WORKTREE_DIR}" diff --cached --quiet; then
    echo "No report changes to publish."
    exit 0
fi

git -C "${WORKTREE_DIR}" \
    -c user.name="Raspitajse Codex Reporter" \
    -c user.email="codex-reports@raspitajse.invalid" \
    commit -m "report: ${TITLE}" >/dev/null

git -C "${WORKTREE_DIR}" push origin "HEAD:${REPORT_BRANCH}" >/dev/null

REPORT_COMMIT="$(git -C "${WORKTREE_DIR}" rev-parse HEAD)"
echo "Report published:"
echo "Path: reports/${REPORT_NAME}"
if [[ -n "${TASK_ID}" ]]; then
    echo "Task ID: ${TASK_ID}"
fi
echo "Commit: ${REPORT_COMMIT}"
