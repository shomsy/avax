#!/usr/bin/env python3
# aggregate-context.py — Agent Harness AI-Native Intelligence Layer
# Aggregates long-term memory and extracts strategic context.

import os
import sys
import json
import glob

MEMORY_DIR = ".agents/management/memories"
OUTPUT_DIR = ".agents/management/evidence/generated"

def aggregate_context(target_dir="."):
    mem_path = os.path.join(target_dir, MEMORY_DIR)
    
    if not os.path.exists(mem_path):
        print("ℹ️ Memory directory missing. Skipping intelligence aggregation.")
        return
        
    context = {
        "strategic_goals": [],
        "failure_patterns": [],
        "risk_assessments": []
    }
    
    # Simple extraction heuristic for markdown memories
    for f in glob.glob(os.path.join(mem_path, "*.md")):
        with open(f, 'r') as file:
            content = file.read()
            if "GOAL" in content or "OBJECTIVE" in content:
                context["strategic_goals"].append(f)
            if "FAIL" in content or "ERROR" in content:
                context["failure_patterns"].append(f)
                
    out_path = os.path.join(target_dir, OUTPUT_DIR, "strategic-context-summary.json")
    with open(out_path, "w") as f:
        json.dump(context, f, indent=2)
        
    print(f"🧠 Intelligence Layer: Context aggregated to {out_path}")
    print(f"  - Goals tracked: {len(context['strategic_goals'])}")
    print(f"  - Failure patterns learned: {len(context['failure_patterns'])}")

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    os.makedirs(os.path.join(target, OUTPUT_DIR), exist_ok=True)
    aggregate_context(target)
