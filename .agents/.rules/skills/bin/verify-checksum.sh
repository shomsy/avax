#!/bin/bash
# verify-checksum.sh — Executable Skill
# Version: 1.0.0

FILE_PATH="$1"
EXPECTED_HASH="$2"
EVIDENCE_FILE=".agents/management/evidence/validation/checksums.json"

mkdir -p "$(dirname "$EVIDENCE_FILE")"

if [ ! -f "$FILE_PATH" ]; then
    echo "❌ Error: File not found."
    exit 1
fi

ACTUAL_HASH=$(sha256sum "$FILE_PATH" | cut -d' ' -f1)

if [ "$ACTUAL_HASH" == "$EXPECTED_HASH" ]; then
    STATUS="MATCH"
    echo "✅ Checksum MATCH."
else
    STATUS="MISMATCH"
    echo "❌ Checksum MISMATCH."
fi

# Write evidence
cat >> "$EVIDENCE_FILE" <<JSON
{
  "timestamp": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "skill": "verify-checksum",
  "file": "$FILE_PATH",
  "status": "$STATUS",
  "actual": "$ACTUAL_HASH",
  "expected": "$EXPECTED_HASH"
}
JSON
