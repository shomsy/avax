#!/usr/bin/env python3
"""runtime-debug.py — Operational Debugging for Agent Harness.

Provides:
  timeline    — Execution timeline visualization (text-based)
  replay-diff — Compare two execution manifests
  trace       — Governance resolution trace for a task
  remediate   — Recommendation engine for common failures

Usage:
  python3 runtime-debug.py timeline [--dir <dir>]
  python3 runtime-debug.py replay-diff <exec_id_1> <exec_id_2> [--dir <dir>]
  python3 runtime-debug.py remediate [--dir <dir>]
"""

import os
import sys
import json
import time

EXEC_DIR = ".agents/management/evidence/execution"


def _target_dir(path=None):
    return os.path.normpath(path) if path else "."


def _load_manifest(target_dir, exec_id):
    """Load an execution manifest by ID."""
    td = _target_dir(target_dir)
    exec_dir = os.path.join(td, EXEC_DIR)
    # Try exact match first
    path = os.path.join(exec_dir, f"execution-manifest-{exec_id}.json")
    if os.path.exists(path):
        with open(path, "r") as f:
            return json.load(f)
    # Try glob
    if not exec_id.startswith("exec-"):
        exec_id = f"exec-{exec_id}"
    for fname in os.listdir(exec_dir):
        if exec_id in fname and fname.endswith(".json"):
            with open(os.path.join(exec_dir, fname), "r") as f:
                return json.load(f)
    return None


def _list_manifests(target_dir):
    """List all execution manifests sorted by timestamp."""
    td = _target_dir(target_dir)
    exec_dir = os.path.join(td, EXEC_DIR)
    if not os.path.exists(exec_dir):
        return []
    manifests = []
    for fname in os.listdir(exec_dir):
        if fname.startswith("execution-manifest-") and fname.endswith(".json"):
            path = os.path.join(exec_dir, fname)
            with open(path, "r") as f:
                try:
                    data = json.load(f)
                    manifests.append(data)
                except json.JSONDecodeError:
                    pass
    # Sort by lifecycle history first timestamp
    manifests.sort(key=lambda m: m.get("lifecycle_history", [{}])[0].get("timestamp", 0))
    return manifests


# ---------------------------------------------------------------------------
# Timeline
# ---------------------------------------------------------------------------

def timeline(target_dir="."):
    """Print execution timeline."""
    manifests = _list_manifests(target_dir)
    if not manifests:
        print("No execution manifests found.")
        return True

    print("=" * 70)
    print(" EXECUTION TIMELINE")
    print("=" * 70)

    for m in manifests:
        exec_id = m.get("execution_id", "unknown")
        task = m.get("task", "unknown")
        state = m.get("lifecycle_state", "unknown")
        tier = m.get("trust_tier", "unknown")
        telemetry = m.get("telemetry", {})
        duration = telemetry.get("total_duration_ms", 0)
        hmac = m.get("hmac_seal", "")[:12]

        # Timeline bar
        history = m.get("lifecycle_history", [])
        if len(history) >= 2:
            start = history[0].get("timestamp", 0)
            end = history[-1].get("timestamp", 0)
            elapsed = (end - start) * 1000
        else:
            elapsed = duration

        state_icon = {
            "REPLAYABLE": "[OK]",
            "FAILED": "[!!]",
            "ROLLED_BACK": "[<<]",
            "INVALIDATED": "[XX]",
            "EXPIRED": "[--]",
        }.get(state, "[??]")

        print(f"  {state_icon} {exec_id}")
        print(f"     Task:      {task}")
        print(f"     State:     {state}")
        print(f"     Tier:      {tier}")
        print(f"     Duration:  {elapsed:.0f}ms (governance: {telemetry.get('governance_resolution_overhead_ms', 0):.0f}ms)")
        print(f"     HMAC Seal: {hmac}...")
        print()

    # Summary
    total = len(manifests)
    ok = sum(1 for m in manifests if m.get("lifecycle_state") == "REPLAYABLE")
    failed = sum(1 for m in manifests if m.get("lifecycle_state") == "FAILED")
    rolled = sum(1 for m in manifests if m.get("lifecycle_state") == "ROLLED_BACK")
    print("-" * 70)
    print(f"  Total: {total} | OK: {ok} | Failed: {failed} | Rolled back: {rolled}")
    print("=" * 70)
    return True


# ---------------------------------------------------------------------------
# Replay Diff
# ---------------------------------------------------------------------------

def replay_diff(exec_id_1, exec_id_2, target_dir="."):
    """Compare two execution manifests."""
    m1 = _load_manifest(target_dir, exec_id_1)
    m2 = _load_manifest(target_dir, exec_id_2)

    if not m1:
        print(f"Manifest not found: {exec_id_1}")
        return False
    if not m2:
        print(f"Manifest not found: {exec_id_2}")
        return False

    print("=" * 70)
    print(f" REPLAY DIFF: {m1['execution_id']} vs {m2['execution_id']}")
    print("=" * 70)

    # Compare fields
    fields = ["task", "trust_tier", "domain_scope", "lifecycle_state"]
    for field in fields:
        v1 = m1.get(field, "N/A")
        v2 = m2.get(field, "N/A")
        match = "==" if v1 == v2 else "!="
        print(f"  {field:20s} [{match}] {v1} -> {v2}")

    # Compare telemetry
    t1 = m1.get("telemetry", {})
    t2 = m2.get("telemetry", {})
    print()
    print("  Telemetry:")
    for key in ["total_duration_ms", "governance_resolution_overhead_ms",
                "context_expansion_budget_bytes", "memory_usage_mb"]:
        v1 = t1.get(key, "N/A")
        v2 = t2.get(key, "N/A")
        print(f"    {key:35s} {v1} vs {v2}")

    # Compare mutations
    j1 = m1.get("mutation_journal", {})
    j2 = m2.get("mutation_journal", {})
    print()
    print("  Mutations:")
    for key in ["created", "modified", "deleted"]:
        c1 = len(j1.get("mutations", {}).get(key, []))
        c2 = len(j2.get("mutations", {}).get(key, []))
        print(f"    {key:10s} {c1} vs {c2}")

    # Compare HMAC seals
    h1 = m1.get("hmac_seal", "N/A")[:16]
    h2 = m2.get("hmac_seal", "N/A")[:16]
    print(f"\n  HMAC Seal: {h1}... vs {h2}...")

    print("=" * 70)
    return True


# ---------------------------------------------------------------------------
# Remediation Engine
# ---------------------------------------------------------------------------

def remediate(target_dir="."):
    """Analyze execution history and recommend remediation actions."""
    manifests = _list_manifests(target_dir)
    recommendations = []

    if not manifests:
        print("No execution history to analyze.")
        return True

    # Pattern 1: Repeated failures on same task
    task_failures = {}
    for m in manifests:
        if m.get("lifecycle_state") in ("FAILED", "ROLLED_BACK"):
            task = m.get("task", "unknown")
            task_failures[task] = task_failures.get(task, 0) + 1

    for task, count in task_failures.items():
        if count >= 2:
            recommendations.append({
                "severity": "HIGH",
                "issue": f"Task '{task}' failed {count} times",
                "action": "Review trust tier and domain scope for this task. "
                          "Consider increasing trust tier or adjusting scope boundaries.",
            })

    # Pattern 2: High governance overhead
    for m in manifests:
        telemetry = m.get("telemetry", {})
        overhead = telemetry.get("governance_resolution_overhead_ms", 0)
        total = telemetry.get("total_duration_ms", 1)
        if total > 0 and overhead / total > 0.8:
            recommendations.append({
                "severity": "MEDIUM",
                "issue": f"High governance overhead in {m.get('execution_id', 'unknown')} "
                         f"({overhead:.0f}ms / {total:.0f}ms = {overhead/total*100:.0f}%)",
                "action": "Reduce must_read rule files or cache governance resolution results.",
            })

    # Pattern 3: Trust boundary violations
    violations = []
    for m in manifests:
        journal = m.get("mutation_journal", {})
        violations.extend(journal.get("violations_detected", []))

    if violations:
        violation_types = set()
        for v in violations:
            vtype = v.split(":")[0] if ":" in v else v
            violation_types.add(vtype)
        recommendations.append({
            "severity": "HIGH",
            "issue": f"{len(violations)} trust boundary violations detected ({len(violation_types)} types)",
            "action": f"Violation types: {', '.join(sorted(violation_types))}. "
                      "Review trust tier assignments and sandbox policies.",
        })

    # Pattern 4: Evidence bloat
    td = _target_dir(target_dir)
    evidence_dir = os.path.join(td, ".agents/management/evidence")
    if os.path.exists(evidence_dir):
        total_size = sum(
            os.path.getsize(os.path.join(dp, fn))
            for dp, _, fns in os.walk(evidence_dir)
            for fn in fns
        )
        size_mb = total_size / (1024 * 1024)
        if size_mb > 50:
            recommendations.append({
                "severity": "LOW",
                "issue": f"Evidence directory is {size_mb:.0f}MB",
                "action": "Run evidence-lifecycle.py compact or increase retention policy.",
            })

    # Pattern 5: Expired leases during execution
    expired_count = sum(
        1 for m in manifests
        if m.get("telemetry", {}).get("lease_expired_during_execution", False)
    )
    if expired_count > 0:
        recommendations.append({
            "severity": "MEDIUM",
            "issue": f"{expired_count} executions had expired leases",
            "action": "Increase lease_duration_sec in capability tokens for long-running tasks.",
        })

    # Print results
    print("=" * 70)
    print(" REMEDIATION RECOMMENDATIONS")
    print("=" * 70)

    if not recommendations:
        print("  No issues detected — system is operating within normal parameters.")
    else:
        # Sort by severity
        severity_order = {"HIGH": 0, "MEDIUM": 1, "LOW": 2}
        recommendations.sort(key=lambda r: severity_order.get(r["severity"], 3))

        for i, rec in enumerate(recommendations, 1):
            print(f"  {i}. [{rec['severity']}] {rec['issue']}")
            print(f"     Action: {rec['action']}")
            print()

    print(f"  Analyzed {len(manifests)} executions, found {len(recommendations)} recommendations.")
    print("=" * 70)
    return True


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 runtime-debug.py <command> [args] [--dir <dir>]")
        print()
        print("Commands:")
        print("  timeline                              Execution timeline visualization")
        print("  replay-diff <id1> <id2>               Compare two execution manifests")
        print("  remediate                             Remediation recommendations")
        return 1

    command = sys.argv[1]
    target_dir = "."
    args = sys.argv[2:]
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]

    if command == "timeline":
        return 0 if timeline(target_dir) else 1
    elif command == "replay-diff":
        if len(args) < 2:
            print("Usage: runtime-debug.py replay-diff <id1> <id2> [--dir <dir>]", file=sys.stderr)
            return 1
        return 0 if replay_diff(args[0], args[1], target_dir) else 1
    elif command == "remediate":
        return 0 if remediate(target_dir) else 1
    else:
        print(f"Unknown command: {command}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
