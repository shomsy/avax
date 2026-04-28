#!/usr/bin/env python3
"""
analyze-instincts.py
Baseline detector for Phase 2 of the Continuous Learning pipeline.
Reads `.agents/management/learning/observations.jsonl`, simulates pattern detection,
and drafts new Instinct MD files into `.agents/management/learning/instincts/`.
"""

import os
import json
import datetime

OBS_PATH = ".agents/management/learning/observations.jsonl"
INSTINCTS_DIR = ".agents/management/learning/instincts"

def execute_analysis():
    if not os.path.exists(OBS_PATH):
        print(f"Skipping analysis. Found no observations at {OBS_PATH}")
        return

    print("Analyzing recent tool observations for systemic patterns...")

    with open(OBS_PATH, 'r') as f:
        observations = [line.strip() for line in f.readlines() if line.strip()]

    # In a full-blown implementation, this is where we would chunk observations
    # and pass them to an LLM for classification via MCP or direct API.
    # We simulate a "discovery" logic threshold here:

    if len(observations) >= 5:
        os.makedirs(INSTINCTS_DIR, exist_ok=True)

        timestamp = datetime.datetime.now().strftime('%Y%m%d%H%M%S')
        new_instinct_id = f"auto-pattern-{timestamp}"
        dest = os.path.join(INSTINCTS_DIR, f"{new_instinct_id}.md")

        yaml_frontmatter = f"""---
id: {new_instinct_id}
trigger: "When observing repetitive unoptimized tool combinations"
action: "Draft a new workflow pipeline to consolidate the effort"
confidence: 0.3
domain: operations
scope: project
occurrences: {len(observations)}
---

### System Discovery
This instinct was drafted automatically run by `analyze-instincts.py` after
detecting an unusually high density ({len(observations)}) of observations.
"""
        with open(dest, "w") as out:
            out.write(yaml_frontmatter)

        print(f"✅ Drafted new behavioral instinct: {dest}")

    else:
        print(f"Only {len(observations)} observations found. Not enough data to classify a new instinct.")

if __name__ == "__main__":
    execute_analysis()
