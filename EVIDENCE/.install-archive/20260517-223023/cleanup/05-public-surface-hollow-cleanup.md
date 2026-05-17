# Phase D: PublicSurface Hollow Cleanup — Evidence

Date: 2026-05-14
Phase: D (PublicSurface Cleanup)
Status: GREEN

## D-A: Hollow PublicSurface Classes

| Item                                                          | Status                          |
|---------------------------------------------------------------|---------------------------------|
| D-A.01 SchemaBuilder::hasTable()                              | ✅ DELETED (previously)          |
| D-A.02 Testing::verifyContracts()                             | ✅ FIXED (previously)            |
| D-A.03-05 Marker interfaces (Command, DomainEvent, Query)     | ✅ ACCEPTED (documented markers) |
| D-A.06 ResponseInterface                                      | ✅ ACCEPTED (PSR-7 type alias)   |
| D-A.07 CacheReadTarget                                        | ✅ DELETED (previously)          |
| D-A.08-12 Cache re-exports (CacheFacade, CacheRegistry, etc.) | ✅ DELETED (previously)          |
| D-A.13 Middleware.php                                         | ✅ DELETED (previously)          |

## D-B: PublicSurface Business Logic Extraction

### D-B.01 Router.php — HIGH — FIXED

**Before:** 300 lines with inline business logic (preg_replace_callback, foreach pipeline, type-check response
normalization)
**After:** 140 lines — only route registration + delegation to capabilities

**Extracted capabilities:**

- `Capabilities/UrlBuilding/SubstituteRouteParameters.php` — URL parameter substitution + query string building
- `Capabilities/ErrorResponseBuilding/BuildErrorResponse.php` — 404/405 response creation
- `Capabilities/MiddlewarePipeline/BuildPipeline.php` — middleware pipeline assembly
- `Capabilities/ResponseNormalization/NormalizeControllerResult.php` — controller result → Response conversion

**PublicSurface now only:** `get()`, `post()`, `put()`, `patch()`, `delete()`, `options()`, `head()`, `any()`,
`group()`, `fallback()`, `url()`, `use()`, `resolve()`, `dispatch()` — all delegating to injected capabilities.

### D-B.02 GraphQLSchema.php — MEDIUM — FIXED

**Before:** 186 lines with inline loops/match/array_map
**After:** 140 lines — delegates field assembly and serialization to capabilities

**Extracted capabilities:**

- `Capabilities/SchemaFieldAssembly/AssembleFieldsFromMap.php` — foreach field map → GraphQLField list
- `Capabilities/SchemaSerialization/SchemaToArray.php` — nested array_map serialization

### D-B.03 Workflow.php — MEDIUM — FIXED

**Before:** `new SagaExecutor()` DI bypass + saga state logic in PublicSurface
**After:** SagaExecutor injected via constructor (required parameter), saga store/delegate only

## D-C: NotImplemented/TODO throws

Scan result: CLEAN — no violations in components/*/System/PublicSurface/ or framework/System/PublicSurface/

## D-D: PublicSurface Classification

| File                | Classification    | Rationale                                                   |
|---------------------|-------------------|-------------------------------------------------------------|
| `Router.php`        | PUBLIC_COMPATIBLE | Interface unchanged, behavior now delegates to capabilities |
| `GraphQLSchema.php` | PUBLIC_COMPATIBLE | Public API unchanged, internals extracted                   |
| `Workflow.php`      | PUBLIC_BREAKING   | Constructor requires SagaExecutor now (was optional)        |
| `AvaxCache.php`     | PUBLIC_BREAKING   | Constructor requires explicit capability injection now      |
| `MessageBus.php`    | PUBLIC_BREAKING   | Switched from static-only to DI-managed singleton instance  |

## D-E: Validation

- Router unit tests: 27/27 PASS
- Router integration tests: 8/8 PASS
- Router hardening tests: 9/9 PASS (1235 assertions)
- Saga tests: 2/2 PASS
- GraphQL tests: 6/6 PASS
- PHPStan: 0 errors on changed files (3 pre-existing mixed property fetch in SagaExecutor.php)
- Composer dump-autoload: GREEN (9281 classes)
