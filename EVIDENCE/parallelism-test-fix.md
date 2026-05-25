# Parallelism PHPUnit Failure Fix Evidence

**Date:** 2026-05-25
**Branch:** architecture/identity-runtime-convergence
**Scope:** Fix 12 pre-existing PHPUnit failures in ProcessPoolParallelismProofTest
**Parent commit:** 7839a4779 (gov: harden public surface and intrusive coupling gates)

## Root Cause

All 12 failures traced to a single bug: **the signing key was not passed from the parent process to child worker processes**.

### Flow

1. Parent process encodes a closure with `CallableSerialization::encode(signingKey: 'proof-key')`
2. HMAC signature computed using `'proof-key'`
3. `bin/avax --payload <base64>` starts in child process
4. Child reads signing key from `$_ENV['AVAX_WORKER_SIGNING_KEY']` → **null** (not set)
5. `CallableSerialization::decode(signingKey: null)` builds pair with default key `'avax-callable-default-key'`
6. HMAC verification fails → Laravel SerializableClosure throws: "Your serialized closure might have been modified or it's unsafe to be unserialized"
7. All 6 test methods fail, each running twice (12 total failures)

## Fix

### File 1: `components/Operations/Parallelism/System/Capabilities/RunThroughProcessPool/StartWorkerProcess.php`

- Added `string|null $signingKey = null` parameter to `start()` method
- When signing key is provided, set `AVAX_WORKER_SIGNING_KEY` environment variable on the child process
- Child process (`bin/avax runWorkerPayload()`) already reads this env var at line 252

### File 2: `components/Operations/Parallelism/System/Capabilities/RunThroughProcessPool/SymfonyProcessParallelRuntime.php`

- Pass `$this->signingKey` as third argument to `$this->starter->start()`

## Before/After

### Before
```
FAILURES!
Tests: 9451, Assertions: 27224, Failures: 12, Risky: 1.

12 failures in ProcessPoolParallelismProofTest:
- test_symfony_process_runtime_starts_multiple_child_processes (x2)
- test_captured_variables_survive_process_boundary (x2)
- test_worker_payloads_are_signed (x2)
- test_result_order_matches_input_order (x2)
- test_worker_exception_returns_structured_failure (x2)
- test_result_contains_worker_metadata (x2)

All with: "Failed to deserialize closure: Your serialized closure might have been modified..."
```

### After
```
OK, but there were issues!
Tests: 9451, Assertions: 27244, Risky: 1.

0 failures. 1 risky test (pre-existing, not a failure).
All 14 parallelism tests pass (7 methods x 2 runs).
```

## Validation

| Command | Result |
|---------|--------|
| `phpunit --filter "ProcessPoolParallelismProofTest"` | OK (14 tests, 46 assertions) |
| `phpunit --no-coverage` (full suite) | OK (9451 tests, 27244 assertions, 0 failures, 1 risky) |
| `phpstan analyse StartWorkerProcess.php SymfonyProcessParallelRuntime.php` | Clean (0 errors) |
| `git diff --check` | Clean |

## Severity Decision

Status: **GREEN** for this fix scope.

- Root cause identified and fixed (missing signing key propagation)
- Fix is minimal: 2 files, 14 lines changed
- No test weakening, no suppression, no assertion removal
- All 12 failures resolved
- No new failures introduced
- PHPStan clean for changed files
- No Identity Slice 2 work
- No Identity business logic modification

## Suppression Check

No suppressions used. No phpstan baseline entries added. No test filters added. No assertions weakened.

## Files Changed

| File | Change |
|------|--------|
| `components/Operations/Parallelism/System/Capabilities/RunThroughProcessPool/StartWorkerProcess.php` | Added $signingKey parameter, pass as AVAX_WORKER_SIGNING_KEY env var |
| `components/Operations/Parallelism/System/Capabilities/RunThroughProcessPool/SymfonyProcessParallelRuntime.php` | Pass signing key to starter->start() |
