# Why This Router Exists

## The Problem

Most PHP frameworks ship with routers that mix concerns, lack type safety, and make strong assumptions about your
application architecture. They often:

- **Mix registration with execution**: Route definitions are tightly coupled to runtime resolution
- **Lack type safety**: Weak typing leads to runtime errors
- **Make framework assumptions**: Built for specific controller patterns, middleware systems, or DI containers
- **Poor error handling**: Inconsistent 404/405 behavior
- **No clear boundaries**: Public API bleeds into internal implementation

## What Makes This Router Different

### 1. **Clean Architecture Separation**

**DSL Registration** (`RouterInterface`, `RouterDsl`):

- Pure route definition and validation
- Immutable route construction
- Framework-agnostic API

**Runtime Resolution** (`HttpRequestRouter`, `RouteMatcher`):

- Pure HTTP request matching
- Parameter extraction and validation
- Clear error contracts (404 vs 405)

**Execution Pipeline** (`RouterKernel`, `RoutePipeline`):

- Middleware orchestration
- Controller dispatching
- Stage-based processing

### 2. **Type Safety First**

```php
// Every route is validated at registration time
$router->get('/users/{id}', 'UserController@show')
    ->where(['id' => '\d+'])  // Compile-time validation
    ->name('user.show');      // IDE autocompletion

// Runtime guarantees
$userId = $request->getAttribute('id'); // int, validated
```

### 3. **Framework Agnostic**

**No assumptions about**:

- Controller structure (`'Controller@method'`, `['Controller', 'method']`, `callable`)
- Middleware interface (PSR-15 compatible)
- Dependency injection (works with any container)
- Response format (PSR-7 HttpMessage)

**Works with any PHP framework or standalone application**.

### 4. **Predictable Error Handling**

**404 vs 405 Distinction**:

- `GET /nonexistent` → 404 Not Found
- `POST /users` (when only GET exists) → 405 Method Not Allowed + `Allow: GET`

**Clear Exception Hierarchy**:

- `RouteNotFoundException` → 404
- `MethodNotAllowedException` → 405 with allowed methods
- `ConstraintValidationException` → 400 Bad Request

### 5. **Performance Conscious**

**Regex-based matching** with optimization:

- Pre-compiled patterns
- Fast rejection for non-matches
- Minimal memory footprint (~50-100 bytes/route)

**Optional caching**:

- Serialized route definitions
- Manifest-based invalidation
- No closure serialization issues

## Design Decisions

### Why Not Annotations?

- **Runtime cost**: Reflection on every request
- **Debugging difficulty**: Routes scattered across controllers
- **Type safety loss**: String-based route definitions
- **Caching complexity**: Cannot serialize closures

### Why Not Single-File Route Definitions?

- **Boot performance**: All routes loaded on every request
- **Memory usage**: Large route tables in memory
- **Developer experience**: No IDE autocompletion for route names

### Why Explicit Route Groups?

```php
// This router
$router->prefix('/api')->middleware(['auth'])->group(function($r) {
    $r->get('/users', 'UserController@index');
});

// vs Laravel's global state
Route::prefix('/api');
Route::middleware(['auth']);
Route::get('/users', 'UserController@index'); // Implicit grouping
```

**Explicit is better than implicit** - no global state, clear scope.

### Why PSR-7 Request Attributes for Parameters?

```php
// Parameters as request attributes
$id = $request->getAttribute('id'); // Type-safe, PSR-7 standard

// vs Route-specific parameter bags
$route = $request->getRoute();
$id = $route->getParameter('id'); // Framework-specific, less interoperable
```

**PSR-7 compatibility** enables use with any PSR-7 framework.

## Who This Router Is For

### ✅ **Framework Authors**

Need a solid routing foundation without imposing opinions.

### ✅ **Enterprise Applications**

Require type safety, clear contracts, and predictable behavior.

### ✅ **API-First Applications**

Need precise HTTP semantics and error handling.

### ✅ **Performance-Critical Applications**

Optimized matching and caching strategies.

### ❌ **Rapid Prototyping**

If you need routes in 5 minutes without thinking about architecture.

### ❌ **Legacy Codebases**

If you have 1000+ routes in global functions that "just work".

## Migration Pain Points Solved

### From Laravel Router

- ✅ Same fluent API (`$router->get()->middleware()->name()`)
- ✅ Route groups work identically
- ✅ Named routes with `route('name')` helper
- ❌ No global `Route` facade (by design - explicit dependency injection)

### From Symfony Router

- ✅ Annotation replacement with fluent API
- ✅ Same constraint syntax (`requirements` → `where()`)
- ✅ Same parameter extraction
- ❌ No XML/YAML route definitions (PHP-only for type safety)

### From Custom Routers

- ✅ Drop-in replacement if you implement `RouterInterface`
- ✅ Clear migration path with feature flags
- ✅ Backward compatibility during transition

## Future-Proofing

**This router is designed to last 5-10 years** because:

1. **Minimal public API**: Only `RouterInterface` and `Router` are guaranteed
2. **PSR standards**: PSR-7, PSR-15, PSR-11 compatibility
3. **Type safety**: PHP 8+ features prevent entire classes of bugs
4. **Clear boundaries**: Internal changes don't break public contracts
5. **Performance headroom**: Designed for 10k+ routes efficiently

## The "Secret Sauce"

**Most routers are built for the framework they're in.** This router was built as a **framework-agnostic component**
that could power any PHP HTTP application.

The difference is subtle but profound: instead of "how do we make routing work in our framework?", we asked "what would
a perfect router look like if it didn't have to care about frameworks?"

---

*This router exists because good routing shouldn't be framework-specific. It should be a commodity component that just
works, everywhere.*