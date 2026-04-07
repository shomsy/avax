# Glossary

- `Flow Entry`: a root file under `DependencyInjection/Flows/` that owns one public action
- `Dependency Area`: a noun-based subfolder under `DependencyInjection/Dependencies/` that owns one internal slice of runtime behavior
- `Service Registration`: one stored rule that says what an abstract resolves to and how long it lives
- `Register For Target`: one target-specific override rule for a consumer/need pair
- `Resolve Request`: one in-flight request for a service id plus overrides and parent chain
- `Service Blueprint`: cached reflection view of constructor, injectable properties, injectable methods, and shared marker
- `Injection Report`: a summary of what a target can receive
- `Scope`: an isolated storage frame for scoped instances
- `Shared Lifetime`: one instance reused across the whole runtime
- `Scoped Lifetime`: one instance reused only inside the current active scope
- `Transient Lifetime`: no reuse; resolve again each time
- `Resolution Policy`: rule set that allows or blocks a resolve request
- `Telemetry`: runtime counters and timeline events emitted by the resolver
- `Clock`: the neutral time source used by observability
