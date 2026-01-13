# HTTP Kernel Component Architecture

## Overview

The HTTP Kernel is the central orchestrator for HTTP request processing in the Avax framework. It coordinates the flow from PSR-7 ServerRequestInterface to PSR-7 ResponseInterface through Router resolution and middleware pipelines.

## Core Principles

- **Single Responsibility**: Orchestrates request processing, delegates to specialized components
- **PSR Compliance**: PSR-7 request/response, PSR-15 middleware
- **Exception Boundary**: Catches all exceptions, converts to HTTP responses
- **Pipeline Architecture**: Composable middleware with clear execution order
- **Framework Agnostic**: No assumptions about controllers, views, or business logic

## Architecture Layers

### 1. Public API Layer (`Kernel`)

**Purpose**: Single, stable entry point for HTTP request processing.

**Interface Contract**:
```php
interface Kernel {
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
```

**Guarantees**:
- PSR-7 compliant input/output
- Exception safety (never throws)
- Deterministic request lifecycle
- BC guaranteed

### 2. Orchestration Layer (`HttpKernel`)

**Purpose**: Coordinates the complete request processing pipeline.

**Responsibilities**:
- Global middleware pipeline setup
- Router integration and error handling
- Response generation and formatting
- Exception boundary management

**Key Components**:
- `HttpKernel`: Main implementation
- `ControllerRequestHandler`: Final pipeline handler
- `RouteMiddlewareHandler`: Route-aware middleware bridge

### 3. Middleware Pipeline Layer (`Psr15MiddlewarePipeline`)

**Purpose**: Immutable, PSR-15 compatible middleware execution.

**Features**:
- Immutable pipeline construction
- PSR-15 `MiddlewareInterface` support
- RequestHandlerInterface chaining
- Short-circuit capability

**Design Decisions**:
- Immutable: `withMiddleware()` returns new instances
- PSR-15: Industry standard for PHP middleware
- Handler chain: Efficient execution without recursion limits

## Request Lifecycle

```
1. HTTP Request Received
   PSR-7 ServerRequestInterface → Kernel::handle()

2. Global Middleware Pipeline
   Kernel → Psr15MiddlewarePipeline (global middleware)
   → RouteMiddlewareHandler (route resolution + route middleware)
   → ControllerRequestHandler (controller execution)

3. Route Resolution
   RouteMiddlewareHandler → RouterInterface::resolve()
   → Route parameters set as request attributes
   → Route-specific middleware applied

4. Controller Execution
   ControllerRequestHandler → ControllerDispatcher
   → Business logic execution
   → Response generation

5. Response Return
   PSR-7 ResponseInterface returned to client

6. Exception Handling
   Any exception → Kernel exception boundary
   → Mapped to appropriate HTTP status code
   → Error response returned
```

## Middleware Architecture

### Global Middleware
- Applied to ALL requests
- Examples: CORS, security headers, logging, rate limiting
- Configured at Kernel instantiation

### Route-Specific Middleware
- Applied based on matched route
- Defined in route configuration
- Examples: authentication, authorization, input validation

### Middleware Contract

**What Middleware CAN Do**:
- Return `ResponseInterface` (short-circuit pipeline)
- Call `$handler->handle()` (continue pipeline)
- Access and modify request attributes
- Throw exceptions (caught by Kernel)

**What Middleware CANNOT Do**:
- Know about routing details
- Directly access controllers
- Modify request URI/path (affects routing)
- Assume execution order

### Pipeline Execution Order

```
Request → Global Middleware 1 → Global Middleware 2 → ... → Global Middleware N
      → Route Resolution → Route Middleware 1 → Route Middleware 2 → ... → Route Middleware N
      → Controller Execution → Response
```

## Error Handling Contract

### Exception Mapping

| Exception Type | HTTP Status | When Thrown |
|----------------|-------------|-------------|
| `RouteNotFoundException` | 404 | Router cannot match request |
| `MethodNotAllowedException` | 405 | Route exists but wrong method |
| `ConstraintValidationException` | 400 | Route parameters invalid |
| `Throwable` (unexpected) | 500 | Any other exception |

### Error Response Format

All errors return JSON responses:
```json
{
  "error": "Human-readable error message"
}
```

### Exception Boundary

The Kernel is the **single point of exception handling**:

```php
try {
    // Complete request processing
    return $pipeline->handle($request);
} catch (Throwable $exception) {
    // Centralized error handling
    return $this->handleException($exception);
}
```

## Integration Points

### Router Integration
- Kernel receives `RouterInterface` dependency
- Router exceptions are caught and converted to responses
- Route parameters become request attributes

### Controller Integration
- Controllers receive PSR-7 request with resolved parameters
- Controllers return PSR-7 responses
- Controller exceptions bubble up to Kernel boundary

### Middleware Integration
- PSR-15 standard interface
- Framework-agnostic middleware components
- Composable pipeline construction

## Performance Characteristics

- **Middleware Overhead**: O(n) where n = middleware count
- **Router Integration**: Single router call per request
- **Exception Handling**: Minimal overhead (only on errors)
- **Memory**: Immutable pipelines share structure efficiently

## Anti-Patterns

### ❌ Framework Coupling
```php
// WRONG - Kernel knows about framework details
class HttpKernel {
    public function handle(Request $request) {
        // Laravel-specific logic
        if (auth()->guest()) { /* ... */ }
    }
}
```

### ❌ Business Logic in Kernel
```php
// WRONG - Kernel does business logic
class HttpKernel {
    public function handle(Request $request) {
        $user = $this->userRepository->find($request->getAttribute('user_id'));
        // ... business logic ...
    }
}
```

### ❌ Multiple Entry Points
```php
// WRONG - Multiple ways to process requests
class HttpKernel {
    public function handle(Request $request) { /* ... */ }
    public function handleApi(Request $request) { /* ... */ }  // NO!
    public function handleWeb(Request $request) { /* ... */ }  // NO!
}
```

## Extension Points

### Custom Middleware
Implement `MiddlewareInterface` for cross-cutting concerns.

### Custom Exception Handling
Extend Kernel to customize exception → response mapping.

### Custom Controllers
Any callable that accepts PSR-7 Request and returns PSR-7 Response.

## Migration Guide

### From Laravel Kernel
- Replace `Kernel::handle()` calls with dependency injection
- PSR-15 middleware replaces Laravel middleware
- Global middleware configured in Kernel constructor
- Route middleware defined in route configuration

### From Symfony HttpKernel
- PSR-15 replaces Symfony event system
- Single `handle()` method instead of multiple event listeners
- Exception handling centralized instead of event-based

### From Custom Frameworks
- Implement `Kernel` interface
- Use PSR-15 middleware for existing logic
- Route resolution becomes middleware responsibility

---

*The HTTP Kernel provides the stable foundation for request processing. Everything above the Kernel is application-specific; everything below is infrastructure.*