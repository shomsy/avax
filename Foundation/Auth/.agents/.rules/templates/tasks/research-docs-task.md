# Task Template: Research Documentation

**Role:** Researcher (Docs/API Discovery)
**Trust Tier:** T0 (Read-only)
**Max Tokens:** 3,000
**Max Files:** 5

---

## Goal

Research documentation, API references, and external resources to provide relevant information for the task.

---

## Input

| Field | Description |
|:------|:------------|
| `task_id` | Unique task identifier |
| `session_id` | Session identifier |
| `user_prompt` | Original user request |
| `research_topic` | What to research (e.g., "API rate limits", "OAuth flow") |
| `preferred_sources` | Preferred doc sources (optional) |

---

## Constraints

```yaml
allowed_actions:
  - webfetch
  - mcp
  - read
  - grep

forbidden_actions:
  - edit
  - delete
  - write
  - create
  - bash

read_only: true
no_refactor: true
max_tokens: 3000
max_files: 5
```

---

## Process

1. **Analyze research topic** from user_prompt
2. **Identify relevant sources:**
   - Official documentation
   - API references
   - MCP-connected resources
   - Community resources
3. **Fetch and extract** relevant information
4. **Summarize findings** with source attribution
5. **Return structured artifact**

---

## Output Schema

Your output MUST be a JSON artifact:

```json
{
  "artifact_version": "1.0.0",
  "agent_role": "researcher",
  "task_id": "string",
  "session_id": "string",
  "timestamp": "ISO8601",
  "goal": "Research {research_topic}",
  "task_type": "research",
  "relevant_files": [],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["webfetch", "mcp", "read"],
    "forbidden_actions": ["edit", "write", "bash"],
    "max_tokens": 3000,
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
  "risks": [],
  "recommended_next_agent": "codex|reviewer|none",
  "acceptance_criteria": [
    "Found relevant {research_topic} documentation",
    "Included source URLs",
    "Summary is accurate"
  ],
  "output_format": "json",
  "status": "success|failed|partial",
  "status_reason": "string",
  "metadata": {
    "tokens_used": 0,
    "model": "gpt-4o-mini",
    "duration_seconds": 0,
    "mcp_tools_used": ["docs-mcp"]
  }
}
```

---

## Example

**Input:**
```
task_id: task-002
session_id: session-001
user_prompt: "What are the OpenAI API rate limits?"
research_topic: "OpenAI API rate limits"
```

**Expected Output:**
```json
{
  "artifact_version": "1.0.0",
  "agent_role": "researcher",
  "task_id": "task-002",
  "session_id": "session-001",
  "timestamp": "2024-03-15T10:32:00Z",
  "goal": "Research OpenAI API rate limits",
  "task_type": "research",
  "relevant_files": [],
  "files_discovered": [],
  "excluded_files": [],
  "constraints": {
    "allowed_actions": ["webfetch", "mcp", "read"],
    "forbidden_actions": ["edit", "write", "bash"],
    "max_tokens": 3000,
    "read_only": true,
    "no_refactor": true
  },
  "docs_findings": [
    {
      "source": "https://platform.openai.com/docs/guides/rate-limits",
      "source_type": "mcp",
      "content": "Tier 1: 500 requests/minute, 150,000 tokens/minute. Tier 2: 3,000 requests/minute...",
      "relevance": "high"
    },
    {
      "source": "https://platform.openai.com/docs/api-reference/chat/create",
      "source_type": "mcp",
      "content": "max_tokens parameter range: 1-4096 for most models...",
      "relevance": "medium"
    }
  ],
  "risks": [],
  "recommended_next_agent": "none",
  "acceptance_criteria": [
    "Found current rate limits",
    "Identified retry strategy",
    "Included source URLs"
  ],
  "output_format": "json",
  "status": "success",
  "status_reason": "Rate limit documentation found and summarized",
  "metadata": {
    "tokens_used": 800,
    "model": "gpt-4o-mini",
    "duration_seconds": 8,
    "mcp_tools_used": ["docs-mcp"]
  }
}
```

---

## Source Priority

| Priority | Source Type |
|:---------|:------------|
| 1 | MCP-connected official docs (most reliable) |
| 2 | Official documentation URLs |
| 3 | Community resources |
| 4 | Blog posts (use with caution) |

---

## Stop Conditions

- [ ] Found at least one relevant doc finding with source URL
- [ ] Summary is accurate and specific
- [ ] Token budget NOT exceeded

If token budget is exhausted:
- Return `status: "partial"`
- Include what was found so far
- Include `remaining_work` in `status_reason`

---

## Anti-Patterns

**DO NOT:**
- Return documentation without source URL
- Use outdated sources without noting
- Fetch entire documents (extract only relevant parts)
- Make implementation recommendations (that's Executor role)
- Skip `source_type` field

**DO:**
- Always include source URL
- Mark source reliability
- Extract only relevant excerpts
- Use MCP tools when available
- Note if information is outdated

---

## MCP Tools Available

This role uses MCP servers for documentation access:

- **docs-mcp**: Official API documentation
- **webfetch**: Direct web fetching
- **filesystem**: Local documentation

Refer to MCP configuration for available servers.
