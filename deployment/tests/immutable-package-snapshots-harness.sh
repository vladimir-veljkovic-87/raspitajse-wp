#!/usr/bin/env bash
set -Eeuo pipefail

source_script="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/deploy-staging.sh"
fixture="$(mktemp -d)"
trap 'rm -rf -- "${fixture}"' EXIT
site="${fixture}/public_html_stage"
state="${fixture}/deploy-state"
snapshots="${state}/raspitajse-core-wpforms-snapshots-v2"
legacy="${state}/raspitajse-core-wpforms-rollback"
remote="${fixture}/remote.git"
branch=feature/fixture
trees=(wp-admin wp-includes wp-content/plugins/wpforms-lite)
roots=(index.php license.txt readme.html wp-activate.php wp-blog-header.php
  wp-comments-post.php wp-cron.php wp-links-opml.php wp-load.php wp-login.php
  wp-mail.php wp-settings.php wp-signup.php wp-trackback.php xmlrpc.php)

mkdir -p -- "${legacy}" "${fixture}/bin"
printf 'legacy\n' > "${legacy}/sentinel"
printf 'unrelated\n' > "${site%/*}/sentinel"
for path in "${trees[@]}"; do
  mkdir -p -- "${site}/${path}"
  printf 'first\n' > "${site}/${path}/fixture"
done
for path in "${roots[@]}"; do printf 'first\n' > "${site}/${path}"; done

git init -q --bare "${remote}"
git clone -q "${remote}" "${fixture}/repo-one"
git -C "${fixture}/repo-one" switch -qc "${branch}"
communications=wp-content/plugins/raspitajse-communications
mkdir -p "${fixture}/repo-one/${communications}" "${site}/${communications}"
printf 'communications fixture\n' > "${fixture}/repo-one/${communications}/fixture"
cp "${fixture}/repo-one/${communications}/fixture" "${site}/${communications}/fixture"
git -C "${fixture}/repo-one" add -- "${communications}"
git -C "${fixture}/repo-one" -c user.name=Fixture -c user.email=fixture@example.invalid commit -qm first
git -C "${fixture}/repo-one" push -qu origin "${branch}"
git clone -qb "${branch}" "${remote}" "${fixture}/repo-two"

for worker in one two; do
  sed -e "s|/home/u601262303/repo/raspitajse-wp|${fixture}/repo-${worker}|g" \
      -e "s|/home/u601262303/domains/raspitajse.com/public_html/public_html_stage|${site}|g" \
      -e "s|/home/u601262303/deploy-state|${state}|g" \
      "${source_script}" > "${fixture}/deploy-${worker}"
  if rg -q '/home/u601262303' "${fixture}/deploy-${worker}"; then
    echo "Fixture script retained a server path." >&2
    exit 1
  fi
done

run() {
  bash "${fixture}/deploy-$1" "$2" "${branch}"
}

reject() {
  local expected="$1"
  shift
  if "$@" > "${fixture}/rejected" 2>&1; then
    echo "Expected rejection: ${expected}" >&2
    exit 1
  fi
  grep -Fq "${expected}" "${fixture}/rejected" || { cat "${fixture}/rejected" >&2; exit 1; }
}

tree_digest() (
  cd -- "$1"
  find . -type f -print0 | LC_ALL=C sort -z | xargs -0 sha256sum | sha256sum
)

# Both processes reach publication with complete temporary trees before either takes the real lock.
export REAL_FLOCK="$(command -v flock)" BARRIER="${fixture}"
cat > "${fixture}/bin/flock" <<'BARRIER_SCRIPT'
#!/usr/bin/env bash
set -eu
touch "${BARRIER}/ready.${WORKER}"
for ((attempt=0; attempt<500; attempt++)); do
  if [[ -f "${BARRIER}/ready.one" && -f "${BARRIER}/ready.two" ]]; then
    exec "${REAL_FLOCK}" "$@"
  fi
  sleep 0.02
done
exit 90
BARRIER_SCRIPT
chmod +x "${fixture}/bin/flock"
PATH="${fixture}/bin:${PATH}" WORKER=one run one snapshot-packages > "${fixture}/one.log" 2>&1 &
one=$!
PATH="${fixture}/bin:${PATH}" WORKER=two run two snapshot-packages > "${fixture}/two.log" 2>&1 &
two=$!
one_status=0
two_status=0
wait "${one}" || one_status=$?
wait "${two}" || two_status=$?
cat "${fixture}/one.log" "${fixture}/two.log"
[[ -f "${fixture}/ready.one" && -f "${fixture}/ready.two" ]]
[[ "${one_status}:${two_status}" == 0:1 || "${one_status}:${two_status}" == 1:0 ]]
grep -Fq 'Snapshot already exists for this commit.' "${fixture}/one.log" "${fixture}/two.log"
[[ -z "$(find "${snapshots}" -name '.*.tmp.*' -print -quit)" ]]

first="${snapshots}/$(git -C "${fixture}/repo-one" rev-parse HEAD)"
first_digest="$(tree_digest "${first}")"
reject 'Snapshot already exists' run one snapshot-packages
[[ "$(tree_digest "${first}")" == "${first_digest}" ]]
run one restore-packages
git -C "${fixture}/repo-one" -c user.name=Fixture -c user.email=fixture@example.invalid commit -qm second --allow-empty
git -C "${fixture}/repo-one" push -q
reject 'Exact snapshot is missing' run one restore-packages
run one snapshot-packages
second="${snapshots}/$(git -C "${fixture}/repo-one" rev-parse HEAD)"
cmp "${first}/manifest.sha256" "${second}/manifest.sha256"

printf 'changed\n' > "${site}/wp-admin/fixture"
printf 'extra\n' > "${site}/wp-admin/extra"
target_digest="$(tree_digest "${site}")"
cp -a "${second}" "${fixture}/valid"
for fault in metadata changed missing extra symlink; do
  case "${fault}" in
    metadata) printf 'invalid\n' > "${second}/metadata"; expected='Snapshot metadata mismatch' ;;
    changed) printf 'invalid\n' > "${second}/wp-admin/fixture"; expected='Snapshot manifest mismatch' ;;
    missing) rm -- "${second}/wp-admin/fixture"; expected='Snapshot manifest mismatch' ;;
    extra) touch "${second}/wp-admin/extra"; expected='Snapshot manifest mismatch' ;;
    symlink) ln -s fixture "${second}/wp-admin/link"; expected='Snapshot manifest mismatch' ;;
  esac
  reject "${expected}" run one restore-packages
  [[ "$(tree_digest "${site}")" == "${target_digest}" ]]
  rm -rf -- "${second}"
  cp -a "${fixture}/valid" "${second}"
done
run one restore-packages
grep -Fxq first "${site}/wp-admin/fixture"
[[ ! -e "${site}/wp-admin/extra" ]]

git -C "${fixture}/repo-one" -c user.name=Fixture -c user.email=fixture@example.invalid commit -qm third --allow-empty
git -C "${fixture}/repo-one" push -q
ln -s fixture "${site}/wp-admin/link"
reject 'Unsafe snapshot entry' run one snapshot-packages
[[ ! -e "${snapshots}/$(git -C "${fixture}/repo-one" rev-parse HEAD)" ]]
[[ -z "$(find "${snapshots}" -name '.*.tmp.*' -print -quit)" ]]
[[ "$(tree_digest "${first}")" == "${first_digest}" ]]
grep -Fxq legacy "${legacy}/sentinel"
grep -Fxq unrelated "${fixture}/sentinel"
echo 'PASS: immutable snapshot, concurrency, restore, validation and cleanup'
