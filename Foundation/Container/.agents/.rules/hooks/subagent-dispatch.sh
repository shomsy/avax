#!/usr/bin/env bash
# Sub-Agent Dispatcher - Context-Pruned Launcher
# Usage: ./subagent-dispatch.sh --task "Fix bug" --files "src/app.ts,docs/api.md" --budget "10k"

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=.agents/hooks/lib.sh
source "$SCRIPT_DIR/lib.sh"

PROJECT_ROOT="$(resolve_project_root)"
SESSION_ID="$(session_id)"
TASK_ID="${AGENT_HARNESS_TASK_ID:-task-$(date +%Y%m%d%H%M%S)-$$}"
TASK_DIR="$(task_dir "$PROJECT_ROOT" "$SESSION_ID" "$TASK_ID")"
TASK_MANDATE=""
TOKEN_BUDGET=""
TARGET_FILES=()
CLIENT_PROFILE=""

# Parse arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --task) TASK_MANDATE="$2"; shift ;;
        --files) IFS=',' read -ra TARGET_FILES <<< "$2"; shift ;;
        --budget) TOKEN_BUDGET="$2"; shift ;;
        --client) CLIENT_PROFILE="$2"; shift ;;
        --session-id) SESSION_ID="$2"; shift ;;
        --task-id) TASK_ID="$2"; shift ;;
        *) echo "Unknown parameter: $1"; exit 1 ;;
    esac
    shift
done

CLIENT_PROFILE="${CLIENT_PROFILE:-generic}"

if [ -z "$TASK_MANDATE" ]; then
    echo "subagent-dispatch.sh requires --task" >&2
    exit 1
fi

TASK_DIR="$(task_dir "$PROJECT_ROOT" "$SESSION_ID" "$TASK_ID")"
mkdir -p "$TASK_DIR"

python3 - "$TASK_DIR/subtask.json" "$TASK_ID" "$SESSION_ID" "$TASK_MANDATE" "$TOKEN_BUDGET" "$CLIENT_PROFILE" "${TARGET_FILES[@]}" <<'PY'
import json
import sys
from datetime import datetime, timezone
from pathlib import Path

output = Path(sys.argv[1])
task_id = sys.argv[2]
session_id = sys.argv[3]
mandate = sys.argv[4]
budget = sys.argv[5]
client_profile = sys.argv[6]
files = sys.argv[7:]

payload = {
    "task_id": task_id,
    "session_id": session_id,
    "mandate": mandate,
    "budget": budget,
    "client_profile": client_profile,
    "files": files,
    "timestamp": datetime.now(timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z"),
    "status": "pending",
}
output.write_text(json.dumps(payload, indent=2, ensure_ascii=True) + "\n", encoding="utf-8")
PY

{
    printf '# Sub-Agent Context - PRUNED\n\n'
    printf '## Mandate\n'
    printf '%s\n\n' "$TASK_MANDATE"
    printf '## Governance (P0 Only)\n'
    printf 'Refer to AGENTS.md for the primary contract.\n\n'
    printf '## Token Budget\n'
    printf '%s\n\n' "${TOKEN_BUDGET:-unspecified}"
    printf '## Targeted Source Code\n'
} > "$TASK_DIR/pruned-context.md"

for file in "${TARGET_FILES[@]}"; do
    if [ -f "$PROJECT_ROOT/$file" ]; then
        printf '\n### File: %s\n' "$file" >> "$TASK_DIR/pruned-context.md"
        printf '```text\n' >> "$TASK_DIR/pruned-context.md"
        cat "$PROJECT_ROOT/$file" >> "$TASK_DIR/pruned-context.md"
        printf '\n```\n' >> "$TASK_DIR/pruned-context.md"
    else
        echo "Warning: File $file not found." >&2
    fi
done

echo "--- Dispatching Sub-Agent ---"
echo "Project Root: $PROJECT_ROOT"
echo "Session:      $SESSION_ID"
echo "Task ID:      $TASK_ID"
echo "Mandate:      $TASK_MANDATE"
echo "Budget:       ${TOKEN_BUDGET:-unspecified}"
echo "Client:       $CLIENT_PROFILE"
echo "Files:        ${TARGET_FILES[*]:-none}"
echo "Sub-task ready at: $TASK_DIR"
echo "Use 'pruned-context.md' as the prompt for the sub-agent session."
