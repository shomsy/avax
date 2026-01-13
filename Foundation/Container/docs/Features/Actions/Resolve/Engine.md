# Engine

## Quick Summary

- Core "Resolution Engine" that deterministically walks a finite-state map (Contextual → Definition → Autowire →
  Evaluate → Instantiate) to turn a service identifier into a real instance.
- Manages precedence across contextual overrides, explicit definitions, and autowiring while recording a full resolution
  trace for diagnostics.
- Coordinates between the `DefinitionStore`, `ResolutionStageHandlerMap`, and `Instantiator` so the rest of the kernel
  only sees a predictable, linear pipeline.

### For Humans: What This Means (Summary)

This is the **Builder Brain** of the container. When you ask for an object, the Engine follows a fixed checklist of
stations, records every stop, and tells the assembly line exactly how to build the right version for you.

## Terminology (MANDATORY, EXPANSIVE)

- **ResolutionStageHandlerMap**: Declarative table that binds each `ResolutionState` to a handler callable.
    - In this file: Built per-resolution to drive the FSM instead of `match` branches.
    - Why it matters: Keeps dispatch deterministic and easy to extend.
- **Discovery Stages**: Contextual, Definition, and Autowire lookups that find a candidate.
    - In this file: Executed in order via the handler map and guarded by `ResolutionPipelineController`.
    - Why it matters: Encodes precedence (specific overrides first, global rules second, autowire last).
- **Evaluation Stage**: Turns a candidate (object/closure/class string/value) into a usable result or delegation.
    - In this file: `evaluateCandidate()` runs before any instantiation.
    - Why it matters: Normalizes all binding types without losing trace data.
- **Instantiation Stage**: Performs the actual construction for class-string candidates.
    - In this file: `instantiateCandidate()` defers to `Instantiator`.
    - Why it matters: Keeps the build step explicit and traceable.
- **Resolution Cycle**: A single FSM-guided walk from request to terminal result, possibly spawning child cycles.
    - In this file: The `resolve()` entry and `resolveFromBindings()` loop.
    - Why it matters: Ensures every service build is observable and repeatable.

### For Humans: What This Means (Terminology)

The Engine follows a **state-to-handler map** to run the discovery stages, then **evaluates** and **instantiates** the
winner. Each run is a **cycle** with breadcrumbs you can inspect later.

## Think of It

Think of a **Custom Pizza Shop**:

- **Customer**: Your application asking for a "Pepperoni Pizza" (Service ID).
- **Engine**: The Lead Chef who checks the order.
- **Contextual Rule**: "This customer has a gluten allergy" (Contextual Binding). The Lead Chef sees this and tells the
  kitchen to use a different crust.
- **Definition Store**: The menu and the secret recipes.
- **Instantiator**: The actual oven and prep area.

### For Humans: What This Means (Analogy)

The Chef (Engine) doesn't just grab any pizza; they look at exactly who is ordering and what the rules are before they
start the oven.

## Story Example

You ask the container for a `PaymentProcessor`. The **Engine** checks the rules. It sees that your application is
currently in "Testing Mode" (managed via a contextual binding). The Engine says: "Normally I'd build the real
CreditCardProcessor, but because we are in testing, I'll build the `MockProcessor` instead." It communicates this to the
`Instantiator`, which builds the mock, and the Engine hands it back to you. Your code never knew the difference—the
Engine handled all the logic.

### For Humans: What This Means (Story)

It allows your application to "Change its mind" about which objects to use based on the environment or situation,
without you having to write complex `if` statements in your code.

## For Dummies

Imagine you're at a library.

1. You give the librarian a book title (Service ID).
2. The librarian checks if someone left a "Special Note" for YOU specifically (Contextual Binding).
3. If not, they check the main catalog (Global Definition).
4. If the book isn't in the catalog but they see it on the "New Arrivals" shelf (Class exists), they mark it to pull
   from the shelf (Autowire), then confirm it’s the right edition (Evaluate) and fetch it (Instantiate).
5. If they find it, they hand it to you. If not, they tell you they can't find it (NotFoundException).

### For Humans: What This Means (Walkthrough)

The Engine is the series of "Checks" that happen between you asking for a service and receiving an object.

## How It Works (Technical)

The `Engine` builds a fresh `ResolutionStageHandlerMap` on every resolution and uses `ResolutionPipelineController` to
enforce the legal path:

1. **Discovery**: Contextual → Definition → Autowire handlers run in order, updating a shared `$candidate` reference and
   recording trace hits/misses.
2. **Evaluation**: If a candidate exists, `evaluateCandidate()` normalizes it (execute closures, delegate to other
   service IDs, or return literals/class strings).
3. **Instantiation**: If the evaluated result is a class string, `instantiateCandidate()` calls the `Instantiator` with
   overrides and context.
4. **Trace + Terminals**: Every stage appends to `ResolutionTrace`; on success the controller advances to `Success`; on
   miss it records `NotFound` and throws `ResolutionExceptionWithTrace`.
5. **Recursion**: Delegations spawn child contexts so nested resolutions keep parent links, depth, and metadata.

### For Humans: What This Means (Technical)

It’s a scripted sequence: search in order, normalize the winner, build it if needed, and write down each step. If
nothing works, it throws an exception carrying the full breadcrumb trail.

## Architecture Role

- **Lives in**: `Features/Actions/Resolve`
- **Role**: Core Resolver Implementation.
- **Primary Collaborators**: `DefinitionStore`, `ScopeRegistry`, `Instantiator`.

### For Humans: What This Means (Architecture)

It is the "Thinking Center" of the resolution process.

## Methods

### Method: setContainer(ContainerInternalInterface $container) {#method-setcontainer}

Technical: Injects the container facade so nested resolutions and instantiations can recurse through the container
safely.

### For Humans: What This Means

Plug the engine into the motherboard so it can call back into the container when building dependencies.

#### Parameters

- `ContainerInternalInterface $container` The container facade used for nested resolutions.

#### Returns

- `void` — the engine just stores the reference.

#### Throws

- `ContainerException` if the container is already set (prevents double-initialization).

#### When to use it

- Immediately after constructing the engine (via builders) so resolution can recurse.

#### Common mistakes

- Calling `resolve()` before this is set; calling it twice; forgetting to initialize related collaborators.

### Method: resolve(KernelContext $context, ?TraceObserverInterface $traceObserver = null) {#method-resolve}

Technical: Entry point that enforces container initialization, then delegates to the FSM-backed binding pipeline,
records trace metadata, and notifies an optional trace observer.

### For Humans: What This Means

This is the “find or build the service” button; it only works once the engine knows its container.

#### Parameters

- `KernelContext $context` Resolution request containing the service ID, overrides, and parent chain.
- `TraceObserverInterface|null $traceObserver` Optional listener to receive the resolution trace.

#### Returns

- `mixed` — the resolved service instance or value.

#### Throws

- `ContainerException` if the container reference is missing;
  `ResolutionException|ResolutionExceptionWithTrace|Throwable` from downstream evaluation/build steps.

#### When to use it

- Any time the kernel needs to resolve a service ID (direct calls or nested dependency resolution).

#### Common mistakes

- Invoking it before wiring internals; assuming it will autowire non-class strings; forgetting to provide a trace
  observer when debugging.

### Method: hasInternals() {#method-hasinternals}

Technical: Indicates whether the engine has a container reference wired and is safe to use.

### For Humans: What This Means

A quick “am I ready?” light for the engine.

#### Parameters

- None.

#### Returns

- `bool` — `true` when the container reference is set.

#### Throws

- None.

#### When to use it

- Guard checks in builders or kernel boot to avoid premature resolution.

#### Common mistakes

- Ignoring this check and hitting the runtime exception in `resolve()`.

### Method: resolveFromBindings(KernelContext $context, ResolutionPipelineController $controller, ResolutionTrace $trace) {#method-resolvefrombindings}

Technical: Builds a per-call handler map, runs discovery stages (contextual/definition/autowire) under FSM control, then
evaluates and instantiates the chosen candidate while recording the entire trace.

### For Humans: What This Means

Checks special rules, normal rules, then autowire; runs the winner through evaluation and construction; logs every stop
so misses return a trace-rich exception.

#### Parameters

- `KernelContext $context` Current resolution request.
- `ResolutionPipelineController $controller` FSM guard enforcing legal transitions.
- `ResolutionTrace $trace` Trace accumulator.

#### Returns

- `mixed` — resolved service instance or value.

#### Throws

- `ResolutionExceptionWithTrace` when no path can satisfy the request; downstream `Throwable` via evaluation/build.

#### When to use it

- Only internally from `resolve()` to keep the resolution order deterministic.

#### Common mistakes

- Expecting a different precedence; forgetting contextual bindings require a parent context.

### Method: resolveContextualBinding(KernelContext $context) {#method-resolvecontextualbinding}

Technical: Checks for a contextual match tied to the requesting parent; returns the evaluated concrete if found,
otherwise null.

### For Humans: What This Means

If the caller has a special exception, honor it; otherwise skip.

#### Parameters

- `KernelContext $context` Current resolution request including parent info.

#### Returns

- `mixed|null` — resolved value when a contextual binding exists; null when absent.

#### Throws

- `Throwable` if evaluating the binding fails.

#### When to use it

- Internally as the first pipeline stage to respect consumer-specific overrides.

#### Common mistakes

- Assuming contextual bindings apply without a parent context; forgetting they bypass global definitions.

### Method: resolveDefinitionBinding(KernelContext $context) {#method-resolvedefinitionbinding}

Technical: Looks up a global definition by service ID and evaluates its concrete when present; otherwise returns null.

### For Humans: What This Means

Use the normal registered rule if one exists; otherwise move on.

#### Parameters

- `KernelContext $context` Current resolution request.

#### Returns

- `mixed|null` — resolved value when a definition exists; null when not.

#### Throws

- `Throwable` if evaluating the definition fails.

#### When to use it

- Internally after contextual bindings to honor registered services.

#### Common mistakes

- Expecting it to autowire; that happens in the next stage.

### Method: resolveAutowireCandidate(KernelContext $context, mixed &$current) {#method-resolveautowirecandidate}

Technical: If no candidate is set and the service ID is a class, returns the class string to defer instantiation;
otherwise returns the existing candidate or null.

### For Humans: What This Means

If nothing matched yet and the ID is a real class, mark it for building later; otherwise keep whatever you already found
or move on.

#### Parameters

- `KernelContext $context` Current resolution request.
- `mixed &$current` Shared candidate reference populated by earlier handlers.

#### Returns

- `mixed|null` — class string when autowire is possible; prior candidate; or null when autowire isn’t an option.

#### Throws

- None directly (construction deferred).

#### When to use it

- Internally as the last discovery stage before evaluation.

#### Common mistakes

- Expecting it to build immediately; it only marks the class for the instantiate stage.

### Method: evaluateCandidate(mixed $candidate, KernelContext $context) {#method-evaluatecandidate}

Technical: Normalizes the candidate—returns objects/values as-is, executes closures with container/overrides, delegates
to other service IDs, or passes class strings through for instantiation.

### For Humans: What This Means

Turn whatever you found into something usable: run a factory, delegate to another service, or keep the class name ready
for building.

#### Parameters

- `mixed $candidate` Concrete definition (object, closure, class string, scalar).
- `KernelContext $context` Current resolution request/overrides.

#### Returns

- `mixed` — resolved instance, delegated result, literal value, or class string.

#### Throws

- `Throwable` when closure execution or nested resolution fails.

#### When to use it

- Internally after discovery succeeded to normalize the winner.

#### Common mistakes

- Forgetting closures receive the container and overrides; delegating to the same service ID causing recursion without
  safeguards.

### Method: instantiateCandidate(mixed $candidate, KernelContext $context) {#method-instantiatecandidate}

Technical: Builds the service when the evaluated candidate is a class string; otherwise returns the candidate unchanged.

### For Humans: What This Means

If you still have a class name, build it now; if it’s already an object/value, just hand it back.

#### Parameters

- `mixed $candidate` Evaluated candidate from the previous stage.
- `KernelContext $context` Current resolution request/overrides.

#### Returns

- `mixed` — instantiated object or the untouched candidate.

#### Throws

- `Throwable` when instantiation fails.

#### When to use it

- Internally right after evaluation, before declaring success.

#### Common mistakes

- Assuming every candidate is a class string; literals and already-built objects bypass construction.

### Method: recordTrace(?TraceObserverInterface $observer, ResolutionTrace $trace) {#method-recordtrace}

Technical: Emits the accumulated trace to an observer when present.

### For Humans: What This Means

If someone is listening, hand them the breadcrumb trail.

#### Parameters

- `TraceObserverInterface|null $observer` Optional trace sink.
- `ResolutionTrace $trace` Completed trace for this resolution.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Internally after resolution completes or fails.

#### Common mistakes

- Forgetting to pass an observer when you need external trace capture.

## Risks & Trade-offs

- **Performance**: Deeply nested resolution trees (A needs B needs C needs D...) can be slow. Use Singletons where
  possible to cache results.
- **Ambiguity**: If you have a class named `Logger` and a binding named `Logger`, the binding takes priority. This can
  sometimes lead to "Surprise" results if you aren't careful with naming.

### For Humans: What This Means (Risks)

The more complex your dependencies, the harder the Engine has to work. If you find your app is slow, check if you're
building too many things from scratch instead of sharing them (as singletons).

## Related Files & Folders

- `DependencyResolver.php`: The helper that finds the "parts" (arguments) for classes.
- `Instantiator.php`: The "Oven" that actually creates the objects.
- `DefinitionStore.php`: The "Recipe Book".

### For Humans: What This Means (Relationships)

The **Engine** makes the plan, the **Store** provides the rules, and the **Instantiator** does the physical building.
