# Pass 2: HTTP Response Layer Convergence — Preflight

## Branch / Commit

- Branch: `main`
- Commit: `cd9bd8a38` (Pass 1 committed, clean worktree)

## Worktree Status

Clean. No dirty files. No untracked files relevant to this pass.

## Current Response Classes

| Symbol                      | File                                                                                             | Current Role                                                                              | Correct Role                                           | Classification                                   |
|-----------------------------|--------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------|--------------------------------------------------------|--------------------------------------------------|
| `Response`                  | `components/HTTP/Response/System/PublicSurface/Response.php`                                     | PSR-7 ResponseInterface impl + static factories (json/text/html/redirect)                 | RESPONSE_OBJECT (value object)                         | RESPONSE_OBJECT                                  |
| `Responses`                 | `components/HTTP/Response/System/PublicSurface/Responses.php`                                    | PSR-17 ResponseFactoryInterface impl; delegates to static Build* flows                    | PUBLIC_SURFACE / fluent API                            | PUBLIC_SURFACE (but acts as factory, not facade) |
| `ResponseInterface`         | `components/HTTP/Response/System/PublicSurface/ResponseInterface.php`                            | PSR-7 marker interface                                                                    | INTERFACE                                              | KEEP                                             |
| `ResponseData`              | `components/HTTP/Response/System/Capabilities/ResponseData/ResponseData.php`                     | Immutable value object for response state                                                 | VALUE_OBJECT                                           | KEEP                                             |
| `ContentType`               | `components/HTTP/Response/System/Capabilities/ContentType.php`                                   | Backed enum for content types                                                             | VALUE_OBJECT                                           | KEEP                                             |
| `ResponseFactory`           | `components/HTTP/System/Capabilities/ResponseBuilding/ResponseFactory.php`                       | Rich factory (json/html/redirect/rateLimited/notFound/error/noContent/empty)              | INTERNAL_CAPABILITY (should be CreateHttpResponse)     | INVALID_OWNERSHIP                                |
| `BuildJsonResponse`         | `components/HTTP/Response/System/Flows/BuildResponse/BuildJsonResponse.php`                      | Static flow that creates JSON response                                                    | INTERNAL_CAPABILITY (duplicates ResponseFactory logic) | DUPLICATE_API                                    |
| `BuildTextResponse`         | `components/HTTP/Response/System/Flows/BuildResponse/BuildTextResponse.php`                      | Static flow that creates text response                                                    | INTERNAL_CAPABILITY (duplicates ResponseFactory logic) | DUPLICATE_API                                    |
| `BuildHtmlResponse`         | `components/HTTP/Response/System/Flows/BuildResponse/BuildHtmlResponse.php`                      | Static flow that creates HTML response                                                    | INTERNAL_CAPABILITY (duplicates ResponseFactory logic) | DUPLICATE_API                                    |
| `BuildRedirectResponse`     | `components/HTTP/Response/System/Flows/BuildResponse/BuildRedirectResponse.php`                  | Static flow that creates redirect response                                                | INTERNAL_CAPABILITY (duplicates ResponseFactory logic) | DUPLICATE_API                                    |
| `BuildEmptyResponse`        | `components/HTTP/Response/System/Flows/BuildResponse/BuildEmptyResponse.php`                     | Static flow that creates empty response                                                   | INTERNAL_CAPABILITY (duplicates ResponseFactory logic) | DUPLICATE_API                                    |
| `ResponseFailure`           | `components/HTTP/Response/System/Foundation/Failure/ResponseFailure.php`                         | Exception class                                                                           | FAILURE_TYPE                                           | KEEP                                             |
| `shortcuts.php` (Response)  | `components/HTTP/Response/System/PublicSurface/shortcuts.php`                                    | Global functions: response(), json_response(), abort() — uses `new ResponseFactory()`     | BC_SHIM / GLOBAL_HELPER                                | RUNTIME_USER (violates DI)                       |
| `shortcuts.php` (Router)    | `components/HTTP/Router/System/PublicSurface/shortcuts.php`                                      | Global functions: route(), redirect(), url() — redirect() uses `new ResponseFactory()`    | BC_SHIM / GLOBAL_HELPER                                | RUNTIME_USER (violates DI)                       |
| `NormalizeControllerResult` | `components/HTTP/Router/System/Capabilities/ResponseNormalization/NormalizeControllerResult.php` | Normalizes controller results → Response; uses `Response::json()/text()` static factories | INTERNAL_CAPABILITY                                    | INTERNAL_CAPABILITY                              |
| `BuildErrorResponse`        | `components/HTTP/Router/System/Capabilities/ErrorResponseBuilding/BuildErrorResponse.php`        | Builds 404/405 error responses; uses `Response::json()` static factory                    | INTERNAL_CAPABILITY                                    | INTERNAL_CAPABILITY                              |
| `SendResponse`              | `components/HTTP/System/Flows/SendResponse/SendResponse.php`                                     | Emits response via PHP header()/echo                                                      | FLOW                                                   | KEEP                                             |

## Current Response Public APIs

1. **`Response` (PublicSurface)** — PSR-7 impl + static factories:
    - `Response::text($content, $status)` → new Response
    - `Response::json($data, $status)` → new Response
    - `Response::html($content, $status)` → new Response
    - `Response::redirect($url, $status)` → new Response
    - All PSR-7 `with*` methods

2. **`Responses` (PublicSurface)** — PSR-17 factory + helpers:
    - `response($data, $status)` → dispatch by type
    - `send($data, $status)` → type-based dispatch
    - `createJsonResponse($data, $status)` → delegates to BuildJsonResponse
    - `createTextResponse($content, $status)` → delegates to BuildTextResponse
    - `createHtmlResponse($html, $status)` → delegates to BuildHtmlResponse
    - `createRedirectResponse($url, $status)` → delegates to BuildRedirectResponse
    - `createResponse($code, $reason)` → delegates to BuildEmptyResponse
    - `createErrorResponse($code, $message)` → JSON error
    - `createResponseWithBody($content, $status, $headers)` → new Response

3. **`ResponseFactory` (HTTP/System)** — Rich factory:
    - `create($status, $headers, $body)` → new Response
    - `json($data, $status, $headers)` → new Response
    - `html($html, $status, $headers)` → new Response
    - `redirect($url, $status)` → new Response
    - `rateLimited($retryAfter)` → JSON 429
    - `notFound($message)` → JSON 404
    - `error($message, $status)` → JSON error
    - `createErrorResponse($message, $status)` → alias for error
    - `noContent()` → 204
    - `empty($status)` → empty response

4. **Global shortcuts:**
    - `response($content, $status, $headers)` → `new ResponseFactory()->create()`
    - `json_response($data, $status, $headers)` → `new ResponseFactory()->json()`
    - `abort($code, $message)` → RuntimeException
    - `redirect($url, $status)` → `new ResponseFactory()->redirect()`

## Current Runtime Users of Response Creation

| File                                                                                             | Pattern                                             | Severity                       |
|--------------------------------------------------------------------------------------------------|-----------------------------------------------------|--------------------------------|
| `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php`                               | Injected `ResponseFactory` (DI) — OK                | ACCEPTABLE                     |
| `components/HTTP/Response/System/PublicSurface/shortcuts.php`                                    | `new ResponseFactory()` in global functions         | HIGH — runtime composition     |
| `components/HTTP/Router/System/PublicSurface/shortcuts.php`                                      | `new ResponseFactory()` in redirect()               | HIGH — runtime composition     |
| `components/HTTP/Router/System/Capabilities/ResponseNormalization/NormalizeControllerResult.php` | `Response::json()/text()` static factories          | MEDIUM — static factory bypass |
| `components/HTTP/Router/System/Capabilities/ErrorResponseBuilding/BuildErrorResponse.php`        | `Response::json()` static factory                   | MEDIUM — static factory bypass |
| `components/HTTP/Response/System/PublicSurface/Responses.php`                                    | Delegates to static Build* flows + `new Response()` | MEDIUM — mixed pattern         |
| `components/HTTP/Response/System/Flows/BuildResponse/Build*.php`                                 | Static methods, `new Response()`                    | LOW — flows but static         |

## Direct `new ResponseFactory()` Patterns

```
components/HTTP/Response/System/PublicSurface/shortcuts.php:18 — new ResponseFactory()->create()
components/HTTP/Response/System/PublicSurface/shortcuts.php:28 — new ResponseFactory()->json()
components/HTTP/Router/System/PublicSurface/shortcuts.php:32 — new ResponseFactory()->redirect()
```

## Duplicate APIs

1. **JSON response creation exists in 4 places:**
    - `Response::json()` (static factory on Response object)
    - `Responses::createJsonResponse()` → BuildJsonResponse
    - `ResponseFactory::json()`
    - `BuildJsonResponse::execute()` (static)

2. **HTML response creation exists in 4 places:**
    - `Response::html()` (static factory)
    - `Responses::createHtmlResponse()` → BuildHtmlResponse
    - `ResponseFactory::html()`
    - `BuildHtmlResponse::execute()` (static)

3. **Text response creation exists in 3 places:**
    - `Response::text()` (static factory)
    - `Responses::createTextResponse()` → BuildTextResponse
    - `ResponseFactory` (no direct text method, but create() with body)
    - `BuildTextResponse::execute()` (static)

4. **Redirect response creation exists in 4 places:**
    - `Response::redirect()` (static factory)
    - `Responses::createRedirectResponse()` → BuildRedirectResponse
    - `ResponseFactory::redirect()`
    - `BuildRedirectResponse::execute()` (static)

## Planned Migration

1. Create `CreateHttpResponse` capability in `components/HTTP/Response/System/Capabilities/CreateHttpResponse/`
    - Single class with: `json()`, `html()`, `text()`, `redirect()`, `empty()`, `create()` methods
    - Creates `new Response()` internally (Response is the produced result)
    - No container lookup, no dependencies

2. Reshape `Responses` into a thin PublicSurface facade
    - Receives `CreateHttpResponse` through constructor
    - Delegates all methods to CreateHttpResponse
    - Exposes: `json()`, `html()`, `text()`, `redirect()`, `empty()`, `send()`

3. Remove static factories from `Response` class
    - Remove `Response::json()`, `Response::text()`, `Response::html()`, `Response::redirect()`
    - `Response` becomes pure value object (PSR-7 impl only)
    - Update all callers to use CreateHttpResponse/Responses

4. Remove duplicate Build*Response static flows
    - Remove `BuildJsonResponse`, `BuildTextResponse`, `BuildHtmlResponse`, `BuildRedirectResponse`,
      `BuildEmptyResponse`
    - Their logic moves into CreateHttpResponse

5. Deprecate or remove `ResponseFactory` (HTTP/System)
    - ResponseFactory becomes a BC shim delegating to CreateHttpResponse
    - OR: Remove entirely if no BC needed (tests + framework can be updated)

6. Create `ResponseServiceProvider`
    - Registers CreateHttpResponse as singleton
    - Registers Responses as singleton (wrapping CreateHttpResponse)

7. Update runtime users:
    - HandleIncomingHttp: inject CreateHttpResponse instead of ResponseFactory
    - NormalizeControllerResult: inject CreateHttpResponse instead of using Response::static factories
    - BuildErrorResponse: inject CreateHttpResponse
    - shortcuts.php: remove or rewire to use service container

## BC Risk

| Change                                      | Risk                                                           | Mitigation                                   |
|---------------------------------------------|----------------------------------------------------------------|----------------------------------------------|
| Remove Response static factories            | MEDIUM — used by NormalizeControllerResult, BuildErrorResponse | Update all callers in same pass              |
| Remove Build*Response flows                 | LOW — only used by Responses                                   | Responses delegates to CreateHttpResponse    |
| Rename ResponseFactory → CreateHttpResponse | HIGH — used by HandleIncomingHttp, shortcuts                   | Inject CreateHttpResponse, update references |
| Remove ResponseFactory entirely             | HIGH — external users may depend on it                         | Keep as BC shim if needed                    |
| Change shortcuts.php                        | LOW — global functions, not autoloaded                         | Update to delegate or remove                 |

## Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
```
