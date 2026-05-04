# AvaX Component Owner Map

Date: 2026-05-03  
Status: RED / not canonical  
Source: `Code-Review-And-ToDo/avax-master-development-plan-v1.md`

## Canonical V1 Owners

| Suite            | Intended Ownership                                                                                                                                          | Status  |
|------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|---------|
| `Application`    | Framework-adjacent application capabilities such as config, container, cache, pipeline, validation, filesystem, facade, feature flags, date/time, and text. | PARTIAL |
| `HTTP`           | Request, response, routing, middleware, session, client, context, API versioning, and HTTP-specific security.                                               | GREEN   |
| `CLI`            | Console entrypoints and command runtime support.                                                                                                            | GREEN   |
| `DataStack`      | Data, database, persistence, and query/runtime data capabilities.                                                                                           | PARTIAL |
| `Identity`       | Auth, access, credentials, tokens, external identity, and tenancy.                                                                                          | PARTIAL |
| `Security`       | Cryptography, hashing, secrets, redaction, output safety, audit, and security primitives.                                                                   | PARTIAL |
| `Operations`     | Events, logging, queues, workflows, observability, delivery, runtime supervision, memory lifecycle, and operational behavior.                               | PARTIAL |
| `Presentation`   | View and rendering concerns.                                                                                                                                | PARTIAL |
| `DeveloperTools` | Diagnostics, code generation, testing, and developer experience tooling.                                                                                    | PARTIAL |

## Non-Canonical Owners Found

| Existing Path              | Required Classification                           | Required Action                                                            |
|----------------------------|---------------------------------------------------|----------------------------------------------------------------------------|
| `components/API`           | V2 contract plane draft                           | Keep locked until V1 GREEN; do not count as V1 proof.                      |
| `components/Integration`   | V2 integration plane draft                        | Keep locked until V1 GREEN.                                                |
| `components/DataLayer`     | Not a real owner in V1 production readiness rules | Fold into `DataStack` or remove as owner through a staged taxonomy repair. |
| `components/DependencyMap` | Unclear production owner                          | Classify as `DeveloperTools`, `Operations`, or tooling before GREEN.       |
| `components/Documentation` | Documentation owner in production tree            | Move documentation truth under `docs/` or classify as DeveloperTools code. |
| `components/Performance`   | Benchmark/performance plane                       | Move to `benchmarks/` or a later-stage owner.                              |
| `components/Server`        | Runtime/server behavior                           | Fold into `framework/System/Capabilities/Runtime` or an approved suite.    |
| `components/.idea`         | IDE metadata inside production tree               | Remove from production component tree.                                     |

## Stage 02 Blockers

```text
[x] No extra top-level components outside final suites.
[x] DataLayer is not a real owner.
[x] CLI/Commands is not a separate runtime owner.
[x] CLI/UI is folded into CLI/Console.
[x] Operations/Monitoring is folded into Operations/Observability.
[ ] composer dump-autoload -o has no skipped production or test classes.
```

## Verdict

The owner map is now documented, but taxonomy is RED until the physical tree and autoload evidence match this map.
