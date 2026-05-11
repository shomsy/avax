# AvaX Declarative Failure Boundary

## Overview

The Failure Boundary is a declarative failure handling system for AvaX. It allows developers to declare **what should happen when something fails** using PHP attributes, while the framework handles the execution through a compiled metadata pipeline.

## Core Principles

1. **Attributes declare, not execute** — PHP attributes describe failure behavior. They contain no logic.
2. **Compiled metadata** — Attributes are compiled once into metadata. No per-request reflection.
3. **Single boundary** — One controlled `try/catch/finally` per execution context.
4. **Pipeline decisions** — A failure pipeline classifies, reports, and routes each failure.
5. **Runtime stays clean** — Controllers, jobs, and commands contain business logic, not error handling boilerplate.

## Before and After

### Before (manual try/catch)

```php
try {
    return $this->users->create($data);
} catch (ValidationFailed $failure) {
    return Response::json($failure->errors(), 422);
} catch (Throwable $failure) {
    $this->logger->error($failure);
    throw $failure;
}
```

### After (declarative)

```php
#[OnFailure(ValidationFailed::class, respondWith: 422)]
#[ReportFailure(channel: 'http')]
public function createUser(CreateUserData $data): Response
{
    return $this->users->create($data);
}
```

## Architecture

```
Attributes → Compiler → CompiledPolicyCache → FailureBoundary → FailurePipeline → Decision
```

1. **Attributes** on methods declare failure behavior
2. **Compiler** reads attributes via reflection (once)
3. **CompiledPolicyCache** stores compiled policies (in-memory + file artifact)
4. **FailureBoundary** wraps action execution in try/catch/finally
5. **FailurePipeline** classifies, reports, and routes each failure
6. **Decision** executes the selected action (retry, fallback, map, dead-letter, rethrow)

## Component Location

```
framework/System/Capabilities/FailureBoundary/
  PublicSurface/     — FailureBoundary static facade
  Flows/            — RunProtectedAction, ResolveFailurePolicy
  Capabilities/     — Pipeline steps, compiler, reporting
  Configuration/    — Config DTO, BuildFailureBoundary
  Foundation/       — Models, attributes, enums, exceptions
  Integration/      — HttpFailureBoundaryMiddleware
```

## Failure Decision Types

| Decision | Description |
|----------|-------------|
| `MapToResult` | Map the failure to a response/result (e.g., 422, 401) |
| `Retry` | Retry the action with configurable backoff |
| `Fallback` | Execute an alternative handler |
| `DeadLetter` | Send to a dead letter queue |
| `Rethrow` | Propagate the exception |
| `ReportOnly` | Report and wrap in UnhandledFailure |

## Boundary Kinds

| Kind | Context |
|------|---------|
| `Http` | HTTP request handling |
| `Console` | CLI command execution |
| `Queue` | Queue job processing |
| `Worker` | Long-running worker |
| `Scheduler` | Scheduled task |
| `Webhook` | Webhook delivery |

## Known Limitations (MVP)

- ReportFailure uses `error_log()` — Observability integration is planned
- DeadLetter logs to `error_log()` — Queue integration is planned
- Timeout attribute is defined but not enforced at the boundary level (requires fiber/runtime support)
- Retry implements its own backoff — will be replaced by a future Resilience component
