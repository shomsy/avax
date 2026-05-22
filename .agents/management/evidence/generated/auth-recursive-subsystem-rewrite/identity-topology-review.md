# Identity Topology Review

Date: 2026-05-22
Branch: architecture/auth-recursive-subsystem-rewrite
Worktree: /home/shomsy/projects/avax-auth-rewrite-v2
Scope: `components/Identity/**`
Mode: HARNESS-FULL
Status: REVIEW_COMPLETE

## 1. Current Topology (Corrected After Governance Merge)

### 1.1 AuthBuilder Current State (NOT what old evidence says)

The AuthBuilder has been further refactored beyond the first-slice evidence:

| File | Lines | Role |
|------|-------|------|
| AuthBuilder.php | 560 | Fluent DSL — delegates to sub-graph builders |
| AssembleAuthIdentityGraph.php | 847 | Assembles core identity graph (sessions, account, recovery, verification, MFA, passkey, SCIM, tenancy) |
| AssembleAuthExternalIdentityGraph.php | 216 | Assembles external identity graph (OAuth, OIDC, Federation, SCIM provisioning, diagnostics) |
| CredentialAuthenticationGraph.php | 149 | Sub-builder for MFA/Passkey credential assembly |
| OAuthIdentityGraph.php | 82 | Sub-builder for OAuth/OIDC assembly |
| FederationIdentityGraph.php | 58 | Sub-builder for Federation assembly |
| ScimProvisioningGraph.php | 71 | Sub-builder for SCIM assembly |
| TenancyAdministrationGraph.php | 92 | Sub-builder for Tenancy assembly |
| **Total Assembly** | **2075** | |

**Key finding**: The "Phase 4 extraction" described in old evidence is ALREADY DONE. `AssembleAuthExternalIdentityGraph` exists (216 lines) and is invoked from AuthBuilder::ready(). AuthBuilder itself is now 560 lines — well within the 300-line threshold if we count only the DSL methods (the remaining pressure is from import statements and null-coalescing validation).

### 1.2 Identity Component Suite Topology

```
components/Identity/
  Access/           — Authorization, policies, throttling, access middleware
  Auth/             — Core authentication, identity root, assembly, builder DSL
  Credentials/      — MFA, TOTP, backup codes, passkeys
  ExternalIdentity/ — OAuth, OIDC, SSO, Federation
  Security/         — Security configuration, change management (minimal, 7 refs)
  Tenancy/          — Multi-tenant, admin elevation, tenant lifecycle
  Tokens/           — JWT, access tokens, refresh tokens, token lifecycle
```

### 1.3 Dependency Graph

```
Auth (root)
  ├── Access (policies, authorization, throttling)
  ├── Credentials (MFA, passkey, backup codes)
  ├── ExternalIdentity (OAuth, OIDC, Federation)
  ├── Tenancy (multi-tenant, admin elevation)
  ├── Tokens (JWT, token lifecycle)
  └── Security (security configuration — minimal)

Cross-cutting:
  Security/Hashing → Auth, Credentials, ExternalIdentity
  Application/Container → All (boot-time resolution)
  HTTP/Request → Tenancy (tenant resolution)
```

## 2. Ownership Truth

### 2.1 Correct Owners

| Responsibility | Current Owner | Correct? |
|----------------|---------------|----------|
| Authentication root | Auth/AuthBuilder | YES — DSL owner |
| Identity assembly | AssembleAuthIdentityGraph | YES — Configuration/Assembly |
| External identity assembly | AssembleAuthExternalIdentityGraph | YES — Configuration/Assembly |
| MFA/Passkey assembly | CredentialAuthenticationGraph | YES — focused sub-builder |
| OAuth assembly | OAuthIdentityGraph | YES — focused sub-builder |
| Federation assembly | FederationIdentityGraph | YES — focused sub-builder |
| SCIM assembly | ScimProvisioningGraph | YES — focused sub-builder |
| Tenancy assembly | TenancyAdministrationGraph | YES — focused sub-builder |
| MFA runtime | Credentials/Mfa/* | YES — capability owner |
| Passkey runtime | Credentials/Passkey/* | YES — capability owner |
| OAuth runtime | ExternalIdentity/OAuth/* | YES — capability owner |
| OIDC runtime | ExternalIdentity/OpenIDConnect/* | YES — capability owner |
| Federation runtime | ExternalIdentity/SingleSignOn/* | YES — capability owner |
| Token lifecycle | Tokens/* | YES — capability owner |
| Access policies | Access/* | YES — capability owner |
| Tenant lifecycle | Tenancy/* | YES — capability owner |
| Security config | Security/* | YES — but minimal (7 refs) |

### 2.2 Duplicate Ownership

The discipline reviews did not flag duplicate ownership across Identity components. This is a positive signal — the component boundaries are clear.

### 2.3 Orphaned Capabilities

- **Identity/Security**: Only 7 references. Owns `SecurityConfigurationStore`, `ApplySecurityChange`, `ApproveSecurityChange`, `BeginSecurityChange`. This is a minimal component that could potentially be absorbed into Tenancy (tenant security) or Auth (identity security). However, keeping it separate is valid if it grows into a full security lifecycle component.
- **DR-0005**: No component-specific tests detected for Identity/Security. This is a gap.

## 3. Global Complexity Reduction

### 3.1 Largest Units

| Unit | Lines | Params | Complexity Driver |
|------|-------|--------|-------------------|
| AssembleAuthIdentityGraph | 847 | 43 (DR-0601) | Assembles entire identity graph — sessions, account, recovery, verification, MFA, passkey, SCIM, tenancy, risk |
| DefaultAuth | 871 | N/A | Default identity capability implementation — delegates to all Identity owners |
| AuthBuilder | 560 | N/A (DSL) | Fluent DSL with 40+ methods — now mostly delegation |
| OAuth | 41 | 14 (DR-0573) | Data carrier with excessive constructor arity |
| OpenSslOidcProvider | 310 | 12 (DR-0575) | OIDC protocol provider — crypto operations |
| InMemoryOAuthClientRegistry | 405 | N/A (DR-0587) | In-memory store — large due to registry operations |
| PushAuthorizationRequest | 378 | N/A (DR-0580) | PAR flow — complex protocol logic |
| JwtIdentity | 390 | 10 (DR-0625) | JWT identity backend |
| SessionIdentity | 311 | 11 (DR-0623) | Session identity backend |

### 3.2 Complexity Reduction Opportunities

**Highest ROI**:
1. **AssembleAuthIdentityGraph (847 lines)** — Split by sub-domain:
   - Session assembly (~100 lines)
   - Account assembly (register, login, logout, change password) (~150 lines)
   - Recovery assembly (password reset, email verification, email change) (~150 lines)
   - MFA/Passkey assembly (delegates to CredentialAuthenticationGraph, but still builds primitives) (~100 lines)
   - SCIM assembly (delegates to ScimProvisioningGraph, but still builds primitives) (~100 lines)
   - Tenancy assembly (delegates to TenancyAdministrationGraph, but still builds primitives) (~100 lines)
   - Risk assessment assembly (~50 lines)

   **However**: Many of these sub-assemblies are already delegated to sub-graph builders. The 847 lines includes building ALL the individual flow/capability objects that the sub-graphs return as primitives. Splitting further requires deciding whether the sub-graph builders should return fully assembled objects (not just primitives) or whether AssembleAuthIdentityGraph should delegate at a higher level.

2. **DefaultAuth (871 lines)** — This is the default identity capability that wraps all Identity owners. Splitting requires identifying logical groupings:
   - Session management methods
   - Authentication methods
   - Account management methods
   - MFA methods
   - Passkey methods
   - Recovery methods

   **Risk**: DefaultAuth is a public-facing capability. Splitting changes its interface.

3. **OAuth (14 params, DR-0573)** — Introduce `OAuthConfiguration` value object to group related parameters.

**Medium ROI**:
4. **AuthBuilder (560 lines)** — Import count (~160 use statements) contributes significantly. The actual method bodies are thin delegation. Could reduce by:
   - Grouping imports (PHP 8+ group use declarations)
   - Moving some DSL methods to sub-graph builders directly (but this breaks the fluent chain)

5. **OpenSslOidcProvider (310 lines)** — Crypto provider. Could split by operation (sign, verify, encrypt, decrypt), but this is a single responsibility (OIDC crypto).

**Low ROI** (mechanical splitting):
6. InMemoryOAuthClientRegistry (405 lines) — Large due to CRUD operations. Splitting would create artificial boundaries.
7. PushAuthorizationRequest (378 lines) — Complex protocol, but single responsibility.

## 4. Change Locality

### 4.1 High Locality (changes stay contained)
- **Assembly classes** — Changes to how objects are constructed don't affect runtime behavior
- **Sub-graph builders** — Changes to CredentialAuthenticationGraph, OAuthIdentityGraph, etc. are isolated to their sub-domain
- **Foundation classes** — Clock, IdGenerator, exceptions — changes are local

### 4.2 Medium Locality (changes affect related flows)
- **Capability classes** — Changes to Mfa, OAuth, Tenants capabilities affect all flows that use them
- **Flow classes** — Changes to Login, Logout, Register flows affect the identity lifecycle

### 4.3 Low Locality (changes affect all consumers)
- **AuthBuilder DSL** — Changes to builder methods affect all callers
- **DefaultAuth** — Changes to default identity capability affect all users
- **PublicSurface (Auth, AuthInterface, User, Tokens, etc.)** — Changes affect external consumers

### 4.4 Safest Next Change
Assembly class refactoring is the safest change:
- No runtime behavior change
- No public API change
- Changes stay in Configuration/Assembly/
- Tests prove assembly produces the same objects

## 5. Intrusive Coupling

### 5.1 Boot-Time Coupling (Acceptable)
- AuthBuilder → All sub-graph builders → All Identity components
- This is expected for a composition root
- Cost is paid once at boot, not per-request

### 5.2 Runtime Coupling (By Design)
- DefaultAuth → All Identity capabilities (sessions, MFA, OAuth, etc.)
- This is intentional — DefaultAuth is the identity root
- Cannot reduce without splitting the identity root into smaller capabilities

### 5.3 Problematic Coupling
| Path | Issue | Severity |
|------|-------|----------|
| Tokens PublicSurface → `new` collaborators (DR-0382) | PublicSurface violates delegation rule | HIGH |
| UserRecord → `new` collaborators (DR-0357) | PublicSurface violates delegation rule | MEDIUM |
| User → `new` collaborators (DR-0358) | PublicSurface violates delegation rule | MEDIUM |
| Access → `new` collaborators (DR-0378) | PublicSurface violates delegation rule | MEDIUM |

### 5.4 Assembly Coupling
- AssembleAuthIdentityGraph receives 43 parameters — not coupling per se, but indicates the assembly class is the convergence point for ALL Identity sub-domains
- AssembleAuthExternalIdentityGraph receives 27 parameters — similarly a convergence point
- The sub-graph builders reduce this coupling by handling their own sub-domain assembly

## 6. Runtime Safety

### 6.1 Boot-Time vs Runtime
- **AuthBuilder**: Boot-time only — no worker state risk
- **AssembleAuthIdentityGraph**: Boot-time only — no worker state risk
- **AssembleAuthExternalIdentityGraph**: Boot-time only — no worker state risk
- **Sub-graph builders**: Boot-time only — no worker state risk
- **DefaultAuth**: Capability (not singleton) — no worker state risk from assembly
- **Identity capabilities**: Request-scoped — no static mutable state found

### 6.2 Null-Coalescing Fallback Construction
AssembleAuthIdentityGraph uses null-coalescing fallback `new` construction for shared primitives:
```php
$currentAuthentication ?? new CurrentAuthentication()
$projectAuthenticatedUser ?? new ProjectAuthenticatedUser()
// ... etc (DR-0363 through DR-0368)
```

This is boot-time acceptable (construction happens once), but should fail-fast for misconfiguration. The current approach silently creates defaults, which could mask configuration errors.

**Recommendation**: Replace null-coalescing `new` with explicit required dependencies. If a dependency is truly optional, document why and make it an explicit `null` default, not a silent `new`.

### 6.3 Static State
No static mutable state found in Identity assembly classes. This is correct for a boot-time composition root.

## 7. Security Correctness

### 7.1 Security-Sensitive Boundaries
All Identity components handle security-sensitive data. The following boundaries must be protected:

| Boundary | Protection | Test Proof |
|----------|------------|------------|
| Authentication | Credential validation, rate limiting, session management | YES (Auth tests) |
| MFA | TOTP, backup codes, challenge verification | YES (Credentials tests) |
| Passkey | WebAuthn ceremonies, challenge stores | YES (Credentials tests) |
| OAuth | Client registration, authorization codes, token exchange | YES (ExternalIdentity tests) |
| OIDC | Push authorization, JARM, metadata, userinfo | YES (ExternalIdentity tests) |
| Federation | Connection management, federated login | PARTIAL (characterization tests) |
| Tokens | JWT signing, token validation, blacklist | YES (Tokens tests) |
| Access | Policy evaluation, permission checks | YES (Access tests) |
| Tenancy | Tenant isolation, admin elevation | YES (Tenancy tests) |
| Security | Configuration changes, approval workflow | NO (DR-0005) |

### 7.2 Fail-Closed Behavior
- AuthBootstrapValidator validates required dependencies at boot — FAIL-CLOSED
- AuthBuilder throws ConfigurationException for missing dependencies — FAIL-CLOSED
- Sub-graph builders return null for unconfigured primitives — FAIL-OPEN (should fail-closed or document why optional)

### 7.3 Negative Tests Needed
- Token validation failure (invalid signature, expired token)
- Credential rejection (wrong password, locked account)
- Access denial (insufficient permissions, policy violation)
- MFA challenge failure (wrong code, expired challenge)
- Federation failure (connection down, metadata mismatch)

## 8. No Graph/Builder/Assembly Theater Assessment

### 8.1 Legitimate Assembly Classes
| Class | Lines | Real Responsibility? |
|-------|-------|---------------------|
| AssembleAuthIdentityGraph | 847 | YES — assembles entire identity graph from primitives |
| AssembleAuthExternalIdentityGraph | 216 | YES — assembles external identity graph |
| CredentialAuthenticationGraph | 149 | YES — assembles MFA/Passkey credential graph |
| OAuthIdentityGraph | 82 | YES — assembles OAuth/OIDC graph |
| FederationIdentityGraph | 58 | YES — assembles Federation graph |
| ScimProvisioningGraph | 71 | YES — assembles SCIM graph |
| TenancyAdministrationGraph | 92 | YES — assembles Tenancy graph |

### 8.2 Legitimate Builder
| Class | Lines | Real Responsibility? |
|-------|-------|---------------------|
| AuthBuilder | 560 | YES — fluent DSL for configuring Auth system |

### 8.3 Verdict
No assembly theater detected. All assembly classes have clear responsibilities:
- They assemble object graphs for boot-time registration
- They do not execute runtime behavior
- They do not contain business logic
- They are in the correct location (Configuration/Assembly/)

The sub-graph builder pattern is legitimate:
- Each sub-graph handles its own sub-domain assembly
- AuthBuilder delegates to sub-graphs for configuration
- AssembleAuthIdentityGraph receives assembled primitives from sub-graphs

## 9. Findings Summary

| ID | Severity | Category | Finding | Recommended Action |
|----|----------|----------|---------|-------------------|
| IT-001 | HIGH | Dogfooding | ALL Identity subcomponents fail how-to-dogfooding.md | Systematic remediation across all components |
| IT-002 | HIGH | PublicSurface | Tokens PublicSurface directly instantiates collaborators (DR-0382) | Move to Configuration/ServiceProvider |
| IT-003 | HIGH | Constructor | AssembleAuthIdentityGraph has 43 constructor params (DR-0601) | Introduce value objects/config groups |
| IT-004 | HIGH | Constructor | AssembleAuthExternalIdentityGraph has 27 constructor params (DR-0600) | Introduce value objects/config groups |
| IT-005 | MEDIUM | Assembly | AssembleAuthIdentityGraph is 847 lines (DR-0602) | Split by sub-domain if extraction clarifies ownership |
| IT-006 | MEDIUM | Assembly | Null-coalescing fallback `new` construction (DR-0363-0368) | Replace with explicit required dependencies |
| IT-007 | MEDIUM | Large Unit | DefaultAuth is 871 lines (DR-0612) | Classify responsibility; split where extraction clarifies ownership |
| IT-008 | MEDIUM | Large Unit | JwtIdentity is 390 lines (DR-0626) | Classify; split only if tests can protect behavior |
| IT-009 | MEDIUM | Large Unit | SessionIdentity is 311 lines (DR-0624) | Classify; split only if tests can protect behavior |
| IT-010 | MEDIUM | PublicSurface | UserRecord, User, Access instantiate collaborators | Move to Configuration/ServiceProvider |
| IT-011 | HIGH | Test Proof | Identity/Security has no component tests (DR-0005) | Add behavior-first tests |
| IT-012 | MEDIUM | Constructor | OAuth has 14 params (DR-0573), OidcProviderMetadata 20 params (DR-0574) | Introduce value objects |

## 10. Next-Slice Recommendation (Corrected)

Given that `AssembleAuthExternalIdentityGraph` already exists and AuthBuilder is now 560 lines, the "Phase 4 extraction" is NOT the next slice.

The highest-value, safest next slices are:

### Option A: Fix Tokens PublicSurface (IT-002) — SAFEST
- Move `new` instantiations from Tokens PublicSurface to Configuration/ServiceProvider
- Small scope, high security impact
- No public API change

### Option B: Replace Null-Coalescing Fallbacks (IT-006) — SAFE
- Replace `?? new X()` with explicit required dependencies in AssembleAuthIdentityGraph
- Small scope, improves fail-closed behavior
- No public API change

### Option C: Reduce AssembleAuthIdentityGraph Constructor (IT-003) — MODERATE
- Group 43 parameters into value objects (IdentityAssemblyConfig, SessionAssemblyConfig, etc.)
- Moderate scope, improves maintainability
- No public API change (constructor params are Configuration-boundary)

**Recommended**: Option B (replace null-coalescing fallbacks) — smallest safe slice that improves security correctness (fail-closed behavior).

---

## 11. Agent Output

Status: REVIEW_COMPLETE
Files changed: 0 (evidence only)
Evidence written: identity-topology-review.md
Remaining risks: See Section 9
Next allowed action: Execute smallest safe slice (Option B: replace null-coalescing fallbacks)
