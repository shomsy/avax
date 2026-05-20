# Implementation Summary — TODO-016 Broken Reference Semantics

## Files Changed

### Production code (2 files)

1. **components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php**
   - Added 4 missing `use` statements:
     - `CompensationExecutor` (Capabilities/Compensation)
     - `IdempotencyStore` (Capabilities/Idempotency)
     - `SagaState` enum (Capabilities/SagaState)
     - `SagaStep` (Capabilities/SagaState — not Saga/SagaStep, to match orchestrator type expectation)
   - Resolves 4 of 5 broken references

2. **components/HTTP/Middleware/System/Flows/RunMiddlewarePipeline/MiddlewarePipelineFailed.php**
   - Fixed import path from `HTTP/System/Capabilities/MiddlewarePipeline/System/Foundation/Failure\MiddlewareFailure` to `HTTP/Middleware/System/Foundation/Failure\MiddlewareFailure`
   - Resolves 1 of 5 broken references

### Tooling code (2 files)

3. **tooling/refactor/check-broken-reference-semantics.php**
   - Changed `$baseDir` from `dirname(__DIR__)` to `getcwd()` to support git worktree contexts where PHP `__DIR__` resolves to the main repo
   - Added `$_OVERRIDE_BASEDIR` passthrough to audit tool
   - Added `Symfony\Component\VarDumper\` to optional vendors list

4. **tooling/audit_broken_refs.php**
   - Added `$_OVERRIDE_BASEDIR` override support for git worktree contexts

### Test code (2 files)

5. **tests/Operations/SagaReferenceSemanticsTest.php** (new)
   - 8 tests proving all internal Saga references resolve correctly
   - Tests: SagaStep, SagaState enum, IdempotencyStore, CompensationExecutor, SagaResult

6. **tests/HTTP/MiddlewareFailureReferenceTest.php** (new)
   - 2 tests proving MiddlewarePipelineFailed correctly extends MiddlewareFailure
   - Tests: inheritance chain, RuntimeException parent

## Design Decisions

### SagaStep type selection
Two SagaStep classes exist in the ApplicationWorkflow component:
- `Capabilities/Saga/SagaStep` — simple readonly value object
- `Capabilities/SagaState/SagaStep` — full class with execute(), compensate(), hasCompensation()

The `SagaOrchestrator::execute()` and `compensate()` methods type-hint `array<SagaState\SagaStep>`. The PublicSurface `Saga` creates steps via `new SagaStep()` and passes them to the orchestrator. Using `Saga/SagaStep` caused a PHPStan type mismatch. Fixed by importing `SagaState/SagaStep` instead.

### Worktree baseDir fix
PHP's `__DIR__` magic constant resolves to the main repository path in git worktree contexts, not the worktree filesystem path. The audit tool scans `baseDir` for PHP files, so it was scanning the main repo (which doesn't have the worktree's edits). Fixed by using `getcwd()` in the checker and passing `$_OVERRIDE_BASEDIR` to the audit tool.

## Scope Compliance

- Only fixed known broken references reported by `check-broken-reference-semantics.php`
- No CSRF/session authority changes
- No HTTP PublicSurface cleanup
- No dynamic class loading cleanup
- No broad namespace rewrite
- No public API redesign
- No feature work
- No fix-this.md rewrite
- No TODO.md rewrite
