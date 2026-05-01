# Prompt To Governance Flow

Version: 1.0.0
Status: Normative
Scope: `AGENTS.md`, `.agents/governance/**`, `.agents/hooks/**`, `.agent/**`

This document defines the canonical event-driven flow that turns a raw prompt
into an executable governance route.

It exists to answer one production question deterministically:

`Given this repository and this prompt, what should the agent read, which lane
and pipeline should it choose, which role should start, what trust posture
applies, and where should evidence go?`

## Core Rule

The agent MUST not jump from prompt text directly into implementation.

Before meaningful execution starts, it must produce a task routing manifest that
records:

- which repo contract was loaded
- how the prompt was classified
- which lanes were selected
- which governance files were selected
- which pipeline and starting role apply
- which context sources are injected
- which trust and approval posture applies
- which evidence targets must be updated

## Canonical Event Chain

The routing chain is:

1. `AGENTSLoaded`
2. `PromptAnalyzed`
3. `IntentClassified`
4. `LaneResolved`
5. `StackResolved`
6. `GovernancePackSelected`
7. `PipelineSelected`
8. `StartingRoleSelected`
9. `ContextAssembled`
10. `SubagentsPlanned`
11. `TrustResolved`
12. `EvidenceTargetsResolved`
13. `TaskManifestWritten`
14. `TaskReady`

Each event must be explainable from repository facts, the prompt, or explicit
local declarations.

## Required Inputs

Routing uses these inputs in order:

1. root `AGENTS.md`
2. reusable rules root: `.agents/.rules/` when present, otherwise `.agents/`
3. current prompt
4. repository tree signals
5. active session context under `.agent/sessions/<SESSION_ID>/`
6. memory and context sources from `.agent/` and `.agents/management/`

## Routing Outputs

The routing step must emit both machine-readable and human-readable artifacts.

Required artifact layout:

```text
.agent/
└── sessions/
    └── <SESSION_ID>/
        ├── current-task
        ├── session_memory.md
        └── tasks/
            └── <TASK_ID>/
                ├── context.json
                ├── context.md
                ├── subagents/
                │   ├── manifest.json
                │   └── 01-explore-core.md
                ├── events.log
                └── result.json
```

## Task Manifest Contract

`context.json` should contain at least:

- schema version
- timestamp, project root, project ID, session ID, task ID, trace ID
- prompt hash and prompt preview
- intent classification
- primary lane and secondary lanes
- selected pipeline
- starting role and recommended role chain
- trust tier and approval mode
- declared and inferred stack facts
- delegation plan and subagent briefs when the prompt is broad enough to benefit
  from offloading discovery or review
- relevant governance files:
  - `must_read`
  - `should_read`
- context injection plan
- evidence targets
- artifact paths

## Intent Classification Rules

The routing engine should classify:

- prompt intent
- task kind
- execution lane
- possible secondary lanes

Intent classification may use heuristics, but it must follow these guardrails:

- explicit user instruction beats inference
- broad or ambiguous prompts stay on broader lanes
- missing confidence must not invent narrow stack rules
- security, release, and operations concerns must be added whenever the prompt
  clearly touches those boundaries

## Governance Pack Selection

The selected rule pack must include:

1. universal governance floor
2. lane-specific governance
3. relevant repository-kind profiles
4. relevant language and framework profiles
5. relevant architecture overlays
6. security and operations layers when needed
7. local support docs when they materially shape execution

The router should distinguish:

- `must_read`: directly constrains the task
- `should_read`: useful support context, examples, or project-local detail

## Pipeline And Role Selection

The routing step must map the task to one canonical pipeline.

If no exact pipeline exists, the routing result must say so explicitly and fall
back to the closest defined flow instead of silently improvising.

The starting role is the role that should own the first controlled stage of the
selected pipeline.

## Context Injection Plan

The router must assemble context in this order:

1. governance floor
2. selected rule pack
3. memory summary and prior session checkpoint
4. local product or business context
5. relevant evidence and backlog targets
6. relevant skills when explicitly requested or strongly matched

Context injection must name exact files, not only categories.

## Subagent Planning

When the task is broad, multi-topic, or clearly benefits from discovery offload,
the router should emit a subagent plan.

The plan SHOULD:

- keep the main agent focused on synthesis and integration
- assign read-only exploration to subagents whenever possible
- prefer the smallest possible brief that still answers the prompt
- keep the planned subagents visible as artifacts under the task directory

This is where Cline-style read-only research subagents and OpenCode-style
primary/subagent orchestration both fit the same harness contract.

## Trust And Approval Resolution

Before tool execution begins, the routing manifest must bind:

- trust tier
- approval mode
- whether dangerous-op checks are active
- whether security and operations overlays are mandatory

The agent must not wait until the first dangerous command to discover its trust
model.

## Evidence Resolution

The routing step must select evidence targets before implementation starts.

Typical targets include:

- `.agents/management/TODO.md`
- `.agents/management/BUGS.md`
- `.agents/review/REVIEWS.md`
- `.agents/management/evidence/CHANGELOG.md`
- `.agents/management/evidence/RISK_REGISTER.md`
- `.agents/management/evidence/RELEASE_CHECKLIST.md`
- `.agents/management/evidence/TRACE_REPORTS.md`

## Failure Modes

Routing fails when:

- the repo contract cannot be found
- the prompt cannot be mapped even to a broad lane
- selected governance files do not exist
- trust posture cannot be determined
- task artifacts cannot be written

On failure, the router must stop with an explicit error instead of guessing.

## Runtime Binding

The portable runtime entrypoints are:

- `.agents/hooks/session-start.sh`
- `.agents/hooks/pre-task.sh`
- `.agents/hooks/pre-tool-use.sh`
- `.agents/hooks/post-tool-use.sh`
- `.agents/hooks/post-task.sh`
- `.agents/hooks/resolve-task-context.py`

`pre-task.sh` is the canonical place where this routing flow is executed.

## Relationship To Other Standards

| Standard | Relationship |
|:---|:---|
| `../policy/execution-policy.md` | execution contract after routing |
| `../hooks/hooks-policy.md` | routing runs at `PreTask` and closes at `PostTask` |
| `../../core/resolution/profile-resolution-algorithm.md` | stack and lane resolution logic |
| `../../delivery/workflows/workflow-pipelines.md` | pipeline and role-chain selection |
| `../../agents/roles/agent-roles.md` | starting-role and handoff rules |
| `../../intelligence/context/context-management.md` | context injection priority |
| `../approvals/approval-policy.md` | trust tier and approval posture |
| `../../delivery/operations/observability-and-error-envelope.md` | trace and routing evidence |
