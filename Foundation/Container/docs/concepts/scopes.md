# Scopes

## Technical Explanation (Summary)

A *scope* is a **bounded context for instance reuse**. It’s how you get “singleton-like” behavior without turning your
whole container into one giant global cache. Scopes exist to make lifetime rules practical:

- Scoped services should be reused **within** a unit of work
- The same scoped services should be **released** when the unit of work ends

You can treat scopes as explicit “lifecycle brackets” around resolution.

### For Humans: What This Means (Summary)

Scopes are “containers inside your container”. They let you say: “During this operation, reuse these things. After it’s
done, forget them.”

## What Creates and Owns Scopes

### Technical Explanation (Ownership)

Scope orchestration typically lives in the Operate layer:

- Scope APIs and orchestration: `Features/Operate/Scope/ScopeManager.php`
- Tracking and lookup: `Features/Operate/Scope/ScopeRegistry.php`

Scope is then consumed by lifetime strategies and kernel steps:

- Retrieval: `Core/Kernel/Steps/RetrieveFromScopeStep.php`
- Storage: `Core/Kernel/Steps/StoreLifecycleStep.php`
- Lifetime strategy: `Core/Kernel/Strategies/ScopedLifecycleStrategy.php`

### For Humans: What This Means (Ownership)

The ScopeManager is the “scope button” you press: begin/end. The kernel steps are where the button actually changes
behavior (reuse vs. rebuild).

## Nested Scopes (And Why They Exist)

### Technical Explanation (Nesting)

Scopes can be nested to support “sub-operations” inside a larger operation (for example: a job that processes multiple
items, where each item has its own scope). Nesting matters because:

- It avoids accidental cross-item state reuse
- It allows controlled reuse at the correct granularity

In practice, this means scope-aware retrieval should prefer the *nearest active scope* when caching scoped services.

### For Humans: What This Means (Nesting)

Nesting is like folders inside folders. You can have a “project” scope and inside it a “task” scope. You want task state
to stay inside the task.

## Risks, Trade-offs & Recommended Practices

### Technical Explanation (Risks)

- **Risk: forgetting to end a scope**  
  *Impact*: memory leaks and stale state reuse.  
  *Mitigation*: make scope lifecycle explicit in the boot/shutdown flow (see
  `Features/Operate/Shutdown/TerminateContainer.php`).

- **Risk: using scoped services outside their scope**  
  *Impact*: surprising behavior or missing dependencies if the scope is gone.  
  *Mitigation*: keep scoped services “operation-local” and avoid passing them into long-lived singletons.

### For Humans: What This Means (Risks)

Scopes work great — but only if you treat them like “try/finally”: you start them, you always finish them.

## Related Files & Jump Links

- Scope APIs: `../Features/Operate/Scope/ScopeManager.md`, `../Features/Operate/Scope/ScopeRegistry.md`
- Scoped strategy: `../Core/Kernel/Strategies/ScopedLifecycleStrategy.md`
- Scope steps: `../Core/Kernel/Steps/RetrieveFromScopeStep.md`, `../Core/Kernel/Steps/StoreLifecycleStep.md`

### For Humans: What This Means (Links)

If you’re debugging “why did I get a different instance?”, scopes are usually the answer. Jump to the scope steps and
strategy docs.
