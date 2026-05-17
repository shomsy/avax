#!/usr/bin/env python3
import sys
import os
import json
import datetime

# update-evidence.py — Executable Skill
# Version: 1.0.0

def main():
    if len(sys.argv) < 3:
        print("Usage: update-evidence.py <type> <json_content>")
        sys.exit(1)
    
    e_type = sys.argv[1]
    content = json.loads(sys.argv[2])
    
    timestamp = datetime.datetime.utcnow().strftime("%Y%m%d-%H%M%S")
    file_path = f".agents/management/evidence/{e_type}/EV-{timestamp}.json"
    
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    
    with open(file_path, "w") as f:
        json.dump(content, f, indent=2)
    
    print(f"✅ Evidence recorded: {file_path}")

if __name__ == "__main__":
    main()
