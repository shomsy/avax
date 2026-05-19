# Stage Report: 11 Golden Path App

## Goal

Prove that the V1 Kernel can successfully boot and execute the Golden Path using public APIs only.

## Scope

### Allowed

- Building the Golden Path App.
- Instantiating ApplicationBuilder.
- Booting Avax.
- Requesting runtime state.
- Executing a state reset.

### Forbidden

- Modifying core framework capabilities.
- Introducing V2/V3 features.

## Files Changed

- `examples/golden-path-app/public/index.php`

## Files Intentionally Not Touched

- Core framework components.

## Validation Commands

```bash
php examples/golden-path-app/public/index.php
```

## Validation Result

```text
GREEN
```

## Evidence

- The `index.php` script successfully instantiates the `ApplicationBuilder` with the appropriate `ProjectPath` and
  `EnvironmentName`.
- The `Avax::boot()` command executes without errors.
- The runtime state is successfully queried, outputting `Runtime State: avax`.
- The `StateResetReport` is correctly returned after executing `resetState()`, resetting the 2 foundational components.

## Remaining Risks

- HTTP Kernel handling of complete PSR-7 requests remains basic in the minimal scope.

## Next Allowed Stage

Stage 12: Public API and Compatibility Governance
