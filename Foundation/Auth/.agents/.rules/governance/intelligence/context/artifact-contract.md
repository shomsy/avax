# Artifact Contract — Sub-Agent Output Schema

Version: 1.0.0
Status: Normative
Scope: `.agents/**`

This document defines the **standard artifact format** that EVERY sub-agent
MUST return. Without this contract, the supervisor cannot validate, route,
or trust sub-agent outputs.

---

## 1) Why Artifact Contract?

Without a standardized artifact:
- Supervisor consumes raw chat logs (wastes tokens)
- Context bloat accumulates across the session
- No way to validate if the sub-agent actually succeeded
- Manual parsing required — error-prone

With artifact contract:
- Supervisor receives structured, validated data
- Token budget stays predictable
- Clear acceptance criteria per task
- Deterministic routing decisions

---

## 2) Artifact Schema

Every sub-agent MUST return a JSON artifact conforming to this schema:

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "mapper|researcher|executor|reviewer",
  "task_id": "string",
  "session_id": "string",
  "timestamp": "ISO8601",

  "goal": "string - specific, measurable goal",
  "task_type": "map|research|execute|review",

  "relevant_files": ["path/to/file1.py", "path/to/file2.ts"],
  "excluded_files": ["path/to/ignored"],
  "files_discovered": ["additional files found during research"],

  "constraints": {
    "allowed_actions": ["read", "grep", "ls"],
    "forbidden_actions": ["edit", "delete", "write"],
    "max_tokens": 5000,
    "read_only": true,
    "no_refactor": true
  },

  "docs_findings": [
    {
      "source": "https://api.example.com/docs",
      "source_type": "mcp|web|local",
      "content": "relevant excerpt or summary",
      "relevance": "high|medium|low"
    }
  ],

  "risks": [
    {
      "severity": "low|medium|high|critical",
      "category": "security|performance|regression|complexity",
      "description": "what could go wrong",
      "mitigation": "how to address it",
      "affected_files": ["file1", "file2"]
    }
  ],

  "code_changes": {
    "files_modified": [],
    "files_created": [],
    "files_deleted": [],
    "diff_summary": "brief summary of changes"
  },

  "recommended_next_agent": "codex|reviewer|none|escalate",
  "acceptance_criteria": [
    "criterion 1 - must be satisfied for task completion",
    "criterion 2"
  ],

  "output_format": "json|markdown|structured",
  "status": "success|failed|partial|escalated",
  "status_reason": "why this status",

  "metadata": {
    "tokens_used": 0,
    "model": "gpt-4o-mini",
    "duration_seconds": 0,
    "mcp_tools_used": ["docs-mcp"],
    "errors": []
  }
}
```

---

## 3) Required Fields

| Field | Type | Required | Description |
|:------|:-----|:---------|:------------|
| `artifact_version` | string | YES | Version of this contract (1.0.0) |
| `agent_role` | enum | YES | Role that generated this artifact |
| `task_id` | string | YES | Unique task identifier |
| `session_id` | string | YES | Session identifier |
| `timestamp` | ISO8601 | YES | When artifact was generated |
| `goal` | string | YES | Specific goal of this task |
| `task_type` | enum | YES | Type of task performed |
| `status` | enum | YES | success\|failed\|partial\|escalated |
| `recommended_next_agent` | enum | YES | What should run next |

---

## 4) Role-Specific Requirements

### 4.1 Mapper Agent

```json
{
  "agent_role": "mapper",
  "task_type": "map",
  "required_fields": [
    "relevant_files",
    "files_discovered",
    "excluded_files"
  ],
  "optional_fields": [
    "docs_findings",
    "risks"
  ]
}
```

**Mapper MUST return:**
- Maximum 5-10 relevant files
- Clear mapping of code paths
- Entry points identified
- Dependencies mapped

### 4.2 Researcher Agent

```json
{
  "agent_role": "researcher",
  "task_type": "research",
  "required_fields": [
    "docs_findings",
    "relevant_files"
  ],
  "optional_fields": [
    "risks"
  ]
}
```

**Researcher MUST return:**
- At least one doc finding with source URL
- API/version references
- Code examples if applicable

### 4.3 Executor Agent (Codex)

```json
{
  "agent_role": "executor",
  "task_type": "execute",
  "required_fields": [
    "code_changes",
    "acceptance_criteria"
  ],
  "optional_fields": [
    "risks",
    "docs_findings"
  ]
}
```

**Executor MUST return:**
- Explicit list of files modified
- Diff summary
- Verification steps taken

### 4.4 Reviewer Agent

```json
{
  "agent_role": "reviewer",
  "task_type": "review",
  "required_fields": [
    "risks",
    "acceptance_criteria"
  ],
  "optional_fields": [
    "code_changes"
  ]
}
```

**Reviewer MUST return:**
- At least one risk identified (or explicit "no risks found")
- Clear allow/block recommendation

---

## 5) Validation Rules

The supervisor validates artifacts BEFORE passing to the next agent:

1. **Schema Validation** — All required fields present
2. **Type Validation** — Fields match expected types
3. **Status Validation** — Status is one of allowed values
4. **Recommendation Validation** — Next agent is valid for current status

### 5.1 Validation Failure Handling

| Validation | Action |
|:------------|:-------|
| Missing required field | **REJECT** — retry with same goal |
| Type mismatch | **REJECT** — retry with corrected schema |
| Status = "failed" | **ESCALATE** — supervisor decides next step |
| Status = "partial" | **RETRY** — max 2 times, then escalate |
| No risks in review | **WARN** — reviewer may have missed something |

---

## 6) Context Budget Enforcement

Each role has a maximum token budget:

| Role | Max Tokens | Max Files |
|:-----|:-----------|:----------|
| **Mapper** | 2,000 | 10 |
| **Researcher** | 3,000 | 5 |
| **Executor** | 15,000 | 20 |
| **Reviewer** | 5,000 | 15 |

If a sub-agent exceeds budget, it MUST:
1. Stop processing
2. Return partial artifact with `status: "partial"`
3. Include `remaining_work` description in `status_reason`

---

## 7) Trust Tier Mapping

| Tier | Role | MCP Access | Write Access |
|:-----|:-----|:------------|:--------------|
| **T0** | Mapper, Researcher | Read-only local | None |
| **T1** | Executor | Read + limited write | Workspace only |
| **T2** | (Future) External write | Requires approval |

---

## 8) Example Artifacts

### 8.1 Mapper Artifact

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "mapper",
  "task_id": "task-20240315-001",
  "session_id": "session-20240315",
  "timestamp": "2024-03-15T10:30:00Z",
  "goal": "Find authentication flow implementation",
  "task_type": "map",
  "relevant_files": [
    "src/auth/login.ts",
    "src/auth/middleware.ts",
    "src/services/session.ts"
  ],
  "files_discovered": [
    "src/auth/providers/oauth.ts",
    "src/auth/validators.ts"
  ],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "grep", "ls"],
    "forbidden_actions": ["edit", "delete"],
    "max_tokens": 2000,
    "read_only": true
  },
  "docs_findings": [],
  "risks": [],
  "recommended_next_agent": "codex",
  "acceptance_criteria": [
    "Found all auth entry points",
    "Identified session handling",
    "Located token validation"
  ],
  "output_format": "structured",
  "status": "success",
  "status_reason": "All auth files identified",
  "metadata": {
    "tokens_used": 1200,
    "model": "gpt-4o-mini",
    "duration_seconds": 15
  }
}
```

### 8.2 Researcher Artifact

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "researcher",
  "task_id": "task-20240315-002",
  "session_id": "session-20240315",
  "timestamp": "2024-03-15T10:32:00Z",
  "goal": "Find OpenAI API rate limit documentation",
  "task_type": "research",
  "relevant_files": [],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["webfetch", "mcp"],
    "forbidden_actions": ["edit", "write"],
    "max_tokens": 3000,
    "read_only": true,
    "no_refactor": true
  },
  "docs_findings": [
    {
      "source": "https://platform.openai.com/docs/guides/rate-limits",
      "source_type": "mcp",
      "content": "Tier 1: 500 requests/minute, 150,000 tokens/minute...",
      "relevance": "high"
    }
  ],
  "risks": [],
  "recommended_next_agent": "none",
  "acceptance_criteria": [
    "Found current rate limits for tier",
    "Identified retry strategy"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "Rate limit documentation found",
  "metadata": {
    "tokens_used": 800,
    "model": "gpt-4o-mini",
    "duration_seconds": 8
  }
}
```

### 8.3 Executor Artifact

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "executor",
  "task_id": "task-20240315-003",
  "session_id": "session-20240315",
  "timestamp": "2024-03-15T10:45:00Z",
  "goal": "Fix login timeout bug",
  "task_type": "execute",
  "relevant_files": [
    "src/auth/login.ts",
    "src/services/session.ts"
  ],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "edit", "grep"],
    "forbidden_actions": ["delete"],
    "max_tokens": 15000,
    "read_only": false
  },
  "docs_findings": [],
  "risks": [
    {
      "severity": "medium",
      "category": "regression",
      "description": "Changing session timeout could affect remember-me functionality",
      "mitigation": "Test both scenarios",
      "affected_files": ["src/auth/login.ts"]
    }
  ],
  "code_changes": {
    "files_modified": ["src/auth/login.ts"],
    "files_created": [],
    "files_deleted": [],
    "diff_summary": "Changed session timeout from 30min to 24h"
  },
  "recommended_next_agent": "reviewer",
  "acceptance_criteria": [
    "Login timeout respects config",
    "Existing tests pass"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "Fix implemented and tested",
  "metadata": {
    "tokens_used": 8500,
    "model": "gpt-4o",
    "duration_seconds": 45
  }
}
```

### 8.4 Reviewer Artifact

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "reviewer",
  "task_id": "task-20240315-004",
  "session_id": "session-20240315",
  "timestamp": "2024-03-15T10:50:00Z",
  "goal": "Review login timeout fix",
  "task_type": "review",
  "relevant_files": [
    "src/auth/login.ts"
  ],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "grep"],
    "forbidden_actions": ["edit", "write"],
    "max_tokens": 5000,
    "read_only": true
  },
  "docs_findings": [],
  "risks": [
    {
      "severity": "low",
      "category": "security",
      "description": "24h session may be too long for sensitive applications",
      "mitigation": "Document recommended timeout per use case",
      "affected_files": []
    }
  ],
  "code_changes": {
    "files_modified": ["src/auth/login.ts"],
    "files_created": [],
    "files_deleted": [],
    "diff_summary": "Changed session timeout from 30min to 24h"
  },
  "recommended_next_agent": "none",
  "acceptance_criteria": [
    "Code compiles",
    "Tests pass",
    "No security issues"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "Changes approved with minor documentation note",
  "metadata": {
    "tokens_used": 2200,
    "model": "gpt-4o",
    "duration_seconds": 20
  }
}
```

---

## 9) Integration Points

### 9.1 Supervisor Hook

The supervisor validates artifacts in `supervisor.sh`:

```bash
# After each sub-agent completes
python3 .agents/hooks/validate-artifact.py "$ARTIFACT_FILE" || {
  echo "Artifact validation failed"
  handle_failure
}
```

### 9.2 Routing Integration

The routing engine reads `recommended_next_agent` to decide:

```python
next_agent = artifact.get("recommended_next_agent")
if next_agent == "codex":
    spawn_executor(brief, artifact)
elif next_agent == "reviewer":
    spawn_reviewer(brief, artifact)
```

### 9.3 Metrics Integration

Supervisor logs artifacts for metrics:

```bash
echo "$ARTIFACT" | jq '.metadata + {task_type, status}'
>> .agents/management/subagent-metrics.jsonl
```

---

## 10) Anti-Patterns

**DO NOT:**
- Return raw chat logs as artifacts
- Skip required fields "for simplicity"
- Use `recommended_next_agent: "none"` when work remains
- Set `status: "success"` without verification
- Exceed token budget without returning `partial`

**DO:**
- Always include `task_id` and `session_id`
- Return specific file paths, not directories
- Use exact enum values
- Include risk assessment even if empty array
- Set realistic `max_tokens` in constraints

---

*End of Artifact Contract*
