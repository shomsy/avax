#!/usr/bin/env python3
from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import sys
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any


SCHEMA_VERSION = "1.0.0"


SUBAGENT_CLUSTER_KEYWORDS: dict[str, list[str]] = {
    "core": [
        "auth",
        "session",
        "login",
        "token",
        "permission",
        "identity",
        "jwt",
        "oauth",
        "user",
    ],
    "data": [
        "db",
        "database",
        "schema",
        "migration",
        "model",
        "cache",
        "storage",
        "sql",
        "table",
    ],
    "interface": [
        "api",
        "route",
        "routing",
        "endpoint",
        "controller",
        "handler",
        "frontend",
        "backend",
        "ui",
        "cli",
    ],
    "delivery": [
        "test",
        "tests",
        "smoke",
        "deploy",
        "release",
        "rollback",
        "ci",
        "cd",
        "trace",
        "observability",
        "runtime",
    ],
    "docs": [
        "doc",
        "docs",
        "readme",
        "documentation",
        "guide",
        "how-to",
        "flow",
        "changelog",
    ],
    "security": [
        "security",
        "threat",
        "vulnerability",
        "secret",
        "abuse",
        "owasp",
        "cve",
    ],
}

SUBAGENT_CLIENT_HINTS: dict[str, list[str]] = {
    "research": ["cline", "opencode"],
    "parallel-build": ["opencode", "blackbox"],
}


def iso_timestamp() -> str:
    return datetime.now().astimezone().isoformat(timespec="seconds")


def sha12(value: str) -> str:
    return hashlib.sha256(value.encode("utf-8")).hexdigest()[:12]


def unique(items: list[str]) -> list[str]:
    seen: set[str] = set()
    ordered: list[str] = []
    for item in items:
        if not item or item in seen:
            continue
        seen.add(item)
        ordered.append(item)
    return ordered


def rules_root(project_root: Path) -> Path:
    mounted = project_root / ".agents" / ".rules"
    if mounted.is_dir():
        return mounted
    return project_root / ".agents"


def read_text(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8")
    except FileNotFoundError:
        return ""


def clean_scalar(raw: str | None) -> str | None:
    if raw is None:
        return None
    value = raw.strip()
    if not value:
        return None
    value = value.strip("`")
    if value in {"[replace]", "[declare explicitly]"}:
        return None
    if "[replace]" in value or "[declare explicitly]" in value:
        return None
    if value.startswith("__") and value.endswith("__"):
        return None
    if re.match(r"^\(e\.g\.,", value, flags=re.IGNORECASE):
        return None
    if value.lower() in {"none", "n/a"}:
        return None
    return value


def parse_list_field(raw: str | None) -> list[str]:
    value = clean_scalar(raw)
    if value is None:
        return []
    parts = [part.strip() for part in re.split(r",\s*", value) if part.strip()]
    normalized: list[str] = []
    for part in parts:
        item = part.strip("`")
        if item in {"[replace]", "[declare explicitly]"}:
            continue
        if item.startswith("__") and item.endswith("__"):
            continue
        if item.endswith(".md"):
            normalized.append(Path(item).stem)
        else:
            normalized.append(item)
    return unique(normalized)


FIELD_PATTERNS = {
    "validation_entrypoint": r"Canonical Validation Entrypoint",
    "dev_entrypoint": r"Canonical Local Development Entrypoint",
    "release_entrypoint": r"Canonical Release or Publish Entrypoint",
    "delivery_kind": r"Delivery Kind",
    "repository_profiles": r"Applied Repository Profiles",
    "languages": r"Languages",
    "frameworks": r"Frameworks Or Runtimes",
    "coding_profiles": r"Applied Coding Profiles",
    "architecture_profiles": r"Applied Architecture Profiles",
    "security_lanes": r"Security Lanes Required",
    "operations_lanes": r"Operations Lanes Required",
}


def extract_field(text: str, label_pattern: str) -> str | None:
    pattern = re.compile(
        rf"^\s*(?:[-*]|\d+\.)?\s*\*\*{label_pattern}\*\*:\s*(.+?)\s*$",
        re.IGNORECASE | re.MULTILINE,
    )
    match = pattern.search(text)
    if not match:
        return None
    return match.group(1).strip()


@dataclass
class StackState:
    delivery_kind: str | None
    repository_profiles: list[str]
    languages: list[str]
    frameworks: list[str]
    coding_profiles: list[str]
    architecture_profiles: list[str]
    security_lanes: list[str]
    operations_lanes: list[str]
    validation_entrypoint: str | None
    dev_entrypoint: str | None
    release_entrypoint: str | None


def parse_agents_stack(root_agents: Path) -> StackState:
    text = read_text(root_agents)
    raw: dict[str, str | None] = {
        key: extract_field(text, pattern) for key, pattern in FIELD_PATTERNS.items()
    }
    return StackState(
        delivery_kind=clean_scalar(raw["delivery_kind"]),
        repository_profiles=parse_list_field(raw["repository_profiles"]),
        languages=parse_list_field(raw["languages"]),
        frameworks=parse_list_field(raw["frameworks"]),
        coding_profiles=parse_list_field(raw["coding_profiles"]),
        architecture_profiles=parse_list_field(raw["architecture_profiles"]),
        security_lanes=parse_list_field(raw["security_lanes"]),
        operations_lanes=parse_list_field(raw["operations_lanes"]),
        validation_entrypoint=clean_scalar(raw["validation_entrypoint"]),
        dev_entrypoint=clean_scalar(raw["dev_entrypoint"]),
        release_entrypoint=clean_scalar(raw["release_entrypoint"]),
    )


def load_json(path: Path) -> dict[str, Any]:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except Exception:
        return {}


def prompt_clusters(prompt: str) -> list[str]:
    lower = prompt.lower()
    clusters: list[str] = []
    for cluster, keywords in SUBAGENT_CLUSTER_KEYWORDS.items():
        if any(keyword in lower for keyword in keywords):
            clusters.append(cluster)
    return clusters


def candidate_subagent_files(
    context_files: list[str], cluster_keywords: list[str], limit: int = 5
) -> list[str]:
    selected: list[str] = []
    normalized_keywords = [keyword.lower() for keyword in cluster_keywords]

    for path in context_files:
        lowered = path.lower()
        if any(keyword in lowered for keyword in normalized_keywords):
            selected.append(path)
        if len(selected) >= limit:
            return unique(selected)

    if not selected:
        return context_files[:limit]

    return unique(selected)


IGNORED_SCAN_DIRS = {
    ".git",
    ".idea",
    ".agent",
    ".agents",
    ".next",
    "__pycache__",
    "build",
    "coverage",
    "dist",
    "node_modules",
    "vendor",
}


def repo_contains_suffix(project_root: Path, suffixes: tuple[str, ...]) -> bool:
    normalized_suffixes = tuple(suffix.lower() for suffix in suffixes)

    for current_root, dirnames, filenames in os.walk(project_root):
        dirnames[:] = [
            dirname for dirname in dirnames if dirname not in IGNORED_SCAN_DIRS
        ]
        for filename in filenames:
            lowered = filename.lower()
            if lowered.endswith(normalized_suffixes):
                return True

    return False


def gather_repo_signals(
    project_root: Path, reusable_rules_root: Path
) -> dict[str, Any]:
    signals: dict[str, Any] = {
        "languages": [],
        "frameworks": [],
        "repository_profiles": [],
        "architecture_profiles": [],
        "system_kind": None,
    }

    if (
        (project_root / ".agents" / "governance").is_dir()
        and (project_root / "scaffolds").is_dir()
        and (project_root / "install-os.sh").is_file()
    ):
        signals["repository_profiles"].append("governance-source")

    if repo_contains_suffix(project_root, (".ts", ".tsx")):
        signals["languages"].append("typescript")
    elif repo_contains_suffix(project_root, (".js", ".jsx", ".mjs", ".cjs")):
        signals["languages"].append("javascript")

    if repo_contains_suffix(project_root, (".php",)):
        signals["languages"].append("php")

    if repo_contains_suffix(project_root, (".css", ".scss", ".sass", ".less")):
        signals["languages"].append("css")

    package_json = project_root / "package.json"
    if package_json.is_file():
        package_data = load_json(package_json)
        deps = {
            **package_data.get("dependencies", {}),
            **package_data.get("devDependencies", {}),
            **package_data.get("peerDependencies", {}),
        }
        if "typescript" not in signals["languages"]:
            signals["languages"].append("javascript")
        signals["languages"].append("nodejs")
        if "react" in deps:
            signals["frameworks"].append("react")
        if "next" in deps:
            signals["frameworks"].append("nextjs")
        if "express" in deps:
            signals["frameworks"].append("express")
        if signals["system_kind"] is None:
            signals["system_kind"] = (
                "web app" if "react" in deps or "next" in deps else "service"
            )

    composer_json = project_root / "composer.json"
    if composer_json.is_file():
        composer_data = load_json(composer_json)
        deps = {
            **composer_data.get("require", {}),
            **composer_data.get("require-dev", {}),
        }
        signals["languages"].append("php")
        if "laravel/framework" in deps or (project_root / "artisan").is_file():
            signals["frameworks"].append("laravel")
            signals["system_kind"] = signals["system_kind"] or "web app"

    for language in signals["languages"]:
        if (
            reusable_rules_root
            / "governance"
            / "architecture"
            / "profiles"
            / "languages"
            / f"{language}.md"
        ).is_file():
            signals["architecture_profiles"].append(language)

    for framework in signals["frameworks"]:
        if (
            reusable_rules_root
            / "governance"
            / "architecture"
            / "profiles"
            / "frameworks"
            / f"{framework}.md"
        ).is_file():
            signals["architecture_profiles"].append(framework)

    return {
        key: unique(value) if isinstance(value, list) else value
        for key, value in signals.items()
    }


LANE_KEYWORDS: dict[str, list[tuple[str, int]]] = {
    "governance": [
        ("agents.md", 5),
        ("governance", 5),
        ("policy", 4),
        ("rule", 4),
        ("profile", 4),
        ("precedence", 4),
        ("scaffold", 4),
        ("installer", 4),
        ("adapter", 4),
        ("hook", 4),
        ("workflow", 3),
        ("pipeline", 3),
    ],
    "review": [
        ("review", 5),
        ("audit", 4),
        ("inspect", 3),
        ("find issues", 5),
        ("code review", 5),
        ("strict review", 5),
    ],
    "documentation": [
        ("documentation", 5),
        ("document", 4),
        ("readme", 4),
        ("docs", 4),
        ("explain", 3),
        ("write up", 3),
    ],
    "security": [
        ("security", 5),
        ("secret", 4),
        ("token", 4),
        ("auth", 4),
        ("permission", 4),
        ("owasp", 4),
        ("cve", 4),
        ("vulnerability", 4),
        ("threat", 4),
    ],
    "release": [
        ("release", 5),
        ("deploy", 5),
        ("publish", 4),
        ("rollback", 4),
        ("ship", 3),
        ("tag", 3),
    ],
    "operations": [
        ("incident", 5),
        ("restore", 4),
        ("recovery", 4),
        ("backup", 3),
        ("runtime", 3),
        ("observability", 4),
        ("trace", 4),
        ("diagnose", 4),
        ("outage", 5),
    ],
    "planning": [
        ("plan", 5),
        ("implementation plan", 5),
        ("roadmap", 3),
        ("break down", 4),
        ("estimate", 4),
        ("organize", 3),
    ],
    "brainstorm": [
        ("brainstorm", 5),
        ("idea", 3),
        ("option", 3),
        ("approach", 3),
        ("tradeoff", 4),
        ("design direction", 4),
    ],
    "coding": [
        ("implement", 5),
        ("build", 4),
        ("add", 3),
        ("create", 3),
        ("change", 3),
        ("update", 3),
        ("fix", 4),
        ("refactor", 4),
    ],
    "exploration": [
        ("explain", 5),
        ("what is", 4),
        ("how does", 4),
        ("where is", 5),
        ("find", 4),
        ("show me", 3),
        ("explore", 5),
        ("map", 5),
        ("analyze repo", 5),
    ],
}

LANE_TIE_PRIORITY: dict[str, int] = {
    "governance": 90,
    "coding": 80,
    "release": 70,
    "operations": 65,
    "review": 60,
    "planning": 55,
    "documentation": 50,
    "security": 45,
    "brainstorm": 40,
}


def classify_prompt(prompt: str) -> dict[str, Any]:
    lower = prompt.lower()
    scores: dict[str, int] = {lane: 0 for lane in LANE_KEYWORDS}
    for lane, patterns in LANE_KEYWORDS.items():
        for needle, weight in patterns:
            if needle in lower:
                scores[lane] += weight

    execute_terms = (
        "implement",
        "create",
        "add",
        "update",
        "fix",
        "refactor",
        "build",
        "patch",
        "rename",
        "reorganize",
        "uradi",
    )
    bug_terms = ("bug", "broken", "regression", "issue", "defect")
    review_terms = ("review", "audit", "inspect", "analyze")
    planning_terms = ("plan", "implementation plan", "estimate", "break down")
    documentation_terms = ("documentation", "document", "readme", "docs", "write up")

    has_execute = any(term in lower for term in execute_terms)
    has_bug = any(term in lower for term in bug_terms)
    has_review = any(term in lower for term in review_terms)
    has_planning = any(term in lower for term in planning_terms)
    has_documentation = any(term in lower for term in documentation_terms)

    if has_execute:
        scores["coding"] += 2
    if has_bug:
        scores["coding"] += 3
    if has_review and not has_execute:
        scores["review"] += 2
    if has_planning and not has_execute:
        scores["planning"] += 2
    if has_documentation and not has_execute:
        scores["documentation"] += 2

    if not any(scores.values()):
        scores["coding"] = 1

    primary_lane = max(
        scores, key=lambda lane: (scores[lane], LANE_TIE_PRIORITY.get(lane, 0))
    )
    primary_score = scores[primary_lane]

    # Rule: If task is not clear or score is low, default to Exploration (Mapper)
    if primary_score < 3:
        primary_lane = "exploration"

    secondary_lanes = [
        lane
        for lane, score in scores.items()
        if lane != primary_lane and score > 0 and score >= max(2, primary_score - 2)
    ]

    if primary_lane == "coding":
        if any(
            word in lower
            for word in ("bug", "fix", "broken", "regression", "issue", "defect")
        ):
            task_kind = "bugfix"
        elif any(
            word in lower for word in ("refactor", "cleanup", "rename", "reorganize")
        ):
            task_kind = "refactoring"
        elif any(
            word in lower for word in ("investigate", "analyze", "why", "root cause")
        ):
            task_kind = "investigation"
        else:
            task_kind = "feature"
    elif primary_lane == "governance":
        task_kind = "governance"
    elif primary_lane == "documentation":
        task_kind = "documentation"
    elif primary_lane == "review":
        task_kind = "review"
    elif primary_lane == "release":
        task_kind = "release"
    elif primary_lane == "operations":
        task_kind = "operations"
    elif primary_lane == "planning":
        task_kind = "planning"
    elif primary_lane == "brainstorm":
        task_kind = "brainstorm"
    elif primary_lane == "security":
        task_kind = "security"
    else:
        task_kind = "investigation"

    intent_mode = "change"
    if any(
        word in lower
        for word in (
            "review",
            "audit",
            "analyze",
            "explain",
            "da li",
            "what",
            "why",
            "how",
        )
    ):
        intent_mode = "analysis"
    if has_execute:
        intent_mode = "execute"

    return {
        "scores": scores,
        "primary_lane": primary_lane,
        "secondary_lanes": unique(secondary_lanes),
        "task_kind": task_kind,
        "intent_mode": intent_mode,
    }


PIPELINE_BY_KIND = {
    "brainstorm": "Minimal Supervisor Flow",
    "planning": "Minimal Supervisor Flow",
    "feature": "Minimal Supervisor Flow",
    "bugfix": "Minimal Supervisor Flow",
    "refactoring": "Minimal Supervisor Flow",
    "documentation": "Minimal Supervisor Flow",
    "review": "Minimal Supervisor Flow",
    "governance": "Minimal Supervisor Flow",
    "operations": "Minimal Supervisor Flow",
    "release": "Minimal Supervisor Flow",
    "investigation": "Minimal Supervisor Flow",
    "security": "Minimal Supervisor Flow",
    "exploration": "Minimal Supervisor Flow",
}

STARTING_ROLE_BY_PIPELINE = {
    "Minimal Supervisor Flow": "supervisor",
}

ROLE_CHAIN_BY_PIPELINE = {
    "Minimal Supervisor Flow": [
        "supervisor",
        "mapper",
        "docs-researcher",
        "codex-executor",
        "reviewer",
    ],
}

# Model Mapping for v1 Alignment
MODEL_BY_ROLE = {
    "supervisor": "gpt-4o-mini",
    "mapper": "gpt-4o-mini",
    "docs-researcher": "gpt-4o-mini",
    "codex-executor": "gpt-4o",
    "reviewer": "gpt-4o",
}


def select_pipeline_and_roles(
    classification: dict[str, Any],
) -> tuple[str, str, list[str]]:
    task_kind = classification["task_kind"]
    pipeline = PIPELINE_BY_KIND.get(task_kind, "Standard Feature Pipeline")
    starting_role = STARTING_ROLE_BY_PIPELINE[pipeline]
    role_chain = ROLE_CHAIN_BY_PIPELINE[pipeline]
    return pipeline, starting_role, role_chain


def select_trust(primary_lane: str, task_kind: str, prompt: str) -> tuple[str, str]:
    lower = prompt.lower()
    if task_kind == "release" or primary_lane == "release":
        tier = "T3"
    elif primary_lane == "operations":
        tier = "T2"
    elif primary_lane == "security":
        tier = (
            "T2"
            if any(
                word in lower
                for word in ("fix", "patch", "rotate", "remediate", "update")
            )
            else "T0"
        )
    elif any(
        word in lower
        for word in (
            "dependency",
            "npm install",
            "composer require",
            "package update",
            "ci",
            "infrastructure",
        )
    ):
        tier = "T2"
    elif primary_lane in {"brainstorm", "planning", "review"}:
        tier = "T0"
    else:
        tier = "T1"

    approval_mode = {
        "T0": "Never",
        "T1": "OnDangerous",
        "T2": "OnExternal",
        "T3": "Always",
    }[tier]
    return tier, approval_mode


def should_delegate_to_subagents(
    prompt: str,
    classification: dict[str, Any],
    must_read: list[str],
    should_read: list[str],
) -> tuple[bool, str, str]:
    lower = prompt.lower()
    clusters = prompt_clusters(prompt)
    broad_cues = (
        "map out",
        "explore",
        "trace",
        "analyze",
        "audit",
        "find",
        "where is",
        "how does",
        "what does",
        "inventory",
        "survey",
        "understand",
    )
    explicit_delegate = any(cue in lower for cue in broad_cues) or "subagent" in lower
    long_prompt = len(lower.split()) >= 18
    broad_lane = classification["primary_lane"] in {
        "planning",
        "review",
        "documentation",
        "governance",
        "security",
        "operations",
    }
    wide_context = len(must_read) + len(should_read) >= 12
    multi_topic = len(clusters) >= 2

    if explicit_delegate or multi_topic or wide_context or (broad_lane and long_prompt):
        if classification["task_kind"] in {"feature", "bugfix", "refactoring"} and (
            multi_topic or explicit_delegate
        ):
            return True, "parallel-build", "parallel implementation split"
        return True, "research", "broad context discovery"

    return False, "none", "narrow task"


def subagent_brief_filename(index: int, kind: str, cluster: str) -> str:
    return f"{index:02d}-{kind}-{cluster}.md"


def build_subagent_brief(
    index: int,
    prompt: str,
    classification: dict[str, Any],
    context_files: list[str],
    cluster: str,
    mode: str,
) -> dict[str, Any]:
    cluster_keywords = SUBAGENT_CLUSTER_KEYWORDS.get(cluster, [cluster])
    focus_files = candidate_subagent_files(context_files, cluster_keywords)
    role = "explore"
    if cluster == "docs":
        role = "synthesis"
    elif cluster == "security" or classification["task_kind"] in {"review", "security"}:
        role = "review"
    elif classification["task_kind"] in {"planning", "governance"}:
        role = "planner"

    if role == "review":
        budget = 2500 if mode == "research" else 3000
    elif role == "planner":
        budget = 3000
    else:
        budget = 4000 if mode == "research" else 6000

    expected_output = {
        "explore": "A short file map with the next 3 files the main agent should inspect.",
        "review": "A risk list with correctness, security, and maintainability findings.",
        "planner": "A minimal breakdown of the work and the smallest safe next step.",
        "synthesis": "A concise documentation or governance summary with clear structure.",
    }[role]

    focus_label = cluster.replace("-", " ").title()
    if cluster == "core":
        goal = f"Map the core {focus_label.lower()} flow and identify the minimum file set needed to continue."
    elif cluster == "data":
        goal = f"Map the {focus_label.lower()} layer and identify dependencies, models, and migrations."
    elif cluster == "interface":
        goal = f"Map the {focus_label.lower()} surface and identify entrypoints and handoff points."
    elif cluster == "delivery":
        goal = "Map the delivery path, validation path, and release or observability touchpoints."
    elif cluster == "docs":
        goal = "Map the documentation and governance sources that explain the requested change."
    elif cluster == "security":
        goal = "Map the security boundaries, trust assumptions, and abuse cases that affect the task."
    else:
        goal = f"Map the relevant code paths for {focus_label.lower()}."

    if (
        classification["task_kind"] in {"feature", "bugfix", "refactoring"}
        and role == "explore"
    ):
        goal = f"Inspect {focus_label.lower()}-related code paths and return the tightest file shortlist for the main agent."

    # Enforce v1 Context Budget: max 5 files
    focus_files = focus_files[:5]

    return {
        "id": f"{role}-{cluster}",
        "name": f"{role}-{cluster}",
        "role": role,
        "mode": "subagent",
        "read_only": True,
        "budget_tokens": budget,
        "model_recommendation": MODEL_BY_ROLE.get(role, "gpt-4o-mini"),
        "client_hints": SUBAGENT_CLIENT_HINTS[mode],
        "focus_cluster": cluster,
        "focus_keywords": cluster_keywords,
        "focus_files": focus_files,
        "goal": goal,
        "prompt": prompt,
        "expected_output": expected_output,
        "exit_criteria": expected_output,
        "index": index,
        "summary": f"{role} on {focus_label.lower()}",
    }


def build_delegation_plan(
    prompt: str,
    classification: dict[str, Any],
    must_read: list[str],
    should_read: list[str],
) -> dict[str, Any]:
    recommended, mode, reason = should_delegate_to_subagents(
        prompt, classification, must_read, should_read
    )
    clusters = prompt_clusters(prompt)
    if not clusters:
        clusters = (
            ["core"]
            if classification["task_kind"] in {"feature", "bugfix", "refactoring"}
            else ["docs"]
        )

    subagents: list[dict[str, Any]] = []
    if recommended:
        for index, cluster in enumerate(clusters[:3], start=1):
            subagents.append(
                build_subagent_brief(
                    index=index,
                    prompt=prompt,
                    classification=classification,
                    context_files=unique(must_read + should_read),
                    cluster=cluster,
                    mode=mode,
                )
            )

        if classification["task_kind"] in {
            "feature",
            "bugfix",
            "refactoring",
            "review",
            "security",
        }:
            review_focus = clusters[0] if clusters else "core"
            subagents.append(
                {
                    "id": "review-risk",
                    "name": "review-risk",
                    "role": "review",
                    "mode": "subagent",
                    "read_only": True,
                    "budget_tokens": 2500,
                    "client_hints": SUBAGENT_CLIENT_HINTS[mode],
                    "focus_cluster": review_focus,
                    "focus_keywords": SUBAGENT_CLUSTER_KEYWORDS.get(
                        review_focus, [review_focus]
                    ),
                    "focus_files": candidate_subagent_files(
                        unique(must_read + should_read),
                        SUBAGENT_CLUSTER_KEYWORDS.get(review_focus, [review_focus]),
                    ),
                    "goal": "Identify correctness, security, and regression risks before the main agent edits files.",
                    "prompt": prompt,
                    "expected_output": "A terse review of risks, missing tests, and safer follow-up actions.",
                    "exit_criteria": "A risk list with a clear allow/block recommendation.",
                    "index": len(subagents) + 1,
                    "summary": "review risk",
                }
            )

    total_budget = sum(item["budget_tokens"] for item in subagents)
    if subagents and total_budget < 8000:
        total_budget = 8000

    return {
        "recommended": recommended,
        "mode": mode,
        "reason": reason,
        "preferred_clients": SUBAGENT_CLIENT_HINTS.get(mode, []),
        "total_budget_tokens": total_budget,
        "subagents": subagents,
    }


def add_existing(paths: list[Path], path: Path) -> None:
    if path.is_file():
        paths.append(path.resolve())


def resolve_rule_files(
    project_root: Path,
    reusable_rules_root: Path,
    stack: StackState,
    signals: dict[str, Any],
    classification: dict[str, Any],
) -> tuple[list[str], list[str]]:
    must_read: list[Path] = []
    should_read: list[Path] = []

    lane = classification["primary_lane"]
    task_kind = classification["task_kind"]
    secondary_lanes = classification["secondary_lanes"]

    add_existing(must_read, project_root / "AGENTS.md")
    add_existing(must_read, reusable_rules_root / "AGENTS.md")

    base_rule_paths = [
        "governance/core/quality/quality-gates.md",
        "governance/core/resolution/profile-resolution-algorithm.md",
        "governance/execution/policy/execution-policy.md",
        "governance/execution/routing/prompt-to-governance-flow.md",
        "governance/execution/approvals/approval-policy.md",
        "governance/core/flags/feature-flags.md",
        "governance/delivery/workflows/workflow-pipelines.md",
        "governance/agents/roles/agent-roles.md",
        "governance/intelligence/context/context-management.md",
    ]
    for relative in base_rule_paths:
        add_existing(must_read, reusable_rules_root / relative)

    lane_paths = {
        "governance": [
            "governance/standards/governance/governance-authoring-standard.md",
            "governance/standards/governance/governance-evolution-policy.md",
            "governance/standards/documentation/how-to-document.md",
        ],
        "documentation": [
            "governance/standards/documentation/how-to-document-flow.md",
            "governance/standards/documentation/how-to-document.md",
        ],
        "review": [
            "governance/standards/review/how-to-code-review.md",
            "governance/standards/review/how-to-strict-review.md",
        ],
        "coding": [
            "governance/standards/coding/how-to-coding-standards.md",
            "governance/standards/coding/naming-standard.md",
            "governance/architecture/architecture-standard.md",
        ],
        "security": [
            "governance/security/README.md",
            "governance/security/threat-modeling-and-abuse-case-policy.md",
            "governance/security/owasp-web-and-api-baseline.md",
        ],
        "release": [
            "governance/delivery/release/release-and-rollback-policy.md",
            "governance/delivery/operations/security-launch-checklist.md",
            "governance/delivery/operations/staging-smoke-checklist.md",
        ],
        "operations": [
            "governance/delivery/operations/README.md",
            "governance/delivery/operations/observability-and-error-envelope.md",
            "governance/delivery/operations/runtime-handoff-contract.md",
            "governance/delivery/operations/backup-and-restore-runbook.md",
        ],
        "planning": [
            "governance/architecture/architecture-standard.md",
            "governance/standards/documentation/how-to-document-flow.md",
        ],
        "brainstorm": [
            "governance/architecture/architecture-standard.md",
        ],
    }

    for relative in lane_paths.get(lane, []):
        add_existing(must_read, reusable_rules_root / relative)

    if task_kind in {"feature", "bugfix", "refactoring"}:
        for relative in lane_paths["coding"]:
            add_existing(must_read, reusable_rules_root / relative)

    repository_profiles = unique(
        stack.repository_profiles + signals["repository_profiles"]
    )
    languages = unique(stack.languages + signals["languages"])
    frameworks = unique(stack.frameworks + signals["frameworks"])
    architecture_profiles = unique(
        stack.architecture_profiles + signals["architecture_profiles"]
    )

    for profile in repository_profiles:
        add_existing(
            must_read,
            reusable_rules_root
            / "governance"
            / "profiles"
            / "repository-kinds"
            / f"{profile}.md",
        )
    for language in languages:
        add_existing(
            must_read,
            reusable_rules_root
            / "governance"
            / "profiles"
            / "languages"
            / f"{language}.md",
        )
    for framework in frameworks:
        add_existing(
            must_read,
            reusable_rules_root
            / "governance"
            / "profiles"
            / "frameworks"
            / f"{framework}.md",
        )
    for profile in architecture_profiles:
        add_existing(
            should_read,
            reusable_rules_root
            / "governance"
            / "architecture"
            / "profiles"
            / "languages"
            / f"{profile}.md",
        )
        add_existing(
            should_read,
            reusable_rules_root
            / "governance"
            / "architecture"
            / "profiles"
            / "frameworks"
            / f"{profile}.md",
        )

    if (
        lane in {"security", "release", "operations"}
        or "security" in secondary_lanes
        or stack.security_lanes
    ):
        add_existing(
            must_read, reusable_rules_root / "governance" / "security" / "README.md"
        )
    if (
        lane in {"operations", "release"}
        or "operations" in secondary_lanes
        or stack.operations_lanes
    ):
        add_existing(
            must_read,
            reusable_rules_root
            / "governance"
            / "delivery"
            / "operations"
            / "README.md",
        )

    local_support = [
        project_root / "README.md",
        project_root / ".agents" / "README.md",
        project_root / ".agents" / "management" / "README.md",
        project_root / ".agents" / "business-logic" / "README.md",
        project_root / ".agents" / "language-specific" / "README.md",
        project_root / ".agents" / "review" / "REVIEWS.md",
    ]
    for path in local_support:
        add_existing(should_read, path)

    return unique([str(path) for path in must_read]), unique(
        [str(path) for path in should_read if path not in must_read]
    )


def resolve_context_sources(
    project_root: Path, session_id: str
) -> dict[str, list[str]]:
    session_dir = project_root / ".agents" / "sessions" / session_id
    sources = {
        "memory": [],
        "session": [],
        "project": [],
        "strategy": [],
    }

    for path in [
        project_root / ".agents" / "memory" / "MEMORY.md",
        project_root / ".agents" / "management" / "memories" / "memory_summary.md",
    ]:
        if path.is_file():
            sources["memory"].append(str(path.resolve()))

    for path in [
        session_dir / "session_memory.md",
        session_dir / "transcript.json",
    ]:
        if path.is_file():
            sources["session"].append(str(path.resolve()))

    for path in [
        project_root / ".agents" / "management" / "TODO.md",
        project_root / ".agents" / "management" / "BUGS.md",
        project_root / ".agents" / "management" / "ACTIVE.md",
        project_root / ".agents" / "management" / "DECISIONS.md",
    ]:
        if path.is_file():
            sources["project"].append(str(path.resolve()))

    for directory in [
        project_root / ".agents" / "context" / "product",
        project_root / ".agents" / "context" / "strategy",
        project_root / ".agents" / "context" / "stakeholders",
        project_root / ".agents" / "context" / "users",
    ]:
        if directory.is_dir():
            for candidate in sorted(directory.glob("*.md"))[:5]:
                sources["strategy"].append(str(candidate.resolve()))

    return sources


def resolve_evidence_targets(
    project_root: Path, primary_lane: str, task_kind: str, trust_tier: str
) -> list[str]:
    candidates: list[Path] = []

    if task_kind == "bugfix":
        candidates.append(project_root / ".agents" / "management" / "BUGS.md")
    else:
        candidates.append(project_root / ".agents" / "management" / "TODO.md")

    if primary_lane == "review":
        candidates.append(project_root / ".agents" / "review" / "REVIEWS.md")
    if primary_lane in {"documentation", "governance", "release"}:
        candidates.append(
            project_root / ".agents" / "management" / "evidence" / "CHANGELOG.md"
        )
    if primary_lane in {"security", "operations", "release"}:
        candidates.append(
            project_root / ".agents" / "management" / "evidence" / "RISK_REGISTER.md"
        )
    if primary_lane == "release":
        candidates.append(
            project_root
            / ".agents"
            / "management"
            / "evidence"
            / "RELEASE_CHECKLIST.md"
        )
    if trust_tier in {"T2", "T3"} or primary_lane in {
        "governance",
        "security",
        "operations",
        "release",
    }:
        candidates.append(
            project_root / ".agents" / "management" / "evidence" / "TRACE_REPORTS.md"
        )

    return unique([str(path.resolve()) for path in candidates if path.is_file()])


def build_manifest(
    project_root: Path, prompt: str, session_id: str, task_id: str
) -> dict[str, Any]:
    reusable_rules_root = rules_root(project_root)
    stack = parse_agents_stack(project_root / "AGENTS.md")
    signals = gather_repo_signals(project_root, reusable_rules_root)
    classification = classify_prompt(prompt)
    pipeline, starting_role, role_chain = select_pipeline_and_roles(classification)
    trust_tier, approval_mode = select_trust(
        classification["primary_lane"], classification["task_kind"], prompt
    )
    must_read, should_read = resolve_rule_files(
        project_root, reusable_rules_root, stack, signals, classification
    )
    context_sources = resolve_context_sources(project_root, session_id)
    evidence_targets = resolve_evidence_targets(
        project_root,
        classification["primary_lane"],
        classification["task_kind"],
        trust_tier,
    )
    delegation = build_delegation_plan(prompt, classification, must_read, should_read)

    project_identifier = sha12(str(project_root.resolve()))
    trace_id = f"trace-{sha12(project_identifier + session_id + task_id + prompt)}"
    prompt_preview = re.sub(r"\s+", " ", prompt).strip()
    prompt_preview = prompt_preview[:220] + ("..." if len(prompt_preview) > 220 else "")

    session_dir = project_root / ".agents" / "sessions" / session_id
    task_dir = session_dir / "tasks" / task_id

    return {
        "schema_version": SCHEMA_VERSION,
        "timestamp": iso_timestamp(),
        "project_root": str(project_root.resolve()),
        "project_id": project_identifier,
        "session_id": session_id,
        "task_id": task_id,
        "trace_id": trace_id,
        "prompt": {
            "hash": sha12(prompt),
            "preview": prompt_preview,
        },
        "intent": {
            "mode": classification["intent_mode"],
            "task_kind": classification["task_kind"],
            "scores": classification["scores"],
        },
        "routing": {
            "primary_lane": classification["primary_lane"],
            "secondary_lanes": classification["secondary_lanes"],
            "pipeline": pipeline,
            "starting_role": starting_role,
            "role_chain": role_chain,
            "model_recommendations": {
                role: MODEL_BY_ROLE.get(role, "unknown") for role in role_chain
            },
            "trust_tier": trust_tier,
            "approval_mode": approval_mode,
        },
        "stack": {
            "declared": {
                "delivery_kind": stack.delivery_kind,
                "repository_profiles": stack.repository_profiles,
                "languages": stack.languages,
                "frameworks": stack.frameworks,
                "coding_profiles": stack.coding_profiles,
                "architecture_profiles": stack.architecture_profiles,
                "security_lanes": stack.security_lanes,
                "operations_lanes": stack.operations_lanes,
                "validation_entrypoint": stack.validation_entrypoint,
                "dev_entrypoint": stack.dev_entrypoint,
                "release_entrypoint": stack.release_entrypoint,
            },
            "inferred": signals,
        },
        "governance_pack": {
            "rules_root": str(reusable_rules_root.resolve()),
            "must_read": must_read,
            "should_read": should_read,
        },
        "context_injection": context_sources,
        "delegation": delegation,
        "evidence_targets": evidence_targets,
        "artifacts": {
            "session_dir": str(session_dir.resolve()),
            "task_dir": str(task_dir.resolve()),
            "context_json": str((task_dir / "context.json").resolve()),
            "context_markdown": str((task_dir / "context.md").resolve()),
            "subagents_dir": str((task_dir / "subagents").resolve()),
            "events_log": str((task_dir / "events.log").resolve()),
            "result_json": str((task_dir / "result.json").resolve()),
            "trace_reports": str(
                (
                    project_root
                    / ".agents"
                    / "management"
                    / "evidence"
                    / "TRACE_REPORTS.md"
                ).resolve()
            ),
        },
        "events": [
            "AGENTSLoaded",
            "PromptAnalyzed",
            "IntentClassified",
            "LaneResolved",
            "StackResolved",
            "GovernancePackSelected",
            "PipelineSelected",
            "StartingRoleSelected",
            "ContextAssembled",
            "SubagentsPlanned",
            "TrustResolved",
            "EvidenceTargetsResolved",
            "TaskManifestWritten",
            "TaskReady",
        ],
    }


def markdown_manifest(manifest: dict[str, Any]) -> str:
    routing = manifest["routing"]
    stack = manifest["stack"]
    delegation = manifest.get("delegation", {})
    lines = [
        f"# Task Routing Summary — {manifest['task_id']}",
        "",
        f"- Timestamp: `{manifest['timestamp']}`",
        f"- Trace ID: `{manifest['trace_id']}`",
        f"- Prompt Hash: `{manifest['prompt']['hash']}`",
        f"- Prompt Preview: {manifest['prompt']['preview']}",
        "",
        "## Routing",
        "",
        f"- Intent Mode: `{manifest['intent']['mode']}`",
        f"- Task Kind: `{manifest['intent']['task_kind']}`",
        f"- Primary Lane: `{routing['primary_lane']}`",
        f"- Secondary Lanes: `{', '.join(routing['secondary_lanes']) or 'none'}`",
        f"- Pipeline: `{routing['pipeline']}`",
        f"- Starting Role: `{routing['starting_role']}`",
        f"- Role Chain: `{', '.join(routing['role_chain'])}`",
        f"- Trust Tier: `{routing['trust_tier']}`",
        f"- Approval Mode: `{routing['approval_mode']}`",
        "",
        "## Stack",
        "",
        f"- Delivery Kind: `{stack['declared']['delivery_kind'] or stack['inferred'].get('system_kind') or 'unknown'}`",
        f"- Repository Profiles: `{', '.join(unique(stack['declared']['repository_profiles'] + stack['inferred']['repository_profiles'])) or 'none'}`",
        f"- Languages: `{', '.join(unique(stack['declared']['languages'] + stack['inferred']['languages'])) or 'none'}`",
        f"- Frameworks: `{', '.join(unique(stack['declared']['frameworks'] + stack['inferred']['frameworks'])) or 'none'}`",
        "",
        "## Must Read",
        "",
    ]
    lines.extend(
        [f"- `{path}`" for path in manifest["governance_pack"]["must_read"]]
        or ["- none"]
    )
    lines.extend(["", "## Should Read", ""])
    lines.extend(
        [f"- `{path}`" for path in manifest["governance_pack"]["should_read"]]
        or ["- none"]
    )
    lines.extend(["", "## Delegation", ""])
    lines.append(
        f"- Recommended: `{str(delegation.get('recommended', False)).lower()}`"
    )
    lines.append(f"- Mode: `{delegation.get('mode', 'none')}`")
    lines.append(f"- Reason: {delegation.get('reason', 'none')}")
    lines.append(
        f"- Preferred Clients: `{', '.join(delegation.get('preferred_clients', [])) or 'none'}`"
    )
    lines.append(f"- Total Budget: `{delegation.get('total_budget_tokens', 0)}`")
    lines.append("- Subagents:")
    if delegation.get("subagents"):
        for item in delegation["subagents"]:
            lines.append(
                f"  - `{item['name']}` | role=`{item['role']}` | budget=`{item['budget_tokens']}` | cluster=`{item['focus_cluster']}`"
            )
    else:
        lines.append("  - none")
    lines.extend(["", "## Context Injection", ""])
    for section, paths in manifest["context_injection"].items():
        lines.append(f"- {section}:")
        if paths:
            lines.extend([f"  - `{path}`" for path in paths])
        else:
            lines.append("  - none")
    lines.extend(["", "## Evidence Targets", ""])
    lines.extend([f"- `{path}`" for path in manifest["evidence_targets"]] or ["- none"])
    return "\n".join(lines) + "\n"


def markdown_subagent_brief(manifest: dict[str, Any], subagent: dict[str, Any]) -> str:
    lines = [
        f"# Sub-Agent Brief — {subagent['name']}",
        "",
        f"- Task ID: `{manifest['task_id']}`",
        f"- Trace ID: `{manifest['trace_id']}`",
        f"- Role: `{subagent['role']}`",
        f"- Model Recommendation: `{subagent.get('model_recommendation', 'unknown')}`",
        f"- Mode: `{subagent['mode']}`",
        f"- Read Only: `{str(subagent['read_only']).lower()}`",
        f"- Budget Tokens: `{subagent['budget_tokens']}`",
        f"- Preferred Clients: `{', '.join(subagent.get('client_hints', [])) or 'none'}`",
        f"- Focus Cluster: `{subagent['focus_cluster']}`",
        f"- Goal: {subagent['goal']}",
        "",
        "## Focus Files",
        "",
    ]
    lines.extend(
        [f"- `{path}`" for path in subagent.get("focus_files", [])] or ["- none"]
    )
    lines.extend(
        [
            "",
            "## Exit Criteria",
            "",
            f"- {subagent['exit_criteria']}",
            "",
            "## Expected Output",
            "",
            f"- {subagent['expected_output']}",
            "",
            "## Prompt",
            "",
            subagent["prompt"],
            "",
        ]
    )
    return "\n".join(lines)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Resolve prompt-to-governance task context."
    )
    parser.add_argument("--project-root", default=os.getcwd())
    parser.add_argument("--prompt")
    parser.add_argument("--prompt-file")
    parser.add_argument("--session-id", required=True)
    parser.add_argument("--task-id", required=True)
    parser.add_argument("--write-json")
    parser.add_argument("--write-markdown")
    parser.add_argument("--write-subagents-dir")
    parser.add_argument("--summary", action="store_true")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    if not args.prompt and not args.prompt_file:
        print(
            "resolve-task-context.py requires --prompt or --prompt-file",
            file=sys.stderr,
        )
        return 2

    prompt = args.prompt or read_text(Path(args.prompt_file))
    project_root = Path(args.project_root).resolve()
    manifest = build_manifest(project_root, prompt, args.session_id, args.task_id)

    if args.write_json:
        output_path = Path(args.write_json)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(
            json.dumps(manifest, indent=2, ensure_ascii=True) + "\n", encoding="utf-8"
        )

    if args.write_markdown:
        output_path = Path(args.write_markdown)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(markdown_manifest(manifest), encoding="utf-8")

    if args.write_subagents_dir:
        output_dir = Path(args.write_subagents_dir)
        output_dir.mkdir(parents=True, exist_ok=True)
        subagents = manifest.get("delegation", {}).get("subagents", [])
        output_dir.joinpath("manifest.json").write_text(
            json.dumps(
                {
                    "task_id": manifest["task_id"],
                    "trace_id": manifest["trace_id"],
                    "recommended": manifest.get("delegation", {}).get(
                        "recommended", False
                    ),
                    "mode": manifest.get("delegation", {}).get("mode", "none"),
                    "reason": manifest.get("delegation", {}).get("reason", "none"),
                    "preferred_clients": manifest.get("delegation", {}).get(
                        "preferred_clients", []
                    ),
                    "subagents": subagents,
                },
                indent=2,
                ensure_ascii=True,
            )
            + "\n",
            encoding="utf-8",
        )
        for subagent in subagents:
            brief_path = output_dir / subagent_brief_filename(
                int(subagent.get("index", 1)),
                subagent.get("role", "explore"),
                subagent.get("focus_cluster", "general"),
            )
            brief_path.write_text(
                markdown_subagent_brief(manifest, subagent), encoding="utf-8"
            )

    if args.summary:
        print(
            f"{manifest['routing']['primary_lane']} | {manifest['routing']['pipeline']} | "
            f"{manifest['routing']['starting_role']} | {manifest['routing']['trust_tier']}"
        )
        return 0

    print(json.dumps(manifest, indent=2, ensure_ascii=True))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
