#!/usr/bin/env bash
set -Eeuo pipefail
python3 - "$(dirname "$0")/../deploy-staging.sh" <<'PYTHON'
import hashlib
import fcntl
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
import time

source = Path(sys.argv[1]).resolve().read_text()
with tempfile.TemporaryDirectory(prefix="raspitajse-transaction-test-") as directory:
    base = Path(directory)
    repo = base / "repo"
    remote = base / "remote.git"
    site = base / "public_html_stage"
    state = base / "state"
    repo.mkdir()
    state.mkdir()
    site.mkdir()
    def run(*args, cwd=repo, success=True):
        result = subprocess.run(args, cwd=cwd, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        if success and result.returncode:
            raise AssertionError(result.stdout)
        if not success:
            assert result.returncode, result.stdout
        return result.stdout.strip()
    def write(path, value):
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(value)
    def fingerprint(path):
        return [(str(p.relative_to(path)), hashlib.sha256(p.read_bytes()).hexdigest()) for p in sorted(path.rglob("*")) if p.is_file()]
    run("git", "init", "-q", "-b", "feature/test")
    run("git", "config", "user.name", "Fixture")
    run("git", "config", "user.email", "fixture@example.invalid")
    run("git", "init", "--bare", "-q", str(remote))
    run("git", "remote", "add", "origin", str(remote))
    script = repo / "deployment/deploy-staging.sh"
    prepared = source.replace("/home/u601262303/repo/raspitajse-wp", str(repo)).replace("/home/u601262303/domains/raspitajse.com/public_html/public_html_stage", str(site)).replace("/home/u601262303/deploy-state", str(state))
    assert "/home/u601262303" not in prepared
    write(script, prepared)
    owned = "wp-content/plugins/raspitajse-communications"
    retired = "wp-content/plugins/hostinger-ai-assistant"
    write(repo/retired/"file", "retired")
    write(site/retired/"file", "retired-runtime")
    roots = "index.php license.txt readme.html wp-activate.php wp-blog-header.php wp-comments-post.php wp-cron.php wp-links-opml.php wp-load.php wp-login.php wp-mail.php wp-settings.php wp-signup.php wp-trackback.php xmlrpc.php".split()
    for path in roots + ["wp-admin/file", "wp-includes/file", "wp-content/plugins/wpforms-lite/file", owned+"/changed", owned+"/deleted"]:
        write(repo/path, "baseline")
        write(site/path, "runtime-drift")
    for root in ["wp-content/themes/superio", "wp-content/plugins/apus-framework", "wp-content/plugins/revslider"]:
        write(repo/root/"file", "vendor")
        write(site/root/"file", "vendor-runtime")
    run("git","add",".")
    run("git","commit","-qm","baseline")
    previous=run("git","rev-parse","HEAD")
    write(repo/owned/"changed","release")
    write(repo/owned/"new","release")
    write(repo/owned/"new-directory/nested","release")
    (repo/owned/"deleted").unlink()
    write(repo/"wp-admin/file","release")
    shutil.rmtree(repo/retired)
    run("git","add",".")
    run("git","commit","-qm","release")
    head=run("git","rev-parse","HEAD")
    run("git","push","-qu","origin","feature/test")
    write(state/"raspitajse-staging.commit", previous+"\n")
    write(state/"raspitajse-staging-communications.manifest","prestate manifest")
    write(site/"unrelated","unrelated")
    write(state/"raspitajse-core-wpforms-rollback/sentinel","legacy")
    write(state/"raspitajse-core-wpforms-snapshots-v2/sentinel","v2")
    before=fingerprint(site)
    before_state=fingerprint(state)
    def mode(name, success=True, prev=previous):
        return run("bash",str(script),name,"feature/test",prev,success=success)
    mode("snapshot-release")
    snapshot=state/"raspitajse-release-snapshots-v1"/(previous+"--"+head)
    assert snapshot.is_dir()
    manifest=json.loads((snapshot/"manifest.json").read_text())
    assert all("superio" not in p for p in manifest["plan"])
    original=fingerprint(snapshot)
    mode("snapshot-release",success=False)
    assert original==fingerprint(snapshot)
    vendor_untouched=fingerprint(site/"wp-content/themes/superio")
    mode("changed")
    assert vendor_untouched==fingerprint(site/"wp-content/themes/superio")
    assert not (site/retired).exists()
    write(site/owned/"changed","new")
    write(site/owned/"new","new")
    (site/owned/"deleted").unlink(missing_ok=True)
    write(site/"wp-admin/extra","extra")
    write(state/"raspitajse-staging.commit",head+"\n")
    write(state/"raspitajse-staging-communications.manifest","new manifest")
    target=fingerprint(site)
    backup=base/"snapshot-backup"
    shutil.copytree(snapshot,backup)
    for fault in ["metadata","changed","missing","extra","symlink","metadata-symlink","root-symlink","head"]:
        if fault=="metadata":
            write(snapshot/"metadata.json","{}")
        elif fault=="head":
            data=json.loads((snapshot/"metadata.json").read_text())
            data["head"]="0"*40
            write(snapshot/"metadata.json",json.dumps(data))
        elif fault=="metadata-symlink":
            (snapshot/"metadata.json").unlink()
            (snapshot/"metadata.json").symlink_to(backup/"metadata.json")
        elif fault=="root-symlink":
            shutil.rmtree(snapshot)
            snapshot.symlink_to(backup,target_is_directory=True)
        elif fault=="extra":
            write(snapshot/"payload/extra","extra")
        else:
            payload=next(p for p in (snapshot/"payload").rglob("*") if p.is_file())
            if fault=="changed": payload.write_text("corrupted")
            elif fault=="missing": payload.unlink()
            else:
                payload.unlink()
                payload.symlink_to(site/"unrelated")
        mode("restore-release",success=False)
        assert target==fingerprint(site)
        if snapshot.is_symlink():
            snapshot.unlink()
        else:
            shutil.rmtree(snapshot)
        shutil.copytree(backup,snapshot)
    mode("restore-release",success=False,prev="0"*40)
    mode("restore-release")
    assert before==fingerprint(site)
    for path, checksum in before_state:
        assert hashlib.sha256((state/path).read_bytes()).hexdigest()==checksum
    print("PASS retired existing root restored byte-exact")

    # A second pair exercises vendor reconciliation and an absent state manifest.
    write(repo/"wp-content/themes/superio/file", "vendor-release")
    run("git","add",".")
    run("git","commit","-qm","vendor release")
    new_head=run("git","rev-parse","HEAD")
    run("git","push","-q")
    write(state/"raspitajse-staging.commit",head+"\n")
    (state/"raspitajse-staging-communications.manifest").unlink()
    vendor_before=fingerprint(site/"wp-content/themes/superio")
    mode("snapshot-release",prev=head)
    second=state/"raspitajse-release-snapshots-v1"/(head+"--"+new_head)
    assert snapshot.is_dir() and second.is_dir()
    assert "site/wp-content/themes/superio" in json.loads((second/"manifest.json").read_text())["plan"]
    write(site/"wp-content/themes/superio/file","mutated")
    write(site/"wp-content/themes/superio/extra","extra")
    write(state/"raspitajse-staging.commit",new_head+"\n")
    write(state/"raspitajse-staging-communications.manifest","mutated")
    state_before_failure=(state/"raspitajse-staging.commit").read_bytes(),(state/"raspitajse-staging-communications.manifest").read_bytes()
    injected=base/"restore-failure.sh"
    injected.write_text(prepared.replace("        payload = final /", "        if index == 1: raise RuntimeError('injected content failure')\n        payload = final /"))
    run("bash",str(injected),"restore-release","feature/test",head,success=False)
    assert state_before_failure==((state/"raspitajse-staging.commit").read_bytes(),(state/"raspitajse-staging-communications.manifest").read_bytes())
    mode("restore-release",prev=head)
    assert vendor_before==fingerprint(site/"wp-content/themes/superio")
    assert not (state/"raspitajse-staging-communications.manifest").exists()
    print("PASS two pairs, vendor full restore, absent manifest and content failure preserves state")

    # Third pair uses separate checkout workers and a directory-lock barrier.
    run("git","commit","-qm","third release","--allow-empty")
    third=run("git","rev-parse","HEAD")
    run("git","push","-q")
    write(state/"raspitajse-staging.commit",new_head+"\n")
    failed=base/"snapshot-failure.sh"
    failed.write_text(prepared.replace("        verify(temporary)", "        verify(temporary)\n        raise RuntimeError('injected publication failure')"))
    run("bash",str(failed),"snapshot-release","feature/test",new_head,success=False)
    store=state/"raspitajse-release-snapshots-v1"
    assert not list(store.glob(".*.tmp.*"))
    worker=base/"worker"
    run("git","clone","-q","-b","feature/test",str(remote),str(worker))
    worker_script=base/"worker.sh"
    worker_script.write_text(prepared.replace(str(repo),str(worker)))
    descriptor=os.open(store,os.O_RDONLY)
    fcntl.flock(descriptor,fcntl.LOCK_EX)
    commands=[["bash",str(script),"snapshot-release","feature/test",new_head],["bash",str(worker_script),"snapshot-release","feature/test",new_head]]
    processes=[subprocess.Popen(c,stdout=subprocess.PIPE,stderr=subprocess.STDOUT,text=True) for c in commands]
    try:
        deadline=time.monotonic()+20
        while len(list(store.glob(".*.tmp.*/metadata.json")))<2 and time.monotonic()<deadline:
            time.sleep(0.02)
        assert len(list(store.glob(".*.tmp.*/metadata.json")))==2
    finally:
        fcntl.flock(descriptor,fcntl.LOCK_UN)
        os.close(descriptor)
    outputs=[p.communicate(timeout=20)[0] for p in processes]
    assert sorted(p.returncode for p in processes)==[0,1],outputs
    assert not list(store.glob(".*.tmp.*"))
    assert original==fingerprint(snapshot)
    print("PASS deterministic concurrent publication and bounded failure cleanup")
    run("git","commit","-qm","absent marker","--allow-empty")
    run("git","push","-q")
    (state/"raspitajse-staging.commit").unlink()
    shutil.rmtree(site/retired)
    mode("snapshot-release",prev=previous)
    write(site/retired/"created", "created-after-snapshot")
    write(state/"raspitajse-staging.commit",run("git","rev-parse","HEAD")+"\n")
    mode("restore-release",prev=previous)
    assert not (state/"raspitajse-staging.commit").exists()
    assert not (site/retired).exists()
    print("PASS absent marker and retired root restored without invented content")
    print("PASS transactional package/owned/absence/drift/state restore; duplicate, corruption and wrong-key rejection")
PYTHON
