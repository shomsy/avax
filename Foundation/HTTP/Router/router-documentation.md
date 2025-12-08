Savršeno — evo ti kompletna **profesionalna dokumentacija za Gemini HTTP Router**, spojena u jedan `.md`-friendly blok,
100% spremna za copy-paste direktno u tvoj repo:

---

# 📘 Gemini HTTP Router — Internal Documentation

> 🚀 **Purpose:** This documentation explains the architecture, responsibilities, and usage of internal HTTP router
> components powering the request-to-response flow in the Gemini framework. Built with **Clean Architecture**, **SOLID
principles**, and **production-grade conventions**.

---

## 🧭 Index

- [Router](#router)
- [RouterKernel](#routerkernel)
- [ControllerDispatcher](#controllerdispatcher)
- [MiddlewareManager](#middlewaremanager)
- [RouteBuilder](#routebuilder)
- [RouteDefinition](#routedefinition)
- [RouteRegistrarProxy](#routeregistrarproxy)
- [HttpRequestRouter](#httprequestrouter)
- [RouteCollection](#routecollection)
- [HeadRequestFallback](#headrequestfallback)
- [RouteCacheCompiler](#routecachecompiler)
- [RouteCacheLoader](#routecacheloader)
- [RouteBootstrapper](#routebootstrapper)
- [HttpMethod Enum](#httpmethod-enum)
- [RouteGroupBuilder / Registrar](#routegroupbuilder--registrar)
- [RouteConstraintValidator](#routeconstraintvalidator)
- [RouteGroupContext](#routegroupcontext)
- [Exceptions](#exceptions)

---

## 📦 Core Components

### 📄 `Router`

**Location:** `Gemini\HTTP\Router`  
**Responsibility:** Public interface for route registration.

```php
$router->get('/logs/{file}', [LogViewerController::class, 'show'])
    ->name('logs.show')
    ->where(['file' => '[\w.-]+\.log$'])
    ->middleware(['auth']);
```

---

### 📄 `RouterKernel`

**Location:** `Gemini\HTTP\Router\Kernel\RouterKernel`  
**Responsibility:** Entry point for request handling: resolves, binds, authorizes, dispatches.

---

### 📄 `ControllerDispatcher`

**Location:** `Gemini\HTTP\Dispatcher\ControllerDispatcher`  
**Responsibility:** Reflective dispatcher resolving request + DI + route parameters.

```php
public function show(Request $request, string $file)
```

---

### 📄 `MiddlewareManager`

**Location:** `Gemini\HTTP\Middleware`  
**Responsibility:** Executes middleware pipeline in order.

```php
$manager->handle($request, [
    AuthMiddleware::class,
    LogMiddleware::class
], fn($req) => $controllerDispatcher->dispatch(...));
```

---

## 🛠 Builders & Definitions

### 🏗 `RouteBuilder`

**Location:** `Gemini\HTTP\Router\Routing\RouteBuilder`  
Fluent DSL for building routes:

```php
RouteBuilder::make('GET', '/users/{id}')
    ->action([UserController::class, 'show'])
    ->name('users.show')
    ->where('id', '\d+')
    ->authorize('can-view-user')
    ->build();
```

---

### 📦 `RouteDefinition`

**Location:** `Gemini\HTTP\Router\Routing\RouteDefinition`  
Immutable DTO describing a route: method, path, constraints, action, etc.

---

### 🔌 `RouteRegistrarProxy`

**Location:** `Gemini\HTTP\Router\Routing\RouteRegistrarProxy`  
Returned from `Router::get()` etc., proxies DSL chaining to builder.

---

## 🔁 Routing Internals

### 🧠 `HttpRequestRouter`

**Location:** `Routing\HttpRequestRouter`  
Matches route based on method/path, resolves params, throws on 404.

---

### 🧱 `RouteCollection`

**Location:** `Routing\RouteCollection`  
Grouped map of routes indexed by method.

---

### 🧠 `HeadRequestFallback`

**Location:** `Support\HeadRequestFallback`  
Auto-converts `HEAD` to `GET` if no `HEAD` route is defined.

---

## ⚡️ Route Caching

### ⚡ `RouteCacheCompiler`

**Location:** `Cache\RouteCacheCompiler`  
Compiles route definitions into serialized PHP array (no closures).

---

### 🔄 `RouteCacheLoader`

**Location:** `Cache\RouteCacheLoader`  
Reads compiled cache and rehydrates routes into router.

---

### 🚀 `RouteBootstrapper`

**Location:** `Bootstrap\RouteBootstrapper`  
Startup orchestrator: loads from cache or re-runs route definitions.

---

## 🧩 Extras

### 🧩 `HttpMethod` Enum

**Location:** `Gemini\HTTP\Router\HttpMethod`  
Strong enum for all HTTP verbs: `GET`, `POST`, `PUT`, etc.

---

### 🧩 `RouteGroupBuilder`

**Location:** `Routing\RouteGroupBuilder`  
DSL for grouping multiple routes with common prefix/middleware/domain.

```php
RouteGroupBuilder::create()
    ->withPrefix('/admin')
    ->withMiddleware(['auth'])
    ->withRoutes(fn($g) => $g->addRoute(...));
```

---

### 🧩 `RouteGroupRegistrar`

**Responsibility:** Loads `.routes.php` files from disk, optionally supporting closures.

---

## ✅ Validation & Context

### 🛠 `RouteConstraintValidator`

**Location:** `Validation\RouteConstraintValidator`  
Regex-based validator for `where(...)` constraints.

```php
validate(['id' => '\d+'], ['id' => '123']) // true
```

---

### 🧩 `RouteGroupContext`

**Location:** `Routing\RouteGroupContext`  
Holds stack of prefix/middleware/authorization during recursive group resolution.

---

## 🚨 Exceptions

### ❗ `RouteNotFoundException`

Thrown by `HttpRequestRouter` when no route matches.

---

### ❗ `UnauthorizedException`

Thrown by `RouterKernel` when `->authorize(...)` policy fails.

---

### ❗ `InvalidRouteException`

Thrown when a route is malformed (e.g. bad syntax or regex).

---

### ❗ `InvalidRouteGroupFileException`

Thrown when a `.routes.php` file does not return a valid `RouteGroupBuilder`.

---

### 🛡️ Error Example

```php
throw InvalidRouteException::forPath('bad_route');
throw RouteNotFoundException::for('POST', '/missing');
throw UnauthorizedException::because('Policy failed');
```

---

## 🧪 Testing & Security

- Routes are deterministic, testable in isolation
- Controllers are unit-testable (DI ready)
- Constraint validation prevents malformed input
- Middleware stack allows rate-limiting, CSRF, etc.

---

## 🧠 Final Thoughts

The Gemini HTTP Router:

✅ Clean Architecture  
✅ Type-safe  
✅ Constraint-aware  
✅ Middleware-first  
✅ DI/Reflection aware  
✅ Stateless and cacheable  
✅ Zero globals, zero hacks, zero magic

---