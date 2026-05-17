# Phase A: DispatchConfiguredRoute Runtime Leak Closure

## Summary

Eliminated 3 runtime composition leak findings from `DispatchConfiguredRoute` (Flows/ class).

## Findings Before

| Line | Finding | Severity |
|------|---------|----------|
| 29 | Builder instantiation in runtime code | HIGH |
| 29 | Null-coalescing fallback to new service | HIGH |
| Various | ControllerResolver/ArgumentResolver/ControllerDispatcher assembly in Flows | HIGH |

## Root Cause

`DispatchConfiguredRoute` (a `Flows/` class) contained static factory methods (`fromRoutesFile`, `fromRouteDefinitions`, `fromRegisteredRoutes`) that assembled the full object graph including `new ControllerResolver`, `new ArgumentResolver`, `new ControllerDispatcher`. Assembly belongs in `Configuration/Builders/`.

## Fix

1. **Created** `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php` — configuration builder that assembles the full `DispatchConfiguredRoute` object graph. All `new` calls for infrastructure classes live here (approved composition context).

2. **Stripped** `DispatchConfiguredRoute.php` to pure runtime execution: constructor + `__invoke()` only. Removed static factory methods, test injection, and builder caching. Callers now use `BuildDispatchConfiguredRoute` directly.

3. **Updated** `ApplicationBuilder.php` (Composition root) to call `(new BuildDispatchConfiguredRoute())->fromRoutesFile()` and `(new BuildDispatchConfiguredRoute())->fromRouteDefinitions()` directly.

## Verification

```
php tooling/refactor/check-runtime-composition-leaks.php | grep DispatchConfiguredRoute
# 0 findings
```

## Files Changed

- `framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php` — stripped to constructor + __invoke
- `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php` — NEW
- `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php` — updated caller
