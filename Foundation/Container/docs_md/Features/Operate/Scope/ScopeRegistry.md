# ScopeRegistry

## Quick Summary
- This file stores resolved instances across singleton and scoped boundaries.
- It exists to implement the runtime storage required by `ServiceLifetime` semantics.
- It removes the complexity of “where do I keep scoped instances?” by providing one dedicated storage engine.

### For Humans: What This Means
This is the actual “place” where the container keeps shared instances.

## Terminology (MANDATORY, EXPANSIVE)
- **Singleton storage**: Instances shared for the lifetime of the container.
  - In this file: `$singletons`.
  - Why it matters: it implements “one instance” reuse.
- **Scope stack**: A stack of scope maps representing nested scopes.
  - In this file: `$scopes` as an array of maps.
  - Why it matters: it supports nested boundaries (if your runtime needs them).
- **Active scope**: The most recent scope on the stack.
  - In this file: accessed via `end($this->scopes)`.
  - Why it matters: scoped get/set targets the current scope.
- **Begin/end scope**: Push/pop operations on the scope stack.
  - In this file: `beginScope()` and `endScope()`.
  - Why it matters: scoped services only make sense inside a boundary.

### For Humans: What This Means
Singletons are “global for this container”. Scopes are “global for this one request/job”.

## Think of It
Think of it like a set of drawers:
- The singleton drawer is always available.
- The scoped drawer exists only while you have an active scope open.

### For Humans: What This Means
You can’t put something into a drawer that isn’t open yet.

## Story Example
At the start of a request, you call `beginScope()`. When the container resolves a scoped service, it stores it in the current scope drawer via `setScoped()`. At the end of the request, you call `endScope()` and the drawer disappears, releasing all scoped instances.

### For Humans: What This Means
You reuse objects during one request, then you throw them away cleanly.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. `set()` stores a singleton.
2. `beginScope()` opens a scoped “layer”.
3. `setScoped()` stores scoped instances into the current layer.
4. `endScope()` closes the current layer.
5. `clear()` or `terminate()` wipes everything.

## How It Works (Technical)
The registry checks the current scope first when reading. If no scope or the id isn’t in scope, it falls back to singleton storage. Scoped writes require an active scope, otherwise `setScoped()` throws. Termination clears both singletons and scopes.

### For Humans: What This Means
Scoped values override singleton values while a scope is active, because the scope is more specific.

## Architecture Role
- Why it lives here: it’s the low-level storage for Operate/Scope.
- What depends on it: `ScopeManager` and any resolution step that stores/retrieves scoped instances.
- What it depends on: basic PHP arrays and lifecycle rules.
- System-level reasoning: clear separation between storage and API keeps scope mechanics stable.

### For Humans: What This Means
This is the engine room. `ScopeManager` is the control panel.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: has(…)

#### Technical Explanation
Returns whether an instance exists either in the current scope (if active) or singleton storage.

##### For Humans: What This Means
“Do we already have this thing stored somewhere?”

##### Parameters
- `string $abstract`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Debugging and conditional checks.

##### Common Mistakes
- Assuming it only checks scoped storage; it checks singleton too.

### Method: get(…)

#### Technical Explanation
Returns the instance from the current scope if present, otherwise from singleton storage, otherwise null.

##### For Humans: What This Means
“Give me the stored instance if we have one.”

##### Parameters
- `string $abstract`

##### Returns
- `mixed`

##### Throws
- No explicit exceptions.

##### When to Use It
- During resolution to reuse existing instances.

##### Common Mistakes
- Expecting it to throw when missing; it returns null.

### Method: set(…)

#### Technical Explanation
Stores a singleton instance under the given id.

##### For Humans: What This Means
Save one instance for the whole container lifecycle.

##### Parameters
- `string $abstract`
- `mixed $instance`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When caching singleton instances.

##### Common Mistakes
- Using it for request-scoped instances; use `setScoped()` instead.

### Method: setScoped(…)

#### Technical Explanation
Stores an instance in the current active scope; throws if no scope is active.

##### For Humans: What This Means
You can only store scoped values while a scope is open.

##### Parameters
- `string $abstract`
- `mixed $instance`

##### Returns
- Returns nothing.

##### Throws
- `RuntimeException`: When no scope is active.

##### When to Use It
- When caching scoped instances during resolution.

##### Common Mistakes
- Forgetting to call `beginScope()` before resolving scoped services.

### Method: beginScope(…)

#### Technical Explanation
Pushes a new empty scope map onto the scope stack.

##### For Humans: What This Means
Open a new scoped “drawer”.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- At the start of a scope (request/job).

##### Common Mistakes
- Opening many nested scopes unintentionally.

### Method: endScope(…)

#### Technical Explanation
Pops the current scope map; throws if there’s no active scope.

##### For Humans: What This Means
Close the current drawer.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- `LogicException`: If you try to end when there’s no active scope.

##### When to Use It
- At the end of a scope (request/job).

##### Common Mistakes
- Ending scopes out of order.

### Method: terminate(…)

#### Technical Explanation
Terminates the scope system by clearing all stored data.

##### For Humans: What This Means
Hard reset.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Container shutdown.

##### Common Mistakes
- Terminating while still in use.

### Method: clear(…)

#### Technical Explanation
Clears singleton and scoped storage and resets the scope stack.

##### For Humans: What This Means
Wipe everything stored.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Memory cleanup or full invalidation.

##### Common Mistakes
- Clearing in the middle of a request and losing expected scoped instances.

## Risks, Trade-offs & Recommended Practices
- Risk: Using scoped storage without clear scope boundaries.
  - Why it matters: you’ll either leak state or lose state unexpectedly.
  - Design stance: scope boundaries must be explicit and centralized.
  - Recommended practice: begin/end scopes in a single lifecycle orchestrator.

### For Humans: What This Means
Scopes are powerful, but only if you open and close them consistently.

## Related Files & Folders
- `docs_md/Features/Operate/Scope/ScopeManager.md`: The façade that wraps this registry.
- `docs_md/Features/Operate/Shutdown/TerminateContainer.md`: Uses termination during shutdown.

### For Humans: What This Means
When debugging scope behavior, check this registry: it’s where the instances actually live.

