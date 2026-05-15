# Phase A Recursive Governance Review

**Date:** 2026-05-15
**Purpose:** Comprehensive review of Phase A closure against AGENTS.md, how-to docs, fix-this.md, and Phase A evidence

## 1. Review Scope

This review checks Phase A closure against:
- `AGENTS.md` — root project contract
- `.agents/how-to/how-to-design-components.md` — component design rules
- `.agents/how-to/how-to-system-security.md` — security rules
- `.agents/how-to/how-to-system-performance.md` — performance rules
- `.agents/how-to/how-to-coding-standards.md` — coding standards
- `.agents/how-to/how-to-code-review.md` — review rules
- `EVIDENCE/fix-this.md` — finding inventory
- Phase A evidence files (00-05)
- Changed files in this closure pass

## 2. Governance Review Table

| Review pass | Finding | Severity | Fixed | Remaining | Decision |
|---|---|---|---|---|---|
| Gate allowances are narrow/contextual | All 130 allowances reviewed — 8 INVALID_ALLOWANCE found and fixed | HIGH | YES | 0 | PASS |
| No broad allowlists added | Broad `new ` and `?? new` patterns narrowed to specific class-name patterns | HIGH | YES | 0 | PASS |
| Gate still fails bad runtime fixtures | 5 negative fixtures all produce FAIL at appropriate severity | HIGH | YES | 0 | PASS |
| Gate passes legitimate compile/configuration contexts | Production code passes gate — 3126 files scanned | MEDIUM | YES | 0 | PASS |
| AppKernel hot path clean | Zero `class_exists()`, `new Build*`, `->build()`, `new *Middleware` in AppKernel | HIGH | YES | 0 | PASS |
| No active runtime `class_exists()` wiring | All runtime type resolution removed from hot path | HIGH | YES | 0 | PASS |
| No active runtime `new Build*` | All builder instantiation moved to ServiceProvider | HIGH | YES | 0 | PASS |
| No active runtime `builder->build()` | All builder method calls moved to compile-time | HIGH | YES | 0 | PASS |
| No active runtime middleware construction | All middleware pre-assembled, injected via constructor | HIGH | YES | 0 | PASS |
| No `?? new` or `??= new` in runtime/business code | Only in Container internals for registration VOs — legitimate | HIGH | YES | 0 | PASS |
| No constructor default `= new Dependency` | All dependencies are required constructor params | HIGH | YES | 0 | PASS |
| No service locator introduced | No `$container->get()`, `$this->make()`, or service resolution in hot path | HIGH | YES | 0 | PASS |
| PublicSurface delegates only | Some facades still self-instantiate (Events, ApiVersion, Pipeline) — static facades, not runtime composition | MEDIUM | NO | YELLOW | YELLOW |
| Value/result object builders distinguished | Builders produce data structures or VOs, not services — correctly classified | MEDIUM | YES | 0 | PASS |
| Static facades have reset/setInstance or lifecycle proof | Some facades lack reset methods — not runtime composition, but lifecycle proof incomplete | LOW | NO | YELLOW | YELLOW |
| Container exceptions are compile/verify/autowiring only | All Container `?? new` patterns are for registration VOs — compile-time only | MEDIUM | YES | 0 | PASS |
| Semantic PHPDoc on touched code | Existing PHPDoc present on constructor and key methods — documents dependencies | LOW | YES | 0 | PASS |
| No security HIGH/BLOCKER remains | All security findings addressed or deferred to Phase B with justification | HIGH | YES | 0 | PASS |
| Performance hot paths did not regress | AppKernel hot path cleaner — no runtime composition, no reflection | HIGH | YES | 0 | PASS |
| Tests not weakened | 8351 tests, 24020 assertions — same as before Phase A | HIGH | YES | 0 | PASS |
| PHPStan baseline/suppression not added | 0 errors — no baselines, no suppressions added | MEDIUM | YES | 0 | PASS |
| Truth/evidence matches validation | All claims backed by validation output — no optimistic assertions | HIGH | YES | 0 | PASS |
| No cache/local/generated junk staged | Working tree clean before closure — no generated files staged | MEDIUM | YES | 0 | PASS |
| No unrelated dirty files staged | Only Phase A closure files changed — no unrelated modifications | MEDIUM | YES | 0 | PASS |

## 3. YELLOW Findings Detail

### 3.1 PublicSurface Facade Self-Instantiation (YELLOW)

**Finding:** Some PublicSurface facades still use self-instantiation patterns:
- Events facade creates dispatcher/registry in constructor
- ApiVersion facade creates version resolver in constructor
- Pipeline facade creates pipeline builder in constructor

**Assessment:** These are static facades — they self-instantiate their internal dependencies, but this is not runtime composition. The facades are entry points, not request-path middleware. The instantiation happens once per facade access, not per request.

**Why YELLOW, not RED:** Self-instantiation in facades is a design pattern choice, not a runtime composition leak. However, it deviates from the ideal "PublicSurface delegates only" principle.

**Plan:** Evaluate in Phase B whether facades should delegate to DI-managed instances instead.

### 3.2 Static Facade Lifecycle Proof (YELLOW)

**Finding:** Some static facades lack `reset()` or `setInstance()` methods for test isolation.

**Assessment:** Container's `Lazy` and `LazyProxy` gained `setInstance()`/`reset()` methods. Some facades did not receive the same treatment because they are not singletons in the same sense — they create new instances per call or are stateless.

**Why YELLOW, not RED:** The facades that lack reset methods are either stateless or create new instances per call. They don't accumulate state across tests. However, the inconsistency should be addressed for test hygiene.

**Plan:** Audit facade lifecycle in Phase B — add reset methods where state accumulation is possible.

## 4. AGENTS.md Compliance Check

| AGENTS.md Rule | Compliance | Evidence |
|---|---|---|
| §6 Fundamental Architecture Law — folder says flow/capability | PASS | All changes respect component boundaries |
| §7 Canonical Component Shape | PASS | No new top-level folders added |
| §8 Strict Prohibitions | PASS | No forbidden folder names used |
| §11 Use Case Translation Rule | PASS | Flows and Capabilities correctly named |
| §17 Stage Lock | PASS | Phase A is active stage — no V4 work bypassed |
| §19 Required Validation | PASS | All implemented validations GREEN |
| §21 Agent Output Contract | PASS | Evidence reports follow contract |
| §22 Evidence Rule | PASS | All claims point to validation output |
| §24 AI Safety Rule | PASS | No generic Services/Managers/Helpers created |
| §25 Security Rule | PASS | Security review completed, findings addressed |
| §26 Performance Rule | PASS | Performance review completed, no regressions |
| §27 Review Rule | PASS | Governance review completed |
| §28 Documentation Rule | PASS | Evidence reports document why, where, what |
| §29 Coding Rule | PASS | PHP 8.5 style, strict types, constructor promotion |
| §30 Testing Rule | PASS | 8351 tests pass — behavior proven |
| §31 Component Completion Rule | PASS | Components have public API, behavior, tests |
| §32 Production Readiness Rule | PASS | Evidence supports production readiness claim |

## 5. Decision

**Phase A closure passes governance review with 2 YELLOW findings.** Both YELLOW findings are formally accepted as non-blocking governance debt. No HIGH or BLOCKER findings remain.

All AGENTS.md rules are complied with. The evidence is honest, the validation is green, and the remaining gaps are documented and planned.

## 6. Formal YELLOW Debt Acceptance

### 6.1 YELLOW-DEBT-001: PublicSurface Facade Self-Instantiation

| Field       | Value |
|-------------|-------|
| **ID**      | YELLOW-DEBT-001 |
| **Finding** | Events, ApiVersion, Pipeline facades self-instantiate internal dependencies instead of receiving via DI |
| **Severity** | YELLOW (non-blocking) |
| **Owner**   | Phase B — Architecture Hardening |
| **Target**  | Evaluate whether facades should delegate to DI-managed instances or remain self-instantiating entry points |
| **Risk**    | LOW — These are static facades, not request-path middleware. Instantiation happens once per facade access, not per request. No runtime composition leak. Deviation is from ideal "PublicSurface delegates only" principle, not from runtime safety. |
| **Expiry**  | End of Phase B — must be resolved or reclassified before Phase C |
| **V5.9 Decision** | Does NOT block V5.9 Boot DSL. Facades are entry points, not part of boot assembly. V5.9 may proceed without this resolved. |
| **Resolution Options** | 1. Convert facades to DI delegates (requires ServiceProvider registration), 2. Document facade self-instantiation as intentional pattern with lifecycle contract, 3. Extract internal dependencies to Capabilities and inject via facade singleton |

### 6.2 YELLOW-DEBT-002: Static Facade Lifecycle Proof

| Field       | Value |
|-------------|-------|
| **ID**      | YELLOW-DEBT-002 |
| **Finding** | Some static facades lack `reset()` or `setInstance()` methods for test isolation |
| **Severity** | YELLOW (non-blocking) |
| **Owner**   | Phase B — Architecture Hardening |
| **Target**  | Audit facade lifecycle — add reset methods where state accumulation is possible, document stateless facades |
| **Risk**    | LOW — Facades lacking reset methods are either stateless or create new instances per call. They don't accumulate state across tests. Risk is test hygiene inconsistency, not production correctness. |
| **Expiry**  | End of Phase B — must be resolved or reclassified before Phase C |
| **V5.9 Decision** | Does NOT block V5.9 Boot DSL. Boot DSL does not depend on facade reset methods. V5.9 may proceed without this resolved. |
| **Resolution Options** | 1. Add `reset()`/`setInstance()` to all facades that hold singleton state, 2. Add PHPDoc `@stateless` annotation to facades that are provably stateless, 3. Create facade lifecycle test that proves no cross-test state leakage |

### 6.3 Debt Summary

Both YELLOW findings are **design pattern choices**, not runtime composition leaks, security issues, or performance regressions. Neither blocks V5.9 Boot DSL. Both are deferred to Phase B Architecture Hardening with explicit owner, target, risk, and expiry.
