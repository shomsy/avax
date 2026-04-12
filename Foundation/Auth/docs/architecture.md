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
├── access()
├── changePassword()
├── register()
├── refresh()
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
│   ├── Access/          # authorization boundary
│   ├── Identity/        # session/JWT strategy coordination
│   ├── OAuth/           # client registry and auth-code contracts
│   ├── PasswordHashing/
│   ├── Session/         # tracked session ownership and revocation
│   ├── Throttle/        # auth-sensitive throttling contracts
│   ├── User/            # internal domain entity + value objects
│   └── UserSource/      # persistence port
├── Flow/
│   ├── AuthenticateRequest/
│   ├── ChangePassword/
│   ├── CheckAuthentication/
│   ├── Diagnostics/
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
│   ├── ReadCurrentUser/
│   ├── Recover/
│   ├── Register/
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
- MFA freshness now survives ingress boundaries through package-owned session/JWT claims (`mfaVerifiedAt`).
- Exception mapping stays at public or ingress boundaries; deep flow code returns domain-safe failures.

## Compatibility Notes

- The old `User` entity is no longer the public auth output.
- `System/Configuration/AuthServiceProvider` moved to `Integrations/AvaxContainer/AuthServiceProvider`.
- Session/JWT adapters stay reusable, but public consumers should code to `Auth`, `AuthenticationContext`, and
  `AuthenticationResult`.
