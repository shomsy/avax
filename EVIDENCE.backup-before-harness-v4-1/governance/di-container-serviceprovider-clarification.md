# DI/Container/ServiceProvider Governance Clarification

Date: 2026-05-15
Type: Governance Clarification Pass
Scope: how-to-dependency-injection.md, how-to-code-review.md, how-to-production-readiness.md

## Purpose

Fix ambiguities and contradictions in DI, ServiceProvider, Configuration/Builders, factory, runtime composition,
PublicSurface, boot, examples, and ServiceProvider coverage governance documents.

## Changes Applied

### 1. how-to-dependency-injection.md

| Section | Change                                       | Reason                                                       |
|---------|----------------------------------------------|--------------------------------------------------------------|
| §3.4    | Removed "ONLY" contradiction                 | Contradicted Configuration/Builders allowance                |
| §3.5    | Added Approved Composition Contexts Rule     | Unified list of all allowed instantiation contexts           |
| §3.6    | Added Optional Dependency Binding Rule       | "Optional default ≠ hidden fallback"                         |
| §4.0    | Added Active Component ServiceProvider Rule  | ROADMAP/SCAFFOLD exempt, forbid empty shells                 |
| §4.4    | Strengthened Boot Rules                      | Explicit health-check limits, idempotency                    |
| §5.3    | Updated Allowed Exceptions                   | References §3.5 instead of listing contexts                  |
| §6.6    | Changed "SUSPICIOUS" to "FORBIDDEN"          | Factory graph assembly is forbidden, not suspicious          |
| §6.10   | Changed "should NOT" to "MUST NOT"           | Golden path examples are mandatory                           |
| §10.1   | Added Container Resolution Rule              | Explicit forbidden/allowed contexts for container resolution |
| §10.2   | Added Approved Container Resolution Contexts | Where container resolution is allowed                        |
| §10.3   | Removed "fall back to zero-arg new"          | Hidden fallback contradicts DI governance                    |
| §11.2   | Added Gate Enforcement Rule                  | Path/context-aware, not class-name allowlists                |
| §14     | Updated Final Law                            | Added gate enforcement, runtime/composition separation       |

### 2. how-to-code-review.md

| Section             | Change                               | Reason                                      |
|---------------------|--------------------------------------|---------------------------------------------|
| §77 governance list | Added how-to-dependency-injection.md | DI governance is mandatory for reviews      |
| §77 governance list | Added how-to-runtime-composition.md  | Runtime composition governance is mandatory |

### 3. how-to-production-readiness.md

| Section              | Change                        | Reason                                                   |
|----------------------|-------------------------------|----------------------------------------------------------|
| §3 Global Acceptance | Added 10 new DI/runtime gates | Runtime composition, service locator, forbidden patterns |

### 4. Gate Tooling Created

| Tool                                | Purpose                                         | Status                      |
|-------------------------------------|-------------------------------------------------|-----------------------------|
| check-container-service-locator.php | Detects service locator patterns in runtime     | PASS                        |
| check-direct-instantiation.php      | Detects ?? new, = new patterns in runtime       | Finds real issues           |
| check-constructor-bloat.php         | Detects constructors with 5+/8+ params          | Finds real issues           |
| check-service-provider-coverage.php | Checks ACTIVE components have ServiceProviders  | PASS (exempts SCAFFOLD)     |
| check-runtime-composition-leaks.php | Existing tool, updated with Container exclusion | Finds known deferred issues |

## Contradictions Resolved

1. **"ONLY" in §3.4 vs Configuration/Builders** — Resolved by creating unified §3.5 Approved Composition Contexts
2. **"SUSPICIOUS" for factory graph assembly** — Changed to "FORBIDDEN" (factories create results, not graphs)
3. **"should NOT" for examples** — Changed to "MUST NOT" (examples teach future AI/humans)
4. **"fall back to zero-arg new"** — Removed (hidden fallback contradicts explicit DI configuration)
5. **Service locator tolerance** — Changed to BLOCKER (service locator in runtime is forbidden)

## New Rules Introduced

1. **Active Component ServiceProvider Rule** — Only ACTIVE components require ServiceProviders
2. **Approved Composition Contexts Rule** — Path/context-based, not class-name allowlists
3. **Container Resolution Rule** — Container::get() forbidden in runtime execution
4. **Gate Enforcement Rule** — Tools scan by path context, not class name allowlists
5. **Optional Dependency Binding Rule** — Register defaults explicitly, not hidden fallbacks

## Cross-References Updated

- how-to-code-review.md §77: Added DI and runtime-composition to mandatory governance checks
- how-to-production-readiness.md §3: Added DI/runtime gates to Global Acceptance Criteria
- how-to-dependency-injection.md §14: Added gate enforcement to Final Law

## Known Findings (Deferred)

The following are real violations detected by the new tools. They are deferred from this pass as they require code
changes, not governance clarification:

### check-direct-instantiation.php

- DispatchConfiguredRoute.php — Assembly in Flow (composition-time operation in runtime folder)
- Multiple files — Constructor default parameter instantiation

### check-runtime-composition-leaks.php

- DispatchConfiguredRoute.php — Resolver/Dispatcher instantiation
- RuntimeBoundary adapters — class_exists() for runtime detection
- StaticStateScanner, ComponentHealthScanner — class_exists() for health scanning
- FailureBoundary — Builder instantiation in PublicSurface
- OpenAPI, ApiBlueprint — Builder instantiation in runtime code

### check-constructor-bloat.php

- Runtime.php — 13 parameters (WARNING)
- Multiple classes — 5-7 parameters (CHECK)

These findings are documented here for future remediation passes.
