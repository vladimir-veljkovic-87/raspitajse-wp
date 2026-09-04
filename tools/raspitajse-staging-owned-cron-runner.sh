#!/usr/bin/env bash
set -Eeuo pipefail

PATH="/usr/local/bin:/usr/bin:/bin"
export PATH
umask 077

readonly STAGING_ROOT="/home/u601262303/domains/raspitajse.com/public_html/public_html_stage"
readonly DEPLOY_MARKER="/home/u601262303/deploy-state/raspitajse-staging.commit"
readonly WP_BIN="/usr/local/bin/wp"
readonly PHP_BIN="/usr/bin/php"
readonly TIMEOUT_BIN="/usr/bin/timeout"
readonly FLOCK_BIN="/usr/bin/flock"
readonly LOCK_FILE="/tmp/raspitajse-staging-owned-cron-runner.lock"
readonly HOOK_TIMEOUT=45
readonly CYCLE_TIMEOUT=180
readonly -a HOOKS=(
  "raspitajse_job_listing_expiry_evaluator"
  "raspitajse_employer_job_expiry_notice_evaluator"
  "raspitajse_candidate_job_alert_evaluator"
)

MODE="run"
case "$#" in
  0) ;;
  1)
    [[ "$1" == "--check-only" ]] || {
      printf '%s\n' '{"result":"ERROR","environment":"unknown","reason":"unsupported_argument","executed_hooks":0}'
      exit 2
    }
    MODE="check"
    ;;
  *)
    printf '%s\n' '{"result":"ERROR","environment":"unknown","reason":"unexpected_arguments","executed_hooks":0}'
    exit 2
    ;;
esac

readonly MODE
readonly SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
readonly REPO_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd -P)"
readonly GUARD_FILE="${SCRIPT_DIR}/raspitajse-staging-owned-cron-guard.php"

START_EPOCH="$(date +%s)"
RUNTIME_ENVIRONMENT="unknown"
RESULT="ERROR"
REASON="initialization_failed"
EXECUTED=0
FINISHED=0
WATCHDOG_PID=""
STATE_DIR=""
STATE_FILE=""
SNAPSHOT_FILE=""
readonly -a DEFAULT_STATUSES=( "pending" "pending" "pending" )
STATUSES=( "${DEFAULT_STATUSES[@]}" )

sanitize_reason() {
  local value="${1:-unknown_error}"
  value="${value//[^a-zA-Z0-9_.-]/_}"
  printf '%s' "${value:0:160}"
}

guard_counters() {
  if [[ -z "${STATE_FILE}" || ! -f "${STATE_FILE}" ]]; then
    printf '0\n0\n0\n0\n0\n0\n0\n'
    return
  fi
  "${PHP_BIN}" -r '
    $state=json_decode((string)@file_get_contents($argv[1]),true);
    foreach(["wp_http_api","http_intercepted","http_unexpected","wp_mail","phpmailer","smtp","payment"] as $key){
      echo (int)(is_array($state)&&isset($state[$key])?$state[$key]:0),PHP_EOL;
    }
  ' "${STATE_FILE}" 2>/dev/null || printf '0\n0\n0\n0\n0\n0\n0\n'
}

emit_result() {
  local end duration reason
  local counter_text
  local -a counters
  [[ "${FINISHED}" -eq 0 ]] || return
  FINISHED=1
  end="$(date +%s)"
  duration=$(( end - START_EPOCH ))
  reason="$(sanitize_reason "${REASON}")"
  counter_text="$(guard_counters)"
  mapfile -t counters <<< "${counter_text}"
  while [[ "${#counters[@]}" -lt 7 ]]; do counters+=( 0 ); done

  printf '{"result":"%s","environment":"%s","mode":"%s","reason":"%s","duration_seconds":%d,"executed_hooks":%d,' \
    "${RESULT}" "${RUNTIME_ENVIRONMENT}" "${MODE}" "${reason}" "${duration}" "${EXECUTED}"
  printf '"hooks":[{"name":"%s","status":"%s"},{"name":"%s","status":"%s"},{"name":"%s","status":"%s"}],' \
    "${HOOKS[0]}" "${STATUSES[0]}" "${HOOKS[1]}" "${STATUSES[1]}" "${HOOKS[2]}" "${STATUSES[2]}"
  printf '"safety":{"wp_http_api_attempts":%d,"http_intercepted":%d,"unexpected_http":%d,"actual_external_network":0,"wp_mail":%d,"phpmailer":%d,"smtp":%d,"payment":%d}}\n' \
    "${counters[0]}" "${counters[1]}" "${counters[2]}" "${counters[3]}" "${counters[4]}" "${counters[5]}" "${counters[6]}"
}

cleanup() {
  local exit_code=$?
  if [[ -n "${WATCHDOG_PID}" ]]; then
    kill "${WATCHDOG_PID}" 2>/dev/null || true
    wait "${WATCHDOG_PID}" 2>/dev/null || true
  fi
  if [[ "${FINISHED}" -eq 0 ]]; then
    REASON="unexpected_exit_${exit_code}"
    emit_result
  fi
  if [[ -n "${STATE_DIR}" && -d "${STATE_DIR}" ]]; then
    case "${STATE_DIR}" in
      /tmp/raspitajse-staging-owned-cron.[A-Za-z0-9]*) rm -rf -- "${STATE_DIR}" ;;
    esac
  fi
}
trap cleanup EXIT

fail_closed() {
  local reason="$1" current="${2:--1}" index
  REASON="${reason}"
  RESULT="ERROR"
  for index in 0 1 2; do
    if [[ "${index}" -eq "${current}" ]]; then
      STATUSES[${index}]="failed_closed"
    elif [[ "${STATUSES[${index}]}" == "pending" ]]; then
      STATUSES[${index}]="skipped_after_failure"
    fi
  done
  emit_result
  exit 1
}

on_cycle_timeout() {
  fail_closed "cycle_timeout"
}
trap on_cycle_timeout TERM

for required in "${WP_BIN}" "${PHP_BIN}" "${TIMEOUT_BIN}" "${FLOCK_BIN}" /usr/bin/git /usr/bin/sha256sum; do
  [[ -x "${required}" ]] || fail_closed "required_binary_missing"
done
"${PHP_BIN}" -r 'exit(function_exists("posix_kill") ? 0 : 1);' || fail_closed "watchdog_signal_unavailable"
[[ -f "${GUARD_FILE}" ]] || fail_closed "guard_file_missing"
[[ -d "${STAGING_ROOT}/wp-content" ]] || fail_closed "staging_root_missing"
[[ ! -L "${STAGING_ROOT}" ]] || fail_closed "staging_root_symlink"
[[ -f "${DEPLOY_MARKER}" ]] || fail_closed "deploy_marker_missing"

exec 9>"${LOCK_FILE}" || fail_closed "lock_file_unavailable"
if ! "${FLOCK_BIN}" -n 9; then
  RESULT="LOCKED"
  REASON="overlap_lock_active"
  for index in 0 1 2; do STATUSES[${index}]="skipped_after_failure"; done
  RUNTIME_ENVIRONMENT="staging"
  emit_result
  exit 0
fi

"${PHP_BIN}" -r '
  $pid=(int)$argv[1];
  $seconds=(int)$argv[2];
  sleep($seconds);
  if (function_exists("posix_kill")) {
    posix_kill($pid, SIGTERM);
  }
' "$$" "${CYCLE_TIMEOUT}" 9>&- &
WATCHDOG_PID=$!

STATE_DIR="$(mktemp -d /tmp/raspitajse-staging-owned-cron.XXXXXXXX)" || fail_closed "state_dir_create_failed"
STATE_FILE="${STATE_DIR}/guard.json"
SNAPSHOT_FILE="${STATE_DIR}/snapshot.out"
printf '%s\n' '{"wp_http_api":0,"http_intercepted":0,"http_unexpected":0,"wp_mail":0,"phpmailer":0,"smtp":0,"payment":0}' > "${STATE_FILE}"
export RASPITAJSE_STAGING_CRON_STATE_FILE="${STATE_FILE}"

branch="$(/usr/bin/git -C "${REPO_ROOT}" branch --show-current 2>/dev/null)" || fail_closed "repository_unavailable"
case "${branch}" in
  staging|feature/*) ;;
  *) fail_closed "source_branch_forbidden" ;;
esac
[[ -z "$(/usr/bin/git -C "${REPO_ROOT}" status --porcelain=v1 --untracked-files=all)" ]] || fail_closed "source_worktree_dirty"
head_sha="$(/usr/bin/git -C "${REPO_ROOT}" rev-parse HEAD 2>/dev/null)" || fail_closed "source_head_unavailable"
marker_sha="$(tr -d '[:space:]' < "${DEPLOY_MARKER}")"
[[ "${head_sha}" =~ ^[0-9a-f]{40}$ ]] || fail_closed "source_head_invalid"
[[ "${marker_sha}" == "${head_sha}" ]] || fail_closed "deploy_source_mismatch"

tree_hash() {
  local root="$1"
  (
    cd -- "${root}"
    find . -type f -print0 | sort -z | xargs -0 /usr/bin/sha256sum | /usr/bin/sha256sum | awk '{print $1}'
  )
}

source_communications="${REPO_ROOT}/wp-content/plugins/raspitajse-communications"
runtime_communications="${STAGING_ROOT}/wp-content/plugins/raspitajse-communications"
[[ -d "${source_communications}" && -d "${runtime_communications}" ]] || fail_closed "communications_tree_missing"
source_hash="$(tree_hash "${source_communications}")" || fail_closed "source_hash_failed"
runtime_hash="$(tree_hash "${runtime_communications}")" || fail_closed "runtime_hash_failed"
[[ "${source_hash}" == "${runtime_hash}" ]] || fail_closed "communications_runtime_mismatch"

snapshot_values() {
  local encoded="$1"
  "${PHP_BIN}" -r '
    $d=json_decode(base64_decode($argv[1],true),true);
    if(!is_array($d)){exit(3);}
    $hooks=[
      "raspitajse_job_listing_expiry_evaluator",
      "raspitajse_employer_job_expiry_notice_evaluator",
      "raspitajse_candidate_job_alert_evaluator"
    ];
    $values=[
      !empty($d["ok"])?"1":"0",
      implode(",",array_map("strval",(array)($d["reason_codes"]??[]))),
      (string)($d["environment"]??"unknown"),
      (string)($d["contract_fingerprint"]??""),
      (string)($d["full_cron_fingerprint"]??""),
      (string)($d["nonallow_cron_fingerprint"]??""),
      (string)($d["action_scheduler"]["pending_count"]??""),
      (string)($d["action_scheduler"]["pending_fingerprint"]??""),
      (string)($d["action_scheduler"]["protected_32733_status"]??""),
      (string)($d["action_scheduler"]["protected_32733_attempts"]??""),
      hash("sha256",json_encode($d["claims"]??[])),
      (string)($d["business"]["fingerprint"]??""),
      (string)($d["due_business"]["total"]??""),
      !empty($d["doing_cron_clear"])?"1":"0"
    ];
    foreach($hooks as $hook){
      $values[]=!empty($d["owned"][$hook]["due"])?"1":"0";
      $values[]=(string)($d["owned"][$hook]["timestamp"]??"0");
      $values[]=(string)($d["owned"][$hook]["event_fingerprint"]??"");
    }
    foreach($values as $value){
      if(strpos($value,"\n")!==false||strpos($value,"\r")!==false){exit(4);}
      echo $value,PHP_EOL;
    }
  ' "${encoded}"
}

capture_snapshot() {
  local target="$1" marker encoded values_file max_seconds now remaining
  now="$(date +%s)"
  remaining=$(( START_EPOCH + CYCLE_TIMEOUT - now ))
  (( remaining > 0 )) || fail_closed "cycle_timeout"
  max_seconds="${HOOK_TIMEOUT}"
  (( remaining < max_seconds )) && max_seconds="${remaining}"
  : > "${SNAPSHOT_FILE}"
  export RASPITAJSE_STAGING_CRON_TARGET="${target}"
  if ! "${TIMEOUT_BIN}" --signal=TERM --kill-after=5s "${max_seconds}s" \
      "${WP_BIN}" --path="${STAGING_ROOT}" --quiet --require="${GUARD_FILE}" \
      eval 'raspitajse_staging_owned_cron_emit_snapshot();' >"${SNAPSHOT_FILE}" 2>"${STATE_DIR}/snapshot.err"; then
    fail_closed "snapshot_failed"
  fi
  marker="$(sed -n 's/^RASPITAJSE_OWNED_CRON_SNAPSHOT=//p' "${SNAPSHOT_FILE}" | tail -n 1)"
  [[ -n "${marker}" ]] || fail_closed "snapshot_marker_missing"
  values_file="${STATE_DIR}/snapshot.values"
  snapshot_values "${marker}" > "${values_file}" || fail_closed "snapshot_decode_failed"
  mapfile -t SNAP < "${values_file}"
  [[ "${#SNAP[@]}" -eq 23 ]] || fail_closed "snapshot_shape_invalid"
  RUNTIME_ENVIRONMENT="${SNAP[2]}"
  [[ "${SNAP[0]}" == "1" ]] || fail_closed "snapshot_contract_${SNAP[1]}"
  [[ "${SNAP[2]}" == "staging" ]] || fail_closed "environment_not_staging"
  [[ "${SNAP[13]}" == "1" ]] || fail_closed "doing_cron_not_clear"
}

assert_stable_state() {
  local -n before_ref=$1
  local -n after_ref=$2
  [[ "${after_ref[3]}" == "${before_ref[3]}" ]] || fail_closed "callback_event_contract_drift"
  [[ "${after_ref[5]}" == "${before_ref[5]}" ]] || fail_closed "nonallow_cron_drift"
  [[ "${after_ref[6]}" == "${before_ref[6]}" && "${after_ref[7]}" == "${before_ref[7]}" ]] || fail_closed "action_scheduler_drift"
  [[ "${after_ref[8]}" == "pending" && "${after_ref[9]}" == "0" ]] || fail_closed "protected_action_drift"
  [[ "${after_ref[10]}" == "${before_ref[10]}" ]] || fail_closed "claim_state_drift"
}

capture_snapshot "preflight"
BASE=( "${SNAP[@]}" )
EXPECTED=( "${SNAP[@]}" )

for index in 0 1 2; do
  hook="${HOOKS[${index}]}"
  capture_snapshot "preflight"
  BEFORE=( "${SNAP[@]}" )
  assert_stable_state EXPECTED BEFORE
  [[ "${BEFORE[4]}" == "${EXPECTED[4]}" ]] || fail_closed "cron_state_drift_before_hook" "${index}"

  due_index=$(( 14 + index * 3 ))
  timestamp_index=$(( due_index + 1 ))
  if [[ "${BEFORE[${due_index}]}" != "1" ]]; then
    STATUSES[${index}]="not_due"
    EXPECTED=( "${BEFORE[@]}" )
    continue
  fi

  if [[ "${MODE}" == "check" ]]; then
    STATUSES[${index}]="due_check_only"
    EXPECTED=( "${BEFORE[@]}" )
    continue
  fi

  export RASPITAJSE_STAGING_CRON_TARGET="${hook}"
  hook_code=0
  "${TIMEOUT_BIN}" --signal=TERM --kill-after=5s "${HOOK_TIMEOUT}s" \
      "${WP_BIN}" --path="${STAGING_ROOT}" --quiet --require="${GUARD_FILE}" \
      cron event run "${hook}" >"${STATE_DIR}/hook-${index}.out" 2>"${STATE_DIR}/hook-${index}.err" || hook_code=$?
  if [[ "${hook_code}" -ne 0 ]]; then
    if [[ "${hook_code}" -eq 124 || "${hook_code}" -eq 137 ]]; then
      fail_closed "hook_timeout" "${index}"
    fi
    fail_closed "hook_execution_failed" "${index}"
  fi

  EXECUTED=$(( EXECUTED + 1 ))
  capture_snapshot "preflight"
  AFTER=( "${SNAP[@]}" )
  assert_stable_state BEFORE AFTER
  [[ "${AFTER[${timestamp_index}]}" -gt "${BEFORE[${timestamp_index}]}" ]] || fail_closed "owned_timestamp_not_advanced" "${index}"
  for other in 0 1 2; do
    [[ "${other}" -eq "${index}" ]] && continue
    other_timestamp=$(( 15 + other * 3 ))
    [[ "${AFTER[${other_timestamp}]}" == "${BEFORE[${other_timestamp}]}" ]] || fail_closed "other_owned_timestamp_moved" "${index}"
  done
  STATUSES[${index}]="executed"
  EXPECTED=( "${AFTER[@]}" )
done

capture_snapshot "preflight"
FINAL=( "${SNAP[@]}" )
assert_stable_state EXPECTED FINAL
[[ "${FINAL[4]}" == "${EXPECTED[4]}" ]] || fail_closed "final_cron_drift"

final_counter_text="$(guard_counters)"
mapfile -t FINAL_COUNTERS <<< "${final_counter_text}"
while [[ "${#FINAL_COUNTERS[@]}" -lt 7 ]]; do FINAL_COUNTERS+=( 0 ); done
[[ "${FINAL_COUNTERS[2]}" -eq 0 ]] || fail_closed "unexpected_http_attempt"
[[ "${FINAL_COUNTERS[6]}" -eq 0 ]] || fail_closed "payment_path_observed"

if [[ "${MODE}" == "check" ]]; then
  RESULT="NOOP"
  REASON="check_only"
elif [[ "${EXECUTED}" -eq 0 ]]; then
  RESULT="NOOP"
  REASON="no_due_events"
else
  RESULT="PASS"
  REASON="completed"
fi

emit_result
exit 0
