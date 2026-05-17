#!/usr/bin/env python3
# failure-recovery.py — V3.0.0 Failure & Recovery Engineering Engine
#
# Phase 6: Enterprise failure safety layer for the Agent Harness runtime.
#
# Engines:
# 1. CrashRecoveryManager    — interrupted execution detection & recovery
# 2. JournalRepair           — mutation journal repair & replay
# 3. LockCleanup             — stale lock detection & cleanup
# 4. ConcurrentConflictHandler — concurrent execution conflict resolution
# 5. ChaosSimulator          — adversarial chaos testing
# 6. CrashSafeReplay         — crash-safe checkpoint & resume
# 7. CorruptedEvidenceIsolator — corrupted evidence quarantine

import os
import sys
import json
import time
import uuid
import shutil
import hashlib
import copy
from enum import Enum
from datetime import datetime, timezone

# ---------------------------------------------------------------------------
# Shared constants & helpers
# ---------------------------------------------------------------------------

EVIDENCE_BASE = ".agents/management/evidence"
EXECUTION_DIR = os.path.join(EVIDENCE_BASE, "execution")
RECOVERY_DIR = os.path.join(EVIDENCE_BASE, "recovery")
LOCKS_DIR = os.path.join(EVIDENCE_BASE, "locks")
QUARANTINE_DIR = os.path.join(EVIDENCE_BASE, "quarantine")
CHECKPOINTS_DIR = os.path.join(RECOVERY_DIR, "checkpoints")
CONFLICTS_LOG = os.path.join(RECOVERY_DIR, "conflicts.jsonl")
CHAOS_RESULTS = os.path.join(RECOVERY_DIR, "chaos-results.json")
RECOVERY_REPORT_PATH = os.path.join(RECOVERY_DIR, "recovery-report.json")

TERMINAL_STATES = {"FAILED", "ROLLED_BACK", "INVALIDATED", "EXPIRED", "REPLAYABLE"}
NON_TERMINAL_STATES = {"CREATED", "PLANNED", "EXECUTING", "VALIDATING"}


def _ensure_dirs(*paths):
    for p in paths:
        os.makedirs(p, exist_ok=True)


def _now_iso():
    return datetime.now(timezone.utc).isoformat()


def _now_epoch():
    return time.time()


def _read_json(path):
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)


def _write_json(path, data):
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2)


def _write_jsonl_line(path, record):
    with open(path, "a", encoding="utf-8") as f:
        f.write(json.dumps(record, default=str) + "\n")


def _file_age_minutes(path):
    return (_now_epoch() - os.path.getmtime(path)) / 60.0


def _sha256_file(path):
    h = hashlib.sha256()
    with open(path, "rb") as f:
        while True:
            chunk = f.read(65536)
            if not chunk:
                break
            h.update(chunk)
    return h.hexdigest()


def _extract_exec_id_from_manifest(filepath):
    basename = os.path.basename(filepath)
    if basename.startswith("execution-manifest-") and basename.endswith(".json"):
        return basename[len("execution-manifest-") : -len(".json")]
    return None


def _find_manifest_for_exec(exec_dir, exec_id):
    candidate = os.path.join(exec_dir, f"execution-manifest-{exec_id}.json")
    if os.path.exists(candidate):
        return candidate
    # Fallback: scan directory
    for f in os.listdir(exec_dir):
        eid = _extract_exec_id_from_manifest(f)
        if eid == exec_id:
            return os.path.join(exec_dir, f)
    return None


# ---------------------------------------------------------------------------
# 1. CrashRecoveryManager
# ---------------------------------------------------------------------------

class CrashRecoveryManager:
    """Detects and recovers interrupted executions stuck in non-terminal states."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        _ensure_dirs(self.exec_dir, self.recovery_dir)

    def _list_manifests(self):
        if not os.path.isdir(self.exec_dir):
            return []
        results = []
        for f in os.listdir(self.exec_dir):
            if f.startswith("execution-manifest-") and f.endswith(".json"):
                results.append(os.path.join(self.exec_dir, f))
        return results

    def detect_interrupted_executions(self):
        """Scan for incomplete executions (CREATED/EXECUTING state without terminal state)."""
        interrupted = []
        for manifest_path in self._list_manifests():
            try:
                manifest = _read_json(manifest_path)
                state = manifest.get("lifecycle_state", "")
                if state in NON_TERMINAL_STATES:
                    interrupted.append({
                        "execution_id": manifest.get("execution_id", "unknown"),
                        "state": state,
                        "manifest_path": manifest_path,
                        "task": manifest.get("task", ""),
                        "trust_tier": manifest.get("trust_tier", ""),
                        "lifecycle_history": manifest.get("lifecycle_history", []),
                        "detected_at": _now_iso(),
                    })
            except (json.JSONDecodeError, OSError):
                # File-level corruption — record as interrupted too
                interrupted.append({
                    "execution_id": _extract_exec_id_from_manifest(manifest_path) or "unknown",
                    "state": "CORRUPTED_MANIFEST",
                    "manifest_path": manifest_path,
                    "task": "",
                    "trust_tier": "",
                    "lifecycle_history": [],
                    "detected_at": _now_iso(),
                })
        return interrupted

    def recover_interrupted(self, exec_id):
        """Attempts to recover an interrupted execution by transitioning it to a terminal state."""
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)
        if manifest_path is None:
            return {"success": False, "error": f"Manifest not found for execution {exec_id}"}

        try:
            manifest = _read_json(manifest_path)
        except (json.JSONDecodeError, OSError) as e:
            return {"success": False, "error": f"Cannot read manifest: {e}"}

        current_state = manifest.get("lifecycle_state", "")
        if current_state in TERMINAL_STATES:
            return {"success": True, "note": f"Execution {exec_id} already in terminal state {current_state}"}

        # Attempt recovery: transition to FAILED with recovery metadata
        lifecycle = manifest.get("lifecycle_history", [])
        lifecycle.append({"state": "FAILED", "timestamp": _now_epoch(), "reason": "recovered_from_interrupt"})

        manifest["lifecycle_state"] = "FAILED"
        manifest["lifecycle_history"] = lifecycle
        manifest["recovery"] = {
            "recovered_at": _now_iso(),
            "original_state": current_state,
            "recovery_engine": "CrashRecoveryManager",
        }

        # Write recovered manifest
        backup_path = manifest_path + ".pre-recovery"
        shutil.copy2(manifest_path, backup_path)
        _write_json(manifest_path, manifest)

        return {
            "success": True,
            "execution_id": exec_id,
            "original_state": current_state,
            "recovered_state": "FAILED",
            "backup_path": backup_path,
        }

    def cleanup_stale_executions(self, max_age_minutes=30):
        """Cleans up executions stuck in non-terminal states beyond the age threshold."""
        interrupted = self.detect_interrupted_executions()
        cleaned = []
        for entry in interrupted:
            if entry["state"] == "CORRUPTED_MANIFEST":
                # For corrupted manifests, use file mtime
                age = _file_age_minutes(entry["manifest_path"])
            else:
                # Use last lifecycle entry timestamp
                history = entry.get("lifecycle_history", [])
                if history:
                    last_ts = history[-1].get("timestamp", _now_epoch())
                    age = (_now_epoch() - last_ts) / 60.0
                else:
                    age = _file_age_minutes(entry["manifest_path"])

            if age > max_age_minutes:
                result = self.recover_interrupted(entry["execution_id"])
                result["age_minutes"] = round(age, 2)
                cleaned.append(result)

        return cleaned

    def recovery_report(self):
        """Generates a recovery report of current system state."""
        interrupted = self.detect_interrupted_executions()
        total_manifests = len(self._list_manifests())

        report = {
            "report_id": f"recovery-{uuid.uuid4()}",
            "generated_at": _now_iso(),
            "summary": {
                "total_executions": total_manifests,
                "interrupted_count": len(interrupted),
                "healthy_count": total_manifests - len(interrupted),
            },
            "interrupted_executions": interrupted,
        }

        _ensure_dirs(self.recovery_dir)
        report_path = os.path.join(self.recovery_dir, "recovery-report.json")
        _write_json(report_path, report)

        return report


# ---------------------------------------------------------------------------
# 2. JournalRepair
# ---------------------------------------------------------------------------

class JournalRepair:
    """Handles mutation journal repair, replay, and integrity verification."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        _ensure_dirs(self.exec_dir, self.recovery_dir)

    def _list_journals(self):
        """Find all mutation journals in execution manifests."""
        journals = []
        if not os.path.isdir(self.exec_dir):
            return journals
        for f in os.listdir(self.exec_dir):
            if f.startswith("execution-manifest-") and f.endswith(".json"):
                path = os.path.join(self.exec_dir, f)
                try:
                    manifest = _read_json(path)
                    journal = manifest.get("mutation_journal")
                    if journal:
                        journals.append({"journal_id": journal.get("journal_id", ""), "manifest_path": path})
                except (json.JSONDecodeError, OSError):
                    journals.append({"journal_id": "corrupted", "manifest_path": path, "error": True})
        return journals

    def detect_corrupted_journals(self):
        """Finds journals with missing or invalid data."""
        corrupted = []
        for entry in self._list_journals():
            if entry.get("error"):
                corrupted.append({
                    "journal_id": "unknown",
                    "manifest_path": entry["manifest_path"],
                    "issue": "MANIFEST_READ_FAILURE",
                })
                continue

            try:
                manifest = _read_json(entry["manifest_path"])
                journal = manifest.get("mutation_journal", {})

                issues = []
                required_fields = ["journal_id", "execution_id", "mutations"]
                for field in required_fields:
                    if field not in journal:
                        issues.append(f"missing_field:{field}")

                mutations = journal.get("mutations", {})
                if not isinstance(mutations, dict):
                    issues.append("mutations_not_a_dict")
                else:
                    for key in ("created", "modified", "deleted"):
                        if key in mutations and not isinstance(mutations[key], list):
                            issues.append(f"mutations.{key}_not_a_list")

                # Verify integrity seal if present
                if "integrity_seal" in manifest:
                    seal = manifest["integrity_seal"]
                    expected = self._compute_manifest_hash(manifest, exclude="integrity_seal")
                    if seal != expected:
                        issues.append("integrity_seal_mismatch")

                if issues:
                    corrupted.append({
                        "journal_id": journal.get("journal_id", "unknown"),
                        "manifest_path": entry["manifest_path"],
                        "issues": issues,
                    })
            except Exception as e:
                corrupted.append({
                    "journal_id": "unknown",
                    "manifest_path": entry["manifest_path"],
                    "issue": f"UNEXPECTED_ERROR: {e}",
                })

        return corrupted

    def _compute_manifest_hash(self, manifest, exclude=None):
        """Compute a deterministic hash of a manifest for integrity checks."""
        data = copy.deepcopy(manifest)
        if exclude and exclude in data:
            del data[exclude]
        return hashlib.sha256(json.dumps(data, sort_keys=True).encode("utf-8")).hexdigest()

    def repair_journal(self, journal_id):
        """Attempts to repair a corrupted journal."""
        # Find the manifest containing this journal
        target = None
        for entry in self._list_journals():
            if entry["journal_id"] == journal_id:
                target = entry["manifest_path"]
                break

        if target is None:
            return {"success": False, "error": f"Journal {journal_id} not found"}

        try:
            manifest = _read_json(target)
        except (json.JSONDecodeError, OSError) as e:
            return {"success": False, "error": f"Cannot read manifest: {e}"}

        journal = manifest.get("mutation_journal", {})
        repairs = []

        # Ensure required fields exist with sane defaults
        if "journal_id" not in journal:
            journal["journal_id"] = f"journal-repaired-{uuid.uuid4()}"
            repairs.append("added_missing_journal_id")

        if "execution_id" not in journal:
            journal["execution_id"] = manifest.get("execution_id", "unknown")
            repairs.append("added_missing_execution_id")

        if "mutations" not in journal or not isinstance(journal.get("mutations"), dict):
            journal["mutations"] = {"created": [], "modified": [], "deleted": []}
            repairs.append("reconstructed_empty_mutations")

        mutations = journal.get("mutations", {})
        for key in ("created", "modified", "deleted"):
            if key in mutations and not isinstance(mutations[key], list):
                mutations[key] = []
                repairs.append(f"fixed_mutations_{key}_to_empty_list")

        if "violations_detected" not in journal:
            journal["violations_detected"] = []
            repairs.append("added_missing_violations_detected")

        if "rollback_executed" not in journal:
            journal["rollback_executed"] = False
            repairs.append("added_missing_rollback_executed")

        if "symlinks_created" not in journal:
            journal["symlinks_created"] = []
            repairs.append("added_missing_symlinks_created")

        # Re-seal the manifest
        if "integrity_seal" in manifest:
            manifest["integrity_seal"] = self._compute_manifest_hash(manifest, exclude="integrity_seal")
            repairs.append("recomputed_integrity_seal")

        manifest["mutation_journal"] = journal
        manifest["repair_metadata"] = {
            "repaired_at": _now_iso(),
            "repairs_applied": repairs,
            "repair_engine": "JournalRepair",
        }

        # Backup original
        backup_path = target + ".pre-repair"
        shutil.copy2(target, backup_path)
        _write_json(target, manifest)

        return {
            "success": True,
            "journal_id": journal["journal_id"],
            "repairs_applied": repairs,
            "backup_path": backup_path,
        }

    def replay_journal(self, journal_id):
        """Replays a journal to restore state."""
        target = None
        for entry in self._list_journals():
            if entry["journal_id"] == journal_id:
                target = entry["manifest_path"]
                break

        if target is None:
            return {"success": False, "error": f"Journal {journal_id} not found"}

        try:
            manifest = _read_json(target)
        except (json.JSONDecodeError, OSError) as e:
            return {"success": False, "error": f"Cannot read manifest: {e}"}

        journal = manifest.get("mutation_journal", {})
        mutations = journal.get("mutations", {"created": [], "modified": [], "deleted": []})

        replay_log = []
        errors = []

        for path in mutations.get("created", []):
            full_path = os.path.join(self.target_dir, path)
            if os.path.exists(full_path):
                replay_log.append({"action": "skip_created_exists", "path": path})
            else:
                replay_log.append({"action": "note_missing_created", "path": path})

        for path in mutations.get("modified", []):
            full_path = os.path.join(self.target_dir, path)
            if os.path.exists(full_path):
                replay_log.append({"action": "verify_modified", "path": path, "exists": True})
            else:
                replay_log.append({"action": "note_missing_modified", "path": path})
                errors.append(f"Modified file missing: {path}")

        for path in mutations.get("deleted", []):
            full_path = os.path.join(self.target_dir, path)
            if os.path.exists(full_path):
                replay_log.append({"action": "note_deleted_still_exists", "path": path})
                errors.append(f"Deleted file still exists: {path}")
            else:
                replay_log.append({"action": "confirm_deleted", "path": path})

        replay_result = {
            "success": len(errors) == 0,
            "journal_id": journal_id,
            "replay_log": replay_log,
            "errors": errors,
            "replayed_at": _now_iso(),
        }

        _write_json(os.path.join(self.recovery_dir, f"replay-{journal_id}.json"), replay_result)
        return replay_result

    def verify_journal_integrity(self, journal_id):
        """Verifies journal integrity."""
        target = None
        for entry in self._list_journals():
            if entry["journal_id"] == journal_id:
                target = entry["manifest_path"]
                break

        if target is None:
            return {"journal_id": journal_id, "integrity": "NOT_FOUND"}

        try:
            manifest = _read_json(target)
        except (json.JSONDecodeError, OSError):
            return {"journal_id": journal_id, "integrity": "CORRUPTED", "reason": "cannot_read_manifest"}

        journal = manifest.get("mutation_journal", {})
        issues = []

        # Structural checks
        for field in ("journal_id", "execution_id", "mutations"):
            if field not in journal:
                issues.append(f"missing_field:{field}")

        mutations = journal.get("mutations", {})
        if isinstance(mutations, dict):
            for key in ("created", "modified", "deleted"):
                if key in mutations and not isinstance(mutations[key], list):
                    issues.append(f"invalid_type:mutations.{key}")

        # Cross-reference: verify execution_id matches
        if journal.get("execution_id") != manifest.get("execution_id"):
            issues.append("execution_id_mismatch")

        # Integrity seal check
        if "integrity_seal" in manifest:
            expected = self._compute_manifest_hash(manifest, exclude="integrity_seal")
            if manifest["integrity_seal"] != expected:
                issues.append("integrity_seal_invalid")

        integrity_status = "VALID" if not issues else "CORRUPTED"
        return {
            "journal_id": journal_id,
            "integrity": integrity_status,
            "issues": issues,
            "verified_at": _now_iso(),
        }


# ---------------------------------------------------------------------------
# 3. LockCleanup
# ---------------------------------------------------------------------------

class LockCleanup:
    """Handles stale lock detection and cleanup."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.locks_dir = os.path.join(self.target_dir, LOCKS_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        _ensure_dirs(self.locks_dir, self.recovery_dir)

    def _list_lock_files(self):
        if not os.path.isdir(self.locks_dir):
            return []
        results = []
        for f in os.listdir(self.locks_dir):
            if f.endswith(".lock"):
                results.append(os.path.join(self.locks_dir, f))
        return results

    def _read_lock(self, path):
        try:
            return _read_json(path)
        except (json.JSONDecodeError, OSError):
            return {"_corrupted": True, "_path": path}

    def detect_stale_locks(self, max_age_minutes=15):
        """Finds locks older than the threshold."""
        stale = []
        for lock_path in self._list_lock_files():
            lock_data = self._read_lock(lock_path)
            lock_id = os.path.basename(lock_path)[:-5]  # strip .lock

            age = _file_age_minutes(lock_path)
            if age > max_age_minutes:
                stale.append({
                    "lock_id": lock_id,
                    "lock_path": lock_path,
                    "age_minutes": round(age, 2),
                    "lock_data": lock_data,
                })

        return stale

    def cleanup_stale_locks(self, max_age_minutes=15):
        """Removes stale locks older than threshold."""
        stale = self.detect_stale_locks(max_age_minutes)
        removed = []

        for entry in stale:
            try:
                os.remove(entry["lock_path"])
                removed.append({
                    "lock_id": entry["lock_id"],
                    "status": "removed",
                    "age_minutes": entry["age_minutes"],
                })
            except OSError as e:
                removed.append({
                    "lock_id": entry["lock_id"],
                    "status": f"failed: {e}",
                })

        return removed

    def force_release_lock(self, lock_id):
        """Force releases a specific lock."""
        lock_path = os.path.join(self.locks_dir, f"{lock_id}.lock")
        if not os.path.exists(lock_path):
            return {"success": False, "error": f"Lock {lock_id} not found"}

        lock_data = self._read_lock(lock_path)

        # Write release marker before removing
        release_record = {
            "lock_id": lock_id,
            "released_at": _now_iso(),
            "release_engine": "LockCleanup",
            "original_data": lock_data,
        }
        release_path = os.path.join(self.recovery_dir, f"released-{lock_id}.json")
        _write_json(release_path, release_record)

        try:
            os.remove(lock_path)
            return {"success": True, "lock_id": lock_id, "release_path": release_path}
        except OSError as e:
            return {"success": False, "error": str(e)}

    def list_active_locks(self):
        """Lists all active locks."""
        active = []
        for lock_path in self._list_lock_files():
            lock_data = self._read_lock(lock_path)
            lock_id = os.path.basename(lock_path)[:-5]
            age = _file_age_minutes(lock_path)
            active.append({
                "lock_id": lock_id,
                "lock_path": lock_path,
                "age_minutes": round(age, 2),
                "lock_data": lock_data,
            })
        return active


# ---------------------------------------------------------------------------
# 4. ConcurrentConflictHandler
# ---------------------------------------------------------------------------

class ConcurrentConflictHandler:
    """Handles concurrent execution conflicts over shared file modifications."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        self.conflicts_log = os.path.join(self.recovery_dir, "conflicts.jsonl")
        _ensure_dirs(self.exec_dir, self.recovery_dir)

    def _list_manifests(self):
        if not os.path.isdir(self.exec_dir):
            return []
        return [
            os.path.join(self.exec_dir, f)
            for f in os.listdir(self.exec_dir)
            if f.startswith("execution-manifest-") and f.endswith(".json")
        ]

    def _get_mutated_files(self, manifest):
        """Extract all files modified/created/deleted by an execution."""
        journal = manifest.get("mutation_journal", {})
        mutations = journal.get("mutations", {})
        files = set()
        for key in ("created", "modified", "deleted"):
            for path in mutations.get(key, []):
                files.add(path)
        return files

    def detect_conflicts(self):
        """Finds concurrent executions modifying the same files."""
        manifests = []
        for path in self._list_manifests():
            try:
                m = _read_json(path)
                manifests.append(m)
            except (json.JSONDecodeError, OSError):
                pass

        # Build map: file -> [exec_ids]
        file_to_execs = {}
        for m in manifests:
            exec_id = m.get("execution_id", "unknown")
            files = self._get_mutated_files(m)
            for f in files:
                file_to_execs.setdefault(f, []).append(exec_id)

        conflicts = []
        for filepath, exec_ids in file_to_execs.items():
            if len(exec_ids) > 1:
                conflicts.append({
                    "file": filepath,
                    "conflicting_executions": exec_ids,
                    "conflict_type": "shared_file_mutation",
                    "detected_at": _now_iso(),
                })

        for conflict in conflicts:
            _write_jsonl_line(self.conflicts_log, conflict)

        return conflicts

    def resolve_conflict(self, exec_id_1, exec_id_2, strategy="first_wins"):
        """Resolves a conflict between two executions."""
        valid_strategies = {"first_wins", "last_wins", "merge"}
        if strategy not in valid_strategies:
            return {"success": False, "error": f"Invalid strategy '{strategy}'. Must be one of {valid_strategies}"}

        # Find manifests
        manifest_1 = None
        manifest_2 = None
        for path in self._list_manifests():
            try:
                m = _read_json(path)
                if m.get("execution_id") == exec_id_1:
                    manifest_1 = m
                elif m.get("execution_id") == exec_id_2:
                    manifest_2 = m
            except (json.JSONDecodeError, OSError):
                pass

        if manifest_1 is None:
            return {"success": False, "error": f"Execution {exec_id_1} not found"}
        if manifest_2 is None:
            return {"success": False, "error": f"Execution {exec_id_2} not found"}

        files_1 = self._get_mutated_files(manifest_1)
        files_2 = self._get_mutated_files(manifest_2)
        overlapping = files_1 & files_2

        resolution = {
            "conflict_id": f"conflict-{uuid.uuid4()}",
            "exec_id_1": exec_id_1,
            "exec_id_2": exec_id_2,
            "strategy": strategy,
            "overlapping_files": list(overlapping),
            "resolved_at": _now_iso(),
        }

        if strategy == "first_wins":
            resolution["winner"] = exec_id_1
            resolution["loser"] = exec_id_2
            resolution["action"] = f"Marked {exec_id_2} conflicts as invalidated for overlapping files"
        elif strategy == "last_wins":
            resolution["winner"] = exec_id_2
            resolution["loser"] = exec_id_1
            resolution["action"] = f"Marked {exec_id_1} conflicts as invalidated for overlapping files"
        elif strategy == "merge":
            resolution["winner"] = "merged"
            resolution["action"] = "Overlapping files flagged for manual merge review"

        resolution_record = {
            "type": "conflict_resolution",
            **resolution,
        }
        _write_jsonl_line(self.conflicts_log, resolution_record)
        _write_json(
            os.path.join(self.recovery_dir, f"resolution-{resolution['conflict_id']}.json"),
            resolution,
        )

        return resolution

    def prevent_conflict(self, exec_id):
        """Checks if an execution would conflict with active ones."""
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)
        if manifest_path is None:
            return {"would_conflict": False, "error": f"Execution {exec_id} not found"}

        try:
            manifest = _read_json(manifest_path)
        except (json.JSONDecodeError, OSError):
            return {"would_conflict": False, "error": "Cannot read manifest"}

        files = self._get_mutated_files(manifest)
        conflicts = self.detect_conflicts()

        active_conflicts = []
        for c in conflicts:
            if exec_id in c["conflicting_executions"]:
                active_conflicts.append(c)

        return {
            "execution_id": exec_id,
            "would_conflict": len(active_conflicts) > 0,
            "conflicts": active_conflicts,
            "checked_at": _now_iso(),
        }

    def get_conflict_report(self):
        """Full conflict analysis."""
        conflicts = self.detect_conflicts()
        total_manifests = len(self._list_manifests())

        report = {
            "report_id": f"conflict-report-{uuid.uuid4()}",
            "generated_at": _now_iso(),
            "summary": {
                "total_executions": total_manifests,
                "total_conflicts": len(conflicts),
                "affected_files": len(set(c["file"] for c in conflicts)),
            },
            "conflicts": conflicts,
        }

        return report


# ---------------------------------------------------------------------------
# 5. ChaosSimulator
# ---------------------------------------------------------------------------

class ChaosSimulator:
    """Adversarial chaos testing for the execution substrate."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        self.chaos_results = os.path.join(self.recovery_dir, "chaos-results.json")
        _ensure_dirs(self.exec_dir, self.recovery_dir)

    def simulate_sigkill(self, exec_id):
        """Simulates SIGKILL mid-execution."""
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)

        simulation = {
            "simulation_id": f"chaos-sigkill-{uuid.uuid4()}",
            "type": "SIGKILL_SIMULATION",
            "exec_id": exec_id,
            "timestamp": _now_iso(),
        }

        if manifest_path is None:
            simulation["result"] = "SKIPPED"
            simulation["reason"] = "Manifest not found"
            return simulation

        try:
            manifest = _read_json(manifest_path)
            state = manifest.get("lifecycle_state", "")

            # Simulate: transition to a partial state
            lifecycle = manifest.get("lifecycle_history", [])
            lifecycle.append({"state": "EXECUTING", "timestamp": _now_epoch()})
            lifecycle.append({"state": "FAILED", "timestamp": _now_epoch(), "signal": "SIGKILL", "exit_code": -9})

            manifest["lifecycle_state"] = "FAILED"
            manifest["lifecycle_history"] = lifecycle
            manifest["chaos_injection"] = {
                "type": "SIGKILL",
                "injected_at": _now_iso(),
                "original_state": state,
            }

            # Backup and write
            backup = manifest_path + ".pre-chaos"
            shutil.copy2(manifest_path, backup)
            _write_json(manifest_path, manifest)

            simulation["result"] = "INJECTED"
            simulation["original_state"] = state
            simulation["injected_state"] = "FAILED"
            simulation["backup_path"] = backup

        except Exception as e:
            simulation["result"] = "ERROR"
            simulation["error"] = str(e)

        return simulation

    def simulate_partial_write(self, exec_id):
        """Simulates partial manifest write (truncated JSON)."""
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)

        simulation = {
            "simulation_id": f"chaos-partial-write-{uuid.uuid4()}",
            "type": "PARTIAL_WRITE_SIMULATION",
            "exec_id": exec_id,
            "timestamp": _now_iso(),
        }

        if manifest_path is None:
            simulation["result"] = "SKIPPED"
            simulation["reason"] = "Manifest not found"
            return simulation

        try:
            # Read original content
            with open(manifest_path, "r", encoding="utf-8") as f:
                original = f.read()

            # Create partial write (truncate to ~70%)
            partial = original[: int(len(original) * 0.7)]

            # Backup
            backup = manifest_path + ".pre-chaos"
            shutil.copy2(manifest_path, backup)

            # Write truncated version
            with open(manifest_path, "w", encoding="utf-8") as f:
                f.write(partial)

            # Verify it's now corrupted
            try:
                _read_json(manifest_path)
                simulation["corruption_verified"] = False
            except json.JSONDecodeError:
                simulation["corruption_verified"] = True

            simulation["result"] = "INJECTED"
            simulation["original_size"] = len(original)
            simulation["truncated_size"] = len(partial)
            simulation["backup_path"] = backup

        except Exception as e:
            simulation["result"] = "ERROR"
            simulation["error"] = str(e)

        return simulation

    def simulate_corrupted_artifact(self, exec_id):
        """Simulates corrupted replay artifact."""
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)

        simulation = {
            "simulation_id": f"chaos-corrupted-artifact-{uuid.uuid4()}",
            "type": "CORRUPTED_ARTIFACT_SIMULATION",
            "exec_id": exec_id,
            "timestamp": _now_iso(),
        }

        if manifest_path is None:
            simulation["result"] = "SKIPPED"
            simulation["reason"] = "Manifest not found"
            return simulation

        try:
            manifest = _read_json(manifest_path)

            # Corrupt the replay_contract checksum
            if "replay_contract" in manifest:
                manifest["replay_contract"]["original_checksum"] = "CORRUPTED_" + uuid.uuid4().hex[:32]

            # Corrupt integrity seal if present
            if "integrity_seal" in manifest:
                manifest["integrity_seal"] = "INVALID_" + uuid.uuid4().hex[:64]

            manifest["chaos_injection"] = {
                "type": "CORRUPTED_ARTIFACT",
                "injected_at": _now_iso(),
                "corrupted_fields": ["replay_contract.original_checksum", "integrity_seal"],
            }

            backup = manifest_path + ".pre-chaos"
            shutil.copy2(manifest_path, backup)
            _write_json(manifest_path, manifest)

            simulation["result"] = "INJECTED"
            simulation["corrupted_fields"] = ["replay_contract.original_checksum", "integrity_seal"]
            simulation["backup_path"] = backup

        except Exception as e:
            simulation["result"] = "ERROR"
            simulation["error"] = str(e)

        return simulation

    def simulate_filesystem_race(self):
        """Simulates TOCTOU race condition by creating a conflict scenario."""
        simulation = {
            "simulation_id": f"chaos-race-{uuid.uuid4()}",
            "type": "TOCTOU_RACE_SIMULATION",
            "timestamp": _now_iso(),
        }

        try:
            # Create two manifests that modify the same file (race condition)
            race_file = "shared_target.txt"

            for i in range(2):
                exec_id = f"race-exec-{uuid.uuid4().hex[:8]}"
                manifest = {
                    "execution_id": exec_id,
                    "lifecycle_state": "REPLAYABLE",
                    "task": f"race_simulator_{i}",
                    "trust_tier": "WORKSPACE_WRITE",
                    "lifecycle_history": [{"state": "REPLAYABLE", "timestamp": _now_epoch()}],
                    "mutation_journal": {
                        "journal_id": f"journal-{exec_id}",
                        "execution_id": exec_id,
                        "mutations": {
                            "created": [race_file],
                            "modified": [],
                            "deleted": [],
                        },
                        "violations_detected": [],
                        "rollback_executed": False,
                        "symlinks_created": [],
                    },
                    "replay_contract": {
                        "contract_id": f"replay-{exec_id}",
                        "execution_id": exec_id,
                        "nonce": str(uuid.uuid4()),
                        "payload_command": f"echo race_{i}",
                        "expected_exit_code": 0,
                        "expected_mutation_count": 1,
                        "original_checksum": hashlib.sha256(f"race_{i}".encode()).hexdigest(),
                    },
                    "telemetry": {
                        "total_duration_ms": 10.0,
                        "command_execution_duration_ms": 5.0,
                        "governance_resolution_overhead_ms": 5.0,
                        "context_expansion_budget_bytes": 0,
                        "memory_usage_mb": 1.0,
                        "lease_expired_during_execution": False,
                        "sanitized_env_vars_removed": 0,
                    },
                    "integrity_seal": "",
                }
                manifest["integrity_seal"] = hashlib.sha256(
                    json.dumps({k: v for k, v in manifest.items() if k != "integrity_seal"}, sort_keys=True).encode()
                ).hexdigest()

                manifest_path = os.path.join(self.exec_dir, f"execution-manifest-{exec_id}.json")
                _write_json(manifest_path, manifest)

            simulation["result"] = "INJECTED"
            simulation["race_file"] = race_file
            simulation["created_executions"] = 2

        except Exception as e:
            simulation["result"] = "ERROR"
            simulation["error"] = str(e)

        return simulation

    def simulate_concurrent_governance_mutation(self):
        """Simulates concurrent governance mutation."""
        simulation = {
            "simulation_id": f"chaos-gov-mutation-{uuid.uuid4()}",
            "type": "CONCURRENT_GOV_MUTATION_SIMULATION",
            "timestamp": _now_iso(),
        }

        try:
            # Create two executions both claiming to modify governance
            gov_paths = [".agents/AGENTS.md", ".agents/governance/core/quality/quality-gates.md"]

            for i in range(2):
                exec_id = f"gov-race-{uuid.uuid4().hex[:8]}"
                manifest = {
                    "execution_id": exec_id,
                    "lifecycle_state": "REPLAYABLE",
                    "task": f"governance_mutation_simulator_{i}",
                    "trust_tier": "GOVERNANCE_WRITE",
                    "domain_scope": "governance",
                    "lifecycle_history": [{"state": "REPLAYABLE", "timestamp": _now_epoch()}],
                    "mutation_journal": {
                        "journal_id": f"journal-{exec_id}",
                        "execution_id": exec_id,
                        "mutations": {
                            "created": [],
                            "modified": gov_paths,
                            "deleted": [],
                        },
                        "violations_detected": [],
                        "rollback_executed": False,
                        "symlinks_created": [],
                    },
                    "replay_contract": {
                        "contract_id": f"replay-{exec_id}",
                        "execution_id": exec_id,
                        "nonce": str(uuid.uuid4()),
                        "payload_command": f"echo gov_{i}",
                        "expected_exit_code": 0,
                        "expected_mutation_count": len(gov_paths),
                        "original_checksum": hashlib.sha256(f"gov_{i}".encode()).hexdigest(),
                    },
                    "telemetry": {
                        "total_duration_ms": 15.0,
                        "command_execution_duration_ms": 8.0,
                        "governance_resolution_overhead_ms": 7.0,
                        "context_expansion_budget_bytes": 0,
                        "memory_usage_mb": 1.0,
                        "lease_expired_during_execution": False,
                        "sanitized_env_vars_removed": 0,
                    },
                    "integrity_seal": "",
                }
                manifest["integrity_seal"] = hashlib.sha256(
                    json.dumps({k: v for k, v in manifest.items() if k != "integrity_seal"}, sort_keys=True).encode()
                ).hexdigest()

                manifest_path = os.path.join(self.exec_dir, f"execution-manifest-{exec_id}.json")
                _write_json(manifest_path, manifest)

            simulation["result"] = "INJECTED"
            simulation["governance_paths"] = gov_paths
            simulation["created_executions"] = 2

        except Exception as e:
            simulation["result"] = "ERROR"
            simulation["error"] = str(e)

        return simulation

    def run_chaos_suite(self):
        """Runs all chaos simulations."""
        results = {
            "suite_id": f"chaos-suite-{uuid.uuid4()}",
            "started_at": _now_iso(),
            "simulations": [],
        }

        # Create a test execution for chaos simulations
        test_exec_id = f"chaos-target-{uuid.uuid4().hex[:8]}"
        self._create_test_manifest(test_exec_id)

        simulations = [
            self.simulate_sigkill(test_exec_id),
            self.simulate_corrupted_artifact(test_exec_id),
            self.simulate_partial_write(test_exec_id),
            self.simulate_filesystem_race(),
            self.simulate_concurrent_governance_mutation(),
        ]

        results["simulations"] = simulations
        results["completed_at"] = _now_iso()
        results["total_simulations"] = len(simulations)
        results["successful_injections"] = sum(1 for s in simulations if s.get("result") == "INJECTED")

        _write_json(self.chaos_results, results)
        return results

    def _create_test_manifest(self, exec_id):
        """Creates a minimal test manifest for chaos simulations."""
        manifest = {
            "execution_id": exec_id,
            "lifecycle_state": "REPLAYABLE",
            "task": "chaos_test_target",
            "trust_tier": "WORKSPACE_WRITE",
            "lifecycle_history": [{"state": "REPLAYABLE", "timestamp": _now_epoch()}],
            "mutation_journal": {
                "journal_id": f"journal-{exec_id}",
                "execution_id": exec_id,
                "mutations": {"created": [], "modified": [], "deleted": []},
                "violations_detected": [],
                "rollback_executed": False,
                "symlinks_created": [],
            },
            "replay_contract": {
                "contract_id": f"replay-{exec_id}",
                "execution_id": exec_id,
                "nonce": str(uuid.uuid4()),
                "payload_command": "echo test",
                "expected_exit_code": 0,
                "expected_mutation_count": 0,
                "original_checksum": hashlib.sha256(b"test").hexdigest(),
            },
            "telemetry": {
                "total_duration_ms": 5.0,
                "command_execution_duration_ms": 2.0,
                "governance_resolution_overhead_ms": 3.0,
                "context_expansion_budget_bytes": 0,
                "memory_usage_mb": 1.0,
                "lease_expired_during_execution": False,
                "sanitized_env_vars_removed": 0,
            },
            "integrity_seal": "",
        }
        manifest["integrity_seal"] = hashlib.sha256(
            json.dumps({k: v for k, v in manifest.items() if k != "integrity_seal"}, sort_keys=True).encode()
        ).hexdigest()

        manifest_path = os.path.join(self.exec_dir, f"execution-manifest-{exec_id}.json")
        _write_json(manifest_path, manifest)
        return manifest_path


# ---------------------------------------------------------------------------
# 6. CrashSafeReplay
# ---------------------------------------------------------------------------

class CrashSafeReplay:
    """Crash-safe checkpoint, resume, and replay recovery."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.exec_dir = os.path.join(self.target_dir, EXECUTION_DIR)
        self.checkpoints_dir = os.path.join(self.target_dir, RECOVERY_DIR, "checkpoints")
        _ensure_dirs(self.exec_dir, self.checkpoints_dir)

    def save_checkpoint(self, exec_id, state):
        """Saves an execution checkpoint."""
        checkpoint = {
            "checkpoint_id": f"checkpoint-{exec_id}-{uuid.uuid4().hex[:8]}",
            "execution_id": exec_id,
            "state": state,
            "saved_at": _now_iso(),
            "saved_at_epoch": _now_epoch(),
            "checksum": hashlib.sha256(json.dumps(state, sort_keys=True, default=str).encode()).hexdigest(),
        }

        checkpoint_path = os.path.join(self.checkpoints_dir, f"{exec_id}.json")

        # If a checkpoint already exists, save it as a previous version
        if os.path.exists(checkpoint_path):
            prev_path = checkpoint_path + ".prev"
            shutil.copy2(checkpoint_path, prev_path)

        _write_json(checkpoint_path, checkpoint)

        return {
            "success": True,
            "checkpoint_id": checkpoint["checkpoint_id"],
            "checkpoint_path": checkpoint_path,
        }

    def load_checkpoint(self, exec_id):
        """Loads the last checkpoint for an execution."""
        checkpoint_path = os.path.join(self.checkpoints_dir, f"{exec_id}.json")
        if not os.path.exists(checkpoint_path):
            return {"success": False, "error": f"No checkpoint found for execution {exec_id}"}

        try:
            checkpoint = _read_json(checkpoint_path)
            return {
                "success": True,
                "checkpoint_id": checkpoint["checkpoint_id"],
                "execution_id": checkpoint["execution_id"],
                "state": checkpoint["state"],
                "saved_at": checkpoint["saved_at"],
                "checksum": checkpoint["checksum"],
            }
        except (json.JSONDecodeError, OSError) as e:
            return {"success": False, "error": f"Cannot read checkpoint: {e}"}

    def resume_from_checkpoint(self, exec_id):
        """Resumes execution from the last checkpoint."""
        loaded = self.load_checkpoint(exec_id)
        if not loaded["success"]:
            return loaded

        # Verify integrity before resuming
        integrity = self.verify_checkpoint_integrity(exec_id)
        if integrity["integrity"] != "VALID":
            return {
                "success": False,
                "error": f"Checkpoint integrity check failed: {integrity.get('issues', [])}",
            }

        # Find the manifest and update lifecycle
        manifest_path = _find_manifest_for_exec(self.exec_dir, exec_id)
        resume_result = {
            "success": True,
            "execution_id": exec_id,
            "checkpoint_state": loaded["state"],
            "resumed_at": _now_iso(),
        }

        if manifest_path:
            try:
                manifest = _read_json(manifest_path)
                lifecycle = manifest.get("lifecycle_history", [])
                lifecycle.append({"state": "RESUMED_FROM_CHECKPOINT", "timestamp": _now_epoch(), "checkpoint_id": loaded["checkpoint_id"]})
                manifest["lifecycle_history"] = lifecycle
                _write_json(manifest_path, manifest)
                resume_result["manifest_updated"] = True
            except (json.JSONDecodeError, OSError):
                resume_result["manifest_updated"] = False
                resume_result["warning"] = "Could not update manifest"

        return resume_result

    def verify_checkpoint_integrity(self, exec_id):
        """Verifies that a checkpoint wasn't corrupted."""
        checkpoint_path = os.path.join(self.checkpoints_dir, f"{exec_id}.json")
        if not os.path.exists(checkpoint_path):
            return {"integrity": "NOT_FOUND", "execution_id": exec_id}

        try:
            checkpoint = _read_json(checkpoint_path)
        except (json.JSONDecodeError, OSError):
            return {"integrity": "CORRUPTED", "execution_id": exec_id, "reason": "cannot_read"}

        issues = []

        # Verify checksum
        state = checkpoint.get("state", {})
        expected_checksum = hashlib.sha256(json.dumps(state, sort_keys=True, default=str).encode()).hexdigest()
        if checkpoint.get("checksum") != expected_checksum:
            issues.append("checksum_mismatch")

        # Verify required fields
        for field in ("checkpoint_id", "execution_id", "state", "saved_at"):
            if field not in checkpoint:
                issues.append(f"missing_field:{field}")

        integrity_status = "VALID" if not issues else "CORRUPTED"
        return {
            "integrity": integrity_status,
            "execution_id": exec_id,
            "issues": issues,
            "verified_at": _now_iso(),
        }


# ---------------------------------------------------------------------------
# 7. CorruptedEvidenceIsolator
# ---------------------------------------------------------------------------

class CorruptedEvidenceIsolator:
    """Scans for, isolates, and reports on corrupted evidence files."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.evidence_base = os.path.join(self.target_dir, EVIDENCE_BASE)
        self.quarantine_dir = os.path.join(self.target_dir, QUARANTINE_DIR)
        self.recovery_dir = os.path.join(self.target_dir, RECOVERY_DIR)
        _ensure_dirs(self.evidence_base, self.quarantine_dir, self.recovery_dir)

    def _scan_evidence_files(self):
        """Recursively find all evidence files."""
        files = []
        if not os.path.isdir(self.evidence_base):
            return files
        for root, dirs, filenames in os.walk(self.evidence_base):
            # Skip quarantine directory
            rel_root = os.path.relpath(root, self.target_dir)
            if rel_root.startswith(QUARANTINE_DIR):
                continue
            for f in filenames:
                files.append(os.path.join(root, f))
        return files

    def scan_for_corruption(self):
        """Scans all evidence for corruption."""
        corrupted = []
        healthy = []

        for filepath in self._scan_evidence_files():
            result = {"path": filepath}

            if filepath.endswith(".json"):
                try:
                    data = _read_json(filepath)

                    # Check integrity seal
                    if "integrity_seal" in data:
                        expected = hashlib.sha256(
                            json.dumps({k: v for k, v in data.items() if k != "integrity_seal"}, sort_keys=True).encode()
                        ).hexdigest()
                        if data["integrity_seal"] != expected:
                            result["issue"] = "integrity_seal_mismatch"
                            corrupted.append(result)
                            continue

                    healthy.append(result)

                except json.JSONDecodeError as e:
                    result["issue"] = f"json_parse_error: {e}"
                    corrupted.append(result)
                except OSError as e:
                    result["issue"] = f"io_error: {e}"
                    corrupted.append(result)

            elif filepath.endswith(".jsonl"):
                try:
                    with open(filepath, "r", encoding="utf-8") as f:
                        for line_num, line in enumerate(f, 1):
                            line = line.strip()
                            if not line:
                                continue
                            json.loads(line)
                    healthy.append(result)
                except (json.JSONDecodeError, OSError) as e:
                    result["issue"] = f"jsonl_error: {e}"
                    corrupted.append(result)

            else:
                # Non-JSON files — check readability
                try:
                    with open(filepath, "rb") as f:
                        f.read(1)
                    healthy.append(result)
                except OSError as e:
                    result["issue"] = f"io_error: {e}"
                    corrupted.append(result)

        return {"corrupted": corrupted, "healthy": healthy, "total_scanned": len(corrupted) + len(healthy)}

    def isolate_corrupted(self, evidence_path):
        """Moves corrupted evidence to quarantine."""
        abs_path = os.path.normpath(os.path.join(self.target_dir, evidence_path))

        if not os.path.exists(abs_path):
            return {"success": False, "error": f"Path not found: {evidence_path}"}

        # Verify it's within evidence base
        if not abs_path.startswith(os.path.normpath(self.evidence_base)):
            return {"success": False, "error": "Path is outside evidence directory"}

        # Compute quarantine path (preserve relative structure)
        rel_path = os.path.relpath(abs_path, self.evidence_base)
        quarantine_path = os.path.join(self.quarantine_dir, rel_path)
        os.makedirs(os.path.dirname(quarantine_path), exist_ok=True)

        # Move to quarantine
        shutil.move(abs_path, quarantine_path)

        isolation_record = {
            "original_path": evidence_path,
            "quarantine_path": os.path.relpath(quarantine_path, self.target_dir),
            "isolated_at": _now_iso(),
            "isolation_engine": "CorruptedEvidenceIsolator",
        }

        _write_json(
            os.path.join(self.quarantine_dir, f"isolation-{uuid.uuid4().hex[:8]}.json"),
            isolation_record,
        )

        return {
            "success": True,
            "original_path": evidence_path,
            "quarantine_path": isolation_record["quarantine_path"],
        }

    def verify_isolation(self, evidence_path):
        """Verifies that evidence is properly isolated in quarantine."""
        abs_path = os.path.normpath(os.path.join(self.target_dir, evidence_path))

        # Check original location no longer has the file
        original_exists = os.path.exists(abs_path)

        # Check it exists in quarantine
        rel_path = os.path.relpath(abs_path, self.evidence_base) if abs_path.startswith(os.path.normpath(self.evidence_base)) else ""
        quarantine_path = os.path.join(self.quarantine_dir, rel_path) if rel_path else ""
        quarantine_exists = os.path.exists(quarantine_path) if quarantine_path else False

        # Also check for isolation record
        isolation_records = []
        if os.path.isdir(self.quarantine_dir):
            for f in os.listdir(self.quarantine_dir):
                if f.startswith("isolation-") and f.endswith(".json"):
                    try:
                        record = _read_json(os.path.join(self.quarantine_dir, f))
                        if record.get("original_path") == evidence_path:
                            isolation_records.append(record)
                    except (json.JSONDecodeError, OSError):
                        pass

        return {
            "evidence_path": evidence_path,
            "original_location_exists": original_exists,
            "quarantine_location_exists": quarantine_exists,
            "isolation_records_found": len(isolation_records),
            "properly_isolated": not original_exists and quarantine_exists,
            "verified_at": _now_iso(),
        }

    def generate_corruption_report(self):
        """Full corruption report."""
        scan_result = self.scan_for_corruption()

        report = {
            "report_id": f"corruption-report-{uuid.uuid4()}",
            "generated_at": _now_iso(),
            "summary": {
                "total_scanned": scan_result["total_scanned"],
                "corrupted_count": len(scan_result["corrupted"]),
                "healthy_count": len(scan_result["healthy"]),
            },
            "corrupted_files": scan_result["corrupted"],
        }

        report_path = os.path.join(self.recovery_dir, "corruption-report.json")
        _write_json(report_path, report)

        return report


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _parse_args(argv):
    """Minimal argument parser (stdlib only)."""
    if len(argv) < 2:
        return None

    subcmd = argv[1]
    args = argv[2:]

    result = {"subcmd": subcmd, "target_dir": ".", "positional": []}

    i = 0
    while i < len(args):
        if args[i] == "--dir" and i + 1 < len(args):
            result["target_dir"] = args[i + 1]
            i += 2
        else:
            result["positional"].append(args[i])
            i += 1

    return result


def main():
    parsed = _parse_args(sys.argv)
    if parsed is None:
        print("Usage:")
        print("  failure-recovery.py recover [--dir <dir>]")
        print("  failure-recovery.py repair <journal_id> [--dir <dir>]")
        print("  failure-recovery.py locks [--dir <dir>]")
        print("  failure-recovery.py conflicts [--dir <dir>]")
        print("  failure-recovery.py chaos [--dir <dir>]")
        print("  failure-recovery.py checkpoint <exec_id> [--dir <dir>]")
        print("  failure-recovery.py quarantine [--dir <dir>]")
        print("  failure-recovery.py report [--dir <dir>]")
        sys.exit(1)

    td = parsed["target_dir"]
    subcmd = parsed["subcmd"]
    positional = parsed["positional"]

    if subcmd == "recover":
        mgr = CrashRecoveryManager(td)
        interrupted = mgr.detect_interrupted_executions()
        if not interrupted:
            print("No interrupted executions found.")
        else:
            print(f"Found {len(interrupted)} interrupted execution(s):")
            for entry in interrupted:
                print(f"  - {entry['execution_id']}: state={entry['state']}, task={entry['task']}")
            print("\nAttempting recovery...")
            for entry in interrupted:
                result = mgr.recover_interrupted(entry["execution_id"])
                print(f"  - {entry['execution_id']}: {'OK' if result['success'] else 'FAILED: ' + result.get('error', '')}")
        report = mgr.recovery_report()
        print(f"\nRecovery report: {report['summary']}")

    elif subcmd == "repair":
        if not positional:
            print("Usage: failure-recovery.py repair <journal_id> [--dir <dir>]")
            sys.exit(1)
        journal_id = positional[0]
        jr = JournalRepair(td)

        # First verify
        integrity = jr.verify_journal_integrity(journal_id)
        print(f"Journal integrity: {integrity['integrity']}")
        if integrity.get("issues"):
            print(f"  Issues: {integrity['issues']}")

        # Then repair
        result = jr.repair_journal(journal_id)
        if result["success"]:
            print(f"Journal repaired: {result['journal_id']}")
            print(f"  Repairs applied: {result['repairs_applied']}")
            print(f"  Backup: {result['backup_path']}")
        else:
            print(f"Repair failed: {result.get('error', 'unknown error')}")
            sys.exit(1)

    elif subcmd == "locks":
        lc = LockCleanup(td)
        active = lc.list_active_locks()
        if not active:
            print("No active locks found.")
        else:
            print(f"Active locks ({len(active)}):")
            for lock in active:
                print(f"  - {lock['lock_id']}: age={lock['age_minutes']:.1f}min")

        stale = lc.detect_stale_locks()
        if stale:
            print(f"\nStale locks ({len(stale)}):")
            removed = lc.cleanup_stale_locks()
            for entry in removed:
                print(f"  - {entry['lock_id']}: {entry['status']}")
        else:
            print("\nNo stale locks detected.")

    elif subcmd == "conflicts":
        cch = ConcurrentConflictHandler(td)
        report = cch.get_conflict_report()
        print(f"Conflict Report: {report['report_id']}")
        print(f"  Total executions: {report['summary']['total_executions']}")
        print(f"  Total conflicts:  {report['summary']['total_conflicts']}")
        print(f"  Affected files:   {report['summary']['affected_files']}")
        if report["conflicts"]:
            print("\nConflicts:")
            for c in report["conflicts"]:
                print(f"  - File: {c['file']}")
                print(f"    Executions: {c['conflicting_executions']}")

    elif subcmd == "chaos":
        cs = ChaosSimulator(td)
        print("Running chaos simulation suite...")
        results = cs.run_chaos_suite()
        print(f"Suite: {results['suite_id']}")
        print(f"Simulations: {results['total_simulations']}")
        print(f"Successful injections: {results['successful_injections']}")
        for sim in results["simulations"]:
            print(f"  - [{sim['type']}] result={sim['result']}")
        print(f"\nResults saved to: {cs.chaos_results}")

    elif subcmd == "checkpoint":
        if not positional:
            print("Usage: failure-recovery.py checkpoint <exec_id> [--dir <dir>]")
            sys.exit(1)
        exec_id = positional[0]
        csr = CrashSafeReplay(td)

        # Load and verify existing checkpoint
        loaded = csr.load_checkpoint(exec_id)
        if loaded["success"]:
            print(f"Loaded checkpoint: {loaded['checkpoint_id']}")
            integrity = csr.verify_checkpoint_integrity(exec_id)
            print(f"Integrity: {integrity['integrity']}")
            if integrity["integrity"] == "VALID":
                resumed = csr.resume_from_checkpoint(exec_id)
                if resumed["success"]:
                    print(f"Resumed from checkpoint. Manifest updated: {resumed.get('manifest_updated')}")
                else:
                    print(f"Resume failed: {resumed.get('error', 'unknown')}")
        else:
            # No checkpoint exists — create one from manifest
            manifest_path = _find_manifest_for_exec(
                os.path.join(td, EXECUTION_DIR), exec_id
            )
            if manifest_path:
                try:
                    manifest = _read_json(manifest_path)
                    state = {
                        "lifecycle_state": manifest.get("lifecycle_state"),
                        "lifecycle_history": manifest.get("lifecycle_history", []),
                        "task": manifest.get("task"),
                    }
                    saved = csr.save_checkpoint(exec_id, state)
                    print(f"Created checkpoint: {saved['checkpoint_id']}")
                    print(f"Path: {saved['checkpoint_path']}")
                except (json.JSONDecodeError, OSError) as e:
                    print(f"Cannot read manifest: {e}")
                    sys.exit(1)
            else:
                print(f"No manifest found for execution {exec_id}")
                sys.exit(1)

    elif subcmd == "quarantine":
        cei = CorruptedEvidenceIsolator(td)
        print("Scanning for corrupted evidence...")
        scan = cei.scan_for_corruption()
        print(f"Total scanned: {scan['total_scanned']}")
        print(f"Corrupted:     {len(scan['corrupted'])}")
        print(f"Healthy:       {len(scan['healthy'])}")

        if scan["corrupted"]:
            print("\nIsolating corrupted files...")
            for entry in scan["corrupted"]:
                result = cei.isolate_corrupted(os.path.relpath(entry["path"], td))
                if result["success"]:
                    print(f"  - {entry['path']} -> {result['quarantine_path']}")
                else:
                    print(f"  - {entry['path']} FAILED: {result.get('error', '')}")

        report = cei.generate_corruption_report()
        print(f"\nCorruption report saved to: {os.path.join(td, RECOVERY_DIR, 'corruption-report.json')}")

    elif subcmd == "report":
        mgr = CrashRecoveryManager(td)
        jr = JournalRepair(td)
        lc = LockCleanup(td)
        cch = ConcurrentConflictHandler(td)
        cei = CorruptedEvidenceIsolator(td)

        recovery = mgr.recovery_report()
        journals = jr.detect_corrupted_journals()
        locks = lc.list_active_locks()
        stale_locks = lc.detect_stale_locks()
        conflicts = cch.get_conflict_report()
        corruption = cei.scan_for_corruption()

        print("=" * 60)
        print("  FAILURE & RECOVERY ENGINE — COMPREHENSIVE REPORT")
        print("=" * 60)
        print(f"\n[Recovery]")
        print(f"  Interrupted executions: {recovery['summary']['interrupted_count']} / {recovery['summary']['total_executions']}")
        print(f"\n[Journals]")
        print(f"  Corrupted journals:     {len(journals)}")
        for j in journals:
            print(f"    - {j['journal_id']}: {j.get('issues', j.get('issue', ''))}")
        print(f"\n[Locks]")
        print(f"  Active locks:           {len(locks)}")
        print(f"  Stale locks:            {len(stale_locks)}")
        print(f"\n[Conflicts]")
        print(f"  Total conflicts:        {conflicts['summary']['total_conflicts']}")
        print(f"  Affected files:         {conflicts['summary']['affected_files']}")
        print(f"\n[Corruption]")
        print(f"  Scanned files:          {corruption['total_scanned']}")
        print(f"  Corrupted:              {len(corruption['corrupted'])}")
        print(f"  Healthy:                {len(corruption['healthy'])}")
        print("=" * 60)

    else:
        print(f"Unknown subcommand: {subcmd}")
        sys.exit(1)


if __name__ == "__main__":
    main()
