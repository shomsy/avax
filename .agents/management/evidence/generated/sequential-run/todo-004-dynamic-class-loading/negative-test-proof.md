# Negative Test Proof

## QueueWorker

- `testRejectsJobClassNotImplementingJobInterface`: Proves that a class implementing `Job` but NOT `JobInterface` is rejected (deleted). Security boundary enforced.
- `testRejectsNonExistentJobClassAndDeletesJob`: Proves that non-existent class names cause fail-closed (job deletion).

## FailureBoundary

- `testRejectsFallbackHandlerNotImplementingInterface`: Proves RuntimeException thrown when fallback class does not implement FailureHandler.
- `testRejectsRecoveryHandlerNotImplementingInterface`: Proves RuntimeException thrown when recovery class does not implement FailureHandler.
- `testRejectsFallbackClassNotFound`: Proves RuntimeException thrown for non-existent fallback class.

All negative tests GREEN (63/63 tests pass).
