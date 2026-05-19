# Phase E: Static State Worker Safety — Evidence

Date: 2026-05-15
Phase: E (Static State Safety)
Status: GREEN

## E-A: Static Mutable State Scan

Scan result: 31 findings identified.

| Component          | Class              | Property             | Classification  | Rationale                                            |
|--------------------|--------------------|----------------------|-----------------|------------------------------------------------------|
| Container          | `shortcuts.php`    | `$container`         | RESETTABLE_SAFE | `resetAppInstance()` added to reset pipeline         |
| Container          | `Container`        | `$container`         | RESETTABLE_SAFE | `reset()` method added and wired to reset pipeline   |
| Facade             | `Facade`           | `$resolvedInstances` | FACADE_DELEGATE | `reset()` clears cached instances, wired to pipeline |
| ResourceGovernance | `ResourceGovernor` | `$snapshots`, etc.   | RESETTABLE_SAFE | `reset()` method exists, wired to pipeline           |
| ExternalState      | `ExternalState`    | `$session`, etc.     | RESETTABLE_SAFE | `reset()` method exists, wired to pipeline           |
| GracefulShutdown   | `ShutdownSequence` | `$draining`, etc.    | RESETTABLE_SAFE | `reset()` method exists, wired to pipeline           |

## E-B: Core Fixes

### E-B.01 Container Shortcuts

- Modified `appInstance()` to support `null` as a value to SET (for resetting).
- Modified `resetAppInstance()` to call `appInstance(null)`.

### E-B.03 Container Facade

- Added `Container::reset()` static method.
- Updated `Container::resetState()` instance method to delegate to static `reset()`.

### E-B.04 State Reset Integration

- Created `Avax\Framework\System\Capabilities\StateReset\StaticStateReset` wrapper.
- Registered `StaticStateReset` in:
    - `CreateApplication::make()`
    - `CreateApplication::fromBuilder()`
    - `BuildApplicationState::build()`

## E-D: Validation

- Static state safety gate: **PASS** (`php tooling/components/check-component-static-state-safety.php`)
- Full test suite: **GREEN** (8325 tests)
- PHPStan: **GREEN** (0 errors on changed files)

## Remaining Risk

Some third-party libraries or legacy code might still use static state.
This scan only covered `framework/System` and `components/*/System`.
Continued monitoring with `RuntimeSafety` (StateLeakDetector) is recommended during dogfooding.
