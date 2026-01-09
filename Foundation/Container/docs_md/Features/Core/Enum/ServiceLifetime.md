# ServiceLifetime

## Quick Summary
- Defines the lifetime policy for a service: `singleton`, `scoped`, or `transient`.
- Exists to make lifetime configuration type-safe and explicit.
- Drives caching/storage decisions in kernel lifecycle steps and strategies.

### For Humans: What This Means
It’s the container’s “how long should this live?” switch.

## Terminology
- **Singleton**: One shared instance for the container lifetime.
- **Scoped**: One instance per scope (like a request/unit-of-work).
- **Transient**: New instance every time.

### For Humans: What This Means
Singleton is shared forever, scoped is shared within a scope, transient is always new.

## Think of It
Like choosing whether to share a notebook: one notebook for the whole team (singleton), one per meeting (scoped), or one per person every time (transient).

### For Humans: What This Means
Lifetime determines how much you reuse.

## Story Example
A database connection is singleton, a request context is scoped, and a lightweight formatter is transient. The container uses these values to store or rebuild each type appropriately.

### For Humans: What This Means
Different lifetimes match different kinds of services.

## For Dummies
- Choose singleton for safe shared infrastructure.
- Choose scoped for per-request/per-job state.
- Choose transient for always-fresh, lightweight objects.

### For Humans: What This Means
Pick the lifetime based on how much state you want to share.

## How It Works (Technical)
A backed enum of strings. Definitions store the enum; lifecycle resolvers select strategies based on it.

### For Humans: What This Means
It’s a safe set of allowed values that other parts of the container interpret.

## Architecture Role
Used throughout definitions and lifecycle management. It’s a foundational vocabulary type.

### For Humans: What This Means
Many subsystems rely on this one enum to stay consistent about caching.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: Singleton

#### Technical Explanation
Enum case representing singleton lifetime.

##### For Humans: What This Means
One shared instance.

##### Parameters
- None.

##### Returns
- `ServiceLifetime`

##### Throws
- None.

##### When to Use It
For stateless/shared infrastructure.

##### Common Mistakes
Using for mutable stateful services.

### Method: Scoped

#### Technical Explanation
Enum case representing scoped lifetime.

##### For Humans: What This Means
One instance per scope.

##### Parameters
- None.

##### Returns
- `ServiceLifetime`

##### Throws
- None.

##### When to Use It
For request/job state.

##### Common Mistakes
Forgetting to end scopes.

### Method: Transient

#### Technical Explanation
Enum case representing transient lifetime.

##### For Humans: What This Means
Always create a new instance.

##### Parameters
- None.

##### Returns
- `ServiceLifetime`

##### Throws
- None.

##### When to Use It
For lightweight, stateless objects.

##### Common Mistakes
Using for expensive-to-build objects.

## Risks, Trade-offs & Recommended Practices
- **Risk: Wrong lifetime**. Causes state leaks (too shared) or performance issues (too transient).
- **Practice: Treat lifetime as a design choice**. Decide intentionally.

### For Humans: What This Means
If you pick the wrong one, you’ll feel it. Choose carefully.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/StoreLifecycleStep.md`: Stores instances according to lifetime.
- `docs_md/Core/Kernel/Steps/RetrieveFromScopeStep.md`: Reads cached instances.
- `docs_md/Core/Kernel/Strategies/index.md`: Concrete caching behaviors.

### For Humans: What This Means
These kernel pieces are where lifetime becomes real behavior.
