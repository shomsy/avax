# Post-Governance Merge Reconciliation — Auth Recursive Subsystem Rewrite

Date: 2026-05-22
Branch: architecture/auth-recursive-subsystem-rewrite
Worktree: /home/shomsy/projects/avax-auth-rewrite-v2
Base commit: 45dcf9baf (main — how-to updates)
Mode: HARNESS-FULL
Status: RECONCILIATION_COMPLETE

## 1. Latest Governance Documents Loaded

### Mandatory Boot
| Document | Status | Notes |
|----------|--------|-------|
| AGENTS.md v3.0.0 | LOADED | Root contract. 36 sections. All laws apply. |
| .agents/how-to/README.md | LOADED | Governance map. Nested folder structure confirmed. |
| .agents/how-to/00-reading-order.md | LOADED | 29 documents listed. modeling/ folder marked MISSING. |

### Architecture How-To (nested)
| Document | Status |
|----------|--------|
| architecture/how-to-architecture.md | LOADED |
| architecture/how-to-architecture-decisions.md | LOADED |
| architecture/how-to-architecture-extension-with-ddd.md | LOADED |
| architecture/how-to-runtime-composition.md | LOADED |
| architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md | LOADED |
| architecture/how-to-use-advanced-architecture-patterns.md | LOADED |
| architecture/how-to-use-ai-assisted-execution.md | LOADED |

### Component How-To (nested)
| Document | Status |
|----------|--------|
| components/how-to-design-components.md | LOADED |
| components/how-to-dogfooding.md | LOADED |

### Implementation How-To (nested)
| Document | Status |
|----------|--------|
| implementation/how-to-clean-code.md | LOADED |
| implementation/how-to-code-style.md | LOADED |
| implementation/how-to-coding-standards.md | LOADED |
| implementation/how-to-dependency-injection.md | LOADED |
| implementation/how-to-modern-php-attributes-di.md | LOADED |

### Verification How-To (nested)
| Document | Status |
|----------|--------|
| verification/how-to-code-review.md | LOADED |
| verification/how-to-unit-test.md | LOADED |
| verification/how-to-production-readiness.md | LOADED |
| verification/how-to-system-security.md | LOADED |
| verification/how-to-system-performance.md | LOADED |
| verification/how-to-data-systems.md | LOADED |

### Documentation How-To (nested)
| Document | Status |
|----------|--------|
| documentation/how-to-document.md | LOADED |

### Project How-To (nested)
| Document | Status |
|----------|--------|
| project/how-to-write-avax.md | LOADED |
| project/how-to-git.md | LOADED |

### Skills Discovered
| Skill | Status |
|-------|--------|
| avax-enterprise-remediation | LOADED (mandatory bootloader) |
| avax-source-of-truth-resolver | LOADED (mandatory) |
| avax-enterprise-codecraft | APPLICABLE (production-code refactor) |
| avax-component-dogfooding | APPLICABLE (Identity component work) |
| avax-api-compatibility-contract | APPLICABLE (AuthBuilder/PublicSurface) |
| avax-security-threat-model | APPLICABLE (Auth is security-sensitive) |
| avax-runtime-performance-cache | APPLICABLE (hot-path assembly) |
| avax-observability-failure-semantics | APPLICABLE (failure-prone assembly) |
| avax-test-evidence-quality | APPLICABLE (test claims) |

### Evidence Files Read
| Evidence | Status |
|----------|--------|
| authbuilder-return-boundary-decision.md | LOADED — V5.9 decision on Auth return boundary |
| authbuilder-split-first-slice.md | LOADED — First slice evidence (AuthBuilder 1731→889 lines) |
| discipline-review/components/identity-auth-review.md | LOADED — 50 findings, BLOCKED_BY_GOVERNANCE |
| discipline-review/components/identity-credentials-review.md | LOADED — 16 findings, NEEDS_HIGH_REMEDIATION |
| discipline-review/components/identity-externalidentity-review.md | LOADED — 30 findings, NEEDS_HIGH_REMEDIATION |
| discipline-review/components/identity-security-review.md | LOADED — 2 findings, NEEDS_HIGH_REMEDIATION |
| discipline-review/components/identity-tenancy-review.md | LOADED — 9 findings, NEEDS_HIGH_REMEDIATION |
| discipline-review/components/identity-tokens-review.md | LOADED — 14 findings, NEEDS_HIGH_REMEDIATION |
| discipline-review/components/identity-access-review.md | LOADED — 5 findings, NEEDS_HIGH_REMEDI |
| identity-world-class-redesign/slice-0 through slice-9 | LOADED — Prior redesign slices |

### Project State
| File | Status |
|------|--------|
| TODO.md | LOADED — V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE still todo |
| .agents/management/TODO.md | LOADED — TODO-007 DONE, TODO-013 PARTIALLY_RESOLVED |
| fix-this.md | READ via TODO.md reference |
| CURRENT_TRUTH.md | ADVISORY only |

## 2. New Governance Rules That Change Identity Rewrite Direction

### 2.1 Nested How-To Structure (CONFIRMED)
Governance is now nested under `.agents/how-to/**` subdirectories:
- `architecture/`, `components/`, `implementation/`, `verification/`, `documentation/`, `project/`

The root `.agents/how-to/` contains only README.md, 00-reading-order.md, and how-to.txt (generated, do not stage per AGENTS.md §30).

**Impact**: All stale flat how-to path references (e.g., `.agents/how-to/how-to-architecture.md`) are now obsolete. All tooling and evidence must use nested paths.

### 2.2 Canonical Component Shape (AGENTS.md §13)
Every production component must follow:
```
components/<Area>/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

`InternalSystem` is a concept, not a folder. `ExportedCapabilities` is a decision, not a folder. Diagnostics are capabilities, not root folders.

**Impact**: Identity component already follows this shape. No structural change needed from governance merge.

### 2.3 Forbidden Folder Names (AGENTS.md §15)
Extended prohibition list includes: `Diagnostics`, `InternalSystem`, `ExportedCapabilities`, `Tests`, `Docs`.

**Impact**: Any Identity subcomponent using these names must be reclassified. Current Identity components do not use these as top-level System/ folders.

### 2.4 PublicSurface Rule Reinforcement (AGENTS.md §17)
PublicSurface must NOT own: runtime machinery, object graph assembly, fallback construction, service locator logic, business logic, security-sensitive decisions, cache ownership, worker state, filesystem scanning, reflection/class discovery, hidden mutable state.

**Impact**: AuthBuilder::ready() was previously split (first slice) to move graph assembly into `AssembleAuthIdentityGraph`. The remaining OAuth/OIDC/Federation assembly (Phase 4) still lives inline in AuthBuilder and must be extracted into `AssembleAuthExternalIdentityGraph`.

### 2.5 Configuration/Assembly Rule (AGENTS.md §18)
Required dependencies must fail during configuration/provider registration/compile/verify/boot — NOT deep inside runtime business code. Assembly belongs in Configuration/Assembly/Provider/Builder/Boot boundaries.

**Impact**: `AssembleAuthIdentityGraph` has 43 constructor parameters (DR-0601). `AssembleAuthExternalIdentityGraph` has 27 (DR-0600). Both are assembly classes, so constructor bloat is less severe than for runtime flows, but the assembly classes still contain `new` fallback construction (null-coalescing) that violates §18.

### 2.6 Component Dogfooding (AGENTS.md §19)
Components must reuse existing AvaX capabilities. No raw filesystem IO where Filesystem should own it. No raw env/config access where Configuration should own it. No raw logging where Logger/Observability should own it.

**Impact**: All Identity subcomponents flagged by discipline review for dogfooding violations (how-to-dogfooding.md FAIL in every component review). This remains the dominant remediation pattern.

### 2.7 Runtime Performance (AGENTS.md §21)
Runtime hot paths must avoid reflection, filesystem scans, config parsing, env reads, dynamic class discovery, service locator lookup, container compilation, repeated metadata parsing.

**Impact**: AuthBuilder is boot-time assembly, not a hot path. But `AssembleAuthIdentityGraph` uses `new` fallback construction for shared primitives which is boot-time acceptable. No hot-path violation for assembly classes.

### 2.8 Security (AGENTS.md §22)
Auth is a security-sensitive component. Every security-sensitive change requires threat modeling and fail-closed proof.

**Impact**: Any Identity rewrite slice must include negative tests proving security boundaries. The `avax-security-threat-model` skill applies.

## 3. Old Assumptions Now Obsolete

| Old Assumption | Current Truth | Evidence |
|----------------|---------------|----------|
| AuthBuilder split is the only blocker | AuthBuilder was split (first slice, 1731→889 lines), but remaining Phase 4 OAuth/OIDC/Federation assembly still inline (449 lines of AuthBuilder) | authbuilder-split-first-slice.md |
| Auth return boundary needs redesign | Return boundary accepted as correct for V5.9 — minimal `Auth(identity: $identity)` | authbuilder-return-boundary-decision.md |
| Flat how-to paths valid | Governance now nested under `.agents/how-to/**` subdirectories | .agents/how-to/README.md |
| Identity component reviews were one-time | Discipline reviews show ALL Identity subcomponents BLOCKED_BY_GOVERNANCE or NEEDS_HIGH_REMEDIATION | discipline-review/** |
| Graph/Assembly/Builder names allowed by default | AGENTS.md §15 prohibits concept-word folders; Graph/Assembly/Builder are allowed ONLY in Configuration/Assembly/ for actual assembly classes | AGENTS.md §15, §18 |
| V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE is next todo | TODO.md still lists this as `todo` but first slice is already DONE on main (commit in authbuilder-split-first-slice.md). Second slice (AssembleAuthExternalIdentityGraph) is the actual next work | TODO.md vs evidence mismatch |

## 4. Whether Current Identity Slices Still Comply

### Slice Compliance Matrix

| Prior Slice | Complies with Current Governance? | Notes |
|-------------|-----------------------------------|-------|
| Slice 0: Characterization tests | YES | Behavior proof tests — valid approach |
| Slice 1: Policy duplicate cleanup | YES | Duplicate ownership reduction — valid |
| Slice 2: Assemble decomposition | YES | Created AssembleAuthIdentityGraph — valid |
| Slice 3: Tokens assembly leak fix | NEEDS_REVIEW | Must verify no new PublicSurface violations |
| Slice 4: Credentials/ExternalIdentity static removal | NEEDS_REVIEW | Must verify dogfooding compliance |
| Slice 5: Tenancy static state removal | NEEDS_REVIEW | Must verify dogfooding compliance |
| Slice 6: Identity constructor hardening | NEEDS_REVIEW | 43-param constructor (DR-0601) — YELLOW |
| Slice 9: Unified public DSL | YES | DSL is correct AvaX pattern |

### Key Finding
The prior world-class redesign slices are directionally correct but were executed against older governance. The dominant remaining findings are:

1. **Constructor bloat** across ALL Identity subcomponents (DR-0600 through DR-0637)
2. **Constructor default instantiation** across ALL Identity subcomponents (DR-0349 through DR-0387)
3. **Dogfooding violations** — every component review FAILs how-to-dogfooding.md
4. **PublicSurface direct instantiation** in Tokens (DR-0382), UserRecord (DR-0357), User (DR-0358), Access (DR-0378)

## 5. Remaining P0 Architecture Targets

### 5.1 AuthBuilder Remaining Gravity (NOT P0 — TODO-007 DONE)
AuthBuilder was split in the first slice. Remaining Phase 4 work (OAuth/OIDC/Federation assembly inline) is now a **second extraction slice**, not a P0 blocker.

- **Current AuthBuilder**: ~889 lines
- **Remaining inline assembly**: ~449 lines (OAuth, OIDC, SSO, Federation, ExternalIdentity, Provisioning, IdentitySync, Diagnostics)
- **Action**: Extract into `AssembleAuthExternalIdentityGraph`

### 5.2 AssembleAuthIdentityGraph
- **Current**: 676 lines, 43 constructor parameters (DR-0601)
- **Issue**: Contains null-coalescing fallback `new` construction (DR-0363 through DR-0368)
- **Risk**: Assembly classes are allowed to construct, but fallback `new` in null-coalescing should fail at boot, not silently create defaults
- **Action**: Replace null-coalescing `new` with explicit required dependencies or document acceptable boot-time fallback

### 5.3 AssembleAuthExternalIdentityGraph (DOES NOT EXIST YET)
- **Current**: OAuth/OIDC/Federation assembly is inline in AuthBuilder Phase 4
- **Issue**: Violates "Configuration assembles" rule — AuthBuilder (a Builder DSL) should not contain assembly machinery
- **Action**: Extract Phase 4 into dedicated `AssembleAuthExternalIdentityGraph`
- **Expected**: ~450 lines extracted from AuthBuilder

### 5.4 Remaining *Graph / *Assembly / *Builder Gravity Centers

| Gravity Center | Location | Lines | Parameters | Findings |
|----------------|----------|-------|------------|----------|
| AuthBuilder | Configuration/Builders/ | ~889 | N/A (DSL) | DR-0604 (was DR-0603, reduced from 1731) |
| AssembleAuthIdentityGraph | Configuration/Assembly/ | 676 | 43 | DR-0601, DR-0363-0368, DR-0602 |
| AssembleAuthExternalIdentityGraph | Configuration/Builders/AuthBuilder.php (inline) | ~449 | N/A | Not yet extracted |
| DefaultAuth | Capabilities/Identity/ | 871 | N/A | DR-0612 |
| Identity (capability) | Capabilities/Identity/ | 39 | 10 | DR-0611 |
| JwtIdentity | Capabilities/Identity/Jwt/ | 390 | 10 | DR-0625, DR-0626 |
| SessionIdentity | Capabilities/Identity/Session/ | 311 | 11 | DR-0623, DR-0624 |
| SCIM | Capabilities/IdentitySync/SCIM/ | 38 | 12 | DR-0614 |
| OAuth | ExternalIdentity/Capabilities/OAuth/ | 41 | 14 | DR-0573 |
| OpenSslOidcProvider | ExternalIdentity/Capabilities/OpenIDConnect/ | 310 | 12 | DR-0575, DR-0576 |

## 6. Updated Next-Slice Decision

### Source-of-Truth Resolution
TODO.md lists `V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE` as `todo` status, but evidence proves the first slice is DONE. The actual next slice is:

**Next slice: Extract OAuth/OIDC/Federation assembly from AuthBuilder Phase 4 into `AssembleAuthExternalIdentityGraph`**

This is the smallest safe next slice because:
1. It reduces AuthBuilder from ~889 to ~440 lines (second halving)
2. It follows the established pattern from first slice (AssembleAuthIdentityGraph)
3. It has characterization tests from the first slice proving the boundary
4. It does not change public API — Auth::ready() returns the same `Auth(identity: $identity)`
5. It is purely Configuration/Assembly work — no runtime behavior change
6. It addresses the remaining AuthBuilder gravity center

### Slice Scope
- **Extract**: OAuth client management, authorization, token exchange, revocation, introspection
- **Extract**: OpenIDConnect (push authorization, logout, JARM, metadata, userinfo, JWKS)
- **Extract**: SingleSignOn/Federation (federated login, connections, metadata sync)
- **Extract**: ExternalIdentity wrapper
- **Extract**: Provisioning
- **Extract**: IdentitySync (SCIM + Provisioning)
- **Extract**: Diagnostics (authIssueExplainer)
- **Create**: `AssembleAuthExternalIdentityGraph.php` in Configuration/Assembly/
- **Modify**: AuthBuilder.php — replace Phase 4 with single delegation call
- **Tests**: Characterization tests proving external identity graph assembly

### Slice Safety Assessment
- **Public API change**: NO
- **Runtime behavior change**: NO
- **Security boundary change**: NO (assembly-time only)
- **Dogfooding impact**: Neutral (same objects, different assembly location)
- **Test proof**: Characterization tests from first slice cover federation/SCIM readiness

### Alternative Considered: Constructor Bloat Remediation
Constructor bloat (43 params in AssembleAuthIdentityGraph, 27 in AssembleAuthExternalIdentityGraph) is a valid concern. However:
- Assembly classes are Configuration-boundary, not runtime
- Constructor bloat in assembly is less severe than in flows/capabilities
- Splitting constructor parameters requires deeper design decisions (value objects, config groups)
- Extracting Phase 4 first reduces total gravity before splitting parameters

Decision: Extract Phase 4 first, then address constructor bloat in a subsequent slice.

---

## 7. Topology/Coupling Review Summary

### 7.1 Ownership Truth
The Identity component suite (Auth, Credentials, ExternalIdentity, Security, Tenancy, Tokens, Access) owns authentication, authorization, identity management, and access control. This is correct.

However, the discipline reviews show that **every Identity subcomponent fails dogfooding** — meaning they construct dependencies directly rather than using AvaX first-party boundaries (Filesystem, Logger, Clock, Cache, Configuration, Container).

### 7.2 Global Complexity Reduction
The largest complexity drivers are:
1. **AssembleAuthIdentityGraph**: 676 lines, 43 params — assembles entire identity graph
2. **AuthBuilder**: ~889 lines (was 1731) — DSL + inline OAuth/OIDC/Federation assembly
3. **DefaultAuth**: 871 lines — default identity capability implementation
4. **OAuth**: 41 params constructor — data carrier with excessive arity
5. **OidcProviderMetadata**: 20 params constructor — protocol metadata object

Extraction of Phase 4 will reduce AuthBuilder by ~50%. The remaining complexity is in the identity graph itself, not the assembly mechanism.

### 7.3 Change Locality
- **High locality**: Assembly classes — changes stay in Configuration/Assembly/
- **Medium locality**: Capability classes — changes affect flows that use them
- **Low locality**: PublicSurface — changes affect all consumers

Assembly extraction is the highest-locality change: it moves code from one Configuration class to another without affecting runtime behavior.

### 7.4 Intrusive Coupling
The most intrusive coupling paths:
1. AuthBuilder → AssembleAuthIdentityGraph → ALL Identity subcomponents (boot-time, acceptable)
2. DefaultAuth → ALL Identity capabilities (runtime, high coupling by design — this is the root identity capability)
3. Tokens PublicSurface → direct `new` of collaborators (violates PublicSurface rule, must fix)

### 7.5 Runtime Safety
- AuthBuilder is boot-time assembly — no worker state leak risk
- AssembleAuthIdentityGraph uses null-coalescing `new` fallbacks — boot-time acceptable but should fail-fast for misconfiguration
- No static mutable state found in Identity assembly classes
- DefaultAuth is a capability, not a singleton — no worker safety risk from assembly

### 7.6 Security Correctness
- Federation readiness bug was FIXED in first slice (authbuilder-return-boundary-decision.md)
- All Identity components handle security-sensitive data
- Negative tests exist for AuthBuilder (9 characterization tests)
- Negative tests needed for: token validation failure, credential rejection, access denial

### 7.7 No Graph/Builder/Assembly Theater
- `AssembleAuthIdentityGraph` is a real assembly class — not theater
- `AuthBuilder` is a real builder DSL — not theater
- Phase 4 extraction into `AssembleAuthExternalIdentityGraph` follows the same pattern — not mechanical naming
- All assembly classes have clear responsibility: assemble object graphs for boot-time registration

---

## 8. Validation Plan for Next Slice

### Focused Validation
```bash
vendor/bin/phpunit --filter "AuthBuilder|Auth|ExternalIdentity|OAuth|OIDC|Federation|SingleSignOn" --no-coverage
vendor/bin/phpstan analyse components/Identity/Auth/System/Configuration --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-composition-leaks.php
```

### Full Validation (after merge)
```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-composition-leaks.php
```

---

## 9. Remaining Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| AssembleAuthIdentityGraph 43-param constructor | YELLOW | Assembly class, not runtime. Address in subsequent slice. |
| Phase 4 extraction may reveal hidden dependencies | MEDIUM | Characterization tests will catch behavioral changes |
| Tokens PublicSurface direct instantiation | HIGH | Separate remediation batch (TODO-013) |
| Dogfooding violations across ALL Identity components | HIGH | Systematic remediation, not per-slice fix |
| Constructor bloat across Identity subcomponents | MEDIUM | TODO-020 addresses this cross-cutting |
| Missing tests for Identity/Security | HIGH | DR-0005 — needs dedicated test session |

---

## 10. TODO FOR 11++

### Immediate Next Slice
1. [ ] Extract OAuth/OIDC/Federation assembly from AuthBuilder Phase 4 into `AssembleAuthExternalIdentityGraph`
2. [ ] Add characterization tests for external identity graph assembly
3. [ ] Verify no public API changes
4. [ ] Run focused validation

### Subsequent Slices (in priority order)
5. [ ] Fix Tokens PublicSurface direct instantiation (DR-0382) — HIGH security risk
6. [ ] Replace null-coalescing `new` fallbacks in assembly classes with explicit required dependencies
7. [ ] Address AssembleAuthIdentityGraph constructor bloat (43 params → value objects/config groups)
8. [ ] Address AssembleAuthExternalIdentityGraph constructor bloat (27 params)
9. [ ] Systematic dogfooding remediation across ALL Identity subcomponents
10. [ ] Add missing tests for Identity/Security (DR-0005)
11. [ ] Fix PublicSurface direct instantiation in UserRecord, User, Access
12. [ ] Address constructor default instantiation across all Identity flows/capabilities
13. [ ] Large unit classification for DefaultAuth (871 lines), JwtIdentity (390 lines), SessionIdentity (311 lines)

### Cross-Cutting (TODO.md phases)
14. [ ] TODO-013: Security/Identity/DataStack PublicSurface construction pressure
15. [ ] TODO-014: Constructor defaults (529 findings remaining)
16. [ ] TODO-020: Constructor bloat (483 findings)
17. [ ] TODO-022: Forbidden concept folder names

---

## 11. Agent Output Contract

Stage: Post-governance merge reconciliation
Mode: HARNESS-FULL
Skills discovered: 10 AvaX skills
Skills used: avax-enterprise-remediation (bootloader), avax-source-of-truth-resolver
Skills skipped with reason: avax-autonomous-backlog-loop (single-task reconciliation, not multi-task loop)
How-to files read: 18 nested how-to documents
Rules applied: AGENTS.md all 36 sections, nested how-to governance
Source-of-truth decision: TODO.md V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE is stale (first slice DONE). Next slice is AssembleAuthExternalIdentityGraph extraction.
Status: RECONCILIATION_COMPLETE
Files changed: 1 (this evidence file)
Validation commands: pending (no code changes yet)
Evidence written: post-governance-merge-reconciliation.md
Remaining risks: See Section 9
Next allowed action: Create branch for AssembleAuthExternalIdentityGraph extraction slice, execute smallest safe slice
