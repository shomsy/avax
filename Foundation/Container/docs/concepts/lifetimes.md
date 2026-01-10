# Lifetimes

## Technical Explanation (Summary)

In this container, a *lifetime* describes **how long a resolved service instance should live** and **when a new instance should be created**. Lifetime is a policy that sits between “definition” (what the service is) and “resolution” (how you build it). It matters because it controls:

- **Identity**: do you get the same instance back or a fresh one?
- **Resource management**: do expensive objects stick around or get released?
- **Correctness**: do stateful services accidentally leak state across requests?

Lifetimes are enforced during resolution by combining:

- A **lifetime declaration** (typically via `ServiceLifetime`)
- A **lifecycle resolver/registry** that selects a strategy
- A **scope-aware cache** that stores or retrieves instances when appropriate

You should think of lifetimes as “the rules of reuse”.

### For Humans: What This Means (Summary)

Lifetime is the container’s memory. It decides whether the container should say “Here, take the one you already had” or “Here, I made you a new one”.

## The Three Common Lifetimes

### Technical Explanation (Common Types)

The common set is:

- **Singleton**: one instance for the container (or for the app lifetime)
- **Scoped**: one instance per scope (often “per request”, “per job”, “per unit of work”)
- **Transient**: a new instance every time

These are typically represented by `Features/Core/Enum/ServiceLifetime.php` and implemented by strategy classes.

Relevant implementations:

- `Core/Kernel/Strategies/SingletonLifecycleStrategy.php`
- `Core/Kernel/Strategies/ScopedLifecycleStrategy.php`
- `Core/Kernel/Strategies/TransientLifecycleStrategy.php`

### For Humans: What This Means (Common Types)

Singleton is “one for everyone”. Scoped is “one per group”. Transient is “always new”.

## Where Lifetimes Actually Get Enforced

### Technical Explanation (Enforcement)

Declaring a lifetime isn’t enough — enforcement happens during the resolution flow:

- The kernel/pipeline decides whether an instance can be **retrieved from a cache**.
- If it can’t, the pipeline **instantiates** and then may **store** the result based on the chosen lifetime.

The enforcement points you’ll typically see:

- Strategy selection: `Core/Kernel/LifecycleResolver.php`, `Core/Kernel/LifecycleStrategyRegistry.php`
- Scope interaction: `Features/Operate/Scope/ScopeManager.php`, `Features/Operate/Scope/ScopeRegistry.php`
- Cache-like behavior in flow: `Core/Kernel/Steps/RetrieveFromScopeStep.php`, `Core/Kernel/Steps/StoreLifecycleStep.php`

### For Humans: What This Means (Enforcement)

Lifetime isn’t a label you stick on a service. It’s a behavior the resolution engine actively applies: “try reuse first” or “always rebuild”.

## Risks, Trade-offs & Recommended Practices

### Technical Explanation (Risks)

- **Risk: accidental shared state (Singleton misuse)**  
  *Why it matters*: mutable state leaks across unrelated operations.  
  *Recommended practice*: keep singletons stateless or explicitly thread-safe; use scoped for stateful “request-ish” services.

- **Risk: scope leaks (Scoped misuse)**  
  *Why it matters*: instances never get released, causing memory/resource leaks.  
  *Recommended practice*: pair scope creation and termination (see `Features/Operate/Shutdown/TerminateContainer.php` and scope APIs).

- **Risk: performance overhead (Transient overuse)**  
  *Why it matters*: expensive object graphs get rebuilt too often.  
  *Recommended practice*: transient for cheap/pure services, scoped for “work units”, singleton for shared infrastructure.

### For Humans: What This Means (Risks)

Lifetimes are a balancing act. Pick the wrong one and you either leak state, leak memory, or burn CPU rebuilding the same stuff.

## Related Files & Jump Links

- `ServiceLifetime`: `../Features/Core/Enum/ServiceLifetime.md`
- Strategy registry/resolver: `../Core/Kernel/LifecycleStrategyRegistry.md`, `../Core/Kernel/LifecycleResolver.md`
- Strategy implementations: `../Core/Kernel/Strategies/SingletonLifecycleStrategy.md`, `../Core/Kernel/Strategies/ScopedLifecycleStrategy.md`, `../Core/Kernel/Strategies/TransientLifecycleStrategy.md`
- Scope management: `../Features/Operate/Scope/ScopeManager.md`, `../Features/Operate/Scope/ScopeRegistry.md`

### For Humans: What This Means (Links)

If you want the “exact rules” used at runtime, jump to the resolver/strategy docs. If you want “where instances get reused or stored”, jump to the kernel steps.
