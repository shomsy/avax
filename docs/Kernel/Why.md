# Why HTTP Kernel Exists

## The Problem

Most frameworks have "kitchen sink" HTTP handling that mixes concerns, creates tight coupling, and makes testing difficult. They often:

- **Mix infrastructure with application logic**: Controllers know about middleware, routing, and responses
- **Complex inheritance hierarchies**: Base controllers with dozens of concerns
- **Global state everywhere**: Static facades, global functions, service locators
- **Inconsistent error handling**: Exceptions leak, error pages vary by context
- **Hard to test**: Request processing is buried in framework internals

## What Makes This Kernel Different

### 1. **Clean Separation of Concerns**

**Infrastructure Layer** (Kernel):
- PSR-7 request/response handling
- Middleware pipeline orchestration
- Exception boundary management
- Router integration

**Application Layer** (Controllers):
- Pure business logic
- PSR-7 request/response only
- No framework awareness
- Easy to unit test

### 2. **PSR Standards as Boundaries**

**PSR-7**: Request/Response contracts
```php
interface Kernel {
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
```

**PSR-15**: Middleware contracts
```php
interface MiddlewareInterface {
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
```

**Result**: Framework-agnostic components that work anywhere.

### 3. **Exception Safety First**

**Single Exception Boundary**:
```php
class HttpKernel {
    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            // All processing
            return $pipeline->handle($request);
        } catch (Throwable $e) {
            // Centralized error handling
            return $this->handleException($e);
        }
    }
}
```

**Guarantees**:
- Never throws exceptions to caller
- Consistent error responses
- Proper HTTP status codes
- No framework-specific error pages

### 4. **Middleware as First-Class Citizen**

**PSR-15 Pipeline**:
- Immutable construction
- Short-circuit capability
- Framework-agnostic
- Easy to test in isolation

**Clear Execution Order**:
```
Global Middleware → Route Resolution → Route Middleware → Controller
```

### 5. **Testability by Design**

**Kernel Contract Tests**:
```php
function kernel_handles_route_not_found() {
    // Given: Router throws RouteNotFoundException
    // When: Kernel processes request
    // Then: Returns 404 response
}
```

**Middleware Unit Tests**:
```php
function middleware_can_short_circuit() {
    // Given: Middleware returns response
    // When: Pipeline executes
    // Then: Controller never called
}
```

## Design Decisions

### Why PSR-15 Over Events?

**Events are too loose**:
```php
// Event system - anything can happen
$dispatcher->dispatch(new RequestEvent($request));
// Who knows what changed?

// PSR-15 - clear contract
$middleware->process($request, $handler);
// Returns Response or calls $handler
```

**PSR-15 guarantees**:
- Immutable request passing
- Clear short-circuit mechanism
- Predictable execution order

### Why Single Entry Point?

**Multiple entry points create complexity**:
```php
// BAD - multiple ways to process
$app->handleWeb($request);
$app->handleApi($request);
$app->handleAdmin($request);

// GOOD - single contract
$kernel->handle($request); // Always the same
```

**Benefits**:
- Consistent behavior
- Easier testing
- Clear extension points

### Why Centralized Error Handling?

**Distributed error handling is fragile**:
```php
// BAD - errors handled everywhere
try { $router->resolve(); } catch (Exception $e) { /* handle */ }
try { $middleware->process(); } catch (Exception $e) { /* handle */ }
try { $controller->execute(); } catch (Exception $e) { /* handle */ }

// GOOD - single boundary
try { $kernel->handle($request); } catch (Exception $e) { /* IMPOSSIBLE */ }
```

**Benefits**:
- Consistent error responses
- No exception leakage
- Easy debugging
- Proper HTTP semantics

### Why Immutable Middleware Pipeline?

**Mutable pipelines are dangerous**:
```php
// BAD - global state
$pipeline->add($middleware); // Affects all requests!

// GOOD - explicit composition
$pipeline = $pipeline->withMiddleware($middleware); // New instance
```

**Benefits**:
- Thread-safe
- Predictable behavior
- Easy to test
- Clear dependencies

## Who This Kernel Is For

### ✅ **Framework Authors**
Need clean HTTP foundations without imposing opinions.

### ✅ **Enterprise Applications**
Require predictable request processing and error handling.

### ✅ **API-First Applications**
Need consistent HTTP semantics and middleware pipelines.

### ✅ **Test-Driven Development**
Easy to test components in isolation.

### ❌ **Rapid Prototyping**
If you need "it just works" without thinking about architecture.

### ❌ **Legacy Monoliths**
If you have 20 layers of inheritance and global state everywhere.

## Migration Pain Points Solved

### From Laravel
- ✅ PSR-15 middleware replaces Laravel middleware
- ✅ Single `handle()` method instead of complex kernel hierarchy
- ✅ Exception handling centralized instead of scattered
- ❌ No global middleware registration (by design - explicit DI)

### From Symfony
- ✅ PSR-15 replaces event listeners
- ✅ Single entry point instead of HttpKernel with events
- ✅ Exception handling centralized instead of event-based
- ❌ No kernel termination events (use PSR-15 middleware)

### From Express.js
- ✅ PSR-15 middleware works like Express middleware
- ✅ Short-circuit with `return $response`
- ✅ Immutable request/response flow
- ❌ No mutable `req`/`res` objects

### From Custom Frameworks
- ✅ Drop-in replacement with `Kernel` interface
- ✅ PSR-15 middleware for existing cross-cutting concerns
- ✅ Clear separation between HTTP and business logic

## Future-Proofing

**This Kernel lasts 5-10 years because**:

1. **PSR Standards**: PSR-7/15 are stable industry standards
2. **Single Responsibility**: Kernel orchestrates, doesn't implement
3. **Exception Safety**: Never throws, always returns responses
4. **Testability**: Every component can be tested in isolation
5. **Framework Agnostic**: No assumptions about your application

## The "Secret Sauce"

**Most "framework kernels" are god objects that do everything.** This Kernel is a **minimal orchestrator** that composes specialized components.

The difference: instead of "how do we cram everything into one class?", we asked "what's the minimal interface needed to process HTTP requests?"

---

*This Kernel exists because clean HTTP processing shouldn't be framework-specific. It should be a commodity component that handles the hard parts so you can focus on your application.*