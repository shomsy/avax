#!/usr/bin/env python3
# storage-lifecycle.py — Phase 4: Storage & Telemetry Survivability Engine
# Version: 1.0.0
#
# Prevents telemetry self-destruction by managing storage quotas, evidence
# retention, telemetry compaction, execution summarization, trace pruning,
# and archive migration.

import os
import sys
import json
import time
import shutil
import hashlib
import argparse
from collections import defaultdict

# ---------------------------------------------------------------------------
# Constants & defaults
# ---------------------------------------------------------------------------

EVIDENCE_DIR = ".agents/management/evidence"

DEFAULT_QUOTAS = {
    "execution": 50 * 1024 * 1024,   # 50 MB
    "telemetry": 20 * 1024 * 1024,   # 20 MB
    "traces":    30 * 1024 * 1024,   # 30 MB
    "replay":    10 * 1024 * 1024,   # 10 MB
    "total":    150 * 1024 * 1024,   # 150 MB
}

DEFAULT_RETENTION_DAYS = {
    "execution": 30,
    "telemetry": 7,
    "traces":    3,
    "replay":    90,
}

# Evidence type → directory mapping (relative to evidence root)
TYPE_DIRS = {
    "execution": "execution",
    "telemetry": "telemetry",
    "traces":    "traces",
    "replay":    "replay",
}

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def get_file_sha256(filepath):
    """Return the hex SHA-256 digest of a file."""
    sha = hashlib.sha256()
    with open(filepath, "rb") as f:
        while True:
            data = f.read(65536)
            if not data:
                break
            sha.update(data)
    return sha.hexdigest()


def dir_size(directory):
    """Return total size in bytes of all files under *directory*."""
    total = 0
    if not os.path.isdir(directory):
        return total
    for root, _dirs, files in os.walk(directory):
        for f in files:
            fp = os.path.join(root, f)
            try:
                total += os.path.getsize(fp)
            except OSError:
                pass
    return total


def collect_files(directory, extensions=None):
    """Return a list of absolute file paths under *directory*, optionally
    filtered by a set of extensions (e.g. {'.json', '.log'})."""
    result = []
    if not os.path.isdir(directory):
        return result
    for root, _dirs, files in os.walk(directory):
        for f in files:
            if extensions and not any(f.endswith(ext) for ext in extensions):
                continue
            result.append(os.path.join(root, f))
    return result


def format_bytes(n):
    """Return a human-readable byte string."""
    for unit in ("B", "KB", "MB", "GB"):
        if abs(n) < 1024:
            return f"{n:.1f} {unit}"
        n /= 1024.0
    return f"{n:.1f} TB"


def write_audit_log(evidence_root, action, details):
    """Append a JSON audit line to the audit log."""
    audit_dir = os.path.join(evidence_root, "generated")
    os.makedirs(audit_dir, exist_ok=True)
    audit_path = os.path.join(audit_dir, "storage-audit.jsonl")
    entry = {
        "timestamp": time.time(),
        "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        "action": action,
        "details": details,
    }
    with open(audit_path, "a", encoding="utf-8") as f:
        f.write(json.dumps(entry) + "\n")


# ---------------------------------------------------------------------------
# StorageQuotaManager
# ---------------------------------------------------------------------------

class StorageQuotaManager:
    """Manages configurable storage quotas per evidence type."""

    def __init__(self, evidence_root, quotas=None):
        self.evidence_root = os.path.normpath(evidence_root)
        self.quotas = dict(DEFAULT_QUOTAS)
        if quotas:
            self.quotas.update(quotas)

    def _type_dir(self, evidence_type):
        subdir = TYPE_DIRS.get(evidence_type, evidence_type)
        return os.path.join(self.evidence_root, subdir)

    def check_quota(self, evidence_type):
        """Return (used_bytes, limit_bytes, percentage) for *evidence_type*."""
        limit = self.quotas.get(evidence_type, self.quotas["total"])
        used = dir_size(self._type_dir(evidence_type))
        pct = (used / limit * 100) if limit else 0
        return used, limit, pct

    def enforce_quota(self, evidence_type):
        """Prune oldest files if *evidence_type* exceeds its quota.

        Returns a list of pruned file paths.
        """
        limit = self.quotas.get(evidence_type, self.quotas["total"])
        target_dir = self._type_dir(evidence_type)
        if not os.path.isdir(target_dir):
            return []

        used = dir_size(target_dir)
        if used <= limit:
            return []

        # Gather all files with their mtimes
        files = []
        for root, _dirs, fnames in os.walk(target_dir):
            for fn in fnames:
                fp = os.path.join(root, fn)
                try:
                    files.append((os.path.getmtime(fp), fp))
                except OSError:
                    pass
        files.sort()  # oldest first

        pruned = []
        for _mtime, fp in files:
            try:
                size = os.path.getsize(fp)
                os.remove(fp)
                pruned.append(fp)
                used -= size
                write_audit_log(self.evidence_root, "quota_enforce", {
                    "type": evidence_type,
                    "file": fp,
                    "size": size,
                })
            except OSError:
                pass
            if used <= limit:
                break
        return pruned

    def get_storage_report(self):
        """Return a full storage usage report dict."""
        report = {
            "timestamp": time.time(),
            "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "types": {},
            "total_used": 0,
            "total_limit": self.quotas.get("total", 0),
        }
        for t in TYPE_DIRS:
            used, limit, pct = self.check_quota(t)
            over = used > limit
            report["types"][t] = {
                "used_bytes": used,
                "used_human": format_bytes(used),
                "limit_bytes": limit,
                "limit_human": format_bytes(limit),
                "percentage": round(pct, 1),
                "over_quota": over,
            }
            report["total_used"] += used
        report["total_used_human"] = format_bytes(report["total_used"])
        report["total_limit_human"] = format_bytes(report["total_limit"])
        report["total_percentage"] = round(
            (report["total_used"] / report["total_limit"] * 100)
            if report["total_limit"] else 0,
            1,
        )
        report["total_over_quota"] = report["total_used"] > report["total_limit"]
        return report


# ---------------------------------------------------------------------------
# EvidenceRetentionEngine
# ---------------------------------------------------------------------------

class EvidenceRetentionEngine:
    """Manages evidence retention policies per evidence type."""

    def __init__(self, evidence_root, retention_days=None):
        self.evidence_root = os.path.normpath(evidence_root)
        self.retention_days = dict(DEFAULT_RETENTION_DAYS)
        if retention_days:
            self.retention_days.update(retention_days)

    def _type_dir(self, evidence_type):
        subdir = TYPE_DIRS.get(evidence_type, evidence_type)
        return os.path.join(self.evidence_root, subdir)

    def _expired_files(self, evidence_type, max_age_seconds):
        """Return list of (mtime, path) for files older than *max_age_seconds*."""
        target_dir = self._type_dir(evidence_type)
        if not os.path.isdir(target_dir):
            return []
        cutoff = time.time() - max_age_seconds
        expired = []
        for root, _dirs, fnames in os.walk(target_dir):
            for fn in fnames:
                fp = os.path.join(root, fn)
                try:
                    mt = os.path.getmtime(fp)
                    if mt < cutoff:
                        expired.append((mt, fp))
                except OSError:
                    pass
        expired.sort()
        return expired

    def preview_retention(self):
        """Show what would be affected without deleting. Returns a dict."""
        preview = {
            "timestamp": time.time(),
            "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "types": {},
            "total_expired_count": 0,
            "total_expired_bytes": 0,
        }
        for t, days in self.retention_days.items():
            if t not in TYPE_DIRS and t == "total":
                continue
            cutoff_seconds = days * 24 * 3600
            expired = self._expired_files(t, cutoff_seconds)
            total_bytes = 0
            for _mt, fp in expired:
                try:
                    total_bytes += os.path.getsize(fp)
                except OSError:
                    pass
            preview["types"][t] = {
                "retention_days": days,
                "expired_count": len(expired),
                "expired_bytes": total_bytes,
                "expired_human": format_bytes(total_bytes),
                "sample_files": [fp for _, fp in expired[:5]],
            }
            preview["total_expired_count"] += len(expired)
            preview["total_expired_bytes"] += total_bytes
        return preview

    def apply_retention(self, action="DELETE"):
        """Archive or delete expired evidence. *action* ∈ {ARCHIVE, COMPACT, DELETE}.

        Returns a summary dict of actions taken.
        """
        summary = {
            "timestamp": time.time(),
            "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "action": action,
            "types": {},
            "total_processed": 0,
        }

        if action == "ARCHIVE":
            archive_root = os.path.join(self.evidence_root, "archive")
            os.makedirs(archive_root, exist_ok=True)

        compacted_dir = os.path.join(self.evidence_root, "compacted")

        for t, days in self.retention_days.items():
            if t not in TYPE_DIRS and t == "total":
                continue
            cutoff_seconds = days * 24 * 3600
            expired = self._expired_files(t, cutoff_seconds)
            processed = 0

            for _mt, fp in expired:
                basename = os.path.basename(fp)
                try:
                    if action == "DELETE":
                        seal = get_file_sha256(fp)
                        os.remove(fp)
                        write_audit_log(self.evidence_root, "retention_delete", {
                            "type": t, "file": basename, "seal": seal,
                        })
                    elif action == "ARCHIVE":
                        seal = get_file_sha256(fp)
                        dest = os.path.join(archive_root, t, basename)
                        os.makedirs(os.path.dirname(dest), exist_ok=True)
                        shutil.move(fp, dest)
                        write_audit_log(self.evidence_root, "retention_archive", {
                            "type": t, "from": basename, "to": dest, "seal": seal,
                        })
                    elif action == "COMPACT":
                        seal = get_file_sha256(fp)
                        summary_data = self._compact_file(fp, t)
                        os.makedirs(compacted_dir, exist_ok=True)
                        compact_path = os.path.join(compacted_dir, f"{t}-{basename}")
                        with open(compact_path, "w", encoding="utf-8") as f:
                            json.dump(summary_data, f, indent=2)
                        os.remove(fp)
                        write_audit_log(self.evidence_root, "retention_compact", {
                            "type": t, "file": basename, "seal": seal,
                        })
                    processed += 1
                except OSError as exc:
                    write_audit_log(self.evidence_root, "retention_error", {
                        "type": t, "file": basename, "error": str(exc),
                    })

            summary["types"][t] = {"processed": processed}
            summary["total_processed"] += processed

        return summary

    def _compact_file(self, filepath, evidence_type):
        """Create a compact summary from a JSON evidence file.

        Preserves the cryptographic seal.
        """
        try:
            with open(filepath, "r", encoding="utf-8") as f:
                data = json.load(f)
        except (json.JSONDecodeError, OSError):
            return {"error": "unreadable", "original_seal": None}

        seal = data.get("integrity_seal") or get_file_sha256(filepath)
        summary = {
            "original_seal": seal,
            "compacted_at": time.time(),
            "compacted_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "type": evidence_type,
        }

        # Extract top-level metadata, prune large payload arrays
        for key, val in data.items():
            if isinstance(val, (list, dict)) and len(json.dumps(val)) > 4096:
                summary[key] = f"[compacted: {len(val)} items]"
            else:
                summary[key] = val
        return summary


# ---------------------------------------------------------------------------
# TelemetryCompactor
# ---------------------------------------------------------------------------

class TelemetryCompactor:
    """Compacts telemetry and trace data into summary statistics."""

    def __init__(self, evidence_root):
        self.evidence_root = os.path.normpath(evidence_root)
        self.output_dir = os.path.join(self.evidence_root, "compacted")
        os.makedirs(self.output_dir, exist_ok=True)

    def compact_telemetry(self):
        """Aggregate telemetry files into summary statistics.

        Returns the path of the compacted output file.
        """
        telemetry_dir = os.path.join(self.evidence_root, "telemetry")
        files = collect_files(telemetry_dir, extensions={".json"})
        if not files:
            # Fallback: scan evidence root for telemetry-like files
            telemetry_dir = os.path.join(self.evidence_root, "..", "..", "evidence")
            if not os.path.isdir(telemetry_dir):
                return None
            files = collect_files(telemetry_dir, extensions={".json"})

        summaries = []
        total_duration_ms = 0
        total_count = 0
        error_count = 0
        durations = []

        for fp in files:
            try:
                with open(fp, "r", encoding="utf-8") as f:
                    data = json.load(f)
            except (json.JSONDecodeError, OSError):
                continue

            seal = get_file_sha256(fp)
            dur = None

            # Try common telemetry duration fields
            telemetry = data.get("telemetry", {})
            if isinstance(telemetry, dict):
                dur = telemetry.get("total_duration_ms") or telemetry.get("duration_ms")
                if "error" in str(data.get("status", "")).lower():
                    error_count += 1
            if dur is None:
                dur = data.get("total_duration_ms") or data.get("duration_ms")
            if data.get("status") in ("FAILED", "ERROR"):
                error_count += 1

            if dur is not None:
                try:
                    dur = float(dur)
                    durations.append(dur)
                    total_duration_ms += dur
                except (ValueError, TypeError):
                    pass
            total_count += 1

            summaries.append({
                "file": os.path.basename(fp),
                "seal": seal,
                "duration_ms": dur,
                "status": data.get("status", "unknown"),
            })

        avg_duration = (total_duration_ms / len(durations)) if durations else 0
        min_duration = min(durations) if durations else 0
        max_duration = max(durations) if durations else 0

        result = {
            "compacted_at": time.time(),
            "compacted_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "total_telemetry_entries": total_count,
            "error_count": error_count,
            "total_duration_ms": round(total_duration_ms, 2),
            "avg_duration_ms": round(avg_duration, 2),
            "min_duration_ms": round(min_duration, 2),
            "max_duration_ms": round(max_duration, 2),
            "summaries": summaries,
        }

        output_path = os.path.join(self.output_dir, "telemetry-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(result, f, indent=2)
        return output_path

    def compact_traces(self):
        """Summarize execution trace files.

        Returns the path of the compacted output file.
        """
        traces_dir = os.path.join(self.evidence_root, "traces")
        files = collect_files(traces_dir, extensions={".json"})

        summaries = []
        total_count = 0
        statuses = defaultdict(int)

        for fp in files:
            try:
                with open(fp, "r", encoding="utf-8") as f:
                    data = json.load(f)
            except (json.JSONDecodeError, OSError):
                continue

            seal = get_file_sha256(fp)
            status = data.get("status", data.get("lifecycle_state", "unknown"))
            statuses[status] += 1
            total_count += 1

            summaries.append({
                "file": os.path.basename(fp),
                "seal": seal,
                "status": status,
                "timestamp": data.get("timestamp", os.path.getmtime(fp)),
            })

        result = {
            "compacted_at": time.time(),
            "compacted_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "total_traces": total_count,
            "status_breakdown": dict(statuses),
            "summaries": summaries,
        }

        output_path = os.path.join(self.output_dir, "traces-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(result, f, indent=2)
        return output_path


# ---------------------------------------------------------------------------
# ExecutionSummarizer
# ---------------------------------------------------------------------------

class ExecutionSummarizer:
    """Summarizes execution history from manifests."""

    def __init__(self, evidence_root):
        self.evidence_root = os.path.normpath(evidence_root)
        self.output_dir = os.path.join(self.evidence_root, "summaries")
        os.makedirs(self.output_dir, exist_ok=True)

    def _load_manifests(self, days=None):
        """Load execution manifests, optionally filtered by age in days."""
        exec_dir = os.path.join(self.evidence_root, "execution")
        files = collect_files(exec_dir, extensions={".json"})
        cutoff = time.time() - (days * 24 * 3600) if days else 0
        manifests = []
        for fp in files:
            try:
                with open(fp, "r", encoding="utf-8") as f:
                    data = json.load(f)
                mt = os.path.getmtime(fp)
                if cutoff and mt < cutoff:
                    continue
                manifests.append((fp, data))
            except (json.JSONDecodeError, OSError):
                pass
        return manifests

    def summarize_executions(self, days=7):
        """Create a summary of recent executions."""
        manifests = self._load_manifests(days)
        total = len(manifests)
        statuses = defaultdict(int)
        tiers = defaultdict(int)
        scopes = defaultdict(int)
        durations = []

        for _fp, data in manifests:
            statuses[data.get("lifecycle_state", "unknown")] += 1
            tiers[data.get("trust_tier", "unknown")] += 1
            scopes[data.get("domain_scope", "unknown")] += 1
            tel = data.get("telemetry", {})
            if isinstance(tel, dict):
                dur = tel.get("total_duration_ms")
                if dur is not None:
                    try:
                        durations.append(float(dur))
                    except (ValueError, TypeError):
                        pass

        avg_dur = (sum(durations) / len(durations)) if durations else 0

        summary = {
            "generated_at": time.time(),
            "generated_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "period_days": days,
            "total_executions": total,
            "status_breakdown": dict(statuses),
            "tier_breakdown": dict(tiers),
            "scope_breakdown": dict(scopes),
            "avg_duration_ms": round(avg_dur, 2),
            "total_duration_ms": round(sum(durations), 2),
        }

        output_path = os.path.join(self.output_dir, "executions-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(summary, f, indent=2)
        write_audit_log(self.evidence_root, "summarize_executions", {
            "days": days, "total": total, "output": output_path,
        })
        return output_path

    def summarize_failures(self, days=30):
        """Summarize failure patterns."""
        manifests = self._load_manifests(days)
        failures = []
        failure_reasons = defaultdict(int)

        for fp, data in manifests:
            state = data.get("lifecycle_state", "")
            if state in ("FAILED", "ROLLED_BACK", "INVALIDATED"):
                seal = get_file_sha256(fp)
                journal = data.get("mutation_journal", {})
                violations = journal.get("violations_detected", [])
                for v in violations:
                    # Bucket by prefix before colon
                    bucket = v.split(":")[0].strip() if ":" in v else v
                    failure_reasons[bucket] += 1
                failures.append({
                    "execution_id": data.get("execution_id", "unknown"),
                    "file": os.path.basename(fp),
                    "seal": seal,
                    "state": state,
                    "violations": violations,
                    "trust_tier": data.get("trust_tier", "unknown"),
                    "scope": data.get("domain_scope", "unknown"),
                    "timestamp": data.get("lifecycle_history", [{}])[-1].get("timestamp", 0),
                })

        summary = {
            "generated_at": time.time(),
            "generated_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "period_days": days,
            "total_failures": len(failures),
            "failure_reasons": dict(failure_reasons),
            "failures": failures,
        }

        output_path = os.path.join(self.output_dir, "failures-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(summary, f, indent=2)
        write_audit_log(self.evidence_root, "summarize_failures", {
            "days": days, "total_failures": len(failures), "output": output_path,
        })
        return output_path

    def summarize_performance(self, days=7):
        """Summarize performance trends."""
        manifests = self._load_manifests(days)
        time_series = []
        durations = []
        overheads = []
        context_budgets = []

        for fp, data in manifests:
            tel = data.get("telemetry", {})
            if not isinstance(tel, dict):
                continue
            dur = tel.get("total_duration_ms")
            overhead = tel.get("governance_resolution_overhead_ms")
            budget = tel.get("context_expansion_budget_bytes")
            ts = data.get("lifecycle_history", [{}])[-1].get("timestamp", 0)

            if dur is not None:
                try:
                    dur = float(dur)
                    durations.append(dur)
                    time_series.append({"timestamp": ts, "duration_ms": dur})
                except (ValueError, TypeError):
                    pass
            if overhead is not None:
                try:
                    overheads.append(float(overhead))
                except (ValueError, TypeError):
                    pass
            if budget is not None:
                try:
                    context_budgets.append(float(budget))
                except (ValueError, TypeError):
                    pass

        summary = {
            "generated_at": time.time(),
            "generated_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "period_days": days,
            "sample_size": len(durations),
            "duration": {
                "avg_ms": round(sum(durations) / len(durations), 2) if durations else 0,
                "min_ms": round(min(durations), 2) if durations else 0,
                "max_ms": round(max(durations), 2) if durations else 0,
            },
            "overhead": {
                "avg_ms": round(sum(overheads) / len(overheads), 2) if overheads else 0,
                "min_ms": round(min(overheads), 2) if overheads else 0,
                "max_ms": round(max(overheads), 2) if overheads else 0,
            },
            "context_budget": {
                "avg_bytes": round(sum(context_budgets) / len(context_budgets), 2) if context_budgets else 0,
                "max_bytes": round(max(context_budgets), 2) if context_budgets else 0,
            },
            "time_series": time_series[:50],  # cap
        }

        output_path = os.path.join(self.output_dir, "performance-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(summary, f, indent=2)
        write_audit_log(self.evidence_root, "summarize_performance", {
            "days": days, "samples": len(durations), "output": output_path,
        })
        return output_path

    def summarize_trust_violations(self, days=30):
        """Summarize security/trust violations."""
        manifests = self._load_manifests(days)
        violations = []
        violation_types = defaultdict(int)
        tiers_affected = defaultdict(int)

        for fp, data in manifests:
            journal = data.get("mutation_journal", {})
            viols = journal.get("violations_detected", [])
            if viols:
                seal = get_file_sha256(fp)
                for v in viols:
                    bucket = v.split(":")[0].strip() if ":" in v else v
                    violation_types[bucket] += 1
                    tiers_affected[data.get("trust_tier", "unknown")] += 1
                violations.append({
                    "execution_id": data.get("execution_id", "unknown"),
                    "file": os.path.basename(fp),
                    "seal": seal,
                    "trust_tier": data.get("trust_tier", "unknown"),
                    "violations": viols,
                    "timestamp": data.get("lifecycle_history", [{}])[-1].get("timestamp", 0),
                })

        summary = {
            "generated_at": time.time(),
            "generated_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "period_days": days,
            "total_violations": len(violations),
            "violation_types": dict(violation_types),
            "tiers_affected": dict(tiers_affected),
            "violations": violations,
        }

        output_path = os.path.join(self.output_dir, "trust-violations-summary.json")
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(summary, f, indent=2)
        write_audit_log(self.evidence_root, "summarize_trust_violations", {
            "days": days, "total": len(violations), "output": output_path,
        })
        return output_path


# ---------------------------------------------------------------------------
# TracePruner
# ---------------------------------------------------------------------------

class TracePruner:
    """Prunes old execution traces using various strategies."""

    def __init__(self, evidence_root):
        self.evidence_root = os.path.normpath(evidence_root)

    def _trace_files(self):
        """Return list of (mtime, size, path) for trace files."""
        traces_dir = os.path.join(self.evidence_root, "traces")
        files = collect_files(traces_dir, extensions={".json"})
        result = []
        for fp in files:
            try:
                mt = os.path.getmtime(fp)
                sz = os.path.getsize(fp)
                result.append((mt, sz, fp))
            except OSError:
                pass
        return result

    def prune_traces(self, keep_count=100):
        """Keep only the *keep_count* most recent traces."""
        files = self._trace_files()
        if len(files) <= keep_count:
            return {"pruned": 0, "kept": len(files)}

        # Sort by mtime descending (newest first)
        files.sort(key=lambda x: x[0], reverse=True)
        to_delete = files[keep_count:]
        pruned = 0
        for _mt, _sz, fp in to_delete:
            try:
                os.remove(fp)
                write_audit_log(self.evidence_root, "prune_traces_count", {
                    "file": os.path.basename(fp),
                })
                pruned += 1
            except OSError:
                pass
        return {"pruned": pruned, "kept": keep_count}

    def prune_by_age(self, max_age_days=30):
        """Remove traces older than *max_age_days*."""
        cutoff = time.time() - (max_age_days * 24 * 3600)
        files = self._trace_files()
        pruned = 0
        for mt, _sz, fp in files:
            if mt < cutoff:
                try:
                    os.remove(fp)
                    write_audit_log(self.evidence_root, "prune_traces_age", {
                        "file": os.path.basename(fp),
                        "age_days": round((time.time() - mt) / 86400, 1),
                    })
                    pruned += 1
                except OSError:
                    pass
        return {"pruned": pruned, "max_age_days": max_age_days}

    def prune_by_size(self, max_size_mb=50):
        """Remove oldest traces until total size is under *max_size_mb*."""
        max_bytes = max_size_mb * 1024 * 1024
        files = self._trace_files()
        total = sum(sz for _mt, sz, _fp in files)
        if total <= max_bytes:
            return {"pruned": 0, "total_bytes": total}

        # Sort oldest first
        files.sort(key=lambda x: x[0])
        pruned = 0
        for _mt, sz, fp in files:
            try:
                os.remove(fp)
                total -= sz
                write_audit_log(self.evidence_root, "prune_traces_size", {
                    "file": os.path.basename(fp),
                    "size": sz,
                })
                pruned += 1
            except OSError:
                pass
            if total <= max_bytes:
                break
        return {"pruned": pruned, "total_bytes": total, "max_bytes": max_bytes}


# ---------------------------------------------------------------------------
# ArchiveMigration
# ---------------------------------------------------------------------------

class ArchiveMigration:
    """Handles archive format migrations and integrity validation."""

    def __init__(self, evidence_root):
        self.evidence_root = os.path.normpath(evidence_root)
        self.archive_dir = os.path.join(self.evidence_root, "archive")

    def list_archives(self):
        """List available archives with metadata."""
        if not os.path.isdir(self.archive_dir):
            return []
        archives = []
        for root, _dirs, files in os.walk(self.archive_dir):
            for fn in files:
                fp = os.path.join(root, fn)
                try:
                    mt = os.path.getmtime(fp)
                    sz = os.path.getsize(fp)
                    archives.append({
                        "path": os.path.relpath(fp, self.evidence_root),
                        "size": sz,
                        "size_human": format_bytes(sz),
                        "mtime": mt,
                        "mtime_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime(mt)),
                    })
                except OSError:
                    pass
        archives.sort(key=lambda x: x["mtime"])
        return archives

    def validate_archive_integrity(self):
        """Validate integrity of archived data.

        Returns a dict with validation results.
        """
        archives = self.list_archives()
        valid = 0
        invalid = 0
        errors = []

        for entry in archives:
            fp = os.path.join(self.evidence_root, entry["path"])
            try:
                with open(fp, "r", encoding="utf-8") as f:
                    data = json.load(f)
                # If the archive has an integrity seal, verify it
                seal = data.get("integrity_seal") or data.get("cryptographic_lineage_seal")
                if seal:
                    # Recompute seal from data minus seal fields
                    sealable = {
                        k: v for k, v in data.items()
                        if k not in ("integrity_seal", "cryptographic_lineage_seal", "sealed_at")
                    }
                    computed = hashlib.sha256(
                        json.dumps(sealable, sort_keys=True).encode("utf-8")
                    ).hexdigest()
                    if computed == seal:
                        valid += 1
                    else:
                        invalid += 1
                        errors.append({
                            "file": entry["path"],
                            "error": "seal_mismatch",
                            "expected": seal,
                            "computed": computed,
                        })
                else:
                    # No seal; check JSON parsability only
                    valid += 1
            except (json.JSONDecodeError, OSError) as exc:
                invalid += 1
                errors.append({
                    "file": entry["path"],
                    "error": str(exc),
                })

        return {
            "timestamp": time.time(),
            "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "total_archives": len(archives),
            "valid": valid,
            "invalid": invalid,
            "errors": errors,
        }

    def migrate_archive(self, from_version, to_version):
        """Migrate archive format from *from_version* to *to_version*.

        Returns a migration report dict.
        """
        archives = self.list_archives()
        migrated = 0
        errors = []

        for entry in archives:
            fp = os.path.join(self.evidence_root, entry["path"])
            try:
                with open(fp, "r", encoding="utf-8") as f:
                    data = json.load(f)

                current_version = data.get("version", "1.0.0")
                if current_version != from_version:
                    continue

                # Apply migration transforms based on version delta
                migrated_data = self._apply_migration(data, from_version, to_version)
                if migrated_data is None:
                    errors.append({
                        "file": entry["path"],
                        "error": f"unsupported migration {from_version} -> {to_version}",
                    })
                    continue

                migrated_data["version"] = to_version
                migrated_data["migrated_at"] = time.time()
                migrated_data["migrated_from"] = from_version

                # Write back
                with open(fp, "w", encoding="utf-8") as f:
                    json.dump(migrated_data, f, indent=2)
                migrated += 1

                write_audit_log(self.evidence_root, "archive_migration", {
                    "file": entry["path"],
                    "from_version": from_version,
                    "to_version": to_version,
                })
            except (json.JSONDecodeError, OSError) as exc:
                errors.append({
                    "file": entry["path"],
                    "error": str(exc),
                })

        return {
            "timestamp": time.time(),
            "timestamp_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "from_version": from_version,
            "to_version": to_version,
            "migrated": migrated,
            "errors": errors,
        }

    def _apply_migration(self, data, from_ver, to_ver):
        """Apply migration transforms. Returns migrated dict or None."""
        # Simple version bumping — extend as needed
        migrated = dict(data)

        # Example: 1.0.0 -> 2.0.0 adds a version field and normalizes timestamps
        if from_ver == "1.0.0" and to_ver >= "2.0.0":
            # Normalize any 'timestamp' fields that are ISO strings to epoch
            if "timestamp" in migrated and isinstance(migrated["timestamp"], str):
                try:
                    migrated["timestamp"] = time.mktime(
                        time.strptime(migrated["timestamp"], "%Y-%m-%dT%H:%M:%SZ")
                    )
                except ValueError:
                    pass

        # Example: 2.0.0 -> 3.0.0 adds seal fields
        if from_ver <= "2.0.0" and to_ver >= "3.0.0":
            if "integrity_seal" not in migrated:
                sealable = {
                    k: v for k, v in migrated.items()
                    if k not in ("integrity_seal", "sealed_at")
                }
                migrated["integrity_seal"] = hashlib.sha256(
                    json.dumps(sealable, sort_keys=True).encode("utf-8")
                ).hexdigest()

        return migrated


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def cmd_quota(args):
    mgr = StorageQuotaManager(args.dir)
    report = mgr.get_storage_report()

    print("=" * 70)
    print("📊  STORAGE QUOTA REPORT")
    print("=" * 70)
    for t, info in report["types"].items():
        status = "⚠️  OVER" if info["over_quota"] else "✅"
        print(f"  {t:12s}: {info['used_human']:>10s} / {info['limit_human']:>10s}  "
              f"({info['percentage']:5.1f}%)  {status}")
    print("-" * 70)
    total_status = "⚠️  OVER" if report["total_over_quota"] else "✅"
    print(f"  {'TOTAL':12s}: {report['total_used_human']:>10s} / {report['total_limit_human']:>10s}  "
          f"({report['total_percentage']:5.1f}%)  {total_status}")
    print("=" * 70)

    # Auto-enforce any over-quota types
    over_types = [t for t, info in report["types"].items() if info["over_quota"]]
    if over_types:
        print("\n⚠️  Enforcing quotas for: " + ", ".join(over_types))
        for t in over_types:
            pruned = mgr.enforce_quota(t)
            print(f"  - {t}: pruned {len(pruned)} files")
    else:
        print("\n✅  All types within quota.")


def cmd_retention(args):
    engine = EvidenceRetentionEngine(args.dir)

    if args.preview:
        preview = engine.preview_retention()
        print("=" * 70)
        print("🔍  RETENTION PREVIEW (no files will be deleted)")
        print("=" * 70)
        for t, info in preview["types"].items():
            print(f"  {t:12s}: {info['retention_days']} day retention — "
                  f"{info['expired_count']} files ({info['expired_human']}) expired")
            for sf in info.get("sample_files", []):
                print(f"             sample: {os.path.basename(sf)}")
        print("-" * 70)
        print(f"  Total: {preview['total_expired_count']} files "
              f"({format_bytes(preview['total_expired_bytes'])}) would be affected")
        print("=" * 70)
    else:
        summary = engine.apply_retention(action="DELETE")
        print("=" * 70)
        print("🗑️  RETENTION APPLIED (DELETE)")
        print("=" * 70)
        for t, info in summary["types"].items():
            print(f"  {t:12s}: {info['processed']} files processed")
        print("-" * 70)
        print(f"  Total: {summary['total_processed']} files processed")
        print("=" * 70)


def cmd_compact(args):
    compactor = TelemetryCompactor(args.dir)

    print("=" * 70)
    print("🗜️  TELEMETRY COMPACTION")
    print("=" * 70)

    tel_path = compactor.compact_telemetry()
    if tel_path:
        print(f"  ✅ Telemetry compacted: {tel_path}")
    else:
        print("  ℹ️  No telemetry files found to compact.")

    trace_path = compactor.compact_traces()
    if trace_path:
        print(f"  ✅ Traces compacted: {trace_path}")
    else:
        print("  ℹ️  No trace files found to compact.")

    print("=" * 70)


def cmd_summarize(args):
    summarizer = ExecutionSummarizer(args.dir)
    days = args.days

    print("=" * 70)
    print(f"📋  EXECUTION SUMMARIES (last {days} days)")
    print("=" * 70)

    paths = [
        ("Executions", summarizer.summarize_executions(days=days)),
        ("Failures", summarizer.summarize_failures(days=min(days, 30))),
        ("Performance", summarizer.summarize_performance(days=days)),
        ("Trust Violations", summarizer.summarize_trust_violations(days=min(days, 30))),
    ]
    for label, p in paths:
        if p:
            print(f"  ✅ {label}: {p}")
        else:
            print(f"  ℹ️  {label}: no data")

    print("=" * 70)


def cmd_prune(args):
    pruner = TracePruner(args.dir)
    strategy = args.strategy
    value = args.value

    print("=" * 70)
    print("✂️  TRACE PRUNING")
    print("=" * 70)

    if strategy == "count":
        result = pruner.prune_traces(keep_count=value)
        print(f"  Kept {result['kept']} most recent traces, pruned {result['pruned']}")
    elif strategy == "age":
        result = pruner.prune_by_age(max_age_days=value)
        print(f"  Pruned {result['pruned']} traces older than {value} days")
    elif strategy == "size":
        result = pruner.prune_by_size(max_size_mb=value)
        print(f"  Pruned {result['pruned']} traces, remaining: {format_bytes(result.get('total_bytes', 0))}")
    else:
        print(f"  ❌ Unknown strategy: {strategy}")
        sys.exit(1)

    print("=" * 70)


def cmd_report(args):
    mgr = StorageQuotaManager(args.dir)
    report = mgr.get_storage_report()
    engine = EvidenceRetentionEngine(args.dir)
    preview = engine.preview_retention()

    print("=" * 70)
    print("📊  COMPREHENSIVE STORAGE & TELEMETRY REPORT")
    print("=" * 70)
    print(f"  Generated: {report['timestamp_iso']}")
    print()

    # Storage usage
    print("  ── Storage Usage ──")
    for t, info in report["types"].items():
        status = "⚠️  OVER" if info["over_quota"] else "✅"
        print(f"    {t:12s}: {info['used_human']:>10s} / {info['limit_human']:>10s}  "
              f"({info['percentage']:5.1f}%)  {status}")
    print(f"    {'TOTAL':12s}: {report['total_used_human']:>10s} / {report['total_limit_human']:>10s}  "
          f"({report['total_percentage']:5.1f}%)")
    print()

    # Retention preview
    print("  ── Retention Preview ──")
    for t, info in preview["types"].items():
        print(f"    {t:12s}: {info['expired_count']} expired files "
              f"({info['expired_human']})")
    print(f"    Total expired: {preview['total_expired_count']} files "
          f"({format_bytes(preview['total_expired_bytes'])})")
    print()

    # Archive info
    archiver = ArchiveMigration(args.dir)
    archives = archiver.list_archives()
    print(f"  ── Archives: {len(archives)} files ──")
    for a in archives[:5]:
        print(f"    {a['path']:50s}  {a['size_human']:>10s}  {a['mtime_iso']}")
    if len(archives) > 5:
        print(f"    ... and {len(archives) - 5} more")

    print()
    print("=" * 70)


def resolve_evidence_root(dir_path):
    """Resolve the evidence root from a project or evidence directory."""
    candidate = os.path.normpath(dir_path)
    # If it already looks like an evidence dir (contains 'evidence' in path)
    if os.path.basename(candidate) == "evidence" and os.path.isdir(candidate):
        return candidate
    # Otherwise, try to find .agents/management/evidence under it
    evidence_candidate = os.path.join(candidate, ".agents", "management", "evidence")
    if os.path.isdir(evidence_candidate):
        return evidence_candidate
    # Fallback: create it
    os.makedirs(evidence_candidate, exist_ok=True)
    return evidence_candidate


def main():
    parser = argparse.ArgumentParser(
        description="Phase 4: Storage & Telemetry Survivability Engine",
    )
    subparsers = parser.add_subparsers(dest="command")

    # quota
    p_quota = subparsers.add_parser("quota", help="Check and enforce storage quotas")
    p_quota.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_quota.set_defaults(func=cmd_quota)

    # retention
    p_ret = subparsers.add_parser("retention", help="Apply or preview retention policies")
    p_ret.add_argument("--preview", action="store_true", help="Preview only, do not delete")
    p_ret.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_ret.set_defaults(func=cmd_retention)

    # compact
    p_compact = subparsers.add_parser("compact", help="Compact telemetry and traces")
    p_compact.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_compact.set_defaults(func=cmd_compact)

    # summarize
    p_sum = subparsers.add_parser("summarize", help="Summarize execution history")
    p_sum.add_argument("--days", type=int, default=7, help="Number of days to summarize (default: 7)")
    p_sum.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_sum.set_defaults(func=cmd_summarize)

    # prune
    p_prune = subparsers.add_parser("prune", help="Prune old traces")
    p_prune.add_argument("--strategy", choices=["count", "age", "size"], default="count",
                         help="Pruning strategy (default: count)")
    p_prune.add_argument("--value", type=int, default=100,
                         help="Strategy value: keep_count, max_age_days, or max_size_mb (default: 100)")
    p_prune.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_prune.set_defaults(func=cmd_prune)

    # report
    p_report = subparsers.add_parser("report", help="Full storage and telemetry report")
    p_report.add_argument("--dir", default=".", help="Target directory (default: .)")
    p_report.set_defaults(func=cmd_report)

    args = parser.parse_args()

    if not hasattr(args, "func"):
        parser.print_help()
        sys.exit(1)

    # Resolve evidence root
    args.dir = resolve_evidence_root(args.dir)
    args.func(args)


if __name__ == "__main__":
    main()
