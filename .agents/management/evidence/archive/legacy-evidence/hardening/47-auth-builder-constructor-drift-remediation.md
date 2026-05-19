# Phase B: AuthBuilder Constructor Drift Remediation

## Summary

Fixed 184 PHPStan errors caused by constructor signature drift in `AuthBuilder::ready()`.

## Root Cause

Multiple capability classes had their constructors refactored (parameter names, types, order changed) but `AuthBuilder::ready()` still called them with old signatures. This is constructor drift, not location issues — AuthBuilder is correctly in `Configuration/Builders/` (approved composition context).

## Classes Fixed

### Login/Logout/Register — Architectural simplification
- **Login**: Was 11 params → Now 4 (FindUserByCredentials, VerifyPassword, StartAuthenticatedSession, IdentityInterface)
- **Logout**: Was 7 params → Now 2 (ClearAuthenticatedIdentity, IdentityInterface)
- **Register**: Was 8 params → Now 4 (ValidateRegistrationData, HashRegisteredPassword, CreateRegisteredUser, IdentityInterface)

### New capability classes instantiated
- FindUserByCredentials, VerifyPassword, StartAuthenticatedSession, ClearAuthenticatedIdentity
- ValidateRegistrationData, HashRegisteredPassword, CreateRegisteredUser

### Parameter name fixes (old → new)
- `$clientRegistry` → `$oAuthClientRegistry` (OAuth classes)
- `$configurationStore` → `$tenantSecurityConfigurationStore` (TenantSecurity)
- `$changeRequestStore` → `$tenantSecurityChangeRequestStore` (TenantSecurity)
- `$requestObjectStore` → `$oidcRequestObjectStore` (OIDC)
- `$elevationStore` → `$adminElevationStore` (AdminRealm)
- `$store` → `$lifecycleStore` (Lifecycle)
- `$codeStore` → `$authorizationCodeStore` (OAuth)
- `$mfaStore` → `$generalMfaStore` (MFA StartMfaChallenge)
- `$challengeStore` → `$mfaChallengeStore` (MFA)
- `$inner` → `$auditLog` (CorrelatingAuditLog)
- `$emailVerificationState` → `$emailVerificationStateStore` (variable rename)
- `$runtime` → `$passkeyRuntime` (Passkey)
- `$credentialStore` → `$passkeyCredentialStore` (Passkey)
- `$readPasskeys` → `$listPasskeys` (Passkey facade)
- `$rateLimit` → `$loginRateLimit` (ChangePassword)

### Type fixes
- `IdentityInterface` accepted by Login/Logout/Register instead of concrete `Identity`
- `UserSourceInterface` accepted by FindUserByCredentials/CreateRegisteredUser
- `CreateRegisteredUser` now creates proper `User` value objects with IdGenerator
- Nullable `$jwtIdentity` guarded with throw in OAuth exchange classes

## Files Changed (AuthBuilder ecosystem)

- `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` — 184 error fixes
- `components/Identity/Auth/System/Flows/Login/Login.php` — IdentityInterface
- `components/Identity/Auth/System/Flows/Login/FindUserByCredentials.php` — UserSourceInterface
- `components/Identity/Auth/System/Flows/Logout/Logout.php` — IdentityInterface
- `components/Identity/Auth/System/Flows/Register/Register.php` — IdentityInterface
- `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php` — UserSourceInterface + User object creation

## Verification

```
vendor/bin/phpstan analyse components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php
# 0 errors
```
