#!/usr/bin/env python3
# replay-evidence.py — Agent Harness Evidence Replay Engine
# Replays validation commands to prevent fake FULL_GREEN status.

import os
import sys
import json
import subprocess
import glob

EVIDENCE_DIR = ".agents/management/evidence"

def replay_validation(target_dir="."):
    val_dir = os.path.join(target_dir, EVIDENCE_DIR, "validation")
    if not os.path.exists(val_dir):
        print("ℹ️ No validation evidence to replay.")
        return True

    errors = []
    
    for f in glob.glob(os.path.join(val_dir, "*.json")):
        with open(f, 'r') as file:
            data = json.load(file)
            
        # Example validation format:
        # { "command": "./verify-governance.sh", "expected_exit": 0, "status": "PASS" }
        command = data.get("command")
        expected = data.get("expected_exit", 0)
        status = data.get("status")
        
        if command and status == "PASS":
            print(f"▶️ Replaying: {command} (from {f})")
            try:
                result = subprocess.run(command, shell=True, capture_output=True, text=True, cwd=target_dir)
                if result.returncode != expected:
                    errors.append(f"FAKE EVIDENCE DETECTED: {command} returned {result.returncode}, expected {expected}")
            except Exception as e:
                errors.append(f"EXECUTION FAILED: {command} - {str(e)}")
                
    if errors:
        print("❌ Evidence Replay FAILED:")
        for e in errors:
            print(f"  - {e}")
        return False
    else:
        print("✅ Evidence Replay PASSED. All claims verified.")
        return True

def generate_event_stream(target_dir="."):
    events = []
    
    # Aggregate all JSON evidence into a timeline
    for root, _, files in os.walk(os.path.join(target_dir, EVIDENCE_DIR)):
        for f in files:
            if f.endswith(".json"):
                path = os.path.join(root, f)
                try:
                    with open(path, 'r') as file:
                        data = json.load(file)
                    
                    # Try to extract timestamp
                    ts = data.get("timestamp") or os.path.getctime(path)
                    events.append({
                        "timestamp": ts,
                        "type": os.path.basename(root),
                        "source": path,
                        "data": data
                    })
                except:
                    pass
                    
    events.sort(key=lambda x: str(x.get("timestamp", "")))
    
    out_path = os.path.join(target_dir, EVIDENCE_DIR, "generated", "governance-event-stream.json")
    with open(out_path, "w") as f:
        json.dump(events, f, indent=2)
    print(f"📅 Generated Event Stream: {out_path} ({len(events)} events)")

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    
    generate_event_stream(target)
    
    if not replay_validation(target):
        sys.exit(1)
    sys.exit(0)
