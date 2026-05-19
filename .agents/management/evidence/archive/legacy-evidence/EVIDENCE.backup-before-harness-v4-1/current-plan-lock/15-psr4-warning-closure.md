# 15 — PSR-4 Warning Closure

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** All PSR-4 autoload warnings from composer dump-autoload -o

## Warnings Found & Fixed

| Warning                                        | File                               | Expected namespace                                  | Current namespace                                                        | Action                                         | Status |
|------------------------------------------------|------------------------------------|-----------------------------------------------------|--------------------------------------------------------------------------|------------------------------------------------|--------|
| Tests\Unit\API\V4SchemaGeneration              | SchemaGenerationTest.php           | Avax\Tests\Unit\API\V4SchemaGeneration              | Tests\Unit\API\V4SchemaGeneration                                        | Fixed namespace                                | FIXED  |
| Tests\Unit\Components\Operations\Observability | ObservabilityExportersTest.php     | Avax\Tests\Unit\Components\Operations\Observability | Tests\Unit\Components\Operations\Observability                           | Fixed namespace + moved file                   | FIXED  |
| Tests\Unit\Components\Operations\Queue         | QueueCommandsTest.php              | Avax\Tests\Unit\Components\Operations\Queue         | Tests\Unit\Components\Operations\Queue                                   | Fixed namespace                                | FIXED  |
| Tests\Unit\DataStack\Database                  | DatabaseConnectionPoolingTest.php  | Avax\Tests\Unit\DataStack\Database                  | Tests\Unit\DataStack\Database                                            | Fixed namespace                                | FIXED  |
| Tests\Unit\Operations\Observability            | ObservabilityTelemetryTest.php     | Avax\Tests\Unit\Operations\Observability            | Tests\Unit\Operations\Observability                                      | Fixed namespace                                | FIXED  |
| Tests\Unit\Operations\MessageBus               | MessagingConsistencyTest.php       | Avax\Tests\Unit\Operations\MessageBus               | Tests\Unit\Operations\MessageBus                                         | Fixed namespace                                | FIXED  |
| Tests\Unit\Operations\Queue                    | QueueWorkerRuntimeTest.php         | Avax\Tests\Unit\Operations\Queue                    | Tests\Unit\Operations\Queue                                              | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4HealthEndpoints         | V4HealthEndpointsTest.php          | Avax\Tests\Unit\Framework\V4HealthEndpoints         | Tests\Unit\Framework\V4HealthEndpoints                                   | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4Health                  | V4HealthTest.php                   | Avax\Tests\Unit\Framework\V4Health                  | Tests\Unit\Framework\V4Health                                            | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4RuntimeBoundary         | V4RuntimeBoundaryTest.php          | Avax\Tests\Unit\Framework\V4RuntimeBoundary         | Tests\Unit\Framework\V4RuntimeBoundary                                   | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4SecurityPolicy          | V4SecurityPolicyTest.php           | Avax\Tests\Unit\Framework\V4SecurityPolicy          | Tests\Unit\Framework\V4SecurityPolicy                                    | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4Benchmarks              | V4BenchmarksTest.php               | Avax\Tests\Unit\Framework\V4Benchmarks              | Tests\Unit\Framework\V4Benchmarks                                        | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\Benchmarks                | RunBenchmarkTest.php               | Avax\Tests\Unit\Framework\Benchmarks                | Tests\Unit\Framework\Benchmarks                                          | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\Benchmarks                | BenchmarkResultTest.php            | Avax\Tests\Unit\Framework\Benchmarks                | Tests\Unit\Framework\Benchmarks                                          | Fixed namespace                                | FIXED  |
| Tests\Unit\Framework\V4SystemDesign            | V4SystemDesignTest.php             | Avax\Tests\Unit\Framework\V4SystemDesign            | Tests\Unit\Framework\V4SystemDesign                                      | Fixed namespace                                | FIXED  |
| Tests\ReferenceApps                            | ReferenceAppSmokeTest.php          | Avax\Tests\ReferenceApps                            | Tests\ReferenceApps                                                      | Fixed namespace                                | FIXED  |
| Avax\Tests\...\RetryResilienceIntegrationTest\ | RetryResilienceIntegrationTest.php | Avax\Tests\Unit\Framework\FailureBoundary           | Avax\Tests\Unit\Framework\FailureBoundary\RetryResilienceIntegrationTest | Removed sub-namespace                          | FIXED  |
| Avax\Tests\...\RecoverWithEnforcementTest\     | RecoverWithEnforcementTest.php     | Avax\Tests\Unit\Framework\FailureBoundary           | Avax\Tests\Unit\Framework\FailureBoundary\RecoverWithEnforcementTest     | Removed sub-namespace + renamed helper classes | FIXED  |
| Avax\Tests\...\TimeoutEnforcementTest\         | TimeoutEnforcementTest.php         | Avax\Tests\Unit\Framework\FailureBoundary           | Avax\Tests\Unit\Framework\FailureBoundary\TimeoutEnforcementTest         | Removed sub-namespace                          | FIXED  |
| Avax\Tests\...\DeadLetterQueueIntegrationTest\ | DeadLetterQueueIntegrationTest.php | Avax\Tests\Unit\Framework\FailureBoundary           | Avax\Tests\Unit\Framework\FailureBoundary\DeadLetterQueueIntegrationTest | Removed sub-namespace                          | FIXED  |

## Additional Fixes

- Moved `tests/Unit/Components/Observability/ObservabilityExportersTest.php` →
  `tests/Unit/Components/Operations/Observability/ObservabilityExportersTest.php` (namespace said
  Operations/Observability but file was in Components/Observability)
- Renamed helper classes in RecoverWithEnforcementTest.php to avoid collision with FailureBoundaryTest.php:
    - TestRecoveryHandler → RecoverWithTestRecoveryHandler
    - TestFallbackHandler → RecoverWithTestFallbackHandler
    - CapturingRecoveryHandler → RecoverWithCapturingRecoveryHandler
    - InvalidRecoveryHandler → RecoverWithInvalidRecoveryHandler

## Result

`composer dump-autoload -o`: **GREEN — 0 PSR-4 warnings**
PHPUnit: **7899 tests, 22835 assertions — ALL PASS**
PHPStan: **0 errors**
