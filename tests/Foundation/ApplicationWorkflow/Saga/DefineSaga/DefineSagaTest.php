<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\ApplicationWorkflow\Saga\DefineSaga;

use Avax\Tests\TestCase;
use components\ApplicationWorkflow\Saga\DefineSaga\DefineSaga;
use components\ApplicationWorkflow\Saga\DefineSaga\InvalidSagaDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaCompensationDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;

final class DefineSagaTest extends TestCase
{
    public function test_valid_linear_saga_definition_passes_validation(): void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory', nextStepName: 'charge_card', hasSideEffect: true, compensation: new SagaCompensationDefinition(name: 'release_inventory')),
                               new SagaStepDefinition(name: 'charge_card'),
                           ],
        );

        new DefineSaga()->validate(componentDefinition: $definition);

        $this->assertSame('checkout', $definition->name);
    }

    public function test_duplicate_step_name_fails_before_runtime(): void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory'),
                               new SagaStepDefinition(name: 'reserve_inventory'),
                           ],
        );

        $this->expectException(InvalidSagaDefinition::class);
        $this->expectExceptionMessage('duplicate step');

        new DefineSaga()->validate(componentDefinition: $definition);
    }

    public function test_unknown_next_step_fails_before_runtime(): void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory', nextStepName: 'missing'),
                           ],
        );

        $this->expectException(InvalidSagaDefinition::class);
        $this->expectExceptionMessage('unknown next step');

        new DefineSaga()->validate(componentDefinition: $definition);
    }
}
