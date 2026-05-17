#!/usr/bin/env python3
# check-complexity-budget.py — Agent Harness Hardened Complexity Budget Enforcer
# Version: 4.2.0 (Hardened Enterprise)
#
# Fails the CI/CD pipeline if governance complexity, size, circular dependency
# loops, duplicate rules, or unreferenced files exceed strict operational budgets.

import os
import sys
import json

INDEX_DIR = ".agents/management/evidence/generated"

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
    unreferenced_rules = len(entropy.get("unreferenced_rules", []))
    circular_loops = len(entropy.get("circular_loops", []))

    # Calculate rule sizes (in characters and lines)
    total_lines = 0
    core_rule_count = 0
    
    for filepath, data in files.items():
        # Core rules are those inside core/ directories
        if "governance/core" in filepath or "governance/standards" in filepath:
            core_rule_count += 1
            
        # Read the file to get size in lines
        full_path = os.path.join(target_dir, filepath)
        if os.path.exists(full_path):
            with open(full_path, 'r', encoding='utf-8') as rf:
                lines = rf.readlines()
                total_lines += len(lines)

    avg_lines_per_rule = total_lines / max(1, total_active_rules)

    # ---------------------------------------------------------
    # OPERATIONAL COMPLEXITY THRESHOLDS (Enterprise Budget)
    # ---------------------------------------------------------
    MAX_TOTAL_ACTIVE_RULES = 380
    MAX_CORE_RULES = 60 # Strict limit to prevent procedural bloat
    MAX_DEAD_RULES = 5
    MAX_DUPLICATE_RULES = 0 # ZERO TOLERANCE FOR ID CLASHES
    MAX_CIRCULAR_LOOPS = 0  # ZERO TOLERANCE FOR CIRCULAR PATHS
    MAX_UNREFERENCED_RULES = 160
    MAX_AVG_RULE_LINES = 150 # Prevent giant monolithic documents

    errors = []
    warnings = []

    print("======================================================================")
    print("📊  AGENT HARNESS OS GOVERNANCE COMPLEXITY AUDIT")
    print("======================================================================")
    print(f"  - Total Active Rules:      {total_active_rules} (Max Budget: {MAX_TOTAL_ACTIVE_RULES})")
    print(f"  - Active Core/Stds Rules:  {core_rule_count} (Max Budget: {MAX_CORE_RULES})")
    print(f"  - Active Shadowed Overlays:{shadowed_rules}")
    print(f"  - Average Lines Per Rule:  {avg_lines_per_rule:.1f} (Max Budget: {MAX_AVG_RULE_LINES})")
    print(f"  - Duplicate Rule Warnings: {duplicate_rules} (Max Budget: {MAX_DUPLICATE_RULES})")
    print(f"  - Circular Dependency Loops:{circular_loops} (Max Budget: {MAX_CIRCULAR_LOOPS})")
    print(f"  - Dead/Inactive Rules:     {dead_rules} (Max Budget: {MAX_DEAD_RULES})")
    print(f"  - Unreferenced/Orphan Rules:{unreferenced_rules} (Max Budget: {MAX_UNREFERENCED_RULES})")
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
        warnings.append(f"WARNING: Unreferenced rules ({unreferenced_rules}) exceed warning limit ({MAX_UNREFERENCED_RULES}).")
        
    if avg_lines_per_rule > MAX_AVG_RULE_LINES:
        errors.append(f"BLOCKER: Average rule lines ({avg_lines_per_rule:.1f}) exceeds threshold ({MAX_AVG_RULE_LINES}). Rules are too complex.")

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
