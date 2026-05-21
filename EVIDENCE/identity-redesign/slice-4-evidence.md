# Slice 4 Evidence — Real Implementations

## Scope

Replace stub/empty implementations with real behavior:
- `Tokens::issue(string $sub): void` (empty body) → `Tokens::issue(TokenSubject $subject): IssuedToken`
- `Access::requirePolicy(AccessPolicy)` — missing from PublicSurface and Runtime
- `Access::requireResourceOwner(int)` — missing from PublicSurface and Runtime
- `Credentials::passwords()` — missing sub-surface

## Changes

### Tokens::issue() — Real Implementation

**Before:** `Tokens::issue(string $sub): void` with empty body.

**After:** `Tokens::issue(TokenSubject $subject): IssuedToken` returns a real issued token.

New types created:
- `TokenSubject` — value object for token subject (userId + claims)
- `IssueToken` — flow that generates token ID, builds claims, encodes via TokenCodec, returns IssuedToken

Assembly updated:
- `TokensGraph::fromRuntime()` now constructs `IssueToken` and passes it to `Tokens`
- `TokensServiceProvider` now registers `IssueToken` flow

### Access::requirePolicy() and requireResourceOwner()

- Added `RequireAccessPolicy` and `RequireResourceOwner` as constructor dependencies to `AccessRuntime`
- Added `requirePolicy(AccessPolicy)` and `requireResourceOwner(int)` to:
  - `AccessRuntime` (delegates to flows)
  - `Access` PublicSurface (delegates to runtime)
  - `AccessInterface` (contract)
  - `Authorization` facade (implements AccessInterface)
- Fixed stale `RequireAccessPolicy` import of deleted `RequirePermission\PermissionDenied` → now uses `Foundation\Exception\PermissionDenied`
- Updated `IdentityRuntime` builder to construct full `RequireAccessPolicy` dependency graph
- Updated `AccessServiceProvider` to register all required flows

### Credentials::passwords()

- Created `Passwords` public surface class (stub, following Mfa/Passkey pattern)
- Added `Passwords` dependency to `CredentialsRuntime`
- Added `passwords()` to `Credentials` PublicSurface and `CredentialsRuntime`
- Updated `CredentialsGraph` assembly

## Validation

- PHPUnit: 156 tests, 462 assertions, OK
- PHPStan: clean on all changed files
- Pre-existing PHPStan findings unrelated to this slice (test trivia, mixed variables, etc.)

## Tests Updated

- `AccessCharacterizationTest::makeAccess()` — added new dependencies to AccessRuntime construction
- `IdentityTargetDslCharacterizationTest::tokensIssueReturnsIssuedToken` — now uses `TokenSubject` and asserts `IssuedToken` return type

## 15 Corrections Status

All 15 known corrections from the plan remain addressed (Slices 1-3). No regressions.

## Final Status: GREEN
