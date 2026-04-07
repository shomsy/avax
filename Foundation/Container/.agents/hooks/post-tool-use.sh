#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=.agents/hooks/lib.sh
source "$SCRIPT_DIR/lib.sh"

ROOT="$(resolve_project_root)"
SESSION="$(session_id)"
TASK_ID="$(current_task_id "$ROOT" "$SESSION")"
TOOL_NAME="${1:-unknown}"
TOOL_INPUT="${2:-}"
TOOL_OUTPUT="${3:-}"

ensure_learning_layout "$ROOT"

PROJECT_ID="$(project_id "$ROOT")"
TS="$(iso_timestamp)"
INPUT_SUMMARY="$(summarize_text "$TOOL_INPUT" 240)"
OUTPUT_SUMMARY="$(summarize_text "$TOOL_OUTPUT" 180)"

log_json_observation "$TS" "$TOOL_NAME" "$INPUT_SUMMARY" "$OUTPUT_SUMMARY" "$PROJECT_ID" "$SESSION" >> "$(learning_dir "$ROOT")/observations.jsonl"
append_task_event "$ROOT" "$SESSION" "$TASK_ID" "PostToolUse" "tool=$TOOL_NAME output=$(summarize_text "$TOOL_OUTPUT" 80)"

echo "[Agent OS PostToolUse] Captured $TOOL_NAME usage for continuous learning."
