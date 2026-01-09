# ScopedLifecycleStrategy

## Quick Summary
Caches service instances within a scope using `ScopeManager`, clearing them when the scope ends. It exists to implement scoped lifetime semantics.

### For Humans: What This Means
It keeps one instance per scope (e.g., per request) and drops it when the scope is over.

## Terminology
- **Scoped**: Lifetime limited to a defined scope boundary.
- **ScopeManager**: Manages scoped storage and lifecycle.
- **End scope**: Point at which scoped instances are cleared.

### For Humans: What This Means
Scoped means per-request/per-unit lifetime; ScopeManager tracks it; end scope is when you wipe them.

## Think of It
Like a hotel room key: valid for your stay (scope), discarded at checkout. ScopeManager is the front desk storing room assignments.

### For Humans: What This Means
You get a temporary key for the duration, then it’s invalidated.

## Story Example
In a web app, each request starts a scope. Services marked scoped are stored via `setScoped`; subsequent resolutions reuse them. At request end, `endScope()` clears them.

### For Humans: What This Means
During a request you reuse the same scoped services; after the request they’re gone.

## For Dummies
- `store` saves instance in current scope.
- `has` checks scoped storage.
- `retrieve` returns scoped instance.
- `clear` calls `endScope()` to drop scoped instances.

Common misconceptions: it’s not global caching; clear is required at scope end; scopes must be defined by the app.

### For Humans: What This Means
It’s per-scope storage; you must end the scope to clean up.

## How It Works (Technical)
Uses `ScopeManager::setScoped`, `has`, `get`, and `endScope` to manage scoped instances. Implements `LifecycleStrategy`.

### For Humans: What This Means
Stores/retrieves via ScopeManager and wipes them when told the scope ended.

## Architecture Role
Implements scoped lifetime for the kernel. Depends on `ScopeManager`; used when services are tagged scoped.

### For Humans: What This Means
It’s the policy the kernel uses when you mark a service scoped.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ScopeManager $scopeManager)

#### Technical Explanation
Injects ScopeManager for scoped storage.

##### For Humans: What This Means
Holds the storage that tracks scoped instances.

##### Parameters
- `ScopeManager $scopeManager`: Scoped storage manager.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Constructed by container when setting up scoped strategy.

##### Common Mistakes
Using a ScopeManager not properly scoped per request/unit.

### Method: store(string $abstract, mixed $instance): void

#### Technical Explanation
Stores the instance in the current scope using `setScoped`.

##### For Humans: What This Means
Puts the scoped instance away for this scope.

##### Parameters
- `string $abstract`: Service ID.
- `mixed $instance`: Instance to cache.

##### Returns
- `void`

##### Throws
- ScopeManager errors.

##### When to Use It
After constructing a scoped service.

##### Common Mistakes
Forgetting to store leads to extra construction within a scope.

### Method: has(string $abstract): bool

#### Technical Explanation
Checks if scoped storage contains the service ID.

##### For Humans: What This Means
Asks if this scope already has the service.

##### Parameters
- `string $abstract`: Service ID.

##### Returns
- `bool`: True if present in this scope.

##### Throws
- ScopeManager errors.

##### When to Use It
Before constructing within a scope.

##### Common Mistakes
Assuming global availability; it’s per-scope.

### Method: retrieve(string $abstract): mixed

#### Technical Explanation
Returns the scoped instance from `ScopeManager` or null if absent.

##### For Humans: What This Means
Gets the scoped service if it was stored.

##### Parameters
- `string $abstract`: Service ID.

##### Returns
- `mixed`: Scoped instance or null.

##### Throws
- ScopeManager errors.

##### When to Use It
When resolving a scoped service inside a scope.

##### Common Mistakes
Expecting a value outside an active scope.

### Method: clear(): void

#### Technical Explanation
Ends the current scope via `endScope()`, clearing scoped instances.

##### For Humans: What This Means
Clears all scoped services at scope end.

##### Parameters
- None.

##### Returns
- `void`

##### Throws
- ScopeManager errors if cleanup fails.

##### When to Use It
At scope boundaries (e.g., end of request/unit of work).

##### Common Mistakes
Not calling clear at scope end, causing leaks across requests.

## Risks, Trade-offs & Recommended Practices
- **Risk: Scope leaks**. Forgetting `clear` bleeds instances across scopes.
- **Risk: Mis-scoping**. Using scoped strategy without proper scope boundaries breaks isolation.
- **Practice: Automate scope management**. Tie `endScope()` to middleware/request lifecycle.

### For Humans: What This Means
Always end scopes automatically, otherwise scoped services may leak.

## Related Files & Folders
- `docs_md/Core/Kernel/Strategies/index.md`: Strategies overview.
- `docs_md/Core/Kernel/Contracts/LifecycleStrategy.md`: Contract implemented here.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Scope storage behavior.

### For Humans: What This Means
See overview/contract for rules and ScopeManager for how scoping works.
