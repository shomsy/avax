<?php

declare(strict_types=1);

namespace Avax\Tests\Operations;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation\CompensationExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Saga;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaResult;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * Regression test for TODO-016: broken reference semantics in ApplicationWorkflow PublicSurface.
 *
 * Proves that Saga PublicSurface correctly resolves:
 * - SagaStep (Capabilities/Saga/SagaStep)
 * - SagaState (Capabilities/SagaState/SagaState)
 * - IdempotencyStore (Capabilities/Idempotency/IdempotencyStore)
 * - CompensationExecutor (Capabilities/Compensation/CompensationExecutor)
 */
final class SagaReferenceSemanticsTest extends TestCase
{
    public function test_saga_defines_returns_saga_instance() : void
    {
        $saga = Saga::define('test-reference-semantics');
        $this->assertInstanceOf(Saga::class, $saga);
    }

    public function test_saga_step_reference_resolves() : void
    {
        $step = new SagaStep(
            name        : 'test-step',
            action      : static fn() => true,
            compensation: null,
        );

        $this->assertSame('test-step', $step->name);
        $this->assertInstanceOf(Closure::class, $step->action);
    }

    public function test_saga_state_enum_reference_resolves() : void
    {
        $this->assertSame('running', SagaState::Running->value);
        $this->assertSame('completed', SagaState::Completed->value);
        $this->assertSame('failed', SagaState::Failed->value);
        $this->assertSame('compensating', SagaState::Compensating->value);
        $this->assertSame('compensated', SagaState::Compensated->value);
    }

    public function test_saga_status_uses_correct_state_reference() : void
    {
        $saga = Saga::define('test-state-ref');
        $this->assertSame(SagaState::Running, $saga->getStatus());

        $saga->fail('test');
        $this->assertSame(SagaState::Failed, $saga->getStatus());
    }

    public function test_idempotency_store_reference_resolves() : void
    {
        $store = new IdempotencyStore();
        $this->assertInstanceOf(IdempotencyStore::class, $store);
    }

    public function test_compensation_executor_reference_resolves() : void
    {
        $executor = new CompensationExecutor();
        $this->assertInstanceOf(CompensationExecutor::class, $executor);
    }

    public function test_saga_result_reference_resolves() : void
    {
        $result = new SagaResult(
            success       : true,
            sagaId        : 'test-1',
            data          : [],
            completedSteps: ['step-a'],
            stepResults   : ['step-a' => true],
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertSame('test-1', $result->sagaId);
    }

    public function test_saga_with_steps_builds_without_reference_errors() : void
    {
        // This proves all internal references resolve when building a saga with steps.
        // If SagaStep, SagaState, IdempotencyStore, or CompensationExecutor were broken,
        // this would throw a class-not-found error.
        $saga = Saga::define('test-build')
            ->step('step-a', static fn() => 'a')
            ->step('step-b', static fn() => 'b');

        $this->assertCount(2, $saga->getSteps());
        $this->assertSame(SagaState::Running, $saga->getStatus());
    }
}
