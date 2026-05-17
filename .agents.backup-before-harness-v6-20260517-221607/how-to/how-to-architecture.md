# AvaX Architecture Governance

## Pragmatic Fractal Flow Architecture and Recursive Ownership Standard

## Status

**MANDATORY** - This document defines non-negotiable architecture rules for AvaX.

---

## 1. Status of This Document

This document is a governance standard.

It is not a style preference note.
It is not a collection of optional tips.
It is not a framework-specific guide.
It is not an academic purity exercise.

It is the structural source of truth for how systems are shaped, named, reviewed, extended, and refactored.

This governance applies to:

- applications
- services
- libraries
- SDKs
- packages
- plugins
- command-line tools
- workflow systems
- data systems
- platform code
- internal tools
- frontend systems
- backend systems
- monoliths
- modular monoliths
- distributed systems
- event-driven systems
- multi-repository and mono-repository systems

This governance is language-agnostic by design.

It must remain valid whether the implementation language is:

- PHP
- TypeScript
- JavaScript
- Go
- Java
- Kotlin
- C#
- Python
- Rust
- Swift
- Ruby
- Elixir
- C++
- or another ecosystem

The technical syntax may vary.
The architectural law must remain stable.

---

## 2. Purpose

The purpose of this governance is to enforce **highest-quality simplicity**.

The system must be:

- easy to read
- easy to explain
- easy to review
- easy to navigate
- easy to extend
- easy to change safely
- easy to refactor honestly
- resilient under growth
- explicit in ownership
- resistant to structural decay
- strong under operational pressure

This governance exists to prevent systems from degrading into:

- technical junk drawers
- framework-shaped warehouses
- layer mazes
- generic helper piles
- fake abstractions
- weak naming
- accidental complexity
- unclear ownership
- structural dishonesty

The goal is not sophistication.
The goal is not novelty.
The goal is not theoretical elegance at the cost of readability.

The goal is:

**simple systems that remain strong under real-world pressure.**

---

## 3. Foundational Philosophy

This governance follows these core architectural values:

- screaming architecture
- vertical slice thinking
- feature-first design
- explicit ownership
- strong locality
- honest modularity
- pragmatic design
- composition over inheritance
- simple public surfaces
- predictable naming
- safe change
- refactorability
- system readability as a first-class requirement

This governance rejects the idea that architecture is mostly about technical layering.

Architecture is primarily about:

- ownership
- responsibility
- reading order
- boundaries
- shape
- clarity
- change safety

A system should read like a story of behavior and responsibility.

It must not read like a storage room of technical categories.

---

## 4. Core Architectural Law

**Status:** MANDATORY  
**Severity:** BLOCKER  
**Scope:** All folder, file, class, function naming

The primary architectural law is:

**folder says flow or capability, unit says responsibility, function says exact action.**

This is the main rule.

Everything else is subordinate to it.

If a structural decision violates this rule, the decision is wrong unless there is an exceptional and explicitly
justified reason.

### Requirement

Folders **MUST** be named after flows or capabilities.

Files/classes **MUST** be named after their responsibility.

Functions/methods **MUST** describe exact action.

```text
// GOOD
Flows/
  RegisterUser/
    RegisterUser.php
  HandleIncomingHttp/
Capabilities/
  CacheReading/
    ReadRedisCache.php

// BAD
Services/
  UserService.php
  HttpHandler.php
Helpers/
  StringHelper.php
```

- Folder names describe flows or capabilities
- File names describe responsibility
- Function names describe exact action

### RED Criteria

- Folder named Services, Helpers, Utils, Common, Managers
- File named somethingService, somethingHelper, somethingUtil
- Function named process(), handle(), doSomething()

### 4.1 Meaning of "unit"

The term **unit** is intentionally neutral.

A unit may be:

- a file
- a class
- a module
- a package entry
- a script
- a component
- a service object
- a function group
- a command
- a handler
- a pipeline owner
- an orchestrator
- a state holder
- another valid ownership boundary

The governance does not depend on a specific programming language or framework doctrine.

What matters is not the technical form.

What matters is:

- ownership
- meaning
- clarity
- placement
- responsibility

---

## 5. Primary Reading Model

The structure must be readable in this order:

**flow -> slice -> unit -> functions**

At every level, reading deeper must increase precision, not confusion.

A reader must be able to ask:

1. What happens here?
2. What broader slice am I in?
3. What unit owns this slice?
4. What exact actions happen inside this unit?

This reading model must work:

- at the repository level
- at the system root level
- at the feature level
- at the flow level
- at the subfolder level
- at the single-unit level

If the reading path becomes weaker deeper in the tree, the structure is degrading.

---

## 6. Fractal Flow Architecture and Recursive Ownership

This governance follows a **fractal** reading rule.

The same law that applies to the whole system also applies recursively inside any folder where it improves clarity.

That means:

- the repository should read clearly
- the system root should read clearly
- each major slice should read clearly
- each subfolder should read clearly
- each deeper unit grouping should read clearly

This is called **Fractal Flow Architecture**.

It may also be called **Recursive Ownership Model**.

### 6.1 Fractal Rule

At every meaningful level of the tree:

- if there is a real sequence of steps, prefer a local flow owner or pipeline-shaped owner
- if there is no real sequence but there is one clear transformation, interpretation, or action, prefer a descriptive
  action owner
- if there is no sequence and no transformation, but a stable piece of owned state exists, prefer a state owner

The same reading law should apply at every level where doing so improves clarity.

### 6.2 Important Boundary

This must **not** become a mechanical rule.

Wrong interpretation:

- every folder must contain a pipeline
- every folder must contain a root owner class
- every folder must contain subfolders
- every folder must mirror the same pattern regardless of context

That is not the intent.

Correct interpretation:

- each folder should read like a small architectural story
- the shape of that story depends on the truth of the local responsibility
- recursive structure is allowed only where it adds clarity
- hierarchy exists to reduce noise, not to perform architecture theater

### 6.3 Recursive Ownership Law

Every meaningful folder should be understandable through these questions:

1. What local flow or capability exists here?
2. Who owns it?
3. Is there a real sequence of steps here?
4. If yes, what is the local flow owner or pipeline owner?
5. If not, what is the most honest action owner or state owner?

If these questions cannot be answered quickly, the folder shape is weak.

---

## 7. Pragmatism First

This governance is strict in principles and pragmatic in application.

That means:

- do not preserve weak structure just because it already exists
- do not redesign into unnecessary complexity because a pattern sounds advanced
- do not force doctrine when a simpler shape is clearer
- do not apply architecture mechanically
- do not optimize for purity over readability

The correct question is never:

- "Which architecture is most fashionable?"
- "Which framework doctrine says this is correct?"
- "Which pattern sounds more senior?"

The correct question is:

**What system shape makes ownership clearer, reading easier, and change safer?**

Pragmatism means:

- prefer the simplest structure that remains strong
- keep concerns local until sharing becomes honest
- accept small duplication before false abstraction
- adapt to language and ecosystem without surrendering clarity
- preserve interoperability when it matters
- add complexity only when it buys structural honesty

---

## 8. Architectural Goal

The target is:

**extreme simplicity with enterprise-grade quality**

This means the system must stay simple while being strong enough for:

- security
- correctness
- maintainability
- safe change
- scale
- operational pressure
- performance awareness
- resilience
- interoperability
- observability where justified
- team growth
- long-lived codebases
- evolving requirements

If something looks impressive but reads worse, it failed.

If something looks simple and survives real-world pressure, it succeeded.

---

## 9. Repo Root vs System Root

The architecture must distinguish between:

- **repo root**
- **system root**

This distinction is mandatory.

### 9.1 Repo Root

The repo root is the operational root of the repository.

It may contain:

- system root
- tests
- docs
- examples
- tooling
- CI/CD files
- package metadata
- governance files
- workspace files
- build files
- developer workflow files

Example:

```text
Project/
  src/
  tests/
  docs/
  examples/
  tooling/
  README.md
  GOVERNANCE.md
  package metadata
```

The repo root does not need to scream domain behavior.

Its job is to separate:

* production structure
* verification
* documentation
* examples
* tooling
* metadata
* operational concerns

### 9.2 System Root

The system root is the canonical root of the actual production structure.

Possible names include:

* `src/`
* `app/`
* `system/`
* `engine/`
* `product/`
* another justified name

The exact name is less important than the role.

Inside the system root, the real architectural law must apply.

That is where the system must scream:

* flows
* capabilities
* ownership
* responsibility
* exact behavior

### 9.3 Rule of Preference

Use the root name that makes system boundaries clearer.

Do not use a separate system root just because it is fashionable.

A separate system root is justified only if it makes the repository meaningfully easier to understand.

---

## 10. System Root Taxonomy

Inside the system root, only the following root slice types are allowed by default:

1. Flow slices
2. Capability slices
3. Configuration slices
4. Foundation slices
5. A small number of stable public surface units when required

If a root-level slice does not honestly fit one of these, it should not exist there.

---

## 11. Flow Slices

### 11.1 Definition

Flow slices describe end-to-end system behavior.

They answer:

**What does the system do?**

Examples:

* Login
* Register
* Checkout
* ReadCurrentUser
* PublishArticle
* ProcessRefund
* ChangePassword
* IncomingHttp
* BuildReport
* SyncCatalog
* HandleWebhook
* ExportInvoice
* TrainModel
* ProcessImport

### 11.2 Role

Flow slices are the main narrative of the system.

They should describe:

* user-facing movement
* business movement
* action completion
* system movement through time
* execution path
* meaningful sequence

### 11.3 Flow Owner Rule

Every flow slice must have one obvious root owner unit.

That unit may be:

* a pipeline owner
* an orchestrator
* a root action entry
* a root handler
* a flow owner
* a root facade
* another clearly justified owner

If the flow is sequential, the owner should gather the sequence.

If the flow is not fully sequential, the owner must still make ownership obvious.

### 11.4 Pipeline Preference

Where a real ordered sequence exists, a pipeline-shaped owner is preferred.

Examples:

* `HandleIncomingHttp`
* `IncomingRequest`
* `BuildResponse`
* `ProcessRefund`
* `HandleWebhook`

Pipeline preference is not a style rule.
It is a readability rule.

If a real sequence exists, the structure should say so.

### 11.5 Flow Locality Rule

A concern that belongs to one flow should remain there until there is strong proof it belongs elsewhere.

Examples:

* login throttling belongs in Login until it becomes truly shared
* refund reconciliation belongs in ProcessRefund
* import row validation belongs in ProcessImport
* response mapping for one endpoint belongs in that endpoint flow

Do not globalize too early.

---

## 12. Capability Slices

### 12.1 Definition

Capability slices describe shared abilities, boundaries, mechanisms, or reusable enablers that support multiple flows.

They answer:

**What does the system use to make flows work?**

Examples:

* Access
* Identity
* Payments
* Search
* Notifications
* Storage
* Routing
* Messaging
* Rendering
* Authorization
* Observability
* Network
* Serialization
* Parsing

### 12.2 Role

Capability slices are not technical leftovers.

They are shared abilities with honest cross-flow ownership.

They may contain:

* shared rules
* reusable domain mechanisms
* stable boundaries
* system-wide infrastructure with explicit meaning
* reusable operational abilities
* shared interpretation logic

### 12.3 Shared Last Rule

A capability should exist only when the concern is truly shared.

Extraction is justified when:

* more than one flow honestly depends on it
* keeping it local would now be misleading
* duplication has become structural rather than incidental
* the extracted thing is clearer than the duplication

Shared is not the default.
Shared is the last responsible option.

### 12.4 Anti-Junk Rule

A capability slice must never become:

* a leftovers bucket
* a technical pantry
* a place where unclear code is hidden
* a grab bag for "useful things"

If a folder collects unrelated pieces, it is not a capability.
It is a governance failure.

---

## 13. Configuration Slices

### 13.1 Definition

Configuration slices describe assembly, wiring, composition, bootstrap, and runtime setup.

They answer:

**How is the system assembled?**

Examples:

* Configuration
* Bootstrap
* Composition
* Wiring
* AssembleIncomingHttp
* AssembleRequest
* RegisterDependencies

### 13.2 Role

Configuration may own:

* dependency assembly
* startup wiring
* object graph creation
* composition policies
* runtime bootstrapping
* environment integration
* package assembly rules

Configuration must not become a hidden behavior layer.

Configuration assembles the system.
It does not become the system.

### 13.3 Builders Rule

Configuration slices may use a `Builders/` folder or namespace for explicit assembly classes.

* `Configuration/Builders` is allowed, but only for assembly builders.
* Runtime builders belong in Capabilities.
* ServiceProvider may delegate to Builders.
* Builders must be exact, tested, and must not become dumping grounds.

---

## 14. Foundation Slices

### 14.1 Definition

Foundation slices contain small, stable, neutral primitives that are too small to deserve their own capability slice.

They answer:

**What small, boring primitives does the system stand on?**

Examples:

```text
Foundation/
  Time/
    Clock
  Ids/
    IdGenerator
  Text/
    NonEmptyText
```

### 14.2 Role

Foundation is for:

* tiny primitives
* narrow low-level atoms
* stable value objects
* small neutral technical building blocks
* very small, reusable foundational pieces

### 14.3 Strict Rule

Foundation must never become:

* a generic helper bucket
* a domain logic dump
* a random utility folder
* a cross-cutting trash pile
* a substitute for unclear ownership

If something has real policy meaning, domain meaning, or flow meaning, it probably belongs elsewhere.

---

## 15. Public Surface Units

The system root may contain a small number of stable public surface units when necessary.

Examples:

* package entry
* root public API
* interface boundary
* facade entry
* export surface

These units must remain:

* small
* stable
* explicit
* intentionally public
* easy to understand

They must not become dumps for unrelated behavior.

---

## 16. Ownership Standard

Everything must have an owner.

Ownership must be visible from:

* naming
* location
* structural neighbors
* slice placement
* relationship to the broader flow or capability

If ownership is not obvious, the architecture is unfinished.

### 16.1 Ownership Questions

Every folder and every unit must answer:

1. Who owns this?
2. Why is it here?
3. Why is it not more honestly owned elsewhere?
4. What broader slice does it belong to?
5. What responsibility would break if this moved?

Weak answers mean weak placement.

### 16.2 Ownership Categories

In practice, units often fall into one of these categories:

#### Flow owner

Owns a meaningful sequence of system movement.

Examples:

* `HandleIncomingHttp`
* `IncomingRequest`
* `ProcessRefund`

#### Action owner

Owns one clear transformation, interpretation, or decision.

Examples:

* `ResolveClientAddress`
* `ParseBodyByContentType`
* `NormalizeProtocolVersion`
* `ReadRequestTarget`

#### State owner

Owns stable state and predictable access or transformation of that state.

Examples:

* `Request`
* `RequestHeaders`
* `RequestedInputs`
* `SessionState`

This distinction is extremely useful and should be applied pragmatically.

---

## 17. Hierarchy Rules

Deeper structure is allowed only when it improves clarity.

A deeper hierarchy is justified when it:

* reflects a real subflow
* reflects a real sub-capability
* reduces noise
* improves scanning
* protects ownership
* prevents oversized flat directories
* improves understanding of local relationships

A deeper hierarchy is not justified when it:

* hides weak naming
* creates cosmetic nesting
* introduces hallway folders
* duplicates concepts already expressed elsewhere
* exists only to make the tree look "clean"
* adds ceremony without explanatory power

Every additional level must earn its existence.

If the tree becomes deeper but not clearer, the structure has worsened.

---

## 18. Locality Before Reuse

This governance prefers **local truth before shared abstraction**.

That means:

* keep behavior near its honest owner
* accept small duplication before speculative extraction
* extract only when the extracted thing becomes clearer than the repeated local implementations
* resist centralizing code just because it might be reused later

Premature shared structure creates fake order.

Real clarity comes from honest locality.

---

## 19. Flow, Action, and State Decision Rule

When shaping any folder or unit, use the following decision rule:

### Step 1: Is there a real sequence of steps?

If yes, create or preserve a **flow owner** or **pipeline-shaped owner**.

### Step 2: If not, is there one clear transformation, interpretation, or decision?

If yes, create a **descriptive action owner**.

### Step 3: If not, is there stable state with a clear owner?

If yes, create a **state owner**.

### Step 4: If none of the above feels honest

The structure is still wrong.
Re-think the ownership model.

This decision rule applies:

* at system level
* at feature level
* at folder level
* inside subfolders
* recursively where useful

---

## 20. Recursive Folder Rule

Every folder should be readable as a small architectural story.

For each folder, ask:

1. What local flow or capability exists here?
2. Who owns that local responsibility?
3. Is there a meaningful sequence here?
4. If yes, where is the local pipeline owner?
5. If not, which action owner or state owner is most honest?

This rule applies recursively where it strengthens readability.

It is a governance rule, not a mechanical template.

---

## 21. Language-Agnostic Rule

This governance must remain valid across languages and paradigms.

It must not depend on:

* class-heavy OOP only
* functional purity only
* one framework’s doctrine
* one packaging convention
* one build tool
* one runtime model

The system may be implemented through:

* classes
* modules
* packages
* namespaces
* folders
* scripts
* functions
* traits
* structs
* interfaces
* protocols
* services
* components

The implementation style may differ.

The ownership law may not.

---

## 22. Ecosystem Rule

Strong ecosystem conventions may be respected only if they do not weaken:

* ownership
* clarity
* screaming readability
* structural honesty
* scanning simplicity
* mental load

Conventions are not automatically correct.

Custom structure is not automatically superior.

The rule is:

**prefer the shape that reduces noise and makes ownership clearer.**

---

## 23. Design Quality Constraints

This governance supports strong engineering discipline, including:

* strong cohesion
* low coupling
* narrow interfaces
* explicit boundaries
* honest abstraction
* composition over inheritance
* locality before indirection
* maintainable low-level design
* safe extension points
* controlled change surface
* law of demeter awareness
* simple contracts
* refactorable internals
* boring public surfaces

These are design constraints, not excuses for complexity.

---

## 24. System Quality Constraints

The architecture must be capable of supporting:

* security by design
* clear authentication boundaries
* clear authorization boundaries
* data protection
* vulnerability awareness
* performance awareness
* scalability
* reliability
* observability where justified
* maintainability
* portability
* compatibility
* interoperability
* cost awareness
* resilience
* operational clarity
* safe deployment
* testability

Concerns must live where they are honestly owned.

Examples:

* rate limiting belongs near the flow or boundary that owns it
* proxy trust rules belong near network interpretation
* composition belongs in configuration
* tiny primitives belong in foundation
* business policy does not belong in foundation
* security interpretation does not belong in random helpers

---

## 25. Forbidden Structural Patterns

The following structural failures are forbidden unless explicitly justified and documented:

* tight coupling
* ownership ambiguity
* fake abstraction
* insufficient abstraction
* abstraction without proof
* premature centralization
* over-engineering
* hierarchy without explanatory value
* duplicated capabilities
* technical junk drawers
* parallel naming for the same concept
* hidden orchestration
* unclear entry points
* bag-of-methods classes
* framework-shaped directories with weak domain meaning
* "misc" folders
* convenience buckets

---

## 26. Forbidden Generic Names

The following names are forbidden as default buckets:

* Services
* Helpers
* Utils
* Utility
* Common
* Misc
* Managers
* Stuff
* Shared
* Base
* Core
* General
* InternalHelpers
* Generic
* Support
* Toolbox

These names hide responsibility instead of clarifying it.

They may exist only if they represent a truly precise, unavoidable, and well-documented concept.

That case is rare.

---

## 27. Naming Standard

Naming must be:

* simple
* banal
* predictable
* descriptive
* child-explainable
* low-noise
* responsibility-oriented

A name should make it obvious, before opening the code:

* what it is
* why it exists
* what it owns
* when it is used
* what it does

### 27.1 Naming Law

* folder says flow or capability
* unit says responsibility
* function says exact action

### 27.2 Preferred Naming by Unit Type

#### Flow owners

Prefer action phrases that describe meaningful system movement.

Examples:

* `HandleIncomingHttp`
* `IncomingRequest`
* `BuildResponse`
* `ProcessRefund`
* `HandleWebhook`

#### Action owners

Prefer verb + object or another equally clear action form.

Examples:

* `ResolveClientAddress`
* `ReadRequestTarget`
* `NormalizeHeaders`
* `ParseBodyByContentType`

#### State owners

Prefer clear nouns that describe the owned concept.

Examples:

* `Request`
* `RequestHeaders`
* `RequestedInputs`
* `RequestBody`
* `SessionState`

### 27.3 One Concept, One Name

A concept must have one name across the system.

Do not mix synonyms for the same thing.

Bad examples:

* `AuthenticateUser` and `RequireAuthentication` if they mean the same thing
* `CurrentUser` and `ReadCurrentUser` if they mean the same thing
* `ResolveIp` and `ReadClientAddress` if they mean the same thing
* `Manager`, `Service`, and `Handler` for one identical concept

Choose one name.
Delete the others.

### 27.4 Canonical Term Registry Rule

One concept must have one canonical name. If the same concept appears under multiple names, review MUST choose one
canonical term and mark the others as aliases, deprecated terms, or wrong terms.

The canonical registry is at:

```text
docs/governance/canonical-terms.md
```

Check the registry before introducing or accepting new terminology. All term decisions MUST be added to the registry.

**Status:** MANDATORY  
**Severity:** HIGH (escalates to BLOCKER — see below)

### Severity Escalation

Default severity is HIGH.

Escalates to **BLOCKER** when naming drift affects any of the following:

```text
PublicSurface
DI/container
Response layer
Events
Runtime
Boot DSL
FailureBoundary
Database lifecycle
security-sensitive APIs
public compatibility
```

Examples that MUST be BLOCKER:

```text
Response / Responses / CreateHttpResponse confusion in active code
EventEmitter / EventDispatcher / EventBus confusion in active runtime
Runtime / Kernel / Application confusion in public boot API
ServiceProvider / Provider / ComponentDefinition confusion in DI assembly
```

### 27.5 Function Naming Rule

Function names must describe exact action.

Prefer:

* `read`
* `build`
* `resolve`
* `parse`
* `normalize`
* `drop`
* `append`
* `has`
* `start`
* `stop`
* `execute`

Avoid vague verbs like:

* `process`
* `handle`
* `run`
* `do`
* `perform`

unless the surrounding unit already makes the meaning obvious and specific.

---

## 28. Flow vs Capability Clarification

A flow slice is not the same as a capability slice.

A flow says:

* what happens
* what sequence executes
* what movement occurs
* what action is completed

A capability says:

* what the system uses repeatedly
* what shared ability exists
* what stable boundary supports multiple flows
* what reusable enabler exists outside one single flow

Simple rule:

* flows say what the system does
* capabilities say what the system uses to do it
* configuration says how the system is assembled
* foundation says what tiny neutral primitives support the base

---

## 29. Review Rule

Every proposed structural change must answer these questions clearly:

1. What does this folder say?
2. What does this unit own?
3. Why is it not owned more honestly at a lower level?
4. Does this reduce noise or only move it?
5. Is this a real capability or just a technical bucket?
6. Is the name obvious without opening the code?
7. Does this create parallel naming for the same concept?
8. Does this make the reading path clearer?
9. Is this local truth or speculative reuse?
10. Would a new team member understand this quickly?
11. Is there a simpler structure with the same clarity?
12. Is this hierarchy earning its existence?

Weak answers mean the proposal is weak.

---

## 30. Placement Decision Framework

When deciding where something belongs, apply this sequence:

### Step 1

Does it belong to one flow only?

If yes, keep it in that flow.

### Step 2

Is it a truly shared ability used across flows?

If yes, consider a capability slice.

### Step 3

Is it only about assembly, wiring, or bootstrap?

If yes, place it in configuration.

### Step 4

Is it a tiny stable primitive?

If yes, place it in foundation.

### Step 5

If none of the above fits honestly, the model is still wrong.

Re-think the structure instead of inventing a bucket.

---

## 31. Public API and Internal Ownership

Public interoperability surfaces may require stable external contracts.

That is acceptable.

Internal ownership structure does not need to mirror external surface terminology exactly, as long as:

* the mapping is explicit
* ownership remains clear
* public interoperability is preserved
* internal clarity is not sacrificed

Example:
A language or standard may require one public interface shape, while the internal ownership model remains clearer and
more expressive.

This is acceptable when done intentionally.

---

## 32. Interoperability and Standards

Standards compatibility is valuable when it provides:

* ecosystem interoperability
* lower integration cost
* predictable external contracts
* safer adoption
* easier replacement

But standards compatibility must not become an excuse for internal chaos.

Preferred rule:

* preserve required external compatibility
* keep internal ownership honest
* bridge between them explicitly

Do not let external interfaces dictate internal disorder.

---

## 33. Testing Governance

All production change must be test-governed.

This governance follows strict incremental TDD wherever practical.

### 33.1 TDD Default Rule

For each small behavior:

1. write a failing test first
2. confirm it fails for the correct reason
3. add the minimum production code
4. run the tests
5. refactor only while all tests remain green
6. repeat in small increments

### 33.2 No Skipping Rule

Do not skip the order:

**failing -> passing -> refactor**

### 33.3 Bug Fix Rule

For a bug fix:

1. write a regression test that reproduces the bug
2. make it fail
3. implement the smallest safe fix
4. refactor only with tests green

### 33.4 Refactor Rule

For a refactor:

1. write characterization tests for existing behavior
2. make current behavior explicit
3. refactor behind those tests
4. add better tests where behavior should be tightened later

### 33.5 Specification Rule

When requirements are unclear:

1. write tests as behavior specification
2. review expectations
3. implement only after behavior is made explicit

### 33.6 Increment Size Rule

Do not build the whole system in one step.

Each increment should implement one behavior or one very small slice of behavior.

### 33.7 Test Preference Rule

Prefer:

* unit tests first
* integration tests second
* end-to-end tests only where the system boundary requires them

### 33.8 Security Testing Rule

Security-relevant behavior must include:

* edge case tests
* abuse case tests
* invalid input tests
* trust boundary tests
* failure mode tests

### 33.9 Required Reporting Per Increment

For every TDD increment, the engineering output should show:

* what test was added
* why it fails
* what minimum code was added
* whether all tests pass after the change

---

## 34. Refactoring Governance

Refactoring is encouraged.
Rewrite is allowed.
Big-bang chaos is not.

### 34.1 Refactor Goal

Refactoring must improve one or more of these:

* ownership
* naming
* change safety
* testability
* readability
* local clarity
* boundary clarity
* operational safety

### 34.2 Rewrite Rule

A deeper rewrite is justified when the existing structure is fundamentally dishonest or too costly to evolve safely.

A rewrite should still be protected by:

* characterization tests
* migration strategy
* staged rollout where needed
* explicit deletion of obsolete concepts

### 34.3 Removal Rule

When better ownership is introduced, obsolete structural patterns should be actively removed.

Do not keep dead structures around to avoid short-term discomfort.

Delete:

* obsolete layers
* fake abstractions
* deprecated synonyms
* duplicate owners
* dead paths
* compatibility layers after their migration window ends

---

## 35. Security Governance

Security is not a separate phase.
It is an ownership and boundary responsibility.

Security concerns should live where they are honestly owned:

* boundary parsing near boundary ownership
* authorization near access ownership
* trust policies near network or identity ownership
* secret handling near secure configuration ownership
* input validation near the responsible boundary or flow

Security must not be hidden in random helper code.

Systems must be designed to support:

* clear trust boundaries
* explicit authorization decisions
* secure input handling
* safe parsing
* safe defaults
* safe failure modes
* auditability where needed
* defensible composition

---

## 36. Performance Governance

Performance matters, but premature optimization is forbidden.

Use this rule:

* first achieve clear ownership and correctness
* then measure
* then optimize where the cost is real and proven

Performance decisions must be explicit and reviewable.

Hot paths may justify specialized structures, but such specialization must be:

* measured
* documented
* isolated
* understandable

Readability remains the default.
Complexity must earn itself.

---

## 37. Documentation Governance

Documentation must reinforce system clarity.

It must explain:

* what the system does
* how the system is shaped
* who owns what
* why major structural decisions exist
* how to change the system safely

Recommended governance documents include:

* architecture overview
* naming standard
* testing strategy
* security boundary notes
* integration boundary notes
* decision records for major structural choices

Documentation must explain real structure, not idealized fiction.

If the docs and the code disagree, the system is lying somewhere.

---

## 38. Change Governance

Every change should preserve or improve:

* clarity
* ownership
* naming
* testability
* safety
* maintainability

Before merging a change, ask:

* does this make the system easier to read?
* does it preserve one concept, one name?
* does it reduce or increase ambiguity?
* does it introduce a bucket?
* does it add speculative abstraction?
* does it improve local ownership?
* is the behavior protected by tests?

A change that "works" but degrades structural clarity is not a good change.

---

## 39. Team Review Checklist

Use this checklist during reviews.

### Structure

* Does the folder say a flow or capability?
* Does the unit obviously own one responsibility?
* Is this level of hierarchy justified?
* Is the reading path clearer than before?

### Naming

* Is the name simple and predictive?
* Does it create a synonym for an existing concept?
* Would a new team member understand it without opening the file?

### Ownership

* Is this owned in the most honest place?
* Is this still local truth, or should it now be shared?
* Has something been extracted too early?

### Testing

* Is the change backed by tests?
* Was behavior specified before implementation?
* Was regression testing added where needed?

### Complexity

* Is this the simplest structure that works?
* Did we add ceremony without clarity?
* Is any abstraction carrying its weight?

### Safety

* Are trust boundaries clear?
* Are failure modes safe?
* Are edge and abuse cases covered where needed?

---

## 40. Canonical Shape

This is a recommended default, not blind doctrine.

```text
Project/
  <system-root>/
    Public surface units when needed
    Flows/
    Capabilities/
    Configuration/
    Foundation/
  tests/
  docs/
  examples/
  tooling/
  README.md
  GOVERNANCE.md
```

Example:

```text
Project/
  src/
    IncomingHttp/
      HandleIncomingHttp/
      IncomingRequest/
      Authenticate/
      Authorize/
      Route/
      ExecuteAction/
      BuildResponse/

    Access/
    Identity/
    Routing/
    Configuration/
    Foundation/

  tests/
  docs/
  tooling/
```

This is only a starting shape.

The real law is not the template.
The real law is:

* ownership
* clarity
* reading order
* locality
* honest abstraction

---

## 41. Example of Recursive Ownership

Example:

```text
IncomingHttp/
  HandleIncomingHttp
  IncomingRequest/
    IncomingRequest
    Request
    RequestHeaders/
      RequestHeaders
      NormalizeHeaders
    RequestBody/
      RequestBody
      ParsedBody
      Parsers/
        ParseBodyByContentType
        ParseJsonBody
        ParseFormBody
```

This is valid because:

* `IncomingHttp` is the flow
* `IncomingRequest` is a local subflow
* `Request` is a state owner
* `NormalizeHeaders` is an action owner
* `Parsers/` is a justified sub-capability
* the same reading law works at every level

This is the intended recursive model.

---

## 42. Anti-Patterns and Corrections

### Anti-pattern: Generic service bucket

```text
Services/
  UserService
  PaymentService
  AuthService
```

Why it is weak:

* no flow meaning
* weak ownership
* hides behavior categories
* encourages god classes

Prefer:

* flow or capability slices with explicit responsibility

### Anti-pattern: Helper dump

```text
Helpers/
  StringHelper
  HttpHelper
  AuthHelper
```

Why it is weak:

* no ownership
* no honest boundary
* random collection behavior

Prefer:

* place each behavior near the flow or capability that owns it

### Anti-pattern: Over-layering

```text
Controllers/
Services/
Repositories/
Factories/
Managers/
Helpers/
```

Why it is weak:

* reads like technical plumbing
* obscures behavior
* weak story of the system

Prefer:

* slices shaped by behavior and shared capability

### Anti-pattern: Recursive ceremony without meaning

```text
Feature/
  Handler/
    HandlerFactory/
      HandlerManager/
```

Why it is weak:

* depth without value
* role confusion
* architecture theater

Prefer:

* the fewest levels that preserve honest ownership

---

## 43. Decision Record Rule

Major architectural decisions should be captured briefly and clearly.

Examples of decisions worth recording:

* why a capability was extracted from a flow
* why a public surface differs from internal ownership structure
* why a special performance optimization exists
* why a rewrite was justified
* why a non-standard tree shape is used

Decision records should explain:

* context
* decision
* consequences

Not essays.
Just enough truth to preserve future clarity.

---

## 44. Governance for Multi-Language Teams

When teams use multiple languages, do not let each language invent a different architectural ideology.

Instead:

* keep the same ownership law
* keep the same naming law
* keep the same reading model
* adapt only syntax and packaging conventions
* preserve the same review questions
* preserve the same anti-pattern definitions

This governance is intended to create structural continuity across languages.

---

## 45. Governance for Libraries vs Applications

### Libraries

Libraries often emphasize capabilities and public surfaces.

They should optimize for:

* stable external contracts
* clear internal ownership
* small public API
* explicit interoperability boundaries

### Applications

Applications often emphasize flows.

They should optimize for:

* business movement clarity
* use-case readability
* localized change
* operational traceability

### Shared rule

Both must still obey:

* ownership
* naming clarity
* recursive readability
* honest abstraction

---

## 46. Governance for Event-Driven Systems

In event-driven or asynchronous systems, a flow may be distributed across time or messages.

That is acceptable.

A flow does not need to be synchronous to be a flow.

Examples:

* `ReceiveOrderPlaced`
* `ValidateOrder`
* `ReserveInventory`
* `PublishShipmentRequested`

The same rules apply:

* make the movement explicit
* keep local ownership honest
* name action owners descriptively
* avoid generic event handler dumping grounds

---

## 47. Governance for Functional or Script-Heavy Systems

In function-oriented systems, the same rules still apply.

Instead of classes, ownership may be expressed by:

* modules
* files
* package entries
* function groups

A file may act as a unit.
A module may act as a flow owner.
A function group may act as an action owner.
A data structure may act as a state owner.

The law remains:

* folder says flow or capability
* unit says responsibility
* function says exact action

---

## 48. Governance for OOP Systems

In object-oriented systems, use OOP pragmatically.

Good uses of objects include:

* state ownership
* boundary ownership
* explicit contracts
* stable collaboration models
* value objects
* small coordinators
* clear flow owners

Bad uses of OOP include:

* inheritance for convenience
* abstract base classes without real value
* object pyramids to simulate importance
* services that own everything
* managers that manage vague things

Use objects to make ownership clearer, not to decorate complexity.

---

## 49. Governance for Frameworks

Frameworks should remain tools, not architecture authors.

Do not let framework folders define the system story by default.

Examples of weak default shapes:

* `controllers/`
* `services/`
* `utils/`
* `middlewares/`
* `repositories/`

These may exist when truly justified, but they should not become the primary reading model unless that shape is
genuinely the clearest for the system.

The system story should still be readable as behavior and ownership.

---

## 50. Final Standard

The final goal of this governance is:

**highest-quality simplicity**

The system must be simple enough to:

* read quickly
* explain quickly
* review honestly
* change safely
* extend predictably
* refactor confidently

And strong enough to:

* survive growth
* survive churn
* survive team change
* survive operational pressure
* stay secure
* stay modular
* stay maintainable

If a structure looks advanced but reads worse, it failed.

If a structure looks simple and remains strong, it succeeded.

---

## 51. Core Summary

Remember these laws:

### Main law

**Folder says flow or capability, unit says responsibility, function says exact action.**

### Recursive law

**The same reading law applies recursively where it improves clarity.**

### Flow law

**Where there is real movement, prefer a flow owner or pipeline-shaped owner.**

### Action law

**Where there is one clear transformation, prefer a descriptive action owner.**

### State law

**Where there is stable owned state, prefer a state owner.**

### Locality law

**Keep truth local until sharing becomes honest.**

### Simplicity law

**If it looks smarter but reads worse, it failed.**

---

## 52. Quality Ratchets and Gate Self-Tests

### 52.1 Non-Regression Rule

Architectural metrics and quality scores MUST NOT regress from the previously established baseline.

- **Namespace Drift**: MUST stay at 0.
- **Duplicate Owners**: MUST stay at 0.
- **Hollow Public Surfaces**: MUST stay at 0.
- **Technical Dumping Grounds**: MUST stay at 0 (enforced by forbidden-folder audit).

**Status:** MANDATORY  
**Severity:** BLOCKER

### 52.2 Gate Self-Test Rule

Every architecture-specific validation gate MUST have a negative test case proving it detects violations (e.g., placing
a class in a forbidden folder).

**Status:** MANDATORY  
**Severity:** HIGH

---

## 53. Evidence-Driven Architectural Claims

### 53.1 Definition of Proof

An architectural claim (e.g., "all components are canonical") is **UNPROVEN** unless it is accompanied by a timestamped
validation report from a canonical tool.

### 53.2 Suspicion of Manual Claims

Manual claims of "correctness" are classified as **HIGH RISK** and MUST be replaced by automated gate evidence at the
earliest opportunity.

**Status:** MANDATORY  
**Severity:** BLOCKER

---
