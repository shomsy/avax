# Phase A AppKernel Hot Path Proof

**Date:** 2026-05-15
**File:** `components/HTTP/System/Capabilities/Kernel/AppKernel.php`
**Purpose:** Prove the AppKernel hot path contains no runtime composition

## 1. Hot Path Check Table

| Check | Result | Evidence |
|---|---|---|
| No runtime `class_exists()` wiring | PASS | AppKernel contains zero `class_exists()` calls — no runtime type resolution |
| No `new Build*` | PASS | No builder instantiation anywhere in AppKernel — middleware assembly removed |
| No `->build()` in request execution | PASS | No builder method calls in request handling path |
| No `new Middleware` | PASS | No middleware construction in AppKernel — all middleware injected |
| No `$middleware[] = new` | PASS | No inline middleware array construction — middleware stack is injected |
| Middleware stack injected | PASS | `middlewareStack` received via constructor parameter |
| Dependencies required by constructor | PASS | All dependencies are constructor parameters — no optional fallbacks to `new` |
| No service locator fallback | PASS | No `$container->get()`, no `$this->make()`, no service resolution in hot path |
| Touched code has Semantic PHPDoc | VERIFY | Existing PHPDoc present on constructor and key methods — documents dependencies and behavior |

## 2. AppKernel Hot Path Analysis

### 2.1 Before Phase A

The AppKernel previously contained:
- Runtime `class_exists()` checks for middleware discovery
- `new BuildMiddleware` builder instantiation
- `->build()` calls to assemble middleware at request time
- Inline `$middleware[] = new SomeMiddleware()` construction
- Service locator-style fallback for dependency resolution

This meant every HTTP request paid the cost of runtime composition — reflection, class loading, builder execution, and middleware instantiation all happened in the hot path.

### 2.2 After Phase A

The AppKernel now:
- Receives `middlewareStack` as a constructor parameter
- Iterates the pre-assembled middleware stack via `array_pop` (efficient)
- Contains zero `class_exists()`, `new Build*`, `->build()`, or `new *Middleware` calls
- Requires all dependencies through constructor — no optional fallbacks
- Delegates composition to `Configuration/ServiceProvider`

### 2.3 Middleware Assembly Location

Middleware is now assembled in `Configuration/ServiceProvider`:
- Compile-time / assembly-time only
- Not in request path
- Not subject to runtime composition rules
- Built once during application bootstrap

### 2.4 RouterBootstrapper Update

`RouterBootstrapper` was updated to:
- Accept `middlewareStack` as a parameter
- Pass it through to AppKernel construction
- No longer delegate to AppKernel for middleware discovery

## 3. Hot Path Performance Impact

| Metric | Before | After |
|---|---|---|
| `class_exists()` per request | Yes (multiple) | 0 |
| `new Build*` per request | Yes | 0 |
| `->build()` per request | Yes | 0 |
| `new *Middleware` per request | Yes (inline) | 0 |
| Reflection per request | Yes (implicit) | 0 |
| Middleware iteration | N/A | `array_pop` (efficient) |

The hot path is now purely iterative — no instantiation, no reflection, no class loading during request handling.

## 4. Decision

The AppKernel hot path is **proven clean**. All runtime composition has been moved to compile-time/assembly-time in `Configuration/ServiceProvider`. The hot path contains only:

- Constructor-received dependencies
- Pre-assembled middleware stack iteration
- Request handling logic

No service locator, no runtime discovery, no builder patterns, no inline instantiation. The AppKernel is now a pure request handler, not a composition engine.
