#!/usr/bin/env python3
"""
Artifact Validator - Validates sub-agent artifacts against the artifact contract.

Usage:
    python3 validate-artifact.py <artifact_file.json>
    python3 validate-artifact.py --strict <artifact_file.json>
    echo '{"artifact_version": "1.0.0", ...}' | python3 validate-artifact.py -
"""

from __future__ import annotations

import argparse
import json
import sys
from datetime import datetime
from pathlib import Path
from typing import Any


ARTIFACT_VERSION = "1.0.0"

REQUIRED_FIELDS = [
    "artifact_version",
    "agent_role",
    "task_id",
    "session_id",
    "timestamp",
    "goal",
    "task_type",
    "status",
    "recommended_next_agent",
]

ROLE_REQUIREMENTS = {
    "mapper": {
        "required_fields": ["relevant_files", "files_discovered"],
        "status_on_failure": "failed",
    },
    "researcher": {
        "required_fields": ["docs_findings"],
        "status_on_failure": "failed",
    },
    "executor": {
        "required_fields": ["code_changes", "acceptance_criteria"],
        "status_on_failure": "failed",
    },
    "reviewer": {
        "required_fields": ["risks", "acceptance_criteria"],
        "status_on_failure": "failed",
    },
}

VALID_STATUSES = {"success", "failed", "partial", "escalated"}
VALID_TASK_TYPES = {"map", "research", "execute", "review"}
VALID_ROLES = {"mapper", "researcher", "executor", "reviewer"}
VALID_NEXT_AGENTS = {"codex", "reviewer", "none", "escalate"}
VALID_SEVERITIES = {"low", "medium", "high", "critical"}
VALID_RISK_CATEGORIES = {"security", "performance", "regression", "complexity"}
VALID_REVIEW_DECISIONS = {"allow", "block", "conditional", "escalate"}


class ValidationError(Exception):
    def __init__(self, errors: list[str]):
        self.errors = errors
        super().__init__(json.dumps(errors, indent=2))


class ArtifactValidator:
    def __init__(self, strict: bool = False):
        self.strict = strict
        self.errors: list[str] = []
        self.warnings: list[str] = []

    def validate(self, artifact: dict[str, Any]) -> tuple[bool, list[str], list[str]]:
        self.errors = []
        self.warnings = []

        self._validate_schema(artifact)
        self._validate_types(artifact)
        self._validate_role_requirements(artifact)
        self._validate_enum_values(artifact)
        self._validate_arrays(artifact)
        self._validate_nested(artifact)

        if self.strict:
            self._validate_strict(artifact)

        is_valid = len(self.errors) == 0
        return is_valid, self.errors, self.warnings

    def _add_error(self, message: str) -> None:
        self.errors.append(message)

    def _add_warning(self, message: str) -> None:
        self.warnings.append(message)

    def _validate_schema(self, artifact: dict[str, Any]) -> None:
        for field in REQUIRED_FIELDS:
            if field not in artifact:
                self._add_error(f"Missing required field: {field}")

    def _validate_types(self, artifact: dict[str, Any]) -> None:
        if "artifact_version" in artifact and not isinstance(
            artifact["artifact_version"], str
        ):
            self._add_error("artifact_version must be a string")

        if "task_id" in artifact and not isinstance(artifact["task_id"], str):
            self._add_error("task_id must be a string")

        if "session_id" in artifact and not isinstance(artifact["session_id"], str):
            self._add_error("session_id must be a string")

        if "timestamp" in artifact and not isinstance(artifact["timestamp"], str):
            self._add_error("timestamp must be a string")

        if "goal" in artifact and not isinstance(artifact["goal"], str):
            self._add_error("goal must be a string")

    def _validate_role_requirements(self, artifact: dict[str, Any]) -> None:
        role = artifact.get("agent_role")
        if role not in ROLE_REQUIREMENTS:
            return

        requirements = ROLE_REQUIREMENTS[role]
        for field in requirements["required_fields"]:
            if field not in artifact:
                self._add_error(f"Role '{role}' requires field: {field}")

    def _validate_enum_values(self, artifact: dict[str, Any]) -> None:
        if "status" in artifact and artifact["status"] not in VALID_STATUSES:
            self._add_error(
                f"Invalid status '{artifact['status']}'. Must be one of: {VALID_STATUSES}"
            )

        if "task_type" in artifact and artifact["task_type"] not in VALID_TASK_TYPES:
            self._add_error(
                f"Invalid task_type '{artifact['task_type']}'. Must be one of: {VALID_TASK_TYPES}"
            )

        if "agent_role" in artifact and artifact["agent_role"] not in VALID_ROLES:
            self._add_error(
                f"Invalid agent_role '{artifact['agent_role']}'. Must be one of: {VALID_ROLES}"
            )

        if (
            "recommended_next_agent" in artifact
            and artifact["recommended_next_agent"] not in VALID_NEXT_AGENTS
        ):
            self._add_error(
                f"Invalid recommended_next_agent '{artifact['recommended_next_agent']}'. Must be one of: {VALID_NEXT_AGENTS}"
            )

    def _validate_arrays(self, artifact: dict[str, Any]) -> None:
        if "relevant_files" in artifact and not isinstance(
            artifact["relevant_files"], list
        ):
            self._add_error("relevant_files must be an array")

        if "excluded_files" in artifact and not isinstance(
            artifact["excluded_files"], list
        ):
            self._add_error("excluded_files must be an array")

        if "docs_findings" in artifact and not isinstance(
            artifact["docs_findings"], list
        ):
            self._add_error("docs_findings must be an array")

        if "risks" in artifact and not isinstance(artifact["risks"], list):
            self._add_error("risks must be an array")

    def _validate_nested(self, artifact: dict[str, Any]) -> None:
        if "constraints" in artifact:
            constraints = artifact["constraints"]
            if not isinstance(constraints, dict):
                self._add_error("constraints must be an object")
            else:
                if "max_tokens" in constraints:
                    if not isinstance(constraints["max_tokens"], int):
                        self._add_error("constraints.max_tokens must be an integer")

        if "code_changes" in artifact:
            code_changes = artifact["code_changes"]
            if not isinstance(code_changes, dict):
                self._add_error("code_changes must be an object")
            else:
                for field in ["files_modified", "files_created", "files_deleted"]:
                    if field in code_changes and not isinstance(
                        code_changes[field], list
                    ):
                        self._add_error(f"code_changes.{field} must be an array")

        if "metadata" in artifact:
            metadata = artifact["metadata"]
            if not isinstance(metadata, dict):
                self._add_error("metadata must be an object")

        self._validate_risks(artifact.get("risks", []))
        self._validate_docs_findings(artifact.get("docs_findings", []))

        if "review_decision" in artifact:
            decision = artifact["review_decision"]
            if decision not in VALID_REVIEW_DECISIONS:
                self._add_error(
                    f"Invalid review_decision '{decision}'. Must be one of: {VALID_REVIEW_DECISIONS}"
                )

    def _validate_risks(self, risks: list[dict[str, Any]]) -> None:
        for i, risk in enumerate(risks):
            if "severity" in risk and risk["severity"] not in VALID_SEVERITIES:
                self._add_error(
                    f"risks[{i}].severity must be one of: {VALID_SEVERITIES}"
                )

            if "category" in risk and risk["category"] not in VALID_RISK_CATEGORIES:
                self._add_error(
                    f"risks[{i}].category must be one of: {VALID_RISK_CATEGORIES}"
                )

    def _validate_docs_findings(self, findings: list[dict[str, Any]]) -> None:
        for i, finding in enumerate(findings):
            if "source" in finding and not isinstance(finding["source"], str):
                self._add_error(f"docs_findings[{i}].source must be a string")

            if "source_type" in finding:
                source_type = finding["source_type"]
                if source_type not in {"mcp", "web", "local"}:
                    self._add_error(
                        f"docs_findings[{i}].source_type must be 'mcp', 'web', or 'local'"
                    )

            if "relevance" in finding:
                relevance = finding["relevance"]
                if relevance not in {"high", "medium", "low"}:
                    self._add_error(
                        f"docs_findings[{i}].relevance must be 'high', 'medium', or 'low'"
                    )

    def _validate_strict(self, artifact: dict[str, Any]) -> None:
        if artifact.get("status") == "success" and not artifact.get(
            "acceptance_criteria"
        ):
            self._add_error("Status 'success' requires acceptance_criteria")

        if artifact.get("agent_role") == "reviewer":
            if not artifact.get("review_decision"):
                self._add_error("Reviewer artifacts require review_decision")

        if artifact.get("recommended_next_agent") == "none":
            if artifact.get("status") != "success":
                self._add_warning(
                    "recommended_next_agent is 'none' but status is not 'success'"
                )

        if artifact.get("risks") == [] and artifact.get("agent_role") == "reviewer":
            self._add_warning("Reviewer returned no risks - may have missed something")


def validate_artifact(
    artifact: dict[str, Any], strict: bool = False
) -> tuple[bool, list[str], list[str]]:
    validator = ArtifactValidator(strict=strict)
    return validator.validate(artifact)


def load_artifact(source: str) -> dict[str, Any]:
    if source == "-":
        return json.load(sys.stdin)

    path = Path(source)
    if not path.exists():
        raise FileNotFoundError(f"Artifact file not found: {source}")

    return json.loads(path.read_text(encoding="utf-8"))


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Validate sub-agent artifacts against the artifact contract"
    )
    parser.add_argument(
        "artifact",
        nargs="?",
        default="-",
        help="Path to artifact JSON file, or '-' for stdin",
    )
    parser.add_argument(
        "--strict",
        action="store_true",
        help="Enable strict validation (reject questionable patterns)",
    )
    parser.add_argument(
        "--warn-only",
        action="store_true",
        help="Exit with success even if warnings exist",
    )
    parser.add_argument(
        "--json",
        action="store_true",
        help="Output JSON format",
    )

    args = parser.parse_args()

    try:
        artifact = load_artifact(args.artifact)
    except FileNotFoundError as e:
        print(f"Error: {e}", file=sys.stderr)
        return 1
    except json.JSONDecodeError as e:
        print(f"Error: Invalid JSON - {e}", file=sys.stderr)
        return 1

    is_valid, errors, warnings = validate_artifact(artifact, strict=args.strict)

    output = {
        "valid": is_valid,
        "errors": errors,
        "warnings": warnings,
    }

    if args.json:
        print(json.dumps(output, indent=2))
    else:
        if warnings and not is_valid:
            print("WARNINGS:", file=sys.stderr)
            for w in warnings:
                print(f"  - {w}", file=sys.stderr)
            print("", file=sys.stderr)

        if not is_valid:
            print("VALIDATION FAILED:", file=sys.stderr)
            for e in errors:
                print(f"  - {e}", file=sys.stderr)
            return 1
        else:
            if warnings and not args.warn_only:
                print("VALIDATION PASSED (with warnings):")
                for w in warnings:
                    print(f"  - {w}")
            else:
                print("VALIDATION PASSED")

    return 0 if (is_valid and args.warn_only) else (0 if is_valid else 1)


if __name__ == "__main__":
    sys.exit(main())
