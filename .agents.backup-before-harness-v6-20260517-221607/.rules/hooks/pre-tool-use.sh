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
COMMAND_TEXT="$TOOL_NAME $TOOL_INPUT"
CONTEXT_PATH="$(task_context_json_path "$ROOT" "$SESSION" "$TASK_ID")"
RULES_FILE="$(approved_rules_file "$ROOT")"

CURRENT_TIER="T0"
APPROVAL_MODE="Never"

if [ -f "$CONTEXT_PATH" ]; then
    CURRENT_TIER="$(json_get "$CONTEXT_PATH" "routing.trust_tier" || printf 'T0\n')"
    APPROVAL_MODE="$(json_get "$CONTEXT_PATH" "routing.approval_mode" || printf 'Never\n')"
fi

REQUIRED_TIER="T0"
NEEDS_APPROVAL=false
REASON="safe-read"

if is_dangerous_command "$COMMAND_TEXT"; then
    REQUIRED_TIER="T3"
    NEEDS_APPROVAL=true
    REASON="dangerous-operation"
elif command_requires_network "$COMMAND_TEXT"; then
    REQUIRED_TIER="T2"
    NEEDS_APPROVAL=true
    REASON="external-network"
elif command_looks_mutating "$COMMAND_TEXT"; then
    REQUIRED_TIER="T1"
    REASON="workspace-mutation"
fi

append_task_event "$ROOT" "$SESSION" "$TASK_ID" "PreToolUse" "tool=$TOOL_NAME tier=$CURRENT_TIER required=$REQUIRED_TIER reason=$REASON"

if [ "$(trust_tier_rank "$CURRENT_TIER")" -lt "$(trust_tier_rank "$REQUIRED_TIER")" ]; then
    echo "[Agent OS PreToolUse] BLOCKED: task tier $CURRENT_TIER is below required tier $REQUIRED_TIER ($REASON)." >&2
    exit 2
fi

if [ "$NEEDS_APPROVAL" = true ] && flag_is_enabled approval_required "$ROOT"; then
    if [ "$REASON" = "dangerous-operation" ]; then
        echo "[Agent OS PreToolUse] APPROVAL REQUIRED: dangerous operation detected." >&2
        exit 2
    fi
    if ! command_prefix_allowed "$COMMAND_TEXT" "$RULES_FILE"; then
        echo "[Agent OS PreToolUse] APPROVAL REQUIRED: mode=$APPROVAL_MODE reason=$REASON command=$COMMAND_TEXT" >&2
        exit 2
    fi
fi

echo "[Agent OS PreToolUse] Allowed: tool=$TOOL_NAME task=$TASK_ID tier=$CURRENT_TIER"
