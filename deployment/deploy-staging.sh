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
PACKAGE_SNAPSHOT_ROOT="/home/u601262303/deploy-state/raspitajse-core-wpforms-snapshots-v2"
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
  ./deploy-staging.sh snapshot-release <branch> [previous-sha]
  ./deploy-staging.sh restore-release <branch> <previous-sha>

Release transactions require Python 3 and use the exact previous/HEAD pair.
An explicit previous-sha is required when the marker is absent and for restore.

Default branch: staging
EOF
}

cleanup() {
  [[ -z "${TMP_CHANGED}" ]] || rm -f -- "${TMP_CHANGED}"
  [[ -z "${TMP_DELETED}" ]] || rm -f -- "${TMP_DELETED}"
  [[ -z "${TMP_STATE}" ]] || rm -f -- "${TMP_STATE}"
  [[ -z "${TMP_MANIFEST}" ]] || rm -f -- "${TMP_MANIFEST}"
  if [[ "${TMP_PACKAGE_ROLLBACK}" == "${PACKAGE_SNAPSHOT_ROOT}"/.*.tmp.* &&
        -d "${TMP_PACKAGE_ROLLBACK}" && ! -L "${TMP_PACKAGE_ROLLBACK}" ]]; then
    rm -rf -- "${TMP_PACKAGE_ROLLBACK}"
  fi
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
  full|changed|snapshot-packages|restore-packages|snapshot-release|restore-release) ;;
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

snapshot_manifest() (
  cd -- "$1"
  local path digest
  find . -mindepth 1 ! -path ./metadata ! -path ./manifest.sha256 -print0 |
    LC_ALL=C sort -z | while IFS= read -r -d '' path; do
      case "${path}" in
        ./wp-admin|./wp-admin/*|./wp-includes|./wp-includes/*|./wp-content|./wp-content/plugins|./wp-content/plugins/wpforms-lite|./wp-content/plugins/wpforms-lite/*|./root) ;;
        ./root/*) is_core_top_level_path "${path#./root/}" || fail "Unexpected snapshot root entry." ;;
        *) fail "Unexpected snapshot entry." ;;
      esac
      [[ "${path}" != *$'\n'* && "${path}" != *$'\t'* && ! -L "${path}" ]] ||
        fail "Unsafe snapshot entry."
      if [[ -d "${path}" ]]; then
        printf 'D\t%s\n' "${path}"
      elif [[ -f "${path}" ]]; then
        digest="$(sha256sum < "${path}")"
        printf 'F\t%s\t%s\n' "${digest%% *}" "${path}"
      else
        fail "Unsupported snapshot entry type."
      fi
    done
)

verify_snapshot() {
  local snapshot="$1" path digest expected
  [[ -d "${snapshot}" && ! -L "${snapshot}" ]] || fail "Exact snapshot is missing or unsafe."
  for path in wp-content wp-content/plugins root "${PACKAGE_TREE_ROOTS[@]}"; do
    [[ -d "${snapshot}/${path}" && ! -L "${snapshot}/${path}" ]] || fail "Missing or unsafe snapshot directory."
  done
  for path in metadata manifest.sha256 "${CORE_TOP_LEVEL_FILES[@]/#/root/}"; do
    [[ -f "${snapshot}/${path}" && ! -L "${snapshot}/${path}" ]] || fail "Missing or unsafe snapshot file."
  done
  digest="$(sha256sum < "${snapshot}/manifest.sha256")"
  expected="$(printf 'format=2\ncommit=%s\nmanifest_sha256=%s' "${HEAD_SHA}" "${digest%% *}")"
  printf '%s\n' "${expected}" | cmp -s - "${snapshot}/metadata" || fail "Snapshot metadata mismatch."
  snapshot_manifest "${snapshot}" | cmp -s - "${snapshot}/manifest.sha256" || fail "Snapshot manifest mismatch."
}

snapshot_packages() {
  local path digest final="${PACKAGE_SNAPSHOT_ROOT}/${HEAD_SHA}"
  [[ -d "${PACKAGE_SNAPSHOT_ROOT%/*}" && ! -L "${PACKAGE_SNAPSHOT_ROOT%/*}" ]] || fail "Unsafe snapshot parent."
  mkdir -p -- "${PACKAGE_SNAPSHOT_ROOT}"
  [[ -d "${PACKAGE_SNAPSHOT_ROOT}" && ! -L "${PACKAGE_SNAPSHOT_ROOT}" ]] || fail "Unsafe snapshot root."
  [[ ! -e "${final}" && ! -L "${final}" ]] || fail "Snapshot already exists for this commit."
  assert_package_target_roots
  TMP_PACKAGE_ROLLBACK="$(mktemp -d "${PACKAGE_SNAPSHOT_ROOT}/.${HEAD_SHA}.tmp.XXXXXX")"
  mkdir -- "${TMP_PACKAGE_ROLLBACK}/root"
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    mkdir -p -- "${TMP_PACKAGE_ROLLBACK}/${path}"
    rsync -a -- "${TARGET_SITE_ROOT}/${path}/" "${TMP_PACKAGE_ROLLBACK}/${path}/"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    [[ -f "${TARGET_SITE_ROOT}/${path}" && ! -L "${TARGET_SITE_ROOT}/${path}" ]] || fail "Unsafe core root file."
    cp -p -- "${TARGET_SITE_ROOT}/${path}" "${TMP_PACKAGE_ROLLBACK}/root/${path}"
  done
  snapshot_manifest "${TMP_PACKAGE_ROLLBACK}" > "${TMP_PACKAGE_ROLLBACK}/manifest.sha256"
  digest="$(sha256sum < "${TMP_PACKAGE_ROLLBACK}/manifest.sha256")"
  printf 'format=2\ncommit=%s\nmanifest_sha256=%s\n' "${HEAD_SHA}" "${digest%% *}" > "${TMP_PACKAGE_ROLLBACK}/metadata"
  verify_snapshot "${TMP_PACKAGE_ROLLBACK}"

  # Lock the shared directory inode only for publication. Each writer owns its temporary directory.
  (
    flock -x 9
    [[ ! -e "${final}" && ! -L "${final}" ]] || fail "Snapshot already exists for this commit."
    mv -T -- "${TMP_PACKAGE_ROLLBACK}" "${final}"
  ) 9< "${PACKAGE_SNAPSHOT_ROOT}"
  TMP_PACKAGE_ROLLBACK=""
  echo "Immutable package snapshot created for ${HEAD_SHA}."
}

restore_packages() {
  local path snapshot="${PACKAGE_SNAPSHOT_ROOT}/${HEAD_SHA}"
  [[ -d "${PACKAGE_SNAPSHOT_ROOT}" && ! -L "${PACKAGE_SNAPSHOT_ROOT}" ]] || fail "Unsafe snapshot root."
  verify_snapshot "${snapshot}"
  assert_package_target_roots
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    [[ ! -e "${TARGET_SITE_ROOT}/${path}" || -f "${TARGET_SITE_ROOT}/${path}" ]] ||
      fail "Unsafe core restore target."
  done
  for path in "${PACKAGE_TREE_ROOTS[@]}"; do
    rsync -a --checksum --delete-delay -- "${snapshot}/${path}/" "${TARGET_SITE_ROOT}/${path}/"
  done
  for path in "${CORE_TOP_LEVEL_FILES[@]}"; do
    cp -p -- "${snapshot}/root/${path}" "${TARGET_SITE_ROOT}/${path}"
  done
  echo "Immutable package snapshot restored for ${HEAD_SHA}."
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
    assert_no_symlink_components "${REPO_DIR}" "${path}"
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    rsync -aR --no-implied-dirs --checksum --no-times --omit-dir-times --itemize-changes -- "${path}" "${TARGET_SITE_ROOT}/"
  done < "${TMP_CHANGED}"

  while IFS= read -r -d '' path; do
    is_allowed_path "${path}" || fail "Refusing unexpected deleted path: ${path}"

    if is_package_path "${path}"; then
      assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
      package_changed=1
      continue
    fi

    echo "Removing deleted file: ${path}"
    assert_no_symlink_components "${TARGET_SITE_ROOT}" "${path}"
    rm -f -- "${TARGET_SITE_ROOT}/${path}"
  done < "${TMP_DELETED}"

  if [[ "${package_changed}" -eq 1 ]]; then
    reconcile_package_trees
  fi

  for path in "${VENDOR_RECONCILE_ROOTS[@]}"; do
    git diff --quiet "${previous_sha}" "${HEAD_SHA}" -- "${path}" && continue
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

release_transaction() {
  python3 - "${MODE}" "${3:-}" "${HEAD_SHA}" "${TARGET_SITE_ROOT}" "${STATE_FILE}" "${MANIFEST_FILE}" <<'PYTHON'
import fcntl
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import stat
import subprocess
import sys
import tempfile

mode, previous, head, site_name, marker_name, communications_name = sys.argv[1:]
site = Path(site_name)
marker = Path(marker_name)
communications = Path(communications_name)
snapshot_root = marker.parent / "raspitajse-release-snapshots-v1"

def require(condition, message):
    if not condition:
        raise RuntimeError(message)

def safe(path):
    for part in [path, *path.parents]:
        require(not part.is_symlink(), "Symlink in transaction path")
    if path.exists():
        require(path.is_file() or path.is_dir(), "Unsupported filesystem type")

def git(*args):
    return subprocess.check_output(["git", *args])

def encoded(value):
    return (json.dumps(value, sort_keys=True, separators=(",", ":")) + "\n").encode()

def digest(data):
    return hashlib.sha256(data).hexdigest()

def inventory(path):
    safe(path)
    if not path.exists():
        return [["", "absent"]]
    result = []
    for entry in [path, *sorted(path.rglob("*"))] if path.is_dir() else [path]:
        safe(entry)
        relative = str(entry.relative_to(path))
        if entry.is_dir():
            result.append([relative, "directory"])
        else:
            result.append([relative, "file", digest(entry.read_bytes())])
    return result

def copy(source, target):
    if source.is_dir():
        shutil.copytree(source, target)
    else:
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(source, target)

def remove(path):
    safe(path)
    if path.is_dir():
        shutil.rmtree(path)
    elif path.exists():
        path.unlink()

safe(site)
safe(marker)
safe(communications)
require(site.is_dir() and marker.parent.is_dir(), "Target roots unavailable")
if mode == "snapshot-release":
    if marker.exists():
        recorded = marker.read_text().strip()
        require(not previous or previous == recorded, "Previous SHA disagrees with marker")
        previous = recorded
else:
    require(bool(previous), "restore-release requires explicit previous SHA")
require(re.fullmatch("[0-9a-f]{40}", previous or ""), "Exact previous SHA required")
require(re.fullmatch("[0-9a-f]{40}", head), "Exact HEAD required")
require(git("rev-parse", "HEAD").decode().strip() == head, "Checkout HEAD mismatch")
git("cat-file", "-e", previous + "^{commit}")
if mode == "restore-release" and marker.exists():
    require(marker.read_text().strip() in (previous, head), "Target transaction mismatch")

packages = ["wp-admin", "wp-includes", "wp-content/plugins/wpforms-lite"]
roots = "index.php license.txt readme.html wp-activate.php wp-blog-header.php wp-comments-post.php wp-cron.php wp-links-opml.php wp-load.php wp-login.php wp-mail.php wp-settings.php wp-signup.php wp-trackback.php xmlrpc.php".split()
vendors = ["wp-content/themes/superio", "wp-content/plugins/apus-framework", "wp-content/plugins/revslider"]
allowed = vendors + ["wp-content/themes/superio-child", "wp-content/plugins/wp-job-board-pro", "wp-content/plugins/wp-job-board-pro-wc-paid-listings", "wp-content/plugins/raspitajse-communications", "wp-content/plugins/raspitajse-commerce", "wp-content/mu-plugins"]
retired = ["wp-content/plugins/hostinger-ai-assistant", "wp-content/plugins/hostinger-easy-onboarding", "wp-content/plugins/google-analytics-for-wordpress"]
changed = [p.decode() for p in git("diff", "--name-only", "--no-renames", "-z", previous, head).split(b"\0") if p]

def under(path, root):
    return path == root or path.startswith(root + "/")

plan = set()
if any(p in roots or any(under(p, r) for r in packages) for p in changed):
    plan.update(packages + roots)
for vendor in vendors:
    if any(under(p, vendor) for p in changed):
        plan.add(vendor)
for path in changed:
    if any(under(path, r) for r in allowed) and not any(under(path, r) for r in plan):
        plan.add(path)
for path in retired:
    before = git("ls-tree", "-r", "--name-only", previous, "--", path)
    after = git("ls-tree", "-r", "--name-only", head, "--", path)
    if before and not after:
        plan.add(path)
plan = sorted(plan)
require(all(not p.startswith("/") and ".." not in Path(p).parts for p in plan), "Unsafe mutation plan")
targets = [site / p for p in plan] + [communications, marker]
labels = ["site/" + p for p in plan] + ["state/communications", "state/marker"]

safe(snapshot_root)
if mode == "snapshot-release":
    snapshot_root.mkdir(exist_ok=True)
require(snapshot_root.is_dir(), "Missing snapshot root")
final = snapshot_root / (previous + "--" + head)

def verify(snapshot):
    safe(snapshot)
    require(snapshot.is_dir(), "Missing exact transaction snapshot")
    require({p.name for p in snapshot.iterdir()} == {"metadata.json", "manifest.json", "payload"}, "Unexpected snapshot inventory")
    safe(snapshot / "metadata.json")
    safe(snapshot / "manifest.json")
    safe(snapshot / "payload")
    metadata = json.loads((snapshot / "metadata.json").read_bytes())
    data = (snapshot / "manifest.json").read_bytes()
    require(metadata == {"format": 1, "previous": previous, "head": head, "manifest_sha256": digest(data)}, "Metadata mismatch")
    manifest = json.loads(data)
    require(manifest["plan"] == labels, "Mutation plan mismatch")
    require(set(manifest) == {"plan", "entries", "absent_parents"}, "Manifest schema mismatch")
    require(len(manifest["entries"]) == len(targets), "Manifest completeness mismatch")
    expected_payload = set()
    for index, entries in enumerate(manifest["entries"]):
        payload = snapshot / "payload" / str(index)
        require(inventory(payload) == entries, "Payload manifest mismatch")
        if entries != [["", "absent"]]:
            expected_payload.add(str(index))
    require({p.name for p in (snapshot / "payload").iterdir()} == expected_payload, "Extra payload")
    for relative in manifest["absent_parents"]:
        require(relative and ".." not in Path(relative).parts and not relative.startswith("/"), "Unsafe parent ledger")
        parent = site / relative
        require(any(parent in target.parents for target in targets[:-2]), "Parent outside plan")
    return manifest

if mode == "snapshot-release":
    require(not final.exists() and not final.is_symlink(), "Snapshot already exists")
    for target in targets:
        inventory(target)
    absent_parents = set()
    for target in targets[:-2]:
        for parent in target.parents:
            if parent == site:
                break
            if not parent.exists():
                absent_parents.add(str(parent.relative_to(site)))
    temporary = Path(tempfile.mkdtemp(prefix="." + previous + "--" + head + ".tmp.", dir=snapshot_root))
    try:
        (temporary / "payload").mkdir()
        entries = []
        for index, target in enumerate(targets):
            before = inventory(target)
            if target.exists():
                copy(target, temporary / "payload" / str(index))
            require(inventory(temporary / "payload" / str(index)) == before, "Snapshot copy mismatch")
            require(inventory(target) == before, "Runtime changed during snapshot")
            entries.append(before)
        manifest = {"plan": labels, "entries": entries, "absent_parents": sorted(absent_parents)}
        data = encoded(manifest)
        (temporary / "manifest.json").write_bytes(data)
        (temporary / "metadata.json").write_bytes(encoded({"format": 1, "previous": previous, "head": head, "manifest_sha256": digest(data)}))
        verify(temporary)
        lock = os.open(snapshot_root, os.O_RDONLY)
        try:
            fcntl.flock(lock, fcntl.LOCK_EX)
            require(not final.exists() and not final.is_symlink(), "Snapshot already exists")
            os.rename(temporary, final)
        finally:
            os.close(lock)
        print("PASS snapshot-release " + final.name)
    finally:
        if temporary.exists():
            shutil.rmtree(temporary)
else:
    manifest = verify(final)
    for target in targets:
        inventory(target)
    # Validate every target before the first write. State files are restored last.
    for index, target in enumerate(targets):
        if index == len(targets) - 2:
            for checked, content in enumerate(targets[:-2]):
                require(inventory(content) == manifest["entries"][checked], "Content restore verification failed")
            for relative in sorted(manifest["absent_parents"], key=lambda p: len(Path(p).parts), reverse=True):
                parent = site / relative
                safe(parent)
                if parent.exists():
                    parent.rmdir()
        payload = final / "payload" / str(index)
        remove(target)
        if payload.exists():
            copy(payload, target)
        require(inventory(target) == manifest["entries"][index], "Restored target mismatch")
    require([inventory(p) for p in targets] == manifest["entries"], "Aggregate prestate mismatch")
    print("PASS restore-release " + digest(encoded(manifest["entries"])))
PYTHON
}
case "${MODE}" in
  snapshot-release|restore-release) release_transaction "$@"; exit 0 ;;
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
