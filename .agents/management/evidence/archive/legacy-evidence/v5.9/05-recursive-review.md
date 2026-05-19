# EVIDENCE/v5.9/05-recursive-review.md

## V5.9 Boot DSL — Recursive Governance Review

**Date:** 2026-05-16
**Branch:** main
**Reviewer:** Agent (self-review)

---

## 1. Governance Documents Read

- `AGENTS.md` — root contract, naming rules, stage lock, prohibitions
- `.agents/how-to/how-to-design-components.md` — canonical component shape, forbidden folders
- `.agents/how-to/how-to-architecture.md` — fractal flow architecture, screaming architecture
- `.agents/how-to/how-to-dependency-injection.md` — DI law, ServiceProvider pattern, container ownership
- `.agents/how-to/how-to-coding-standards.md` — PHP 8.5 style, strict types, constructor promotion
- `.agents/how-to/how-to-code-review.md` — review checklist
- `.agents/how-to/how-to-system-security.md` — security boundaries
- `.agents/how-to/how-to-system-performance.md` — performance rules
- `.agents/how-to/how-to-unit-test.md` — test quality rules
- `.agents/how-to/how-to-production-readiness.md` — production readiness criteria

---

## 2. Compliance Matrix

| Rule                         | Finding                                                                 | Severity | Status    |
|------------------------------|-------------------------------------------------------------------------|----------|-----------|
| Folder = flow or capability  | BootDsl/ is a configuration capability; BootWithDsl is a flow           | N/A      | COMPLIANT |
| Unit = responsibility        | Each class has a single clear responsibility                            | N/A      | COMPLIANT |
| Function = exact action      | Method names describe exact action                                      | N/A      | COMPLIANT |
| No forbidden folders         | No Services/, Helpers/, Utils/, etc.                                    | N/A      | COMPLIANT |
| Strict types                 | All files use `declare(strict_types=1)`                                 | N/A      | COMPLIANT |
| Constructor promotion        | Used in BootDslEngine                                                   | N/A      | COMPLIANT |
| Named arguments              | Used where allowed, not in PHPUnit assertions                           | N/A      | COMPLIANT |
| Imports over FQN             | All classes imported, not FQN (except internal cross-namespace)         | N/A      | COMPLIANT |
| @throws tags                 | Not applicable — no checked exceptions                                  | N/A      | COMPLIANT |
| Readonly where useful        | BootWithDsl is readonly; BootDslEngine uses readonly constructor params | N/A      | COMPLIANT |
| Small public surface         | BootDslBuilder has 10 public methods; BootDslEngine has 3               | N/A      | COMPLIANT |
| Explicit failure behavior    | LogicException thrown for invalid state transitions                     | N/A      | COMPLIANT |
| No secret logging            | No logging in Boot DSL                                                  | N/A      | COMPLIANT |
| No unsafe filesystem         | ProjectPath is passed in, not constructed from user input               | N/A      | COMPLIANT |
| No hidden I/O                | Boot DSL has no I/O                                                     | N/A      | COMPLIANT |
| Tests prove behavior         | 6 tests cover enum, registry, builder, engine                           | N/A      | COMPLIANT |
| No skeleton without behavior | All classes have real behavior                                          | N/A      | COMPLIANT |
| No broad catch-and-ignore    | No catch-and-ignore in Boot DSL                                         | N/A      | COMPLIANT |
| Stage lock respected         | No production behavior for locked stages                                | N/A      | COMPLIANT |
| Backward compatibility       | Avax::boot() unchanged; Avax::create() unchanged                        | N/A      | COMPLIANT |

---

## 3. Violations Found

**None.**

---

## 4. Notes

- The Boot DSL uses `SimpleContainer` for this first slice because the full `DIContainer` depends on unimplemented flow
  classes (`RegisterDependencies`). This is documented in the design lock as acceptable for the first vertical slice.
  Future slices will migrate to the full DI container.
- `Avax::dsl()` is a separate method from `Avax::boot()` to avoid PHPStan union type issues across all existing call
  sites. This is a deliberate design decision: `boot()` stays backward-compatible, `dsl()` is the new fluent path.
- The Boot DSL does not implement auto-scanning of ServiceProviders. Providers must be explicitly registered via
  `withProvider()` / `withProviders()`. This matches the design lock scope.

---

## 5. Final Decision

**APPROVED — No unresolved findings. Ready for final evidence and commit.**
