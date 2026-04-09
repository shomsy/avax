# Router Component Architecture

## Overview

The Avax HTTP Router is a production-ready, type-safe HTTP request router designed for modern PHP applications. It
provides a clean separation between route registration (DSL), runtime resolution, and execution pipeline.

## Core Principles

- **BC Guaranteed**: Public API (`RouterInterface`, `Router`) maintains backward compatibility
- **Type Safe**: Full type declarations and validation
- **Separation of Concerns**: DSL registration ≠ Runtime resolution ≠ Pipeline execution
- **Deterministic**: Routes are immutable after registration
- **Performant**: Regex-based matching with optional caching

## Architecture Layers

### 1. Public API Layer (`RouterInterface`, `Router`)

**Purpose**: Stable, guaranteed API for applications.

**Components**:

- `RouterInterface`: DSL contract for route registration
- `Router`: Runtime contract for request resolution

**Guarantees**:

- Method signatures won't change
- Behavior is documented and stable
- Exceptions are well-defined

### 2. DSL Registration Layer (`RouterDsl`)

**Purpose**: Fluent interface for defining routes and groups.

**Responsibilities**:

- Route definition and validation
- Group context management (prefix, middleware, constraints)
- Attribute-based route registration
- Fallback handler setup

**Key Classes**:

- `RouterDsl`: Implements `RouterInterface`
- `RouteBuilder`: Immutable route construction
- `RouteGroupContext`: Group state management

### 3. Runtime Resolution Layer (`HttpRequestRouter`, `RouteMatcher`)

**Purpose**: Match HTTP requests to registered routes.

**Responsibilities**:

- HTTP method and path matching
- Parameter extraction and default application
- Constraint validation
- 404/405 error distinction

**Key Classes**:

- `HttpRequestRouter`: Main resolution engine
- `RouteMatcher`: Pure matching algorithm
- `RouteConstraintValidator`: Parameter validation

### 4. Execution Pipeline Layer (`RouterKernel`, `RoutePipeline`)

**Purpose**: Execute matched routes through middleware and dispatch.

**Responsibilities**:

- Middleware pipeline construction
- Request attribute injection
- Stage ordering enforcement
- Controller dispatching

**Key Classes**:

- `RouterKernel`: Pipeline orchestration
- `RoutePipeline`: Middleware chain
- `RouteExecutor`: Final dispatch

## Request Lifecycle

```
1. DSL Registration Phase
   Application → RouterInterface → RouterDsl → RouteBuilder → RouteRegistry

2. Bootstrap Phase
   RouteRegistry → HttpRequestRouter.add() → Internal route table

3. Runtime Resolution Phase
   HTTP Request → Router.resolve() → RouterKernel.handle()
   → HttpRequestRouter.resolve() → RouteMatcher.match()
   → Parameter extraction & validation

4. Execution Phase
   RouterKernel → RoutePipeline → Middleware chain → RouteExecutor → Controller
```

## Route Definition Structure

```php
final readonly class RouteDefinition {
    public string $method;        // HTTP method (GET, POST, etc.)
    public string $path;          // URL pattern (/users/{id})
    public mixed $action;         // Handler (callable, array, string)
    public array $middleware;     // Middleware stack
    public string $name;          // Optional route name
    public array $constraints;    // Parameter regex patterns
    public array $defaults;       // Default parameter values
    public ?string $domain;       // Domain constraint
    public array $attributes;     // Additional metadata
    public ?string $authorization; // Auth policy
    public array $parameters;     // Runtime parameters (populated during resolution)
}
```

## Error Handling Contract

### RouteNotFoundException (404)

**When**: No routes match the request path at all
**Thrown by**: `HttpRequestRouter::resolve()`
**Caught by**: `Router::resolve()` → Returns 404 response

### MethodNotAllowedException (405)

**When**: Routes exist for path but not the requested HTTP method
**Thrown by**: `HttpRequestRouter::resolve()`
**Caught by**: `Router::resolve()` → Returns 405 response with `Allow` header

### Constraint Validation Errors

**When**: Route parameters fail regex validation
**Thrown by**: `RouteConstraintValidator::validate()`
**Handled**: As 400 Bad Request or custom error

## Caching Strategy

**Format**: Serialized `RouteDefinition` array (no closures)
**Storage**: PHP file with manifest hash
**Validation**: Cache invalid if manifest doesn't match
**Fallback**: Rebuild from route definitions if cache invalid

## Anti-Patterns (What NOT To Do)

### ❌ Direct HttpRequestRouter Usage

```php
// WRONG - depends on internal implementation
$router = new HttpRequestRouter(...);
$router->resolve($request);
```

```php
// CORRECT - use public API
$router = $container->get(Router::class);
$router->resolve($request);
```

### ❌ Route Mutation After Registration

```php
// WRONG - routes are immutable
$route = $router->getRouteByName('user.show');
$route->path = '/modified'; // NO!
```

### ❌ Middleware in Route Actions

```php
// WRONG - middleware belongs in route definition
$router->get('/admin', function() {
    // auth check here - NO!
});
```

```php
// CORRECT - use middleware
$router->middleware(['auth'])->group(function($r) {
    $r->get('/admin', 'AdminController@index');
});
```

## Performance Characteristics

- **Route Registration**: O(1) per route
- **Route Matching**: O(n) where n = routes per method
- **Parameter Extraction**: O(k) where k = parameters in route
- **Cache Loading**: O(m) where m = cached routes
- **Memory**: ~50-100 bytes per route definition

## Extension Points

### Custom Route Constraints

Implement `RouteConstraintValidator` for domain-specific validation.

### Custom Middleware

Implement PSR-15 middleware interface.

### Custom Route Executors

Extend `RouteExecutor` for specialized dispatching.

## Migration Guide

### From Laravel Router

- Replace `Route::get()` with `$router->get()`
- Route groups work identically
- Middleware syntax: `middleware(['auth'])` instead of `middleware('auth')`

### From Symfony Router

- Replace `@Route` annotations with `$router->get()->name()`
- Parameter constraints: `where(['id' => '\d+'])` instead of `requirements`

## Future Compatibility

**Guaranteed Compatible**:

- Public API method signatures
- Exception types and messages
- Route definition structure
- DSL fluent interface

**May Change** (with deprecation notice):

- Internal implementation details
- Performance optimizations
- Additional configuration options

---

*This document defines the canonical architecture. Implementation details may vary but the public contracts and
behaviors documented here are guaranteed stable.*