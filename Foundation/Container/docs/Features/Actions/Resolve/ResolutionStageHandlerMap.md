# ResolutionStageHandlerMap

## Quick Summary

Technical: Declarative table that maps `ResolutionState` values (contextual, definition, autowire, evaluate,
instantiate) to their handlers, preserving order for deterministic dispatch inside the resolution engine.

### For Humans: What This Means

It is the lookup chart the engine uses so it always knows which function to run next, without hard-coded `match`
statements.

## Terminology

- **ResolutionState**: Enum describing each step of the pipeline; here it is the key in the map.
- **Handler**: A callable that executes the logic for a specific state (contextual lookup, definition lookup, autowire,
  evaluate, instantiate).
- **Ordered Dispatch**: The guarantee that handlers run in a fixed sequence.

### For Humans: What This Means

States are the names of each checkpoint; handlers are the actions at those checkpoints; the order is locked in so runs
are predictable.

## Think of It

Think of a train schedule: each station (state) has a specific crew (handler) waiting. The schedule determines which
crew works when.

### For Humans: What This Means

No surprises—each stop has its team ready, and the stops never shuffle.

## Story Example

The engine starts a resolution. It asks the map, “Who handles `contextual`?” It runs that callable. Miss? The map points
to the next state, `definition`. Miss again? It points to `autowire`. Hit? It then runs `evaluate` to normalize the
result and `instantiate` to build it before declaring success. The map ensured the order and the chosen callable.

### For Humans: What This Means

The map is the guidebook the engine flips through to decide who works next, including the final “prep” and “build”
stations.

## For Dummies

1. The map lists states in order.
2. Each state has an assigned handler.
3. The engine loops through the order, running the handler.
4. Discovery states set a candidate; Evaluate normalizes it; Instantiate builds if needed.
5. If no handler produces a candidate, the pipeline terminates with not-found.

### For Humans: What This Means

It’s a checklist the engine follows line by line: search, normalize, build, or fail with a clear trace.

## How It Works

- Stores handlers keyed by state value for stable lookup.
- Keeps an ordered list of states to enable deterministic iteration.
- Provides `nextStateAfter()` to navigate forward without branching logic.

### For Humans: What This Means

The class keeps both a map and an ordered list so the engine can both “find by name” and “know who’s next” without
guesswork.

## Architecture Role

- **Lives in**: `Features/Actions/Resolve`
- **Role**: Configuration holder for the resolution FSM dispatch.
- **Collaborators**: `Engine` (consumer), `ResolutionState`, `ResolutionPipelineController`.

### For Humans: What This Means

It’s the routing table for the engine’s finite state machine.

## Methods

### Method: __construct(array<ResolutionState, callable(KernelContext):mixed> $handlers) {#method-__construct}

Technical: Seeds the map with callables keyed by resolution state and records the dispatch order.

### For Humans: What This Means

You hand it the “state → handler” list once; it remembers both the lookup and the sequence.

#### Parameters

- `array<ResolutionState, callable(KernelContext):mixed> $handlers` Mapping of states to the callables that should run
  for that state.

#### Returns

- `self`

#### Throws

- None.

#### When to use it

- When creating the engine’s dispatch table.

#### Common mistakes

- Passing handlers keyed by strings instead of `ResolutionState` enums.

### Method: get(ResolutionState $state) {#method-get}

Technical: Returns the callable for the requested state or throws when unknown.

### For Humans: What This Means

Ask the map for a state and get back the function to run; if the state is missing, you get a clear exception.

#### Parameters

- `ResolutionState $state` State identifier.

#### Returns

- `callable(KernelContext):mixed` The handler for the state.

#### Throws

- `ContainerException` when the state has no handler.

#### When to use it

- During dispatch for the current state.

#### Common mistakes

- Requesting a state that wasn’t registered.

### Method: orderedStates() {#method-orderedstates}

Technical: Returns the list of states in the dispatch order.

### For Humans: What This Means

Gives you the exact sequence to iterate through.

#### Parameters

- None.

#### Returns

- `list<ResolutionState>` Ordered states.

#### Throws

- None.

#### When to use it

- When iterating through the FSM in order.

#### Common mistakes

- Assuming associative array order without using this helper.

### Method: nextStateAfter(ResolutionState $state) {#method-nextstateafter}

Technical: Provides the next state in the configured sequence, or null when at the end.

### For Humans: What This Means

Tells the engine who comes after the current crew.

#### Parameters

- `ResolutionState $state` Current state.

#### Returns

- `ResolutionState|null` Next state or null if none exist.

#### Throws

- None.

#### When to use it

- To move forward deterministically after a miss.

#### Common mistakes

- Ignoring null and assuming there is always a next state.

## Risks & Trade-offs

- **Misconfigured map**: Missing handlers cause runtime exceptions.
- **Static order**: Changing the order requires rebuilding the map.

### For Humans: What This Means

If you forget to register a handler or change the order, the engine will complain or behave differently—keep the map
accurate.

## Related Files & Folders

- `Features/Actions/Resolve/Engine.php`: Consumer of the map for dispatch.
- `Core/Kernel/ResolutionPipelineController.php`: FSM transition enforcement paired with this map.

### For Humans: What This Means

The map tells the engine what to run; the controller enforces the legal steps between those runs.
