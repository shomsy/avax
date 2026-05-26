# Test Strategy — Risk-Based Behavioral Testing

**Version:** 2.0.0
**Status:** Current
**Scope:** All AvaX production code, tests, validation pipelines, and agent output

## Core Philosophy

Coverage percentage is NOT truth.
Behavioral proof is truth.

AvaX V1 optimizes for:

- architectural velocity
- critical behavioral confidence
- runtime safety
- security invariants
- meaningful branch coverage

NOT:

- meaningless line coverage inflation
- boilerplate tests
- fake confidence

## Testing Layers

- **Unit/Behavior tests:** Fast, focused, one behavior per test. Verify observable behavior, not implementation.
- **Component tests:** Test component with real internal collaborators.
- **Integration tests:** Database, filesystem, network, runtime, external integration.
- **Contract tests:** PublicSurface APIs, component boundaries, events, generated metadata.
- **Architecture/Governance tests:** Canonical shape, naming, dependency direction, composition leaks.
- **Acceptance tests:** Feature/business behavior from user/system perspective.
- **E2E/Canonical journeys:** Critical user/system journeys only.

## Required Test Types per Unit

Every meaningful production unit MUST have applicable tests from:

| Category | What It Proves |
|----------|---------------|
| Happy path | Works when all inputs are valid |
| Failed-when / Sad path | Rejects invalid conditions intentionally |
| Validation path | Rejects malformed/boundary data correctly |
| Security path | Fails closed under attack or misuse |
| Runtime/Lifecycle | State safety in long-lived workers |

A unit with only happy-path coverage is NOT proven.

## Forbidden Patterns

The following are FORBIDDEN unless explicitly accepted as YELLOW debt:

- assertTrue(true)
- meaningless not-null assertions
- constructor-only tests without behavior
- getter/setter-only tests
- coverage-padding tests
- implementation-detail obsession
- mocking the entire subject under test
- tests that only prove execution
- tests written only to increase percentages

## Coverage Policy by Phase

### V1 (Active Development)

- 100% line coverage NOT required
- meaningful behavioral confidence REQUIRED
- branch/path coverage preferred over line coverage
- critical flows MUST be tested

### Production Hardening

- coverage may be increased toward 100%
- coverage used as review/completeness aid
- remaining uncovered code becomes audit/review target

### Enterprise Readiness

- high coverage expected
- behavioral correctness remains more important than raw percentage

## Identity/Security Special Rules

Identity, Auth, Tokens, Sessions, Authorization, Security, Tenant boundaries MUST have:

- positive tests
- negative tests
- denial-path tests
- invalidity-path tests
- fail-closed tests

No security-sensitive flow may be GREEN with only happy-path coverage.

## Governance Sources

- AGENTS.md §24 — Test Evidence Rule
- AGENTS.md §24A — Risk-Based Behavioral Testing Rule
- how-to-unit-test.md §93 — Risk-Based Behavioral Testing Policy
- avax-test-evidence-quality SKILL
