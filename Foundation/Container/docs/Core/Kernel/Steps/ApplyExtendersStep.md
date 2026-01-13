# ApplyExtendersStep

## Quick Summary

- Runs after a service has been instantiated and applies user-provided extenders from the definition store.
- Gives extenders access to the container via the current scope so they can wrap or decorate the instance.
- Records extender counts and timing metadata to help diagnostics.

### For Humans: What This Means (Summary)

It lets you hook into the resolution just after creation to decorate or replace services in a controlled, documented
way.

## Terminology (MANDATORY, EXPANSIVE)- **Extenders

**: Callables registered per service that receive the created instance (and optionally the container) and can return a
modified instance.

- **DefinitionStore**: The registry holding extender callables and other definition metadata.
- **ScopeManager**: Provides scoped access to the container so extenders can resolve dependencies safely.
- **Context metadata (`extenders.*`)**: Tracks how many extenders ran and when.

### For Humans: What This Means

Extenders are like decorators; DefinitionStore is where they live; ScopeManager lets them reach the container; metadata
notes their execution.

## Think of It

Imagine a tailor who adds final touches to a garment after it’s sewn. This step is that tailor—it lets registered
extenders adjust the object before it leaves the factory.

### For Humans: What This Means (Think)

After the container builds an object, the extenders tweak it before it’s handed to your code.

## Story Example

A service needs a proxy to log method calls. You register an extender that wraps the instance after the kernel resolves
it. `ApplyExtendersStep` detects the extender, invokes it with the fresh instance and container, and replaces the
context instance with the proxy.

### For Humans: What This Means (Story)

You can add cross-cutting behavior without messing with the class—just register an extender and let the step apply the
proxy for you.

## For Dummies

1. Skip if there’s no instance or an injection target flag is set.
2. Look up extenders for the current service ID from `DefinitionStore`.
3. Resolve the container from `ScopeManager` if available.
4. Run each extender with the instance (and container when provided); if it returns a new instance, replace the one in
   context.
5. Store metadata about extender count and completion time.

Common misconceptions: extenders run before dependencies are injected—they run after, during this step. They can return
null to leave the instance untouched.

### For Humans: What This Means (Dummies)

It only touches the object after injections, and returning null keeps the original.

## How It Works (Technical)

`__invoke` checks instance presence, fetches extenders, optionally grabs the container from the current scope, runs each
extender, overwrites the instance if an extender returns a replacement, and updates metadata with counts and timestamps.

### For Humans: What This Means (How)

It loops through your decorators, gives them the instance, and lets them replace it while recording what happened.

## Architecture Role

Sits near the end of the kernel pipeline to apply post-construction decorators defined by service definitions. Depends
on `DefinitionStore` for extender metadata and `ScopeManager` for container access, and feeds metadata into diagnostics
or logging.

### For Humans: What This Means (Role)

It’s the final customization step before the resolved service reaches your code.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Fetches extenders for the service, resolves the container from the scope if present, invokes each extender with the
current instance and optional container, updates the context instance when an extender returns a result, and records
metadata about extender execution.

##### For Humans: What This Means (__invoke)

It runs the registered decorators, swaps the instance if needed, and notes how many ran.

##### Parameters (__invoke)

- `KernelContext $context`: Shared state carrying the resolved instance and metadata.

##### Returns (__invoke)

- `void`: Mutates the context to carry the possibly replaced instance.

##### Throws (__invoke)

- Exceptions thrown by extenders propagate through the pipeline.

##### When to Use It (__invoke)

Executed automatically after service creation; you indirectly trigger it by registering extenders.

##### Common Mistakes (__invoke)

- Assuming extenders run before dependencies are injected (they run after).
- Not checking for null returns; returning null leaves the original instance untouched.

## Risks, Trade-offs & Recommended Practices

- **Risk: Extender failures**. Unhandled exceptions bubble up and can break resolution; keep extenders resilient.
- **Risk: Container access assumptions**. The container may not always exist in the scope; handle null container
  references gracefully.
- **Practice: Keep extenders idempotent**. Avoid modifying state in ways that can’t be repeated if the pipeline reruns.
- **Practice: Record metadata**. Use the `extenders.*` metadata added here to monitor how many decorators executed.

### For Humans: What This Means (Risks)

Make extenders safe and log-friendly; they run late in the pipeline, so handle absence of container carefully.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Source of extender metadata.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Provides the scope containing the container.

### For Humans: What This Means (Related)

See how the DefinitionStore registers extenders, how ScopeManager exposes the container, and where this step sits in the
pipeline.

### Method: __construct(...)

#### Technical Explanation (__construct)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (__construct)

- See the PHP signature in the source file for exact types and intent.

##### Returns (__construct)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (__construct)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (__construct)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (__construct)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
