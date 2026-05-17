# Phase B Facade Behavior Inventory

**Date:** 2026-05-15

## 1. Facade Inventory

| Facade | File | Static state? | Self-instantiates? | Instantiated class | Public value or runtime machinery? | Uses container/provider wiring? | Reset proof? | Decision |
|---|---|---:|---:|---|---|---:|---:|---|
| ApiVersion | `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php` | YES (`?VersionRegistry $versionRegistry`) | YES (`new VersionRegistry()` in `registry()`) | VersionRegistry | RUNTIME MACHINERY — mutable state (support(), markDeprecated()) | NO — no provider, lazy fallback creates instance | YES (reset/setInstance) | **INVALID** — lazy self-instantiation of runtime machinery, no provider wiring |
| Pipeline | `components/Application/Pipeline/System/PublicSurface/Pipeline.php` | YES (`?HookRegistry $hookRegistry`) | YES (`new HookRegistry()` in `registry()`) | HookRegistry | RUNTIME MACHINERY — mutable registry (add(), execute()) | PARTIAL — PipelineServiceProvider registers HookRegistry but boot says "No boot wiring needed", facade ignores container | YES (reset/setInstance) | **INVALID** — lazy self-instantiation creates second independent registry, ignores provider |
| CompiledCache | `components/Application/Cache/System/PublicSurface/CompiledCache.php` | YES | NO (uses `instance()` that throws if not configured) | — | — | YES (`use()` method for DI) | YES | VALID — fails clearly if not configured |
| CallableSerialization | `components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php` | YES | YES (`new BuildCallableSerialization()` in `pair()`) | BuildCallableSerialization | RUNTIME MACHINERY — builder | NO — lazy fallback | YES | **YELLOW** — builder produces configuration, not service, but lazy fallback remains |
| Parallel | `components/Operations/Parallelism/System/PublicSurface/Parallel.php` | YES | YES (`new BuildParallelRuntime()->build()`) | BuildParallelRuntime | RUNTIME MACHINERY — runtime builder | NO — `setRuntime()` for testing | YES | **YELLOW** — builder produces runtime, lazy fallback remains |
| FailureBoundary | `framework/System/Capabilities/FailureBoundary/PublicSurface/FailureBoundary.php` | YES | YES (`new BuildFailureBoundary()->build()`) | BuildFailureBoundary | RUNTIME MACHINERY | NO — `setInstance()` for testing | YES + setInstance | **ACCEPTABLE** — proven safe per §7.1, deterministic |

## 2. Classification Summary

**INVALID (must fix):**
- ApiVersion — lazy `new VersionRegistry()` of mutable runtime machinery, no provider
- Pipeline — lazy `new HookRegistry()` creates second independent registry, ignores provider

**YELLOW (accept as debt or fix):**
- CallableSerialization — lazy builder fallback
- Parallel — lazy builder fallback

**ACCEPTABLE:**
- CompiledCache — fails clearly if not configured
- FailureBoundary — proven safe per §7.1

## 3. HookRegistry Placement Issue

HookRegistry lives in `PublicSurface/` but is **internal mutable machinery**. It is:
- Registered by PipelineServiceProvider as a singleton
- Created independently by Pipeline facade via lazy fallback
- A mutable registry that stores hook closures
- NOT a public API entry point — it's internal state

**Correct placement:** `components/Application/Pipeline/System/Capabilities/PipelineHooks/HookRegistry.php`

## 4. ApiVersionResolved Duplicate

ApiVersionResolved is defined in TWO files:
1. `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php` (lines 62-69) — inline class
2. `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersionResolved.php` (lines 9-16) — separate file

Both define `final readonly class ApiVersionResolved` in the same namespace. This is a **duplicate class definition**. PHP will load whichever file is autoloaded first and silently ignore the other, or throw "cannot redeclare class" if both are loaded.

**Fix:** Remove the inline class from ApiVersion.php. Keep the separate file.
