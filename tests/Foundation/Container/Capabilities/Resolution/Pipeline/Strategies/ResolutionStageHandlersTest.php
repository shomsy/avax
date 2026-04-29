<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Resolution\Pipeline\Strategies;

use Avax\Tests\TestCase;
use components\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use components\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionStageHandlers;
use components\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionState;
use components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;

final class ResolutionStageHandlersTest extends TestCase
{
    public function test_ordered_states_and_handlers() : void
    {
        $handlers = new ResolutionStageHandlers(handlers: [
                                                              ResolutionState::ContextualLookup->value => static fn (KernelContext $context) : string => 'contextual:' . $context->serviceId,
                                                              ResolutionState::DefinitionLookup->value => static fn (KernelContext $context) : string => 'definition:' . $context->serviceId,
                                                          ]);

        $this->assertSame(
            expected: [ResolutionState::ContextualLookup, ResolutionState::DefinitionLookup],
            actual  : $handlers->orderedStates()
        );

        $context = new KernelContext(serviceId: 'foo');
        $handler = $handlers->get(state: ResolutionState::ContextualLookup);
        $this->assertSame(expected: 'contextual:foo', actual: $handler($context));
    }

    public function test_next_state_after() : void
    {
        $handlers = new ResolutionStageHandlers(handlers: [
                                                              ResolutionState::ContextualLookup->value => static fn (KernelContext $context) : string => 'contextual',
                                                              ResolutionState::DefinitionLookup->value => static fn (KernelContext $context) : string => 'definition',
                                                          ]);

        $this->assertSame(
            expected: ResolutionState::DefinitionLookup,
            actual  : $handlers->nextStateAfter(state: ResolutionState::ContextualLookup)
        );
        $this->assertNull(actual: $handlers->nextStateAfter(state: ResolutionState::DefinitionLookup));
    }

    public function test_throws_on_missing_handler() : void
    {
        $handlers = new ResolutionStageHandlers(handlers: [
                                                              ResolutionState::ContextualLookup->value => static fn (KernelContext $context) : string => 'contextual',
                                                          ]);

        $this->expectException(exception: ContainerException::class);
        $handlers->get(state: ResolutionState::Autowire);
    }
}
