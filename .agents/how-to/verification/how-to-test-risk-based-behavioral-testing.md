# How To Test Risk-Based Behavioral Testing

## Status

**MANDATORY** — This document defines a risk-based testing strategy across all development phases.

**Severity:** HIGH when security boundaries lack negative tests.

---

## 1. Testing Philosophy

The project uses **risk-based behavioral testing**. Coverage percentage is NOT truth. Behavioral proof is truth.

A test that proves the right behavior is worth 100 tests that prove nothing.

---

## 2. Phase Strategy

### V1 (Current) — Risk-Based Velocity

```text
Goal: Architectural velocity with critical behavioral confidence.

Coverage: NOT 100% required.
Focus: Meaningful behavioral confidence, runtime safety, security invariants.
Forbidden: Meaningless line coverage inflation, boilerplate tests, fake confidence.
```

**What MUST be tested in V1:**
```text
- All security boundaries (positive AND negative paths)
- All public API contracts
- All runtime safety boundaries
- All fail-closed behavior
- All authentication and authorization decisions
- All data validation boundaries
```

**What MAY be deferred in V1:**
```text
- Getter/setter-only tests
- Constructor-only tests without behavior
- Trivial value object tests
- Configuration assembly tests with no runtime behavior
- 100% branch coverage on non-security code
```

### V2 — Progressive Hardening

```text
Goal: Increase confidence through broader coverage and deeper negative testing.

Coverage: Target 70-85% line coverage as review aid.
Focus: Edge cases, error handling, observability, failure semantics.
```

### V3 — Enterprise Confidence

```text
Goal: Near-complete confidence coverage.

Coverage: High coverage expected (85%+).
Focus: Behavioral correctness remains more important than raw percentage.
Property-based testing for critical invariants.
Chaos testing for runtime boundaries.
```

---

## 3. Required Test Types

Every meaningful production unit MUST have tests from the applicable categories:

### 3.1 Happy Path

```text
What: Valid input produces expected result.
Purpose: Prove the feature works.
Example: Valid credentials authenticate successfully.
```

### 3.2 Sad Path (Failed-When)

```text
What: Invalid input fails correctly.
Purpose: Prove the boundary rejects what it should.
Examples:
  - Wrong password fails authentication
  - Expired token fails validation
  - Missing required field fails registration
  - Unauthorized user fails access check
```

### 3.3 Security Path

```text
What: Security boundaries enforce fail-closed behavior.
Purpose: Prove the system is secure even when attacked.
Examples:
  - SQL injection in input is rejected
  - XSS payload is sanitized
  - Secret is never logged
  - Authorization denial on wrong tenant
  - Replay attack is detected
```

### 3.4 Lifecycle Path

```text
What: State transitions work correctly through the lifecycle.
Purpose: Prove state management is correct.
Examples:
  - Session creation → active → refresh → expire → cleanup
  - Token issuance → validation → revocation
  - MFA enrollment → challenge → verification → backup
```

### 3.5 Validation Path

```text
What: Input validation rejects invalid data.
Purpose: Prove the boundary normalizes and validates correctly.
Examples:
  - DTO validation rejects malformed data
  - Value object rejects invalid values
  - Boundary normalization works both ways
```

### 3.6 Integration Boundary Path

```text
What: Component boundaries interact correctly.
Purpose: Prove integration points work.
Examples:
  - Auth component correctly reads from Session component
  - Token component correctly issues through Signing component
```

### 3.7 Runtime Safety Path

```text
What: Worker safety, reset behavior, long-lived process safety.
Purpose: Prove the system is safe in production runtimes.
Examples:
  - Static state resets between requests
  - No cross-request data leakage
  - Memory does not grow unbounded
```

---

## 4. Special Rules

### 4.1 Security-Sensitive Flows

Identity, Auth, Tokens, Sessions, Authorization, Tenant, and Security boundaries MUST have:

```text
- Positive test: Valid input succeeds
- Negative test: Invalid input fails
- Invalid-input test: Malformed input is rejected
- Fail-closed test: System fails safe when components fail
```

**A security boundary without a negative test is NOT proven.**

### 4.2 Authentication Flows

```text
MUST test:
  - Valid credentials authenticate
  - Wrong password fails
  - Missing user fails
  - Empty credentials fail
  - Brute force is rate-limited
  - Account lockout works (if configured)
```

### 4.3 Authorization Flows

```text
MUST test:
  - Authorized user accesses resource
  - Unauthorized user denied
  - Cross-tenant access denied
  - Role escalation denied
  - Permission check on object, not just route
```

### 4.4 Token Flows

```text
MUST test:
  - Valid token verifies
  - Expired token rejects
  - Tampered token rejects
  - Revoked token rejects
  - Wrong audience rejects
  - Wrong issuer rejects
```

---

## 5. Explicitly Forbidden

The following are FORBIDDEN unless explicitly accepted as YELLOW debt:

```text
- assertTrue(true)
- Meaningless not-null assertions
- Constructor-only tests without behavior
- Getter/setter-only tests
- Coverage-padding tests
- Implementation-detail obsession
- Mocking the entire subject under test
- Tests that only prove execution
- Tests written only to increase percentages
- Changing tests to fit broken behavior
- Fake GREEN by running irrelevant tests
- No negative tests for security gates
- No regression test for fixed bug
- No contract test for public API change
```

---

## 6. Test Organization

```text
tests/
  Unit/
    Components/
      <ComponentName>/
        <SubArea>/
          <Behavior>Test.php    — Proves specific behavior
    Framework/
      <Subsystem>/
        <Behavior>Test.php
  Integration/
    <ComponentName>/
      <Integration>Test.php
  Feature/
    <ComponentName>/
      <Feature>Test.php
  Architecture/
    <ComponentName>/
      <Boundary>Test.php        — Proves architectural invariants
  GoldenPathRuntime/
    <RuntimeName>/
      <GoldenPath>Test.php
  Contract/
    <ComponentName>/
      <Contract>Test.php        — Proves public API stability
  Operations/
    <Operation>/
      <Operation>Test.php
```

---

## 7. Shallow Test Detection

Tests are considered **shallow** when they:

```text
- Have no meaningful assertions
- Only prove the code executes without error
- Test implementation details instead of behavior
- Mock every dependency including the subject
- Cover only the happy path for security-sensitive code
- Use assertTrue(true) or equivalent
- Test constructors or getters without behavior
```

Shallow test detection is automated by the configured shallow-test checker.

---

## 8. Shallow-Test Gate Adoption Policy

**Status:** MANDATORY
**Severity:** BLOCKER for new or changed tests.

The shallow-test checker is enforced in phased mode during legacy adoption.

```bash
php tooling/testing/check-shallow-tests.php --mode=baseline
php tooling/testing/check-shallow-tests.php --mode=changed
```

Rules:

- New tests must prove behavior, not construction, existence, or assertion count.
- Modified tests must not introduce `assertTrue(true)`, constructor-only proof, getter-only proof, reflection-coupled proof, or happy-path-only security proof.
- Security-sensitive changed tests must include negative and fail-closed proof where the tested behavior owns authentication, authorization, tokens, sessions, credentials, tenants, secrets, request signing, or security policy.
- Legacy shallow tests may remain only when recorded in `.agents/management/baselines/shallow-tests-baseline.json` with owner, reason, remediation category, and review date.
- A baseline entry is not permission to add similar tests. It is a debt marker.
- Identity rewrite slices treat shallow-test findings in changed files as HARD BLOCKERS.

FULL_GREEN is forbidden while shallow-test full mode still reports unaccepted findings.

---

## 9. Validation

```bash
# Run all tests
vendor/bin/phpunit --no-coverage

# Run specific component tests
vendor/bin/phpunit --filter=Identity

# Run architecture tests
vendor/bin/phpunit --testsuite=Architecture

# Run security-sensitive tests
vendor/bin/phpunit --filter=Auth
vendor/bin/phpunit --filter=Token
vendor/bin/phpunit --filter=Access

# Detect shallow tests
run the configured shallow-test checker
```

---

## 10. Relationship to AGENTS.md

AGENTS.md routes test-related work to this document and the `avax-test-evidence-quality` skill.

This document owns the detailed risk-based behavioral testing strategy.

---

## 11. Final Law

Test behavior, not implementation.
Test security with negative paths.
Test runtime safety with reset behavior.
Test public API with contract tests.
Coverage percentage is not truth.
Behavioral proof is truth.
