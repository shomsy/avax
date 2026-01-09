# Features/Operate/Scope

## What This Folder Represents
This folder manages “scoped lifetimes” at runtime.

Technically, `Features/Operate/Scope` provides the infrastructure that stores and retrieves scoped instances and defines how scopes begin/end/terminate. It is the runtime counterpart to the `ServiceLifetime::Scoped` concept: once a service is marked as scoped, this folder’s components are responsible for ensuring instances are reused within a scope and released at the right time.

### For Humans: What This Means
This is the part that makes “per request” or “per job” lifetimes actually work.

## What Belongs Here
- Runtime registries for scoped instances.
- Convenience wrappers (`ScopeManager`) that expose an easy API.

### For Humans: What This Means
If you ever asked “where does the container keep scoped objects?”, the answer is here.

## What Does NOT Belong Here
- Registration and definition models (those live in `Features/Define`).
- Resolution pipeline steps (those live in `Core/Kernel` / `Features/Actions`).

### For Humans: What This Means
Scope is storage and lifecycle boundaries, not object creation rules.

## How Files Collaborate
`ScopeManager` acts as the friendly façade. Under the hood, it delegates to a registry (such as `ScopeRegistry`) that actually stores instances and controls scope boundaries.

### For Humans: What This Means
You talk to the manager, and the manager talks to the storage engine.

