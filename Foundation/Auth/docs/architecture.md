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
├── beginAdminElevation() / endAdminElevation() / requireAdminElevation()
├── changePassword()
├── register()
├── refresh()
├── suspendUser() / reactivateUser() / deprovisionUser()
├── beginPasskeyRegistration() / completePasskeyRegistration()
├── beginPasskeyAuthentication() / completePasskeyAuthentication() / readPasskeys() / renamePasskey() / revokePasskey()
├── registerFederationConnection() / readFederationConnections() / discoverFederationConnection()
├── startFederatedLogin() / completeFederatedLogin()
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
│   ├── OAuth/           # client registry and auth-code contracts
│   ├── Passkey/         # passkey credentials, challenges, runtime seam
│   ├── PasswordHashing/
│   ├── Risk/            # deterministic risk signals and decisions
│   ├── Session/         # tracked session ownership and revocation
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
│   ├── OAuth/
│   ├── Passkey/
│   ├── Provisioning/
│   ├── ReadCurrentUser/
│   ├── Recover/
│   ├── Register/
│   ├── Risk/
│   ├── Session/
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
  fresh-MFA, and admin-elevation requirements.
- Passkeys and federation stay adapter-first: the kernel owns orchestration and storage contracts, while standards-heavy
  protocol work stays behind runtime interfaces.
- Maintenance work stays local to the owning slice through `CleanupExpired*` flows and `Flow/Diagnostics/ExportAuditEvents/`.
- MFA freshness now survives ingress boundaries through package-owned session/JWT claims (`mfaVerifiedAt`).
- Exception mapping stays at public or ingress boundaries; deep flow code returns domain-safe failures.

## Compatibility Notes

- The old `User` entity is no longer the public auth output.
- `System/Configuration/AuthServiceProvider` moved to `Integrations/AvaxContainer/AuthServiceProvider`.
- Session/JWT adapters stay reusable, but public consumers should code to `Auth`, `AuthenticationContext`, and
  `AuthenticationResult`.
