# Architecture Extension
## Public Surface and DDD Domain Modeling for AvaX Screaming Architecture

## 1. Status of This Document

This document is an architecture extension to the main AvaX architecture governance.

It defines two related rules:

1. When and how a framework or component system root may expose a `PublicSurface/` folder.
2. How selected Domain-Driven Design concepts may be used without violating the AvaX architecture law.

This document is not a style preference.
This document is not a generic DDD folder template.
This document is not a place to introduce ceremony.
This document is not a justification for technical buckets such as `Entities/`, `ValueObjects/`, `Services/`, `Repositories/`, `Contracts/`, `Adapters/`, `Helpers/`, or `Managers/` at the system root.

This document exists to protect clarity.

The main AvaX architecture law still wins:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

DDD concepts are allowed only when they make that law stronger.

They are forbidden when they make the system more generic, more technical, more abstract, or harder to read.

This document must be applied together with the rest of the `.agents/how-to/*.md` governance.

If another governance document is stricter, the stricter rule wins.

---

## 2. Operational Summary

Use `PublicSurface/` only when a component or framework root exposes a real stable public API.

Use DDD only when it improves:

```text
meaning
language
ownership
invariants
identity
lifecycle
state transitions
persistence boundaries
public API compatibility
safe change
```

Never create root-level technical DDD buckets:

```text
Domain/
  Entities/
  ValueObjects/
  Aggregates/
  Repositories/
  Services/
  Events/
```

Preferred shape:

```text
bounded context says language boundary
folder says flow or capability
unit says domain responsibility
function says exact domain action
```

Public surface receives calls.
Flows execute behavior.
Capabilities power reusable behavior.
Configuration assembles.
Foundation supports tiny neutral primitives.

DDD is a semantic layer.

Screaming architecture remains the structural layer.

If DDD makes the system harder to explain, remove it.

If DDD makes ownership, invariants, lifecycle, or public compatibility safer, use it.

---

## 3. Purpose

The purpose of this extension is to define clear boundaries for public APIs and domain modeling.

A system needs two kinds of clarity:

```text
External clarity:
What may users call?
What is stable?
What is public?
What is versioned?
What is safe to depend on?

Internal clarity:
What does the system do?
Who owns behavior?
Where do invariants live?
Where does state change?
Which words mean what?
Which concepts are real domain concepts?
```

`PublicSurface/` protects external clarity.

DDD-inspired domain modeling protects internal meaning when the problem is complex enough to need it.

Both must serve the same goal:

```text
highest-quality simplicity
```

The system must stay:

```text
easy to read
easy to explain
easy to review
easy to extend
easy to refactor safely
explicit in ownership
resistant to structural decay
strong under real-world pressure
```

If DDD makes the code look more sophisticated but less readable, it failed.

If DDD makes ownership, language, invariants, lifecycle, and change safer, it succeeded.

---

## 4. Relationship to Main Governance

This extension does not replace the main architecture governance.

It adds specific rules for:

```text
public API ownership
public API stability
public API compatibility
DDD vocabulary
bounded contexts
ubiquitous language
value objects
entities
aggregates
repositories
domain services
factories
domain events
DDD recovery from legacy code
DDD proof requirements
```

Correct interpretation:

```text
DDD is a semantic layer.
Screaming architecture remains the structural layer.
```

Wrong interpretation:

```text
DDD replaces the architecture with generic technical folders.
```

The folder tree must still tell a story of behavior and ownership.

The reader should not open a folder and see a technical warehouse.

Bad:

```text
System/
  Domain/
    Entities/
    ValueObjects/
    Services/
    Repositories/
```

Good:

```text
System/
  Flows/
    VerifyReleaseReadiness/
    DetectBreakingPublicApiChange/

  Capabilities/
    ReleaseReadiness/
    PublicApiCompatibility/
    RuntimeSafety/
```

Then inside those slices, DDD concepts may appear as units when they clarify responsibility.

---

# Part I: Public Surface

---

## 5. PublicSurface Purpose

`PublicSurface/` is the explicit home for stable public API entrypoints.

Its purpose is to separate:

```text
what external users are allowed to touch
what the framework or component owns internally
what may change freely
what must remain stable and versioned carefully
```

A public API is a compatibility promise.

Internal structure may evolve aggressively.

Public surface must evolve deliberately.

`PublicSurface/` exists to make that boundary visible in the filesystem.

---

## 6. PublicSurface Core Rule

Every framework or component system root may have `PublicSurface/` when it exposes stable public API units.

However, `PublicSurface/` must be justified.

A system root must not create `PublicSurface/` automatically, mechanically, or decoratively.

Use `PublicSurface/` only when it improves:

```text
API clarity
package usability
boundary safety
external discoverability
long-term compatibility
documentation quality
```

If a folder has no stable external API, it must not have `PublicSurface/`.

---

## 7. PublicSurface Ownership Rule

`PublicSurface/` owns external entrypoints only.

It may receive the first user call.

It may normalize simple public input.

It may return stable public values.

It may delegate to internal flows and capabilities.

It must not own the real behavior of the system.

Correct responsibility:

```text
PublicSurface/ receives public calls.
Flows/ execute behavior.
Capabilities/ provide reusable mechanisms.
Configuration/ assembles the system.
Foundation/ provides tiny neutral primitives.
```

`PublicSurface/` is a doorway.

It is not the engine.

---

## 8. Mandatory Delegation Rule

Every public surface unit must delegate real work to `Flows/`, `Capabilities/`, or `Configuration/`.

A public surface class may coordinate the first public call.

It must not implement the full behavior.

Good:

```text
PublicSurface/
  Cache.php
    delegates to Flows/ReadCachedValue
    delegates to Flows/StoreCachedValue
    delegates to Flows/RememberCachedValue
```

Bad:

```text
PublicSurface/
  Cache.php
    contains storage logic
    serializes values directly
    calculates TTL internals directly
    talks directly to the filesystem
    manages driver behavior internally
```

If the public surface starts doing the work, the boundary has failed.

---

## 9. PublicSurface Allowed Contents

`PublicSurface/` may contain:

```text
facade classes
root public API classes
package entrypoints
stable aliases
public DTOs that are part of the external API
public value objects that are part of the external API
public factories that protect users from internal construction details
public kernel interfaces when they are part of the external framework API
narrow public contracts when the word contract is actually part of the user-facing language
```

Allowed framework example:

```text
framework/
  System/
    PublicSurface/
      Avax.php
      HttpKernel.php
      ConsoleKernel.php
      RuntimeKernel.php

      Facades/
        App.php
        Route.php
        Cache.php
```

Allowed component example:

```text
components/
  Application/
    Cache/
      System/
        PublicSurface/
          Cache.php
          CacheKey.php
          CacheTtl.php
```

Use public names that a user understands.

Do not expose internal architecture terms unless the user truly needs them.

---

## 10. PublicSurface Forbidden Contents

`PublicSurface/` must not contain:

```text
business logic
runtime machinery
flow implementation
runtime-specific implementation
infrastructure details
request-scoped mutable state
service registration internals
internal registries unless intentionally public
random helper classes
generic utility classes
dumping-ground abstractions
framework internals hidden behind public-looking names
```

Bad examples:

```text
PublicSurface/
  HandleIncomingHttp.php
  SwooleRequestAdapter.php
  BuildContainer.php
  ResolveDependencies.php
  CacheHelper.php
  InternalRegistry.php
  RuntimeStateStore.php
  WorkerLoop.php
```

These belong elsewhere:

```text
Flows/
  HandleIncomingHttp/

Capabilities/
  RuntimeIntegration/
    RunApplicationOnSwoole/

Configuration/
  BuildApplication/

Foundation/
  Time/
  Paths/
  Failure/
```

---

## 11. PublicSurface Factory Boundary Rule

PublicSurface may expose public factories only when they create public value/result objects or protect users from internal construction details.

PublicSurface factories MUST NOT:

\`\`\`text
assemble runtime service graphs
instantiate runtime services
access the container as service locator
create middleware, dispatchers, resolvers, clients, stores, loggers, repositories, or framework runtime services
hide dependency assembly
\`\`\`

**Allowed:** \`Responses::json()\` delegates to \`CreateHttpResponse\` and returns \`Response\`.
**Forbidden:** \`Responses::json()\` creates new \`CreateHttpResponse\` internally.

PublicSurface may create produced public values. PublicSurface must not assemble machinery.

---


## 12. Public API Stability Rule

Changing `PublicSurface/` is a public API decision.

Any breaking change inside `PublicSurface/` must be treated as a versioned compatibility change.

This includes:

```text
renaming public classes
removing public methods
changing method signatures
changing return types
changing exception behavior
changing lifecycle guarantees
changing facade behavior
changing public DTO or value object shape
changing public factory behavior
changing public event shape
changing documented semantics
```

Internal folders may change more freely.

`PublicSurface/` must change carefully because users build code against it.

---

## 13. Small Surface Rule

`PublicSurface/` must stay small.

A large public surface is a design warning.

If too many files are needed in `PublicSurface/`, ask:

```text
Is the public API too broad?
Are internals leaking?
Are too many concepts exposed?
Is this component doing too much?
Should some APIs be moved behind a smaller facade?
Should some public concepts remain internal?
```

A strong public API is usually small, boring, predictable, and easy to document.

---

## 14. No Runtime Leakage Rule

Runtime-specific APIs must not leak into `PublicSurface/`.

Framework users may choose a runtime.

Core public APIs must not force users to know about runtime internals unless the public API is explicitly about runtime integration.

Forbidden:

```text
PublicSurface/
  SwooleRequest.php
  RoadRunnerWorker.php
  FrankenPhpResponse.php
```

Allowed when explicitly scoped:

```text
PublicSurface/
  RuntimeKernel.php
  RuntimeKernelInterface.php
```

Runtime-specific implementation belongs in clearly named runtime capabilities:

```text
Capabilities/
  RuntimeIntegration/
    RunApplicationOnSwoole/
    RunApplicationOnRoadRunner/
    RunApplicationOnFrankenPhp/
    RunApplicationOnWorkerman/
```

The public API may expose framework runtime abstractions.

It must not expose server-specific implementation details by accident.

---

## 15. No Request State Rule

`PublicSurface/` must not hold request-scoped mutable state.

This is critical for long-lived runtimes.

A public surface class must not store:

```text
current request
current response
current user
current session state
current route match
current correlation context
per-request cache
temporary runtime state
```

Request-specific state must live in explicit request scope ownership.

This protects the system from leaking state between requests in worker runtimes.

---

## 16. PublicSurface Placement Rule

Use `PublicSurface/` inside a framework or component system root.

Framework example:

```text
framework/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/
    Foundation/
```

Component example:

```text
components/
  HTTP/
    Router/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Do not create a top-level repository `PublicSurface/` folder unless the repository itself is a single package with one system root.

---

## 17. PublicSurface Naming Rule

Files inside `PublicSurface/` must use public API names.

Good:

```text
Avax.php
HttpKernel.php
ConsoleKernel.php
RuntimeKernel.php
Cache.php
Router.php
Database.php
```

Weak:

```text
ApiManager.php
PublicHelper.php
MainService.php
FacadeThing.php
Processor.php
InternalBridge.php
```

Names must be obvious to a user who has not opened the implementation.

A public API name must explain what the user is touching.

---

## 18. PublicSurface Documentation Rule

Every `PublicSurface/` folder must be documented.

The documentation must answer:

```text
What is the public API here?
Who is allowed to use it?
What is stable?
What is intentionally hidden?
Which internal flows does it delegate to?
What must not be used directly?
What counts as a breaking change?
```

If the public surface cannot be documented simply, the API is probably too broad or too unclear.

---

## 19. PublicSurface Proof Rule

A public surface is accepted only when at least one test proves that:

```text
the public entrypoint can be called
the public entrypoint delegates to the correct internal owner
the public entrypoint does not own internal behavior
the public entrypoint does not leak runtime-specific implementation
the public entrypoint does not retain request-scoped state
```

Example proof:

```text
Database::configuration()
  delegates to DatabaseBuilder
  assembles Database
  exposes schema()
  schema() delegates to Schema capability
  behavior is proven through SQLite runtime test
```

No proof means no green status.

---

# Part II: DDD Adapted to Screaming Architecture

---

## 20. Status of DDD in This Architecture

This architecture may use parts of Domain-Driven Design.

However, DDD must be adapted to the existing screaming architecture.

DDD concepts are useful when they clarify:

```text
domain language
ownership
invariants
lifecycle
identity
state transitions
business rules
event meaning
boundaries between meanings
persistence boundaries
public API compatibility
```

DDD concepts are harmful when they create:

```text
generic folders
artificial layers
anemic models
service dumping grounds
repository dumping grounds
abstract names
technical language
architecture theater
```

This governance accepts DDD as a discipline of meaning.

It rejects DDD as a folder template.

---

## 21. DDD Integration Law

DDD concepts must obey this rule:

```text
bounded context says language boundary
folder says flow or capability
unit says domain responsibility
function says exact domain action
```

This is the DDD version of the main architecture law.

The folder still must not say only the technical DDD type.

Bad:

```text
System/
  Domain/
    Entities/
    ValueObjects/
    Aggregates/
    Repositories/
    Services/
```

Good:

```text
System/
  Flows/
    VerifyReleaseReadiness/
      VerifyReleaseReadiness.php
      ReleaseReadinessVerified.php

  Capabilities/
    ReleaseReadiness/
      ReleaseCandidate.php
      ReleaseReadinessState.php
      ReleaseReadinessPolicy.php
      ReleaseCandidateRepository.php
```

The reader should first understand the system behavior or capability.

Only then should the reader discover whether a unit is a value object, entity, aggregate, repository, domain service, factory, or event.

---

## 22. What Ubiquitous Language Means Here

Ubiquitous Language is the shared language of a bounded context.

It is not merely a glossary.

It is the language that appears consistently in:

```text
folder names
unit names
function names
tests
documentation
public API
domain events
error messages
review comments
architecture discussions
```

A term belongs to the Ubiquitous Language only if the team can explain:

```text
What does this word mean?
Where is it valid?
Who uses it?
What does it include?
What does it exclude?
Which invariant or behavior depends on it?
Which other terms is it commonly confused with?
```

If the word cannot pass this test, it is not yet a stable domain term.

---

## 23. Ubiquitous Language Must Be Simple

The language must be easy to read.

The goal is not to sound academic.

The goal is to make the system obvious.

Good domain language sounds like what the system really protects:

```text
Compatibility
BreakingChange
PublicApi
Deprecation
ReleaseReadiness
RuntimeSafety
ComponentCompletion
HealthStatus
FailureReason
```

Weak technical language hides meaning:

```text
Contract
Manager
Processor
Handler
Service
Data
Info
Object
Module
Core
Common
Shared
```

Some technical terms are allowed when they are the real language of the product.

For example, `PublicApi`, `Schema`, `Runtime`, or `Kernel` may be valid in a framework.

But a term is not valid merely because developers recognize it.

It must describe real ownership.

---

## 24. Contract Naming Rule

Do not use `Contract` as a broad bounded context name.

`Contract` is usually too technical and too vague.

Prefer names that say what the system protects.

Good:

```text
Compatibility
PublicApiCompatibility
BreakingChangeDetection
PublicApiVersioning
DeprecationPolicy
```

Weak:

```text
Contract
Contracts
ContractPlane
ContractContext
ContractService
```

The word `contract` may still be used narrowly when it is the correct public or technical term.

Allowed narrow examples:

```text
ConsumerContractTest
EventContract
CommandContract
OpenApiContract
CacheStoreContract
```

But do not use `Contract` as a large folder or bounded context when the real concern is compatibility, versioning, deprecation, or public API safety.

The better question is:

```text
What promise is being protected?
```

If the answer is backward compatibility, name it `Compatibility`.

If the answer is public API shape, name it `PublicApi`.

If the answer is breaking change detection, name it `DetectBreakingPublicApiChange`.

---

## 25. Adapter Naming Rule

Do not use `Adapters/` as a broad dumping ground.

The word adapter may be valid in discussion, but a folder named `Adapters/` usually hides real ownership.

Prefer names that describe the boundary or technology:

```text
Capabilities/
  S3ObjectStorage/
  LocalObjectStorage/
  FakeObjectStorage/
  RedisCacheStore/
  FileCacheStore/
  RunApplicationOnSwoole/
  RunApplicationOnRoadRunner/
```

Weak:

```text
Capabilities/
  Adapters/
    S3Adapter.php
    RedisAdapter.php
    SwooleAdapter.php
```

The folder should explain what the implementation does or which boundary it integrates.

Do not sort code by architectural jargon when a clearer capability name exists.

---

## 26. Bounded Context

A Bounded Context is a boundary where language has one stable meaning.

It answers:

```text
Where does this word mean exactly this?
Where does it stop meaning this?
Which model owns this meaning?
Which other contexts must not reinterpret this concept?
```

A Bounded Context is not a folder category.

A Bounded Context is a meaning boundary.

### 25.1 Naming Bounded Contexts

A bounded context name must describe a real capability, business area, platform area, or language boundary.

Good:

```text
Runtime
Compatibility
Delivery
Reliability
Observability
Identity
Access
Billing
Catalog
Inventory
Checkout
```

Weak:

```text
Domain
Core
Common
Shared
Contracts
Entities
Services
Business
```

### 25.2 Bounded Context Documentation

Every explicit bounded context must define:

```md
## <Context Name>

### Meaning
What this context means.

### Owns
What this context owns.

### Does Not Own
What this context must not own.

### Language
Canonical terms and meanings.

### Invariants
Rules that must always hold.

### Flows
End-to-end behaviors owned by the context.

### Capabilities
Shared mechanisms owned by the context.

### Public Surface
What external users may call, if any.

### Events
Facts this context may emit.

### Integrations
Other contexts or external systems it collaborates with.

### Forbidden Names
Words that are too vague or misleading here.
```

### 25.3 Bounded Context Example: Compatibility

```md
## Compatibility

### Meaning
Compatibility protects users from unsafe public API change.

### Owns
- public API versioning
- breaking change detection
- deprecation rules
- migration safety
- public schema description
- compatibility reports

### Does Not Own
- runtime execution
- HTTP request handling
- deployment
- infrastructure integrations
- general documentation

### Language
- PublicApi: stable surface users may depend on
- BreakingChange: change that may break existing users
- Deprecation: supported warning before removal
- CompatibilityReport: result of evaluating API safety
- MigrationPath: documented way to move from old API to new API

### Invariants
- A breaking change must not be released silently.
- A deprecated public API must have a documented migration path.
- A compatibility report must identify the changed public element.
- Internal changes are not breaking changes unless they affect public behavior.
```

This is stronger than a generic `Contract` context because it explains the real domain concern.

---

## 27. When Bounded Contexts Are Needed

Use explicit bounded contexts when:

```text
the same word has different meanings in different parts of the system
a capability has its own language and invariants
teams can own parts of the system independently
public API stability matters
the model is large enough that local terms need boundaries
integrations require clear translation between concepts
release safety depends on knowing which model owns which rule
domain events cross boundaries
one area changes for different reasons than another area
```

Example:

```text
Runtime and Delivery should not share one vague model.

Runtime owns application lifecycle and worker safety.
Delivery owns release verification and production readiness.
```

These are different languages.

They deserve separate context definitions.

---

## 28. When Bounded Contexts Are Not Needed

Do not create explicit bounded contexts when:

```text
the project is small
there is only one clear language
there are no competing meanings
there are no strong invariants
there is no independent ownership boundary
the context name would be decorative
a simple flow/capability structure already explains everything
adding the context would only create documentation noise
```

Small systems may use only:

```text
Flows/
Capabilities/
Configuration/
Foundation/
PublicSurface/
```

That is enough until the language becomes large enough to need stronger boundaries.

Do not add DDD structure just to look mature.

---

## 29. Tactical DDD Concepts

Use tactical DDD concepts only when they solve a real clarity or correctness problem.

### 28.1 Aggregate

An Aggregate owns a consistency boundary.

It protects invariants that must be true together.

Prefer domain names:

```text
ReleaseCandidate
PublicApi
RuntimeWorker
Order
Invoice
```

Avoid suffixes unless they remove ambiguity:

```text
ReleaseCandidateAggregate
OrderAggregate
```

Place the aggregate in the capability or flow that owns its invariants:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseCandidate.php
    ReleaseCandidateId.php
    ReadinessState.php
    ReleaseApprovedForProduction.php
```

Do not place it in:

```text
Domain/
  Aggregates/
```

### 28.2 Entity

An Entity has identity and lifecycle.

Good names:

```text
RuntimeWorker
ApplicationInstance
UserAccount
Invoice
Order
```

Weak names:

```text
WorkerEntity
DataEntity
BusinessEntity
```

An entity must protect lifecycle behavior.

It must not be a database row wrapper with setters.

### 28.3 Value Object

A Value Object is immutable and represents a meaningful value.

Good names:

```text
PublicApiVersion
HealthStatus
FailureReason
ReleaseVersion
EmailAddress
Money
```

Weak names:

```text
StringValue
Name
Version
Status
Value
```

A value object must validate or express meaning.

Do not wrap primitives mechanically.

### 28.4 Repository

A Repository persists and retrieves aggregates.

It is not a generic table gateway.

Good:

```text
ReleaseCandidateRepository
PublicApiRepository
RuntimeWorkerRepository
```

Weak:

```text
DataRepository
StorageRepository
GenericRepository
DatabaseRepository
```

Place repository interfaces near the aggregate capability:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseCandidate.php
    ReleaseCandidateRepository.php

Capabilities/
  ReleaseReadinessStorage/
    SqlReleaseCandidateRepository.php
```

A repository is allowed only when there is a real aggregate or lifecycle to protect.

### 28.5 Domain Service

A Domain Service is an exact domain action that does not naturally belong to one entity or value object.

Good:

```text
DetectBreakingPublicApiChange
CalculateReleaseReadiness
VerifyRuntimeSafety
EvaluateDeprecationPolicy
```

Weak:

```text
CompatibilityService
RuntimeService
DomainService
Processor
Handler
Manager
```

A domain service must have one clear action.

### 28.6 Factory

A Factory owns meaningful creation.

Use it when construction has invariants or policy.

Good:

```text
CreateReleaseCandidate
BuildRuntimeConfiguration
CreatePublicApiSnapshot
```

Weak:

```text
Factory
ObjectFactory
DomainFactory
```

Do not create factories for trivial constructors.

### 28.7 DDD Factory vs Runtime Assembly Rule

A DDD factory owns meaningful creation of domain/value/result objects when construction has invariants, policy, or language meaning.

A DDD factory MUST NOT assemble framework runtime service graphs.

**Allowed:** `CreateReleaseCandidate` creates `ReleaseCandidate` with invariants.
**Forbidden:** `RuntimeFactory` creates `Router`, `EventDispatcher`, `Logger`, `MiddlewareStack`, `DatabaseConnection`.

If a class assembles runtime services, it belongs in `ServiceProvider`, `System/Configuration`, or `System/Configuration/Builders` — not in a DDD factory.

Factories create meaningful objects. Configuration assembles the system.

### 28.8 Domain Event

A Domain Event records a completed domain fact.

Use past tense.

Good:

```text
ReleaseApprovedForProduction
BreakingPublicApiChangeDetected
PublicApiDeprecated
RuntimeSafetyViolated
```

Weak:

```text
ReleaseEvent
UpdateEvent
DoRelease
ProcessCompatibility
```

A domain event is not a command.

A command asks for action.

An event records a fact.

---

## 30. Ubiquitous Language Dictionary

Every significant bounded context should have a small language dictionary.

The dictionary must be practical, not academic.

Recommended shape:

```md
# <Context Name> Language

## Terms

### <Term>

Meaning:

Use when:

Do not use when:

Common confusion:

Code names:

Events:

Invariants:

Examples:
```

---

## 31. Required DDD Language Outputs

When a bounded context is explicitly introduced, the design must produce:

```text
1. Context name
2. Meaning
3. Owned language
4. Forbidden or weak terms
5. Flows
6. Capabilities
7. Aggregates where needed
8. Value objects where needed
9. Entities where needed
10. Repositories where persistence boundaries exist
11. Domain services where behavior has no natural object owner
12. Factories where construction has domain meaning
13. Domain events where domain facts matter
14. Invariants
15. Public surface, if any
16. Compatibility rules, if public behavior exists
17. Tests that prove the important behavior or invariant
```

This does not mean every bounded context must have every DDD tactical pattern.

It means every bounded context must honestly decide which ones are needed.

---

## 32. When DDD Is Needed

Use DDD concepts when the system has real domain pressure.

DDD is needed when:

```text
language is becoming unclear
the same word means different things in different areas
important invariants must be protected
state transitions are meaningful
objects have lifecycle
public API compatibility matters
release safety depends on domain rules
business or platform rules are not obvious from simple procedural code
tests need clearer behavior language
multiple teams or components need shared meaning
events represent important facts
persistence boundaries need to protect aggregates
workflows coordinate meaningful domain concepts
```

Example:

```text
Release readiness is not just a boolean.

It may depend on tests, static analysis, public API compatibility,
runtime safety, documentation, security, performance, and rollback posture.

That deserves language.
```

---

## 33. When DDD Is Not Needed

Do not use DDD concepts when the code is simple and already clear.

DDD is not needed when:

```text
the behavior is a simple transformation
there is no lifecycle
there are no meaningful invariants
there is no domain language beyond the action itself
a function or small unit is enough
introducing a value object would only wrap a primitive without meaning
introducing an aggregate would create ceremony
introducing a repository would hide a simple capability
introducing a domain service would create a vague service
introducing a bounded context would only rename an existing folder
the team cannot explain the term without reading code
```

Simple code is allowed.

Good:

```text
Capabilities/
  Paths/
    NormalizePath.php
```

Weak:

```text
Domain/
  ValueObjects/
    PathValue.php

Domain/
  Services/
    PathService.php
```

---

## 34. DDD Minimum Rule

Use the smallest DDD concept that solves the clarity problem.

Recommended progression:

```text
1. Better name
2. Better function
3. Better unit
4. Value object
5. Entity
6. Aggregate
7. Domain event
8. Repository
9. Domain service
10. Factory
11. Explicit bounded context
```

Do not jump to bounded contexts, aggregates, and repositories when a better name would solve the problem.

The first solution to unclear design is usually better language.

---

## 35. DDD Proof Rule

A DDD concept is accepted only when at least one test proves the behavior or invariant it exists to protect.

Examples:

```text
Value object test proves invalid values cannot be created.
Aggregate test proves invalid state transition is rejected.
Repository test proves aggregate can be saved and restored without leaking storage shape.
Domain event test proves the fact is recorded after the domain action.
PublicSurface test proves public API delegates without owning internal behavior.
```

No proof means the concept is not green.

A DDD concept may exist temporarily as yellow during active design, but it must not be called production-ready until its behavior is proven.

---

## 36. DDD and Flow Slices

Flow slices describe end-to-end behavior.

They answer:

```text
What happens?
```

DDD units inside a flow must support that flow.

Good:

```text
Flows/
  ApproveReleaseForProduction/
    ApproveReleaseForProduction.php
    ReleaseApprovedForProduction.php
    ReleaseApprovalFailed.php
```

Allowed when the flow owns local concepts:

```text
Flows/
  ApproveReleaseForProduction/
    ReleaseApprovalDecision.php
    ReleaseApprovalReason.php
```

Do not extract local concepts into shared capabilities too early.

A flow-local value object or event may stay local until it is truly shared.

Shared last.

---

## 37. DDD and Capability Slices

Capability slices describe reusable system abilities.

They answer:

```text
What ability supports multiple flows?
```

DDD units inside a capability must support that capability.

Good:

```text
Capabilities/
  PublicApiCompatibility/
    PublicApi.php
    PublicApiSnapshot.php
    PublicApiVersion.php
    CompatibilityReport.php
    CompatibilityResult.php
    BreakingChange.php
    DeprecationNotice.php
    DetectBreakingPublicApiChange.php
    BreakingPublicApiChangeDetected.php
```

This is valid because compatibility has its own language, invariants, and repeated use.

Bad:

```text
Capabilities/
  Domain/
    Entity.php
    Value.php
    Repository.php
```

This is invalid because it says nothing about the system ability.

---

## 38. DDD and PublicSurface

Public DDD objects may appear in `PublicSurface/` only when they are part of the stable user-facing API.

Allowed:

```text
PublicSurface/
  Cache.php
  CacheKey.php
  CacheTtl.php
```

Allowed if external users receive or create these values:

```text
PublicSurface/
  PublicApiVersion.php
  DeprecationNotice.php
  CompatibilityReport.php
```

Forbidden:

```text
PublicSurface/
  InternalReleaseAggregate.php
  SqlReleaseRepository.php
  RuntimeStateEntity.php
  DetectBreakingPublicApiChange.php
```

Public surface should expose simple stable concepts.

Internal domain machinery should remain internal.

---

## 39. DDD and Foundation

Foundation contains tiny neutral primitives.

It must not become a hidden domain layer.

Allowed Foundation examples:

```text
Foundation/
  Clock/
  Result/
  Failure/
  Path/
  Uuid/
```

Forbidden:

```text
Foundation/
  User/
  Order/
  ReleaseCandidate/
  ComponentCompletion/
  CompatibilityRules/
```

If a concept carries domain meaning, it does not belong in Foundation.

It belongs in the flow or capability that owns it.

---

## 40. DDD and Configuration

Configuration owns assembly, wiring, bootstrapping, and composition.

It may build domain objects or services.

It must not own domain behavior.

Good:

```text
Configuration/
  BuildCompatibilityPipeline.php
  RegisterReleaseReadinessDependencies.php
```

Bad:

```text
Configuration/
  DetectBreakingPublicApiChange.php
  CalculateReleaseReadiness.php
```

The first assembles.

The second does domain work and belongs in flows or capabilities.

---

# Part III: Recovery and Migration

---

## 41. Existing Code Migration Rule

When existing code uses generic DDD folders, do not rename mechanically.

Translate by ownership:

```text
Entity goes near the capability that owns its lifecycle.
Value object goes near the capability or flow that gives it meaning.
Repository goes near the aggregate capability it persists.
Domain service becomes an exact action inside a flow or capability.
Event goes near the flow or capability that emits it.
Factory becomes CreateSomething or BuildSomething near the object it creates.
```

Old structure is not restored.

Old behavior is recovered into the current architecture.

Bad recovery:

```text
restore old Domain/Entities/User.php exactly where it was
restore old Infrastructure/Repositories/UserRepository.php exactly where it was
```

Good recovery:

```text
recover UserAccount lifecycle behavior into:
Capabilities/
  UserAccounts/
    UserAccount.php
    UserAccountId.php
    UserAccountRepository.php

recover login behavior into:
Flows/
  LoginUser/
    LoginUser.php
    UserLoggedIn.php
```

This is critical for `avax-backup.txt`, `Framework.txt`, `Components.txt`, and git history recovery.

Old code is evidence.

Current architecture is the target.

Tests are the judge.

---

## 42. Legacy Recovery Classification

When recovering behavior from old material, classify each candidate:

```text
GREEN:
Old behavior clearly maps to current architecture and has proof or obvious test path.

YELLOW:
Old behavior is valuable but needs design translation.

RED:
Old behavior conflicts with current architecture or has unclear ownership.

BLOCKED:
Old behavior would unlock V2/V3/V4 too early, create skeleton code, or violate active stage lock.
```

Recovery must produce:

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

Do not restore code without this classification.

---

## 43. Skeleton Recovery Ban

Do not create empty classes, fake facades, fake contracts, or placeholder implementations just to silence tooling.

Allowed:

```text
restore known behavior from backup
repair namespace drift
repair named-argument drift
add focused tests for existing behavior
write report-only recovery inventory
```

Forbidden:

```text
create empty class because a reference is missing
create fake public surface with no behavior
create generic interface because a dependency complains
create V2/V3 class while V1 is red
mark partial behavior as green
```

Static integrity is not the same as behavior completeness.

A missing reference may be fixed only when ownership is clear.

If ownership is unclear, classify it and stop.

---

# Part IV: Review and Examples

---

## 44. Naming Matrix

Use this matrix during design and review.

| DDD Concept | Folder Name Should Say | Unit Name Should Say | Function Name Should Say | Good Example | Weak Example |
|---|---|---|---|---|---|
| Bounded Context | language boundary | not usually a unit | not usually a function | `Compatibility` | `Contract` |
| Flow | behavior | flow owner | exact action | `DetectBreakingPublicApiChange` | `ProcessContract` |
| Capability | reusable ability | responsibility | exact action | `PublicApiCompatibility` | `Contracts` |
| Aggregate | owning capability or flow | domain concept | state transition | `ReleaseCandidate` | `ReleaseAggregate` |
| Entity | owning capability or flow | identity concept | lifecycle action | `RuntimeWorker` | `WorkerEntity` |
| Value Object | owning capability or flow | meaningful value | value behavior | `PublicApiVersion` | `StringValue` |
| Repository | owning aggregate capability | aggregate persistence boundary | retrieve or save exactness | `ReleaseCandidateRepository` | `DataRepository` |
| Domain Service | owning flow or capability | exact domain action | exact sub-action | `CalculateReleaseReadiness` | `ReleaseService` |
| Factory | owning flow or capability | exact creation action | create or build exact object | `CreateReleaseCandidate` | `ObjectFactory` |
| Domain Event | emitting flow or capability | past-tense fact | not usually behavior | `ReleaseApprovedForProduction` | `ReleaseEvent` |

---

## 45. Forbidden DDD Anti-Patterns

The following are forbidden unless explicitly justified inside a narrow local scope:

```text
Domain/
  Entities/
  ValueObjects/
  Aggregates/
  Repositories/
  Services/
  Events/
```

Why forbidden:

```text
The folder says technical type, not flow or capability.
The reader learns how the code is categorized, not what the system does.
```

Also forbidden:

```text
Entity suffix everywhere
Aggregate suffix everywhere
Service suffix as default
Repository for every table
Factory for every constructor
Event for every method call
Value object for every primitive
Bounded context for every folder
```

DDD must not become decoration.

---

## 46. Acceptable DDD Exceptions

A technical DDD folder may be allowed only inside a very narrow local scope when it improves navigation and does not weaken ownership.

Example:

```text
Capabilities/
  PublicApiCompatibility/
    Events/
      BreakingPublicApiChangeDetected.php
      PublicApiDeprecated.php
```

This is acceptable only if:

```text
the parent capability is already clear
the subfolder reduces noise
the event names remain domain-specific
the folder does not become a generic dumping ground
```

Even then, prefer direct placement until noise justifies grouping.

The parent must say ownership first.

The child may group technical shape second.

---

## 47. Practical Decision Tree

Before introducing a DDD concept, ask:

```text
1. Is the current code unclear?
2. Is the unclear part caused by weak language?
3. Is there a real domain concept here?
4. Does this concept own invariants, identity, lifecycle, creation, persistence, or facts?
5. Can a better name solve it without a new pattern?
6. Would this DDD concept reduce cognitive load?
7. Would it still obey folder says flow or capability?
8. Can the team explain it in plain language?
9. Can the behavior or invariant be proven by test?
```

If the answer to question 6, 7, or 9 is no, do not mark the concept green.

---

## 48. Review Checklist for DDD Usage

A DDD design passes review only if:

```text
every DDD concept has a clear reason to exist
every bounded context has a meaning boundary
every aggregate protects real invariants
every entity has identity and lifecycle
every value object has meaningful validation or behavior
every repository persists an aggregate boundary
every domain service has an exact action name
every factory owns non-trivial creation
every domain event is a past-tense fact
no generic Domain/Entities/Services architecture appears at the system root
no vague names like Manager, Helper, Processor, or generic Service hide ownership
technical DDD terms do not override screaming architecture
ubiquitous language is documented where the context is significant
the design is easier to explain after DDD was added
important behavior is proven by tests
```

If DDD makes the design harder to explain, remove it.

---

## 49. Review Classification

Use this classification during review.

### GREEN

```text
DDD concept protects real language or invariant,
obeys flow/capability ownership,
and is proven by test.
```

### YELLOW

```text
DDD concept is plausible,
local,
readable,
and useful,
but proof is incomplete.
```

### RED

```text
DDD concept exists mostly because of pattern habit,
creates generic structure,
or makes ownership less clear.
```

### BLOCKER

```text
DDD folders or generic services hide ownership.
Public surface leaks internals.
Public surface owns behavior.
A public API compatibility rule is violated.
A DDD structure violates active stage lock.
A concept is called production-ready without proof.
```

---

## 50. DDD Documentation Template

Use this template when a context or capability needs explicit domain documentation.

```md
# <Context or Capability Name> Domain Language

## Meaning

<Plain explanation of what this context or capability protects.>

## When to Use This Language

<When these words should be used.>

## When Not to Use This Language

<Where these terms do not apply.>

## Canonical Terms

### <Term>

Meaning:

Use when:

Do not use when:

Common confusion:

Code names:

Events:

Invariants:

## Flows

- <FlowName>: <what behavior it owns>

## Capabilities

- <CapabilityName>: <what reusable ability it owns>

## Aggregates

- <AggregateName>: <which invariants it protects>

## Entities

- <EntityName>: <identity and lifecycle>

## Value Objects

- <ValueObjectName>: <meaning and validation>

## Repositories

- <RepositoryName>: <which aggregate it persists>

## Domain Services

- <ActionName>: <which domain action it owns>

## Factories

- <CreateSomething>: <why construction needs ownership>

## Domain Events

- <SomethingHappened>: <what fact happened and who cares>

## Public Surface

<Public API terms, if any.>

## Forbidden Terms

- <WeakName>: <why it is forbidden>

## Proof

<Which tests or validation reports prove this behavior.>

## Open Questions

- <Question that still needs a domain decision>
```

---

## 51. Example: Compatibility Domain Model

This example shows DDD adapted to screaming architecture.

```text
components/
  Delivery/
    Compatibility/
      System/
        PublicSurface/
          CompatibilityInspector.php
          CompatibilityReport.php
          PublicApiVersion.php

        Flows/
          DetectBreakingPublicApiChange/
            DetectBreakingPublicApiChange.php
            BreakingPublicApiChangeDetected.php

          DeprecatePublicApi/
            DeprecatePublicApi.php
            PublicApiDeprecated.php

          DescribePublicApi/
            DescribePublicApi.php
            PublicApiDescription.php

        Capabilities/
          PublicApiCompatibility/
            PublicApi.php
            PublicApiId.php
            PublicApiSnapshot.php
            PublicApiVersion.php
            CompatibilityReport.php
            CompatibilityResult.php
            BreakingChange.php
            DeprecationNotice.php

          PublicApiVersioning/
            SupportedPublicApiVersions.php
            PublicApiVersionPolicy.php

          PublicApiSchemaDescription/
            BuildPublicApiSchema.php
            PublicApiSchema.php

        Configuration/
          BuildCompatibilitySystem.php

        Foundation/
          Comparison/
            Difference.php
```

Why this is good:

```text
Compatibility says what the component protects.
Flows say what happens.
Capabilities say what reusable abilities exist.
Units say exact responsibility.
Events are past-tense facts.
Value objects have domain meaning.
PublicSurface stays small.
```

Why this is better than `Contract`:

```text
Contract says a technical artifact exists.
Compatibility says users must not be broken accidentally.
```

---

## 52. Example: Release Readiness Domain Model

```text
components/
  Delivery/
    ReleaseReadiness/
      System/
        PublicSurface/
          ReleaseVerifier.php
          ReleaseReadinessReport.php

        Flows/
          VerifyReleaseReadiness/
            VerifyReleaseReadiness.php
            ReleaseReadinessVerified.php
            ReleaseReadinessRejected.php

          ApproveReleaseForProduction/
            ApproveReleaseForProduction.php
            ReleaseApprovedForProduction.php

        Capabilities/
          ReleaseReadiness/
            ReleaseCandidate.php
            ReleaseCandidateId.php
            ReleaseVersion.php
            ReadinessState.php
            ReleaseReadinessReport.php
            CalculateReleaseReadiness.php
            ReleaseCandidateRepository.php

          RollbackPlanning/
            RollbackPlan.php
            VerifyRollbackPlan.php

        Configuration/
          BuildReleaseReadinessSystem.php
```

Domain language:

```text
ReleaseCandidate:
The aggregate root for release approval state.

ReadinessState:
A value object that explains whether release conditions are green, yellow, or red.

CalculateReleaseReadiness:
A domain action that evaluates release gates.

ReleaseApprovedForProduction:
A domain fact that approval happened.
```

---

## 53. Example: Runtime Safety Domain Model

```text
framework/
  System/
    PublicSurface/
      RuntimeKernel.php
      RuntimeStatus.php

    Flows/
      HandleIncomingHttp/
        HandleIncomingHttp.php

      ResetApplicationState/
        ResetApplicationState.php
        ApplicationStateReset.php

      VerifyRuntimeSafety/
        VerifyRuntimeSafety.php
        RuntimeSafetyViolationDetected.php

    Capabilities/
      WorkerLifecycle/
        RuntimeWorker.php
        RuntimeWorkerId.php
        WorkerState.php

      RequestScope/
        RequestScope.php
        OpenRequestScope.php
        CloseRequestScope.php

      RuntimeSafety/
        RuntimeSafetyReport.php
        RuntimeSafetyRule.php
        VerifyNoRequestStateLeak.php

    Configuration/
      BuildRuntimeSystem.php
```

Domain language:

```text
RuntimeWorker:
Entity with identity and lifecycle.

RequestScope:
Capability that owns request-local state boundaries.

RuntimeSafetyReport:
Value object or report that describes whether long-lived runtime rules are satisfied.

RuntimeSafetyViolationDetected:
Domain event emitted when safety rules fail.
```

---

## 54. Example: Persistence Domain Model

Persistence and ORM are allowed to use DDD concepts only when they protect real persistence boundaries.

Bad:

```text
components/
  DataStack/
    Database/
      System/
        Domain/
          Entities/
          Repositories/
          Services/
```

Good:

```text
components/
  DataStack/
    Database/
      System/
        Capabilities/
          EntityPersistence/
            PersistEntity.php
            RefreshEntity.php
            RemoveEntity.php

          IdentityMap/
            PersistenceIdentityMap.php

          UnitOfWork/
            TrackEntityChange.php
            FlushTrackedChanges.php

          EntityMetadata/
            ReadEntityMetadata.php
            EntityMetadata.php

          EntityHydration/
            HydrateEntity.php
            ExtractEntityPayload.php
```

Why this is good:

```text
The folders say persistence capability.
The units say exact responsibility.
DDD concepts exist only where they protect identity, lifecycle, or persistence behavior.
```

---

# Part V: Combined Architecture Review

---

## 55. Combined Review Checklist

A design using `PublicSurface/` and DDD passes only if all of the following are true.

### Public Surface

```text
PublicSurface/ exists only when there is a real public API boundary.
Public classes are externally useful.
Public names are user-facing.
Public API is small.
Public API delegates real behavior internally.
No flow implementation lives in PublicSurface/.
No runtime-specific implementation leaks into PublicSurface/.
No request-scoped mutable state lives in PublicSurface/.
Public API stability and compatibility are documented.
PublicSurface behavior is proven by tests.
```

### DDD

```text
DDD concepts are used only where they clarify meaning.
Bounded contexts have clear language boundaries.
No generic root Domain/Entities/Services folders exist.
Folders still say flow or capability.
Units say domain responsibility.
Functions say exact domain action.
Aggregates protect real invariants.
Entities have identity and lifecycle.
Value objects carry real meaning.
Repositories persist aggregate boundaries.
Domain services have exact action names.
Factories own meaningful creation.
Domain events are past-tense facts.
Ubiquitous language is documented where needed.
No technical term is used when a clearer domain term exists.
Important behavior or invariants are proven by tests.
```

### Simplicity

```text
The design is easier to explain after the structure is applied.
The structure is not decorative.
Small code stays small.
Shared concepts are extracted only when sharing is honest.
Style does not override clarity.
Naming does not become academic.
```

### Recovery

```text
Old code is treated as evidence.
Old behavior is recovered into current architecture.
Old structure is not blindly restored.
Recovered behavior is mapped to V1/V2/V3/V4.
Recovered behavior has tests.
Skeleton-only recovery is rejected.
```

---

## 56. Final Law

Public surface receives.

Flows execute.

Capabilities power.

Configuration assembles.

Foundation supports.

Bounded contexts define where language is valid.

Aggregates protect invariants.

Entities own identity and lifecycle.

Value objects protect meaning.

Repositories protect aggregate persistence.

Domain services own exact domain actions.

Factories own meaningful creation.

Domain events record completed facts.

Old code is evidence.

Current architecture is the target.

Tests are the judge.

Everything must remain readable.

Everything must remain simple.

The system must speak in the language of what it does, what it protects, and what must never become unclear.
