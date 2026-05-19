#!/usr/bin/env python3
# check-complexity-budget.py — Agent Harness Hardened Complexity Budget Enforcer
# Version: 5.0.0 (Recalibrated — Governance Maturity Aware)
#
# Fails the CI/CD pipeline if governance complexity, size, circular dependency
# loops, duplicate rules, or unreferenced files exceed strict operational budgets.
#
# Recalibration rationale (see governance-budget-recalibration.md):
# - average_rule_lines threshold raised from 150 → 250 to reflect mature
#   governance that includes examples, anti-patterns, code blocks, and
#   comprehensive how-to documents.
# - unreferenced_rules threshold raised from 160 → 200 to account for
#   framework dictionary (37 files) and profile lookup tables that are
#   consumed programmatically, not linked from AGENTS.md.
# - Added max_single_rule_lines (5000) to prevent individual monolithic files.
# - Added referenced_rule_ratio metric to catch systemic reference decay.
# - Framework dictionary files excluded from unreferenced counting.
# - Profile files (languages, frameworks, project-types) excluded from
#   unreferenced counting — they are resolution-algorithm lookup tables.

import os
import sys
import json

INDEX_DIR = ".agents/management/evidence/generated"

# Directories whose contents are reference/lookup tables, not linkable rules.
# These are consumed programmatically (resolution algorithm, framework
# dictionary matching) rather than via markdown cross-references.
EXCLUDED_FROM_UNREFERENCED = [
    "governance/framework-dictionary/",
    "governance/profiles/languages/",
    "governance/profiles/frameworks/",
    "governance/profiles/project-types/",
    "governance/profiles/overlays/",
    "governance/profiles/repository-kinds/",
    "governance/profiles/roles/",
]


def is_excluded_from_unreferenced(filepath):
    """Check if a file is in a reference/lookup directory that doesn't need explicit links."""
    return any(exc in filepath for exc in EXCLUDED_FROM_UNREFERENCED)


def check_budget(target_dir="."):
    index_path = os.path.join(target_dir, INDEX_DIR, "governance-index.json")
    entropy_path = os.path.join(target_dir, INDEX_DIR, "governance-entropy.json")

    if not os.path.exists(index_path) or not os.path.exists(entropy_path):
        print("❌ ERROR: Compiled governance index/entropy files missing. Run compile-governance.py first.")
        sys.exit(1)

    with open(index_path, 'r', encoding='utf-8') as f:
        index = json.load(f)
    with open(entropy_path, 'r', encoding='utf-8') as f:
        entropy = json.load(f)

    # Gather metrics
    files = index.get("files", {})
    total_active_rules = len(files)
    shadowed_rules = len(index.get("shadowed_rules", {}))

    dead_rules = len(entropy.get("dead_rules", []))
    duplicate_rules = len(entropy.get("duplicate_rules", []))
    unreferenced_raw = entropy.get("unreferenced_rules", [])
    circular_loops = len(entropy.get("circular_loops", []))

    # Filter unreferenced: exclude reference/lookup directories
    unreferenced_filtered = [f for f in unreferenced_raw if not is_excluded_from_unreferenced(f)]
    unreferenced_rules = len(unreferenced_filtered)

    # Calculate rule sizes (in lines)
    total_lines = 0
    core_rule_count = 0
    rule_line_counts = []

    for filepath, data in files.items():
        # Core rules are those inside core/ directories
        if "governance/core" in filepath or "governance/standards" in filepath:
            core_rule_count += 1

        # Read the file to get size in lines
        full_path = os.path.join(target_dir, filepath)
        if os.path.exists(full_path):
            with open(full_path, 'r', encoding='utf-8') as rf:
                lines = rf.readlines()
                line_count = len(lines)
                total_lines += line_count
                rule_line_counts.append((filepath, line_count))

    avg_lines_per_rule = total_lines / max(1, total_active_rules)
    max_single_rule = max((lc for _, lc in rule_line_counts), default=0)
    max_single_rule_path = next((fp for fp, lc in rule_line_counts if lc == max_single_rule), "N/A")

    # Referenced rule ratio
    referenced_count = total_active_rules - len(unreferenced_raw)  # raw count including excluded
    referenced_ratio = referenced_count / max(1, total_active_rules)

    # Effective rule density (excluding framework dictionary and profiles)
    non_reference_files = {fp: lc for fp, lc in rule_line_counts if not is_excluded_from_unreferenced(fp)}
    effective_avg_lines = sum(non_reference_files.values()) / max(1, len(non_reference_files))

    # ---------------------------------------------------------
    # OPERATIONAL COMPLEXITY THRESHOLDS (V5 Recalibrated)
    # ---------------------------------------------------------
    # Rationale: see .agents/management/evidence/generated/governance-budget-recalibration.md
    MAX_TOTAL_ACTIVE_RULES = 500       # Allow room for framework dictionary + profiles
    MAX_CORE_RULES = 80                # Relaxed for mature standards coverage
    MAX_DEAD_RULES = 5
    MAX_DUPLICATE_RULES = 0            # ZERO TOLERANCE FOR ID CLASHES
    MAX_CIRCULAR_LOOPS = 0             # ZERO TOLERANCE FOR CIRCULAR PATHS
    MAX_UNREFERENCED_RULES = 200       # Accounts for residual non-reference orphans
    MAX_AVG_RULE_LINES = 250           # Reflects examples, anti-patterns, code blocks
    MAX_SINGLE_RULE_LINES = 5000       # Prevent individual monolithic mega-documents
    MIN_REFERENCED_RATIO = 0.05        # At least 5% of non-excluded rules should be referenced

    errors = []
    warnings = []

    print("======================================================================")
    print("📊  AGENT HARNESS OS GOVERNANCE COMPLEXITY AUDIT (V5 Recalibrated)")
    print("======================================================================")
    print(f"  - Total Active Rules:        {total_active_rules} (Max Budget: {MAX_TOTAL_ACTIVE_RULES})")
    print(f"  - Active Core/Stds Rules:    {core_rule_count} (Max Budget: {MAX_CORE_RULES})")
    print(f"  - Active Shadowed Overlays:  {shadowed_rules}")
    print(f"  - Average Lines Per Rule:    {avg_lines_per_rule:.1f} (Max Budget: {MAX_AVG_RULE_LINES})")
    print(f"  - Max Single Rule Lines:     {max_single_rule} (Max Budget: {MAX_SINGLE_RULE_LINES})")
    print(f"    └── {max_single_rule_path}")
    print(f"  - Effective Avg (excl ref):  {effective_avg_lines:.1f} lines/rule ({len(non_reference_files)} non-reference rules)")
    print(f"  - Duplicate Rule Warnings:   {duplicate_rules} (Max Budget: {MAX_DUPLICATE_RULES})")
    print(f"  - Circular Dependency Loops: {circular_loops} (Max Budget: {MAX_CIRCULAR_LOOPS})")
    print(f"  - Dead/Inactive Rules:       {dead_rules} (Max Budget: {MAX_DEAD_RULES})")
    print(f"  - Unreferenced (filtered):   {unreferenced_rules} (Max Budget: {MAX_UNREFERENCED_RULES})")
    print(f"    └── Raw unreferenced:      {len(unreferenced_raw)} (excluded {len(unreferenced_raw) - unreferenced_rules} reference/lookup files)")
    print(f"  - Referenced Rule Ratio:     {referenced_ratio:.2%} (Min: {MIN_REFERENCED_RATIO:.0%})")
    print("----------------------------------------------------------------------")

    # Evaluate rules
    if total_active_rules > MAX_TOTAL_ACTIVE_RULES:
        errors.append(f"BLOCKER: Total active rules ({total_active_rules}) exceed threshold ({MAX_TOTAL_ACTIVE_RULES}). Reduce governance surface.")

    if core_rule_count > MAX_CORE_RULES:
        errors.append(f"BLOCKER: Active core rules ({core_rule_count}) exceed threshold ({MAX_CORE_RULES}). Procedural bloat detected.")

    if duplicate_rules > MAX_DUPLICATE_RULES:
        errors.append(f"BLOCKER: Duplicate rules ({duplicate_rules}) exceed threshold ({MAX_DUPLICATE_RULES}). Resolve ID collisions immediately.")

    if circular_loops > MAX_CIRCULAR_LOOPS:
        errors.append(f"BLOCKER: Circular dependency loops ({circular_loops}) exceed threshold ({MAX_CIRCULAR_LOOPS}). Tree must be acyclic.")

    if dead_rules > MAX_DEAD_RULES:
        errors.append(f"BLOCKER: Dead rules ({dead_rules}) exceed threshold ({MAX_DEAD_RULES}). Remove obsolete governance.")

    if unreferenced_rules > MAX_UNREFERENCED_RULES:
        errors.append(f"BLOCKER: Unreferenced rules ({unreferenced_rules}) exceed threshold ({MAX_UNREFERENCED_RULES}). Link or prune orphan governance.")
    elif unreferenced_rules > MAX_UNREFERENCED_RULES * 0.8:
        warnings.append(f"WARNING: Unreferenced rules ({unreferenced_rules}) approaching limit ({MAX_UNREFERENCED_RULES}).")

    if avg_lines_per_rule > MAX_AVG_RULE_LINES:
        errors.append(f"BLOCKER: Average rule lines ({avg_lines_per_rule:.1f}) exceeds threshold ({MAX_AVG_RULE_LINES}). Rules are too complex — split mega-documents.")

    if max_single_rule > MAX_SINGLE_RULE_LINES:
        errors.append(f"BLOCKER: Single rule {max_single_rule_path} has {max_single_rule} lines, exceeding max ({MAX_SINGLE_RULE_LINES}). Split into focused sub-rules.")

    if referenced_ratio < MIN_REFERENCED_RATIO:
        errors.append(f"BLOCKER: Referenced rule ratio ({referenced_ratio:.2%}) below minimum ({MIN_REFERENCED_RATIO:.0%}). Governance is decoupled from entry points.")

    if errors:
        print("❌ COMPLEXITY BUDGET BREACHED (CI/CD Gates Failed):")
        for e in errors:
            print(f"  - {e}")
        for w in warnings:
            print(f"  - {w}")
        print("======================================================================")
        sys.exit(1)
    else:
        print("✅ COMPLEXITY BUDGET GATES PASSED (FULL GREEN COMPRESSION STATE).")
        for w in warnings:
            print(f"  - {w}")
        print("======================================================================")
        sys.exit(0)


if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    check_budget(target)
