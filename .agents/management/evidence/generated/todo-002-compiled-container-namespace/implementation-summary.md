# Implementation Summary — TODO-002 Compiled Container Namespace Emission Fix

## Problem

Compiled container source emission referenced old `Avax\Container\...` namespaces while current code lives under `Avax\Components\Application\Container\System\...`. Generated PHP artifacts would fail at runtime with class-not-found errors.

## Root Cause

Three locations in the compiler/emitter emitted stale namespaces:

1. `MethodEmitter::emitDynamicMethod()` — emitted `\Avax\Container\Capabilities\Resolution\ResolveDependency` and `\Avax\Container\Capabilities\Resolution\ResolveRequest`
2. `MethodEmitter::emitDirectMethod()` — same stale namespaces in method signature
3. `MethodEmitter::fallbackExpression()` — emitted `\Avax\Container\Capabilities\Diagnostics\Errors\ContainerException`
4. `CompileContainer::sourceFor()` — emitted `\Avax\Container\Capabilities\Composition\Compilation\CompiledContainer` as base class

Additionally, two pre-existing bugs were discovered and fixed as part of the namespace correction:

5. `MethodEmitter::emitDirectMethod()` — called `emitArguments(serviceId: $serviceId, plan: $resolvePlan)` with wrong named parameter names
6. `MethodEmitter::emitDirectMethod()` — used `$this(...)` in a pipe operator which tried to call the MethodEmitter object as a callable instead of `var_export(...)`

## Changes Made

### MethodEmitter.php (3 namespace fixes + 2 bug fixes)
- Line 28: Fixed `emitDynamicMethod` to emit current `ResolveDependency` and `ResolveRequest` namespaces
- Line 55: Fixed `emitDirectMethod` to emit current `ResolveDependency` and `ResolveRequest` namespaces
- Line 151: Fixed `fallbackExpression` to emit current `ContainerException` namespace
- Line 48: Fixed `emitArguments` call to use correct named parameters (`resolvePlan:` first, `serviceId:` second)
- Line 52: Fixed `$this(...)` to `var_export(value: $value, return: true)` for proper serialization export

### CompileContainer.php (1 namespace fix)
- Line 789: Fixed `sourceFor` to extend current `CompiledContainer` base class namespace

### Test (new file)
- `tests/Unit/Components/Application/Container/CompiledContainerNamespaceEmissionTest.php` — 8 tests, 38 assertions

## Namespace Mapping

| Old (broken) | New (correct) |
|---|---|
| `Avax\Container\Capabilities\Resolution\ResolveDependency` | `Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency` |
| `Avax\Container\Capabilities\Resolution\ResolveRequest` | `Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest` |
| `Avax\Container\Capabilities\Diagnostics\Errors\ContainerException` | `Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors\ContainerException` |
| `Avax\Container\Capabilities\Composition\Compilation\CompiledContainer` | `Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer` |

## Design Decision

Namespace emission was fixed at the compilation boundary (MethodEmitter and CompileContainer::sourceFor) rather than creating a compatibility layer. The generated code now directly references current namespaces. This is the simplest and most correct approach:

- No compatibility shims needed
- No runtime overhead
- Clear source of truth: the emitter produces what the runtime expects
- Tests prove both the string output and the actual runtime loading

## Advanced OOP Quality

- MethodEmitter: emits source code, does not execute it (separation of concerns)
- CompileContainer: coordinates compilation, delegates emission to MethodEmitter
- No new generic helpers, utilities, or managers created
- All emitted references are typed class constants and fully-qualified class names
- Tests use subprocess isolation for runtime loading to avoid PHP anonymous class redeclaration
