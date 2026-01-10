# ServiceLifetime

## Quick Summary
- Defines the lifetime policy for a service: `singleton`, `scoped`, or `transient`.
- Exists to make lifetime configuration type-safe and explicit.
- Drives caching/storage decisions in kernel lifecycle steps and strategies.

### For Humans: What This Means (Summary)
It’s the container’s “how long should this live?” switch.

## Terminology (MANDATORY, EXPANSIVE)- **Singleton**: One shared instance for the container lifetime.
- **Scoped**: One instance per scope (like a request/unit-of-work).
- **Transient**: New instance every time.

### For Humans: What This Means
Singleton is shared forever, scoped is shared within a scope, transient is always new.

## Think of It
Like choosing whether to share a notebook: one notebook for the whole team (singleton), one per meeting (scoped), or one per person every time (transient).

### For Humans: What This Means (Think)
Lifetime determines how much you reuse.

## Story Example
A database connection is singleton, a request context is scoped, and a lightweight formatter is transient. The container uses these values to store or rebuild each type appropriately.

### For Humans: What This Means (Story)
Different lifetimes match different kinds of services.

## For Dummies
- Choose singleton for safe shared infrastructure.
- Choose scoped for per-request/per-job state.
- Choose transient for always-fresh, lightweight objects.

### For Humans: What This Means (Dummies)
Pick the lifetime based on how much state you want to share.

## How It Works (Technical)
A backed enum of strings. Definitions store the enum; lifecycle resolvers select strategies based on it.

### For Humans: What This Means (How)
It’s a safe set of allowed values that other parts of the container interpret.

## Architecture Role
Used throughout definitions and lifecycle management. It’s a foundational vocabulary type.

### For Humans: What This Means (Role)
Many subsystems rely on this one enum to stay consistent about caching.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: Singleton

#### Technical Explanation (Singleton)
Enum case representing singleton lifetime.

##### For Humans: What This Means (Singleton)
One shared instance.

##### Parameters (Singleton)
- None.

##### Returns (Singleton)
- `ServiceLifetime`

##### Throws (Singleton)
- None.

##### When to Use It (Singleton)
For stateless/shared infrastructure.

##### Common Mistakes (Singleton)
Using for mutable stateful services.

### Method: Scoped

#### Technical Explanation (Scoped)
Enum case representing scoped lifetime.

##### For Humans: What This Means (Scoped)
One instance per scope.

##### Parameters (Scoped)
- None.

##### Returns (Scoped)
- `ServiceLifetime`

##### Throws (Scoped)
- None.

##### When to Use It (Scoped)
For request/job state.

##### Common Mistakes (Scoped)
Forgetting to end scopes.

### Method: Transient

#### Technical Explanation (Transient)
Enum case representing transient lifetime.

##### For Humans: What This Means (Transient)
Always create a new instance.

##### Parameters (Transient)
- None.

##### Returns (Transient)
- `ServiceLifetime`

##### Throws (Transient)
- None.

##### When to Use It (Transient)
For lightweight, stateless objects.

##### Common Mistakes (Transient)
Using for expensive-to-build objects.

## Risks, Trade-offs & Recommended Practices
- **Risk: Wrong lifetime**. Causes state leaks (too shared) or performance issues (too transient).
- **Practice: Treat lifetime as a design choice**. Decide intentionally.

### For Humans: What This Means (Risks)
If you pick the wrong one, you’ll feel it. Choose carefully.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/StoreLifecycleStep.md`: Stores instances according to lifetime.
- `docs_md/Core/Kernel/Steps/RetrieveFromScopeStep.md`: Reads cached instances.
- `docs_md/Core/Kernel/Strategies/index.md`: Concrete caching behaviors.

### For Humans: What This Means (Related)
These kernel pieces are where lifetime becomes real behavior.
