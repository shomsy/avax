# Provider Wiring Consistency Recheck

**Date:** 2026-05-15

## 1. Provider Wiring Tests

| Provider                     | Scenario                              | Test                              | Result | Decision |
|------------------------------|---------------------------------------|-----------------------------------|--------|----------|
| ApiVersioningServiceProvider | Registers VersionRegistry singleton   | ApiVersioningServiceProviderTest  | PASS   | Proven   |
| ApiVersioningServiceProvider | Boot wires ApiVersion facade          | ApiVersioningServiceProviderTest  | PASS   | Proven   |
| ApiVersioningServiceProvider | Facade uses provider-created registry | ApiVersioningServiceProviderTest  | PASS   | Proven   |
| ApiVersioningServiceProvider | No second source of truth             | ApiVersioningServiceProviderTest  | PASS   | Proven   |
| ApiVersioningServiceProvider | Reset clears facade state             | ApiVersion facade lifecycle tests | PASS   | Proven   |
| ApiVersioningServiceProvider | Unconfigured usage fails clearly      | ApiVersion facade lifecycle tests | PASS   | Proven   |
| PipelineServiceProvider      | Registers HookRegistry singleton      | PipelineServiceProviderTest       | PASS   | Proven   |
| PipelineServiceProvider      | Boot wires Pipeline facade            | PipelineServiceProviderTest       | PASS   | Proven   |
| PipelineServiceProvider      | Facade uses provider-created registry | PipelineServiceProviderTest       | PASS   | Proven   |
| PipelineServiceProvider      | No second source of truth             | PipelineServiceProviderTest       | PASS   | Proven   |
| PipelineServiceProvider      | Reset clears facade state             | Pipeline facade lifecycle tests   | PASS   | Proven   |
| PipelineServiceProvider      | Unconfigured usage fails clearly      | Pipeline facade lifecycle tests   | PASS   | Proven   |

## 2. Code Inspection

- ApiVersioningServiceProvider::register() — registers VersionRegistry singleton from config
- ApiVersioningServiceProvider::boot() — resolves VersionRegistry, injects into ApiVersion::setInstance()
- PipelineServiceProvider::register() — registers HookRegistry singleton
- PipelineServiceProvider::boot() — resolves HookRegistry, injects into Pipeline::setInstance()
- ApiVersion::registry() — throws RuntimeException if unconfigured (no lazy new)
- Pipeline::registry() — throws RuntimeException if unconfigured (no lazy new)
- No second VersionRegistry source of truth exists
- No second HookRegistry source of truth exists

## 3. Decision

Provider wiring proof remains valid. Both providers register singleton and wire facade during boot. No duplicate
registry. No lazy self-instantiation.
