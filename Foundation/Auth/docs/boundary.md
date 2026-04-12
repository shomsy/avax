# Kernel And Integration Boundary

This package is now split into two explicit lanes:

1. `System/` is the auth kernel.
2. `integrations/` is the optional integration surface.

The package does not try to be a full identity platform. It owns application
auth flows. It does not own enterprise control-plane products.

Current delivery posture is documented in
`docs/adr/001-auth-scope-and-trust-boundaries.md`.

## Auth Kernel

`System/` owns:

- public facade and boundary types: `Auth`, `AuthInterface`, `Access`,
  `AccessInterface`, `AuthenticationRequest`, `AuthenticationContext`,
  `AuthenticationResult`, `AuthenticatedUser`
- core flows: `AuthenticateRequest`, `Login`, `Register`, `Logout`, `Refresh`,
  `Recover`, `Verify`, `Mfa`, `ChangePassword`, `ReadCurrentUser`, `OAuth`
- shared auth capabilities: `Access`, `Identity`, `PasswordHashing`,
  `OAuth`, `Session`, `Throttle`, `UserSource`
- package-owned contracts: session store, token issuer/verifier, refresh store,
  audit log, clock, MFA store, user source
- package-owned runtime strategies for session and token auth
- auth diagnostics contracts and audit events

Kernel rule:

- integrations may call kernel contracts
- kernel code does not import integration namespaces

## Integration Surface

`integrations/` owns:

- container adapters such as `Integrations/AvaxContainer/AuthServiceProvider`
- transport-to-kernel mapping such as
  `Integrations/Http/MapAuthenticationRequest`
- boundary failure mapping such as `Integrations/Http/MapAuthFailure`
- header and cookie extraction glue used by transport mappers
- optional persistence, observability, and framework adapters that depend on
  kernel contracts only

Integration rule:

- adapters stay thin
- adapters never become required to understand kernel behavior
- swapping adapters must not change kernel contracts

## Explicit Non-Goals

This package does not own today:

- central IdP behavior
- OIDC provider behavior
- SAML federation brokering
- SCIM provisioning
- workforce lifecycle management
- adaptive risk as a standalone platform
- enterprise IAM control-plane workflows

## Final Target Tree

```text
System/
  Auth.php
  AuthInterface.php
  Configuration/
    AuthBuilder.php
  Capability/
    Access/
    Identity/
    OAuth/
    PasswordHashing/
    Session/
    Throttle/
    User/
    UserSource/
  Flow/
    AuthenticateRequest/
    ChangePassword/
    CheckAuthentication/
    Diagnostics/
    Login/
    Logout/
    Mfa/
    OAuth/
    ReadCurrentUser/
    Recover/
    Register/
    Session/
    Token/
    Verify/
  Foundation/
    Clock.php
    IdGenerator.php

integrations/
  avax-container/
    AuthServiceProvider.php
  http/
    HttpAuthenticationInput.php
    HttpAuthFailure.php
    MapAuthenticationRequest.php
    MapAuthFailure.php
  headers/
    ReadBearerToken.php
  cookies/
    ResolveSessionAllowance.php
```

## Public API Freeze

Stable application API to keep public:

- `Auth`
- `AuthInterface`
- `Auth::configuration()` and `AuthBuilder`
- `Access`
- `AccessInterface`
- `AuthenticationRequest`
- `AuthenticationContext`
- `AuthenticationResult`
- `AuthenticationState`
- `Flow/Session/ActiveSession`
- `AuthenticatedUser`
- `Credentials`
- `RegistrationData`
- `RegistrationResult`
- `ChangePasswordData`
- `RefreshAuthenticationRequest`
- `RegisterClientData`
- `AuthorizeCodeData`
- `ExchangeAuthorizationCodeData`
- `ExchangeRefreshTokenData`
- `RevokeTokenData`
- `IntrospectTokenData`
- `OAuthTokenGrant`
- `IssuedAuthorizationCode`
- `TokenIntrospection`
- `PasswordResetChallenge`
- `EmailVerificationChallenge`
- `MfaEnrollment`
- `MfaChallenge`
- `MfaRecoveryChallenge`
- `BackupCodeSet`
- MFA request types used by `AuthInterface`

Stable extension contracts to keep public:

- `Identity`
- `UserSourceInterface`
- `PasswordHasher`
- `LoginRateLimit`
- `LoginRateLimitStorageInterface`
- `SessionStoreInterface`
- `SessionIdentityInterface`
- `SessionIdentity`
- `SessionCookieSettings`
- `TokenIssuerInterface`
- `TokenVerifierInterface`
- `TokenCodecInterface`
- `HmacTokenCodec`
- `OAuthClientRegistryInterface`
- `AuthorizationCodeStoreInterface`
- `JwtIdentityInterface`
- `JwtIdentity`
- `RefreshTokenStoreInterface`
- `TokenRevocationStoreInterface`
- `PasswordResetStoreInterface`
- `EmailVerificationStoreInterface`
- `EmailVerificationStateStoreInterface`
- `MfaStoreInterface`
- `MfaChallengeStoreInterface`
- `TotpInterface`
- `AuditLogInterface`
- `IdentityInterface`
- `Clock`
- `IdGeneratorInterface`

Move behind contract or treat as internal machinery:

- `Capability/User/*`
- `Capability/Identity/IssuedAuthentication`
- `Flow/*/*Record`
- `Flow/*/InMemory*`
- `Flow/AuthenticateRequest/ProjectAuthenticatedUser`
- `Flow/Mfa/TotpVerification`

Replace or deprecate:

- `System/Configuration/AuthServiceProvider` is replaced by
  `Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider`
- transport callers should stop hand-parsing bearer headers and use
  `Integrations/Http/MapAuthenticationRequest`
- HTTP boundaries should map kernel failures through
  `Integrations/Http/MapAuthFailure`

## Compatibility Risks

- `System/Configuration/AuthServiceProvider` moved to
  `Integrations/AvaxContainer/AuthServiceProvider`
- new `integrations/` namespace is autoloaded and should be used for optional
  framework glue
- access denial exception messages are now generic; callers needing the specific
  requirement must read `requirement()`

## Execution Plan

1. Audit every class as kernel, integration, or misplaced.
2. Freeze the public facade and boundary request/result types.
3. Keep one owner per flow inside `System/`.
4. Extract framework and transport glue into `integrations/`.
5. Document extension points, non-goals, and compatibility risks.
6. Verify kernel-only and adapter smoke paths.
