# Identity Target Architecture Component Dogfooding Review

## Components Used

- Application Container: `IdentityServiceProvider` registers configuration and runtime assembly through `ContainerInterface`.
- Identity Auth: root assembly uses existing `Auth` and `AuthIdentity::fromBackends()` compatibility path.
- Identity Access: root assembly uses existing `Access`, `AuthorizationEngine`, `BeginAdminElevation`, and `EndAdminElevation`.
- Identity Tokens: root assembly uses existing `TokensGraph::hmac()` assembly boundary.
- Identity Credentials, Tenancy, Risk, ExternalIdentity: root assembly reuses existing public surfaces.

## Components Bypassed

None newly bypassed by this slice. The root static DSL still cannot receive a container without a public API compatibility decision.

## Raw Primitives

- `GuestSessionIdentity` is a first-party fail-closed capability for guest/no-session behavior.
- `DateTimeImmutable` is used as explicit caller-provided time context for policy hour checks.

## Classification

DOGFOODS_EXISTING_COMPONENTS with ACCEPTED_YELLOW for the remaining static root DSL compatibility bridge.
