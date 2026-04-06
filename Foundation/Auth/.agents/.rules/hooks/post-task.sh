#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=.agents/hooks/lib.sh
source "$SCRIPT_DIR/lib.sh"

ROOT="$(resolve_project_root)"
SESSION="$(session_id)"
TASK_ID=""
STATUS="completed"
SUMMARY=""
VALIDATION=""

while [ "$#" -gt 0 ]; do
    case "$1" in
        --task-id)
            TASK_ID="${2:-}"
            shift 2
            ;;
        --session-id)
            SESSION="${2:-}"
            shift 2
            ;;
        --status)
            STATUS="${2:-}"
            shift 2
            ;;
        --summary)
            SUMMARY="${2:-}"
            shift 2
            ;;
        --validation)
            VALIDATION="${2:-}"
            shift 2
            ;;
        *)
            echo "Unknown argument: $1" >&2
            exit 2
            ;;
    esac
done

ensure_session_layout "$ROOT" "$SESSION"
ensure_trace_layout "$ROOT"
set_current_session_id "$ROOT" "$SESSION"

if [ -z "$TASK_ID" ]; then
    TASK_ID="$(current_task_id "$ROOT" "$SESSION")"
fi

JSON_PATH="$(task_context_json_path "$ROOT" "$SESSION" "$TASK_ID")"
RESULT_PATH="$(task_result_json_path "$ROOT" "$SESSION" "$TASK_ID")"

if [ ! -f "$JSON_PATH" ]; then
    echo "No task context found for task=$TASK_ID session=$SESSION" >&2
    exit 1
fi

TRACE_ID="$(json_get "$JSON_PATH" "trace_id" || true)"
PRIMARY_LANE="$(json_get "$JSON_PATH" "routing.primary_lane" || true)"
PIPELINE="$(json_get "$JSON_PATH" "routing.pipeline" || true)"
TRUST_TIER="$(json_get "$JSON_PATH" "routing.trust_tier" || true)"

python3 - "$JSON_PATH" "$RESULT_PATH" "$STATUS" "$SUMMARY" "$VALIDATION" <<'PY'
import json
import sys
from datetime import datetime
from pathlib import Path

context_path = Path(sys.argv[1])
result_path = Path(sys.argv[2])
status = sys.argv[3]
summary = sys.argv[4]
validation = sys.argv[5]

payload = json.loads(context_path.read_text(encoding="utf-8"))
result = {
    "timestamp": datetime.now().astimezone().isoformat(timespec="seconds"),
    "trace_id": payload.get("trace_id"),
    "task_id": payload.get("task_id"),
    "status": status,
    "summary": summary,
    "validation": validation,
    "primary_lane": payload.get("routing", {}).get("primary_lane"),
    "pipeline": payload.get("routing", {}).get("pipeline"),
}
result_path.parent.mkdir(parents=True, exist_ok=True)
result_path.write_text(json.dumps(result, indent=2, ensure_ascii=True) + "\n", encoding="utf-8")
PY

append_task_event "$ROOT" "$SESSION" "$TASK_ID" "PostTask" "status=$STATUS validation=$(summarize_text "$VALIDATION" 120)"

{
    printf '\n## Task Result — %s\n\n' "$(iso_timestamp)"
    printf -- '- Task ID: `%s`\n' "$TASK_ID"
    printf -- '- Trace ID: `%s`\n' "$TRACE_ID"
    printf -- '- Status: `%s`\n' "$STATUS"
    printf -- '- Pipeline: `%s`\n' "$PIPELINE"
    printf -- '- Summary: %s\n' "$(summarize_text "$SUMMARY" 240)"
    if [ -n "$VALIDATION" ]; then
        printf -- '- Validation: `%s`\n' "$(summarize_text "$VALIDATION" 240)"
    fi
} >> "$(session_dir "$ROOT" "$SESSION")/session_memory.md"

{
    printf -- '- %s | trace=%s | task=%s | lane=%s | pipeline=%s | tier=%s | artifact=`%s` | outcome=%s\n' \
        "$(iso_timestamp)" "$TRACE_ID" "$TASK_ID" "$PRIMARY_LANE" "$PIPELINE" "$TRUST_TIER" "$RESULT_PATH" "$STATUS"
} >> "$(trace_reports_file "$ROOT")"

if [ -f "$(current_task_pointer_file "$ROOT" "$SESSION")" ]; then
    current_pointer="$(sed -n '1p' "$(current_task_pointer_file "$ROOT" "$SESSION")")"
    if [ "$current_pointer" = "$TASK_ID" ]; then
        rm -f "$(current_task_pointer_file "$ROOT" "$SESSION")"
    fi
fi

echo "[Agent OS PostTask] Task closed."
echo "[Agent OS PostTask] task=$TASK_ID trace=$TRACE_ID status=$STATUS result_json=$RESULT_PATH"
