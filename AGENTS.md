# AGENTS.md — Local Project Contract

Version: 2.0.0
Status: Normative / Local / Root Contract
Scope: `./**`

This file is the project-specific root contract for AvaX.

It defines how agents must read rules, resolve conflicts, identify the active stage, execute work, validate output, and
report results.

This file is not optional.
This file is not a suggestion.
This file is not a generic agent hint.

If an agent works in this repository, this file is the first local contract it must obey.

The reusable `.agents` project is mounted in `.agents/.rules/`.
The mounted copy provides reusable rules and protocols.
The local project rules in this repository decide how those reusable rules apply to AvaX.

Local AvaX governance wins for AvaX-specific naming, filesystem shape, stage lock, and framework architecture.

---

## 0. Core Operating Principle

AvaX is governed by evidence, not optimism.

No agent may mark work as complete, green, production-ready, secure, performant, or architecture-compliant unless
current validation evidence supports that claim.

The agent must always distinguish:

```text
Rule Precedence:
Which rules win when documents disagree.

Project State Source of Truth:
Which files describe the current project status, active stage, blockers, and next allowed action.
```

These are different things.

A project status file may describe current work.
It does not override governance rules.

A governance rule may define how work must be done.
It does not automatically prove current project status.

---

## 1. Operational Modes

## 1.1 Standard Mode

Standard Mode is the default.

Trigger:

```text
Any normal request such as:
- review this
- implement this
- refactor this
- fix this
- generate this
- inspect this
- continue the recovery
```

Scope:

```text
AGENTS.md
.agents/GOVERNANCE_INDEX.md if present
.agents/how-to/**
CURRENT_TRUTH.md
Code-Review-And-ToDo/EXECUTION.md
active task files
relevant source files
```

Rules:

```text
Follow local AvaX rules strictly.
Do not expand into full reusable harness protocols unless required by the task.
Do not ignore reusable mounted rules when they are directly relevant.
Do not let reusable generic rules override local AvaX filesystem and naming rules.
```

## 1.2 Harness-Full Mode

Harness-Full Mode is activated only by the explicit phrase:

```text
uradi po pravilima .agents
```

Scope:

```text
All local rules.
All `.agents/how-to/**` rules.
All mounted `.agents/.rules/**` rules.
All relevant skills, management files, review files, workflow files, and governance protocols.
```

Hard rule:

```text
Harness-Full Mode expands the rule set.
It does not weaken local AvaX rules.
```

Local AvaX `how-to` rules still win for:

```text
filesystem shape
component structure
naming
stage lock
PublicSurface rules
Flow vs Capability rules
forbidden folders
runtime-agnostic framework direction
long-lived worker safety
```

---

## 2. Order of Precedence

Agents MUST follow this rule precedence order.

This order decides which rule wins when documents disagree.

1. `AGENTS.md`
2. `.agents/GOVERNANCE_INDEX.md`
3. `.agents/how-to/**`
4. `Code-Review-And-ToDo/EXECUTION.md`, only for active stage and execution order
5. `CURRENT_TRUTH.md`, only for current project status
6. `.agents/.rules/AGENTS.md`
7. `.agents/.rules/governance/core/quality/quality-gates.md`
8. `.agents/.rules/governance/core/resolution/profile-resolution-algorithm.md`
9. `.agents/.rules/governance/profiles/**`
10. `.agents/.rules/governance/architecture/**`
11. `.agents/.rules/governance/security/**`
12. `.agents/.rules/governance/execution/policy/execution-policy.md`
13. `.agents/.rules/governance/execution/routing/prompt-to-governance-flow.md`
14. `.agents/.rules/governance/execution/hooks/hooks-policy.md`
15. `.agents/.rules/governance/execution/approvals/approval-policy.md`
16. `.agents/.rules/governance/core/flags/feature-flags.md`
17. `.agents/.rules/governance/standards/review/how-to-code-review.md`
18. `.agents/.rules/governance/standards/review/how-to-strict-review.md`
19. `.agents/.rules/governance/standards/coding/how-to-coding-standards.md`
20. `.agents/.rules/governance/standards/coding/naming-standard.md`
21. `.agents/.rules/governance/standards/documentation/how-to-document-flow.md`
22. `.agents/.rules/governance/standards/documentation/how-to-document.md`
23. `.agents/.rules/governance/standards/governance/governance-authoring-standard.md`
24. `.agents/.rules/governance/standards/governance/governance-evolution-policy.md`
25. `.agents/.rules/governance/delivery/release/release-and-rollback-policy.md`
26. `.agents/.rules/governance/intelligence/memory/memory-lifecycle.md`
27. `.agents/.rules/governance/skills/contract/skill-contract.md`
28. `.agents/.rules/governance/agents/roles/agent-roles.md`
29. `.agents/.rules/governance/delivery/workflows/workflow-pipelines.md`
30. `.agents/.rules/governance/intelligence/context/context-management.md`
31. `.agents/.rules/governance/intelligence/learning/continuous-learning.md`
32. `.agents/.rules/governance/intelligence/learning/instincts-policy.md`
33. `.agents/.rules/governance/integrations/platforms/platform-compatibility.md`
34. `.agents/.rules/governance/integrations/mcp/mcp-integration-policy.md`
35. `.agents/.rules/governance/execution/sandbox/sandbox-boundary-policy.md`
36. `.agents/.rules/governance/agents/orchestration/society-of-mind-pattern.md`
37. `.agents/.rules/governance/delivery/operations/**`
38. `.agents/skills/**`
39. `.agents/management/ACTIVE.md`
40. `.agents/management/TIMELINE.md`
41. `.agents/management/TODO.md`
42. `.agents/management/BUGS.md`
43. `.agents/review/REVIEWS.md`
44. `README.md`
45. `docs/**`
46. older review, archive, dump, and backup files

If a lower-priority file disagrees with a higher-priority file, the higher-priority file wins.

If an archive, backup, old review, `Framework.txt`, `Components.txt`, or `avax-backup.txt` disagrees with current
governance, it is evidence only.

Old code is evidence.
Current governance is the target.
Validation is the judge.

---

## 3. Project State Source of Truth

Project state must be read in this order.

This order describes current status, not rule priority.

1. `CURRENT_TRUTH.md`
2. `Code-Review-And-ToDo/EXECUTION.md`
3. `.agents/management/ACTIVE.md`
4. `.agents/management/TODO.md`
5. `.agents/management/BUGS.md`
6. `Code-Review-And-ToDo/master-plan/*.md`
7. `Code-Review-And-ToDo/recovery-reports/*.md`
8. latest validation reports
9. older review/archive files
10. backup dumps and historical snapshots

If project state files disagree:

```text
current validation output beats old reports
CURRENT_TRUTH.md beats old planning documents
EXECUTION.md beats generic TODO order for active work
new proof reports beat old assumptions
```

If there is no current validation evidence, the agent must say the status is unproven.

---

## 4. Required Preflight

Before editing code or documentation, every agent must identify:

```text
active mode
active stage
forbidden scope
relevant governance documents
relevant source files
expected validation commands
expected output artifact
next allowed action
```

The agent must not edit code if it cannot answer:

```text
What stage are we in?
What is forbidden right now?
Which how-to documents apply?
What validation proves this work?
```

For implementation, refactor, recovery, or review work, the agent must read:

```text
AGENTS.md
.agents/GOVERNANCE_INDEX.md if present
CURRENT_TRUTH.md
Code-Review-And-ToDo/EXECUTION.md if present
relevant .agents/how-to/*.md documents
relevant source files
relevant tests
latest relevant validation or recovery reports
```

---

## 5. Vision and Inspiration

AvaX is not a clone.

AvaX is a synthesis of modern software engineering excellence.

When designing, draw inspiration from:

```text
Laravel:
Developer experience, expressive public API, pragmatic ergonomics.

Symfony:
Decoupled components, explicit contracts, disciplined boundaries.

Spring Boot:
Enterprise-grade assembly, operational readiness, configuration discipline.

ASP.NET Core:
High-performance middleware pipeline, runtime clarity, host model.

Phoenix / Elixir:
Runtime clarity, supervision mindset, worker safety, fault awareness.

Go:
Simplicity, explicitness, small interfaces, low magic.
```

Inspiration is not imitation.

AvaX must keep its own architecture law:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

---

## 6. Fundamental Architecture Law

Strictly follow this hierarchy for everything created, moved, renamed, reviewed, or refactored:

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

This rule applies to:

```text
framework
components
labs
examples
tooling
tests
documentation structure where practical
agent-generated code
recovered code
```

The system must read like a story of ownership and behavior.

It must not read like a warehouse of technical categories.

---

## 7. Canonical Component Shape

Every production component must follow the canonical AvaX component shape:

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

Diagnostics are capabilities, not a root folder.

Tests normally live in the central test tree unless a component intentionally exports a test kit.

Documentation normally lives in `docs/`, with component-local README allowed only as a short ownership summary if local
governance allows it.

---

## 8. Strict Prohibitions

Do NOT use the following names as default directories, namespaces, or broad structural buckets:

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

The full forbidden-folder law is defined in:

```text
.agents/how-to/how-to-design-components.md
.agents/how-to/how-to-architecture.md
.agents/how-to/how-to-architecture-extension-with-ddd.md
.agents/how-to/how-to-use-advanced-architecture-patterns.md
.agents/how-to/how-to-system-security.md
.agents/how-to/how-to-system-performance.md
```

If a word from the forbidden list is truly the domain language of a specific component, the exception must be explicitly
justified through governance.

No automatic scaffolding.

No just-in-case folders.

No technical dumping grounds.

---

## 9. Concept Words Are Not Folder Names

Some words describe responsibilities, not filesystem names.

Examples:

```text
contract
adapter
diagnostic
test
documentation
manifest
command
query
policy
specification
security
performance
```

These words may describe what a component must provide.

They must not automatically become folders.

Correct:

```text
Capabilities/
  PublicApiCompatibility/
  S3ObjectStorage/
  CheckCacheHealth/
  DiagnoseCacheConfiguration/
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

The filesystem must still say flow or capability.

---

## 10. Local Naming Overrides Reusable Rules

Mounted reusable rules may use generic terms such as:

```text
contract
adapter
command
query
handler
service
```

For AvaX, these terms are interpreted as responsibilities or concepts, not default folder names.

Local AvaX naming rules override reusable mounted rules for:

```text
folder names
namespace names
component structure
PublicSurface placement
Flow vs Capability shape
DDD placement
advanced pattern placement
security and performance code placement
```

If a reusable rule says `Command`, AvaX should usually translate it to an exact flow name.

If a reusable rule says `Adapter`, AvaX should usually translate it to a concrete external boundary name.

If a reusable rule says `Contract`, AvaX should usually translate it to `PublicApiCompatibility`, schema, event promise,
interface, or another precise responsibility.

---

## 11. Use Case Translation Rule

AvaX does not use `UseCases/` as a default folder.

In AvaX vocabulary, a use case is represented as a `Flow`.

A flow owns one complete user, system, runtime, or platform action.

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

The folder must say what happens, not which architectural pattern is being used.

Use `Flow` when one action completes the story.

Use `Capability` when behavior is reusable across multiple flows.

Default to Flow first.

Extract to Capability only after reuse is honest.

---

## 12. Project-Specific Architecture Boundaries

AvaX uses these local boundaries:

```text
components/
  Reusable platform components with canonical System shape.

framework/System/PublicSurface/
  User-facing and framework-facing public entrypoints.

framework/System/Flows/
  Framework-level and multi-component orchestration flows.

framework/System/Capabilities/
  Internal framework capabilities and runtime machinery.

framework/System/Configuration/
  Framework assembly, registration, configuration, and bootstrapping.

framework/System/Foundation/
  Tiny neutral framework primitives.
```

Additional local meaning:

```text
labs/
  Experimental work only. Must not be production path unless promoted.

examples/
  Demonstrations and reference applications. Must not define production framework behavior.

tooling/
  Developer, governance, audit, validation, migration, recovery, and refactor tools.

tests/
  Canonical test tree.

docs/
  Canonical long-form documentation.

Code-Review-And-ToDo/
  Execution plans, recovery reports, validation evidence, audits, and temporary operational artifacts.
```

`components/` does not mean only pure logic.

It means reusable platform components with clear public boundaries, internal behavior, configuration, failure models,
tests, observability, diagnostics, and runtime safety where applicable.

---

## 13. Documentation Location Resolution

Documentation location is resolved as follows:

```text
docs/
  Canonical long-form documentation.

components/<Area>/<Component>/README.md
  Allowed only as a short component ownership summary when useful.

Code-Review-And-ToDo/
  Temporary recovery reports, validation evidence, audits, execution plans, and operational artifacts.

inline PHPDoc/comments
  Local intent, constraints, failure behavior, public API explanation, and junior-readable context.
```

Rules:

```text
Architecture documentation lives in docs/.
Component ownership summaries may live beside the component.
Recovery and validation evidence may live in Code-Review-And-ToDo/.
Random documentation must not be scattered elsewhere.
If component README and docs disagree, canonical docs win unless the README is explicitly newer and linked to a pending docs update.
```

Short version:

```text
README summarizes.
docs explain.
reports prove.
```

---

## 14. Applied Governance Stack

Delivery Kind:

```text
PHP Framework
```

Languages:

```text
php
markdown
shell
python for tooling only when useful
```

Frameworks or runtimes:

```text
avax
php-fpm
frankenphp
roadrunner
swoole
workerman
reactphp
amp
fiber-based runtimes
```

Applied coding profiles:

```text
.agents/.rules/governance/profiles/languages/php.md
```

Applied architecture profiles:

```text
screaming-architecture
vertical-slice
feature-first
fractal-flow-architecture
recursive-ownership
runtime-agnostic-framework
long-lived-worker-safe
component-platform-engine
public-surface-boundary
evidence-driven-governance
```

Security lanes required:

```text
.agents/.rules/governance/security/**
.agents/how-to/how-to-system-security.md
```

Operations lanes required:

```text
.agents/.rules/governance/delivery/operations/**
.agents/how-to/how-to-production-readiness.md
.agents/how-to/how-to-system-performance.md
```

Design and recovery lanes required:

```text
.agents/how-to/how-to-design-components.md
.agents/how-to/how-to-architecture-extension-with-ddd.md
.agents/how-to/how-to-use-advanced-architecture-patterns.md
```

---

## 15. Project Workspace

Project-specific agent workspace:

```text
.agents/business-logic/
.agents/language-specific/
.agents/management/
.agents/hooks/
.agents/review/
.agents/how-to/
```

Reusable mounted rules:

```text
.agents/.rules/
```

Project execution and evidence workspace:

```text
Code-Review-And-ToDo/
Code-Review-And-ToDo/recovery-reports/
Code-Review-And-ToDo/master-plan/
Code-Review-And-ToDo/templates/
```

---

## 16. Project-Specific Exceptions

Default:

```text
None.
```

Follow Screaming Architecture rules strictly.

Any exception must be documented in:

```text
.agents/GOVERNANCE_EXCEPTIONS.md
```

or another explicitly approved governance exception file.

Every exception must include:

```text
rule
path
reason
risk
owner
expiry
required cleanup
approval
validation
```

Temporary exceptions without expiry are forbidden.

---

## 17. Stage Lock

Only one stage may be active at a time.

No V2 implementation before V1 Kernel Green is proven.

No V3 implementation before V1 Kernel Green and V2 platform baseline are proven.

No V4 implementation before V1, V2, and V3 have sufficient proof according to current roadmap governance.

Planning may continue.

Architecture notes may continue.

Design documents may continue.

Implementation waits for the active stage to allow it.

Agents must not bypass stage lock by calling production code:

```text
preparation
scaffolding
harmless foundation
future-proofing
skeleton
placeholder
temporary
```

If it creates production behavior for a locked stage, it is forbidden.

---

## 18. V1 Kernel Green Definition

V1 Kernel Green means current evidence proves:

```text
component taxonomy is canonical
autoload is clean
namespaces match ownership
tests load and target canonical classes
static analysis is green or honestly baselined
runtime safety is proven
public surface does not leak internals
component completion rules are enforceable
security baseline is documented and tested
performance baseline is documented and measured where claimed
observability baseline exists
golden path app works through public APIs
production-readiness report agrees with validation
```

No V1 Green claim may be made without current validation output.

---

## 19. Required Validation

The canonical validation set is:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
```

When available, governance validation also includes:

```bash
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-stage-lock.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/security/check-security-governance.php
php tooling/performance/check-performance-governance.php
```

If a planned validation command does not exist yet, the agent must report it as:

```text
PLANNED / NOT IMPLEMENTED
```

not as pass.

---

## 20. Validation Selection Rule

Run the full canonical validation set when:

```text
work changes architecture
work changes component shape
work changes namespaces
work changes autoload
work changes public surface
work changes runtime behavior
work changes security-sensitive behavior
work changes performance-sensitive behavior
work claims stage progress
work claims production readiness
```

Run focused validation when:

```text
work is local
scope is narrow
full validation would be wasteful
focused proof is enough for local status
```

Focused validation must still be listed exactly.

If full validation was not run, final status must say:

```text
focused validation only
full validation not run
remaining risk
```

---

## 21. Agent Output Contract

Every agent execution must end with:

```text
Stage:
Status:
Files changed:
Validation commands:
Validation summary:
Remaining risks:
Next allowed action:
```

For larger tasks, also include:

```text
Governance documents read:
Rules applied:
Rules intentionally not applicable:
Evidence written:
Reports updated:
```

Status must be one of:

```text
GREEN
YELLOW
RED
BLOCKED
PARTIAL
UNKNOWN
```

Do not use vague status such as:

```text
looks good
probably fine
should work
almost done
```

---

## 22. Evidence Rule

Every important claim must point to evidence.

Examples:

```text
Tests pass because command X produced Y.
PHPStan is clean because command X produced empty output.
Runtime doctor is green because command X passed.
DatabaseBuilder assembly is proven because tests X pass.
Security redaction is proven because tests X pass.
Performance claim is proven because benchmark X shows Y.
```

If evidence is missing, say:

```text
not proven
```

Do not infer green status from intent.

---

## 23. Recovery Rule

When recovering from old sources:

```text
avax-backup.txt
Framework.txt
Components.txt
git history
old reviews
old archives
```

apply this rule:

```text
Old behavior is valuable.
Old structure is not automatically valuable.
```

Recovery must classify each candidate:

```text
source
old path
old namespace
behavior summary
target V1/V2/V3/V4 stage
target current component
target flow or capability
proof test needed
risk
decision
```

Do not restore old architecture mechanically.

Do not create skeletons to silence tools.

Do not promote old code without current tests.

---

## 24. AI Safety Rule for Code Changes

AI-generated code is untrusted until reviewed and validated.

Reject AI output that:

```text
creates generic Services/Managers/Helpers
creates forbidden folders
creates skeleton classes without behavior
adds public API without tests
adds security-sensitive behavior without tests
adds performance claims without evidence
logs secrets
bypasses authorization
uses raw SQL with user input
uses unsafe filesystem paths
adds hidden I/O
adds broad catch-and-ignore failures
changes stage scope silently
```

AI must follow active stage lock.

AI must produce evidence.

---

## 25. Security Rule

Security is a property of every boundary.

Agents must apply:

```text
.agents/how-to/how-to-system-security.md
```

when work touches:

```text
public surface
HTTP endpoint
authentication
authorization
session
tokens
secrets
configuration
database
cache
filesystem
queue
events
messages
external I/O
logs
telemetry
runtime state
admin/control plane
AI-generated code
```

Security-sensitive work must include negative tests where practical.

No secret may be logged, reported, dumped, or returned raw.

Authentication is not authorization.

Authorization must protect the object, not only the route.

---

## 26. Performance Rule

Performance claims require evidence.

Agents must apply:

```text
.agents/how-to/how-to-system-performance.md
```

when work touches:

```text
hot paths
runtime boot
HTTP kernel
router
container
database
cache
filesystem
queue
workers
events
messages
external I/O
serialization
view rendering
batch processing
long-lived runtimes
benchmark claims
memory behavior
```

No hidden I/O.

No unbounded public operation.

No retry without limit.

No external call without timeout.

No performance optimization may weaken security or correctness.

---

## 27. Review Rule

Any code review must apply:

```text
.agents/how-to/how-to-code-review.md
```

and must inventory every applicable:

```text
.agents/how-to/how-to-*.md
```

A review that does not check the governance set is incomplete.

The review must include:

```text
governance documents read
rules applied
compliance matrix
violations
severity
required action
final decision
```

---

## 28. Documentation Rule

Documentation must follow:

```text
.agents/how-to/how-to-document.md
```

and the documentation location resolution in this file.

Documentation is a design artifact.

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

Do not write documentation that merely restates code.

---

## 29. Coding Rule

PHP code must follow:

```text
.agents/how-to/how-to-coding-standards.md
.agents/how-to/how-to-code-style.md
.agents/how-to/how-to-clean-code.md
```

Project-specific PHP preferences include:

```text
PHP 8.5 style where useful and supported by the project
strict types
constructor promotion by default
named arguments where allowed
imports instead of fully qualified names
@throws tags where applicable
string|null instead of ?string
static anonymous functions where possible
typed class constants where applicable
readonly where useful
small public surface
explicit failure behavior
```

Do not use named arguments with APIs that explicitly forbid them, such as PHPUnit assertions marked with
`@no-named-arguments`.

---

## 30. Testing Rule

Tests must follow:

```text
.agents/how-to/how-to-unit-test.md
```

Tests must prove behavior, not implementation trivia.

Security and performance-sensitive behavior require negative and boundary tests where practical.

A refactor without regression protection is risky.

A public API without tests is not green.

---

## 31. Component Completion Rule

A component is not finished because it has folders.

A component is finished only when it has:

```text
public API
internal behavior
configuration
failure model
reliability policy where needed
observability
tests
runtime safety
diagnostics
examples
documentation
operator evidence
```

Component completion must follow:

```text
.agents/how-to/how-to-design-components.md
```

Partial components must be marked honestly.

Do not call a component platform-ready without proof.

---

## 32. Production Readiness Rule

Production readiness must follow:

```text
.agents/how-to/how-to-production-readiness.md
```

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

## 33. Final Law

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

Security protects every boundary.

Performance requires evidence.

Tests prove behavior.

Reports prove status.

Stage lock controls scope.

Old code is evidence.

Current governance is the target.

Validation is the judge.
