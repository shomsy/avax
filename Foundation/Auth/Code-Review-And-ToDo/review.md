# ARCHITECTURE NOTES

## Verification Status (2026-04-21)

Canonical closure status now lives in `.agents/management/review-closure.md`.
This review file remains the high-priority artifact and architectural hypothesis set, but it is no longer the canonical
closure register.

Final production-ready re-check completed on 2026-04-22 00:00 CEST.
The closure register is now closed: every tracked item is either `verified fixed` or `intentionally retained` with
code/test/doc evidence.

- `AuthServiceProvider` and the container adapter were verified against the current kernel contracts and repaired.
- The bootstrap fatal in `ProjectAuthenticatedUser` was fixed and `AuthBuilder::ready()` now fails fast when identity
  backends are missing.
- Optional capability owners now expose explicit readiness semantics and typed capability-unavailable failures instead
  of generic late `RuntimeException` errors.
- README quick-start imports were corrected to the current namespaces and new mirrored `docs/.../how-this-works.md`
  pages were added for the main ownership zones.
- Release tooling was repaired so `conformance` and `quality-gates` no longer deadlock on high-output subprocesses,
  Rector is configured non-interactively for shipped sources, and the evidence bundle is now complete.
- The review finding about `Identity` local-owner composition was only partially confirmed: the façade still keeps
  direct session/JWT backend handles intentionally because issuance and clearing are cross-cutting identity coordination
  behavior, not local sub-owner behavior.
- Validation evidence after these repairs is now expected from `phpunit`, `phpstan`, `phpstan.strict`, `conformance`,
  `quality-gates`, `system-shape`, `source-truth`, and the completed evidence bundle, not from the earlier failing
  baseline captured below.

## Phase 0: Context and Scope Gate

### 0.1 System Identity

- **System Type:** shared library / auth kernel
- **Primary Consumers:** internal teams, app layer, integration adapters
- **Runtime Context:** mixed (`HTTP request`, CLI/tooling, tests, optional container integration)
- **Lifecycle:** stable core with active expansion

### 0.2 Intended Use-Cases and Anti-Use-Cases

- **Intended Use-Cases:**
    - authenticate users through one canonical auth entry
    - resolve session and bearer-token identity into one immutable auth context
    - expose package-owned flows for MFA, passkeys, OAuth, OIDC, SCIM, tenancy, and diagnostics
    - keep transport and container concerns in `integrations/`, not in the kernel
- **Anti-Use-Cases:**
    - act as a framework-shaped service locator
    - leak transport-specific logic into `System/`
    - require consumers to guess which capability is actually configured at runtime

### 0.3 Non-Goals

- full UI/auth product implementation
- framework-specific container ownership inside the kernel
- documentation style review

### 0.4 Compatibility Contract

- **Public API Stability Requirement:** strict for `Auth`, `AuthInterface`, and consumer-facing capability entry points
- **Backwards Compatibility:** required for public kernel surface; optional for thin integrations if documented
- **Performance Budget:** request-resolution path should stay simple and predictable; build-time object churn is
  acceptable, request-time indirection is not

## Phase 1: System and Architecture Review

### 1.1 Actual Execution Flow (As-Built)

```mermaid
flowchart LR
  A[Consumer / Adapter] --> B[Auth::configuration or container adapter]
  B --> C[AuthBuilder::ready]
  C --> D[Capability coordinators]
  D --> E[Flow / Runtime owners]
  E --> F[Stores / identities / audit]
  F --> G[AuthenticationContext / results / side effects]
```

**This is how the system actually works.**

`Foundation/Auth` is organized around one kernel entry, but the real assembly center is `AuthBuilder::ready()`.
Consumers enter through `Auth`, `AuthInterface`, or an integration adapter such as `AuthServiceProvider`.
`ready()` wires coordinators such as `Access`, `Identity`, `ExternalIdentity`, `IdentitySync`, `Tenancy`, and
`Diagnostics`.
Those coordinators delegate to flow/runtime owners that execute the real behavior.
Stores, clocks, registries, and runtime interfaces hold state or external seams.
`AuthenticationContext` and flow result objects are the main stable outputs.
The architecture is coherent at the domain level, but the composition layer is overloaded.

### 2. Central Abstraction Identification

> This system is fundamentally organized around **capability coordinators that delegate to flow/runtime owners**.

> Secondary axis: **configuration-driven assembly** (adds complexity and is only partially justified).

### 3. Central Abstraction Stress Test

- Does every feature flow through it? **Yes**
- Does it accumulate responsibilities over time? **Yes**
- Is it harder to change than surrounding components? **Yes**

**Assessment:** ⚠️ Weak

The capability/flow axis is sound and readable in the tree (`README.md`, `docs/architecture/overview.md`), but the
secondary axis in `AuthBuilder::ready()` absorbs too much assembly, fallback behavior, and readiness logic. The system
is still organized around the right domain story, yet evolution pressure is now concentrated in composition instead of
staying local to slices.

### 4. Responsibility and Boundary Mapping

| Component                                | Orchestrates | Executes  | Holds State | Notes                                                      |
|------------------------------------------|--------------|-----------|-------------|------------------------------------------------------------|
| `System/Auth.php`                        | Yes          | No        | No          | Thin public entry over capabilities                        |
| `System/Configuration/AuthBuilder.php`   | Yes          | Partially | Yes         | Composition root, defaults, feature readiness, mode gating |
| `System/Capabilities/*` coordinators     | Yes          | No        | No          | Domain owners that expose sub-capabilities                 |
| `System/Flows/*` and `Runtime/*` owners  | No           | Yes       | Sometimes   | Real use-case execution lives here                         |
| stores / registries / runtime interfaces | No           | Sometimes | Yes         | Persistence and external seams                             |
| `integrations/*`                         | Yes          | Sometimes | No          | Thin adapters are intended here                            |

**Responsibility boundaries are: stressed.**

The domain tree is mostly clear.
The composition root is the main place where orchestration, execution decisions, and readiness state collapse together.

### 5. Pipeline and Control Flow Analysis

Canonical bootstrap pipeline:

| Step                                       | Mandatory | Conditional | Mutates State | Terminal |
|--------------------------------------------|-----------|-------------|---------------|----------|
| `Auth::configuration()`                    | Yes       | No          | No            | No       |
| `AuthBuilder::*with...()` configuration    | No        | Yes         | Yes           | No       |
| `AuthBuilder::ready()` validation          | Yes       | No          | No            | No       |
| default dependency creation                | Yes       | Yes         | No            | No       |
| capability coordinator assembly            | Yes       | Yes         | No            | No       |
| consumer calls `Auth` or capability method | Yes       | No          | Depends       | No       |
| flow/runtime executes side effects         | Yes       | Yes         | Yes           | Yes      |

Is this pipeline a formal state machine? **No**

There are explicit build and runtime phases, but readiness is partly implicit and encoded through nullable dependencies
rather than explicit configuration states.

### 6. Mutability Audit

| Object                                                          | Scope           | Lifetime    | Why Mutable?                                   | Classification           |
|-----------------------------------------------------------------|-----------------|-------------|------------------------------------------------|--------------------------|
| `AuthBuilder`                                                   | per build       | short       | fluent assembly and option accumulation        | Necessary, but oversized |
| `CurrentAuthentication`                                         | request/session | medium      | holds current auth resolution                  | Necessary                |
| session / token / SCIM / tenant stores                          | runtime         | medium/long | persistence and revocation state               | Necessary                |
| nullable handler fields inside `OAuth`, `OpenIDConnect`, `SCIM` | object lifetime | long        | encode configuration readiness in object state | Design Smell             |

**Mutability is: justified in runtime state holders, misplaced in configuration-as-readiness wrappers.**

### 7. System Invariants

| Invariant                                                  | Enforced Where                                                       | Evidence                                                                                                         | Status       |
|------------------------------------------------------------|----------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|--------------|
| Public auth ingress is explicit                            | `Auth`, `Access`, `Identity` entry methods                           | [`README.md`](../README.md), [`System/Auth.php`](../System/Auth.php)                                             | Enforced     |
| Transport concerns stay outside the kernel                 | `integrations/` vs `System/` split                                   | [`README.md`](../README.md), [`docs/architecture/overview.md`](../docs/architecture/overview.md)                 | Enforced     |
| Authentication context is the canonical current-user model | `Access::authenticateRequest/current/check/user`                     | [`README.md`](../README.md), [`System/Capabilities/Access/Access.php`](../System/Capabilities/Access/Access.php) | Enforced     |
| Composition must produce a buildable kernel                | `AuthBuilder::ready()`, container adapter                            | broken by `ProjectAuthenticatedUser` fatal and stale provider assembly                                           | Not enforced |
| Invalid capability states should be unrepresentable        | optional capability coordinators                                     | nullable handlers + runtime exceptions in `OAuth`, `OpenIDConnect`, `SCIM`                                       | Partially    |
| Optional integrations must track current kernel contracts  | `integrations/avax-container/AuthServiceProvider.php`, docs/examples | stale `Identity` construction in adapter and README                                                              | Not enforced |
| Documentation must explain ownership without opening code  | `docs/` governance                                                   | no mirrored `docs/System/...`, zero `how-this-works.md` files                                                    | Not enforced |

## Phase 2: Foundational and Critical Design Review

### 8. Routine Enterprise Design Failures

#### 8.1 Framework-in-a-Framework Syndrome

**Present**

Evidence:

- `System/Configuration/AuthBuilder.php` is 1568 lines and assembles nearly every capability.
- readiness, defaults, and runtime substitution are encoded in one imperative method.
- integration adapters now depend on internal constructor details instead of a stable assembly seam.

#### 8.2 Abstractions Without Real Variance

| Abstraction                     | Classification | Evidence                                                                                   |
|---------------------------------|----------------|--------------------------------------------------------------------------------------------|
| `AuthInterface`                 | Acceptable     | stable public kernel contract                                                              |
| `AuditLegalHoldPolicyInterface` | Acceptable     | integration seam for exporters                                                             |
| `IdentityInterface`             | Design debt    | adapter binds it, but actual concrete construction drifted and broke `AuthServiceProvider` |

#### 8.3 Too Clever Design Test

**Risky**

The folder structure is optimized for reading.
The composition layer is optimized for flexible writing/configuration, which now requires internal knowledge to use
safely.

### 9. Configuration as Architectural Signal

**Classification:** Risky

Yes, configuration has become a proxy for architecture.
Behavioral readiness is encoded through flags or presence checks such as `enterpriseMode`, `withPasskeyRuntime()`,
`withOidcProvider()`, `withFederationRuntime()`, and `withRefreshTokenStore()`.
Invalid combinations are possible because capability objects are still constructed while backing handlers remain
nullable or outdated.

### 10. Performance-by-Design Sanity Check

| Question                                           | Answer | Notes                                               |
|----------------------------------------------------|--------|-----------------------------------------------------|
| Is caching required for acceptable performance?    | No     | main pressure is correctness/evolution, not caching |
| Are many objects created per request or resolve?   | No     | object churn is concentrated in build phase         |
| Could parts be plain functions instead of objects? | Yes    | some readiness wrappers are purely defensive shells |
| Is reflection on the hot path without mitigation?  | No     | not observed in reviewed paths                      |

**Conclusion:** no design-level performance risk was found. The active problem is composition fragility, not raw runtime
cost.

### 11. Failure Modes and Diagnostic Surface

| Question                                             | Answer    | Notes                                                                                |
|------------------------------------------------------|-----------|--------------------------------------------------------------------------------------|
| Are errors categorized?                              | Partially | domain/runtime exceptions exist, but several failures are generic `RuntimeException` |
| Do exceptions include context?                       | Partially | some are clear, many configuration failures are generic                              |
| Can the system explain why a decision was made?      | Yes       | diagnostics/explainability slice exists                                              |
| Are failure states explicit in the pipeline or axis? | Partially | optional capability readiness is deferred to runtime                                 |

**Conclusion:** failure handling is adequate inside domain flows, but weak at composition and optional-capability
boundaries.

### 12. Rewrite Heuristics

| Heuristic                                           | Weight | Checked |
|-----------------------------------------------------|--------|---------|
| Central abstraction is wrong                        | 2      | No      |
| Pipeline relies on implicit ordering                | 2      | No      |
| Configuration complexity mirrors design complexity  | 1      | Yes     |
| Usage requires explanation to avoid misuse          | 1      | Yes     |
| Performance depends on mitigation, not structure    | 1      | No      |
| New features require touching multiple core classes | 2      | Yes     |

**Rewrite Score:** 4

The score supports redesign, not rewrite. The domain axis is still correct, but the configuration/composition model is
stressed enough that changes now spread through builder logic, adapters, and docs.

# FINDINGS

### Finding: Avax container adapter no longer matches the current `Identity` contract

- **Symptom:** `AuthServiceProvider` still constructs `Identity` with only `sessionIdentity` and `jwtIdentity`, while
  the current `Identity` constructor requires the capability coordinators as non-optional arguments.
- **Root Cause:** the core `Identity` role evolved from a thin identity wrapper into a full capability coordinator, but
  the optional container adapter and onboarding example were not updated with the new assembly contract.
- **Impact:** the optional integration surface is broken for consumers using the Avax container, and the README
  instructs users to instantiate an impossible object.
- **Evidence:** [
  `integrations/avax-container/AuthServiceProvider.php:60`](../integrations/avax-container/AuthServiceProvider.php#L60)
  to [
  `integrations/avax-container/AuthServiceProvider.php:79`](../integrations/avax-container/AuthServiceProvider.php#L79), [
  `System/Capabilities/Identity/Identity.php:37`](../System/Capabilities/Identity/Identity.php#L37) to [
  `System/Capabilities/Identity/Identity.php:46`](../System/Capabilities/Identity/Identity.php#L46), [
  `README.md:57`](../README.md#L57) to [`README.md:71`](../README.md#L71). Verified by
  `vendor/bin/phpunit tests/Integrations/AvaxContainer/AuthServiceProviderTest.php`, which fails with two
  `ArgumentCountError` errors.
- **Risk Level:** High

### Finding: The canonical build path is fragile enough to break before user code runs

- **Symptom:** `AuthBuilder::ready()` trips over a fatal class-load issue in `ProjectAuthenticatedUser`, and the same
  method also assembles feature coordinators using readiness branches that can still produce invalid constructor
  arguments.
- **Root Cause:** one composition root owns validation, defaults, feature readiness, and cross-capability assembly for
  the whole product. That makes small local mistakes fatal to the global build path.
- **Impact:** the default bootstrap path is not trustworthy; a local import regression can break the kernel, and
  optional-feature assembly is hard to reason about safely.
- **Evidence:** [`System/Configuration/AuthBuilder.php:588`](../System/Configuration/AuthBuilder.php#L588) to [
  `System/Configuration/AuthBuilder.php:760`](../System/Configuration/AuthBuilder.php#L760), [
  `System/Configuration/AuthBuilder.php:1174`](../System/Configuration/AuthBuilder.php#L1174) to [
  `System/Configuration/AuthBuilder.php:1245`](../System/Configuration/AuthBuilder.php#L1245), [
  `System/Flows/CheckAuthentication/AuthenticateRequest/ProjectAuthenticatedUser.php:7`](../System/Flows/CheckAuthentication/AuthenticateRequest/ProjectAuthenticatedUser.php#L7)
  to [
  `System/Flows/CheckAuthentication/AuthenticateRequest/ProjectAuthenticatedUser.php:8`](../System/Flows/CheckAuthentication/AuthenticateRequest/ProjectAuthenticatedUser.php#L8), [
  `System/Capabilities/Identity/Passkey/Passkey.php:27`](../System/Capabilities/Identity/Passkey/Passkey.php#L27) to [
  `System/Capabilities/Identity/Passkey/Passkey.php:34`](../System/Capabilities/Identity/Passkey/Passkey.php#L34).
  Verified by `vendor/bin/phpunit tests/Configuration/AuthBuilderTest.php`, which fatals while loading
  `ProjectAuthenticatedUser`.
- **Risk Level:** High

### Finding: Optional capabilities are modeled as always-present facades with nullable internals

- **Symptom:** capability objects such as `OAuth`, `OpenIDConnect`, and `SCIM` are always exposed, but individual
  methods fail later with generic runtime exceptions like "not configured".
- **Root Cause:** readiness is encoded as nullable handler fields inside consumer-facing capability wrappers instead of
  explicit optional boundaries or fail-fast assembly contracts.
- **Impact:** invalid states are representable, consumers need internal knowledge to know which methods are safe, and
  configuration becomes hidden architecture.
- **Evidence:** [
  `System/Capabilities/ExternalIdentity/OpenIDConnect/OpenIDConnect.php:27`](../System/Capabilities/ExternalIdentity/OpenIDConnect/OpenIDConnect.php#L27)
  to [
  `System/Capabilities/ExternalIdentity/OpenIDConnect/OpenIDConnect.php:93`](../System/Capabilities/ExternalIdentity/OpenIDConnect/OpenIDConnect.php#L93), [
  `System/Capabilities/ExternalIdentity/OAuth/OAuth.php:41`](../System/Capabilities/ExternalIdentity/OAuth/OAuth.php#L41)
  to [
  `System/Capabilities/ExternalIdentity/OAuth/OAuth.php:199`](../System/Capabilities/ExternalIdentity/OAuth/OAuth.php#L199), [
  `System/Capabilities/IdentitySync/SCIM/SCIM.php:38`](../System/Capabilities/IdentitySync/SCIM/SCIM.php#L38) to [
  `System/Capabilities/IdentitySync/SCIM/SCIM.php:177`](../System/Capabilities/IdentitySync/SCIM/SCIM.php#L177), and the
  unconditional facade assembly in [
  `System/Configuration/AuthBuilder.php:1259`](../System/Configuration/AuthBuilder.php#L1259) to [
  `System/Configuration/AuthBuilder.php:1325`](../System/Configuration/AuthBuilder.php#L1325).
- **Risk Level:** Medium

### Finding: Auth documentation does not satisfy the repository’s own documentation governance

- **Symptom:** `Foundation/Auth/docs` contains broad narrative documents, but it does not mirror the `System/` tree,
  there are zero `how-this-works.md` files, and the README quick start is stale.
- **Root Cause:** documentation is being maintained as thematic coverage instead of as a filesystem-mirrored design
  artifact tied to ownership folders.
- **Impact:** the component cannot honestly claim compliance with the documentation standard in `Foundation/AI Prompts`;
  understanding still depends on opening the code.
- **Evidence:** documentation rules in [
  `Foundation/AI Prompts/how-to-document.md:31`](../../AI%20Prompts/how-to-document.md#L31) to [
  `Foundation/AI Prompts/how-to-document.md:59`](../../AI%20Prompts/how-to-document.md#L59) and [
  `Foundation/AI Prompts/how-to-document.md:150`](../../AI%20Prompts/how-to-document.md#L150) to [
  `Foundation/AI Prompts/how-to-document.md:154`](../../AI%20Prompts/how-to-document.md#L154);
  `find Foundation/Auth -name 'how-this-works.md'` returned `0`; `find Foundation/Auth/docs -path '*/System/*'` returned
  no mirrored `docs/System/...` subtree; stale example at [`README.md:57`](../README.md#L57) to [
  `README.md:71`](../README.md#L71).
- **Risk Level:** Medium

# DECISION

⚠️ **Redesign**

The system is fundamentally sound at the domain-axis level: the `System/` tree still tells a clear auth story through
capability coordinators and owned flows. The problem is that safe evolution no longer follows that same simplicity.
`AuthBuilder::ready()` has become the secondary axis that absorbs too much assembly logic, optional-feature readiness,
and cross-capability wiring, and that pressure is now visible as real breakage in the container adapter, the default
build path, and the public documentation. This is not a rewrite case because the domain boundaries and public kernel
model are still worth keeping. It is also not a simple "keep and improve" case because the current composition model is
already producing regressions that spread across adapters and consumer entry points. The correct next action is a
targeted redesign of composition and readiness modeling while preserving the high-level `Auth` and capability story.

# DECISIONS-LOG

- **2026-04-21:** Treated `Foundation/Auth` as a shared auth kernel, not as a PR-sized change set, because the review
  standard in `AI Prompts` is system-level.
- **2026-04-21:** Chose the primary axis as capability coordinator -> flow/runtime owner, because that is how the tree
  and public surface are structured in `README.md` and `docs/architecture/overview.md`.
- **2026-04-21:** Marked the secondary axis as configuration-driven assembly, because `AuthBuilder::ready()` now governs
  feature readiness and cross-capability composition.
- **2026-04-21:** Escalated from "keep and improve" to "redesign" after verifying real breakage in `AuthServiceProvider`
  tests and the `AuthBuilder` bootstrap path.
- **2026-04-21:** Did not mark rewrite candidate because the core domain slicing remains coherent; the failure cluster
  is concentrated in composition, optional capability modeling, and documentation drift.

# NEXT STEPS

## Constraints

- **API stability requirement:** preserve `Auth`, `AuthInterface`, and existing high-level capability entry points where
  possible
- **Performance budget:** keep request-resolution paths simple; composition changes must not add per-request indirection
- **Security boundaries:** no optional capability should look enabled when its safety/runtime contract is absent
- **Time and risk tolerance:** moderate; use small iterations with characterization tests before structural moves
- **Migration expectations:** preserve the kernel story, redesign the assembly story

## Kill Criteria

- After two iterations, adding one optional capability still requires touching `AuthBuilder`, an integration adapter,
  and public docs.
- Consumers can still obtain capability objects that only fail later with generic "not configured" runtime exceptions.
- The default bootstrap path still depends on broad incidental knowledge of the full `System/` tree.

## If Redesign

- **Which abstraction is being redesigned:** composition and readiness modeling around `AuthBuilder`, optional
  capability exposure, and thin integration seams
- **What remains intact:** the `System/` domain slices, `Auth` public story, and the kernel vs integration boundary
- **First 3 concrete actions:**
    1. Restore bootstrap integrity first: fix `AuthServiceProvider`, `README` quick start, and
       `ProjectAuthenticatedUser`, then add characterization tests that prove `AuthBuilder::ready()` and the Avax
       container adapter both build a usable kernel.
    2. Split `AuthBuilder` into smaller assemblers or readiness profiles per capability area, while keeping
       `Auth::configuration()` as the public entry.
    3. Replace nullable handler facades with explicit optional accessors or fail-fast build contracts so unsupported
       capability surfaces are not exposed as if they were available.
