# Identity Component Test Strategy

## Overview

This document defines the test strategy for the AvaX Identity component. Testing is risk-based and behavior-first, following the AvaX Risk-Based Behavioral Testing Rule.

## Testing Principles

1. **Behavior over coverage**: Tests must prove behavior, not construction
2. **Risk-based**: Critical flows and security boundaries receive the most thorough testing
3. **Negative tests required**: Security-sensitive flows must have denial-path tests
4. **Fail-closed proof**: Authentication must prove it denies access on any uncertainty
5. **Observable behavior**: Security events must be verifiable through test assertions

## Test Categories

### 1. Authentication Tests

**Happy Path**:
- Valid password authentication produces verified identity
- Valid passkey authentication produces verified identity
- Valid OAuth/OIDC authentication produces verified identity
- Valid MFA challenge completion produces verified identity
- API key authentication produces verified identity

**Failed-When**:
- Invalid password fails authentication
- Expired passkey fails authentication
- Invalid OAuth token fails authentication
- Failed MFA challenge fails authentication
- Invalid API key fails authentication
- Unknown credential type fails authentication

**Security**:
- Generic error messages on all failures (no information leakage)
- Credentials are never logged
- Authentication fails closed on uncertainty
- Brute force attempts are rate-limited
- Account lockout triggers after threshold

### 2. Token Tests

**Happy Path**:
- Valid token passes all validation checks
- Token validation produces verified identity
- Token refresh produces new access and refresh tokens
- Token revocation prevents further use

**Failed-When**:
- Invalid signature fails validation
- Expired token fails validation
- Invalid issuer fails validation
- Invalid audience fails validation
- Revoked token fails validation
- Malformed token fails validation
- Algorithm confusion attack fails validation

**Security**:
- Token values are never logged
- Short-lived access tokens limit exposure window
- Refresh token rotation detects concurrent use
- Token binding prevents transfer

### 3. Session Tests

**Happy Path**:
- Session creation produces valid session
- Session renewal extends session lifetime
- Session termination removes session state
- Concurrent sessions are independently manageable

**Failed-When**:
- Invalid session ID fails session loading
- Expired session fails validation
- Terminated session fails validation
- Session fixation attack is prevented (ID regeneration)

**Security**:
- Session IDs are cryptographically random
- Session state does not leak between requests in long-lived workers
- Sessions are invalidated on security events (password change, logout)
- Session cookies have correct security attributes

### 4. Tenant Resolution Tests

**Happy Path**:
- Token claim tenant is resolved correctly
- Header tenant is resolved correctly
- Subdomain tenant is resolved correctly
- Path segment tenant is resolved correctly
- Tenant validation against identity succeeds

**Failed-When**:
- Missing tenant on tenant-required request fails
- Non-existent tenant fails resolution
- Identity without tenant access fails
- Conflicting tenant signals fail closed

**Security**:
- Tenant isolation is enforced at data access
- Cross-tenant data access is denied
- Tenant context is per-request, not per-session

### 5. Authorization Tests

**Happy Path**:
- Authorized action on resource is allowed
- Role-based permission grants access
- Resource-level permission grants access

**Failed-When**:
- Unauthorized action is denied
- Action on unauthorized resource is denied
- Missing permission is denied (deny-by-default)
- Route guard without resource-level check is insufficient

**Security**:
- Deny-by-default for unexpressed permissions
- Authorization protects the resource, not only the route
- Authorization decisions are observable

### 6. Observability Tests

**Happy Path**:
- Authentication attempt produces event
- Authentication success produces event
- Authentication failure produces event
- Token validation produces event
- Session lifecycle events are produced
- Authorization decisions produce events

**Security**:
- Security events do not contain credential values
- Security events contain enough context for investigation
- Events are correlated for attack detection

## Negative Test Requirements

Every security-sensitive flow MUST have negative tests:

| Flow | Required Negative Tests |
|------|----------------------|
| Password Auth | Invalid password, locked account, expired credential |
| Passkey Auth | Invalid challenge, expired challenge, wrong origin |
| OAuth/OIDC | Invalid token, wrong issuer, wrong audience, expired ID token |
| MFA | Invalid MFA code, expired MFA challenge, missing MFA when required |
| Token Validation | Invalid signature, expired, wrong issuer, wrong audience, revoked |
| Session | Invalid ID, expired, terminated, fixation attempt |
| Tenant Resolution | Missing tenant, invalid tenant, unauthorized tenant, conflicting signals |
| Authorization | Missing permission, wrong tenant, resource-level denial |

## Test Organization

```
tests/
  Identity/
    Authentication/
      PasswordAuthenticationTest.php
      PasskeyAuthenticationTest.php
      OAuthAuthenticationTest.php
      MFAAuthenticationTest.php
      ApiKeyAuthenticationTest.php
    Tokens/
      TokenValidationTest.php
      TokenIssuanceTest.php
      TokenRevocationTest.php
      TokenRefreshTest.php
    Sessions/
      SessionCreationTest.php
      SessionValidationTest.php
      SessionTerminationTest.php
      SessionFixationTest.php
    Tenant/
      TenantResolutionTest.php
      TenantValidationTest.php
      TenantIsolationTest.php
    Authorization/
      PermissionCheckTest.php
      RoleBasedAccessTest.php
      ResourceLevelAccessTest.php
    Observability/
      SecurityEventTest.php
    Integration/
      LoginFlowIntegrationTest.php
      RequestAuthIntegrationTest.php
```

## Coverage Policy

**V1 Phase**:
- 100% line coverage NOT required
- All critical flows MUST be tested (happy path + failed-when)
- All security boundaries MUST have negative tests
- All fail-closed behavior MUST be proven

**Production Hardening Phase**:
- Coverage increased toward meaningful thresholds
- Remaining uncovered code becomes audit target
- Branch/path coverage preferred over line coverage

## Validation Gates

All tests must pass:
```bash
vendor/bin/phpunit --filter Identity --no-coverage
```

Security-negative tests must prove denial:
```bash
vendor/bin/phpunit --filter ".*Negative.*|.*Failed.*|.*Denial.*|.*Unauthorized.*" --no-coverage
```
