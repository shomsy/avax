<?php

declare(strict_types=1);

namespace components\Tests\Foundation\ApplicationWorkflow\Saga\DefineSaga;

use components\ApplicationWorkflow\Saga\DefineSaga\DefineSaga;
use components\ApplicationWorkflow\Saga\DefineSaga\InvalidSagaDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaCompensationDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use components\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use components\Tests\TestCase;

final class DefineSagaTest extends TestCase
{
    public function testValidLinearSagaDefinitionPassesValidation() : void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory', nextStepName: 'charge_card', hasSideEffect: true, compensation: new SagaCompensationDefinition(name: 'release_inventory')),
                               new SagaStepDefinition(name: 'charge_card'),
                           ]
        );

        new DefineSaga()->validate(definition: $definition);

        $this->assertSame('checkout', $definition->name);
    }

    public function testDuplicateStepNameFailsBeforeRuntime() : void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory'),
                               new SagaStepDefinition(name: 'reserve_inventory'),
                           ]
        );

        $this->expectException(InvalidSagaDefinition::class);
        $this->expectExceptionMessage('duplicate step');

        new DefineSaga()->validate(definition: $definition);
    }

    public function testUnknownNextStepFailsBeforeRuntime() : void
    {
        $definition = new SagaDefinition(
            name         : 'checkout',
            firstStepName: 'reserve_inventory',
            steps        : [
                               new SagaStepDefinition(name: 'reserve_inventory', nextStepName: 'missing'),
                           ]
        );

        $this->expectException(InvalidSagaDefinition::class);
        $this->expectExceptionMessage('unknown next step');

        new DefineSaga()->validate(definition: $definition);
    }
}
