#!/usr/bin/env python3
# explain-governance.py — Agent Harness Governance Explainability & Traceability Engine
# Version: 4.2.0 (Hardened Enterprise)
#
# Usage:
#   python3 explain-governance.py [Rule ID or File Path]
#   python3 explain-governance.py --shadows
#   python3 explain-governance.py --circular

import os
import sys
import json

GENERATED_DIR = ".agents/management/evidence/generated"

def explain_governance(query=None, show_shadows=False, show_circular=False):
    index_path = os.path.join(GENERATED_DIR, "governance-index.json")
    precedence_path = os.path.join(GENERATED_DIR, "governance-precedence.json")

    if not os.path.exists(index_path) or not os.path.exists(precedence_path):
        print("❌ ERROR: Compiled governance index missing. Please run compile-governance.py first.")
        sys.exit(1)

    with open(index_path, 'r', encoding='utf-8') as f:
        index = json.load(f)
    with open(precedence_path, 'r', encoding='utf-8') as f:
        precedence_data = json.load(f)

    precedence = precedence_data.get("precedence", {})
    shadowed = precedence_data.get("shadowed_rules", {})

    print("======================================================================")
    print("🧠  AGENT HARNESS OS GOVERNANCE EXPLAINABILITY TRACER")
    print("======================================================================")

    if show_shadows:
        print("\n🔍 DIAGNOSTICS: Priority Overlay Shadowing Map")
        print("----------------------------------------------------------------------")
        if not shadowed:
            print("  ✅ No shadowed rules. All active paths correspond to default baseline.")
        for shadowed_path, active_path in shadowed.items():
            print(f"  [!] Shadowed: {shadowed_path}")
            print(f"      └── WON:  {active_path}")
            print(f"          └── Reason: {precedence.get(active_path, {}).get('reason')}")
        print("======================================================================")
        return

    if show_circular:
        print("\n🔍 DIAGNOSTICS: Circular Dependency Loops")
        print("----------------------------------------------------------------------")
        loops = index.get("circular_loops", [])
        if not loops:
            print("  ✅ No circular dependency loops detected. Precedence tree is acyclic.")
        else:
            for idx, loop in enumerate(loops):
                print(f"  [!] Circular Loop #{idx + 1}:")
                print("      " + " ➔ ".join(loop))
        print("======================================================================")
        return

    if query:
        print(f"\n🔍 QUERY TRACE: '{query}'")
        print("----------------------------------------------------------------------")
        # Search by file path first
        found = False
        for filepath, data in index["files"].items():
            if query.lower() in filepath.lower() or any(query.lower() in rid.lower() for rid in data.get("rule_ids", [])):
                found = True
                prec = precedence.get(filepath, {})
                print(f"📍 Mapped File: {filepath}")
                print(f"   - Rule IDs:  {', '.join(data.get('rule_ids', [])) or '[None]'}")
                print(f"   - Frontmatter: {json.dumps(data.get('frontmatter'), indent=2)}")
                print(f"   - Resolution: Active (Won)")
                print(f"   - Shadowing:  {'Overwrote Baseline: ' + prec.get('shadowed_path') if prec.get('shadowed_path') else 'Clean Baseline Default'}")
                print(f"   - Win Reason: {prec.get('reason')}")
                
                # Check if it has links
                if data.get("links"):
                    print(f"   - Outbound Dependencies: {', '.join(data.get('links'))}")
                
                # Check if any rule shadows this path
                for shadow_k, shadow_v in shadowed.items():
                    if shadow_k == filepath:
                        print(f"   - [!] SHADOW WARNING: This baseline is INACTIVE. Shadowed by: {shadow_v}")
                print("----------------------------------------------------------------------")

        if not found:
            print(f"  ❌ No matching active rules or files found for query: '{query}'")
        print("======================================================================")
        return

    # Print general active topology summary
    print(f"\n📊 ACTIVE TOPOLOGY SUMMARY:")
    print(f"  - Active Governance Rules: {len(index['files'])}")
    print(f"  - Shadowed Overlay Rules:  {len(shadowed)}")
    print(f"  - Duplicate Rule Warnings: {len(index['duplicate_rules'])}")
    print(f"  - Inactive/Dead Rules:     {len(index['dead_rules'])}")
    print(f"  - Circular Dependency Loops: {len(index['circular_loops'])}")
    print("\n💡 Operator Tip:")
    print("  - To trace overlays:     python3 explain-governance.py --shadows")
    print("  - To trace loops:        python3 explain-governance.py --circular")
    print("  - To explain a rule/file: python3 explain-governance.py [Rule-ID/Path]")
    print("======================================================================")

if __name__ == "__main__":
    query = None
    show_shadows = False
    show_circular = False

    if len(sys.argv) > 1:
        arg = sys.argv[1]
        if arg == "--shadows":
            show_shadows = True
        elif arg == "--circular":
            show_circular = True
        else:
            query = arg

    explain_governance(query, show_shadows, show_circular)
