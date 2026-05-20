# Full Enterprise Skill Stack — Evidence Summary

Date: 2026-05-20
Executor: Qoder
Branch: main
Type: governance/skill authoring

## Discovery Summary

Existing skills found (13 total):
- avax-enterprise-remediation (bootloader)
- avax-autonomous-backlog-loop (autonomous execution)
- avax-enterprise-codecraft (HLD/LLD/OOP/SOLID)
- avax-component-dogfooding (component reuse)
- avax-runtime-performance-cache (hot-path/cache/worker)
- recovery, review, refactor, testing, validation, security, performance, documentation (reusable mounted skills)

No equivalent skills found for: source-of-truth-resolver, security-threat-model, api-compatibility-contract, test-evidence-quality, observability-failure-semantics.

## New Skills Created

1. `.agents/skills/avax-source-of-truth-resolver/SKILL.md`
   - Prevents stale source contradictions
   - 10-level source precedence: git > AGENTS.md > skills > TODO.md > fix-this.md > evidence > CURRENT_TRUTH > how-to > learning/memory > archive
   - Detects: DONE-as-active, TODO/fix-this mismatch, stale CURRENT_TRUTH, stale V5 references, evidence/validation disagreement, memory/git contradiction
   - Requires source-of-truth-decision.md
   - Hard blocker on unreconcilable contradiction

2. `.agents/skills/avax-security-threat-model/SKILL.md`
   - Explicit threat modeling for security-sensitive changes
   - Covers: auth, session, CSRF, tokens, crypto, redaction, secrets, logging, serialization, SQL, CSV, filesystem, redirects, headers, cookies, cache with user data, runtime state, dependency loading
   - 8 threat classes: injection, tampering, replay, leakage, stale state, privilege escalation, SSRF, path traversal, deserialization, cache poisoning, timing, downgrade
   - Requires: threat-analysis.md, negative-test-proof.md, security-review.md
   - Security HIGH/BLOCKER cannot be downgraded without evidence
   - Long-lived worker security checks

3. `.agents/skills/avax-api-compatibility-contract/SKILL.md`
   - Protects public API, PublicSurface, DSL, facade behavior, backward compatibility
   - Covers: App::/Route::/Auth::/Responses:: APIs, Builders, configuration public APIs, package exports, contract tests, deprecations
   - Requires: api-compatibility.md, public-contract-test-proof.md
   - 4 breaking change classifications: MAJOR_BREAK, MINOR_BREAK, PATCH_SAFE, BEHAVIOR_EXTENSION
   - Public API break without explicit approval = BLOCKER
   - DSL stability and facade behavior rules

4. `.agents/skills/avax-test-evidence-quality/SKILL.md`
   - Tests must prove behavior, not construction
   - Forbids: shallow tests, changing tests to fit broken behavior, missing negative tests, fake GREEN
   - Requires: behavior test, negative test, regression test, contract test, worker/runtime test
   - Requires: test-proof.md, negative-test-proof.md, regression-proof.md
   - 5 classifications: BEHAVIOR_PROVEN, SECURITY_NEGATIVE_PROVEN, CONTRACT_PROVEN, TESTS_TOO_SHALLOW_BLOCKER, TEST_SCOPE_WRONG_BLOCKER
   - Regression test rule: every fixed bug needs regression test
   - Negative test rule: security boundaries need negative tests
   - Contract test rule: public API changes need contract tests

5. `.agents/skills/avax-observability-failure-semantics/SKILL.md`
   - Clear failure behavior, debuggability, observability, safe error semantics
   - Covers: runtime, DI/container, boot, HTTP, security, filesystem, persistence, queue, worker, cache, external IO, failure boundaries
   - 12 failure semantics questions: what can fail, where, exception type, error message safety, redaction, log events, metrics, retryable vs fatal, fail-open vs fail-closed, worker state, user vs developer message, debuggability
   - 5 failure classifications: FAIL_CLOSED, FAIL_OPEN, RETRYABLE, FATAL, DEGRADED
   - Requires: failure-semantics.md, observability-review.md
   - Error message safety rules (no secrets, PII, raw SQL, stack traces)
   - Long-lived worker observability rules

## Skills Updated

1. `.agents/skills/index.md`
   - Added 10 new routing entries for all new skills
   - Added 5 new entries to Skill Files table

2. `.agents/skills/avax-enterprise-remediation/SKILL.md`
   - Rewrote Bootloader Rule with comprehensive routing: autonomous backlog loop, enterprise codecraft, component dogfooding, runtime performance cache, source-of-truth resolver, security threat model, API compatibility contract, test evidence quality, observability failure semantics
   - Clarified: bootloader is not the whole OS; agents must not use only bootloader if specific skills apply

3. `.agents/skills/avax-autonomous-backlog-loop/SKILL.md`
   - Added source-of-truth-resolver to boot order (step 3)
   - Updated per-slice flow with security threat model, API compatibility contract, observability failure semantics, test evidence quality routing
   - Added source-of-truth-decision.md requirement per slice

4. `AGENTS.md`
   - Added section 24.5: Full `.agents` Operating System Rule
   - Establishes bootloader vs operating system distinction
   - Mandates full .agents discovery
   - Requires source precedence
   - Defines PARTIAL vs HARD_BLOCKER
   - Routes: production code → codecraft, components → dogfooding, runtime → performance cache, security → threat model, public API → compatibility contract, tests → evidence quality, failure-prone → observability semantics, every task → source-of-truth resolver

## Routing Changes

| Task Type | Required Skills |
|-----------|----------------|
| Every task | source-of-truth-resolver |
| Autonomous sweep | autonomous-backlog-loop + source-of-truth-resolver |
| Production code | enterprise-codecraft + source-of-truth-resolver |
| Component work | component-dogfooding + source-of-truth-resolver |
| Runtime/cache/performance | runtime-performance-cache + source-of-truth-resolver |
| Security-sensitive | security-threat-model + source-of-truth-resolver |
| Public API/PublicSurface | api-compatibility-contract + source-of-truth-resolver |
| Tests/validation | test-evidence-quality + source-of-truth-resolver |
| Failure-prone/runtime | observability-failure-semantics + source-of-truth-resolver |

## New Mandatory Gates

- Source-of-Truth Gate: source discovery, conflict detection, precedence application, contradiction resolution, source-of-truth-decision.md
- Threat Model Gate: asset/attacker/input analysis, threat classification, fail-closed proof, negative tests, worker-state risk
- API Compatibility Gate: public API inventory, backward compatibility assessment, migration path, contract tests, deprecation timeline
- Test Evidence Gate: behavior proof, negative proof, regression proof, contract proof, shallow test detection
- Observability/Failure Gate: failure point identification, exception classification, error message safety, redaction, retry limits, fail-closed proof

## Validation

```
composer validate --no-check-publish: GREEN (./composer.json is valid)
php tooling/governance/check-governance-index-current.php: GREEN (Governance index is current)
php tooling/governance/check-root-evidence-hygiene.php: GREEN (Root Evidence Hygiene PASSED)
```

All validation GREEN.

## Final Decision

FULL_ENTERPRISE_SKILL_STACK_ADDED
