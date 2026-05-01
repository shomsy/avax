# Continuous Learning — Observe, Detect, Evolve Pipeline

Version: 1.0.0
Status: Normative
Scope: `.agents/management/learning/**`

This document defines the continuous learning system that enables agents to
autonomously detect behavioral patterns during sessions and codify them into
reusable governance artifacts.

Extends `memory-lifecycle.md` (which handles *what to remember*) with a system
for *what to learn* — turning repeated observations into instincts, and
graduated instincts into formal skills, rules, and governance additions.

Inspired by ECC's Continuous Learning v2 architecture with its observation
hooks, instinct management, and evolution pipeline.

---

## 1) Learning Pipeline Overview

```
Tool Invocation          Instinct Maturation          Governance Growth
      │                        │                            │
      ▼                        ▼                            ▼
┌─────────────┐         ┌──────────────┐            ┌──────────────┐
│  Phase 1:   │         │   Phase 2:   │            │   Phase 3:   │
│  Observe    │────────►│   Detect     │───────────►│   Evolve     │
│  (capture)  │         │  (instincts) │            │  (graduate)  │
└─────────────┘         └──────────────┘            └──────────────┘
     hooks                 pattern                    skills/rules
     policy                analysis                   governance
```

---

## 2) Storage Layout

All learning artifacts reside under `.agents/management/learning/`:

```
.agents/management/learning/
├── observations.jsonl           # Append-only tool usage log
├── instincts/                   # Discovered behavioral patterns
│   ├── prefer-const-over-let.md
│   ├── always-run-lint.md
│   └── ...
├── evolved/                     # Graduated artifacts
│   ├── skills/                  # Promoted to formal skills
│   ├── rules/                   # Promoted to governance rules
│   └── agents/                  # Promoted to agent definitions
└── config.md                    # Learning system configuration
```

### File Semantics

| File | Purpose | Written By | Lifecycle |
|:---|:---|:---|:---|
| `observations.jsonl` | Raw tool invocation capture | Phase 1 hooks | Pruned periodically |
| `instincts/*.md` | Detected patterns with confidence | Phase 2 analysis | Pruned or promoted |
| `evolved/skills/` | Skills graduated from instincts | Phase 3 evolution | Moved to `.agents/skills/` |
| `evolved/rules/` | Rules graduated from instincts | Phase 3 evolution | Merged into governance |

---

## Runtime Support

The baseline implementation helpers live alongside the learning artifacts:
- `.agents/hooks/post-tool-use.sh` captures Phase 1 observations
- `.agents/management/learning/analyze-instincts.py` performs Phase 2 analysis

These helpers keep the observation and instinct pipeline outside the manager
agent's active context window.

---

## 3) Phase 1: Observe (Capture)

### Trigger
Fires at `PostToolUse` hook for every tool invocation during a session.

### What to Capture
Each observation records:
```jsonl
{"ts":"2026-04-02T20:10:00+02:00","tool":"FileWrite","input_summary":"Created auth.ts","output_summary":"Success","project_id":"abc123","session_id":"sess-001"}
```

### Fields

| Field | Type | Description |
|:---|:---|:---|
| `ts` | ISO 8601 | Timestamp of invocation |
| `tool` | string | Tool name (FileRead, FileWrite, Bash, etc.) |
| `input_summary` | string | Compact summary of inputs (max 200 chars) |
| `output_summary` | string | Compact summary of result (max 200 chars) |
| `project_id` | string | Project identity hash (from git remote or path) |
| `session_id` | string | Current session identifier |

### Project Scoping
Observations are scoped per-project using project identity:
1. **Preferred**: Hash of `git remote get-url origin` (portable across clones)
2. **Fallback**: Hash of repository root absolute path

### Volume Control
- Maximum file size: 50,000 lines
- When exceeded: Archive oldest 25,000 lines to `observations.archive.jsonl`
- Archived files are pruned after 30 days if content has been analyzed

---

## 4) Phase 2: Detect (Instincts)

### Trigger
Analysis runs when any of these conditions are met:
1. Observation count exceeds 100 unanalyzed entries
2. At session end (if unanalyzed observations exist)
3. Manual request

### What to Detect
The analysis looks for:
- **Repeated tool sequences**: Same 3+ tool chain used multiple times
- **Consistent patterns**: Agent consistently makes the same choice
- **Error-correction loops**: Agent detects and fixes the same type of error
- **Style preferences**: Consistent code style, naming, or structure choices

### Instinct Format
Instincts are stored as YAML-frontmatter markdown in `instincts/`:

```markdown
---
id: prefer-const-over-let
trigger: "When declaring variables in TypeScript"
action: "Use const unless reassignment is needed"
confidence: 0.5
domain: code-style
scope: project
occurrences: 4
first_seen: 2026-04-01T10:00:00+02:00
last_seen: 2026-04-02T20:00:00+02:00
---

## Evidence
- Session sess-001: Used const in 12/14 declarations
- Session sess-003: Refactored let→const in review feedback
```

### Confidence Scoring

| Occurrences | Confidence | Status |
|:---:|:---:|:---|
| 1–2 | 0.3 | Tentative — barely observed |
| 3–5 | 0.5 | Emerging — consistent enough to note |
| 6–10 | 0.7 | Established — reliable pattern |
| 11+ | 0.85 | Mature — eligible for promotion |

### Instinct Domains

| Domain | Examples |
|:---|:---|
| `code-style` | Naming conventions, formatting preferences |
| `testing` | Test-first patterns, assertion styles |
| `git` | Commit message format, branching strategy |
| `architecture` | File organization, module patterns |
| `workflow` | Tool usage sequences, review habits |
| `security` | Vulnerability avoidance patterns |

---

## 5) Phase 3: Evolve (Graduate)

### Trigger
Manual review or when instinct reaches confidence ≥ 0.85.

### Promotion Targets

| Source Confidence | Promotion Target | Destination |
|:---:|:---|:---|
| ≥ 0.85 | Governance Rule | `evolved/rules/` → merge into governance |
| ≥ 0.85 | Formal Skill | `evolved/skills/` → move to `.agents/skills/` |
| ≥ 0.85 | Agent Definition | `evolved/agents/` → move to agent roles |

### Promotion Process
1. **Review**: Human reviews the instinct and its evidence
2. **Formalize**: Convert instinct to proper SKILL.md or governance rule format
3. **Move**: Place graduated artifact in the appropriate location
4. **Archive**: Mark original instinct as `promoted: true`
5. **Log**: Record the promotion in `observations.jsonl` as a meta-event

### Graduation Criteria
An instinct MUST meet ALL of these before promotion:
1. Confidence ≥ 0.85
2. Last occurrence within the last 30 days (still active)
3. No contradicting instincts exist
4. Human has reviewed and approved

---

## 6) Guardrails

### Self-Loop Prevention
The learning system MUST NOT observe its own analysis actions. Any tool
invocation triggered by Phase 2 or Phase 3 is excluded from `observations.jsonl`.

### Re-entrancy Guard
Only one analysis process may run at a time. If Phase 2 is already running,
new triggers are queued.

### Cooldown
Minimum 60 seconds between analysis runs to prevent resource exhaustion.

### Scope Isolation
- Project-scoped instincts only apply to the project they were observed in
- Global instincts (scope: `global`) require manual promotion by human

---

## 7) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `memory-lifecycle.md` | Memories = what to remember; Learning = what to learn |
| `hooks-policy.md` | Phase 1 fires at `PostToolUse`; Phase 2 fires at session end |
| `instincts-policy.md` | Defines the instinct format and confidence model in detail |
| `skill-contract.md` | Graduated skills must comply with the skill contract |
| `feature-flags.md` | Controlled by `continuous_learning` flag |
| `quality-gates.md` | Graduated rules become quality gates |
