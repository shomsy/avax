#!/usr/bin/env python3
# observability.py — Phase 8: Operational UX & Observability
#
# Diagnostic tools that give operators fast, structured insight into execution
# history, replay health, governance resolution, authority chains, failures,
# mutations, and drift.
#
# Usage:
#   python3 observability.py explain <exec_id> [--dir <dir>]
#   python3 observability.py explain-last [<n>] [--dir <dir>]
#   python3 observability.py replay <exec_id> [--dir <dir>]
#   python3 observability.py replay-health [--dir <dir>]
#   python3 observability.py governance [--dir <dir>]
#   python3 observability.py timeline [--days N] [--dir <dir>]
#   python3 observability.py timeline-by-state <state> [--dir <dir>]
#   python3 observability.py authority <exec_id> [--dir <dir>]
#   python3 observability.py delegation-tree <token_id> [--dir <dir>]
#   python3 observability.py find-authorized <token_id> [--dir <dir>]
#   python3 observability.py remediate <exec_id> [--dir <dir>]
#   python3 observability.py health [--dir <dir>]
#   python3 observability.py mutations <exec_id> [--dir <dir>]
#   python3 observability.py trust-violations [--days N] [--dir <dir>]
#   python3 observability.py replay-diff <exec_id> <replay_run> [--dir <dir>]
#   python3 observability.py drift <exec_id> [--dir <dir>]

import os
import sys
import json
import time
import hashlib
from datetime import datetime, timezone

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

EVIDENCE_BASE = ".agents/management/evidence"
EXECUTION_DIR = os.path.join(EVIDENCE_BASE, "execution")
REPLAY_DIR = os.path.join(EVIDENCE_BASE, "replay")
GENERATED_DIR = os.path.join(EVIDENCE_BASE, "generated")
IDENTITY_DIR = os.path.join(EVIDENCE_BASE, "identity")
TRUST_DIR = os.path.join(EVIDENCE_BASE, "security")
GOVERNANCE_DIR = ".agents/governance"

OUTPUT_DIR = os.path.join(EVIDENCE_BASE, "evidence", "generated")

TERMINAL_STATES = {"FAILED", "ROLLED_BACK", "INVALIDATED", "EXPIRED", "REPLAYABLE"}

# ---------------------------------------------------------------------------
# Utility helpers
# ---------------------------------------------------------------------------


def _ensure_dir(path):
    os.makedirs(path, exist_ok=True)


def _read_json(path):
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)


def _write_json(path, data):
    _ensure_dir(os.path.dirname(path))
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2)


def _jsonl_read(path):
    if not os.path.exists(path):
        return []
    records = []
    with open(path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line:
                records.append(json.loads(line))
    return records


def _now_iso():
    return datetime.now(timezone.utc).isoformat()


def _epoch_to_iso(epoch):
    try:
        return datetime.fromtimestamp(epoch, tz=timezone.utc).isoformat()
    except (OSError, ValueError, TypeError):
        return str(epoch)


def _format_duration(ms):
    if ms < 1000:
        return f"{ms:.0f}ms"
    return f"{ms / 1000:.2f}s"


def _find_manifest(exec_dir, exec_id):
    candidate = os.path.join(exec_dir, f"execution-manifest-{exec_id}.json")
    if os.path.exists(candidate):
        return candidate
    for fname in os.listdir(exec_dir):
        if fname.startswith("execution-manifest-") and fname.endswith(".json"):
            eid = fname[len("execution-manifest-") : -len(".json")]
            if eid == exec_id:
                return os.path.join(exec_dir, fname)
    return None


def _list_manifests(exec_dir, limit=None):
    manifests = []
    if not os.path.isdir(exec_dir):
        return manifests
    for fname in sorted(os.listdir(exec_dir)):
        if fname.startswith("execution-manifest-") and fname.endswith(".json"):
            path = os.path.join(exec_dir, fname)
            try:
                data = _read_json(path)
                manifests.append(data)
            except Exception:
                pass
    if limit:
        manifests = manifests[-limit:]
    return manifests


def _load_replay_snapshot(exec_id, target_dir):
    path = os.path.join(target_dir, REPLAY_DIR, f"snapshot-{exec_id}.json")
    if os.path.exists(path):
        return _read_json(path)
    # Try replay manifest
    rpath = os.path.join(target_dir, REPLAY_DIR, f"replay-{exec_id}.json")
    if os.path.exists(rpath):
        return _read_json(rpath)
    return None


def _load_identity_chain(target_dir):
    path = os.path.join(target_dir, IDENTITY_DIR, "delegation-lineage.jsonl")
    return _jsonl_read(path)


def _load_authority_graph(target_dir):
    path = os.path.join(target_dir, IDENTITY_DIR, "authority-graph.json")
    if os.path.exists(path):
        return _read_json(path)
    return {"nodes": {}, "edges": []}


def _load_governance_index(target_dir):
    path = os.path.join(target_dir, GENERATED_DIR, "governance-index.json")
    if os.path.exists(path):
        return _read_json(path)
    return {}


def _file_age_days(path):
    try:
        mtime = os.path.getmtime(path)
        return (time.time() - mtime) / 86400.0
    except OSError:
        return 0


def _sha256_str(s):
    return hashlib.sha256(s.encode("utf-8")).hexdigest()


# ---------------------------------------------------------------------------
# 1. ExecutionExplainabilityDashboard
# ---------------------------------------------------------------------------


class ExecutionExplainabilityDashboard:
    """Explains what happened during an execution: what, why, failures,
    timeline, and authority chain."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)

    def explain_execution(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        return self._build_explanation(manifest)

    def explain_last_n(self, n=10):
        manifests = _list_manifests(self.exec_dir)
        # Take last N (most recent files are usually last in sorted order)
        selected = manifests[-n:] if len(manifests) > n else manifests
        explanations = []
        for m in selected:
            explanations.append(self._build_explanation(m))
        return explanations

    def _build_explanation(self, manifest):
        exec_id = manifest.get("execution_id", "unknown")
        state = manifest.get("lifecycle_state", "unknown")
        task = manifest.get("task", "unknown")
        tier = manifest.get("trust_tier", "unknown")
        scope = manifest.get("domain_scope", "unknown")
        telemetry = manifest.get("telemetry", {})
        lifecycle = manifest.get("lifecycle_history", [])
        authority = manifest.get("authority_lineage", {})
        mutations = manifest.get("mutation_journal", {})
        violations = mutations.get("violations_detected", [])
        replay_contract = manifest.get("replay_contract", {})

        # Build timeline
        timeline_entries = []
        for entry in lifecycle:
            ts = entry.get("timestamp", 0)
            timeline_entries.append({
                "state": entry.get("state"),
                "timestamp": _epoch_to_iso(ts),
                "epoch": ts,
            })

        # Determine failure info
        failure_info = None
        if state in ("FAILED", "ROLLED_BACK"):
            failure_info = {
                "violations": violations,
                "rollback_executed": mutations.get("rollback_executed", False),
                "created_files": len(mutations.get("mutations", {}).get("created", [])),
                "modified_files": len(mutations.get("mutations", {}).get("modified", [])),
                "deleted_files": len(mutations.get("mutations", {}).get("deleted", [])),
            }

        explanation = {
            "execution_id": exec_id,
            "task": task,
            "trust_tier": tier,
            "domain_scope": scope,
            "final_state": state,
            "timeline": timeline_entries,
            "duration": {
                "total_ms": telemetry.get("total_duration_ms", 0),
                "command_ms": telemetry.get("command_execution_duration_ms", 0),
                "overhead_ms": telemetry.get("governance_resolution_overhead_ms", 0),
            },
            "authority_chain": {
                "initiator": authority.get("initiator", "unknown"),
                "parent_token_id": authority.get("parent_token_id", "unknown"),
                "capability_signature": authority.get("capability_signature", "")[:16] + "..." if authority.get("capability_signature") else "",
            },
            "failure": failure_info,
            "replayable": bool(replay_contract),
            "integrity_seal": manifest.get("integrity_seal", "")[:16] + "..." if manifest.get("integrity_seal") else "",
        }
        return explanation

    def print_explanation(self, explanation):
        if isinstance(explanation, list):
            for ex in explanation:
                self._print_single(ex)
                print("-" * 72)
        else:
            self._print_single(explanation)

    def _print_single(self, ex):
        if "error" in ex:
            print(f"ERROR: {ex['error']}")
            return

        print("=" * 72)
        print(f"  Execution:  {ex['execution_id']}")
        print(f"  Task:       {ex['task']}")
        print(f"  Trust Tier: {ex['trust_tier']}")
        print(f"  Scope:      {ex['domain_scope']}")
        print(f"  State:      {ex['final_state']}")
        print(f"  Duration:   {_format_duration(ex['duration']['total_ms'])} "
              f"(cmd: {_format_duration(ex['duration']['command_ms'])}, "
              f"overhead: {_format_duration(ex['duration']['overhead_ms'])})")
        print("-" * 72)

        print("  Timeline:")
        for t in ex.get("timeline", []):
            print(f"    [{t['timestamp']}] {t['state']}")

        print("-" * 72)
        auth = ex.get("authority_chain", {})
        print(f"  Authority:  {auth.get('initiator')} -> {auth.get('parent_token_id')}")
        print(f"  Signature:  {auth.get('capability_signature')}")

        failure = ex.get("failure")
        if failure:
            print("-" * 72)
            print("  FAILURE DETAILS:")
            print(f"    Rollback executed: {failure['rollback_executed']}")
            for v in failure.get("violations", []):
                print(f"    VIOLATION: {v}")
            print(f"    Files created:  {failure['created_files']}")
            print(f"    Files modified: {failure['modified_files']}")
            print(f"    Files deleted:  {failure['deleted_files']}")

        print(f"  Integrity:  {ex['integrity_seal']}")
        print(f"  Replayable: {ex['replayable']}")
        print("=" * 72)


# ---------------------------------------------------------------------------
# 2. ReplayDiagnostics
# ---------------------------------------------------------------------------


class ReplayDiagnostics:
    """Replay health diagnostics: success rate, drift rate, corruption count."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.replay_dir = os.path.join(self.target_dir, REPLAY_DIR)
        self.output_path = os.path.join(self.target_dir, GENERATED_DIR, "replay-diagnostics.json")

    def diagnose_replay(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        snapshot = _load_replay_snapshot(exec_id, self.target_dir)

        replay_contract = manifest.get("replay_contract", {})
        telemetry = manifest.get("telemetry", {})

        drift_count = 0
        drift_details = []

        if snapshot:
            original_hashes = manifest.get("context_package", {}).get("dependency_checksums", {})
            current_hashes = snapshot.get("dependency_checksums", {})
            for path, orig_hash in original_hashes.items():
                if path not in current_hashes:
                    drift_count += 1
                    drift_details.append({"path": path, "type": "missing"})
                elif current_hashes[path] != orig_hash:
                    drift_count += 1
                    drift_details.append({"path": path, "type": "mutated"})

        integrity_valid = True
        seal = manifest.get("integrity_seal", "")
        if seal:
            # Quick seal verification: recompute
            raw = {k: v for k, v in manifest.items() if k != "integrity_seal"}
            expected = _sha256_str(json.dumps(raw, sort_keys=True))
            integrity_valid = (seal == expected)

        result = {
            "execution_id": exec_id,
            "replay_snapshot_available": snapshot is not None,
            "integrity_seal_valid": integrity_valid,
            "drift_count": drift_count,
            "drift_details": drift_details,
            "expected_exit_code": replay_contract.get("expected_exit_code"),
            "expected_mutation_count": replay_contract.get("expected_mutation_count"),
            "nonce": manifest.get("nonce", "")[:16] if manifest.get("nonce") else "",
            "lease_expired": telemetry.get("lease_expired_during_execution", False),
        }
        return result

    def check_replay_health(self):
        manifests = _list_manifests(self.exec_dir)
        total = len(manifests)
        replayable = 0
        replayed = 0
        replay_success = 0
        total_drift = 0
        corruption_count = 0

        for m in manifests:
            state = m.get("lifecycle_state", "")
            if state == "REPLAYABLE":
                replayable += 1

            exec_id = m.get("execution_id", "")
            snapshot = _load_replay_snapshot(exec_id, self.target_dir)
            if snapshot:
                replayed += 1
                replay_success += 1

            # Check integrity
            seal = m.get("integrity_seal", "")
            if seal:
                raw = {k: v for k, v in m.items() if k != "integrity_seal"}
                expected = _sha256_str(json.dumps(raw, sort_keys=True))
                if seal != expected:
                    corruption_count += 1

            # Check drift
            original_hashes = m.get("context_package", {}).get("dependency_checksums", {})
            if snapshot:
                current_hashes = snapshot.get("dependency_checksums", {})
                for path, orig_hash in original_hashes.items():
                    if path not in current_hashes or current_hashes[path] != orig_hash:
                        total_drift += 1

        success_rate = (replay_success / replayed * 100) if replayed > 0 else 0
        drift_rate = (total_drift / total * 100) if total > 0 else 0

        diagnostics = {
            "timestamp": _now_iso(),
            "total_executions": total,
            "replayable_executions": replayable,
            "replayed_executions": replayed,
            "replay_success_count": replay_success,
            "replay_success_rate": round(success_rate, 2),
            "total_drift_count": total_drift,
            "drift_rate": round(drift_rate, 2),
            "corruption_count": corruption_count,
        }

        _write_json(self.output_path, diagnostics)
        return diagnostics

    def print_diagnostics(self, diag):
        if isinstance(diag, dict) and "error" in diag:
            print(f"ERROR: {diag['error']}")
            return

        if "total_executions" in diag:
            # Health summary
            print("=" * 72)
            print("  REPLAY HEALTH DIAGNOSTICS")
            print("=" * 72)
            print(f"  Total Executions:      {diag['total_executions']}")
            print(f"  Replayable:            {diag['replayable_executions']}")
            print(f"  Replayed:              {diag['replayed_executions']}")
            print(f"  Success Rate:          {diag['replay_success_rate']}%")
            print(f"  Drift Count:           {diag['total_drift_count']}")
            print(f"  Drift Rate:            {diag['drift_rate']}%")
            print(f"  Corruption Count:      {diag['corruption_count']}")
            print("=" * 72)
            print(f"  Output: {self.output_path}")
        else:
            print("=" * 72)
            print(f"  REPLAY DIAGNOSTIC: {diag['execution_id']}")
            print("=" * 72)
            print(f"  Snapshot Available:    {diag['replay_snapshot_available']}")
            print(f"  Integrity Seal Valid:  {diag['integrity_seal_valid']}")
            print(f"  Drift Count:           {diag['drift_count']}")
            print(f"  Expected Exit Code:    {diag['expected_exit_code']}")
            print(f"  Expected Mutations:    {diag['expected_mutation_count']}")
            print(f"  Lease Expired:         {diag['lease_expired']}")
            if diag.get("drift_details"):
                print("  Drift Details:")
                for d in diag["drift_details"]:
                    print(f"    [{d['type']}] {d['path']}")
            print("=" * 72)


# ---------------------------------------------------------------------------
# 3. GovernanceResolutionDiagnostics
# ---------------------------------------------------------------------------


class GovernanceResolutionDiagnostics:
    """Governance resolution diagnostics: profile coverage, rule consistency,
    and overall health."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.output_path = os.path.join(self.target_dir, GENERATED_DIR, "governance-diagnostics.json")

    def diagnose_resolution(self):
        index = _load_governance_index(self.target_dir)
        files = index.get("files", {})
        profiles = index.get("profiles", {})
        precedence = index.get("precedence", [])

        total_files = len(files)
        files_with_frontmatter = 0
        files_missing_value = []
        files_missing_protection = []

        for fp, data in files.items():
            fm = data.get("frontmatter", {})
            if fm:
                files_with_frontmatter += 1
            if not (fm.get("operational_value") or fm.get("value")):
                files_missing_value.append(fp)
            if not fm.get("protection"):
                files_missing_protection.append(fp)

        diagnostics = {
            "timestamp": _now_iso(),
            "total_governance_files": total_files,
            "files_with_frontmatter": files_with_frontmatter,
            "files_missing_operational_value": files_missing_value,
            "files_missing_protection": files_missing_protection,
            "profiles_loaded": list(profiles.keys()) if isinstance(profiles, dict) else profiles,
            "precedence_rules_count": len(precedence),
            "frontmatter_coverage": round(files_with_frontmatter / total_files * 100, 2) if total_files > 0 else 0,
        }

        return diagnostics

    def check_profile_coverage(self):
        gov_dir = os.path.join(self.target_dir, GOVERNANCE_DIR)
        profiles_dir = os.path.join(gov_dir, "profiles")
        coverage = {
            "profiles_directory": profiles_dir,
            "profiles_found": [],
            "profile_types": {},
        }

        if os.path.isdir(profiles_dir):
            for root, dirs, files in os.walk(profiles_dir):
                for f in files:
                    if f.endswith(".md"):
                        rel = os.path.relpath(os.path.join(root, f), self.target_dir)
                        coverage["profiles_found"].append(rel)
                        # Classify by parent directory
                        parent = os.path.basename(os.path.dirname(rel))
                        coverage["profile_types"].setdefault(parent, []).append(rel)

        return coverage

    def check_rule_consistency(self):
        index = _load_governance_index(self.target_dir)
        files = index.get("files", {})
        title_map = {}
        conflicts = []
        duplicates = []

        for fp, data in files.items():
            fm = data.get("frontmatter", {})
            title = fm.get("title", "")
            if title:
                if title in title_map:
                    duplicates.append({
                        "title": title,
                        "files": [title_map[title], fp],
                    })
                else:
                    title_map[title] = fp

        # Check for conflicting protection levels
        for fp, data in files.items():
            fm = data.get("frontmatter", {})
            protection = fm.get("protection", "")
            precedence = data.get("precedence", 0)
            # Check if a lower precedence file has higher protection
            for fp2, data2 in files.items():
                if fp == fp2:
                    continue
                fm2 = data2.get("frontmatter", {})
                protection2 = fm2.get("protection", "")
                precedence2 = data2.get("precedence", 0)
                if protection != protection2 and precedence == precedence2:
                    key = tuple(sorted([fp, fp2]))
                    if key not in [tuple(sorted(c["files"])) for c in conflicts]:
                        conflicts.append({
                            "files": list(key),
                            "issue": "same precedence but different protection levels",
                            "protection": [protection, protection2],
                        })

        result = {
            "timestamp": _now_iso(),
            "total_rules": len(files),
            "duplicate_titles": duplicates,
            "protection_conflicts": conflicts,
            "consistency_score": round(
                (1 - (len(duplicates) + len(conflicts)) / max(len(files), 1)) * 100, 2
            ),
        }
        return result

    def print_diagnostics(self, diag):
        if isinstance(diag, list):
            for d in diag:
                self._print_single(d)
                print("-" * 72)
        else:
            self._print_single(diag)

    def _print_single(self, diag):
        print("=" * 72)
        if "total_governance_files" in diag:
            print("  GOVERNANCE RESOLUTION DIAGNOSTICS")
            print("=" * 72)
            print(f"  Total Governance Files:     {diag['total_governance_files']}")
            print(f"  Frontmatter Coverage:       {diag['frontmatter_coverage']}%")
            print(f"  Missing Operational Value:   {len(diag['files_missing_operational_value'])}")
            print(f"  Missing Protection:          {len(diag['files_missing_protection'])}")
            print(f"  Profiles Loaded:             {len(diag.get('profiles_loaded', []))}")
            print(f"  Precedence Rules:            {diag['precedence_rules_count']}")
        elif "profiles_directory" in diag:
            print("  PROFILE COVERAGE")
            print("=" * 72)
            print(f"  Profiles Directory: {diag['profiles_directory']}")
            print(f"  Profiles Found:     {len(diag['profiles_found'])}")
            for ptype, files in diag.get("profile_types", {}).items():
                print(f"    {ptype}: {len(files)}")
        elif "total_rules" in diag:
            print("  RULE CONSISTENCY CHECK")
            print("=" * 72)
            print(f"  Total Rules:           {diag['total_rules']}")
            print(f"  Duplicate Titles:      {len(diag['duplicate_titles'])}")
            print(f"  Protection Conflicts:  {len(diag['protection_conflicts'])}")
            print(f"  Consistency Score:     {diag['consistency_score']}%")
            if diag["duplicate_titles"]:
                print("  Duplicates:")
                for dup in diag["duplicate_titles"]:
                    print(f"    '{dup['title']}' -> {dup['files']}")
            if diag["protection_conflicts"]:
                print("  Conflicts:")
                for c in diag["protection_conflicts"]:
                    print(f"    {c['files']}: {c['issue']}")
        else:
            print("  GOVERNANCE DIAGNOSTIC (unknown format)")
            print(json.dumps(diag, indent=2))
        print("=" * 72)
        print(f"  Output: {self.output_path}")


# ---------------------------------------------------------------------------
# 4. ExecutionTimelineViewer
# ---------------------------------------------------------------------------


class ExecutionTimelineViewer:
    """Visualize execution timelines, filtered by state or time range."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.output_path = os.path.join(self.target_dir, GENERATED_DIR, "timeline.json")

    def show_timeline(self, days=7):
        manifests = _list_manifests(self.exec_dir)
        cutoff = time.time() - (days * 86400)
        filtered = []
        for m in manifests:
            lifecycle = m.get("lifecycle_history", [])
            if lifecycle:
                last_ts = lifecycle[-1].get("timestamp", 0)
                if last_ts >= cutoff:
                    filtered.append(m)
            else:
                filtered.append(m)
        return self._build_timeline(filtered)

    def show_timeline_by_state(self, state):
        manifests = _list_manifests(self.exec_dir)
        filtered = [m for m in manifests if m.get("lifecycle_state", "").upper() == state.upper()]
        return self._build_timeline(filtered)

    def export_timeline(self, fmt="text", days=7, state=None):
        if state:
            data = self.show_timeline_by_state(state)
        else:
            data = self.show_timeline(days=days)

        if fmt == "json":
            _write_json(self.output_path, data)
            return self.output_path
        else:
            self.print_timeline(data)
            return data

    def _build_timeline(self, manifests):
        entries = []
        for m in manifests:
            exec_id = m.get("execution_id", "unknown")
            state = m.get("lifecycle_state", "unknown")
            task = m.get("task", "unknown")
            tier = m.get("trust_tier", "unknown")
            lifecycle = m.get("lifecycle_history", [])
            telemetry = m.get("telemetry", {})

            start_ts = lifecycle[0].get("timestamp", 0) if lifecycle else 0
            end_ts = lifecycle[-1].get("timestamp", 0) if lifecycle else 0

            entries.append({
                "execution_id": exec_id,
                "task": task,
                "trust_tier": tier,
                "state": state,
                "start_time": _epoch_to_iso(start_ts) if start_ts else "",
                "end_time": _epoch_to_iso(end_ts) if end_ts else "",
                "duration_ms": telemetry.get("total_duration_ms", 0),
                "lifecycle_steps": len(lifecycle),
            })

        # Sort by start time
        entries.sort(key=lambda e: e.get("start_time", ""))
        return {"timestamp": _now_iso(), "entries": entries, "count": len(entries)}

    def print_timeline(self, data):
        entries = data.get("entries", [])
        if not entries:
            print("No executions found in the specified range.")
            return

        print("=" * 72)
        print(f"  EXECUTION TIMELINE ({data['count']} executions)")
        print("=" * 72)
        print(f"  {'ID':<36} {'STATE':<14} {'DURATION':<12} {'START TIME'}")
        print("-" * 72)
        for e in entries:
            dur = _format_duration(e["duration_ms"])
            print(f"  {e['execution_id']:<36} {e['state']:<14} {dur:<12} {e['start_time']}")
        print("=" * 72)


# ---------------------------------------------------------------------------
# 5. AuthorityTraceViewer
# ---------------------------------------------------------------------------


class AuthorityTraceViewer:
    """Traces authority chains, delegation trees, and authorized actions."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.identity_dir = os.path.join(self.target_dir, IDENTITY_DIR)

    def trace_authority(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        authority = manifest.get("authority_lineage", {})
        token = manifest.get("capability_token", {})
        lifecycle = manifest.get("lifecycle_history", [])

        # Build full chain from lineage
        chain = []
        parent_id = authority.get("parent_token_id", "")
        chain.append({
            "level": 0,
            "token_id": token.get("token_id", ""),
            "parent_token_id": parent_id,
            "trust_tier": token.get("trust_tier", ""),
            "allowed_tools": token.get("allowed_tools", []),
            "allowed_scopes": token.get("allowed_scopes", []),
            "issued_at": _epoch_to_iso(token.get("issued_at", 0)),
        })

        # Try to resolve parent from identity lineage
        lineage = _load_identity_chain(self.target_dir)
        depth = 1
        current_parent = parent_id
        visited = {token.get("token_id", "")}
        while current_parent and current_parent not in visited:
            visited.add(current_parent)
            # Find in lineage
            found = None
            for entry in lineage:
                if entry.get("token_id") == current_parent or entry.get("child_token_id") == current_parent:
                    found = entry
                    break
            if found:
                chain.append({
                    "level": depth,
                    "token_id": found.get("token_id", current_parent),
                    "parent_token_id": found.get("parent_token_id", ""),
                    "trust_tier": found.get("trust_tier", ""),
                })
                current_parent = found.get("parent_token_id", "")
                depth += 1
            else:
                break

        result = {
            "execution_id": exec_id,
            "authority_chain": chain,
            "initiator": authority.get("initiator", "unknown"),
            "depth": len(chain),
        }
        return result

    def show_delegation_tree(self, token_id):
        lineage = _load_identity_chain(self.target_dir)
        authority_graph = _load_authority_graph(self.target_dir)

        tree = {
            "root_token_id": token_id,
            "children": [],
            "total_delegations": 0,
        }

        # Find direct children of this token
        for entry in lineage:
            if entry.get("parent_token_id") == token_id:
                child = {
                    "token_id": entry.get("token_id", ""),
                    "trust_tier": entry.get("trust_tier", ""),
                    "children": [],
                }
                tree["children"].append(child)
                tree["total_delegations"] += 1

                # Find grandchildren
                for entry2 in lineage:
                    if entry2.get("parent_token_id") == entry.get("token_id"):
                        child["children"].append({
                            "token_id": entry2.get("token_id", ""),
                            "trust_tier": entry2.get("trust_tier", ""),
                        })
                        tree["total_delegations"] += 1

        return tree

    def find_authorized_actions(self, token_id):
        manifests = _list_manifests(self.exec_dir)
        authorized = []

        for m in manifests:
            token = m.get("capability_token", {})
            if token.get("token_id") == token_id:
                authorized.append({
                    "execution_id": m.get("execution_id", ""),
                    "task": m.get("task", ""),
                    "trust_tier": token.get("trust_tier", ""),
                    "allowed_tools": token.get("allowed_tools", []),
                    "allowed_scopes": token.get("allowed_scopes", []),
                    "state": m.get("lifecycle_state", ""),
                })

        # Also check from identity lineage
        lineage = _load_identity_chain(self.target_dir)
        for entry in lineage:
            if entry.get("token_id") == token_id:
                authorized.append({
                    "source": "identity_lineage",
                    "token_id": token_id,
                    "trust_tier": entry.get("trust_tier", ""),
                    "narrowed_tools": entry.get("narrowed_tools", []),
                    "narrowed_scopes": entry.get("narrowed_scopes", []),
                })

        return {
            "token_id": token_id,
            "authorized_actions": authorized,
            "count": len(authorized),
        }

    def print_authority(self, data):
        print("=" * 72)
        if "authority_chain" in data:
            print(f"  AUTHORITY TRACE: {data['execution_id']}")
            print("=" * 72)
            print(f"  Initiator: {data['initiator']}")
            print(f"  Chain Depth: {data['depth']}")
            for level in data["authority_chain"]:
                indent = "    " + "  " * level.get("level", 0)
                print(f"{indent}Level {level['level']}: {level['token_id']}")
                if level.get("trust_tier"):
                    print(f"{indent}  Trust Tier: {level['trust_tier']}")
                if level.get("allowed_tools"):
                    print(f"{indent}  Tools: {', '.join(level['allowed_tools'])}")
                if level.get("allowed_scopes"):
                    print(f"{indent}  Scopes: {', '.join(level['allowed_scopes'])}")
        elif "root_token_id" in data:
            print(f"  DELEGATION TREE: {data['root_token_id']}")
            print("=" * 72)
            print(f"  Total Delegations: {data['total_delegations']}")
            self._print_tree(data["children"], indent=2)
        elif "authorized_actions" in data:
            print(f"  AUTHORIZED ACTIONS: {data['token_id']}")
            print("=" * 72)
            print(f"  Total: {data['count']}")
            for a in data["authorized_actions"]:
                print(f"    - {a.get('execution_id', a.get('source', 'unknown'))}: "
                      f"tier={a.get('trust_tier', '')}, "
                      f"tools={a.get('allowed_tools', a.get('narrowed_tools', []))}")
        else:
            print("  AUTHORITY DATA (unknown format)")
            print(json.dumps(data, indent=2))
        print("=" * 72)

    def _print_tree(self, children, indent=0):
        prefix = "  " * indent
        for child in children:
            print(f"{prefix}└─ {child['token_id']} (tier: {child.get('trust_tier', 'unknown')})")
            if child.get("children"):
                self._print_tree(child["children"], indent + 1)


# ---------------------------------------------------------------------------
# 6. FailureRemediationGuide
# ---------------------------------------------------------------------------


class FailureRemediationGuide:
    """Diagnoses failures and provides actionable remediation steps."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.output_path = os.path.join(self.target_dir, GENERATED_DIR, "remediation-guide.json")

    def diagnose_failure(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        state = manifest.get("lifecycle_state", "")
        mutations = manifest.get("mutation_journal", {})
        violations = mutations.get("violations_detected", [])
        telemetry = manifest.get("telemetry", {})
        token = manifest.get("capability_token", {})

        # Classify failure
        root_cause = "unknown"
        impact = "unknown"
        category = "unknown"

        if violations:
            first_v = violations[0]
            if "ACCIDENTAL_MUTATION" in first_v:
                category = "trust_boundary_violation"
                root_cause = "READ_ONLY execution attempted to modify files"
                impact = "Mutations were rolled back; execution results lost"
            elif "GOVERNANCE_MUTATION_BREACH" in first_v:
                category = "governance_boundary_violation"
                root_cause = "WORKSPACE_WRITE execution modified protected governance paths"
                impact = "Governance integrity potentially compromised; rollback executed"
            elif "FROZEN_BASELINE_BREACH" in first_v:
                category = "frozen_baseline_violation"
                root_cause = "GOVERNANCE_WRITE execution modified frozen baseline rules/configs"
                impact = "Frozen governance baseline was modified; rollback executed"
            elif "SYMLINK_ESCAPE" in first_v:
                category = "symlink_escape"
                root_cause = "Symlinks created in restricted tier, potential sandbox escape"
                impact = "Sandbox boundary may have been breached"
            elif "TOKEN_REVOKED" in first_v or "TOKEN_ESCALATION_BLOCKED" in first_v:
                category = "token_violation"
                root_cause = "Capability token was revoked or attempted escalation"
                impact = "Execution blocked by security policy"
            else:
                category = "other_violation"
                root_cause = first_v
                impact = "Trust boundary violation detected"

        if telemetry.get("lease_expired_during_execution"):
            root_cause = "Lease expired during execution"
            category = "lease_expiry"
            impact = "Execution may have been interrupted"

        # Build remediation steps
        remediation = self._get_remediation_steps(category, violations)

        guide = {
            "execution_id": exec_id,
            "state": state,
            "category": category,
            "root_cause": root_cause,
            "impact": impact,
            "violations": violations,
            "remediation_steps": remediation,
            "prevention": self._get_prevention_steps(category),
            "token_trust_tier": token.get("trust_tier", ""),
            "timestamp": _now_iso(),
        }
        return guide

    def get_remediation_steps(self, exec_id):
        guide = self.diagnose_failure(exec_id)
        if "error" in guide:
            return guide
        return guide

    def check_system_health(self):
        manifests = _list_manifests(self.exec_dir)
        total = len(manifests)
        failed = 0
        rolled_back = 0
        expired = 0
        replayable = 0
        violations_by_category = {}

        for m in manifests:
            state = m.get("lifecycle_state", "")
            mutations = m.get("mutation_journal", {})
            violations = mutations.get("violations_detected", [])

            if state == "FAILED":
                failed += 1
            elif state == "ROLLED_BACK":
                rolled_back += 1
            elif state == "EXPIRED":
                expired += 1
            elif state == "REPLAYABLE":
                replayable += 1

            for v in violations:
                if "ACCIDENTAL_MUTATION" in v:
                    violations_by_category["accidental_mutation"] = violations_by_category.get("accidental_mutation", 0) + 1
                elif "GOVERNANCE_MUTATION_BREACH" in v:
                    violations_by_category["governance_breach"] = violations_by_category.get("governance_breach", 0) + 1
                elif "FROZEN_BASELINE_BREACH" in v:
                    violations_by_category["frozen_baseline"] = violations_by_category.get("frozen_baseline", 0) + 1
                elif "SYMLINK_ESCAPE" in v:
                    violations_by_category["symlink_escape"] = violations_by_category.get("symlink_escape", 0) + 1
                elif "TOKEN" in v:
                    violations_by_category["token_violation"] = violations_by_category.get("token_violation", 0) + 1
                else:
                    violations_by_category["other"] = violations_by_category.get("other", 0) + 1

        health_score = round(
            (replayable / max(total, 1)) * 100, 2
        )

        health = {
            "timestamp": _now_iso(),
            "total_executions": total,
            "failed": failed,
            "rolled_back": rolled_back,
            "expired": expired,
            "replayable": replayable,
            "health_score": health_score,
            "violations_by_category": violations_by_category,
            "top_violation_category": max(violations_by_category, key=violations_by_category.get) if violations_by_category else "none",
        }

        _write_json(self.output_path, health)
        return health

    def _get_remediation_steps(self, category, violations):
        steps = {
            "trust_boundary_violation": [
                "1. Review the task command for unintended file modifications",
                "2. Verify READ_ONLY trust tier is appropriate for the task",
                "3. If writes are needed, re-run with WORKSPACE_WRITE tier",
                "4. Check rolled-back files: git status to see what was reverted",
                "5. Re-execute with correct trust tier and scope",
            ],
            "governance_boundary_violation": [
                "1. Identify which governance paths were modified",
                "2. Verify changes were intentional (rollback already executed)",
                "3. If governance changes are needed, use GOVERNANCE_WRITE tier",
                "4. Review .agents/ protection rules",
                "5. Re-execute with appropriate trust tier",
            ],
            "frozen_baseline_violation": [
                "1. Frozen baseline rules (.agents/.rules, .agents/config) were modified",
                "2. These paths should only be changed through governance update process",
                "3. Verify rollback restored original state",
                "4. If baseline update is needed, follow governance change process",
            ],
            "symlink_escape": [
                "1. Investigate symlink creation: security risk assessment needed",
                "2. Review execution command for intentional/unintentional symlink creation",
                "3. Consider tightening sandbox boundaries",
                "4. Audit any files that may have been accessed through symlinks",
            ],
            "token_violation": [
                "1. Check why token was revoked or escalation was attempted",
                "2. Review capability token narrowing constraints",
                "3. Ensure child token is strict subset of parent",
                "4. Re-request delegation with correct narrowing",
            ],
            "lease_expiry": [
                "1. Lease expired during execution; execution may be incomplete",
                "2. Increase lease_duration for long-running tasks",
                "3. Re-execute with longer lease",
            ],
            "unknown": [
                "1. Review execution manifest for detailed lifecycle history",
                "2. Check telemetry for duration and resource usage",
                "3. Examine mutation journal for what changed",
                "4. Consider replaying execution to reproduce issue",
            ],
        }
        return steps.get(category, steps["unknown"])

    def _get_prevention_steps(self, category):
        prevention = {
            "trust_boundary_violation": "Use correct trust tier for the task; validate task commands before execution",
            "governance_boundary_violation": "Respect .agents/ protection boundaries; use GOVERNANCE_WRITE when needed",
            "frozen_baseline_violation": "Frozen baselines should only be updated through governance process",
            "symlink_escape": "Avoid symlink creation in restricted tiers; audit workspace before execution",
            "token_violation": "Ensure proper capability narrowing; avoid escalation patterns",
            "lease_expiry": "Set appropriate lease durations based on expected execution time",
            "unknown": "Review execution parameters and trust tier before re-running",
        }
        return prevention.get(category, prevention["unknown"])

    def print_guide(self, guide):
        if "error" in guide:
            print(f"ERROR: {guide['error']}")
            return

        if "total_executions" in guide:
            # System health
            print("=" * 72)
            print("  SYSTEM HEALTH CHECK")
            print("=" * 72)
            print(f"  Total Executions:    {guide['total_executions']}")
            print(f"  Failed:              {guide['failed']}")
            print(f"  Rolled Back:         {guide['rolled_back']}")
            print(f"  Expired:             {guide['expired']}")
            print(f"  Replayable:          {guide['replayable']}")
            print(f"  Health Score:        {guide['health_score']}%")
            print(f"  Top Violation:       {guide['top_violation_category']}")
            if guide.get("violations_by_category"):
                print("  Violations by Category:")
                for cat, count in guide["violations_by_category"].items():
                    print(f"    {cat}: {count}")
            print("=" * 72)
            print(f"  Output: {self.output_path}")
        else:
            print("=" * 72)
            print(f"  FAILURE REMEDIATION GUIDE: {guide['execution_id']}")
            print("=" * 72)
            print(f"  State:         {guide['state']}")
            print(f"  Category:      {guide['category']}")
            print(f"  Root Cause:    {guide['root_cause']}")
            print(f"  Impact:        {guide['impact']}")
            print(f"  Trust Tier:    {guide['token_trust_tier']}")
            if guide.get("violations"):
                print("  Violations:")
                for v in guide["violations"]:
                    print(f"    - {v}")
            print("-" * 72)
            print("  Remediation Steps:")
            for step in guide.get("remediation_steps", []):
                print(f"    {step}")
            print("-" * 72)
            print(f"  Prevention:    {guide.get('prevention', 'N/A')}")
            print("=" * 72)


# ---------------------------------------------------------------------------
# 7. MutationVisualizer
# ---------------------------------------------------------------------------


class MutationVisualizer:
    """Visualizes file mutations and trust violation patterns."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)

    def visualize_mutations(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        mutations = manifest.get("mutation_journal", {})
        state = manifest.get("lifecycle_state", "")

        created = mutations.get("mutations", {}).get("created", [])
        modified = mutations.get("mutations", {}).get("modified", [])
        deleted = mutations.get("mutations", {}).get("deleted", [])
        violations = mutations.get("violations_detected", [])
        rollback = mutations.get("rollback_executed", False)

        result = {
            "execution_id": exec_id,
            "state": state,
            "rollback_executed": rollback,
            "mutations": {
                "created": created,
                "modified": modified,
                "deleted": deleted,
            },
            "violations": violations,
            "total_mutations": len(created) + len(modified) + len(deleted),
        }
        return result

    def visualize_trust_violations(self, days=7):
        manifests = _list_manifests(self.exec_dir)
        cutoff = time.time() - (days * 86400)
        violations_by_exec = []

        for m in manifests:
            lifecycle = m.get("lifecycle_history", [])
            last_ts = lifecycle[-1].get("timestamp", 0) if lifecycle else 0
            if last_ts < cutoff:
                continue

            mutations = m.get("mutation_journal", {})
            violations = mutations.get("violations_detected", [])
            if violations:
                violations_by_exec.append({
                    "execution_id": m.get("execution_id", ""),
                    "state": m.get("lifecycle_state", ""),
                    "trust_tier": m.get("trust_tier", ""),
                    "violations": violations,
                    "rollback_executed": mutations.get("rollback_executed", False),
                    "timestamp": _epoch_to_iso(last_ts) if last_ts else "",
                })

        return {
            "days": days,
            "violations_count": len(violations_by_exec),
            "violations": violations_by_exec,
        }

    def print_mutations(self, data):
        if "error" in data:
            print(f"ERROR: {data['error']}")
            return

        print("=" * 72)
        print(f"  MUTATION VISUALIZATION: {data['execution_id']}")
        print("=" * 72)
        print(f"  State:           {data['state']}")
        print(f"  Rollback:        {data['rollback_executed']}")
        print(f"  Total Mutations: {data['total_mutations']}")
        print("-" * 72)

        created = data["mutations"].get("created", [])
        modified = data["mutations"].get("modified", [])
        deleted = data["mutations"].get("deleted", [])

        if created:
            print("  CREATED FILES:")
            for f in created:
                print(f"    + {f}")
        if modified:
            print("  MODIFIED FILES:")
            for f in modified:
                print(f"    ~ {f}")
        if deleted:
            print("  DELETED FILES:")
            for f in deleted:
                print(f"    - {f}")

        if data.get("violations"):
            print("-" * 72)
            print("  VIOLATIONS:")
            for v in data["violations"]:
                print(f"    X {v}")

        print("=" * 72)

    def print_trust_violations(self, data):
        print("=" * 72)
        print(f"  TRUST VIOLATIONS (last {data['days']} days)")
        print("=" * 72)
        print(f"  Total Violations: {data['violations_count']}")
        print("-" * 72)

        if data["violations"]:
            print(f"  {'EXECUTION ID':<36} {'TIER':<18} {'STATE':<14} {'VIOLATIONS'}")
            print("-" * 72)
            for v in data["violations"]:
                vcount = len(v.get("violations", []))
                rb = " [ROLLED BACK]" if v.get("rollback_executed") else ""
                print(f"  {v['execution_id']:<36} {v['trust_tier']:<18} {v['state']:<14} {vcount}{rb}")
        else:
            print("  No trust violations detected in the specified period.")

        print("=" * 72)


# ---------------------------------------------------------------------------
# 8. ReplayDiffViewer
# ---------------------------------------------------------------------------


class ReplayDiffViewer:
    """Compares replay results and shows drift details."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.replay_dir = os.path.join(self.target_dir, REPLAY_DIR)

    def diff_replay(self, exec_id, replay_run=None):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        snapshot = _load_replay_snapshot(exec_id, self.target_dir)

        if not snapshot:
            return {"error": f"No replay snapshot found for '{exec_id}'"}

        original_hashes = manifest.get("context_package", {}).get("dependency_checksums", {})
        replay_hashes = snapshot.get("dependency_checksums", {})

        diffs = {
            "created_in_replay": [],
            "missing_in_replay": [],
            "mutated_in_replay": [],
            "unchanged": [],
        }

        all_paths = set(list(original_hashes.keys()) + list(replay_hashes.keys()))
        for path in sorted(all_paths):
            in_orig = path in original_hashes
            in_replay = path in replay_hashes

            if in_orig and not in_replay:
                diffs["missing_in_replay"].append(path)
            elif not in_orig and in_replay:
                diffs["created_in_replay"].append(path)
            elif original_hashes[path] != replay_hashes[path]:
                diffs["mutated_in_replay"].append({
                    "path": path,
                    "original_hash": original_hashes[path][:16] + "...",
                    "replay_hash": replay_hashes[path][:16] + "...",
                })
            else:
                diffs["unchanged"].append(path)

        contract = manifest.get("replay_contract", {})
        result = {
            "execution_id": exec_id,
            "replay_run": replay_run or "latest",
            "original_file_count": len(original_hashes),
            "replay_file_count": len(replay_hashes),
            "diffs": diffs,
            "summary": {
                "created": len(diffs["created_in_replay"]),
                "missing": len(diffs["missing_in_replay"]),
                "mutated": len(diffs["mutated_in_replay"]),
                "unchanged": len(diffs["unchanged"]),
            },
            "expected_exit_code": contract.get("expected_exit_code"),
        }
        return result

    def show_drift_report(self, exec_id):
        manifest_path = _find_manifest(self.exec_dir, exec_id)
        if not manifest_path:
            return {"error": f"Execution manifest not found for '{exec_id}'"}

        manifest = _read_json(manifest_path)
        snapshot = _load_replay_snapshot(exec_id, self.target_dir)

        original_hashes = manifest.get("context_package", {}).get("dependency_checksums", {})
        current_hashes = snapshot.get("dependency_checksums", {}) if snapshot else {}

        drift_entries = []
        for path, orig_hash in sorted(original_hashes.items()):
            if path not in current_hashes:
                drift_entries.append({
                    "path": path,
                    "drift_type": "missing",
                    "original_hash": orig_hash,
                    "current_hash": None,
                })
            elif current_hashes[path] != orig_hash:
                drift_entries.append({
                    "path": path,
                    "drift_type": "mutated",
                    "original_hash": orig_hash,
                    "current_hash": current_hashes[path],
                })

        total_files = len(original_hashes)
        drift_count = len(drift_entries)
        drift_rate = round((drift_count / max(total_files, 1)) * 100, 2)

        # Also check integrity seal
        seal = manifest.get("integrity_seal", "")
        seal_valid = False
        if seal:
            raw = {k: v for k, v in manifest.items() if k != "integrity_seal"}
            expected = _sha256_str(json.dumps(raw, sort_keys=True))
            seal_valid = (seal == expected)

        report = {
            "execution_id": exec_id,
            "timestamp": _now_iso(),
            "total_files": total_files,
            "drift_count": drift_count,
            "drift_rate": drift_rate,
            "integrity_seal_valid": seal_valid,
            "drift_entries": drift_entries,
        }
        return report

    def print_diff(self, data):
        if "error" in data:
            print(f"ERROR: {data['error']}")
            return

        print("=" * 72)
        print(f"  REPLAY DIFF: {data['execution_id']} (run: {data['replay_run']})")
        print("=" * 72)
        print(f"  Original Files: {data['original_file_count']}")
        print(f"  Replay Files:   {data['replay_file_count']}")
        print("-" * 72)

        summary = data.get("summary", {})
        print(f"  Created:  {summary.get('created', 0)}")
        print(f"  Missing:  {summary.get('missing', 0)}")
        print(f"  Mutated:  {summary.get('mutated', 0)}")
        print(f"  Unchanged: {summary.get('unchanged', 0)}")

        diffs = data.get("diffs", {})
        if diffs.get("created_in_replay"):
            print("-" * 72)
            print("  CREATED IN REPLAY:")
            for f in diffs["created_in_replay"]:
                print(f"    + {f}")
        if diffs.get("missing_in_replay"):
            print("-" * 72)
            print("  MISSING IN REPLAY:")
            for f in diffs["missing_in_replay"]:
                print(f"    - {f}")
        if diffs.get("mutated_in_replay"):
            print("-" * 72)
            print("  MUTATED IN REPLAY:")
            for m in diffs["mutated_in_replay"]:
                print(f"    ~ {m['path']}")
                print(f"      original: {m['original_hash']}")
                print(f"      replay:   {m['replay_hash']}")

        print("=" * 72)

    def print_drift(self, data):
        if "error" in data:
            print(f"ERROR: {data['error']}")
            return

        print("=" * 72)
        print(f"  DRIFT REPORT: {data['execution_id']}")
        print("=" * 72)
        print(f"  Total Files:        {data['total_files']}")
        print(f"  Drift Count:        {data['drift_count']}")
        print(f"  Drift Rate:         {data['drift_rate']}%")
        print(f"  Integrity Seal:     {'VALID' if data['integrity_seal_valid'] else 'INVALID'}")
        print("-" * 72)

        if data.get("drift_entries"):
            print(f"  {'PATH':<50} {'TYPE':<10} {'STATUS'}")
            print("-" * 72)
            for e in data["drift_entries"]:
                status = "DRIFTED" if e["drift_type"] == "mutated" else "MISSING"
                print(f"  {e['path']:<50} {e['drift_type']:<10} {status}")
        else:
            print("  No drift detected.")

        print("=" * 72)


# ---------------------------------------------------------------------------
# CLI entry point
# ---------------------------------------------------------------------------


def _parse_args(argv):
    """Minimal argument parser (stdlib only, no argparse dependency needed
    but we use argparse for clean CLI — it's in stdlib)."""
    import argparse

    parser = argparse.ArgumentParser(
        description="Phase 8: Operational UX & Observability — Diagnostic tools for Agent Harness",
    )
    subparsers = parser.add_subparsers(dest="command", help="Available commands")

    # explain
    p = subparsers.add_parser("explain", help="Explain an execution")
    p.add_argument("exec_id", help="Execution ID to explain")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # explain-last
    p = subparsers.add_parser("explain-last", help="Explain last N executions")
    p.add_argument("n", nargs="?", type=int, default=10, help="Number of executions (default: 10)")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # replay
    p = subparsers.add_parser("replay", help="Replay diagnostic for an execution")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # replay-health
    p = subparsers.add_parser("replay-health", help="Check overall replay health")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # governance
    p = subparsers.add_parser("governance", help="Governance resolution diagnostics")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # timeline
    p = subparsers.add_parser("timeline", help="Show execution timeline")
    p.add_argument("--days", type=int, default=7, help="Number of days (default: 7)")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # timeline-by-state
    p = subparsers.add_parser("timeline-by-state", help="Show timeline filtered by state")
    p.add_argument("state", help="State to filter by (e.g., FAILED, REPLAYABLE)")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # authority
    p = subparsers.add_parser("authority", help="Trace authority chain for an execution")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # delegation-tree
    p = subparsers.add_parser("delegation-tree", help="Show delegation tree for a token")
    p.add_argument("token_id", help="Token ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # find-authorized
    p = subparsers.add_parser("find-authorized", help="Find authorized actions for a token")
    p.add_argument("token_id", help="Token ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # remediate
    p = subparsers.add_parser("remediate", help="Failure remediation guide")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # health
    p = subparsers.add_parser("health", help="System health check")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # mutations
    p = subparsers.add_parser("mutations", help="Visualize mutations for an execution")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # trust-violations
    p = subparsers.add_parser("trust-violations", help="Show trust violation patterns")
    p.add_argument("--days", type=int, default=7, help="Number of days (default: 7)")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # replay-diff
    p = subparsers.add_parser("replay-diff", help="Diff replay results")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("replay_run", nargs="?", default="latest", help="Replay run identifier")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    # drift
    p = subparsers.add_parser("drift", help="Show drift report for an execution")
    p.add_argument("exec_id", help="Execution ID")
    p.add_argument("--dir", default=".", help="Target directory (default: current)")

    args = parser.parse_args(argv[1:] if len(argv) > 1 else ["--help"])
    return args


def main(argv=None):
    args = _parse_args(argv or sys.argv)
    target_dir = args.dir

    if args.command == "explain":
        dashboard = ExecutionExplainabilityDashboard(target_dir)
        result = dashboard.explain_execution(args.exec_id)
        dashboard.print_explanation(result)

    elif args.command == "explain-last":
        dashboard = ExecutionExplainabilityDashboard(target_dir)
        result = dashboard.explain_last_n(args.n)
        dashboard.print_explanation(result)

    elif args.command == "replay":
        diag = ReplayDiagnostics(target_dir)
        result = diag.diagnose_replay(args.exec_id)
        diag.print_diagnostics(result)

    elif args.command == "replay-health":
        diag = ReplayDiagnostics(target_dir)
        result = diag.check_replay_health()
        diag.print_diagnostics(result)

    elif args.command == "governance":
        gov = GovernanceResolutionDiagnostics(target_dir)
        resolution = gov.diagnose_resolution()
        coverage = gov.check_profile_coverage()
        consistency = gov.check_rule_consistency()

        # Combine into single diagnostics output
        combined = {
            "timestamp": _now_iso(),
            "resolution": resolution,
            "profile_coverage": coverage,
            "rule_consistency": consistency,
        }
        output_path = os.path.join(target_dir, GENERATED_DIR, "governance-diagnostics.json")
        _write_json(output_path, combined)

        gov.print_diagnostics(resolution)
        print()
        gov.print_diagnostics(coverage)
        print()
        gov.print_diagnostics(consistency)

    elif args.command == "timeline":
        viewer = ExecutionTimelineViewer(target_dir)
        data = viewer.show_timeline(days=args.days)
        viewer.print_timeline(data)

    elif args.command == "timeline-by-state":
        viewer = ExecutionTimelineViewer(target_dir)
        data = viewer.show_timeline_by_state(args.state)
        viewer.print_timeline(data)

    elif args.command == "authority":
        viewer = AuthorityTraceViewer(target_dir)
        result = viewer.trace_authority(args.exec_id)
        viewer.print_authority(result)

    elif args.command == "delegation-tree":
        viewer = AuthorityTraceViewer(target_dir)
        result = viewer.show_delegation_tree(args.token_id)
        viewer.print_authority(result)

    elif args.command == "find-authorized":
        viewer = AuthorityTraceViewer(target_dir)
        result = viewer.find_authorized_actions(args.token_id)
        viewer.print_authority(result)

    elif args.command == "remediate":
        guide = FailureRemediationGuide(target_dir)
        result = guide.diagnose_failure(args.exec_id)
        guide.print_guide(result)

    elif args.command == "health":
        guide = FailureRemediationGuide(target_dir)
        result = guide.check_system_health()
        guide.print_guide(result)

    elif args.command == "mutations":
        viz = MutationVisualizer(target_dir)
        result = viz.visualize_mutations(args.exec_id)
        viz.print_mutations(result)

    elif args.command == "trust-violations":
        viz = MutationVisualizer(target_dir)
        result = viz.visualize_trust_violations(days=args.days)
        viz.print_trust_violations(result)

    elif args.command == "replay-diff":
        viewer = ReplayDiffViewer(target_dir)
        result = viewer.diff_replay(args.exec_id, args.replay_run)
        viewer.print_diff(result)

    elif args.command == "drift":
        viewer = ReplayDiffViewer(target_dir)
        result = viewer.show_drift_report(args.exec_id)
        viewer.print_drift(result)

    else:
        print("Unknown command. Use --help for usage.")
        sys.exit(1)


if __name__ == "__main__":
    main()
