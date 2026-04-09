# Architecture Convergence

Status: in progress  
Date: 2026-04-09  
Scope: package tree normalization only

## Migration Posture

- freeze feature expansion during this refactor
- converge to `src/` as the system root
- make `Flows/` the first readable system narrative
- move shared engine lanes under `Capabilities/`
- keep `Foundation/` tiny and neutral
- delete legacy hallway paths after tests, tools, and docs point at the new tree

## Validation Entrypoints

- canonical PHP lint: `find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l`
- full smoke suite: `./tests/run-smoke-tests.sh`
- diagnostics contracts: `./tests/check-diagnostics-contracts.sh`
- benchmark guard: `./tests/check-benchmarks.sh`
- diff integrity: `git diff --check`

## Gravity Wells

- `Container.php`
- `DependencyInjection/Flows/CreateContainer.php`
- `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`
- `DependencyInjection/Dependencies/Resolution/CompiledRuntime.php`

## Current To Target Mapping

| Current area | Owner lane | Target area |
| --- | --- | --- |
| `Container.php`, `ContainerInterface.php`, `ContextContainer.php` | public surface | `src/` |
| `DependencyInjection/Flows/*` | flow owner | `src/Flows/*/` |
| `DependencyInjection/Dependencies/Bindings/*` | declaration | `src/Capabilities/Declaration/Bindings/` |
| `DependencyInjection/Dependencies/Ownership/*` | declaration | `src/Capabilities/Declaration/Ownership/` |
| `DependencyInjection/Dependencies/Providers/*` | declaration | `src/Capabilities/Declaration/Providers/` |
| `DependencyInjection/Dependencies/Blueprints/*` | declaration | `src/Capabilities/Declaration/Blueprints/` |
| `Configuration/*` | composition | `src/Capabilities/Composition/` |
| `Configuration/Assembly/*` | composition | `src/Capabilities/Composition/Assembly/` |
| `Compilation/*` | composition | `src/Capabilities/Composition/Compilation/` |
| `DependencyInjection/Dependencies/Resolution/ResolveRequest.php` | resolution | `src/Capabilities/Resolution/` |
| `DependencyInjection/Dependencies/Resolution/ResolvePlan.php` | resolution | `src/Capabilities/Resolution/` |
| `DependencyInjection/Dependencies/Resolution/ResolveDependencies.php` | resolution | `src/Capabilities/Resolution/` |
| `DependencyInjection/Dependencies/Resolution/ResolutionPolicy.php` | resolution | `src/Capabilities/Resolution/` |
| `DependencyInjection/Dependencies/Resolution/LifetimePlan.php` | resolution | `src/Capabilities/Resolution/` |
| `DependencyInjection/Dependencies/Resolution/ServiceResolver.php` | resolution gravity well | `src/Capabilities/Resolution/` after slimming |
| `DependencyInjection/Dependencies/Resolution/BuildService.php` | execution | `src/Capabilities/Execution/` |
| `DependencyInjection/Injection/**` | execution | `src/Capabilities/Execution/Injection/` |
| `DependencyInjection/Scopes/**` | runtime | `src/Capabilities/Runtime/Scopes/` |
| `Runtime/*` | runtime | `src/Capabilities/Runtime/` |
| `DependencyInjection/Dependencies/Resolution/CompiledRuntime.php` | runtime | `src/Capabilities/Runtime/` |
| `Observability/*` | diagnostics | `src/Capabilities/Diagnostics/Observability/` |
| `Errors/*` | diagnostics | `src/Capabilities/Diagnostics/Errors/` |
| `Foundation/*` | foundation | `src/Foundation/*` |

## Lane Owner Labels

### Public Surface

- `Container.php`
- `ContainerInterface.php`
- `ContextContainer.php`

### Flow Owners

- `DependencyInjection/Flows/CreateContainer.php`
- `DependencyInjection/Flows/RegisterServices.php`
- `DependencyInjection/Flows/BootProviders.php`
- `DependencyInjection/Flows/ResolveService.php`
- `DependencyInjection/Flows/OpenScope.php`
- `DependencyInjection/Flows/CloseScope.php`
- `DependencyInjection/Flows/CallFunction.php`

### Declaration

- `DependencyInjection/Dependencies/Bindings/*`
- `DependencyInjection/Dependencies/Ownership/*`
- `DependencyInjection/Dependencies/Providers/*`
- `DependencyInjection/Dependencies/Blueprints/*`

### Composition

- `Configuration/*`
- `Compilation/*`

### Resolution

- `DependencyInjection/Dependencies/Resolution/ResolveRequest.php`
- `DependencyInjection/Dependencies/Resolution/ResolvePlan.php`
- `DependencyInjection/Dependencies/Resolution/ResolveDependencies.php`
- `DependencyInjection/Dependencies/Resolution/ResolutionPolicy.php`
- `DependencyInjection/Dependencies/Resolution/LifetimePlan.php`
- `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`

### Execution

- `DependencyInjection/Dependencies/Resolution/BuildService.php`
- `DependencyInjection/Injection/*`

### Runtime

- `DependencyInjection/Scopes/*`
- `Runtime/*`
- `DependencyInjection/Dependencies/Resolution/CompiledRuntime.php`

### Diagnostics

- `Observability/*`
- `Errors/*`

### Foundation

- `Foundation/*`
