# AvaX Framework Terms

Canonical technical dictionary for AvaX governance.

Each term includes: simple explanation, AvaX meaning, allowed usage, forbidden misuse, learning links.

---

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

**Status:** MANDATORY
**Purpose:** Define canonical meanings for technical terms used in AvaX class names, documentation, governance, and tests.

---

## Test Pyramid

**Simple explanation:** A principle for organizing tests by granularity: many fast focused tests at the base, fewer broad tests in the middle, very few end-to-end tests at the top.

**AvaX meaning:** A test portfolio principle, not a rigid layer naming scheme. AvaX requires: unit/behavior tests, component tests, integration tests, contract tests, architecture/governance tests, acceptance tests, and minimal E2E/canonical journeys.

**Allowed usage:** "Test pyramid" as a concept for test distribution, pipeline ordering by speed/scope, evidence showing test layer coverage.

**Forbidden misuse:** Treating the pyramid as dogma about exact ratios. Having all tests at one layer and calling it "our pyramid." Running E2E before focused tests.

**Learning:** `how-to-unit-test.md` — Section 91

---

## Unit Test

**Simple explanation:** A fast, focused test verifying one observable behavior of a single unit.

**AvaX meaning:** The base of the test pyramid. Fast, deterministic, behavior-focused. Verifies observable behavior, not internal implementation. One clear behavior per test.

**Allowed usage:** `testBehaviorReturnsCorrectValue`, `testRejectsInvalidInput`, behavior tests in the unit test suite.

**Forbidden misuse:** Tests that mirror internal call order. Tests that break on every refactor. Tests requiring extensive mocking of everything.

**Learning:** `how-to-unit-test.md` — Section 91.1, 91.3

---

## Component Test

**Simple explanation:** A test verifying a component works correctly with its real internal collaborators.

**AvaX meaning:** Tests a complete component (PublicSurface through Capabilities) with real internal dependencies. Fewer than unit tests, more confidence than isolated unit tests.

**Allowed usage:** `testComponentProcessesValidRequest`, component tests exercising PublicSurface through to Capabilities.

**Forbidden misuse:** Component tests that mock every internal collaborator. Component tests that are just slower unit tests.

**Learning:** `how-to-unit-test.md` — Section 91.1

---

## Integration Test

**Simple explanation:** A test verifying interaction with external systems: database, filesystem, network, runtime, or vendor APIs.

**AvaX meaning:** Separate from unit behavior tests. Fewer, clear, deterministic. Tests real infrastructure or protected fakes. Does not duplicate edge cases from unit tests.

**Allowed usage:** `testDatabasePersistsAndRetrievesUser`, `testFilesystemWritesAndReadsMetadata`, `testRuntimeHandlesWorkerReset`.

**Forbidden misuse:** Integration tests that duplicate every unit test edge case. Integration tests that are flaky or nondeterministic.

**Learning:** `how-to-unit-test.md` — Section 91.8

---

## Contract Test

**Simple explanation:** A test verifying that an API or boundary meets consumer expectations.

**AvaX meaning:** Required for PublicSurface APIs, component boundaries, domain events, generated metadata, runtime adapters, and external integrations. Consumer expectations must be executable. Breaking contracts fail fast in CI.

**Allowed usage:** `testPublicSurfaceAcceptsNaturalInputs`, `testEventSchemaIsStable`, `testAdapterMeetsExternalContract`.

**Forbidden misuse:** Contract tests that verify internal implementation. Contract tests that drift from the real implementation without detection.

**Learning:** `how-to-unit-test.md` — Section 91.7

---

## Acceptance Test

**Simple explanation:** A test proving that a feature or business behavior works from the user/system perspective.

**AvaX meaning:** Closer to the top of the pyramid. Describes what works, not how it works internally. Uses ubiquitous language. May use Given/When/Then format.

**Allowed usage:** `testUserCanRegisterAndLogin`, `testTenantIsolationPreventsCrossAccess`, acceptance tests in Given/When/Then format.

**Forbidden misuse:** Acceptance tests that describe internal implementation steps. Acceptance tests that are just slower integration tests.

**Learning:** `how-to-unit-test.md` — Section 91.10

---

## End-to-End Test

**Simple explanation:** A test exercising a complete user/system journey through the entire stack.

**AvaX meaning:** The top of the pyramid. Expensive and flaky. Covers only critical journeys. Does not duplicate lower-level edge cases.

**Allowed usage:** `testCriticalUserJourneyFromLoginToLogout`, canonical journey tests for the most important system flows.

**Forbidden misuse:** E2E tests that cover every edge case. E2E suite larger than the unit test suite. E2E tests that duplicate unit/component test scenarios.

**Learning:** `how-to-unit-test.md` — Section 91.9

---

## Test Double

**Simple explanation:** A substitute for a real dependency used in tests.

**AvaX meaning:** An umbrella term for fakes, stubs, mocks, and spies. Each type serves a different purpose and must be used intentionally. Not all doubles are mocks.

**Allowed usage:** "Test double" as a general concept when discussing test strategy.

**Forbidden misuse:** Using "mock" to mean all test doubles. Not distinguishing between fake, stub, mock, and spy.

**Learning:** `how-to-unit-test.md` — Section 91.6

---

## Fake

**Simple explanation:** A working implementation suitable for tests but not production (e.g., in-memory database).

**AvaX meaning:** A real-enough implementation for testing slow/external dependencies. Must be protected by contract tests against the real implementation to prevent drift.

**Allowed usage:** `InMemoryCache`, `FakeEmailService`, `InMemoryDatabase` — working implementations used in tests.

**Forbidden misuse:** Fakes without contract tests protecting them. Fakes used in production code. Fakes that silently behave differently from the real implementation.

**Learning:** `how-to-unit-test.md` — Section 91.6

---

## Stub

**Simple explanation:** A test double providing pre-programmed responses with no assertion on how it was called.

**AvaX meaning:** Used when a test needs specific input from a dependency but does not care how the dependency was called. No call verification.

**Allowed usage:** `StubClock::fixedNow('2024-01-01')`, `StubRandom::alwaysReturns(0.5)`.

**Forbidden misuse:** Using a stub when the test needs to verify that a dependency was called correctly (use a mock instead).

**Learning:** `how-to-unit-test.md` — Section 91.6

---

## Mock

**Simple explanation:** A test double pre-programmed with expectations that verifies call behavior.

**AvaX meaning:** Used when the test needs to verify that a dependency was called with specific arguments, a specific number of times. Fails if expectations are not met.

**Allowed usage:** `MockLogger::expectsLog('user_registered')`, `MockEventDispatcher::expectsDispatch(UserRegistered::class)`.

**Forbidden misuse:** Mocking everything. Using a mock when a stub would suffice. Mocking internal implementation details that should be tested through behavior.

**Learning:** `how-to-unit-test.md` — Section 91.6

---

## Spy

**Simple explanation:** A test double that records calls for later verification.

**AvaX meaning:** Used when the test needs to verify calls after the fact, rather than setting expectations beforehand. More flexible than mocks.

**Allowed usage:** `SpyLogger::getLoggedMessages()`, `SpyEventDispatcher::getDispatchedEvents()` — verified after the action.

**Forbidden misuse:** Using a spy when call order matters and must fail fast (use a mock instead).

**Learning:** `how-to-unit-test.md` — Section 91.6

---

## Characterization Test

**Simple explanation:** A test that captures current behavior before a large refactor, serving as a safety net.

**AvaX meaning:** Written before large refactors or rewrites. Captures "what the code does now" without judging whether it's correct. A safety net, not a design guide.

**Allowed usage:** Tests written before splitting a god class, before extracting a subsystem, before rewriting a flow.

**Forbidden misuse:** Characterization tests that become permanent design tests. Characterization tests treated as specification rather than safety net.

**Learning:** `how-to-unit-test.md` — Section 91.14

---

## Fast Feedback

**Simple explanation:** The principle that validation should provide the quickest possible signal about whether a change is safe.

**AvaX meaning:** Pipeline stages ordered by speed and scope. Fast tests (syntax, static analysis, focused unit tests) run first. Slow tests (integration, E2E) run last. Agents must follow this order.

**Allowed usage:** "Fast feedback pipeline" describing the test execution order. Evidence showing focused tests ran before broad tests.

**Forbidden misuse:** Running E2E before focused tests. Running all tests when only one unit changed. Ignoring the speed/scope ordering principle.

**Learning:** `how-to-unit-test.md` — Section 91.2

---

## Flaky Test

**Simple explanation:** A test that sometimes passes and sometimes fails without code changes.

**AvaX meaning:** A production risk. Flaky tests destroy confidence in the test suite and pipeline. E2E tests are most prone. Flaky tests must be fixed, deleted, or quarantined — never ignored.

**Allowed usage:** "Flaky test" as a classification for unreliable tests. Quarantine flags for known flaky tests.

**Forbidden misuse:** Ignoring flaky tests. Accepting flaky E2E as "just how it is." Running flaky tests until they pass and calling it GREEN.

**Learning:** `how-to-unit-test.md` — Section 91.9

Dictionary of terms related to Separation of Concern governance.

---

## Separation of Concern (SoC)

**Simple:** Split things that change for different reasons.

**AvaX meaning:** Universal principle behind modularization, encapsulation, functions, objects, layering, flows, capabilities, and subsystems. Not a pattern — the reason patterns exist.

**Allowed:** "SoC at function/class/capability/component level", "SoC review lens", "change-axis test"

**Forbidden:** Using SoC to justify file shuffling without reducing coupling

**Learning:** [emlandre.com — On Separation of Concern](https://emlandre.com/2024/01/05/on-separation-of-concern-soc/)

---

## Concern

**Simple:** A reason to care about a unit of code.

**AvaX meaning:** Any reason to understand, change, test, deploy, secure, observe, configure, or evolve a unit. A concern maps to a single owner.

**Allowed:** "This class has one concern", "cross-cutting concern", "concern ownership"

**Forbidden:** Using "concern" to mean "any piece of code" without a change driver

---

## Cross-Cutting Concern

**Simple:** Something that affects many parts of the system.

**AvaX meaning:** Security, logging, validation, caching, transactions, observability, reset safety, configuration, error handling. Must be explicit capabilities or policies, not scattered helpers.

**Allowed:** "explicit cross-cutting capability", "observability boundary", "security policy"

**Forbidden:** "just add a helper function", inline security logic, scattered logging patterns

**Learning:** [how-to-architecture.md — Section 57.6](../.agents/how-to/architecture/how-to-architecture.md)

---

## Change Axis

**Simple:** A direction in which code evolves.

**AvaX meaning:** A specific reason or driver that causes a unit to change. Multiple unrelated change axes in one unit means bad SoC.

**Allowed:** "change-axis test", "what causes this to change", "related change drivers"

**Forbidden:** Ignoring change axes when evaluating class size or complexity

---

## Coupling

**Simple:** How much one unit depends on another.

**AvaX meaning:** Coupling is not automatically evil. Coupling must be explicit, intentional, and balanced. Assessed through knowledge exchanged, integration strength, physical/logical distance, change frequency, and co-change pressure. Bad coupling is hidden, intrusive, or accidental. Good coupling matches logical closeness and has documented integration knowledge.

**Balanced coupling dimensions:** Afferent/Efferent (inward/outward), Temporal (time-based coordination), Semantic (shared knowledge/contracts), Intrusive (implementation detail leakage).

**Allowed:** "coupling direction", "low coupling", "coupling reduction", "balanced coupling", "explicit coupling", "intentional coupling", "intrusive coupling", "change-together"

**Forbidden:** Assuming all coupling is bad, hiding coupling behind physical distance, creating intrusive coupling on implementation details

**Learning:** [how-to-architecture.md — Section 58](../.agents/how-to/architecture/how-to-architecture.md), [how-to-code-review.md — Section 27](../.agents/how-to/verification/how-to-code-review.md)

---

## Cohesion

**Simple:** How well the parts of a unit belong together.

**AvaX meaning:** High cohesion means methods, properties, and decisions in a unit share a conceptual center. SoC improves cohesion by removing unrelated work. Cohesion and coupling trade off against each other — improving one may worsen the other. The goal is balanced cohesion: high enough for clarity, not so high that it creates isolation and duplicated knowledge.

**Allowed:** "high cohesion", "conceptual center", "cohesive responsibility", "balanced cohesion"

**Forbidden:** Claiming cohesion while storing unrelated logic in one unit, maximizing cohesion at the cost of global complexity

**Learning:** [how-to-architecture.md — Section 58](../.agents/how-to/architecture/how-to-architecture.md)

---

## Local Complexity

**Simple:** How hard it is to understand one specific unit (function, class, module).

**AvaX meaning:** Local complexity is the cognitive load of reasoning about one unit in isolation. Reducing local complexity is good only when it does not increase global complexity. A unit that appears simpler because the system became harder to understand has not been improved.

**Allowed:** "reduced local complexity", "local simplicity"

**Forbidden:** Reducing local complexity by increasing global complexity

**Learning:** [how-to-architecture.md — Section 58.3](../.agents/how-to/architecture/how-to-architecture.md), [how-to-clean-code.md — Section 5.16.1](../.agents/how-to/implementation/how-to-clean-code.md)

---

## Global Complexity

**Simple:** How hard it is to understand the whole system.

**AvaX meaning:** Global complexity is the total cognitive load of reasoning about how all units work together. Modularity must reduce global complexity, not increase it. Extracting indirection, creating micro-abstractions, or hiding coordination behind events may reduce local complexity but increase global complexity — this is a failed refactoring.

**Allowed:** "reduced global complexity", "system-wide understanding"

**Forbidden:** Increasing global complexity to reduce local complexity

**Learning:** [how-to-architecture.md — Section 58.3](../.agents/how-to/architecture/how-to-architecture.md), [how-to-clean-code.md — Section 5.16.1](../.agents/how-to/implementation/how-to-clean-code.md)

---

## Intrusive Coupling

**Simple:** Depending on another unit's implementation details, not its published contract.

**AvaX meaning:** Intrusive coupling occurs when a unit depends on storage layout, private state shape, private workflow order, lifecycle internals, or unpublished invariants of another unit. It is invisible in contracts but fragile in practice — renaming an internal class breaks a dependent. This is the most dangerous form of coupling because it is hidden.

**Allowed:** Dependencies on published contracts, explicit interfaces, documented boundary value objects

**Forbidden:** Dependencies on internal class names, internal state shape, internal workflow order, lifecycle internals, storage layout

**Learning:** [how-to-architecture.md — Section 58.9](../.agents/how-to/architecture/how-to-architecture.md), [how-to-dependency-injection.md — Section 7.7](../.agents/how-to/implementation/how-to-dependency-injection.md)

---

## Integration Strength

**Simple:** How tightly two units are bound together through their connection mechanism.

**AvaX meaning:** Integration strength measures the binding mechanism between units: direct call (strongest), interface, event, published contract (weakest). Strong integration is justified when units are logically close and share a change axis. Strong integration between independent units is wrong. The integration strength must match the logical closeness.

**Allowed:** "strong integration for shared change axis", "weak integration for independent units"

**Forbidden:** Strong integration between units that change independently, weak integration for units that always change together

**Learning:** [how-to-architecture.md — Section 58.5](../.agents/how-to/architecture/how-to-architecture.md)

---

## Change-Together Smell

**Simple:** Multiple modules requiring simultaneous edits for the same change.

**AvaX meaning:** Co-change pressure is the strongest signal that ownership is split incorrectly. When a single feature requires touching 3+ modules outside the same flow or capability, the decomposition is suspect. Frequent co-change across module boundaries is a decomposition problem, not a coordination problem.

**Allowed:** Co-change within the same flow or capability, documented co-change for shared change axis

**Forbidden:** Frequent co-change across unrelated modules without ownership reassessment

**Learning:** [how-to-architecture.md — Section 58.6](../.agents/how-to/architecture/how-to-architecture.md)

---

## Physical Distance

**Simple:** Putting modules in different folders, packages, namespaces, processes, or repositories.

**AvaX meaning:** Physical distance is organizational, not architectural. Different folders, packages, or processes do not remove coupling if knowledge leaks between them. If two modules communicate through implementation details, they are coupled regardless of distance. Physical distance that masks coupling is worse than honest colocation.

**Allowed:** Physical distance that matches logical distance — separation because knowledge does NOT leak

**Forbidden:** Using physical distance as evidence of decoupling when knowledge still flows

**Learning:** [how-to-architecture.md — Section 58.7](../.agents/how-to/architecture/how-to-architecture.md)

---

## Decomposition Principle

**Simple:** The documented reason why a module boundary exists.

**AvaX meaning:** A decomposition principle answers: why does this boundary exist, what change axis does it protect, what knowledge does it isolate, what cognitive load does it reduce? Good reasons: change locality, information ownership, cohesive behavior, predictable impact. Bad reasons: file is long, technical category, constructor size, framework layer.

**Allowed:** "change locality", "information ownership", "cohesive behavior", "security boundary", "runtime boundary"

**Forbidden:** Creating boundaries because "the file is long", "by technical category", "by framework layer"

**Learning:** [how-to-architecture.md — Section 58.10](../.agents/how-to/architecture/how-to-architecture.md), [how-to-clean-code.md — Section 5.16.3](../.agents/how-to/implementation/how-to-clean-code.md)

---

## Modularity

**Simple:** How a system is divided into modules with clear boundaries.

**AvaX meaning:** The purpose of modularity is not separation — it is predictable change. A module boundary is correct only when change location is obvious, change impact is predictable, integration knowledge is explicit, and cognitive load is reduced. Modules that are separate but require simultaneous changes have failed the modularity test.

**Allowed:** "predictable change", "obvious change location", "explicit integration knowledge", "reduced cognitive load"

**Forbidden:** Creating modules that are separate but change together, modularity that increases global complexity

**Learning:** [how-to-architecture.md — Section 58.4](../.agents/how-to/architecture/how-to-architecture.md), [how-to-clean-code.md — Section 5.16.2](../.agents/how-to/implementation/how-to-clean-code.md)

---

## Composition Boundary

**Simple:** Where separated parts come together.

**AvaX meaning:** After separating concerns, composition must happen through clear parent, gateway, or configuration boundaries. Not through god builders or god graphs.

**Allowed:** "clean composition boundary", "configuration boundary", "gateway composition"

**Forbidden:** "just wire everything together", god builders, service locator composition

**Learning:** [how-to-architecture.md — Section 57.7](../.agents/how-to/architecture/how-to-architecture.md)
