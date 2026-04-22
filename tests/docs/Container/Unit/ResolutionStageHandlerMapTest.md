# ResolutionStageHandlerMapTest

## Quick Summary

Technical: Verifies the handler map preserves order, returns the correct callable, advances to the right next state, and
throws when a handler is missing.

### For Humans: What This Means

Checks that the engine’s dispatch table is correct, predictable, and fails loudly when misconfigured.

## Terminology

- **Handler Map**: State → callable lookup.
- **Ordered States**: The sequence the engine iterates.
- **Missing Handler**: A state without a registered callable.

### For Humans: What This Means

It’s the checklist of steps; order matters, and missing entries should trigger an error.

## Think of It

Like a train timetable that also lists the crew for each stop.

### For Humans: What This Means

Every stop has an assigned team; if a stop has no team, the system should complain.

## Story Example

The map defines `contextual` then `definition`. The test asks for the first handler and gets the contextual callable; it
asks “what’s next?” and gets `definition`; it then asks for `autowire` and sees the map throw because no handler exists.

### For Humans: What This Means

We prove the map follows its schedule and yells when a stop has no crew.

## For Dummies

1. Build a map with two states.
2. Confirm the order list matches.
3. Retrieve and run the first handler.
4. Ask for the next state.
5. Ask for an unregistered state and expect an exception.

### For Humans: What This Means

It’s a simple checklist to ensure the map is complete and in order.

## How It Works (Technical)

Uses `ResolutionStageHandlerMap` with closures, asserts `orderedStates()`, runs a handler via `get()`, checks
`nextStateAfter()`, and expects `ContainerException` on unknown states.

### For Humans: What This Means

The test pokes every control surface of the map so surprises are caught early.

## Architecture Role

- **Lives in**: `tests/Unit`
- **Role**: Guardrail for the FSM dispatch table used by the engine.

### For Humans: What This Means

Keeps the engine’s routing table trustworthy.

## Methods

### Method: testOrderedStatesAndHandlers() {#method-testorderedstatesandhandlers}

Technical: Asserts the map stores states in order and returns the matching handler for a given state.

### For Humans: What This Means

Proves the schedule is intact and the right crew shows up.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None (assertions only).

#### When to use it

- Always run; it validates map integrity.

#### Common mistakes

- Reordering states without updating tests.

### Method: testNextStateAfter() {#method-testnextstateafter}

Technical: Verifies `nextStateAfter()` returns the following state or null at the end.

### For Humans: What This Means

Checks the map knows who comes next.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None (assertions only).

#### When to use it

- Run in suite; ensures deterministic progression.

#### Common mistakes

- Assuming there is always a next state.

### Method: testThrowsOnMissingHandler() {#method-testthrowsonmissinghandler}

Technical: Expects `ContainerException` when requesting an unregistered state handler.

### For Humans: What This Means

Ensures the map complains loudly when a stop has no crew.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `ContainerException` as part of the expectation.

#### When to use it

- Run in suite to guard against incomplete maps.

#### Common mistakes

- Forgetting to register a new state’s handler when adding states.

## Risks & Trade-offs

- **Map Drift**: Adding states without handlers will surface here.
- **Order Sensitivity**: Changing order requires test updates.

### For Humans: What This Means

If you change the steps or add new ones, update the map and these tests together.

## Related Files & Folders

- `Features/Actions/Resolve/ResolutionStageHandlerMap.php`
- `Features/Actions/Resolve/Engine.php`

### For Humans: What This Means

The test protects the dispatch map the engine depends on.
