# Task Template: Execute Code Change

**Role:** Executor (Codex/Implementation)
**Trust Tier:** T1 (Workspace Write)
**Max Tokens:** 15,000
**Max Files:** 20

---

## Goal

Implement the requested code change based on the brief and any prior research findings.

---

## Input

| Field | Description |
|:------|:------------|
| `task_id` | Unique task identifier |
| `session_id` | Session identifier |
| `user_prompt` | Original user request |
| `brief` | Concise brief from previous agents |
| `relevant_files` | Files to modify (from Mapper) |
| `docs_findings` | Documentation references (from Researcher) |
| `acceptance_criteria` | What defines success |
| `constraints` | What NOT to do |

---

## Constraints

```yaml
allowed_actions:
  - read
  - edit
  - grep
  - create
  - delete

forbidden_actions:
  - git_push
  - deploy
  - external_write

read_only: false
max_tokens: 15000
max_files: 20
no_mass_refactor: true
```

---

## Process

1. **Review brief** and acceptance criteria
2. **Review relevant files** provided
3. **Review docs_findings** if provided
4. **Implement changes** according to acceptance criteria
5. **Run validation** (tests, typecheck, lint)
6. **Verify changes** match acceptance criteria
7. **Return structured artifact**

---

## Output Schema

Your output MUST be a JSON artifact:

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "executor",
  "task_id": "string",
  "session_id": "string",
  "timestamp": "ISO8601",
  "goal": "Implement {feature/fix}",
  "task_type": "execute",
  "relevant_files": ["path/to/file1.py", "path/to/file2.ts"],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "edit", "grep"],
    "forbidden_actions": ["git_push", "deploy"],
    "max_tokens": 15000,
    "read_only": false,
    "no_mass_refactor": true
  },
  "docs_findings": [],
  "risks": [
    {
      "severity": "low|medium|high|critical",
      "category": "security|performance|regression|complexity",
      "description": "what could go wrong",
      "mitigation": "how to address",
      "affected_files": []
    }
  ],
  "code_changes": {
    "files_modified": ["path/to/file1.py"],
    "files_created": ["path/to/new.ts"],
    "files_deleted": [],
    "diff_summary": "brief summary of changes"
  },
  "recommended_next_agent": "reviewer|none",
  "acceptance_criteria": [
    "criterion 1 - verified",
    "criterion 2 - verified"
  ],
  "output_format": "json",
  "status": "success|failed|partial",
  "status_reason": "string",
  "metadata": {
    "tokens_used": 0,
    "model": "gpt-4o",
    "duration_seconds": 0,
    "validation_run": true,
    "tests_passed": true
  }
}
```

---

## Example

**Input:**
```
task_id: task-003
session_id: session-001
user_prompt: "Fix the login timeout bug"
brief: "Session timeout hardcoded to 30 min. Need to use config."
relevant_files: ["src/auth/login.ts"]
acceptance_criteria:
  - "Login timeout respects config"
  - "Existing tests pass"
```

**Expected Output:**
```json
{
  "artifact_version": "1.0.0",
  "agent_role": "executor",
  "task_id": "task-003",
  "session_id": "session-001",
  "timestamp": "2024-03-15T10:45:00Z",
  "goal": "Fix login timeout bug",
  "task_type": "execute",
  "relevant_files": [
    "src/auth/login.ts",
    "src/config/index.ts"
  ],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "edit", "grep"],
    "forbidden_actions": ["git_push", "deploy"],
    "max_tokens": 15000,
    "read_only": false,
    "no_mass_refactor": true
  },
  "docs_findings": [],
  "risks": [
    {
      "severity": "medium",
      "category": "regression",
      "description": "Changing session timeout could affect remember-me",
      "mitigation": "Test both scenarios",
      "affected_files": ["src/auth/login.ts"]
    }
  ],
  "code_changes": {
    "files_modified": ["src/auth/login.ts"],
    "files_created": [],
    "files_deleted": [],
    "diff_summary": "Changed session timeout from hardcoded 30min to config.get('SESSION_TIMEOUT')"
  },
  "recommended_next_agent": "reviewer",
  "acceptance_criteria": [
    "Login timeout respects config - VERIFIED",
    "Existing tests pass - VERIFIED"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "Fix implemented, tested, and verified",
  "metadata": {
    "tokens_used": 8500,
    "model": "gpt-4o",
    "duration_seconds": 45,
    "validation_run": true,
    "tests_passed": true
  }
}
```

---

## Validation Requirements

BEFORE returning success, you MUST run:

1. **Syntax validation** (if applicable):
   ```bash
   # Python
   python3 -m py_compile src/file.py
   
   # JavaScript/TypeScript
   npx tsc --noEmit
   ```

2. **Tests** (if exists):
   ```bash
   npm test
   # or
   pytest
   ```

3. **Linter** (if configured):
   ```bash
   npm run lint
   # or
   ruff check src/
   ```

Include validation results in `metadata.validation_run` and `metadata.tests_passed`.

---

## Acceptance Criteria Verification

For each acceptance criterion:
- Mark as "VERIFIED" if satisfied
- Mark as "FAILED" if not satisfied
- Add explanation if partial

---

## Stop Conditions

- [ ] All acceptance criteria verified
- [ ] Tests pass (or explicit reason why not)
- [ ] No syntax errors
- [ ] Changes are minimal and focused

If cannot complete:
- Return `status: "failed"` with clear reason
- Do NOT partially implement and claim success

---

## Anti-Patterns

**DO NOT:**
- Refactor unrelated code
- Make changes beyond the scope
- Skip validation
- Claim success without verification
- Leave commented-out code

**DO:**
- Stick to the brief
- Run validation before completing
- Include risk assessment
- Make minimal changes
- Clean up any debug code

---

## Trust Tier Guidelines

As a T1 agent:
- You CAN modify files in the workspace
- You CANNOT push to git
- You CANNOT deploy to production
- You CANNOT make external API calls that modify data
- Any external write requires human approval

If the task requires T2 access:
- Return artifact with `recommended_next_agent: "escalate"`
- Explain why escalation is needed
