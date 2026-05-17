#!/usr/bin/env python3
"""agent-harness-diagnose.py — V6 Adoption & Upgrade Diagnostics

Diagnoses adoption readiness, upgrade readiness, migration readiness,
install health, and orphan runtime artifact detection.

Usage:
    python3 agent-harness-diagnose.py /path/to/project [--verbose] [--json]
"""

import json
import os
import sys
import hashlib
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

# Runtime exclusion patterns matching the installer
RUNTIME_EXCLUDE_PATTERNS = [
    "__pycache__", "*.pyc", "*.pyo", ".pytest_cache",
    "replay-snapshot*", "replay_snapshot*", "quarantine",
    "stress-output*", "stress_output*", "*.tmp", "*.tmp.*",
    ".DS_Store", "Thumbs.db", "*.lock", "node_modules", "vendor",
]

# Forbidden folder names — based on AvaX AGENTS.md Section 8
# These are generic technical dumping grounds that should not exist.
FORBIDDEN_DIRECTORY_NAMES = {
    "Services", "Helpers", "Utils", "Common", "Shared",
    "Managers", "Core", "Support", "Adapters", "Contracts",
    "Handlers", "Processors", "Commands", "Queries",
    "Domain", "Entities", "ValueObjects", "Aggregates",
    "Repositories", "Events", "CQRS", "EventSourcing",
    "Sagas", "Policies", "Specifications",
    "Diagnostics", "Tests", "Docs",
    "InternalSystem", "ExportedCapabilities",
}

# Allowed ecosystem terms — these are legitimate framework/runtime names
# that may appear in paths even though they overlap with forbidden terms.
ALLOWED_ECOSYSTEM_TERMS = {
    # PHP ecosystem
    "ServiceProvider", "Middleware", "Console", "Migrations",
    "Factories", "Seeders", "Listeners", "Observers",
    "Providers", "Facades", "Controllers", "Requests",
    "Resources", "Notifications", "Mail", "Channels",
    # Runtime adapters
    "RoadRunner", "Swoole", "FrankenPHP", "Workerman",
    "ReactPHP", "Amp", "Fiber",
    # Testing
    "PHPUnit", "Pest", "TestCase", "Feature", "Unit",
    # Build/CI
    "Composer", "Rector", "PHPStan", "CSFixer",
}


def file_checksum(path: str) -> str:
    """SHA256 checksum of a file."""
    h = hashlib.sha256()
    try:
        with open(path, "rb") as f:
            for chunk in iter(lambda: f.read(8192), b""):
                h.update(chunk)
        return h.hexdigest()
    except (OSError, IOError):
        return ""


class LayoutDetector:
    """Detect which Agent Harness layout is in use.

    Adopted layout (preferred): .agents/.rules/skills/bin, .agents/.rules/governance
    Legacy/local layout:       .agents/skills/bin, .agents/governance
    """

    def __init__(self, target: Path):
        self.target = target
        self._layout = self._detect()

    @property
    def name(self) -> str:
        return self._layout

    @property
    def skills_bin(self) -> Path:
        if self._layout == "adopted":
            return self.target / ".agents" / ".rules" / "skills" / "bin"
        return self.target / ".agents" / "skills" / "bin"

    @property
    def hooks(self) -> Path:
        if self._layout == "adopted":
            return self.target / ".agents" / ".rules" / "hooks"
        return self.target / ".agents" / "hooks"

    @property
    def governance(self) -> Path:
        if self._layout == "adopted":
            return self.target / ".agents" / ".rules" / "governance"
        return self.target / ".agents" / "governance"

    @property
    def baseline_rules(self) -> Path:
        return self.target / ".agents" / ".rules"

    @property
    def workspace_skills(self) -> Path:
        """Non-baseline skills dir (workspace, not .rules)."""
        return self.target / ".agents" / "skills"

    def _detect(self) -> str:
        adopted = (
            (self.target / ".agents" / ".rules" / "skills" / "bin").is_dir()
            or (self.target / ".agents" / ".rules" / "governance").is_dir()
        )
        if adopted:
            return "adopted"
        return "legacy"


def _resolve_skills_bin(target: Path) -> Path:
    """Return the skills/bin path, adopted layout first."""
    adopted = target / ".agents" / ".rules" / "skills" / "bin"
    if adopted.is_dir():
        return adopted
    return target / ".agents" / "skills" / "bin"


def _resolve_hooks(target: Path) -> Path:
    """Return the hooks path, adopted layout first."""
    adopted = target / ".agents" / ".rules" / "hooks"
    if adopted.is_dir():
        return adopted
    return target / ".agents" / "hooks"


def _resolve_governance(target: Path) -> Path:
    """Return the governance path, adopted layout first."""
    adopted = target / ".agents" / ".rules" / "governance"
    if adopted.is_dir():
        return adopted
    return target / ".agents" / "governance"


def is_runtime_excluded(path_str: str) -> bool:
    """Check if a path matches runtime exclusion patterns."""
    p = Path(path_str)
    name = p.name

    for pattern in RUNTIME_EXCLUDE_PATTERNS:
        if "*" in pattern:
            import fnmatch
            if fnmatch.fnmatch(name, pattern) or fnmatch.fnmatch(str(p), pattern):
                return True
        else:
            if pattern in str(p) or pattern == name:
                return True
    return False


def diagnose(target: str, verbose: bool = False) -> dict[str, Any]:
    """Run full diagnostic suite against target project."""
    result: dict[str, Any] = {
        "diagnostic_version": "6.0.0",
        "timestamp": datetime.now(timezone.utc).isoformat(),
        "target": os.path.abspath(target),
        "overall_status": "GREEN",
        "checks": {},
        "findings": [],
        "recommendations": [],
    }

    target_path = Path(target).resolve()

    # Detect layout
    layout = LayoutDetector(target_path)
    result["detected_layout"] = layout.name

    # --- ADOPTION READINESS ---
    adoption = _check_adoption_readiness(target_path, layout, verbose)
    result["checks"]["adoption_readiness"] = adoption
    result["findings"].extend(adoption.get("findings", []))

    # --- UPGRADE READINESS ---
    upgrade = _check_upgrade_readiness(target_path, layout, verbose)
    result["checks"]["upgrade_readiness"] = upgrade
    result["findings"].extend(upgrade.get("findings", []))

    # --- MIGRATION READINESS ---
    migration = _check_migration_readiness(target_path, layout, verbose)
    result["checks"]["migration_readiness"] = migration
    result["findings"].extend(migration.get("findings", []))

    # --- INSTALL HEALTH ---
    health = _check_install_health(target_path, layout, verbose)
    result["checks"]["install_health"] = health
    result["findings"].extend(health.get("findings", []))

    # --- ORPHAN RUNTIME ARTIFACTS ---
    orphans = _check_orphan_artifacts(target_path, layout, verbose)
    result["checks"]["orphan_artifacts"] = orphans
    result["findings"].extend(orphans.get("findings", []))

    # --- BASELINE INTEGRITY ---
    baseline = _check_baseline_integrity(target_path, layout, verbose)
    result["checks"]["baseline_integrity"] = baseline
    result["findings"].extend(baseline.get("findings", []))

    # --- NAMING COMPLIANCE ---
    naming = _check_naming_compliance(target_path, layout, verbose)
    result["checks"]["naming_compliance"] = naming
    result["findings"].extend(naming.get("findings", []))

    # Determine overall status
    severities = [f.get("severity", "info") for f in result["findings"]]
    if any(s == "critical" for s in severities):
        result["overall_status"] = "RED"
    elif any(s == "warning" for s in severities):
        result["overall_status"] = "YELLOW"

    # Generate recommendations
    if result["overall_status"] == "RED":
        result["recommendations"].append("Resolve critical findings before adoption")
    if not adoption.get("ready", False):
        result["recommendations"].append("Run install-os.sh --adopt to establish baseline")
    if orphans.get("orphan_count", 0) > 5:
        result["recommendations"].append("Consider cleaning orphan runtime artifacts")

    return result


def _check_adoption_readiness(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check if the target is ready for adoption."""
    check = {
        "ready": True,
        "status": "GREEN",
        "findings": [],
        "metrics": {},
    }

    has_agents = (target / ".agents").is_dir()
    has_agents_md = (target / "AGENTS.md").is_file()
    has_evidence = (target / "EVIDENCE").is_dir()
    has_baseline = layout.baseline_rules.is_dir()

    check["metrics"] = {
        "has_agents": has_agents,
        "has_agents_md": has_agents_md,
        "has_evidence": has_evidence,
        "has_baseline_rules": has_baseline,
        "detected_layout": layout.name,
    }

    if has_baseline:
        check["findings"].append({
            "severity": "info",
            "message": "Baseline rules already installed, adoption will be merge-aware",
        })
    elif has_agents:
        check["findings"].append({
            "severity": "warning",
            "message": ".agents exists but no baseline rules; adoption will overlay",
        })
    else:
        check["findings"].append({
            "severity": "info",
            "message": "Clean target, adoption will create full baseline",
        })

    # Check for pre-existing governance conflicts
    custom_gov = list((target / ".agents").glob("governance/**/*")) if (target / ".agents").is_dir() else []
    if custom_gov:
        check["findings"].append({
            "severity": "info",
            "message": f"Pre-existing custom governance found ({len(custom_gov)} files)",
        })

    # Check for interrupted install markers
    journal_dir = target / ".agents" / "management" / "evidence" / "install-journal"
    if journal_dir.is_dir():
        interrupted = list(journal_dir.glob("interrupted-*.json"))
        if interrupted:
            check["findings"].append({
                "severity": "warning",
                "message": f"{len(interrupted)} interrupted install recovery markers found",
            })

    return check


def _check_upgrade_readiness(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check if the target is ready for upgrade."""
    check = {
        "ready": False,
        "status": "RED",
        "findings": [],
        "metrics": {},
    }

    baseline_dir = layout.baseline_rules
    if not baseline_dir.is_dir():
        check["findings"].append({
            "severity": "critical",
            "message": "No baseline rules found; run --adopt before --upgrade",
        })
        return check

    check["ready"] = True
    check["status"] = "GREEN"

    # Count baseline files
    baseline_files = list(baseline_dir.rglob("*"))
    baseline_files = [f for f in baseline_files if f.is_file() and not is_runtime_excluded(str(f))]
    check["metrics"]["baseline_file_count"] = len(baseline_files)

    # Check for locally modified baseline files
    # (Would need source comparison — just report count for now)
    check["metrics"]["baseline_path"] = str(baseline_dir)

    # Check journal for last install version
    journal_dir = target / ".agents" / "management" / "evidence" / "install-journal"
    if journal_dir.is_dir():
        journals = sorted(journal_dir.glob("journal-*.jsonl"))
        if journals:
            last_journal = journals[-1]
            check["metrics"]["last_install_journal"] = last_journal.name
            check["findings"].append({
                "severity": "info",
                "message": f"Last install journal: {last_journal.name}",
            })

    return check


def _check_migration_readiness(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check if the target has legacy structures that need migration."""
    check = {
        "ready": False,
        "has_legacy": False,
        "status": "GREEN",
        "findings": [],
        "metrics": {},
    }

    legacy_items = []

    # Check for legacy management files at old locations
    mgmt_dir = target / ".agents" / "management"
    if mgmt_dir.is_dir():
        for legacy in ["BUGS.md", "TODO.md"]:
            if (mgmt_dir / legacy).is_file():
                legacy_items.append(f".agents/management/{legacy}")

    # Check for docs/governance
    docs_gov = target / "docs" / "governance"
    if docs_gov.is_dir():
        legacy_items.append("docs/governance")

    # Check for old harness artifacts
    for old_artifact in ["merge-files.sh", "verify-governance.sh"]:
        if (target / old_artifact).is_file():
            # These are actually current, not legacy
            pass

    check["metrics"]["legacy_items"] = legacy_items
    check["has_legacy"] = len(legacy_items) > 0

    if legacy_items:
        check["ready"] = True
        check["findings"].append({
            "severity": "info",
            "message": f"Legacy structures found: {', '.join(legacy_items)}",
        })
        check["findings"].append({
            "severity": "info",
            "message": "Run install-os.sh --migrate to archive legacy structures",
        })
    else:
        check["findings"].append({
            "severity": "info",
            "message": "No legacy structures detected",
        })

    return check


def _check_install_health(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check the health of the current installation."""
    check = {
        "status": "GREEN",
        "findings": [],
        "metrics": {},
    }

    required_dirs = [
        ".agents/.rules",
        ".agents/management",
        ".agents/management/evidence",
        ".agents/management/evidence/validation",
        ".agents/management/evidence/generated",
        ".agents/management/evidence/install-journal",
        ".agents/business-logic",
        ".agents/language-specific",
    ]

    # Skills dir: check adopted layout first, then workspace skills
    skills_bin = _resolve_skills_bin(target)
    if skills_bin.is_dir():
        check["metrics"]["skills_bin_path"] = str(skills_bin.relative_to(target))
    else:
        required_dirs.append(str(skills_bin.relative_to(target)))

    missing_dirs = []
    for d in required_dirs:
        if not (target / d).is_dir():
            missing_dirs.append(d)

    check["metrics"]["required_dirs_total"] = len(required_dirs)
    check["metrics"]["required_dirs_present"] = len(required_dirs) - len(missing_dirs)
    check["metrics"]["required_dirs_missing"] = missing_dirs

    if missing_dirs:
        check["status"] = "YELLOW"
        check["findings"].append({
            "severity": "warning",
            "message": f"{len(missing_dirs)} required directories missing: {', '.join(missing_dirs)}",
        })

    # Check for AGENTS.md
    if not (target / "AGENTS.md").is_file():
        check["status"] = "RED"
        check["findings"].append({
            "severity": "critical",
            "message": "Root AGENTS.md contract missing",
        })
    else:
        check["metrics"]["agents_md_size"] = (target / "AGENTS.md").stat().st_size

    # Check evidence dashboard
    evidence_files = ["CURRENT.md", "ACTIVE_PLAN.md", "FLOW.md", "LINKS.md"]
    present_evidence = []
    for ef in evidence_files:
        if (target / "EVIDENCE" / ef).is_file():
            present_evidence.append(ef)

    check["metrics"]["evidence_dashboard_files"] = f"{len(present_evidence)}/{len(evidence_files)}"

    if len(present_evidence) < len(evidence_files):
        check["findings"].append({
            "severity": "warning",
            "message": f"Incomplete evidence dashboard: {len(present_evidence)}/{len(evidence_files)} files",
        })

    # Check workspace directories
    workspace_dirs = [
        ".agents/memory",
        ".agents/sessions",
        ".agents/context/product",
        ".agents/context/users",
        ".agents/context/strategy",
        ".agents/context/stakeholders",
    ]
    missing_ws = [d for d in workspace_dirs if not (target / d).is_dir()]
    check["metrics"]["workspace_dirs_missing"] = len(missing_ws)

    return check


def _check_orphan_artifacts(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Detect orphan runtime artifacts that should be cleaned."""
    check = {
        "orphan_count": 0,
        "status": "GREEN",
        "findings": [],
        "orphans": [],
    }

    # Scan for common orphan patterns
    orphan_patterns = [
        ("__pycache__", "Python bytecode cache"),
        (".pytest_cache", "Pytest cache"),
        ("*.pyc", "Compiled Python files"),
        ("*.pyo", "Optimized Python files"),
        ("replay-snapshot*", "Replay snapshot artifacts"),
        ("quarantine", "Quarantined artifacts"),
        ("stress-output*", "Stress test output"),
        (".DS_Store", "macOS metadata"),
        ("Thumbs.db", "Windows thumbnails"),
    ]

    found_orphans = []
    for pattern, description in orphan_patterns:
        if "*" in pattern:
            import fnmatch
            for root, dirs, files in os.walk(target):
                # Skip .git and vendor
                if ".git" in root or "vendor" in root:
                    continue
                for f in files:
                    if fnmatch.fnmatch(f, pattern):
                        full_path = os.path.join(root, f)
                        rel = os.path.relpath(full_path, target)
                        found_orphans.append({"path": rel, "type": description})
                for d in dirs:
                    if fnmatch.fnmatch(d, pattern):
                        full_path = os.path.join(root, d)
                        rel = os.path.relpath(full_path, target)
                        found_orphans.append({"path": rel, "type": description})
        else:
            for root, dirs, files in os.walk(target):
                if ".git" in root or "vendor" in root:
                    continue
                if pattern in dirs:
                    full_path = os.path.join(root, pattern)
                    rel = os.path.relpath(full_path, target)
                    found_orphans.append({"path": rel, "type": description})
                if pattern in files:
                    full_path = os.path.join(root, pattern)
                    rel = os.path.relpath(full_path, target)
                    found_orphans.append({"path": rel, "type": description})

    # Limit to first 20 for display
    display_limit = 20
    check["orphan_count"] = len(found_orphans)
    check["orphans"] = found_orphans[:display_limit]

    if len(found_orphans) > display_limit:
        check["orphans"].append({"path": f"... and {len(found_orphans) - display_limit} more", "type": ""})

    if found_orphans:
        check["findings"].append({
            "severity": "info",
            "message": f"{len(found_orphans)} orphan runtime artifacts detected",
        })

    # Check for stale install journal recovery markers
    journal_dir = target / ".agents" / "management" / "evidence" / "install-journal"
    if journal_dir.is_dir():
        interrupted = list(journal_dir.glob("interrupted-*.json"))
        if interrupted:
            check["findings"].append({
                "severity": "warning",
                "message": f"{len(interrupted)} interrupted install markers (may indicate incomplete adoption)",
            })

    return check


def _check_baseline_integrity(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check baseline rules integrity."""
    check = {
        "status": "GREEN",
        "findings": [],
        "metrics": {},
    }

    baseline_dir = layout.baseline_rules
    if not baseline_dir.is_dir():
        check["status"] = "RED"
        check["findings"].append({
            "severity": "critical",
            "message": "No baseline rules directory found",
        })
        return check

    # Check key governance files exist
    key_files = [
        "AGENTS.md",
        "governance/core/quality/quality-gates.md",
        "governance/profiles/languages",
        "governance/architecture",
        "governance/security",
        "governance/execution",
    ]

    missing_key = []
    for kf in key_files:
        path = baseline_dir / kf
        if not path.exists():
            missing_key.append(kf)

    check["metrics"]["key_baseline_components"] = f"{len(key_files) - len(missing_key)}/{len(key_files)}"

    if missing_key:
        check["status"] = "YELLOW"
        check["findings"].append({
            "severity": "warning",
            "message": f"Missing key baseline components: {', '.join(missing_key)}",
        })

    # Count total baseline files
    all_files = list(baseline_dir.rglob("*"))
    all_files = [f for f in all_files if f.is_file() and not is_runtime_excluded(str(f))]
    check["metrics"]["total_baseline_files"] = len(all_files)

    # Check for runtime artifacts inside baseline
    runtime_in_baseline = []
    for f in all_files:
        if is_runtime_excluded(str(f)):
            runtime_in_baseline.append(str(f.relative_to(baseline_dir)))

    if runtime_in_baseline:
        check["findings"].append({
            "severity": "warning",
            "message": f"Runtime artifacts found inside baseline: {', '.join(runtime_in_baseline[:5])}",
        })

    return check


def _resolve_framework_dictionary(target: Path) -> Path | None:
    """Find the framework dictionary index.json, adopted layout first."""
    candidates = [
        target / ".agents" / ".rules" / "governance" / "framework-dictionary" / "index.json",
        target / ".agents" / "governance" / "framework-dictionary" / "index.json",
    ]
    for c in candidates:
        if c.is_file():
            return c
    return None


def _load_framework_dictionary(target: Path) -> dict[str, Any] | None:
    """Load the framework dictionary index.json if available."""
    index_path = _resolve_framework_dictionary(target)
    if index_path is None:
        return None
    try:
        with open(index_path, "r", encoding="utf-8") as f:
            return json.load(f)
    except (json.JSONDecodeError, OSError):
        return None


def _term_matches_dir_name(term: str, dir_name: str) -> bool:
    """Check if a dictionary term matches a directory name (case-insensitive, plural-aware)."""
    # Direct match
    if term.lower() == dir_name.lower():
        return True
    # Singular/plural: Contracts matches Contract, Repositories matches Repository
    singular = term
    plural_dir = dir_name.rstrip("s") if dir_name.endswith("s") else dir_name
    if singular.lower() == plural_dir.lower():
        return True
    # Also try adding 's' to the term
    if (term + "s").lower() == dir_name.lower():
        return True
    return False


def _path_context_matches(target: Path, dir_path: str, allowed_contexts: list[str]) -> bool:
    """Check if the directory path context matches any allowed context from the dictionary.

    Checks if the relative path contains keywords that align with allowed contexts.
    """
    path_lower = dir_path.lower()
    path_parts = Path(dir_path).parts

    # Build a set of context keywords from the allowed contexts
    context_keywords = set()
    for ctx in allowed_contexts:
        for word in ctx.lower().split():
            if len(word) > 3:  # Skip short words like "in", "of", "for"
                context_keywords.add(word)

    # Check if any path part or path segment matches context keywords
    for part in path_parts:
        part_lower = part.lower()
        # Direct keyword match in path
        if part_lower in context_keywords:
            return True
        # Check if the full path contains context-relevant segments
        for kw in context_keywords:
            if kw in part_lower:
                return True

    # Special case: framework-dictionary terms in components/ are generally legitimate
    # because components follow capability-based naming
    if "components" in path_parts:
        return True

    # Special case: terms in tests/ following the component structure are acceptable
    # (tests mirror production structure)
    if "tests" in path_parts:
        return True

    # Special case: EVIDENCE archive paths are historical, not active violations
    if "evidence" in path_parts and (".install-archive" in path_parts or "archive" in path_parts):
        return True

    return False


def _check_naming_compliance(target: Path, layout: LayoutDetector, verbose: bool) -> dict[str, Any]:
    """Check for forbidden directory names in the project using framework dictionary.

    Uses the framework dictionary to distinguish legitimate ecosystem usage
    from generic dumping ground naming.
    """
    check = {
        "status": "GREEN",
        "findings": [],
        "metrics": {},
        "violations": [],
        "dictionary_exceptions": [],
    }

    # Load framework dictionary
    dictionary = _load_framework_dictionary(target)
    dict_terms = {}
    if dictionary:
        dict_terms = dictionary.get("terms", {})

    forbidden_found = []
    dictionary_allowed = []
    suspicious = []

    for root, dirs, _files in os.walk(target):
        # Skip hidden dirs, git, vendor, node_modules
        skip_dirs = {".git", "vendor", "node_modules", ".qoder"}
        root_parts = Path(root).parts
        if any(sd in root_parts for sd in skip_dirs):
            continue

        for d in dirs:
            if d in FORBIDDEN_DIRECTORY_NAMES:
                full_path = os.path.join(root, d)
                rel = os.path.relpath(full_path, target)

                # Check against framework dictionary
                dict_match = None
                for term, entry in dict_terms.items():
                    if _term_matches_dir_name(term, d):
                        dict_match = (term, entry)
                        break

                if dict_match:
                    term_name, entry = dict_match
                    allowed_contexts = entry.get("allowed_contexts", [])
                    if _path_context_matches(target, rel, allowed_contexts):
                        # GREEN: legitimate ecosystem usage in correct context
                        dictionary_allowed.append({
                            "path": rel,
                            "name": d,
                            "term": term_name,
                            "reason": f"Legitimate {entry.get('classification', 'ecosystem')} term in allowed context",
                        })
                    else:
                        # YELLOW: term exists but context is suspicious
                        suspicious.append({
                            "path": rel,
                            "name": d,
                            "term": term_name,
                            "reason": f"Dictionary term used outside allowed contexts",
                        })
                elif d in ALLOWED_ECOSYSTEM_TERMS:
                    # Fallback: legacy ecosystem allowance
                    dictionary_allowed.append({
                        "path": rel,
                        "name": d,
                        "term": d,
                        "reason": "Allowed ecosystem term (legacy allowance)",
                    })
                else:
                    # RED: forbidden name with no dictionary justification
                    forbidden_found.append({"path": rel, "name": d})

    check["metrics"]["forbidden_dirs_found"] = len(forbidden_found)
    check["metrics"]["dictionary_exceptions"] = len(dictionary_allowed)
    check["metrics"]["suspicious_contexts"] = len(suspicious)
    check["violations"] = forbidden_found[:20]
    check["dictionary_exceptions"] = dictionary_allowed[:20]

    # Build overall status
    if forbidden_found:
        check["status"] = "YELLOW"
        sample = ", ".join(v["path"] for v in forbidden_found[:5])
        check["findings"].append({
            "severity": "warning",
            "message": f"{len(forbidden_found)} forbidden directory name(s) with no dictionary justification: {sample}",
        })

    if suspicious:
        if check["status"] == "GREEN":
            check["status"] = "YELLOW"
        sample = ", ".join(v["path"] for v in suspicious[:3])
        check["findings"].append({
            "severity": "warning",
            "message": f"{len(suspicious)} dictionary term(s) used in suspicious context: {sample}",
        })

    if dictionary_allowed and verbose:
        sample = ", ".join(v["path"] for v in dictionary_allowed[:3])
        check["findings"].append({
            "severity": "info",
            "message": f"{len(dictionary_allowed)} directory name(s) allowed via framework dictionary: {sample}",
        })

    return check


def main():
    if len(sys.argv) < 2:
        print("Usage: python3 agent-harness-diagnose.py /path/to/project [--verbose] [--json]")
        sys.exit(1)

    target = sys.argv[1]
    verbose = "--verbose" in sys.argv
    json_output = "--json" in sys.argv

    if not os.path.isdir(target):
        print(f"ERROR: {target} is not a directory")
        sys.exit(1)

    result = diagnose(target, verbose)

    if json_output:
        print(json.dumps(result, indent=2))
    else:
        _print_human_readable(result, verbose)

    # Exit code based on status
    status = result.get("overall_status", "UNKNOWN")
    if status == "RED":
        sys.exit(2)
    elif status == "YELLOW":
        sys.exit(1)
    else:
        sys.exit(0)


def _print_human_readable(result: dict, verbose: bool):
    """Print human-readable diagnostic report."""
    print("=" * 60)
    print("AGENT HARNESS V6 DIAGNOSTICS")
    print("=" * 60)
    print(f"Target: {result['target']}")
    print(f"Timestamp: {result['timestamp']}")
    print(f"Detected Layout: {result.get('detected_layout', 'unknown')}")
    print(f"Overall Status: {result['overall_status']}")
    print()

    # Print check summaries
    for check_name, check_data in result["checks"].items():
        status = check_data.get("status", "UNKNOWN")
        print(f"[{status}] {check_name}")

        if verbose and "metrics" in check_data:
            for key, value in check_data["metrics"].items():
                print(f"       {key}: {value}")

        if verbose and "findings" in check_data:
            for finding in check_data["findings"]:
                sev = finding.get("severity", "info")
                msg = finding.get("message", "")
                print(f"       [{sev}] {msg}")

        print()

    # Print all findings
    if result["findings"]:
        print("FINDINGS:")
        for f in result["findings"]:
            sev = f.get("severity", "info")
            msg = f.get("message", "")
            print(f"  [{sev.upper()}] {msg}")
        print()

    # Print recommendations
    if result["recommendations"]:
        print("RECOMMENDATIONS:")
        for r in result["recommendations"]:
            print(f"  - {r}")
        print()

    print("=" * 60)


if __name__ == "__main__":
    main()
