<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\ApplicationWorkflow\Saga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use Avax\ApplicationWorkflow\Saga\RunSagaStep\SagaStepFailure;
use Avax\ApplicationWorkflow\Saga\Saga;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaStartCommand;
use Avax\Tests\TestCase;
use RuntimeException;
use PHPUnit\Framework\TestCase;

final class SagaLifecycleTest extends TestCase
{
    public function testStartSagaCreatesStateEventAndIdempotentResult() : void
    {
        $saga       = Saga::inMemory();
        $definition = $this->definition();
        $command    = new SagaStartCommand(definitionName: 'checkout', commandKey: 'checkout-123');

        $first  = $saga->start()->start(definition: $definition, command: $command);
        $second = $saga->start()->start(definition: $definition, command: $command);

        $state  = $saga->state()->read(instanceId: $first->id);
        $events = $saga->state()->readEvents(instanceId: $first->id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('reserve_inventory', $state->currentStepName);
        $this->assertSame('saga.started', $events[0]->type);
    }

    public function testRunSagaStepRecordsTransitionUntilCompletion() : void
    {
        $saga       = Saga::inMemory();
        $definition = $this->definition();
        $instance   = $saga->start()->start(
            definition: $definition,
            command   : new SagaStartCommand(definitionName: 'checkout', commandKey: 'checkout-456')
        );

        $firstStep  = $saga->runStep()->run(definition: $definition, instanceId: $instance->id);
        $secondStep = $saga->runStep()->run(definition: $definition, instanceId: $instance->id);

        $completion = $saga->complete()->complete(instanceId: $instance->id);
        $timeline   = $saga->inspect()->timeline(instanceId: $instance->id);

        $this->assertSame('charge_card', $firstStep->state->currentStepName);
        $this->assertSame('completed', $secondStep->state->status);
        $this->assertNotNull($completion);
        $this->assertCount(4, $timeline->events);
    }

    public function testFailedStepRecordsFailureStateAndEventBeforeThrowing() : void
    {
        $saga       = Saga::inMemory();
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(
                                   name   : 'reserve_inventory',
                                   runStep: static fn () : never => throw new RuntimeException(message: 'inventory unavailable')
                               ),
                           ]
        );
        $instance   = $saga->start()->start(
            definition: $definition,
            command   : new SagaStartCommand(definitionName: 'checkout', commandKey: 'checkout-789')
        );

        try {
            $saga->runStep()->run(definition: $definition, instanceId: $instance->id);
            $this->fail(message: 'Expected saga step failure.');
        } catch (SagaStepFailure) {
            $state    = $saga->state()->read(instanceId: $instance->id);
            $failures = $saga->inspect()->traceFailure(instanceId: $instance->id);

            $this->assertSame('failed', $state->status);
            $this->assertSame('saga.step.failed', $failures[0]->type);
        }
    }

    private function definition() : SagaDefinition
    {
        return new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory', nextStepName: 'charge_card', runStep: static fn () : string => 'reserved'),
                               new SagaStepDefinition(name: 'charge_card', runStep: static fn () : string => 'charged'),
                           ]
        );
    }
}
