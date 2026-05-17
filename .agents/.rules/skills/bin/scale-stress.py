#!/usr/bin/env python3
"""scale-stress.py — Scale & Concurrency Stress Tests for Agent Harness.

Stress tests:
  - huge repository simulation (many files, deep directories)
  - thousands of executions (manifest generation + validation)
  - parallel replay workloads
  - large evidence histories
  - nonce/revocation registry growth

Usage:
  python3 scale-stress.py run [--dir <dir>] [--executions <N>] [--files <N>]
"""

import os
import sys
import json
import time
import hashlib
import uuid
import tempfile
import shutil
from concurrent.futures import ThreadPoolExecutor

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _target_dir(path=None):
    return os.path.normpath(path) if path else "."


def _measure(name, fn):
    """Run a function and return (name, duration_ms, result)."""
    start = time.time()
    result = fn()
    duration = (time.time() - start) * 1000
    return name, duration, result


# ---------------------------------------------------------------------------
# Test: Massive File Scanning
# ---------------------------------------------------------------------------

def test_massive_scan(file_count=1000, depth=5):
    """Test file scanning performance on a large directory tree."""
    tmpdir = tempfile.mkdtemp(prefix="stress-scan-")
    try:
        # Create directory tree
        dirs = [tmpdir]
        for d in range(depth):
            new_dirs = []
            for parent in dirs[-min(d + 1, 10):]:  # Limit branching
                for i in range(min(5, file_count // max(len(dirs), 1) + 1)):
                    subdir = os.path.join(parent, f"dir-{d}-{i}")
                    os.makedirs(subdir, exist_ok=True)
                    new_dirs.append(subdir)
            dirs.extend(new_dirs)

        # Create files
        for i in range(file_count):
            dir_idx = i % max(len(dirs) - 1, 1)
            filepath = os.path.join(dirs[dir_idx + 1] if dir_idx + 1 < len(dirs) else tmpdir, f"file-{i}.txt")
            with open(filepath, "w") as f:
                f.write(f"content-{i}\n" * 10)

        # Scan (simulating scan_files_state)
        start = time.time()
        state = {}
        for root, dirs_list, files in os.walk(tmpdir):
            dirs_list[:] = [d for d in dirs_list if d != ".git"]
            for file in files:
                filepath = os.path.join(root, file)
                rel_path = os.path.relpath(filepath, tmpdir)
                hasher = hashlib.sha256()
                with open(filepath, "rb") as f:
                    while True:
                        chunk = f.read(65536)
                        if not chunk:
                            break
                        hasher.update(chunk)
                state[rel_path] = hasher.hexdigest()
        duration = (time.time() - start) * 1000

        return {
            "test": "massive_scan",
            "files": len(state),
            "duration_ms": duration,
            "files_per_sec": len(state) / max(duration / 1000, 0.001),
            "status": "PASS" if duration < 5000 else "WARN",
        }
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)


# ---------------------------------------------------------------------------
# Test: Execution Manifest Generation
# ---------------------------------------------------------------------------

def test_manifest_generation(exec_count=1000):
    """Test generating and writing execution manifests at scale."""
    tmpdir = tempfile.mkdtemp(prefix="stress-manifest-")
    exec_dir = os.path.join(tmpdir, ".agents/management/evidence/execution")
    os.makedirs(exec_dir, exist_ok=True)

    try:
        start = time.time()
        for i in range(exec_count):
            manifest = {
                "execution_id": f"exec-stress-{i:06d}",
                "task": f"stress-test-task-{i}",
                "trust_tier": "READ_ONLY",
                "domain_scope": "security",
                "lifecycle_state": "REPLAYABLE",
                "lifecycle_history": [
                    {"state": "CREATED", "timestamp": time.time()},
                    {"state": "REPLAYABLE", "timestamp": time.time() + 0.01},
                ],
                "telemetry": {
                    "total_duration_ms": 10.0,
                    "governance_resolution_overhead_ms": 5.0,
                },
                "mutation_journal": {
                    "mutations": {"created": [], "modified": [], "deleted": []},
                    "violations_detected": [],
                },
                "integrity_seal": hashlib.sha256(str(i).encode()).hexdigest(),
            }
            path = os.path.join(exec_dir, f"execution-manifest-exec-stress-{i:06d}.json")
            with open(path, "w") as f:
                json.dump(manifest, f)
        duration = (time.time() - start) * 1000

        # Verify by reading back
        read_start = time.time()
        count = 0
        for fname in os.listdir(exec_dir):
            if fname.startswith("execution-manifest-"):
                with open(os.path.join(exec_dir, fname), "r") as f:
                    json.load(f)
                count += 1
        read_duration = (time.time() - read_start) * 1000

        return {
            "test": "manifest_generation",
            "executions": exec_count,
            "write_duration_ms": duration,
            "write_per_sec": exec_count / max(duration / 1000, 0.001),
            "read_duration_ms": read_duration,
            "read_count": count,
            "read_per_sec": count / max(read_duration / 1000, 0.001),
            "status": "PASS" if duration < 10000 else "WARN",
        }
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)


# ---------------------------------------------------------------------------
# Test: Nonce Registry Growth
# ---------------------------------------------------------------------------

def test_nonce_growth(nonce_count=10000):
    """Test nonce registry with large number of entries."""
    tmpdir = tempfile.mkdtemp(prefix="stress-nonce-")
    reg_path = os.path.join(tmpdir, "nonce-registry.jsonl")

    start = time.time()
    nonces = {}
    for i in range(nonce_count):
        nonce = str(uuid.uuid4())
        nonces[nonce] = {
            "token_id": f"token-{i}",
            "registered_at": time.time(),
            "expires_at": time.time() + 3600,
        }

    # Persist
    with open(reg_path, "w") as f:
        for nonce, info in nonces.items():
            f.write(json.dumps({"nonce": nonce, **info}) + "\n")
    write_duration = (time.time() - start) * 1000

    # Reload
    reload_start = time.time()
    reloaded = {}
    with open(reg_path, "r") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            entry = json.loads(line)
            reloaded[entry["nonce"]] = entry
    reload_duration = (time.time() - reload_start) * 1000

    file_size = os.path.getsize(reg_path)

    return {
        "test": "nonce_growth",
        "nonces": nonce_count,
        "write_duration_ms": write_duration,
        "reload_duration_ms": reload_duration,
        "file_size_kb": file_size / 1024,
        "status": "PASS" if reload_duration < 2000 else "WARN",
    }


# ---------------------------------------------------------------------------
# Test: Parallel Replay
# ---------------------------------------------------------------------------

def _replay_single(args):
    """Simulate a single replay operation."""
    idx, total = args
    # Simulate manifest loading + hash computation
    manifest_data = f"replay-data-{idx}-{uuid.uuid4()}"
    hash_result = hashlib.sha256(manifest_data.encode()).hexdigest()
    time.sleep(0.001)  # Simulate minimal I/O
    return idx, hash_result


def test_parallel_replay(exec_count=500, workers=4):
    """Test parallel replay workload."""
    start = time.time()
    results = []
    with ThreadPoolExecutor(max_workers=workers) as executor:
        for result in executor.map(_replay_single, [(i, exec_count) for i in range(exec_count)]):
            results.append(result)
    duration = (time.time() - start) * 1000

    return {
        "test": "parallel_replay",
        "executions": exec_count,
        "workers": workers,
        "duration_ms": duration,
        "per_sec": exec_count / max(duration / 1000, 0.001),
        "status": "PASS" if duration < 5000 else "WARN",
    }


# ---------------------------------------------------------------------------
# Test: Evidence History Growth
# ---------------------------------------------------------------------------

def test_evidence_growth(entry_count=1000):
    """Test evidence directory with many manifest files."""
    tmpdir = tempfile.mkdtemp(prefix="stress-evidence-")
    exec_dir = os.path.join(tmpdir, "execution")
    os.makedirs(exec_dir, exist_ok=True)

    start = time.time()
    for i in range(entry_count):
        manifest = {
            "execution_id": f"exec-evidence-{i:06d}",
            "task": f"task-{i}",
            "lifecycle_state": "REPLAYABLE",
            "telemetry": {"total_duration_ms": 10.0},
            "hmac_seal": hashlib.sha256(str(i).encode()).hexdigest(),
        }
        with open(os.path.join(exec_dir, f"execution-manifest-exec-evidence-{i:06d}.json"), "w") as f:
            json.dump(manifest, f)
    write_duration = (time.time() - start) * 1000

    # List directory (simulating graph build)
    list_start = time.time()
    file_count = len(os.listdir(exec_dir))
    list_duration = (time.time() - list_start) * 1000

    total_size = sum(
        os.path.getsize(os.path.join(exec_dir, f))
        for f in os.listdir(exec_dir)
    )

    return {
        "test": "evidence_growth",
        "entries": entry_count,
        "write_duration_ms": write_duration,
        "list_duration_ms": list_duration,
        "total_size_kb": total_size / 1024,
        "status": "PASS" if list_duration < 1000 else "WARN",
    }


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def run_stress_tests(target_dir=".", exec_count=1000, file_count=1000):
    """Run all stress tests."""
    results = []

    tests = [
        ("massive_scan", lambda: test_massive_scan(file_count)),
        ("manifest_generation", lambda: test_manifest_generation(exec_count)),
        ("nonce_growth", lambda: test_nonce_growth(exec_count * 10)),
        ("parallel_replay", lambda: test_parallel_replay(exec_count // 2)),
        ("evidence_growth", lambda: test_evidence_growth(exec_count)),
    ]

    print("=" * 70)
    print(" SCALE & CONCURRENCY STRESS TESTS")
    print("=" * 70)
    print(f"  Target executions per test: {exec_count}")
    print(f"  Target files per test: {file_count}")
    print()

    all_pass = True
    for name, fn in tests:
        try:
            name, duration, result = _measure(name, fn)
            results.append(result)
            icon = "PASS" if result.get("status") == "PASS" else "WARN"
            print(f"  [{icon}] {name}: {duration:.0f}ms")
            if icon == "WARN":
                all_pass = False
        except Exception as e:
            print(f"  [FAIL] {name}: {e}")
            all_pass = False

    print()
    print("-" * 70)
    print("  Summary:")
    for r in results:
        print(f"    {r['test']}: {r['status']} ({r.get('duration_ms', 0):.0f}ms)")
    print("=" * 70)

    return all_pass


def main():
    if len(sys.argv) < 2:
        print("Usage: python3 scale-stress.py run [--dir <dir>] [--executions <N>] [--files <N>]")
        return 1

    command = sys.argv[1]
    target_dir = "."
    exec_count = 1000
    file_count = 1000
    args = sys.argv[2:]
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]
        elif args[idx] == "--executions" and idx + 1 < len(args):
            exec_count = int(args[idx + 1])
        elif args[idx] == "--files" and idx + 1 < len(args):
            file_count = int(args[idx + 1])

    if command == "run":
        ok = run_stress_tests(target_dir, exec_count, file_count)
        return 0 if ok else 1
    else:
        print(f"Unknown command: {command}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
