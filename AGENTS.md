

````markdown
# AGENTS.md - AvaX Local Project Contract

Version: 3.0.0  
Status: Normative / Local / Root Contract  
Scope: `./**`  
Project: AvaX

This file is the root execution contract for AI agents working in this repository.

It is not optional.  
It is not advisory.  
It is not a generic hint file.  
It is the first local contract every agent must read and obey before planning, editing, reviewing, validating, committing, or reporting work in AvaX.

AvaX uses `.agents` as a project-local AI engineering operating system.

The AvaX Enterprise Remediation Skill is the mandatory bootloader.

The full `.agents` ecosystem is the operating system.

Current governance is the target.  
Current validation is the judge.  
Evidence is the proof.

---

## 0. Core Operating Principle

AvaX is governed by evidence, not optimism.

No agent may mark work as complete, green, production-ready, secure, performant, architecture-compliant, or review-ready unless current evidence proves it.

Every important claim must be backed by:

```text
current git state
current source files
current validation output
current evidence
current TODO/fix-this state
````

If evidence is missing, the correct status is:

```text
NOT_PROVEN
```

not:

```text
probably fine
looks good
should work
almost done
```

Agents must always distinguish between:

```text
Rule Precedence:
Which governance rules win when documents disagree.

Project State Source of Truth:
Which files describe the current project status, active backlog, blockers, completed work, and next allowed action.

Execution Routing:
Which skills, workflows, and operating loops must be loaded for the current task.
```

These are separate concerns.

A project status file describes current work.
It does not override governance.

A governance rule defines how work must be done.
It does not prove current project status.

A skill routes execution.
It does not override AGENTS.md or local how-to rules.

---

## 1. Non-Negotiable Laws

The following laws apply to every task.

```text
1. No implementation on dirty main.
2. No production-code implementation directly on main.
3. One task or bounded slice = one branch/worktree/evidence package/review candidate.
4. PublicSurface receives and delegates. It must not own runtime machinery.
5. Configuration/Assembly/Provider boundaries assemble object graphs.
6. Flows execute behavior.
7. Capabilities power reusable behavior.
8. Foundation stays small and primitive.
9. Components must dogfood existing AvaX capabilities where appropriate.
10. Runtime hot paths must avoid reflection, filesystem scans, config parsing, env reads, dynamic discovery, and object-graph assembly unless explicitly justified.
11. Security-sensitive behavior must fail closed and have negative tests.
12. Tests must prove behavior, not construction trivia.
13. PARTIAL is not a stop condition in autonomous work.
14. HARD_BLOCKER is the real stop condition.
15. Evidence is mandatory.
```

---

## 2. Operating Modes

### 2.1 Standard Mode

Standard Mode is the default mode for normal requests.

Triggered by requests such as:

```text
review this
implement this
refactor this
fix this
inspect this
continue this task
generate this
```

Standard Mode requires reading:

```text
AGENTS.md
relevant .agents/how-to/**
relevant .agents/skills/**
TODO.md
fix-this.md
latest relevant evidence
relevant source files
relevant tests
```

Standard Mode still obeys all AvaX local governance.

It may use focused context when the task is narrow.

It must not ignore matching skills or local rules.

### 2.2 Harness-Full Mode

Harness-Full Mode is activated by any of these signals:

```text
HARNESS-FULL
uradi po pravilima .agents
Use full .agents potential
Use the AvaX Enterprise Remediation Skill
maximum sweep
autonomous backlog loop
radi što više
nastavi sam
bez dodatnih promptova
zatvori listu
task po task
dok ima tokena
full governance
11++
enterprise-grade
```

Harness-Full Mode expands the rule set.

It requires full `.agents` discovery, classification, skill routing, evidence reading, source-of-truth resolution, and task-specific governance.

Harness-Full Mode does not weaken local AvaX rules.

### 2.3 Autonomous Backlog Mode

Autonomous Backlog Mode is used when the user asks the agent to continue through the backlog without repeated prompts.

It must use:

```text
avax-enterprise-remediation
avax-autonomous-backlog-loop
avax-source-of-truth-resolver
all task-relevant skills
all task-relevant how-to rules
all task-relevant evidence
```

In this mode:

```text
PARTIAL means continue with the next smallest safe slice.
PARTIAL_WITH_YELLOW means continue with the next smallest safe slice if ownership is clear.
TODO_CLOSED means move to the next highest-priority TODO.
HARD_BLOCKER means stop and write evidence.
```

The agent must not stop merely because one slice is complete.

---

## 3. Rule Precedence

This section decides which governance rule wins when documents disagree.

Rule precedence:

```text
1. AGENTS.md
2. .agents/GOVERNANCE_INDEX.md if present
3. .agents/how-to/**
4. project-local skill contracts in .agents/skills/**
5. mounted reusable rules in .agents/.rules/**
6. task-local evidence instructions
7. docs/**
8. README.md
9. old reviews, archives, backups, generated dumps
```

Local AvaX rules win for:

```text
filesystem shape
component structure
naming
PublicSurface rules
Flow vs Capability rules
forbidden folders
runtime-agnostic framework direction
long-lived worker safety
component dogfooding
AvaX-specific DI/runtime rules
AvaX-specific stage/backlog rules
```

Mounted reusable rules are valuable, but they do not override local AvaX architecture laws.

If a lower-priority rule disagrees with a higher-priority rule, the higher-priority rule wins.

If old code, old reviews, backups, dumps, or archived evidence disagree with current governance, they are evidence only.

Old code is evidence.
Current governance is the target.
Validation is the judge.

---

## 4. Project State Source of Truth

This section decides current project status, not governance priority.

State source-of-truth order:

```text
1. current git state
2. latest validation output
3. latest task-specific evidence
4. TODO.md
5. fix-this.md
6. CURRENT_TRUTH.md, only if not marked stale
7. EVIDENCE/EXECUTION.md, only for active execution flow
8. .agents/management/ACTIVE.md, if current
9. .agents/management/TODO.md, if explicitly marked current
10. .agents/management/BUGS.md, if current
11. learning and memory
12. older reports, reviews, plans, archives, dumps, backups
```

If project state files disagree:

```text
current git state beats old reports
latest validation beats old assumptions
latest evidence beats old planning
TODO.md beats stale CURRENT_TRUTH.md
fix-this.md defines canonical backlog items when TODO.md references it
CURRENT_TRUTH.md is advisory if it declares itself stale
old V4/V5/V5.9 notes are advisory unless current evidence activates them
```

If there is no current validation evidence, the agent must say:

```text
status not proven
```

### 4.1 Backlog File Roles

AvaX uses the following roles:

```text
fix-this.md
  Canonical remediation backlog and source finding map.

TODO.md
  Operational execution board for AI iterations, current lanes, priorities, and active remediation planning.

.agents/management/TODO.md
  Advisory or legacy unless explicitly marked as the current execution board.

CURRENT_TRUTH.md
  Current project truth only if fresh and not contradicted by newer evidence.

EVIDENCE/EXECUTION.md
  Active execution instructions only when current evidence says that flow is active.
```

No agent may select a TODO if it is already DONE, VERIFIED, VERIFIED_WITH_MAPPINGS, or integrated on main.

No agent may recommend a completed task as the next batch.

---

## 5. Execution Routing Precedence

Execution routing decides which workflow and skills must be loaded.

Routing order:

```text
1. avax-enterprise-remediation as mandatory bootloader
2. avax-source-of-truth-resolver for every task
3. avax-autonomous-backlog-loop for autonomous or multi-task execution
4. task-relevant specialized skills
5. task-relevant how-to rules
6. task-relevant evidence
7. task-specific validation commands
```

The bootloader starts the process.
It is not the whole operating system.

Agents must not use only the bootloader when more specific skills apply.

---

## 6. Full `.agents` Operating System Rule

The `.agents` folder is part of the execution contract.

Agents must discover, classify, and apply the full `.agents` ecosystem.

Required discovery in Harness-Full and Autonomous modes:

```bash
find .agents -maxdepth 5 -type f | sort
find .agents/skills -type f | sort 2>/dev/null || true
find .agents/how-to -type f | sort 2>/dev/null || true
find .agents/management -maxdepth 5 -type f | sort 2>/dev/null || true
```

Classify discovered files as:

```text
MANDATORY_BOOT
TASK_RELEVANT_SKILLS
TASK_RELEVANT_HOW_TO
PROJECT_MEMORY
PROJECT_LEARNING
CURRENT_TRUTH
EVIDENCE
ADVISORY_ONLY
STALE_OR_SKIP_WITH_REASON
```

Every task must write or update context-loaded evidence containing:

```text
current branch
current worktree
base commit
dirty status
AGENTS.md read
skills discovered
skills used
skills skipped with reason
how-to files discovered
how-to files applied
learning files discovered
learning files used
memory files discovered
memory files used
evidence files read
TODO.md section read
fix-this.md section read
source finding IDs
source conflicts found
final source-of-truth decision
```

---

## 7. Mandatory Skill Routing

### 7.1 Required Skills by Task Type

Every task:

```text
avax-enterprise-remediation
avax-source-of-truth-resolver
```

Autonomous backlog work:

```text
avax-autonomous-backlog-loop
```

Production-code implementation, refactor, architecture cleanup:

```text
avax-enterprise-codecraft
```

Component work or framework-internal reuse:

```text
avax-component-dogfooding
```

Runtime, hot path, cache, metadata, worker, performance-sensitive work:

```text
avax-runtime-performance-cache
```

Security-sensitive work:

```text
avax-security-threat-model
```

Public API, PublicSurface, facade, DSL, builder, configuration API work:

```text
avax-api-compatibility-contract
```

Tests or validation claims:

```text
avax-test-evidence-quality
```

Failure-prone work, runtime work, IO, security, persistence, queue, HTTP, cache:

```text
avax-observability-failure-semantics
```

Review work:

```text
review skill if present
how-to-code-review.md
all task-relevant how-to rules
```

Validation work:

```text
validation skill if present
task-specific validation commands
governance validation gates
```

Recovery work:

```text
recovery skill if present
Recovery Rule in this file
source-of-truth resolver
```

### 7.2 Skill Absence Rule

If a referenced skill is missing, the agent must report:

```text
SKILL_MISSING
```

and classify impact:

```text
NO_IMPACT
YELLOW
BLOCKER
```

If the missing skill is required for safe execution, the task must stop.

---

## 8. Autonomous Backlog Loop Rule

Autonomous work must not stop after one partial slice.

Loop:

```text
1. Resolve current source of truth.
2. Select highest-priority active TODO.
3. Create or reuse the correct branch/worktree.
4. Execute the smallest safe slice.
5. Validate.
6. Write evidence.
7. Commit the slice.
8. Self-review.
9. Merge to main only if review says MERGE_READY or MERGE_READY_WITH_YELLOW.
10. Run post-merge focused validation.
11. Update TODO.md/fix-this.md only when truth changed.
12. If TODO is still PARTIAL, continue the next slice.
13. If TODO is CLOSED, move to the next TODO.
14. Stop only on HARD_BLOCKER.
```

Soft continue conditions:

```text
focused validation GREEN
focused validation GREEN_WITH_ACCEPTED_YELLOW
remaining findings belong to the same TODO
ownership remains clear
next slice has bounded file scope
main is clean
evidence can be written
```

Hard stop conditions:

```text
dirty main cannot be classified
ownership boundary unclear
public API break required without evidence
new validation failure in changed files cannot be fixed in scope
security behavior cannot be proven fail-closed
source-of-truth contradiction cannot be reconciled
mandatory governance file missing
evidence cannot be written
branch/worktree contamination
human architecture decision required
context budget too low to safely continue
```

PARTIAL is not a stop condition.

---

## 9. Branch and Worktree Policy

### 9.1 Current Remediation Policy

For current remediation and backlog execution:

```text
main
  integration branch

task branches/worktrees
  implementation, remediation, analysis, review, and evidence work

origin/main
  remote integration truth after push
```

Rules:

```text
No production-code implementation directly on main.
Governance-only changes may be committed on main if main is clean and scope is documentation/agents/evidence only.
One active task or bounded slice uses one dedicated branch/worktree.
A multi-slice TODO may continue on the same task branch if the branch remains clean, scoped, and reviewed per slice.
Merge into main is sequential.
No self-push unless explicitly instructed.
No force push.
No squash unless explicitly justified.
No stale worktree reuse without inspection.
```

Branch naming:

```text
architecture/todo-XXX-short-name
security/todo-XXX-short-name
cleanup/todo-XXX-short-name
verify/todo-XXX-short-name
docs/todo-XXX-short-name
review/todo-XXX-short-name
```

Worktree naming:

```text
../avax-todo-XXX-short-name
```

Every task branch must include:

```text
context evidence
implementation or analysis evidence
validation evidence
governance review
final decision
```

### 9.2 Stage-Specific Branch Policy

Older stage-specific branch policies, including V4 branch policies, apply only when current evidence explicitly activates that stage workflow.

If current remediation evidence says task branches/worktrees from `main` are active, the current remediation policy wins for the active work.

Stage-specific policies remain useful, but must not override the current execution mode unless explicitly activated.

---

## 10. Required Preflight

Before editing code or documentation, every agent must identify:

```text
active mode
active task
active stage or remediation context
source-of-truth decision
forbidden scope
required skills
required how-to documents
relevant source files
expected validation commands
expected evidence files
next allowed action
```

For implementation, refactor, recovery, review, or merge work, the agent must read:

```text
AGENTS.md
relevant .agents/skills/**
relevant .agents/how-to/**
TODO.md
fix-this.md
latest relevant evidence
relevant source files
relevant tests
latest relevant validation reports
```

No agent may edit code if it cannot answer:

```text
What task is active?
What is forbidden?
Which rules apply?
What evidence proves this work?
What validation must run?
What branch/worktree owns this work?
```

---

## 11. AvaX Vision

AvaX is not another MVC framework.

AvaX is a runtime-agnostic modern PHP application platform and engineering system.

It targets:

```text
PHP-FPM
FrankenPHP
RoadRunner
Swoole
Workerman
ReactPHP
Amp
Fibers
CLI
tests
long-lived workers
future async/concurrent runtimes
```

AvaX is:

```text
flow-oriented
capability-oriented
runtime-neutral
governance-driven
component-based
evidence-first
developer-experience aware
production-readiness oriented
```

AvaX is inspired by:

```text
Laravel
  expressive public API and developer experience

Symfony
  decoupled components and explicit boundaries

Spring Boot
  enterprise assembly and configuration discipline

ASP.NET Core
  middleware/runtime clarity and performance discipline

Phoenix / Elixir
  supervision mindset, runtime clarity, fault awareness

Go
  simplicity, explicitness, small interfaces, low magic
```

Inspiration is not imitation.

AvaX keeps its own law:

```text
Folder says flow or capability.
Unit says responsibility.
Function says exact action.
```

---

## 12. Fundamental Architecture Law

Strictly follow this hierarchy:

```text
Folder = Flow or Capability
File/Class/Module = Responsibility
Method/Function = Exact Action
```

Equivalent form:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

The system must read like a story of ownership and behavior.

It must not read like a warehouse of technical categories.

This applies to:

```text
framework
components
tooling
tests
docs where practical
agents
evidence
generated code
recovered code
```

---

## 13. Canonical Component Shape

Every production component must follow the canonical AvaX shape:

```text
components/
  <Area>/
    <Component>/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Rules:

```text
System/ is required.
Capabilities/ is required for real components.
PublicSurface/ is conditional.
Flows/ is conditional.
Configuration/ is conditional.
Foundation/ is optional and must stay small.
```

No other top-level folders inside `System/` are allowed by default.

`InternalSystem` is a concept, not a folder.
`ExportedCapabilities` is a decision, not a folder.
Diagnostics are capabilities, not root folders.

Tests normally live in the central test tree unless a component intentionally exports a test kit.

Documentation normally lives in `docs/`, with component-local README allowed only as a short ownership summary.

---

## 14. Framework Shape

Framework code uses:

```text
framework/System/PublicSurface/
  User-facing and framework-facing public entrypoints.

framework/System/Flows/
  Framework-level and multi-component orchestration flows.

framework/System/Capabilities/
  Internal framework capabilities and runtime machinery.

framework/System/Configuration/
  Assembly, registration, configuration, providers, bootstrapping.

framework/System/Foundation/
  Tiny neutral primitives.
```

Rules:

```text
PublicSurface receives and delegates.
Flows execute.
Capabilities power.
Configuration assembles.
Foundation supports.
```

PublicSurface must stay small, stable, and thin.

Runtime/business flows must not assemble missing object graphs.

Configuration/Assembly/Provider owns construction.

---

## 15. Strict Naming and Folder Prohibitions

Do not use these as default directories, namespaces, or broad buckets:

```text
Services
Helpers
Utils
Common
Shared
Managers
Core
Support
Adapters
Contracts
Handlers
Processors
Commands
Queries
Domain
Entities
ValueObjects
Aggregates
Repositories
Events
CQRS
EventSourcing
Sagas
Policies
Specifications
Diagnostics
Tests
Docs
InternalSystem
ExportedCapabilities
```

This list is not exhaustive.

Concept words may describe responsibilities, but they must not automatically become folders.

Correct:

```text
Capabilities/
  PublicApiCompatibility/
  S3ObjectStorage/
  CheckCacheHealth/
  RegisterUser/
  ReadUserProfile/
```

Wrong:

```text
Contracts/
Adapters/
Diagnostics/
Tests/
Docs/
Commands/
Queries/
Security/
Performance/
```

If a forbidden term is truly domain language, the exception must be documented with:

```text
rule
path
reason
risk
owner
expiry
cleanup
validation
```

No just-in-case folders.
No dumping grounds.
No scaffolding theater.

---

## 16. Flow and Capability Rule

AvaX does not use `UseCases/` as a default folder.

A use case is represented as a Flow.

A Flow owns one complete user, system, runtime, or platform action.

Correct:

```text
System/
  Flows/
    RegisterUser/
      RegisterUser.php

    ChangePassword/
      ChangePassword.php

    RunMigration/
      RunMigration.php
```

Incorrect:

```text
Application/
  UseCases/
    RegisterUserUseCase.php

System/
  UseCases/
    ChangePasswordUseCase.php
```

Use `Flow` when one action completes the story.

Use `Capability` when behavior is reusable across multiple flows.

Default to Flow first.

Extract to Capability only after reuse is honest.

---

## 17. PublicSurface Rule

PublicSurface is a boundary, not a place for machinery.

Allowed:

```text
stable public API
DSL entrypoint
facade method
input normalization for public ergonomics
delegation to internal Flow/Capability/Configuration owner
```

Forbidden:

```text
runtime machinery
object graph assembly
fallback construction of required dependencies
service locator logic
business logic
security-sensitive decisions
cache ownership
worker state
filesystem scanning
reflection/class discovery
hidden mutable state
```

PublicSurface must be tested through public contract tests.

Any PublicSurface change must use:

```text
avax-api-compatibility-contract
avax-enterprise-codecraft
avax-test-evidence-quality
avax-source-of-truth-resolver
```

---

## 18. Dependency Injection and Assembly Rule

Required dependencies must fail during configuration, provider registration, compile, verify, or boot.

They must not fail deep inside runtime business code.

Assembly belongs in:

```text
Configuration/
Assembly/
Provider/
Builder
Boot/bootstrap boundary
```

Runtime flow code should execute, not assemble.

Forbidden:

```text
new MissingDependency() as fallback in runtime/business code
hidden service locator usage
global app() shortcuts inside component internals
runtime object graph construction in PublicSurface
runtime class discovery for dependency resolution
```

Allowed exceptions must be documented.

---

## 19. Component Dogfooding Rule

AvaX components must dogfood AvaX capabilities.

Before adding local logic, raw PHP, or ad-hoc infrastructure, the agent must ask:

```text
Does AvaX already have a component or capability for this concern?
Am I bypassing an existing first-party boundary?
Am I duplicating logic?
Is this allowed Foundation/adapter code?
Is dependency direction valid?
Can this create a circular dependency?
```

Components should reuse existing AvaX capabilities where architecturally appropriate:

```text
Filesystem
Clock / Time
Logger / Observability
Events
Cache
Configuration
Container / DI
Runtime lifecycle
Security / Cryptography / Redaction
HTTP abstractions
Validation
Serialization
Failure boundaries
Testing utilities
Metadata / Compilation
```

Allowed dependency paths:

```text
stable PublicSurface APIs
approved Capability APIs
Configuration/Assembly/Provider boundaries
documented internal component dependency
adapter boundary
Foundation primitive where appropriate
test utility in tests only
```

Forbidden:

```text
reaching into another component private internals
circular component dependency
hidden service locator calls
global app() shortcuts inside internals
raw filesystem IO where Filesystem should own it
raw env/config access where Configuration should own it
raw logging/echo/print where Logger/Observability should own it
raw cache arrays where Cache should own it
raw security primitives without Security/Cryptography boundary
duplicated serializers/parsers/validators
local mini-frameworks inside components
```

Every production-code task must include component dogfooding review when relevant.

---

## 20. Enterprise Codecraft Rule

Production code must be enterprise-grade by design, not merely test-passing.

Every production-code change must use `avax-enterprise-codecraft`.

Before code, the agent must write or update design evidence answering:

```text
What behavior is owned here?
Which class owns it?
Which invariant is protected?
Which system boundary is involved?
What lifecycle phase is affected?
What public API must remain stable?
What dependencies are injected?
What dependencies are assembled?
What failure modes exist?
What must fail closed?
What tests prove behavior?
```

Architecture-heavy changes require:

```text
high-level-design.md
low-level-design.md
ownership-boundary.md
dependency-boundary.md
```

Every meaningful implementation must assess:

```text
SOLID
high cohesion
low coupling
coupling direction
dependency inversion
runtime safety
security impact
performance impact
testability
observability
operability
backward compatibility
```

Forbidden classifications:

```text
TOO_MECHANICAL
FAKE_OOP
ARCHITECTURE_THEATER
NEEDS_REDESIGN
```

These block commit unless explicitly accepted as YELLOW with owner, risk, mitigation, and expiry.

---

## 21. Runtime Performance and Cache Discipline Rule

Runtime hot paths must avoid:

```text
reflection
filesystem scans
glob/recursive directory scans
config parsing
env reads
dynamic class discovery
class_exists as runtime discovery
service locator lookup
container compilation
repeated metadata parsing
repeated route compilation
repeated attribute scanning
unbounded array growth
mutable static per-request cache without reset lifecycle
hidden singleton state
```

Caching requires:

```text
owner
purpose
key shape
namespace
scope
lifecycle
invalidation
stale-data risk
memory bound
concurrency behavior
serialization format if any
observability/debugging
fallback/failure behavior
worker safety
```

Cache without invalidation/lifecycle is forbidden unless it is immutable deployment-time compiled data.

Long-lived worker state leaks are blocking findings.

Performance claims require proof:

```text
benchmark
before/after timing
memory comparison
proof work moved from request-time to boot/compile/warmup
static gate proving no hot-path reflection/filesystem scan
```

---

## 22. Security Rule

Security is a property of every boundary.

Security-sensitive work must use:

```text
avax-security-threat-model
.agents/how-to/verification/how-to-system-security.md
```

Security-sensitive areas include:

```text
public surface
HTTP
authentication
authorization
session
CSRF
tokens
secrets
configuration
database
cache
filesystem
queue
events
messages
external IO
logs
telemetry
runtime state
serialization
request signing
cookies
headers
admin/control plane
AI-generated code
```

Security work must answer:

```text
what asset is protected?
who is the attacker?
which inputs are untrusted?
what must fail closed?
where can injection/tamper/replay/leak/stale state happen?
what negative test proves the boundary?
```

No secret may be logged, dumped, returned raw, or exposed in evidence.

Authentication is not authorization.

Authorization must protect the object, not only the route.

---

## 23. API Compatibility Rule

Public API, PublicSurface, facade, DSL, builder, and configuration API work must use:

```text
avax-api-compatibility-contract
```

Every public API task must document:

```text
public API changed: YES/NO
backward compatible: YES/NO
migration needed: YES/NO
contract tests updated: YES/NO
old behavior preserved: YES/NO
deprecation path if changed
semantic version impact
examples still work
```

Public API break without explicit approval is a blocker.

---

## 24. Test Evidence Rule

Tests must prove behavior.

They must not merely instantiate classes or assert implementation trivia.

Every test-related task must use:

```text
avax-test-evidence-quality
.agents/how-to/verification/how-to-unit-test.md
```

Required proof where relevant:

```text
behavior test
negative test
regression test
public contract test
worker/runtime safety test
security boundary test
performance proof
```

Forbidden:

```text
changing tests to fit broken behavior
fake GREEN by running irrelevant tests
tests that only prove construction
no negative tests for security
no regression test for fixed bug
no contract test for public API change
```

---

## 25. Observability and Failure Semantics Rule

Meaningful runtime, IO, security, persistence, queue, cache, HTTP, DI, and boot changes must use:

```text
avax-observability-failure-semantics
```

Every failure-prone task must document:

```text
what can fail
where it should fail
exception type
message safety
sensitive data redaction
log event needed
metric/tracing needed
retryable or fatal
fail-open or fail-closed
worker state after failure
user-facing vs developer-facing message
debuggability
```

Failure mode unclear for a critical path is a blocker.

---

## 26. Documentation Rule

Documentation must follow:

```text
.agents/how-to/documentation/how-to-document.md
```

Documentation must explain:

```text
why this exists
where it belongs
what it owns
what it does not own
how to use it
how it fails
how it is tested
```

Documentation must not merely restate code.

Documentation location:

```text
docs/
  canonical long-form documentation

component README
  short ownership summary only

EVIDENCE/
  execution reports, validation reports, audits, operational proof

inline PHPDoc/comments
  local intent, constraints, failure behavior, public API explanation
```

README summarizes.
Docs explain.
Reports prove.

---

## 27. Required Validation

Canonical validation set:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

Governance validation when available:

```bash
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-stage-lock.php
php tooling/governance/check-root-evidence-hygiene.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-service-provider-coverage.php
php tooling/refactor/check-constructor-bloat.php
php tooling/security/check-security-governance.php
php tooling/performance/check-performance-governance.php
```

If a command does not exist, report:

```text
PLANNED / NOT IMPLEMENTED
```

Do not report missing commands as pass.

### 27.1 Full vs Focused Validation

Run full validation when work changes:

```text
architecture
component shape
namespaces
autoload
public surface
runtime behavior
security-sensitive behavior
performance-sensitive behavior
stage progress
production-readiness claims
```

Focused validation is allowed for narrow work.

If full validation was not run, final status must say:

```text
focused validation only
full validation not run
remaining risk
```

---

## 28. Evidence Rule

Every important claim must point to evidence.

Examples:

```text
Tests pass because command X produced Y.
PHPStan is clean because command X produced no errors.
Runtime doctor is green because command X passed.
Security redaction is proven because tests X passed.
Performance claim is proven because benchmark X shows Y.
Public API compatibility is proven because contract tests X passed.
```

Evidence packages for production-code tasks should include:

```text
context-loaded.md
source-of-truth-decision.md
design-before-code.md
high-level-design.md when architecture/system impact exists
low-level-design.md when non-trivial implementation exists
ownership-boundary.md
dependency-boundary.md when dependencies changed
component-dogfooding-review.md when relevant
api-compatibility.md when public API is touched
threat-analysis.md when security-sensitive
performance-design.md when performance-sensitive
cache-design.md when caching
cache-not-used.md when caching is rejected
runtime-safety-proof.md when runtime/worker state is touched
failure-semantics.md when failure-prone
test-proof.md
negative-test-proof.md when relevant
regression-proof.md when relevant
validation-output.md
governance-review.md
final-decision.md
```

Evidence must describe what was proven and what remains unproven.

---

## 29. Review Rule

Any code review must apply:

```text
.agents/how-to/verification/how-to-code-review.md
all task-relevant .agents/how-to/**/how-to-*.md
all task-relevant skills
```

A review must include:

```text
governance documents read
skills applied
rules applied
compliance matrix
violations
severity
security findings
architecture findings
runtime findings
test evidence findings
required action
final decision
```

Review decisions:

```text
MERGE_READY
MERGE_READY_WITH_YELLOW
MERGE_BLOCKED
NEEDS_REPAIR
NEEDS_DEEPER_AUDIT
```

Security HIGH/BLOCKER findings must be loud and blocking unless explicitly accepted with evidence.

---

## 30. Commit and Push Rule

Before commit:

```text
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

Commit only scoped files.

Commit message must describe the actual change.

Implementation commits must not be mixed with unrelated governance commits unless explicitly justified.

Push only when explicitly instructed by the user or current workflow says push is allowed.

---

## 31. Recovery Rule

When recovering from old sources:

```text
avax-backup.txt
Framework.txt
Components.txt
old branches
git history
old reviews
old archives
generated dumps
```

apply:

```text
Old behavior is valuable.
Old structure is not automatically valuable.
```

Recovery must classify:

```text
source
old path
old namespace
behavior summary
target component
target flow or capability
tests needed
risk
decision
```

Do not restore old architecture mechanically.

Do not create skeletons to silence tools.

Do not promote old code without current tests.

---

## 32. Stage Lock Rule

Only one implementation stage may be active at a time unless current evidence explicitly authorizes parallel remediation.

Stage lock applies to product/runtime feature stages.

Backlog remediation may proceed through task branches when current TODO.md/fix-this.md/evidence authorizes it.

Agents must not bypass stage lock by calling work:

```text
preparation
scaffolding
harmless foundation
future-proofing
temporary
placeholder
```

If it creates production behavior for a locked stage, it is forbidden.

---

## 33. Local Agent Workspace

The `.agents/` folder is the local AI engineering workspace.

Routing:

```text
.agents/skills/**
  executable playbooks and task routing

.agents/how-to/**
  local governance rules and quality laws

.agents/management/**
  active state, learning, memory, evidence, coordination

.agents/management/learning/**
  lessons to avoid repeated mistakes

.agents/management/memory/**
  durable project memory if present

.agents/review/**
  prior reviews and review state

.agents/business-logic/**
  project meaning and business language if present

.agents/.rules/**
  mounted reusable governance
```

If a matching skill exists, read it.

Memory and learning guide execution.
Validation proves execution.

---

## 34. Agent Output Contract

Every agent execution must end with:

```text
Stage:
Status:
Files changed:
Validation commands:
Validation summary:
Evidence written:
Remaining risks:
Next allowed action:
```

For larger tasks, also include:

```text
Mode:
Skills discovered:
Skills used:
Skills skipped with reason:
How-to files read:
Rules applied:
Rules intentionally not applicable:
Source-of-truth decision:
Reports updated:
Final git status:
Push readiness:
```

Allowed status values:

```text
GREEN
GREEN_WITH_ACCEPTED_YELLOW
YELLOW
RED
BLOCKED
PARTIAL
UNKNOWN
```

Autonomous loop final decisions:

```text
AUTONOMOUS_BACKLOG_LOOP_COMPLETE
AUTONOMOUS_BACKLOG_LOOP_PARTIAL
AUTONOMOUS_BACKLOG_LOOP_BLOCKED
```

Task final decisions:

```text
TODO_CLOSED
TODO_PARTIAL_WITH_YELLOW
TODO_BLOCKED
```

Do not use vague final status.

---

## 35. Production Readiness Rule

Production readiness requires agreement between:

```text
architecture
taxonomy
autoload
namespaces
tests
static analysis
runtime safety
public API stability
security baseline
performance baseline
observability
documentation
compatibility bridges
current validation evidence
```

No marketing language.

No optimism.

Only evidence.

---

## 36. Final Law

AvaX must stay simple, explicit, and strong.

The repository must not become a pile of components.

Components must not become a pile of folders.

Folders must say flow or capability.

Units must say responsibility.

Functions must say exact action.

Concept words are not folder names.

PublicSurface receives.

Flows execute.

Capabilities power.

Configuration assembles.

Foundation supports.

Components dogfood AvaX.

Security protects every boundary.

Performance requires evidence.

Caching requires lifecycle and invalidation.

Tests prove behavior.

Reports prove status.

Stage lock controls scope.

Old code is evidence.

Current governance is the target.

Validation is the judge.

PARTIAL means continue.

HARD_BLOCKER means stop.

AvaX builds AvaX with AvaX.
