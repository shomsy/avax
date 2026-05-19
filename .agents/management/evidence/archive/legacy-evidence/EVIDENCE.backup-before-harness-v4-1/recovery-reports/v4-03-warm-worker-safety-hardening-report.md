# V4-03 Warm Worker Safety Hardening Report

**Date:** 2026-05-09
**Stage:** V4-03 Full Warm Worker Safety Hardening
**Branch:** main
**Status:** GREEN

## Goal

Turn the V4-03 Warm Worker Safety Baseline into full V4-03 Warm Worker Safety by implementing:

1. Formal Warm State Contract
2. Request scope reset lifecycle
3. State reset registry integration
4. Scoped container lifecycle reset
5. MemoryGuard enforcement
6. Memory metrics/snapshots
7. Leak detection baseline
8. Worker lifecycle safety checks
9. ReactPHP runtime reset integration proof

## Files Created

### Warm State Contract

- `framework/System/Runtime/WarmApplication/WarmStateContract.php` — Formal contract defining allowed warm state and
  must-reset state
- `framework/System/Runtime/WarmApplication/AllowedWarmState.php` — Enum of 14 state categories safe to keep warm
- `framework/System/Runtime/WarmApplication/MustResetState.php` — Enum of 15 state categories that must reset

### Request Reset Lifecycle

- `framework/System/Runtime/WarmApplication/HandleWarmRequest.php` — Complete warm request lifecycle orchestrator
- `framework/System/Runtime/WarmApplication/FlushScopedInstances.php` — Flushes scoped instances, runs
  StateResetRegistry, resets RequestScope

### State Leak Detection

- `framework/System/Runtime/WarmApplication/RuntimeStateLeak.php` — Value object describing a specific state leak
- `framework/System/Runtime/WarmApplication/DetectLeakedState.php` — Enhanced with structured leak detection, scope
  tracking, UNKNOWN handling

### MemoryGuard

- `framework/System/Runtime/MemoryGuard/MonitorWorkerMemory.php` — Full memory tracking with before/after/peak, delta,
  growth rate, thresholds
- `framework/System/Runtime/MemoryGuard/CalculateMemoryGrowthRate.php` — Linear regression growth rate calculation
- `framework/System/Runtime/MemoryGuard/RequestWorkerRecycle.php` — Decision/finding object for graceful worker recycle

### Tests

- `tests/Unit/Framework/V4WarmWorkerSafety/WarmWorkerSafetyTest.php` — 44 tests covering all V4-03 requirements

### Documentation

- `framework/System/Runtime/WarmApplication/HOW_THIS_WORKS.md` — Lifecycle, allowed warm state, must-reset state,
  integration guide
- `framework/System/Runtime/MemoryGuard/HOW_THIS_WORKS.md` — MemoryGuard behavior, thresholds, safety guarantees

## Files Modified

- `framework/System/Runtime/ReactPhp/RunReactHttpServer.php` — Added `startWarmSmoke()`, `setWarmHandler()`,
  `setMemoryGuard()` for warm worker integration
- `framework/System/Runtime/WarmApplication/DetectLeakedState.php` — Enhanced with structured leak detection,
  RequestScope integration, checkStatus()

## Validation Evidence

### PHPUnit

```
tests/Unit/Framework/V4WarmWorkerSafety/WarmWorkerSafetyTest.php: 44 tests, 104 assertions — GREEN
tests/Unit/Framework/V4Runtime/ReactRuntimeAndWarmSafetyTest.php: 20 tests, 46 assertions — GREEN
```

### Test Coverage Matrix

| Requirement                                        | Test                                                        | Status |
|----------------------------------------------------|-------------------------------------------------------------|--------|
| Warm State Contract exposes allowed warm state     | `testWarmStateContractExposesAllowedWarmState`              | GREEN  |
| Warm State Contract exposes must-reset state       | `testWarmStateContractExposesMustResetState`                | GREEN  |
| Warm State Contract has no request-specific values | `testWarmStateContractHasNoRequestSpecificRuntimeValues`    | GREEN  |
| Warm State Contract is immutable                   | `testWarmStateContractIsEffectivelyImmutable`               | GREEN  |
| Allowed warm state contains compiled concepts      | `testAllowedWarmStateContainsCompiledConcepts`              | GREEN  |
| Must-reset state contains request concepts         | `testMustResetStateContainsRequestConcepts`                 | GREEN  |
| Request scope opens before handling                | `testRequestScopeOpensBeforeHandling`                       | GREEN  |
| Request scope closes after handling                | `testRequestScopeClosesAfterHandling`                       | GREEN  |
| Flush closes and reopens scope                     | `testFlushScopedInstancesClosesAndReopensScope`             | GREEN  |
| Flush runs reset registry                          | `testFlushScopedInstancesRunsResetRegistry`                 | GREEN  |
| Reset runs after successful request                | `testResetLifecycleRunsAfterSuccessfulRequest`              | GREEN  |
| Reset runs after failed request                    | `testResetLifecycleRunsAfterFailedRequest`                  | GREEN  |
| Scoped instance not reused between requests        | `testScopedInstanceFromRequest1NotReusedInRequest2`         | GREEN  |
| Reset lifecycle order is deterministic             | `testResetLifecycleOrderIsDeterministic`                    | GREEN  |
| Clean runtime reports no leak                      | `testCleanRuntimeReportsNoLeak`                             | GREEN  |
| Leaked request state detected                      | `testFakeLeakedRequestStateIsDetected`                      | GREEN  |
| Leaked scoped instance detected                    | `testFakeLeakedScopedInstanceIsDetected`                    | GREEN  |
| Unavailable subsystem not fake GREEN               | `testUnavailableSubsystemReportsNotFakeGreen`               | GREEN  |
| Leak detector clears state                         | `testLeakDetectorClearsState`                               | GREEN  |
| Residual scope data detected                       | `testLeakDetectorDetectsResidualScopeData`                  | GREEN  |
| Memory snapshot records before/after/peak          | `testMemorySnapshotRecordsBeforeAfterPeak`                  | GREEN  |
| Memory delta calculated                            | `testMemoryDeltaIsCalculated`                               | GREEN  |
| Growth rate calculated                             | `testGrowthRateIsCalculated`                                | GREEN  |
| Soft threshold produces warning                    | `testSoftThresholdProducesWarning`                          | GREEN  |
| Hard threshold produces recycle request            | `testHardThresholdProducesRecycleRequest`                   | GREEN  |
| Max requests produces recycle request              | `testMaxRequestsThresholdProducesRecycleRequest`            | GREEN  |
| Recycle decision is graceful                       | `testRecycleDecisionIsGraceful`                             | GREEN  |
| No mid-request termination                         | `testNoMidRequestTermination`                               | GREEN  |
| React smoke invokes reset lifecycle                | `testReactSmokeRequestInvokesResetLifecycle`                | GREEN  |
| Sequential requests do not share state             | `testTwoSequentialReactSmokeRequestsDoNotShareRequestState` | GREEN  |
| Exception triggers reset                           | `testExceptionInReactRequestStillTriggersReset`             | GREEN  |
| Memory snapshot for React request                  | `testMemorySnapshotRecordedForReactRequest`                 | GREEN  |
| App does not expose ReactPHP classes               | `testAppPublicMethodsDoNotExposeReactPhpClasses`            | GREEN  |
| No duplicate RequestScope implementation           | `testNoDuplicateRequestScopeImplementation`                 | GREEN  |
| Warm worker uses existing runtime                  | `testWarmWorkerSafetyUsesExistingRuntime`                   | GREEN  |
| MemoryGuard does not depend on ReactPHP            | `testMemoryGuardDoesNotDependOnReactPhpDirectly`            | GREEN  |
| ReactPHP runtime depends on WarmApplication        | `testReactPhpRuntimeDependsOnWarmApplication`               | GREEN  |
| PublicSurface stays thin                           | `testPublicSurfaceStaysThin`                                | GREEN  |

## Remaining Risks

1. V4-17 (RoadRunner/Swoole/FrankenPHP adapters) remains blocked until full V4-03 GREEN
2. Real process restart based on RequestWorkerRecycle decisions is V4-17 scope
3. MemoryGuard soft/hard thresholds use defaults — production tuning needed per deployment

## Next Allowed Action

1. Run full canonical validation suite
2. If GREEN, push to origin/main
3. V4-04 Developer Experience can begin after V4-03 is confirmed GREEN
