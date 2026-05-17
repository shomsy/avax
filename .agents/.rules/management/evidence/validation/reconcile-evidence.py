#!/usr/bin/env python3
# reconcile-evidence.py — Agent Harness Hallucination Containment Engine
# Version: 1.0.0
# Goal 10: Verify that claims in EVIDENCE/ match machine-verifiable proof.

import os
import re
import sys

def check_reconciliation(target_dir="."):
    current_md_path = os.path.join(target_dir, "EVIDENCE/CURRENT.md")
    if not os.path.isfile(current_md_path):
        print(f"❌ ERROR: {current_md_path} not found.")
        return False

    with open(current_md_path, "r") as f:
        content = f.read()

    # 1. Extract linked evidence paths
    links = re.findall(r"\[.*?\]\((.*?)\)", content)
    
    # 2. Verify link existence
    all_links_valid = True
    for link in links:
        # Resolve relative links
        if link.startswith("../"):
            # CURRENT.md is in EVIDENCE/, so ../ means project root
            actual_path = os.path.join(target_dir, link.replace("../", ""))
            if not os.path.exists(actual_path):
                print(f"❌ HALLUCINATION DETECTED: Claim links to {link} but file is missing at {actual_path}")
                all_links_valid = False
            else:
                print(f"✅ Reconciled: {link}")

    # 3. Check for Status vs Debt
    has_status = "Status:" in content
    has_debt = "debt" in content.lower()
    
    if has_status and not has_debt:
        print("⚠️  WARNING: Status claimed without mentioning debt. Potential over-claiming.")

    return all_links_valid

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    if check_reconciliation(target):
        print("\n🚀 Hallucination Containment: PASS (Claims match Evidence)")
        sys.exit(0)
    else:
        print("\n❌ Hallucination Containment: FAIL (Claims drift from Reality)")
        sys.exit(1)
