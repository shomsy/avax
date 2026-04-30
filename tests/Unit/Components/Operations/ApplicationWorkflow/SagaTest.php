<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\ApplicationWorkflow;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation\CompensationExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyKey;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Retries\RetryPolicy;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\StepRunner\StepRunner;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Saga;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaResult;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Workflow;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SagaTest extends TestCase
{
    // ==================== Saga DSL Tests ====================

    public function testDefineSagaWithMultipleSteps() : void
    {
        $orderCreated     = false;
        $paymentCharged   = false;
        $confirmationSent = false;

        $saga = Saga::define('order-processing')
            ->step('create-order', function (array $ctx) use (&$orderCreated) {
                $orderCreated = true;

                return ['orderId' => 'ORD-123'];
            })
            ->step('charge-payment', function (array $ctx) use (&$paymentCharged) {
                $paymentCharged = true;

                return ['paymentId' => 'PAY-456'];
            })
            ->step('send-confirmation', function (array $ctx) use (&$confirmationSent) {
                $confirmationSent = true;

                return ['sent' => true];
            });

        $result = $saga->execute([]);

        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($orderCreated);
        $this->assertTrue($paymentCharged);
        $this->assertTrue($confirmationSent);
        $this->assertCount(3, $result->completedSteps);
        $this->assertSame('create-order', $result->completedSteps[0]);
        $this->assertSame('charge-payment', $result->completedSteps[1]);
        $this->assertSame('send-confirmation', $result->completedSteps[2]);
    }

    public function testSagaExecutesStepsInOrder() : void
    {
        $executionOrder = [];

        $saga = Saga::define('sequential-test')
            ->step('first', function () use (&$executionOrder) {
                $executionOrder[] = 'first';

                return [];
            })
            ->step('second', function () use (&$executionOrder) {
                $executionOrder[] = 'second';

                return [];
            })
            ->step('third', function () use (&$executionOrder) {
                $executionOrder[] = 'third';

                return [];
            });

        $saga->execute([]);

        $this->assertSame(['first', 'second', 'third'], $executionOrder);
    }

    public function testSagaPassesContextBetweenSteps() : void
    {
        $saga = Saga::define('context-flow')
            ->step('step-one', function (array $ctx) {
                return ['value1' => 'from-step-one'];
            })
            ->step('step-two', function (array $ctx) {
                return ['value2' => $ctx['value1'] . '-extended'];
            });

        $result = $saga->execute([]);

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('from-step-one-extended', $result->data['value2']);
    }

    // ==================== Compensation Tests ====================

    public function testCompensationRunsOnFailure() : void
    {
        $orderCreated  = false;
        $orderRefunded = false;
        $paymentFailed = false;

        $saga = Saga::define('order-with-compensation')
            ->step('create-order',
                function (array $ctx) use (&$orderCreated) {
                    $orderCreated = true;

                    return ['orderId' => 'ORD-123'];
                },
                function (array $ctx) use (&$orderRefunded) {
                    $orderRefunded = true;
                }
            )
            ->step('charge-payment',
                function (array $ctx) use (&$paymentFailed) {
                    $paymentFailed = true;
                    throw new RuntimeException('Payment gateway unavailable');
                },
                function (array $ctx) {
                    // This compensation should NOT run since step failed
                }
            );

        $result = $saga->execute([]);

        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($orderCreated);
        $this->assertTrue($paymentFailed);
        $this->assertTrue($orderRefunded, 'Compensation should have run for create-order');
        $this->assertSame('Payment gateway unavailable', $result->getFailureReason());
    }

    public function testCompensationRunsInReverseOrder() : void
    {
        $compensationOrder = [];

        $saga = Saga::define('reverse-compensation')
            ->step('step-a',
                function () { return []; },
                function () use (&$compensationOrder) {
                    $compensationOrder[] = 'compensate-a';
                }
            )
            ->step('step-b',
                function () { return []; },
                function () use (&$compensationOrder) {
                    $compensationOrder[] = 'compensate-b';
                }
            )
            ->step('step-c',
                function () { return []; },
                function () use (&$compensationOrder) {
                    $compensationOrder[] = 'compensate-c';
                }
            )
            ->step('step-failing',
                function () {
                    throw new RuntimeException('Step failed');
                }
            );

        $result = $saga->execute([]);

        $this->assertFalse($result->isSuccessful());
        // Compensation should run in reverse: c, b, a
        $this->assertSame(['compensate-c', 'compensate-b', 'compensate-a'], $compensationOrder);
    }

    public function testExplicitCompensation() : void
    {
        $compensated = [];

        $saga = Saga::define('explicit-compensation')
            ->step('create-order',
                function () { return ['orderId' => 'ORD-1']; },
                function () use (&$compensated) {
                    $compensated[] = 'order';
                }
            )
            ->step('reserve-inventory',
                function () { return ['reserved' => true]; },
                function () use (&$compensated) {
                    $compensated[] = 'inventory';
                }
            );

        // Execute successfully first
        $result = $saga->execute([]);
        $this->assertTrue($result->isSuccessful());

        // Now explicitly compensate
        $compensationResult = $saga->compensate();

        $this->assertTrue($compensationResult->isSuccessful());
        $this->assertSame(['inventory', 'order'], $compensated);
    }

    public function testCompensationWithoutHandler() : void
    {
        $saga = Saga::define('no-compensation-handler')
            ->step('step-without-compensation', function () {
                return [];
            })
            ->step('failing-step', function () {
                throw new RuntimeException('Failed');
            });

        $result = $saga->execute([]);

        // Should fail but not throw since there's no compensation to run
        $this->assertFalse($result->isSuccessful());
    }

    public function testFailMethod() : void
    {
        $saga = Saga::define('fail-test')
            ->step('step-one', function () { return []; });

        $saga->execute([]);

        $result = $saga->fail('Manual failure reason');

        $this->assertFalse($result->isSuccessful());
        $this->assertSame('Manual failure reason', $result->getFailureReason());
        $this->assertSame(SagaState::Failed, $saga->getStatus());
    }

    // ==================== Resume Tests ====================

    public function testResumeFromStoredState() : void
    {
        $store    = new InMemorySagaStore();
        $workflow = new Workflow($store);

        $executionCount = 0;

        $saga = Saga::define('resumable-saga')
            ->step('step-one', function () use (&$executionCount) {
                $executionCount++;

                return ['step' => 1];
            })
            ->step('step-two', function () use (&$executionCount) {
                $executionCount++;

                return ['step' => 2];
            })
            ->withStore($store);

        // Execute and store
        $result = $saga->execute([]);
        $this->assertTrue($result->isSuccessful());
        $sagaId = $result->sagaId;

        // Verify stored
        $stored = $store->findById($sagaId);
        $this->assertNotNull($stored);
        $this->assertSame($sagaId, $stored->getId());
    }

    public function testWorkflowStartAndResume() : void
    {
        $store    = new InMemorySagaStore();
        $workflow = new Workflow($store);

        $saga = Saga::define('workflow-test')
            ->step('step-one', function () { return ['data' => 'one']; })
            ->withStore($store);

        $store->save($saga);
        $sagaId = $saga->getId();

        // Resume the saga
        $result = $workflow->resume($sagaId);

        $this->assertTrue($result->isSuccessful());
    }

    public function testWorkflowCancel() : void
    {
        $store       = new InMemorySagaStore();
        $compensated = false;

        $saga = Saga::define('cancellable-saga')
            ->step('create-order',
                function () { return ['orderId' => 'ORD-1']; },
                function () use (&$compensated) {
                    $compensated = true;
                }
            )
            ->withStore($store);

        // Execute to complete the step
        $saga->execute([]);

        $store->save($saga);
        $sagaId = $saga->getId();

        // Cancel the saga
        $workflow = new Workflow($store);
        $result   = $workflow->cancel($sagaId);

        $this->assertTrue($compensated);
    }

    public function testResumeNotFoundSaga() : void
    {
        $workflow = new Workflow(new InMemorySagaStore());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Saga with ID "nonexistent" not found');

        $workflow->resume('nonexistent');
    }

    // ==================== Idempotency Tests ====================

    public function testIdempotencyKeyGeneration() : void
    {
        $key1 = IdempotencyKey::generate('saga-1', 'step-a', '0');
        $key2 = IdempotencyKey::generate('saga-1', 'step-a', '0');
        $key3 = IdempotencyKey::generate('saga-1', 'step-a', '1');

        $this->assertSame($key1->toString(), $key2->toString());
        $this->assertNotSame($key1->toString(), $key3->toString());
    }

    public function testIdempotencyStorePreventsDuplicateExecution() : void
    {
        $store = new IdempotencyStore();
        $key   = IdempotencyKey::generate('saga-1', 'step-a', '0');

        $this->assertFalse($store->hasExecuted($key));

        $store->record($key, ['result' => 'success']);

        $this->assertTrue($store->hasExecuted($key));
        $this->assertSame(['result' => 'success'], $store->getResult($key));
    }

    public function testStepRunnerIdempotency() : void
    {
        $executionCount = 0;
        $step           = new SagaStep(
            name  : 'counting-step',
            action: function () use (&$executionCount) {
                $executionCount++;

                return ['count' => $executionCount];
            },
        );

        $runner = new StepRunner();
        $key    = IdempotencyKey::generate('saga-1', 'counting-step', '0');

        $result1 = $runner->execute($step, [], $key);

        // Second execution with same key should return cached result
        $result2 = $runner->execute($step, [], $key);

        $this->assertSame(1, $executionCount, 'Step should only execute once due to idempotency');
        $this->assertSame($result1, $result2);
    }

    // ==================== StepRunner Tests ====================

    public function testStepRunnerExecutesStep() : void
    {
        $step = new SagaStep(
            name  : 'test-step',
            action: function (array $ctx) {
                return ['processed' => true, 'input' => $ctx['input'] ?? null];
            },
        );

        $runner = new StepRunner();
        $key    = IdempotencyKey::generate('saga-1', 'test-step', '0');

        $result = $runner->execute($step, ['input' => 'data'], $key);

        $this->assertTrue($result['processed']);
        $this->assertSame('data', $result['input']);
    }

    public function testStepRunnerThrowsOnFailure() : void
    {
        $step = new SagaStep(
            name  : 'failing-step',
            action: function () {
                throw new RuntimeException('Step failed');
            },
        );

        $runner = new StepRunner(retryPolicy: RetryPolicy::none());
        $key    = IdempotencyKey::generate('saga-1', 'failing-step', '0');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Step failed');

        $runner->execute($step, [], $key);
    }

    // ==================== RetryPolicy Tests ====================

    public function testRetryPolicyExponentialBackoff() : void
    {
        $policy = RetryPolicy::exponential(maxAttempts: 3, baseBackoffMs: 100);

        $this->assertSame(100, $policy->getDelayForAttempt(1));   // 100 * 2^0
        $this->assertSame(200, $policy->getDelayForAttempt(2));   // 100 * 2^1
        $this->assertSame(400, $policy->getDelayForAttempt(3));   // 100 * 2^2
        $this->assertFalse($policy->canRetry(3));
        $this->assertTrue($policy->canRetry(0));
        $this->assertTrue($policy->canRetry(2));
    }

    public function testRetryPolicyLinearBackoff() : void
    {
        $policy = RetryPolicy::linear(maxAttempts: 3, backoffMs: 100);

        $this->assertSame(100, $policy->getDelayForAttempt(1));
        $this->assertSame(200, $policy->getDelayForAttempt(2));
        $this->assertSame(300, $policy->getDelayForAttempt(3));
    }

    public function testRetryPolicyNone() : void
    {
        $policy = RetryPolicy::none();

        $this->assertSame(0, $policy->getDelayForAttempt(1));
        $this->assertFalse($policy->canRetry(1));
    }

    // ==================== CompensationExecutor Tests ====================

    public function testCompensationExecutorReverseOrder() : void
    {
        $compensated = [];

        $steps = [
            new SagaStep('a', function () { return []; }, function () use (&$compensated) { $compensated[] = 'a'; }),
            new SagaStep('b', function () { return []; }, function () use (&$compensated) { $compensated[] = 'b'; }),
            new SagaStep('c', function () { return []; }, function () use (&$compensated) { $compensated[] = 'c'; }),
        ];

        $executor = new CompensationExecutor();
        $result   = $executor->execute($steps, ['a', 'b', 'c'], []);

        $this->assertTrue($result->success);
        $this->assertSame(['c', 'b', 'a'], $compensated);
    }

    public function testCompensationExecutorHandlesPartialCompensation() : void
    {
        $compensated = [];

        $steps = [
            new SagaStep('a', function () { return []; }, function () use (&$compensated) { $compensated[] = 'a'; }),
            new SagaStep('b', function () { return []; }, function () use (&$compensated) { throw new RuntimeException('Compensation failed'); }),
            new SagaStep('c', function () { return []; }, function () use (&$compensated) { $compensated[] = 'c'; }),
        ];

        $executor = new CompensationExecutor();
        $result   = $executor->execute($steps, ['a', 'b', 'c'], []);

        $this->assertFalse($result->success);
        $this->assertSame(['c', 'a'], $result->compensatedSteps);
        $this->assertSame(['b'], $result->failedSteps);
        $this->assertStringContainsString('Compensation failed', $result->failureReason);
    }

    public function testCompensationExecutorWithEmptyCompletedSteps() : void
    {
        $steps = [
            new SagaStep('a', function () { return []; }, function () { return null; }),
        ];

        $executor = new CompensationExecutor();
        $result   = $executor->execute($steps, [], []);

        $this->assertTrue($result->success);
        $this->assertEmpty($result->compensatedSteps);
    }

    // ==================== SagaState Tests ====================

    public function testSagaStateEnum() : void
    {
        $this->assertSame('running', SagaState::Running->value);
        $this->assertSame('completed', SagaState::Completed->value);
        $this->assertSame('failed', SagaState::Failed->value);
        $this->assertSame('compensating', SagaState::Compensating->value);
        $this->assertSame('compensated', SagaState::Compensated->value);
    }

    // ==================== SagaStore Tests ====================

    public function testInMemorySagaStore() : void
    {
        $store = new InMemorySagaStore();

        $saga = Saga::define('test-saga')
            ->step('step-one', fn () => []);

        $store->save($saga);

        $found = $store->findById($saga->getId());
        $this->assertNotNull($found);
        $this->assertSame($saga->getId(), $found->getId());

        $notFound = $store->findById('nonexistent');
        $this->assertNull($notFound);
    }

    public function testInMemorySagaStoreUpdateStatus() : void
    {
        $store = new InMemorySagaStore();

        $saga = Saga::define('test-saga')
            ->step('step-one', fn () => []);

        $store->save($saga);
        $this->assertSame(SagaState::Running, $saga->getStatus());

        $store->updateStatus($saga, SagaState::Completed);

        $found = $store->findById($saga->getId());
        $this->assertSame(SagaState::Completed, $found->getStatus());
    }

    public function testInMemorySagaStoreDelete() : void
    {
        $store = new InMemorySagaStore();

        $saga = Saga::define('test-saga')
            ->step('step-one', fn () => []);

        $store->save($saga);
        $this->assertNotNull($store->findById($saga->getId()));

        $store->delete($saga->getId());
        $this->assertNull($store->findById($saga->getId()));
    }

    // ==================== SagaResult Tests ====================

    public function testSagaResultGetStepResult() : void
    {
        $result = new SagaResult(
            success       : true,
            sagaId        : 'test-1',
            data          : [],
            completedSteps: ['step-a', 'step-b'],
            stepResults   : ['step-a' => ['id' => 1], 'step-b' => ['id' => 2]],
        );

        $this->assertSame(['id' => 1], $result->getStepResult('step-a'));
        $this->assertSame(['id' => 2], $result->getStepResult('step-b'));
        $this->assertNull($result->getStepResult('nonexistent'));
    }

    public function testSagaResultIsSuccessful() : void
    {
        $successResult = new SagaResult(
            success       : true,
            sagaId        : 'test-1',
            data          : [],
            completedSteps: [],
            stepResults   : [],
        );

        $failureResult = new SagaResult(
            success       : false,
            sagaId        : 'test-2',
            data          : [],
            completedSteps: [],
            stepResults   : [],
            failureReason : 'Something went wrong',
        );

        $this->assertTrue($successResult->isSuccessful());
        $this->assertFalse($failureResult->isSuccessful());
        $this->assertSame('Something went wrong', $failureResult->getFailureReason());
    }

    // ==================== SagaStep Tests ====================

    public function testSagaStepExecute() : void
    {
        $step = new SagaStep(
            name  : 'test-step',
            action: function (array $ctx) {
                return ['result' => $ctx['value'] * 2];
            },
        );

        $result = $step->execute(['value' => 5]);
        $this->assertSame(['result' => 10], $result);
    }

    public function testSagaStepCompensate() : void
    {
        $compensated = false;

        $step = new SagaStep(
            name        : 'test-step',
            action      : fn () => [],
            compensation: function () use (&$compensated) {
                $compensated = true;
            },
        );

        $step->compensate([]);
        $this->assertTrue($compensated);
    }

    public function testSagaStepWithoutCompensation() : void
    {
        $step = new SagaStep(
            name  : 'test-step',
            action: fn () => [],
        );

        $this->assertFalse($step->hasCompensation());
        $result = $step->compensate([]);
        $this->assertNull($result);
    }

    // ==================== Workflow Tests ====================

    public function testWorkflowStart() : void
    {
        $store    = new InMemorySagaStore();
        $workflow = new Workflow($store);

        // Define saga and save it to store first
        $saga = Saga::define('order-workflow')
            ->step('create-order', fn () => ['orderId' => 'ORD-1'])
            ->withStore($store);

        $store->save($saga);

        // Now use workflow to start
        $result = $workflow->start('order-workflow', ['item' => 'test']);

        $this->assertTrue($result->isSuccessful());
    }

    public function testWorkflowWithCustomStore() : void
    {
        $store    = new InMemorySagaStore();
        $workflow = new Workflow($store);

        $this->assertSame($store, $workflow->store());
    }

    // ==================== Complex Integration Test ====================

    public function testFullSagaLifecycleWithCompensation() : void
    {
        $events = [];

        $saga = Saga::define('full-lifecycle')
            ->step('create-order',
                function (array $ctx) use (&$events) {
                    $events[] = 'order-created';

                    return ['orderId' => 'ORD-1', ...$ctx];
                },
                function (array $ctx) use (&$events) {
                    $events[] = 'order-refunded';
                }
            )
            ->step('reserve-inventory',
                function (array $ctx) use (&$events) {
                    $events[] = 'inventory-reserved';

                    return ['reservationId' => 'RES-1'];
                },
                function (array $ctx) use (&$events) {
                    $events[] = 'inventory-released';
                }
            )
            ->step('process-payment',
                function (array $ctx) use (&$events) {
                    $events[] = 'payment-processing';
                    throw new RuntimeException('Payment declined');
                },
                function (array $ctx) use (&$events) {
                    $events[] = 'payment-refunded';
                }
            );

        $result = $saga->execute(['customerId' => 'CUST-1']);

        $this->assertFalse($result->isSuccessful());
        $this->assertSame('Payment declined', $result->getFailureReason());

        // Verify execution order
        $this->assertSame([
                              'order-created',
                              'inventory-reserved',
                              'payment-processing',
                              'inventory-released',  // Compensation in reverse
                              'order-refunded',
                          ], $events);

        // Verify saga state
        $this->assertSame(SagaState::Compensated, $saga->getStatus());
    }
}
