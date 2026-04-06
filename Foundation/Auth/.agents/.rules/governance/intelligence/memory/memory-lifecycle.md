# Memory Lifecycle — Extraction, Consolidation, and Injection Pipeline

Version: 1.0.0
Status: Normative
Scope: `.agents/management/memories/**`

This document defines the operational lifecycle for agent memories within the
Agent Harness OS. It extends the `universal-memory-standard.md` with concrete
phases, storage conventions, and quality constraints.

Inspired by multi-phase memory pipelines that extract knowledge from completed
sessions, consolidate it into searchable structures, and inject relevant
context into new sessions.

---

## 1) Memory Pipeline Overview

```
Completed Task                     New Session
     │                                  ▲
     ▼                                  │
┌─────────────┐                  ┌──────────────┐
│  Phase 1:   │                  │   Phase 3:   │
│  Extraction │                  │  Injection   │
└──────┬──────┘                  └──────┬───────┘
       │                                ▲
       ▼                                │
┌─────────────┐                  ┌──────────────┐
│  Phase 2:   │─────────────────►│   Memory     │
│Consolidation│                  │   Storage    │
└──────┬──────┘                  └──────────────┘
       │
       ▼
┌─────────────┐
│  Phase 2b:  │
│   Pruning   │
└─────────────┘
```

---

## 2) Storage Layout

All memory artifacts reside under `.agents/management/memories/`:

```
.agents/management/memories/
├── MEMORY.md                      # Consolidated long-term knowledge
├── memory_summary.md              # Compact version for context injection
├── raw_memories.md                # Unprocessed extraction results (append-only)
├── task_summaries/                # Per-task extraction outputs
│   ├── 2026-04-02_fix-auth-bug.md
│   ├── 2026-04-01_refactor-ui.md
│   └── ...
└── learned_skills/                # Skills discovered during tasks
    └── SKILL.md                   # Auto-generated skill definitions
```

### File Semantics

| File | Purpose | Written By | Max Size |
|:---|:---|:---|:---|
| `MEMORY.md` | Canonical consolidated knowledge base | Phase 2 | Unbounded |
| `memory_summary.md` | Truncated summary for injection | Phase 2 | ~5,000 tokens |
| `raw_memories.md` | Append-only raw extraction log | Phase 1 | Pruned periodically |
| `task_summaries/*.md` | Individual task extractions | Phase 1 | ~2,000 tokens each |

---

## 3) Phase 1: Extraction

### Trigger
Fires at the `PostTask` hook after every completed task.

### Process
1. **Identify completable task**: The task must have reached DoD
   (implementation + validation + evidence).
2. **Extract raw memory**: Summarize the task into two outputs:
   - `raw_memory`: Detailed markdown covering what was done, why, and how.
   - `task_summary`: Compact one-paragraph recap.
3. **Persist**: Write `task_summary` to `task_summaries/` with naming
   convention: `YYYY-MM-DD_<slug>.md`.
4. **Append**: Append `raw_memory` to `raw_memories.md` with timestamp
   header.

### Naming Convention
Task summary filenames use the pattern:
```
<ISO-date>_<kebab-case-task-slug>.md
```
Example: `2026-04-02_add-webhook-retry-logic.md`

### Content Template
```markdown
# Task Summary: <title>

**Date**: <ISO 8601 timestamp>
**Type**: bugfix | feature | refactor | docs | chore
**Profile**: <resolved profile name>

## What Was Done
<2-4 sentences>

## Key Decisions
- <decision 1>
- <decision 2>

## Files Changed
- `path/to/file.ts` — <brief description>

## Lessons Learned
- <insight that may help future tasks>
```

---

## 4) Phase 2: Consolidation

### Trigger
Consolidation runs when any of these conditions are met:
1. **Threshold**: More than 10 unprocessed task summaries exist.
2. **Scheduled**: At the start of any new session (if stale summaries exist).
3. **Manual**: Explicitly requested by the user.

### Process
1. **Gather**: Read all task summaries from `task_summaries/` that are
   newer than the last consolidation watermark.
2. **Merge**: Incorporate new knowledge into `MEMORY.md`, organized by
   topic/domain rather than chronologically.
3. **Summarize**: Regenerate `memory_summary.md` as a compact version of
   `MEMORY.md`, truncated to ~5,000 tokens.
4. **Watermark**: Record the consolidation timestamp to prevent
   reprocessing.

### MEMORY.md Structure
```markdown
# Agent Memory — Consolidated Knowledge

Last consolidated: <ISO 8601 timestamp>
Watermark: <latest task_summary filename processed>

## Architecture Patterns
- <learned pattern 1>
- <learned pattern 2>

## User Preferences
- <preference 1>
- <preference 2>

## Proven Workflows
- <workflow 1>
- <workflow 2>

## Known Pitfalls
- <pitfall 1>
- <pitfall 2>

## Domain Knowledge
- <domain fact 1>
- <domain fact 2>
```

---

## 5) Phase 2b: Pruning

### Purpose
Prevent unbounded growth of memory storage.

### Rules
1. **Stale summaries**: Task summaries unused for more than 90 days
   (`max_unused_days = 90`) are pruned from `task_summaries/`.
2. **Batch size**: Prune at most 20 summaries per consolidation cycle.
3. **Protection**: Summaries referenced by `MEMORY.md` are never pruned
   until the reference is removed during consolidation.
4. **Raw memories**: `raw_memories.md` entries older than 180 days may be
   removed if their content has been consolidated into `MEMORY.md`.

### Usage Tracking
Each task summary tracks a `usage_count` in its YAML frontmatter:
```yaml
---
usage_count: 3
last_used: 2026-04-01T10:00:00+02:00
---
```
Summaries with `usage_count = 0` and age > `max_unused_days` are pruned first.

---

## 6) Phase 3: Injection

### Trigger
At the start of every new agent session (fires before `PreTask` hook).

### Process
1. **Read**: Load `memory_summary.md`.
2. **Truncate**: If content exceeds injection token limit (~5,000 tokens),
   truncate to the most recent/relevant sections.
3. **Inject**: Prepend the summary to the agent's working context as
   background knowledge.
4. **Citation requirement**: When the agent uses information from memory,
   it MUST cite the source file and section:
   ```
   [memory: MEMORY.md § Proven Workflows]
   ```

### Injection Format
```markdown
<memory-context>
The following is a summary of knowledge from previous sessions.
Use this to inform your decisions. Cite specific sections when referencing
this knowledge.

<contents of memory_summary.md>
</memory-context>
```

---

## 7) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `universal-memory-standard.md` | This file operationalizes the abstract standard |
| `hooks-policy.md` | Phase 1 fires at `PostTask`; Phase 3 fires at session start |
| `quality-gates.md` | Memory extraction is part of the DoD evidence chain |
| `execution-policy.md` | Memory injection informs task classification |
