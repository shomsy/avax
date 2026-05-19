# Universal Agent Memory Standard

Version: 1.0.0
Status: Normative / Agent-Agnostic

## Purpose

Define a standardized memory lifecycle for agents operating within this governance
framework. This standard ensures that agents can maintain cross-session context,
learn from past interactions, and manage their own "internal" documentation
without provider-specific lock-in.

## 0) The Agent Directory

All memory and session artifacts should reside in the `.agent/` directory at the
repository root. This directory is typically ignored by version control except
for specific managed memory files.

## 1) Phase 1: Process Launch & Session Init

Every interaction must belong to a **Session**.
- A unique `SESSION_ID` must be generated or resolved.
- The agent must verify the presence of `.agent/` structure.
- Initialization rituals (cache warming, rule discovery) must precede execution.

## 2) Phase 2: Memory Discovery

Agents must resolve rules and context in a hierarchical order (highest priority last):

1. **Managed**: System-wide or enterprise-level rules.
2. **User**: Global user-specific settings (e.g., `~/.agent/`).
3. **Project**: Repository-level rules (e.g., `.agents/AGENTS.md`).
4. **Local**: Uncommitted local overrides (e.g., `AGENTS.local.md`).
5. **Memory**: Long-term project memory (e.g., `.agent/memory/MEMORY.md`).

## 3) Phase 3: Context Assembly

Before an API call, the agent must assemble the context:
- **System Prompt**: Core governance and identity.
- **Memory Section**: Relevant snippets from Phase 2.
- **User Context**: Current task and cursor state.
- **System Context**: Git status, branch, and environment facts.

## 4) Phase 4: Direct Memory Operations

The agent is authorized to perform CRUD operations on memory files:
- `Read`: Fetch topic-specific memory (e.g., `coding-patterns.md`).
- `Write/Edit`: Update memories with new facts or corrections.
- `Search`: Grep through the `.agent/memory/` directory for relevance.

## 5) Phase 5: Post-Response Background Extraction

After a response is generated, a background process (or the agent itself) must:
- Extract key learnings, recurring bugs, or architectural decisions.
- Append these to the active session log or relevant memory topic.
- Update `SESSION_HISTORY.md` or equivalent.

## 6) Phase 6: Compaction & Integration

To manage context window limits:
- Old or redundant session messages must be summarized.
- Summaries are integrated into the "Long-Term Memory" (`MEMORY.md`).
- Ephemeral details are pruned.

## 7) Phase 7: Persistence Layer Layout

Standard directory structure:
```markdown
.agent/
├── memory/
│   ├── MEMORY.md         # The main index and core facts
│   ├── learnings.md      # Accumulated technical lessons
│   └── architecture.md   # Project-specific design decisions
└── sessions/
    └── <SESSION_ID>/
        ├── current-task
        ├── transcript.json
        └── session_memory.md
        └── tasks/
            └── <TASK_ID>/
                ├── context.json
                ├── context.md
                ├── events.log
                └── result.json
```

## 8) Phase 8: Cross-Session Feedback Loop

The memory system forms a **Self-Improving Loop**:
- Session N extracts `->` Session Memory.
- Session Memory consolidates `->` Project Memory.
- Session N+1 reads Project Memory `->` Improved Performance.

## Completion Criteria

A memory operation is complete only when:
1. Facts are verified against the real code (no hallucinations).
2. The `.agent/` structure remains consistent.
3. The "Self-Improving Loop" is updated with the latest evidence.
