#!/usr/bin/env bash
set -Eeuo pipefail

MODE="${1:-}"
BRANCH="${2:-staging}"

REPO_DIR="/home/u601262303/repo/raspitajse-wp"
EXPECTED_TARGET_SITE_ROOT="/home/u601262303/domains/raspitajse.com/public_html/public_html_stage"
TARGET_SITE_ROOT="${EXPECTED_TARGET_SITE_ROOT}"
STATE_FILE="/home/u601262303/deploy-state/raspitajse-staging.commit"

ALLOWLIST=(
  "wp-content/themes/superio-child"
  "wp-content/plugins/wp-job-board-pro"
  "wp-content/plugins/wp-job-board-pro-wc-paid-listings"
  "wp-content/plugins/raspitajse-communications"
  "wp-content/mu-plugins"
)

TMP_CHANGED=""
TMP_DELETED=""
TMP_STATE=""

usage() {
  cat <<'EOF'
Usage:
  ./deploy-staging.sh full [staging|feature/<branch>]
  ./deploy-staging.sh changed [staging|feature/<branch>]

Default branch: staging
EOF
}

cleanup() {
  [[ -z "${TMP_CHANGED}" ]] || rm -f -- "${TMP_CHANGED}"
  [[ -z "${TMP_DELETED}" ]] || rm -f -- "${TMP_DELETED}"
  [[ -z "${TMP_STATE}" ]] || rm -f -- "${TMP_STATE}"
}

fail() {
  echo "ERROR: $*" >&2
  exit 1
}

on_error() {
  local exit_code=$?
  echo "Deployment failed on line ${BASH_LINENO[0]} (exit ${exit_code}). State marker was not updated." >&2
  exit "${exit_code}"
}

trap cleanup EXIT
trap on_error ERR

case "${MODE}" in
  full|changed) ;;
  *) usage; exit 2 ;;
esac

case "${BRANCH}" in
  staging|feature/*) ;;
  *) fail "Staging deploy is allowed only from 'staging' or 'feature/*'. Refusing branch: ${BRANCH}" ;;
esac

[[ "${TARGET_SITE_ROOT}" == "${EXPECTED_TARGET_SITE_ROOT}" ]] \
  || fail "Unexpected staging target: ${TARGET_SITE_ROOT}"
[[ "${TARGET_SITE_ROOT}" == */public_html_stage ]] \
  || fail "Target does not end in public_html_stage: ${TARGET_SITE_ROOT}"
[[ ! -L "${TARGET_SITE_ROOT}" ]] \
  || fail "Staging target root must not be a symlink: ${TARGET_SITE_ROOT}"

[[ -d "${REPO_DIR}/.git" ]] || fail "Git repository not found at ${REPO_DIR}"
[[ -d "${TARGET_SITE_ROOT}/wp-content" ]] || fail "Staging wp-content not found under ${TARGET_SITE_ROOT}"

cd "${REPO_DIR}"

[[ -z "$(git status --porcelain=v1 --untracked-files=all)" ]] \
  || fail "Repository is not clean. Move/commit/stash server-side files before deploying."

git fetch origin --prune

git show-ref --verify --quiet "refs/remotes/origin/${BRANCH}" \
  || fail "Remote branch origin/${BRANCH} does not exist."

git checkout -B "${BRANCH}" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

[[ -z "$(git status --porcelain=v1 --untracked-files=all)" ]] \
  || fail "Repository became dirty after checkout/reset."

HEAD_SHA="$(git rev-parse HEAD)"
ORIGIN_SHA="$(git rev-parse "origin/${BRANCH}")"
[[ "${HEAD_SHA}" == "${ORIGIN_SHA}" ]] \
  || fail "Local HEAD does not match origin/${BRANCH}."

is_allowed_path() {
  local path="$1"

  [[ "${path}" != /* ]] || return 1
  [[ "${path}" != *".."* ]] || return 1

  case "${path}" in
    wp-content/themes/superio-child/*|wp-content/plugins/wp-job-board-pro/*|wp-content/plugins/wp-job-board-pro-wc-paid-listings/*|wp-content/plugins/raspitajse-communications/*|wp-content/mu-plugins/*)
      return 0
      ;;
    *)
      return 1
      ;;
  esac
}

full_deploy() {
  local path

  echo "Starting full staging deploy from ${BRANCH} (${HEAD_SHA})."

  for path in "${ALLOWLIST[@]}"; do
    [[ -d "${REPO_DIR}/${path}" ]] || fail "Allowlisted source directory is missing: ${path}"
    [[ ! -L "${TARGET_SITE_ROOT}/${path}" ]] || fail "Refusing symlinked target directory: ${path}"

    mkdir -p "${TARGET_SITE_ROOT}/${path}"

    echo "Syncing ${path}"
    rsync -a --checksum --no-times --omit-dir-times --delete-delay --itemize-changes -- \
      "${REPO_DIR}/${path}/" \
      "${TARGET_SITE_ROOT}/${path}/"
  done
}

changed_deploy() {
  [[ -f "${STATE_FILE}" ]] || fail "No previous successful deploy marker found. Run a full deploy first."

  local previous_sha path
  previous_sha="$(tr -d '[:space:]' < "${STATE_FILE}")"

  [[ -n "${previous_sha}" ]] || fail "Deploy state marker is empty. Run a full deploy."
  git cat-file -e "${previous_sha}^{commit}" 2>/dev/null \
    || fail "Previous deploy commit ${previous_sha} is not available locally. Run a full deploy."

  if [[ "${previous_sha}" == "${HEAD_SHA}" ]]; then
    echo "No changes to deploy; ${HEAD_SHA} is already the last successful staging deploy."
    return 0
  fi

  if ! git merge-base --is-ancestor "${previous_sha}" "${HEAD_SHA}"; then
    echo "Previous deployed commit is not an ancestor of HEAD; syncing the complete tree diff between commits."
  fi

  TMP_CHANGED="$(mktemp)"
  TMP_DELETED="$(mktemp)"

  git diff --name-only --no-renames -z --diff-filter=ACMRTUXB \
    "${previous_sha}" "${HEAD_SHA}" -- "${ALLOWLIST[@]}" > "${TMP_CHANGED}"

  git diff --name-only --no-renames -z --diff-filter=D \
    "${previous_sha}" "${HEAD_SHA}" -- "${ALLOWLIST[@]}" > "${TMP_DELETED}"

  echo "Deploying changed files from ${previous_sha} to ${HEAD_SHA}."

  while IFS= read -r -d '' path; do
    is_allowed_path "${path}" || fail "Refusing unexpected changed path: ${path}"
    [[ -e "${REPO_DIR}/${path}" || -L "${REPO_DIR}/${path}" ]] \
      || fail "Changed source path is missing: ${path}"

    echo "Syncing changed file: ${path}"
    rsync -aR --checksum --no-times --omit-dir-times --itemize-changes -- "${path}" "${TARGET_SITE_ROOT}/"
  done < "${TMP_CHANGED}"

  while IFS= read -r -d '' path; do
    is_allowed_path "${path}" || fail "Refusing unexpected deleted path: ${path}"

    echo "Removing deleted file: ${path}"
    rm -f -- "${TARGET_SITE_ROOT}/${path}"
  done < "${TMP_DELETED}"
}

case "${MODE}" in
  full) full_deploy ;;
  changed) changed_deploy ;;
esac

mkdir -p "$(dirname "${STATE_FILE}")"
TMP_STATE="${STATE_FILE}.tmp.$$"
printf '%s\n' "${HEAD_SHA}" > "${TMP_STATE}"
mv -f -- "${TMP_STATE}" "${STATE_FILE}"
TMP_STATE=""

echo "Staging deploy successful."
echo "Mode: ${MODE}"
echo "Branch: ${BRANCH}"
echo "Commit: ${HEAD_SHA}"
echo "Target: ${TARGET_SITE_ROOT}"
echo "State marker: ${STATE_FILE}"
