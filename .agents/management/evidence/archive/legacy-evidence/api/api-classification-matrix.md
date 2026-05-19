# AvaX API Classification Matrix

**Date:** 2026-05-02
**Stage:** Stage 03 — API Classification and Evolution Rules
**Status:** STAGE-03 ACTIVE

---

## Purpose

This matrix defines the stability classification for every major API surface in AvaX.
It is the authoritative reference for what is `@public`, `@internal`, or `@experimental`.

---

## Classification Legend

| Symbol | Meaning                                                        |
|--------|----------------------------------------------------------------|
| 🟢     | `@public` — Stable, semantically versioned                     |
| 🟡     | `@internal` — Supported but may change without notice          |
| 🔴     | `@experimental` — Early implementation, no stability guarantee |
| ⚠️     | `@deprecated` — Functional but scheduled for removal           |
| ⬛      | `@removed` — No longer available                               |

---

## Framework PublicSurface

| Class                                               | Classification | Notes                         |
|-----------------------------------------------------|----------------|-------------------------------|
| `Avax\Framework\System\PublicSurface\Avax`          | 🟢 public      | Main framework entrypoint     |
| `Avax\Framework\System\PublicSurface\RuntimeKernel` | 🟢 public      | Runtime kernel abstraction    |
| `Avax\Framework\System\PublicSurface\HttpKernel`    | 🟢 public      | HTTP kernel abstraction       |
| `Avax\Framework\System\PublicSurface\ConsoleKernel` | 🟢 public      | Console kernel abstraction    |
| `Avax\Framework\System\PublicSurface\Runtime`       | 🟢 public      | Runtime capability entrypoint |

---

## Framework Flows

| Class                                                 | Classification | Notes                     |
|-------------------------------------------------------|----------------|---------------------------|
| `Avax\Framework\System\Flows\BootApplication\*`       | 🟡 internal    | Boot orchestration        |
| `Avax\Framework\System\Flows\HandleIncomingHttp\*`    | 🟡 internal    | HTTP request handling     |
| `Avax\Framework\System\Flows\RunConsoleCommand\*`     | 🟡 internal    | CLI command execution     |
| `Avax\Framework\System\Flows\ResetApplicationState\*` | 🟡 internal    | State reset after request |
| `Avax\Framework\System\Flows\ShutdownRuntime\*`       | 🟡 internal    | Graceful shutdown         |

---

## Framework Capabilities

| Class                                                       | Classification | Notes                              |
|-------------------------------------------------------------|----------------|------------------------------------|
| `Avax\Framework\System\Capabilities\Runtime\*`              | 🟡 internal    | Runtime context, mode, environment |
| `Avax\Framework\System\Capabilities\RequestScope\*`         | 🟡 internal    | Request lifecycle scope            |
| `Avax\Framework\System\Capabilities\StateReset\*`           | 🟡 internal    | State reset registry               |
| `Avax\Framework\System\Capabilities\ComponentRegistry\*`    | 🟡 internal    | Component registration             |
| `Avax\Framework\System\Capabilities\RuntimeSafety\*`        | 🟡 internal    | Runtime safety boundary            |
| `Avax\Framework\System\Capabilities\ExternalState\*`        | 🟡 internal    | External state leak detection      |
| `Avax\Framework\System\Capabilities\ResourceGovernance\*`   | 🟡 internal    | Memory and time budgets            |
| `Avax\Framework\System\Capabilities\WorkerManagement\*`     | 🟡 internal    | Worker lifecycle                   |
| `Avax\Framework\System\Capabilities\RuntimeCompatibility\*` | 🟡 internal    | Runtime adapter compatibility      |
| `Avax\Framework\System\Capabilities\PreCommit\*`            | 🟡 internal    | Pre-commit validation              |

---

## Application Suite

| Component                | PublicSurface | Internal    | Status                               |
|--------------------------|---------------|-------------|--------------------------------------|
| Application/Cache        | 🟡 internal   | 🟡 internal | Partial — PHPStan errors in progress |
| Application/Config       | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Container    | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/DateTime     | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Facade       | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/FeatureFlags | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Filesystem   | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Pipeline     | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Text         | 🟡 internal   | 🟡 internal | Unknown                              |
| Application/Validation   | 🟡 internal   | 🟡 internal | Unknown                              |

---

## HTTP Suite

| Component          | PublicSurface | Internal    | Status                        |
|--------------------|---------------|-------------|-------------------------------|
| HTTP/Request       | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| HTTP/Response      | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/Router        | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/Middleware    | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/Session       | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/Security      | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/Client        | 🟡 internal   | 🟡 internal | Unknown                       |
| HTTP/ApiVersioning | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |

---

## Operations Suite

| Component                      | PublicSurface | Internal    | Status                    |
|--------------------------------|---------------|-------------|---------------------------|
| Operations/Events              | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Logging             | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Queue               | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/MessageBus          | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/ApplicationWorkflow | 🟡 internal   | 🟡 internal | Partial — Saga refactored |
| Operations/Mail                | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Notifications       | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Realtime            | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Scheduler           | 🟡 internal   | 🟡 internal | Unknown                   |
| Operations/Resilience          | 🟡 internal   | 🟡 internal | Unknown                   |

---

## Identity Suite

| Component                 | PublicSurface | Internal    | Status                        |
|---------------------------|---------------|-------------|-------------------------------|
| Identity/Auth             | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| Identity/Access           | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| Identity/Credentials      | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| Identity/Tokens           | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| Identity/ExternalIdentity | 🟡 internal   | 🟡 internal | Unknown                       |
| Identity/Tenancy          | 🟡 internal   | 🟡 internal | Unknown                       |

---

## Security Suite

| Component        | PublicSurface | Internal    | Status  |
|------------------|---------------|-------------|---------|
| Security         | 🟡 internal   | 🟡 internal | Unknown |
| Security/Hashing | 🟡 internal   | 🟡 internal | Unknown |
| Security/Secrets | 🟡 internal   | 🟡 internal | Unknown |

---

## DataStack Suite

| Component             | PublicSurface | Internal    | Status  |
|-----------------------|---------------|-------------|---------|
| DataStack/Data        | 🟡 internal   | 🟡 internal | Unknown |
| DataStack/Database    | 🟡 internal   | 🟡 internal | Unknown |
| DataStack/Persistence | 🟡 internal   | 🟡 internal | Unknown |

---

## CLI Suite

| Component   | PublicSurface | Internal    | Status                      |
|-------------|---------------|-------------|-----------------------------|
| CLI/Console | 🟡 internal   | 🟡 internal | Partial — basic test passes |

---

## DeveloperTools Suite

| Component                     | PublicSurface | Internal    | Status                        |
|-------------------------------|---------------|-------------|-------------------------------|
| DeveloperTools/Diagnostics    | 🟡 internal   | 🟡 internal | Partial — targeted tests pass |
| DeveloperTools/CodeGeneration | 🟡 internal   | 🟡 internal | Unknown                       |
| DeveloperTools/Testing        | 🟡 internal   | 🟡 internal | Unknown                       |

---

## Presentation Suite

| Component         | PublicSurface | Internal    | Status  |
|-------------------|---------------|-------------|---------|
| Presentation/View | 🟡 internal   | 🟡 internal | Unknown |

---

## Classification Rules Summary

```text
@public  → only in System/PublicSurface/ lanes
@internal → System/Capabilities/, System/Flows/, System/Configuration/
@experimental → labs/, new components before Stage 04 completion
@deprecated → must have replacement and deprecation notice
@removed → completely removed, documented in changelog
```

---

## Maintenance

This matrix must be updated:

```text
[ ] When a new component is added (Stage 04)
[ ] When a component changes classification
[ ] When an API is deprecated or removed
[ ] During each Stage 03 review pass
```

---

## Related Documents

- `docs/governance/public-api-policy.md` — stability level definitions
- `docs/governance/deprecation-policy.md` — deprecation lifecycle
- `docs/governance/compatibility-policy.md` — compatibility bridges