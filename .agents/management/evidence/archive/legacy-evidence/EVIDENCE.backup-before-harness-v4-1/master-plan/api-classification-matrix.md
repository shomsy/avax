# API Classification Matrix

Date: 2026-05-06
Stage: 03 - API Classification and Evolution Rules
Status: ACTIVE / GOVERNANCE EVIDENCE

## Purpose

This matrix classifies AvaX API stability by ownership path before broad PublicSurface completion.

The matrix prevents accidental public API by making the default rule explicit:

```text
PublicSurface receives.
Flows execute.
Capabilities power.
Configuration assembles.
Foundation supports.
```

## Stability Tags

| Tag             | Meaning                                  | Allowed owner                                                                                  | Change rule                                                     |
|-----------------|------------------------------------------|------------------------------------------------------------------------------------------------|-----------------------------------------------------------------|
| `@public`       | Supported external API.                  | `framework/System/PublicSurface/**`, `components/*/*/System/PublicSurface/**`                  | Breaking changes require deprecation and major version removal. |
| `@internal`     | Implementation API.                      | `System/Flows/**`, `System/Capabilities/**`, `System/Configuration/**`, `System/Foundation/**` | May change without public compatibility promise.                |
| `@experimental` | Preview or labs API.                     | `labs/**`, incomplete components, explicitly marked preview APIs                               | May change or be removed until promoted.                        |
| `@deprecated`   | Supported but scheduled for replacement. | Existing public or compatibility API with replacement                                          | Must include replacement and removal plan.                      |
| `@removed-in`   | Removal target marker.                   | Deprecated API docs and migration notes                                                        | Must name the first version where the API is absent.            |

## Path Classification Rules

| Path pattern                             | Classification       | Notes                                                                               |
|------------------------------------------|----------------------|-------------------------------------------------------------------------------------|
| `framework/System/PublicSurface/**`      | `@public`            | Framework-facing entrypoints such as HTTP, Console, Runtime, and Avax facade.       |
| `components/*/*/System/PublicSurface/**` | `@public`            | Component entrypoints callable by applications or other components.                 |
| `components/*/*/System/Flows/**`         | `@internal`          | Complete component actions; not a stable external API by default.                   |
| `components/*/*/System/Capabilities/**`  | `@internal`          | Reusable component muscles; exported only through PublicSurface or explicit policy. |
| `components/*/*/System/Configuration/**` | `@internal`          | Assembly and registration code.                                                     |
| `components/*/*/System/Foundation/**`    | `@internal`          | Local primitives and failures.                                                      |
| `components/*/*/tests/**`                | test-only            | Not production API.                                                                 |
| `examples/**`                            | non-production       | Usage examples, not framework contracts.                                            |
| `labs/**`                                | `@experimental`      | Planning or experimental implementation until promoted.                             |
| `tooling/**`                             | internal tooling     | Not framework API.                                                                  |
| `components/compat.php`                  | compatibility bridge | Alias lifecycle governed by `docs/governance/compatibility-policy.md`.              |

## Component Public Surface Matrix

| Area           | Component / surface                                | Current classification                    | Required proof before stable release                                                                      |
|----------------|----------------------------------------------------|-------------------------------------------|-----------------------------------------------------------------------------------------------------------|
| Framework      | `framework/System/PublicSurface/**`                | `@public`                                 | Public API tests, runtime doctor, PHPStan, compatibility policy.                                          |
| Application    | `Application/Cache/System/PublicSurface/**`        | `@public`                                 | Cache contract tests, PHPStan, runtime-safety checks.                                                     |
| Application    | `Application/Config/System/PublicSurface/**`       | `@public`                                 | Config read/write tests and config security review.                                                       |
| Application    | `Application/Container/System/PublicSurface/**`    | `@public`                                 | Container resolution tests and no runtime leaks.                                                          |
| Application    | `Application/DateTime/System/PublicSurface/**`     | `@public`                                 | Clock/time tests and deterministic fake clock support.                                                    |
| Application    | `Application/Facade/System/PublicSurface/**`       | `@public`                                 | Long-lived runtime reset proof.                                                                           |
| Application    | `Application/FeatureFlags/System/PublicSurface/**` | `@public`                                 | Flag evaluation tests and safe defaults.                                                                  |
| Application    | `Application/Filesystem/System/PublicSurface/**`   | `@public`                                 | Path traversal negative tests and filesystem security review.                                             |
| Application    | `Application/Pipeline/System/PublicSurface/**`     | `@public`                                 | Pipeline execution tests.                                                                                 |
| Application    | `Application/Text/System/PublicSurface/**`         | `@public`                                 | Text transform and validation tests.                                                                      |
| Application    | `Application/Validation/System/PublicSurface/**`   | `@public`                                 | Validation positive and negative tests.                                                                   |
| DataStack      | `DataStack/Data/System/PublicSurface/**`           | `@public`                                 | Data object, collection, and shape tests.                                                                 |
| DataStack      | `DataStack/Database/System/PublicSurface/**`       | `@public`                                 | Query, schema, migration, transaction, and SQLite proof tests.                                            |
| DataStack      | `DataStack/Persistence/System/PublicSurface/**`    | `@public`                                 | Persistence contract tests and failure model.                                                             |
| HTTP           | `HTTP/Request/System/PublicSurface/**`             | `@public`                                 | Request creation tests and input boundary checks.                                                         |
| HTTP           | `HTTP/Response/System/PublicSurface/**`            | `@public`                                 | Response construction tests and safe output behavior.                                                     |
| HTTP           | `HTTP/Router/System/PublicSurface/**`              | `@public`                                 | Route registration, dispatch, and failure tests.                                                          |
| HTTP           | `HTTP/Session/System/PublicSurface/**`             | `@public`                                 | Session isolation and security tests.                                                                     |
| HTTP           | `HTTP/Security/System/PublicSurface/**`            | `@public`                                 | CSRF, signed URL, and header security tests where applicable.                                             |
| HTTP           | `HTTP/Client/System/PublicSurface/**`              | `@public`                                 | Timeout and external I/O safety tests.                                                                    |
| HTTP           | `HTTP/Context/System/PublicSurface/**`             | `@public`                                 | Request-scope isolation proof.                                                                            |
| HTTP           | `HTTP/ContentNegotiation/System/PublicSurface/**`  | `@public`                                 | Negotiation and formatter tests.                                                                          |
| HTTP           | `HTTP/Middleware/System/PublicSurface/**`          | `@public`                                 | Middleware pipeline tests.                                                                                |
| HTTP           | `HTTP/Dispatcher/System/PublicSurface/**`          | `@public`                                 | Controller dispatch tests.                                                                                |
| Identity       | `Identity/*/System/PublicSurface/**`               | `@public`                                 | Authentication, authorization, token, and tenancy security tests by component.                            |
| Security       | `Security/*/System/PublicSurface/**`               | `@public`                                 | Secret handling, redaction, and crypto policy tests.                                                      |
| Operations     | `Operations/*/System/PublicSurface/**`             | `@public`                                 | Queue, event, message, mail, scheduler, resilience, observability, and runtime-safety tests by component. |
| Presentation   | `Presentation/*/System/PublicSurface/**`           | `@public`                                 | View rendering tests and output escaping policy.                                                          |
| DeveloperTools | `DeveloperTools/*/System/PublicSurface/**`         | `@public` or `@experimental` by component | Must not expose production secrets; diagnostics must redact sensitive data.                               |
| CLI            | `CLI/*/System/PublicSurface/**`                    | `@public`                                 | Command execution and failure tests.                                                                      |

## Compatibility Alias Classification

| Owner                    | Classification                                                         | Lifecycle                                                                                                                                       |
|--------------------------|------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------|
| `components/compat.php`  | `@deprecated` compatibility bridge / `@internal` implementation detail | Alias must point to a canonical class, include replacement policy in docs, and be removed according to `docs/governance/deprecation-policy.md`. |
| Legacy namespace aliases | `@deprecated` when user-callable, otherwise `@internal`                | Must not hide missing implementations or silence static analysis.                                                                               |

## Stage 03 Acceptance Mapping

| Requirement                               | Evidence                                             |
|-------------------------------------------|------------------------------------------------------|
| `@public` is defined                      | `docs/governance/public-api-policy.md`, this matrix  |
| `@internal` is defined                    | `docs/governance/public-api-policy.md`, this matrix  |
| `@experimental` is defined                | `docs/governance/public-api-policy.md`, this matrix  |
| `@deprecated` is defined                  | `docs/governance/deprecation-policy.md`, this matrix |
| `@removed-in` is defined                  | `docs/governance/public-api-policy.md`, this matrix  |
| PublicSurface breaking-change rule exists | `docs/governance/public-api-policy.md`               |
| Compatibility alias lifecycle exists      | `docs/governance/compatibility-policy.md`            |

## Remaining Work

- Later stages must annotate or verify individual public classes as component completion proceeds.
- Full public API tests remain part of later kernel-green and production-readiness gates.
