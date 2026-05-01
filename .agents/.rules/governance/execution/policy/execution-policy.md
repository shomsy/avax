# Execution Policy

Version: 1.2.0
Status: Normative

## Purpose

Define the transition from analysis to execution and prevent endless re-analysis,
scope drift, and undocumented deviations.

## 1. Execution Modes

The Agent OS operates in two distinct modes to optimize for both discovery and implementation:

### 1.1 Explore Mode (Read-Only)
- **Goal**: Discovery, mapping, research, documentation analysis.
- **Constraints**: **NO CODE CHANGES ALLOWED**.
- **Agent Choice**: Use "cheap" or "mini" models (Mapper, Docs Researcher).
- **Output**: Mandatory artifact summarizing findings (Brief/Research).

### 1.2 Execute Mode (Implementation)
- **Goal**: Code writing, refactoring, bug fixes, integration.
- **Constraints**: Restricted to the **approved scope** and file list from Explore Mode.
- **Agent Choice**: Use primary/powerful models (Codex Executor).
- **Execution Lane**: Implementation must follow the predefined role chain.

Rules:

- each pass must leave the repository in a valid state
- no silent redesign or unapproved scope expansion
- local release or delivery rituals belong in the adopting repository, not here

## 2. Iteration Contract

Each iteration should declare:

1. primary goal
2. non-goals
3. constraints
4. completion criteria
5. execution lane

## 3. Completion Ceremony

Every iteration, regardless of size, MUST conclude with the closing steps
defined by the adopting repository.

At minimum, closure must:

1. record what changed
2. record how it was validated
3. update the active queue or bug record with timestamps
4. perform any local release, sync, or archival step required by the repo
5. **Phase 5 & 8: Memory Extraction** - Extract learnings and update the `.agents/memory/` memory or session logs as per the **Universal Memory Standard**.
6. **ProdOps: Meeting Snapshot to Action** - If the iteration involves a meeting cleanup, extract triggers, decisions, and actions into `.agents/management/TODO.md` and `.agents/context/`.

## 4. Evidence Requirements

A completed iteration must include:

- changed file list
- validation commands and results
- backlog status update in `.agents/management/TODO.md` or `.agents/management/BUGS.md`
- timestamped evidence or management update using the local timeline policy
- any local evidence artifact required by project governance
- **Memory Delta**: Updated `.agents/memory/` or session-specific context extraction.
