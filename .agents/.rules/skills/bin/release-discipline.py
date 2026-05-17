#!/usr/bin/env python3
# release-discipline.py — V5.1.0 Phase 9: Release & Evolution Discipline
#
# Managers:
#   1. SemanticVersionManager
#   2. RuntimeCompatibilityGuarantees
#   3. MigrationContractManager
#   4. UpgradeDowngradeStrategy
#   5. ChangelogManager
#   6. DeprecationManager
#   7. GovernanceSchemaVersioner
#   8. ReleasePackager
#
# CLI:
#   python3 release-discipline.py version [--dir <dir>]
#   python3 release-discipline.py bump major|minor|patch [--dir <dir>]
#   python3 release-discipline.py compat [--dir <dir>]
#   python3 release-discipline.py migrate [--dir <dir>]
#   python3 release-discipline.py upgrade <version> [--dir <dir>]
#   python3 release-discipline.py changelog [--dir <dir>]
#   python3 release-discipline.py deprecations [--dir <dir>]
#   python3 release-discipline.py schema [--dir <dir>]
#   python3 release-discipline.py release <version> [--dir <dir>]

import argparse
import json
import os
import re
import sys
from datetime import datetime, timezone


# ---------------------------------------------------------------------------
# Path helpers
# ---------------------------------------------------------------------------

def _base_dir(cli_dir=None):
    """Return the root base directory for evidence storage."""
    if cli_dir:
        return cli_dir
    # Default: .agents/management relative to script location
    script_dir = os.path.dirname(os.path.abspath(__file__))
    # .agents/skills/bin -> .agents/management
    return os.path.normpath(os.path.join(script_dir, "..", "..", "management"))


def _ensure_dir(path):
    os.makedirs(path, exist_ok=True)


def _evidence_dir(base):
    return os.path.join(base, "evidence")


def _generated_dir(base):
    return os.path.join(_evidence_dir(base), "generated")


def _migrations_dir(base):
    return os.path.join(_evidence_dir(base), "migrations")


def _read_json(path):
    if not os.path.exists(path):
        return None
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)


def _write_json(path, data):
    _ensure_dir(os.path.dirname(path))
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)


def _write_text(path, text):
    _ensure_dir(os.path.dirname(path))
    with open(path, "w", encoding="utf-8") as f:
        f.write(text)


def _read_text(path):
    if not os.path.exists(path):
        return None
    with open(path, "r", encoding="utf-8") as f:
        return f.read()


# ---------------------------------------------------------------------------
# 1. SemanticVersionManager
# ---------------------------------------------------------------------------

SEMVER_RE = re.compile(r"^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-([\dA-Za-z-]+(?:\.[\dA-Za-z-]+)*))?(?:\+([\dA-Za-z-]+(?:\.[\dA-Za-z-]+)*))?$")

DEFAULT_VERSION = "5.1.0"


class SemanticVersionManager:
    """Manages semantic versioning for the governance platform."""

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._version_file = os.path.join(_generated_dir(self.base), "version.json")

    # -- public -----------------------------------------------------------

    def get_version(self):
        """Returns current version string."""
        data = _read_json(self._version_file)
        if data and "version" in data:
            return data["version"]
        # Initialize with default version
        _write_json(self._version_file, {
            "version": DEFAULT_VERSION,
            "initialized_at": datetime.now(timezone.utc).isoformat(),
        })
        return DEFAULT_VERSION

    def bump_version(self, part="patch"):
        """Bumps version and persists it."""
        current = self.get_version()
        m = SEMVER_RE.match(current)
        if not m:
            raise ValueError(f"Invalid version format: {current}")
        major, minor, patch = int(m.group(1)), int(m.group(2)), int(m.group(3))
        if part == "major":
            major += 1
            minor = 0
            patch = 0
        elif part == "minor":
            minor += 1
            patch = 0
        elif part == "patch":
            patch += 1
        else:
            raise ValueError(f"Unknown bump part: {part!r}")
        new_version = f"{major}.{minor}.{patch}"
        old_version = current
        _write_json(self._version_file, {
            "version": new_version,
            "previous_version": old_version,
            "bumped_at": datetime.now(timezone.utc).isoformat(),
            "bump_part": part,
        })
        return new_version

    def validate_version_format(self, version):
        """Validates semver format. Returns (bool, error_message)."""
        if SEMVER_RE.match(version):
            return True, None
        return False, f"Invalid semver format: {version!r} (expected MAJOR.MINOR.PATCH)"

    def compare_versions(self, v1, v2):
        """Compares two versions. Returns -1, 0, or 1."""
        t1 = self._parse_version_tuple(v1)
        t2 = self._parse_version_tuple(v2)
        if t1 < t2:
            return -1
        if t1 > t2:
            return 1
        return 0

    @staticmethod
    def _parse_version_tuple(version):
        """Parse a version string into a comparable tuple (supports 2 or 3 parts)."""
        m = SEMVER_RE.match(version)
        if m:
            return (int(m.group(1)), int(m.group(2)), int(m.group(3)))
        # Fallback: try simple MAJOR.MINOR or MAJOR.MINOR.PATCH
        parts = version.split(".")
        try:
            return tuple(int(p) for p in parts)
        except ValueError:
            raise ValueError(f"Invalid version: {version!r}")


# ---------------------------------------------------------------------------
# 2. RuntimeCompatibilityGuarantees
# ---------------------------------------------------------------------------

class RuntimeCompatibilityGuarantees:
    """Documents and checks runtime compatibility guarantees between versions."""

    # Default matrix seeded with known versions
    DEFAULT_MATRIX = {
        "5.0.0": {"python_min": "3.10", "python_max": None, "governance_schemas": ["5.0"], "breaking_changes": False, "notes": "Initial v5 platform release"},
        "5.1.0": {"python_min": "3.10", "python_max": None, "governance_schemas": ["5.0", "5.1"], "breaking_changes": False, "notes": "Phase 9 release discipline"},
    }

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._matrix_file = os.path.join(_generated_dir(self.base), "compatibility-matrix.json")
        self._version_mgr = SemanticVersionManager(base_dir)

    # -- public -----------------------------------------------------------

    def _load_matrix(self):
        data = _read_json(self._matrix_file)
        if data and "matrix" in data:
            return data["matrix"]
        # Initialize with default matrix
        self._save_matrix(dict(self.DEFAULT_MATRIX))
        return dict(self.DEFAULT_MATRIX)

    def _save_matrix(self, matrix):
        _write_json(self._matrix_file, {
            "matrix": matrix,
            "updated_at": datetime.now(timezone.utc).isoformat(),
        })

    def check_runtime_compat(self, version):
        """Checks if a given version is compatible with the current runtime."""
        matrix = self._load_matrix()
        entry = matrix.get(version)
        resolved_version = version
        if entry is None:
            # Fall back to major.minor entry (e.g., 5.1.1 -> 5.1.0)
            m = SEMVER_RE.match(version)
            if m:
                fallback = f"{m.group(1)}.{m.group(2)}.0"
                entry = matrix.get(fallback)
                resolved_version = fallback
        if entry is None:
            return {
                "version": version,
                "compatible": False,
                "reason": f"Version {version} not found in compatibility matrix",
            }
        current_python = f"{sys.version_info.major}.{sys.version_info.minor}"
        py_min = entry.get("python_min")
        py_max = entry.get("python_max")
        compat = True
        reasons = []
        if py_min:
            if self._version_mgr.compare_versions(current_python, py_min) < 0:
                compat = False
                reasons.append(f"Python {current_python} < minimum {py_min}")
        if py_max:
            if self._version_mgr.compare_versions(current_python, py_max) > 0:
                compat = False
                reasons.append(f"Python {current_python} > maximum {py_max}")
        return {
            "version": version,
            "compatible": compat,
            "python_version": current_python,
            "reasons": reasons if not compat else ["All checks passed"],
            "entry": entry,
        }

    def get_compatibility_matrix(self):
        """Returns the full compatibility matrix."""
        return self._load_matrix()

    def check_governance_schema_compat(self):
        """Checks governance schema compatibility for the current version."""
        current = self._version_mgr.get_version()
        matrix = self._load_matrix()
        if current not in matrix:
            return {
                "version": current,
                "schema_compatible": False,
                "reason": "Version not in matrix",
            }
        entry = matrix[current]
        schemas = entry.get("governance_schemas", [])
        return {
            "version": current,
            "schema_compatible": True,
            "supported_schemas": schemas,
        }


# ---------------------------------------------------------------------------
# 3. MigrationContractManager
# ---------------------------------------------------------------------------

class MigrationContractManager:
    """Manages migration contracts between versions."""

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._migrations_dir = _migrations_dir(self.base)
        self._registry_file = os.path.join(self._migrations_dir, "registry.json")

    # -- public -----------------------------------------------------------

    def _load_registry(self):
        data = _read_json(self._registry_file)
        if data:
            return data
        # Initialize with empty registry
        registry = {"migrations": [], "applied": []}
        self._save_registry(registry)
        return registry

    def _save_registry(self, registry):
        _write_json(self._registry_file, registry)

    def create_migration(self, from_version, to_version, steps):
        """Creates a migration contract."""
        migration_id = f"{from_version}_to_{to_version}"
        migration = {
            "id": migration_id,
            "from_version": from_version,
            "to_version": to_version,
            "steps": steps,
            "created_at": datetime.now(timezone.utc).isoformat(),
            "applied": False,
        }
        path = os.path.join(self._migrations_dir, f"{migration_id}.json")
        _write_json(path, migration)
        registry = self._load_registry()
        registry["migrations"].append(migration_id)
        self._save_registry(registry)
        return migration_id

    def apply_migrations(self):
        """Applies all pending migrations."""
        pending = self.get_pending_migrations()
        applied = []
        registry = self._load_registry()
        for mid in pending:
            path = os.path.join(self._migrations_dir, f"{mid}.json")
            data = _read_json(path)
            if data:
                data["applied"] = True
                data["applied_at"] = datetime.now(timezone.utc).isoformat()
                _write_json(path, data)
                if mid not in registry["applied"]:
                    registry["applied"].append(mid)
                applied.append(mid)
        self._save_registry(registry)
        return applied

    def get_pending_migrations(self):
        """Lists unapplied migrations."""
        registry = self._load_registry()
        applied = set(registry.get("applied", []))
        all_migrations = registry.get("migrations", [])
        return [m for m in all_migrations if m not in applied]

    def validate_migration_applied(self, migration_id):
        """Checks if a migration was applied."""
        path = os.path.join(self._migrations_dir, f"{migration_id}.json")
        data = _read_json(path)
        if data is None:
            return False, f"Migration {migration_id!r} not found"
        return data.get("applied", False), None


# ---------------------------------------------------------------------------
# 4. UpgradeDowngradeStrategy
# ---------------------------------------------------------------------------

class UpgradeDowngradeStrategy:
    """Handles upgrade and downgrade planning."""

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._plan_file = os.path.join(_generated_dir(self.base), "upgrade-plan.json")
        self._version_mgr = SemanticVersionManager(base_dir)
        self._compat_mgr = RuntimeCompatibilityGuarantees(base_dir)
        self._migration_mgr = MigrationContractManager(base_dir)

    # -- public -----------------------------------------------------------

    def plan_upgrade(self, from_version, to_version):
        """Plans an upgrade path."""
        cmp = self._version_mgr.compare_versions(from_version, to_version)
        if cmp >= 0:
            return {"error": f"Cannot upgrade: {from_version} >= {to_version}"}
        steps = self._compute_steps(from_version, to_version)
        safe = self.check_upgrade_safe(from_version, to_version)
        plan = {
            "type": "upgrade",
            "from_version": from_version,
            "to_version": to_version,
            "safe": safe["safe"],
            "warnings": safe.get("warnings", []),
            "steps": steps,
            "planned_at": datetime.now(timezone.utc).isoformat(),
        }
        _write_json(self._plan_file, plan)
        return plan

    def plan_downgrade(self, from_version, to_version):
        """Plans a downgrade path."""
        cmp = self._version_mgr.compare_versions(from_version, to_version)
        if cmp <= 0:
            return {"error": f"Cannot downgrade: {from_version} <= {to_version}"}
        steps = self._compute_steps(to_version, from_version)
        steps.reverse()
        safe = self.check_downgrade_safe(from_version, to_version)
        plan = {
            "type": "downgrade",
            "from_version": from_version,
            "to_version": to_version,
            "safe": safe["safe"],
            "warnings": safe.get("warnings", []),
            "steps": steps,
            "planned_at": datetime.now(timezone.utc).isoformat(),
        }
        _write_json(self._plan_file, plan)
        return plan

    def check_upgrade_safe(self, from_version, to_version):
        """Checks if an upgrade is safe."""
        warnings = []
        compat = self._compat_mgr.check_runtime_compat(to_version)
        if not compat["compatible"]:
            reason_items = compat.get("reasons", [])
            if not reason_items and compat.get("reason"):
                reason_items = [compat["reason"]]
            warnings.append(f"Runtime compatibility issue: {reason_items}")
        matrix = self._compat_mgr.get_compatibility_matrix()
        if to_version in matrix:
            entry = matrix[to_version]
            if entry.get("breaking_changes"):
                warnings.append(f"Version {to_version} has breaking changes")
        return {
            "safe": len(warnings) == 0,
            "warnings": warnings,
        }

    def check_downgrade_safe(self, from_version, to_version):
        """Checks if a downgrade is safe."""
        warnings = []
        compat = self._compat_mgr.check_runtime_compat(to_version)
        if not compat["compatible"]:
            reason_items = compat.get("reasons", [])
            if not reason_items and compat.get("reason"):
                reason_items = [compat["reason"]]
            warnings.append(f"Runtime compatibility issue: {reason_items}")
        # Check if from_version introduced breaking changes that to_version doesn't support
        matrix = self._compat_mgr.get_compatibility_matrix()
        if from_version in matrix and to_version in matrix:
            from_schemas = set(matrix[from_version].get("governance_schemas", []))
            to_schemas = set(matrix[to_version].get("governance_schemas", []))
            unsupported = from_schemas - to_schemas
            if unsupported:
                warnings.append(f"Downgrade would lose support for schemas: {sorted(unsupported)}")
        return {
            "safe": len(warnings) == 0,
            "warnings": warnings,
        }

    # -- internal ---------------------------------------------------------

    def _compute_steps(self, from_v, to_v):
        """Compute intermediate steps between two versions."""
        try:
            f_parts = SemanticVersionManager._parse_version_tuple(from_v)
            t_parts = SemanticVersionManager._parse_version_tuple(to_v)
        except ValueError:
            return ["Manual intervention required"]
        # Pad to 3 elements
        while len(f_parts) < 3:
            f_parts = f_parts + (0,)
        while len(t_parts) < 3:
            t_parts = t_parts + (0,)
        f_major, f_minor, f_patch = f_parts
        t_major, t_minor, t_patch = t_parts
        steps = []
        # Major bumps
        for mj in range(f_major + 1, t_major + 1):
            steps.append(f"Bump major to {mj}.0.0")
        if t_major > f_major:
            f_minor = 0
            f_patch = 0
        # Minor bumps
        for mn in range(f_minor + 1, t_minor + 1):
            steps.append(f"Bump minor to {t_major}.{mn}.0")
        if t_minor > f_minor:
            f_patch = 0
        # Patch bumps
        for pt in range(f_patch + 1, t_patch + 1):
            steps.append(f"Bump patch to {t_major}.{t_minor}.{pt}")
        if not steps:
            steps = ["No version steps needed"]
        return steps


# ---------------------------------------------------------------------------
# 5. ChangelogManager
# ---------------------------------------------------------------------------

VALID_CHANGE_TYPES = {"added", "changed", "deprecated", "removed", "fixed", "security"}


class ChangelogManager:
    """Manages changelog entries."""

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._changelog_file = os.path.join(_generated_dir(self.base), "CHANGELOG.md")
        self._entries_file = os.path.join(_generated_dir(self.base), "changelog-entries.json")

    # -- public -----------------------------------------------------------

    def add_entry(self, version, entry_type, description):
        """Adds a changelog entry."""
        if entry_type not in VALID_CHANGE_TYPES:
            raise ValueError(f"Invalid change type: {entry_type!r}. Must be one of {sorted(VALID_CHANGE_TYPES)}")
        valid, err = SemanticVersionManager(self.base).validate_version_format(version)
        if not valid:
            raise ValueError(err)
        entry = {
            "version": version,
            "type": entry_type,
            "description": description,
            "date": datetime.now(timezone.utc).strftime("%Y-%m-%d"),
        }
        entries = self._load_entries()
        entries.append(entry)
        _write_json(self._entries_file, {"entries": entries})
        return entry

    def generate_changelog(self):
        """Generates full changelog as Markdown."""
        entries = self._load_entries()
        # Group by version
        by_version = {}
        for e in entries:
            by_version.setdefault(e["version"], []).append(e)
        # Sort versions descending
        sorted_versions = sorted(by_version.keys(),
                                 key=lambda v: tuple(int(x) for x in v.split(".")[:3]),
                                 reverse=True)
        lines = ["# Changelog\n"]
        for ver in sorted_versions:
            lines.append(f"\n## [{ver}] - {entries[0]['date'] if by_version[ver] else 'unknown'}\n")
            # Group by type
            by_type = {}
            for e in by_version[ver]:
                by_type.setdefault(e["type"], []).append(e["description"])
            for t in sorted(by_type.keys()):
                lines.append(f"\n### {t.capitalize()}\n")
                for desc in by_type[t]:
                    lines.append(f"- {desc}")
        if not entries:
            lines.append("\nNo entries yet.\n")
        text = "\n".join(lines) + "\n"
        _write_text(self._changelog_file, text)
        return text

    def get_changes_since(self, version):
        """Gets changes since a given version."""
        entries = self._load_entries()
        cmp = SemanticVersionManager(self.base).compare_versions
        return [e for e in entries if cmp(e["version"], version) > 0]

    # -- internal ---------------------------------------------------------

    def _load_entries(self):
        data = _read_json(self._entries_file)
        if data and "entries" in data:
            return data["entries"]
        # Initialize with empty entries list
        _write_json(self._entries_file, {"entries": [], "initialized_at": datetime.now(timezone.utc).isoformat()})
        return []


# ---------------------------------------------------------------------------
# 6. DeprecationManager
# ---------------------------------------------------------------------------

class DeprecationManager:
    """Manages feature deprecations."""

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._deprecations_file = os.path.join(_generated_dir(self.base), "deprecations.json")

    # -- public -----------------------------------------------------------

    def deprecate_feature(self, name, version, replacement, removal_version):
        """Marks a feature as deprecated."""
        deprecation = {
            "name": name,
            "deprecated_in": version,
            "replacement": replacement,
            "removal_version": removal_version,
            "deprecated_at": datetime.now(timezone.utc).isoformat(),
            "status": "deprecated",
        }
        items = self._load_deprecations()
        # Update existing or append
        for i, item in enumerate(items):
            if item["name"] == name:
                items[i] = deprecation
                _write_json(self._deprecations_file, {"deprecations": items})
                return deprecation
        items.append(deprecation)
        _write_json(self._deprecations_file, {"deprecations": items})
        return deprecation

    def get_deprecations(self):
        """Lists active deprecations."""
        items = self._load_deprecations()
        return [d for d in items if d.get("status") == "deprecated"]

    def check_deprecated_usage(self):
        """Checks for deprecated feature usage (static analysis placeholder)."""
        items = self.get_deprecations()
        results = []
        for dep in items:
            results.append({
                "feature": dep["name"],
                "deprecated_in": dep["deprecated_in"],
                "replacement": dep["replacement"],
                "removal_version": dep["removal_version"],
                "status": "deprecated",
            })
        return results

    def get_removal_schedule(self):
        """Shows upcoming removals sorted by removal version."""
        items = self.get_deprecations()
        schedule = []
        for dep in items:
            schedule.append({
                "feature": dep["name"],
                "removal_version": dep["removal_version"],
                "deprecated_in": dep["deprecated_in"],
            })
        schedule.sort(key=lambda x: tuple(int(v) for v in x["removal_version"].split(".")[:3]))
        return schedule

    # -- internal ---------------------------------------------------------

    def _load_deprecations(self):
        data = _read_json(self._deprecations_file)
        if data and "deprecations" in data:
            return data["deprecations"]
        # Initialize with empty deprecations list
        _write_json(self._deprecations_file, {"deprecations": [], "initialized_at": datetime.now(timezone.utc).isoformat()})
        return []


# ---------------------------------------------------------------------------
# 7. GovernanceSchemaVersioner
# ---------------------------------------------------------------------------

class GovernanceSchemaVersioner:
    """Governance schema versioning."""

    DEFAULT_SCHEMA_VERSION = "5.1"

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._schema_file = os.path.join(_generated_dir(self.base), "schema-version.json")
        self._version_mgr = SemanticVersionManager(base_dir)

    # -- public -----------------------------------------------------------

    def get_schema_version(self):
        """Returns current governance schema version."""
        data = _read_json(self._schema_file)
        if data and "schema_version" in data:
            return data["schema_version"]
        # Initialize with default schema version
        _write_json(self._schema_file, {
            "schema_version": self.DEFAULT_SCHEMA_VERSION,
            "initialized_at": datetime.now(timezone.utc).isoformat(),
        })
        return self.DEFAULT_SCHEMA_VERSION

    def validate_schema_compatibility(self):
        """Validates schema compatibility for the current version."""
        current_version = self._version_mgr.get_version()
        schema_version = self.get_schema_version()
        # Extract major.minor from version
        m = SEMVER_RE.match(current_version)
        if not m:
            return {"compatible": False, "reason": f"Invalid version: {current_version}"}
        version_major_minor = f"{m.group(1)}.{m.group(2)}"
        compatible = (version_major_minor == schema_version)
        return {
            "version": current_version,
            "schema_version": schema_version,
            "compatible": compatible,
        }

    def check_schema_drift(self):
        """Checks for schema drift between expected and actual."""
        schema_version = self.get_schema_version()
        expected = self.DEFAULT_SCHEMA_VERSION
        drift = schema_version != expected
        return {
            "expected_schema_version": expected,
            "actual_schema_version": schema_version,
            "drift_detected": drift,
            "status": "drift" if drift else "synced",
        }


# ---------------------------------------------------------------------------
# 8. ReleasePackager
# ---------------------------------------------------------------------------

class ReleasePackager:
    """Packages releases."""

    REQUIRED_ARTIFACTS = [
        "version.json",
        "compatibility-matrix.json",
        "CHANGELOG.md",
        "deprecations.json",
        "schema-version.json",
    ]

    def __init__(self, base_dir=None):
        self.base = _base_dir(base_dir)
        self._version_mgr = SemanticVersionManager(base_dir)
        self._changelog_mgr = ChangelogManager(base_dir)
        self._compat_mgr = RuntimeCompatibilityGuarantees(base_dir)
        self._schema_mgr = GovernanceSchemaVersioner(base_dir)
        self._upgrade_mgr = UpgradeDowngradeStrategy(base_dir)

    # -- public -----------------------------------------------------------

    def create_release_manifest(self, version):
        """Creates a release manifest."""
        manifest = {
            "version": version,
            "created_at": datetime.now(timezone.utc).isoformat(),
            "artifacts": {},
            "schema_version": self._schema_mgr.get_schema_version(),
            "compatibility": self._compat_mgr.get_compatibility_matrix(),
            "changes": self._changelog_mgr.get_changes_since(version) if False else [],
        }
        gen_dir = _generated_dir(self.base)
        for artifact in self.REQUIRED_ARTIFACTS:
            path = os.path.join(gen_dir, artifact)
            manifest["artifacts"][artifact] = {
                "path": path,
                "exists": os.path.exists(path),
            }
        manifest_file = os.path.join(gen_dir, f"release-{version}.json")
        _write_json(manifest_file, manifest)
        return manifest

    def validate_release_artifacts(self):
        """Validates all release artifacts exist."""
        gen_dir = _generated_dir(self.base)
        results = {}
        all_present = True
        for artifact in self.REQUIRED_ARTIFACTS:
            path = os.path.join(gen_dir, artifact)
            exists = os.path.exists(path)
            results[artifact] = exists
            if not exists:
                all_present = False
        return {
            "valid": all_present,
            "artifacts": results,
            "missing": [a for a, present in results.items() if not present],
        }

    def generate_release_notes(self, version):
        """Generates release notes for a version."""
        cmp = self._version_mgr.compare_versions
        changes = self._changelog_mgr.get_changes_since(version)
        deprecations = DeprecationManager(self.base).get_deprecations()
        lines = [
            f"# Release {version}",
            f"",
            f"Generated at: {datetime.now(timezone.utc).isoformat()}",
            f"",
        ]
        # Group changes by type
        by_type = {}
        for c in changes:
            by_type.setdefault(c["type"], []).append(c)
        for t in sorted(by_type.keys()):
            lines.append(f"## {t.capitalize()}")
            lines.append("")
            for c in by_type[t]:
                lines.append(f"- {c['description']}")
            lines.append("")
        if deprecations:
            lines.append("## Deprecations")
            lines.append("")
            for d in deprecations:
                lines.append(f"- **{d['name']}**: use **{d['replacement']}** instead (removal in {d['removal_version']})")
            lines.append("")
        return "\n".join(lines)

    def check_release_ready(self):
        """Checks if ready for release."""
        issues = []
        # Check artifacts
        validation = self.validate_release_artifacts()
        if not validation["valid"]:
            issues.append(f"Missing artifacts: {validation['missing']}")
        # Check schema compatibility
        schema_compat = self._schema_mgr.validate_schema_compatibility()
        if not schema_compat["compatible"]:
            issues.append(f"Schema incompatibility: version {schema_compat['version']} vs schema {schema_compat['schema_version']}")
        # Check changelog has entries
        entries = self._changelog_mgr._load_entries()
        if not entries:
            issues.append("No changelog entries")
        return {
            "ready": len(issues) == 0,
            "issues": issues,
        }


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _cli_version(args):
    mgr = SemanticVersionManager(args.dir)
    version = mgr.get_version()
    valid, err = mgr.validate_version_format(version)
    print(f"Current version: {version}")
    if not valid:
        print(f"WARNING: {err}")
        return 1
    return 0


def _cli_bump(args):
    mgr = SemanticVersionManager(args.dir)
    old = mgr.get_version()
    new = mgr.bump_version(args.part)
    print(f"Version bumped: {old} -> {new}")
    return 0


def _cli_compat(args):
    mgr = RuntimeCompatibilityGuarantees(args.dir)
    current = SemanticVersionManager(args.dir).get_version()
    result = mgr.check_runtime_compat(current)
    print(f"Runtime compatibility for {result['version']}:")
    print(f"  Compatible: {result['compatible']}")
    if result.get("reasons"):
        for r in result["reasons"]:
            print(f"  - {r}")
    schema = mgr.check_governance_schema_compat()
    print(f"\nGovernance schema compatibility:")
    print(f"  Compatible: {schema['schema_compatible']}")
    if schema.get("supported_schemas"):
        print(f"  Supported schemas: {', '.join(schema['supported_schemas'])}")
    return 0


def _cli_migrate(args):
    mgr = MigrationContractManager(args.dir)
    pending = mgr.get_pending_migrations()
    if pending:
        print(f"Pending migrations ({len(pending)}):")
        for mid in pending:
            print(f"  - {mid}")
        print("\nApplying migrations...")
        applied = mgr.apply_migrations()
        print(f"Applied: {applied}")
    else:
        print("No pending migrations.")
    return 0


def _cli_upgrade(args):
    version_mgr = SemanticVersionManager(args.dir)
    current = version_mgr.get_version()
    strategy = UpgradeDowngradeStrategy(args.dir)
    plan = strategy.plan_upgrade(current, args.version)
    if "error" in plan:
        print(f"Error: {plan['error']}")
        return 1
    print(f"Upgrade plan: {current} -> {args.version}")
    print(f"  Safe: {plan['safe']}")
    if plan.get("warnings"):
        print("  Warnings:")
        for w in plan["warnings"]:
            print(f"    - {w}")
    print("  Steps:")
    for step in plan["steps"]:
        print(f"    - {step}")
    return 0


def _cli_changelog(args):
    mgr = ChangelogManager(args.dir)
    text = mgr.generate_changelog()
    print(text)
    return 0


def _cli_deprecations(args):
    mgr = DeprecationManager(args.dir)
    deps = mgr.get_deprecations()
    if deps:
        print(f"Active deprecations ({len(deps)}):")
        for d in deps:
            print(f"  - {d['name']} (deprecated in {d['deprecated_in']})")
            print(f"    Replacement: {d['replacement']}")
            print(f"    Removal: {d['removal_version']}")
    else:
        print("No active deprecations.")
    schedule = mgr.get_removal_schedule()
    if schedule:
        print("\nRemoval schedule:")
        for s in schedule:
            print(f"  - {s['feature']} -> removal in {s['removal_version']}")
    return 0


def _cli_schema(args):
    mgr = GovernanceSchemaVersioner(args.dir)
    version = mgr.get_schema_version()
    print(f"Schema version: {version}")
    compat = mgr.validate_schema_compatibility()
    print(f"  Compatible: {compat['compatible']}")
    drift = mgr.check_schema_drift()
    print(f"  Drift detected: {drift['drift_detected']} ({drift['status']})")
    return 0


def _cli_release(args):
    mgr = ReleasePackager(args.dir)
    version = args.version
    print(f"Release preparation for {version}...")
    # Create manifest
    manifest = mgr.create_release_manifest(version)
    print(f"  Manifest created: release-{version}.json")
    # Validate artifacts
    validation = mgr.validate_release_artifacts()
    print(f"  Artifacts valid: {validation['valid']}")
    if validation.get("missing"):
        print(f"    Missing: {', '.join(validation['missing'])}")
    # Check readiness
    readiness = mgr.check_release_ready()
    print(f"  Release ready: {readiness['ready']}")
    if readiness.get("issues"):
        for issue in readiness["issues"]:
            print(f"    Issue: {issue}")
    # Generate notes
    notes = mgr.generate_release_notes(version)
    print(f"\n{notes}")
    return 0 if readiness["ready"] else 1


def main():
    parser = argparse.ArgumentParser(
        description="Phase 9: Release & Evolution Discipline",
    )
    parser.add_argument("--dir", default=None, help="Base directory override")
    subparsers = parser.add_subparsers(dest="command")

    subparsers.add_parser("version", help="Show current version")
    bump_p = subparsers.add_parser("bump", help="Bump version")
    bump_p.add_argument("part", choices=["major", "minor", "patch"])
    subparsers.add_parser("compat", help="Check runtime compatibility")
    subparsers.add_parser("migrate", help="Apply pending migrations")
    upgrade_p = subparsers.add_parser("upgrade", help="Plan upgrade")
    upgrade_p.add_argument("version", help="Target version")
    subparsers.add_parser("changelog", help="Generate changelog")
    subparsers.add_parser("deprecations", help="Show deprecations")
    subparsers.add_parser("schema", help="Show schema version info")
    release_p = subparsers.add_parser("release", help="Prepare release")
    release_p.add_argument("version", help="Release version")

    args = parser.parse_args()
    if not args.command:
        parser.print_help()
        return 1

    handlers = {
        "version": _cli_version,
        "bump": _cli_bump,
        "compat": _cli_compat,
        "migrate": _cli_migrate,
        "upgrade": _cli_upgrade,
        "changelog": _cli_changelog,
        "deprecations": _cli_deprecations,
        "schema": _cli_schema,
        "release": _cli_release,
    }
    handler = handlers.get(args.command)
    if not handler:
        parser.print_help()
        return 1
    return handler(args)


if __name__ == "__main__":
    sys.exit(main())
