# Task Template: Map Repository

**Role:** Mapper / Researcher (Read-Only Discovery)
**Trust Tier:** T0 (Read-only)
**Max Tokens:** 2,000
**Max Files:** 10

---

## Goal

Map the codebase to identify relevant files, entry points, and dependencies for the requested task.

---

## Input

| Field | Description |
|:------|:------------|
| `task_id` | Unique task identifier |
| `session_id` | Session identifier |
| `user_prompt` | Original user request |
| `focus_area` | What area to focus on (e.g., "auth", "payments", "API") |

---

## Constraints

```yaml
allowed_actions:
  - read
  - grep
  - ls
  - find
  - glob

forbidden_actions:
  - edit
  - delete
  - write
  - create

read_only: true
no_refactor: true
max_tokens: 2000
max_files: 10
```

---

## Process

1. **Analyze focus area** from user_prompt
2. **Search for relevant files** using glob/grep patterns
3. **Map code paths** - entry points, dependencies
4. **Identify key files** - prioritize by relevance
5. **Return structured artifact**

---

## Output Schema

Your output MUST be a JSON artifact:

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "mapper",
  "task_id": "string",
  "session_id": "string",
  "timestamp": "ISO8601",
  "goal": "Map {focus_area} implementation",
  "task_type": "map",
  "relevant_files": ["path/to/file1.py", "path/to/file2.ts"],
  "files_discovered": ["additional files found"],
  "excluded_files": ["files explicitly excluded"],
  "constraints": {
    "allowed_actions": ["read", "grep", "ls"],
    "forbidden_actions": ["edit", "delete"],
    "max_tokens": 2000,
    "read_only": true,
    "no_refactor": true
  },
  "docs_findings": [],
  "risks": [],
  "recommended_next_agent": "codex|reviewer|none",
  "acceptance_criteria": [
    "Identified all {focus_area} entry points",
    "Mapped key dependencies",
    "Found relevant test files"
  ],
  "output_format": "json",
  "status": "success|failed|partial",
  "status_reason": "string",
  "metadata": {
    "tokens_used": 0,
    "model": "gpt-4o-mini",
    "duration_seconds": 0
  }
}
```

---

## Example

**Input:**
```
task_id: task-001
session_id: session-001
user_prompt: "Find the authentication flow"
focus_area: "auth"
```

**Expected Output:**
```json
{
  "artifact_version": "1.0.0",
  "agent_role": "mapper",
  "task_id": "task-001",
  "session_id": "session-001",
  "timestamp": "2024-03-15T10:30:00Z",
  "goal": "Map authentication flow implementation",
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
    "read_only": true,
    "no_refactor": true
  },
  "docs_findings": [],
  "risks": [],
  "recommended_next_agent": "codex",
  "acceptance_criteria": [
    "Found auth entry points",
    "Identified session handling",
    "Located token validation"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "All auth files identified",
  "metadata": {
    "tokens_used": 1200,
    "model": "gpt-4o-mini",
    "duration_seconds": 15
  }
}
```

---

## Stop Conditions

- [ ] All relevant files identified
- [ ] Entry points mapped
- [ ] Dependencies documented
- [ ] Token budget NOT exceeded

If token budget is exhausted:
- Return `status: "partial"`
- Include `remaining_work` in `status_reason`
- Exit immediately

---

## Anti-Patterns

**DO NOT:**
- Return more than 10 files
- Edit any files
- Create new files
- Make assumptions about implementation
- Skip the `relevant_files` field

**DO:**
- Be specific with file paths
- Use relative paths from project root
- Prioritize by relevance to task
- Include test files if relevant
