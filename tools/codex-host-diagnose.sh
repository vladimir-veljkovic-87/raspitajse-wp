#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'EOF'
Usage: bash tools/codex-host-diagnose.sh [--probe-namespace]

Print low-cost Hostinger resource diagnostics without changing WordPress or Git state.
With --probe-namespace, also run one short-lived bubblewrap namespace probe.
The probe is diagnostic only; it does not weaken or bypass Codex sandboxing.
EOF
}

probe_namespace=0
case "${1:-}" in
    "") ;;
    --probe-namespace) probe_namespace=1 ;;
    -h|--help) usage; exit 0 ;;
    *) usage >&2; exit 2 ;;
esac

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
uid="$(id -u)"
user_name="$(id -un 2>/dev/null || printf 'uid-%s' "$uid")"

printf 'codex_host_diagnose=1\n'
printf 'utc=%s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
printf 'repo_root=%s\n' "$repo_root"
printf 'user=%s\n' "$user_name"
printf 'uid=%s\n' "$uid"

show_fs() {
    local label="$1"
    local path="$2"
    local blocks inodes

    blocks="$(df -Pk "$path" 2>/dev/null | awk 'NR==2 {printf "size_kb=%s used_kb=%s avail_kb=%s use_pct=%s", $2, $3, $4, $5}')"
    inodes="$(df -Pi "$path" 2>/dev/null | awk 'NR==2 {printf "inodes=%s iused=%s ifree=%s iuse_pct=%s", $2, $3, $4, $5}')"

    printf '%s_fs=%s %s\n' "$label" "${blocks:-unavailable}" "${inodes:-unavailable}"
}

show_fs repo "$repo_root"
show_fs tmp /tmp

process_count="$(ps -u "$uid" -o pid= 2>/dev/null | awk 'NF {n++} END {print n+0}')"
thread_count="$(ps -u "$uid" -o nlwp= 2>/dev/null | awk 'NF {n += $1} END {print n+0}')"
printf 'user_processes=%s\n' "$process_count"
printf 'user_threads=%s\n' "$thread_count"

if process_limit="$(ulimit -u 2>/dev/null)"; then
    printf 'ulimit_user_processes=%s\n' "$process_limit"
else
    printf 'ulimit_user_processes=unavailable\n'
fi

for name in max_user_namespaces max_pid_namespaces max_mnt_namespaces max_net_namespaces; do
    path="/proc/sys/user/$name"
    if [[ -r "$path" ]]; then
        printf '%s=%s\n' "$name" "$(cat "$path")"
    else
        printf '%s=unavailable\n' "$name"
    fi
done

if command -v bwrap >/dev/null 2>&1; then
    printf 'bwrap_present=YES\n'
else
    printf 'bwrap_present=NO\n'
fi

if [[ "$probe_namespace" -ne 1 ]]; then
    printf 'namespace_probe=SKIPPED\n'
    exit 0
fi

if ! command -v bwrap >/dev/null 2>&1; then
    printf 'namespace_probe=UNAVAILABLE\n'
    exit 0
fi

probe_dir="$(mktemp -d /tmp/raspitajse-namespace-probe.XXXXXX)"
cleanup() {
    rm -rf -- "$probe_dir"
}
trap cleanup EXIT

set +e
probe_output="$(bwrap --die-with-parent --unshare-user --unshare-pid --ro-bind / / --proc /proc /bin/true 2>&1)"
probe_rc=$?
set -e

if [[ "$probe_rc" -eq 0 ]]; then
    printf 'namespace_probe=AVAILABLE\n'
elif grep -Eqi 'No space left on device|ENOSPC' <<<"$probe_output"; then
    printf 'namespace_probe=ENOSPC\n'
else
    printf 'namespace_probe=FAILED_OTHER\n'
fi
printf 'namespace_probe_rc=%s\n' "$probe_rc"
