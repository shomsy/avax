# AvaX Framework Technical Dictionary

**Status:** MANDATORY
**Purpose:** Define canonical meanings for technical terms used in AvaX class names, documentation, and governance.

When a technical term appears in a class name, PHPDoc, or governance document, its meaning is defined here.

---

## Runtime

**Simple explanation:** The executing application lifecycle environment.

**AvaX meaning:** The set of lifecycle phases, request scope, worker state, and execution context that an application moves through (boot, request, worker, reset, shutdown).

**Allowed usage:** `AccessRuntime`, `BuildAuthRuntime`, `runtime()` method returning runtime state.

**Forbidden misuse:** Using "Runtime" as a dumping ground for unrelated behavior.

**Learning:** `how-to-runtime-composition.md`

---

## PublicSurface

**Simple explanation:** The stable public API entrypoints that receive and delegate.

**AvaX meaning:** The thin boundary layer that accepts natural inputs, normalizes them, and delegates to internal Flows or Capabilities. Must not own machinery.

**Allowed usage:** `PublicSurface/` folder, facade classes.

**Forbidden misuse:** Putting business logic or object graph assembly in PublicSurface.

**Learning:** `how-to-architecture.md`, `how-to-design-components.md`

---

## Capability

**Simple explanation:** A reusable ability or mechanism that supports multiple flows.

**AvaX meaning:** A shared system boundary or reusable behavior that multiple flows depend on.

**Allowed usage:** `Capabilities/` folder, named capability classes.

**Forbidden misuse:** Using "Capability" as a folder name for technical categories.

**Learning:** `how-to-architecture.md`

---

## Flow

**Simple explanation:** A complete end-to-end action or use case.

**AvaX meaning:** A single narrative of behavior from start to finish. Replaces "UseCase" terminology.

**Allowed usage:** `Flows/` folder, action-named flow classes.

**Forbidden misuse:** Using "Flow" as a suffix (`UserFlow`). Flow names should be actions.

**Learning:** `how-to-architecture.md`

---

## Provider

**Simple explanation:** A registration and assembly unit for dependencies.

**AvaX meaning:** A class that registers bindings, configures defaults, and declares component dependencies in the container.

**Allowed usage:** `AuthServiceProvider`, `register()` method.

**Forbidden misuse:** Provider classes that execute runtime behavior.

**Learning:** `how-to-dependency-injection.md`

---

## Builder

**Simple explanation:** A class that assembles an object graph at configuration time.

**AvaX meaning:** Configuration-time assembly that constructs dependency graphs. Must live in `Configuration/` or `Configuration/Builders/`.

**Allowed usage:** `BuildAuthRuntime`, `ConfigureAuth`.

**Forbidden misuse:** Builder classes in runtime folders or acting as service locators.

**Learning:** `how-to-design-components.md`, `how-to-dependency-injection.md`

---

## Factory

**Simple explanation:** A class that creates domain objects or runtime results.

**AvaX meaning:** Result-creation boundary. Distinct from Builder: Factory creates runtime results; Builder assembles dependency graphs.

**Allowed usage:** `TokenFactory`, `ResponseFactory`.

**Forbidden misuse:** Factory classes that assemble dependency graphs.

**Learning:** `how-to-dependency-injection.md`

---

## Assembly

**Simple explanation:** The act of composing and wiring dependencies together.

**AvaX meaning:** Configuration-time dependency graph construction. Belongs in `Configuration/`, not runtime.

**Allowed usage:** `Assembly/` subfolder within `Configuration/`.

**Forbidden misuse:** `*Assembly` class names in runtime folders.

**Learning:** `how-to-dependency-injection.md`

---

## Graph

**Simple explanation:** A dependency graph assembled at configuration time.

**AvaX meaning:** The complete set of interconnected dependencies forming a component's runtime.

**Allowed usage:** `TokensGraph` (in Configuration/Assembly/ only).

**Forbidden misuse:** `*Graph` class names outside Configuration/Assembly/.

**Learning:** `how-to-dependency-injection.md`

---

## DSL

**Simple explanation:** A domain-specific language — fluent API surface for human readability.

**AvaX meaning:** User-facing fluent configuration or query APIs. Not a class name suffix.

**Allowed usage:** DSL as a concept, fluent method chains.

**Forbidden misuse:** `*Dsl` class name suffixes.

**Learning:** `how-to-architecture.md`, `how-to-dependency-injection.md`

---

## Facade

**Simple explanation:** A thin public entrypoint that delegates to internal owners.

**AvaX meaning:** A stable public API class that receives natural inputs and delegates internally.

**Allowed usage:** `Cache` facade, `Auth` facade.

**Forbidden misuse:** Facades that accumulate ad-hoc behavior.

**Learning:** `how-to-design-components.md`

---

## Policy

**Simple explanation:** A rule or decision about what is allowed.

**AvaX meaning:** An authorization, access-control, or behavioral rule unit.

**Allowed usage:** `RequirePermission`, `RateLimitPolicy`.

**Forbidden misuse:** Policy classes that own orchestration or IO.

**Learning:** `how-to-architecture.md`

---

## Token

**Simple explanation:** A cryptographic or session-based credential for authentication/authorization.

**AvaX meaning:** JWT, access tokens, refresh tokens, CSRF tokens owned by the Identity/Tokens subsystem.

**Allowed usage:** `TokenSigner`, `TokenValidator`.

**Forbidden misuse:** Token classes that own authentication flows.

**Learning:** Identity subsystem governance

---

## Session

**Simple explanation:** A stateful interaction context between user/system and application.

**AvaX meaning:** HTTP session state, worker session context, or authentication session tracking.

**Allowed usage:** `SessionRegistry`, `SessionExpiry`.

**Forbidden misuse:** Session classes that own authentication decisions.

**Learning:** Identity subsystem governance

---

## Credential

**Simple explanation:** Proof of identity — passwords, keys, certificates, passkeys.

**AvaX meaning:** Authentication proof material owned by the Identity/Credentials subsystem.

**Allowed usage:** `CredentialAuthority`, `CredentialValidator`.

**Forbidden misuse:** Credential classes that own session management.

**Learning:** Identity subsystem governance

---

## Tenant

**Simple explanation:** A multi-tenant isolation boundary — organization, account, workspace.

**AvaX meaning:** The unit of data and access isolation in multi-tenant systems.

**Allowed usage:** `TenantAccess`, `TenantIsolation`, `TenantContext`.

**Forbidden misuse:** Tenant classes that own user authentication.

**Learning:** Identity subsystem governance

---

## Elevation

**Simple explanation:** Temporary privilege increase — admin elevation, role escalation.

**AvaX meaning:** A controlled, auditable, time-limited increase in user permissions.

**Allowed usage:** `ElevationRequest`, `RequireElevation`.

**Forbidden misuse:** Elevation classes that own base authentication.

**Learning:** Identity subsystem governance

---

## Risk

**Simple explanation:** A measurable threat or uncertainty factor in security decisions.

**AvaX meaning:** A quantified security risk score used in adaptive authentication and authorization.

**Allowed usage:** `RiskAssessment`, `RiskScore`, `RiskPolicy`.

**Forbidden misuse:** Risk classes that own authentication.

**Learning:** Identity subsystem governance

---

## Object-Oriented Thinking

**Simple explanation:** Modeling real-world and system complexity as interacting objects with clear responsibilities.

**AvaX meaning:** Classes must represent meaningful concepts: roles, tasks, information, views, commands, events, aggregates, policies, capabilities, or transformations. Not pattern names.

**Allowed usage:** `CredentialAuthority`, `SessionRegistry`, `RiskAssessment` — classes that represent real domain concepts.

**Forbidden misuse:** `UserManagerHelper`, `AbstractBaseComponentHandler` — classes that exist only because a pattern name is available.

**Learning:** `how-to-design-components.md` — Section 30.1

---

## Ubiquitous Language

**Simple explanation:** Names that make sense to both developers and domain stakeholders, supporting conversation.

**AvaX meaning:** Class, folder, and API names must be understood by stakeholders, not just compile correctly. Names support conversation.

**Allowed usage:** `CredentialAuthority` (stakeholders understand credentials and authority), `SessionRegistry` (sessions and registration).

**Forbidden misuse:** `AbstractBaseComponentHandler`, `GenericServiceProcessor` — names that only make sense as framework mechanics.

**Learning:** `how-to-design-components.md` — Section 30.4

---

## Bounded Context

**Simple explanation:** A clear boundary within which a particular domain model is valid and consistent.

**AvaX meaning:** A major subsystem with owned concepts, consumed concepts, published APIs/events, and explicit handover contracts. Maps to `System/` folder boundaries.

**Allowed usage:** Identity bounded context, Cache bounded context — each with clear ownership and published contracts.

**Forbidden misuse:** Creating bounded contexts for every class. A bounded context must own meaningful information and behavior.

**Learning:** `how-to-design-components.md` — Section 30.7, `how-to-architecture-extension-with-ddd.md`

---

## Context Map

**Simple explanation:** A document showing relationships, coupling, and information flow between bounded contexts.

**AvaX meaning:** Evidence showing upstream/downstream relationships, handover contracts, accepted coupling, and information ownership between subsystems.

**Allowed usage:** Context map evidence file for Identity redesign showing upstream (ExternalLogin) and downstream (SessionRegistry) relationships.

**Forbidden misuse:** Stale context maps that do not match current code. A stale map is worse than no map.

**Learning:** `how-to-design-components.md` — Section 30.7

---

## EventStorming

**Simple explanation:** A collaborative discovery technique that models system behavior through events, commands, and aggregates.

**AvaX meaning:** For complex flows: discover events first, derive commands, identify aggregate owners, map handovers, then write code. Required for Identity/Auth/Tokens/Tenancy/Risk redesigns.

**Allowed usage:** Event list, command list, aggregate ownership map, and handover inventory captured in evidence before coding complex flows.

**Forbidden misuse:** Using EventStorming output as decoration. EventStorming informs design; it does not replace code.

**Learning:** `how-to-design-components.md` — Section 30.5

---

## Command

**Simple explanation:** An intent to perform an action that may change state.

**AvaX meaning:** A unit that expresses "do this" — derived from events in EventStorming, executed by Flows. Commands mutate state.

**Allowed usage:** `RegisterUser` command (executed by Flow), `ChangePassword` command.

**Forbidden misuse:** Command classes that both mutate and return data without explicit result object.

**Learning:** `how-to-design-components.md` — Section 30.12

---

## Domain Event

**Simple explanation:** A fact that something happened in the domain, expressed in the past tense.

**AvaX meaning:** A named fact representing completed domain behavior. Used for EventStorming discovery, event sourcing, and cross-context communication.

**Allowed usage:** `UserRegistered`, `PasswordChanged`, `SessionExpired` — past-tense facts representing domain behavior.

**Forbidden misuse:** `EventFactoryBuilderProxy` — events named after patterns, not domain facts.

**Learning:** `how-to-design-components.md` — Section 30.3, `how-to-architecture-extension-with-ddd.md`

---

## Aggregate

**Simple explanation:** A cluster of domain objects treated as a single unit for data changes, with one root entity protecting invariants.

**AvaX meaning:** An information owner and invariant protector. In AvaX, maps to a Capability with clear ownership of data and rules.

**Allowed usage:** `UserAggregate` owns user data, validates invariants, and coordinates related entities.

**Forbidden misuse:** Aggregates that span multiple bounded contexts or own unrelated concepts.

**Learning:** `how-to-architecture-extension-with-ddd.md`

---

## CQRS

**Simple explanation:** Command Query Responsibility Segregation — separating state-changing operations from read operations.

**AvaX meaning:** Separate commands that mutate state from queries that read state. Not applied as ceremony — used to clarify state change versus knowledge access.

**Allowed usage:** `RegisterUser` (command) vs `GetUserProfile` (query), separate flows for write and read paths.

**Forbidden misuse:** Creating separate command/query infrastructure for simple CRUD where no benefit exists.

**Learning:** `how-to-design-components.md` — Section 30.12

---

## Data Mesh

**Simple explanation:** An architectural approach treating data as a product owned by domain teams, consumed by others through stable interfaces.

**AvaX meaning:** When AvaX exposes data across components/contexts, treat it as a product: owned, documented, stable, consumer-oriented, transformed for consumer needs.

**Allowed usage:** Identity risk signals exposed as documented events, telemetry data as versioned products.

**Forbidden misuse:** Dumping raw internal state across boundaries and calling it "data mesh."

**Learning:** `how-to-design-components.md` — Section 30.13

---

## Data Product

**Simple explanation:** A unit of data that is owned, documented, stable, and designed for consumer needs.

**AvaX meaning:** Data exposed across component/context boundaries with clear producer ownership, consumer orientation, stable API, and versioning where needed.

**Allowed usage:** Domain events as data products, identity risk signals as data products, metadata as data products.

**Forbidden misuse:** Raw database rows or internal arrays exposed to consumers as "data products."

**Learning:** `how-to-design-components.md` — Section 30.13

---

## Event Sourcing

**Simple explanation:** A persistence strategy storing changes as a sequence of events rather than current state.

**AvaX meaning:** An architectural option for auditability, state reconstruction, time-travel debugging, or change history. Distinct from EventStorming (discovery technique).

**Allowed usage:** Storing identity audit events as event history, reconstructing session state from event log.

**Forbidden misuse:** Using Event Sourcing as ceremony where simple state persistence is sufficient. Conflating with EventStorming.

**Learning:** `how-to-design-components.md` — Section 30.14

---

## View

**Simple explanation:** A consumer-specific interface exposing knowledge without leaking internal models.

**AvaX meaning:** A first-class architecture artifact: public API, workspace, query surface, command surface, transformation boundary, event processing boundary, or communication bridge.

**Allowed usage:** PublicSurface as a view, query API as a view, workspace endpoint as a view.

**Forbidden misuse:** Views that expose internal domain models. Views should be consumer-oriented, not producer-convenient.

**Learning:** `how-to-design-components.md` — Section 30.11

---

## Knowledge Backbone

**Simple explanation:** The explicit organization of key information objects, their ownership, and transformations in a system.

**AvaX meaning:** Important systems must have explicitly named and owned knowledge: key information objects, ownership, transformations, update flows, consumers, and views.

**Allowed usage:** Identity subsystem documenting: User (owned by CredentialAuthority), Session (owned by SessionRegistry), Token (owned by TokenAuthority).

**Forbidden misuse:** Core knowledge accidentally trapped inside `UserManager`, `AuthService`, or configuration wrappers without explicit ownership.

**Learning:** `how-to-design-components.md` — Section 30.9

---

## Handover

**Simple explanation:** The transfer of information across subsystem or context boundaries — a primary architectural risk point.

**AvaX meaning:** Every boundary crossing where information, state, or responsibility changes ownership. Must be documented, contracted, and tested.

**Allowed usage:** Handover evidence documenting: what crosses, who owns it, what can be lost, what transforms, what contract protects, what tests prove.

**Forbidden misuse:** Hidden handovers where information crosses boundaries without contract or test. "It just works" is not a handover strategy.

**Learning:** `how-to-design-components.md` — Section 30.10

---

## Architectural Quanta

**Simple explanation:** The smallest deployable or independently evolvable unit of architecture.

**AvaX meaning:** Bounded contexts, views, or capabilities that could become independently deployable units. Design boundaries should keep this option possible.

**Allowed usage:** Designing Identity subsystem as a potential independent deployment unit, with stable APIs and event contracts.

**Forbidden misuse:** Creating hard runtime dependencies that prevent independent evolution, without acknowledging the tradeoff.

**Learning:** `how-to-design-components.md` — Section 30.21

---

## Claims-Based SWOT

**Simple explanation:** A structured way to evaluate architectural alternatives by recording claims about strengths, weaknesses, opportunities, and threats.

**AvaX meaning:** For major architecture choices, record: distributed vs centralized options, SWOT analysis, assumptions, evidence, tactical feasibility. Decisions must not be opinion-only.

**Allowed usage:** Architecture decision document for Identity redesign recording: centralized auth service (strengths/weaknesses) vs distributed auth capabilities (strengths/weaknesses).

**Forbidden misuse:** "We chose distributed because it's modern" without recording claims, evidence, or tradeoffs.

**Learning:** `how-to-design-components.md` — Section 30.16, Section 30.20

---

## Tactical Design

**Simple explanation:** Code-level, implementation-detail design that proves strategic architecture decisions are feasible.

**AvaX meaning:** LLD and code-level evidence showing that a strategic architecture choice actually works in practice. The devil is in the details.

**Allowed usage:** Prototype code proving that distributed auth capabilities can maintain session consistency. LLD proving that Event Sourcing prototype handles replay correctly.

**Forbidden misuse:** Strategic architecture diagrams without any code-level proof. "It should work" is not tactical evidence.

**Learning:** `how-to-design-components.md` — Section 30.17

---

## Strategic Design

**Simple explanation:** High-level architecture decisions about system boundaries, slicing, context maps, and major technology choices.

**AvaX meaning:** Architecture decisions about problem-space slicing, bounded contexts, context maps, distributed vs centralized choices, and major technology bets.

**Allowed usage:** Identity redesign strategic design: bounded context boundaries, upstream/downstream relationships, session ownership decisions.

**Forbidden misuse:** Strategic design without tactical evidence. High-level diagrams alone are not GREEN architecture.

**Learning:** `how-to-design-components.md` — Section 30.17

---

## IRTV

**Simple explanation:** Information, Roles, Tasks, Views — a modeling framework for complex subsystems.

**AvaX meaning:** A modeling checklist: Information (what knowledge matters?), Roles (who/what uses it?), Tasks (what work is performed?), Views (what interfaces expose it?). Guides subsystem naming, PublicSurface design, flow boundaries.

**Allowed usage:** IRTV analysis for Identity redesign: Information (User, Session, Token), Roles (CredentialAuthority, SessionRegistry, TokenAuthority), Tasks (Verify, Issue, Revoke), Views (Auth API, Session API, Token API).

**Forbidden misuse:** Filling out IRTV as a checkbox exercise without using it to guide actual design decisions.

**Learning:** `how-to-design-components.md` — Section 30.8

---

## Transformation Recipe

**Simple explanation:** A mental model understanding components as repeatable transformation machines: input → recipe → output → preserved knowledge.

**AvaX meaning:** A way to think about flows, policies, compilers, metadata graphs, and runtime plans. Not a naming convention — a thinking tool.

**Allowed usage:** Understanding the Auth flow as: input (credentials) → recipe (verify, check MFA, assess risk) → output (session, token) → preserved knowledge (session state, audit log).

**Forbidden misuse:** Creating `Constructor` or `Recipe` classes because of this mental model. It is a way of thinking, not a naming reason.

**Learning:** `how-to-design-components.md` — Section 30.18
