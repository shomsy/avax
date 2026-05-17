#!/usr/bin/env python3
# governance-observability.py — Agent Harness Observability Engine
# Version: 1.0.0
# Goal 7: Metrics, Entropy, and Drift detection.

import os
import sys
import glob

def calculate_metrics(target_dir="."):
    metrics = {}

    # 1. Entropy Score (Complexity vs Governance)
    # Simple proxy: number of markdown files in .agents/governance vs total project files
    gov_files = len(glob.glob(os.path.join(target_dir, ".agents/governance/**/*.md"), recursive=True))
    total_files = sum([len(files) for r, d, files in os.walk(target_dir) if ".git" not in r])
    
    metrics["entropy_score"] = round(gov_files / total_files if total_files > 0 else 0, 4)

    # 2. Review Debt
    review_dir = os.path.join(target_dir, ".agents/management/evidence/reviews")
    review_files = glob.glob(os.path.join(review_dir, "*.json"))
    if review_files:
        latest_review = max(review_files, key=os.path.getctime)
        import json
        with open(latest_review, "r") as f:
            data = json.load(f)
            metrics["review_debt"] = len(data.get("findings", []))
            metrics["latest_status"] = data.get("status", "UNKNOWN")
    else:
        metrics["review_debt"] = 0
        metrics["latest_status"] = "NO_REVIEW"

    # 3. Evidence Density
    evidence_files = len(glob.glob(os.path.join(target_dir, ".agents/management/evidence/**/*.json"), recursive=True))
    metrics["evidence_density"] = evidence_files

    return metrics

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    m = calculate_metrics(target)
    print("📊 Governance Observability Metrics:")
    for k, v in m.items():
        print(f"  - {k}: {v}")
