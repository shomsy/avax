# InjectDependenciesStep

## Quick Summary
- Performs property and method injection on the resolved instance.
- Delegates injection work to the `InjectDependencies` action, passing the `KernelContext` for chain-aware resolution.
- Skips injection for literals, delegated resolutions, and manual injection mode.

### For Humans: What This Means (Summary)
After the container builds an object, this step fills in extra dependencies (properties/method calls) so the object is fully wired.

## Terminology (MANDATORY, EXPANSIVE)- **Property injection**: Setting dependencies directly onto object properties.
- **Method injection**: Calling methods to provide dependencies after construction.
- **InjectDependencies**: Action that performs the actual injection logic.
- **Manual injection**: A context flag that disables automatic injection when you want full control.
- **Delegated resolution**: A mode where another resolver owns the resolution and injection should not be duplicated.

### For Humans: What This Means
Property injection fills fields, method injection calls setter-like methods, InjectDependencies does the work, manual injection means “don’t auto-inject”, delegated means “someone else handled it”.

## Think of It
Like assembling furniture: construction gives you the main frame, but you still need to tighten screws and attach accessories. This step does those finishing attachments.

### For Humans: What This Means (Think)
The constructor gets you the object; this step finishes wiring it.

## Story Example
A service is created with constructor dependencies, but also uses `#[Inject]` attributes on properties. Without this step, those properties remain null. With `InjectDependenciesStep`, the injector runs and fills those properties, then updates the context instance.

### For Humans: What This Means (Story)
It prevents “half-built” services by ensuring your injection attributes actually take effect.

## For Dummies
1. If resolution was delegated or manual injection is enabled, do nothing.
2. If the instance is missing or not an object (literal), do nothing.
3. Call `InjectDependencies->execute(target, context)`.
4. Replace the context instance with the injected instance.
5. Record injection metadata.

Common misconceptions:
- “Injection happens in the constructor.” Not for property/method injection; this step handles it.
- “It runs for scalars.” It only injects into objects.

### For Humans: What This Means (Dummies)
It’s the post-constructor injection stage, and it only applies to real objects.

## How It Works (Technical)
`__invoke` checks flags (`resolution.delegated`, `manualInjection`) and verifies the instance is an object. It then calls the `InjectDependencies` action and overwrites the context instance with the injected result. It writes `inject.performed` and `inject.time` metadata.

### For Humans: What This Means (How)
It runs the injector only when safe and relevant, then saves the updated instance.

## Architecture Role
Sits after instance construction and before finalization steps to ensure the resolved instance is fully wired. Depends on the Actions/Inject subsystem and uses context metadata to coordinate with other steps.

### For Humans: What This Means (Role)
It’s the wiring step that makes sure your object isn’t missing dependency “attachments”.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(InjectDependencies $injector)

#### Technical Explanation (__construct)
Stores the injector action used to perform property and method injection.

##### For Humans: What This Means (__construct)
Keeps a reference to the tool that will do the injection.

##### Parameters (__construct)
- `InjectDependencies $injector`: Injection action.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
Constructed by the container when assembling kernel steps.

##### Common Mistakes (__construct)
Injecting an injector that doesn’t support the project’s injection conventions.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)
Runs injection when resolution isn’t delegated and manual injection is off, and when the instance is an object. Calls injector and overwrites context with injected instance, then records metadata.

##### For Humans: What This Means (__invoke)
If it’s allowed and the instance is an object, it injects dependencies and updates the instance.

##### Parameters (__invoke)
- `KernelContext $context`: Holds instance, flags, and metadata.

##### Returns (__invoke)
- `void`

##### Throws (__invoke)
- `ReflectionException` when reflection-based injection fails.

##### When to Use It (__invoke)
Automatically invoked during service resolution.

##### Common Mistakes (__invoke)
- Forgetting that manual injection disables this step.
- Expecting it to run on literal values.

## Risks, Trade-offs & Recommended Practices
- **Risk: Reflection overhead**. Injection may use reflection; keep it efficient and cache analysis where possible.
- **Risk: Hidden dependencies**. Property injection can hide required dependencies; prefer constructor injection for critical requirements.
- **Practice: Use metadata flags**. Respect `manualInjection` and `resolution.delegated` to avoid double-injecting.

### For Humans: What This Means (Risks)
Injection is powerful but can be slower and less explicit; use it carefully and avoid running it twice.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Injection action invoked here.
- `docs_md/Features/Actions/Inject/PropertyInjector.md`: Likely implementation used by the action.

### For Humans: What This Means (Related)
To understand what gets injected and how, follow the InjectDependencies action and its injector implementation.
