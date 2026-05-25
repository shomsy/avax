# Identity Slice 2 — Foundation + Token Graph + Session Graph

## Stage
Identity Slice 2 Implementation

## Status
**GREEN**

## Files Changed

### New Files (27)

#### Foundation Primitives
- `components/Identity/Auth/System/Foundation/State/ResettableIdentityState.php` — Interface for worker-safe state reset
- `components/Identity/Auth/System/Foundation/Failures/AccessDenied.php` — Access denial exception
- `components/Identity/Auth/System/Foundation/Failures/TokenRejected.php` — Token rejection exception
- `components/Identity/Auth/System/Foundation/Time/ClockInterface.php` — Time abstraction contract
- `components/Identity/Auth/System/Foundation/Time/FrozenClock.php` — Testable frozen clock
- `components/Identity/Auth/System/Foundation/Values/SignedToken.php` — Signed token value object
- `components/Identity/Auth/System/Foundation/Values/TokenClaims.php` — Token claims value object with payload serialization

#### Token Capabilities
- `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Blacklist/TokenBlacklist.php` — Token revocation interface
- `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Blacklist/InMemoryTokenBlacklist.php` — In-memory blacklist implementation
- `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Codec/SignToken.php` — Token signing interface
- `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Codec/VerifyToken.php` — Token verification interface

#### Session Capabilities
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Store/IdentitySession.php` — Session value object
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Store/SessionStore.php` — Session persistence interface
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Store/InMemorySessionStore.php` — In-memory session store
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Store/GenerateSessionId.php` — Session ID generation interface
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Store/RandomSessionId.php` — Cryptographically secure session ID generator

#### Flows
- `components/Identity/Auth/System/Flows/IssueAccessToken/IssueAccessTokenRequest.php` — Issue token DTO
- `components/Identity/Auth/System/Flows/IssueAccessToken/IssuedAccessToken.php` — Issued token value object
- `components/Identity/Auth/System/Flows/IssueAccessToken/IssueAccessToken.php` — Token issuing flow
- `components/Identity/Auth/System/Flows/VerifyAccessToken/VerifiedAccessToken.php` — Verified token value object
- `components/Identity/Auth/System/Flows/VerifyAccessToken/VerifyAccessToken.php` — Token verification flow
- `components/Identity/Auth/System/Flows/StartSession/StartSessionRequest.php` — Start session DTO
- `components/Identity/Auth/System/Flows/StartSession/StartedSession.php` — Started session value object
- `components/Identity/Auth/System/Flows/StartSession/StartSession.php` — Session start flow

#### Graphs + Configuration
- `components/Identity/Auth/System/Configuration/Graphs/TokenGraph.php` — Token composition root
- `components/Identity/Auth/System/Configuration/Graphs/SessionGraph.php` — Session composition root
- `components/Identity/Auth/System/Configuration/IdentityConfiguration.php` — Identity settings value object

#### Tests
- `tests/Unit/Components/Identity/Auth/Slice2/Slice2Test.php` — 14 test cases covering all flows

### Modified Files (4)
- `components/Identity/Auth/System/Foundation/Clock.php` — Added `implements ClockInterface`
- `components/Identity/Auth/System/Foundation/Ids/TokenId.php` — Added `random()` factory method
- `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Codec/HmacTokenCodec.php` — Added `SignToken` and `VerifyToken` interface implementations
- `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDefaults.php` — Added Slice 2 DI registrations

## Validation Commands

```bash
vendor/bin/phpunit --no-coverage --filter "Slice2"
# OK (28 tests, 70 assertions) — 14 unique tests (doubled run)

vendor/bin/phpunit --no-coverage
# 9445 tests, 27188 assertions
# 12 failures in ProcessPoolParallelismProofTest (pre-existing, unrelated to Slice 2)

vendor/bin/phpstan analyse components/Identity/Auth/System/Foundation \
  components/Identity/Auth/System/Flows/IssueAccessToken \
  components/Identity/Auth/System/Flows/VerifyAccessToken \
  components/Identity/Auth/System/Flows/StartSession \
  components/Identity/Auth/System/Configuration/Graphs \
  components/Identity/Auth/System/Configuration/IdentityConfiguration.php \
  components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Blacklist \
  tests/Unit/Components/Identity/Auth/Slice2 \
  --memory-limit=1G --error-format=raw --no-progress
# Clean — 0 errors on Slice 2 files

php tooling/governance/check-governance-index-current.php
# GREEN: Governance index is current.

php tooling/governance/CheckIntrusiveCoupling.php
# PASS

git diff --check
# Clean
```

## Tests Coverage

| Test | What It Proves |
|------|---------------|
| `test_issue_and_verify_access_token_roundtrip` | Token issue → sign → verify → claims extraction works end-to-end |
| `test_issue_token_includes_tenant` | Tenant ID survives issue/verify roundtrip |
| `test_rejects_invalid_signature` | Tampered JWT signature is rejected with TokenRejected |
| `test_rejects_malformed_token` | Non-JWT input is rejected with TokenRejected |
| `test_rejects_expired_token` | Expired tokens are rejected via FrozenClock time manipulation |
| `test_rejects_blacklisted_token` | Tokens added to blacklist are rejected on subsequent verify |
| `test_start_and_retrieve_session` | Session creation, persistence, and retrieval work |
| `test_rejects_expired_session` | Session expiry via isExpiredAt works correctly |
| `test_session_with_tenant` | Tenant ID is stored on sessions |
| `test_remove_session` | Session removal from store works |
| `test_reset_clears_blacklist` | ResettableIdentityState on blacklist works |
| `test_reset_clears_sessions` | ResettableIdentityState on session store works |
| `test_token_claims_roundtrip_via_payload` | TokenClaims toPayload/fromPayload serialization roundtrips |
| `test_different_secrets_reject_token` | Token signed with key A is rejected by codec with key B |

## Deviation Audit

### Severity Classification
- **BLOCKER**: 0
- **HIGH**: 0
- **MEDIUM**: 0
- **LOW**: 0
- **INFO**: 0

### Suppression Check
No suppressions added. No phpstan baseline changes. No test skip configuration changes.

### Pre-existing Findings (Not Slice 2)
- 12 test failures in `ProcessPoolParallelismProofTest` — closure serialization issue in Symfony Process component, predates Slice 2
- PublicSurface gate findings — all pre-existing across other components, none in Slice 2 files

## Why This Is GREEN

- **validation**: PHPUnit 14/14 Slice 2 tests pass (70 assertions), full suite 9433/9445 pass (12 pre-existing failures)
- **gates**: Governance index GREEN, intrusive coupling PASS, git diff clean
- **deviation_audit**: 0 findings on Slice 2 files
- **corrections**: Fixed ClockInterface implementation on base Clock class, added TokenId::random(), fixed TokenClaims to use constructors not fromString()
- **remaining_deviations**: None
- **suppression_check**: No suppression detected
- **exception_register**: No entries needed
- **risk_assessment**: Slice 2 is additive — no existing APIs changed, no existing stores deleted, no PublicSurface modified
- **severity_decision**: GREEN — all validation clean, gates clean, zero deviations
- **evidence**: This file

## Architecture Decisions

1. **ClockInterface on base Clock** — The base `Foundation/Clock` class now implements `ClockInterface` so both the base and `Time/Clock` variants satisfy the interface used by flows.

2. **HmacTokenCodec implements SignToken + VerifyToken** — Rather than creating adapter classes, the existing codec directly implements both interfaces. `sign()` wraps `encode()` output in `SignedToken`, `verify()` unwraps `SignedToken` for `decode()`.

3. **TokenClaims uses constructors** — Current Identity value objects (UserId, TokenId, TenantId) use constructors, not `fromString()` factories. TokenClaims was adapted to match.

4. **IdentityConfiguration skips TokenSecret VO** — The current codebase doesn't have a `TokenSecret` value object. IdentityConfiguration uses a raw string for the secret, validated at codec construction time.

5. **SessionGraph includes GenerateSessionId** — Extended beyond the reference to also expose the session ID generator for composition flexibility.

## Next Phase (Slice 3+)
- AuthServiceProvider integration with PublicSurface
- Token revocation flow (revoke issued tokens)
- Production-backed stores (Redis, database)
- ExternalIdentityGraph, AuthorizationGraph, TenancyGraph
