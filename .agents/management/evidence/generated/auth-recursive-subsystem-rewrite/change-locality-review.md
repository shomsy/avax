# Change Locality Review — Identity Component Suite

Date: 2026-05-22
Branch: architecture/auth-recursive-subsystem-rewrite
Scope: `components/Identity/**`
Status: REVIEW_COMPLETE

## 1. Change Locality Matrix

This review maps each Identity finding to its change locality — how many files, components, and test files are affected by a remediation.

### 1.1 Assembly-Class Changes (Highest Locality)

| Finding | Files Affected | Components Affected | Tests Affected | Risk |
|---------|----------------|---------------------|----------------|------|
| IT-006: Null-coalescing fallbacks in AssembleAuthIdentityGraph | 1 (AssembleAuthIdentityGraph.php) | 1 (Auth) | 9 (characterization tests) | LOW — boot-time only |
| IT-003: 43-param constructor | 1 (AssembleAuthIdentityGraph.php) | 1 (Auth) | 9 (characterization tests) | LOW — value objects only |
| IT-004: 27-param constructor | 1 (AssembleAuthExternalIdentityGraph.php) | 1 (Auth) | 0 (no dedicated tests) | LOW-MEDIUM — need tests first |
| IT-005: 847-line assembly class | 1-3 (split into sub-assemblies) | 1 (Auth) | 9 (characterization tests) | MEDIUM — requires careful ownership split |

### 1.2 PublicSurface Changes (Medium Locality)

| Finding | Files Affected | Components Affected | Tests Affected | Risk |
|---------|----------------|---------------------|----------------|------|
| IT-002: Tokens PublicSurface `new` | 1 (Tokens.php) | 1 (Tokens) | Tokens tests | MEDIUM — must not break token creation |
| IT-010: UserRecord/User/Access `new` | 3 (UserRecord.php, User.php, Access.php) | 2 (Auth, Access) | Auth + Access tests | MEDIUM — must not break identity creation |

### 1.3 Constructor Bloat Changes (Cross-Cutting, Low Locality)

| Finding | Files Affected | Components Affected | Tests Affected | Risk |
|---------|----------------|---------------------|----------------|------|
| DR-0573: OAuth 14 params | 1 (OAuth.php) | 1 (ExternalIdentity) | ExternalIdentity tests | MEDIUM — data carrier change |
| DR-0574: OidcProviderMetadata 20 params | 1 (OidcProviderMetadata.php) | 1 (ExternalIdentity) | ExternalIdentity tests | LOW — pure data object |
| DR-0612: DefaultAuth 871 lines | 1-5 (split DefaultAuth) | 1 (Auth) | Auth tests | HIGH — public capability split |
| DR-0625: JwtIdentity 390 lines | 1 (JwtIdentity.php) | 1 (Auth) | Auth tests | MEDIUM — capability split |
| DR-0623: SessionIdentity 311 lines | 1 (SessionIdentity.php) | 1 (Auth) | Auth tests | MEDIUM — capability split |

### 1.4 Dogfooding Changes (Cross-Cutting, Lowest Locality)

| Finding | Files Affected | Components Affected | Tests Affected | Risk |
|---------|----------------|---------------------|----------------|------|
| IT-001: ALL Identity dogfooding | 30+ (across all Identity) | 7 (all Identity) | All Identity tests | HIGH — systematic remediation |

## 2. Intrusive Coupling Analysis

### 2.1 Coupling Chains

```
Chain 1 (Boot-time, acceptable):
  AuthServiceProvider → AuthBuilder → Sub-graph builders → AssembleAuthIdentityGraph → ALL Identity flows/capabilities
  Scope: Boot-time only
  Blast radius: Application startup
  Risk: LOW — if assembly fails, app fails fast

Chain 2 (Runtime, by design):
  Auth PublicSurface → DefaultAuth → Identity → Sessions/MFA/OAuth/Tenancy/Passkey
  Scope: Per-request authentication
  Blast radius: Every authenticated request
  Risk: MEDIUM — changes affect all auth flows

Chain 3 (Runtime, problematic):
  Tokens PublicSurface → new JwtSigner, new TokenCodec, new TokenBlacklist
  Scope: Per-request token operations
  Blast radius: Token creation/validation
  Risk: HIGH — violates PublicSurface rule, hard to test, hard to swap

Chain 4 (Runtime, problematic):
  UserRecord/User → new collaborators
  Scope: Per-request identity resolution
  Blast radius: Identity display
  Risk: MEDIUM — smaller impact than Tokens
```

### 2.2 Coupling Direction

```
Correct direction (dependency inversion):
  PublicSurface → Flow → Capability → Foundation
  Configuration → Assembly → Flow/Capability

Incorrect direction (found in Identity):
  PublicSurface → new Capability (bypasses Configuration)
  Flow → new Foundation (bypasses Configuration)
  Assembly → new Flow (should receive, not construct)
```

### 2.3 Coupling Hotspots

| Hotspot | Incoming Coupling | Outgoing Coupling | Type |
|---------|-------------------|-------------------|------|
| AssembleAuthIdentityGraph | 1 (AuthBuilder) | 30+ (Identity flows/capabilities) | Boot-time convergence — ACCEPTABLE |
| DefaultAuth | 2 (AuthBuilder, tests) | 10+ (Identity capabilities) | Runtime convergence — BY DESIGN |
| Identity (capability) | 3 (AuthBuilder, DefaultAuth, tests) | 7 (Identity owners) | Runtime convergence — BY DESIGN |
| Tokens PublicSurface | External consumers | 8 (new instantiations) | Runtime violation — MUST FIX |

## 3. Global vs Local Complexity

### 3.1 Changes That Reduce Global Complexity

| Change | Global Impact | Reason |
|--------|---------------|--------|
| Fix Tokens PublicSurface (IT-002) | HIGH — eliminates runtime coupling violation across token operations | All token creation/validation goes through proper Configuration |
| Replace null-coalescing fallbacks (IT-006) | MEDIUM — improves fail-closed behavior across entire Identity assembly | Boot-time validation is clearer, errors are caught earlier |
| Systematic dogfooding (IT-001) | HIGH — eliminates raw PHP primitives across all Identity components | All Identity components use AvaX first-party boundaries |

### 3.2 Changes That Only Reduce Local Complexity

| Change | Local Impact | Global Impact | Reason |
|--------|--------------|---------------|--------|
| Split DefaultAuth (DR-0612) | Redlines DefaultAuth from 871 to smaller units | NONE — still the same capability surface | Mechanical split, no behavioral improvement |
| Split JwtIdentity (DR-0626) | Redlines JwtIdentity from 390 to smaller units | NONE — still the same JWT backend | Mechanical split |
| Reduce OAuth params (DR-0573) | Cleaner constructor signature | MINOR — easier to read, same runtime | Cosmetic improvement |
| Split AssembleAuthIdentityGraph (IT-005) | Smaller assembly files | MINOR — still converges at boot | Mechanical split unless sub-domains become independently configurable |

### 3.3 Verdict

**Do NOT prioritize mechanical splits.** They reduce local line counts without reducing global complexity.

**DO prioritize**:
1. PublicSurface rule violations (IT-002, IT-010) — these are architecture violations that make testing harder and coupling tighter
2. Fail-closed behavior (IT-006) — this is a security correctness improvement
3. Dogfooding (IT-001) — this is a systematic quality improvement across all components

## 4. Safe Slice Ordering

### 4.1 Safest Slices First (small scope, no API change, high impact)

| Order | Slice | Scope | Files | API Change? | Test Proof Needed |
|-------|-------|-------|-------|-------------|-------------------|
| 1 | Replace null-coalescing fallbacks | Assembly | 1 | NO | Update characterization tests |
| 2 | Fix Tokens PublicSurface `new` | PublicSurface | 1 | NO | Update Tokens tests |
| 3 | Fix User/UserRecord/Access `new` | PublicSurface | 3 | NO | Update Auth/Access tests |

### 4.2 Moderate Slices (require design decisions)

| Order | Slice | Scope | Files | API Change? | Test Proof Needed |
|-------|-------|-------|-------|-------------|-------------------|
| 4 | Reduce OAuth constructor params | Capability | 1-2 | NO (value object) | Update ExternalIdentity tests |
| 5 | Reduce OidcProviderMetadata params | Data object | 1 | NO (value object) | Update ExternalIdentity tests |
| 6 | Add tests for Identity/Security | Test proof | 2-3 | NO | New tests |

### 4.3 Risky Slices (require careful design)

| Order | Slice | Scope | Files | API Change? | Test Proof Needed |
|-------|-------|-------|-------|-------------|-------------------|
| 7 | Split DefaultAuth | Capability | 1-5 | YES (internal API) | Full Auth test suite |
| 8 | Split JwtIdentity | Capability | 1-3 | YES (internal API) | Auth test suite |
| 9 | Systematic dogfooding | All Identity | 30+ | POTENTIALLY | All Identity tests |

## 5. Recommended Execution Order

1. **Slice 1: Replace null-coalescing fallbacks** (IT-006)
   - 1 file, assembly class, boot-time only
   - Improves fail-closed behavior
   - Characterization tests already exist to prove behavior
   - No public API change

2. **Slice 2: Fix Tokens PublicSurface** (IT-002)
   - 1 file, HIGH severity
   - Eliminates runtime coupling violation
   - Requires ServiceProvider or Configuration owner to assemble

3. **Slice 3: Fix User/UserRecord/Access PublicSurface** (IT-010)
   - 3 files, MEDIUM severity
   - Same pattern as Slice 2

4. **Slice 4: Add tests for Identity/Security** (IT-011)
   - 2-3 files, HIGH severity (no test proof)
   - Must have tests before any remediation in Security component

5. **Slice 5: Reduce constructor params for data carriers** (IT-003, IT-004, DR-0573, DR-0574)
   - Multiple files, MEDIUM severity
   - Value objects improve maintainability without changing behavior

6. **Slice 6+: Systematic dogfooding** (IT-001)
   - 30+ files, HIGH severity
   - Requires per-component approach, not a single slice

---

## 6. Agent Output

Status: REVIEW_COMPLETE
Files changed: 0 (evidence only)
Evidence written: change-locality-review.md
Next allowed action: Execute Slice 1 — replace null-coalescing fallbacks in AssembleAuthIdentityGraph
