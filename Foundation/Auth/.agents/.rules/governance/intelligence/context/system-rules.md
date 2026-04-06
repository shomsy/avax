# System Rules - Sub-Agent Orchestration

Version: 1.0.0
Status: Normative
Scope: `.agents/**`

This document defines the core system rules for the sub-agent orchestration.

---

## 1) Codex is NOT Default

**Codex is a specialist, NOT a secretary.**

Codex (the strong/executor model) MUST NOT be the default agent for every task.
It should ONLY be used when:
- Implementation is required (feature, bugfix, refactor)
- Repo-wide reasoning is needed
- Final integration of multiple inputs is required
- Technical review with strong judgment is needed

Codex MUST NOT be used for:
- Code mapping / file discovery
- First documentation read
- CI log summarization
- File search
- Task classification
- "Tell me what happens here" exploratory tasks

---

## 2) Two Modes of Operation

### Explore Mode (Read-Only)

**When to use:**
- Mapping codebase structure
- Finding relevant files
- Researching documentation
- CI log analysis
- Any task without code changes

**Characteristics:**
- Use cheap/fast models (gpt-4o-mini)
- No code modifications
- Returns artifact with file references
- Budget: 2,000-3,000 tokens

### Execute Mode (Write)

**When to use:**
- Implementing features
- Bug fixes
- Significant refactoring
- Multi-file changes
- Tasks that require code changes

**Characteristics:**
- Use strong model (gpt-4o, Claude, etc.)
- Code modifications allowed
- Returns artifact with code_changes
- Budget: 10,000-15,000 tokens

---

## 3) Four Core Roles

| Role | Mode | Trust Tier | Model | Purpose |
|:-----|:-----|:-----------|:-------|:--------|
| **Supervisor/Router** | - | - | - | Routes tasks, orchestrates flow |
| **Mapper** | Explore | T0 | gpt-4o-mini | Map code, find files |
| **Researcher** | Explore | T0 | gpt-4o-mini | Docs, API, external research |
| **Executor** | Execute | T1 | gpt-4o | Implement, fix, refactor |
| **Reviewer** | - | T0 | gpt-4o | Review changes, identify risks |

---

## 4) Routing Rules

### Automatic Routing by Task Type

| Task Pattern | First Agent | Next Agent |
|:-------------|:------------|:------------|
| "map / where / how does / find / explore / trace / understand" | Mapper | Executor if code change needed |
| "docs / documentation / API / version / learn about" | Researcher | Executor if implementation needed |
| "CI / build / deploy / failed / log / test failure" | Researcher + CI MCP | Executor if fix needed |
| "implement / fix / refactor / change / modify / add / create" | Executor | Reviewer |
| "review / check / verify / audit / risks" | Reviewer | (end) |

### If Task is Unclear:
**Always** send to Mapper first. Never start with Executor.

---

## 5) Context Budget Rules

### Per-Agent Limits

| Agent | Max Files | Max Tokens | Max Goals |
|:------|:----------|:-----------|:----------|
| Mapper | 10 | 2,000 | 1 |
| Researcher | 5 | 3,000 | 1 |
| Executor | 20 | 15,000 | 1 |
| Reviewer | 15 | 5,000 | 1 |

### Supervisor Rules:
- NEVER pass entire repo to any agent
- ALWAYS pass pruned context
- ALWAYS include `relevant_files` in brief
- ALWAYS include `acceptance_criteria`

---

## 6) Model Selection

### Default Models

| Task Type | Recommended Model |
|:----------|:------------------|
| Mapper / Explorer | gpt-4o-mini |
| Researcher / Docs | gpt-4o-mini |
| Executor / Code | gpt-4o |
| Reviewer | gpt-4o |

### Override via Environment:
```bash
export AGENT_HARNESS_MODEL_MAPPER="gpt-4o-mini"
export AGENT_HARNESS_MODEL_EXECUTOR="gpt-4o"
export AGENT_HARNESS_MODEL_REVIEWER="gpt-4o"
```

---

## 7) Trust Tier Enforcement

| Tier | Role | Access | Default |
|:-----|:-----|:-------|:--------|
| **T0** | Mapper, Researcher, Reviewer | Read-only | All agents default to T0 |
| **T1** | Executor | Workspace write | Only for execute tasks |
| **T2** | (Future) | External write | Requires human approval |

Default: If not specified, treat as T0 (no write).

---

## 8) Human Approval Gates

Tasks requiring human approval:
- External write operations (T2+)
- Production deployments
- Secret modifications
- Dependency additions
- Any `external_write` action

Before any T2 operation:
1. Supervisor requests approval
2. Human must explicitly approve
3. Only then proceed to Executor

---

## 9) Metrics & Success Measurement

Track these metrics:

| Metric | Description |
|:-------|:------------|
| `tasks_without_codex` | Tasks resolved without calling Executor |
| `tasks_with_codex` | Tasks that required Executor |
| `avg_codex_input_tokens` | Average token input to Executor |
| `routing_accuracy` | How often first agent was correct |
| `failed_artifacts` | Artifacts that failed validation |

Success goal: **Reduce Executor calls by 60%+**

---

*End of System Rules*
