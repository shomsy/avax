Savršeno! Sad ćemo ti odraditi **kompletnu profesionalnu dokumentaciju** za `Middleware` modul u Gemini Framework-u – u
istom stilu kao za Router, u Markdown formatu, spremno za `.md` fajl.

---

# 📘 Gemini HTTP Middleware — Internal Module Documentation

> 🛡️ **Purpose**: This documentation explains the responsibilities, structure, and real-world application of Gemini's
> HTTP middleware stack.  
> Built on Clean Architecture, SOLID principles, and secure-by-default design.  
> Fully PSR-compliant and battle-ready for production environments.

---

## 🧭 Index

- [MiddlewareManager](#middlewaremanager)
- [MiddlewarePipeline](#middlewarepipeline)
- [MiddlewarePipelineLogger](#middlewarepipelinelogger)
- [CorsMiddleware](#corsmiddleware)
- [CsrfMiddleware](#csrsmiddleware)
- [ExceptionHandlerMiddleware](#exceptionhandlermiddleware)
- [JsonResponseMiddleware](#jsonresponsemiddleware)
- [IpRestrictionMiddleware](#iprestrictionmiddleware)
- [RateLimiterMiddleware](#ratelimitermiddleware)
- [RequestLoggerMiddleware](#requestloggermiddleware)
- [SecurityHeadersMiddleware](#securityheadersmiddleware)
- [SessionLifecycleMiddleware](#sessionlifecyclemiddleware)
- [MiddlewareExecutionException](#middlewareexecutionexception)

---

## 🧠 Middleware Architecture

Gemini uses a layered middleware system with:

- Global middleware: Applied to every request.
- Route-specific middleware: Declared per-route.
- Middleware groups: Logical composition of reusable chains.

The `MiddlewareManager` compiles and executes pipelines using `MiddlewarePipeline`, with detailed tracing by
`MiddlewarePipelineLogger`.

---

## 📦 Core Orchestration Components

---

### 🧩 `MiddlewareManager`

**Location**: `Gemini\HTTP\Middleware\MiddlewareManager`

#### 🧠 Responsibilities

- Registers and resolves all middleware.
- Supports global middleware, named groups, per-route stacks.
- Provides execution + logging via `MiddlewarePipeline`.

#### 🔍 Key Methods

| Method                      | Description                          |
|-----------------------------|--------------------------------------|
| `addGlobalMiddleware()`     | Registers global middleware          |
| `registerGroup()`           | Registers named group                |
| `getPipeline()`             | Resolves and builds middleware stack |
| `executeRouteMiddleware()`  | Executes route-level middleware      |
| `executeGlobalMiddleware()` | Runs global stack independently      |

#### 🧪 Real-World Use

```php
$pipeline = $middlewareManager->getPipeline(['auth', 'log'], $request);
$response = $pipeline->execute($request);
```

---

### 🧩 `MiddlewarePipeline`

**Location**: `Gemini\HTTP\Middleware\MiddlewarePipeline`

#### 🧠 Responsibilities

- Manages prioritized middleware stack
- Executes pipeline until valid `ResponseInterface` is returned

#### ✅ Features

- Priority-based sorting (`lower` = higher priority)
- Final fallback handler if stack exhausted
- Fully PSR-compatible

#### 🔍 Real-World Analogy

Like a conveyor belt of middleware, each piece either passes or finishes the job.

#### 🔧 Signature

```php
public function add(callable $middleware, int $priority = 10) : void
public function execute(RequestInterface $request) : ResponseInterface
```

---

### 🧩 `MiddlewarePipelineLogger`

**Location**: `Gemini\HTTP\Middleware\MiddlewarePipelineLogger`

#### 🧠 Responsibilities

- Logs lifecycle of middleware pipeline
- Helps debug which middleware runs and in what order

#### 🧪 Real-World Output

```text
⚙️ Starting middleware pipeline
⛓ Executing middleware: AuthMiddleware
⛓ Executing middleware: ThrottleMiddleware
✅ Finished middleware pipeline
```

---

## 🧱 Standard Middleware Classes

---

### 🔐 `CorsMiddleware`

**Responsibility**: Adds `Access-Control-*` headers to support CORS

```php
$response->withHeader('Access-Control-Allow-Origin', '*');
```

> Enables cross-origin access for APIs.

---

### 🛡 `CsrfMiddleware`

**Responsibility**: Validates CSRF tokens for `POST`, `PUT`, `DELETE`, `PATCH`

- Rejects invalid tokens with `403 Forbidden`
- Integrates with `CsrfTokenManager`

```php
if (! $this->csrfTokenManager->validateToken($token)) {
    return $this->responseFactory->createResponse(403);
}
```

---

### 🔥 `ExceptionHandlerMiddleware`

**Responsibility**: Global exception catcher for the pipeline

- Logs all unhandled exceptions
- Uses **Spatie Ignition** in development
- Sends generic `500` in production

#### 🔧 Key Features

- Developer-friendly in `APP_ENV=development`
- Production-safe fallback messaging

---

### 🧬 `JsonResponseMiddleware`

**Responsibility**: Ensures all responses are JSON-formatted

> Ideal for APIs – intercepts and transforms response to JSON structure.

---

### 🚧 `IpRestrictionMiddleware` (Abstract)

**Responsibility**: Base class for building IP-filtering middleware

- Subclass must implement `isAllowedIp(string $ip)`
- Returns `403` if not allowed

#### 🔧 Extension Example

```php
class OfficeIpMiddleware extends IpRestrictionMiddleware {
    protected function isAllowedIp(string $ip): bool {
        return in_array($ip, ['192.168.1.10']);
    }
}
```

---

### 🚦 `RateLimiterMiddleware`

**Responsibility**: Enforces rate limits via `RateLimiterService`

- Tracks attempts per IP
- Uses PSR-6 compatible caching

#### 🔧 Configurable

- Max attempts (default: 60)
- Time window in seconds (default: 60)
- Identifier strategy (e.g. IP-based)

---

### 📊 `RequestLoggerMiddleware`

**Responsibility**: Logs each incoming request

```php
$this->logger->info('Incoming request', [
    'method' => $request->getMethod(),
    'uri' => (string) $request->getUri(),
    'ip' => $request->getClientIp()
]);
```

> Improves observability and forensic debugging

---

### 🧯 `SecurityHeadersMiddleware`

**Responsibility**: Adds defensive HTTP headers

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`

> Prevents MIME-sniffing, clickjacking, XSS

---

### 🗂 `SessionLifecycleMiddleware`

**Responsibility**: Manages session start and shutdown

- Starts session early
- Ensures it's saved and cleanly closed
- Includes fallback if response is missing

---

## ❗ `MiddlewareExecutionException`

**Location**: `Gemini\HTTP\Middleware\MiddlewareExecutionException`

### 🧠 Responsibility

Thrown when:

- Middleware pipeline fails to return a valid `ResponseInterface`
- Middleware itself crashes unexpectedly

### 🔧 Example

```php
throw new MiddlewareExecutionException("Bad middleware call");
```

---

## 🧪 Testability

- Every middleware is fully testable in isolation
- Pipelines can be mocked or traced with `MiddlewarePipelineLogger`
- Exceptions are catchable by upstream error handlers

---

## 🔐 Security Considerations

- `CsrfMiddleware`, `RateLimiterMiddleware`, `SecurityHeadersMiddleware` are key for web app hardening
- Middleware order matters – always log, then validate, then authorize

---

## 🧠 Final Thoughts

✅ Cleanly layered  
✅ Fully DI-compatible  
✅ Structured, observable, testable  
✅ Designed for high availability and traceability

---