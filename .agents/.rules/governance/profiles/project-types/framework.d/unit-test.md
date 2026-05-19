# How to Unit Test

## Pragmatic Unit Testing Governance and Behavior Specification Standard

---

## Status

**MANDATORY** - This document defines non-negotiable unit testing rules.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 1. Status of This Document

This document is a unit testing governance standard.

It is not a loose collection of testing tips.
It is not a framework-specific PHPUnit guide.
It is not a code coverage checklist.
It is not an academic testing manifesto.

It defines how unit tests must be named, structured, written, reviewed, and maintained.

This document applies to:

- applications
- services
- libraries
- SDKs
- packages
- plugins
- command-line tools
- workflow systems
- backend systems
- frontend systems
- framework-agnostic components
- reusable infrastructure components
- domain components
- architectural refactors
- bug fixes
- security-sensitive behavior
- public API behavior

The technical syntax may differ by language or framework.

The testing law must remain stable.

---

## 2. Purpose

The purpose of unit testing is not to make coverage numbers look good.

The purpose of unit testing is to prove that a unit behaves intentionally.

A good unit test suite must make behavior:

- explicit
- stable
- reviewable
- refactor-safe
- easy to understand
- easy to change safely
- resistant to regression
- resistant to accidental complexity
- clear enough to act as living documentation

A unit test suite must answer:

```text
What does this unit promise?
When does it succeed?
When does it fail?
Where are its boundaries?
Which behavior must never regress?
```

If tests do not answer these questions, they are weak.

---

## 3. Core Unit Testing Philosophy

Unit tests are behavior specifications.

They must describe what the tested unit does from the outside.

They must not lock the implementation from the inside.

A good unit test protects behavior.

A bad unit test protects implementation details.

The goal is not to test every line.

The goal is to protect every meaningful rule.

---

## 4. Definition of a Unit

A unit is the smallest meaningful ownership boundary that can be tested through observable behavior.

A unit may be:

* a class
* a function
* a module
* a file
* a value object
* a parser
* a builder
* a resolver
* a normalizer
* a validator
* a policy
* a pipeline step
* a flow owner
* a state owner
* an action owner
* a package public API method

A unit is not automatically equal to one class.

A unit is the thing that owns a behavior.

The correct question is:

```text
What behavior is owned here?
```

Not:

```text
How many methods exist here?
```

---

## 5. Main Unit Testing Law

Production code follows this naming law:

```text
naming must reflect responsibility
```

Unit tests follow the verification version of that law:

```text
test folder says verification scope
test class says tested unit or tested responsibility
test method says exact observable behavior under a clear condition
```

This is the core unit testing naming law.

Every test name must make behavior visible before opening the test body.

---

## 6. Test Folder Naming Rule

Test folders should make the verification scope obvious.

Recommended default shape:

```text
tests/
  Unit/
  Integration/
  Feature/
  Contract/
  Support/
```

For unit tests:

```text
tests/
  Unit/
    <same ownership path as production when useful>
```

Example:

```text
src/
  IncomingHttp/
    ReadRequest/
      ReadRequest.php
      RequestHeaders/
        NormalizeHeaders.php

tests/
  Unit/
    IncomingHttp/
      ReadRequest/
        ReadRequestTest.php
        RequestHeaders/
          NormalizeHeadersTest.php
```

The test tree should help a reader find the test for a production unit quickly.

The test tree must not become architecture theater.

Mirror production structure when it improves navigation.

Do not mirror production structure mechanically when it creates noise.

---

## 7. Test Class Naming Rule

A test class must clearly say what unit or responsibility is being tested.

Good:

```php
ReadRequestTest
BuildResponseTest
NormalizeHeadersTest
ParseJsonBodyTest
RegisterUserTest
RequirePermissionTest
CreateSessionTokenTest
```

Weak:

```php
RequestTest
ResponseTest
ServiceTest
ManagerTest
HelperTest
ValidationTest
```

A broad name is allowed only when the tested production unit is honestly broad and intentionally owns that
responsibility.

If the test class name is vague, usually the production unit name is also vague.

---

## 8. Test Method Naming Rule

A test method must describe exact observable behavior under a clear condition.

Default naming pattern:

```text
test_it_<expected_behavior>_when_<condition>()
```

Examples:

```php
test_it_creates_user_when_registration_data_is_valid()
test_it_fails_when_email_is_already_taken()
test_it_rejects_password_when_password_is_too_short()
test_it_returns_null_when_authenticated_user_does_not_exist()
test_it_does_not_duplicate_header_when_header_already_exists()
```

For edge cases:

```text
test_it_<expected_behavior>_when_<boundary_condition>()
```

Examples:

```php
test_it_accepts_password_when_password_has_minimum_allowed_length()
test_it_returns_empty_array_when_input_is_empty()
test_it_preserves_zero_when_value_is_zero()
```

For exceptions:

```text
test_it_throws_<exception_or_failure>_when_<condition>()
```

Examples:

```php
test_it_throws_invalid_email_exception_when_email_is_malformed()
test_it_throws_permission_denied_when_user_has_no_required_role()
```

For boolean behavior:

```php
test_it_returns_true_when_user_has_required_permission()
test_it_returns_false_when_user_has_no_required_permission()
```

For no-op behavior:

```php
test_it_does_nothing_when_session_is_already_closed()
test_it_does_not_dispatch_event_when_user_is_not_changed()
```

A test name must answer:

```text
What behavior is being proven?
Under what condition?
What result should happen?
```

If the name does not answer these questions, rename it.

---

## 9. Forbidden Weak Test Names

Avoid vague test names.

Bad:

```php
test_success()
test_failed()
test_valid()
test_invalid()
test_handle()
test_process()
test_run()
test_case_1()
test_constructor()
test_validation()
test_exception()
test_repository()
```

These names hide behavior.

A reader should not need to open the test body to understand what rule is being tested.

---

## 10. Behavior Over Implementation Rule

Unit tests must verify observable behavior, not internal implementation.

Good:

```php
test_it_returns_authenticated_user_when_session_contains_valid_user_id()
```

Weak:

```php
test_it_calls_find_by_id_method()
test_it_uses_array_key_user_id()
test_it_executes_private_validation_method()
```

A test should survive internal refactoring if the public behavior remains the same.

If a test breaks because private code moved, but behavior did not change, the test was too coupled to implementation.

---

## 11. Public Behavior Rule

Test public behavior.

Do not test private methods directly.

Bad:

```php
test_normalize_email()
```

Better:

```php
test_it_registers_user_with_normalized_email()
```

Private methods are implementation details.

If a private method is complex enough to deserve direct testing, that is usually a design smell.

Extract the behavior into a dedicated public unit and test that unit.

---

## 12. Unit Test Scenario Coverage Rule

Every meaningful unit must be tested through the scenario types that apply to it.

A tested unit is incomplete if it only proves that the successful case works.

A tested unit must also prove that it fails safely, predictably, and intentionally.

Required scenario types:

```text
Happy path
Failure path
Edge case
Regression case where applicable
Security or abuse case where applicable
```

---

## 13. Happy Path Tests

Happy path tests prove that the unit works when all required inputs, state, and dependencies are valid.

Examples:

```php
test_it_registers_user_when_data_is_valid()
test_it_returns_profile_when_user_exists()
test_it_builds_response_when_payload_is_complete()
test_it_parses_json_body_when_json_is_valid()
```

Happy path tests answer:

```text
Does it work when everything is valid?
```

A unit test suite with no happy path test is incomplete.

---

## 14. Failure Path Tests

Failure path tests prove that the unit rejects invalid behavior correctly.

They verify expected exceptions, error results, validation messages, fallback behavior, or safe refusal.

Examples:

```php
test_it_fails_when_email_is_missing()
test_it_fails_when_password_is_too_short()
test_it_fails_when_user_already_exists()
test_it_throws_exception_when_repository_cannot_save()
test_it_returns_error_when_payload_is_malformed()
```

Failure path tests answer:

```text
Does it fail correctly when something is invalid?
```

A unit test suite with no failure path test is usually weak.

---

## 15. Edge Case Tests

Edge case tests prove that behavior remains stable near boundaries.

Examples:

```php
test_it_accepts_password_when_password_has_minimum_allowed_length()
test_it_rejects_password_when_password_is_one_character_too_short()
test_it_returns_empty_collection_when_no_records_exist()
test_it_preserves_zero_when_value_is_zero()
test_it_handles_duplicate_role_without_creating_duplicate_assignment()
```

Edge cases include:

* empty values
* minimum allowed values
* maximum allowed values
* zero
* null where allowed
* duplicate operations
* unusual but valid inputs
* boundary state transitions
* repeated calls
* already completed states
* missing optional values

Edge case tests answer:

```text
Does it remain stable at the boundaries?
```

---

## 16. Regression Tests

Every fixed bug should receive a regression test.

Regression test workflow:

```text
1. Write a test that reproduces the bug.
2. Confirm the test fails for the correct reason.
3. Implement the smallest safe fix.
4. Keep the test permanently.
```

Example:

```php
test_it_does_not_drop_query_parameters_when_url_contains_fragment()
```

A bug fix without a regression test is suspicious.

It may be acceptable for trivial issues, but it must not become the default.

---

## 17. Security and Abuse Case Tests

Security-sensitive units must include abuse and failure mode tests.

Required for security-sensitive behavior:

* invalid input tests
* malformed input tests
* trust boundary tests
* authorization failure tests
* authentication failure tests
* permission denial tests
* unsafe state transition tests
* injection-like input tests where relevant
* replay or duplicate action tests where relevant
* safe failure mode tests

Examples:

```php
test_it_denies_access_when_user_has_no_required_role()
test_it_rejects_redirect_url_when_host_is_not_trusted()
test_it_rejects_token_when_signature_is_invalid()
test_it_fails_when_csrf_token_is_missing()
test_it_does_not_reveal_user_existence_when_login_fails()
```

Security tests must prove that unsafe behavior is rejected intentionally.

---

## 18. Arrange, Act, Assert Rule

Every unit test should follow a clear structure:

```text
Arrange
Act
Assert
```

Meaning:

```text
Arrange: prepare data, dependencies, state
Act: execute the behavior
Assert: verify the observable outcome
```

Example:

```php
public function test_it_registers_user_when_data_is_valid(): void
{
    // Arrange
    $data = RegistrationData::fromArray([
        'email' => 'milos@example.com',
        'password' => 'StrongPassword123',
    ]);

    // Act
    $user = $this->registerUser->handle($data);

    // Assert
    self::assertSame('milos@example.com', $user->email()->value());
}
```

Do not write tests where setup, action, and assertion are mixed randomly.

Messy test structure creates weak diagnostics.

---

## 19. One Act Rule

A unit test should have one main Act.

Good:

```php
$result = $this->registerUser->handle($data);
```

Weak:

```php
$user = $this->registerUser->handle($data);
$this->loginUser->handle($credentials);
$this->changePassword->handle($command);
```

Multiple main actions usually mean the test is testing too much.

Exception:

A test may perform multiple setup actions if they are clearly part of Arrange.

The tested behavior itself should remain one main action.

---

## 20. Given, When, Then Rule

Use Given, When, Then as the mental model for behavior clarity.

```text
Given some context
When something happens
Then expected behavior occurs
```

Example test name:

```php
test_it_fails_when_email_is_already_taken()
```

Meaning:

```text
Given an existing user with that email
When a new registration uses the same email
Then registration fails
```

This makes tests read like specifications.

Use this structure in comments only when it clarifies complex tests.

Do not add mechanical comments to every trivial test.

---

## 21. One Test, One Reason to Fail

A test should verify one meaningful behavior.

Bad:

```php
test_it_registers_user_and_sends_email_and_logs_event_and_creates_session()
```

Better:

```php
test_it_registers_user_when_data_is_valid()
test_it_sends_welcome_email_after_user_is_registered()
test_it_records_registration_event_after_user_is_registered()
test_it_creates_session_after_successful_registration()
```

If one test fails, the reason should be obvious.

A test with multiple unrelated assertions becomes hard to diagnose.

---

## 22. Assertion Precision Rule

Assertions must be specific.

Weak:

```php
self::assertNotNull($user);
self::assertTrue($result->isSuccess());
```

Better:

```php
self::assertSame('milos@example.com', $user->email()->value());
self::assertInstanceOf(RegistrationSucceeded::class, $result);
```

Weak assertions allow broken behavior to pass.

A test should assert the specific observable result that matters.

---

## 23. Exception Testing Rule

Failure tests must prove the exact expected failure.

Good:

```php
$this->expectException(InvalidEmailAddress::class);
```

Better when the domain has a precise exception:

```php
$this->expectException(EmailAddressIsInvalid::class);
```

Avoid:

```php
$this->expectException(Exception::class);
```

Generic exception tests are usually too weak.

Only use generic exceptions if the design truly exposes generic exceptions, which should be rare.

---

## 24. Test Data Rule

Test data must be clear and intentional.

Only show the data that matters to the behavior.

Weak:

```php
$user = UserFactory::new()->create();
```

Better:

```php
$user = UserFactory::new()
    ->withEmail('milos@example.com')
    ->verified()
    ->create();
```

If a value matters, make it explicit.

If a value does not matter, hide it behind a builder or factory.

Avoid giant fixtures unless the behavior truly requires them.

---

## 25. Test Data Builder Rule

Prefer Test Data Builders for expressive and flexible setup.

Example:

```php
$user = UserBuilder::new()
    ->withEmail('milos@example.com')
    ->verified()
    ->build();
```

Builders are useful when tests need small variations.

They make intent visible without repeating irrelevant setup.

---

## 26. Object Mother Rule

Object Mothers are allowed for canonical examples.

Example:

```php
$user = UserMother::verifiedUser();
```

But Object Mothers must not become junk drawers.

Weak:

```php
UserMother::random()
UserMother::valid()
UserMother::admin()
UserMother::blocked()
UserMother::withEverything()
```

Preferred rule:

```text
Use builders for variation.
Use mothers for a few stable canonical examples.
```

---

## 27. Value Object Rule

When production code uses value objects, tests should normally use the same domain language.

Good:

```php
$email = EmailAddress::fromString('milos@example.com');
```

Acceptable only when testing primitive validation directly:

```php
$email = 'milos@example.com';
```

Tests should reinforce the domain model, not bypass it unnecessarily.

---

## 28. Mock Boundaries, Not Domain Objects

Mocks should be used for behavior that crosses a boundary.

Good mock candidates:

```text
Repository
Mailer
EventBus
Clock
UuidGenerator
HttpClient
FileStorage
Queue
Logger
ExternalGateway
```

Bad mock candidates:

```text
Value objects
Simple DTOs
Pure functions
Internal domain objects
Collections
Small state objects
```

Core rule:

```text
Mock boundaries, not your own domain.
```

Over-mocking creates fragile tests that know too much about implementation.

---

## 29. Test Doubles Rule

Use the right test double for the job.

Definitions:

```text
Dummy: passed but never used
Stub: returns prepared data
Fake: working simplified implementation
Mock: verifies interaction
Spy: records what happened for later assertion
```

Use a stub when you only need data:

```php
$userRepository->findByEmail($email)->willReturn($user);
```

Use a mock when the interaction itself is the behavior:

```php
$mailer->shouldReceive('sendWelcomeEmail')->once();
```

Use a fake when behavior is richer and reused across tests:

```php
$users = new InMemoryUserRepository();
```

Best default:

```text
Prefer fakes and stubs.
Use mocks only when the interaction is the behavior.
```

---

## 30. Interaction Testing Rule

Only verify method calls when the interaction is the observable behavior.

Good:

```php
test_it_sends_welcome_email_after_user_is_registered()
```

In that case, verifying mailer interaction is valid.

Weak:

```php
test_it_calls_user_repository_find_by_email()
```

That usually tests implementation, not behavior.

Do not verify internal collaboration unless that collaboration is the contract.

---

## 31. Deterministic Test Rule

A unit test must always pass or always fail for the same code.

Avoid uncontrolled:

* current time
* random values
* real network
* real filesystem
* real database
* external APIs
* execution order dependency
* environment-dependent values
* global mutable state

Use controlled dependencies:

* FrozenClock
* FixedUuidGenerator
* FixedRandomStringGenerator
* InMemoryRepository
* FakeHttpClient
* VirtualFilesystem
* explicit environment stubs

Bad:

```php
$now = new DateTimeImmutable();
```

Better:

```php
$clock = new FrozenClock('2026-04-24 12:00:00');
```

Flaky tests are a governance failure.

---

## 32. No Test Order Dependency Rule

Each test must be able to run alone.

Bad:

```text
test_1_creates_user()
test_2_updates_same_user()
test_3_deletes_same_user()
```

Good:

```text
Each test creates its own required state.
```

A test must not depend on:

* previous test execution
* shared mutated state
* global state left by another test
* database records from another test
* test execution order

---

## 33. Isolation Rule

A unit test should isolate the tested unit from external systems.

Unit tests should not depend on:

* real databases
* real queues
* real HTTP services
* real mail servers
* real file storage
* real clocks
* real random generators
* real framework bootstrapping unless the unit is the framework integration itself

If a test needs these, it is probably an integration test.

---

## 34. Unit vs Integration vs Feature vs Contract Tests

A test must clearly belong to one level.

Recommended split:

```text
Unit tests: one behavior owner, fast, isolated
Integration tests: real collaboration between components
Feature tests: full use case from outside
Contract tests: same behavior guaranteed across implementations
End-to-end tests: whole system through real boundaries
```

Do not casually mix levels.

A unit test should not quietly become an integration test.

An integration test should not pretend to be a unit test.

---

## 35. Test Pyramid Rule

Default priority:

```text
Many unit tests
Some integration tests
Few end-to-end tests
```

Unit tests are fast and precise.

Integration tests prove wiring and collaboration.

End-to-end tests prove critical flows but are slower and more brittle.

Do not use end-to-end tests to compensate for weak unit design.

---

## 36. Contract Test Rule

Use contract tests when multiple implementations must behave the same way.

Examples:

```text
SessionStoreContractTest
CacheStoreContractTest
UserRepositoryContractTest
FilesystemContractTest
HttpClientContractTest
```

Then run the same behavior tests against:

```text
InMemorySessionStore
RedisSessionStore
DatabaseSessionStore
```

Rule:

```text
Every adapter must prove that it honors the contract.
```

Contract tests are especially important for:

* repositories
* storage adapters
* cache adapters
* session adapters
* HTTP clients
* queue adapters
* filesystem adapters
* serialization adapters

---

## 37. Characterization Test Rule

Before refactoring unclear legacy code, write characterization tests.

Characterization tests capture current behavior.

They do not judge whether the behavior is ideal.

They freeze what the system currently does so refactoring can happen safely.

Workflow:

```text
1. Observe existing behavior.
2. Write tests that describe current behavior.
3. Refactor behind those tests.
4. Add stricter behavior tests later where behavior should be improved.
```

Rule:

```text
Do not deeply refactor behavior that is not protected by tests.
```

---

## 38. TDD Default Rule

For new behavior, prefer the TDD loop where practical.

```text
Red
Green
Refactor
```

Meaning:

```text
Red: write a failing test
Green: implement the smallest working behavior
Refactor: clean the design while tests stay green
```

For each small behavior:

```text
1. Write a failing test first.
2. Confirm it fails for the correct reason.
3. Add the minimum production code.
4. Run the tests.
5. Refactor only while all tests remain green.
6. Repeat in small increments.
```

TDD is especially useful for:

* domain rules
* validation
* parsers
* value objects
* state transitions
* security-sensitive behavior
* pipelines
* policies
* public API contracts

TDD is not mandatory for every trivial class.

But meaningful behavior should not remain unprotected.

---

## 39. No Skipping Rule

Do not skip the testing order when practicing TDD.

Required order:

```text
failing -> passing -> refactor
```

Wrong order:

```text
implementation -> retroactive tests -> fake confidence
```

Retroactive tests are sometimes necessary, especially in legacy code.

But for new behavior, test-first is preferred.

---

## 40. Increment Size Rule

Do not build a whole system in one step.

Each increment should implement one behavior or one small slice of behavior.

A good TDD increment is small enough that failure is easy to understand.

If the test requires a huge setup, the increment is probably too large or the design is too coupled.

---

## 41. Required TDD Reporting Rule

When an AI agent or engineer performs a TDD increment, the output should show:

```text
1. What test was added.
2. Why it fails.
3. What minimum code was added.
4. Whether all tests pass after the change.
```

This prevents fake TDD.

---

## 42. Mutation Testing Rule

Code coverage only proves that code was executed.

Mutation testing proves that tests can detect broken logic.

Example mutation:

```php
if ($age >= 18)
```

changed to:

```php
if ($age > 18)
```

If tests still pass, the tests are too weak.

Rule:

```text
High coverage is not enough.
Tests must kill meaningful mutations.
```

Mutation testing is especially valuable for:

* domain rules
* validators
* parsers
* authorization policies
* financial calculations
* security-sensitive conditions
* boundary conditions

---

## 43. Coverage Rule

Coverage is a signal, not the goal.

Good coverage strategy:

```text
Critical domain logic: very high coverage
Security-sensitive logic: very high coverage
Risky infrastructure: strong integration and contract coverage
Trivial DTOs: test only if they contain behavior
Framework glue: test through integration or feature tests
```

Bad strategy:

```text
Chase 100% coverage by testing getters, setters, and framework wiring without meaningful behavior.
```

Coverage must support confidence.

Coverage must not become cosmetic.

---

## 44. Do Not Test Framework Internals

Do not test that PHPUnit, Symfony, Laravel, Doctrine, PSR interfaces, or a framework container works.

Test your behavior around them.

Weak:

```php
test_container_resolves_service()
```

Better:

```php
test_it_builds_login_flow_with_configured_dependencies()
```

Frameworks are tools.

Your tests should protect your contracts.

---

## 45. Public API Stability Rule

For reusable packages, tests must protect the public surface.

Public API examples:

* facade methods
* builder methods
* configuration DSL
* public interfaces
* result objects
* documented exceptions
* documented behavior
* public package entrypoints

Internal classes may change.

Public behavior must not break accidentally.

A public API test should read like a promise to package users.

---

## 46. Fluent API and DSL Testing Rule

If a component exposes a fluent API or DSL, unit tests must prove the public language is stable.

Examples:

```php
test_it_builds_configuration_through_fluent_builder()
test_it_preserves_method_chain_when_option_is_enabled()
test_it_fails_when_required_builder_step_is_missing()
```

For DSL-like APIs, tests must verify:

* happy path language
* invalid combinations
* missing required steps
* order constraints where relevant
* default values
* final built object or command
* public error messages where relevant

The DSL is part of the public API.

Protect it directly.

---

## 47. State Transition Testing Rule

If a unit owns state transitions, tests must prove valid and invalid transitions.

Examples:

```php
test_it_starts_session_when_session_is_new()
test_it_fails_when_closed_session_is_started_again()
test_it_marks_invoice_as_paid_when_payment_is_confirmed()
test_it_does_not_mark_invoice_as_paid_twice()
```

State transition tests should cover:

* initial state
* valid transition
* invalid transition
* repeated transition
* final state
* emitted event if that is part of the contract

---

## 48. Parser and Normalizer Testing Rule

Parsers and normalizers require strong edge case coverage.

Examples:

```php
test_it_parses_json_body_when_body_is_valid_json()
test_it_fails_when_json_body_is_malformed()
test_it_returns_empty_body_when_body_is_empty()
test_it_normalizes_header_names_to_lowercase()
test_it_preserves_header_values_when_normalizing_header_names()
```

Parser tests should cover:

* valid input
* malformed input
* empty input
* boundary input
* unusual but valid input
* unsupported input
* encoding issues where relevant

---

## 49. Policy and Authorization Testing Rule

Policies must be tested explicitly.

Examples:

```php
test_it_allows_access_when_user_has_required_permission()
test_it_denies_access_when_user_has_no_required_permission()
test_it_denies_access_when_user_is_not_authenticated()
test_it_denies_access_when_resource_belongs_to_another_tenant()
```

Policy tests must prove both allow and deny behavior.

A policy with only happy path tests is unsafe.

---

## 50. Time-Based Behavior Testing Rule

Time-based behavior must use a controlled clock.

Bad:

```php
$expiresAt = new DateTimeImmutable('+15 minutes');
```

Better:

```php
$clock = new FrozenClock('2026-04-24 12:00:00');
$expiresAt = $tokenFactory->create($clock)->expiresAt();
```

Time-based tests should cover:

* current time
* before boundary
* exact boundary
* after boundary
* expiration
* renewal
* timezone behavior where relevant

---

## 51. Randomness Testing Rule

Random behavior must be controlled through explicit dependencies.

Use:

* fixed random generator
* deterministic UUID generator
* seeded generator
* fake token generator

Do not assert against uncontrolled random output.

Bad:

```php
self::assertNotEmpty($token);
```

Better:

```php
self::assertSame('fixed-token-value', $token->value());
```

When randomness itself is the subject, use statistical or property-based tests carefully, not fragile exact-value tests.

---

## 52. Property-Based Testing Rule

Property-based testing is useful when behavior should hold across many inputs.

Good candidates:

* parsers
* serializers
* normalizers
* mathematical rules
* collection operations
* value object invariants
* reversible transformations

Example properties:

```text
Serializing and then deserializing returns equivalent data.
Normalizing twice gives the same result.
Sorting result is always ordered.
Invalid email strings are always rejected.
```

Property-based tests are not a replacement for named scenario tests.

They complement them.

---

## 53. Idempotency Testing Rule

If behavior should be safe to repeat, test idempotency.

Examples:

```php
test_it_does_not_duplicate_role_when_role_is_already_assigned()
test_it_does_not_create_second_session_when_session_already_exists()
test_it_returns_same_result_when_normalization_runs_twice()
```

Idempotency matters for:

* retries
* queues
* imports
* event handling
* normalization
* caching
* state transitions
* external integrations

---

## 54. Error Message Testing Rule

Test exact error messages only when messages are part of the public contract.

Good candidates:

* public validation errors
* API error responses
* CLI error output
* developer-facing package exceptions
* documented exception messages

Avoid testing exact messages for internal exceptions unless they are part of the contract.

Prefer testing exception type and structured error code where available.

---

## 55. Result Object Testing Rule

If a unit returns result objects, tests should verify the exact result type and important data.

Example:

```php
$result = $this->registerUser->handle($command);

self::assertInstanceOf(RegistrationSucceeded::class, $result);
self::assertSame('milos@example.com', $result->user()->email()->value());
```

Avoid weak checks:

```php
self::assertTrue($result->isSuccess());
```

Boolean result checks are often too vague.

---

## 56. Event Testing Rule

If events are part of the observable contract, test them.

Examples:

```php
test_it_records_user_registered_event_when_user_is_registered()
test_it_does_not_record_event_when_registration_fails()
```

Do not test internal event plumbing unless the plumbing is the unit.

Test the observable event contract:

* event type
* event payload
* event count
* event order where relevant

---

## 57. Logging Testing Rule

Do not test logs by default.

Test logs only when logging is part of the required behavior.

Good candidates:

* audit logs
* security logs
* compliance logs
* critical operational logs
* failure diagnostics required by contract

Weak:

```php
test_it_logs_debug_message()
```

Good:

```php
test_it_records_security_audit_log_when_permission_is_denied()
```

---

## 58. Comments in Tests Rule

Test code should be readable without excessive comments.

Use comments only when they clarify behavior or separate complex phases.

Allowed:

```php
// Arrange
// Act
// Assert
```

Allowed for complex cases:

```php
// This exact boundary matters because tokens expire at the same second.
```

Avoid comments that repeat the code.

The test name and structure should carry most of the explanation.

---

## 59. Test Helpers Rule

Test helpers must clarify intent, not hide behavior.

Weak:

```php
$this->prepareEverything();
```

Better:

```php
$this->givenExistingUserWithEmail('milos@example.com');
$this->givenPasswordPolicyRequiresMinimumLength(12);
```

A helper name must say what state it creates.

If a helper hides important behavior, the test becomes dishonest.

---

## 60. Fixture Rule

Fixtures should be small, local, and intentional.

Avoid global giant fixtures.

Good fixtures:

* small
* named by scenario
* easy to inspect
* close to the test when possible
* only contain relevant data

Bad fixtures:

* huge JSON files with unknown relevance
* shared state across many unrelated tests
* fixtures that require tribal knowledge
* fixtures that silently change behavior for multiple tests

---

## 61. Snapshot Testing Rule

Snapshot tests are allowed only when they are useful and reviewed carefully.

Good candidates:

* generated documentation
* generated schemas
* stable public output
* complex serialized structures

Bad candidates:

* behavior that should be asserted precisely
* unstable output
* large snapshots nobody reviews

Snapshot updates must be intentional.

Never approve snapshot changes blindly.

---

## 62. Data Provider Rule

Use data providers for the same behavior across multiple inputs.

Good:

```php
test_it_rejects_invalid_email_addresses()
```

with cases:

```text
empty string
missing at sign
missing domain
invalid characters
```

Do not use data providers to hide unrelated behaviors in one test.

Each data provider must represent one behavior rule with multiple examples.

---

## 63. Naming for Data Provider Cases

Each data provider case should have a descriptive name.

Good:

```php
'yields error when email is empty' => [''],
'yields error when email has no domain' => ['milos@'],
'yields error when email has no at sign' => ['milos.example.com'],
```

Weak:

```php
['']
['milos@']
['milos.example.com']
```

Named cases make failures readable.

---

## 64. Setup Method Rule

Use `setUp()` only for truly common setup.

Do not put scenario-specific setup in `setUp()`.

Weak:

```php
protected function setUp(): void
{
    $this->user = UserBuilder::new()->admin()->verified()->build();
}
```

Better:

```php
protected function setUp(): void
{
    $this->clock = new FrozenClock('2026-04-24 12:00:00');
}
```

Scenario-specific state belongs inside the test or in explicit helper methods.

Hidden setup makes tests harder to read.

---

## 65. Shared State Rule

Avoid shared mutable state between tests.

Each test should build its own state.

Allowed shared state:

* immutable test constants
* fixed test values
* reusable builders
* reusable fakes reset per test
* common dependency setup that does not encode scenario behavior

Forbidden shared state:

* mutated static state
* reused entity instances across tests
* shared database records
* shared fake repositories that are not reset
* global configuration changed by one test and reused by another

---

## 66. Speed Rule

Unit tests must be fast enough to run during normal development.

A slow unit test suite will not be run often.

If a unit test is slow, check for:

* accidental database access
* real network calls
* unnecessary framework bootstrapping
* large fixtures
* sleeps
* polling
* uncontrolled filesystem access
* expensive cryptography where a fake would be enough

Slow behavior belongs in integration tests unless speed is part of the unit contract.

---

## 67. No Sleep Rule

Unit tests must not use real sleep.

Bad:

```php
sleep(1);
usleep(500000);
```

Use controlled time instead.

Good:

```php
$clock->moveForward(seconds: 1);
```

Real sleeping creates slow and flaky tests.

---

## 68. Global State Rule

Avoid global state in unit tests.

If global state is unavoidable, isolate it and reset it.

Global state includes:

* environment variables
* superglobals
* static caches
* global configuration
* singleton state
* locale
* timezone
* current working directory
* global error handlers

A test that changes global state must restore it.

---

## 69. Superglobal Testing Rule

In PHP, do not casually test through real superglobals.

Prefer explicit input objects.

Weak:

```php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['id'] = '123';
```

Better:

```php
$request = ServerInputBuilder::new()
    ->withMethod('POST')
    ->withQueryParam('id', '123')
    ->build();
```

Superglobal tests are acceptable only when the unit specifically owns superglobal reading.

---

## 70. Filesystem Rule

Unit tests should not touch the real filesystem unless the unit itself owns filesystem behavior.

Use:

* virtual filesystem
* in-memory file storage
* temporary isolated directory
* fake file reader
* fake file writer

If a real temporary directory is used, it must be isolated and cleaned.

---

## 71. Database Rule

Unit tests should not use a real database.

Use:

* in-memory repository
* fake repository
* stubbed repository
* contract tests for repository implementations
* integration tests for real database behavior

If a test uses SQL, transactions, migrations, indexes, or a real connection, it is not a unit test.

It is an integration test.

---

## 72. HTTP Rule

Unit tests should not call real HTTP services.

Use:

* fake HTTP client
* stubbed response
* recorded contract fixture when appropriate
* integration test for real client behavior

A unit test must not depend on internet access.

---

## 73. CLI Unit Testing Rule

For CLI units, test command behavior without relying on a real terminal unless terminal behavior is the unit.

Test:

* parsed options
* command result
* exit code
* output text where it is contract
* validation errors
* failure modes

Examples:

```php
test_it_returns_success_exit_code_when_command_completes()
test_it_prints_error_when_required_option_is_missing()
```

---

## 74. Package Configuration Testing Rule

Configuration builders and package assembly code must be tested through their public behavior.

Examples:

```php
test_it_registers_default_clock_when_no_clock_is_configured()
test_it_uses_custom_cache_store_when_cache_store_is_configured()
test_it_fails_when_required_adapter_is_missing()
```

Do not test container internals.

Test the package's promised assembly behavior.

---

## 75. Naming Consistency Rule

One concept must have one name across production code and tests.

Do not mix synonyms in test names.

Bad:

```php
test_it_authenticates_user_when_credentials_are_valid()
test_it_logs_in_user_when_credentials_are_valid()
```

If `AuthenticateUser` and `LoginUser` are different concepts, make that difference explicit.

If they mean the same thing, choose one name.

Tests should reinforce the system language.

---

## 76. Test Class Organization Rule

Inside a test class, group tests by behavior when needed.

Recommended order:

```text
happy path
failure paths
edge cases
regression cases
security or abuse cases
```

Do not over-engineer grouping.

But do keep related tests close.

A reader should see the behavior map of the unit quickly.

---

## 77. Test Body Length Rule

A unit test should be short enough to understand quickly.

Long tests usually indicate:

* too much setup
* hidden integration behavior
* weak builders
* too many assertions
* too broad tested unit
* poor production design

Long tests are allowed only when the scenario is genuinely complex and still readable.

---

## 78. Production Design Feedback Rule

Difficult tests are design feedback.

If a unit is painful to test, ask:

```text
Is the unit doing too much?
Are dependencies hidden?
Is time/randomness/global state uncontrolled?
Is behavior mixed with infrastructure?
Is there a missing value object?
Is there a missing port?
Is the public API unclear?
```

Do not blindly make tests more complex to compensate for poor design.

Improve the design.

---

## 79. AI Testing Rule

An AI agent must not merely add tests for coverage.

The AI must first identify the behavior contract of the unit.

Then it must create tests for:

```text
happy path
failure path
edge cases
regression cases where applicable
security or abuse cases where applicable
```

AI-generated tests must verify observable behavior, not implementation details.

AI-generated tests must not use vague names.

AI-generated tests must not over-mock domain objects.

AI-generated tests must not create fake confidence.

---

## 80. AI Required Output Rule

When an AI agent writes or updates unit tests, it must report:

```text
1. Tested unit or responsibility
2. Behavior contract discovered
3. Happy path tests added
4. Failure path tests added
5. Edge case tests added
6. Regression tests added, if any
7. Security or abuse tests added, if relevant
8. Test doubles used and why
9. What was intentionally not tested and why
10. Final test command result
```

This makes the work reviewable.

---

## 81. Forbidden Unit Testing Anti-Patterns

The following patterns are forbidden by default:

* testing private methods directly
* testing implementation details
* over-mocking internal domain objects
* using real time
* using real random values
* using real network calls
* depending on test order
* using vague test names
* hiding scenario setup in unclear helpers
* asserting only `true` or `not null`
* testing framework internals
* writing tests only for coverage
* skipping failure paths
* skipping edge cases for boundary-heavy logic
* creating giant fixtures nobody understands
* using sleep in tests
* leaving global state dirty
* writing one massive test for many behaviors
* using mocks to prove every internal call
* using integration tests and calling them unit tests

---

## 82. Good Unit Test Example

```php
public function test_it_registers_user_when_registration_data_is_valid(): void
{
    // Arrange
    $users = new InMemoryUserRepository();
    $passwords = new FakePasswordHasher('hashed-password');

    $registerUser = new RegisterUser(
        users: $users,
        passwords: $passwords,
    );

    $data = RegistrationData::fromArray([
        'email' => 'milos@example.com',
        'password' => 'StrongPassword123',
    ]);

    // Act
    $user = $registerUser->handle($data);

    // Assert
    self::assertSame('milos@example.com', $user->email()->value());
    self::assertSame('hashed-password', $user->password()->hash());
    self::assertTrue($users->hasEmail(EmailAddress::fromString('milos@example.com')));
}
```

Why this is good:

* name describes behavior and condition
* Arrange, Act, Assert is clear
* fake repository is used instead of real database
* fake hasher controls external behavior
* assertions verify observable outcome
* no private methods are tested
* no framework internals are tested

---

## 83. Good Failure Test Example

```php
public function test_it_fails_when_email_is_already_taken(): void
{
    // Arrange
    $users = new InMemoryUserRepository();
    $users->save(UserBuilder::new()
        ->withEmail('milos@example.com')
        ->build());

    $registerUser = new RegisterUser(
        users: $users,
        passwords: new FakePasswordHasher('hashed-password'),
    );

    $data = RegistrationData::fromArray([
        'email' => 'milos@example.com',
        'password' => 'StrongPassword123',
    ]);

    // Assert
    $this->expectException(EmailIsAlreadyTaken::class);

    // Act
    $registerUser->handle($data);
}
```

Why this is good:

* failure condition is explicit
* exact domain exception is expected
* repository is fake, not mocked unnecessarily
* behavior is tested through public API

---

## 84. Good Edge Case Example

```php
public function test_it_accepts_password_when_password_has_minimum_allowed_length(): void
{
    // Arrange
    $policy = new PasswordPolicy(minimumLength: 12);

    // Act
    $result = $policy->validate('123456789012');

    // Assert
    self::assertInstanceOf(PasswordAccepted::class, $result);
}
```

Companion failure test:

```php
public function test_it_rejects_password_when_password_is_one_character_too_short(): void
{
    // Arrange
    $policy = new PasswordPolicy(minimumLength: 12);

    // Act
    $result = $policy->validate('12345678901');

    // Assert
    self::assertInstanceOf(PasswordRejected::class, $result);
}
```

Boundary tests should usually come in pairs.

---

## 85. Weak Unit Test Example

```php
public function test_success(): void
{
    $service = new UserService();

    $result = $service->handle([
        'email' => 'milos@example.com',
    ]);

    self::assertTrue($result);
}
```

Why this is weak:

* test name says nothing
* tested behavior is unclear
* input meaning is unclear
* assertion is too vague
* result contract is unclear
* `UserService` may be a vague production unit
* failure paths are not protected

---

## 86. Unit Test Review Checklist

Use this checklist during review.

### Naming

```text
Does the test class name identify the tested unit or responsibility?
Does the test method name describe exact behavior?
Does the name include the condition?
Is the name free of vague terms like success, failed, process, handle, case_1?
```

### Behavior

```text
Does the test verify behavior, not implementation?
Would the test survive internal refactoring?
Is the observable outcome clear?
Is the public contract protected?
```

### Scenario Coverage

```text
Is there a happy path test?
Is there a failure path test?
Are edge cases covered where relevant?
Is there a regression test for fixed bugs?
Are security or abuse cases covered where relevant?
```

### Structure

```text
Does the test follow Arrange, Act, Assert?
Is there one main Act?
Does the test have one reason to fail?
Is setup visible and intentional?
```

### Test Doubles

```text
Are mocks used only at boundaries?
Would a fake or stub be clearer?
Are domain objects over-mocked?
Are external systems controlled?
```

### Determinism

```text
Is time controlled?
Is randomness controlled?
Is there no real network?
Is there no real database?
Is there no test order dependency?
Is global state restored?
```

### Assertions

```text
Are assertions precise?
Are exact exceptions tested?
Are result objects verified specifically?
Are weak assertTrue/assertNotNull checks avoided?
```

### Maintainability

```text
Is test data minimal and expressive?
Are helpers clear?
Are fixtures small?
Is the test fast?
Would a new developer understand the behavior quickly?
```

---

## 87. Minimum Standard Per Meaningful Unit

Every meaningful unit should have:

```text
At least one happy path test
At least one failure path test where failure is possible
Edge case tests where boundaries exist
Regression tests for fixed bugs
Security or abuse tests for trust-sensitive behavior
Precise behavior names
Deterministic dependencies
No unnecessary mocks
```

A unit that cannot fail does not need artificial failure tests.

A trivial DTO with no behavior does not need meaningless tests.

A security-sensitive unit needs stronger coverage than a passive data holder.

Apply judgment.

Do not create fake completeness.

---

## 88. Final Unit Testing Laws

Remember these laws:

### Main law

```text
Unit tests specify behavior.
```

### Naming law

```text
test folder says verification scope
test class says tested unit or responsibility
test method says exact observable behavior under a clear condition
```

### Scenario law

```text
Cover happy path, failure path, and edge cases where relevant.
```

### Behavior law

```text
Test public behavior, not private implementation.
```

### Isolation law

```text
Mock boundaries, not domain objects.
```

### Determinism law

```text
Control time, randomness, IO, network, database, and global state.
```

### Refactor law

```text
Do not deeply refactor behavior that is not protected by tests.
```

### Regression law

```text
Every fixed bug deserves a test that proves it stays fixed.
```

### Simplicity law

```text
If a test looks clever but reads worse, it failed.
```

---

## 89. Core Summary

A good unit test suite does not only prove that code works.

It proves that code behaves intentionally.

It should be:

* fast
* clear
* deterministic
* behavior-focused
* implementation-resistant
* refactor-friendly
* failure-aware
* edge-case-aware
* precise
* boring in the best possible way

## 90. Gate Self-Test Cross-Reference

Every mandatory validation gate or architecture test MUST have at least one negative test case proving it fails when the
rule is violated.

A gate that cannot fail is not a gate.

---

The final standard is simple:

```text
A unit test should make the behavior so obvious that the production code has nowhere to hide.
```
