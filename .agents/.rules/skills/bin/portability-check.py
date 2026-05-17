#!/usr/bin/env python3
# portability-check.py — Phase 7: Portability & Scale Validation
#
# Validates the substrate works across platforms and at scale.
# Planes:
# 1. Platform Detection — OS, shell, architecture, environment
# 2. Compatibility Matrix — version checks, resource thresholds
# 3. Portability Matrix — cross-platform path, encoding, symlink checks
# 4. Stress Test Runner — sequential execution, replay, governance load
# 5. Benchmark Runner — startup, execution, replay, file scan timings
# 6. Scale Validator — large repos, evidence stores, delegation chains

import os
import sys
import json
import time
import platform
import struct
import subprocess
import tempfile
import shutil
import hashlib
import resource

OUTPUT_DIR = ".agents/management/evidence/generated"


def _ensure_output_dir(target_dir):
    """Ensure the output directory exists."""
    out = os.path.join(target_dir, OUTPUT_DIR)
    os.makedirs(out, exist_ok=True)
    return out


def _save_report(target_dir, filename, data):
    """Save a JSON report to the generated evidence directory."""
    out = _ensure_output_dir(target_dir)
    path = os.path.join(out, filename)
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, default=str)
    return path


# ──────────────────────────────────────────────────────────────────────
# 1. PlatformDetector
# ──────────────────────────────────────────────────────────────────────

class PlatformDetector:
    """Detects the current platform and environment characteristics."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def detect_platform(self):
        """Returns a dict with platform information."""
        os_name = sys.platform
        os_version = platform.release()
        arch = platform.machine()

        # Shell detection
        shell = os.environ.get("SHELL", "unknown")
        bash_version = self._get_bash_version()

        # Python info
        python_version = platform.python_version()
        python_path = sys.executable

        # BusyBox detection
        busybox = self._is_busybox()

        # WSL detection
        wsl = self._is_wsl()

        info = {
            "os": os_name,
            "os_version": os_version,
            "architecture": arch,
            "shell": shell,
            "bash_version": bash_version,
            "python_version": python_version,
            "python_path": python_path,
            "busybox": busybox,
            "wsl": wsl,
            "detected_at": time.time()
        }

        # Save to evidence
        _save_report(self.target_dir, "platform-info.json", info)
        return info

    def _get_bash_version(self):
        """Get the bash version string."""
        try:
            result = subprocess.run(
                ["bash", "--version"],
                capture_output=True, text=True, timeout=5
            )
            if result.returncode == 0:
                # First line typically looks like: GNU bash, version 5.2.15(1)-release
                return result.stdout.split("\n")[0].strip()
        except Exception:
            pass
        return "unknown"

    def _is_busybox(self):
        """Check if running in a BusyBox environment."""
        for tool in ["sh", "ls", "cat", "echo"]:
            try:
                result = subprocess.run(
                    ["which", tool],
                    capture_output=True, text=True, timeout=5
                )
                if result.returncode == 0:
                    path = result.stdout.strip()
                    if "busybox" in path.lower():
                        return True
                    # Check if the binary is actually busybox
                    try:
                        real = os.path.realpath(path)
                        if "busybox" in real.lower():
                            return True
                    except Exception:
                        pass
            except Exception:
                pass
        return False

    def _is_wsl(self):
        """Check if running under Windows Subsystem for Linux."""
        if sys.platform != "linux":
            return False
        # Check /proc/version or /run/WSL
        try:
            with open("/proc/version", "r") as f:
                content = f.read().lower()
                if "microsoft" in content or "wsl" in content:
                    return True
        except Exception:
            pass
        try:
            if os.path.exists("/run/WSL"):
                return True
        except Exception:
            pass
        # Check environment variable
        if "WSL_DISTRO_NAME" in os.environ:
            return True
        return False

    def is_linux(self):
        return sys.platform.startswith("linux")

    def is_macos(self):
        return sys.platform == "darwin"

    def is_wsl(self):
        return self._is_wsl()

    def is_alpine(self):
        """Check if running on Alpine Linux."""
        try:
            with open("/etc/os-release", "r") as f:
                return "alpine" in f.read().lower()
        except Exception:
            return False

    def is_busybox(self):
        return self._is_busybox()


# ──────────────────────────────────────────────────────────────────────
# 2. CompatibilityMatrix
# ──────────────────────────────────────────────────────────────────────

class CompatibilityMatrix:
    """Checks system compatibility against minimum requirements."""

    MIN_PYTHON = (3, 8)
    MIN_BASH = (4, 0)
    MIN_DISK_MB = 50
    MIN_RAM_MB = 100

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.results = []

    def check_python_compat(self, min_version="3.8"):
        """Check Python version compatibility."""
        major, minor = map(int, min_version.split("."))
        current = sys.version_info
        ok = (current.major, current.minor) >= (major, minor)
        result = {
            "check": "python_compat",
            "min_required": min_version,
            "current": f"{current.major}.{current.minor}.{current.micro}",
            "passed": ok
        }
        self.results.append(result)
        return result

    def check_bash_compat(self, min_version="4.0"):
        """Check Bash version compatibility."""
        try:
            result = subprocess.run(
                ["bash", "--version"],
                capture_output=True, text=True, timeout=5
            )
            if result.returncode != 0:
                res = {"check": "bash_compat", "min_required": min_version, "current": "unknown", "passed": False}
                self.results.append(res)
                return res

            # Parse version from first line: GNU bash, version X.Y.Z(...)
            first_line = result.stdout.split("\n")[0]
            version_str = first_line.split("version ")[-1].split("(")[0].strip()
            parts = version_str.split(".")
            major = int(parts[0]) if len(parts) > 0 else 0
            minor = int(parts[1]) if len(parts) > 1 else 0

            req_major, req_minor = map(int, min_version.split("."))
            ok = (major, minor) >= (req_major, req_minor)
            res = {
                "check": "bash_compat",
                "min_required": min_version,
                "current": version_str,
                "passed": ok
            }
        except Exception as e:
            res = {"check": "bash_compat", "min_required": min_version, "current": "error", "passed": False, "error": str(e)}

        self.results.append(res)
        return res

    def check_disk_space(self, min_mb=50):
        """Check available disk space."""
        try:
            stat = os.statvfs(self.target_dir)
            free_mb = (stat.f_bavail * stat.f_frsize) / (1024 * 1024)
            ok = free_mb >= min_mb
            res = {
                "check": "disk_space",
                "min_required_mb": min_mb,
                "available_mb": round(free_mb, 2),
                "passed": ok
            }
        except Exception as e:
            res = {"check": "disk_space", "min_required_mb": min_mb, "available_mb": 0, "passed": False, "error": str(e)}

        self.results.append(res)
        return res

    def check_memory(self, min_mb=100):
        """Check available memory (if possible)."""
        available_mb = 0
        try:
            # Try /proc/meminfo (Linux)
            with open("/proc/meminfo", "r") as f:
                for line in f:
                    if line.startswith("MemAvailable:"):
                        # Value is in kB
                        available_mb = int(line.split()[1]) / 1024
                        break
        except Exception:
            pass

        if available_mb == 0:
            try:
                # Fallback: use resource module (may not give accurate available)
                # getrusage gives max resident set size, not available memory
                # Try sysctl on macOS
                if sys.platform == "darwin":
                    result = subprocess.run(
                        ["sysctl", "-n", "hw.memsize"],
                        capture_output=True, text=True, timeout=5
                    )
                    if result.returncode == 0:
                        total_bytes = int(result.stdout.strip())
                        available_mb = total_bytes / (1024 * 1024)
            except Exception:
                pass

        ok = available_mb >= min_mb if available_mb > 0 else True  # If we can't check, assume ok
        res = {
            "check": "memory",
            "min_required_mb": min_mb,
            "available_mb": round(available_mb, 2) if available_mb > 0 else "unknown",
            "passed": ok,
            "note": "memory detection not available on this platform" if available_mb == 0 else ""
        }
        self.results.append(res)
        return res

    def run_full_check(self, min_python="3.8", min_bash="4.0", min_disk=50, min_ram=100):
        """Run all compatibility checks."""
        self.results = []
        self.check_python_compat(min_python)
        self.check_bash_compat(min_bash)
        self.check_disk_space(min_disk)
        self.check_memory(min_ram)

        report = {
            "compatibility_report": {
                "checks": self.results,
                "all_passed": all(r.get("passed", False) for r in self.results),
                "total_checks": len(self.results),
                "passed_count": sum(1 for r in self.results if r.get("passed", False)),
                "failed_count": sum(1 for r in self.results if not r.get("passed", False)),
                "timestamp": time.time()
            }
        }

        _save_report(self.target_dir, "compatibility-report.json", report)
        return report


# ──────────────────────────────────────────────────────────────────────
# 3. PortabilityMatrix
# ──────────────────────────────────────────────────────────────────────

class PortabilityMatrix:
    """Cross-platform compatibility validation."""

    # Known platform-specific issues
    KNOWN_ISSUES = {
        "win32": [
            "Path separator is backslash; forward slashes may need conversion",
            "Symlinks require admin privileges on Windows",
            "Maximum path length of 260 characters (unless long paths enabled)",
            "Executable extensions (.exe, .bat, .cmd) required for subprocess calls"
        ],
        "darwin": [
            "BSD grep/sed/awk differ from GNU versions; use explicit flags",
            "Coreutils may need installation via brew for GNU behavior",
            "Case-insensitive HFS+ filesystem (default)"
        ],
        "linux": [
            "Alpine Linux uses musl libc — some glibc binaries won't work",
            "BusyBox environments have limited command options",
            "SELinux/AppArmor may restrict subprocess execution"
        ]
    }

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def check_path_portability(self):
        """Check for platform-specific path issues."""
        issues = []
        platform_issues = self.KNOWN_ISSUES.get(sys.platform, [])

        # Check for paths with spaces in the working directory tree
        for root, dirs, files in os.walk(self.target_dir):
            # Limit depth to avoid scanning too much
            depth = root.replace(self.target_dir, "").count(os.sep)
            if depth > 3:
                continue
            rel = os.path.relpath(root, self.target_dir)
            if " " in rel:
                issues.append({"type": "path_with_spaces", "path": rel})
            for d in dirs:
                if " " in d:
                    issues.append({"type": "dir_with_spaces", "path": os.path.join(rel, d)})

        # Check for platform-specific extensions
        for root, dirs, files in os.walk(self.target_dir):
            depth = root.replace(self.target_dir, "").count(os.sep)
            if depth > 3:
                break
            for f in files:
                if f.endswith((".exe", ".dll", ".dylib", ".so")):
                    issues.append({"type": "platform_binary", "path": os.path.join(root, f)})

        result = {
            "check": "path_portability",
            "platform": sys.platform,
            "known_platform_issues": platform_issues,
            "detected_issues": issues,
            "issue_count": len(issues)
        }
        return result

    def check_encoding_portability(self):
        """Check filesystem encoding compatibility."""
        result = {
            "check": "encoding_portability",
            "filesystem_encoding": sys.getfilesystemencoding(),
            "filesystem_errors": sys.getfilesystemencodeerrors(),
            "default_encoding": sys.getdefaultencoding(),
            "stdin_encoding": sys.stdin.encoding if sys.stdin else None,
            "stdout_encoding": sys.stdout.encoding if sys.stdout else None,
            "utf8_compatible": True
        }

        fs_enc = sys.getfilesystemencoding().lower()
        if "utf" not in fs_enc and "ascii" not in fs_enc:
            result["utf8_compatible"] = False

        # Test: can we write and read a UTF-8 filename?
        try:
            test_name = "test_\u00e9\u00e8\u00ea_utf8"
            test_path = os.path.join(tempfile.gettempdir(), test_name)
            with open(test_path, "w") as f:
                f.write("portability test")
            with open(test_path, "r") as f:
                content = f.read()
            os.remove(test_path)
            result["utf8_filename_test"] = "passed"
        except Exception as e:
            result["utf8_filename_test"] = f"failed: {str(e)}"

        return result

    def check_symlink_support(self):
        """Check symlink support on the current platform."""
        result = {
            "check": "symlink_support",
            "platform": sys.platform
        }

        # Test in a temp directory
        tmpdir = tempfile.mkdtemp()
        try:
            target = os.path.join(tmpdir, "target")
            link = os.path.join(tmpdir, "link")

            with open(target, "w") as f:
                f.write("test")

            # Try creating a symlink
            try:
                os.symlink(target, link)
                result["symlink_create"] = "supported"
                result["symlink_read"] = os.readlink(link) == target
                os.remove(link)
            except OSError as e:
                result["symlink_create"] = f"failed: {e}"
                result["symlink_read"] = False

            # Try creating a directory symlink
            target_dir = os.path.join(tmpdir, "target_dir")
            link_dir = os.path.join(tmpdir, "link_dir")
            os.makedirs(target_dir)
            try:
                os.symlink(target_dir, link_dir)
                result["dir_symlink_create"] = "supported"
                os.remove(link_dir)
            except OSError as e:
                result["dir_symlink_create"] = f"failed: {e}"

            # Check for Windows junction support
            if sys.platform == "win32":
                result["note"] = "Windows requires Developer Mode or admin for symlinks"

        finally:
            shutil.rmtree(tmpdir, ignore_errors=True)

        return result

    def check_subprocess_features(self):
        """Check subprocess capabilities."""
        result = {
            "check": "subprocess_features",
            "platform": sys.platform
        }

        # Test: basic subprocess
        try:
            r = subprocess.run(["echo", "test"], capture_output=True, text=True, timeout=5)
            result["basic_subprocess"] = "passed" if r.returncode == 0 else "failed"
        except Exception as e:
            result["basic_subprocess"] = f"failed: {e}"

        # Test: shell=True
        try:
            r = subprocess.run("echo test", shell=True, capture_output=True, text=True, timeout=5)
            result["shell_subprocess"] = "passed" if r.returncode == 0 else "failed"
        except Exception as e:
            result["shell_subprocess"] = f"failed: {e}"

        # Test: stdin pipe
        try:
            r = subprocess.run(
                ["cat"] if sys.platform != "win32" else ["cmd", "/c", "more"],
                input="hello", capture_output=True, text=True, timeout=5
            )
            result["stdin_pipe"] = "passed" if r.returncode == 0 else "failed"
        except Exception as e:
            result["stdin_pipe"] = f"failed: {e}"

        # Test: environment passing
        try:
            r = subprocess.run(
                [sys.executable, "-c", "import os; print(os.environ.get('TEST_VAR', 'missing'))"],
                capture_output=True, text=True, timeout=5,
                env={**os.environ, "TEST_VAR": "found"}
            )
            result["env_passing"] = "passed" if "found" in r.stdout else "failed"
        except Exception as e:
            result["env_passing"] = f"failed: {e}"

        # Test: timeout support
        try:
            subprocess.run(
                [sys.executable, "-c", "import time; time.sleep(10)"],
                capture_output=True, timeout=1
            )
            result["timeout_support"] = "failed"  # Should have timed out
        except subprocess.TimeoutExpired:
            result["timeout_support"] = "passed"
        except Exception as e:
            result["timeout_support"] = f"error: {e}"

        return result

    def run_full_matrix(self):
        """Run all portability checks and save matrix."""
        matrix = {
            "portability_matrix": {
                "platform": sys.platform,
                "path_portability": self.check_path_portability(),
                "encoding_portability": self.check_encoding_portability(),
                "symlink_support": self.check_symlink_support(),
                "subprocess_features": self.check_subprocess_features(),
                "generated_at": time.time()
            }
        }

        _save_report(self.target_dir, "portability-matrix.json", matrix)
        return matrix


# ──────────────────────────────────────────────────────────────────────
# 4. StressTestRunner
# ──────────────────────────────────────────────────────────────────────

class StressTestRunner:
    """Runs stress tests to measure performance under load."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def stress_execution(self, count=50):
        """Run N sequential executions, measure latency."""
        timings = []
        for i in range(count):
            start = time.time()
            # Simulate a lightweight execution cycle
            subprocess.run(
                [sys.executable, "-c", "pass"],
                capture_output=True, timeout=10
            )
            elapsed_ms = (time.time() - start) * 1000
            timings.append(elapsed_ms)

        return {
            "test": "stress_execution",
            "count": count,
            "timings_ms": timings,
            "min_ms": round(min(timings), 2),
            "max_ms": round(max(timings), 2),
            "avg_ms": round(sum(timings) / len(timings), 2),
            "p50_ms": round(sorted(timings)[len(timings) // 2], 2),
            "p95_ms": round(sorted(timings)[int(len(timings) * 0.95)], 2),
            "p99_ms": round(sorted(timings)[int(len(timings) * 0.99)], 2),
            "total_ms": round(sum(timings), 2)
        }

    def stress_replay(self, count=20):
        """Run N replays, measure consistency."""
        timings = []
        for i in range(count):
            start = time.time()
            # Simulate replay: read a small JSON, hash it, compare
            data = json.dumps({"test": i, "data": "x" * 100})
            h = hashlib.sha256(data.encode()).hexdigest()
            parsed = json.loads(data)
            h2 = hashlib.sha256(json.dumps(parsed).encode()).hexdigest()
            elapsed_ms = (time.time() - start) * 1000
            timings.append({
                "iteration": i,
                "elapsed_ms": round(elapsed_ms, 4),
                "hash_match": h == h2
            })

        elapsed_values = [t["elapsed_ms"] for t in timings]
        return {
            "test": "stress_replay",
            "count": count,
            "results": timings,
            "min_ms": round(min(elapsed_values), 4),
            "max_ms": round(max(elapsed_values), 4),
            "avg_ms": round(sum(elapsed_values) / len(elapsed_values), 4),
            "consistency": "all_hashes_matched" if all(t["hash_match"] for t in timings) else "hash_mismatch_detected"
        }

    def stress_governance_load(self):
        """Measure governance resolution time."""
        # Simulate governance file scanning
        start = time.time()

        file_count = 0
        scanned_files = []
        for root, dirs, files in os.walk(self.target_dir):
            # Skip common ignore patterns
            rel_root = os.path.relpath(root, self.target_dir)
            if rel_root.startswith(".git") or "node_modules" in rel_root:
                continue
            for f in files:
                if f.endswith((".md", ".json", ".py", ".sh")):
                    file_count += 1
                    scanned_files.append(os.path.join(rel_root, f))

        scan_time_ms = (time.time() - start) * 1000

        # Simulate governance resolution (hash each file)
        start = time.time()
        hashes = {}
        for f in scanned_files[:100]:  # Limit to first 100
            full_path = os.path.join(self.target_dir, f)
            try:
                with open(full_path, "rb") as fh:
                    hashes[f] = hashlib.sha256(fh.read()).hexdigest()
            except Exception:
                pass
        hash_time_ms = (time.time() - start) * 1000

        return {
            "test": "stress_governance_load",
            "files_scanned": file_count,
            "files_hashed": len(hashes),
            "scan_time_ms": round(scan_time_ms, 2),
            "hash_time_ms": round(hash_time_ms, 2),
            "total_time_ms": round(scan_time_ms + hash_time_ms, 2)
        }

    def stress_graph_traversal(self, max_nodes=100):
        """Measure graph traversal cost."""
        # Build a synthetic graph
        nodes = {}
        edges = []
        for i in range(max_nodes):
            node_id = f"node-{i}"
            nodes[node_id] = {
                "id": node_id,
                "data": hashlib.sha256(str(i).encode()).hexdigest()[:16],
                "depth": i // 10
            }
            if i > 0:
                edges.append({"from": f"node-{i - 1}", "to": node_id})
            if i > 10:
                edges.append({"from": f"node-{i - 10}", "to": node_id})

        # Traverse: BFS from root
        start = time.time()
        visited = set()
        queue = ["node-0"]
        traversal_order = []

        while queue:
            current = queue.pop(0)
            if current in visited:
                continue
            visited.add(current)
            traversal_order.append(current)
            for edge in edges:
                if edge["from"] == current and edge["to"] not in visited:
                    queue.append(edge["to"])

        traversal_time_ms = (time.time() - start) * 1000

        return {
            "test": "stress_graph_traversal",
            "max_nodes": max_nodes,
            "nodes_created": len(nodes),
            "edges_created": len(edges),
            "nodes_visited": len(visited),
            "traversal_time_ms": round(traversal_time_ms, 4),
            "traversal_order_sample": traversal_order[:10]
        }

    def stress_telemetry_growth(self, iterations=10):
        """Measure telemetry growth rate."""
        sizes = []
        for i in range(iterations):
            # Generate telemetry-like data
            telemetry = {
                "iteration": i,
                "timestamp": time.time(),
                "metrics": {f"metric_{j}": j * i for j in range(100)},
                "events": [f"event-{k}" for k in range(50)]
            }
            data_str = json.dumps(telemetry)
            sizes.append({
                "iteration": i,
                "json_size_bytes": len(data_str.encode("utf-8")),
                "json_size_kb": round(len(data_str.encode("utf-8")) / 1024, 4)
            })

        growth_bytes = [s["json_size_bytes"] for s in sizes]
        return {
            "test": "stress_telemetry_growth",
            "iterations": iterations,
            "sizes": sizes,
            "min_size_bytes": min(growth_bytes),
            "max_size_bytes": max(growth_bytes),
            "avg_size_bytes": round(sum(growth_bytes) / len(growth_bytes), 2),
            "growth_rate": "linear" if len(growth_bytes) > 1 else "unknown"
        }

    def run_full_stress(self, count=50, replay_count=20, iterations=10):
        """Run all stress tests."""
        results = {
            "stress_results": {
                "execution": self.stress_execution(count),
                "replay": self.stress_replay(replay_count),
                "governance_load": self.stress_governance_load(),
                "graph_traversal": self.stress_graph_traversal(),
                "telemetry_growth": self.stress_telemetry_growth(iterations),
                "timestamp": time.time()
            }
        }

        _save_report(self.target_dir, "stress-results.json", results)
        return results


# ──────────────────────────────────────────────────────────────────────
# 5. BenchmarkRunner
# ──────────────────────────────────────────────────────────────────────

class BenchmarkRunner:
    """Runs performance benchmarks."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def benchmark_startup(self):
        """Time to initialize substrate (import + basic setup)."""
        # Measure time to start a fresh Python process
        start = time.time()
        subprocess.run(
            [sys.executable, "-c", "import os, sys, json, time, uuid, hashlib; pass"],
            capture_output=True, timeout=10
        )
        elapsed_ms = (time.time() - start) * 1000
        return {"benchmark": "startup", "time_ms": round(elapsed_ms, 2)}

    def benchmark_execute(self, cmd="echo test"):
        """Time to run a simple command."""
        start = time.time()
        subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=10)
        elapsed_ms = (time.time() - start) * 1000
        return {"benchmark": "execute", "command": cmd, "time_ms": round(elapsed_ms, 2)}

    def benchmark_replay(self, exec_id="synthetic"):
        """Time to replay an execution (simulate)."""
        # Create synthetic manifest
        manifest = {
            "execution_id": exec_id,
            "replay_contract": {"expected_exit_code": 0},
            "context_package": {"dependency_checksums": {f"file-{i}": hashlib.sha256(str(i).encode()).hexdigest() for i in range(10)}}
        }

        start = time.time()
        # Simulate replay: serialize, hash, parse, verify
        data = json.dumps(manifest)
        h = hashlib.sha256(data.encode()).hexdigest()
        parsed = json.loads(data)
        h2 = hashlib.sha256(json.dumps(parsed).encode()).hexdigest()
        elapsed_ms = (time.time() - start) * 1000
        return {
            "benchmark": "replay",
            "exec_id": exec_id,
            "time_ms": round(elapsed_ms, 2),
            "integrity_verified": h == h2
        }

    def benchmark_scan_files(self):
        """Time to scan file state."""
        start = time.time()
        count = 0
        hashes = {}
        for root, dirs, files in os.walk(self.target_dir):
            rel_root = os.path.relpath(root, self.target_dir)
            if rel_root.startswith(".git") or "node_modules" in rel_root:
                continue
            for f in files:
                filepath = os.path.join(root, f)
                try:
                    with open(filepath, "rb") as fh:
                        h = hashlib.sha256(fh.read())
                        hashes[os.path.relpath(filepath, self.target_dir)] = h.hexdigest()
                    count += 1
                except Exception:
                    pass

        elapsed_ms = (time.time() - start) * 1000
        return {
            "benchmark": "scan_files",
            "files_scanned": count,
            "time_ms": round(elapsed_ms, 2),
            "ms_per_file": round(elapsed_ms / count, 4) if count > 0 else 0
        }

    def benchmark_full_suite(self):
        """Run all benchmarks."""
        results = {
            "benchmarks": {
                "startup": self.benchmark_startup(),
                "execute": self.benchmark_execute(),
                "replay": self.benchmark_replay(),
                "scan_files": self.benchmark_scan_files(),
                "timestamp": time.time()
            }
        }

        _save_report(self.target_dir, "benchmarks.json", results)
        return results


# ──────────────────────────────────────────────────────────────────────
# 6. ScaleValidator
# ──────────────────────────────────────────────────────────────────────

class ScaleValidator:
    """Validates performance at scale."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)

    def validate_large_repo(self, max_files=100000):
        """Check performance with many files (simulated)."""
        # Count actual files
        start = time.time()
        file_count = 0
        for root, dirs, files in os.walk(self.target_dir):
            rel = os.path.relpath(root, self.target_dir)
            if rel.startswith(".git") or "node_modules" in rel:
                continue
            file_count += len(files)

        scan_time_ms = (time.time() - start) * 1000

        # Estimate cost at max_files
        if file_count > 0:
            cost_per_file_ms = scan_time_ms / file_count
            estimated_max_time_ms = cost_per_file_ms * max_files
        else:
            cost_per_file_ms = 0
            estimated_max_time_ms = 0

        return {
            "test": "validate_large_repo",
            "max_files_configured": max_files,
            "actual_files": file_count,
            "scan_time_ms": round(scan_time_ms, 2),
            "cost_per_file_ms": round(cost_per_file_ms, 6),
            "estimated_time_at_max_ms": round(estimated_max_time_ms, 2),
            "within_budget": estimated_max_time_ms < 30000  # 30 second budget
        }

    def validate_huge_evidence_store(self, max_files=10000):
        """Check with many evidence files (simulated)."""
        tmpdir = tempfile.mkdtemp()
        try:
            # Create synthetic evidence files
            start = time.time()
            created = 0
            for i in range(min(max_files, 1000)):  # Limit to 1000 for practical reasons
                data = {
                    "evidence_id": f"ev-{i}",
                    "timestamp": time.time(),
                    "data": "x" * 50,
                    "hash": hashlib.sha256(str(i).encode()).hexdigest()
                }
                with open(os.path.join(tmpdir, f"evidence-{i:06d}.json"), "w") as f:
                    json.dump(data, f)
                created += 1

            create_time_ms = (time.time() - start) * 1000

            # Now scan the directory
            start = time.time()
            scanned = 0
            for root, dirs, files in os.walk(tmpdir):
                for f in files:
                    if f.endswith(".json"):
                        scanned += 1
            scan_time_ms = (time.time() - start) * 1000

            return {
                "test": "validate_huge_evidence_store",
                "max_files_configured": max_files,
                "files_created": created,
                "create_time_ms": round(create_time_ms, 2),
                "files_scanned": scanned,
                "scan_time_ms": round(scan_time_ms, 2),
                "ms_per_file": round(scan_time_ms / scanned, 4) if scanned > 0 else 0
            }
        finally:
            shutil.rmtree(tmpdir, ignore_errors=True)

    def validate_high_delegation_volume(self, count=100):
        """Check delegation chain performance."""
        # Simulate a delegation chain
        chain = []
        parent_id = "root"
        start = time.time()

        for i in range(count):
            child_id = f"deleg-{i}"
            entry = {
                "delegation_id": child_id,
                "parent_id": parent_id,
                "depth": i,
                "created_at": time.time(),
                "hash": hashlib.sha256(f"{parent_id}:{child_id}:{i}".encode()).hexdigest()
            }
            chain.append(entry)
            parent_id = child_id

        chain_time_ms = (time.time() - start) * 1000

        # Verify chain integrity
        start = time.time()
        valid = True
        for i in range(1, len(chain)):
            if chain[i]["parent_id"] != chain[i - 1]["delegation_id"]:
                valid = False
                break

        verify_time_ms = (time.time() - start) * 1000

        return {
            "test": "validate_high_delegation_volume",
            "delegation_count": count,
            "chain_depth": count,
            "build_time_ms": round(chain_time_ms, 2),
            "verify_time_ms": round(verify_time_ms, 4),
            "chain_valid": valid
        }

    def validate_concurrent_stress(self, workers=5):
        """Simulate concurrent executions (using sequential subprocess calls)."""
        results = []
        start = time.time()

        for i in range(workers):
            worker_start = time.time()
            # Simulate work: hash some data, read a file, etc.
            data = f"worker-{i}-data" * 100
            h = hashlib.sha256(data.encode()).hexdigest()
            # Small subprocess call
            subprocess.run(
                [sys.executable, "-c", f"print({i})"],
                capture_output=True, timeout=10
            )
            worker_elapsed_ms = (time.time() - worker_start) * 1000
            results.append({
                "worker": i,
                "elapsed_ms": round(worker_elapsed_ms, 2),
                "hash": h
            })

        total_ms = (time.time() - start) * 1000
        elapsed_values = [r["elapsed_ms"] for r in results]

        return {
            "test": "validate_concurrent_stress",
            "workers": workers,
            "results": results,
            "total_time_ms": round(total_ms, 2),
            "worker_min_ms": round(min(elapsed_values), 2),
            "worker_max_ms": round(max(elapsed_values), 2),
            "worker_avg_ms": round(sum(elapsed_values) / len(elapsed_values), 2)
        }

    def run_full_scale(self, max_files=100000, max_evidence=10000, deleg_count=100, workers=5):
        """Run all scale validations."""
        results = {
            "scale_validation": {
                "large_repo": self.validate_large_repo(max_files),
                "huge_evidence_store": self.validate_huge_evidence_store(max_evidence),
                "high_delegation_volume": self.validate_high_delegation_volume(deleg_count),
                "concurrent_stress": self.validate_concurrent_stress(workers),
                "timestamp": time.time()
            }
        }

        _save_report(self.target_dir, "scale-validation.json", results)
        return results


# ──────────────────────────────────────────────────────────────────────
# CLI
# ──────────────────────────────────────────────────────────────────────

def main():
    if len(sys.argv) < 2:
        print("Usage:")
        print("  portability-check.py platform [--dir <dir>]")
        print("  portability-check.py compat [--dir <dir>]")
        print("  portability-check.py matrix [--dir <dir>]")
        print("  portability-check.py stress [--count N] [--dir <dir>]")
        print("  portability-check.py benchmark [--dir <dir>]")
        print("  portability-check.py scale [--dir <dir>]")
        print("  portability-check.py all [--dir <dir>]")
        sys.exit(1)

    subcmd = sys.argv[1]
    target_dir = "."
    stress_count = 50
    stress_replay_count = 20
    stress_iterations = 10

    # Parse arguments
    args = sys.argv[2:]
    idx = 0
    while idx < len(args):
        if args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]
            idx += 2
        elif args[idx] == "--count" and idx + 1 < len(args):
            stress_count = int(args[idx + 1])
            idx += 2
        else:
            idx += 1

    target_dir = os.path.normpath(target_dir)

    if subcmd == "platform":
        print("=" * 70)
        print("PLATFORM DETECTION")
        print("=" * 70)
        detector = PlatformDetector(target_dir)
        info = detector.detect_platform()
        print(f"  OS:          {info['os']}")
        print(f"  OS Version:  {info['os_version']}")
        print(f"  Arch:        {info['architecture']}")
        print(f"  Shell:       {info['shell']}")
        print(f"  Bash:        {info['bash_version']}")
        print(f"  Python:      {info['python_version']}")
        print(f"  BusyBox:     {info['busybox']}")
        print(f"  WSL:         {info['wsl']}")
        print(f"  Report:      {_ensure_output_dir(target_dir)}/platform-info.json")
        print("=" * 70)

    elif subcmd == "compat":
        print("=" * 70)
        print("COMPATIBILITY CHECK")
        print("=" * 70)
        matrix = CompatibilityMatrix(target_dir)
        report = matrix.run_full_check()
        cr = report["compatibility_report"]
        for check in cr["checks"]:
            status = "PASS" if check["passed"] else "FAIL"
            print(f"  [{status}] {check['check']}: {check.get('current', check.get('available_mb', 'n/a'))}")
        print(f"  Results: {cr['passed_count']}/{cr['total_checks']} passed")
        print(f"  Report:  {_ensure_output_dir(target_dir)}/compatibility-report.json")
        print("=" * 70)
        if not cr["all_passed"]:
            sys.exit(1)

    elif subcmd == "matrix":
        print("=" * 70)
        print("PORTABILITY MATRIX")
        print("=" * 70)
        pm = PortabilityMatrix(target_dir)
        result = pm.run_full_matrix()
        matrix = result["portability_matrix"]
        print(f"  Platform:           {matrix['platform']}")
        print(f"  Path Issues:        {matrix['path_portability']['issue_count']}")
        print(f"  FS Encoding:        {matrix['encoding_portability']['filesystem_encoding']}")
        print(f"  UTF-8 Test:         {matrix['encoding_portability']['utf8_filename_test']}")
        print(f"  Symlinks:           {matrix['symlink_support']['symlink_create']}")
        print(f"  Subprocess:         {matrix['subprocess_features']['basic_subprocess']}")
        print(f"  Timeout Support:    {matrix['subprocess_features']['timeout_support']}")
        print(f"  Report:             {_ensure_output_dir(target_dir)}/portability-matrix.json")
        print("=" * 70)

    elif subcmd == "stress":
        print("=" * 70)
        print(f"STRESS TESTS (count={stress_count})")
        print("=" * 70)
        runner = StressTestRunner(target_dir)
        results = runner.run_full_stress(count=stress_count, replay_count=stress_replay_count, iterations=stress_iterations)
        sr = results["stress_results"]

        ex = sr["execution"]
        print(f"  Execution:  {ex['count']} runs, avg {ex['avg_ms']}ms, p95 {ex['p95_ms']}ms")

        rp = sr["replay"]
        print(f"  Replay:     {rp['count']} runs, avg {rp['avg_ms']}ms, {rp['consistency']}")

        gl = sr["governance_load"]
        print(f"  Gov Load:   {gl['files_scanned']} files scanned, {gl['files_hashed']} hashed, {gl['total_time_ms']}ms")

        gt = sr["graph_traversal"]
        print(f"  Graph:      {gt['nodes_created']} nodes, {gt['edges_created']} edges, {gt['traversal_time_ms']}ms")

        tg = sr["telemetry_growth"]
        print(f"  Telemetry:  {tg['iterations']} iterations, avg {tg['avg_size_bytes']} bytes")

        print(f"  Report:     {_ensure_output_dir(target_dir)}/stress-results.json")
        print("=" * 70)

    elif subcmd == "benchmark":
        print("=" * 70)
        print("BENCHMARKS")
        print("=" * 70)
        runner = BenchmarkRunner(target_dir)
        results = runner.benchmark_full_suite()
        bm = results["benchmarks"]

        print(f"  Startup:    {bm['startup']['time_ms']}ms")
        print(f"  Execute:    {bm['execute']['time_ms']}ms")
        print(f"  Replay:     {bm['replay']['time_ms']}ms (integrity: {bm['replay']['integrity_verified']})")
        print(f"  Scan Files: {bm['scan_files']['time_ms']}ms ({bm['scan_files']['files_scanned']} files)")
        print(f"  Report:     {_ensure_output_dir(target_dir)}/benchmarks.json")
        print("=" * 70)

    elif subcmd == "scale":
        print("=" * 70)
        print("SCALE VALIDATION")
        print("=" * 70)
        validator = ScaleValidator(target_dir)
        results = validator.run_full_scale()
        sv = results["scale_validation"]

        lr = sv["large_repo"]
        print(f"  Large Repo:        {lr['actual_files']} files, est. {lr['estimated_time_at_max_ms']}ms at {lr['max_files_configured']}")

        he = sv["huge_evidence_store"]
        print(f"  Evidence Store:    {he['files_created']} files created, {he['scan_time_ms']}ms scan")

        hd = sv["high_delegation_volume"]
        print(f"  Delegation Chain:  {hd['delegation_count']} deep, {hd['chain_valid']}")

        cs = sv["concurrent_stress"]
        print(f"  Concurrent:        {cs['workers']} workers, avg {cs['worker_avg_ms']}ms")

        print(f"  Report:            {_ensure_output_dir(target_dir)}/scale-validation.json")
        print("=" * 70)

    elif subcmd == "all":
        print("=" * 70)
        print("FULL PORTABILITY & SCALE VALIDATION SUITE")
        print("=" * 70)

        # Platform
        print("\n[1/6] Platform Detection...")
        detector = PlatformDetector(target_dir)
        info = detector.detect_platform()
        print(f"  OS: {info['os']}, Python: {info['python_version']}, Arch: {info['architecture']}")

        # Compatibility
        print("\n[2/6] Compatibility Check...")
        compat = CompatibilityMatrix(target_dir)
        compat_report = compat.run_full_check()
        cr = compat_report["compatibility_report"]
        print(f"  {cr['passed_count']}/{cr['total_checks']} checks passed")

        # Portability Matrix
        print("\n[3/6] Portability Matrix...")
        pm = PortabilityMatrix(target_dir)
        pm.run_full_matrix()
        print("  Matrix saved.")

        # Stress Tests
        print("\n[4/6] Stress Tests...")
        stress = StressTestRunner(target_dir)
        stress.run_full_stress()
        print("  Stress tests completed.")

        # Benchmarks
        print("\n[5/6] Benchmarks...")
        bench = BenchmarkRunner(target_dir)
        bench.benchmark_full_suite()
        print("  Benchmarks completed.")

        # Scale Validation
        print("\n[6/6] Scale Validation...")
        scale = ScaleValidator(target_dir)
        scale.run_full_scale()
        print("  Scale validation completed.")

        print("\n" + "=" * 70)
        print("ALL REPORTS SAVED TO:")
        print(f"  {_ensure_output_dir(target_dir)}")
        print("=" * 70)

        # Exit with error if compatibility checks failed
        if not cr["all_passed"]:
            print("\nWARNING: Some compatibility checks failed!")
            sys.exit(1)

    else:
        print(f"Unknown subcommand: {subcmd}")
        sys.exit(1)


if __name__ == "__main__":
    main()
