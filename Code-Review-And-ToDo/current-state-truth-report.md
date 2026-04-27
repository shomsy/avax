# Current State Truth Report

> Generated: 2026-04-27
> Phase: 1 – Regenerate real filesystem truth

---

## Component Directory Classification

### Migrated Components (System/ Structure Present)

| Component    | Path                            | Classification    | Namespace                                 | Notes                                                            |
|--------------|---------------------------------|-------------------|-------------------------------------------|------------------------------------------------------------------|
| Auth         | components/Auth/System/         | **Real owner**    | `Avax\Components\Auth\System\...`         | Clean structure                                                  |
| Cache        | components/Cache/System/        | **Real owner**    | `Avax\Components\Cache\System\...`        | Clean structure                                                  |
| Commands     | components/Commands/            | **Unclear**       | needs audit                               | No System/ dir visible                                           |
| Config       | components/Config/System/       | **Real owner**    | `Avax\Components\Config\System\...`       | PublicSurface holds mutable state (violation)                    |
| Container    | components/Container/           | **Real owner**    | mixed                                     | DI/Capabilities subtree; generated namespace in CompileContainer |
| Data         | components/Data/System/         | **Partial owner** | `Avax\Components\Data\System\...`         | DataFoundation still owns real behavior                          |
| Database     | components/Database/System/     | **Real owner**    | `Avax\Database\System\...`                | Legacy root files remain (Database.php, Query.php etc.)          |
| DateTime     | components/DateTime/            | **Unclear**       | needs audit                               | No System/ dir visible                                           |
| DumpDebugger | components/DumpDebugger/System/ | **Real owner**    | `Avax\Components\DumpDebugger\System\...` | Small component                                                  |
| Events       | components/Events/              | **Unclear**       | needs audit                               | No System/ dir visible                                           |
| Filesystem   | components/Filesystem/System/   | **Real owner**    | `Avax\Components\Filesystem\System\...`   | Clean structure                                                  |
| HTTP         | components/HTTP/System/         | **Real owner**    | `Avax\Components\HTTP\System\...`         | Complex: Kernel, Request, Router, Response, Session, etc.        |
| Logging      | components/Logging/             | **Real owner**    | `Avax\Components\Logging\System\...`      | Clean structure                                                  |
| Mail         | components/Mail/System/         | **Real owner**    | `Avax\Components\Mail\System\...`         | Clean structure                                                  |
| Middleware   | components/Middleware/          | **Unclear**       | needs audit                               | Separate from HTTP/Middleware?                                   |
| Persistence  | components/Persistence/System/  | **Real owner**    | `Avax\Components\Persistence\System\...`  | Clean structure                                                  |
| Queue        | components/Queue/System/        | **Real owner**    | `Avax\Components\Queue\System\...`        | Clean structure                                                  |
| Security     | components/Security/            | **Unclear**       | needs audit                               | Separate from HTTP/Security?                                     |
| Session      | components/Session/             | **Unclear**       | needs audit                               | Duplicate of HTTP/Session?                                       |
| Text         | components/Text/System/         | **Real owner**    | `Avax\Components\Text\System\...`         | Clean structure                                                  |
| Validation   | components/Validation/          | **Unclear**       | needs audit                               | No System/ dir visible                                           |
| View         | components/View/                | **Real owner**    | needs audit                               | View engine component                                            |

### Legacy / Deprecated Components

| Component      | Path                       | Classification                              | Evidence                                                                                                                                                                                                                                                         |
|----------------|----------------------------|---------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| DataFoundation | components/DataFoundation/ | **Stale legacy — REAL BEHAVIOR STILL HERE** | Arrhae.php (627 lines), Collection.php (464 lines), DataTransfer/ ecosystem, Collections/, Composites/, Contracts/, Exceptions/, Flows/, Internal/, Interop/, ObjectHandling/, Structures/, Validation/, Values/ — ALL contain real production code, NOT bridges |
| DataLayer      | (does not exist)           | **Fully removed**                           | No directory found. Migration appears complete.                                                                                                                                                                                                                  |

### Framework

| Path              | Classification | Notes                                                                                                |
|-------------------|----------------|------------------------------------------------------------------------------------------------------|
| framework/System/ | **Real owner** | Runtime/lifecycle owner. Contains Capabilities/, Configuration/, Flows/, Foundation/, PublicSurface/ |

### Bridge Files

| File                  | Classification           | Notes                                                                                                                                                       |
|-----------------------|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| components/compat.php | **Compatibility bridge** | 53 class aliases mapping old → new namespaces. Contains bidirectional aliases (some old→new, some new→old). Mixed `components\...` and `Avax\...` patterns. |

---

## Namespace Drift Report

### `components\...` (lowercase) namespace declarations

| File                                                                           | Namespace                                            | Problem                                |
|--------------------------------------------------------------------------------|------------------------------------------------------|----------------------------------------|
| components/DataFoundation/Arrhae.php                                           | `components\DataFoundation`                          | Legacy lowercase root                  |
| components/ApplicationWorkflow/Saga/CompensateSaga/ChooseCompensationSteps.php | `components\ApplicationWorkflow\Saga\CompensateSaga` | Legacy lowercase root                  |
| components/ApplicationWorkflow/Saga/* (multiple)                               | `components\ApplicationWorkflow\...`                 | All Saga files use lowercase           |
| components/Container/DI/Capabilities/.../CompileContainer.php                  | generates `components\Container\...`                 | Generated code embeds legacy namespace |
| components/compat.php                                                          | maps `components\...` ↔ `Avax\...`                   | Bridge file, acceptable                |

### `Avax\DataFoundation\...` namespace declarations

| File                                                    | Namespace                                |
|---------------------------------------------------------|------------------------------------------|
| components/DataFoundation/Collection.php                | `Avax\DataFoundation`                    |
| components/DataFoundation/DataTransfer/DataTransfer.php | `Avax\DataFoundation\DataTransfer`       |
| components/DataFoundation/DataTransfer/* (all subfiles) | `Avax\DataFoundation\DataTransfer\...`   |
| components/DataFoundation/Collections/*                 | `Avax\DataFoundation\Collections\...`    |
| components/DataFoundation/Composites/*                  | `Avax\DataFoundation\Composites\...`     |
| components/DataFoundation/Contracts/*                   | `Avax\DataFoundation\Contracts\...`      |
| components/DataFoundation/Exceptions/*                  | `Avax\DataFoundation\Exceptions\...`     |
| components/DataFoundation/Flows/*                       | `Avax\DataFoundation\Flows\...`          |
| components/DataFoundation/Internal/*                    | `Avax\DataFoundation\Internal\...`       |
| components/DataFoundation/Interop/*                     | `Avax\DataFoundation\Interop\...`        |
| components/DataFoundation/ObjectHandling/*              | `Avax\DataFoundation\ObjectHandling\...` |
| components/DataFoundation/Structures/*                  | `Avax\DataFoundation\Structures\...`     |
| components/DataFoundation/Validation/*                  | `Avax\DataFoundation\Validation\...`     |
| components/DataFoundation/Values/*                      | `Avax\DataFoundation\Values\...`         |

### `Avax\DataLayer\...` namespace declarations

**None found.** DataLayer appears fully migrated.

---

## Unsafe SQL Report

| File                                                                               | Line       | Pattern                             | Classification         | Severity                                   |
|------------------------------------------------------------------------------------|------------|-------------------------------------|------------------------|--------------------------------------------|
| Database/System/Capabilities/Migrations/ExportDatabase/DatabaseExporter.php        | 64         | `addslashes()` in INSERT VALUES     | **Production code**    | **HIGH** — SQL injection vector            |
| Database/System/Capabilities/Migrations/Design/Column/Render/ColumnSQLRenderer.php | 90         | `addslashes()` in COMMENT clause    | **Production code**    | **MEDIUM** — schema builder, limited input |
| HTTP/Router/System/Capabilities/RouterMetrics/RouterMetricsCollector.php           | 196        | `addslashes()` in Prometheus labels | **Production code**    | **LOW** — not SQL context                  |
| Database/Database.txt                                                              | 5076, 7004 | `addslashes()`                      | **Generated artifact** | N/A — text dump                            |
| HTTP/Router/Router.txt                                                             | 1682       | `addslashes()`                      | **Generated artifact** | N/A — text dump                            |

---

## Recovered Skeleton Report (Summary)

**15 files** in `components/ApplicationWorkflow/Saga/` contain `describeResponsibility()`.
Mixed: some have real behavior (ChooseCompensationSteps has 83 lines of real logic), some are pure architecture notes.
See: `Code-Review-And-ToDo/recovered-skeletons.md` for full classification.

---

## Root-Level Legacy Files in Database/

| File                       | Classification         | Action Needed                                                         |
|----------------------------|------------------------|-----------------------------------------------------------------------|
| Database/Database.php      | **Stale legacy**       | Should be bridge or removed; System/PublicSurface/Database.php exists |
| Database/EntityManager.php | **Stale legacy**       | Should be bridge or removed; Persistence owns EntityManager           |
| Database/Migrations.php    | **Stale legacy**       | Should be bridge or removed                                           |
| Database/Query.php         | **Stale legacy**       | Should be bridge or removed; System/PublicSurface/Query.php exists    |
| Database/Schema.php        | **Stale legacy**       | Should be bridge or removed                                           |
| Database/Telemetry.php     | **Stale legacy**       | Should be bridge or removed                                           |
| Database/Transactions.php  | **Stale legacy**       | Should be bridge or removed                                           |
| Database/functions.php     | **Bridge**             | Function aliases                                                      |
| Database/Database.txt      | **Generated artifact** | Delete                                                                |
| Database/refactor.md       | **Generated artifact** | Move to docs or delete                                                |

---

## Duplicate Owner Report

| Responsibility                   | Owner 1                                                  | Owner 2                                             | Resolution                                      |
|----------------------------------|----------------------------------------------------------|-----------------------------------------------------|-------------------------------------------------|
| Collections (Collection, Arrhae) | DataFoundation/Collection.php, DataFoundation/Arrhae.php | Data/System/Capabilities/Collections/               | DataFoundation files are REAL CODE, not bridges |
| DataTransfer ecosystem           | DataFoundation/DataTransfer/                             | (not yet migrated to Data)                          | DataFoundation owns all behavior                |
| ObjectHandling/DTO               | DataFoundation/ObjectHandling/DTO/                       | Persistence/System/Capabilities/ObjectHandling/DTO/ | compat.php bridges, unclear which owns          |
| Composites (Pair, Tuple, etc.)   | DataFoundation/Composites/                               | (not migrated)                                      | DataFoundation owns all behavior                |
| Structures (Stack, Queue, etc.)  | DataFoundation/Structures/                               | (not migrated)                                      | DataFoundation owns all behavior                |
| Values (Option, Result)          | DataFoundation/Values/                                   | (not migrated)                                      | DataFoundation owns all behavior                |
| Flows (Pipeline, Batch, etc.)    | DataFoundation/Flows/                                    | Data/System/Flows/                                  | Unclear split                                   |

---

## Autoload Configuration

```json
"psr-4": {
    "Avax\\Framework\\": "framework/",
    "Avax\\": "components/",
    "components\\": "components/"
}
```

**Problem**: Both `Avax\` and `components\` map to `components/`. This enables the mixed namespace issue by design.
Cannot be removed until all `components\...` namespaces are migrated.

---

## Summary

| Category                        | Count | Status                                                    |
|---------------------------------|-------|-----------------------------------------------------------|
| Real owners (clean)             | 14    | OK                                                        |
| Real owners (partial)           | 2     | Data, Database                                            |
| Stale legacy with real behavior | 1     | DataFoundation                                            |
| Fully removed legacy            | 1     | DataLayer                                                 |
| Unclear components              | 6     | Commands, DateTime, Events, Middleware, Security, Session |
| Bridge files                    | 1     | compat.php                                                |
| Unsafe SQL files                | 2     | HIGH/MEDIUM severity                                      |
| Recovered skeletons             | 15    | ApplicationWorkflow/Saga                                  |
| Namespace drift files           | 15+   | components\ and Avax\DataFoundation\                      |
