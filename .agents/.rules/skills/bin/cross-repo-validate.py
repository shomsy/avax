#!/usr/bin/env python3
"""cross-repo-validate.py — Validates Agent Harness against external repositories.

Tests the harness against different repository types to prove it works
beyond the harness itself.

Usage:
  python3 cross-repo-validate.py run [--repos <dir1,dir2,...>]
  python3 cross-repo-validate.py quick [--dir <repo_path>]

Quick test:
  Validates a single repository against harness compatibility.

Multi-repo test:
  Validates multiple repositories and reports aggregate results.
"""

import os
import sys
import json
import time
import shutil
import tempfile
import subprocess

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _target_dir(path=None):
    return os.path.normpath(path) if path else "."


def _fmt_score(score):
    """Format compatibility score dict as 'X/Y' string."""
    if isinstance(score, dict):
        return f"{score['score']}/{score['max_score']}"
    return str(score)


def _run(cmd, cwd=None, timeout=30):
    """Run a command and return (returncode, stdout, stderr)."""
    try:
        result = subprocess.run(
            cmd,
            cwd=cwd,
            capture_output=True,
            text=True,
            timeout=timeout,
        )
        return result.returncode, result.stdout.strip(), result.stderr.strip()
    except subprocess.TimeoutExpired:
        return -1, "", "timeout"
    except Exception as e:
        return -1, "", str(e)


# ---------------------------------------------------------------------------
# Quick Validation — single repo
# ---------------------------------------------------------------------------

def validate_single_repo(repo_path, harness_root=None):
    """Validate a single repository against Agent Harness compatibility."""
    repo = _target_dir(repo_path)
    harness = _target_dir(harness_root) or os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

    results = {}

    # 1. Repository exists and is a git repo
    is_git = os.path.exists(os.path.join(repo, ".git"))
    results["is_git_repo"] = is_git

    # 2. Has source files / 6. File count and size — single os.walk pass
    source_extensions = {".py", ".ts", ".tsx", ".js", ".jsx", ".php", ".go", ".rs", ".java", ".rb", ".cs", ".c", ".cpp", ".h"}
    source_files = []
    total_files = 0
    total_size = 0
    skip_dirs = ("node_modules", "vendor", "build", "dist", "target")
    for root, dirs, files in os.walk(repo):
        dirs[:] = [d for d in dirs if not d.startswith(".") and d not in skip_dirs]
        for f in files:
            total_files += 1
            try:
                total_size += os.path.getsize(os.path.join(root, f))
            except OSError:
                pass
            if os.path.splitext(f)[1] in source_extensions:
                source_files.append(f)
    results["source_files"] = len(source_files)
    results["has_source_code"] = len(source_files) > 0

    # 3. Language detection
    lang_counts = {}
    for f in source_files:
        ext = os.path.splitext(f)[1]
        lang_counts[ext] = lang_counts.get(ext, 0) + 1
    results["languages"] = lang_counts

    # 4. Harness compatibility — can we run the diagnose tool?
    diagnose_script = os.path.join(harness, ".agents/skills/bin/agent-harness-diagnose.py")
    if os.path.exists(diagnose_script):
        rc, stdout, stderr = _run([sys.executable, diagnose_script, "diagnose", "--dir", repo], timeout=30)
        results["diagnose_available"] = True
        results["diagnose_passed"] = rc == 0
        results["diagnose_detail"] = stdout[-200:] if stdout else stderr[-200:]
    else:
        results["diagnose_available"] = False
        results["diagnose_passed"] = False
        results["diagnose_detail"] = "Diagnose script not found"

    # 5. Bootstrap compatibility
    bootstrap_script = os.path.join(harness, ".agents/skills/bin/agent-harness-diagnose.py")
    if os.path.exists(bootstrap_script):
        rc, stdout, stderr = _run([sys.executable, bootstrap_script, "bootstrap", "--dir", repo], timeout=30)
        results["bootstrap_available"] = True
        results["bootstrap_passed"] = rc == 0
    else:
        results["bootstrap_available"] = False
        results["bootstrap_passed"] = False

    results["total_files"] = total_files
    results["total_size_mb"] = round(total_size / (1024 * 1024), 1)

    # 7. Check for common project files
    project_files = ["package.json", "composer.json", "Cargo.toml", "go.mod",
                     "pyproject.toml", "setup.py", "requirements.txt",
                     "pom.xml", "build.gradle", "Gemfile", "CMakeLists.txt"]
    found_project_files = [f for f in project_files if os.path.exists(os.path.join(repo, f))]
    results["project_files"] = found_project_files

    # Overall compatibility score
    score = 0
    max_score = 5
    if results.get("is_git_repo"): score += 1
    if results.get("has_source_code"): score += 1
    if results.get("diagnose_available"): score += 1
    if results.get("diagnose_passed"): score += 1
    if results.get("bootstrap_passed"): score += 1

    results["compatibility_score"] = {"score": score, "max_score": max_score}
    results["compatible"] = score >= 3  # At least 3/5 for basic compatibility

    return results


# ---------------------------------------------------------------------------
# Multi-Repo Validation
# ---------------------------------------------------------------------------

def validate_multiple_repos(repo_paths, harness_root=None):
    """Validate multiple repositories and report aggregate results."""
    print("=" * 70)
    print(" CROSS-REPOSITORY VALIDATION HARNESS")
    print("=" * 70)
    print(f"  Repositories to test: {len(repo_paths)}")
    print(f"  Harness root: {harness_root}")
    print()

    all_results = []
    for repo_path in repo_paths:
        repo_name = os.path.basename(os.path.abspath(repo_path))
        print(f"  Testing: {repo_name} ({repo_path})...")
        start = time.time()
        try:
            result = validate_single_repo(repo_path, harness_root)
            duration = (time.time() - start) * 1000
            result["repo_name"] = repo_name
            result["repo_path"] = repo_path
            result["duration_ms"] = duration
            all_results.append(result)

            score = result.get("compatibility_score", "?")
            compatible = "YES" if result.get("compatible") else "NO"
            print(f"    Score: {_fmt_score(score)} | Compatible: {compatible} | {duration:.0f}ms")
        except Exception as e:
            print(f"    ERROR: {e}")
            all_results.append({
                "repo_name": repo_name,
                "repo_path": repo_path,
                "error": str(e),
                "compatible": False,
            })
        print()

    # Summary
    print("=" * 70)
    print(" VALIDATION SUMMARY")
    print("=" * 70)

    compatible = [r for r in all_results if r.get("compatible")]
    incompatible = [r for r in all_results if not r.get("compatible")]

    print(f"  Total tested:     {len(all_results)}")
    print(f"  Compatible:       {len(compatible)}")
    print(f"  Incompatible:     {len(incompatible)}")
    print()

    if compatible:
        print("  Compatible repos:")
        for r in compatible:
            langs = ", ".join(r.get("languages", {}).keys())
            print(f"    - {r['repo_name']} ({_fmt_score(r.get('compatibility_score', '?'))}) [{langs}]")

    if incompatible:
        print("  Incompatible repos:")
        for r in incompatible:
            print(f"    - {r['repo_name']}: {r.get('error', 'failed validation')}")

    print("=" * 70)

    return len(compatible) > 0 and len(incompatible) == 0


# ---------------------------------------------------------------------------
# Demo Mode — validate against sample repos
# ---------------------------------------------------------------------------

def run_demo(harness_root=None):
    """Run demo validation against sample repository types."""
    harness = _target_dir(harness_root) or os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

    print("=" * 70)
    print(" CROSS-REPOSITORY VALIDATION — DEMO MODE")
    print("=" * 70)
    print()

    # Test against this harness itself (baseline)
    print("  [1/3] Testing harness repository (baseline)...")
    result1 = validate_single_repo(harness, harness)
    print(f"    Score: {_fmt_score(result1['compatibility_score'])} | Compatible: {result1['compatible']}")
    print(f"    Files: {result1['source_files']} source, {result1['total_files']} total")
    print(f"    Languages: {', '.join(result1.get('languages', {}).keys()) or 'none detected'}")
    print()

    # Test against a temp directory (simulating empty repo)
    print("  [2/3] Testing empty repository (edge case)...")
    tmpdir = tempfile.mkdtemp(prefix="cross-repo-test-")
    try:
        # Create a minimal .git
        os.makedirs(os.path.join(tmpdir, ".git"))
        # Create a single source file
        with open(os.path.join(tmpdir, "main.py"), "w") as f:
            f.write("print('hello')\n")
        result2 = validate_single_repo(tmpdir, harness)
        print(f"    Score: {_fmt_score(result2['compatibility_score'])} | Compatible: {result2['compatible']}")
        print(f"    Files: {result2['source_files']} source, {result2['total_files']} total")
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)
    print()

    # Test against a simulated PHP/Laravel-style repo
    print("  [3/3] Testing simulated PHP/Laravel repository...")
    tmpdir2 = tempfile.mkdtemp(prefix="cross-repo-test-laravel-")
    try:
        os.makedirs(os.path.join(tmpdir2, ".git"))
        os.makedirs(os.path.join(tmpdir2, "app/Http/Controllers"))
        os.makedirs(os.path.join(tmpdir2, "routes"))
        with open(os.path.join(tmpdir2, "composer.json"), "w") as f:
            json.dump({"require": {"laravel/framework": "^10.0"}}, f)
        with open(os.path.join(tmpdir2, "artisan"), "w") as f:
            f.write("#!/usr/bin/env php\n")
        with open(os.path.join(tmpdir2, "app/Http/Controllers/UserController.php"), "w") as f:
            f.write("<?php\nnamespace App\\Http\\Controllers;\n")
        with open(os.path.join(tmpdir2, "routes/web.php"), "w") as f:
            f.write("<?php\nRoute::get('/', function () {});\n")
        result3 = validate_single_repo(tmpdir2, harness)
        print(f"    Score: {_fmt_score(result3['compatibility_score'])} | Compatible: {result3['compatible']}")
        print(f"    Files: {result3['source_files']} source, {result3['total_files']} total")
        print(f"    Languages: {', '.join(result3.get('languages', {}).keys()) or 'none detected'}")
        print(f"    Project files: {', '.join(result3.get('project_files', []))}")
    finally:
        shutil.rmtree(tmpdir2, ignore_errors=True)
    print()

    # Overall summary
    all_compatible = all([
        result1.get("compatible", False),
        result2.get("compatible", False),
        result3.get("compatible", False),
    ])
    print("=" * 70)
    print(" DEMO SUMMARY")
    print("=" * 70)
    print(f"  Repositories tested: 3")
    print(f"  All compatible: {'YES' if all_compatible else 'NO'}")
    print(f"  Harness baseline: {_fmt_score(result1['compatibility_score'])}")
    print("=" * 70)

    return all_compatible


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 cross-repo-validate.py <command> [options]")
        print()
        print("Commands:")
        print("  run [--repos <dir1,dir2,...>]  Validate multiple repositories")
        print("  quick --dir <repo_path>         Validate a single repository")
        print("  demo                            Run demo validation")
        return 1

    command = sys.argv[1]

    if command == "demo":
        ok = run_demo()
        return 0 if ok else 1

    elif command == "quick":
        repo_path = "."
        harness_root = None
        args = sys.argv[2:]
        for idx in range(len(args)):
            if args[idx] == "--dir" and idx + 1 < len(args):
                repo_path = args[idx + 1]
        result = validate_single_repo(repo_path, harness_root)
        print(json.dumps(result, indent=2))
        return 0 if result.get("compatible") else 1

    elif command == "run":
        repo_paths = []
        harness_root = None
        args = sys.argv[2:]
        for idx in range(len(args)):
            if args[idx] == "--repos" and idx + 1 < len(args):
                repo_paths = [p.strip() for p in args[idx + 1].split(",")]
            elif args[idx] == "--dir" and idx + 1 < len(args):
                harness_root = args[idx + 1]
        if not repo_paths:
            print("Error: --repos required for 'run' command", file=sys.stderr)
            return 1
        ok = validate_multiple_repos(repo_paths, harness_root)
        return 0 if ok else 1

    else:
        print(f"Unknown command: {command}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
