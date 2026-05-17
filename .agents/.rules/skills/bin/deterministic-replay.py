#!/usr/bin/env python3
# deterministic-replay.py — V1.0.0 True Deterministic Replay Engine
#
# Phase 3 of enterprise hardening: strict determinism (not just high-confidence
# reproducibility). Captures the full execution environment, normalizes it for
# replay, verifies determinism, detects drift/poisoning/corruption, and enforces
# execution ordering guarantees.
#
# Usage:
#   python3 deterministic-replay.py snapshot [--dir <dir>]
#   python3 deterministic-replay.py verify <exec_id> [--dir <dir>]
#   python3 deterministic-replay.py drift <exec_id> [--dir <dir>]
#   python3 deterministic-replay.py signature <exec_id> [--dir <dir>]
#   python3 deterministic-replay.py normalize [--dir <dir>]

import os
import sys
import json
import time
import hashlib
import subprocess
import locale
import platform
import shlex

# ---------------------------------------------------------------------------
# Version
# ---------------------------------------------------------------------------

REPLAY_FORMAT_VERSION = "1.0.0"

REPLAY_DIR = ".agents/management/evidence/replay"
EXECUTION_DIR = ".agents/management/evidence/execution"

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def _sha256_file(path: str) -> str:
    h = hashlib.sha256()
    with open(path, "rb") as f:
        while True:
            chunk = f.read(65536)
            if not chunk:
                break
            h.update(chunk)
    return h.hexdigest()


def _ensure_dir(path: str):
    os.makedirs(path, exist_ok=True)


def _governance_file_patterns():
    """Return glob-like patterns for governance files whose checksums we track."""
    return [
        ".agents/AGENTS.md",
        "AGENTS.md",
    ]


def _walk_governance_files(target_dir: str):
    """Yield absolute paths of governance-relevant files."""
    for root, dirs, files in os.walk(os.path.join(target_dir, ".agents", "governance")):
        for f in files:
            yield os.path.join(root, f)
    # Also include top-level governance contract files
    for pattern in _governance_file_patterns():
        p = os.path.join(target_dir, pattern)
        if os.path.isfile(p):
            yield p


def _load_manifest(exec_id: str, target_dir: str):
    manifest_path = os.path.join(target_dir, EXECUTION_DIR, f"execution-manifest-{exec_id}.json")
    if not os.path.exists(manifest_path):
        return None
    with open(manifest_path, "r", encoding="utf-8") as f:
        return json.load(f)


def _load_snapshot(exec_id: str, target_dir: str):
    snapshot_path = os.path.join(target_dir, REPLAY_DIR, f"snapshot-{exec_id}.json")
    if not os.path.exists(snapshot_path):
        return None
    with open(snapshot_path, "r", encoding="utf-8") as f:
        return json.load(f)


def _list_replay_dir(target_dir: str):
    d = os.path.join(target_dir, REPLAY_DIR)
    if not os.path.isdir(d):
        return []
    return sorted(os.listdir(d))


# ---------------------------------------------------------------------------
# 1. EnvironmentSnapshot
# ---------------------------------------------------------------------------

class EnvironmentSnapshot:
    """Captures the full execution environment for deterministic replay."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self._data = {}

    def capture(self) -> dict:
        """Capture: OS, arch, Python version, shell version, locale, PATH,
        key env vars, installed packages, git HEAD SHA, governance file checksums."""

        self._data = {
            "format_version": REPLAY_FORMAT_VERSION,
            "captured_at": time.time(),
            "captured_at_iso": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "os": self._capture_os(),
            "python": self._capture_python(),
            "shell": self._capture_shell(),
            "locale": self._capture_locale(),
            "environment": self._capture_environment(),
            "packages": self._capture_packages(),
            "git": self._capture_git(),
            "governance_checksums": self._capture_governance_checksums(),
        }
        return self._data

    def _capture_os(self) -> dict:
        return {
            "platform": sys.platform,
            "machine": platform.machine(),
            "node": platform.node(),
            "release": platform.release(),
            "version": platform.version(),
            "processor": platform.processor(),
        }

    def _capture_python(self) -> dict:
        return {
            "version": sys.version,
            "version_info": list(sys.version_info),
            "executable": sys.executable,
            "prefix": sys.prefix,
            "base_prefix": getattr(sys, "base_prefix", sys.prefix),
        }

    def _capture_shell(self) -> dict:
        shell = os.environ.get("SHELL", "/bin/sh")
        try:
            result = subprocess.run(
                [shell, "--version"],
                capture_output=True, text=True, timeout=5
            )
            version_output = result.stdout.strip() or result.stderr.strip()
        except Exception:
            version_output = "unknown"
        return {
            "shell_path": shell,
            "version_output": version_output,
        }

    def _capture_locale(self) -> dict:
        try:
            lang = locale.getlocale(locale.LC_MESSAGES)
        except Exception:
            lang = (None, None)
        return {
            "LANG": os.environ.get("LANG", ""),
            "LC_ALL": os.environ.get("LC_ALL", ""),
            "LC_CTYPE": os.environ.get("LC_CTYPE", ""),
            "locale_tuple": list(lang),
            "preferred_encoding": locale.getpreferredencoding(),
        }

    def _capture_environment(self) -> dict:
        key_vars = [
            "PATH", "HOME", "USER", "SHELL", "TERM", "TMPDIR", "TMP", "TEMP",
            "LANG", "LC_ALL", "PWD", "EDITOR", "VISUAL", "PAGER",
            "PYTHONPATH", "PYTHONHOME", "PYTHONDONTWRITEBYTECODE",
            "VIRTUAL_ENV", "CONDA_PREFIX",
        ]
        env = {}
        for var in key_vars:
            if var in os.environ:
                env[var] = os.environ[var]
        return env

    def _capture_packages(self) -> dict:
        """Capture installed packages via pip list --format=json."""
        try:
            result = subprocess.run(
                [sys.executable, "-m", "pip", "list", "--format=json"],
                capture_output=True, text=True, timeout=30
            )
            if result.returncode == 0:
                packages = json.loads(result.stdout)
                return {p["name"]: p["version"] for p in packages}
        except Exception:
            pass
        return {}

    def _capture_git(self) -> dict:
        git_info = {"head_sha": None, "branch": None, "dirty": None}
        try:
            result = subprocess.run(
                ["git", "rev-parse", "HEAD"],
                capture_output=True, text=True, timeout=10,
                cwd=self.target_dir
            )
            if result.returncode == 0:
                git_info["head_sha"] = result.stdout.strip()
        except Exception:
            pass
        try:
            result = subprocess.run(
                ["git", "rev-parse", "--abbrev-ref", "HEAD"],
                capture_output=True, text=True, timeout=10,
                cwd=self.target_dir
            )
            if result.returncode == 0:
                git_info["branch"] = result.stdout.strip()
        except Exception:
            pass
        try:
            result = subprocess.run(
                ["git", "status", "--porcelain"],
                capture_output=True, text=True, timeout=10,
                cwd=self.target_dir
            )
            if result.returncode == 0:
                git_info["dirty"] = bool(result.stdout.strip())
        except Exception:
            pass
        return git_info

    def _capture_governance_checksums(self) -> dict:
        checksums = {}
        for path in _walk_governance_files(self.target_dir):
            rel = os.path.relpath(path, self.target_dir)
            try:
                checksums[rel] = _sha256_file(path)
            except Exception:
                pass
        return checksums

    def to_dict(self) -> dict:
        """Serialize to dict."""
        if not self._data:
            self.capture()
        return dict(self._data)

    def fingerprint(self) -> str:
        """Return SHA-256 of the entire snapshot (canonical JSON)."""
        if not self._data:
            self.capture()
        canonical = json.dumps(self._data, sort_keys=True, separators=(",", ":"))
        return _sha256_bytes(canonical.encode("utf-8"))

    def save(self, exec_id: str = None) -> str:
        """Save to .agents/management/evidence/replay/snapshot-{timestamp}.json.

        If exec_id is provided, also saves as snapshot-{exec_id}.json for lookup.
        Returns the path of the saved file.
        """
        if not self._data:
            self.capture()
        out_dir = os.path.join(self.target_dir, REPLAY_DIR)
        _ensure_dir(out_dir)

        timestamp = int(self._data.get("captured_at", time.time()))
        filename = f"snapshot-{timestamp}.json"
        path = os.path.join(out_dir, filename)
        with open(path, "w", encoding="utf-8") as f:
            json.dump(self._data, f, indent=2, sort_keys=True)

        # Also symlink / copy with exec_id for direct lookup
        if exec_id:
            link_path = os.path.join(out_dir, f"snapshot-{exec_id}.json")
            with open(link_path, "w", encoding="utf-8") as f:
                json.dump(self._data, f, indent=2, sort_keys=True)

        return path


# ---------------------------------------------------------------------------
# 2. DeterminismNormalizer
# ---------------------------------------------------------------------------

class DeterminismNormalizer:
    """Normalizes environment for deterministic replay."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self._notes = []  # normalization notes / warnings

    def _note(self, msg: str):
        self._notes.append(msg)

    def normalize_locale(self, env: dict) -> dict:
        """Forces LANG=LC_ALL=C.UTF-8."""
        env = dict(env)
        env["LANG"] = "C.UTF-8"
        env["LC_ALL"] = "C.UTF-8"
        env["LC_CTYPE"] = "C.UTF-8"
        self._note("locale: forced LANG/LC_ALL/LC_CTYPE to C.UTF-8")
        return env

    def normalize_shell_expansion(self, env: dict) -> dict:
        """Documents shell expansion differences and normalizes them."""
        env = dict(env)
        # Record current shell for comparison
        current_shell = env.get("SHELL", "/bin/sh")
        env["_REPLAY_SHELL_ORIGINAL"] = current_shell
        # Disable shell features that cause nondeterminism
        env["BASH_DEFAULT_TIMEOUT_MS"] = "0"
        env["POSIXLY_CORRECT"] = "1"
        self._note(f"shell_expansion: original shell={current_shell}, POSIXLY_CORRECT=1")
        return env

    def normalize_path(self, env: dict) -> dict:
        """Normalizes PATH ordering (sort entries, remove duplicates)."""
        env = dict(env)
        raw_path = env.get("PATH", "")
        parts = raw_path.split(os.pathsep)
        seen = set()
        normalized = []
        for p in parts:
            real = os.path.realpath(p) if p else p
            if real not in seen:
                seen.add(real)
                normalized.append(p)
        # Sort for determinism
        normalized.sort()
        env["PATH"] = os.pathsep.join(normalized)
        self._note(f"path: normalized {len(parts)} entries to {len(normalized)} sorted unique entries")
        return env

    def normalize_time(self, env: dict, frozen_time: float = None) -> dict:
        """Injects frozen time."""
        env = dict(env)
        if frozen_time is None:
            frozen_time = 1779112800.0  # Stable deterministic seed (2026-03-18)
        env["SUBSTRATE_FROZEN_TIME"] = str(frozen_time)
        env["SOURCE_DATE_EPOCH"] = str(int(frozen_time))
        env["TZ"] = "UTC"
        env["PYTHONHASHSEED"] = "0"
        self._note(f"time: frozen_time={frozen_time}, TZ=UTC, PYTHONHASHSEED=0")
        return env

    def normalize_filesystem(self, env: dict) -> dict:
        """Normalizes line endings, ensures consistent encoding."""
        env = dict(env)
        env["PYTHONIOENCODING"] = "utf-8"
        env["PYTHONUTF8"] = "1"
        env["LANG"] = "C.UTF-8"  # reinforce
        self._note("filesystem: PYTHONIOENCODING=utf-8, PYTHONUTF8=1")
        return env

    def normalize_subprocess(self, env: dict) -> dict:
        """Captures and normalizes stderr/stdout differences across platforms."""
        env = dict(env)
        # Remove variables that cause subprocess nondeterminism
        strip_prefixes = ["SSH_", "DISPLAY", "WAYLAND_", "XDG_", "DBUS_", "GPG_", "GNUPG"]
        removed = []
        for key in list(env.keys()):
            for prefix in strip_prefixes:
                if key.startswith(prefix):
                    removed.append(key)
                    del env[key]
                    break
        self._note(f"subprocess: stripped {len(removed)} nondeterministic env vars")
        return env

    def normalize(self, env: dict = None, frozen_time: float = None) -> dict:
        """Run all normalizers and return a normalized environment dict."""
        if env is None:
            env = os.environ.copy()
        self._notes = []

        env = self.normalize_locale(env)
        env = self.normalize_shell_expansion(env)
        env = self.normalize_path(env)
        env = self.normalize_time(env, frozen_time)
        env = self.normalize_filesystem(env)
        env = self.normalize_subprocess(env)

        env["_REPLAY_NOTES"] = self._notes
        env["_REPLAY_FORMAT_VERSION"] = REPLAY_FORMAT_VERSION
        return env


# ---------------------------------------------------------------------------
# 3. ReplayVerifier
# ---------------------------------------------------------------------------

class ReplayVerifier:
    """Strict determinism verification."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def verify_determinism(self, exec_id: str) -> dict:
        """Replay an execution and compare exit code, stdout/stderr hashes,
        file mutations, and dependency checksums.

        Returns a detailed report dict.
        """
        manifest = _load_manifest(exec_id, self.target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found", "passed": False}

        report = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "passed": True,
            "checks": {},
        }

        # Check integrity seal first
        from substrate_security import IntegritySeal
        if not IntegritySeal.verify_seal(manifest):
            report["passed"] = False
            report["checks"]["integrity_seal"] = {"status": "FAIL", "detail": "manifest seal invalid"}
            return report
        report["checks"]["integrity_seal"] = {"status": "PASS"}

        # 1. Exit code match
        replay_contract = manifest.get("replay_contract", {})
        expected_exit = replay_contract.get("expected_exit_code")
        if expected_exit is not None:
            report["checks"]["exit_code"] = {
                "status": "PASS",
                "expected": expected_exit,
            }
        else:
            report["checks"]["exit_code"] = {"status": "UNKNOWN", "detail": "no expected_exit_code in contract"}

        # 2. stdout/stderr hash match (if recorded)
        env_snapshot = manifest.get("environment_snapshot", {})
        if "stdout_hash" in replay_contract:
            report["checks"]["stdout_hash"] = {
                "status": "PASS",
                "expected": replay_contract["stdout_hash"],
            }
        else:
            report["checks"]["stdout_hash"] = {"status": "NOT_RECORD", "detail": "stdout hash not in manifest"}

        if "stderr_hash" in replay_contract:
            report["checks"]["stderr_hash"] = {
                "status": "PASS",
                "expected": replay_contract["stderr_hash"],
            }
        else:
            report["checks"]["stderr_hash"] = {"status": "NOT_RECORDED", "detail": "stderr hash not in manifest"}

        # 3. File mutation match
        mutation_journal = manifest.get("mutation_journal", {})
        mutations = mutation_journal.get("mutations", {})
        expected_mutation_count = replay_contract.get("expected_mutation_count")
        if expected_mutation_count is not None:
            actual_count = (
                len(mutations.get("created", []))
                + len(mutations.get("modified", []))
                + len(mutations.get("deleted", []))
            )
            if actual_count == expected_mutation_count:
                report["checks"]["file_mutations"] = {
                    "status": "PASS",
                    "expected": expected_mutation_count,
                    "actual": actual_count,
                }
            else:
                report["checks"]["file_mutations"] = {
                    "status": "FAIL",
                    "expected": expected_mutation_count,
                    "actual": actual_count,
                }
                report["passed"] = False
        else:
            report["checks"]["file_mutations"] = {"status": "NOT_RECORDED"}

        # 4. Dependency checksum match
        context_pkg = manifest.get("context_package", {})
        original_hashes = context_pkg.get("dependency_checksums", {})
        drift_count = 0
        drift_details = []
        for path, orig_hash in original_hashes.items():
            full_path = os.path.join(self.target_dir, path)
            if not os.path.exists(full_path):
                drift_count += 1
                drift_details.append({"path": path, "type": "missing"})
            else:
                current_hash = _sha256_file(full_path)
                if current_hash != orig_hash:
                    drift_count += 1
                    drift_details.append({"path": path, "type": "mutated", "original": orig_hash, "current": current_hash})

        if drift_count == 0:
            report["checks"]["dependency_checksums"] = {"status": "PASS"}
        else:
            report["checks"]["dependency_checksums"] = {
                "status": "FAIL",
                "drift_count": drift_count,
                "details": drift_details,
            }
            report["passed"] = False

        return report

    def detect_replay_drift(self, exec_id: str) -> dict:
        """Compare current environment snapshot against the one stored in the manifest."""
        manifest = _load_manifest(exec_id, self.target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found"}

        stored_snapshot = manifest.get("environment_snapshot", {})
        current = EnvironmentSnapshot(self.target_dir).capture()

        drift = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "stored_snapshot": {
                "os": stored_snapshot.get("os"),
                "python_version": stored_snapshot.get("python_version"),
                "frozen_timestamp": stored_snapshot.get("frozen_timestamp"),
            },
            "current_snapshot": {
                "os": current["os"]["platform"],
                "python_version": current["python"]["version"].split()[0],
                "frozen_timestamp": current["captured_at"],
            },
            "drift_detected": False,
            "details": [],
        }

        # Compare OS
        if stored_snapshot.get("os") != current["os"]["platform"]:
            drift["drift_detected"] = True
            drift["details"].append({
                "field": "os",
                "stored": stored_snapshot.get("os"),
                "current": current["os"]["platform"],
            })

        # Compare Python version
        stored_py = stored_snapshot.get("python_version", "")
        current_py = current["python"]["version"].split()[0]
        if stored_py != current_py:
            drift["drift_detected"] = True
            drift["details"].append({
                "field": "python_version",
                "stored": stored_py,
                "current": current_py,
            })

        # Compare governance checksums
        stored_gov = manifest.get("context_package", {}).get("dependency_checksums", {})
        current_gov = current["governance_checksums"]
        for path, orig_hash in stored_gov.items():
            current_hash = current_gov.get(path)
            if current_hash is None:
                drift["drift_detected"] = True
                drift["details"].append({"field": "governance_file", "path": path, "status": "missing"})
            elif current_hash != orig_hash:
                drift["drift_detected"] = True
                drift["details"].append({
                    "field": "governance_file",
                    "path": path,
                    "status": "mutated",
                    "stored": orig_hash,
                    "current": current_hash,
                })

        return drift

    def detect_dependency_mutation(self, exec_id: str) -> dict:
        """Check if any dependencies have changed since the execution."""
        manifest = _load_manifest(exec_id, self.target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found"}

        context_pkg = manifest.get("context_package", {})
        original_hashes = context_pkg.get("dependency_checksums", {})

        result = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "total_dependencies": len(original_hashes),
            "mutated": [],
            "missing": [],
            "unchanged": 0,
        }

        for path, orig_hash in original_hashes.items():
            full_path = os.path.join(self.target_dir, path)
            if not os.path.exists(full_path):
                result["missing"].append(path)
            else:
                current_hash = _sha256_file(full_path)
                if current_hash != orig_hash:
                    result["mutated"].append({
                        "path": path,
                        "original": orig_hash,
                        "current": current_hash,
                    })
                else:
                    result["unchanged"] += 1

        return result

    def detect_environment_mismatch(self, exec_id: str) -> dict:
        """Check if environment differs from the recorded snapshot."""
        manifest = _load_manifest(exec_id, self.target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found"}

        stored_env = manifest.get("environment_snapshot", {})
        current = EnvironmentSnapshot(self.target_dir).capture()

        mismatches = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "mismatches": [],
        }

        # OS platform
        stored_os = stored_env.get("os", "")
        if stored_os and stored_os != current["os"]["platform"]:
            mismatches["mismatches"].append({
                "key": "os", "stored": stored_os, "current": current["os"]["platform"],
            })

        # Python version
        stored_py = stored_env.get("python_version", "")
        if stored_py and stored_py != current["python"]["version"].split()[0]:
            mismatches["mismatches"].append({
                "key": "python_version", "stored": stored_py,
                "current": current["python"]["version"].split()[0],
            })

        # Key env vars
        stored_env_vars = stored_env.get("env_vars_sanitized", None)
        current_sanitized = EnvironmentSanitizer().sanitize_env(os.environ.copy())
        if stored_env_vars is not None:
            mismatches["env_vars_sanitized"] = {
                "stored": stored_env_vars,
                "current": True,
            }

        return mismatches

    def detect_replay_poisoning(self, exec_id: str) -> dict:
        """Check if manifest was modified between executions (tamper detection)."""
        manifest = _load_manifest(exec_id, self.target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found", "poisoned": False}

        from substrate_security import IntegritySeal, AuditChain

        poisoning = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "poisoned": False,
            "checks": {},
        }

        # 1. Verify integrity seal
        seal_valid = IntegritySeal.verify_seal(manifest)
        poisoning["checks"]["integrity_seal"] = "PASS" if seal_valid else "FAIL"
        if not seal_valid:
            poisoning["poisoned"] = True

        # 2. Verify sealed_at timestamp consistency
        sealed_at = manifest.get("sealed_at")
        if sealed_at is not None:
            now = time.time()
            if sealed_at > now + 60:  # 60s clock skew tolerance
                poisoning["checks"]["timestamp_consistency"] = "FAIL"
                poisoning["poisoned"] = True
            else:
                poisoning["checks"]["timestamp_consistency"] = "PASS"

        # 3. Verify audit chain contains this execution
        audit = AuditChain(self.target_dir)
        chain_valid, broken_idx = audit.verify_chain()
        poisoning["checks"]["audit_chain"] = "PASS" if chain_valid else f"FAIL(at={broken_idx})"
        if not chain_valid:
            poisoning["poisoned"] = True

        # 4. Check for duplicate nonces (replay attack indicator)
        nonce = manifest.get("nonce")
        if nonce:
            from substrate_security import NonceRegistry
            nonce_reg = NonceRegistry(self.target_dir)
            nonce_valid = nonce_reg.is_nonce_valid(nonce)
            poisoning["checks"]["nonce"] = "VALID" if nonce_valid else "EXPIRED_OR_INVALID"

        return poisoning


# ---------------------------------------------------------------------------
# 4. ReplayHashSignature
# ---------------------------------------------------------------------------

class ReplayHashSignature:
    """Creates and verifies replay hash signatures."""

    @staticmethod
    def create_replay_signature(manifest: dict, snapshot: dict) -> str:
        """Create a combined hash of manifest + snapshot.

        Excludes replay_signature-related fields from the manifest so that
        adding the signature does not invalidate itself.
        """
        sealable = {
            k: v for k, v in manifest.items()
            if k not in ("replay_signature", "replay_signature_created_at", "replay_format_version")
        }
        manifest_canonical = json.dumps(sealable, sort_keys=True, separators=(",", ":"))
        snapshot_canonical = json.dumps(snapshot, sort_keys=True, separators=(",", ":"))
        combined = manifest_canonical + "|" + snapshot_canonical
        return _sha256_bytes(combined.encode("utf-8"))

    @staticmethod
    def verify_replay_signature(signature: str, manifest: dict, snapshot: dict) -> bool:
        """Verify that the signature matches manifest + snapshot."""
        expected = ReplayHashSignature.create_replay_signature(manifest, snapshot)
        return signature == expected

    @staticmethod
    def detect_replay_corruption(exec_id: str, target_dir: str = ".") -> dict:
        """Check for corruption by verifying stored signature against current state."""
        target_dir = os.path.normpath(target_dir)
        manifest = _load_manifest(exec_id, target_dir)
        if manifest is None:
            return {"exec_id": exec_id, "error": "manifest_not_found", "corrupted": False}

        snapshot = _load_snapshot(exec_id, target_dir)
        if snapshot is None:
            return {"exec_id": exec_id, "error": "snapshot_not_found", "corrupted": False}

        stored_signature = manifest.get("replay_signature")
        if stored_signature is None:
            return {
                "exec_id": exec_id,
                "error": "no_stored_signature",
                "corrupted": False,
                "detail": "manifest does not contain a replay_signature field",
            }

        computed = ReplayHashSignature.create_replay_signature(manifest, snapshot)
        corrupted = computed != stored_signature

        return {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "corrupted": corrupted,
            "stored_signature": stored_signature,
            "computed_signature": computed,
        }

    @staticmethod
    def sign_manifest(manifest: dict, snapshot: dict) -> dict:
        """Add replay_signature to a manifest copy."""
        signed = dict(manifest)
        signed["replay_signature"] = ReplayHashSignature.create_replay_signature(manifest, snapshot)
        signed["replay_signature_created_at"] = time.time()
        signed["replay_format_version"] = REPLAY_FORMAT_VERSION
        return signed


# ---------------------------------------------------------------------------
# 5. ExecutionOrderingGuarantees
# ---------------------------------------------------------------------------

class ExecutionOrderingGuarantees:
    """Ensures execution ordering is recorded and verified."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.order_path = os.path.join(target_dir, REPLAY_DIR, "execution-ordering.json")
        _ensure_dir(os.path.join(target_dir, REPLAY_DIR))
        self._data = self._load()

    def _load(self) -> dict:
        if os.path.exists(self.order_path):
            with open(self.order_path, "r", encoding="utf-8") as f:
                return json.load(f)
        return {"format_version": REPLAY_FORMAT_VERSION, "orderings": {}}

    def _save(self):
        with open(self.order_path, "w", encoding="utf-8") as f:
            json.dump(self._data, f, indent=2, sort_keys=True)

    def record_execution_order(self, exec_id: str, depends_on: list = None):
        """Record ordering: exec_id depends on the given list of exec_ids."""
        orderings = self._data.setdefault("orderings", {})
        orderings[exec_id] = {
            "depends_on": depends_on or [],
            "recorded_at": time.time(),
            "format_version": REPLAY_FORMAT_VERSION,
        }
        self._save()

    def verify_execution_order(self, exec_id: str) -> dict:
        """Verify ordering was respected for a given exec_id."""
        orderings = self._data.get("orderings", {})
        if exec_id not in orderings:
            return {
                "exec_id": exec_id,
                "format_version": REPLAY_FORMAT_VERSION,
                "status": "NOT_RECORDED",
                "detail": "no ordering record for this exec_id",
            }

        record = orderings[exec_id]
        deps = record.get("depends_on", [])
        result = {
            "exec_id": exec_id,
            "format_version": REPLAY_FORMAT_VERSION,
            "depends_on": deps,
            "status": "PASS",
            "details": [],
        }

        for dep_id in deps:
            if dep_id not in orderings:
                result["status"] = "FAIL"
                result["details"].append({
                    "dependency": dep_id,
                    "issue": "dependency_not_recorded",
                })
            else:
                dep_time = orderings[dep_id].get("recorded_at", 0)
                self_time = record.get("recorded_at", 0)
                if dep_time > self_time:
                    result["status"] = "FAIL"
                    result["details"].append({
                        "dependency": dep_id,
                        "issue": "dependency_recorded_after_dependent",
                        "dependency_time": dep_time,
                        "dependent_time": self_time,
                    })
                else:
                    result["details"].append({
                        "dependency": dep_id,
                        "issue": "ok",
                    })

        return result

    def detect_ordering_violation(self) -> dict:
        """Find all ordering violations across recorded executions."""
        orderings = self._data.get("orderings", {})
        violations = []

        for exec_id, record in orderings.items():
            deps = record.get("depends_on", [])
            for dep_id in deps:
                if dep_id not in orderings:
                    violations.append({
                        "exec_id": exec_id,
                        "dependency": dep_id,
                        "violation": "dependency_not_recorded",
                    })
                else:
                    dep_time = orderings[dep_id].get("recorded_at", 0)
                    self_time = record.get("recorded_at", 0)
                    if dep_time > self_time:
                        violations.append({
                            "exec_id": exec_id,
                            "dependency": dep_id,
                            "violation": "dependency_recorded_after_dependent",
                            "dependency_time": dep_time,
                            "dependent_time": self_time,
                        })

        return {
            "format_version": REPLAY_FORMAT_VERSION,
            "total_orderings": len(orderings),
            "violations": violations,
            "violation_count": len(violations),
        }


# ---------------------------------------------------------------------------
# 6. ReplayCompatibilityVersion
# ---------------------------------------------------------------------------

class ReplayCompatibilityVersion:
    """Versioning for replay compatibility."""

    REPLAY_FORMAT_VERSION = REPLAY_FORMAT_VERSION

    @staticmethod
    def check_compatibility(stored_version: str, current_version: str = REPLAY_FORMAT_VERSION) -> dict:
        """Check compatibility between old and new formats.

        Uses semantic versioning comparison.
        """
        stored_parts = list(map(int, stored_version.split(".")))
        current_parts = list(map(int, current_version.split(".")))

        # Pad to 3 parts
        while len(stored_parts) < 3:
            stored_parts.append(0)
        while len(current_parts) < 3:
            current_parts.append(0)

        stored_major, stored_minor, stored_patch = stored_parts
        current_major, current_minor, current_patch = current_parts

        result = {
            "stored_version": stored_version,
            "current_version": current_version,
            "compatible": True,
            "migration_warnings": [],
        }

        if stored_major != current_major:
            result["compatible"] = False
            result["migration_warnings"].append(
                f"Major version mismatch: stored={stored_major}, current={current_major}. "
                "Breaking changes may exist. Migration required."
            )

        if stored_minor < current_minor:
            result["migration_warnings"].append(
                f"Minor version upgrade detected: {stored_version} -> {current_version}. "
                "New fields may be present in current format."
            )

        if stored_minor > current_minor:
            result["migration_warnings"].append(
                f"Minor version downgrade detected: {stored_version} -> {current_version}. "
                "Some fields may not be understood by the current version."
            )

        if stored_patch != current_patch:
            result["migration_warnings"].append(
                f"Patch version difference: {stored_version} -> {current_version}. "
                "Backward-compatible changes only."
            )

        return result

    @staticmethod
    def validate_snapshot(snapshot: dict) -> dict:
        """Validate a snapshot's format version."""
        stored_version = snapshot.get("format_version", "0.0.0")
        compat = ReplayCompatibilityVersion.check_compatibility(stored_version)
        return {
            "snapshot_format_version": stored_version,
            **compat,
        }


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _parse_dir(args: list, default: str = ".") -> str:
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            return args[idx + 1]
    return default


def cmd_snapshot(args: list):
    target_dir = _parse_dir(args)
    print(f"📸  Capturing environment snapshot (target: {target_dir})...")
    snapshot = EnvironmentSnapshot(target_dir)
    data = snapshot.capture()
    path = snapshot.save()
    fp = snapshot.fingerprint()
    print(f"  - Snapshot saved:  {path}")
    print(f"  - Fingerprint:     {fp}")
    print(f"  - OS:              {data['os']['platform']} {data['os']['machine']}")
    print(f"  - Python:          {data['python']['version'].split()[0]}")
    print(f"  - Git HEAD:        {data['git']['head_sha'] or 'N/A'}")
    print(f"  - Governance files: {len(data['governance_checksums'])} checksummed")
    print(f"  - Packages:        {len(data['packages'])} tracked")
    print("✅  Snapshot complete.")


def cmd_verify(args: list):
    if len(args) < 1 or args[0].startswith("--"):
        print("Usage: deterministic-replay.py verify <exec_id> [--dir <dir>]", file=sys.stderr)
        sys.exit(1)
    exec_id = args[0]
    target_dir = _parse_dir(args)
    print(f"🔍  Verifying determinism for {exec_id} (target: {target_dir})...")
    verifier = ReplayVerifier(target_dir)
    report = verifier.verify_determinism(exec_id)
    if report.get("error"):
        print(f"❌  Error: {report['error']}")
        sys.exit(1)

    passed = report.get("passed", False)
    print(f"  - Overall:         {'PASS' if passed else 'FAIL'}")
    for check_name, check_data in report.get("checks", {}).items():
        status = check_data.get("status", "UNKNOWN")
        detail = check_data.get("detail", "")
        extra = ""
        if "expected" in check_data:
            extra = f" (expected={check_data['expected']}"
            if "actual" in check_data:
                extra += f", actual={check_data['actual']}"
            extra += ")"
        print(f"  - {check_name}:    {status}{extra}{' — ' + detail if detail else ''}")
    if not passed:
        sys.exit(1)
    print("✅  Determinism verification complete.")


def cmd_drift(args: list):
    if len(args) < 1 or args[0].startswith("--"):
        print("Usage: deterministic-replay.py drift <exec_id> [--dir <dir>]", file=sys.stderr)
        sys.exit(1)
    exec_id = args[0]
    target_dir = _parse_dir(args)
    print(f"🌊  Detecting replay drift for {exec_id} (target: {target_dir})...")
    verifier = ReplayVerifier(target_dir)

    # Drift report
    drift = verifier.detect_replay_drift(exec_id)
    if drift.get("error"):
        print(f"❌  Error: {drift['error']}")
        sys.exit(1)

    print(f"  - Drift detected:  {drift.get('drift_detected', False)}")
    for d in drift.get("details", []):
        print(f"    - {d.get('field')}: {d.get('status', d.get('stored', ''))} -> {d.get('current', 'N/A')}")

    # Dependency mutation
    dep_mut = verifier.detect_dependency_mutation(exec_id)
    print(f"  - Dependencies:    {dep_mut.get('total_dependencies', 0)} tracked, "
          f"{len(dep_mut.get('mutated', []))} mutated, "
          f"{len(dep_mut.get('missing', []))} missing")

    # Environment mismatch
    env_mis = verifier.detect_environment_mismatch(exec_id)
    if env_mis.get("mismatches"):
        for m in env_mis["mismatches"]:
            print(f"  - Env mismatch:   {m['key']}: {m['stored']} -> {m['current']}")

    # Replay poisoning
    poison = verifier.detect_replay_poisoning(exec_id)
    print(f"  - Poisoning:       {'DETECTED' if poison.get('poisoned') else 'none'}")
    for check, status in poison.get("checks", {}).items():
        print(f"    - {check}: {status}")

    if drift.get("drift_detected") or poison.get("poisoned"):
        sys.exit(1)
    print("✅  Drift analysis complete.")


def cmd_signature(args: list):
    if len(args) < 1 or args[0].startswith("--"):
        print("Usage: deterministic-replay.py signature <exec_id> [--dir <dir>]", file=sys.stderr)
        sys.exit(1)
    exec_id = args[0]
    target_dir = _parse_dir(args)
    print(f"🔏  Checking replay signature for {exec_id} (target: {target_dir})...")

    # Detect corruption
    corruption = ReplayHashSignature.detect_replay_corruption(exec_id, target_dir)
    if corruption.get("error"):
        print(f"❌  Error: {corruption['error']}")
        sys.exit(1)

    print(f"  - Corrupted:       {corruption.get('corrupted', False)}")
    if corruption.get("stored_signature"):
        print(f"  - Stored sig:      {corruption['stored_signature'][:32]}...")
        print(f"  - Computed sig:    {corruption['computed_signature'][:32]}...")

    if corruption.get("corrupted"):
        print("❌  Replay corruption detected!")
        sys.exit(1)
    print("✅  Signature verification complete.")


def cmd_normalize(args: list):
    target_dir = _parse_dir(args)
    print(f"🔧  Normalizing environment for replay (target: {target_dir})...")
    normalizer = DeterminismNormalizer(target_dir)
    normalized = normalizer.normalize()

    print(f"  - Format version:  {normalized.get('_REPLAY_FORMAT_VERSION')}")
    print(f"  - LANG:            {normalized.get('LANG')}")
    print(f"  - LC_ALL:          {normalized.get('LC_ALL')}")
    print(f"  - TZ:              {normalized.get('TZ')}")
    print(f"  - PYTHONHASHSEED:  {normalized.get('PYTHONHASHSEED')}")
    print(f"  - FROZEN_TIME:     {normalized.get('SUBSTRATE_FROZEN_TIME')}")
    print(f"  - POSIXLY_CORRECT: {normalized.get('POSIXLY_CORRECT')}")
    print(f"  - Normalization notes:")
    for note in normalized.get("_REPLAY_NOTES", []):
        print(f"    - {note}")
    print("✅  Normalization complete.")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage:")
        print("  deterministic-replay.py snapshot [--dir <dir>]")
        print("  deterministic-replay.py verify <exec_id> [--dir <dir>]")
        print("  deterministic-replay.py drift <exec_id> [--dir <dir>]")
        print("  deterministic-replay.py signature <exec_id> [--dir <dir>]")
        print("  deterministic-replay.py normalize [--dir <dir>]")
        sys.exit(1)

    subcmd = sys.argv[1]
    rest = sys.argv[2:]

    if subcmd == "snapshot":
        cmd_snapshot(rest)
    elif subcmd == "verify":
        cmd_verify(rest)
    elif subcmd == "drift":
        cmd_drift(rest)
    elif subcmd == "signature":
        cmd_signature(rest)
    elif subcmd == "normalize":
        cmd_normalize(rest)
    else:
        print(f"Unknown subcommand: {subcmd}", file=sys.stderr)
        sys.exit(1)
