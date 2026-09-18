#!/usr/bin/env bash
set -Eeuo pipefail

MODE="${1:-}"
BRANCH="${2:-staging}"

REPO_DIR="/home/u601262303/repo/raspitajse-wp"
EXPECTED_TARGET_SITE_ROOT="/home/u601262303/domains/raspitajse.com/public_html/public_html_stage"
TARGET_SITE_ROOT="${EXPECTED_TARGET_SITE_ROOT}"
STATE_FILE="/home/u601262303/deploy-state/raspitajse-staging.commit"
MANIFEST_FILE="/home/u601262303/deploy-state/raspitajse-staging-communications.manifest"

ALLOWLIST=(
  "wp-content/themes/superio-child"
  "wp-content/themes/superio"
  "wp-content/plugins/apus-framework"
  "wp-content/plugins/revslider"
  "wp-content/plugins/wp-job-board-pro"
  "wp-content/plugins/wp-job-board-pro-wc-paid-listings"
  "wp-content/plugins/raspitajse-communications"
  "wp-content/plugins/raspitajse-commerce"
  "wp-content/mu-plugins"
)

PACKAGE_TREE_ROOTS=(
  "wp-admin"
  "wp-includes"
  "wp-content/plugins/wpforms-lite"
)

# Exact regular files from the official WordPress package root. Configuration,
# server-control and arbitrary repository-root files are intentionally absent.
CORE_TOP_LEVEL_FILES=(
  "index.php"
  "license.txt"
  "readme.html"
  "wp-activate.php"
  "wp-blog-header.php"
  "wp-comments-post.php"
  "wp-cron.php"
  "wp-links-opml.php"
  "wp-load.php"
  "wp-login.php"
  "wp-mail.php"
  "wp-settings.php"
  "wp-signup.php"
  "wp-trackback.php"
  "xmlrpc.php"
)

DEPLOY_PATHS=("${ALLOWLIST[@]}" "${PACKAGE_TREE_ROOTS[@]}" "${CORE_TOP_LEVEL_FILES[@]}")
PACKAGE_ROLLBACK_DIR="/home/u601262303/deploy-state/raspitajse-core-wpforms-rollback"
VENDOR_RECONCILE_ROOTS=(
  "wp-content/themes/superio"
  "wp-content/plugins/apus-framework"
  "wp-content/plugins/revslider"
)

# Retired vendor payloads are deletion-only. They are intentionally excluded
# from ALLOWLIST so a full or changed deploy can never reinstall them.
REMOVED_PLUGIN_ROOTS=(
  "wp-content/plugins/hostinger-ai-assistant"
  "wp-content/plugins/hostinger-easy-onboarding"
  "wp-content/plugins/google-analytics-for-wordpress"
)

TMP_CHANGED=""
TMP_DELETED=""
TMP_STATE=""
TMP_MANIFEST=""
TMP_PACKAGE_ROLLBACK=""

usage() {
  cat <<'EOF'
Usage:
  ./deploy-staging.sh full [staging|feature/<branch>]
  ./deploy-staging.sh changed [staging|feature/<branch>]
  ./deploy-staging.sh snapshot-packages [staging|feature/<branch>]
  ./deploy-staging.sh restore-packages [staging|feature/<branch>]

Default branch: staging
EOF
}

cleanup() {
  [[ -z "${TMP_CHANGED}" ]] || rm -f -- "${TMP_CHANGED}"
  [[ -z "${TMP_DELETED}" ]] || rm -f -- "${TMP_DELETED}"
  [[ -z "${TMP_STATE}" ]] || rm -f -- "${TMP_STATE}"
  [[ -z "${TMP_MANIFEST}" ]] || rm -f -- "${TMP_MANIFEST}"
  [[ -z "${TMP_PACKAGE_ROLLBACK}" ]] || rm -rf -- "${TMP_PACKAGE_ROLLBACK}"
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
  full|changed|snapshot-packages|restore-packages) ;;
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

is_core_top_level_path() {
  local path="$1" allowed
  for allowed in "${CORE_TOP_LEVEL_FILES[@]}"; do
    [[ "${path}" == "${allowed}" ]] && return 0
  done
  return 1
}

is_package_path() {
  local path="$1"
  case "${path}" in
    wp-admin/*|wp-includes/*|wp-content/plugins/wpforms-lite/*) return 0 ;;
  esac
  is_core_top_level_path "${path}"
}

is_allowed_path() {
  local path="$1"
  [[ "${path}" != /* ]] || return 1
  [[ "${path}" != *".."* ]] || return 1
  case "${path}" in
    wp-content/themes/superio-child/*|wp-content/themes/superio/*|wp-content/plugins/apus-framework/*|wp-content/plugins/revslider/*|wp-content/plugins/wp-job-board-pro/*|wp-content/plugins/wp-job-board-pro-wc-paid-listings/*|wp-content/plugins/raspitajse-communications/*|wp-content/plugins/raspitajse-commerce/*|wp-content/mu-plugins/*|wp-admin/*|wp-includes/*|wp-content/plugins/wpforms-lite/*) return 0 ;;
  esac
  is_core_top_level_path "${path}"
}

assert_no_symlink_components() {
  local base="$1" relative="$2" part
  local current="${base}"
  local -a parts
  IFS='/' read -r -a parts <<< "${relative}"
  for part in "${parts[@]}"; do
    [[ -n "${part}" ]] || fail "Empty path component in ${relative}."
    current="${current}/${part}"
    [[ ! -L "${current}" ]] || fail "Refusing symlinked package path: ${current}"
  done
}

package_sources_complete() {
  local path
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do [[ -d "${REPO_DIR}/${path}" && ! -L "${REPO_DIR}/${path}" ]] || return 1; done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do [[ -f "${REPO_DIR}/${path}" && ! -L "${REPO_DIR}/${path}" ]] || return 1; done
}

assert_package_target_roots() {
  local path
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    [[ -d "${TARGET_SITE_ROOT}/${path}" ]] || fail "Package target root is missing: ${path}"
    [[ ! -L "${TARGET_SITE_ROOT}/${path}" ]] || fail "Refusing symlinked package target root: ${path}"
  done
}

reconcile_package_trees() {
  local path
  package_sources_complete || fail "Complete canonical core/WPForms source inventory is required."
  assert_package_target_roots
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    [[ -z "$(find "${REPO_DIR}/${path}" -type l -print -quit)" ]] || fail "Package source tree contains a symlink: ${path}"
    [[ -z "$(find "${TARGET_SITE_ROOT}/${path}" -type l -print -quit)" ]] || fail "Package target tree contains a symlink: ${path}"
    echo "Reconciling package root: ${path}"
    rsync -a --checksum --no-times --omit-dir-times --delete-delay -- "${REPO_DIR}/${path}/" "${TARGET_SITE_ROOT}/${path}/"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    assert_no_symlink_components "${REPO_DIR}" "${path}"
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    echo "Syncing canonical core root file: ${path}"
    rsync -a --checksum --no-times --omit-dir-times -- "${REPO_DIR}/${path}" "${TARGET_SITE_ROOT}/${path}"
  done
}

snapshot_packages() {
  local path metadata
  [[ ! -e "${PACKAGE_ROLLBACK_DIR}" && ! -L "${PACKAGE_ROLLBACK_DIR}" ]] || fail "Package rollback snapshot already exists: ${PACKAGE_ROLLBACK_DIR}"
  assert_package_target_roots
  TMP_PACKAGE_ROLLBACK="$(mktemp -d "${PACKAGE_ROLLBACK_DIR}.tmp.XXXXXX")"
  mkdir -p "${TMP_PACKAGE_ROLLBACK}/root" "${TMP_PACKAGE_ROLLBACK}/wp-content/plugins"
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    [[ -z "$(find "${TARGET_SITE_ROOT}/${path}" -type l -print -quit)" ]] || fail "Package target tree contains a symlink: ${path}"
    mkdir -p "${TMP_PACKAGE_ROLLBACK}/${path}"
    rsync -a --delete -- "${TARGET_SITE_ROOT}/${path}/" "${TMP_PACKAGE_ROLLBACK}/${path}/"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    [[ -f "${TARGET_SITE_ROOT}/${path}" && ! -L "${TARGET_SITE_ROOT}/${path}" ]] || fail "Canonical live core root file is missing or unsafe: ${path}"
    cp -p -- "${TARGET_SITE_ROOT}/${path}" "${TMP_PACKAGE_ROLLBACK}/root/${path}"
  done
  metadata="${TMP_PACKAGE_ROLLBACK}/metadata"
  printf 'format=1\ncommit=%s\n' "${HEAD_SHA}" > "${metadata}"
  mv -- "${TMP_PACKAGE_ROLLBACK}" "${PACKAGE_ROLLBACK_DIR}"
  TMP_PACKAGE_ROLLBACK=""
  echo "Package rollback snapshot created for ${HEAD_SHA}."
}

restore_packages() {
  local path snapshot_commit
  [[ -d "${PACKAGE_ROLLBACK_DIR}" && ! -L "${PACKAGE_ROLLBACK_DIR}" ]] || fail "Package rollback snapshot is missing or unsafe."
  snapshot_commit="$(sed -n 's/^commit=//p' "${PACKAGE_ROLLBACK_DIR}/metadata")"
  [[ "${snapshot_commit}" == "${HEAD_SHA}" ]] || fail "Rollback snapshot commit does not match checked out deploy source."
  assert_package_target_roots
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    [[ -d "${PACKAGE_ROLLBACK_DIR}/${path}" && ! -L "${PACKAGE_ROLLBACK_DIR}/${path}" ]] || fail "Rollback package tree is missing or unsafe: ${path}"
    [[ -z "$(find "${PACKAGE_ROLLBACK_DIR}/${path}" -type l -print -quit)" ]] || fail "Rollback package tree contains a symlink: ${path}"
    rsync -a --checksum --delete-delay -- "${PACKAGE_ROLLBACK_DIR}/${path}/" "${TARGET_SITE_ROOT}/${path}/"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    [[ -f "${PACKAGE_ROLLBACK_DIR}/root/${path}" && ! -L "${PACKAGE_ROLLBACK_DIR}/root/${path}" ]] || fail "Rollback core root file is missing or unsafe: ${path}"
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    cp -p -- "${PACKAGE_ROLLBACK_DIR}/root/${path}" "${TARGET_SITE_ROOT}/${path}"
  done
  echo "Package rollback snapshot restored for ${HEAD_SHA}."
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

  if package_sources_complete; then
    reconcile_package_trees
  else
    echo "Canonical core package is not tracked at ${HEAD_SHA}; package roots left unchanged."
  fi
}

changed_deploy() {
  [[ -f "${STATE_FILE}" ]] || fail "No previous successful deploy marker found. Run a full deploy first."

  local previous_sha path previous_count deleted_count package_changed=0
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
    "${previous_sha}" "${HEAD_SHA}" -- "${DEPLOY_PATHS[@]}" > "${TMP_CHANGED}"

  git diff --name-only --no-renames -z --diff-filter=D \
    "${previous_sha}" "${HEAD_SHA}" -- "${DEPLOY_PATHS[@]}" > "${TMP_DELETED}"

  echo "Deploying changed files from ${previous_sha} to ${HEAD_SHA}."

  while IFS= read -r -d '' path; do
    is_allowed_path "${path}" || fail "Refusing unexpected changed path: ${path}"
    [[ -e "${REPO_DIR}/${path}" || -L "${REPO_DIR}/${path}" ]] \
      || fail "Changed source path is missing: ${path}"

    if is_package_path "${path}"; then
      assert_no_symlink_components "${REPO_DIR}" "${path}"
      assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
      package_changed=1
      continue
    fi

    echo "Syncing changed file: ${path}"
    rsync -aR --checksum --no-times --omit-dir-times --itemize-changes -- "${path}" "${TARGET_SITE_ROOT}/"
  done < "${TMP_CHANGED}"

  while IFS= read -r -d '' path; do
    is_allowed_path "${path}" || fail "Refusing unexpected deleted path: ${path}"

    if is_package_path "${path}"; then
      assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
      package_changed=1
      continue
    fi

    echo "Removing deleted file: ${path}"
    rm -f -- "${TARGET_SITE_ROOT}/${path}"
  done < "${TMP_DELETED}"

  if [[ "${package_changed}" -eq 1 ]]; then
    reconcile_package_trees
  fi

  for path in "${VENDOR_RECONCILE_ROOTS[@]}"; do
    [[ -d "${REPO_DIR}/${path}" ]] \
      || fail "Vendor reconciliation source is missing: ${path}"
    [[ ! -L "${TARGET_SITE_ROOT}/${path}" ]] \
      || fail "Refusing symlinked vendor reconciliation target: ${path}"

    mkdir -p "${TARGET_SITE_ROOT}/${path}"

    echo "Reconciling vendor root: ${path}"
    rsync -a --checksum --no-times --omit-dir-times --delete-delay -- \
      "${REPO_DIR}/${path}/" \
      "${TARGET_SITE_ROOT}/${path}/"
  done

  for path in "${REMOVED_PLUGIN_ROOTS[@]}"; do
    # Once a removal has been deployed, later commits have no source tree and
    # no previous deployed tree to reconcile for this exact root.
    [[ ! -e "${REPO_DIR}/${path}" && ! -L "${REPO_DIR}/${path}" ]] || continue
    git cat-file -e "${previous_sha}:${path}" 2>/dev/null || continue

    previous_count="$(git ls-tree -r --name-only "${previous_sha}" -- "${path}" | wc -l)"
    deleted_count="$(git diff --name-only --no-renames --diff-filter=D \
      "${previous_sha}" "${HEAD_SHA}" -- "${path}" | wc -l)"

    [[ "${previous_count}" -gt 0 ]] \
      || fail "Retired plugin root had no tracked files in previous deploy: ${path}"
    [[ "${deleted_count}" -eq "${previous_count}" ]] \
      || fail "Refusing partial retired plugin removal: ${path} (${deleted_count}/${previous_count})"
    [[ ! -L "${TARGET_SITE_ROOT}/${path}" ]] \
      || fail "Refusing symlinked retired plugin root: ${path}"

    echo "Removing retired staging plugin root: ${path} (${deleted_count} tracked files)"
    rm -rf -- "${TARGET_SITE_ROOT}/${path}"
    [[ ! -e "${TARGET_SITE_ROOT}/${path}" && ! -L "${TARGET_SITE_ROOT}/${path}" ]] \
      || fail "Retired plugin root still exists after removal: ${path}"
  done
}

case "${MODE}" in
  full) full_deploy ;;
  changed) changed_deploy ;;
  snapshot-packages) snapshot_packages; exit 0 ;;
  restore-packages) restore_packages ;;
esac

COMMUNICATIONS_PATH="wp-content/plugins/raspitajse-communications"
[[ -d "${REPO_DIR}/${COMMUNICATIONS_PATH}" && -d "${TARGET_SITE_ROOT}/${COMMUNICATIONS_PATH}" ]] \
  || fail "Communications source/runtime tree is missing after deploy."
tree_hash() {
  local root="$1"
  (
    cd -- "${root}"
    find . -type f -print0 | sort -z | xargs -0 sha256sum | sha256sum | awk '{print $1}'
  )
}
SOURCE_COMMUNICATIONS_SHA="$(tree_hash "${REPO_DIR}/${COMMUNICATIONS_PATH}")"
RUNTIME_COMMUNICATIONS_SHA="$(tree_hash "${TARGET_SITE_ROOT}/${COMMUNICATIONS_PATH}")"
[[ "${SOURCE_COMMUNICATIONS_SHA}" =~ ^[0-9a-f]{64}$ ]] || fail "Invalid source communications tree hash."
[[ "${SOURCE_COMMUNICATIONS_SHA}" == "${RUNTIME_COMMUNICATIONS_SHA}" ]] \
  || fail "Communications runtime parity verification failed after deploy."

mkdir -p "$(dirname "${MANIFEST_FILE}")"
TMP_MANIFEST="${MANIFEST_FILE}.tmp.$$"
printf 'format=1\ncommit=%s\ncommunications_sha256=%s\n' "${HEAD_SHA}" "${SOURCE_COMMUNICATIONS_SHA}" > "${TMP_MANIFEST}"
mv -f -- "${TMP_MANIFEST}" "${MANIFEST_FILE}"
TMP_MANIFEST=""

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
echo "Communications manifest: ${MANIFEST_FILE}"
