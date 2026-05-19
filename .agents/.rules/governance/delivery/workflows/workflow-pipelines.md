# Workflow Pipelines — End-to-End SDLC Workflows

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines canonical workflow pipelines that govern the full
lifecycle of work items within the Agent Harness OS. Pipelines ensure that
every task follows a structured, repeatable process from idea to completion.

Inspired by Superpowers' linear workflow pipeline with its strict plan
failure definitions, 1% skill invocation rule, and verification-before-
completion philosophy.

---
 
 ## 1) Minimal Supervisor Flow (v1 Default)
 
 This is the canonical pipeline for Token-Aware Orchestration. It minimizes Codex usage by pre-filtering context through specialized sub-agents.
 
 ```
  📥 Task Input ──► 🧩 Classify ──► 🔍 Explore ──► 📝 Artifact ──► 🛠 Execute ──► ✅ Review ──► 🚀 Done
     (Supervisor)    (Supervisor)    (Mapper)       (Mapper)      (Codex)        (Reviewer)
 ```
 
 ### Stage Details:
 1. **Classify**: Supervisor decides if it's an Explore or Execute task.
 2. **Explore**: Mapper/Docs Researcher explores the repo (Read-only, cheap model).
 3. **Artifact**: Sub-agent returns the Mandatory 9-field Artifact.
 4. **Decision**: Supervisor decides if the task is complete (stop) or requires Execute Mode (next).
 5. **Execute**: Codex Executor performs the change based ON the artifact (High-reasoning).
 6. **Review**: Final Reviewer checks the diff before completion.
 
 ## 2) The 1% Rule

If there is even a **1% chance** that a governance rule, skill, or standard
applies to the current task, the agent MUST invoke it. This ensures:
- No governance bypass due to agent judgment errors
- Comprehensive quality coverage
- Consistent adherence to standards

---

## 2) Standard Feature Pipeline

The canonical pipeline for implementing a new feature:

```
 📋 Brainstorm    📐 Plan       🔴 Test First    🛠 Implement
    (planner)     (architect)     (tester)        (implementer)
       │              │              │                 │
       ▼              ▼              ▼                 ▼
  Requirements   Design Doc     Failing Tests     Source Code
  + User Story   + API Spec     + Test Plan       + Comments
       │              │              │                 │
       └──────────────┴──────────────┴─────────────────┘
                              │
                    ✅ Verify (implementer)
                    Run tests + lint + type-check
                              │
                    🔍 Review (reviewer)
                    Multi-pass code review
                              │
                    📝 Document (documenter)
                    Docs + changelog + backlog
                              │
                    🚀 Complete (releaser)
                    Final validation + merge
```

### Stage Details

#### 1. Brainstorm (planner)
- **Input**: User requirement or feature request
- **Output**: Clear requirements, user stories, acceptance criteria
- **Gate**: Requirements must be unambiguous and testable

#### 2. Plan (architect)
- **Input**: Requirements from brainstorm
- **Output**: Implementation plan with file-level detail
- **Gate**: Plan must pass Plan Failure Check (§3)

#### 3. Test First (tester)
- **Input**: Implementation plan
- **Output**: Failing test suite covering acceptance criteria
- **Gate**: Tests must compile/parse but fail (TDD red phase)

#### 4. Implement (implementer)
- **Input**: Failing tests + implementation plan
- **Output**: Source code that passes all tests
- **Gate**: All tests pass (TDD green phase)

#### 5. Verify (implementer)
- **Input**: Completed implementation
- **Output**: Full validation results (tests + lint + type-check)
- **Gate**: Zero failures in test, lint, and type-check

#### 6. Review (reviewer)
- **Input**: Source code + test results
- **Output**: Review findings, approval/rejection
- **Gate**: All review checks pass per `how-to-code-review.md`

#### 7. Document (documenter)
- **Input**: Approved implementation
- **Output**: Updated docs, changelog, backlog
- **Gate**: Documentation is complete and accurate

#### 8. Complete (releaser)
- **Input**: All previous artifacts
- **Output**: Merged code, updated version, release notes
- **Gate**: All quality gates pass per `quality-gates.md`

---

## 3) Plan Failure Definitions

Implementation plans that contain any of the following are **automatically
rejected**. The agent MUST revise the plan before proceeding:

### Prohibited in Plans

| Violation | Example | Why It Fails |
|:---|:---|:---|
| **TBD markers** | "Authentication: TBD" | Undefined scope |
| **Vague descriptions** | "Handle errors appropriately" | No actionable detail |
| **Undefined references** | "Similar to Task 3" | Ambiguous dependency |
| **Placeholder code** | `// TODO: implement later` | Incomplete specification |
| **Missing file paths** | "Update the config file" | Which file? |
| **Scope gaps** | No test plan mentioned | Missing pipeline stage |
| **Circular references** | "See the plan for details" | Self-referential |

### Required in Plans

Every valid plan MUST include:
1. **Exact file paths** for every file to be created or modified
2. **Specific changes** described at the function/method level
3. **Test strategy** with concrete test cases
4. **Risk assessment** for non-trivial changes
5. **Rollback strategy** if the change fails

---

## 4) Bugfix Pipeline

A streamlined pipeline for fixing bugs:

```
🔍 Investigate → 🔴 Reproduce → 🛠 Fix → ✅ Verify → 🔍 Review
  (reviewer)      (tester)     (impl.)    (impl.)     (reviewer)
```

### Stage Details

1. **Investigate**: Analyze the bug report, identify root cause
2. **Reproduce**: Write a failing test that demonstrates the bug
3. **Fix**: Implement the minimum change to fix the bug
4. **Verify**: Ensure the fix passes and no regressions occur
5. **Review**: Verify the fix is correct and complete

---

## 5) Documentation Pipeline

For documentation-only changes:

```
📋 Scope → 📝 Write → 🔍 Review → ✅ Verify Links
 (planner)  (documenter) (reviewer)   (implementer)
```

---

## 6) Brainstorm Flow

For option exploration before commitment:

```
🧠 Gather Context → ⚖️ Compare Options → 🧾 Recommend Direction
   (planner)         (architect)         (planner)
```

Outputs:

1. bounded problem statement
2. viable options with tradeoffs
3. recommended direction and open risks

---

## 7) Planning Pipeline

For turning broad scope into an executable plan:

```
📋 Scope → 🔎 Inventory → 📐 Plan → 🧾 Queue/Handoff
 (planner)  (planner)    (architect)   (planner)
```

Minimum outputs:

1. exact scope and non-goals
2. file-level or surface-level impact map
3. executable plan with validation path
4. backlog or handoff record

---

## 8) Governance Pipeline

For changes to rules, profiles, scaffolds, installers, adapters, or precedence:

```
📋 Scope → 🧭 Impact Map → 🛠 Update Governance → ✅ Validate Surfaces → 🔍 Review → 🧾 Snapshot
 (planner)   (architect)      (implementer)           (implementer)       (reviewer)   (documenter)
```

Mandatory outputs:

1. affected precedence and path list
2. synchronized governance, scaffold, installer, and README changes
3. validation results
4. refreshed generated or merged artifacts when versioned

---

## 9) Review Pipeline

For review-only tasks where findings are the primary deliverable:

```
📋 Scope → 🔎 Inspect → 🧪 Validate Claims → 📝 Findings
 (reviewer)  (reviewer)    (reviewer)         (reviewer)
```

Primary output:

- ordered findings with file references, assumptions, and residual risks

---

## 10) Operations Pipeline

For incident, restore, runtime diagnosis, or operator tasks:

```
🚨 Triage → 🔎 Diagnose → 🛠 Repair/Contain → ✅ Verify → 🧾 Evidence
 (reviewer)  (reviewer)      (implementer)        (implementer)  (documenter)
```

Mandatory outputs:

1. trigger and impact statement
2. trace or incident identifiers
3. repair or containment action
4. verification result
5. operator evidence and rollback or follow-up note

---

## 11) Investigation Flow

For discovery tasks where diagnosis is the deliverable:

```
🔎 Scope → 📚 Read Evidence → 🧠 Form Hypothesis → 🧾 Findings/Next Step
 (reviewer)  (reviewer)       (architect)        (reviewer)
```

---

## 12) Security Review Flow

For security-focused review, audit, or triage work:

```
🔐 Scope → 🔎 Inspect Surface → 🧪 Validate Controls → 📝 Security Findings
 (security-reviewer) (security-reviewer) (security-reviewer) (security-reviewer)
```

---

## 13) Release Pipeline

For release, publish, or rollback preparation:

```
📦 Prepare Candidate → ✅ Verify Gates → 🧾 Release Evidence → 🚀 Execute / Rollback
   (releaser)          (reviewer)         (documenter)          (releaser)
```

The detailed release procedure is still governed by
`release-and-rollback-policy.md`.

---

## 14) Refactoring Pipeline

For code improvements without feature changes:

```
📐 Analyze → 🔴 Baseline Tests → 🛠 Refactor → ✅ Verify → 🔍 Review
 (architect)    (tester)         (implementer)   (implementer) (reviewer)
```

Key constraint: **Baseline tests must pass both before AND after refactoring.**

---

## 15) Verification-Before-Completion

No task is considered complete until:

1. All tests pass (zero failures)
2. Linting passes (zero warnings treated as errors)
3. Type checking passes (if applicable)
4. Code review is approved
5. Documentation is updated
6. Evidence is recorded with timestamps

The agent MUST NOT mark a task as done, complete, or finished until all
verification steps have passed. This is a **non-negotiable** rule.

---

## 16) Pipeline Selection

The agent selects the appropriate pipeline based on task classification
from `execution-policy.md`:

| Task Lane | Pipeline | Minimum Stages |
|:---|:---|:---|
| Brainstorm | Brainstorm Flow | 3 stages |
| Planning | Planning Pipeline | 4 stages |
| Feature | Standard Feature Pipeline | All 8 stages |
| Bugfix | Bugfix Pipeline | 5 stages |
| Governance | Governance Pipeline | 6 stages |
| Review | Review Pipeline | 4 stages |
| Documentation | Documentation Pipeline | 4 stages |
| Operations | Operations Pipeline | 5 stages |
| Security | Security Review Flow or Bugfix Pipeline | scoped subset |
| Refactoring | Refactoring Pipeline | 5 stages |
| Investigation | Investigation Flow | 4 stages |
| Release | Release Pipeline | 4 stages |

---

## 17) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `agent-roles.md` | Roles execute pipeline stages |
| `hooks-policy.md` | `PreTask` selects pipeline; `PostTask` verifies completion |
| `quality-gates.md` | Gates enforce per-stage verification |
| `execution-policy.md` | Task lane determines which pipeline |
| `how-to-code-review.md` | Review stage follows these standards |
| `approval-policy.md` | Approval required at specific gates |
| `memory-lifecycle.md` | PostTask extracts memories after completion |
