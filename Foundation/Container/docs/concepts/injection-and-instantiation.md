# Injection and Instantiation

## Technical Explanation (Summary)

Instantiation and injection are two different jobs:

- **Instantiation**: creating the object (constructor + arguments)
- **Injection**: supplying additional dependencies after the object exists (properties, setters, post-construct hooks)

This split exists because it gives you flexibility:

- You can instantiate an object without injecting (for inspection, prototyping, or special flows)
- You can inject into an existing instance (for late wiring, decorators, or external objects)

In this codebase, instantiation and injection are implemented as reusable actions, often used by kernel steps.

### For Humans: What This Means (Summary)

Instantiation is “building the car”. Injection is “installing the GPS, radio, and safety systems after the car is
built”.

## The Main Actors You’ll Encounter

### Technical Explanation (Actors)

Common implementation points:

- Instantiation: `Features/Actions/Instantiate/Instantiator.php`
- Constructor/parameter resolution: `Features/Actions/Resolve/Resolvers/*`,
  `Features/Actions/Resolve/ParameterResolutionChain.php`, `Features/Actions/Resolve/TypeResolver.php`
- Property injection: `Features/Actions/Inject/PropertyInjector.php`
- Dependency injection orchestration: `Features/Actions/Inject/InjectDependencies.php`
- Invocation (methods, post-construct): `Features/Actions/Invoke/InvocationExecutor.php`,
  `Features/Actions/Invoke/InvokeAction.php`

Kernel step entry points often include:

- `Core/Kernel/Steps/InstantiateStep.php`
- `Core/Kernel/Steps/InjectDependenciesStep.php`
- `Core/Kernel/Steps/InvokePostConstructStep.php`

### For Humans: What This Means (Actors)

If you want to understand “how does the container actually fill in the blanks?”, these are the classes that do the
hands-on work.

## Why the Container Separates These Phases

### Technical Explanation (Rationale)

Separating phases lets the container:

- Fail early with clearer errors (parameter resolution vs. injection)
- Cache expensive reflection analysis independently
- Provide inspection and diagnostics without mutating state

You can see this split reflected in the action folders: instantiate, inject, invoke, resolve.

### For Humans: What This Means (Rationale)

When something fails, you don’t want “it didn’t work”. You want “it failed to pick constructor parameters” vs. “it
failed to inject properties”. The phase split gives you that clarity.

## Risks, Trade-offs & Recommended Practices

### Technical Explanation (Risks)

- **Risk: hidden work during injection**  
  *Impact*: surprising side effects if setters do heavy logic.  
  *Mitigation*: keep injection targets lightweight; reserve heavy work for explicit boot/run stages.

- **Trade-off: more moving parts**  
  *Impact*: more classes and more steps to understand.  
  *Mitigation*: use the concept docs (like this one) plus the kernel flow docs to keep a stable mental model.

### For Humans: What This Means (Risks)

Yes, it’s more “pieces”. But the pieces are simpler. That’s the point: lots of small Lego bricks instead of one giant
mystery blob.

## Related Files & Jump Links

- Instantiate: `../Features/Actions/Instantiate/Instantiator.md`
- Inject: `../Features/Actions/Inject/InjectDependencies.md`, `../Features/Actions/Inject/PropertyInjector.md`
- Invoke: `../Features/Actions/Invoke/InvocationExecutor.md`, `../Features/Actions/Invoke/InvokeAction.md`
- Core steps: `../Core/Kernel/Steps/index.md`

### For Humans: What This Means (Links)

If you’re working on constructor wiring, live in the Resolve + Instantiate docs. If you’re working on property/setter
wiring, live in Inject + Invoke docs.
