# Codex Execution Report

- Task: Zadatak 2.70 — Kompletan transakcioni rollback paket za combined staging release
- Task ID: 2.70
- Result: PASS
- Recorded at (UTC): 2026-11-17T17:23:46Z
- Source branch: feature/task-2.70-transactional-staging-rollback
- Source HEAD: 2c0c70123af61fc539f61d3349fff369fe1cd9d5
- Source working tree clean: YES
- Staging deploy marker: unavailable
- Staging environment: unavailable

## Task report

Zadatak 2.70 — Kompletan transakcioni rollback paket za combined staging release

PASS: COMPLETE_TRANSACTIONAL_STAGING_ROLLBACK_READY

Branch feature/task-2.70-transactional-staging-rollback.
Verified local/remote HEAD 2c0c70123af61fc539f61d3349fff369fe1cd9d5.
Direct parent accepted release 0c233ad646d7f8f0bac1079eb21f7e73b43d29b0.
Baseline f6093c11d71ddc175ebf83fc69453c20aaca5239.
Accepted release preflight confirmed 19 ahead, 0 behind and exactly 22 changed paths.
Working tree clean. Previous 2.69.2 attestation PASS.

Changed files
- deployment/deploy-staging.sh
- deployment/tests/transactional-release-rollback-harness.sh

No delta outside deployment. All 20 accepted release paths outside deployment retain identical blobs; the accepted 22-path inventory includes two deployment paths.

Interface
snapshot-release <branch> [previous-sha]
restore-release <branch> <previous-sha>
Snapshot reads previous SHA from marker and rejects a conflicting explicit argument. Missing marker requires explicit previous SHA. Restore requires explicit previous SHA and exact checked-out HEAD.
Python 3 is required and documented in usage; available in test environment, no server installation attempted.

Mutation plan
Deterministic PREVIOUS..HEAD Git diff expands any package change to all three package roots and 15 core root files. Changed/deleted owned allowlisted files are included individually. Vendor roots are reconciled and snapshotted only when their diff is touched. Retired roots are protected when present in previous Git tree and absent in release. Marker and Communications manifest are included with absence represented. Absent parent directories are recorded for safe rmdir cleanup.
Owned-file rsync now uses --no-implied-dirs to preserve existing parent attributes outside the plan. Owned changed/deleted paths reject symlink components.

Snapshot
Root /home/u601262303/deploy-state/raspitajse-release-snapshots-v1.
Key is full PREVIOUS_SHA--HEAD_SHA.
metadata.json contains format 1, previous/head and SHA-256 of deterministic manifest.json.
Manifest contains sorted plan, file/directory/absence inventories with SHA-256 file hashes and absent-parent ledger. Runtime bytes, including drift from Git baseline, are authoritative.
Same-parent temporary snapshot is fully verified before exclusive publication flock and atomic rename. Duplicate rejection occurs under lock. Cleanup removes only the operation-created temporary directory. No latest/glob selection, legacy fallback, retention or pruning.

Restore
Before writes validates exact pair, checkout/marker transaction, safe types, path components, metadata, complete manifest and payload.
Content is restored and verified first, including removal of newly created ledger files and package/vendor extras. Communications manifest and marker are restored last, byte-exact or absent.
Content failure leaves marker/manifest unchanged and reports failure. A failed restore may leave partially restored content, requiring recovery while the caller retains transaction locks.
Successful restore verifies aggregate ledger prestate. Unrelated paths and legacy/package-v2 snapshots remain outside the plan.

Validation
Docker dependencies were prepared locally, then bridge networking disconnected. All behavioral tests ran offline in private fixture roots. No server paths remained in fixture script copies.
bash -n PASS for deployment and both harness scripts.
git diff --check PASS, including final commit in fresh clone.
shellcheck SKIPPED, not installed.
Immutable package harness PASS twice.
Transactional release harness PASS twice.
Both harnesses PASS twice again in fresh clone of exact final commit.
Stacked product journey PASS twice, repeated twice in fresh clone.
Candidate desired positions, employer job posting, candidate application tracking and private-message notification harnesses PASS.
PHP lint PASS for cumulative product PHP changes and imported core PHP files.
Initial stdin-fed lint invocation failed from argument handling; direct Docker invocation and final fresh-clone lint passed.
Fresh checkout confirmed accepted release as direct parent and no changes outside deployment.

Behavioral coverage
- Exact pair key, deterministic plan and coexistence of multiple pairs.
- Full package and changed owned restoration after actual fixture changed deploy.
- Newly created file/parent cleanup and deleted file return.
- Runtime drift restored from snapshot rather than Git.
- Marker and manifest byte-exact restoration, including absence.
- Untouched vendor remains unchanged during changed deploy; touched vendor fully restored with extras removed.
- Existing and absent retired-plugin lifecycle.
- Duplicate rejection leaves first snapshot fingerprint unchanged.
- Deterministic concurrency uses separate checkouts and publication-directory flock held until both temporary snapshots have metadata. Release yields exactly one success and one rejection, with no operation temp left.
- Wrong previous and metadata HEAD rejection.
- Changed/missing/extra snapshot payload, bad metadata and symlinked root/payload/metadata rejected before target changes.
- Injected prepublication failure cleans only its temporary snapshot.
- Injected failure after content restore but before state copy preserves marker and manifest.
- Successful full runtime fingerprint equals prestate.
- Unrelated runtime/deploy-state, legacy and package-v2 sentinels preserved.

Remote feature HEAD verified equal to local. Remote staging and prior release remained unchanged.
Real server snapshot/restore/deploy invocations 0.
Staging filesystem and DB/cache changes 0.
Scheduler/mail/payment/browser actions 0.
Production access 0.
Deploy NO.
Production touched: NO.
No SSH or WordPress runtime invocation in Task 2.70. Test container removed.

Report publication initially blocked by automatic approval review usage limit; the publication command was not executed at that time. User requested continuation.

Next task defined only
Zadatak 2.71 — Immutable transactional snapshot i combined staging deploy.
Start from exact remote 2c0c70123af61fc539f61d3349fff369fe1cd9d5, confirm Python 3 and active runtime protections, acquire cron-runner then runtime locks, create/verify exact commit-pair snapshot, perform one combined changed deploy and server-side gates. On any failed gate invoke exact restore-release and prove byte-exact prestate, marker and manifest. No browser/fixture journey or production access. Not executed here.
