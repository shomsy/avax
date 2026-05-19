# How-To 11/11 Enterprise-Grade Production Readiness Hardening — Preflight

## Identity

| Field | Value |
|---|---|
| Date | 2026-05-15 |
| Branch | main |
| Commit | b103d17e3eb10cd7519346256723efcbc0a45c76 |
| Message | V5.8.9 AuthBuilder Constructor Drift & Runtime Gate Closure is complete. |
| Remote | origin/main |
| Mode | Harness-Full Mode — governance-only hardening |

## Discovered How-To Inventory

All 19 files discovered under `.agents/how-to/`:

| # | File | Lines | Purpose |
|---|---|---|---|
| 1 | `how-to-architecture.md` | 2059 | System architecture, screaming-architecture, fractal ownership |
| 2 | `how-to-architecture-extension-with-ddd.md` | 2478 | PublicSurface rules, DDD integration into screaming architecture |
| 3 | `how-to-clean-code.md` | 1500 | Clean code principles, AI governance, multi-author synthesis |
| 4 | `how-to-code-review.md` | 1199 | Enterprise code review process, governance compliance matrix |
| 5 | `how-to-code-style.md` | 414 | PHP code style: constructor promotion, nullable types, imports, etc. |
| 6 | `how-to-coding-standards.md` | 1538 | Master prompt: pragmatic DDD, PHP 8.5, security, quality standards |
| 7 | `how-to-dependency-injection.md` | 1524 | DI law, ServiceProvider pattern, Container Ownership Rule |
| 8 | `how-to-design-components.md` | 2321 | Component shape, canonical filesystem law, plane model |
| 9 | `how-to-document.md` | 474 | Documentation standard, HowThisWorks format |
| 10 | `how-to-dogfooding.md` | 953 | Internal reuse, component adoption matrix |
| 11 | `how-to-events-listeners-event-sourcing-cqrs-realtime.md` | 1367 | Events DSL, CQRS, event sourcing, realtime |
| 12 | `how-to-git.md` | 67 | Git workflow, pre-commit review, commit discipline |
| 13 | `how-to-modern-php-attributes-di.md` | 789 | PHP 8.x features, attributes, compiled metadata, hot-path discipline |
| 14 | `how-to-production-readiness.md` | 1075 | Production readiness gates, status system, exception register |
| 15 | `how-to-runtime-composition.md` | 1048 | Runtime composition leak law, forbidden patterns, container ownership |
| 16 | `how-to-system-performance.md` | 2125 | Performance laws, hot paths, budgets, benchmarks |
| 17 | `how-to-system-security.md` | 2343 | Security laws, boundaries, classification, OWASP alignment |
| 18 | `how-to-unit-test.md` | 2549 | Unit testing governance, naming, AAA, TDD |
| 19 | `how-to-use-advanced-architecture-patterns.md` | 2043 | Advanced patterns: CQRS, Event Sourcing, Outbox, Saga |

Total: 19 files, ~28,000 lines of governance.

## Documents Read

- AGENTS.md
- CURRENT_TRUTH.md
- EVIDENCE/EXECUTION.md
- .agents/management/TODO.md
- .agents/management/ACTIVE.md
- .agents/management/BUGS.md
- All 19 `.agents/how-to/how-to-*.md` files
- .agents/GOVERNANCE_EXCEPTIONS.md (found)
- EVIDENCE/cleanup/accepted-exceptions-ledger.md (found)

## Documents Planned for Patching

| # | Document | Patches Planned |
|---|---|---|
| 1 | `how-to-architecture.md` | Minor: Canonical Term Registry cross-ref |
| 2 | `how-to-architecture-extension-with-ddd.md` | P1: DDD Factory vs Runtime Assembly rule |
| 3 | `how-to-clean-code.md` | P1: Large Unit Review Thresholds |
| 4 | `how-to-code-review.md` | P0: Security Must Scream, Security Trigger, Commit Block, Critical Quality Signal, Gate Self-Test, Large Unit Thresholds |
| 5 | `how-to-coding-standards.md` | Fix ServiceProvider wording (broad → ACTIVE components only) |
| 6 | `how-to-dependency-injection.md` | P0: Fix ServiceProvider contradiction. P1: PublicSurface Factory Boundary |
| 7 | `how-to-design-components.md` | P0: Fix ServiceProvider contradiction. P0: Component Status Ownership |
| 8 | `how-to-document.md` | Status State Machine cross-ref |
| 9 | `how-to-dogfooding.md` | **FIX**: 4-backtick fence defect trapping lines 2-55 in code block |
| 10 | `how-to-events-*-realtime.md` | **FIX**: Update V5.7 status from "NOT_STARTED" to "COMPLETE/GREEN", V5.8 from "PLANNED" to "COMPLETE/GREEN" |
| 11 | `how-to-git.md` | P0: Security Commit Block, commit discipline hardening |
| 12 | `how-to-modern-php-attributes-di.md` | No changes planned |
| 13 | `how-to-production-readiness.md` | **REWRITE**: Entire doc is massively stale (claims RED, CURRENT_TRUTH says GREEN). Add: State Machine, Security rules, Gate rules, Quality Ratchet, Examples rule, Exception Register |
| 14 | `how-to-runtime-composition.md` | No changes planned |
| 15 | `how-to-system-performance.md` | P1: Performance trigger cross-rule |
| 16 | `how-to-system-security.md` | P0: Security Must Scream, Security Review Trigger, Security Commit Block. P1: Performance trigger cross-rule |
| 17 | `how-to-unit-test.md` | **FIX**: Remove Serbian text at line 1 |
| 18 | `how-to-use-advanced-architecture-patterns.md` | No changes planned |
| 19 | `how-to.txt` | Not a how-to rule doc (raw backup) — no changes |

Total: 14 files will be patched (5 have P0 fixes, 2 have stale-content rewrites, 2 have markdown/text defects).

## Missing Expected Documents

| Expected | Status |
|---|---|
| `docs/governance/canonical-terms.md` | **MISSING** — must be created |
| `EVIDENCE/accepted-exceptions-ledger.md` (at canonical path) | **MISSING** — exists at `EVIDENCE/cleanup/` only, empty |
| `.agents/governance/canonical-terms.md` | **MISSING** |

All 19 how-to files are present. No governance document is outright missing, but supporting governance artifacts are absent.

## Conflicting Rules

| Conflict | Documents | Risk |
|---|---|---|
| ServiceProvider "every component" vs "every ACTIVE production component" | `how-to-coding-standards.md` line 1157 vs `how-to-dependency-injection.md` §4.0 | MEDIUM — agents may create ServiceProviders for ROADMAP/LABS components |
| Production readiness RED vs GREEN | `how-to-production-readiness.md` vs `CURRENT_TRUTH.md` | HIGH — agents reading production-readiness doc will think system is RED |
| V5.7 "NOT_STARTED" vs "COMPLETE/GREEN" | `how-to-events-*.md` vs `CURRENT_TRUTH.md` | HIGH — agents will not emit/rely on existing event infrastructure |
| Quality ratchet incomplete | `how-to-code-review.md` §14.1 has it, `how-to-production-readiness.md` lacks it | MEDIUM — ratchet rule not enforced in production gate context |

## Ambiguous Rules

| Rule | Document | Ambiguity |
|---|---|---|
| "Scope" for ServiceProvider exempt statuses | `how-to-dependency-injection.md` §4.0 | What exactly qualifies as PURE_FOUNDATION? Unclear boundary. |
| Static facade law | `how-to-runtime-composition.md` §7 | Says "may exist" but calls it YELLOW — unclear when GREEN permitted. |
| Container resolution contexts | `how-to-dependency-injection.md` §10.2 | Lists `composition roots (Avax::create, CreateApplication)` — what about AppKernel? |

## Weak Severity Rules

| Rule | Document | Issue |
|---|---|---|
| Static anonymous functions | `how-to-code-style.md` | Severity LOW — should be MEDIUM for hot paths |
| Pipe operator | `how-to-code-style.md` | Severity LOW — should be MEDIUM given potential misuse |
| Constructor promotion | `how-to-code-style.md` | Severity HIGH — correct |
| `@throws` documentation | `how-to-code-style.md` | Severity MEDIUM — should be HIGH for public APIs |

## Fake-GREEN Risks

| Risk | Details |
|---|---|
| Stale production-readiness doc | Agent reading it will think system is RED. Agent reading CURRENT_TRUTH will think GREEN. Disagreement = trust erosion. |
| Empty ServiceProvider directories | 35 components have `System/Configuration/` dir with no ServiceProvider. These are silent — no gate catches missing providers unless `check-service-provider-coverage.php` runs. |
| Empty exception register | `EVIDENCE/cleanup/accepted-exceptions-ledger.md` is empty. Any governance exception taken is invisible. |
| V5.7/V5.8 event roadmap status | Events doc says "NOT_STARTED" but real code exists. Agent may reimplement or avoid existing infrastructure. |

## Security Review Gaps

| Gap | Details |
|---|---|
| No Security Must Scream rule | Security findings can be hidden as "cleanup", "style", "low priority" |
| No Security Review Trigger rule | No trigger list — security review may be skipped silently |
| No Security Commit Block rule | GREEN commit with unresolved HIGH/BLOCKER security issue is not explicitly forbidden in git workflow |
| No global security review requirement | Security review evidence format not standardized |
| `how-to-system-security.md` lacks CI/gate enforcement section | All rules are manual review — no automated gates defined |

## Commit-Blocking Gaps

| Gap | Details |
|---|---|
| Security Commit Block rule | **MISSING** — no rule says "do not commit with unresolved security issue" |
| Recursive governance review | Exists in `how-to-code-review.md` and `how-to-git.md` — but not mandatory in all docs |
| Quality ratchet | Exists in `how-to-code-review.md` §14.1 — but not in commit flow |
| Zero-scan gate rule | Exists in `how-to-runtime-composition.md` §12.2 — not in commit flow |

## Gate Enforcement Gaps

| Gate | Status |
|---|---|
| `check-direct-instantiation.php` | Referenced in DI doc §11 — exists |
| `check-container-service-locator.php` | Referenced — exists |
| `check-service-provider-coverage.php` | Referenced — exists |
| `check-runtime-composition-leaks.php` | Referenced — exists |
| `check-component-canonical-shape.php` | Referenced — exists |
| `check-public-surface.php` | Referenced — exists |
| `check-advanced-pattern-folder-violations.php` | Referenced — exists |
| Security gate (self-test) | **MISSING** — no automated security governance gate |
| Performance gate (self-test) | **MISSING** — no automated performance governance gate |
| Gate self-test requirement | Missing as MANDATORY/BLOCKER — gates exist but their own testability is unproven |

## Validation Commands

Available from `AGENTS.md` and `how-to-production-readiness.md`:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress

php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-stage-lock.php
php avax runtime:doctor
```

## Next Step

Create `EVIDENCE/governance/33-how-to-11-11-worktree-baseline.md` — worktree classification.
