# Phase A Security and Performance Review

**Date:** 2026-05-15
**Purpose:** Security and performance review of all areas touched by Phase A closure

## 1. Security and Performance Review Table

| Area touched | Security triggered? | Performance triggered? | What was checked | Finding | Severity | Fixed? | Blocks commit? |
|---|---|---|---|---|---|---|---|
| AppKernel hot path | NO | YES | Request path composition, middleware assembly, reflection usage | No runtime composition — all middleware injected via constructor | NONE | YES — proven clean | NO |
| SessionIdentity | YES | NO | Session state in singleton, auth context leakage, `#[SensitiveParameter]` usage | Nullable sessionRegistry marked `#[SensitiveParameter]`, no session state in singleton | LOW | YES | NO |
| GraphQL schema builders | YES | NO | Schema data structure isolation, execution state leakage | Schema builders produce data structures only — no services or execution state | NONE | YES | NO |
| Cache lazy singletons | YES | YES | User-data leakage through cache keys, runtime instantiation cost | No user-data leakage — lazy patterns replaced with DI, no runtime instantiation | NONE | YES | NO |
| Database lazy singletons | NO | NO | Connection pooling integrity, query isolation | All lazy patterns replaced with DI — connection lifecycle managed by DI | NONE | YES | NO |
| Container internals | YES | NO | `?? new` patterns for registration VOs, service resolution safety | `?? new` patterns are for registration value objects — legitimate container internals, not service resolution | LOW | YES | NO |
| HTTP middleware | YES | YES | Middleware stack assembly, request path performance | Middleware assembled in ServiceProvider — no runtime construction, efficient `array_pop` iteration | NONE | YES | NO |
| Operations task builders | NO | NO | Task definition DSL, builder pattern isolation | All lazy patterns replaced with DI — builders are definition-time only | NONE | YES | NO |
| Queue worker registration | NO | NO | Worker pool configuration, job isolation | All lazy patterns replaced with DI — worker config is compile-time only | NONE | YES | NO |
| Resilience circuit breakers | NO | NO | Circuit breaker state isolation, failure containment | All lazy patterns replaced with DI — circuit state is per-instance | NONE | YES | NO |
| Events dispatcher registration | YES | NO | Event listener isolation, dispatch loop safety | Dispatcher registration is compile-time — no runtime listener injection in request path | LOW | YES | NO |
| Pipeline builder | NO | NO | Pipeline definition vs execution isolation | Pipeline builder is definition-time only — execution uses pre-built pipeline | NONE | YES | NO |
| API version resolver | NO | NO | Version resolution strategy isolation | Version resolver is configuration-time — no runtime version discovery | NONE | YES | NO |
| Gate allowance narrowing | YES | NO | Broad patterns hiding real instantiation | All 8 INVALID_ALLOWANCE patterns fixed — gate now uses specific class-name patterns | HIGH | YES | NO |
| TokenStore (remaining) | YES | NO | Auth token lifecycle, refresh token storage | Deferred to Phase B — not in request hot path, requires auth context redesign | MEDIUM | NO — deferred | NO |
| RollbackTenantSecurity (remaining) | YES | NO | Tenant security migration integrity | Deferred to Phase B — migration tooling, not runtime path | LOW | NO — deferred | NO |

## 2. Security Review Summary

### 2.1 SessionIdentity Security

- **Finding:** Nullable `sessionRegistry` parameter could leak session state in logs/stack traces
- **Fix:** Marked `#[SensitiveParameter]` on constructor parameter
- **Proof:** No session state stored in singleton — all session data is per-request via registry

### 2.2 GraphQL Security

- **Finding:** Schema builders could potentially store execution state
- **Proof:** Schema builders produce immutable data structures — no services, no execution context, no user data
- **Risk:** None — schema is definition-time only

### 2.3 Cache Security

- **Finding:** Lazy singleton cache could leak user data across requests
- **Proof:** Cache keys are isolated from user input — lazy patterns replaced with DI-managed instances
- **Risk:** None — no cross-request data leakage

### 2.4 Container Security

- **Finding:** `?? new` patterns could resolve arbitrary services at runtime
- **Proof:** Remaining `?? new` patterns are for registration value objects — not service resolution
- **Risk:** Low — container internals are compile-time only

### 2.5 HTTP Middleware Security

- **Finding:** Runtime middleware construction could inject unauthorized middleware
- **Proof:** Middleware stack is pre-assembled in ServiceProvider — no runtime construction possible
- **Risk:** None — middleware is immutable after assembly

## 3. Performance Review Summary

### 3.1 AppKernel Hot Path

- **Finding:** Runtime composition was paying per-request cost for reflection, class loading, and instantiation
- **Fix:** All composition moved to ServiceProvider — hot path is pure iteration
- **Proof:** Zero `class_exists()`, `new Build*`, `->build()`, `new *Middleware` in AppKernel
- **Impact:** Request path cost reduced from O(n) instantiation to O(n) iteration

### 3.2 Middleware Stack

- **Finding:** Inline middleware construction was creating objects per request
- **Fix:** Pre-assembled middleware stack, iterated via `array_pop`
- **Proof:** `array_pop` iteration is efficient — no allocation, no reflection
- **Impact:** Middleware iteration is now O(1) per middleware, no allocation

### 3.3 Cache Paths

- **Finding:** Lazy singleton patterns were creating cache instances on first access
- **Fix:** All cache instances managed by DI — no lazy instantiation in request path
- **Proof:** Zero `??= new` in cache hot paths
- **Impact:** No first-access latency spike — cache ready at request start

### 3.4 GraphQL Execution

- **Finding:** Schema builders could create types during execution
- **Proof:** Schema is built once at compile-time — execution uses pre-built schema
- **Impact:** No per-request schema construction cost

## 4. Decision

**No security HIGH or BLOCKER findings remain.** All security-sensitive areas have been reviewed and findings addressed.

**No performance regressions introduced.** All hot paths are cleaner than before — runtime composition eliminated from request path, lazy patterns replaced with DI.

**Remaining YELLOW findings are deferred to Phase B** and do not block commit:
- TokenStore requires auth context redesign
- RollbackTenantSecurityChange is migration tooling
- Some PublicSurface facades still self-instantiate (static facades, not runtime composition)
