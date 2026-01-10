# InvokePostConstructStep

## Quick Summary
- Executes conventional post-construction lifecycle hooks (`init`, `initialize`, `setup`, `postConstruct`) on the resolved instance.
- Uses `InvokeAction` to invoke methods so invocation behavior stays consistent across the system.
- Records invoked method names and errors into `KernelContext` metadata without necessarily failing resolution.

### For Humans: What This Means (Summary)
After the container builds and injects an object, this step gives it a chance to “finish booting” by calling common init methods if they exist.

## Terminology (MANDATORY, EXPANSIVE)- **Post-construct hook**: A method you call after construction/injection to finalize setup.
- **Conventional method names**: Known names this step looks for (`init`, `initialize`, `setup`, `postConstruct`).
- **InvokeAction**: Centralized invocation mechanism used to call methods/targets.
- **Lifecycle metadata (`lifecycle.*`)**: Context metadata recording which hooks ran and which failed.

### For Humans: What This Means
Post-construct hooks are “finalize yourself” methods. This step looks for common names and calls them using one shared invoker, then writes a record in metadata.

## Think of It
Like starting a car: turning the key creates the engine (construction), but the engine still needs to warm up and run checks (post-construct hooks). This step runs those checks.

### For Humans: What This Means (Think)
It’s the “warm up and finalize” stage right after creation.

## Story Example
A service needs to precompute a cache after its dependencies are injected. The class defines a public `initialize()` method. After resolution, this step detects `initialize`, invokes it, and records that it ran. If it fails, the error is recorded in metadata.

### For Humans: What This Means (Story)
You can add a simple init method and the container will call it for you.

## For Dummies
1. Skip if resolving an injection target or a delegated resolution.
2. Skip if the instance is missing or not an object.
3. Reflect the object.
4. For each known init method name, if a public method exists, invoke it.
5. Record invoked methods and any errors in `lifecycle` metadata.

Common misconceptions:
- “This calls every method.” It only calls a small set of known names.
- “Failure always breaks resolution.” It records errors but may continue.

### For Humans: What This Means (Dummies)
It’s intentionally conservative: only common init names, and errors are captured for diagnostics.

## How It Works (Technical)
Uses `ReflectionClass` on the resolved instance, checks for a set of method names and public visibility, calls them via `InvokeAction`, and stores successful invocations and errors in metadata. Exceptions during reflection are swallowed for safety.

### For Humans: What This Means (How)
It checks “do you have a public init method?” and if yes, calls it and keeps notes.

## Architecture Role
Late pipeline step that supports lifecycle hooks after instantiation and injection. Depends on the invocation subsystem (`InvokeAction`) and records outcomes in metadata.

### For Humans: What This Means (Role)
It’s the final “let the object initialize itself” step.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(InvokeAction $invoker)

#### Technical Explanation (__construct)
Stores the invoker used to call lifecycle hook methods.

##### For Humans: What This Means (__construct)
Keeps the method-calling tool used by this step.

##### Parameters (__construct)
- `InvokeAction $invoker`: Invocation action.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
Constructed by the container when building the pipeline.

##### Common Mistakes (__construct)
Using an invoker that doesn’t respect the project’s invocation conventions.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)
Skips in injection target/delegated modes, checks for object instance, reflects it, and invokes known init methods if public. Records invoked methods and per-method errors in lifecycle metadata.

##### For Humans: What This Means (__invoke)
If your object has a public init method with a known name, it calls it and records what happened.

##### Parameters (__invoke)
- `KernelContext $context`: Contains resolved instance and metadata.

##### Returns (__invoke)
- `void`

##### Throws (__invoke)
- None (errors during invocation are captured into metadata).

##### When to Use It (__invoke)
Automatically invoked late in the resolution pipeline.

##### Common Mistakes (__invoke)
Expecting private/protected hooks to run; only public hooks are invoked.

## Risks, Trade-offs & Recommended Practices
- **Risk: Hidden side effects**. Post-construct hooks can do real work (I/O, caches) and slow resolution; keep them lightweight.
- **Risk: Silent failures**. Because errors are stored in metadata, you must observe them; otherwise failures may go unnoticed.
- **Practice: Prefer explicit interfaces**. For critical initialization, consider explicit lifecycle contracts instead of name conventions.
- **Practice: Monitor `lifecycle.errors`**. Surface and alert on recorded hook failures.

### For Humans: What This Means (Risks)
Don’t put heavy work in init hooks, and don’t ignore failures—this step records them but won’t necessarily stop the world.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Features/Actions/Invoke/Core/InvokeAction.md`: Invoker used here.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: Metadata storage.

### For Humans: What This Means (Related)
Follow the invoker to see how calls happen, and the context docs to see where results are recorded.
