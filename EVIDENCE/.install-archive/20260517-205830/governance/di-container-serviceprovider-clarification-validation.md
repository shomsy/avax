# DI/Container/ServiceProvider Clarification — Validation

Date: 2026-05-15
Type: Validation Evidence
Scope: Gate tooling output

## Gate Tooling Results

### check-container-service-locator.php

```
Status: PASS
```

Scans runtime folders for Container::get(), $container->get(), app(), resolve() patterns.
Application/Container component is excluded (container resolution is its job).

### check-direct-instantiation.php

```
Status: FAIL
Findings: 20+ constructor default parameter instantiations in runtime folders
```

Key findings:

- framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php — Multiple `= new` patterns
- framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php — `new Uri()` (value object, acceptable)
- framework/System/Flows/AuditContainerScope/AuditContainerScope.php — `new ContainerAnalyzer()`

Note: Some findings are value object construction (acceptable per Container Ownership Rule). Others are service
instantiation in Flows (violations).

### check-constructor-bloat.php

```
Status: FAIL
Warnings: 1 WARNING (13 params), 20+ CHECK (5-7 params)
```

Key finding:

- framework/System/Capabilities/Runtime/Runtime.php — 13 parameters (WARNING)

This is a diagnostic tool, not a blocker. Human judgment required.

### check-service-provider-coverage.php

```
Status: PASS (for ACTIVE components)
```

Results:

- Application/Cache: OK
- Application/Container: OK
- DataStack/Database: OK
- HTTP/Router: OK
- Identity/Auth: OK
- All SCAFFOLD/ROADMAP components: SKIP (exempt)

### check-runtime-composition-leaks.php

```
Status: FAIL
Findings: 30+ runtime composition leaks
```

Categories:

1. **class_exists() in scanners** — Health/diagnostic tools that need class discovery
2. **Builder instantiation in PublicSurface** — FailureBoundary, OpenAPI, ApiBlueprint
3. **Dispatcher/Resolver instantiation in Flows** — DispatchConfiguredRoute
4. **Null-coalescing fallbacks** — RollbackTenantSecurityChange, OpenAPI, ApiContracts

These are known deferred issues from previous cleanup passes.

## Summary

| Gate                            | Status | Notes                               |
|---------------------------------|--------|-------------------------------------|
| check-container-service-locator | PASS   | No service locator in runtime       |
| check-direct-instantiation      | FAIL   | Real issues found, needs code fixes |
| check-constructor-bloat         | FAIL   | Diagnostic, human judgment required |
| check-service-provider-coverage | PASS   | All ACTIVE components covered       |
| check-runtime-composition-leaks | FAIL   | Known deferred issues               |

Governance clarification pass is complete. Code remediation is a separate pass.
