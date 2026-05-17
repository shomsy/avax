#!/usr/bin/env python3
"""agent-harness-diagnose.py — Interactive Diagnostics & Self-Healing for Agent Harness.

Provides:
  diagnose   — Full system health check with explain-why failures
  bootstrap  — One-command onboarding for new repositories
  status     — Quick operational status summary
  self-heal  — Automatic repair suggestions for detected issues

Usage:
  python3 agent-harness-diagnose.py diagnose [--dir <dir>]
  python3 agent-harness-diagnose.py bootstrap [--dir <dir>]
  python3 agent-harness-diagnose.py status [--dir <dir>]
  python3 agent-harness-diagnose.py self-heal [--dir <dir>]
"""

import os
import sys
import json
import stat
import shutil
import time

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

BIN_DIR = os.path.dirname(os.path.abspath(__file__))
REQUIRED_PYTHON = (3, 9)
REQUIRED_BIN_FILES = [
    "execution-substrate.py",
    "substrate_security.py",
    "command_sandbox.py",
    "crypto_seals.py",
    "execution_analysis.py",
    "compile-governance.py",
    "lint-governance.py",
    "check-complexity-budget.py",
    "evidence-lifecycle.py",
    "replay-evidence.py",
]
REQUIRED_HOOK_FILES = [
    "lib.sh",
    "session-start.sh",
    "pre-task.sh",
    "post-task.sh",
    "pre-tool-use.sh",
    "post-tool-use.sh",
    "resolve-task-context.py",
]
REQUIRED_DIRS = [
    ".agents/management/evidence",
    ".agents/management/evidence/execution",
    ".agents/management/evidence/security",
    ".agents/management/evidence/generated",
    ".agents/management/evidence/traces",
    ".agents/management/evidence/raw",
    ".agents/management/evidence/archive",
    ".agents/skills/bin",
    ".agents/hooks",
    ".agents/governance",
]
REQUIRED_GOVERNANCE_FILES = [
    ".agents/AGENTS.md",
    ".agents/governance/core/quality/quality-gates.md",
    ".agents/governance/core/bootstrap/agent-bootstrap.md",
    ".agents/governance/core/resolution/profile-resolution-algorithm.md",
]

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _target_dir(path=None):
    return os.path.normpath(path) if path else "."


def _check(cond, label, detail="", fix=""):
    """Return a check result dict."""
    status = "PASS" if cond else "FAIL"
    return {
        "status": status,
        "label": label,
        "detail": detail if detail else ("OK" if cond else "Not found / not valid"),
        "fix": fix if not cond else "",
    }


def _run_check(path, label, fix=""):
    return _check(os.path.exists(path), label, fix=fix)


# ---------------------------------------------------------------------------
# Diagnose
# ---------------------------------------------------------------------------

def diagnose(target_dir="."):
    """Run full system health check."""
    td = _target_dir(target_dir)
    results = []

    # 1. Python version
    py_ok = sys.version_info >= REQUIRED_PYTHON
    results.append(_check(
        py_ok,
        "Python version",
        f"{sys.version_info.major}.{sys.version_info.minor}.{sys.version_info.micro}",
        f"Upgrade to Python {'.'.join(str(x) for x in REQUIRED_PYTHON)}+",
    ))

    # 2. Required bin files
    for f in REQUIRED_BIN_FILES:
        p = os.path.join(td, ".agents/skills/bin", f)
        results.append(_run_check(
            p, f"bin/{f}",
            f"Re-run install-os.sh or copy {f} to .agents/skills/bin/",
        ))

    # 3. Required hook files
    for f in REQUIRED_HOOK_FILES:
        p = os.path.join(td, ".agents/hooks", f)
        results.append(_run_check(
            p, f"hooks/{f}",
            f"Re-run install-os.sh or copy {f} to .agents/hooks/",
        ))

    # 4. Required directories
    for d in REQUIRED_DIRS:
        p = os.path.join(td, d)
        results.append(_run_check(
            p, f"dir {d}",
            f"mkdir -p {d}",
        ))

    # 5. Governance contract files
    for f in REQUIRED_GOVERNANCE_FILES:
        p = os.path.join(td, f)
        results.append(_run_check(
            p, f"governance {f}",
            f"Restore {f} from agent-harness repository",
        ))

    # 6. HMAC key (security)
    hmac_key = os.path.join(td, ".agents/management/evidence/security/hmac-key.bin")
    hmac_exists = os.path.exists(hmac_key)
    hmac_perm_ok = False
    if hmac_exists:
        st = os.stat(hmac_key)
        hmac_perm_ok = stat.S_IMODE(st.st_mode) == 0o600
    results.append(_check(
        hmac_exists,
        "HMAC key exists",
        fix="Run: python3 .agents/skills/bin/crypto_seals.py key generate",
    ))
    if hmac_exists:
        results.append(_check(
            hmac_perm_ok,
            "HMAC key permissions (0o600)",
            fix="Run: chmod 600 " + hmac_key,
        ))

    # 7. AGENTS.md at root
    results.append(_run_check(
        os.path.join(td, "AGENTS.md"),
        "Root AGENTS.md",
        "Copy scaffolds/AGENTS.md to repository root and customize",
    ))

    # 8. install-os.sh executable
    installer = os.path.join(td, "install-os.sh")
    if os.path.exists(installer):
        inst_exec = os.access(installer, os.X_OK)
        results.append(_check(
            inst_exec,
            "install-os.sh is executable",
            fix="chmod +x install-os.sh",
        ))

    # 9. Evidence directory not bloated (>100MB)
    evidence_dir = os.path.join(td, ".agents/management/evidence")
    if os.path.exists(evidence_dir):
        total_size = sum(
            os.path.getsize(os.path.join(dirpath, filename))
            for dirpath, dirnames, filenames in os.walk(evidence_dir)
            for filename in filenames
        )
        size_mb = total_size / (1024 * 1024)
        results.append(_check(
            size_mb < 100,
            f"Evidence size ({size_mb:.1f}MB < 100MB)",
            fix="Run: python3 .agents/skills/bin/evidence-lifecycle.py compact",
        ))

    # 10. No shell=True in execution path
    exec_substrate = os.path.join(td, ".agents/skills/bin/execution-substrate.py")
    if os.path.exists(exec_substrate):
        with open(exec_substrate, "r") as f:
            content = f.read()
        shell_true = "shell=True" in content
        results.append(_check(
            not shell_true,
            "No shell=True in execution-substrate.py",
            fix="Use SafeSubprocessRunner from command_sandbox.py",
        ))

    # Summary
    passed = sum(1 for r in results if r["status"] == "PASS")
    failed = sum(1 for r in results if r["status"] == "FAIL")

    print("=" * 70)
    print(" AGENT HARNESS DIAGNOSTICS")
    print("=" * 70)
    for r in results:
        icon = "OK" if r["status"] == "PASS" else "FAIL"
        print(f"  [{icon}] {r['label']}: {r['detail']}")
        if r["fix"]:
            print(f"         FIX: {r['fix']}")
    print("-" * 70)
    print(f"  Results: {passed} passed, {failed} failed, {len(results)} total")
    print("=" * 70)

    return failed == 0


# ---------------------------------------------------------------------------
# Bootstrap
# ---------------------------------------------------------------------------

def bootstrap(target_dir="."):
    """One-command onboarding for a new repository."""
    td = _target_dir(target_dir)
    steps = []

    # Step 1: Create directory structure
    for d in REQUIRED_DIRS:
        p = os.path.join(td, d)
        if not os.path.exists(p):
            os.makedirs(p, exist_ok=True)
            steps.append(f"  Created: {d}")

    # Step 2: Copy skeleton if not present
    agents_md = os.path.join(td, "AGENTS.md")
    if not os.path.exists(agents_md):
        scaffold = os.path.join(os.path.dirname(BIN_DIR), "..", "..", "scaffolds", "AGENTS.md")
        if os.path.exists(scaffold):
            shutil.copy2(scaffold, agents_md)
            steps.append("  Copied: scaffolds/AGENTS.md -> AGENTS.md")
        else:
            steps.append("  SKIP: AGENTS.md scaffold not found (copy manually)")

    # Step 3: Create management files
    mgmt_files = {
        "CURRENT.md": "# Operational Truth\n\n- Status: BOOTSTRAPPING\n",
        "STATUS.md": "# Status\n\n- Overall: YELLOW (bootstrapping)\n",
        "TODO.md": "# TODO\n\n# Backlog items go here\n",
        "BUGS.md": "# Bugs\n\n# No active bugs\n",
        "DECISIONS.md": "# Decisions\n\n# Architecture decisions go here\n",
        "RISKS.md": "# Risks\n\n# Risk register\n",
    }
    for name, content in mgmt_files.items():
        p = os.path.join(td, ".agents/management", name)
        if not os.path.exists(p):
            with open(p, "w") as f:
                f.write(content)
            steps.append(f"  Created: .agents/management/{name}")

    # Step 4: Generate HMAC key
    hmac_key = os.path.join(td, ".agents/management/evidence/security/hmac-key.bin")
    if not os.path.exists(hmac_key):
        sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
        from crypto_seals import HMACKeyManager
        mgr = HMACKeyManager(target_dir=td)
        key = mgr.generate_key()
        mgr.save_key(key)
        steps.append("  Generated: HMAC-SHA256 key")

    # Step 5: Verify governance files
    missing_gov = []
    for f in REQUIRED_GOVERNANCE_FILES:
        if not os.path.exists(os.path.join(td, f)):
            missing_gov.append(f)
    if missing_gov:
        steps.append(f"  WARNING: Missing governance files: {', '.join(missing_gov)}")
    else:
        steps.append("  Verified: All core governance files present")

    # Step 6: Compile governance index
    compile_script = os.path.join(os.path.dirname(os.path.abspath(__file__)), "compile-governance.py")
    if os.path.exists(compile_script):
        import subprocess
        rc = subprocess.run([sys.executable, compile_script, "--dir", td], capture_output=True, text=True)
        if rc.returncode == 0:
            steps.append("  Compiled: governance index")
        else:
            steps.append(f"  SKIP: governance compilation failed ({rc.stderr.strip()})")

    print("=" * 70)
    print(" AGENT HARNESS BOOTSTRAP")
    print("=" * 70)
    for s in steps:
        print(s)
    print("=" * 70)
    print("  Next: Run 'python3 agent-harness-diagnose.py diagnose' to verify")
    print("=" * 70)
    return True


# ---------------------------------------------------------------------------
# Status
# ---------------------------------------------------------------------------

def status(target_dir="."):
    """Quick operational status."""
    td = _target_dir(target_dir)

    # Count executions
    exec_dir = os.path.join(td, ".agents/management/evidence/execution")
    exec_count = 0
    if os.path.exists(exec_dir):
        exec_count = len([f for f in os.listdir(exec_dir) if f.startswith("execution-manifest-")])

    # Check HMAC chain
    chain_path = os.path.join(td, ".agents/management/evidence/security/hmac-audit-chain.jsonl")
    chain_entries = 0
    if os.path.exists(chain_path):
        with open(chain_path, "r") as f:
            chain_entries = sum(1 for line in f if line.strip())

    # Evidence size
    evidence_dir = os.path.join(td, ".agents/management/evidence")
    total_size = 0
    if os.path.exists(evidence_dir):
        total_size = sum(
            os.path.getsize(os.path.join(dirpath, filename))
            for dirpath, dirnames, filenames in os.walk(evidence_dir)
            for filename in filenames
        )

    # Governance rules
    index_path = os.path.join(td, ".agents/management/evidence/generated/governance-index.json")
    rule_count = 0
    if os.path.exists(index_path):
        with open(index_path, "r") as f:
            data = json.load(f)
        rule_count = len(data.get("files", {}))

    print("=" * 70)
    print(" AGENT HARNESS STATUS")
    print("=" * 70)
    print(f"  Executions tracked:  {exec_count}")
    print(f"  HMAC audit entries:  {chain_entries}")
    print(f"  Evidence size:       {total_size / (1024 * 1024):.1f} MB")
    print(f"  Governance rules:    {rule_count}")
    print("=" * 70)
    return True


# ---------------------------------------------------------------------------
# Self-Heal
# ---------------------------------------------------------------------------

def self_heal(target_dir="."):
    """Automatic repair of detected issues."""
    td = _target_dir(target_dir)
    fixes_applied = []

    # Fix 1: Create missing directories
    for d in REQUIRED_DIRS:
        p = os.path.join(td, d)
        if not os.path.exists(p):
            os.makedirs(p, exist_ok=True)
            fixes_applied.append(f"Created directory: {d}")

    # Fix 2: Fix HMAC key permissions
    hmac_key = os.path.join(td, ".agents/management/evidence/security/hmac-key.bin")
    if os.path.exists(hmac_key):
        st = os.stat(hmac_key)
        current_mode = stat.S_IMODE(st.st_mode)
        if current_mode != 0o600:
            os.chmod(hmac_key, 0o600)
            fixes_applied.append(f"Fixed HMAC key permissions: {oct(current_mode)} -> 0o600")

    # Fix 3: Compact evidence if bloated
    evidence_dir = os.path.join(td, ".agents/management/evidence")
    if os.path.exists(evidence_dir):
        total_size = sum(
            os.path.getsize(os.path.join(dirpath, filename))
            for dirpath, dirnames, filenames in os.walk(evidence_dir)
            for filename in filenames
        )
        if total_size > 100 * 1024 * 1024:
            # Run evidence lifecycle compaction
            lifecycle_script = os.path.join(os.path.dirname(os.path.abspath(__file__)), "evidence-lifecycle.py")
            if os.path.exists(lifecycle_script):
                import subprocess
                rc = subprocess.run([sys.executable, lifecycle_script, "compact", "--dir", td], capture_output=True, text=True)
                if rc.returncode == 0:
                    fixes_applied.append("Compacted evidence (was >100MB)")
                else:
                    fixes_applied.append(f"Evidence compaction failed: {rc.stderr.strip()}")

    # Fix 4: Clean expired nonces
    nonce_path = os.path.join(td, ".agents/management/evidence/security/nonce-registry.jsonl")
    if os.path.exists(nonce_path):
        cleaned = 0
        kept = 0
        now = time.time()
        entries = []
        with open(nonce_path, "r") as f:
            for line in f:
                line = line.strip()
                if not line:
                    continue
                entry = json.loads(line)
                if entry.get("expires_at", 0) > now:
                    entries.append(line)
                    kept += 1
                else:
                    cleaned += 1
        if cleaned > 0:
            with open(nonce_path, "w") as f:
                for e in entries:
                    f.write(e + "\n")
            fixes_applied.append(f"Cleaned {cleaned} expired nonces ({kept} remaining)")

    # Fix 5: Clean expired revocations
    revocation_path = os.path.join(td, ".agents/management/evidence/security/revocation-registry.jsonl")
    if os.path.exists(revocation_path):
        cleaned = 0
        now = time.time()
        entries = []
        with open(revocation_path, "r") as f:
            for line in f:
                line = line.strip()
                if not line:
                    continue
                entry = json.loads(line)
                # Keep revocations for 30 days
                if now - entry.get("revoked_at", 0) < 30 * 86400:
                    entries.append(line)
                else:
                    cleaned += 1
        if cleaned > 0:
            with open(revocation_path, "w") as f:
                for e in entries:
                    f.write(e + "\n")
            fixes_applied.append(f"Cleaned {cleaned} old revocation entries")

    if not fixes_applied:
        print("=" * 70)
        print(" SELF-HEAL: No issues detected — system is healthy")
        print("=" * 70)
    else:
        print("=" * 70)
        print(" SELF-HEAL: Applied fixes")
        print("=" * 70)
        for fix in fixes_applied:
            print(f"  [FIX] {fix}")
        print("=" * 70)

    return True


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 agent-harness-diagnose.py <command> [--dir <dir>]")
        print()
        print("Commands:")
        print("  diagnose    Full system health check with explain-why failures")
        print("  bootstrap   One-command onboarding for new repositories")
        print("  status      Quick operational status summary")
        print("  self-heal   Automatic repair of detected issues")
        return 1

    command = sys.argv[1]
    target_dir = "."
    args = sys.argv[2:]
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]

    commands = {
        "diagnose": diagnose,
        "bootstrap": bootstrap,
        "status": status,
        "self-heal": self_heal,
    }

    if command not in commands:
        print(f"Unknown command: {command}", file=sys.stderr)
        return 1

    ok = commands[command](target_dir)
    return 0 if ok else 1


if __name__ == "__main__":
    sys.exit(main())
