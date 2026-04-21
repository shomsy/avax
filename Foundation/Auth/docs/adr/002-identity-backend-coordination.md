# ADR 002: Identity Keeps Session And JWT Backend Coordination

Status: Accepted
Date: 2026-04-21

## Context

The review re-check questioned whether `System/Capabilities/Identity/Identity.php` still legitimately owns direct
`SessionIdentityInterface` and `JwtIdentityInterface` handles or whether that was leftover design debt.

## Decision

The current design is intentional and retained.

`Identity` continues to coordinate direct session and JWT backends because issuance, clearing, mode resolution, refresh
handoffs, and logout-side effects are cross-cutting identity concerns. Those responsibilities sit above narrower
sub-owners such as `Authentication`, `Sessions`, and `Passkey`.

## Why this stays here

- `Identity::issue()` must coordinate both backends to produce one coherent `IssuedAuthentication`.
- `Identity::clear()` must clear the session lane and revoke token state when the current context carries token data.
- Authentication mode resolution belongs to the identity coordinator, not to one specific runtime slice.
- Moving these handles lower would duplicate cross-cutting lifecycle rules and obscure the public identity boundary.

## Consequences

- The direct backend handles remain part of the `Identity` coordinator contract.
- Integrations that need to assemble only the backend portion should prefer the stable seam:
  `Identity::fromBackends(...)` or `AuthBuilder::withIdentityBackends(...)`.
- Future refactors should keep this coordination centralized unless a new design proves the cross-cutting behavior can
  be
  preserved without spreading issuance and clearing logic across multiple owners.
