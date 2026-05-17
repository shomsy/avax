#!/usr/bin/env python3
# evidence-lifecycle.py — Agent Harness Hardened Evidence Lifecycle Engine
# Version: 4.2.0 (Hardened Enterprise)
#
# Manages retention policies, performs raw trace garbage collection, compacts
# expired logs, establishes cryptographic parent-child lineage traces, and
# compiles the validation provenance graph.

import os
import sys
import json
import time
import hashlib
import glob

EVIDENCE_DIR = ".agents/management/evidence"
ARCHIVE_DIR = ".agents/management/evidence/archive"
RETENTION_DAYS = 7  # Maximum age of individual raw validation runs in days
RETENTION_SECONDS = RETENTION_DAYS * 24 * 3600

def get_file_sha256(filepath):
    sha = hashlib.sha256()
    with open(filepath, 'rb') as f:
        while True:
            data = f.read(65536)
            if not data:
                break
            sha.update(data)
    return sha.hexdigest()

def execute_lifecycle(target_dir="."):
    raw_dir = os.path.normpath(os.path.join(target_dir, EVIDENCE_DIR, "validation"))
    archive_path = os.path.normpath(os.path.join(target_dir, ARCHIVE_DIR))
    os.makedirs(archive_path, exist_ok=True)
    
    current_time = time.time()
    
    compaction_events = []
    garbage_collected_files = []
    
    print("======================================================================")
    print("🧹  AGENT HARNESS OS EVIDENCE LIFECYCLE CONTROLLER")
    print("======================================================================")
    
    # 1. Scan for raw validation runs
    if not os.path.exists(raw_dir):
        print("ℹ️  No validation raw evidence folder detected.")
        sys.exit(0)

    json_files = glob.glob(os.path.join(raw_dir, "*.json"))
    print(f"🔍 Scanning {len(json_files)} raw validation traces for retention limit...")
    
    for f in json_files:
        mtime = os.path.getmtime(f)
        age_seconds = current_time - mtime
        
        # Check if expired
        if age_seconds > RETENTION_SECONDS:
            try:
                with open(f, 'r', encoding='utf-8') as rf:
                    data = json.load(rf)
                
                checksum = get_file_sha256(f)
                compaction_events.append({
                    "original_file": os.path.basename(f),
                    "original_hash": checksum,
                    "timestamp": data.get("timestamp") or mtime,
                    "command": data.get("command", "unknown"),
                    "status": data.get("status", "unknown"),
                    "details": data
                })
                garbage_collected_files.append(f)
            except Exception as e:
                print(f"⚠️  Error reading file during cleanup: {f} ({str(e)})")

    # 2. Compact and seal if expired traces exist
    if compaction_events:
        compactor_timestamp = int(current_time)
        compactor_filename = f"compaction-{compactor_timestamp}.json"
        compactor_file_path = os.path.join(archive_path, compactor_filename)
        
        # Calculate parent-child cryptographic lineage trace
        hasher = hashlib.sha256()
        for e in compaction_events:
            hasher.update(e["original_hash"].encode('utf-8'))
        lineage_seal = hasher.hexdigest()
        
        compaction_manifest = {
            "compactor_timestamp": compactor_timestamp,
            "compactor_run_mtime": time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(current_time)),
            "retention_policy_days": RETENTION_DAYS,
            "compacted_files_count": len(compaction_events),
            "cryptographic_lineage_seal": lineage_seal,
            "traces": compaction_events
        }
        
        with open(compactor_file_path, 'w', encoding='utf-8') as wf:
            json.dump(compaction_manifest, wf, indent=2)
            
        print(f"📦  Consolidated {len(compaction_events)} expired raw traces.")
        print(f"🔒  Sealed compaction: {compactor_file_path}")
        print(f"🔑  SHA-256 Lineage Seal: {lineage_seal}")
        
        # 3. Garbage collect (delete) raw expired source files
        for f in garbage_collected_files:
            try:
                os.remove(f)
            except Exception as e:
                print(f"⚠️  Failed to delete: {f} ({str(e)})")
        print(f"🗑️  Garbage collection complete. Released {len(garbage_collected_files)} raw log files.")
    else:
        print("✅  All validation traces are active and within the retention period (no cleanup needed).")

    # 4. Generate provenance graph
    provenance_graph = {"nodes": {}, "edges": []}
    
    # Trace raw active files
    active_jsons = glob.glob(os.path.join(raw_dir, "*.json"))
    for f in active_jsons:
        rel_f = os.path.relpath(f, target_dir)
        try:
            with open(f, 'r', encoding='utf-8') as rf:
                data = json.load(rf)
            provenance_graph["nodes"][rel_f] = {
                "type": "active_raw_trace",
                "checksum": get_file_sha256(f),
                "timestamp": data.get("timestamp", os.path.getmtime(f)),
                "command": data.get("command", "unknown"),
                "git_commit": data.get("git_commit", "unknown")
            }
        except:
            pass

    # Trace archived compactions
    archive_jsons = glob.glob(os.path.join(archive_path, "compaction-*.json"))
    for f in archive_jsons:
        rel_f = os.path.relpath(f, target_dir)
        try:
            with open(f, 'r', encoding='utf-8') as rf:
                data = json.load(rf)
            provenance_graph["nodes"][rel_f] = {
                "type": "compacted_archive",
                "checksum": get_file_sha256(f),
                "timestamp": data.get("compactor_timestamp"),
                "lineage_seal": data.get("cryptographic_lineage_seal"),
                "files_count": data.get("compacted_files_count")
            }
            # Add dependency edges from compaction to the compacted original files
            for trace in data.get("traces", []):
                child_id = trace.get("original_file")
                provenance_graph["edges"].append({
                    "from": rel_f,
                    "to": child_id,
                    "type": "compacts"
                })
        except:
            pass

    prov_path = os.path.normpath(os.path.join(target_dir, EVIDENCE_DIR, "generated", "governance-provenance-graph.json"))
    with open(prov_path, "w", encoding='utf-8') as wf:
        json.dump(provenance_graph, wf, indent=2)
    print(f"🌐  Provenance Graph updated: {prov_path} ({len(provenance_graph['nodes'])} nodes tracked)")
    print("======================================================================")

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "."
    execute_lifecycle(target)
