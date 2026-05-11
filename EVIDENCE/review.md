# Deep Code Review: Critical Components

## Review Summary

This review examines 10 critical component areas in `/home/shomsy/projects/avax/components/` against AvaX governance
rules including canonical component shape, forbidden folders, dogfooding, raw file operations, callable serialization
safety, constructor bloat, failure models, observability, and modern PHP patterns.

---

## 1. Application/Container/System — DI Container

### Canonical Shape: PASS

Structure follows `System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}` correctly.

### Forbidden Folders: PASS

No Services, Helpers, Utils, Adapters, Contracts, etc. inside `System/`.

### PublicSurface Thinness: PASS

- `System/Container.php` (line 22-136): Thin static facade delegating to `ContainerInterface`.
- `System/PublicSurface/ContainerInterface.php` (line 24): Clean interface extending PSR-11.
- `System/Foundation/DIContainer.php` (line 41-538): `final readonly` class with single constructor dep (
  `ResolveDependency`), delegates all behavior to flows/capabilities.

### Constructor Bloat: PASS

- `DIContainer` has 1 dep: `ResolveDependency` (line 43).
- `ContextContainer` has 2 deps: `FoundationContainer`, `ResolveDependency`, `array $context` (line 42).

### Issues Found:

**MEDIUM — `Foundation/Container.php` is an empty shell** (line 7-9):

```php
namespace Avax\Components\Application\Container\System\Foundation;
class Container {}
```

This is a dead class with no behavior. `System/Container.php` is the actual facade. The Foundation `Container` class
appears to be an incomplete placeholder.

**MEDIUM — `Foundation/ContextContainer.php` is also empty** (line 7-9):
Same issue — empty class in Foundation while `System/ContextContainer.php` holds the real implementation.

**LOW — `ContainerBuilder` returns wrong type** (Configuration/ContainerBuilder.php:20):

```php
public function build(): Container
```

Returns `System/Container` (the static facade), not the real `DIContainer`. This is architecturally inconsistent — the
builder should build the engine, not the facade.

**LOW — ContainerInterface has 50+ methods** (line 24-332):
While well-organized, the interface is very large. Many `debug*` methods could potentially be extracted into a
`ContainerDiagnosticsInterface` for better SRP, though this is debatable for a DI container.

**LOW — `CreateContainerConfig` uses mutable properties** (line 58-77):
Class is `readonly` but properties like `$sliceBoundaryMode`, `$policyProfiles`, etc. are declared as public mutable
properties on lines 58-77, then assigned in the constructor body (lines 117-129). This is contradictory — either the
class should use constructor promotion with readonly properties, or not be `readonly`.

---

## 2. Application/Config/System — Config Repository

### Canonical Shape: PASS

Follows `System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Forbidden Folders: PASS

### Issues Found:

**LOW — `ConfigurationRepository` uses plain `array` state** (Capabilities/Repository/ConfigurationRepository.php:12):

```php
private array $items = [];
```

No immutability, no typed value objects. For a config repository, this is acceptable but not ideal.

**LOW — `Config` PublicSurface throws RuntimeException for missing keys** (PublicSurface/Config.php:30-31):

```php
throw new RuntimeException(sprintf('Configuration key [%s] does not exist.', $key));
```

Should use a domain-specific exception from `Foundation/Failure/ConfigFailure.php` instead of generic
`RuntimeException`.

---

## 3. Application/Cache/System — Cache

### Canonical Shape: PASS

Well-structured with `System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Dogfooding: PASS

`FileCacheStore` (Capabilities/Storage/StoreCachedValues/FileCacheStore.php:14,30,39) correctly uses
`Application/Filesystem/System/PublicSurface/Filesystem`:

```php
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
private Filesystem $filesystem;
```

### Issues Found:

**MEDIUM — `loadInBackground` swallows all exceptions** (AvaxCache.php:225-235):

```php
private function loadInBackground(string $key, int|DateInterval|null $ttl, callable $loader): void
{
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    try {
        $this->set(key: $key, value: $loader(), ttl: $ttl);
    } catch (Throwable) {
    }
}
```

Empty catch block swallows all errors. At minimum, this should log the failure. This is a silent failure in a background
operation.

**MEDIUM — `MemcachedCacheStore` in `Capabilities/Stores/` uses direct Memcached** (
Capabilities/Stores/MemcachedCacheStore.php:11-83):
This class is in `Capabilities/Stores/` (not `Capabilities/Storage/StoreCachedValues/`) and implements a different
interface (`CacheStoreInterface` from Foundation). It duplicates the storage pattern used by the canonical
`RedisCacheStore` in `Capabilities/Storage/StoreCachedValues/`. Inconsistent placement.

**LOW — `AvaxCache` constructor has 10 parameters** (AvaxCache.php:42-53):
This exceeds the 4-dep guideline, though most are optional with defaults. Consider using a configuration value object.

---

## 4. Application/Filesystem/System — Filesystem

### Canonical Shape: PASS

`System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Raw File Operations: EXPECTED (this IS the filesystem owner)

The Filesystem component is the canonical owner of raw file operations. Found in:

- `Flows/ReadFile/ReadFile.php:16,24`: `file_exists()`, `file_get_contents()`
- `Flows/WriteFile/WriteFile.php:16,17,22`: `is_dir()`, `mkdir()`, `file_put_contents()`

This is correct — these are the filesystem's own raw operations.

### Issues Found:

**MEDIUM — `ReadFile` does not sanitize path traversal** (Flows/ReadFile/ReadFile.php:33-36):

```php
private function sanitizePath(string $path) : string
{
    return str_replace(["\0", "\n", "\r"], '', $path);
}
```

Only strips null/newline chars. Does NOT resolve `..` or check against a root directory. Path traversal protection
exists in `Capabilities/LocalPaths/RejectPathTraversal.php` but is NOT used by the flows.

**MEDIUM — `WriteFile` does not sanitize path traversal** (Flows/WriteFile/WriteFile.php:31-34):
Same issue — only strips null/newlines, no traversal protection.

**LOW — `Filesystem` PublicSurface is not `final readonly`** (PublicSurface/Filesystem.php:31):
Should be `final readonly class` per AvaX modern PHP preferences.

---

## 5. Application/Storage/System — Storage

### Canonical Shape: PASS

### Dogfooding: PASS

`LocalDisk` (Capabilities/Disks/LocalDisk/LocalDisk.php:7,17) correctly uses
`Application/Filesystem/System/PublicSurface/Filesystem`:

```php
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
public function __construct(private Filesystem $filesystem, ...)
```

All storage operations delegate to Filesystem — correct ownership.

### Issues Found:

**LOW — `MemoryDisk` does not exist as a proper disk implementation check needed**:
Directory exists at `Capabilities/Disks/MemoryDisk/` but needs verification it implements `Disk` interface.

---

## 6. DataStack/Database/System — Database

### Size: 297 PHP files — this is the largest component

### Dogfooding: PASS (partial)

Database migrations correctly use Filesystem component:

- `Capabilities/Migrations/LoadMigrations/MigrationLoader.php:7`: uses `Filesystem`
- `Capabilities/Migrations/MigrationEngine.php:7`: uses `Filesystem`
- `Capabilities/Migrations/CLI/MigrateCommand.php:7`: uses `Filesystem`

No raw `file_get_contents`/`file_put_contents` found in Database (good).

### Forbidden Folders: FAIL — 4 `Contracts` directories found:

1. `Capabilities/Connections/Contracts/` — contains `DatabaseConnection.php` (interface)
2. `Capabilities/Connections/Pools/Contracts/` — contains `ConnectionPoolInterface.php`
3. `Capabilities/Telemetry/Events/Contracts/` — contains `EventBusInterface.php`, `DispatchStrategyInterface.php`
4. `Capabilities/Transactions/Contracts/` — contains `TransactionsInterface.php`

Per AGENTS.md Section 8, `Contracts` is a forbidden folder name. These interfaces should be renamed and moved into
capability folders (e.g., `ConnectionContracts/` → `OpenConnection/` as `DatabaseConnectionInterface.php`).

### Issues Found:

**HIGH — 4 `Contracts` folders violate forbidden-folder law** (see above).

**MEDIUM — No dedicated health/doctor check for Database**:
Only `PdoConnection.php` and `DatabaseConnectionPool.php` have mentions of "health". There is no explicit
`CheckDatabaseHealth` or `DiagnoseDatabaseConnection` flow for the runtime doctor. The `ping()` method on
`DatabaseConnection` interface exists but is not exposed as a doctor capability.

**LOW — Database PublicSurface constructor has 7 deps** (PublicSurface/Database.php:22-30):

```php
public function __construct(
    private Connections $connections,
    private QueryCapability $queryCapability,
    private EntitiesCapability $entitiesCapability,
    private SchemaCapability $schemaCapability,
    private MigrationsCapability $migrationsCapability,
    private TransactionsCapability $transactionsCapability,
    private TelemetryCapability $telemetryCapability,
)
```

This is a facade aggregator — acceptable for a component gateway, but worth noting.

---

## 7. DataStack/DataTransfer/System — DTO

### Canonical Shape: PASS

`System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Modern PHP: FAIL — uses `?Type` instead of `Type|null`

Found 15+ occurrences:

- `ValueConversionContext.php:14`: `public ?string $propertyName = null`
- `AttributeCompiler.php:28,29`: `?string $cacheDir`, `?string $configHash`
- `DataFieldType.php:81`: `?string` return type
- `DataField.php:58,96`: `?string` return types
- `DataShapeCompiler.php:34,180`: `?string`
- `DataTransferViolation.php:18`: `?string $code`
- `SerializeLegacyDTO.php:22,51`: `?int`
- `ConvertDataObjectToArray.php:18`: `?int`
- `SerializeDataObject.php:18,30`: `?int`
- `ConvertDataObjectToJson.php:17`: `?int`
- `DataTransferConfig.php:23`: `?string`
- `DataTransfer.php:136`: `?string`

### Hot Path / Compiled Metadata:

`DataShapeInspection/CacheDataShape.php` and `CompileDataShapeSchema.php` exist for compiled metadata — good.

### Issues Found:

**MEDIUM — Widespread `?Type` instead of `Type|null`** (see above, 15+ occurrences).

**LOW — `AbstractDTO` in `Capabilities/LegacyTransfer/`** (line 11):
Legacy transfer exists but should be clearly deprecated or documented as transitional.

---

## 8. DataStack/Data/System — Data Utilities

### Canonical Shape: PASS

### Issues Found:

**LOW — `serialize()` used in probabilistic data structures**:

- `HyperLogLog.php:87`: `serialize(value: $value)` for hashing — acceptable since it's for CRC32 hash input, not
  transport.
- `CountMinSketch.php:94`: Same pattern — acceptable.

These are internal hashing uses, not cross-process serialization, so they are fine.

---

## 9. HTTP/ — All Sub-components

### HTTP/Router/System

**Canonical Shape: PASS**
**Forbidden Folders: PASS**
**PublicSurface: PASS** — `Router.php` is thin facade.

### HTTP/Middleware/System

**Canonical Shape: PASS**
**Forbidden Folders: PASS**

### HTTP/Request/System

**Canonical Shape: PASS**

**Issues Found:**

**MEDIUM — PSR-7 `with*` methods are partially no-ops** (Request.php:103-106, 113-116, 123-126, 190-193, 195-198):

```php
public function withoutHeader($name) : self { return $this; }
public function withBody(StreamInterface $body) : self { return $this; }
public function withRequestTarget($target) : self { return $this; }
public function withUploadedFiles(array $uploadedFiles) : self { return $this; }
public function withParsedBody($data) : self { return $this; }
```

These methods return `$this` without cloning or modifying state. This violates PSR-7 immutability contract — callers
expect a new instance with the modified state.

### HTTP/Response/System

**Canonical Shape: PASS**

**Issues Found:**

**LOW — Constructor is not using constructor promotion** (Response.php:16):

```php
public function __construct(int $statusCode = 200, array $headers = [], ?StreamInterface $stream = null, string $reasonPhrase = '', string $protocolVersion = '1.1')
```

Should use promoted properties per AvaX modern PHP preferences.

### HTTP/Session/System

**Canonical Shape: PASS**

**Dogfooding: PASS**
`FileSessionStore` (Capabilities/Storage/FileSessionStore.php:7,11) uses
`Application/Filesystem/System/PublicSurface/Filesystem`.

**Issues Found:**

**LOW — `gc()` method uses raw `filemtime()`** (FileSessionStore.php:99):

```php
$mtime = filemtime($file);
```

This is a read-only stat operation, acceptable, but could potentially use Filesystem capability if one existed for file
metadata.

### HTTP/System (root HTTP component)

**Canonical Shape: PASS**
Contains kernel, body, headers, URI capabilities.

### HTTP/Client/System

**Canonical Shape: PASS**
Has `Capabilities/Resilience/` which may contain retry logic — should check if it uses Reliability component.

---

## 10. Operations/Concurrency/System — Fibers

### Canonical Shape: PASS

`System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Fiber Safety: PASS

`FiberTaskRuntime.php` (line 15-200):

- Creates fibers with proper error capture (line 127-133)
- Handles fiber lifecycle: start, suspend, resume, terminate
- Chunks tasks by concurrency limit
- Results and errors tracked in separate arrays

**Issues Found:**

**MEDIUM — Fiber tasks catch Throwable but don't propagate cancellation** (FiberTaskRuntime.php:127-133):

```php
return new Fiber(function () use ($action, $name, &$results, &$errors) : void {
    try {
        $results[$name] = $action();
    } catch (Throwable $e) {
        $errors[$name] = $e;
    }
});
```

No `CancellationToken` integration visible in the fiber runtime. `CancellationToken.php` exists but is not wired into
`FiberTaskRuntime`.

**LOW — `Concurrency::all()` is identical to `Concurrency::run()`** (PublicSurface/Concurrency.php:32-35):

```php
public static function all(array $tasks) : ConcurrentResult
{
    return self::run(tasks: $tasks);
}
```

Redundant alias — fine for API ergonomics but should be documented.

**LOW — Race tasks don't cancel losing fibers** (FiberTaskRuntime.php:89-112):
`runFibersUntilFirstResult()` stops when first result arrives but doesn't explicitly terminate remaining fibers. PHP
will clean them up on GC, but explicit cancellation would be cleaner.

---

## 11. Presentation/View/System — View Engine

### Canonical Shape: PASS

`System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}`.

### Dogfooding: PARTIAL

`BladeTemplateEngine.php` (line 7-8) uses BOTH filesystem components:

```php
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem as ApplicationFilesystem;
use Avax\Components\Operations\Filesystem\System\PublicSurface\Filesystem;
```

Then in `clearCompiledViews()` (line 45-49):

```php
$filesystem = new ApplicationFilesystem();
foreach ($filesystem->listFilesByPattern(...) as $compiledView) {
    Filesystem::delete(path: $compiledView);  // Uses Operations/Filesystem static
}
```

**Issues Found:**

**MEDIUM — View uses two different Filesystem components** (BladeTemplateEngine.php:7-8, 45-49):
Instantiates `ApplicationFilesystem` for listing, then uses `Operations/Filesystem` static for deletion. Should use one
consistently. The existence of two Filesystem components (Application and Operations) is itself an architectural
concern.

**MEDIUM — `BladeTemplateEngine` extends external `BladeOne`** (line 12):

```php
class BladeTemplateEngine extends BladeOne implements TemplateEngineInterface
```

Inheritance from external library — changes in BladeOne could break this. Consider composition over inheritance.

**MEDIUM — Blade directives call global functions** (lines 22-34):
Directives reference `csrf_token()`, `auth()`, `policy()`, `view()` — these are global function calls. If these
functions aren't registered, templates will fail at runtime. No guard/fallback is present.

**LOW — `clearCompiledViews()` creates new Filesystem instance** (line 45):

```php
$filesystem = new ApplicationFilesystem();
```

Should be dependency-injected.

---

## Cross-Cutting Findings

### Forbidden Folders (AGENTS.md Section 8)

| Location                                                              | Forbidden Name | Files                                                |
|-----------------------------------------------------------------------|----------------|------------------------------------------------------|
| `components/API/Contracts`                                            | Contracts      | entire directory                                     |
| `DataStack/Database/System/Capabilities/Connections/Contracts/`       | Contracts      | DatabaseConnection.php                               |
| `DataStack/Database/System/Capabilities/Connections/Pools/Contracts/` | Contracts      | ConnectionPoolInterface.php                          |
| `DataStack/Database/System/Capabilities/Telemetry/Events/Contracts/`  | Contracts      | EventBusInterface.php, DispatchStrategyInterface.php |
| `DataStack/Database/System/Capabilities/Transactions/Contracts/`      | Contracts      | TransactionsInterface.php                            |
| `Operations/Filesystem/System/Capabilities/Adapters/`                 | Adapters       | LocalStorageAdapter.php                              |
| `Security/Cryptography/System/PublicSurface/Contracts`                | Contracts      | directory exists                                     |

**7 forbidden folder violations found.**

### Dogfooding Assessment

| Component                  | Uses Owner? | Notes                                                           |
|----------------------------|-------------|-----------------------------------------------------------------|
| Cache (FileCacheStore)     | Filesystem  | PASS — uses Application/Filesystem                              |
| Storage (LocalDisk)        | Filesystem  | PASS — uses Application/Filesystem                              |
| Session (FileSessionStore) | Filesystem  | PASS — uses Application/Filesystem                              |
| Database (Migrations)      | Filesystem  | PASS — uses Application/Filesystem                              |
| Database (core)            | Cache       | NOT VERIFIED — no explicit cache usage found in query execution |
| Database (core)            | Reliability | NOT VERIFIED — no retry logic from Reliability component found  |

### Raw File Operations Outside Filesystem

| Location                           | Operation                    | Severity                                          |
|------------------------------------|------------------------------|---------------------------------------------------|
| `FileSessionStore.php:99`          | `filemtime()`                | LOW — read-only stat                              |
| `Cache/FileCacheStore.php:163-164` | `RecursiveDirectoryIterator` | MEDIUM — bypasses Filesystem for recursive delete |

### Callable Serialize/Unserialize

| Location                                    | Pattern                                                 | Severity                                   |
|---------------------------------------------|---------------------------------------------------------|--------------------------------------------|
| `Security/Cryptography/DecryptValue.php:58` | `unserialize($plaintext, ['allowed_classes' => false])` | LOW — allowed_classes=false, fallback path |
| `Foundation/CallableSerialization/`         | Uses `laravel/serializable-closure`                     | PASS — dedicated owner component           |

### Constructor Bloat (>4 deps)

| Component                         | Deps Count           | Notes                                 |
|-----------------------------------|----------------------|---------------------------------------|
| `Container/CreateContainerConfig` | 13 params            | Configuration object — acceptable     |
| `Container/ContextContainer`      | 3                    | PASS                                  |
| `Container/DIContainer`           | 1                    | PASS                                  |
| `Cache/AvaxCache`                 | 10 (mostly optional) | MEDIUM — consider config value object |
| `Database/Database`               | 7 (facade)           | LOW — facade aggregator               |
| `View/View`                       | 2                    | PASS                                  |

### Missing Health/Doctor Checks

| Component  | Has Doctor?                             | Notes                                  |
|------------|-----------------------------------------|----------------------------------------|
| Cache      | Partial — `Health` capability exists    |                                        |
| Database   | Missing — no explicit health check flow | Should have `CheckDatabaseHealth` flow |
| Filesystem | Missing — no filesystem health check    |                                        |
| Session    | Missing                                 |                                        |
| View       | Missing                                 |                                        |

### Missing Observability Events

| Component   | Has Telemetry?                                    | Notes                                       |
|-------------|---------------------------------------------------|---------------------------------------------|
| Cache       | PASS — `CacheMetrics` capability                  |                                             |
| Database    | PASS — `Telemetry` capability with OTel           |                                             |
| Container   | PASS — `ResolutionMetrics`, `ResolutionTelemetry` |                                             |
| Filesystem  | Missing — no operation metrics                    | Should emit events for I/O operations       |
| Session     | PASS — `SessionEventBus` capability               |                                             |
| Concurrency | Missing — no task execution metrics               | Should emit task start/complete/fail events |
| View        | Missing                                           |                                             |

### Modern PHP Compliance

| Pattern                           | Status              | Count                       |
|-----------------------------------|---------------------|-----------------------------|
| `?Type` instead of `Type\|null`   | FAIL                | 15+ in DataTransfer         |
| `final readonly` on value objects | Mixed               | Some classes missing        |
| Constructor promotion             | Mixed               | `Response.php` not using it |
| `@throws` tags                    | Partial             | Some flows missing          |
| `static` anonymous functions      | Not checked broadly |                             |

---

## Priority Recommendations

### HIGH

1. **Rename `Contracts/` folders** in Database and other locations to capability-specific names (e.g.,
   `ConnectionContracts` → `OpenConnection/ConnectionContract.php`).
2. **Rename `Adapters/` folder** in Operations/Filesystem to a flow/capability name.
3. **Fix PSR-7 `with*` no-ops** in HTTP/Request — they violate the immutability contract.

### MEDIUM

4. **Add path traversal protection** to Filesystem ReadFile/WriteFile flows (use existing `RejectPathTraversal`
   capability).
5. **Add logging to Cache `loadInBackground`** — don't silently swallow exceptions.
6. **Wire CancellationToken into FiberTaskRuntime** for proper fiber cancellation.
7. **Unify Filesystem usage** in BladeTemplateEngine — use one component consistently.
8. **Add Database health/doctor check** flow for connection pool monitoring.
9. **Inject Filesystem into BladeTemplateEngine** instead of `new` instantiation.
10. **Remove recursive directory iterator** from FileCacheStore — use Filesystem capability.

### LOW

11. **Replace `?Type` with `Type|null`** across DataTransfer (15+ occurrences).
12. **Use constructor promotion** in Response and similar classes.
13. **Add observability events** to Filesystem and Concurrency components.
14. **Clean up empty Foundation shell classes** in Container (`Container.php`, `ContextContainer.php`).
15. **Make Filesystem PublicSurface `final readonly`**.
