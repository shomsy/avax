Evo kompletan predlog za ceo `how-to-prompt-to-ai.md`, preuređen iz tvog drafta u stabilan interni governance/how-to dokument, a ne samo jednokratni prompt. Ugradio sam postojeći sadržaj i dodao strukturu za context loading, prompt types, cheap OOP rejection, evidence naming, review/commit gates i multi-agent rad. 

````md
# how-to-prompt-to-ai.md

## Status

- Status: INTERNAL_DRAFT
- Scope: Project AI-assisted execution prompts
- Applies to: Codex, Qoder, OpenCode, Antigravity, Copilot-style agents, and any AI executor working on this stack
- Owner: Project maintainer
- Purpose: Defines how AI agents must be prompted, constrained, reviewed, validated, and allowed to commit
- Non-goal: This file does not replace `AGENTS.md` or `.agents/how-to/*.md`; it explains how prompts must force agents to obey them

---

## 1. Purpose

The framework uses AI-assisted engineering, but AI speed is useful only when it preserves engineering quality.

This document defines the required standard for writing prompts to AI agents working on the project.

Every AI prompt must produce work that is:

- enterprise-grade
- governance-compliant
- evidence-backed
- test-proven
- security-aware
- runtime-safe
- architecturally honest
- advanced OOP, not cheap OOP
- suitable for Staff/Principal-level review

A fast low-quality patch is failure.

The framework must look like the work of a serious framework engineer.

If a change would embarrass the project in a Staff/Principal-level review, it must not be committed.

---

## 2. Authority and Precedence

AI prompts must respect this authority order:

1. `AGENTS.md`
2. local `.agents/AGENTS.md` if present
3. authoritative `.agents/how-to/*.md`
4. current `fix-this.md`
5. current evidence and reconciliation files
6. current truth/backlog files
7. assigned TODO prompt
8. agent local judgment

A prompt must never ask an agent to bypass project governance.

If the prompt conflicts with `AGENTS.md` or an authoritative `how-to-*.md`, the agent must stop and report the conflict.

---

## 3. Global Quality Bar

All implementation must be at the level of:

- Staff/Principal engineer review
- enterprise-grade PHP 8.5 style
- advanced OOP, not cheap OOP
- strict how-to governance
- behavior-first tests
- security/runtime safety
- clear ownership boundaries
- production-ready error handling
- maintainable long-term architecture

Passing tests is not enough.

A task is complete only when:

1. implementation/fix is done
2. focused validation passes
3. required governance gates pass
4. recursive governance review passes
5. evidence is written
6. truth/backlog files match reality when touched
7. staged files are intentional
8. no forbidden local/generated files are committed

---

## 4. Mandatory Agent Context Loading

Before touching code, every AI agent must load the complete local operating context.

This is a hard gate.

No context, no code.

### 4.1 Required Governance Context

The agent must inspect and use, when present:

- `AGENTS.md`
- `.agents/AGENTS.md`
- `.agents/how-to/*.md`
- `.agents/management/**`
- `.agents/management/evidence/**`
- `.agents/management/evidence/generated/**`
- `.agents/management/decisions/**`
- `.agents/management/learning/**`
- `.agents/management/memory/**`
- `.agents/skills/**`
- `.agents/prompts/**`
- `.agents/templates/**`
- `.agents/tooling/**`

### 4.2 Required Project Truth Context

The agent must inspect and use, when present:

- `fix-this.md`
- `CURRENT_TRUTH.md`
- `TODO.md`
- `EXECUTION.md`
- `CHANGELOG.md`
- release/current stage docs
- review-reconciliation evidence
- discipline-review evidence
- dual-review evidence
- security/runtime escalation evidence
- source finding coverage evidence

### 4.3 Skills Usage

If `.agents/skills/` exists, the agent must:

- list available skills
- identify which skills apply to the assigned TODO
- read relevant skill instructions before implementation
- use the relevant skill instructions during implementation
- record which skills were used in evidence

If no skills are applicable, the agent must state:

```text
skills used: NONE
````

### 4.4 Memory and Learning Usage

If memory or learning files exist, the agent must:

* read relevant memories/lessons before planning
* extract applicable warnings, naming rules, prior mistakes, accepted exceptions, and project preferences
* avoid repeating previously recorded mistakes
* cite the memory/learning file in evidence when it influences a decision

The agent must not re-decide a settled governance decision unless:

* the decision is contradicted by current code
* the decision is expired
* the decision is explicitly marked as YELLOW/temporary
* the assigned TODO requires revisiting it

### 4.5 Required Context Loading Evidence

Before implementation, the agent must write a preflight section in its evidence:

```md
## Agent Context Loaded

- AGENTS.md read: YES/NO
- .agents/AGENTS.md read: YES/NO/ABSENT
- how-to files discovered: <count>
- how-to files read: <count>
- skills discovered: <count>
- skills used: <list or NONE>
- memory/learning files discovered: <count>
- memory/learning files used: <list or NONE>
- project truth files read: <list>
- review evidence read: <list>
- assigned fix-this TODO: <TODO-ID>
- source clusters read: <cluster IDs>
- source finding IDs read: <IDs>
- applicable governance warnings: <short list>
- accepted YELLOW constraints: <short list>
- pre-existing dirty files: <classification>
```

If the agent cannot find a referenced skill, memory, or evidence file, it must report:

* expected path
* actual result
* impact on confidence
* whether the task can continue

The agent must not continue if required context for the assigned TODO is missing.

---

## 5. Prompt Types

Every AI prompt must clearly declare its type.

### 5.1 Review-Only Prompt

Allowed:

* inspect code
* classify findings
* write evidence
* create review reports
* create heatmaps
* create recommendations

Forbidden:

* production code changes
* test changes
* composer/autoload changes
* public API changes
* remediation
* opportunistic cleanup

### 5.2 Verification-Only Prompt

Allowed:

* confirm or reject findings
* search code paths
* run non-mutating validation
* add evidence
* classify false positives or confirmed risks

Forbidden:

* remediation
* production code changes
* test changes unless explicitly requested
* public API changes
* backlog rewrites unless explicitly requested

### 5.3 Remediation Prompt

Allowed:

* implement exactly one assigned TODO or one bounded slice
* add/update focused tests
* update focused evidence
* run validation
* commit only if gates pass

Forbidden:

* unrelated cleanup
* feature work
* mechanical renames
* broad architecture rewrite
* public API changes unless explicitly approved
* fixing findings outside scope

### 5.4 Integration Prompt

Allowed:

* merge approved branches sequentially
* resolve merge conflicts only inside approved scope
* rerun validation after every merge
* update integration evidence

Forbidden:

* new feature work
* new remediation
* opportunistic cleanup
* silent conflict resolution

### 5.5 Planning/Reconciliation Prompt

Allowed:

* merge review findings
* rewrite `fix-this.md` when explicitly requested
* create clusters and TODO models
* classify source findings

Forbidden:

* production code changes
* test changes
* remediation
* fake closure of unresolved findings

---

## 6. Prompt Completeness Checklist

Every execution prompt must include:

* mission
* project and mode
* prompt type
* assigned TODO ID
* assigned priority
* source clusters
* source finding IDs
* strict scope
* non-goals
* authoritative sources
* mandatory context loading requirement
* allowed files
* forbidden files
* expected design direction
* advanced OOP expectations
* required tests
* validation commands
* evidence path
* recursive governance review loop
* commit gate
* final output format
* allowed final decisions

A prompt missing these sections is not 11++.

---

## 7. Global Non-Goals for AI Work

Unless explicitly approved, AI agents must not do:

* feature work
* roadmap work
* broad cleanup
* unrelated refactor
* mechanical renames
* public API changes
* test weakening
* broad suppressions
* fake GREEN
* markdown-only closure
* quick fixes below how-to quality
* speculative redesign
* code movement without ownership proof
* “while I was here” improvements

---

## 8. Advanced OOP Requirements

Every code change must satisfy these principles.

### 8.1 Responsibility Ownership

Required:

* class name says responsibility
* method name says exact action
* folder says flow or capability
* public API says user intent
* internal class says internal responsibility

Forbidden:

* vague technical dumping grounds
* generic `Service`, `Manager`, `Helper`, `Util`, `Processor`, `Handler` unless explicitly allowed by project dictionary
* fake abstractions
* moving code into smaller classes without clearer ownership

### 8.2 Real Object Modeling

Required:

* objects protect invariants
* value objects are meaningful
* entities are used only where identity/lifecycle matters
* factories exist only when creation has rules
* result objects are typed and explicit
* domain/state transitions are visible

Forbidden:

* empty wrappers
* anemic value objects
* factories that only call `new`
* interfaces with no real variance
* arrays where typed result objects are expected
* polymorphism used only for appearance

### 8.3 Boundary Discipline

Required:

* PublicSurface is small and delegates
* Configuration assembles
* Flows execute
* Capabilities provide reusable ability
* Foundation stays tiny and neutral
* Runtime executes, not assembles
* DI/container ownership is explicit

Forbidden:

* PublicSurface owning real behavior
* Configuration executing runtime behavior
* Flows assembling object graphs
* Capabilities as dumping grounds
* Foundation containing domain/application behavior
* Runtime constructing configuration graphs

### 8.4 Dependency Discipline

Required:

* dependencies are explicit
* missing required dependencies fail at boot/compile/verify
* configuration owners assemble defaults
* runtime receives ready collaborators
* test fixtures do not hide production smell

Forbidden:

* hidden service locator
* runtime `class_exists() + new`
* dynamic `new` from payload without allowlist and interface check
* default dependency construction in business/runtime code
* fallback construction outside approved Configuration/Assembly zones
* nullable required services used as fallback switches

### 8.5 Security Discipline

Security findings are HIGH/BLOCKER by default.

Blocking areas include:

* unsafe deserialization
* authentication
* authorization
* sessions
* CSRF
* secrets
* logging sensitive data
* redirects
* file paths
* command execution
* dynamic class loading
* crypto
* tokens
* cookies
* serialization
* user-controlled class names

Required:

* threat explanation
* fail-closed behavior
* negative tests
* abuse tests
* no downgrade without evidence
* no accepted security debt without owner/expiry/mitigation

### 8.6 Runtime Discipline

Required:

* long-lived worker safety considered
* request scope explicit
* reset behavior automatic or tested
* hot-path work bounded
* runtime-specific APIs hidden behind adapters

Forbidden:

* static mutable request/user/session/security state
* hidden global state
* hot-path reflection without compiled metadata
* hot-path object graph assembly without justification
* request-scoped state in singletons/PublicSurface
* runtime-specific API leaks into core public APIs

### 8.7 Test Discipline

Required:

* tests prove behavior, not instantiation
* happy path
* failure path
* edge case
* regression case
* security negative/abuse tests where relevant
* runtime two-request/leak/reset tests where relevant
* precise assertions

Forbidden:

* `assertTrue(true)`
* smoke-only proof
* broad suppressions
* PHPStan ignores hiding weak tests
* tests that only verify implementation details
* deleting or weakening tests to pass

### 8.8 Error Handling Discipline

Required:

* exceptions carry context
* security fails closed
* failure modes are observable
* domain/runtime/programmer errors are distinguishable
* recovery is explicit

Forbidden:

* silent catch-and-continue unless intentional and tested
* swallowing `Throwable` without policy
* generic exceptions without context
* degraded fallback in security-sensitive code without evidence
* logging sensitive values

---

## 9. Cheap OOP Rejection Rule

Reject code that:

* creates wrapper classes without invariants
* creates factories without creation rules
* creates managers/services/helpers as dumping grounds
* moves code into smaller classes without clearer ownership
* uses interfaces only to look enterprise
* hides procedural code behind objects
* uses arrays where typed result objects are expected
* creates clever DSL that is not obvious
* makes public call sites verbose with nested value-object construction
* exposes mechanical conversion at call sites
* creates “architecture theater” without behavior proof

Good OOP is not more classes.

Good OOP is clearer ownership, safer invariants, better testability, and lower change cost.

---

## 10. Multi-Agent Rule

Each execution agent must receive exactly one bounded TODO or one verification-only batch.

Agents must not overlap touched files unless the coordinator explicitly approves.

If an agent discovers that the assigned TODO overlaps another active agent’s scope, the agent must stop and report:

* overlapping files
* overlapping TODO IDs
* risk
* recommended sequencing

Speed does not justify conflict.

---

## 11. Multi-Agent Role Model

### 11.1 Coordinator Agent

Responsibilities:

* owns global execution order
* assigns TODOs
* prevents file overlap
* reviews worktree status
* merges branches one by one
* ensures `fix-this.md` remains truthful
* tracks active agents
* never writes production code unless explicitly assigned

### 11.2 Execution Agent

Responsibilities:

* implements exactly one assigned TODO
* reads all relevant governance and evidence
* writes tests before or alongside changes
* runs required validation
* writes evidence
* performs recursive governance review
* commits only if clean

### 11.3 Review Agent

Responsibilities:

* reviews each execution agent diff
* checks how-to compliance
* checks advanced OOP quality
* checks security/runtime regressions
* checks test quality
* checks evidence truthfulness
* rejects fake GREEN

### 11.4 Integration Agent

Responsibilities:

* merges approved branches sequentially
* reruns full validation after each merge
* detects conflicts/regressions
* updates integration evidence

---

## 12. Per-Agent Task Template

Each execution agent must receive this information:

```md
# Agent Task

## Assignment

- Assigned TODO ID:
- Assigned priority:
- Assigned source clusters:
- Assigned source finding IDs:
- Root type:
- Unit/component:
- Assigned files:
- Allowed files:
- Forbidden files:

## Scope

- Strict scope:
- Non-goals:
- Expected design direction:
- Public API policy:
- Security/runtime policy:

## Required Context

- Governance files:
- Evidence files:
- Skills:
- Memory/learning:
- Prior decisions:
- Accepted YELLOW constraints:

## Required Tests

- Focused tests:
- Negative tests:
- Regression tests:
- Runtime/security tests:

## Required Validation

- Commands:
- Gates:
- PHPStan scope:
- PHPUnit scope:

## Required Evidence

- Evidence directory:
- Required evidence files:
- Required context loading proof:
- Required validation output:
- Required governance review:

## Commit

- Commit allowed: YES/NO
- Commit message:
- Commit gate:
- Final output format:
```

No agent should receive vague work like:

```text
clean up Auth
fix DI
improve PublicSurface
make this better
```

Every assigned task must be bounded.

---

## 13. Worktree Preflight for Every Agent

Before doing any work, every agent must run:

```bash
git status --short
git diff --stat
```

The agent must classify dirty files:

* CLEAN
* PRE_EXISTING_RELEVANT
* PRE_EXISTING_UNRELATED
* GENERATED_NOISE
* BLOCKER

The agent must stop if unrelated production/test/composer/autoload files are dirty.

The agent must not stage:

* project-specific part files (`*.part-*`)
* `.codex` files
* screenshots
* IDE/editor files
* local cache files
* unrelated generated evidence
* temporary `.txt` files
* aggregate dumps
* generated files outside assigned scope

---

## 14. Evidence Naming Rule

Every execution/remediation task must write evidence under:

```text
.agents/management/evidence/generated/<task-name>/
```

Recommended files:

```text
context-loaded.md
implementation-summary.md
validation-output.md
governance-review.md
final-decision.md
```

For security/runtime tasks, also include when relevant:

```text
threat-analysis.md
negative-test-proof.md
runtime-safety-proof.md
```

For verification-only tasks, include:

```text
verification-summary.md
confirmed-findings.md
false-positive-candidates.md
next-action.md
```

Rules:

* Do not write evidence into root.
* Do not create random timestamped files unless required.
* Do not commit dumps.
* Evidence must match code.
* Evidence must not claim FULL_GREEN when YELLOW remains.
* Evidence must include commands actually run.
* Evidence must include scanned item counts where gates report them.

---

## 15. Pre-Commit Governance Loop

Before commit, every execution agent must perform this recursive loop:

1. implement/fix
2. run focused tests
3. run required validation commands
4. run relevant governance gates
5. perform governance code review against all applicable how-to rules
6. classify findings:

   * BLOCKER
   * HIGH
   * MEDIUM
   * LOW
   * ACCEPTED_YELLOW
   * ACCEPTED_EXCEPTION
7. fix every BLOCKER/HIGH/MEDIUM
8. rerun validation
9. rerun governance review
10. write evidence
11. check staged files
12. commit only when clean

Fixing broken tests is implementation work.

After fixing a test, PHPStan issue, gate failure, or validation issue, the agent must run governance review again.

A test fix must not be committed only because the test now passes.

---

## 16. Commit Forbidden Conditions

Commit is forbidden if:

* tests fail
* PHPStan fails
* required gates fail
* BLOCKER/HIGH/MEDIUM findings remain
* security HIGH/BLOCKER remains
* public API changed without approval
* evidence is missing
* `fix-this.md` or evidence lies
* unrelated files are staged
* generated dumps/local files are staged
* validation scanned zero files
* agent cannot explain the behavior change
* context loading evidence is missing
* assigned TODO source findings were not read
* relevant skills/memory/learning files were ignored
* implementation contradicts existing decisions without decision record

---

## 17. Accepted YELLOW Rule

Accepted YELLOW is allowed only if it includes:

* affected files
* exact pattern
* severity
* why it is not HIGH/BLOCKER
* owner
* expiry/target batch
* risk
* mitigation
* evidence location
* next action

If YELLOW exists, final status must not be FULL_GREEN.

Allowed final statuses:

* FULL_GREEN
* GREEN_WITH_ACCEPTED_YELLOW_DEBT
* TODO_CLOSED
* TODO_PARTIAL_WITH_YELLOW
* TODO_BLOCKED
* VERIFICATION_COMPLETE
* VERIFICATION_INCOMPLETE
* REVIEW_COMPLETE
* REVIEW_BLOCKED

A task with unresolved accepted YELLOW must not report pure FULL_GREEN.

---

## 18. Context Compliance Gate

Commit is forbidden if:

* the agent did not load `.agents` governance
* applicable how-to files were not read
* assigned `fix-this.md` TODO was not read
* source clusters/findings were not read
* relevant skills were ignored
* relevant memory/learning files were ignored
* evidence does not say what context was loaded
* implementation contradicts existing project memory/learning without explicit decision record

If context loading is missing or fake, classify as HIGH.

If missing context caused incorrect implementation, classify as BLOCKER.

---

## 19. Review Agent Checklist

The review agent must verify:

* scope was obeyed
* no unrelated files changed
* code follows how-to rules
* OOP is real, not cosmetic
* names are boring, precise, predictive
* no `Service`/`Manager`/`Helper`/`Util` generic escape
* PublicSurface delegates
* Configuration owns assembly
* runtime does not assemble
* security behavior is fail-closed
* tests prove behavior
* evidence matches code
* final status is honest
* context loading was performed
* source findings were respected
* accepted YELLOW is documented
* no fake GREEN exists

The review agent must also check:

* Did the execution agent read the assigned `fix-this.md` TODO?
* Did it read source clusters and source finding IDs?
* Did it load all applicable how-to documents?
* Did it check `.agents/skills` if present?
* Did it check memory/learning files if present?
* Did it use previous evidence instead of rediscovering blindly?
* Did it preserve accepted decisions?
* Did it avoid repeating known mistakes?
* Did evidence record context loading truthfully?

---

## 20. Validation Rules

Every remediation prompt must define exact validation commands.

At minimum, use a focused set:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --filter "<FocusedArea>" --no-coverage
vendor/bin/phpstan analyse <focused-paths> --memory-limit=1G
```

Then add relevant gates:

```bash
php tooling/<governance-area>/check-<specific-rule>.php
php tooling/<governance-area>/check-<other-rule>.php
```

Use optional gates when available and relevant:

```bash
php tooling/<governance-area>/check-<optional-rule>.php
```

If a command is unavailable, the agent must document:

* requested command
* actual command if substituted
* reason
* status impact

Do not use `|| true` to hide failed gates.

A failed optional gate is still evidence.

A validation command that scans zero files is not proof.

---

## 21. Parallelization Rules

Parallel work is allowed only when:

* touched files do not overlap
* conceptual ownership does not conflict
* each agent has a bounded TODO
* each agent has its own branch/worktree
* one coordinator owns integration order
* one review agent validates each result

Recommended first parallel lanes:

* Agent A: TODO-001 serialized payload hardening
* Agent B: TODO-002 compiled container namespace emission
* Agent C: TODO-026 SQL/CSV verification-only
* Agent D: TODO-031 supplemental scan verification-only

Do not parallelize these until the first parallel round is reviewed and merged:

* TODO-003 CSRF/session authority
* TODO-004 dynamic class loading
* TODO-005 static security/runtime state
* TODO-006 framework public entrypoint composition
* TODO-007 AuthBuilder split

These are too architecture-sensitive for uncontrolled parallel execution.

---

## 22. Merge Order

Merge only one approved agent branch at a time.

After every merge:

1. run focused validation for affected area
2. run relevant governance gates
3. check `git status --short`
4. update integration evidence
5. confirm no unrelated files entered the merge
6. confirm `fix-this.md` remains truthful if touched

The integration agent must stop if a merge introduces:

* validation failure
* governance failure
* evidence mismatch
* public API drift
* unrelated file changes
* new generated noise
* unresolved conflict

---

## 23. Agent Output Required

Each execution agent must return:

1. Assigned TODO
2. Worktree preflight status
3. Context loaded summary
4. Files inspected
5. Files changed
6. Design decision summary
7. Advanced OOP quality notes
8. Tests added/updated
9. Validation output
10. Governance review findings table
11. Evidence path
12. Remaining YELLOW if any
13. Commit hash or reason no commit was made
14. Final decision:

    * TODO_CLOSED
    * TODO_PARTIAL_WITH_YELLOW
    * TODO_BLOCKED
15. One-sentence reason

A verification-only agent must return:

1. Assigned verification TODO
2. Worktree preflight status
3. Context loaded summary
4. Files inspected
5. Findings confirmed
6. Findings rejected
7. Findings needing deeper audit
8. Tests/searches/commands run
9. Evidence path
10. Recommended promotion/demotion
11. Commit hash or reason no commit was made
12. Final decision:

    * VERIFICATION_COMPLETE
    * VERIFICATION_INCOMPLETE
    * VERIFICATION_BLOCKED

---

## 24. Prompt Anti-Patterns

Do not write prompts that say:

```text
clean this up
make it enterprise grade
fix all issues
review and improve
refactor this component
make tests pass
finish the TODOs
do what makes sense
```

These prompts are too vague.

They create scope drift.

They produce fake quality.

Every prompt must say:

* exact assigned TODO
* exact scope
* exact non-goals
* exact source findings
* exact validation
* exact evidence
* exact commit rules

---

## 25. 11++ Prompt Skeleton

Use this skeleton for remediation prompts:

```text
MISSION: <Exact mission> to 11++

Project: <project-name>
Mode: HARNESS-FULL
Prompt Type: Remediation
Assigned TODO: <TODO-ID>
Priority: <P0/P1/P2/P3>

GOAL:
<One precise outcome.>

STRICT SCOPE:
<What may change.>

NON-GOALS:
<What must not change.>

AUTHORITATIVE SOURCES:
- AGENTS.md
- .agents/AGENTS.md if present
- relevant .agents/how-to/*.md
- fix-this.md
- source clusters
- source finding coverage
- relevant evidence

MANDATORY CONTEXT LOADING:
<Require Agent Context Loaded section.>

SOURCE FINDINGS:
- <SCR/HTD/SAI/DR/OLD-FIX IDs>

ALLOWED FILES:
- <paths>

FORBIDDEN FILES:
- <paths>

DESIGN DIRECTION:
<Expected architecture direction.>

ADVANCED OOP REQUIREMENTS:
<Specific OOP rules for this task.>

SECURITY/RUNTIME RULES:
<If relevant.>

TESTS REQUIRED:
<Focused tests.>

VALIDATION REQUIRED:
<Exact commands.>

EVIDENCE REQUIRED:
<Path and required files.>

RECURSIVE GOVERNANCE REVIEW:
<Required loop.>

COMMIT GATE:
<Exact commit rules.>

FINAL OUTPUT:
<Exact numbered output contract.>
```

---

## 26. 11++ Verification Prompt Skeleton

Use this skeleton for verification-only prompts:

```text
MISSION: <Exact verification mission> to 11++

Project: <project-name>
Mode: HARNESS-FULL
Prompt Type: Verification-only
Assigned TODO: <TODO-ID>

GOAL:
Confirm, reject, or classify specific findings without remediation.

STRICT SCOPE:
Verification only.

FORBIDDEN:
- no production code changes
- no test changes unless explicitly allowed
- no remediation
- no public API changes
- no fix-this rewrite

AUTHORITATIVE SOURCES:
- AGENTS.md
- .agents/how-to/*.md
- fix-this.md
- relevant review evidence
- source finding coverage

MANDATORY CONTEXT LOADING:
<Require Agent Context Loaded section.>

FINDINGS TO VERIFY:
- <IDs and files>

VERIFICATION STEPS:
- searches
- tests
- static checks
- evidence inspection

CLASSIFICATION:
- CONFIRMED
- PARTIAL
- NEEDS_DEEPER_AUDIT
- FALSE_POSITIVE_CANDIDATE
- ACCEPTED_EXCEPTION_CANDIDATE

EVIDENCE REQUIRED:
<Path and expected files.>

VALIDATION REQUIRED:
<Non-mutating commands.>

FINAL OUTPUT:
<Exact numbered output contract.>
```

---

## 27. 11++ Review Prompt Skeleton

Use this skeleton for review-only prompts:

```text
MISSION: <Exact review mission> to 11++

Project: <project-name>
Mode: HARNESS-FULL
Prompt Type: Review-only

GOAL:
Produce findings and evidence only.

STRICT SCOPE:
Review only.

FORBIDDEN:
- no production code changes
- no test changes
- no remediation
- no public API changes
- no fix-this rewrite unless explicitly requested

AUTHORITATIVE SOURCES:
- AGENTS.md
- .agents/how-to/*.md
- relevant code paths
- relevant evidence

MANDATORY CONTEXT LOADING:
<Require Agent Context Loaded section.>

REVIEW MODEL:
- strict code review
- how-to deviation audit
- security/runtime review
- test proof review
- evidence truth review

FINDING TEMPLATE:
- ID
- severity
- source
- file/path
- problem
- why it matters
- required action
- proof required

EVIDENCE REQUIRED:
<Path and expected files.>

VALIDATION REQUIRED:
<Non-mutating commands.>

FINAL OUTPUT:
<Exact numbered output contract.>
```

---

## 28. Final Rule

AI agents are allowed to accelerate the project.

They are not allowed to lower its engineering standard.

The framework must look like the work of a serious framework engineer.

No context, no code.

No evidence, no commit.

No tests, no trust.

No governance review, no GREEN.

No advanced OOP, no merge.

```
```
