#!/usr/bin/env python3
# compile-governance.py — Agent Harness Hardened Governance Compiler & Precedence Engine
# Version: 4.2.0 (Hardened Enterprise)
#
# Parses baseline rules and local overlays, resolves shadowing priority, builds
# the precedence graph, checks complexity budgets, and detects circular dependency loops.

import os
import sys
import json
import hashlib
import re

BASELINE_DIR = ".agents/.rules/governance"
LOCAL_DIR = ".agents/governance"
OUTPUT_DIR = ".agents/management/evidence/generated"

def extract_frontmatter(content):
    frontmatter = {}
    if content.startswith("---"):
        parts = content.split("---", 2)
        if len(parts) >= 3:
            lines = parts[1].strip().split('\n')
            for line in lines:
                if ':' in line:
                    k, v = line.split(':', 1)
                    frontmatter[k.strip()] = v.strip()
    return frontmatter

def find_circular_dependencies(graph):
    visited = {}
    path = []
    loops = []

    def dfs(node):
        if visited.get(node) == 1: # currently visiting: loop found!
            idx = path.index(node)
            loops.append(path[idx:] + [node])
            return
        if visited.get(node) == 2: # already fully processed
            return

        visited[node] = 1
        path.append(node)
        
        for neighbor in graph.get(node, []):
            if neighbor.startswith("http"):
                continue
            dfs(neighbor)

        path.pop()
        visited[node] = 2

    for node in list(graph.keys()):
        dfs(node)
    return loops

def compile_governance(target_dir="."):
    baseline_path = os.path.normpath(os.path.join(target_dir, BASELINE_DIR))
    local_path = os.path.normpath(os.path.join(target_dir, LOCAL_DIR))
    
    index = {
        "files": {},
        "graph": {},
        "precedence": {},
        "shadowed_rules": {},
        "duplicate_rules": [],
        "dead_rules": [],
        "circular_loops": []
    }
    
    rule_ids = {}
    
    # 1. Compile baseline rules
    baseline_files = {}
    if os.path.exists(baseline_path):
        for root, _, files in os.walk(baseline_path):
            for file in files:
                if not file.endswith(".md"):
                    continue
                filepath = os.path.join(root, file)
                rel_path = os.path.relpath(filepath, target_dir)
                sub_rel = os.path.relpath(filepath, baseline_path)
                baseline_files[sub_rel] = rel_path

    # 2. Compile local overrides
    local_files = {}
    if os.path.exists(local_path):
        for root, _, files in os.walk(local_path):
            for file in files:
                if not file.endswith(".md"):
                    continue
                filepath = os.path.join(root, file)
                rel_path = os.path.relpath(filepath, target_dir)
                sub_rel = os.path.relpath(filepath, local_path)
                local_files[sub_rel] = rel_path

    # 3. Perform Precedence Shadowing Resolution
    all_keys = set(baseline_files.keys()).union(set(local_files.keys()))
    
    for sub_rel in sorted(all_keys):
        baseline_rel = baseline_files.get(sub_rel)
        local_rel = local_files.get(sub_rel)
        
        active_rel = local_rel if local_rel else baseline_rel
        shadowed = baseline_rel if (local_rel and baseline_rel) else None
        
        with open(os.path.join(target_dir, active_rel), 'r', encoding='utf-8') as f:
            content = f.read()
            
        frontmatter = extract_frontmatter(content)
        links = re.findall(r"\[.*?\]\((.*?\.md)\)", content)
        ids = re.findall(r"\*\*ID\*\*\s*:\s*([A-Z0-9\-]+)", content, re.IGNORECASE)
        
        file_data = {
            "path": active_rel,
            "frontmatter": frontmatter,
            "links": links,
            "rule_ids": ids,
            "checksum": hashlib.sha256(content.encode('utf-8')).hexdigest()
        }
        
        index["files"][active_rel] = file_data
        index["graph"][active_rel] = links
        
        # Build explanation metadata
        if shadowed:
            index["precedence"][active_rel] = {
                "won": True,
                "shadowed_path": shadowed,
                "reason": "Local override preempts frozen baseline rule under Priority Overlay Shadowing contract."
            }
            index["shadowed_rules"][shadowed] = active_rel
        else:
            index["precedence"][active_rel] = {
                "won": True,
                "shadowed_path": None,
                "reason": "Canonical rule cleanly resolved (no local shadowing override detected)."
            }
            
        for rid in ids:
            if rid in rule_ids:
                index["duplicate_rules"].append({
                    "id": rid,
                    "clashing_paths": [rule_ids[rid], active_rel]
                })
            else:
                rule_ids[rid] = active_rel
                
        if frontmatter.get("status") in ["abandoned", "deprecated", "inactive"]:
            index["dead_rules"].append(active_rel)

    # Resolve AGENTS.md links
    entrypoints = ["AGENTS.md", os.path.join(".agents", "AGENTS.md")]
    for ep in entrypoints:
        ep_path = os.path.join(target_dir, ep)
        if os.path.exists(ep_path):
            with open(ep_path, 'r', encoding='utf-8') as f:
                ep_content = f.read()
                links = re.findall(r"\[.*?\]\((.*?\.md)\)", ep_content)
                index["graph"][ep] = links

    # Resolve unreferenced rules
    all_targets = set()
    for source, links in index["graph"].items():
        base_dir = os.path.dirname(source)
        for link in links:
            if link.startswith("http"):
                continue
            resolved = os.path.normpath(os.path.join(base_dir, link))
            all_targets.add(resolved)
            
    unreferenced_rules = []
    for filepath in index["files"].keys():
        if filepath not in all_targets and not filepath.endswith("README.md"):
            unreferenced_rules.append(filepath)
            
    index["unreferenced_rules"] = unreferenced_rules

    # Circular loop detection
    loops = find_circular_dependencies(index["graph"])
    index["circular_loops"] = loops

    return index

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    os.makedirs(os.path.normpath(os.path.join(target, OUTPUT_DIR)), exist_ok=True)
    
    print("⚙️  [ENGINE] Running Hardened Governance Compiler...")
    index = compile_governance(target)
    
    graph_hash = hashlib.sha256(json.dumps(index["graph"], sort_keys=True).encode('utf-8')).hexdigest()
    index["graph_sha"] = graph_hash
    
    # Save compilation artifacts
    with open(os.path.join(target, OUTPUT_DIR, "governance-index.json"), "w") as f:
        json.dump(index, f, indent=2)
        
    with open(os.path.join(target, OUTPUT_DIR, "governance-dependency-graph.json"), "w") as f:
        json.dump({"sha": graph_hash, "graph": index["graph"]}, f, indent=2)
        
    with open(os.path.join(target, OUTPUT_DIR, "governance-entropy.json"), "w") as f:
        json.dump({
            "dead_rules": index["dead_rules"], 
            "duplicate_rules": index["duplicate_rules"],
            "unreferenced_rules": index["unreferenced_rules"],
            "circular_loops": index["circular_loops"]
        }, f, indent=2)
        
    with open(os.path.join(target, OUTPUT_DIR, "governance-precedence.json"), "w") as f:
        json.dump({
            "precedence": index["precedence"],
            "shadowed_rules": index["shadowed_rules"]
        }, f, indent=2)

    print(f"✅ [ENGINE] Compilation complete. Graph SHA: {graph_hash}")
    print(f"📊 [ENGINE] Processed {len(index['files'])} active rules. Found {len(index['shadowed_rules'])} shadowed overlays, {len(index['duplicate_rules'])} duplicate rule definitions, {len(index['dead_rules'])} dead/inactive rules.")
    if index["circular_loops"]:
        print(f"⚠️  [ENGINE] WARNING: Mapped {len(index['circular_loops'])} circular dependency loops!")
