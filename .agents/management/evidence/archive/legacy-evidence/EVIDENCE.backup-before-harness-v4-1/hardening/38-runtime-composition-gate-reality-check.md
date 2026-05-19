# V5.8.8 Runtime Composition Gate Reality Check

## Date

2026-05-15

## Gate: check-runtime-composition-leaks.php

### Result: FAIL (163 findings)

### V5.8.7 New Findings (3)

| Finding                            | File                        | Pattern                      |          Active runtime? | Gate result | Correct classification          | Blocks V5.9? | Fix/proof                           |
|------------------------------------|-----------------------------|------------------------------|-------------------------:|-------------|---------------------------------|-------------:|-------------------------------------|
| Resolver instantiation (line 92)   | DispatchConfiguredRoute.php | `new ControllerResolver()`   | YES — V4 App API runtime | MEDIUM      | Active runtime composition leak |          YES | Resolve through container or inject |
| Resolver instantiation (line 93)   | DispatchConfiguredRoute.php | `new ArgumentResolver()`     | YES — V4 App API runtime | MEDIUM      | Active runtime composition leak |          YES | Resolve through container or inject |
| Dispatcher instantiation (line 99) | DispatchConfiguredRoute.php | `new ControllerDispatcher()` | YES — V4 App API runtime | MEDIUM      | Active runtime composition leak |          YES | Resolve through container or inject |

### Pre-existing Findings (160)

All other findings pre-exist V5.8.8. They span:

- **class_exists runtime discovery** — ~70 findings across Container, Database, Cache, API, Resilience, Queue,
  Observability, FailureBoundary components
- **null-coalescing new fallbacks** — ~25 findings across Database, Cache, API, Resilience, Tasks, Session components
- **builder build() in runtime** — ~15 findings across API, CallableSerialization, Cache, Delivery components
- **lazy singleton composition** — ~20 findings across Container, Auth, Queue, Cache components
- **registry instantiation in runtime** — ~15 findings across Events, Queue, RuntimeSupervision, Pipeline, API
  components
- **middleware instantiation in runtime** — ~10 findings across ObjectStorage, AppKernel, Database components

### Classification

All pre-existing findings are:

- **NOT introduced by V5.8.8**
- **Pre-existing architecture debt** across many components
- **Outside V5.8.8 scope** (PHPStan/gate/truth integrity closure)
- **Documented in prior passes** as known runtime composition patterns

### V5.9 Blocking Decision

**3 new DispatchConfiguredRoute findings BLOCK V5.9** — these are active runtime composition leaks in the V4 App API
dispatch path.

Pre-existing findings DO NOT individually block V5.9 but collectively represent runtime composition debt that should be
addressed in a dedicated pass.

## Gate: check-component-runtime-assembly.php

### Result: FAIL (3 violations)

| Finding                               | File                 | Pattern                                                                  |              Active runtime? | Gate result | Correct classification          | Blocks V5.9? | Fix/proof                          |
|---------------------------------------|----------------------|--------------------------------------------------------------------------|-----------------------------:|-------------|---------------------------------|-------------:|------------------------------------|
| Null-coalescing new: fieldAssembler   | GraphQLSchema.php:52 | `$this->fieldAssembler = $fieldAssembler ?? new AssembleFieldsFromMap()` | YES — GraphQL public surface | FORBIDDEN   | Pre-existing assembly violation |           NO | Pre-existing, outside V5.8.8 scope |
| Null-coalescing new: schemaSerializer | GraphQLSchema.php:53 | `$this->schemaSerializer = $schemaSerializer ?? new SchemaToArray()`     | YES — GraphQL public surface | FORBIDDEN   | Pre-existing assembly violation |           NO | Pre-existing, outside V5.8.8 scope |
| Null-coalescing new: schemaRouter     | GraphQLSchema.php:54 | `$this->schemaRouter = $schemaRouter ?? new SchemaRouter()`              | YES — GraphQL public surface | FORBIDDEN   | Pre-existing assembly violation |           NO | Pre-existing, outside V5.8.8 scope |

All 3 violations are pre-existing in the GraphQL component. They are not new to V5.8.8.
