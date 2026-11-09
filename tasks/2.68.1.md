# Zadatak 2.68.1 — Resume and complete the byte-exact WordPress 7.1.2 release preparation

Status: READY
Staging baseline: f6093c11d71ddc175ebf83fc69453c20aaca5239
Product release-candidate base: 45d6e8c3aafb5f48ce9d50fd61924886a0924c2d
Previous task: 2.68
Supersedes execution retry of: 2.56.1
Target: repository-only continuation of release preparation
Production: FORBIDDEN
Time budget: 50 minutes
Risk class: AUTONOMOUS_SAFE
Mutation class: RELEASE_PREPARATION_REPOSITORY_ONLY

## Objective

Resume the interrupted Task 2.68 work, resolve the proven Git line-ending normalization blocker with the smallest path-specific `.gitattributes` exception, and complete one remote release-preparation branch containing:

1. the accepted 2.63–2.67 product stack;
2. immutable exact-commit package snapshot/restore tooling; and
3. canonical WordPress core byte-identical to the verified official WordPress 7.1.2 package.

Required result: `PASS: IMMUTABLE_WORDPRESS_7_1_2_PRODUCT_RELEASE_BRANCH_READY`.

This task does not resume Task 2.56.1. Its E2E intent is superseded by the later complete product batch deploy/QA contract. It must not deploy, mutate staging/runtime/database/cache, create live fixtures, execute browser QA, or access production.

## Fresh proven state

- `origin/staging` and the live staging marker are `f6093c11d71ddc175ebf83fc69453c20aaca5239`.
- `origin/codex-tasks` at `4d57f78b25b5dfce0286c444b3ab5554eefa9916` contained Task 2.68 with this same baseline.
- The 2026-11-09 report for Task 2.56.1 stopped before locks, fixtures, source changes or deploy because that obsolete task required `ff42719f218e440b168b57690b70a2402ee66809`.
- Host checkout was clean on local branch `feature/task-2.68-wp-7.1.2-release-preparation` at local-only commit `1da38e2cb4d53e1f4356d6454248b9a276c2c6fa`.
- No remote branch with that name existed when this task was authored. The local-only commit must be inspected, not assumed valid and not destroyed.
- Task 2.68 exposed a byte-identity blocker: repository-wide text rules normalize line endings, while some files in the official package require their original bytes. `.gitattributes` was outside the original allowed scope.
- The accepted product RC is `45d6e8c3aafb5f48ce9d50fd61924886a0924c2d` with the exact 2.63–2.67 ancestry recorded in Task 2.68.
- Official runtime/core manifest SHA-256 remains `8b4f6e4a20b87010eeb3730a533fd5d16119ed49c6342f7f33f5a47f5b26e2a2`.

## Preconditions and safe local-branch adoption

1. Fetch `origin/staging`, `origin/codex-tasks`, `origin/codex-reports` and remote feature refs.
2. Prove `origin/staging` and live marker equal the declared baseline, staging environment is exact, guards remain active, and no competing Git/rsync/deploy/core-update process exists.
3. Prove remote `feature/task-2.67-product-journey-integration` equals the product RC and retains the accepted ancestry.
4. Confirm `feature/task-2.68-wp-7.1.2-release-preparation` still has no remote ref before any push.
5. If the clean local branch and local-only commit named above exist:
   - prove the product RC is an ancestor;
   - inventory every commit and changed path from the product RC;
   - inspect the complete diff for credentials, secrets, production paths, unsafe deletion, weakened guards and scope drift;
   - adopt the local work only if every changed path fits Task 2.68 scope and every change is attributable to its snapshot tooling or official-core reconciliation objective.
6. If the named local branch/commit no longer exists, create the branch from the exact product RC and perform the complete work below.
7. Never reset, delete, overwrite, force-update or silently recreate an existing local branch. Unexpected ancestry/path/content is `BLOCKED: LOCAL_2_68_CONTINUATION_SCOPE_DRIFT`.

The obsolete 2.56.1 baseline is not a precondition for this task and must not be restored, checked out or used as application source.

## Allowed delta from the product RC

Only:

- `.gitattributes`;
- `deployment/deploy-staging.sh`;
- focused offline tests under `deployment/tests/`;
- `license.txt`;
- `wp-admin/about.php`;
- `wp-includes/images/crystal/license.txt`;
- `wp-includes/js/codemirror/csslint.js`;
- `wp-includes/template.php`;
- `wp-includes/version.php`.

No 2.63–2.67 product file may change. No vendor plugin/theme, configuration, secret, upload, database, production or unrelated WordPress path may change.

## Required correction — exact-path EOL preservation

1. Read the existing `.gitattributes` rules from the product RC and prove the global `text`/`eol=lf` rules are the cause of the byte mismatch.
2. Download and verify the official archive exactly as defined below, then identify by byte comparison which of the six official delta files cannot survive the current Git clean/smudge rules byte-identically.
3. Add exceptions only for the exact affected paths. Use path-specific attributes that disable text normalization and remove inherited EOL conversion, equivalent to `-text !eol`.
4. Do not weaken rules for any directory, wildcard, file type, product path or additional core file.
5. After applying attributes, copy the six verified official files, stage them, and prove both:
   - staged/index blobs equal the official archive bytes; and
   - committed Git blobs equal the official archive bytes after commit.
6. Prove a fresh checkout of the final commit reproduces the complete official canonical-core manifest exactly.

If exact byte identity requires anything broader than `.gitattributes` plus exact-path exceptions for a subset of the six authorized core files, stop with `BLOCKED: BYTE_EXACT_CORE_REQUIRES_BROADER_POLICY_CHANGE`.

## Official WordPress 7.1.2 reference

Use only:

- `https://wordpress.org/wordpress-7.1.2.zip`;
- published MD5 `c332b9a5bcab2dea2a263b649bdb2a1c`;
- published SHA-1 `fab6ce3a905a1f4085ffff7fd9920bc693f5dd94`.

In a fresh non-symlink task-private `/tmp/raspitajse-task-2.68.1-*` directory, perform exactly one bounded unauthenticated HTTPS GET. Allow at most two redirects, every hop limited to `wordpress.org` or `downloads.wordpress.org`. No cookies, authorization, request body, referrer, site data, retry, mirror or alternate URL.

Before extraction require a regular non-symlink archive, exact published MD5/SHA-1, recorded SHA-256, and a safe ZIP containing only one `wordpress/` prefix with no traversal, absolute path, symlink or device entry.

Require:

- package WordPress version 7.1.2 and DB version 61833;
- official aggregate canonical-core manifest `8b4f6e4a20b87010eeb3730a533fd5d16119ed49c6342f7f33f5a47f5b26e2a2`;
- pre-import content differences at exactly the six authorized paths and no others;
- post-import/fresh-checkout differences of zero: no missing, extra, type or content mismatch.

Remove only the exact task-private directory after evidence is recorded and prove it absent.

## Immutable snapshot tooling completion

Complete and validate the original Task 2.68 tooling contract:

- versioned snapshot root under `/home/u601262303/deploy-state`;
- immutable deterministic directory keyed by exact checked-out commit SHA;
- atomic same-parent temporary creation and rename only after verification;
- exact package scope: `wp-admin`, `wp-includes`, `wp-content/plugins/wpforms-lite`, and the existing 15 canonical root files;
- format version, exact commit and deterministic aggregate SHA-256 manifest in metadata;
- reject existing same-commit snapshot without altering it;
- exact-HEAD restore only, with no latest/glob selection or implicit legacy fallback;
- validate types, metadata commit, completeness and manifest before any target write;
- preserve existing allowlist, staging-root, branch, clean-tree, marker and Communications parity guards;
- no automatic pruning or retention deletion.

The fixed legacy snapshot `/home/u601262303/deploy-state/raspitajse-core-wpforms-rollback` must remain byte-for-byte untouched. Do not run snapshot or restore modes against real server paths in this task.

## Offline and regression gates

Run from task-private fixtures only:

- `bash -n` for changed shell files;
- `shellcheck` only if already installed;
- immutable snapshot harness twice from clean fixture roots, including atomic creation, deterministic manifest, duplicate rejection, two commit keys, exact-key restore, wrong metadata, changed/missing/extra entries, symlink rejection, no legacy fallback, bounded failure cleanup and unchanged unrelated/legacy fixtures;
- PHP lint for the changed PHP core files and every cumulative owned product PHP file in the 2.67 manifest;
- `git diff --check`;
- all accepted 2.63–2.66 focused harnesses;
- the 2.67 cross-feature product harness twice;
- unchanged hashes for every recorded 2.67 product file;
- exact final allowed-path inventory from the product RC;
- fresh-checkout official-core manifest verification after the final commit.

No live database, fixtures, SMTP, payment, scheduler execution, browser, external HTTP beyond the one fixed archive GET, or production access.

## Commit and push boundary

- Preserve valid adopted local commits; add the smallest corrective/finalizing commit(s) without rewriting history.
- Push only `feature/task-2.68-wp-7.1.2-release-preparation` after all gates pass.
- Never force-push.
- Do not merge, fast-forward or update `staging`, previous feature branches or `main`.
- Verify remote feature HEAD equals local HEAD and the product RC ancestry is exact.
- Produce a categorized path/hash manifest for product files, deployment tooling/tests, `.gitattributes`, and six official core files.

## Later batch deploy/QA contract — define, do not execute

The report must define the next single guarded task from the exact remote 2.68.1 HEAD:

1. acquire nonblocking locks in order `/tmp/raspitajse-staging-owned-cron-runner.lock`, then `/tmp/raspitajse-staging-runtime.lock`; release in reverse order;
2. create and fully verify a fresh immutable snapshot keyed by the exact release commit before deploy;
3. deploy the complete release once, never feature-by-feature;
4. run server-side candidate/employer journey first, including the still-needed E2E intent from 2.56.1, then human desktop/mobile browser QA;
5. use collision-resistant fixture ledger and delete only ledger-owned fixtures, proving T0/T1 protected-state equality;
6. intercept staging mail, payment and external HTTP before real transport;
7. require manual CAPTCHA completion only—never disable, bypass or automate it;
8. rollback to the fresh verified immutable snapshot on any failed gate and prove Git/live/marker/protected-state alignment;
9. perform no production action.

## Zero-runtime-mutation contract

Required counters: staging/runtime changes `0`; deploy-state/legacy snapshot changes `0`; real server snapshot/restore/deploy invocations `0`; database/cache changes `0`; live fixtures `0`; real mail `0`; scheduler/payment execution `0`; browser requests `0`; unexpected external requests `0`; production access `0`.

Do not acquire staging mutation locks because this task is repository-only.

## Report

Publish exactly one Zadatak 2.68.1 report through `codex-reports`, verify it remotely, and STOP.

Report: preconditions; handling of the local-only `1da38e2c…` continuation; exact adopted commits/paths; `.gitattributes` root cause and exact exceptions; official archive transport/digests/safety; index/commit/fresh-checkout byte proofs; snapshot tooling and harness results; complete product regressions; final remote feature HEAD and categorized hash manifest; zero-mutation counters; unchanged staging/marker/legacy material; exact next batch deploy/QA contract; and `Production touched: NO`.
