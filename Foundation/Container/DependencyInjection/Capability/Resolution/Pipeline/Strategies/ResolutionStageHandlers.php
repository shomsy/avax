<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies;

use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;

/**
 * Ordered handler lookup for resolution discovery stages.
 */
final class ResolutionStageHandlers
{
    /** @var array<string, callable(KernelContext):mixed> */
    private array $handlers;

    /** @var list<ResolutionState> */
    private array $order;

    /** @param array<string|ResolutionState, callable(KernelContext):mixed> $handlers */
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

    public function get(ResolutionState $state) : callable
    {
        if (! array_key_exists($state->value, $this->handlers)) {
            throw new ContainerException(message: sprintf('No handler registered for state [%s]', $state->value));
        }

        return $this->handlers[$state->value];
    }

    /** @return list<ResolutionState> */
    public function orderedStates() : array
    {
        return $this->order;
    }

    public function nextStateAfter(ResolutionState $state) : ResolutionState|null
    {
        $index = array_search($state, $this->order, true);
        if ($index === false) {
            return null;
        }

        return $this->order[$index + 1] ?? null;
    }
}
