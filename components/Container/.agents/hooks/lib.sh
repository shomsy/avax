#!/bin/bash
set -euo pipefail

resolve_project_root() {
    local library_dir
    local candidate_root

    if [ -n "${AGENT_HARNESS_PROJECT_ROOT:-}" ] && [ -d "${AGENT_HARNESS_PROJECT_ROOT}" ]; then
        printf '%s\n' "${AGENT_HARNESS_PROJECT_ROOT}"
        return
    fi

    library_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    candidate_root="$(cd "$library_dir/../.." && pwd)"
    if [ -d "$candidate_root/.agents" ] && [ -f "$candidate_root/AGENTS.md" ]; then
        printf '%s\n' "$candidate_root"
        return
    fi

    if git_root="$(git rev-parse --show-toplevel 2>/dev/null)"; then
        printf '%s\n' "$git_root"
        return
    fi

    pwd
}

repo_agents_dir() {
    printf '%s/.agents\n' "$1"
}

repo_agent_runtime_dir() {
    printf '%s/.agents\n' "$1"
}

runtime_logs_dir() {
    printf '%s/logs\n' "$(repo_agent_runtime_dir "$1")"
}

learning_dir() {
    printf '%s/management/learning\n' "$(repo_agents_dir "$1")"
}

memories_dir() {
    printf '%s/management/memories\n' "$(repo_agents_dir "$1")"
}

rules_dir() {
    local root="$1"

    if [ -d "$root/.agents/.rules" ]; then
        printf '%s/.agents/.rules\n' "$root"
        return
    fi

    printf '%s/.agents\n' "$root"
}

approved_rules_file() {
    printf '%s/governance/security/approved-commands.rules\n' "$(rules_dir "$1")"
}

iso_timestamp() {
    date +"%Y-%m-%dT%H:%M:%S%z" | sed 's/\(..\)$/:\1/'
}

hash_string() {
    local value="$1"

    if command -v python3 >/dev/null 2>&1; then
        python3 - "$value" <<'PY'
import hashlib
import sys

print(hashlib.sha256(sys.argv[1].encode("utf-8")).hexdigest()[:12])
PY
        return
    fi

    if command -v sha256sum >/dev/null 2>&1; then
        printf '%s' "$value" | sha256sum | awk '{print substr($1, 1, 12)}'
        return
    fi

    printf '%s' "$value" | shasum -a 256 | awk '{print substr($1, 1, 12)}'
}

project_id() {
    local root="$1"
    local remote_url

    remote_url="$(git -C "$root" remote get-url origin 2>/dev/null || true)"
    if [ -n "$remote_url" ]; then
        hash_string "$remote_url"
        return
    fi

    hash_string "$root"
}

session_id() {
    local root

    if [ -n "${AGENT_HARNESS_SESSION_ID:-}" ]; then
        printf '%s\n' "${AGENT_HARNESS_SESSION_ID}"
        return
    fi

    if [ -n "${SESSION_ID:-}" ]; then
        printf '%s\n' "${SESSION_ID}"
        return
    fi

    root="$(resolve_project_root)"
    if [ -f "$(current_session_pointer_file "$root")" ]; then
        sed -n '1p' "$(current_session_pointer_file "$root")"
        return
    fi

    printf 'sess-%s-%s\n' "$(date +%Y%m%d%H%M%S)" "$$"
}

ensure_learning_layout() {
    local root="$1"
    local base

    base="$(learning_dir "$root")"
    mkdir -p "$base/instincts" "$base/evolved/skills" "$base/evolved/rules" \
        "$base/evolved/agents" "$base/archived"
    touch "$base/observations.jsonl"
}

ensure_memory_layout() {
    local root="$1"
    local base

    base="$(memories_dir "$root")"
    mkdir -p "$base/task_summaries" "$base/learned_skills"
    touch "$base/raw_memories.md"
    touch "$base/MEMORY.md"
    touch "$base/memory_summary.md"
}

ensure_session_layout() {
    local root="$1"
    local current_session_id="$2"
    local runtime_dir

    runtime_dir="$(repo_agent_runtime_dir "$root")"
    mkdir -p "$runtime_dir/sessions/$current_session_id" "$runtime_dir/memory" \
        "$runtime_dir/sessions/$current_session_id/tasks" \
        "$runtime_dir/context/product" "$runtime_dir/context/users" \
        "$runtime_dir/context/strategy" "$runtime_dir/context/stakeholders"
    touch "$runtime_dir/sessions/$current_session_id/session_memory.md"
}

ensure_runtime_log_layout() {
    local root="$1"

    mkdir -p "$(runtime_logs_dir "$root")"
}

session_dir() {
    printf '%s/sessions/%s\n' "$(repo_agent_runtime_dir "$1")" "$2"
}

current_session_pointer_file() {
    printf '%s/current-session\n' "$(repo_agent_runtime_dir "$1")"
}

set_current_session_id() {
    local root="$1"
    local current_session_id="$2"

    mkdir -p "$(repo_agent_runtime_dir "$root")"
    printf '%s\n' "$current_session_id" > "$(current_session_pointer_file "$root")"
}

task_dir() {
    printf '%s/tasks/%s\n' "$(session_dir "$1" "$2")" "$3"
}

current_task_pointer_file() {
    printf '%s/current-task\n' "$(session_dir "$1" "$2")"
}

set_current_task_id() {
    local root="$1"
    local current_session_id="$2"
    local current_task_id="$3"

    mkdir -p "$(session_dir "$root" "$current_session_id")"
    printf '%s\n' "$current_task_id" > "$(current_task_pointer_file "$root" "$current_session_id")"
}

current_task_id() {
    local root="$1"
    local current_session_id="$2"

    if [ -n "${AGENT_HARNESS_TASK_ID:-}" ]; then
        printf '%s\n' "${AGENT_HARNESS_TASK_ID}"
        return
    fi

    if [ -f "$(current_task_pointer_file "$root" "$current_session_id")" ]; then
        sed -n '1p' "$(current_task_pointer_file "$root" "$current_session_id")"
        return
    fi

    printf 'task-%s\n' "$(date +%Y%m%d%H%M%S)"
}

task_context_json_path() {
    printf '%s/context.json\n' "$(task_dir "$1" "$2" "$3")"
}

task_context_markdown_path() {
    printf '%s/context.md\n' "$(task_dir "$1" "$2" "$3")"
}

task_subagents_dir() {
    printf '%s/subagents\n' "$(task_dir "$1" "$2" "$3")"
}

task_events_log_path() {
    printf '%s/events.log\n' "$(task_dir "$1" "$2" "$3")"
}

task_result_json_path() {
    printf '%s/result.json\n' "$(task_dir "$1" "$2" "$3")"
}

trace_reports_file() {
    printf '%s/management/evidence/TRACE_REPORTS.md\n' "$(repo_agents_dir "$1")"
}

trace_bundles_dir() {
    printf '%s/management/evidence/traces\n' "$(repo_agents_dir "$1")"
}

ensure_trace_layout() {
    local root="$1"

    mkdir -p "$(trace_bundles_dir "$root")"
    touch "$(trace_reports_file "$root")"
}

sanitize_text() {
    printf '%s' "$1" | tr '\n' ' ' | tr '\r' ' ' | sed 's/[[:space:]]\+/ /g; s/^ //; s/ $//'
}

summarize_text() {
    local limit="${2:-200}"
    local clean

    clean="$(sanitize_text "$1")"
    if [ "${#clean}" -le "$limit" ]; then
        printf '%s\n' "$clean"
        return
    fi

    printf '%s...\n' "${clean:0:$((limit - 3))}"
}

flag_default() {
    case "$1" in
        strict_review) printf 'ON\n' ;;
        memory_extraction) printf 'ON\n' ;;
        memory_consolidation) printf 'OFF\n' ;;
        memory_injection) printf 'ON\n' ;;
        approval_required) printf 'ON\n' ;;
        auto_approval_rules) printf 'ON\n' ;;
        dangerous_op_detection) printf 'ON\n' ;;
        auto_backlog_update) printf 'ON\n' ;;
        hooks_enabled) printf 'ON\n' ;;
        offload_notes) printf 'ON\n' ;;
        evidence_required) printf 'ON\n' ;;
        naming_standard) printf 'ON\n' ;;
        profile_resolution) printf 'ON\n' ;;
        security_gates) printf 'ON\n' ;;
        continuous_learning) printf 'ON\n' ;;
        instincts_enabled) printf 'ON\n' ;;
        *) printf 'OFF\n' ;;
    esac
}

flag_env_name() {
    printf 'AGENT_HARNESS_FLAG_%s\n' "$(printf '%s' "$1" | tr '[:lower:]-' '[:upper:]_')"
}

flag_is_enabled() {
    local flag_name="$1"
    local root="$2"
    local env_name
    local env_value
    local override_line

    env_name="$(flag_env_name "$flag_name")"
    env_value="${!env_name:-}"
    if [ -n "$env_value" ]; then
        case "$env_value" in
            ON|on|true|TRUE|1) return 0 ;;
            OFF|off|false|FALSE|0) return 1 ;;
        esac
    fi

    override_line="$(awk -F'|' -v flag_name="$flag_name" '
        $0 ~ /^[[:space:]]*\|/ {
            field = $2
            gsub(/[[:space:]`]/, "", field)
            if (field == flag_name) {
                line = $0
            }
        }
        END {
            if (line != "") {
                print line
            }
        }
    ' "$root/AGENTS.md" 2>/dev/null || true)"
    if printf '%s' "$override_line" | grep -Eq '\|[[:space:]]*OFF[[:space:]]*\|'; then
        return 1
    fi
    if printf '%s' "$override_line" | grep -Eq '\|[[:space:]]*ON[[:space:]]*\|'; then
        return 0
    fi

    [ "$(flag_default "$flag_name")" = "ON" ]
}

trust_tier_rank() {
    case "$1" in
        T0) printf '0\n' ;;
        T1) printf '1\n' ;;
        T2) printf '2\n' ;;
        T3) printf '3\n' ;;
        *) printf '0\n' ;;
    esac
}

command_prefix_allowed() {
    local command_text="$1"
    local rules_file="$2"
    local line
    local prefix

    [ -f "$rules_file" ] || return 1

    while IFS= read -r line; do
        case "$line" in
            DENY:*)
                prefix="${line#DENY: }"
                if [[ "$command_text" == "$prefix"* ]]; then
                    return 1
                fi
                ;;
        esac
    done < "$rules_file"

    while IFS= read -r line; do
        case "$line" in
            ALLOW:*)
                prefix="${line#ALLOW: }"
                if [[ "$command_text" == "$prefix"* ]]; then
                    return 0
                fi
                ;;
        esac
    done < "$rules_file"

    return 1
}

is_dangerous_command() {
    local command_text="$1"

    case "$command_text" in
        *"rm -rf /"*|*"rm -rf ~"*|*"sudo "*|*" kill -9 "*|kill\ -9*|killall*|*"systemctl "*|systemctl*|service*|*"curl | bash"*|*"wget | sh"*|*"chown "*)
            return 0
            ;;
        *"DROP TABLE"*|*"DROP DATABASE"*|*"TRUNCATE TABLE"*)
            return 0
            ;;
    esac

    if printf '%s' "$command_text" | grep -Eq '(^|[[:space:]])rm[[:space:]]+-rf([[:space:]]|$)'; then
        return 0
    fi

    if command_looks_mutating "$command_text" && printf '%s' "$command_text" | grep -Eq '(\.git/hooks/|\.agents/|\.codex/)'; then
        return 0
    fi

    if printf '%s' "$command_text" | grep -Eq '(^|[[:space:]])curl[[:space:]].*http://'; then
        if ! printf '%s' "$command_text" | grep -Eq 'http://(localhost|127\.0\.0\.1)'; then
            return 0
        fi
    fi

    if printf '%s' "$command_text" | grep -Eq '(^|[[:space:]])wget[[:space:]].*http://'; then
        if ! printf '%s' "$command_text" | grep -Eq 'http://(localhost|127\.0\.0\.1)'; then
            return 0
        fi
    fi

    return 1
}

command_requires_network() {
    printf '%s' "$1" | grep -Eq '(^|[[:space:]])(curl|wget|ssh|scp|rsync[[:space:]].*@|nc|ncat|ftp)([[:space:]]|$)'
}

command_looks_mutating() {
    printf '%s' "$1" | grep -Eq '(^|[[:space:]])(mkdir|touch|rm|mv|cp|install|chmod|chown|sed -i|perl -pi|tee|truncate|apply_patch|git commit|git push|npm install|pnpm add|yarn add)([[:space:]]|$)|>|>>'
}

log_json_observation() {
    local ts="$1"
    local tool="$2"
    local input_summary="$3"
    local output_summary="$4"
    local current_project_id="$5"
    local current_session_id="$6"

    python3 - "$ts" "$tool" "$input_summary" "$output_summary" "$current_project_id" "$current_session_id" <<'PY'
import json
import sys

payload = {
    "ts": sys.argv[1],
    "tool": sys.argv[2],
    "input_summary": sys.argv[3],
    "output_summary": sys.argv[4],
    "project_id": sys.argv[5],
    "session_id": sys.argv[6],
}
print(json.dumps(payload, ensure_ascii=True))
PY
}

json_get() {
    local json_file="$1"
    local dotted_path="$2"

    python3 - "$json_file" "$dotted_path" <<'PY'
import json
import sys
from pathlib import Path

path = Path(sys.argv[1])
if not path.exists():
    sys.exit(1)

payload = json.loads(path.read_text(encoding="utf-8"))
value = payload
for segment in sys.argv[2].split("."):
    if isinstance(value, dict):
        value = value.get(segment)
    else:
        value = None
    if value is None:
        break

if isinstance(value, list):
    print(",".join(str(item) for item in value))
elif value is not None:
    print(value)
PY
}

append_task_event() {
    local root="$1"
    local current_session_id="$2"
    local current_task_id="$3"
    local event_name="$4"
    local message="$5"
    local event_log

    event_log="$(task_events_log_path "$root" "$current_session_id" "$current_task_id")"
    mkdir -p "$(dirname "$event_log")"
    printf '[%s] %s: %s\n' "$(iso_timestamp)" "$event_name" "$message" >> "$event_log"
}
