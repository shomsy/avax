# Architecture Overview

The package now reads in two lanes:

- `System/` is the auth kernel.
- `integrations/` is the optional integration surface.

## Canonical Public Surface

- `Auth` is the single package entrypoint.
- `AuthenticationRequest` is the single request ingress input.
- `AuthenticationContext` is the single runtime auth state model.
- `AuthenticationResult` is the single successful auth output model.
- `AuthenticatedUser` is the public auth user snapshot.

## Runtime Ownership

```text
Auth
├── login()
├── authenticateRequest()
├── current()
├── logout()
├── logoutAllSessions()
├── readActiveSessions()
├── revokeSession()
├── access()
├── registerOAuthClient() / readOAuthClients() / readWorkloadIdentities()
├── authorizeOAuthCode() / exchangeOAuthCode() / exchangeOAuthClientCredentials() / exchangeOAuthRefreshToken()
├── revokeOAuthToken() / introspectOAuthToken()
├── readOidcProviderMetadata() / readOidcJsonWebKeySet() / readOidcUserInfo()
├── beginAdminElevation() / endAdminElevation() / requireAdminElevation()
├── changePassword()
├── register()
├── refresh()
├── suspendUser() / reactivateUser() / deprovisionUser()
├── beginPasskeyRegistration() / completePasskeyRegistration()
├── beginPasskeyAuthentication() / completePasskeyAuthentication() / readPasskeys() / renamePasskey() / revokePasskey()
├── registerFederationConnection() / readFederationConnections() / discoverFederationConnection()
├── startFederatedLogin() / completeFederatedLogin()
├── registerScimDirectory() / rotateScimToken() / provisionScimUser() / deleteScimUser() / readScimUsers() / syncScimGroups()
├── readTenantSecurityConfiguration() / beginTenantSecurityChange()
├── approveTenantSecurityChange() / applyTenantSecurityChange() / rollbackTenantSecurityChange()
├── assessCurrentRisk() / readRiskSignals()
├── beginPasswordReset() / resetPassword()
├── beginEmailVerification() / verifyEmail()
└── startMfaEnrollment() / confirmMfaEnrollment() / beginMfaChallenge() / verifyMfaChallenge() / regenerateBackupCodes() / disableMfa() / beginMfaRecovery() / confirmMfaRecovery()
```

## Flow Slices

```text
System/
├── Auth.php
├── AuthInterface.php
├── Configuration/
│   └── AuthBuilder.php
├── Capability/
│   ├── Access/          # authorization boundary + composed access policy
│   ├── AdminRealm/      # privileged elevation state
│   ├── Federation/      # tenant-aware SSO contracts and identity links
│   ├── Identity/        # session/JWT strategy coordination
│   ├── Oidc/            # provider metadata, JWKS, ID-token seams
│   ├── OAuth/           # client registry and auth-code contracts
│   ├── Passkey/         # passkey credentials, challenges, runtime seam
│   ├── PasswordHashing/
│   ├── Risk/            # deterministic risk signals and decisions
│   ├── Scim/            # directory config, provisioning identity, group sync
│   ├── Session/         # tracked session ownership and revocation
│   ├── TenantSecurity/  # tenant security config and change workflow
│   ├── Throttle/        # auth-sensitive throttling contracts
│   ├── User/            # internal domain entity + value objects
│   └── UserSource/      # persistence port
├── Flow/
│   ├── AdminRealm/
│   ├── AuthenticateRequest/
│   ├── ChangePassword/
│   ├── CheckAuthentication/
│   ├── Diagnostics/
│   ├── Federation/
│   ├── Login/
│   ├── Logout/
│   ├── Mfa/
│   │   ├── Backup/
│   │   ├── Challenge/
│   │   ├── Disable/
│   │   ├── Enroll/
│   │   ├── Recover/
│   │   └── StepUp/
│   ├── Oidc/
│   ├── OAuth/
│   ├── Passkey/
│   ├── Provisioning/
│   ├── ReadCurrentUser/
│   ├── Recover/
│   ├── Register/
│   ├── Risk/
│   ├── Scim/
│   ├── Session/
│   ├── TenantSecurity/
│   ├── Token/
│   └── Verify/
└── Foundation/

integrations/
├── avax-container/
├── cookies/
├── headers/
└── http/
```

## Boundary Rules

- kernel code never imports the `Integrations` namespace
- optional adapters depend on kernel contracts only
- Session and JWT are strategies behind the same `Identity` coordination contract.
- OAuth owns a separate machine/API lane and does not reuse browser session flows as its token model.
- `Capability/Session/` is the durable truth for tracked web sessions when the application provides a registry.
- Request authentication happens only in `Flow/AuthenticateRequest/AuthenticateRequest.php`.
- Authorization reads `CurrentAuthentication`; it no longer reaches into session/JWT adapters directly.
- `Capability/Access/Policy/AccessPolicy` is the package-owned way to combine role, permission, resource-owner,
  fresh-MFA, admin-elevation, and explicit actor-tier assurance requirements.
- `Capability/Access/Policy/IdentityPolicyCatalog` owns the package default assurance matrix for user, privileged user,
  admin, support, tenant admin, machine identity, and break-glass posture.
- `Capability/Oidc/` owns asymmetric provider behavior that is small enough to stay package-local; framework-neutral
  HTTP publishing lives in `integrations/http/Oidc/`, while certification remains outside the kernel and logout,
  dynamic client registration, plus request-object claim validation are handled as kernel-owned OIDC flows.
- Passkeys and federation stay adapter-first: the kernel owns orchestration and storage contracts, while standards-heavy
  protocol work stays behind runtime interfaces.
- `Capability/OAuth/SenderConstraint/` owns DPoP and mTLS binding metadata for sender-constrained token posture.
- `Capability/Scim/` owns directory and provisioned-identity truth, while the HTTP adapter lives in
  `integrations/http/Scim/`; external directory UIs and broader workforce-control-plane productization still remain separate.
- `Capability/TenantSecurity/` owns tenant-scoped security configuration and approval lifecycle, while the thin admin
  API adapter lives in `integrations/http/TenantSecurity/` and the richer admin UI plus tenancy-management product
  surfaces remain application-owned.
- Maintenance work stays local to the owning slice through `CleanupExpired*` flows and `Flow/Diagnostics/ExportAuditEvents/`.
- MFA freshness now survives ingress boundaries through package-owned session/JWT claims (`mfaVerifiedAt`).
- Exception mapping stays at public or ingress boundaries; deep flow code returns domain-safe failures.

## Compatibility Notes

- The old `User` entity is no longer the public auth output.
- `System/Configuration/AuthServiceProvider` moved to `Integrations/AvaxContainer/AuthServiceProvider`.
- Session/JWT adapters stay reusable, but public consumers should code to `Auth`, `AuthenticationContext`, and
  `AuthenticationResult`.
