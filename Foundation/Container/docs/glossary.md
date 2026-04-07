# Glossary

- `Flow`: a system behavior entry such as `ResolveService` or `BootProviders`
- `Sub-flow`: a recognizable inner part of a flow when that extra level reduces noise
- `Step`: one ordered piece inside a pipeline or other explicit sequence
- `Capability`: a shared runtime ability used by flows
- `Configuration`: assembly and wiring of the container runtime
- `Foundation`: tiny neutral primitives with no stronger home
- `Facade`: one stable public entry that hides multiple internals
- `Coordinator`: owner unit that directs multiple collaborators without implying a strict transform pipeline
- `Pipeline`: ordered sequence where each step transforms the previous state
- `Port`: contract boundary to the outside world
- `Definition`: stored binding rule for a service identifier
- `Prototype`: analyzed reflection model used to avoid repeating discovery work
- `Scope`: isolated runtime storage lane for scoped instances
- `Lifetime`: reuse rule such as singleton, scoped, or transient
- `Resolution`: turning a requested identifier into a value or object
- `Injection`: filling properties or methods after an object exists
- `Invocation`: executing a callable with container-managed arguments
