# AuthBuilder Return Boundary Decision

Date: 2026-05-19
Branch: main
Decision Owner: AvaX architecture/security owner
Scope: `AuthBuilder::ready()` return boundary and graph exposure

## Decision Question

Is `AuthBuilder::ready()` supposed to return minimal `PublicSurface\Auth`?
Or should it return a fuller Auth runtime object?

## Current State

`AuthBuilder::ready()` returns `new Auth(identity: $identity)`.

The `Auth` public surface exposes:
- `login()`, `logout()`, `user()`, `guest()`, `check()`
- `register()`, `changePassword()`
- `logoutAllSessions()`

All of these delegate to the `Identity` root object.

## Graph Parts Built During ready()

### Exposed through Auth -> Identity:
- Identity.authentication (login, logout, refreshAuthentication)
- Identity.sessions (logoutAllSessions, readActiveSessions, revokeSession)
- Identity.account (changePassword, beginEmailChange, confirmEmailChange, register)
- Identity.recovery (beginPasswordReset, resetPassword)
- Identity.verification (beginEmailVerification, verifyEmail)
- Identity.mfa (full MFA lifecycle)
- Identity.passkey (full passkey lifecycle)

### Built but NOT reachable through Auth:
- Tenancy (tenants, security) — fully assembled, unreachable
- SCIM (directories, provisioning, bulk, groups, outage) — fully assembled, unreachable
- OAuth/OIDC (client management, authorization, token exchange, introspection) — fully assembled, unreachable
- SingleSignOn/Federation (federated login, connections, metadata sync) — fully assembled, unreachable
- ExternalIdentity (oauth + OIDC + SSO wrapper) — fully assembled, unreachable
- IdentitySync (scim + provisioning wrapper) — fully assembled, unreachable
- Diagnostics (authIssueExplainer) — fully assembled, unreachable
- AssessCurrentRisk, ReadRiskSignals — fully assembled, unreachable
- Provisioning (suspend, reactivate, deprovision) — fully assembled, unreachable
- RequireAdminElevation — constructed but unreachable
- GroupRoleMappingValidator — constructed but unreachable
- AuthCapabilityReadiness — used internally, not exposed

## Decision for V5.9

**The current return boundary is ACCEPTED as correct for V5.9.**

Rationale:

1. **Minimal identity usage is the common case.** Most applications only need login/logout/register/password. Returning a minimal `Auth(identity: $identity)` is correct for this path.

2. **The unreachable graph parts are intentional deferred wiring.** The AuthBuilder DSL accepts configuration for tenancy, SCIM, OAuth, OIDC, federation, etc. These objects are assembled so that:
   - They are validated at build time (fail-fast if misconfigured)
   - They will be reachable when the Auth public surface grows
   - They prove the builder can construct the full graph

3. **This is not a waste.** The objects are constructed once at boot, not per-request. The assembly cost is paid during application startup. The objects are not leaked or lost — they are simply not yet exposed through the public surface.

4. **The alternative is worse.** Returning a massive god-object with every capability would violate the small-public-surface principle. Incrementally exposing capabilities through targeted Auth surface methods is the correct approach.

## What Would Count as a Breaking Public DSL Change

Any change that:
- Removes an existing `Auth` public method
- Changes the return type of an existing `Auth` public method
- Changes the parameter types/signature of an existing `Auth` public method
- Adds a required parameter to an existing builder DSL method
- Removes an existing builder DSL method
- Changes the exception type thrown by an existing public method

## What is Deferred

1. **Tenancy exposure through Auth** — `Auth::tenancy()`, `Auth::switchTenant()`, etc.
2. **SCIM exposure through Auth or separate surface** — SCIM is typically an admin API, not end-user auth.
3. **OAuth/OIDC exposure** — These are external identity protocols, not core auth.
4. **Federation exposure** — SSO/Federation requires separate entry points.
5. **Risk assessment exposure** — `Auth::assessRisk()` may be useful but is internal-first.
6. **Provisioning exposure** — Admin lifecycle operations, not end-user auth.

## Known Issues

### FEDERATION_READINESS_BUG (FIXED)

`AssembleAuthIdentityGraph` always passed `federationRuntime: null` to `AuthCapabilityReadiness::from()` (line 226). This meant `federation()` ALWAYS returned false, even when a `FederationRuntimeInterface` was configured via `AuthBuilder::withFederationRuntime()`.

Fix applied: Added `FederationRuntimeInterface|null $federationRuntime = null` parameter to `AssembleAuthIdentityGraph` constructor and passed `$this->federationRuntime` from `AuthBuilder::ready()`.

Proven by characterization test: `federationRuntimeConfigurationEnablesFederationReadiness` passes with runtime, `federationReadinessIsFalseWithoutRuntime` passes without runtime.

## AuthIdentityGraph Array Contract (ACCEPTED_YELLOW)

The `AssembleAuthIdentityGraph::assemble()` method returns a plain array with 14 string keys. This is an ACCEPTED_YELLOW for V5.9:

- No behavior change risk from converting to typed readonly object
- The array is unpacked immediately in `AuthBuilder::ready()` and never exposed externally
- Converting to a typed `AuthIdentityGraph` result object is safe but not required for this batch

Decision: DEFER typed result object to second extraction slice. Accept current array contract with rationale that it is an internal-only boundary.

## V5.9 Decision Summary

| Aspect | Decision | Status |
|--------|----------|--------|
| Return minimal Auth(identity) | ACCEPTED | GREEN |
| Unreachable graph parts assembled at boot | INTENTIONAL DEFERRED WIRING | GREEN_WITH_YELLOW |
| Array return from AssembleAuthIdentityGraph | INTERNAL CONTRACT, DEFER TYPING | ACCEPTED_YELLOW |
| Federation readiness bug | FIXED | GREEN |
| No public DSL changes | MAINTAINED | GREEN |
| Characterization tests | ADDED (5 tests, 18 assertions) | GREEN |
| AuthenticationContext named parameter bug | FIXED | GREEN |
