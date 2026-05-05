# AvaX Component Owner Map

Date: 2026-05-05  
Status: FROZEN TARGET / STAGE 02 TAXONOMY GREEN / REPOSITORY RED  
Source: `CURRENT_TRUTH.md`, `Code-Review-And-ToDo/EXECUTION.md`, `.agents/how-to/*.md`

## Stage 01 Decision

This file freezes the V1 component ownership map.

Stage 02 has now removed the observed non-canonical roots from production `components/` ownership and archived them as
non-production recovery material. This does not mark any component complete and does not prove V1 Kernel Green.

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

## Archived Non-Canonical Top-Level Component Roots

| Old Path                       | Classification                                           | Stage 02 Result                                                                                              |
|--------------------------------|----------------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `components/.idea`             | Editor metadata inside production tree                   | Archived under `Code-Review-And-ToDo/archive/noncanonical-components/stage-02/`.                            |
| `components/Data`              | Duplicate or legacy data owner                           | Archived as non-production recovery material.                                                               |
| `components/DataLayer`         | Forbidden owner; DataLayer is not a V1 suite             | Archived as non-production recovery material.                                                               |
| `components/DependencyMap`     | Tooling/developer diagnostics owner                      | Archived as non-production recovery material.                                                               |
| `components/Documentation`     | Documentation/code-generation concern in production tree | Archived as non-production recovery material.                                                               |
| `components/DumpDebugger`      | Developer diagnostics concern                            | Archived as non-production recovery material.                                                               |
| `components/GracefulShutdown`  | Runtime lifecycle concern                                | Archived as non-production recovery material.                                                               |
| `components/Infrastructure`    | Generic forbidden platform bucket                        | Archived as non-production recovery material.                                                               |
| `components/Logging`           | Operational logging concern                              | Archived as non-production recovery material.                                                               |
| `components/Performance`       | Benchmark/performance concern                            | Archived as non-production recovery material.                                                               |
| `components/Persistence`       | Data persistence concern                                 | Archived as non-production recovery material.                                                               |
| `components/ResourceGovernor`  | Runtime resource governance concern                      | Archived as non-production recovery material.                                                               |
| `components/Response`          | HTTP response concern                                    | Archived as non-production recovery material.                                                               |
| `components/Server`            | Runtime/server adapter concern                           | Archived as non-production recovery material.                                                               |
| `components/StatelessBoundary` | Runtime safety concern                                   | Archived as non-production recovery material.                                                               |
| `components/WorkerManager`     | Worker lifecycle concern                                 | Archived as non-production recovery material.                                                               |

These archived roots are not production owners and must not be counted as V1 proof.

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
Console
System
```

`CLI/Commands` and `CLI/UI` are no longer top-level CLI children in the production tree.

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

`Monitoring` is no longer a top-level Operations child in the production tree. Operational components must include
failure models, diagnostics, and tests before completion can be claimed.

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

Stage 02 archived duplicate `Security/System/Hashing` and `Security/System/Secrets` ownership material. Current
production ownership is `Security/Hashing`, `Security/Secrets`, and the suite-level `Security/System` boundary.

## Stage 02 Blockers

```text
[x] No extra top-level components outside final suites.
[x] DataLayer is not a real owner.
[x] CLI/Commands is not a separate runtime owner.
[x] CLI/UI is folded out of top-level CLI ownership.
[x] Operations/Monitoring is folded out of top-level Operations ownership.
[x] Security/Hashing and Security/System/Hashing ownership is singular for production.
[x] Security/Secrets and Security/System/Secrets ownership is singular for production.
[ ] composer dump-autoload -o has no skipped production classes.
[x] check-component-suite-structure.php passes.
```

## Verdict

The owner map is frozen.

The Stage 02 physical component taxonomy is GREEN. Repository readiness remains RED until autoload, tests, PHPStan,
broken refs, and component completion are proven.
