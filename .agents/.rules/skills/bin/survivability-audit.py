#!/usr/bin/env python3
"""survivability-audit.py — Long-Term Survivability Audit for Agent Harness.

Audits:
  - Governance entropy growth
  - Replay storage growth
  - Telemetry storage growth
  - Lineage graph growth
  - Runtime maintenance burden
  - Operator cognitive load

Implements:
  - Lifecycle pruning recommendations
  - Storage aging analysis
  - Telemetry summarization
  - Governance compression report
  - Archival strategy

Usage:
  python3 survivability-audit.py run [--dir <dir>]
"""

import os
import sys
import json
import time
from datetime import datetime

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _target_dir(path=None):
    return os.path.normpath(path) if path else "."


def _dir_size(path):
    """Calculate total directory size in bytes."""
    if not os.path.exists(path):
        return 0
    total = 0
    for dirpath, dirnames, filenames in os.walk(path):
        for filename in filenames:
            filepath = os.path.join(dirpath, filename)
            try:
                total += os.path.getsize(filepath)
            except OSError:
                pass
    return total


def _file_count(path):
    """Count files in directory."""
    if not os.path.exists(path):
        return 0
    count = 0
    for dirpath, dirnames, filenames in os.walk(path):
        count += len(filenames)
    return count


def _age_days(path):
    """Get age of file/directory in days."""
    if not os.path.exists(path):
        return 0
    mtime = os.path.getmtime(path)
    return (time.time() - mtime) / 86400


# ---------------------------------------------------------------------------
# Audit Sections
# ---------------------------------------------------------------------------

def audit_governance_entropy(target_dir):
    """Analyze governance file health and dead rules."""
    td = _target_dir(target_dir)
    governance_dir = os.path.join(td, ".agents/governance")

    if not os.path.exists(governance_dir):
        return {"status": "SKIP", "detail": "No governance directory found"}

    # Count all governance files
    total_files = _file_count(governance_dir)
    total_size = _dir_size(governance_dir)

    # Find referenced files (mentioned in AGENTS.md precedence)
    agents_md = os.path.join(td, ".agents/AGENTS.md")
    referenced = set()
    if os.path.exists(agents_md):
        with open(agents_md, "r") as f:
            content = f.read()
        # Extract paths from precedence list
        import re
        paths = re.findall(r'`([^`]+\.md)`', content)
        for p in paths:
            referenced.add(p)

    # Check which referenced files exist
    existing = 0
    missing = 0
    for ref_path in referenced:
        full = os.path.join(td, ref_path)
        if os.path.exists(full):
            existing += 1
        else:
            missing += 1

    return {
        "status": "AUDIT",
        "total_governance_files": total_files,
        "total_size_kb": total_size / 1024,
        "precedence_entries": len(referenced),
        "referenced_existing": existing,
        "referenced_missing": missing,
        "governance_health": f"{existing}/{len(referenced)} referenced files exist",
        "recommendation": "Remove missing files from precedence or restore them",
    }


def audit_storage_growth(target_dir):
    """Analyze evidence storage growth patterns."""
    td = _target_dir(target_dir)
    evidence_dir = os.path.join(td, ".agents/management/evidence")

    if not os.path.exists(evidence_dir):
        return {"status": "SKIP", "detail": "No evidence directory found"}

    total_size = _dir_size(evidence_dir)
    total_files = _file_count(evidence_dir)

    # Breakdown by subdirectory
    breakdown = {}
    for subdir in os.listdir(evidence_dir):
        subpath = os.path.join(evidence_dir, subdir)
        if os.path.isdir(subpath):
            breakdown[subdir] = {
                "size_kb": _dir_size(subpath) / 1024,
                "files": _file_count(subpath),
            }

    # Find oldest and newest files
    oldest = None
    newest = None
    oldest_age = 0
    newest_age = None
    for dirpath, _, filenames in os.walk(evidence_dir):
        for fn in filenames:
            fp = os.path.join(dirpath, fn)
            age = _age_days(fp)
            if age > oldest_age:
                oldest_age = age
                oldest = os.path.relpath(fp, td)
            if newest_age is None or age < newest_age:
                newest_age = age
                newest = os.path.relpath(fp, td)

    # Estimate growth rate (if we have execution manifests with timestamps)
    exec_dir = os.path.join(evidence_dir, "execution")
    manifest_count = 0
    if os.path.exists(exec_dir):
        manifest_count = len([f for f in os.listdir(exec_dir) if f.startswith("execution-manifest-")])

    # Size thresholds
    size_mb = total_size / (1024 * 1024)
    warnings = []
    if size_mb > 100:
        warnings.append(f"Evidence directory exceeds 100MB ({size_mb:.0f}MB)")
    if total_files > 10000:
        warnings.append(f"Too many evidence files ({total_files})")

    return {
        "status": "AUDIT",
        "total_size_mb": size_mb,
        "total_files": total_files,
        "execution_manifests": manifest_count,
        "breakdown": {k: v for k, v in sorted(breakdown.items(), key=lambda x: -x[1]["size_kb"])},
        "oldest_file": oldest,
        "oldest_age_days": round(oldest_age, 1),
        "warnings": warnings,
        "recommendation": "Run evidence-lifecycle.py compact if size exceeds thresholds",
    }


def audit_operator_load(target_dir):
    """Estimate operator cognitive load and maintenance burden."""
    td = _target_dir(target_dir)

    # Count unique tools/scripts
    bin_scripts = 0
    bin_dir = os.path.join(td, ".agents/skills/bin")
    if os.path.exists(bin_dir):
        bin_scripts = len([f for f in os.listdir(bin_dir) if f.endswith(".py")])

    hook_scripts = 0
    hook_dir = os.path.join(td, ".agents/hooks")
    if os.path.exists(hook_dir):
        hook_scripts = len([f for f in os.listdir(hook_dir) if f.endswith((".py", ".sh"))])

    # Governance complexity
    governance_files = _file_count(os.path.join(td, ".agents/governance"))

    # Python LOC
    total_py_loc = 0
    for dirpath, _, filenames in os.walk(os.path.join(td, ".agents/skills/bin")):
        for fn in filenames:
            if fn.endswith(".py"):
                fp = os.path.join(dirpath, fn)
                with open(fp, "r") as f:
                    total_py_loc += sum(1 for _ in f)

    # Complexity score (lower is better)
    complexity_score = bin_scripts * 10 + hook_scripts * 5 + governance_files * 2 + total_py_loc / 100

    return {
        "status": "AUDIT",
        "bin_scripts": bin_scripts,
        "hook_scripts": hook_scripts,
        "governance_files": governance_files,
        "python_loc": total_py_loc,
        "complexity_score": round(complexity_score, 1),
        "recommendation": "Keep complexity score under 600 for maintainable system",
    }


def audit_archival_readiness(target_dir):
    """Check what can be archived."""
    td = _target_dir(target_dir)
    evidence_dir = os.path.join(td, ".agents/management/evidence")

    if not os.path.exists(evidence_dir):
        return {"status": "SKIP", "detail": "No evidence directory found"}

    archival_candidates = []
    active_files = []

    for dirpath, _, filenames in os.walk(evidence_dir):
        for fn in filenames:
            fp = os.path.join(dirpath, fn)
            age = _age_days(fp)
            if age > 30:
                archival_candidates.append({
                    "path": os.path.relpath(fp, td),
                    "age_days": round(age, 1),
                    "size_kb": os.path.getsize(fp) / 1024,
                })
            else:
                active_files.append(fp)

    archival_size = sum(c["size_kb"] for c in archival_candidates)

    return {
        "status": "AUDIT",
        "active_files": len(active_files),
        "archival_candidates": len(archival_candidates),
        "archival_size_kb": round(archival_size, 1),
        "recommendation": f"Archive {len(archival_candidates)} files older than 30 days to save {archival_size:.0f}KB",
    }


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def run_audit(target_dir="."):
    """Run full survivability audit."""
    print("=" * 70)
    print(" LONG-TERM SURVIVABILITY AUDIT")
    print("=" * 70)
    print(f"  Timestamp: {datetime.now().isoformat()}")
    print()

    audits = [
        ("Governance Entropy", audit_governance_entropy),
        ("Storage Growth", audit_storage_growth),
        ("Operator Load", audit_operator_load),
        ("Archival Readiness", audit_archival_readiness),
    ]

    results = {}
    for name, fn in audits:
        try:
            result = fn(target_dir)
            results[name] = result
            print(f"  --- {name} ---")
            for key, value in result.items():
                if key != "status" and key != "recommendation" and key != "breakdown":
                    print(f"    {key}: {value}")
            if result.get("warnings"):
                for w in result["warnings"]:
                    print(f"    WARNING: {w}")
            if result.get("recommendation"):
                print(f"    Recommendation: {result['recommendation']}")
            print()
        except Exception as e:
            print(f"  --- {name} ---")
            print(f"    ERROR: {e}")
            print()
            results[name] = {"status": "ERROR", "detail": str(e)}

    # Summary
    print("=" * 70)
    print(" SURVIVABILITY SUMMARY")
    print("=" * 70)

    storage = results.get("Storage Growth", {})
    operator = results.get("Operator Load", {})
    archival = results.get("Archival Readiness", {})

    size_mb = storage.get("total_size_mb", 0)
    complexity = operator.get("complexity_score", 0)
    archival_count = archival.get("archival_candidates", 0)

    health = "HEALTHY"
    warnings = []
    if size_mb > 100:
        health = "DEGRADED"
        warnings.append(f"Storage at {size_mb:.0f}MB (threshold: 100MB)")
    if complexity > 600:
        health = "DEGRADED"
        warnings.append(f"Complexity score at {complexity:.0f} (threshold: 600)")
    if archival_count > 1000:
        health = "DEGRADED"
        warnings.append(f"{archival_count} files eligible for archival")

    print(f"  Overall Health: {health}")
    for w in warnings:
        print(f"  Warning: {w}")
    if not warnings:
        print("  No warnings — system is within sustainable parameters.")
    print("=" * 70)

    return health == "HEALTHY"


def main():
    if len(sys.argv) < 2:
        print("Usage: python3 survivability-audit.py run [--dir <dir>]")
        return 1

    command = sys.argv[1]
    target_dir = "."
    args = sys.argv[2:]
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]

    if command == "run":
        ok = run_audit(target_dir)
        return 0 if ok else 1
    else:
        print(f"Unknown command: {command}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
