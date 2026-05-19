# Context Window Management — Budget Allocation & Compaction Strategy

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines how agents should manage their context window when
operating under the Agent Harness OS. It establishes budget allocations,
compaction triggers, and priority rules to prevent context overflow during
long-running tasks.

Inspired by ECC's Strategic Compaction system and Cline's context budget
model that ensures agents never lose critical governance context even at
window limits.

---

## 1) Context Budget Allocation

The agent's total context window is divided into priority tiers:

| Tier | Max % | Content | Priority |
|:---:|:---:|:---|:---:|
| **P0** | 10% | Governance rules (AGENTS.md, active policies) | Highest |
| **P1** | 15% | Active skills (injected SKILL.md bodies) | High |
| **P2** | 10% | Memory context (memory_summary.md) | Medium |
| **P3** | 65% | Working context (conversation, code, files) | Standard |

### Budget Rules
1. **P0 is non-compactable**: Governance rules are never removed from context
2. **P1 caps at 3 skills**: Maximum 3 skills injected simultaneously
   (10,000 tokens each, 30,000 total per `skill-contract.md`)
3. **P2 truncates gracefully**: Memory summary can be shortened if needed
4. **P3 is the compaction target**: Working context is compacted first
5. **Sub-Agent Context Limit**: Sub-agents (Mapper, Docs Researcher) are strictly limited to **2-5 files** and **1 primary goal** per pass.
6. **Codex Context Limit**: Codex Executor is limited to the files and goals identified in the sub-agent's artifact.

---

## 2) Compaction Triggers

Context compaction fires when any of these conditions are met:

| Trigger | Threshold | Action |
|:---|:---:|:---|
| Window utilization | ≥ 80% | Compact working context |
| Tool output too large | > 5,000 tokens | Summarize instead of raw include |
| Session duration | > 30 exchanges | Compact older exchanges |
| Manual request | User asks | Full compaction with checkpoint |

---

## 3) Compaction Protocol

### Step 1: Checkpoint
Before compacting, save a checkpoint with:
- Current task state and progress
- Key decisions made so far
- Open questions or blockers
- Files modified in this session

Checkpoint format:
```markdown
## Context Checkpoint — <ISO timestamp>

### Task State
- Working on: <current task description>
- Progress: <stage in workflow pipeline>
- Role: <current agent role>

### Key Decisions
1. <decision 1 with rationale>
2. <decision 2 with rationale>

### Modified Files
- `path/to/file.ts` — <brief change summary>

### Open Items
- <blocker or question 1>
```

### Step 2: Summarize Older Exchanges
Replace detailed older exchanges with compact summaries:
- Keep the last 10 exchanges in full detail
- Compress exchanges 11–30 into a 2-sentence summary each
- Drop exchanges older than 30 (they should be in checkpoint)

### Step 3: Trim Tool Outputs
Replace large tool outputs with summaries:
- File contents > 200 lines → show first/last 20 lines + summary
- Command outputs > 100 lines → show last 30 lines + summary
- Search results > 50 matches → show top 10 + count

### Step 4: Verify Governance Integrity
After compaction, verify that P0 tier is intact:
- AGENTS.md precedence rules still in context
- Active governance policies still loaded
- Current trust tier still enforced

---

## 4) Priority Queue for Context Injection

When injecting context at session start, use this priority order:

1. **Governance rules** (AGENTS.md + active policies)
2. **Memory summary** (memory_summary.md)
3. **Active instincts** (high-confidence instincts for this project)
4. **Relevant skills** (skills matching current task)
5. **Previous session checkpoint** (if resuming)
6. **Conversation history** (most recent first)

If total injection exceeds the context budget, trim from the bottom
of the list (conversation history first, then checkpoints, etc.).

---

## 5) Large Content Strategies

### File Reading
When reading files that may be large:
1. Read file metadata first (line count, size)
2. If > 500 lines, read only the relevant section
3. Use search/grep to locate specific content before reading

### Command Output
When running commands that may produce large output:
1. Pipe through `head`, `tail`, or `grep` to filter
2. Limit `git log` to `-n 20` entries
3. Use `--summary` flags when available

### Multiple Files
When working across many files:
1. Process files sequentially, not all at once
2. Summarize each file's changes before moving to the next
3. Keep a running tally of all modifications

---

## 6) Token Optimization Patterns

Inspired by ECC's token optimization strategies:

| Pattern | Savings | How |
|:---|:---:|:---|
| Compact file references | ~30% | Use `file.ts:L45-60` instead of full content |
| Abbreviated diffs | ~50% | Show only changed lines, not full context |
| Structured summaries | ~60% | JSON/table summaries instead of prose |
| Deferred loading | ~40% | Load files on-demand, not upfront |
| Batch operations | ~20% | Group related reads into single operation |

---

## 7) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `memory-lifecycle.md` | Memory injection consumes P2 budget |
| `skill-contract.md` | Skill injection consumes P1 budget (max 3 skills) |
| `hooks-policy.md` | `PreTask` inject context follows priority queue |
| `feature-flags.md` | Compaction behavior controlled by flags |
| `continuous-learning.md` | Learning observations excluded from compaction |
