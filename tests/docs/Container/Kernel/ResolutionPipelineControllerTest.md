# ResolutionPipelineControllerTest

## Quick Summary

- Technical: Validates that the pipeline controller accepts valid transitions and rejects invalid ones.
- Ensures FSM enforcement is working.

### For Humans: What This Means

Tests that the “traffic controller” only lets legal moves happen.

## Terminology

- **Valid Transition**: Allowed move between states.
- **Invalid Transition**: Disallowed move that must throw.

### For Humans: What This Means

Good moves pass; bad moves fail loudly.

## Think of It

Like checking that doors open only to the right rooms.

### For Humans: What This Means

The controller won’t let you jump to the wrong step.

## Story Example

Moving from ContextualLookup to DefinitionLookup is fine; trying to jump to Instantiate throws.

### For Humans: What This Means

Legal path works; illegal shortcut is blocked.

## For Dummies

1. Create controller.
2. Move along a valid path.
3. Expect exception for invalid path.

### For Humans: What This Means

We test that correct moves succeed and wrong ones explode.

## How It Works (Technical)

Uses PHPUnit to assert successful state change and to expect `ContainerException` on invalid transition.

### For Humans: What This Means

The test proves the controller enforces its rules.

## Architecture Role

Guards the FSM contract for the resolution pipeline.

### For Humans: What This Means

Keeps the resolver honest about its steps.

## Methods

### Method: testAllowsValidTransition() {#method-testallowsvalidtransition}

Technical: Asserts that moving from `ContextualLookup` to `DefinitionLookup` succeeds.

### For Humans: What This Means

Proves a normal, legal hop works.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None (aside from assertion failures).

#### When to use it

- Run in the suite; it enforces the happy-path transition.

#### Common mistakes

- Altering transitions without updating the test.

### Method: testThrowsOnInvalidTransition() {#method-testthrowsoninvalidtransition}

Technical: Asserts that skipping directly to `Instantiate` throws `ContainerException`.

### For Humans: What This Means

Proves illegal shortcuts are blocked.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `ContainerException` via expectation when transition is invalid.

#### When to use it

- Run in the suite to guard the FSM contract.

#### Common mistakes

- Forgetting to keep the transition table aligned with test expectations.

### Method: testTerminalTransitionWithoutHitFails() {#method-testterminaltransitionwithouthitfails}

Technical: Ensures moving to a terminal success state without a recorded hit triggers `ContainerException`.

### For Humans: What This Means

You can’t declare victory without actually resolving something.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `ContainerException` via expectation when hit flag is false.

#### When to use it

- Run in the suite; it enforces the hit-required terminal rule.

#### Common mistakes

- Allowing terminal transitions without setting the hit flag.

### Method: testCannotSkipInstantiate() {#method-testcannotskipinstantiate}

Technical: Asserts that attempting to reach `Success` without first entering `Instantiate` triggers
`ContainerException`.

### For Humans: What This Means

You can’t skip the construction step and still claim success.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `ContainerException` on invalid shortcut.

#### When to use it

- Run in the suite to guard against future transition loosening.

#### Common mistakes

- Loosening transitions and allowing terminals without construction.

## Risks & Trade-offs

- None major; ensures correctness of the FSM guard.

### For Humans: What This Means

Simple safety net—keep it running.

## Related Files & Folders

- `Core/Kernel/ResolutionPipelineController.php`
- `Core/Kernel/ResolutionState.php`

### For Humans: What This Means

Tests the controller and its state map.
