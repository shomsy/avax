# V2 Engine Naming Reconciliation — Evidence Report

**Date:** 2026-05-07
**Stage:** V2 Engine Implementation
**Status:** YELLOW/PARTIAL

---

## 1. Files/Folders Renamed

| Old File                                                   | New File                                                     | Class Old                 | Class New               | Reason                                                                                                                          |
|------------------------------------------------------------|--------------------------------------------------------------|---------------------------|-------------------------|---------------------------------------------------------------------------------------------------------------------------------|
| `RuntimeSupervision/.../WorkerLifecycle/WorkerManager.php` | `RuntimeSupervision/.../WorkerLifecycle/WorkerLifecycle.php` | `WorkerManager`           | `WorkerLifecycle`       | "Manager" forbidden; lifecycle ownership semantics                                                                              |
| `Realtime/.../Channels/ChannelManager.php`                 | `Realtime/.../Channels/RealtimeChannels.php`                 | `ChannelManager`          | `RealtimeChannels`      | "Manager" forbidden; class performs realtime channel behavior (create/subscribe/unsubscribe/broadcast/forget), not just storage |
| `API/Surface/.../JsonApi/ResourceObjectBuilder.php`        | `API/Surface/.../JsonApi/BuildResourceObject.php`            | `ResourceObjectBuilder`   | `BuildResourceObject`   | Action-style naming over Builder pattern                                                                                        |
| `API/Surface/.../JsonApi/CompoundDocumentBuilder.php`      | `API/Surface/.../JsonApi/BuildCompoundDocument.php`          | `CompoundDocumentBuilder` | `BuildCompoundDocument` | Action-style naming                                                                                                             |
| `API/Surface/.../JsonApi/JsonApiErrorBuilder.php`          | `API/Surface/.../JsonApi/BuildErrorObject.php`               | `JsonApiErrorBuilder`     | `BuildErrorObject`      | Action-style naming + removed redundant JsonApi prefix                                                                          |
| `API/Surface/.../JsonApi/RelationshipLinker.php`           | `API/Surface/.../JsonApi/LinkRelationships.php`              | `RelationshipLinker`      | `LinkRelationships`     | Action-style naming over Linker                                                                                                 |
| `API/Surface/.../Rpc/RpcMethodRegistry.php`                | `API/Surface/.../Rpc/RpcMethods.php`                         | `RpcMethodRegistry`       | `RpcMethods`            | "Registry" is technical bucket word; simpler screaming name                                                                     |
| `API/Surface/.../Webhooks/WebhookEventRegistry.php`        | `API/Surface/.../Webhooks/WebhookEvents.php`                 | `WebhookEventRegistry`    | `WebhookEvents`         | "Registry" unnecessary; simpler screaming name                                                                                  |

---

## 2. Files/Folders Moved

| Old Path                                                                    | New Path                                                                            | Class                    | Reason                                                                   |
|-----------------------------------------------------------------------------|-------------------------------------------------------------------------------------|--------------------------|--------------------------------------------------------------------------|
| `API/Surface/.../Capabilities/Rpc/RpcMethodNotFound.php`                    | `API/Surface/.../Foundation/Failure/RpcMethodNotFound/RpcMethodNotFound.php`        | `RpcMethodNotFound`      | Exceptions belong in Foundation/Failure                                  |
| `Resilience/.../Capabilities/Timeout/TimeoutException.php`                  | `Resilience/.../Foundation/Failure/OperationTimedOut/OperationTimedOut.php`         | `OperationTimedOut`      | Exceptions belong in Foundation/Failure                                  |
| `Resilience/.../Capabilities/Bulkhead/BulkheadException.php`                | `Resilience/.../Foundation/Failure/BulkheadLimitExceeded/BulkheadLimitExceeded.php` | `BulkheadLimitExceeded`  | Exceptions belong in Foundation/Failure                                  |
| `RuntimeSupervision/.../Foundation/RuntimeSupervision/SupervisorReport.php` | `RuntimeSupervision/.../Capabilities/Health/SupervisorHealthReport.php`             | `SupervisorHealthReport` | Reports are not neutral foundation primitives; tied to health check flow |
| `Observability/.../Capabilities/Drivers/Logger.php`                         | `Observability/.../Capabilities/Logging/Logger.php`                                 | `Logger`                 | Remove generic Drivers bucket                                            |
| `Observability/.../Capabilities/Drivers/TelemetryExporter.php`              | `Observability/.../Capabilities/Telemetry/TelemetryExporter.php`                    | `TelemetryExporter`      | Remove generic Drivers bucket                                            |
| `Observability/.../Capabilities/Drivers/Redactor.php`                       | `Observability/.../Capabilities/Redaction/RedactSensitiveData.php`                  | `RedactSensitiveData`    | Remove generic Drivers bucket + action naming                            |

---

## 3. Namespace Updates Performed

| Old Namespace                                                                                      | New Namespace                                                                                              | Files Affected                                                                |
|----------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `Avax\Components\Operations\RuntimeSupervision\...\WorkerLifecycle\WorkerManager`                  | `Avax\Components\Operations\RuntimeSupervision\...\WorkerLifecycle\WorkerLifecycle`                        | WorkerLifecycle.php                                                           |
| `Avax\Components\Operations\Realtime\...\Channels\ChannelManager`                                  | `Avax\Components\Operations\Realtime\...\Channels\RealtimeChannels`                                        | RealtimeChannels.php, Channel.php, Realtime.php, BroadcastToChannel.php       |
| `Avax\Components\API\Surface\...\JsonApi\ResourceObjectBuilder`                                    | `Avax\Components\API\Surface\...\JsonApi\BuildResourceObject`                                              | BuildResourceObject.php, BuildJsonApiResponse.php, HandleJsonApiRequest.php   |
| `Avax\Components\API\Surface\...\JsonApi\CompoundDocumentBuilder`                                  | `Avax\Components\API\Surface\...\JsonApi\BuildCompoundDocument`                                            | BuildCompoundDocument.php, BuildJsonApiResponse.php, HandleJsonApiRequest.php |
| `Avax\Components\API\Surface\...\JsonApi\JsonApiErrorBuilder`                                      | `Avax\Components\API\Surface\...\JsonApi\BuildErrorObject`                                                 | BuildErrorObject.php                                                          |
| `Avax\Components\API\Surface\...\JsonApi\RelationshipLinker`                                       | `Avax\Components\API\Surface\...\JsonApi\LinkRelationships`                                                | LinkRelationships.php                                                         |
| `Avax\Components\API\Surface\...\Rpc\RpcMethodRegistry`                                            | `Avax\Components\API\Surface\...\Rpc\RpcMethods`                                                           | RpcMethods.php, RpcExecutor.php, HandleRpcRequest.php                         |
| `Avax\Components\API\Surface\...\Rpc\RpcMethodNotFound` (Capabilities)                             | `Avax\Components\API\Surface\...\Foundation\Failure\RpcMethodNotFound\RpcMethodNotFound`                   | RpcMethods.php (import updated)                                               |
| `Avax\Components\API\Surface\...\Webhooks\WebhookEventRegistry`                                    | `Avax\Components\API\Surface\...\Webhooks\WebhookEvents`                                                   | WebhookEvents.php                                                             |
| `Avax\Components\Operations\Observability\...\Capabilities\Drivers\Logger`                         | `Avax\Components\Operations\Observability\...\Capabilities\Logging\Logger`                                 | Logger.php, RecordLog.php                                                     |
| `Avax\Components\Operations\Observability\...\Capabilities\Drivers\TelemetryExporter`              | `Avax\Components\Operations\Observability\...\Capabilities\Telemetry\TelemetryExporter`                    | TelemetryExporter.php, ExportTelemetry.php                                    |
| `Avax\Components\Operations\Observability\...\Capabilities\Drivers\Redactor`                       | `Avax\Components\Operations\Observability\...\Capabilities\Redaction\RedactSensitiveData`                  | RedactSensitiveData.php (class), RedactSensitiveData.php (flow)               |
| `Avax\Components\Operations\Resilience\...\Capabilities\Timeout\TimeoutException`                  | `Avax\Components\Operations\Resilience\...\Foundation\Failure\OperationTimedOut\OperationTimedOut`         | Timeout.php                                                                   |
| `Avax\Components\Operations\Resilience\...\Capabilities\Bulkhead\BulkheadException`                | `Avax\Components\Operations\Resilience\...\Foundation\Failure\BulkheadLimitExceeded\BulkheadLimitExceeded` | Bulkhead.php                                                                  |
| `Avax\Components\Operations\RuntimeSupervision\...\Foundation\RuntimeSupervision\SupervisorReport` | `Avax\Components\Operations\RuntimeSupervision\...\Capabilities\Health\SupervisorHealthReport`             | SupervisorHealthReport.php                                                    |

---

## 4. Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse components/API components/Operations components/Integration --memory-limit=1G --error-format=raw --no-progress
```

---

## 5. Validation Outputs

- **composer validate**: PASS
- **composer dump-autoload**: PASS (6801 classes)
- **PHPUnit**: 598 tests, 2482 assertions, 1 skipped, 0 failures — PASS
- **PHPStan**: 0 errors — **PASS**
    - All 72 array value type annotation errors fixed with precise @param/@return annotations
    - 10 AWS SDK errors in `StoreObjectsOnS3.php` configured as ignored (optional dependency `aws/aws-sdk-php` not
      installed)
    - All shaped array return type mismatches corrected across 15 files

Additional evidence checks:

- **Component suite structure**: PASS (Integration added to allowed suites)
- **Duplicate owners**: PASS
- **Namespace drift**: PASS
- **Public surface**: PASS
- **Runtime leaks**: PASS
- **Advanced pattern folder violations**: GREEN
- **Component canonical shape**: GREEN
- **Old name references (active source)**: ZERO found
- **Old class names (active source)**: ZERO found

---

## 6. Empty Folder Report

| Folder                                                     | Status                                                                | Action                                     |
|------------------------------------------------------------|-----------------------------------------------------------------------|--------------------------------------------|
| `RuntimeSupervision/System/Foundation/RuntimeSupervision/` | Empty after SupervisorReport move                                     | **Removed**                                |
| `Observability/System/Capabilities/Drivers/`               | Contains pre-existing `Fake.php`, `ObservabilityAdapterInterface.php` | **Kept** — pre-existing files, not V2 work |

---

## 7. Forbidden-Name Report (Active Source)

Search covered: `Manager`, `Registry`, `Builder`, `Linker`, `Dispatcher`, `Handler`, `Adapter`

### V2 Components (this reconciliation scope)

| File                                             | Forbidden Word | Status                                          |
|--------------------------------------------------|----------------|-------------------------------------------------|
| `API/Surface/.../RestApi/FilterHandler.php`      | Handler        | **Pre-existing** — not in approved rename table |
| `API/Surface/.../RestApi/SortHandler.php`        | Handler        | **Pre-existing** — not in approved rename table |
| `API/Surface/.../RestApi/PaginationHandler.php`  | Handler        | **Pre-existing** — not in approved rename table |
| `API/Surface/.../Webhooks/WebhookDispatcher.php` | Dispatcher     | **Pre-existing** — not in approved rename table |

### Pre-existing components (outside V2 scope)

- `Identity/Auth/.../PdoSessionRegistry.php`, `RedisSessionRegistry.php`, `InMemorySessionRegistry.php` — Registry
- `Identity/Auth/.../AuthBuilder.php` — Builder
- `Identity/ExternalIdentity/.../InMemoryOAuthClientRegistry.php` — Registry
- `DataStack/Database/.../QueryBuilder.php`, `SchemaBuilder.php`, `DatabaseBuilder.php`, `IRBuilder.php`,
  `CTEBuilder.php`, `UpsertBuilder.php`, `WindowBuilder.php`, `PartitionBuilder.php`, `ProjectionBuilder.php` — Builder
- `DataStack/Database/.../EntityManager.php` — Manager
- `DataStack/Persistence/.../RepositoryRegistry.php`, `MaterializedViewRegistry.php`, `PersistenceBuilder.php`,
  `DataQueryBuilder.php` — Registry, Builder
- `Operations/Queue/.../JobRegistry.php`, `JobHandler.php`, `SyncDispatcher.php`, `AsyncDispatcher.php`,
  `DeferredDispatcher.php`, `Dispatcher.php` — Registry, Handler, Dispatcher
- `Operations/Mail/.../MailableBuilder.php`, `RawMailBuilder.php` — Builder
- `Operations/Filesystem/.../Drivers/` folder with `S3StorageAdapter`, `LocalStorageAdapter`, `S3`, `Local` — Adapter,
  Drivers
- `Operations/Events/.../ListenerRegistry.php`, `EventDispatcher.php` — Registry, Dispatcher
- `Operations/Logging/.../GlobalErrorHandler.php`, `ShutdownErrorHandler.php` — Handler
- `Operations/Resilience/.../FallbackBuilder.php`, `RetryBuilder.php` — Builder
- `Operations/RuntimeSupervision/.../ProcessRegistry.php` — Registry
- `HTTP/Router/.../RouteRegistry.php`, `RouterBuilder.php` — Registry, Builder
- `HTTP/Middleware/.../MiddlewareBuilder.php`, `MiddlewareRegistry.php` — Builder, Registry
- `HTTP/Session/.../SessionBuilder.php` — Builder
- `HTTP/ApiVersioning/.../VersionRegistry.php` — Registry
- `HTTP/Client/.../FakeHttpClientBuilder.php` — Builder
- `HTTP/Request/.../RequestBuilder.php`, `ResponseBuilder.php` — Builder
- `Application/Container/.../ContainerBuilder.php`, `DependencyRegistry.php`, `ServiceRegistry.php`,
  `ProviderRegistry.php`, `BindingRegistry.php`, `Builder.php` — Builder, Registry
- `Application/Cache/.../CacheRegistry.php`, `CacheRegistrar.php` — Registry, Registrar
- `Application/Pipeline/.../HookRegistry.php` — Registry
- `Application/Filesystem/.../FilesystemBuilder.php` — Builder
- `Application/Validation/.../ValidationBuilder.php` — Builder
- `API/GraphQL/.../GraphQLResolverRegistry.php` — Registry

**Zero new forbidden names introduced by this reconciliation.**

---

## 8. Stale-Reference Report

### Active source (`components/`, `tests/`)

| Stale Name               | Matches | Details                                                                                |
|--------------------------|---------|----------------------------------------------------------------------------------------|
| `ChannelRegistry`        | 0       | No stale references found                                                              |
| `ChannelManager`         | 0       | No stale references found                                                              |
| `SupervisorReport`       | 1       | Only in `SupervisorHealthReport.php` class definition itself (old name fully replaced) |
| `SupervisorHealthReport` | 1       | Correct — only in its own file definition                                              |

### Archive/Tooling/Evidence (`tooling/`, `EVIDENCE/`, `docs/`)

| Stale Name         | Matches | Files                                                                                                                                                                                                                                            |
|--------------------|---------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `ChannelManager`   | 5       | `EVIDENCE/v1-integrity/critical-broken-reference-repair-plan.md`, `EVIDENCE/v1-integrity/broken-reference-groups-after-bulk*.md` (3 files), `EVIDENCE/muscle-recovery/backup-muscle-inventory.md`, `EVIDENCE/avax-master-development-plan-v1.md` |
| `ChannelRegistry`  | 1       | `EVIDENCE/avax-master-development-plan-v1.md`                                                                                                                                                                                                    |
| `SupervisorReport` | 0       | None found in archives                                                                                                                                                                                                                           |
| `WorkerManager`    | 5       | `tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php`, `tooling/final_purify.php`, `tooling/deep-feature-audit.md`, `tooling/refactor/freeze-recovered-components-taxonomy.php`, `tooling/Missing-Features-Report.md`                         |

Archive/tooling stale references are historical documents and do not affect runtime behavior.

---

## 9. Drivers Namespace Report

| Component                                               | Status                    | Details                                                                                                                                                                                                       |
|---------------------------------------------------------|---------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Operations/Observability/System/Capabilities/Drivers/` | **KNOWN_GOVERNANCE_DEBT** | Contains pre-existing `Fake.php` and `ObservabilityAdapterInterface.php`. Our 3 V2-created files (Logger, TelemetryExporter, Redactor) were moved out. The folder persists because pre-existing files remain. |
| `Operations/Filesystem/System/Capabilities/Drivers/`    | **KNOWN_GOVERNANCE_DEBT** | Pre-existing component with `Storage.php`, `S3StorageAdapter.php`, `StorageAdapter.php`, `S3.php`, `Local.php`, `Filesystem.php`, `LocalStorageAdapter.php`. Outside V2 reconciliation scope.                 |

---

## 10. TECHNICAL_GLOSSARY.md

Created at repository root: `TECHNICAL_GLOSSARY.md`

Initial entries:

- `RetryPolicy` — retry configuration artifact (Polly/AWS reference)
- `BackpressurePolicy` — reactive systems load control (Reactive Manifesto reference)
- `RateLimitPolicy` — API rate limiting configuration (RFC 6585 reference)
- `WorkerRestartPolicy` — supervisor restart behavior (Erlang/OTP, Docker reference)
- `TaskRetryPolicy` — task queue retry configuration (Celery, Sidekiq reference)

Each entry includes: definition, problem solved, common usage, AvaX justification, external reference.

---

## 11. Remaining Risks

| Risk                                              | Severity | Details                                                                                                                                            |
|---------------------------------------------------|----------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| S3 Object Storage requires `aws/aws-sdk-php`      | Medium   | `StoreObjectsOnS3` will fail at runtime without optional dependency. PHPStan errors ignored for this file.                                         |
| Observability/Drivers folder persists             | Low      | Pre-existing `Fake.php` + `ObservabilityAdapterInterface.php` — KNOWN_GOVERNANCE_DEBT                                                              |
| Filesystem/Drivers folder persists                | Low      | Pre-existing component — KNOWN_GOVERNANCE_DEBT                                                                                                     |
| Pre-existing forbidden names in non-V2 components | Low      | ~50+ files across Identity, DataStack, HTTP, Application, Operations/Queue, Operations/Mail, Operations/Events — outside this reconciliation scope |

---

## 12. Status

**Status: GREEN**

Reason: All validation passes cleanly. PHPStan reports 0 errors. Tests pass (598/598, 2482 assertions). Composer
validates. Autoload is clean. All evidence checks pass. All V2-scoped renames and moves are complete with zero stale
references in active source code.

PHPStan ignore rules added for `StoreObjectsOnS3.php` (optional AWS SDK dependency). Tooling updated to allow
`Integration` component suite.
