# Glossary

- `Flow Entry`: a root file under `src/Flows/<FlowName>/` that owns one public action
- `Capability Lane`: a noun-based subfolder under `src/Capabilities/` that owns one shared internal work area
- `Service Registration`: one stored rule that says what an abstract resolves to and how long it lives
- `Register For Target`: one target-specific override rule for a consumer/need pair
- `Resolve Request`: one in-flight request for a service id plus overrides and parent chain
- `Service Blueprint`: cached reflection view of constructor, injectable properties, injectable methods, and shared
  marker
- `Injection Report`: a summary of what a target can receive
- `Context View`: a container view created by `forContext()` that feeds scalar named arguments into resolution and
  invocation
- `Deferred Service`: a registration that stays out of the default compile warmup until it is explicitly requested or
  needed by another compiled service
- `Compiled Runtime Artifact`: the generated container file that holds direct hot-path service methods
- `Compiled Metadata`: the JSON sidecar that records checksum, config hash, environment, changed service ids, and
  per-service signatures for the compiled runtime artifact
- `Compile Mode`: the build/runtime policy for compiled artifacts such as `dev`, `ci`, `production`, or `warmup`
- `Diagnostics Mode`: the observability policy for runtime reports and timeline recording, such as `minimal` or
  `detailed`
- `Scope`: an isolated storage frame for scoped instances
- `Shared Lifetime`: one instance reused across the whole runtime
- `Scoped Lifetime`: one instance reused only inside the current active scope
- `Transient Lifetime`: no reuse; resolve again each time
- `Resolution Policy`: rule set that allows or blocks a resolve request
- `Validate`: the diagnostics pass that checks obvious registration and blueprint issues without resolving services
- `Describe Service`: the human-readable service inspection surface
- `Debug Service`: the alias for service inspection when a caller wants explicit debug wording
- `Debug Plan`: the human-readable plan view for a service
- `Debug Tags`: the human-readable tag view for a tag
- `Debug Aliases`: the human-readable alias map view
- `Debug Scope`: the human-readable scope storage snapshot
- `Boot Providers`: the deterministic provider lifecycle that registers then boots providers in dependency order
- `Deferred Provider`: a provider that implements `DeferredProviderInterface` and is booted only when one of its owned
  services is first resolved
- `Benchmark Comparison`: a normalized comparison report between this container's benchmark artifact and one or more
  peer benchmark artifacts
- `Telemetry`: runtime counters and timeline events emitted by the resolver
- `Clock`: the neutral time source used by observability
- `Environment Hook`: a container-owned env lookup such as `Container::env()`
- `Slice View`: a logical container view over the same runtime filtered by a specific slice (flow, capability,
  configuration, or foundation)
- `Root Composition View`: the full container view without filtering; exposes all services
- `Flow Slice`: a slice category for end-to-end system behavior (e.g., 'flow.login')
- `Capability Slice`: a slice category for shared abilities that support multiple flows (e.g., 'capability.payments')
- `Configuration Slice`: a slice category for assembly, composition, setup, and wiring
- `Foundation Slice`: a slice category for low-level primitives and technical atoms
- `Slice Visibility`: the rule set that determines which services a slice can access
- `Export`: a declaration that makes a shared service available to importing slices
- `Import`: a declaration that a slice requires access to a capability or shared service
- `Private Visibility`: service stays inside the owning slice; not accessible cross-slice
- `Internal Visibility`: implementation detail of the owning slice; not accessible cross-slice
- `Shared Visibility`: reusable across slices when explicitly exported and imported
- `Public Visibility`: part of the stable top-level surface
- `Pooled Lifetime`: a service lifetime model where instances are reset and reused from a pool rather than disposed
- `Pool Reset`: the action of clearing a pooled instance's state before returning it for reuse
- `Overflow Strategy`: the policy for handling pool exhaustion (FAIL, EVICT, CREATE_NEW, BLOCK)
- `Pool Statistics`: runtime metrics about pool utilization (hits, misses, resets, evictions)
- `ResettableInterface`: the contract that marks a class as having a safe `reset()` method for pooled reuse
- `Structural Diff`: the comparison between authored service graph and compiled/derived state
- `Dead Registration`: a service that is registered but not reachable from any entry point
- `Duplicate Concept`: multiple services claiming the same logical concept name
- `Impact Analysis`: the diagnostic that shows what would be affected if a service changed
