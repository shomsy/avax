# Ownership Boundary

## Owner Decisions

- `BuildRunApplication` owns construction of the default `RunApplication` dispatch pipeline.
- `RunApplication` owns route dispatch execution and controller result normalization.
- `App` owns public route registration and delegates handling.
- `CreateApplication` and `BootDslEngine` remain composition/creation owners for this slice.

## Why BuildRunApplication Exists

The default dispatch pipeline is an object graph, not runtime behavior. It must be assembled in Configuration so that runtime flow code receives ready collaborators.

## Not Fake OOP

This extraction changes ownership:

- before: runtime flow statically built its own collaborators
- after: configuration builder assembles collaborators and runtime flow executes

The new class owns one concrete assembly graph and has no runtime behavior.

## Residual Ownership Debt

- `BootDsl::create()` still builds BootDslEngine inputs.
- `Avax::create()` still builds CreateApplication inputs.
- `App::asConsoleKernel()` and `App::asRuntimeKernel()` still construct compatibility adapters.
- `CreateApplication` still owns broader App/Runtime assembly.

These remain TODO-006 follow-up slices.
