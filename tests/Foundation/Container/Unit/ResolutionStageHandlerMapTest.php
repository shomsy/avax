<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\ResolutionState;
use Avax\Container\Features\Actions\Resolve\ResolutionStageHandlerMap;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/tests/Unit/ResolutionStageHandlerMapTest.md#quick-summary
 */
final class ResolutionStageHandlerMapTest extends TestCase
{
    /**
     * @see docs/tests/Unit/ResolutionStageHandlerMapTest.md#method-testorderedstatesandhandlers
     */
    public function test_ordered_states_and_handlers() : void
    {
        $map = new ResolutionStageHandlerMap(handlers: [
            ResolutionState::ContextualLookup->value => static fn(KernelContext $context) : string => 'contextual:' . $context->serviceId,
            ResolutionState::DefinitionLookup->value => static fn(KernelContext $context) : string => 'definition:' . $context->serviceId,
        ]);

        $this->assertSame(
            expected: [ResolutionState::ContextualLookup, ResolutionState::DefinitionLookup],
            actual  : $map->orderedStates()
        );

        $context = new KernelContext(serviceId: 'foo');
        $handler = $map->get(state: ResolutionState::ContextualLookup);
        $this->assertSame(expected: 'contextual:foo', actual: $handler($context));
    }

    /**
     * @see docs/tests/Unit/ResolutionStageHandlerMapTest.md#method-testnextstateafter
     */
    public function test_next_state_after() : void
    {
        $map = new ResolutionStageHandlerMap(handlers: [
            ResolutionState::ContextualLookup->value => static fn(KernelContext $context) : string => 'contextual',
            ResolutionState::DefinitionLookup->value => static fn(KernelContext $context) : string => 'definition',
        ]);

        $this->assertSame(
            expected: ResolutionState::DefinitionLookup,
            actual  : $map->nextStateAfter(state: ResolutionState::ContextualLookup)
        );
        $this->assertNull(actual: $map->nextStateAfter(state: ResolutionState::DefinitionLookup));
    }

    /**
     * @see docs/tests/Unit/ResolutionStageHandlerMapTest.md#method-testthrowsonmissinghandler
     */
    public function test_throws_on_missing_handler() : void
    {
        $map = new ResolutionStageHandlerMap(handlers: [
            ResolutionState::ContextualLookup->value => static fn(KernelContext $context) : string => 'contextual',
        ]);

        $this->expectException(exception: ContainerException::class);
        $map->get(state: ResolutionState::Autowire);
    }
}
