# Service Provider Assembly Map

Date: 2026-05-14
Source: Phase 3C — Remaining Components Ledger

## Classification Legend

| Classification | Meaning |
|---|---|
| ACTIVE_GREEN_PROVIDER_REQUIRED | Component is ACTIVE_GREEN and needs a real ServiceProvider |
| ACTIVE_YELLOW_PROVIDER_REQUIRED | Component is ACTIVE_YELLOW and needs a real ServiceProvider |
| ROADMAP_PROVIDER_DEFERRED | Component is ROADMAP — provider deferred |
| SCAFFOLD_PROVIDER_DEFERRED | Component is SCAFFOLD — provider deferred until real behavior exists |
| LABS_ONLY_PROVIDER_NOT_REQUIRED | Component is labs-only — no provider needed |
| EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | Component is evidence-only — no provider needed |
| PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | Component is pure foundation primitives — no provider needed |
| TEST_ONLY_PROVIDER_NOT_REQUIRED | Component is test-only — no provider needed |

---

## Components WITH ServiceProvider (GREEN)

| Component | Provider File | Status |
|---|---|---|
| Application/Cache | CacheServiceProvider.php | Converted to canonical interface |
| Application/Container | ContainerServiceProvider.php | Already conforming |
| Application/Filesystem | FilesystemServiceProvider.php | Already conforming |
| DataStack/Database | DatabaseServiceProvider.php | Already conforming |
| HTTP/Client | HttpClientServiceProvider.php | NEW — thin ServiceProvider |
| HTTP/Middleware | MiddlewareServiceProvider.php | NEW — SCAFFOLD, empty but registered |
| HTTP/Response | ResponseServiceProvider.php | NEW — registers response builders |
| HTTP/Router | HttpRouterServiceProvider.php | Already conforming |
| HTTP/Session | SessionServiceProvider.php | NEW — converted from ComponentProviderInterface |
| HTTP/System | HttpServiceProvider.php | NEW — registers HTTP kernels, middleware |
| Identity/Auth | AuthServiceProvider.php | NEW — registers Identity + Auth |
| Operations/Events | EventsServiceProvider.php | Already conforming |
| Operations/Logging | LoggingServiceProvider.php | Already conforming |
| Security/Cryptography | CryptographyServiceProvider.php | Already conforming |
| Security/Redaction | RedactionServiceProvider.php | Already conforming |
| Framework/FailureBoundary | FailureBoundaryServiceProvider.php | Already conforming |
| Framework/Queue | QueueServiceProvider.php | Already conforming |
| Framework (root) | FrameworkServiceProvider.php | NEW — registers runtime safety, config repo |

---

## Components WITHOUT ServiceProvider — Classified

### API Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| API/ApiBlueprint | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — no runtime assembly needed | NO |
| API/Contracts | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — contract definitions only | NO |
| API/GraphQL | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — schema definitions only | NO |
| API/OpenAPI | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — spec generation only | NO |
| API/SchemaGeneration | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — schema tooling only | NO |

### Application Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Application/Config | ACTIVE_GREEN | ACTIVE_GREEN_PROVIDER_REQUIRED | Configuration component needs assembly entrypoint | NO |
| Application/DateTime | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | Value types and time primitives — no DI needed | NO |
| Application/Facade | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Facade infrastructure — no runtime assembly | NO |
| Application/FeatureFlags | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — feature flag definitions | NO |
| Application/Localization | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — translation data only | NO |
| Application/Pipeline | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | Pipeline primitive — used by others, no DI assembly | NO |
| Application/Storage | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — storage primitives | NO |
| Application/Text | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | Text utilities — no DI needed | NO |
| Application/Validation | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — validation rules only | NO |

### CLI Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| CLI/Console | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — console I/O | NO |
| CLI/System | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | System-level CLI — no runtime assembly | NO |

### DataStack Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| DataStack/Data | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — data primitives | NO |
| DataStack/DataTransfer | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | DTO/value transfer objects — no DI needed | NO |
| DataStack/Persistence | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — persistence abstractions | NO |

### DeveloperTools Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| DeveloperTools/CodeGeneration | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component — no behavior yet | NO |
| DeveloperTools/Diagnostics | ACTIVE_YELLOW | ACTIVE_YELLOW_PROVIDER_REQUIRED | Diagnostics component needs assembly | NO |
| DeveloperTools/Documentation | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component | NO |
| DeveloperTools/DumpDebugger | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Debug tooling — no DI assembly | NO |
| DeveloperTools/Dx | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Developer experience — no DI assembly | NO |
| DeveloperTools/System | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component | NO |
| DeveloperTools/Testing | ROADMAP | ROADMAP_PROVIDER_DEFERRED | ROADMAP — not active | NO |

### Foundation

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Foundation/CallableSerialization | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | Serialization primitive — no DI needed | NO |

### HTTP Area (remaining)

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| HTTP/AfterResponse | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — after-response hook | NO |
| HTTP/ApiVersioning | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — versioning rules | NO |
| HTTP/ContentNegotiation | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — content rules | NO |
| HTTP/Context | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — context primitives | NO |
| HTTP/Dispatcher | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Covered by HTTP/System HttpServiceProvider | NO |
| HTTP/Request | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Request value types — no DI assembly | NO |
| HTTP/SecureRequest | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — request security | NO |
| HTTP/Security | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — HTTP security rules | NO |
| HTTP/URI | ACTIVE_GREEN | PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | URI value types — no DI needed | NO |

### Identity Area (remaining)

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Identity/Access | ACTIVE_YELLOW | ACTIVE_YELLOW_PROVIDER_REQUIRED | Access control needs assembly entrypoint | NO |
| Identity/Credentials | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component | NO |
| Identity/ExternalIdentity | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — external identity adapter | NO |
| Identity/Security | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — identity security rules | NO |
| Identity/System | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | System-level identity — covered by AuthServiceProvider | NO |
| Identity/Tenancy | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — tenancy rules | NO |
| Identity/Tokens | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — token primitives | NO |

### Integration Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Integration/ObjectStorage | ACTIVE_YELLOW | ACTIVE_YELLOW_PROVIDER_REQUIRED | External I/O component needs assembly entrypoint | NO |

### Operations Area (remaining)

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Operations/ApplicationWorkflow | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — workflow definitions | NO |
| Operations/BackgroundProcesses | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is HEALTH_PRESENT — covered by QueueServiceProvider | NO |
| Operations/Concurrency | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — concurrency primitives | NO |
| Operations/Delivery | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is HEALTH_PRESENT — delivery rules | NO |
| Operations/Filesystem | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Covered by Application/Filesystem Service Provider | NO |
| Operations/Mail | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component | NO |
| Operations/MemoryLifecycle | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is HEALTH_PRESENT — memory management | NO |
| Operations/MessageBus | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — message bus rules | NO |
| Operations/Notifications | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — notification rules | NO |
| Operations/Observability | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is HEALTH_PRESENT — observability rules | NO |
| Operations/Parallelism | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — parallelism primitives | NO |
| Operations/Queue | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD — covered by framework QueueServiceProvider | NO |
| Operations/Realtime | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — realtime rules | NO |
| Operations/Resilience | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — resilience policies | NO |
| Operations/RuntimeSupervision | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is HEALTH_PRESENT — supervision rules | NO |
| Operations/Scheduler | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — scheduling rules | NO |
| Operations/System | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | System-level ops — no DI assembly | NO |
| Operations/Tasks | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — task primitives | NO |

### Presentation Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Presentation/System | ROADMAP | ROADMAP_PROVIDER_DEFERRED | ROADMAP component | NO |
| Presentation/View | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — view rendering | NO |

### Security Area (remaining)

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| Security/DataProtection | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — data protection rules | NO |
| Security/Hashing | SCAFFOLD | SCAFFOLD_PROVIDER_DEFERRED | SCAFFOLD component | NO |
| Security/Privacy | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — privacy rules | NO |
| Security/Secrets | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | Health check is SCAFFOLD — secrets management | NO |
| Security/System | ACTIVE_GREEN | SCAFFOLD_PROVIDER_DEFERRED | System-level security — no DI assembly | NO |

### SystemDesign Area

| Component | Status | Classification | Reason | Blocks V5.9? |
|---|---|---|---|---:|
| SystemDesign/System | EVIDENCE_ONLY | EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | Evidence-only component | NO |
| SystemDesign/examples | EVIDENCE_ONLY | EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | Examples only | NO |
| SystemDesign/reference-architectures | EVIDENCE_ONLY | EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | Reference architectures only | NO |
| SystemDesign/schemas | EVIDENCE_ONLY | EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | Schema definitions only | NO |

---

## ACTIVE_YELLOW Components Requiring Providers (Follow-up)

These components are ACTIVE_YELLOW and should get real ServiceProviders in a follow-up pass:

| Component | Owner | Target Stage | Blocks V5.9? |
|---|---|---|---:|
| Application/Config | TBD | V5.10 | NO |
| DeveloperTools/Diagnostics | TBD | V5.10 | NO |
| Identity/Access | TBD | V5.10 | NO |
| Integration/ObjectStorage | TBD | V5.10 | NO |

---

## Summary

| Classification | Count |
|---|---|
| HAS_SERVICE_PROVIDER | 18 |
| ACTIVE_GREEN_PROVIDER_REQUIRED | 3 |
| ACTIVE_YELLOW_PROVIDER_REQUIRED | 4 |
| SCAFFOLD_PROVIDER_DEFERRED | 56 |
| ROADMAP_PROVIDER_DEFERRED | 3 |
| PURE_FOUNDATION_PROVIDER_NOT_REQUIRED | 6 |
| EVIDENCE_ONLY_PROVIDER_NOT_REQUIRED | 4 |
