# Features/Actions/Invoke

## What This Folder Represents
This folder contains the callable invocation subsystem: it lets the container call a function/method/closure/invokable object while automatically resolving its parameters from the container and an optional `KernelContext`. It exists because “calling a callable” becomes non-trivial once you want consistent autowiring, overrides, reflection caching, and context-aware circular dependency protection.

### For Humans: What This Means
This is the container’s “smart call” feature. You give it something to call, and it figures out the arguments and calls it safely.

## What Belongs Here
- `InvocationExecutor`: The low-level engine that reflects callables, resolves parameters, and invokes.
- `InvocationContext`: The immutable state container for one invocation.
- `InvokeAction`: The high-level action you call from the rest of the system.
- Reflection caching helpers under `Cache/`.

### For Humans: What This Means
If it helps the container call a callable with autowired arguments, it belongs here.

## What Does NOT Belong Here
- Service resolution (building objects) — that’s `Features/Actions/Resolve`.
- Property/method injection into objects — that’s `Features/Actions/Inject`.
- Kernel pipeline orchestration — that’s `Core/Kernel/*`.

### For Humans: What This Means
This folder is about calling functions/methods, not about building services or injecting into objects.

## How Files Collaborate
`InvokeAction` wires a `InvocationExecutor` once it has a container. The executor normalizes targets (like `Class@method`), creates reflection objects, builds `ParameterPrototype`s, asks `DependencyResolverInterface` to resolve arguments, and finally invokes the callable. `InvocationContext` carries the state through this process, and `ReflectionCache` avoids repeated reflection.

### For Humans: What This Means
InvokeAction is the friendly entry point, InvocationExecutor does the real work, InvocationContext carries state, and ReflectionCache keeps it fast.
