#!/bin/bash
# recursive-review-engine.sh — Agent Harness Deterministic Review Engine
# Version: 1.0.0
# Goal 2: Machine-executable recursive review with auto-classification.

set -e

TARGET_DIR="${1:-.}"
REVIEW_ID="REV-$(date +%Y%m%d-%H%M%S)"
EVIDENCE_DIR="$TARGET_DIR/.agents/management/evidence/reviews"

mkdir -p "$EVIDENCE_DIR"
RESULT_FILE="$EVIDENCE_DIR/$REVIEW_ID.json"

echo "🔎 Starting Recursive Review Engine (ID: $REVIEW_ID)..."

# Initializing review report
cat > "$RESULT_FILE" <<EOF
{
  "review_id": "$REVIEW_ID",
  "timestamp": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "target": "$TARGET_DIR",
  "findings": []
}
EOF

add_finding() {
    local severity="$1"
    local category="$2"
    local message="$3"
    local remediation="$4"

    # Use python to append to JSON (robust)
    python3 - <<EOF
import json
with open("$RESULT_FILE", "r") as f:
    data = json.load(f)
data["findings"].append({
    "severity": "$severity",
    "category": "$category",
    "message": "$message",
    "remediation": "$remediation"
})
with open("$RESULT_FILE", "w") as f:
    json.dump(data, f, indent=2)
EOF
}

# --- Engine 1: Version Integrity ---
echo "⚙️  Running: Version Integrity Check..."
if grep -r "Version: 1.1.0" "$TARGET_DIR/.agents/governance/" | grep -v "phases" >/dev/null 2>&1; then
    add_finding "BLOCKER" "VERSION_DRIFT" "Found stale Version 1.1.0 references in canonical governance." "Run truth-reconciliation pass and bump to 3.0.0."
else
    echo "✅ Version Integrity: OK"
fi

# --- Engine 2: Precedence Guard ---
echo "⚙️  Running: Precedence Guard..."
if ! grep "AGENTS.md" "$TARGET_DIR/AGENTS.md" | grep ".agents/AGENTS.md" >/dev/null 2>&1; then
    add_finding "HIGH" "PRECEDENCE_VIOLATION" "Root AGENTS.md does not point to .agents/AGENTS.md as master." "Update root AGENTS.md to follow the V3 precedence model."
fi

# --- Engine 3: Dashboard Bloat ---
echo "⚙️  Running: Dashboard Anti-Bloat..."
for f in "$TARGET_DIR/EVIDENCE/"*.md; do
    LINES=$(wc -l < "$f")
    if [ "$LINES" -gt 50 ]; then
        add_finding "MEDIUM" "DASHBOARD_BLOAT" "$f is too long ($LINES lines)." "Move implementation detail to .agents/management/evidence/ and keep dashboard concise."
    fi
done

# --- Final Summary ---
SEVERITIES=$(python3 -c "import json; d=json.load(open('$RESULT_FILE')); print(' '.join([f['severity'] for f in d['findings']]))")

if [[ $SEVERITIES == *"BLOCKER"* ]]; then
    STATUS="RED"
elif [[ $SEVERITIES == *"HIGH"* ]]; then
    STATUS="YELLOW"
else
    STATUS="GREEN"
fi

# Update final status in JSON
python3 - <<EOF
import json
with open("$RESULT_FILE", "r") as f:
    data = json.load(f)
data["status"] = "$STATUS"
with open("$RESULT_FILE", "w") as f:
    json.dump(data, f, indent=2)
EOF

echo "🏁 Review Complete. Status: $STATUS"
echo "📄 Report written to: $RESULT_FILE"

if [ "$STATUS" == "RED" ]; then
    exit 1
fi
