# KernelConfigFactoryTest

## Quick Summary

Technical: Verifies debug defaults and explicit overrides for strictMode, autoDefine, and devMode in
`KernelConfigFactory::create()`.

### For Humans: What This Means

Ensures environment flags and overrides produce the expected container behavior.

## Terminology

- **strictMode**: Disables auto-definitions, requires explicit bindings.
- **autoDefine**: Allows autowiring when enabled.
- **devMode**: Enables developer-friendly behaviors (traces/telemetry).

### For Humans: What This Means

These flags control how forgiving or strict the container is during resolution.

## Think of It

Like toggling safety switches: strict is the guardrail, autoDefine is the helper, devMode is the dashboard.

### For Humans: What This Means

You decide whether the system is strict, helpful, or verbose.

## Story Example

When `debug=true`, the container is strict and dev-friendly; when `debug=false`, it’s permissive for production;
explicit overrides always win regardless of debug.

### For Humans: What This Means

Environment defaults exist, but you can override them deliberately.

## For Dummies

1. Build config with `debug=true` → strict on, autoDefine off, devMode on.
2. Build config with `debug=false` → strict off, autoDefine on, devMode off.
3. Provide explicit overrides → overrides win.

### For Humans: What This Means

Defaults change with debug, but your explicit choices take priority.

## How It Works (Technical)

Creates configs via `KernelConfigFactory::create()` with different flag combinations and asserts resulting properties.

### For Humans: What This Means

The test locks the precedence rules for these flags.

## Architecture Role

- **Lives in**: `tests/Core/Kernel`
- **Role**: Guards configuration semantics for the container kernel.

### For Humans: What This Means

Protects against config regressions that could change resolution behavior.

## Methods

### Method: testDebugTrueConfig() {#method-testdebugtrueconfig}

Technical: Asserts debug=true yields strict=true, autoDefine=false, devMode=true.

### For Humans: What This Means

Debug mode should be strict and chatty.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Run in suite; locks debug defaults.

#### Common mistakes

- Forgetting debug defaults when changing factory logic.

### Method: testDebugFalseConfig() {#method-testdebugfalseconfig}

Technical: Asserts debug=false yields strict=false, autoDefine=true, devMode=false.

### For Humans: What This Means

Production defaults should be permissive and quiet.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Run in suite to guard production defaults.

#### Common mistakes

- Accidentally tightening production defaults.

### Method: testOverrideHonored() {#method-testoverridehonored}

Technical: Confirms explicit overrides trump debug defaults.

### For Humans: What This Means

Your explicit choice wins over environment-based defaults.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Always; ensures override precedence.

#### Common mistakes

- Ignoring override parameters when refactoring the factory.

## Risks & Trade-offs

- Tests must evolve if new flags are added.

### For Humans: What This Means

Add new cases when expanding the config.

## Related Files & Folders

- `Core/Kernel/KernelConfigFactory.php`

### For Humans: What This Means

Tests protect the factory that sets container runtime behavior.
