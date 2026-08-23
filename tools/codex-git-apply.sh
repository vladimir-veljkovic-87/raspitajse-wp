#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'EOF'
Usage:
  cat change.patch | bash tools/codex-git-apply.sh
  cat change.patch | bash tools/codex-git-apply.sh --check-only

Namespace-resilient fallback for repository edits when Codex apply_patch fails
with a confirmed bubblewrap/namespace ENOSPC error.

Safety properties:
- feature/* branches only;
- clean working tree required before application;
- patch is checked before mutation;
- absolute/traversal/.git/wp-config/production-like paths are rejected;
- git diff --check runs after application;
- does not invoke apply_patch or bubblewrap.
EOF
}

check_only=0
case "${1:-}" in
    "") ;;
    --check-only) check_only=1 ;;
    -h|--help) usage; exit 0 ;;
    *) usage >&2; exit 2 ;;
esac

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
cd "$repo_root"

if [[ ! -d .git ]]; then
    echo "ERROR: repository root does not contain .git" >&2
    exit 3
fi

branch="$(git symbolic-ref --quiet --short HEAD || true)"
if [[ "$branch" != feature/* ]]; then
    echo "ERROR: namespace-free patch fallback is allowed only on feature/* branches; current=${branch:-DETACHED}" >&2
    exit 4
fi

if [[ -n "$(git status --porcelain=v1 --untracked-files=all)" ]]; then
    echo "ERROR: working tree must be clean before namespace-free patch application" >&2
    exit 5
fi

patch_file="$(mktemp /tmp/raspitajse-codex-git-apply.XXXXXX.patch)"
cleanup() {
    rm -f -- "$patch_file"
}
trap cleanup EXIT

cat > "$patch_file"
if [[ ! -s "$patch_file" ]]; then
    echo "ERROR: empty patch" >&2
    exit 6
fi

# Reject dangerous patch target paths before asking Git to parse/apply them.
# This intentionally scans only diff header/path metadata, not arbitrary patch body text.
if awk '
    /^(diff --git |--- |\+\+\+ |rename from |rename to |copy from |copy to )/ {
        line=$0
        if (line ~ /(^|[[:space:]])\/+/ ||
            line ~ /(^|[[:space:]])(a\/|b\/)?\.\.\// ||
            line ~ /(^|[[:space:]])(a\/|b\/)?\.git(\/|$)/ ||
            line ~ /wp-config\.php([[:space:]]|$)/ ||
            line ~ /public_html([[:space:]\/]|$)/) {
            bad=1
        }
    }
    END { exit bad ? 0 : 1 }
' "$patch_file"; then
    echo "ERROR: patch metadata contains a forbidden absolute/traversal/.git/wp-config/public_html path" >&2
    exit 7
fi

git apply --check --whitespace=error-all "$patch_file"
echo "patch_check=PASS"

if [[ "$check_only" -eq 1 ]]; then
    echo "patch_apply=SKIPPED"
    exit 0
fi

git apply --whitespace=error-all "$patch_file"
git diff --check

echo "patch_apply=PASS"
echo "branch=$branch"
echo "changed_files:"
git diff --name-only --
