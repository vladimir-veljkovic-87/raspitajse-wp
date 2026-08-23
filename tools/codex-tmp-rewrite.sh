#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'EOF'
Usage:
  cat corrected-file | bash tools/codex-tmp-rewrite.sh /tmp/raspitajse-task-.../file [expected_sha256]

Atomically rewrites one existing regular file inside a Raspitajse-owned /tmp task
folder. Intended for temporary guards/harnesses when apply_patch is unavailable
because of host namespace ENOSPC.

The helper:
- refuses symlinks and paths outside /tmp/raspitajse-*;
- optionally requires an exact pre-edit SHA-256;
- writes to a sibling temporary file and atomically renames it;
- prints only hashes/path metadata, never file contents;
- does not invoke apply_patch or bubblewrap.
EOF
}

if [[ $# -lt 1 || $# -gt 2 ]]; then
    usage >&2
    exit 2
fi

target="$1"
expected_sha="${2:-}"

case "$target" in
    /tmp/raspitajse-task-*/*|/tmp/raspitajse-*/*) ;;
    *)
        echo "ERROR: target must be inside a Raspitajse-owned /tmp task directory" >&2
        exit 3
        ;;
esac

if [[ -L "$target" ]]; then
    echo "ERROR: symlink targets are not allowed" >&2
    exit 4
fi

if [[ ! -f "$target" ]]; then
    echo "ERROR: target must already exist as a regular file" >&2
    exit 5
fi

parent="$(cd "$(dirname "$target")" && pwd -P)"
resolved_target="$parent/$(basename "$target")"
case "$resolved_target" in
    /tmp/raspitajse-task-*/*|/tmp/raspitajse-*/*) ;;
    *)
        echo "ERROR: resolved target escaped the permitted /tmp prefix" >&2
        exit 6
        ;;
esac

before_sha="$(sha256sum "$resolved_target" | awk '{print $1}')"
if [[ -n "$expected_sha" && "$before_sha" != "$expected_sha" ]]; then
    echo "ERROR: pre-edit SHA-256 does not match expected value" >&2
    echo "before_sha256=$before_sha" >&2
    exit 7
fi

replacement="$(mktemp "$parent/.codex-rewrite.XXXXXX")"
cleanup() {
    rm -f -- "$replacement"
}
trap cleanup EXIT

cat > "$replacement"
if [[ ! -s "$replacement" ]]; then
    echo "ERROR: replacement content is empty" >&2
    exit 8
fi

chmod --reference="$resolved_target" "$replacement" 2>/dev/null || true
mv -f -- "$replacement" "$resolved_target"
trap - EXIT

after_sha="$(sha256sum "$resolved_target" | awk '{print $1}')"
printf 'tmp_rewrite=PASS\n'
printf 'target=%s\n' "$resolved_target"
printf 'before_sha256=%s\n' "$before_sha"
printf 'after_sha256=%s\n' "$after_sha"
