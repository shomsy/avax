#!/bin/bash
# =============================================================================
# Supervisor Orchestration Script
# 
# Main entry point for the sub-agent orchestration system.
# Implements the Supervisor role: receives task, routes to sub-agents, 
# validates artifacts, and manages the execution flow.
#
# Usage:
#   ./supervisor.sh --prompt "Fix the login bug"
#   ./supervisor.sh --prompt-file /path/to/prompt.txt
#   ./supervisor.sh --task-id custom-task-id --prompt "..."
#
# Environment:
#   AGENT_HARNESS_PROJECT_ROOT   - Project root (optional)
#   AGENT_HARNESS_SESSION_ID     - Session ID (optional)
#   AGENT_HARNESS_MODEL_MAPPER   - Model for mapper agent
#   AGENT_HARNESS_MODEL_EXECUTOR - Model for executor agent
#   AGENT_HARNESS_MODEL_REVIEWER - Model for reviewer agent
# =============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/lib.sh"

ROOT="$(resolve_project_root)"
SESSION_ID="$(session_id)"
TASK_ID=""
PROMPT=""
PROMPT_FILE=""
DRY_RUN=false
VERBOSE=false

MODEL_MAPPER="${AGENT_HARNESS_MODEL_MAPPER:-gpt-4o-mini}"
MODEL_EXECUTOR="${AGENT_HARNESS_MODEL_EXECUTOR:-gpt-4o}"
MODEL_REVIEWER="${AGENT_HARNESS_MODEL_REVIEWER:-gpt-4o}"

while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --prompt)
            PROMPT="$2"
            shift 2
            ;;
        --prompt-file)
            PROMPT_FILE="$2"
            shift 2
            ;;
        --task-id)
            TASK_ID="$2"
            shift 2
            ;;
        --session-id)
            SESSION_ID="$2"
            shift 2
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --verbose|-v)
            VERBOSE=true
            shift
            ;;
        --help|-h)
            echo "Usage: supervisor.sh [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --prompt TEXT        Task prompt (mutually exclusive with --prompt-file)"
            echo "  --prompt-file FILE   Read prompt from file"
            echo "  --task-id ID         Custom task ID (optional)"
            echo "  --session-id ID      Session ID (optional)"
            echo "  --dry-run            Show routing decision without executing"
            echo "  --verbose, -v        Verbose output"
            echo ""
            echo "Environment:"
            echo "  AGENT_HARNESS_MODEL_MAPPER      Model for mapper (default: gpt-4o-mini)"
            echo "  AGENT_HARNESS_MODEL_EXECUTOR    Model for executor (default: gpt-4o)"
            echo "  AGENT_HARNESS_MODEL_REVIEWER    Model for reviewer (default: gpt-4o)"
            exit 0
            ;;
        *)
            echo "Unknown argument: $1" >&2
            exit 1
            ;;
    esac
done

if [[ -z "$PROMPT" ]] && [[ -z "$PROMPT_FILE" ]]; then
    echo "Error: Either --prompt or --prompt-file is required" >&2
    exit 1
fi

if [[ -n "$PROMPT_FILE" ]]; then
    if [[ ! -f "$PROMPT_FILE" ]]; then
        echo "Error: Prompt file not found: $PROMPT_FILE" >&2
        exit 1
    fi
    PROMPT="$(cat "$PROMPT_FILE")"
fi

ensure_learning_layout "$ROOT"
ensure_memory_layout "$ROOT"
ensure_session_layout "$ROOT" "$SESSION_ID"
ensure_trace_layout "$ROOT"
set_current_session_id "$ROOT" "$SESSION_ID"

if [[ -z "$TASK_ID" ]]; then
    TASK_ID="task-$(date +%Y%m%d%H%M%S)-$$-$(hash_string "$PROMPT" | head -c 8)"
fi
mkdir -p "$(task_dir "$ROOT" "$SESSION_ID" "$TASK_ID")"
set_current_task_id "$ROOT" "$SESSION_ID" "$TASK_ID"

log_event() {
    local level="$1"
    local message="$2"
    if [[ "$VERBOSE" == "true" ]] || [[ "$level" == "ERROR" ]]; then
        echo "[$(iso_timestamp)] [$level] $message" >&2
    fi
}

log_event "INFO" "Supervisor started - Task: $TASK_ID, Session: $SESSION_ID"
log_event "INFO" "Prompt: $(summarize_text "$PROMPT" 100)"

resolve_task_context() {
    local prompt="$1"
    local task_id="$2"
    local session_id="$3"
    
    local json_path="$(task_context_json_path "$ROOT" "$session_id" "$task_id")"
    local subagents_dir="$(task_subagents_dir "$ROOT" "$session_id" "$task_id")"
    
    python3 "$SCRIPT_DIR/resolve-task-context.py" \
        --project-root "$ROOT" \
        --session-id "$session_id" \
        --task-id "$task_id" \
        --write-json "$json_path" \
        --write-subagents-dir "$subagents_dir" \
        --prompt "$prompt" >/dev/null 2>&1
    
    echo "$json_path"
}

classify_task() {
    local json_path="$1"
    
    local primary_lane
    primary_lane=$(json_get "$json_path" "routing.primary_lane" || echo "unknown")
    
    local task_kind
    task_kind=$(json_get "$json_path" "routing.task_kind" || echo "unknown")
    
    local delegation_recommended
    delegation_recommended=$(json_get "$json_path" "delegation.recommended" || echo "false")
    
    local subagent_count
    subagent_count=$(python3 - "$json_path" <<'PY'
import json
import sys
path = sys.argv[1]
payload = json.loads(open(path).read())
print(len(payload.get("delegation", {}).get("subagents", [])))
PY
)
    
    echo "$primary_lane|$task_kind|$delegation_recommended|$subagent_count"
}

determine_role() {
    local primary_lane="$1"
    local task_kind="$2"
    local prompt="$3"
    
    local lower_prompt
    lower_prompt=$(echo "$prompt" | tr '[:upper:]' '[:lower:]')
    
    if echo "$lower_prompt" | grep -qE "(map|where|how does|find|explore|trace|understand|explain)"; then
        echo "mapper"
        return
    fi
    
    if echo "$lower_prompt" | grep -qE "(docs|documentation|api|verzija|version|learn about|read about)"; then
        echo "researcher"
        return
    fi
    
    if echo "$lower_prompt" | grep -qE "(review|check|verify|audit|risks|security scan|look over)"; then
        echo "reviewer"
        return
    fi
    
    if echo "$lower_prompt" | grep -qE "(implement|fix|refactor|change|modify|add|create|update|bug|error|delete|remove)"; then
        echo "executor"
        return
    fi
    
    if echo "$lower_prompt" | grep -qE "(ci|build|deploy|failed|log|palo|test failure|build error)"; then
        echo "ci-triage"
        return
    fi
    
    echo "mapper"
}

json_path=$(resolve_task_context "$PROMPT" "$TASK_ID" "$SESSION_ID")
log_event "INFO" "Task context resolved: $json_path"

classification=$(classify_task "$json_path")
PRIMARY_LANE=$(echo "$classification" | cut -d'|' -f1)
TASK_KIND=$(echo "$classification" | cut -d'|' -f2)
DELEGATION_REC=$(echo "$classification" | cut -d'|' -f3)
SUBAGENT_COUNT=$(echo "$classification" | cut -d'|' -f4)

log_event "INFO" "Classification: lane=$PRIMARY_LANE, kind=$TASK_KIND, delegation=$DELEGATION_REC, agents=$SUBAGENT_COUNT"

ROLE=$(determine_role "$PRIMARY_LANE" "$TASK_KIND" "$PROMPT")
log_event "INFO" "Determined role: $ROLE"

if [[ "$DRY_RUN" == "true" ]]; then
    echo "=== DRY RUN - Routing Decision ==="
    echo "Task ID:     $TASK_ID"
    echo "Session ID:  $SESSION_ID"
    echo "Primary:    $PRIMARY_LANE"
    echo "Task Kind:  $TASK_KIND"
    echo "Role:       $ROLE"
    echo "Delegation: $DELEGATION_REC"
    echo "Subagents:  $SUBAGENT_COUNT"
    echo "================================"
    exit 0
fi

create_brief() {
    local role="$1"
    local prompt="$2"
    local task_id="$3"
    local session_id="$4"
    
    local template_file=""
    case "$role" in
        mapper)    template_file="$ROOT/.agents/templates/tasks/map-repo-task.md" ;;
        researcher) template_file="$ROOT/.agents/templates/tasks/research-docs-task.md" ;;
        executor)   template_file="$ROOT/.agents/templates/tasks/execute-change-task.md" ;;
        reviewer)   template_file="$ROOT/.agents/templates/tasks/review-diff-task.md" ;;
        ci-triage)  template_file="$ROOT/.agents/templates/tasks/research-docs-task.md" ;;
        *)         template_file="$ROOT/.agents/templates/tasks/map-repo-task.md" ;;
    esac
    
    local brief_content
    if [[ -f "$template_file" ]]; then
        brief_content=$(cat "$template_file")
    else
        brief_content="Role: $role
Goal: Execute task based on prompt
Input: $prompt"
    fi
    
    local brief_file="$(task_dir "$ROOT" "$session_id" "$task_id")/brief-$role.md"
    
    {
        echo "# Brief - $role"
        echo ""
        echo "## Task ID"
        echo "$task_id"
        echo ""
        echo "## Session ID"
        echo "$session_id"
        echo ""
        echo "## Original Prompt"
        echo "$prompt"
        echo ""
        echo "## Template"
        echo "$brief_content"
    } > "$brief_file"
    
    echo "$brief_file"
}

brief_file=$(create_brief "$ROLE" "$PROMPT" "$TASK_ID" "$SESSION_ID")
log_event "INFO" "Brief created: $brief_file"

execute_agent() {
    local role="$1"
    local brief_file="$2"
    local model="$3"
    
    log_event "INFO" "Executing $role agent with model $model"
    
    local artifact_file="$(task_dir "$ROOT" "$SESSION_ID" "$TASK_ID")/artifact-$role.json"
    
    echo "TODO: Implement agent execution for role: $role"
    echo "Brief: $brief_file"
    echo "Model: $model"
    echo "Output: $artifact_file"
    
    echo '{}' > "$artifact_file"
    
    echo "$artifact_file"
}

validate_artifact() {
    local artifact_file="$1"
    
    if [[ ! -f "$artifact_file" ]]; then
        log_event "ERROR" "Artifact file not found: $artifact_file"
        return 1
    fi
    
    if ! python3 "$SCRIPT_DIR/validate-artifact.py" "$artifact_file" >/dev/null 2>&1; then
        log_event "ERROR" "Artifact validation failed: $artifact_file"
        return 1
    fi
    
    log_event "INFO" "Artifact validated: $artifact_file"
    return 0
}

get_next_agent() {
    local artifact_file="$1"
    
    if [[ ! -f "$artifact_file" ]]; then
        echo "none"
        return
    fi
    
    local next_agent
    next_agent=$(python3 - "$artifact_file" <<'PY'
import json
import sys
path = sys.argv[1]
try:
    payload = json.loads(open(path).read())
    print(payload.get("recommended_next_agent", "none"))
except:
    print("none")
PY
)
    
    echo "$next_agent"
}

case "$ROLE" in
    mapper|researcher|ci-triage)
        log_event "INFO" "Running read-only agent: $ROLE"
        case "$ROLE" in
            mapper) model="$MODEL_MAPPER" ;;
            researcher) model="$MODEL_MAPPER" ;;
            ci-triage) model="$MODEL_MAPPER" ;;
        esac
        
        artifact_file=$(execute_agent "$ROLE" "$brief_file" "$model")
        
        if ! validate_artifact "$artifact_file"; then
            log_event "ERROR" "Artifact validation failed"
            exit 1
        fi
        
        next_agent=$(get_next_agent "$artifact_file")
        log_event "INFO" "Next agent: $next_agent"
        
        if [[ "$next_agent" == "codex" ]] || [[ "$next_agent" == "executor" ]]; then
            log_event "INFO" "Routing to executor"
            artifact_file=$(execute_agent "executor" "$brief_file" "$MODEL_EXECUTOR")
            
            if ! validate_artifact "$artifact_file"; then
                log_event "ERROR" "Executor artifact validation failed"
                exit 1
            fi
            
            next_agent=$(get_next_agent "$artifact_file")
            
            if [[ "$next_agent" == "reviewer" ]]; then
                log_event "INFO" "Routing to reviewer"
                artifact_file=$(execute_agent "reviewer" "$brief_file" "$MODEL_REVIEWER")
                
                if ! validate_artifact "$artifact_file"; then
                    log_event "ERROR" "Reviewer artifact validation failed"
                    exit 1
                fi
            fi
        fi
        ;;
    executor)
        log_event "INFO" "Running executor: $ROLE"
        artifact_file=$(execute_agent "$ROLE" "$brief_file" "$MODEL_EXECUTOR")
        
        if ! validate_artifact "$artifact_file"; then
            log_event "ERROR" "Executor artifact validation failed"
            exit 1
        fi
        
        next_agent=$(get_next_agent "$artifact_file")
        
        if [[ "$next_agent" == "reviewer" ]]; then
            log_event "INFO" "Routing to reviewer"
            artifact_file=$(execute_agent "reviewer" "$brief_file" "$MODEL_REVIEWER")
            
            if ! validate_artifact "$artifact_file"; then
                log_event "ERROR" "Reviewer artifact validation failed"
                exit 1
            fi
        fi
        ;;
    reviewer)
        log_event "INFO" "Running reviewer: $ROLE"
        artifact_file=$(execute_agent "$ROLE" "$brief_file" "$MODEL_REVIEWER")
        
        if ! validate_artifact "$artifact_file"; then
            log_event "ERROR" "Reviewer artifact validation failed"
            exit 1
        fi
        ;;
    *)
        log_event "ERROR" "Unknown role: $ROLE"
        exit 1
        ;;
esac

log_event "INFO" "Supervisor completed successfully - Task: $TASK_ID"

{
    echo ""
    echo "## Task Complete - $TASK_ID"
    echo ""
    echo "- Role: $ROLE"
    echo "- Status: SUCCESS"
    echo "- Artifacts: $(task_dir "$ROOT" "$SESSION_ID" "$TASK_ID")/*"
} >&2
