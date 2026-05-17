# Critical Quality Signal Rule

## Decision

The Critical Quality Signal rule is added to:
- `how-to-code-review.md` — as new Section 19
- `how-to-production-readiness.md` — as new Section 15
- `how-to-clean-code.md` — as new Section in review section
- `how-to-system-security.md` — as new Section 43 (cross-ref)
- `how-to-system-performance.md` — as new Section 44 (cross-ref)

## Rule Text

### Critical Quality Signal Rule

The review MUST loudly flag anything that threatens: security, data integrity, runtime safety, long-lived worker safety, dependency graph correctness, public API compatibility, static analysis baseline, test reliability, performance hot paths, observability of failures, rollback/recovery safety, container verification, request scope isolation, tenant isolation, state reset safety, or failure boundary correctness.

The following must not pass silently: hidden fallback construction, runtime service assembly, service locator usage in business code, missing dependency checks in business/runtime code, mutable static state without reset proof, request state stored in singleton, fake ServiceProvider, fake PublicSurface, broad try/catch swallowing errors, broad PHPStan ignores, weak tests, assertTrue(true), evidence claiming GREEN while validation says otherwise, gate PASS with RED content, mandatory gate with zero scanned files, fake compatibility shim, duplicate canonical concepts, large builders acting as hidden containers, or examples showing non-canonical style.

If it can create a security hole, corrupt data, hide a runtime failure, break long-lived workers, or fake correctness, it must scream in review.
