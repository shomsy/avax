# TODO-006 Slice A Low-Level Design

## Changed Units

- `BuildRunApplication`: assembles the default `RunApplication` pipeline.
- `RunApplication`: receives ready dependencies and executes dispatch.
- `App`: receives a ready `RunApplication` dispatcher.
- `CreateApplication`: builds and passes the dispatcher.
- `BootDslEngine`: builds and passes the dispatcher.
- `V4AppDoesNotDuplicateComponentsTest`: checks the new assembly boundary.

## Before

`App::handle()` called `ensureInitialized()`, which called `RunApplication::withDefaultResolutionPipeline()`. That static factory built the route facade container, controller resolver, argument resolver, request reader, route matcher, and dispatch flow.

## After

`BuildRunApplication::fromDefaultResolutionPipeline()` assembles that graph. `RunApplication` has no static graph factory. `App` has a non-null dispatcher constructor dependency.

## Dependency Flow

`CreateApplication` / `BootDslEngine` -> `BuildRunApplication` -> `RunApplication` -> execute request dispatch.

## Failure Behavior

Missing dispatch dependencies now fail when the app is assembled instead of lazily during request handling.

## Tests

Focused contract and behavior tests must prove:

- public app creation still works
- BootDsl creation still works
- App handle dispatch still works
- `RunApplication` no longer owns `RouteFacadeContainer` assembly

## Static/Governance Checks

- focused PHPUnit
- focused PHPStan
- public-surface gate
- direct-instantiation gate with residual findings classified
- runtime-composition gate with pre-existing findings classified
- namespace drift and governance index/root evidence gates
