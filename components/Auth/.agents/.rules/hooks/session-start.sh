#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=.agents/hooks/lib.sh
source "$SCRIPT_DIR/lib.sh"

ROOT="$(resolve_project_root)"
SESSION="$(session_id)"

ensure_learning_layout "$ROOT"
ensure_memory_layout "$ROOT"
ensure_session_layout "$ROOT" "$SESSION"
ensure_trace_layout "$ROOT"
ensure_runtime_log_layout "$ROOT"
set_current_session_id "$ROOT" "$SESSION"

echo "[Agent OS] Session Starting. Verifying Governance Context..."
echo "[Agent OS] project_root=$ROOT session_id=$SESSION"

if [ -f "$ROOT/.agents/management/memories/memory_summary.md" ]; then
    echo "[Agent OS] Active memory_summary.md found. Injecting into context."
fi

date -Iseconds >> "$(runtime_logs_dir "$ROOT")/session-starts.log"

echo "[Agent OS] Governance boundaries successfully established."
