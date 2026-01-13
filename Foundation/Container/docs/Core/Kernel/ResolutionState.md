# ResolutionState

## Quick Summary

- Technical: Enum listing all resolution pipeline states (lookups, evaluation, instantiation, terminals).
- Defines the canonical FSM nodes used by the engine/pipeline controller.

### For Humans: What This Means

It’s the official list of steps the resolver is allowed to be in—nothing outside this list should happen.

## Terminology

- **ContextualLookup/DefinitionLookup/Autowire**: Lookup phases in order of specificity.
- **Evaluate/Instantiate**: Materialization phases.
- **Success/Failure/NotFound**: Terminal states.

### For Humans: What This Means

These are the only legal “stations” in the journey from request to instance: check context, check registry, try
autowire, evaluate, build, and then end in success/failure/not-found.

## Think of It

Like subway stops on a fixed line: you can’t invent new stops mid-ride.

### For Humans: What This Means

The train follows the mapped stops—no surprise detours.

## Story Example

Engine enters `ContextualLookup`, then `DefinitionLookup`, then `Autowire`, evaluates and instantiates, ending in
`Success`.

### For Humans: What This Means

The resolver walks through predefined steps; if none work, it ends in `NotFound`.

## For Dummies

1. Start at ContextualLookup.
2. If no match, go to DefinitionLookup.
3. If no match, go to Autowire.
4. Evaluate/Instantiate if possible.
5. Finish in Success or NotFound/Failure.

### For Humans: What This Means

The resolver moves through these steps in order and stops at a terminal state.

## How It Works (Technical)

Enum cases represent FSM nodes; controllers validate transitions against these nodes.

### For Humans: What This Means

Code uses this enum to ensure only expected steps are taken.

## Architecture Role

Anchors the FSM used by the resolution pipeline.

### For Humans: What This Means

This is the authoritative list of states the pipeline may visit.

## Methods

- Enum only; no methods.

### For Humans: What This Means

It’s a constants list, not behavior.

## Risks & Trade-offs

- Adding states requires updating transitions and docs.

### For Humans: What This Means

Don’t add a new stop without updating the map.

## Related Files & Folders

- `Core/Kernel/ResolutionPipelineController.php` — enforces transitions.
- `Features/Actions/Resolve/Engine.php` — uses the states.

### For Humans: What This Means

Controller uses this enum; engine follows the resulting rules.
