# Facade Self-Instantiation Final Proof

**Date:** 2026-05-15

## 1. Inspection Method

Manual code review of ApiVersion.php and Pipeline.php for forbidden patterns:
- `new VersionRegistry` / `new HookRegistry`
- `?? new`
- `??= new`
- Lazy fallback
- Hidden default registry
- Container service locator

## 2. Results

| Facade | Forbidden pattern found? | Provider-wired? | Reset proof? | Decision |
|---|---|---|---|---|
| ApiVersion | 0 | YES (ApiVersioningServiceProvider) | YES (reset() + setInstance()) | PASS |
| Pipeline | 0 | YES (PipelineServiceProvider) | YES (reset() + setInstance()) | PASS |

## 3. ApiVersion Evidence

- Line 23: `private static ?VersionRegistry $versionRegistry = null;` — null default, no lazy creation
- Line 30-37: `setInstance()` — only accepts already-assembled VersionRegistry
- Line 48-58: `registry()` — throws RuntimeException if null, no `?? new` fallback
- No `new VersionRegistry` anywhere in file
- No `?? new` anywhere in file
- No `??= new` anywhere in file
- No container service locator (no `$container->get()` or `$container->make()`)
- PhpDoc: class explains PublicSurface boundary, @throws on all public methods

## 4. Pipeline Evidence

- Line 21: `private static ?HookRegistry $hookRegistry = null;` — null default, no lazy creation
- Line 28-35: `setInstance()` — only accepts already-assembled HookRegistry
- Line 46-56: `registry()` — throws RuntimeException if null, no `?? new` fallback
- No `new HookRegistry` anywhere in file
- No `?? new` anywhere in file
- No `??= new` anywhere in file
- No container service locator
- PhpDoc: class explains PublicSurface boundary, @throws on all public methods

## 5. Decision

Both facades are **PROVEN CLEAN**. No self-instantiation. Provider-wired. Reset-safe. Fail clearly when unconfigured.
