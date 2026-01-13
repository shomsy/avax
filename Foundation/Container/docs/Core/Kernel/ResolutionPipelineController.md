# ResolutionPipelineController

## Quick Summary

- Technical: FSM guard that enforces allowed resolution state transitions.
- Prevents invalid jumps between resolution stages.

### For Humans: What This Means

It’s the traffic controller ensuring the resolver only moves between legal steps.

## Terminology

- **Transition**: A move from one `ResolutionState` to another.
- **Invalid Transition**: Any move not declared in the controller’s table.

### For Humans: What This Means

Transitions are permitted moves; anything else is blocked with an error.

## Think of It

Like an air-traffic controller clearing planes only on approved routes.

### For Humans: What This Means

It stops the resolver from taking shortcuts that could break expectations.

## Story Example

ContextualLookup → DefinitionLookup is allowed; ContextualLookup → Instantiate throws a `ContainerException`.

### For Humans: What This Means

Allowed paths proceed; illegal jumps are rejected.

## For Dummies

1. Start at an initial state.
2. Ask controller to move to the next.
3. If allowed, it updates; if not, it errors.

### For Humans: What This Means

You must follow the map; wrong turns are blocked.

## How It Works (Technical)

- Holds a transition table keyed by current state with a linear path: Contextual → Definition → Autowire → Evaluate →
  Instantiate → Success/NotFound.
- `advanceTo()` validates the requested next state and enforces that terminal transitions require a resolution hit (
  autowire miss may go straight to `NotFound`; instantiate miss can also go terminal).

### For Humans: What This Means

It checks “is this move on the list?” and only then lets you proceed, refusing to finish unless something was actually
found or built.

## Architecture Role

Keeps the resolution pipeline deterministic and formally bounded.

### For Humans: What This Means

Guarantees the resolver follows the designed path.

## Methods

### Method: advanceTo(ResolutionState $next, bool $hit = false): void {#method-advanceto}

Technical: Validates the requested transition against the table and rejects terminal moves unless a resolution hit has
been recorded.

### For Humans: What This Means

It only lets you finish if you actually found something; otherwise you must continue along the allowed path.

#### Parameters

- `ResolutionState $next` State you want to move to.
- `bool $hit` Whether a resolution result was found to justify a terminal move.

#### Returns

- `void`

#### Throws

- `ContainerException` for illegal transitions or premature terminal moves.

#### When to use it

- Whenever advancing the pipeline after a stage completes.

#### Common mistakes

- Skipping states; attempting terminal transitions without a hit.

### Method: state(): ResolutionState {#method-state}

Technical: Returns the current state of the controller.

### For Humans: What This Means

Asks “where are we right now?” in the pipeline.

#### Parameters

- None.

#### Returns

- `ResolutionState` Current state.

#### Throws

- None.

#### When to use it

- For diagnostics or branching logic that needs the current state.

#### Common mistakes

- Assuming the state advanced when it didn’t—always read it.

### Method: isTerminal(ResolutionState $state): bool {#method-isterminal}

Technical: Indicates whether the provided state is terminal (`success`, `failure`, or `not_found`).

### For Humans: What This Means

Checks if a state means “we’re done.”

#### Parameters

- `ResolutionState $state` State to evaluate.

#### Returns

- `bool` True when terminal.

#### Throws

- None.

#### When to use it

- Guard logic when considering transitions.

#### Common mistakes

- Treating non-terminal states as endpoints.

## Risks & Trade-offs

- Transition table must be maintained when states change.

### For Humans: What This Means

Update the map whenever you add/remove steps.

## Related Files & Folders

- `Core/Kernel/ResolutionState.php` — defines the states.
- `Features/Actions/Resolve/Engine.php` — consumes controller.

### For Humans: What This Means

States live in the enum; engine uses the controller to stay on track.
