#!/usr/bin/env python3
# lint-governance.py — Agent Harness Governance & Semantic Architecture Linter
# Version: 4.2.0 (Hardened Enterprise)
#
# Enforces machine-executable governance, links consistency, and performs
# anti-dogmatic semantic quality audits on codebase abstractions (detecting hollow
# facades without blocking justified framework patterns).

import os
import sys
import json
import re

OUTPUT_DIR = ".agents/management/evidence/generated"
CONFIG_FILE = ".agents/config/project.json"

BANNED_SUFFIXES = ["Manager", "Helper", "Service", "Util", "Facade"]

def load_naming_allowances(target_dir):
    config_path = os.path.join(target_dir, CONFIG_FILE)
    if os.path.exists(config_path):
        try:
            with open(config_path, 'r', encoding='utf-8') as f:
                config = json.load(f)
                return config.get("naming_allowances", [])
        except:
            pass
    # Standard enterprise allowances defaults
    return ["ServiceProvider", "Migration", "Kernel", "Configuration", "Controller", "Application"]

def analyze_hollow_class(filepath, classname, content):
    # Strip comments to inspect raw structure
    cleaned = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    cleaned = re.sub(r'//.*', '', cleaned)
    
    # Simple semantic heuristics:
    # 1. Total lines of the file
    lines = [line.strip() for line in cleaned.split('\n') if line.strip()]
    line_count = len(lines)
    
    # 2. State tracking: Check if the class declares private/protected member variables
    has_state = False
    state_patterns = [
        r'\b(private|protected|public)\s+\$[a-zA-Z0-9_]+', # PHP state
        r'\b(private|protected|public|readonly)\s+[a-zA-Z0-9_]+\s*:', # TS state
        r'\bself\.[a-zA-Z0-9_]+\s*=' # Python state
    ]
    for p in state_patterns:
        if re.search(p, cleaned):
            has_state = True
            break
            
    # 3. Code forwarder detection: Check if methods are simple single-line wrappers
    # E.g., return $this->provider->method() or return provider.call()
    forwarders_count = len(re.findall(r'return\s+[^;\}]+->[a-zA-Z0-9_]+\([^)]*\);', cleaned))
    methods_count = len(re.findall(r'\b(public|protected|private)?\s*function\s+[a-zA-Z0-9_]+', cleaned))
    
    is_hollow = False
    reason = ""
    
    if line_count < 25 and not has_state:
        is_hollow = True
        reason = f"File is extremely short ({line_count} lines of code) and contains no encapsulated member state."
    elif methods_count > 0 and forwarders_count >= (methods_count * 0.8):
        is_hollow = True
        reason = f"Highly boilerplate forwarding pattern detected: {forwarders_count}/{methods_count} methods are simple wrappers."
        
    return is_hollow, reason

def audit_codebase_steering(target_dir, allowances):
    violations = []
    semantic_exceptions = []
    
    # Scan target directory for production source files (PHP, TS, JS, Go, Python)
    ignored_dirs = {
        ".git", ".agents", "vendor", "node_modules", "projects", 
        "tests", "storage", "bootstrap", "database", "dist", "build", "artifacts"
    }
    
    for root, dirs, files in os.walk(target_dir):
        # Filter out ignored directories
        dirs[:] = [d for d in dirs if d not in ignored_dirs and not d.startswith(".")]
        
        for file in files:
            ext = os.path.splitext(file)[1].lower()
            if ext not in [".php", ".ts", ".js", ".go", ".py"]:
                continue
                
            filepath = os.path.join(root, file)
            rel_path = os.path.relpath(filepath, target_dir)
            
            try:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
            except:
                continue
                
            # Regex to find class/interface declarations
            classes = re.findall(r'\b(class|interface)\s+([a-zA-Z0-9_]+)', content)
            
            for ctype, classname in classes:
                # Check for banned suffixes
                matched_suffix = None
                for suffix in BANNED_SUFFIXES:
                    if classname.endswith(suffix):
                        matched_suffix = suffix
                        break
                        
                if matched_suffix:
                    # Heuristic A: Explicit allowed naming exception
                    is_allowed = False
                    for allowed in allowances:
                        if classname.endswith(allowed):
                            is_allowed = True
                            break
                            
                    if is_allowed:
                        continue
                        
                    # Heuristic B: Semantic implementation thickness audit
                    is_hollow, reason = analyze_hollow_class(filepath, classname, content)
                    
                    if is_hollow:
                        violations.append(
                            f"BLOCKER: Hollow/fake '{ctype}' abstraction '{classname}' in {rel_path} uses a banned suffix '{matched_suffix}'.\n"
                            f"          └── Diagnostics: {reason}\n"
                            f"          └── Remediation: Eliminate redundant facade/forwarding boilerplate and implement direct, encapsulated behavior."
                        )
                    else:
                        semantic_exceptions.append(
                            f"ℹ️  Semantic Exception allowed: class '{classname}' in {rel_path} has complex state/behavior and is not a hollow facade."
                        )
                        
    return violations, semantic_exceptions

def lint_governance(target_dir="."):
    index_path = os.path.join(target_dir, OUTPUT_DIR, "governance-index.json")
    if not os.path.exists(index_path):
        print("❌ ERROR: governance-index.json missing. Run compile-governance.py first.")
        sys.exit(1)
        
    with open(index_path, 'r', encoding='utf-8') as f:
        index = json.load(f)
        
    errors = []
    
    # 1. Duplicate Rule Check
    if index.get("duplicate_rules"):
        for dr in index["duplicate_rules"]:
            errors.append(f"BLOCKER: Duplicate Rule ID '{dr['id']}' in clashing paths: {', '.join(dr['clashing_paths'])}")
            
    # 2. Dead Links Check
    graph = index.get("graph", {})
    for filepath, links in graph.items():
        base_dir = os.path.dirname(filepath)
        for link in links:
            if link.startswith("http"):
                continue
            resolved = os.path.normpath(os.path.join(base_dir, link))
            if not os.path.exists(os.path.join(target_dir, resolved)):
                errors.append(f"HIGH: Broken governance link in {filepath} -> {link}")
                
    # 3. Required Frontmatter
    files = index.get("files", {})
    for filepath, data in files.items():
        if "governance/core" in filepath and "status" not in data.get("frontmatter", {}):
            errors.append(f"MEDIUM: Missing required 'status' frontmatter in core governance: {filepath}")

    # 4. Anti-Dogmatic AI Quality Steering Audit
    allowances = load_naming_allowances(target_dir)
    print("🧠  [ENGINE] Executing Anti-Dogmatic Naming & Abstraction Audit...")
    steering_violations, exceptions = audit_codebase_steering(target_dir, allowances)
    
    for exc in exceptions:
        print(f"  {exc}")
        
    for viol in steering_violations:
        errors.append(viol)

    print("----------------------------------------------------------------------")
    if errors:
        print("❌ Governance Linting FAILED:")
        for e in errors:
            print(f"  - {e}")
        sys.exit(1)
    else:
        print("✅ Governance Linting PASSED.")
        sys.exit(0)

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    lint_governance(target)
