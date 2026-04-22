#!/usr/bin/env python3
"""
Lint script to validate how-this-works.md files contain mermaid diagrams

Exit codes:
    0 - All how-this-works.md files have mermaid blocks
    1 - One or more files missing mermaid blocks
"""

import os
import sys
from pathlib import Path


def main():
    # Script is in scripts/, project root is parent
    script_path = Path(__file__).resolve()
    project_root = script_path.parent.parent

    print("=== Mermaid Validation for how-this-works.md ===\n")

    original_cwd = os.getcwd()
    os.chdir(project_root)

    all_files = []
    for path in Path(".").rglob("how-this-works.md"):
        rel_path = str(path)
        if ".agents/" not in rel_path:
            all_files.append(rel_path)

    all_files.sort()
    total = len(all_files)

    os.chdir(original_cwd)

    if total == 0:
        print("No how-this-works.md files found.")
        return 0

    print(f"Found {total} how-this-works.md files\n")

    missing = 0
    for file_path in all_files:
        full_path = project_root / file_path
        content = full_path.read_text(encoding="utf-8", errors="ignore")

        if "```mermaid" in content:
            print(f"PASS: {os.path.basename(file_path)}")
        else:
            print(f"FAIL: {os.path.basename(file_path)} - MISSING mermaid block")
            missing += 1

    print(f"\n=== Summary ===")
    print(f"Total files: {total}")
    print(f"Missing mermaid: {missing}")

    if missing > 0:
        print(
            f"\nFAIL: {missing} how-this-works.md file(s) missing mermaid diagram block"
        )
        return 1

    print(f"\nPASS: All how-this-works.md files have mermaid diagrams")
    return 0


if __name__ == "__main__":
    sys.exit(main())
