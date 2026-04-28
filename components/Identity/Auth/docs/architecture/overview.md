# Architecture Overview

`Foundation/Auth` is a pure PHP auth kernel with one canonical production root: `System/`.

The repository root separates production code from `tests/`, `docs/`, `examples/`, and `tooling/`.
Inside `System/`, the architecture reads in this order:

1. public surface: [System/Auth.php](../../System/Auth.php), [System/AuthInterface.php](../../System/AuthInterface.php)
2. root ownership zones: `Access/`, `Identity/`, `ExternalIdentity/`, `IdentitySync/`, `Tenancy/`, `Diagnostics/`
3. implementation lanes: `Flow/`, `Capability/`, `Configuration/`, `Foundation/`

The root ownership zones are thin, explicit owner units:

- `Access/` owns authentication context, access enforcement entry, admin elevation, and risk entry.
- `Identity/` owns local account authentication, sessions, recovery, MFA, verification, and passkeys.
- `ExternalIdentity/` owns OAuth, OIDC, federation, and protocol-facing identity contracts.
- `IdentitySync/` owns SCIM, provisioning lifecycle, and directory synchronization.
- `Tenancy/` owns tenants, membership, invitations, transfer, and tenant security change control.
- `Diagnostics/` owns explainability and operator-facing auth diagnostics.

`Flow/` remains the local use-case lane where the detailed implementations live.
`Capability/` remains the shared ability lane used by multiple flows.

The rule is stable:

- folder says flow or capability
- unit says responsibility
- function says exact action
