# Phase H: Health / Doctor Checks for Core Components — Evidence

Date: 2026-05-15
Phase: H (Health Checks)
Status: GREEN

## H-A: Component Implementation

Implemented `check(): HealthReport` on the following core components:

### 1. Application/Cache

- **Capability**: `CheckCacheHealth`
- **PublicSurface**: `Cache::check()`
- **Probes**: Configuration status, store connectivity, latency (ms), and memory usage metrics.

### 2. Application/Container

- **Capability**: `CheckContainerHealth`
- **PublicSurface**: `Container::check()`
- **Probes**: Configuration status, PSR-11 resolution responsiveness, and compiled artifact validity/compatibility.

### 3. Application/Filesystem

- **Capability**: `CheckFilesystemHealth`
- **PublicSurface**: `Filesystem::check()` (instance method)
- **Probes**: Temp directory writability (probe file write/read/delete) and root path accessibility.

## H-B: Runtime Doctor Integration

### Centralized Health Scanning

Created `ComponentHealthScanner` capability within the `RuntimeSafety` subsystem. This scanner:

- Discovers and executes health checks for core components.
- Translates `HealthFinding` into `RuntimeSafetyFinding`.
- Categorizes findings as `CRITICAL` or `WARNING` based on status.

### Unified Inspection

Updated `RuntimeSafety::inspect()` to include `ComponentHealthScanner`.
This ensures that `isWorkerSafe()` now accounts for component health.

### Validation Output

`php avax runtime:doctor --worker` now correctly identifies and blocks unsafe worker deployments if core components (
like the Container) are misconfigured.

**Sample Output:**

```text
Avax Runtime Doctor
Mode: worker

[CRITICAL] Container Health [container.configuration]: Container is not configured
  Component: Application/Container

[WARNING] Cache Health [cache.configuration]: Cache is not configured
  Component: Application/Cache

Summary:
  1 critical, 1 warnings, 0 info
Application is NOT safe for long-lived workers.
```

## Next Steps

- Implement health checks for remaining components (Identity, Security, Operations).
- Add `runtime:doctor` to CI/CD pipeline as a mandatory gate.
