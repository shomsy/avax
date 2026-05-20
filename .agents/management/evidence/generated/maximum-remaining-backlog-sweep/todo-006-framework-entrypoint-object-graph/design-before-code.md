# TODO-006 Slice A Design Before Code

## Active Design Problem

`App` and `RunApplication` formed a runtime/PublicSurface assembly path:

- `App` lazily initialized a dispatcher.
- `RunApplication::withDefaultResolutionPipeline()` assembled the default dispatch pipeline.
- The assembled graph included router matching, controller resolving, argument resolving, secure request input building, request reading, and response normalization dependencies.

This violated the rule that PublicSurface receives/delegates and runtime flows execute.

## Design Decision

Create `BuildRunApplication` under `framework/System/Configuration/Builders`.

The builder owns exactly one assembly graph: the default `RunApplication` dispatch pipeline used by zero-configuration `App` creation.

## Scope

In scope:

- move default dispatcher assembly out of `RunApplication`
- remove lazy dispatcher construction from `App`
- pass a ready dispatcher from `CreateApplication` and `BootDslEngine`
- update the architecture contract test

Out of scope:

- `Avax::create()` graph extraction
- `BootDsl::create()` graph extraction
- `App` kernel adapter construction
- TODO-007 AuthBuilder work
- public API redesign

## Expected Behavior

Public usage remains stable:

- `Avax::create()`
- `BootDsl::create()`
- `App::get()`, `post()`, `run()`, and `handle()`
- controller result normalization
- route dispatch behavior

## Design Classification

- HLD: SOUND for Slice A
- LLD: SOUND for Slice A
- OOP: real ownership extraction, not wrapper theater
- cohesion: improved because assembly sits in one configuration owner
- coupling: improved because `RunApplication` no longer knows the concrete default graph
- public API compatibility: preserved
- remaining status: TODO_PARTIAL_WITH_YELLOW
