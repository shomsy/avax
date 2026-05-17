#!/usr/bin/env python3
# command_sandbox.py — V1.0.0 Safe Subprocess Execution Sandbox
#
# Gap 1: Replaces shell=True with safe subprocess execution.
# Provides a sandboxed command execution layer that eliminates shell=True
# from the critical path by parsing, whitelisting, and resource-limiting
# all subprocess invocations.
#
# Standard-library only: shlex, subprocess, resource, os, sys, json

import os
import sys
import json
import shlex
import subprocess
from typing import Callable

# ---------------------------------------------------------------------------
# 1. CommandParser — safely parses shell command strings into argument lists
# ---------------------------------------------------------------------------

# Shell metacharacters that indicate unsafe shell constructs.
# These are intentionally blocked to prevent injection.
_UNSAFE_PATTERNS = [
    ";",       # command separator
    "&&",      # logical AND
    "||",      # logical OR
    "|",       # pipe
    "$(",      # command substitution (paren)
    "`",        # command substitution (backtick)
    ">",       # redirect
    "<",       # redirect input
    ">>",      # append redirect
    "2>",      # stderr redirect
    "&>",      # redirect all output
    ">|",      # force redirect (csh/zsh)
    "<(",      # process substitution
    ">>(",     # process substitution append
]


class CommandParser:
    """
    Safely parses a shell command string into an argv list.

    Rejects any command that contains shell metacharacters that would
    require shell=True to interpret (pipelines, redirects, subshells,
    variable expansion, etc.).
    """

    @staticmethod
    def _contains_unsafe_patterns(cmd_string: str) -> list[str]:
        """Return a list of unsafe patterns found in the command string."""
        found = []
        for pattern in _UNSAFE_PATTERNS:
            if pattern in cmd_string:
                found.append(pattern)
        return found

    @classmethod
    def parse_command(cls, cmd_string: str) -> tuple[list[str], dict]:
        """
        Parse a command string into an [argv] list.

        Args:
            cmd_string: The command string to parse (e.g. 'echo hello world').

        Returns:
            A tuple of (argv_list, parsed_info_dict).

        Raises:
            ValueError: If the command contains unsafe shell syntax.
        """
        if not cmd_string or not cmd_string.strip():
            raise ValueError("command rejected: unsafe shell syntax (empty command)")

        unsafe = cls._contains_unsafe_patterns(cmd_string)
        if unsafe:
            raise ValueError(
                f"command rejected: unsafe shell syntax — found disallowed "
                f"metacharacter(s): {', '.join(repr(p) for p in unsafe)}"
            )

        try:
            argv = shlex.split(cmd_string)
        except ValueError as e:
            raise ValueError(f"command rejected: unsafe shell syntax — {e}") from e

        if not argv:
            raise ValueError("command rejected: unsafe shell syntax (no command found)")

        parsed_info = {
            "executable": argv[0],
            "arguments": argv[1:],
            "raw": cmd_string,
        }
        return argv, parsed_info


# ---------------------------------------------------------------------------
# 2. AllowedCommandRegistry — whitelist of allowed commands
# ---------------------------------------------------------------------------

_DEFAULT_ALLOWED = [
    "echo", "cat", "ls", "grep", "find",
    "python3", "python", "node", "bash", "sh",
    "git", "make", "pip", "npm", "go", "rustc", "cargo",
]


class AllowedCommandRegistry:
    """
    Maintains a whitelist of allowed commands.

    The whitelist is persisted to
    .agents/management/evidence/generated/allowed-commands.json.
    """

    def __init__(self, storage_path: str | None = None):
        if storage_path is None:
            # Resolve relative to the repository root (cwd or script dir)
            base = os.getcwd()
            storage_path = os.path.join(
                base, ".agents", "management", "evidence", "generated",
                "allowed-commands.json",
            )
        self._storage_path = storage_path
        self._allowed: dict[str, str] = {}  # cmd -> reason
        self._load()

    # -- persistence --

    def _load(self) -> None:
        """Load the whitelist from the JSON file, or seed defaults."""
        if os.path.exists(self._storage_path):
            with open(self._storage_path, "r", encoding="utf-8") as f:
                data = json.load(f)
            self._allowed = data.get("commands", {})
        else:
            # Seed with defaults
            self._allowed = {cmd: "default" for cmd in _DEFAULT_ALLOWED}
            self._save()

    def _save(self) -> None:
        """Persist the current whitelist to disk."""
        os.makedirs(os.path.dirname(self._storage_path), exist_ok=True)
        with open(self._storage_path, "w", encoding="utf-8") as f:
            json.dump({"commands": self._allowed}, f, indent=2)

    # -- public API --

    def is_allowed(self, cmd: str) -> bool:
        """Check if *cmd* (basename) is in the whitelist."""
        return os.path.basename(cmd) in self._allowed

    def add_allowed(self, cmd: str, reason: str = "") -> None:
        """Add *cmd* to the whitelist with an optional reason."""
        self._allowed[os.path.basename(cmd)] = reason or "manually added"
        self._save()

    def remove_allowed(self, cmd: str) -> None:
        """Remove *cmd* from the whitelist."""
        self._allowed.pop(os.path.basename(cmd), None)
        self._save()

    def list_allowed(self) -> dict[str, str]:
        """Return a copy of the allowed-commands mapping."""
        return dict(self._allowed)


# ---------------------------------------------------------------------------
# 3. ResourceLimiter — applies OS-level resource limits
# ---------------------------------------------------------------------------

_DEFAULT_LIMITS = {
    "max_memory_mb": 512,
    "cpu_time_sec": 300,
    "max_processes": 32,
    "max_file_size_mb": 64,
}


class ResourceLimiter:
    """
    Applies OS-level resource limits to subprocesses via the resource module
    (Unix only). On non-Unix platforms, limits are a no-op with a warning.
    """

    def __init__(
        self,
        max_memory_mb: int | None = None,
        cpu_time_sec: int | None = None,
        max_processes: int | None = None,
        max_file_size_mb: int | None = None,
    ):
        self.max_memory_mb = max_memory_mb
        self.cpu_time_sec = cpu_time_sec
        self.max_processes = max_processes
        self.max_file_size_mb = max_file_size_mb

    @classmethod
    def from_defaults(cls) -> "ResourceLimiter":
        return cls(**_DEFAULT_LIMITS)

    def _apply_limits(self) -> None:
        """Apply resource limits in the child process (called via preexec_fn)."""
        try:
            import resource  # Unix-only
        except ImportError:
            return

        if self.max_memory_mb is not None:
            limit_bytes = self.max_memory_mb * 1024 * 1024
            resource.setrlimit(resource.RLIMIT_AS, (limit_bytes, limit_bytes))

        if self.cpu_time_sec is not None:
            resource.setrlimit(resource.RLIMIT_CPU, (self.cpu_time_sec, self.cpu_time_sec))

        if self.max_processes is not None:
            resource.setrlimit(resource.RLIMIT_NPROC, (self.max_processes, self.max_processes))

        if self.max_file_size_mb is not None:
            limit_bytes = self.max_file_size_mb * 1024 * 1024
            resource.setrlimit(resource.RLIMIT_FSIZE, (limit_bytes, limit_bytes))

    def apply_to_subprocess(self, process=None, limits: dict | None = None) -> Callable | None:
        """
        Return a preexec_fn callable that applies the configured limits
        in the child process.

        On non-Unix (Windows), returns None with a warning printed to stderr.
        """
        if sys.platform == "win32":
            print(
                "WARNING: ResourceLimiter is not supported on Windows; "
                "returning no-op preexec_fn.",
                file=sys.stderr,
            )
            return None
        return self._apply_limits


# ---------------------------------------------------------------------------
# 4. SafeSubprocessRunner — safe subprocess execution
# ---------------------------------------------------------------------------

class SafeSubprocessRunner:
    """
    Safe subprocess execution pipeline:
      parse command → check allowed → apply resource limits → run with shell=False
    """

    def __init__(
        self,
        registry: AllowedCommandRegistry | None = None,
        resource_limiter: ResourceLimiter | None = None,
        env: dict | None = None,
        cwd: str | None = None,
        timeout: int = 300,
    ):
        self.registry = registry or AllowedCommandRegistry()
        self.limiter = resource_limiter or ResourceLimiter.from_defaults()
        self.env = env
        self.cwd = cwd
        self.timeout = timeout

    def run_safe(
        self,
        command_string: str,
        env: dict | None = None,
        cwd: str | None = None,
        timeout: int | None = None,
        max_memory_mb: int | None = None,
    ) -> subprocess.CompletedProcess:
        """
        Execute a command string safely (shell=False).

        Returns a CompletedProcess with stdout, stderr, returncode.
        On policy violations, returns a CompletedProcess with returncode=1
        and an error message in stderr.
        """
        effective_env = env or self.env
        effective_cwd = cwd or self.cwd
        effective_timeout = timeout if timeout is not None else self.timeout

        # Step 1 — Parse
        try:
            argv, parsed_info = CommandParser.parse_command(command_string)
        except ValueError as e:
            return subprocess.CompletedProcess(
                args=command_string, returncode=1, stdout="", stderr=str(e)
            )

        # Step 2 — Check whitelist
        if not self.registry.is_allowed(argv[0]):
            return subprocess.CompletedProcess(
                args=command_string, returncode=1,
                stdout="",
                stderr=f"command rejected: not in allowed list ({argv[0]!r})",
            )

        # Step 3 — Build resource-limited runner
        if max_memory_mb is not None:
            self.limiter.max_memory_mb = max_memory_mb

        preexec = self.limiter.apply_to_subprocess()

        # Step 4 — Execute with shell=False
        try:
            result = subprocess.run(
                argv,
                shell=False,
                capture_output=True,
                text=True,
                cwd=effective_cwd,
                env=effective_env,
                timeout=effective_timeout,
                preexec_fn=preexec,
            )
            return result
        except subprocess.TimeoutExpired as e:
            return subprocess.CompletedProcess(
                args=command_string, returncode=-124,
                stdout=e.stdout or "", stderr=f"Execution timed out after {effective_timeout}s"
            )
        except Exception as e:
            return subprocess.CompletedProcess(
                args=command_string, returncode=-1,
                stdout="", stderr=f"Execution failed: {e}"
            )


# ---------------------------------------------------------------------------
# 5. SandboxPolicy — configurable sandbox policy
# ---------------------------------------------------------------------------

_TRUST_TIERS = ["READ_ONLY", "WORKSPACE_WRITE", "GOVERNANCE_WRITE", "TRUSTED"]

# Per-tier allowed command lists
_TIER_COMMANDS = {
    "READ_ONLY": ["echo", "cat", "ls", "grep", "find", "python3", "python", "node"],
    "WORKSPACE_WRITE": [
        "echo", "cat", "ls", "grep", "find",
        "python3", "python", "node", "bash", "sh",
        "git", "make", "pip", "npm",
    ],
    "GOVERNANCE_WRITE": [
        "echo", "cat", "ls", "grep", "find",
        "python3", "python", "node", "bash", "sh",
        "git", "make", "pip", "npm", "go", "rustc", "cargo",
    ],
    "TRUSTED": [
        "echo", "cat", "ls", "grep", "find",
        "python3", "python", "node", "bash", "sh",
        "git", "make", "pip", "npm", "go", "rustc", "cargo",
    ],
}

# Per-tier resource limits
_TIER_LIMITS = {
    "READ_ONLY": {
        "max_memory_mb": 256,
        "cpu_time_sec": 60,
        "max_processes": 8,
        "max_file_size_mb": 0,  # no file writes allowed
    },
    "WORKSPACE_WRITE": {
        "max_memory_mb": 512,
        "cpu_time_sec": 300,
        "max_processes": 32,
        "max_file_size_mb": 64,
    },
    "GOVERNANCE_WRITE": {
        "max_memory_mb": 1024,
        "cpu_time_sec": 600,
        "max_processes": 64,
        "max_file_size_mb": 128,
    },
    "TRUSTED": {
        "max_memory_mb": 2048,
        "cpu_time_sec": 1800,
        "max_processes": 128,
        "max_file_size_mb": 512,
    },
}


class SandboxPolicy:
    """
    Configurable sandbox policy mapping trust tiers to allowed commands
    and resource limits.
    """

    @classmethod
    def get_policy_for_tier(cls, tier: str) -> dict:
        """
        Return the full policy dict for a given trust tier.

        Args:
            tier: One of READ_ONLY, WORKSPACE_WRITE, GOVERNANCE_WRITE, TRUSTED.

        Returns:
            A dict with keys: tier, allowed_commands, resource_limits.

        Raises:
            ValueError: If tier is not recognized.
        """
        if tier not in _TRUST_TIERS:
            raise ValueError(
                f"Unknown trust tier: {tier!r}. Valid tiers: {_TRUST_TIERS}"
            )
        return {
            "tier": tier,
            "allowed_commands": list(_TIER_COMMANDS[tier]),
            "resource_limits": dict(_TIER_LIMITS[tier]),
        }

    @classmethod
    def enforce_policy(cls, command_string: str, tier: str) -> dict:
        """
        Check a command string against the tier policy.

        Returns:
            A dict with keys: allowed (bool), reason (str), parsed_argv (list|None).
        """
        if tier not in _TRUST_TIERS:
            return {
                "allowed": False,
                "reason": f"Unknown trust tier: {tier!r}",
                "parsed_argv": None,
            }

        # Parse
        try:
            argv, parsed_info = CommandParser.parse_command(command_string)
        except ValueError as e:
            return {
                "allowed": False,
                "reason": str(e),
                "parsed_argv": None,
            }

        # Check tier whitelist
        allowed_cmds = _TIER_COMMANDS[tier]
        cmd_name = os.path.basename(argv[0])
        if cmd_name not in allowed_cmds:
            return {
                "allowed": False,
                "reason": f"command rejected: not in allowed list for tier {tier} ({cmd_name!r})",
                "parsed_argv": argv,
            }

        return {
            "allowed": True,
            "reason": f"command allowed for tier {tier}",
            "parsed_argv": argv,
        }


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _cli_parse(cmd_string: str) -> None:
    """CLI: parse a command string and print the result."""
    try:
        argv, info = CommandParser.parse_command(cmd_string)
        print(f"Command: {cmd_string!r}")
        print(f"  Executable: {info['executable']}")
        print(f"  Arguments:  {info['arguments']}")
        print(f"  argv:       {argv}")
    except ValueError as e:
        print(f"ERROR: {e}", file=sys.stderr)
        sys.exit(1)


def _cli_check(cmd_string: str) -> None:
    """CLI: check if a command is allowed."""
    registry = AllowedCommandRegistry()
    try:
        argv, info = CommandParser.parse_command(cmd_string)
    except ValueError as e:
        print(f"REJECTED: {e}")
        sys.exit(1)

    cmd_name = argv[0]
    if registry.is_allowed(cmd_name):
        print(f"ALLOWED: {cmd_string!r} — {cmd_name!r} is in the allowed list")
    else:
        print(f"REJECTED: {cmd_string!r} — {cmd_name!r} is NOT in the allowed list")
        sys.exit(1)


def _cli_run(cmd_string: str) -> None:
    """CLI: safely run a command."""
    runner = SafeSubprocessRunner()
    result = runner.run_safe(cmd_string)
    if result.stdout:
        print(result.stdout, end="")
    if result.stderr:
        print(result.stderr, end="", file=sys.stderr)
    if result.returncode != 0:
        sys.exit(result.returncode if result.returncode > 0 else 1)


def _cli_allowed() -> None:
    """CLI: list all allowed commands."""
    registry = AllowedCommandRegistry()
    allowed = registry.list_allowed()
    print(f"Allowed commands ({len(allowed)}):")
    for cmd in sorted(allowed):
        print(f"  {cmd}  ({allowed[cmd]})")


def _cli_policy(tier: str) -> None:
    """CLI: show policy for a trust tier."""
    try:
        policy = SandboxPolicy.get_policy_for_tier(tier)
    except ValueError as e:
        print(f"ERROR: {e}", file=sys.stderr)
        sys.exit(1)

    print(f"Trust Tier: {policy['tier']}")
    print(f"  Allowed Commands ({len(policy['allowed_commands'])}):")
    for cmd in policy["allowed_commands"]:
        print(f"    - {cmd}")
    print(f"  Resource Limits:")
    for key, val in policy["resource_limits"].items():
        print(f"    - {key}: {val}")


def main() -> None:
    if len(sys.argv) < 2:
        print("Usage:")
        print("  command_sandbox.py parse  <command_string>")
        print("  command_sandbox.py check  <command_string>")
        print("  command_sandbox.py run    <command_string>")
        print("  command_sandbox.py allowed")
        print("  command_sandbox.py policy <TIER>")
        print()
        print("Tiers: READ_ONLY, WORKSPACE_WRITE, GOVERNANCE_WRITE, TRUSTED")
        sys.exit(1)

    subcmd = sys.argv[1]

    if subcmd == "parse":
        if len(sys.argv) < 3:
            print("Usage: command_sandbox.py parse <command_string>", file=sys.stderr)
            sys.exit(1)
        _cli_parse(sys.argv[2])

    elif subcmd == "check":
        if len(sys.argv) < 3:
            print("Usage: command_sandbox.py check <command_string>", file=sys.stderr)
            sys.exit(1)
        _cli_check(sys.argv[2])

    elif subcmd == "run":
        if len(sys.argv) < 3:
            print("Usage: command_sandbox.py run <command_string>", file=sys.stderr)
            sys.exit(1)
        _cli_run(sys.argv[2])

    elif subcmd == "allowed":
        _cli_allowed()

    elif subcmd == "policy":
        if len(sys.argv) < 3:
            print("Usage: command_sandbox.py policy <TIER>", file=sys.stderr)
            sys.exit(1)
        _cli_policy(sys.argv[2])

    else:
        print(f"Unknown subcommand: {subcmd!r}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
