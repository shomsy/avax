#!/usr/bin/env python3
# memory-lifecycle.py — Agent Harness Memory Engine
# Version: 1.0.0
# Goal 2: Evidence-derived memory updates and stale detection.

import os
import sys
import json
import time

MEMORY_DIR = ".agents/memory"
EVIDENCE_DIR = ".agents/management/evidence"

def detect_stale_memories(target_dir="."):
    stale = []
    mem_path = os.path.join(target_dir, MEMORY_DIR)
    now = time.time()
    
    if not os.path.exists(mem_path):
        return stale

    for f in os.listdir(mem_path):
        if f.endswith(".json"):
            path = os.path.join(mem_path, f)
            mtime = os.path.getmtime(path)
            # Stale if older than 30 days
            if (now - mtime) > (30 * 24 * 3600):
                stale.append(f)
    return stale

def derive_memory_from_evidence(target_dir="."):
    # Logic to look for "APPROVED" reviews or "COMPLETE" phases
    # and update the central memory.
    suggestions = []
    review_dir = os.path.join(target_dir, EVIDENCE_DIR, "reviews")
    if os.path.exists(review_dir):
        for f in os.listdir(review_dir):
            if f.endswith(".json"):
                with open(os.path.join(review_dir, f), "r") as r:
                    data = json.load(r)
                    if data.get("status") == "GREEN":
                        suggestions.append({
                            "type": "TRUTH",
                            "fact": f"Repository passed recursive review {data['review_id']}",
                            "source": f
                        })
    return suggestions

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    print("🧠 Starting Memory Lifecycle Check...")
    
    stale = detect_stale_memories(target)
    if stale:
        print(f"⚠️  STALE MEMORIES DETECTED: {', '.join(stale)}")
    else:
        print("✅ No stale memories found.")

    suggestions = derive_memory_from_evidence(target)
    if suggestions:
        print(f"💡 MEMORY SUGGESTIONS FOUND: {len(suggestions)}")
        for s in suggestions:
            print(f"  - [{s['type']}] {s['fact']} (Source: {s['source']})")
