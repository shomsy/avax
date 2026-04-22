# Router Failure Mode Checklist

## Route Resolution Failures

### ❌ No Route Matches Path

**Trigger**: `GET /nonexistent-path`
**Expected**: `RouteNotFoundException` → 404 response
**Verified**: `HttpRequestRouter::resolve()` checks `RouteMatcher::match()` result
**Fallback**: `FallbackManager` invoked if configured

### ❌ Wrong HTTP Method for Existing Path

**Trigger**: `POST /users` (when only `GET /users` exists)
**Expected**: `MethodNotAllowedException` with `allowedMethods` → 405 response + `Allow` header
**Verified**: `findAllowedMethodsForPath()` identifies available methods
**Edge Case**: `HEAD` requests auto-fallback to `GET` via `HeadRequestFallback`

### ❌ Route Parameter Constraint Failure

**Trigger**: `GET /users/abc` with `where(['id' => '\d+'])`
**Expected**: `RouteConstraintValidator` throws exception → 400 Bad Request
**Verified**: Validation occurs after route match but before pipeline execution
**Recovery**: Clear error message indicating which constraint failed

### ❌ Named Route Not Found

**Trigger**: `Router::getRouteByName('nonexistent')`
**Expected**: `RouteNotFoundException` with descriptive message
**Verified**: `HttpRequestRouter::getByName()` checks `namedRoutes` array
**Use Case**: Template helpers, redirects, URL generation

## Registration Failures

### ❌ Invalid Route Path Syntax

**Trigger**: `$router->get('invalid{path', 'Controller@action')`
**Expected**: `InvalidArgumentException` during route registration
**Verified**: `RoutePathValidator::validate()` called in `RouteDefinition` constructor
**Prevention**: Fail fast at registration time, not runtime

### ❌ Invalid HTTP Method

**Trigger**: `$router->invalidMethod('/path', 'Controller@action')`
**Expected**: `InvalidArgumentException` (enum validation)
**Verified**: `HttpMethod` enum constraint
**Prevention**: Type-safe method registration

### ❌ Duplicate Named Routes

**Trigger**: Two routes with same `->name('duplicate')`
**Expected**: Silent overwrite (last wins) - **Current Behavior**
**Consider**: Should this throw? Currently allowed for flexibility
**Rationale**: Named routes are for reverse lookup, duplicates are valid

## Pipeline Execution Failures

### ❌ Middleware Throws Exception

**Trigger**: Auth middleware fails in pipeline
**Expected**: Exception bubbles up, pipeline stops
**Verified**: `RoutePipeline` executes middleware in order
**Recovery**: Framework-level exception handling

### ❌ Controller/Action Not Found

**Trigger**: Route points to non-existent controller method
**Expected**: `RouteExecutor` throws appropriate exception
**Verified**: `ControllerDispatcher::dispatch()` handles resolution
**Recovery**: Clear error messages for debugging

### ❌ Stage Ordering Violation

**Trigger**: Middleware registered after dispatch stage
**Expected**: `StageOrderException` during pipeline construction
**Verified**: `StageChain` enforces `stages → middleware → dispatch` order
**Prevention**: Fail fast during route registration

## Caching Failures

### ❌ Cache File Corrupt

**Trigger**: Manual cache file editing or disk corruption
**Expected**: Fallback to route recompilation
**Verified**: `RouteCacheLoader` validates cache integrity
**Recovery**: Automatic cache rebuild

### ❌ Manifest Mismatch

**Trigger**: Route files changed since cache creation
**Expected**: Cache invalidation, route recompilation
**Verified**: `RouteCacheManifest` hash comparison
**Recovery**: Transparent performance degradation during first request

### ❌ Closure Routes in Cache

**Trigger**: Attempting to cache routes with closures
**Expected**: `RuntimeException` during cache compilation
**Verified**: `RouteDefinition::toArray()` checks for closures
**Prevention**: Clear error message, fallback to non-cached execution

## Performance Degradation Modes

### ❌ Too Many Routes

**Trigger**: 10,000+ routes in single method
**Expected**: O(n) matching becomes slow
**Mitigation**: Group routes by domain/prefix, use caching
**Monitoring**: Route count logging in `RouteMatcher`

### ❌ Complex Regex Constraints

**Trigger**: Routes with expensive regex patterns
**Expected**: Parameter validation becomes bottleneck
**Mitigation**: Pre-compile patterns, cache validation results
**Prevention**: Complexity limits in `RouteConstraintValidator`

### ❌ Memory Leaks

**Trigger**: Route definitions holding large closures
**Expected**: Memory usage growth over time
**Prevention**: `RouteDefinition` immutability, no closure caching
**Monitoring**: Memory usage tracking in long-running processes

## Security Failure Modes

### ❌ Path Traversal

**Trigger**: `GET /../../../etc/passwd`
**Expected**: Sanitization prevents access
**Verified**: `RouteMatcher` uses `filter_var(FILTER_SANITIZE_URL)`
**Additional**: Constraint validation prevents malicious parameters

### ❌ Parameter Injection

**Trigger**: SQL injection via route parameters
**Expected**: Parameters are sanitized and validated
**Verified**: `extractParameters()` applies `FILTER_SANITIZE_STRING`
**Defense**: Constraint regex prevents invalid input

### ❌ Route Enumeration

**Trigger**: Automated scanning for route discovery
**Expected**: Consistent 404 responses for non-existent paths
**Verified**: No route information leakage in error responses
**Additional**: Same response timing for 404 vs constraint failures

## Recovery Strategies

### Circuit Breaker Pattern

- After N consecutive failures, enter degraded mode
- Return generic 500 responses instead of attempting routing
- Automatic recovery after successful requests

### Graceful Degradation

- Cache failures → Recompile routes (slower first request)
- Matcher failures → Fallback to simple 404
- Pipeline failures → Skip middleware, direct dispatch

### Monitoring Integration

- Route matching latency
- Cache hit/miss ratios
- Error rates by route pattern
- Memory usage per request

---

## Testing Checklist

- [ ] All exceptions map to correct HTTP status codes
- [ ] Error messages don't leak sensitive information
- [ ] Recovery mechanisms work under load
- [ ] Performance doesn't degrade >10% in failure modes
- [ ] Security controls remain active during failures