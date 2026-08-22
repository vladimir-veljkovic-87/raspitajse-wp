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
  cat report.md | tools/codex-report.sh "Task title" PASS

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
    STAGING_ENVIRONMENT="$(wp --path="${STAGING_ROOT}" eval 'echo wp_get_environment_type();' 2>/dev/null || true)"
    [[ -n "${STAGING_ENVIRONMENT}" ]] || STAGING_ENVIRONMENT="unavailable"
fi

if [[ -t 0 ]]; then
    BODY="No additional task details were provided."
else
    BODY="$(cat)"
fi

TITLE="${TITLE//$'\n'/ }"
UTC_ISO="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
UTC_FILE="$(date -u +%Y%m%dT%H%M%SZ)"
SLUG="$(printf '%s' "${TITLE}" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//' | cut -c1-80)"
[[ -n "${SLUG}" ]] || SLUG="task"
REPORT_NAME="${UTC_FILE}-${SLUG}.md"

TMP_REPORT="$(mktemp)"
cat > "${TMP_REPORT}" <<EOF
# Codex Execution Report

- Task: ${TITLE}
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

git fetch origin "${REPORT_BRANCH}" --prune
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
echo "Codex report published."
echo "Branch: ${REPORT_BRANCH}"
echo "Report: reports/${REPORT_NAME}"
echo "Commit: ${REPORT_COMMIT}"
