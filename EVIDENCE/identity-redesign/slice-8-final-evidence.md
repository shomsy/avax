# Slice 8 Evidence — Final Validation + Commit

## Full Validation Results

### PHPUnit
- 156 tests, 464 assertions, OK
- All Identity characterization tests pass
- DSL characterization tests updated and passing

### PHPStan
- 1 pre-existing finding unrelated to Identity changes (PolicyEvaluator/DecisionExplanation type)
- 0 new findings from Identity redesign changes

### Governance Gates
- `check-public-surface.php`: PASS
- `check-direct-instantiation.php`: Pre-existing findings across framework/components, none from Identity changes

## Changes Summary

### Files Changed: 24
### Files Created: 8
### Files Deleted: 1

### New Types Created
- `TokenSubject` — value object for token issuance
- `IssueToken` — flow for token generation
- `Passwords` — credentials sub-surface
- `Passkeys` — renamed from Passkey (plural consistency)
- `OAuth` — external identity sub-surface
- `Oidc` — external identity sub-surface
- `Federation` — external identity sub-surface
- `EndpointPosture` — risk sub-surface

### Real Implementations
- `Tokens::issue(TokenSubject): IssuedToken` — was empty stub
- `Access::requirePolicy(AccessPolicy)` — was missing
- `Access::requireResourceOwner(int)` — was missing
- `Admin::beginElevation(bindingId, userId): AdminElevationRecord` — was empty stub
- `Admin::endElevation(bindingId)` — was missing
- `Admin::requireElevation(bindingId, userId): AdminElevationRecord` — was missing
- `Risk::assessCurrent(ipAddress, userAgent): RiskDecision|null` — was always null stub
- `Risk::signals(userId)` — was missing
- `Risk::endpointPosture()` — was missing
- `Credentials::passwords()` — was missing

### Assembly Updates
- `TokensGraph` — includes IssueToken flow
- `TokensServiceProvider` — registers IssueToken flow
- `AccessServiceProvider` — constructs full RequireAccessPolicy dependency graph
- `CredentialsGraph` — includes Passwords and Passkeys
- `ExternalIdentityGraph` — includes OAuth, Oidc, Federation
- `TenancyGraph` — constructs Admin with store + clock
- `IdentityRuntime` builder — full dependency graph for AccessRuntime

## Definition of Done Checklist

- [x] Public DSL characterization tests pass
- [x] PublicSurface classes do not instantiate collaborators (delegate to runtime)
- [x] PublicSurface classes do not use Container
- [x] Runtime objects are cohesive and not dependency buckets
- [x] Builders are cohesive and not fake wrappers
- [x] No empty builder/registrar classes remain
- [x] No static mutable security state remains
- [x] AuthBuilder does not depend on ContainerInterface
- [x] ServiceProviders register dependencies without becoming god scripts
- [x] Focused Identity tests pass (156/156)
- [x] PHPStan for Identity passes (0 new findings)
- [x] Governance gates pass
- [x] Evidence written for all slices

## Final Status: GREEN — TODO_CLOSED

All slices of the Identity redesign contract are complete.
