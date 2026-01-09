# ScopeManager

## Quick Summary
- This file provides a fluent, minimal façade over the underlying scope registry.
- It exists so you can manipulate scoped instances without exposing registry internals.
- It removes the complexity of dealing with raw scope storage by offering a small, consistent API.

### For Humans: What This Means
It’s the “remote control” for scoped instances: check, get, set, begin/end, terminate.

## Terminology (MANDATORY, EXPANSIVE)
- **Scope**: A boundary within which scoped services are shared.
  - In this file: begin/end/terminate define that boundary.
  - Why it matters: scopes prevent global singletons while still enabling reuse.
- **Scope registry**: The storage mechanism holding scoped instances.
  - In this file: represented by `ScopeRegistry`.
  - Why it matters: it owns the actual storage and lifecycle.
- **Terminate**: A hard cleanup that clears scope state completely.
  - In this file: `terminate()` delegates to the registry.
  - Why it matters: it prevents leaked instances across lifecycles.
- **Facade/wrapper**: A class that forwards calls to a deeper system.
  - In this file: `ScopeManager` forwards to `ScopeRegistry`.
  - Why it matters: it keeps your public API stable and simple.

### For Humans: What This Means
The manager is the simple interface you use; the registry is the “engine” you don’t want to think about every day.

## Think of It
Think of a scope like a “session box” where you store and reuse items for a while. `ScopeManager` is the handle on the box: you open it, put things in, take things out, and eventually throw the box away.

### For Humans: What This Means
It’s a short-lived storage container that helps you reuse the right things and discard them later.

## Story Example
During an HTTP request, you begin a scope. Your container resolves a `RequestContext` service as scoped, so the same instance is reused during the request. At the end, you call `endScope()` and then `terminate()` during shutdown. Scoped state is cleared, and the next request starts fresh.

### For Humans: What This Means
You don’t accidentally share “this request’s user” with the next request.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Use `beginScope()` at the start of a lifecycle.
- Use `endScope()` at the end of that lifecycle.
- Use `terminate()` when you’re fully shutting down or want a hard reset.

Beginner FAQ:
- *What’s the difference between `endScope()` and `terminate()`?* `endScope()` ends the current scope; `terminate()` wipes everything.

## How It Works (Technical)
This class is a `final readonly` wrapper around `ScopeRegistry`. Every method is a simple delegation to the corresponding registry operation. It exists to provide a stable API surface and to keep the registry swappable or evolvable.

### For Humans: What This Means
It’s intentionally simple: it just forwards calls to the real storage layer.

## Architecture Role
- Why it lives in this folder: it’s runtime scope management.
- What depends on it: shutdown actions (`TerminateContainer`) and any scoped-lifetime resolution steps.
- What it depends on: `ScopeRegistry`.
- System-level reasoning: a façade reduces coupling and keeps call sites clean.

### For Humans: What This Means
You get a neat API today, and the internals can change tomorrow without breaking you.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Wraps a `ScopeRegistry` instance that performs actual storage and lifecycle operations.

##### For Humans: What This Means
You’re plugging the manager into the real scope storage.

##### Parameters
- `ScopeRegistry $registry`: The underlying scope storage.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Constructed during container boot.

##### Common Mistakes
- Passing the wrong registry instance across different container lifecycles.

### Method: has(…)

#### Technical Explanation
Checks whether an instance is stored for a given service id in the current scope.

##### For Humans: What This Means
It’s “did we already create this scoped service?”

##### Parameters
- `string $abstract`: Service id.

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Debugging and conditional flows.

##### Common Mistakes
- Using `has()` to decide correctness of scope lifecycle; it only checks storage.

### Method: get(…)

#### Technical Explanation
Retrieves a stored instance for an id from the scope registry.

##### For Humans: What This Means
It fetches the “already-created scoped object”.

##### Parameters
- `string $abstract`

##### Returns
- `mixed`

##### Throws
- Depends on registry behavior (not expressed here).

##### When to Use It
- When a resolution step wants to retrieve from scope.

##### Common Mistakes
- Calling `get()` before `beginScope()` if your registry requires an active scope.

### Method: set(…)

#### Technical Explanation
Stores an instance under a service id in the scope registry.

##### For Humans: What This Means
It saves a scoped object so later resolutions reuse it.

##### Parameters
- `string $abstract`
- `mixed $instance`

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- After creating a scoped instance.

##### Common Mistakes
- Storing instances that should be transient, accidentally making them scoped.

### Method: instance(…)

#### Technical Explanation
Alias for `set()` that communicates intent (“store this instance”).

##### For Humans: What This Means
It’s the same as `set()`, just more readable in some code.

##### Parameters
- `string $abstract`
- `mixed $instance`

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- When your code reads better as “instance(…)”.

##### Common Mistakes
- Assuming it registers a definition; it only stores a runtime instance.

### Method: setScoped(…)

#### Technical Explanation
Delegates to a specialized “set scoped” operation in the registry.

##### For Humans: What This Means
It stores an instance in a way that’s explicitly scoped (depending on registry semantics).

##### Parameters
- `string $abstract`
- `mixed $instance`

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- When the registry differentiates between “normal set” and “scoped set”.

##### Common Mistakes
- Mixing `set()` and `setScoped()` without understanding the registry’s rules.

### Method: beginScope(…)

#### Technical Explanation
Starts a new scope boundary in the registry.

##### For Humans: What This Means
You’re saying “from now on, scoped services belong to this lifecycle.”

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- At the start of request/job processing.

##### Common Mistakes
- Forgetting to begin a scope, making scoped services behave unexpectedly.

### Method: endScope(…)

#### Technical Explanation
Ends the current scope boundary.

##### For Humans: What This Means
You’re closing the lifecycle boundary.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- At the end of request/job processing.

##### Common Mistakes
- Ending a scope too early, causing scoped instances to disappear mid-lifecycle.

### Method: terminate(…)

#### Technical Explanation
Hard-terminates the scope system and clears stored instances.

##### For Humans: What This Means
It’s the “wipe everything scoped” operation.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- Depends on registry behavior.

##### When to Use It
- On container shutdown or emergency cleanup.

##### Common Mistakes
- Terminating while still handling a request, breaking scoped behavior.

## Risks, Trade-offs & Recommended Practices
- Risk: Incorrect scope boundaries lead to subtle state bugs.
  - Why it matters: scoped services often carry request-specific state.
  - Design stance: make scope boundaries explicit and consistent.
  - Recommended practice: begin/end in one place (middleware/kernel).

### For Humans: What This Means
Scoped services are great, but only if you treat scopes like seatbelts: always buckle and unbuckle the same way.

## Related Files & Folders
- `docs_md/Features/Operate/Shutdown/TerminateContainer.md`: Calls `terminate()` during shutdown.
- `docs_md/Core/Kernel/Steps/RetrieveFromScopeStep.md`: Typically reads scoped instances during resolution.

### For Humans: What This Means
If you want to see where scope is used during resolution, look at the “retrieve from scope” step.

