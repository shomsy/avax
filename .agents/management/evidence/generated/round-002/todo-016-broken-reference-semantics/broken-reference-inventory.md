# Broken Reference Inventory — TODO-016

## Pre-fix state

5 active broken references reported by `check-broken-reference-semantics.php`:

### 1. Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\System\Foundation\Failure\MiddlewareFailure
- **Referenced in**: `components/HTTP/Middleware/System/Flows/RunMiddlewarePipeline/MiddlewarePipelineFailed.php:7`
- **Problem**: Import path points to non-existent namespace. The actual class lives at `Avax\Components\HTTP\Middleware\System\Foundation\Failure\MiddlewareFailure`.
- **Root cause**: Import used `HTTP/System/Capabilities/MiddlewarePipeline/System/Foundation/Failure` instead of `HTTP/Middleware/System/Foundation/Failure`.

### 2. Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor
- **Referenced in**: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:55`
- **Problem**: Class not imported. Lives at `System/Capabilities/Compensation/CompensationExecutor`.
- **Root cause**: Missing `use` statement.

### 3. Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore
- **Referenced in**: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:54`
- **Problem**: Class not imported. Lives at `System/Capabilities/Idempotency/IdempotencyStore`.
- **Root cause**: Missing `use` statement.

### 4. Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState
- **Referenced in**: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:34,112,128,165,186,194`
- **Problem**: Enum not imported. Lives at `System/Capabilities/SagaState/SagaState`.
- **Root cause**: Missing `use` statement.

### 5. Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep
- **Referenced in**: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:30,98,248`
- **Problem**: Class not imported. Two SagaStep classes exist:
  - `System/Capabilities/Saga/SagaStep` — simple readonly class (name, action, compensation)
  - `System/Capabilities/SagaState/SagaStep` — full class with execute(), compensate(), hasCompensation()
- **Root cause**: Missing `use` statement. PHPStan requires `SagaState/SagaStep` because the orchestrator expects that type.

## Post-fix state

All 5 references resolved:
1. MiddlewarePipelineFailed: corrected import to `HTTP/Middleware/System/Foundation/Failure\MiddlewareFailure`
2. Saga.php: added `use` for `CompensationExecutor`
3. Saga.php: added `use` for `IdempotencyStore`
4. Saga.php: added `use` for `SagaState` enum
5. Saga.php: added `use` for `SagaState/SagaStep` (correct type matching orchestrator expectation)

Additional tooling fix:
- `check-broken-reference-semantics.php`: added `getcwd()` baseDir override for git worktree support (PHP `__DIR__` resolves to main repo in worktree contexts)
- `audit_broken_refs.php`: added `$_OVERRIDE_BASEDIR` support for git worktree contexts
- `check-broken-reference-semantics.php`: added `Symfony\Component\VarDumper\` to optional vendors list (guarded by `class_exists()`, not a required dependency)
