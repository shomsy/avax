# TODO-006 Slice D High-Level Design

## Object Graph Assembly Analysis

- **Target**: `framework/System/PublicSurface/Avax.php`
- **Current Findings**:
  - `Avax::create()` constructs `CreateHttpResponse`, `CreateRequestFromGlobals` (with 8 collaborators), and `CreateApplication` (with 8 collaborators).
  - `bootInternal()` constructs `CreateHttpResponse`, `HandleIncomingHttp`, `BootApplication`, `BuildApplicationState` (with 4 collaborators), `HttpKernel`, `ConsoleKernel`, `RunConsoleCommand`, `PreCommitConfig`, `PreCommit`, `RuntimeKernel`, and `ResetApplicationState`.
- **Verdict**: This is **true object-graph assembly** that violates the principle "PublicSurface receives and delegates; Configuration/Assembly/Provider boundaries assemble object graphs".

## Design Choices

1. **Keep PublicSurface Thin**: The `Avax` class is the primary public facade for booting the framework. It must remain 100% stable but perform zero inline assembly.
2. **Configuration Boundary Ownership**: We introduce `BuildAvaxEngine` under `framework/System/Configuration/BuildApplication/Builders/`. It owns all static object graph constructions for both the simple V4 `App` factory and the full boot sequence.
3. **No Circularity**: `Avax` delegates to `BuildAvaxEngine`, which builds and returns `App` or `Avax` instances. Because `BuildAvaxEngine` is in `Configuration` and `Avax` is in `PublicSurface`, this respects a clean dependency flow (Public entrypoints delegate to Configuration).

## Architectural Evaluations

- **Public API Impact**: None. `Avax::create()` and `Avax::boot()` signatures, parameters, and return types are strictly preserved.
- **Runtime/Hot-Path Impact**: 100% neutral. Moving static instantiation from one method to another has zero runtime penalty in PHP.
- **Dogfooding Impact**: Cleanly aligns framework assembly with the screaming architecture structure.
- **Failure Semantics**: Assembly errors will fail immediately during application construction / boot phase (fail-fast), not deep within request flows.
