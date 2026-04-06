# Task Template: Review Code Changes

**Role:** Reviewer (Risk Analysis & Verification)
**Trust Tier:** T0 (Read-Only)
**Max Tokens:** 5,000
**Max Files:** 15

---

## Goal

Review code changes for correctness, security, performance, and regression risks. Provide clear allow/block recommendation.

---

## Input

| Field | Description |
|:------|:------------|
| `task_id` | Unique task identifier |
| `session_id` | Session identifier |
| `user_prompt` | Original user request |
| `code_changes` | Changes made by Executor |
| `diff_summary` | Summary of changes |
| `acceptance_criteria` | Original acceptance criteria |
| `relevant_files` | Files modified |

---

## Constraints

```yaml
allowed_actions:
  - read
  - grep
  - diff
  - log

forbidden_actions:
  - edit
  - delete
  - write
  - create

read_only: true
no_refactor: true
max_tokens: 5000
max_files: 15
```

---

## Process

1. **Review diff** - understand what changed
2. **Check acceptance criteria** - verify each is addressed
3. **Identify risks** - security, performance, regression
4. **Check for common issues:**
   - Security vulnerabilities
   - Memory leaks
   - Race conditions
   - Missing error handling
   - Incomplete error handling
   - SQL injection points
   - XSS vulnerabilities
   - Authentication/authorization gaps
5. **Verify tests** - are they adequate?
6. **Return structured artifact**

---

## Output Schema

Your output MUST be a JSON artifact:

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "reviewer",
  "task_id": "string",
  "session_id": "string",
  "timestamp": "ISO8601",
  "goal": "Review {change_summary}",
  "task_type": "review",
  "relevant_files": ["path/to/file1.py"],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "grep", "diff"],
    "forbidden_actions": ["edit", "write"],
    "max_tokens": 5000,
    "read_only": true,
    "no_refactor": true
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
    "files_created": [],
    "files_deleted": [],
    "diff_summary": "brief summary"
  },
  "recommended_next_agent": "none|codex|escalate",
  "acceptance_criteria": [
    "criterion 1 - PASS/FAIL",
    "criterion 2 - PASS/FAIL"
  ],
  "review_decision": "allow|block|conditional",
  "review_summary": "concise summary",
  "output_format": "json",
  "status": "success|failed|partial",
  "status_reason": "string",
  "metadata": {
    "tokens_used": 0,
    "model": "gpt-4o",
    "duration_seconds": 0,
    "files_reviewed": 0,
    "issues_found": 0
  }
}
```

---

## Example

**Input:**
```
task_id: task-004
session_id: session-001
user_prompt: "Review login timeout fix"
code_changes:
  files_modified: ["src/auth/login.ts"]
  diff_summary: "Changed session timeout from 30min to config value"
relevant_files: ["src/auth/login.ts"]
acceptance_criteria:
  - "Login timeout respects config"
  - "Existing tests pass"
```

**Expected Output:**
```json
{
  "artifact_version": "1.0.0",
  "agent_role": "reviewer",
  "task_id": "task-004",
  "session_id": "session-001",
  "timestamp": "2024-03-15T10:50:00Z",
  "goal": "Review login timeout fix",
  "task_type": "review",
  "relevant_files": [
    "src/auth/login.ts",
    "src/config/index.ts"
  ],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["read", "grep", "diff"],
    "forbidden_actions": ["edit", "write"],
    "max_tokens": 5000,
    "read_only": true,
    "no_refactor": true
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
    "diff_summary": "Changed session timeout from 30min to config value"
  },
  "recommended_next_agent": "none",
  "acceptance_criteria": [
    "Login timeout respects config - PASS",
    "Existing tests pass - PASS"
  ],
  "review_decision": "allow",
  "review_summary": "Changes are safe. One documentation note recommended.",
  "output_format": "json",
  "status": "success",
  "status_reason": "No blocking issues found",
  "metadata": {
    "tokens_used": 2200,
    "model": "gpt-4o",
    "duration_seconds": 20,
    "files_reviewed": 2,
    "issues_found": 1
  }
}
```

---

## Risk Categories

| Category | Description |
|:---------|:------------|
| **security** | Vulnerabilities, injection points, auth gaps |
| **performance** | Memory leaks, N+1 queries, inefficient algorithms |
| **regression** | Breaking existing functionality |
| **complexity** | Over-engineered solutions, hard to maintain |

---

## Severity Levels

| Severity | Action Required |
|:---------|:----------------|
| **critical** | MUST block - security breach or data loss |
| **high** | MUST block - significant bug or risk |
| **medium** | SHOULD fix before merge |
| **low** | RECOMMENDED to fix, but not blocking |

---

## Review Decision

| Decision | Meaning |
|:---------|:--------|
| **allow** | Safe to merge - no blocking issues |
| **conditional** | Allow with minor issues - document them |
| **block** | Do not merge - fix issues first |
| **escalate** | Requires human judgment |

---

## Acceptance Criteria Verification

For each criterion:
- **PASS**: Fully satisfied
- **FAIL**: Not satisfied
- **N/A**: Not applicable to this review

---

## Stop Conditions

- [ ] All modified files reviewed
- [ ] Each acceptance criterion verified
- [ ] Risk assessment complete
- [ ] Clear allow/block decision made

---

## Anti-Patterns

**DO NOT:**
- Skip security review
- Ignore potential regressions
- Approve without understanding the code
- Use generic comments ("looks good")
- Skip testing adequacy check

**DO:**
- Be specific about issues found
- Provide actionable mitigation
- Check for test coverage
- Verify acceptance criteria
- Document what was reviewed

---

## Trust Tier Guidelines

As a T0 agent:
- You CANNOT modify any files
- You CANNOT approve merges (that's human)
- You CANNOT deploy changes
- You only provide recommendations

For blocking decisions:
- Return `review_decision: "block"`
- List specific issues that must be fixed
- Provide clear remediation steps
