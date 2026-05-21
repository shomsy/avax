# AvaX Framework Technical Dictionary

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
