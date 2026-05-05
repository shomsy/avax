# AvaX Component Owner Map

Date: 2026-05-05  
Status: FROZEN TARGET / CURRENT COMPONENT TREE RED  
Source: `CURRENT_TRUTH.md`, `Code-Review-And-ToDo/EXECUTION.md`, `.agents/how-to/*.md`

## Stage 01 Decision

This file freezes the V1 component ownership map.

It does not mark the physical component tree green. Stage 01 observed non-canonical roots under `components/`; Stage 02
must repair or classify them before taxonomy integrity can be GREEN.

## Canonical V1 Component Owners

| Suite            | Ownership                                                                                                                                                          | Current Status |
|------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------|
| `Application`    | Framework-adjacent application capabilities: config, container, cache, pipeline, validation, filesystem, facade, feature flags, date/time, localization, and text. | PARTIAL        |
| `CLI`            | Console entrypoints, command runtime support, command rendering, and CLI-specific user interaction.                                                                | PARTIAL        |
| `DataStack`      | Data, database, persistence, query, migration, transaction, and runtime data capabilities.                                                                         | PARTIAL        |
| `DeveloperTools` | Diagnostics, code generation, test tooling, developer experience, and local development assistance.                                                                | PARTIAL        |
| `HTTP`           | Request, response, routing, middleware, session, context, URI, client, API versioning, and HTTP-specific security.                                                 | PARTIAL        |
| `Identity`       | Auth, access, credentials, tokens, external identity, security workflows, and tenancy.                                                                             | PARTIAL        |
| `Operations`     | Events, logging, queues, workflows, observability, delivery, resilience, scheduler, supervision, memory lifecycle, and operational behavior.                       | PARTIAL        |
| `Presentation`   | View and rendering concerns.                                                                                                                                       | PARTIAL        |
| `Security`       | Cryptography, hashing, secrets, redaction, output safety, audit, and security primitives.                                                                          | PARTIAL        |

No V1 component may be marked complete only because these folders exist. Completion requires public contract, internal
behavior, adapters where needed, configuration, health/doctor checks, failure model, observability, tests,
documentation,
and diagnostics.

## Non-Canonical Top-Level Component Roots

| Existing Path                  | Classification                                           | Required Stage 02 Action                                                                                    |
|--------------------------------|----------------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `components/.idea`             | Editor metadata inside production tree                   | Remove from `components/` or quarantine outside production ownership.                                       |
| `components/Data`              | Duplicate or legacy data owner                           | Fold into `components/DataStack/Data` or classify as non-production recovery material.                      |
| `components/DataLayer`         | Forbidden owner; DataLayer is not a V1 suite             | Fold into `components/DataStack/Persistence` or `components/DataStack/System` according to actual behavior. |
| `components/DependencyMap`     | Tooling/developer diagnostics owner                      | Move or classify under `tooling/DependencyMap` or `components/DeveloperTools`.                              |
| `components/Documentation`     | Documentation/code-generation concern in production tree | Move documentation truth under `docs/` or classify executable behavior under `DeveloperTools`.              |
| `components/DumpDebugger`      | Developer diagnostics concern                            | Fold into `components/DeveloperTools/DumpDebugger` or remove duplicate owner.                               |
| `components/GracefulShutdown`  | Runtime lifecycle concern                                | Fold into `framework/System/Capabilities/Runtime/GracefulShutdown` or an approved Operations owner.         |
| `components/Infrastructure`    | Generic forbidden platform bucket                        | Slice into real component owners or labs/recovery; do not keep as production owner.                         |
| `components/Logging`           | Operational logging concern                              | Fold into `components/Operations/Logging`.                                                                  |
| `components/Performance`       | Benchmark/performance concern                            | Move to `benchmarks/` or approved Operations/DeveloperTools owner.                                          |
| `components/Persistence`       | Data persistence concern                                 | Fold into `components/DataStack/Persistence`.                                                               |
| `components/ResourceGovernor`  | Runtime resource governance concern                      | Fold into `framework/System/Capabilities/ResourceGovernance` or `components/Operations/RuntimeSupervision`. |
| `components/Response`          | HTTP response concern                                    | Fold into `components/HTTP/Response`.                                                                       |
| `components/Server`            | Runtime/server adapter concern                           | Fold into `framework/System/Capabilities/Runtime` or `framework/System/Flows` as appropriate.               |
| `components/StatelessBoundary` | Runtime safety concern                                   | Fold into `framework/System/Capabilities/RuntimeSafety` or approved HTTP boundary ownership.                |
| `components/WorkerManager`     | Worker lifecycle concern                                 | Fold into `framework/System/Capabilities/WorkerManagement` or `components/Operations/RuntimeSupervision`.   |

These roots are blockers. They must not be counted as V1 proof until Stage 02 repairs or explicitly classifies them.

## Suite-Level Notes

### Application

Current observed children:

```text
Cache
Config
Container
DateTime
Facade
FeatureFlags
Filesystem
Localization
Pipeline
System
Text
Validation
```

`Application/System` is allowed only if it owns real application-level behavior. It must not become a generic bucket.

### CLI

Current observed children:

```text
Commands
Console
System
UI
```

`CLI/Commands` and `CLI/UI` are taxonomy risks until folded into `CLI/Console` or explicitly justified.

### DataStack

Current observed children:

```text
Data
Database
Persistence
System
```

`DataStack/System/Capabilities/DataLayer` must be checked during Stage 02 so it does not preserve a forbidden owner
under
a different path.

### DeveloperTools

Current observed children:

```text
CodeGeneration
Diagnostics
DumpDebugger
Dx
System
Testing
```

This suite may own diagnostics and local tooling, but it must not hide production runtime behavior.

### HTTP

Current observed children:

```text
AfterResponse
ApiVersioning
Client
ContentNegotiation
Context
Dispatcher
Middleware
Request
Response
Router
Security
Session
System
URI
```

HTTP remains the owner for request/response behavior. The separate `components/Response` root is forbidden.

### Identity

Current observed children:

```text
Access
Auth
Credentials
ExternalIdentity
Security
System
Tenancy
Tokens
```

Identity security workflows must not duplicate cross-cutting cryptography/secrets owned by `components/Security`.

### Operations

Current observed children:

```text
ApplicationWorkflow
Concurrency
Delivery
Events
Filesystem
Logging
Mail
MemoryLifecycle
MessageBus
Monitoring
Notifications
Observability
Queue
Realtime
Resilience
RuntimeSupervision
Scheduler
System
Tasks
```

`Monitoring` must be folded into `Observability` or explicitly classified. Operational components must include failure
models, diagnostics, and tests before completion can be claimed.

### Presentation

Current observed children:

```text
System
View
```

Rendering behavior belongs here. HTTP response formatting belongs in `HTTP/Response`.

### Security

Current observed children:

```text
Hashing
Secrets
System
```

Stage 02 must resolve duplicated shapes such as `Security/Hashing` and `Security/System/Hashing`, and `Security/Secrets`
and `Security/System/Secrets`.

## Stage 02 Blockers

```text
[ ] No extra top-level components outside final suites.
[ ] DataLayer is not a real owner.
[ ] CLI/Commands is not a separate runtime owner unless explicitly approved.
[ ] CLI/UI is folded into CLI/Console or explicitly approved.
[ ] Operations/Monitoring is folded into Operations/Observability or explicitly approved.
[ ] Security/Hashing and Security/System/Hashing ownership is singular.
[ ] Security/Secrets and Security/System/Secrets ownership is singular.
[ ] composer dump-autoload -o has no skipped production classes.
[ ] check-component-suite-structure.php passes.
```

## Verdict

The owner map is frozen.

The physical component tree is RED until Stage 02 repairs or explicitly classifies the non-canonical roots listed above.
