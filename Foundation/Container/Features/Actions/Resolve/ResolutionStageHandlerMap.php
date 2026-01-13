<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\ResolutionState;
use Avax\Container\Features\Core\Exceptions\ContainerException;

/**
 * Declarative mapping of resolution states to handler callables.
 *
 * @see docs/Features/Actions/Resolve/ResolutionStageHandlerMap.md#quick-summary
 */
final class ResolutionStageHandlerMap
{
    /** @var array<string, callable(KernelContext):mixed> */
    private array $handlers;

    /** @var list<ResolutionState> */
    private array $order;

    /**
     * @param array<string|ResolutionState, callable(KernelContext):mixed> $handlers
     *
     * @return void
     *
     * @see docs/Features/Actions/Resolve/ResolutionStageHandlerMap.md#method-__construct
     */
    public function __construct(array $handlers)
    {
        $this->handlers = [];
        $this->order    = [];

        foreach ($handlers as $state => $handler) {
            $stateEnum                         = $state instanceof ResolutionState ? $state : ResolutionState::from(value: $state);
            $this->handlers[$stateEnum->value] = $handler;
            $this->order[]                     = $stateEnum;
        }
    }

    /**
     * Get the handler callable for the given state.
     *
     * @param ResolutionState $state Target state.
     *
     * @return callable(KernelContext):mixed
     *
     * @throws ContainerException When no handler is registered for the state.
     *
     * @see docs/Features/Actions/Resolve/ResolutionStageHandlerMap.md#method-get
     */
    public function get(ResolutionState $state) : callable
    {
        if (! array_key_exists($state->value, $this->handlers)) {
            throw new ContainerException(message: sprintf('No handler registered for state [%s]', $state->value));
        }

        return $this->handlers[$state->value];
    }

    /**
     * @return list<ResolutionState>
     *
     * @see docs/Features/Actions/Resolve/ResolutionStageHandlerMap.md#method-orderedstates
     */
    public function orderedStates() : array
    {
        return $this->order;
    }

    /**
     * Determine the next state in the configured order.
     *
     * @param ResolutionState $state Current state.
     *
     * @return ResolutionState|null Next state or null when at the end.
     *
     * @see docs/Features/Actions/Resolve/ResolutionStageHandlerMap.md#method-nextstateafter
     */
    public function nextStateAfter(ResolutionState $state) : ResolutionState|null
    {
        $index = array_search($state, $this->order, true);
        if ($index === false) {
            return null;
        }

        return $this->order[$index + 1] ?? null;
    }
}
