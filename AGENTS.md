# AGENTS.md - AvaX Thin Execution Contract

Version: 4.0.0
Status: Normative / Local / Root Contract
Scope: `./**`
Project: AvaX

This file is the first contract every AI agent must read before planning, editing, validating, reviewing, committing, or reporting work in AvaX.

AGENTS.md is intentionally small. It is the constitution and router, not the encyclopedia.

Detailed SDLC rules live in:

```text
.agents/how-to/00-how-to-reading-order.md
.agents/how-to/**
.agents/skills/**
.agents/management/**
ARCHITECTURE.md
```

If this file and another governance document disagree, this file wins for routing, stop conditions, precedence, source-of-truth selection, evidence, validation, branch policy, and final reporting.

---

## 0. AvaX Goal

AvaX is a runtime-agnostic PHP application platform and engineering system.

The goal is to build AvaX as:

```text
flow-oriented
capability-oriented
runtime-neutral
component-based
evidence-first
security-aware
performance-aware
AI-readable
production-readiness governed
```

The project north star is `ARCHITECTURE.md`.

The execution map is `.agents/how-to/00-how-to-reading-order.md`.

The project overlay is `.agents/how-to/project/how-to-write-avax.md`.

---

## 1. What AGENTS.md Does

AGENTS.md defines:

```text
- non-negotiable stop laws
- rule precedence
- source-of-truth order
- execution mode routing
- mandatory skill routing
- required preflight
- branch/worktree policy
- validation and evidence contract
- output/reporting contract
```

AGENTS.md does not duplicate detailed rules for architecture, components, testing, security, performance, documentation, review, or implementation. Those rules are loaded through `.agents/how-to/00-how-to-reading-order.md` and task-relevant skills.

---

## 2. Non-Negotiable Laws

These laws apply to every task:

```text
1. Evidence beats optimism.
2. Current validation is the judge.
3. No production-code implementation directly on main.
4. No implementation on dirty main.
5. One bounded task slice owns one evidence package and one review decision.
6. No Identity implementation may start during governance-only work.
7. No finding may be fixed by suppression, weakening, bypass, or concealment.
8. No GREEN claim without current validation and evidence.
9. BLOCKER and unresolved HIGH findings block GREEN and commit.
10. Tests must prove behavior, not construction trivia.
11. Security-sensitive behavior must fail closed and have negative tests.
12. Runtime hot paths must be proven safe for long-lived workers.
13. PublicSurface receives and delegates; it must not own runtime machinery.
14. Configuration/Assembly/Provider boundaries assemble object graphs.
15. Flows execute behavior; Capabilities power reusable behavior.
16. Components must dogfood AvaX capabilities where appropriate.
17. Generated evidence proves status but is not canonical governance unless promoted.
18. PARTIAL is not a stop condition in autonomous work.
19. HARD_BLOCKER is the real stop condition.
20. Every remaining deviation must be classified.
```

Detailed architecture laws are in:

```text
ARCHITECTURE.md
.agents/how-to/project/how-to-write-avax.md
.agents/how-to/architecture/**
.agents/how-to/components/**
```

---

## 2A. Deviation Audit Lifecycle

For implementation, refactor, remediation, validation, review, or readiness claims, use this lifecycle:

```text
validate
audit findings
classify severity
correct BLOCKER/HIGH
revalidate
audit again
write evidence
report final status
```

GREEN is forbidden when:

```text
- any BLOCKER remains
- any HIGH remains without explicit phase allowance
- validation failed or was skipped
- a finding was hidden by suppression or weakened tooling
- remaining deviations are unclassified
- evidence does not support the claim
```

Phase allowance is allowed only with owner, risk, mitigation, expiry or review date, and evidence.

---

## 2B. Canonical Severity System

All findings use these severities:

| Severity | Meaning | Blocks GREEN | Blocks Commit |
|---|---|:---:|:---:|
| BLOCKER | Unsafe, false, exploitable, corrupting, or mandatory-governance failure. | YES | YES |
| HIGH | Significant correctness, safety, architecture, security, or evidence gap. | YES | YES unless explicitly phase-allowed |
| MEDIUM | Maintainability, documentation, local ownership, or test-quality issue. | NO if tracked | NO if tracked |
| LOW | Cleanup, wording, or minor consistency issue. | NO | NO |
| INFO | Observation with no required action. | NO | NO |

Escalate to BLOCKER when the issue threatens:

```text
security
data integrity
runtime safety
long-lived worker safety
truth/evidence integrity
public API compatibility
dependency graph correctness
rollback/recovery safety
```

Every finding must include:

```text
severity
finding
governance_source
where
why_it_matters
required_action
blocks_GREEN
```

---

## 2C. No Suppression Rule

Do not resolve findings by:

```text
disabling tests
weakening assertions
broadening ignores
adding baselines for new violations
hiding failures behind fallbacks
changing tests to match broken behavior
removing checks instead of fixing causes
marking issues pre-existing without classification
```

Allowed legacy baselines must live under `.agents/management/baselines/` with owner, reason, severity, remediation category, creation date, and review date.

New or changed-scope violations may not use the legacy baseline as an excuse.

---

## 2D. GREEN Justification

Any final status claiming GREEN or GREEN_WITH_ACCEPTED_YELLOW must explain:

```text
validation
gates
deviation_audit
corrections
remaining_deviations
suppression_check
exception_register_or_baseline
risk_assessment
evidence
why status is not YELLOW or RED
```

GREEN without this justification is UNPROVEN and must be treated as RED.

---

## 2E. Remaining Drift Classification

Every unresolved issue must include:

```text
severity
impact
owner
phase_allowance
mitigation
future_plan
evidence
```

Unclassified drift is a BLOCKER.

---

## 3. Rule Precedence

When governance sources disagree, use this order:

```text
1. AGENTS.md
2. .agents/GOVERNANCE_INDEX.md
3. .agents/how-to/README.md
4. .agents/how-to/00-how-to-reading-order.md
5. task-relevant .agents/how-to/**
6. task-relevant .agents/skills/**
7. ARCHITECTURE.md for project north-star architecture
8. .agents/.rules/**
9. task-local evidence
10. docs/**
11. README.md
12. old reviews, archives, backups, generated dumps
```

Old code and old reports are evidence, not governance.

Current governance is the target.

Current validation is the judge.

---

## 4. Project State Source Of Truth

Project state is resolved in this order:

```text
1. current git state
2. latest validation output
3. latest task-specific evidence
4. TODO.md
5. fix-this.md
6. CURRENT_TRUTH.md if present and not stale
7. EVIDENCE/EXECUTION.md only as a legacy/transitional source when current evidence activates it
8. .agents/management/ACTIVE.md if current
9. .agents/management/TODO.md if explicitly current
10. .agents/management/BUGS.md if current
11. learning and memory
12. older reports, plans, archives, dumps, backups
```

If sources disagree:

```text
current git state beats old reports
latest validation beats old assumptions
latest evidence beats old plans
TODO.md beats stale CURRENT_TRUTH.md
fix-this.md owns canonical remediation findings when TODO.md references it
```

If the contradiction cannot be reconciled, stop with HARD_BLOCKER and write evidence.

---

## 5. Execution Modes

### Standard Mode

Default for focused requests such as fix, inspect, review, refactor, generate, explain, or validate.

Required `.agents` SDLC load for every task:

```text
AGENTS.md
.agents/GOVERNANCE_INDEX.md
.agents/how-to/README.md
.agents/how-to/00-how-to-reading-order.md
.agents/how-to/**/*.md (complete governance reading order)
.agents/skills/**/SKILL.md (all skills loaded before routing)
.agents/management/** (active state, evidence, learning, memory, baselines)
ARCHITECTURE.md
latest relevant .agents/management/evidence/**
relevant source/test/docs files
```

After loading the complete `.agents` SDLC, apply the task-relevant subset. Do not skip the complete discovery step just because the task looks small.

### Harness-Full Mode

Triggered by:

```text
enterprise-grade
11++
full governance
uradi po pravilima .agents
maximum sweep
radi sto vise
nastavi sam
bez dodatnih promptova
autonomous backlog loop
```

Required discovery:

```bash
find .agents -maxdepth 5 -type f | sort
find .agents/skills -type f | sort 2>/dev/null || true
find .agents/how-to -type f | sort 2>/dev/null || true
find .agents/management -maxdepth 5 -type f | sort 2>/dev/null || true
```

Classify discovered files as mandatory, task-relevant, evidence, memory, learning, advisory, stale, or skipped with reason.

### Autonomous Backlog Mode

Use when the user asks to continue through backlog without repeated prompts.

Loop:

```text
resolve source of truth
select active highest-priority task
execute smallest safe slice
validate
audit
write evidence
review
continue on PARTIAL
stop only on HARD_BLOCKER
```

---

## 6. Mandatory Skill Routing

Every task must load every local skill contract before routing:

```bash
find .agents/skills -type f -name SKILL.md | sort
```

Every task must use at least:

```text
avax-source-of-truth-resolver
```

Task routing:

| Task type | Required skill/docs |
|---|---|
| production code, refactor, architecture | `avax-enterprise-codecraft` |
| component work | `avax-component-dogfooding` |
| runtime, hot path, cache, workers | `avax-runtime-performance-cache` |
| security-sensitive work | `avax-security-threat-model` |
| public API, PublicSurface, DSL, builder, config API | `avax-api-compatibility-contract` |
| tests or validation claims | `avax-test-evidence-quality` |
| failure-prone IO/runtime/security/persistence/cache/HTTP | `avax-observability-failure-semantics` |
| review | review skill if present and `.agents/how-to/verification/how-to-code-review.md` |
| self-explaining docs | `self-explaining-architecture` |
| autonomous backlog | `avax-autonomous-backlog-loop` |

If a required skill is missing, report `SKILL_MISSING` with impact: NO_IMPACT, YELLOW, or BLOCKER.

---

## 7. Required Preflight

Before editing, identify:

```text
active mode
active task
source-of-truth decision
forbidden scope
required skills
required how-to documents
relevant files
expected validation commands
expected evidence files
next allowed action
```

Before editing production code, also read:

```text
ARCHITECTURE.md
.agents/how-to/project/how-to-write-avax.md
task-relevant architecture/component/implementation/verification docs
relevant tests
latest relevant validation reports
```

No agent may edit if it cannot answer:

```text
What task is active?
What is forbidden?
Which rules apply?
What evidence proves this work?
What validation must run?
What branch/worktree owns this work?
```

---

## 8. SDLC Rule Map

Use this map instead of searching from memory:

| Concern | Canonical rule source |
|---|---|
| reading order | `.agents/how-to/00-how-to-reading-order.md` |
| governance index | `.agents/GOVERNANCE_INDEX.md` |
| AvaX project overlay | `.agents/how-to/project/how-to-write-avax.md` |
| git/commit/push | `.agents/how-to/project/how-to-git.md` |
| architecture | `.agents/how-to/architecture/how-to-architecture.md` |
| architecture decisions | `.agents/how-to/architecture/how-to-architecture-decisions.md` |
| runtime composition | `.agents/how-to/architecture/how-to-runtime-composition.md` |
| component shape | `.agents/how-to/components/how-to-design-components.md` |
| dogfooding | `.agents/how-to/components/how-to-dogfooding.md` |
| flow modeling | `.agents/how-to/modeling/how-to-model-flows.md` |
| clean code | `.agents/how-to/implementation/how-to-clean-code.md` |
| coding standards | `.agents/how-to/implementation/how-to-coding-standards.md` |
| code style | `.agents/how-to/implementation/how-to-code-style.md` |
| DI | `.agents/how-to/implementation/how-to-dependency-injection.md` |
| documentation | `.agents/how-to/documentation/how-to-document.md` |
| self-explaining architecture | `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md` |
| review | `.agents/how-to/verification/how-to-code-review.md` |
| tests | `.agents/how-to/verification/how-to-unit-test.md` |
| risk-based testing | `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md` |
| production readiness | `.agents/how-to/verification/how-to-production-readiness.md` |
| security | `.agents/how-to/verification/how-to-system-security.md` |
| performance/cache | `.agents/how-to/verification/how-to-system-performance.md` |
| data correctness | `.agents/how-to/verification/how-to-data-systems.md` |
| review packs | `.agents/how-to/verification/how-to-create-ai-code-review-packs.md` |

---

## 9. Documentation Shape Clarification

Global documentation, project overlays, local component docs, and evidence are different layers.

Use:

```text
docs/                               global documentation
.agents/how-to/project/             AvaX project overlay governance
components/<Area>/<Component>/docs/ local component documentation
.agents/management/evidence/**      proof and reports
```

`Docs/` as a generic technical bucket inside `System/` is forbidden by component shape rules.

`components/<Area>/<Component>/docs/` is allowed for local self-explaining architecture and does not violate the `Docs/` bucket prohibition.

---

## 10. Branch, Worktree, Commit, Push

Default policy:

```text
main is integration
task branches/worktrees own implementation slices
governance-only work may be committed on main only if main is clean and scope is documentation/agents/evidence only
production-code implementation does not happen directly on main
no force push
no self-push unless explicitly instructed
```

Before commit:

```bash
git status --short
git diff --stat
git diff --check
```

Do not stage:

```text
.codex
.qoder
.gigaide
avax.part-*
.agents/how-to/how-to.txt
temporary files
IDE files
screenshots
unrelated generated dumps
unrelated evidence
```

---

## 11. Validation Contract

Validation must match the scope.

Run full validation when work changes:

```text
architecture
component shape
namespaces/autoload
public surface
runtime behavior
security-sensitive behavior
performance-sensitive behavior
stage progress
production-readiness claims
```

Focused validation is allowed for narrow governance/doc/tooling work, but the final report must say:

```text
focused validation only
full validation not run
remaining risk
```

Canonical validation commands are defined by `.agents/how-to/verification/how-to-production-readiness.md` and current gate adoption evidence.

At minimum for governance work, run applicable:

```bash
git diff --check
php tooling/governance/check-governance-canonical-truth.php
php tooling/governance/check-governance-leakage.php
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-self-explaining-architecture.php --mode=changed
php tooling/testing/check-shallow-tests.php --mode=changed
```

Report missing commands as MISSING, not PASS.

---

## 12. Evidence Contract

Every important claim must point to current evidence.

Evidence should record:

```text
current branch
dirty status
sources loaded
skills used
how-to docs applied
source-of-truth decision
validation commands
validation outputs
findings
corrections
remaining risks
final decision
```

Write task evidence under:

```text
.agents/management/evidence/generated/<task-name>/
```

New agent evidence belongs in `.agents/management/evidence/**`.

Root `EVIDENCE/` is a legacy/transitional evidence surface. Do not write new task evidence there unless a legacy tool or explicit human instruction requires it.

If a file must be written under root `EVIDENCE/`, its filename must start with a timestamp:

```text
YYYY-MM-DD-HH-MM-SS-descriptive-name.md
```

Example:

```text
EVIDENCE/2026-05-26-14-30-00-runtime-doctor-validation.md
```

Evidence proves what happened. It does not become canonical governance unless explicitly promoted.

---

## 13. Final Output Contract

Every final response must include enough information for a reviewer to know what happened.

For normal tasks include:

```text
Stage
Status
Files changed
Validation commands
Validation summary
Evidence written
Remaining risks
Next allowed action
```

For larger tasks also include:

```text
Mode
Skills used
How-to files read
Source-of-truth decision
Reports updated
Final git status
Push readiness
```

Allowed statuses:

```text
GREEN
GREEN_WITH_ACCEPTED_YELLOW
YELLOW
RED
BLOCKED
PARTIAL
UNKNOWN
```

Do not use vague final status.

---

## 14. Final Law

AvaX builds AvaX with AvaX.

Keep the root contract thin.

Load the right `.agents` rules.

Prove claims with current validation.

Stop on HARD_BLOCKER.

Continue on PARTIAL when ownership is clear.
