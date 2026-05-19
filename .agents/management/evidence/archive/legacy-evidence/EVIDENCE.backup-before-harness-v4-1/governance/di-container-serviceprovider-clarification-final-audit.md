# DI/Container/ServiceProvider Clarification — Final Audit

Date: 2026-05-15
Type: Final Audit Report
Scope: Governance documents, gate tooling, evidence

## Governance Documents Modified

| Document                       | Sections Changed                                                        | Lines Changed |
|--------------------------------|-------------------------------------------------------------------------|---------------|
| how-to-dependency-injection.md | §3.4, §3.5, §3.6, §4.0, §4.4, §5.3, §6.6, §6.10, §10.1-10.5, §11.2, §14 | ~200          |
| how-to-code-review.md          | §77 (governance list)                                                   | ~2            |
| how-to-production-readiness.md | §3 (Global Acceptance Criteria)                                         | ~10           |

## Gate Tools Created

| Tool                                | File              | Lines |
|-------------------------------------|-------------------|-------|
| check-container-service-locator.php | tooling/refactor/ | 180   |
| check-direct-instantiation.php      | tooling/refactor/ | 180   |
| check-constructor-bloat.php         | tooling/refactor/ | 160   |
| check-service-provider-coverage.php | tooling/refactor/ | 240   |

## Gate Tools Updated

| Tool                                | Change                                            |
|-------------------------------------|---------------------------------------------------|
| check-runtime-composition-leaks.php | Added /Application/Container/ to allowed contexts |

## Evidence Files Created

| File                                                                          | Purpose                                            |
|-------------------------------------------------------------------------------|----------------------------------------------------|
| EVIDENCE/governance/di-container-serviceprovider-clarification.md             | Change summary, contradictions resolved, new rules |
| EVIDENCE/governance/di-container-serviceprovider-clarification-validation.md  | Gate tooling output                                |
| EVIDENCE/governance/di-container-serviceprovider-clarification-final-audit.md | This file                                          |

## Rules Summary

### New Mandatory Rules

1. **Active Component ServiceProvider Rule** — Only ACTIVE components need ServiceProviders
2. **Approved Composition Contexts Rule** — Path/context-based exemption, not class-name allowlists
3. **Container Resolution Rule** — Service locator forbidden in runtime execution
4. **Gate Enforcement Rule** — Tools must scan by path context, not class-name allowlists
5. **Optional Dependency Binding Rule** — Explicit default binding, not hidden fallbacks

### Strengthened Rules

1. **Factory Precision** — "SUSPICIOUS" changed to "FORBIDDEN" for graph assembly
2. **Golden Path Examples** — "should NOT" changed to "MUST NOT"
3. **Boot Idempotency** — Explicit health-check limits added
4. **Final Law** — Added gate enforcement, runtime/composition separation

## Contradictions Resolved

| Contradiction                                          | Resolution                                 |
|--------------------------------------------------------|--------------------------------------------|
| "ONLY" in §3.4 vs Configuration/Builders               | Unified §3.5 Approved Composition Contexts |
| "SUSPICIOUS" vs "FORBIDDEN" for factory graph assembly | Changed to FORBIDDEN                       |
| "should NOT" vs "MUST NOT" for examples                | Changed to MUST NOT                        |
| "fall back to zero-arg new" vs explicit DI             | Removed hidden fallback                    |
| Service locator tolerance vs DI mandate                | Changed to BLOCKER                         |

## Cross-Document Consistency

| Document Pair                      | Consistency | Notes                              |
|------------------------------------|-------------|------------------------------------|
| DI doc vs runtime-composition doc  | CONSISTENT  | Both use path/context-aware rules  |
| DI doc vs code-review doc          | CONSISTENT  | Code review includes DI governance |
| DI doc vs production-readiness doc | CONSISTENT  | Production gates include DI checks |
| DI doc vs tooling                  | CONSISTENT  | Tools implement governance rules   |

## Acceptance Criteria

- [x] All 18 sections of requested changes applied
- [x] Contradictions identified and resolved
- [x] Language strengthened where ambiguous (SUSPICIOUS→FORBIDDEN, should→MUST)
- [x] Gate tools created and functional
- [x] Gate tools are path/context-aware, not class-name allowlists
- [x] Cross-references updated across all documents
- [x] Evidence files created
- [x] Validation run and documented

## Status

**GREEN** — Governance clarification pass complete.

Code remediation for detected violations is a separate pass.
