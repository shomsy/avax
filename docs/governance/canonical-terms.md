# AvaX Canonical Term Registry

## Status

NORMATIVE — source of truth for naming decisions.

## Rules

- One concept MUST have one canonical name.
- Allowed aliases are permitted only for backward compatibility.
- Forbidden aliases MUST NOT be used in new code.

## Registry

| Canonical term | Meaning | Allowed aliases | Forbidden aliases | Notes |
|---|---|---|---|---|
| Response | Concrete PSR-7 response object / produced result | none unless public compatibility needs it | ResponseFactory as object name | Response is the thing |
| CreateHttpResponse | Internal response creation capability | none | ResponseFactory as canonical implementation | Machine that creates Response |
| Responses | PublicSurface facade / PSR-17 adapter | ResponseFactoryInterface binding if intended | Response as facade | Public API |
| ServiceProvider | Component registration owner | Provider only if external standard requires it | Manager, Service | Registers dependencies |
| Configuration/Builders | Assembly builders | Assemble, Build when exact | runtime factory | Assembles system |
| Runtime | Execution lifecycle | Worker, Kernel only when precise | App when meaning differs | Runtime executes |
| EventEmitter | Emits events | EventDispatcher only if mapped | EventBus unless separate concept | Choose one meaning |
| EventDispatcher | Dispatches events to listeners | EventBus only for PSR-14 compat | EventEmitter if meaning differs | Dispatches |
| CompiledListenerRegistry | Frozen listener map | none | ListenerRegistry when meaning differs | Hot-path dispatch |
| Flow | End-to-end behavior | UseCase only in legacy docs | Process, Handler, Service | One action completes the story |
| Capability | Reusable behavior | Ability | Helper, Util, Manager | Powers multiple flows |
| PublicSurface | Stable public API facade | Entrypoint, Facade | ApiManager, Service | Receives and delegates |
| Fluent API | Immutable builder chain | DSL | mutable setter chain | Call site reads like intent |
| FailureBoundary | Declarative failure handling | ErrorBoundary, ExceptionBoundary | ErrorHandler when meaning differs | Attribute-driven boundary |
| HealthCheck | Readiness/liveness probe | Health | HealthService | Operator-facing |
| DoctorCheck | Deep diagnostic | Diagnostic | Diagnostics folder | Operator-facing |
| Router | Route matching | RouteMatcher | RouteDispatcher when meaning differs | Matches to handler |
| Container | DI service container | none | ContainerInterface for public compat | Owns the object graph |
| Kernel | HTTP/runtime lifecycle | App when precise | Application when meaning differs | Receives request, returns response |
| ResolveCallable | Class-string to invocable resolver | CallableResolver | Resolver when vague | Primary callable resolver |
| Observability | Logs, metrics, traces, audit | Telemetry, Monitoring | O11y abbreviation | Observability plane |
## Migration

When a canonical rename occurs:
1. Old name gets deprecated alias (backward compat)
2. All new code uses canonical name
3. Old name is removed in next major version
4. Evidence file records the migration
