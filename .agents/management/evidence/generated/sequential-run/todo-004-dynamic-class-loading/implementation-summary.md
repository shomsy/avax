# Implementation Summary

## Design Decisions

The security issue: Three runtime code paths performed `class_exists() + new $className()` without validating that the class implements a known contract, allowing arbitrary class instantiation from payload/configuration.

### Changes

1. **QueueWorker.php**: Added `is_subclass_of($class, JobInterface::class)` check. If the class does not implement JobInterface, the job is silently deleted (fail-closed) without executing the handler. The RuntimeException throw was initially considered but rejected because QueueWorker.process() wraps execution in try-catch, which would swallow the exception. Instead, fail-closed by deletion.

2. **RunRecoveryAction.php**: Replaced weak `method_exists($handler, '__invoke')` check with `is_subclass_of($recoverClass, FailureHandler::class)` check. If the class does not implement FailureHandler, a RuntimeException is thrown before construction.

3. **RunFallbackAction.php**: Same change as RunRecoveryAction — replaced `method_exists` with `is_subclass_of($fallbackClass, FailureHandler::class)` check.

4. **FailureHandler.php (new)**: Created the `FailureHandler` interface in `FailureBoundary/Foundation/` defining `__invoke(Throwable $failure, FailureContext $context): mixed`. This provides a typed contract for recovery and fallback handlers instead of duck-typing via `method_exists`.

### Interface Placement

`FailureHandler` lives in `framework/System/Capabilities/FailureBoundary/Foundation/` as an internal framework primitive. It is not part of the public surface — it's a contract between FailureBoundary capabilities and their handlers.

## Files Changed

| File | Change |
|------|--------|
| framework/System/Capabilities/FailureBoundary/Foundation/FailureHandler.php | NEW — FailureHandler interface |
| framework/System/Capabilities/FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php | Added FailureHandler interface check |
| framework/System/Capabilities/FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php | Added FailureHandler interface check |
| components/Operations/Queue/System/PublicSurface/QueueWorker.php | Added JobInterface check |
| tests/Unit/Framework/FailureBoundary/FailureBoundaryTest.php | Added 3 negative tests + updated TestFallbackHandler to implement FailureHandler |
| tests/Unit/Framework/FailureBoundary/RecoverWithEnforcementTest.php | Updated test handlers to implement FailureHandler |
| tests/Unit/Operations/Queue/QueueWorkerSecurityTest.php | NEW — 3 security tests |

## What Did NOT Change

- Container internals (class_exists+new sites in Container)
- Migration/seeder commands
- Resilience Fallback component (separate ownership)
- Public API surface
- Existing behavior for valid handlers
