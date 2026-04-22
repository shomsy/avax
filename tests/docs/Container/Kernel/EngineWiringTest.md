# EngineWiringTest

## Quick Summary

- Technical: Verifies the engine refuses double initialization of the container reference.
- Ensures one-shot wiring invariant holds.

### For Humans: What This Means

Confirms you can’t plug the engine into the container twice.

## Terminology

- **One-shot wiring**: Container can only be set once.
- **Guard**: Exception thrown on repeated wiring.

### For Humans: What This Means

The engine protects itself from being rewired mid-flight.

## Think of It

Like a breaker that trips if you try to connect power twice.

### For Humans: What This Means

Once powered, it won’t let you reconnect and risk damage.

## Story Example

Call `setContainer()` once (ok), call it again (throws `ContainerException`).

### For Humans: What This Means

First plug works; second plug is blocked.

## For Dummies

1. Build engine.
2. Wire container once.
3. Second wiring throws.

### For Humans: What This Means

You can’t “replug” the engine.

## How It Works (Technical)

Uses a PHPUnit mock for `ContainerInternalInterface`, asserts exception on second `setContainer()`.

### For Humans: What This Means

The test proves the guard exists and fires.

## Architecture Role

Protects the engine’s lifecycle invariants.

### For Humans: What This Means

Ensures stable resolution by preventing rewiring.

## Methods

- `testDoubleInitializationThrows()`

### For Humans: What This Means

The only check: second wiring is illegal.

## Risks & Trade-offs

- None; simple lifecycle safety.

### For Humans: What This Means

Keep this guard to avoid hard-to-debug errors.

## Related Files & Folders

- `Features/Actions/Resolve/Engine.php`
- `Features/Actions/Resolve/Contracts/EngineInterface.php`

### For Humans: What This Means

Test exercises the engine’s wiring guard.
