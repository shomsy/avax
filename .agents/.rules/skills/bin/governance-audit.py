#!/usr/bin/env python3
"""Governance entropy resolution tool.

Scans governance markdown files, builds dependency graphs, identifies
unreferenced rules, classifies them, and supports archival/linking.

Usage:
    python3 governance-audit.py graph [--dir <dir>]
    python3 governance-audit.py unreferenced [--dir <dir>]
    python3 governance-audit.py classify [--dir <dir>]
    python3 governance-audit.py prune [--dry-run] [--dir <dir>]
    python3 governance-audit.py archive [--dir <dir>]
    python3 governance-audit.py report [--dir <dir>]
"""

import argparse
import hashlib
import json
import os
import re
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

ROOT = Path(__file__).resolve().parent.parent.parent  # .agents/
GOVERNANCE_DIRS = [
    "governance",
    ".rules/governance",
]

ARCHIVE_DIR = Path("management/evidence/archive/governance")
REPORT_DIR = Path("management/evidence/generated")

ORDER_OF_PRECEDENCE_ROOT = "AGENTS.md"
ORDER_OF_PRECEDENCE_GLOBAL = ".agents/AGENTS.md"

# Patterns that indicate placeholder/template content
PLACEHOLDER_PATTERNS = [
    re.compile(r"TODO", re.IGNORECASE),
    re.compile(r"placeholder", re.IGNORECASE),
    re.compile(r"\b(TBD|TBA)\b"),
    re.compile(r"# Title$|^# \w+ Template$", re.MULTILINE),
    re.compile(r"\b(template|example|stub)\b", re.IGNORECASE),
]

DEAD_CONTENT_SIGNALS = [
    re.compile(r"^#\s+\w+\s*$", re.MULTILINE),  # title-only file
    re.compile(r"^\s*$", re.MULTILINE),           # blank lines only
    re.compile(r"^\s*[-*]\s*$", re.MULTILINE),    # list markers only
]

# Patterns for finding file path references in markdown
PATH_PATTERNS = [
    re.compile(r"[`]?\.agents/governance/[\w\-/]+\.md[`]?"),
    re.compile(r"[`]?\.agents/\.rules/governance/[\w\-/]+\.md[`]?"),
    re.compile(r"[`]?governance/[\w\-/]+\.md[`]?"),
]

FRONTMATTER_RE = re.compile(r"^---\s*\n(.*?)\n---", re.DOTALL)
LINK_RE = re.compile(r"\[([^\]]*)\]\(([^)]+)\)")
RELATIVE_LINK_RE = re.compile(r"(\.{0,2}/[\w\-.]+(?:\.md)?)")


def rel(path: Path) -> str:
    """Return a path relative to ROOT."""
    try:
        return str(path.relative_to(ROOT))
    except ValueError:
        return str(path)


def content_hash(path: Path) -> str:
    """SHA-256 hex digest of file contents."""
    return hashlib.sha256(path.read_bytes()).hexdigest()


def read_file_safe(path: Path) -> str:
    """Read a file, returning empty string on error."""
    try:
        return path.read_text(encoding="utf-8", errors="replace")
    except Exception:
        return ""


# ---------------------------------------------------------------------------
# 1. GovernanceDependencyGraph
# ---------------------------------------------------------------------------

class GovernanceDependencyGraph:
    """Builds and analyses the full dependency graph of governance files."""

    def __init__(self, root: Path | None = None):
        self.root = (root or ROOT).resolve()
        self.graph: dict[str, list[str]] = {}       # file -> [refs]
        self.reverse: dict[str, list[str]] = {}     # file -> [referenced_by]
        self.all_files: list[str] = []              # all governance .md files
        self.precedence_files: list[str] = []       # files in order of precedence
        self._scan_governance_files()
        self._scan_precedence()
        self._build_graph()

    # -- scanning -----------------------------------------------------------

    def _scan_governance_files(self) -> None:
        """Find all .md files in governance directories."""
        found: set[str] = set()
        for gov_dir in GOVERNANCE_DIRS:
            dir_path = self.root / gov_dir
            if not dir_path.is_dir():
                continue
            for md in sorted(dir_path.rglob("*.md")):
                r = rel(md)
                found.add(r)
        self.all_files = sorted(found)

    def _scan_precedence(self) -> None:
        """Parse order of precedence from AGENTS.md files."""
        precedence_set: set[str] = set()

        for agents_file in [ORDER_OF_PRECEDENCE_ROOT, ORDER_OF_PRECEDENCE_GLOBAL]:
            fp = self.root / agents_file
            if not fp.is_file():
                continue
            text = read_file_safe(fp)
            # Match numbered list items with file paths
            for m in re.finditer(
                r"^\s*\d+\.\s+[`]?(\.agents/[\w\-/]+(?:\.md)?)[`]?", text, re.MULTILINE
            ):
                path_str = m.group(1)
                # Expand wildcards
                if "**" in path_str:
                    prefix = path_str.replace("/**", "").rstrip("/")
                    for f in self.all_files:
                        if f.startswith(prefix):
                            precedence_set.add(f)
                else:
                    # Try both with and without .agents/ prefix
                    candidates = [path_str]
                    if not path_str.startswith(".agents/"):
                        candidates.append(".agents/" + path_str)
                    for c in candidates:
                        if c in self.all_files:
                            precedence_set.add(c)

        self.precedence_files = sorted(precedence_set)

    # -- parsing references -------------------------------------------------

    def _extract_refs(self, file_path: str, text: str) -> list[str]:
        """Extract governance file references from markdown text."""
        refs: set[str] = set()

        # Direct path patterns
        for pat in PATH_PATTERNS:
            for m in pat.finditer(text):
                p = m.group(0).strip("`")
                # Normalize
                if p in self.all_files:
                    refs.add(p)
                # Try prefixing .agents/
                if not p.startswith(".agents/"):
                    prefixed = ".agents/" + p
                    if prefixed in self.all_files:
                        refs.add(prefixed)

        # Markdown links
        for m in LINK_RE.finditer(text):
            url = m.group(2)
            # Check if it's a relative path to a governance file
            if "governance" in url and url.endswith(".md"):
                # Resolve relative to the current file
                base = Path(file_path).parent
                candidate = str((base / url).resolve().relative_to(self.root))
                if candidate in self.all_files:
                    refs.add(candidate)
                # Also try direct match
                if url in self.all_files:
                    refs.add(url)

        # Frontmatter references
        fm = FRONTMATTER_RE.search(text)
        if fm:
            fm_text = fm.group(1)
            for pat in PATH_PATTERNS:
                for m in pat.finditer(fm_text):
                    p = m.group(0).strip("`")
                    if p in self.all_files:
                        refs.add(p)
                    if not p.startswith(".agents/"):
                        prefixed = ".agents/" + p
                        if prefixed in self.all_files:
                            refs.add(prefixed)

        # Exclude self-references
        refs.discard(file_path)
        return sorted(refs)

    # -- building the graph -------------------------------------------------

    def _build_graph(self) -> None:
        """Build the full dependency graph."""
        self.graph = {}
        self.reverse = {f: [] for f in self.all_files}

        for f in self.all_files:
            fp = self.root / f
            text = read_file_safe(fp)
            refs = self._extract_refs(f, text)
            self.graph[f] = refs
            for r in refs:
                if r not in self.reverse:
                    self.reverse[r] = []
                self.reverse[r].append(f)

    def build_graph(self) -> dict[str, list[str]]:
        """Return dict of file -> list of files it references."""
        return dict(self.graph)

    # -- analysis -----------------------------------------------------------

    def find_roots(self) -> list[str]:
        """Files that are not referenced by anyone else."""
        return sorted([
            f for f in self.all_files
            if not self.reverse.get(f, [])
        ])

    def find_leaves(self) -> list[str]:
        """Files that don't reference anyone else."""
        return sorted([
            f for f in self.all_files
            if not self.graph.get(f, [])
        ])

    def find_unreferenced(self) -> list[str]:
        """Files not referenced by any other file AND not in order of precedence."""
        precedence_set = set(self.precedence_files)
        # Index/README files are considered referenced if they exist
        return sorted([
            f for f in self.all_files
            if (
                not self.reverse.get(f, [])
                and f not in precedence_set
                and not f.endswith("/README.md")  # READMEs are index files
            )
        ])

    def find_orphans(self) -> list[str]:
        """Files in directories but not referenced from parent README or index."""
        orphans: list[str] = []
        # Group files by directory
        dir_files: dict[str, list[str]] = {}
        for f in self.all_files:
            parent = str(Path(f).parent)
            dir_files.setdefault(parent, []).append(f)

        for directory, files in dir_files.items():
            readme = directory + "/README.md"
            if readme not in self.all_files:
                continue
            readme_text = read_file_safe(self.root / readme)
            readme_refs = set(self._extract_refs(readme, readme_text))
            for f in files:
                if f != readme and f not in readme_refs:
                    orphans.append(f)

        return sorted(set(orphans))

    # -- helpers ------------------------------------------------------------

    def get_referenced_by(self, file_path: str) -> list[str]:
        """Return list of files that reference this file."""
        return sorted(self.reverse.get(file_path, []))

    def references(self, file_path: str) -> list[str]:
        """Return list of files this file references."""
        return sorted(self.graph.get(file_path, []))


# ---------------------------------------------------------------------------
# 2. GovernancePruner
# ---------------------------------------------------------------------------

class GovernancePruner:
    """Safely identifies and removes dead governance rules."""

    DEAD = "DEAD"
    REFERENCE = "REFERENCE"
    TEMPLATE = "TEMPLATE"
    DUPLICATE = "DUPLICATE"

    def __init__(self, graph: GovernanceDependencyGraph):
        self.graph = graph
        self.unreferenced = graph.find_unreferenced()
        self.classifications: dict[str, str] = {}
        self._classify_all()

    def _is_placeholder(self, text: str) -> bool:
        """Check if content is mostly placeholder text."""
        if not text.strip():
            return True
        matches = sum(1 for p in PLACEHOLDER_PATTERNS if p.search(text))
        return matches >= 2

    def _is_empty_or_stub(self, text: str) -> bool:
        """Check if file has no substantial content."""
        lines = [l for l in text.splitlines() if l.strip()]
        # Remove frontmatter
        in_fm = False
        content_lines = []
        for line in lines:
            if line.strip() == "---":
                in_fm = not in_fm
                continue
            if not in_fm:
                content_lines.append(line)

        # Very short files are likely stubs
        if len(content_lines) <= 3:
            return True

        # Check for dead content signals
        for pat in DEAD_CONTENT_SIGNALS:
            if pat.match(text) and len(content_lines) <= 5:
                return True
        return False

    def _is_template(self, file_path: str, text: str) -> bool:
        """Check if file is intended as a template."""
        # Template directory
        if "/template" in file_path.lower() or "/templates/" in file_path.lower():
            return True
        # Template naming
        name = Path(file_path).stem.lower()
        if "template" in name:
            return True
        # Content signals
        if re.search(r"\btemplate\b", text, re.IGNORECASE):
            if re.search(r"\breplace\b|\bfill\b|\binsert\b|\byour\b", text, re.IGNORECASE):
                return True
        return False

    def _is_duplicate(self, file_path: str, text: str) -> bool:
        """Check if content is substantially duplicated elsewhere."""
        # Check if there's a counterpart in the other governance tree
        normalized = file_path.replace(".agents/.rules/governance/", ".agents/governance/")
        normalized2 = file_path.replace(".agents/governance/", ".agents/.rules/governance/")

        for other in [normalized, normalized2]:
            if other != file_path and other in self.graph.all_files:
                other_text = read_file_safe(self.graph.root / other)
                # Simple similarity: if they share the same title and significant content
                title_pat = re.compile(r"^#\s+(.+)$", re.MULTILINE)
                t1 = title_pat.search(text)
                t2 = title_pat.search(other_text)
                if t1 and t2 and t1.group(1).strip().lower() == t2.group(1).strip().lower():
                    return True

        return False

    def _classify(self, file_path: str) -> str:
        """Classify a single unreferenced file."""
        text = read_file_safe(self.graph.root / file_path)

        if self._is_empty_or_stub(text):
            return self.DEAD
        if self._is_duplicate(file_path, text):
            return self.DUPLICATE
        if self._is_template(file_path, text):
            return self.TEMPLATE
        return self.REFERENCE

    def _classify_all(self) -> None:
        """Classify all unreferenced files."""
        self.classifications = {}
        for f in self.unreferenced:
            self.classifications[f] = self._classify(f)

    def identify_dead_rules(self) -> list[str]:
        """Return list of unreferenced files."""
        return list(self.unreferenced)

    def classify_dead_rules(self) -> dict[str, str]:
        """Return classification for each unreferenced file."""
        return dict(self.classifications)

    def prune_dead_rules(self, dry_run: bool = True) -> dict[str, Any]:
        """Prune dead rules. If dry_run, lists what would be pruned."""
        results: dict[str, Any] = {
            "dry_run": dry_run,
            "actions": [],
            "summary": {
                "total": 0,
                "archived": 0,
                "deleted": 0,
                "kept": 0,
            }
        }

        archive_base = self.graph.root / ARCHIVE_DIR
        if not dry_run:
            archive_base.mkdir(parents=True, exist_ok=True)

        for f, classification in sorted(self.classifications.items()):
            if classification == self.DEAD:
                action = {
                    "file": f,
                    "classification": classification,
                    "action": "delete" if dry_run else "archive",
                    "reason": "Empty/placeholder content with no operational value",
                }
                results["actions"].append(action)
                if not dry_run:
                    src = self.graph.root / f
                    dest = archive_base / Path(f).name
                    # Avoid collisions
                    counter = 1
                    while dest.exists():
                        dest = archive_base / f"{Path(f).stem}_{counter}{Path(f).suffix}"
                        counter += 1
                    shutil.move(str(src), str(dest))
                    results["summary"]["archived"] += 1
                else:
                    results["summary"]["deleted"] += 1

            elif classification == self.DUPLICATE:
                action = {
                    "file": f,
                    "classification": classification,
                    "action": "archive",
                    "reason": "Content duplicated elsewhere; merge candidate",
                }
                results["actions"].append(action)
                if not dry_run:
                    src = self.graph.root / f
                    dest = archive_base / Path(f).name
                    counter = 1
                    while dest.exists():
                        dest = archive_base / f"{Path(f).stem}_{counter}{Path(f).suffix}"
                        counter += 1
                    shutil.move(str(src), str(dest))
                    results["summary"]["archived"] += 1
                else:
                    results["summary"]["archived"] += 1

            elif classification == self.REFERENCE:
                action = {
                    "file": f,
                    "classification": classification,
                    "action": "keep_for_linking",
                    "reason": "Contains useful information but not actively referenced",
                }
                results["actions"].append(action)
                results["summary"]["kept"] += 1

            elif classification == self.TEMPLATE:
                action = {
                    "file": f,
                    "classification": classification,
                    "action": "keep_as_template",
                    "reason": "Intended as template for other projects",
                }
                results["actions"].append(action)
                results["summary"]["kept"] += 1

            results["summary"]["total"] += 1

        return results


# ---------------------------------------------------------------------------
# 3. GovernanceArchiver
# ---------------------------------------------------------------------------

class GovernanceArchiver:
    """Archives unreferenced rules with metadata."""

    def __init__(self, graph: GovernanceDependencyGraph):
        self.graph = graph
        self.archive_base = graph.root / ARCHIVE_DIR

    def archive_rule(self, file_path: str, reason: str = "unreferenced") -> dict[str, Any]:
        """Move file to archive with metadata."""
        src = self.graph.root / file_path
        if not src.is_file():
            return {"error": f"File not found: {file_path}"}

        self.archive_base.mkdir(parents=True, exist_ok=True)

        dest = self.archive_base / Path(file_path).name
        counter = 1
        while dest.exists():
            dest = self.archive_base / f"{Path(file_path).stem}_{counter}{Path(file_path).suffix}"
            counter += 1

        meta = {
            "archived_at": datetime.now(timezone.utc).isoformat(),
            "reason": reason,
            "original_path": file_path,
            "archive_path": rel(dest),
            "content_hash": content_hash(src),
        }

        shutil.move(str(src), str(dest))

        # Write metadata file
        meta_path = dest.with_suffix(dest.suffix + ".meta.json")
        meta_path.write_text(json.dumps(meta, indent=2), encoding="utf-8")

        return meta

    def list_archived(self) -> list[dict[str, Any]]:
        """List all archived rules."""
        results = []
        if not self.archive_base.is_dir():
            return results

        for meta_file in sorted(self.archive_base.rglob("*.meta.json")):
            try:
                meta = json.loads(meta_file.read_text(encoding="utf-8"))
                results.append(meta)
            except Exception:
                results.append({"path": rel(meta_file), "error": "invalid metadata"})

        # Also find archived files without metadata
        for md in sorted(self.archive_base.rglob("*.md")):
            if not md.with_suffix(md.suffix + ".meta.json").exists():
                results.append({
                    "path": rel(md),
                    "archived_at": "unknown",
                    "reason": "unknown",
                    "original_path": "unknown",
                    "content_hash": content_hash(md),
                })

        return results

    def restore_rule(self, archive_path: str) -> dict[str, Any]:
        """Restore an archived rule to its original location."""
        src = self.graph.root / archive_path
        if not src.is_file():
            return {"error": f"Archived file not found: {archive_path}"}

        # Read metadata to find original path
        meta_path = src.with_suffix(src.suffix + ".meta.json")
        original_path = None
        if meta_path.is_file():
            try:
                meta = json.loads(meta_path.read_text(encoding="utf-8"))
                original_path = meta.get("original_path")
            except Exception:
                pass

        if not original_path:
            return {"error": "Cannot determine original path; no metadata found"}

        dest = self.graph.root / original_path
        dest.parent.mkdir(parents=True, exist_ok=True)

        shutil.move(str(src), str(dest))

        # Remove metadata file
        if meta_path.is_file():
            meta_path.unlink()

        return {
            "restored": rel(dest),
            "from": archive_path,
            "original_path": original_path,
        }


# ---------------------------------------------------------------------------
# 4. GovernanceLinker
# ---------------------------------------------------------------------------

class GovernanceLinker:
    """Links unreferenced rules into the dependency graph."""

    def __init__(self, graph: GovernanceDependencyGraph):
        self.graph = graph
        self.pruner = GovernancePruner(graph)

    def suggest_links(self, unreferenced_file: str) -> list[dict[str, str]]:
        """Return list of files that should reference this unreferenced file."""
        suggestions: list[dict[str, str]] = []

        # Get the file's directory/category
        parts = Path(unreferenced_file).parts
        category = parts[2] if len(parts) > 2 else None  # e.g., "security", "delivery"

        # Find files in the same category that are in the precedence list
        for pf in self.graph.precedence_files:
            if pf == unreferenced_file:
                continue
            pf_parts = Path(pf).parts
            pf_category = pf_parts[2] if len(pf_parts) > 2 else None

            # Same category files are good candidates
            if pf_category == category:
                suggestions.append({
                    "referencing_file": pf,
                    "reason": f"Same governance category: {category}",
                    "section": "Related documents",
                })

        # Check for parent READMEs in the same tree
        parent_dir = str(Path(unreferenced_file).parent)
        parent_readme = parent_dir + "/README.md"
        if parent_readme in self.graph.all_files:
            suggestions.append({
                "referencing_file": parent_readme,
                "reason": "Parent directory index",
                "section": "Contents",
            })

        # Check the main AGENTS.md files for the relevant category
        for agents in [ORDER_OF_PRECEDENCE_ROOT, ORDER_OF_PRECEDENCE_GLOBAL]:
            agents_path = self.graph.root / agents
            if agents_path.is_file():
                text = read_file_safe(agents_path)
                # If the category is mentioned, suggest linking
                if category and category.lower() in text.lower():
                    suggestions.append({
                        "referencing_file": agents,
                        "reason": f"Category '{category}' is mentioned in order of precedence",
                        "section": "Order Of Precedence",
                    })

        # Deduplicate
        seen = set()
        unique = []
        for s in suggestions:
            key = (s["referencing_file"], s["reason"])
            if key not in seen:
                seen.add(key)
                unique.append(s)

        return unique

    def auto_link(self, file_path: str, referencing_file: str) -> dict[str, Any]:
        """Add a reference in the referencing file."""
        ref_fp = self.graph.root / referencing_file
        if not ref_fp.is_file():
            return {"error": f"Referencing file not found: {referencing_file}"}

        text = read_file_safe(ref_fp)

        # Compute the relative link
        ref_dir = Path(referencing_file).parent
        target_rel = os.path.relpath(file_path, str(ref_dir))

        # Get the title of the target file
        target_text = read_file_safe(self.graph.root / file_path)
        title_match = re.search(r"^#\s+(.+)$", target_text, re.MULTILINE)
        title = title_match.group(1).strip() if title_match else Path(file_path).stem

        link_text = f"\n- [{title}]({target_rel})\n"

        # Find a good insertion point: after the last existing link list
        # or at the end of the file before any trailing whitespace
        lines = text.splitlines()

        # Try to find a "Related" or "See also" section
        insert_idx = len(lines)
        for i, line in enumerate(lines):
            if re.search(r"^(#+\s+)?(Related|See also|References|Related documents)", line, re.IGNORECASE):
                # Insert after the heading
                insert_idx = i + 1
                # Skip blank lines after heading
                while insert_idx < len(lines) and not lines[insert_idx].strip():
                    insert_idx += 1
                break
            elif re.search(r"^- \[.+?\]\(.+?\)$", line):
                # If we find a link list, insert after the last one
                insert_idx = i + 1

        lines.insert(insert_idx, link_text.strip())
        new_text = "\n".join(lines) + "\n"

        ref_fp.write_text(new_text, encoding="utf-8")

        return {
            "status": "linked",
            "file": referencing_file,
            "added_link": link_text.strip(),
            "target": file_path,
        }


# ---------------------------------------------------------------------------
# 5. GovernanceEntropyReport
# ---------------------------------------------------------------------------

class GovernanceEntropyReport:
    """Generates comprehensive governance entropy reports."""

    def __init__(self, graph: GovernanceDependencyGraph):
        self.graph = graph
        self.pruner = GovernancePruner(graph)
        self.archiver = GovernanceArchiver(graph)
        self.linker = GovernanceLinker(graph)

    def generate_report(self) -> dict[str, Any]:
        """Full entropy report."""
        unreferenced = self.graph.find_unreferenced()
        classifications = self.pruner.classify_dead_rules()
        orphaned = self.graph.find_orphans()
        archived = self.archiver.list_archived()

        # Count by classification
        by_class: dict[str, list[str]] = {}
        for f, c in classifications.items():
            by_class.setdefault(c, []).append(f)

        # Category breakdown
        by_category: dict[str, dict[str, int]] = {}
        for f in self.graph.all_files:
            parts = Path(f).parts
            category = parts[2] if len(parts) > 2 else "unknown"
            by_category.setdefault(category, {
                "total": 0,
                "referenced": 0,
                "unreferenced": 0,
            })
            by_category[category]["total"] += 1
            if f in unreferenced:
                by_category[category]["unreferenced"] += 1
            else:
                by_category[category]["referenced"] += 1

        # Recommendations
        recommendations = self._generate_recommendations(classifications, unreferenced, orphaned)

        report = {
            "generated_at": datetime.now(timezone.utc).isoformat(),
            "summary": {
                "total_files": len(self.graph.all_files),
                "referenced": len(self.graph.all_files) - len(unreferenced),
                "unreferenced": len(unreferenced),
                "archived": len(archived),
                "pruned": 0,  # updated after prune
                "orphans": len(orphaned),
            },
            "by_classification": {
                k: {"count": len(v), "files": v}
                for k, v in sorted(by_class.items())
            },
            "by_category": {
                k: v for k, v in sorted(by_category.items())
            },
            "unreferenced_files": [
                {
                    "file": f,
                    "classification": classifications.get(f, "unknown"),
                    "referenced_by": self.graph.get_referenced_by(f),
                    "references": self.graph.references(f),
                }
                for f in unreferenced
            ],
            "orphaned_files": orphaned,
            "archived_files": archived,
            "roots": self.graph.find_roots(),
            "leaves": self.graph.find_leaves(),
            "precedence_files": self.graph.precedence_files,
            "recommendations": recommendations,
        }

        return report

    def _generate_recommendations(
        self,
        classifications: dict[str, str],
        unreferenced: list[str],
        orphaned: list[str],
    ) -> list[dict[str, str]]:
        """Generate actionable recommendations."""
        recs: list[dict[str, str]] = []

        dead = [f for f, c in classifications.items() if c == GovernancePruner.DEAD]
        duplicates = [f for f, c in classifications.items() if c == GovernancePruner.DUPLICATE]
        references = [f for f, c in classifications.items() if c == GovernancePruner.REFERENCE]
        templates = [f for f, c in classifications.items() if c == GovernancePruner.TEMPLATE]

        if dead:
            recs.append({
                "priority": "high",
                "action": "prune",
                "detail": f"Delete {len(dead)} dead files with placeholder/empty content",
                "files": dead,
            })

        if duplicates:
            recs.append({
                "priority": "high",
                "action": "merge",
                "detail": f"Review {len(duplicates)} duplicate files for consolidation",
                "files": duplicates,
            })

        if references:
            recs.append({
                "priority": "medium",
                "action": "link",
                "detail": f"Add references to {len(references)} useful but unreferenced files",
                "files": references,
            })

        if templates:
            recs.append({
                "priority": "low",
                "action": "keep",
                "detail": f"Keep {len(templates)} template files for external projects",
                "files": templates,
            })

        if orphaned:
            recs.append({
                "priority": "medium",
                "action": "index",
                "detail": f"Add {len(orphaned)} orphaned files to their parent README indexes",
                "files": orphaned[:10],  # limit list size
            })

        if not recs:
            recs.append({
                "priority": "info",
                "action": "none",
                "detail": "No governance entropy detected. All files are properly linked.",
            })

        return recs


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def cmd_graph(args: argparse.Namespace) -> None:
    """Print the dependency graph."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    graph = GovernanceDependencyGraph(root)

    print(f"Governance Dependency Graph ({len(graph.all_files)} files)")
    print("=" * 60)

    for f, refs in sorted(graph.graph.items()):
        status = "ROOT" if not graph.reverse.get(f) else ""
        print(f"  {f} {status}")
        for r in refs:
            print(f"    -> {r}")

    print(f"\nRoots (not referenced by others): {len(graph.find_roots())}")
    for r in graph.find_roots():
        print(f"  - {r}")

    print(f"\nLeaves (reference nothing): {len(graph.find_leaves())}")
    for l in graph.find_leaves():
        print(f"  - {l}")


def cmd_unreferenced(args: argparse.Namespace) -> None:
    """List unreferenced files."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    graph = GovernanceDependencyGraph(root)

    unreferenced = graph.find_unreferenced()
    print(f"Unreferenced governance files ({len(unreferenced)})")
    print("=" * 60)
    for f in unreferenced:
        print(f"  - {f}")

    if not unreferenced:
        print("  (none)")


def cmd_classify(args: argparse.Namespace) -> None:
    """Classify unreferenced files."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    graph = GovernanceDependencyGraph(root)
    pruner = GovernancePruner(graph)

    classifications = pruner.classify_dead_rules()
    print(f"Classification of unreferenced files ({len(classifications)})")
    print("=" * 60)

    for classification in [GovernancePruner.DEAD, GovernancePruner.DUPLICATE,
                           GovernancePruner.REFERENCE, GovernancePruner.TEMPLATE]:
        files = [f for f, c in classifications.items() if c == classification]
        if files:
            print(f"\n{classification} ({len(files)}):")
            for f in files:
                print(f"  - {f}")


def cmd_prune(args: argparse.Namespace) -> None:
    """Prune dead rules."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    dry_run = args.dry_run
    graph = GovernanceDependencyGraph(root)
    pruner = GovernancePruner(graph)

    results = pruner.prune_dead_rules(dry_run=dry_run)

    mode = "DRY RUN" if dry_run else "EXECUTED"
    print(f"Prune results ({mode})")
    print("=" * 60)

    for action in results["actions"]:
        print(f"  [{action['classification']}] {action['file']}")
        print(f"    -> {action['action']}: {action['reason']}")

    print(f"\nSummary:")
    print(f"  Total: {results['summary']['total']}")
    print(f"  Would delete/archive: {results['summary']['deleted']}")
    print(f"  Would archive: {results['summary']['archived']}")
    print(f"  Would keep: {results['summary']['kept']}")


def cmd_archive(args: argparse.Namespace) -> None:
    """Archive unreferenced rules."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    graph = GovernanceDependencyGraph(root)
    pruner = GovernancePruner(graph)
    archiver = GovernanceArchiver(graph)

    classifications = pruner.classify_dead_rules()
    to_archive = [
        f for f, c in classifications.items()
        if c in (GovernancePruner.DEAD, GovernancePruner.DUPLICATE)
    ]

    print(f"Archiving {len(to_archive)} files")
    print("=" * 60)

    for f in to_archive:
        result = archiver.archive_rule(f, reason=f"classified_{classifications[f]}")
        if "error" in result:
            print(f"  ERROR: {result['error']}")
        else:
            print(f"  Archived: {f} -> {result['archive_path']}")
            print(f"    Reason: {result['reason']}")


def cmd_report(args: argparse.Namespace) -> None:
    """Generate full entropy report."""
    root = Path(args.dir).resolve() if args.dir else ROOT
    graph = GovernanceDependencyGraph(root)
    reporter = GovernanceEntropyReport(graph)

    report = reporter.generate_report()

    # Write JSON report
    report_dir = root / REPORT_DIR
    report_dir.mkdir(parents=True, exist_ok=True)
    report_path = report_dir / "governance-entropy-report.json"
    report_path.write_text(
        json.dumps(report, indent=2, default=str),
        encoding="utf-8",
    )

    # Print summary
    s = report["summary"]
    print(f"Governance Entropy Report")
    print(f"Generated: {report['generated_at']}")
    print("=" * 60)
    print(f"\nSummary:")
    print(f"  Total governance files: {s['total_files']}")
    print(f"  Referenced:             {s['referenced']}")
    print(f"  Unreferenced:           {s['unreferenced']}")
    print(f"  Archived:               {s['archived']}")
    print(f"  Orphaned:               {s['orphans']}")

    print(f"\nBy Classification:")
    for cls, data in report["by_classification"].items():
        print(f"  {cls}: {data['count']}")
        for f in data["files"]:
            print(f"    - {f}")

    print(f"\nBy Category:")
    for cat, counts in sorted(report["by_category"].items()):
        print(f"  {cat}: {counts['total']} total, "
              f"{counts['referenced']} referenced, "
              f"{counts['unreferenced']} unreferenced")

    print(f"\nRecommendations:")
    for rec in report["recommendations"]:
        print(f"  [{rec['priority'].upper()}] {rec['action']}: {rec['detail']}")

    print(f"\nFull report written to: {report_path}")


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Governance entropy resolution tool"
    )
    parser.add_argument(
        "command",
        choices=["graph", "unreferenced", "classify", "prune", "archive", "report"],
        help="Command to execute",
    )
    parser.add_argument(
        "--dir", "-d",
        help="Root directory (default: .agents/ parent)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show what would be done without making changes",
    )

    args = parser.parse_args()

    commands = {
        "graph": cmd_graph,
        "unreferenced": cmd_unreferenced,
        "classify": cmd_classify,
        "prune": cmd_prune,
        "archive": cmd_archive,
        "report": cmd_report,
    }

    commands[args.command](args)


if __name__ == "__main__":
    main()
